<?php
/**
 * UNIVERSAL COMMERCE — ADMIN ORDER MANAGEMENT CONSOLE
 * Central oversight across all businesses, brands, orders, and payment statuses.
 */

declare(strict_types=1);

require_once __DIR__ . '/bootstrap.php';
require_once __DIR__ . '/modules/commerce/CommerceEnv.php';
require_once __DIR__ . '/modules/commerce/FatturaElettronicaService.php';

use Dependex\Commerce\UniversalCommerce;
use Dependex\Commerce\FatturaElettronicaService;

$u = current_user();
if (!$u || empty($u['is_admin'])) {
    // Check if user has admin privileges or fallback to prompt
    // For demo/console in local, if no is_admin field, check role
    if (!$u || !in_array($u['role'] ?? '', ['ADMIN', 'SUPER_ADMIN', 'COORDINATORE'])) {
        // Render minimal access check or allow if localhost/dev
        if (!in_array($_SERVER['REMOTE_ADDR'] ?? '', ['127.0.0.1', '::1'])) {
            header('Location: login.php?next=admin-orders.php');
            exit;
        }
    }
}

$commerce = UniversalCommerce::getInstance();
$db = $commerce->getDb();

// Filter parameters
$filterBiz = trim((string)($_GET['business_id'] ?? ''));
$filterStatus = trim((string)($_GET['status'] ?? ''));
$search = trim((string)($_GET['q'] ?? ''));

// Build SQL query
$sql = "
    SELECT o.*, c.email as customer_email, c.first_name, c.last_name, b.name as business_name
    FROM commerce_orders o
    LEFT JOIN commerce_customers c ON o.customer_id = c.id
    LEFT JOIN commerce_businesses b ON o.business_id = b.id
    WHERE 1=1
";
$params = [];

if ($filterBiz) {
    $sql .= " AND o.business_id = ?";
    $params[] = $filterBiz;
}
if ($filterStatus) {
    $sql .= " AND o.payment_status = ?";
    $params[] = $filterStatus;
}
if ($search) {
    $sql .= " AND (o.order_number LIKE ? OR c.email LIKE ? OR c.first_name LIKE ? OR c.last_name LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
    $params[] = "%$search%";
    $params[] = "%$search%";
}

$sql .= " ORDER BY o.created_at DESC LIMIT 100";

$stmt = $db->prepare($sql);
$stmt->execute($params);
$orders = $stmt->fetchAll(\PDO::FETCH_ASSOC);

// CSV Export Action
if (($_GET['action'] ?? '') === 'export_csv') {
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename=mirco_orders_' . date('Ymd_His') . '.csv');
    $out = fopen('php://output', 'w');
    fputcsv($out, ['Order Number', 'Date', 'Customer Email', 'Customer Name', 'Company Name', 'VAT Number', 'Fiscal Code', 'Business', 'Total Amount', 'Currency', 'Payment Status', 'Source Domain']);
    foreach ($orders as $row) {
        fputcsv($out, [
            $row['order_number'],
            $row['created_at'],
            $row['customer_email'],
            trim(($row['first_name'] ?? '') . ' ' . ($row['last_name'] ?? '')),
            $row['company_name'] ?? '',
            $row['vat_number'] ?? '',
            $row['fiscal_code'] ?? '',
            $row['business_name'] ?? '',
            number_format((float)$row['total_amount'], 2, '.', ''),
            $row['currency'],
            $row['payment_status'],
            $row['source_domain'] ?? ''
        ]);
    }
    fclose($out);
    exit;
}

// XML SDI Fattura Elettronica Export Action
if (($_GET['action'] ?? '') === 'export_xml' && !empty($_GET['order_id'])) {
    $xmlService = new FatturaElettronicaService($db);
    $res = $xmlService->generateXml((string)$_GET['order_id']);
    if ($res['ok']) {
        header('Content-Type: application/xml; charset=utf-8');
        header('Content-Disposition: attachment; filename=' . $res['filename']);
        echo $res['xml'];
        exit;
    } else {
        die("Errore generazione XML: " . htmlspecialchars($res['error']));
    }
}

// Fetch businesses for filter
$businesses = $db->query("SELECT id, name FROM commerce_businesses ORDER BY name ASC")->fetchAll(\PDO::FETCH_ASSOC);

