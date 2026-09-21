<?php
declare(strict_types=1);
require_once __DIR__ . '/bootstrap.php';

$u = require_login();
$sic = trim((string)($_GET['form'] ?? $_POST['form'] ?? ''));

$st = db()->prepare("SELECT * FROM form_templates WHERE (sic_id = ? OR code = ?) AND status = 'ACTIVE'");
$st->execute([$sic, $sic]);
$f = $st->fetch();

if (!$f) {
    http_response_code(404);
    $pageTitle = 'Modulo Non Trovato · Form Factory';
    $metaDesc = 'Il modulo richiesto non è presente o non è attualmente attivo.';
    require __DIR__ . '/_header.php';
    ?>
    <main class="page-container" style="max-width: 700px; margin: 40px auto; padding: 24px 16px; text-align: center;">
      <section class="card" style="background: #101116; border: 1px solid rgba(212, 175, 55, 0.3); border-radius: 20px; padding: 40px 24px;">
        <h1 style="color: #FFFFFF; font-size: 1.6rem; margin-bottom: 16px;">Modulo Non Trovato</h1>
        <p style="color: var(--muted, #a1a1aa); margin-bottom: 24px;">
          Il questionario o modulo selezionato non è disponibile.
        </p>
        <a class="btn primary" href="form-builder.php">← Torna all'Elenco Moduli</a>
      </section>
    </main>
    <?php
    require __DIR__ . '/_footer.php';
    exit;
}

$schema = json_decode((string)$f['schema_json'], true) ?: [];
$done = false;
$err = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    try {
        $payload = [];
        foreach ($schema['fields'] ?? [] as $field) {
            $val = trim((string)($_POST[$field['name']] ?? ''));
            if (!empty($field['required']) && $val === '') {
                throw new RuntimeException('Compila il campo obbligatorio: ' . ($field['label'] ?? $field['name']));
            }
            $payload[$field['name']] = $val;
        }
        $ss = sic_id();
        db()->prepare('
            INSERT INTO form_submissions(sic_id, form_sic_id, user_sic_id, scope_sic_id, payload_json) 
            VALUES(?, ?, ?, ?, ?)
        ')->execute([
            $ss, $f['sic_id'], $u['sic_id'], null, 
            json_encode($payload, JSON_UNESCAPED_UNICODE)
        ]);
        audit($u['sic_id'], 'FORM_SUBMIT', $ss, ['form' => $f['code']]);
        $done = true;
    } catch (Throwable $e) {
        $err = $e->getMessage();
    }
}

$pageTitle = $f['title'] . ' · Compilazione Modulo';
$metaDesc = 'Compilazione guidata del modulo ' . $f['title'] . ' con registrazione SIC.';

require __DIR__ . '/_header.php';
?>

