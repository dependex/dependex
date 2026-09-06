<?php 
require_once 'bootstrap.php';
$public = isset($_GET['public']);
if (!$public) $u = current_user(); else $u = null;
$pageTitle = 'Trova il tuo Club Territoriale · Rete Mondiale Viva';
$metaDesc = 'Cerca tra i 542 Club Alcologici Territoriali. Una sedia libera per te e la tua famiglia, senza liste d’attesa né giudizi.';
require '_header.php';
require '_dependex-world-map.php';
?>

<section class="hero compact" style="text-align:center;padding:3rem 1.5rem 2rem;">
  <div class="gold-glow-badge mb-3">
    <?=dx_icon('compass', '', 14)?>
    <span>RETE ECOLOGICO-SOCIALE HUDOLIN · 542 CLUB VIVI</span>
  </div>
  <h1 style="font-size:clamp(1.8rem, 3.5vw, 2.6rem);font-weight:800;letter-spacing:-0.02em;margin:0.5rem 0 0.8rem;color:#FFFFFF;">
    Trova il Club più vicino a casa tua.<br>
    <span class="gold-foil-text">
      C’è una sedia pronta per te, senza formalità né burocrazia.
    </span>
  </h1>
  <p style="max-width:700px;margin:0 auto;color:#d1d5db;font-size:1.05rem;line-height:1.65;">
    Nei Club Alcologici Territoriali non ci sono cartelle cliniche esposte né diagnosi punitive. C’è un cerchio di persone e famiglie che hanno conosciuto la stessa sofferenza e che oggi camminano insieme, una settimana alla volta.
  </p>
  
  <div style="display:flex;justify-content:center;gap:1.5rem;flex-wrap:wrap;margin-top:1.5rem;font-size:0.9rem;color:#D4AF37;">
    <span style="display:inline-flex;align-items:center;gap:6px;">
      <?=dx_icon('check-circle', '', 16)?> <b style="color:#FFFFFF;">Completamente Gratuito</b>
    </span>
    <span style="display:inline-flex;align-items:center;gap:6px;">
      <?=dx_icon('check-circle', '', 16)?> <b style="color:#FFFFFF;">Aperto a Famigliari e Amici</b>
    </span>
    <span style="display:inline-flex;align-items:center;gap:6px;">
      <?=dx_icon('check-circle', '', 16)?> <b style="color:#FFFFFF;">Riservatezza & Dignità Assoluta</b>
    </span>
  </div>
</section>

<div style="margin-top:1rem;">
  <?=dependex_world_map_card('70vh')?>
</div>

<section class="lux-metallic-card p-4 p-md-5 my-5 text-center" style="border: 1px solid rgba(212,175,55,0.3);">
  <h3 style="margin-top:0;font-size:1.4rem;color:#FFFFFF;font-family:var(--font-serif);">
    Non riesci a raggiungere un Club fisico o preferisci un primo contatto riservato?
  </h3>
  <p style="color:#cbd5e1;max-width:640px;margin:0.5rem auto 1.5rem;font-size:0.96rem;line-height:1.6;">
    Oltre alla presenza fisica sul territorio, puoi approfondire con i manuali e i diari ufficiali Amazon KDP o dialogare in totale anonimato con un Servitore-Insegnante della rete.
  </p>
  <div style="display:flex;justify-content:center;gap:1rem;flex-wrap:wrap;">
    <a class="btn primary" href="offers.php" style="padding:0 24px;">
      <?=dx_icon('book-open', '', 16)?>
      <span style="margin-left:6px;">Libri & Manuali Amazon KDP</span>
    </a>
    <a class="btn-rainbow-outline" href="help.php" style="padding:0 22px;">
      <?=dx_icon('shield', '', 16)?>
      <span style="margin-left:6px;">Richiedi Orientamento Riservato</span>
    </a>
  </div>
</section>

<?php require '_footer.php';?>