// Metrics
$metricsStmt = $db->query("
    SELECT 
        COUNT(*) as total_orders,
        COALESCE(SUM(CASE WHEN payment_status = 'PAID' THEN total_amount ELSE 0 END), 0) as paid_revenue,
        COUNT(CASE WHEN payment_status = 'PAID' THEN 1 END) as paid_orders,
        COUNT(CASE WHEN payment_status = 'PENDING' THEN 1 END) as pending_orders
    FROM commerce_orders
");
$metrics = $metricsStmt->fetch(\PDO::FETCH_ASSOC);

$pageTitle = 'Console Ordini Universali · Mirco Universe';
include __DIR__ . '/_header.php';
?>

<div class="luxury-backdrop" style="min-height:90vh;padding:40px 16px;">
  <div class="content-container" style="max-width:1200px;margin:0 auto;">

    <!-- TOP HEADER -->
    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:28px;flex-wrap:wrap;gap:16px;">
      <div>
        <div style="display:flex;align-items:center;gap:8px;font-size:12px;color:var(--gold-primary);text-transform:uppercase;font-weight:700;letter-spacing:1px;margin-bottom:4px;">
          <?=dx_icon('shield', '', 14)?> Commerce Core Central
        </div>
        <h1 style="font-size:26px;font-weight:800;color:#FFF;margin:0;letter-spacing:-0.5px;display:flex;align-items:center;gap:10px;">
          <?=dx_icon('credit-card', '', 26)?> Console Amministrazione Ordini
        </h1>
        <p style="color:var(--text-muted);font-size:13px;margin:4px 0 0 0;">
          Gestione centralizzata vendite, PayPal capture, fatturazione e delivery per tutti i brand Mirco Pregnolato
        </p>
      </div>

      <div style="display:flex;align-items:center;gap:10px;">
        <a href="?<?=http_build_query(array_merge($_GET, ['action' => 'export_csv']))?>" class="btn" style="font-size:12px;padding:8px 14px;display:inline-flex;align-items:center;gap:6px;border-color:rgba(212,175,55,0.4);color:var(--gold-primary);">
          <?=dx_icon('trending-up', '', 14)?> Esporta CSV
        </a>
        <a href="cart.php" class="btn" style="font-size:12px;padding:8px 16px;display:inline-flex;align-items:center;gap:6px;">
          <?=dx_icon('shopping-cart', '', 14)?> Carrello
        </a>
        <a href="offers.php" class="btn primary" style="font-size:12px;padding:8px 16px;display:inline-flex;align-items:center;gap:6px;">
          <?=dx_icon('sparkles', '', 14)?> Offerte
        </a>
      </div>
    </div>

    <!-- METRICS CARDS -->
    <div style="display:grid;grid-template-columns:repeat(auto-fit, minmax(220px, 1fr));gap:16px;margin-bottom:28px;">
      <div class="card" style="background:var(--bg-card);border:1px solid rgba(212,175,55,0.2);padding:18px 20px;border-radius:14px;">
        <div style="font-size:12px;color:var(--text-muted);font-weight:600;margin-bottom:4px;">Fatturato Incassato (PAID)</div>
        <div style="font-size:24px;font-weight:800;color:var(--gold-primary);">
          €<?=number_format((float)$metrics['paid_revenue'], 2)?>
        </div>
      </div>
      <div class="card" style="background:var(--bg-card);border:1px solid rgba(212,175,55,0.2);padding:18px 20px;border-radius:14px;">
        <div style="font-size:12px;color:var(--text-muted);font-weight:600;margin-bottom:4px;">Ordini Saldati</div>
        <div style="font-size:24px;font-weight:800;color:#FFF;">
          <?=$metrics['paid_orders']?> <span style="font-size:13px;color:var(--text-muted);font-weight:400;">/ <?=$metrics['total_orders']?> totali</span>
        </div>
      </div>
      <div class="card" style="background:var(--bg-card);border:1px solid rgba(212,175,55,0.2);padding:18px 20px;border-radius:14px;">
        <div style="font-size:12px;color:var(--text-muted);font-weight:600;margin-bottom:4px;">In Attesa / Pending</div>
        <div style="font-size:24px;font-weight:800;color:#E5A93C;">
          <?=$metrics['pending_orders']?>
        </div>
      </div>
      <div class="card" style="background:var(--bg-card);border:1px solid rgba(212,175,55,0.2);padding:18px 20px;border-radius:14px;">
        <div style="font-size:12px;color:var(--text-muted);font-weight:600;margin-bottom:4px;">Gateway Attivo</div>
        <div style="font-size:18px;font-weight:700;color:#FFF;display:flex;align-items:center;gap:6px;">
          <?=dx_icon('check-circle', '', 16)?> PayPal REST v2
        </div>
        <div style="font-size:11px;color:var(--gold-primary);margin-top:2px;">Produzione Live Certificata</div>
      </div>
    </div>

    <!-- FILTERS BAR -->
    <div class="card" style="background:var(--bg-card);border:1px solid rgba(255,255,255,0.08);border-radius:14px;padding:16px 20px;margin-bottom:24px;">
      <form method="GET" style="display:flex;gap:12px;align-items:center;flex-wrap:wrap;">
        <div style="flex:1;min-width:200px;">
          <input type="text" name="q" value="<?=h($search)?>" placeholder="Cerca ordine, email o nome cliente..." 
            style="width:100%;background:rgba(255,255,255,0.05);border:1px solid rgba(255,255,255,0.15);border-radius:8px;padding:9px 12px;color:#FFF;font-size:13px;">
        </div>

        <div>
          <select name="business_id" style="background:rgba(255,255,255,0.05);border:1px solid rgba(255,255,255,0.15);border-radius:8px;padding:9px 12px;color:#FFF;font-size:13px;">
            <option value="">Tutti i Business</option>
            <?php foreach ($businesses as $b): ?>
              <option value="<?=h($b['id'])?>" <?=$filterBiz === $b['id'] ? 'selected' : ''?>><?=h($b['name'])?></option>
            <?php endforeach; ?>
          </select>
        </div>

        <div>
          <select name="status" style="background:rgba(255,255,255,0.05);border:1px solid rgba(255,255,255,0.15);border-radius:8px;padding:9px 12px;color:#FFF;font-size:13px;">
            <option value="">Tutti gli Stati</option>
            <option value="PAID" <?=$filterStatus === 'PAID' ? 'selected' : ''?>>PAID (Pagato)</option>
            <option value="PENDING" <?=$filterStatus === 'PENDING' ? 'selected' : ''?>>PENDING (In attesa)</option>
            <option value="FAILED" <?=$filterStatus === 'FAILED' ? 'selected' : ''?>>FAILED (Fallito)</option>
            <option value="REFUNDED" <?=$filterStatus === 'REFUNDED' ? 'selected' : ''?>>REFUNDED (Rimborsato)</option>
          </select>
        </div>

        <button type="submit" class="btn primary" style="padding:9px 18px;font-size:13px;font-weight:700;">
          Filtra
        </button>
        <?php if ($filterBiz || $filterStatus || $search): ?>
          <a href="admin-orders.php" class="btn" style="padding:9px 14px;font-size:13px;">Reset</a>
        <?php endif; ?>
      </form>
    </div>

    <!-- ORDERS TABLE -->
    <div class="card" style="background:var(--bg-card);border:1px solid rgba(212,175,55,0.18);border-radius:16px;overflow:hidden;box-shadow:0 8px 30px rgba(0,0,0,0.5);">
      <div style="overflow-x:auto;">
        <table style="width:100%;border-collapse:collapse;font-size:13px;text-align:left;">
          <thead>
            <tr style="border-bottom:1px solid rgba(255,255,255,0.1);background:rgba(255,255,255,0.02);color:var(--text-muted);font-size:12px;text-transform:uppercase;">
              <th style="padding:14px 18px;">Ordine</th>
              <th style="padding:14px 18px;">Data</th>
              <th style="padding:14px 18px;">Cliente</th>
              <th style="padding:14px 18px;">Business & Origine</th>
              <th style="padding:14px 18px;">Totale</th>
              <th style="padding:14px 18px;">Stato Pagamento</th>
              <th style="padding:14px 18px;text-align:right;">Azioni</th>
            </tr>
          </thead>
          <tbody>
            <?php if (empty($orders)): ?>
              <tr>
                <td colspan="7" style="padding:40px;text-align:center;color:var(--text-muted);">
                  Nessun ordine trovato con i filtri selezionati.
                </td>
              </tr>
            <?php else: ?>
              <?php foreach ($orders as $ord): ?>
                <tr style="border-bottom:1px solid rgba(255,255,255,0.05);transition:background 0.2s;" onmouseover="this.style.background='rgba(255,255,255,0.02)'" onmouseout="this.style.background='transparent'">
                  <td style="padding:14px 18px;font-weight:700;color:var(--gold-primary);">
                    <?=h($ord['order_number'])?>
                  </td>
                  <td style="padding:14px 18px;color:var(--text-muted);white-space:nowrap;">
                    <?=date('d/m/Y H:i', strtotime($ord['created_at']))?>
                  </td>
                  <td style="padding:14px 18px;">
                    <div style="font-weight:600;color:#FFF;"><?=h(trim(($ord['first_name'] ?? '') . ' ' . ($ord['last_name'] ?? ''))) ?: 'Ospite'?></div>
                    <div style="font-size:11px;color:var(--text-muted);"><?=h($ord['customer_email'])?></div>
                  </td>
                  <td style="padding:14px 18px;">
                    <div style="font-weight:600;color:#FFF;"><?=h($ord['business_name'] ?? 'Mirco Universe')?></div>
                    <div style="font-size:11px;color:var(--text-muted);"><?=h($ord['source_domain'] ?? 'dependex.social')?></div>
                  </td>
                  <td style="padding:14px 18px;font-weight:700;color:#FFF;white-space:nowrap;">
                    €<?=number_format((float)$ord['total_amount'], 2)?>
                  </td>
                  <td style="padding:14px 18px;">
                    <?php if ($ord['payment_status'] === 'PAID'): ?>
                      <span style="background:rgba(60,255,100,0.15);color:#44FF88;border:1px solid rgba(60,255,100,0.3);padding:3px 10px;border-radius:12px;font-size:11px;font-weight:700;">
                        PAGATO
                      </span>
                    <?php elseif ($ord['payment_status'] === 'PENDING'): ?>
                      <span style="background:rgba(255,180,60,0.15);color:#FFB43C;border:1px solid rgba(255,180,60,0.3);padding:3px 10px;border-radius:12px;font-size:11px;font-weight:700;">
                        IN ATTESA
                      </span>
                    <?php elseif ($ord['payment_status'] === 'REFUNDED'): ?>
                      <span style="background:rgba(180,180,180,0.15);color:#CCC;border:1px solid rgba(180,180,180,0.3);padding:3px 10px;border-radius:12px;font-size:11px;font-weight:700;">
                        RIMBORSATO
                      </span>
                    <?php else: ?>
                      <span style="background:rgba(255,60,60,0.15);color:#FF6666;border:1px solid rgba(255,60,60,0.3);padding:3px 10px;border-radius:12px;font-size:11px;font-weight:700;">
                        <?=h($ord['payment_status'])?>
                      </span>
                    <?php endif; ?>
                  </td>
                  <td style="padding:14px 18px;text-align:right;white-space:nowrap;display:flex;justify-content:flex-end;gap:6px;">
                    <a href="?action=export_xml&order_id=<?=urlencode($ord['id'])?>" class="btn" style="font-size:11px;padding:5px 9px;display:inline-flex;align-items:center;gap:4px;border-color:rgba(212,175,55,0.4);color:var(--gold-primary);" title="Scarica Fattura Elettronica XML per Agenzia delle Entrate">
                      <?=dx_icon('download', '', 12)?> XML SDI
                    </a>
                    <a href="order-confirmation.php?order_id=<?=urlencode($ord['id'])?>" target="_blank" class="btn" style="font-size:11px;padding:5px 10px;display:inline-flex;align-items:center;gap:4px;">
                      <?=dx_icon('eye', '', 12)?> Ricevuta
                    </a>
                  </td>
                </tr>
              <?php endforeach; ?>
            <?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>

  </div>
</div>

<?php include __DIR__ . '/_footer.php'; ?>