<main class="page-container" style="max-width: 800px; margin: 0 auto; padding: 24px 16px;">
  <section class="section-head" style="margin-bottom: 28px;">
    <div style="margin-bottom: 8px;">
      <a href="form-builder.php" style="color: #D4AF37; text-decoration: none; font-size: 0.9rem; font-weight: 600;">
        ← Torna ai Moduli
      </a>
    </div>
    <span class="eyebrow" style="color: var(--neon-gold, #D4AF37); font-weight: 700; text-transform: uppercase; font-size: 0.85rem; letter-spacing: 0.08em;">
      <?= h($f['code']) ?>
    </span>
    <h1 style="font-size: clamp(1.8rem, 4vw, 2.4rem); font-weight: 800; margin: 8px 0 10px; color: #FFFFFF;">
      <?= h($f['title']) ?>
    </h1>
    <p style="color: var(--muted, #a1a1aa); font-size: 1rem; line-height: 1.6;">
      Compila con cura i campi richiesti. I dati vengono trasmessi in modo cifrato e protetto.
    </p>
  </section>

  <?php if ($done): ?>
    <section class="card" style="background: rgba(74, 222, 128, 0.08); border: 1px solid rgba(74, 222, 128, 0.4); border-radius: 20px; padding: 36px 24px; text-align: center; margin-bottom: 32px;">
      <div style="font-size: 2.5rem; margin-bottom: 12px;">✓</div>
      <h2 style="color: #4ade80; font-size: 1.4rem; font-weight: 800; margin-bottom: 8px;">Modulo Inviato con Successo!</h2>
      <p style="color: #d1d5db; margin-bottom: 24px; font-size: 0.95rem;">
        La tua compilazione è stata registrata con identificativo crittografico nel sistema.
      </p>
      <a class="btn primary" href="form-builder.php" style="padding: 12px 24px; border-radius: 12px; text-decoration: none; font-weight: 700;">
        Torna all'Elenco Moduli
      </a>
    </section>
  <?php else: ?>
    <?php if ($err): ?>
      <div class="alert danger" style="background: rgba(239, 68, 68, 0.12); border: 1px solid rgba(239, 68, 68, 0.4); color: #f87171; padding: 16px 20px; border-radius: 12px; margin-bottom: 24px; font-weight: 600;">
        <?= h($err) ?>
      </div>
    <?php endif; ?>

    <section class="card" style="background: #101116; border: 1px solid rgba(212, 175, 55, 0.25); border-radius: 20px; padding: 32px 24px;">
      <form method="post" style="display: flex; flex-direction: column; gap: 20px;">
        <input type="hidden" name="<?= CSRF_KEY ?>" value="<?= h(csrf_token()) ?>">
        <input type="hidden" name="form" value="<?= h($f['sic_id']) ?>">

        <?php foreach ($schema['fields'] ?? [] as $x): 
          $fName = $x['name'];
          $fReq = !empty($x['required']);
        ?>
          <div>
            <label for="field-<?= h($fName) ?>" style="display: block; color: #FFFFFF; font-weight: 600; font-size: 0.95rem; margin-bottom: 6px;">
              <?= h($x['label']) ?> <?= $fReq ? '<span style="color: #f87171;">*</span>' : '' ?>
            </label>

            <?php if ($x['type'] === 'textarea'): ?>
              <textarea 
                id="field-<?= h($fName) ?>" 
                name="<?= h($fName) ?>" 
                rows="5"
                <?= $fReq ? 'required' : '' ?>
                style="width: 100%; padding: 10px 14px; background: rgba(255,255,255,0.05); border: 1px solid rgba(255,255,255,0.15); border-radius: 10px; color: #FFFFFF; font-size: 1rem; line-height: 1.5;"
              ></textarea>
            <?php elseif ($x['type'] === 'select'): ?>
              <select 
                id="field-<?= h($fName) ?>" 
                name="<?= h($fName) ?>" 
                <?= $fReq ? 'required' : '' ?>
                style="width: 100%; min-height: 44px; padding: 10px 14px; background: #181b22; border: 1px solid rgba(255,255,255,0.15); border-radius: 10px; color: #FFFFFF; font-size: 1rem;"
              >
                <option value="">-- Seleziona un'opzione --</option>
                <?php foreach ($x['options'] ?? [] as $o): ?>
                  <option value="<?= h($o) ?>"><?= h($o) ?></option>
                <?php endforeach; ?>
              </select>
            <?php else: ?>
              <input 
                id="field-<?= h($fName) ?>" 
                type="<?= h($x['type']) ?>" 
                name="<?= h($fName) ?>" 
                <?= $fReq ? 'required' : '' ?>
                style="width: 100%; min-height: 44px; padding: 10px 14px; background: rgba(255,255,255,0.05); border: 1px solid rgba(255,255,255,0.15); border-radius: 10px; color: #FFFFFF; font-size: 1rem;"
              >
            <?php endif; ?>
          </div>
        <?php endforeach; ?>

        <button type="submit" class="btn primary" style="align-self: flex-start; min-height: 44px; padding: 0 28px; border-radius: 12px; font-weight: 700; cursor: pointer;">
          Invia Modulo
        </button>
      </form>
    </section>
  <?php endif; ?>
</main>

<?php require __DIR__ . '/_footer.php'; ?>