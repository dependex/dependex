<?php 
require_once 'bootstrap.php';
$public = isset($_GET['public']);
if (!$public) $u = current_user(); else $u = null;
$pageTitle = 'Trova il tuo Club Territoriale · Rete Mondiale Viva';
$metaDesc = 'Cerca tra i 542 Club Alcologici Territoriali. Una sedia libera per te e la tua famiglia, senza liste d’attesa né giudizi.';
require '_header.php';
require '_dependex-world-map.php';
?>

<section class="hero compact" style="text-align:center;padding:2.5rem 1.5rem 1.5rem;">
  <div class="eyebrow" style="color:var(--lux-gold);letter-spacing:0.12em;">RETE ECOLOGICO-SOCIALE HUDOLIN · 542 CLUB</div>
  <h1 style="font-size:clamp(1.7rem, 3.2vw, 2.4rem);font-weight:800;letter-spacing:-0.02em;margin:0.5rem 0 0.8rem;">
    Trova il Club più vicino a casa tua.<br>
    <span style="background:linear-gradient(135deg,var(--lux-gold),#fff);-webkit-background-clip:text;-webkit-text-fill-color:transparent;">
      C’è una sedia pronta per te, senza formalità né burocrazia.
    </span>
  </h1>
  <p style="max-width:680px;margin:0 auto;color:var(--text-muted);font-size:1rem;line-height:1.6;">
    Nei Club Alcologici Territoriali non ci sono cartelle cliniche esposte o diagnosi punitive. C’è un cerchio di persone e famiglie che hanno conosciuto la stessa sofferenza e che oggi si sostengono a vicenda, una settimana alla volta.
  </p>
  
  <div style="display:flex;justify-content:center;gap:1.5rem;flex-wrap:wrap;margin-top:1.2rem;font-size:0.88rem;color:var(--text-main);">
    <span>✓ <b>Completamente Gratuito</b></span>
    <span>✓ <b>Aperto a Famigliari e Amici</b></span>
    <span>✓ <b>Riservatezza & Dignità Assoluta</b></span>
  </div>
</section>

<div style="margin-top:1rem;">
  <?=dependex_world_map_card('70vh')?>
</div>

<section class="lux-card" style="margin:2.5rem 0 3rem;text-align:center;">
  <h3 style="margin-top:0;font-size:1.2rem;">Non riesci a trovare un Club nella tua zona o preferisci un primo contatto online?</h3>
  <p style="color:var(--text-muted);max-width:620px;margin:0.5rem auto 1.2rem;font-size:0.95rem;">
    Oltre alla presenza fisica sul territorio, puoi iniziare subito con il nostro Starter Kit digitale o dialogare in modo riservato con un facilitatore della community.
  </p>
  <div style="display:flex;justify-content:center;gap:1rem;flex-wrap:wrap;">
    <a class="btn primary" href="offers.php">Scopri il Percorso Guidato</a>
    <a class="btn" href="help.php">Richiedi Orientamento Riservato</a>
  </div>
</section>

<?php require '_footer.php';?>