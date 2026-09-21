<?php
declare(strict_types=1);
require_once __DIR__ . '/bootstrap.php';
$u = require_login();
$pageTitle = 'DRX Wallet & Punti Vitalità · DEPENDEX';
$metaDesc = 'Gestione del tuo saldo DRX e Punti Vitalità maieutici: trasparenza, semplicità e impatto nella comunità dei Club.';
$canonicalUrl = 'https://dependex.social/wallet.php';

require '_header.php';
?>
<div class="container py-4" style="max-width: 960px; margin: 0 auto; padding: 0 1rem;">
  <section class="section-head mb-4 text-center">
    <span class="badge-neon-rainbow mb-2">
      <span class="dot"></span>
      <span>VALORE ETICO & COMUNITARIO</span>
    </span>
    <h1 style="font-family: var(--font-serif); font-size: clamp(1.6rem, 3.2vw, 2.4rem); color: #ffffff; font-weight: 800; margin: 0 0 8px;">
      DRX Wallet Personale
    </h1>
    <p style="color: #cbd5e1; font-size: 0.95rem; max-width: 650px; margin: 0 auto;">
      Dialogo, Relazioni ed eXperienza: il valore condiviso delle azioni positive nella vita reale e nei Club.
    </p>
  </section>

  <!-- WALLET BALANCE HERO CARD -->
  <section class="p-4 p-md-5 mb-4 text-center" style="background: radial-gradient(circle at center, rgba(20,26,45,0.95), rgba(10,13,22,0.98)); border: 2px solid rgba(253,230,138,0.35); border-radius: 24px; box-shadow: var(--rainbow-glow);">
    <span style="font-size: 0.82rem; color: #94a3b8; text-transform: uppercase; letter-spacing: 1px; font-weight: 700; display: block; margin-bottom: 6px;">
      Saldo Punti Vitalità (PV / DRX)
    </span>
    <div style="font-size: clamp(2.8rem, 7vw, 4.2rem); font-weight: 800; color: #fde68a; font-family: var(--font-serif); line-height: 1.1; margin-bottom: 12px;">
      <?=number_format((float)($u['drx_balance'] ?? 0), 0, ',', '.')?> <small style="font-size: 0.45em; color: #67e8f9;">DRX</small>
    </div>
    <div class="d-flex justify-content-center align-items-center gap-2 mb-4">
      <span class="badge-neon-rainbow" style="font-size: 0.8rem;">
        <span class="dot"></span>
        <span>Grado Attuale: <?=h(rank_for_drx((float)($u['drx_balance'] ?? 0)))?></span>
      </span>
    </div>
    <div class="d-flex justify-content-center gap-3 flex-wrap">
      <a class="btn-rainbow-neon small" href="vault.php" style="padding: 10px 22px;">
        <?=dx_icon('lock', '', 14)?> Esplora Community Vault
      </a>
      <a class="btn small" href="dashboard.php" style="background: rgba(255,255,255,0.06); border: 1px solid rgba(255,255,255,0.15); color: #fff; border-radius: 10px; padding: 10px 18px;">
        Dashboard Personale
      </a>
    </div>
  </section>

  <!-- 2 CARDS INFORMATIVE -->
  <div class="row g-4">
    <div class="col-md-6">
      <div class="p-4 h-100" style="background: rgba(12, 16, 28, 0.9); border: 1px solid rgba(6, 182, 212, 0.25); border-radius: 18px;">
        <h2 style="font-size: 1.2rem; color: #67e8f9; font-family: var(--font-serif); font-weight: 800; margin-bottom: 12px; display: flex; align-items: center; gap: 8px;">
          <?=dx_icon('shield', 'text-neon-cyan', 18)?>
          <span>Wallet Custodial Sicuro</span>
        </h2>
        <p style="font-size: 0.88rem; color: #cbd5e1; line-height: 1.5; margin-bottom: 16px;">
          Esperienza semplice e intuitiva senza barriere tecniche. Il tuo saldo resta custodito in modo sicuro sulla piattaforma e associato al tuo identificatore crittografico SIC-ID.
        </p>
        <button class="btn small" disabled style="opacity: 0.5; background: rgba(255,255,255,0.05); border: 1px solid rgba(255,255,255,0.1); color: #94a3b8; font-size: 0.8rem;">
          Trasferimento su Wallet Personale (Prossimamente)
        </button>
      </div>
    </div>

    <div class="col-md-6">
      <div class="p-4 h-100" style="background: rgba(12, 16, 28, 0.9); border: 1px solid rgba(224, 169, 109, 0.25); border-radius: 18px;">
        <h2 style="font-size: 1.2rem; color: #fde68a; font-family: var(--font-serif); font-weight: 800; margin-bottom: 12px; display: flex; align-items: center; gap: 8px;">
          <?=dx_icon('trending-up', 'text-neon-gold', 18)?>
          <span>Come maturare Punti Vitalità</span>
        </h2>
        <ul style="font-size: 0.88rem; color: #cbd5e1; line-height: 1.6; padding-left: 20px; margin: 0;">
          <li>Riflessione quotidiana nel diario di bordo personale (+15 PV)</li>
          <li>Completamento di un micro-passo di vita reale (+10 / +20 PV)</li>
          <li>Partecipazione attiva agli incontri settimanali del Club (+50 PV)</li>
          <li>Condivisione di testimonianze e supporto solidale (+30 PV)</li>
        </ul>
      </div>
    </div>
  </div>
</div>
<?php require '_footer.php'; ?>