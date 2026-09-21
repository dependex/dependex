<?php
declare(strict_types=1);
require_once __DIR__ . '/bootstrap.php';

$u = require_login();
$code = trim((string)($_GET['template'] ?? $_POST['template'] ?? ''));

$st = db()->prepare('SELECT * FROM document_templates WHERE code = ?');
$st->execute([$code]);
$t = $st->fetch();

if (!$t) {
    http_response_code(404);
    $pageTitle = 'Modello Non Trovato · Document Factory';
    $metaDesc = 'Il modello documentale richiesto non è presente.';
    require __DIR__ . '/_header.php';
    ?>
    <main class="page-container" style="max-width: 700px; margin: 40px auto; padding: 24px 16px; text-align: center;">
      <section class="card" style="background: #101116; border: 1px solid rgba(212, 175, 55, 0.3); border-radius: 20px; padding: 40px 24px;">
        <h1 style="color: #FFFFFF; font-size: 1.6rem; margin-bottom: 16px;">Modello Documentale Non Trovato</h1>
        <p style="color: var(--muted, #a1a1aa); margin-bottom: 24px;">
          Il template specificato non è disponibile nell'archivio modelli.
        </p>
        <a class="btn primary" href="documents.php">← Torna all'Elenco Modelli</a>
      </section>
    </main>
    <?php
    require __DIR__ . '/_footer.php';
    exit;
}

$done = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $title = trim((string)($_POST['title'] ?? $t['title']));
    $body = trim((string)($_POST['body'] ?? ''));
    $docDate = trim((string)($_POST['date'] ?? date('Y-m-d')));

    $html = '<article class="print-doc" style="max-width: 800px; margin: 0 auto; font-family: system-ui, sans-serif; padding: 40px; color: #111827;">'
          . '<h1 style="font-size: 2rem; margin-bottom: 12px; border-bottom: 2px solid #D4AF37; padding-bottom: 8px;">' . h($title) . '</h1>'
          . '<p style="font-size: 0.95rem; color: #4b5563; margin-bottom: 24px;"><strong>Data Emissione:</strong> ' . h($docDate) . '</p>'
          . '<div style="font-size: 1.05rem; line-height: 1.7; white-space: pre-wrap;">' . nl2br(h($body)) . '</div>'
          . '</article>';

    $sic = sic_id();
    $dir = __DIR__ . '/storage/generated';
    if (!is_dir($dir)) {
        mkdir($dir, 0770, true);
    }
    $path = $dir . '/' . $sic . '.html';
    file_put_contents($path, '<!doctype html><meta charset="utf-8"><title>' . h($title) . '</title>' . $html);
    $hash = hash_file('sha256', $path);

    db()->prepare('
        INSERT INTO generated_documents(sic_id, template_sic_id, owner_sic_id, title, payload_json, html_path, sha256) 
        VALUES(?, ?, ?, ?, ?, ?, ?)
    ')->execute([
        $sic, $t['sic_id'], $u['sic_id'], $title, 
        json_encode($_POST, JSON_UNESCAPED_UNICODE), $path, $hash
    ]);
    $done = $sic;
}

$pageTitle = 'Compila: ' . $t['title'] . ' · Document Factory';
$metaDesc = 'Compilazione guidata del documento associativo ' . $t['title'] . '.';

require __DIR__ . '/_header.php';
?>

