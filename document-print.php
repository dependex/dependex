<?php
declare(strict_types=1);
require_once __DIR__ . '/bootstrap.php';

$u = require_login();
$id = trim((string)($_GET['id'] ?? ''));

$st = db()->prepare('SELECT * FROM generated_documents WHERE sic_id = ? AND owner_sic_id = ?');
$st->execute([$id, $u['sic_id']]);
$d = $st->fetch();

if (!$d) {
    http_response_code(404);
    $pageTitle = 'Documento Non Trovato · Archivio';
    $metaDesc = 'Il documento richiesto non è presente o non hai i permessi di lettura.';
    require __DIR__ . '/_header.php';
    ?>
    <main class="page-container" style="max-width: 700px; margin: 40px auto; padding: 24px 16px; text-align: center;">
      <section class="card" style="background: #101116; border: 1px solid rgba(212, 175, 55, 0.3); border-radius: 20px; padding: 40px 24px;">
        <h1 style="color: #FFFFFF; font-size: 1.6rem; margin-bottom: 16px;">Documento Non Trovato</h1>
        <p style="color: var(--muted, #a1a1aa); margin-bottom: 24px;">
          Il documento cercato non è disponibile nel tuo archivio o è stato rimosso.
        </p>
        <a class="btn primary" href="documents.php">← Torna alla Modulistica</a>
      </section>
    </main>
    <?php
    require __DIR__ . '/_footer.php';
    exit;
}

$payload = json_decode((string)($d['payload_json'] ?? '{}'), true) ?: [];
?>
<!doctype html>
<html lang="it">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?= h($d['title']) ?> · DEPENDEX Document</title>
  <style>
    body {
      font-family: system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
      margin: 0;
      padding: 32px 16px;
      background: #070709;
      color: #FFFFFF;
    }
    .paper {
      max-width: 800px;
      margin: 0 auto;
      background: #FFFFFF;
      color: #111827;
      padding: 48px;
      border-radius: 12px;
      box-shadow: 0 16px 40px rgba(0,0,0,0.8);
    }
    .head {
      border-bottom: 2px solid #D4AF37;
      padding-bottom: 16px;
      margin-bottom: 24px;
    }
    .sic {
      font-family: monospace;
      font-size: 0.8rem;
      color: #6b7280;
    }
    .body {
      white-space: pre-wrap;
      line-height: 1.7;
      font-size: 1.05rem;
      margin-bottom: 32px;
    }
    .actions {
      max-width: 800px;
      margin: 24px auto 0;
      display: flex;
      gap: 12px;
      justify-content: flex-end;
    }
    .btn {
      display: inline-flex;
      align-items: center;
      padding: 10px 20px;
      border-radius: 10px;
      font-weight: 700;
      cursor: pointer;
      text-decoration: none;
      font-size: 0.95rem;
    }
    .btn-gold {
      background: linear-gradient(135deg, #FFF2B2, #D4AF37);
      color: #070709;
      border: 0;
    }
    .btn-outline {
      background: transparent;
      color: #FFFFFF;
      border: 1px solid rgba(255,255,255,0.2);
    }
    @media print {
      body { background: #FFFFFF; color: #000000; padding: 0; }
      .paper { box-shadow: none; padding: 0; border-radius: 0; }
      .actions { display: none !important; }
      @page { size: A4; margin: 20mm; }
    }
  </style>
</head>
<body>
  <div class="actions">
    <a href="documents.php" class="btn btn-outline">← Torna all'Archivio</a>
    <button onclick="window.print()" class="btn btn-gold">🖨️ Stampa / Salva come PDF</button>
  </div>

  <main class="paper">
    <div class="head">
      <div style="display: flex; justify-content: space-between; align-items: baseline; flex-wrap: wrap; margin-bottom: 8px;">
        <span style="font-weight: 800; color: #b45309; font-size: 0.85rem; letter-spacing: 0.1em; text-transform: uppercase;">
          DEPENDEX · RETE HUDOLIN
        </span>
        <span style="font-size: 0.85rem; color: #6b7280;">
          Data: <?= h((string)($payload['date'] ?? date('Y-m-d'))) ?>
        </span>
      </div>
      <h1 style="font-size: 1.8rem; margin: 0 0 8px; color: #111827;">
        <?= h($d['title']) ?>
      </h1>
      <div class="sic">Identificativo SIC: <?= h($d['sic_id']) ?></div>
    </div>

    <div class="body">
      <?= h($payload['body'] ?? '') ?>
    </div>

    <div style="border-top: 1px solid #e5e7eb; padding-top: 16px; font-size: 0.8rem; color: #6b7280; display: flex; justify-content: space-between; flex-wrap: wrap; gap: 8px;">
      <span>Impronta SHA-256: <code><?= h(substr((string)$d['sha256'], 0, 24)) ?>...</code></span>
      <span>Generato tramite DEPENDEX Document Factory</span>
    </div>
  </main>
</body>
</html>