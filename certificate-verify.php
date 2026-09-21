<?php
declare(strict_types=1);
require_once __DIR__ . '/bootstrap.php';

$tok = trim((string)($_GET['token'] ?? ''));
$c = null;
$searched = false;

if ($tok !== '') {
    $searched = true;
    $st = db()->prepare('SELECT * FROM certificates WHERE verify_token = ? OR sic_id = ?');
    $st->execute([$tok, $tok]);
    $c = $st->fetch();
}

$pageTitle = $c ? ($c['title'] . ' · Verifica Attestato') : 'Verifica Attestato Ufficiale · DEPENDEX';
$metaDesc = 'Strumento di verifica crittografica degli attestati e traguardi di formazione della Rete Hudolin e DEPENDEX.';

require __DIR__ . '/_header.php';
?>

<main class="page-container" style="max-width: 900px; margin: 32px auto; padding: 0 16px;">
  <section class="section-head" style="text-align: center; margin-bottom: 32px;">
    <span class="eyebrow" style="color: var(--neon-gold, #D4AF37); font-weight: 700; text-transform: uppercase; font-size: 0.85rem; letter-spacing: 0.08em;">
      Achievement & Certificate Engine
    </span>
    <h1 style="font-size: clamp(1.8rem, 4vw, 2.4rem); font-weight: 800; margin: 8px 0 12px; color: #FFFFFF;">
      Verifica Attestato Ufficiale
    </h1>
    <p style="color: var(--muted, #a1a1aa); max-width: 600px; margin: 0 auto; line-height: 1.6;">
      Verifica l'autenticità e l'integrità dei traguardi formativi rilasciati dall'ecosistema DEPENDEX e dalla Rete Hudolin.
    </p>
  </section>

  <!-- Form di ricerca/inserimento codice -->
  <section class="card" style="background: #101116; border: 1px solid rgba(212, 175, 55, 0.25); border-radius: 20px; padding: 28px; margin-bottom: 32px;">
    <form method="get" action="certificate-verify.php" style="display: flex; flex-direction: column; gap: 16px; max-width: 640px; margin: 0 auto;">
      <label for="token-input" style="color: #FFFFFF; font-weight: 600; font-size: 0.95rem;">
        Inserisci Token di Verifica o Codice SIC dell'Attestato:
      </label>
      <div style="display: flex; gap: 12px; flex-wrap: wrap;">
        <input 
          id="token-input"
          type="text" 
          name="token" 
          value="<?= h($tok) ?>" 
          placeholder="Es. cert-xxxx-xxxx o SIC-..." 
          required 
          style="flex: 1; min-width: 220px; min-height: 44px; padding: 10px 16px; background: rgba(255,255,255,0.05); border: 1px solid rgba(255,255,255,0.15); border-radius: 12px; color: #FFFFFF; font-size: 1rem;"
        >
        <button type="submit" class="btn primary" style="min-height: 44px; padding: 0 24px; border-radius: 12px; font-weight: 700; cursor: pointer;">
          Verifica Ora
        </button>
      </div>
    </form>
  </section>

  <?php if ($searched && !$c): ?>
    <section class="card" style="background: rgba(239, 68, 68, 0.08); border: 1px solid rgba(239, 68, 68, 0.3); border-radius: 16px; padding: 24px; text-align: center; margin-bottom: 32px;">
      <p style="color: #f87171; font-weight: 600; margin: 0;">
        Nessun attestato corrispondente al token o codice identificativo inserito. Verifica di aver digitato correttamente tutti i caratteri.
      </p>
    </section>
  <?php endif; ?>

  <?php if ($c): ?>
    <section class="certificate-sheet" style="background: #101116; border: 4px solid #D4AF37; border-radius: 24px; padding: 48px 32px; text-align: center; box-shadow: 0 16px 40px rgba(0,0,0,0.8), 0 0 30px rgba(212,175,55,0.15); margin-bottom: 32px;">
      <div style="display: inline-block; background: rgba(74, 222, 128, 0.12); color: #4ade80; border: 1px solid rgba(74, 222, 128, 0.3); padding: 6px 14px; border-radius: 20px; font-weight: 700; font-size: 0.85rem; margin-bottom: 20px;">
        ✓ Attestato Autentico e Verificato nel Registro
      </div>

      <p style="color: #D4AF37; font-weight: 800; letter-spacing: 0.15em; font-size: 0.95rem; margin-bottom: 8px;">
        DEPENDEX · RETE HUDOLIN · TRAGUARDI VIVI
      </p>

      <h2 style="color: #FFFFFF; font-size: clamp(1.8rem, 4vw, 2.5rem); font-weight: 900; margin: 0 0 12px; line-height: 1.2;">
        <?= h($c['title']) ?>
      </h2>

      <p style="color: #d1d5db; font-size: 1.1rem; margin-bottom: 16px;">
        Attestato digitale di completamento e traguardo
      </p>

      <h3 style="color: #FFF2B2; font-size: 1.3rem; font-weight: 700; margin: 0 0 20px;">
        <?= h($c['certificate_type']) ?>
      </h3>

      <div style="max-width: 500px; margin: 0 auto 24px; padding: 16px; background: rgba(255,255,255,0.03); border-radius: 12px; border: 1px solid rgba(255,255,255,0.06); text-align: left; font-size: 0.9rem; color: #a1a1aa; line-height: 1.6;">
        <div><strong>Emesso il:</strong> <?= h($c['issued_at']) ?></div>
        <div><strong>Identificativo SIC:</strong> <code style="color: #FFF2B2;"><?= h($c['sic_id']) ?></code></div>
        <div><strong>Token di Validazione:</strong> <code style="color: #D4AF37;"><?= h($c['verify_token']) ?></code></div>
      </div>

      <div style="display: flex; justify-content: center; gap: 16px; flex-wrap: wrap;">
        <button onclick="window.print()" class="btn primary" style="min-height: 44px; padding: 0 24px; border-radius: 12px; font-weight: 700; cursor: pointer;">
          🖨️ Stampa / Salva in PDF
        </button>
        <a href="academy.php" class="btn" style="min-height: 44px; line-height: 44px; padding: 0 20px; border-radius: 12px; text-decoration: none; font-weight: 600;">
          Esplora Corsi Academy
        </a>
      </div>
    </section>
  <?php endif; ?>
</main>

<style>
@media print {
  body { background: #FFFFFF !important; color: #000000 !important; }
  header, footer, .section-head, form, .btn, .card { display: none !important; }
  .certificate-sheet { border: 2px solid #000000 !important; box-shadow: none !important; background: #FFFFFF !important; color: #000000 !important; }
  .certificate-sheet h2, .certificate-sheet h3, .certificate-sheet p { color: #000000 !important; }
}
</style>

<?php require __DIR__ . '/_footer.php'; ?>