<main class="page-container" style="max-width: 840px; margin: 0 auto; padding: 24px 16px;">
  <section class="section-head" style="margin-bottom: 28px;">
    <div style="margin-bottom: 8px;">
      <a href="documents.php" style="color: #D4AF37; text-decoration: none; font-size: 0.9rem; font-weight: 600;">
        ← Torna ai Modelli
      </a>
    </div>
    <span class="eyebrow" style="color: var(--neon-gold, #D4AF37); font-weight: 700; text-transform: uppercase; font-size: 0.85rem; letter-spacing: 0.08em;">
      <?= h($t['category'] ?? 'MODELLO ASSOCIATIVO') ?>
    </span>
    <h1 style="font-size: clamp(1.8rem, 4vw, 2.4rem); font-weight: 800; margin: 8px 0 10px; color: #FFFFFF;">
      <?= h($t['title']) ?>
    </h1>
    <p style="color: var(--muted, #a1a1aa); font-size: 1rem; line-height: 1.6;">
      <?= h($t['description'] ?? 'Compila i dettagli del documento. Potrai visualizzarlo, stamparlo o salvarlo in formato PDF.') ?>
    </p>
  </section>

  <?php if ($done): ?>
    <section class="card" style="background: rgba(74, 222, 128, 0.08); border: 1px solid rgba(74, 222, 128, 0.4); border-radius: 20px; padding: 36px 24px; text-align: center; margin-bottom: 32px;">
      <div style="font-size: 2.5rem; margin-bottom: 12px;">✓</div>
      <h2 style="color: #4ade80; font-size: 1.4rem; font-weight: 800; margin-bottom: 8px;">Documento Generato con Successo!</h2>
      <p style="color: #d1d5db; margin-bottom: 20px; font-size: 0.95rem;">
        Identificativo Univoco SIC: <code style="color: #FFF2B2;"><?= h($done) ?></code>
      </p>
      <div style="display: flex; gap: 12px; justify-content: center; flex-wrap: wrap;">
        <a class="btn primary" href="document-print.php?id=<?= urlencode($done) ?>" target="_blank" style="padding: 12px 24px; border-radius: 12px; text-decoration: none; font-weight: 700;">
          🖨️ Apri e Stampa in PDF
        </a>
        <a class="btn" href="documents.php" style="padding: 12px 20px; border-radius: 12px; text-decoration: none; font-weight: 600;">
          Torna all'Elenco Modelli
        </a>
      </div>
    </section>
  <?php else: ?>
    <section class="card" style="background: #101116; border: 1px solid rgba(212, 175, 55, 0.25); border-radius: 20px; padding: 32px 24px;">
      <form method="post" style="display: flex; flex-direction: column; gap: 20px;">
        <input type="hidden" name="<?= CSRF_KEY ?>" value="<?= h(csrf_token()) ?>">
        <input type="hidden" name="template" value="<?= h($code) ?>">

        <div>
          <label for="doc-title" style="display: block; color: #FFFFFF; font-weight: 600; font-size: 0.95rem; margin-bottom: 6px;">
            Titolo Ufficiale del Documento:
          </label>
          <input 
            id="doc-title" 
            name="title" 
            type="text" 
            value="<?= h($t['title']) ?>" 
            required 
            style="width: 100%; min-height: 44px; padding: 10px 16px; background: rgba(255,255,255,0.05); border: 1px solid rgba(255,255,255,0.15); border-radius: 12px; color: #FFFFFF; font-size: 1rem;"
          >
        </div>

        <div>
          <label for="doc-date" style="display: block; color: #FFFFFF; font-weight: 600; font-size: 0.95rem; margin-bottom: 6px;">
            Data di Riferimento:
          </label>
          <input 
            id="doc-date" 
            name="date" 
            type="date" 
            value="<?= date('Y-m-d') ?>" 
            required 
            style="width: 100%; min-height: 44px; padding: 10px 16px; background: rgba(255,255,255,0.05); border: 1px solid rgba(255,255,255,0.15); border-radius: 12px; color: #FFFFFF; font-size: 1rem;"
          >
        </div>

        <div>
          <label for="doc-body" style="display: block; color: #FFFFFF; font-weight: 600; font-size: 0.95rem; margin-bottom: 6px;">
            Contenuto e Note del Documento:
          </label>
          <textarea 
            id="doc-body" 
            name="body" 
            rows="10" 
            placeholder="Inserisci il verbale, le deliberazioni o i punti concordati..."
            required
            style="width: 100%; padding: 12px 16px; background: rgba(255,255,255,0.05); border: 1px solid rgba(255,255,255,0.15); border-radius: 12px; color: #FFFFFF; font-size: 1rem; line-height: 1.6;"
          ></textarea>
        </div>

        <button type="submit" class="btn primary" style="align-self: flex-start; min-height: 44px; padding: 0 28px; border-radius: 12px; font-weight: 700; cursor: pointer;">
          Genera Documento con Marcatura SIC
        </button>
      </form>
    </section>
  <?php endif; ?>
</main>

<?php require __DIR__ . '/_footer.php'; ?>