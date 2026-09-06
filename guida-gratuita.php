<?php
/**
 * DEPENDEX & OLTRE — GUIDA GRATUITA PER LA FAMIGLIA & DIARIO
 * Lead Magnet ad alta conversione per attrarre e accogliere persone e famiglie:
 * "I Primi 7 Giorni: Guida di Orientamento e Rinascita per la Famiglia nel Metodo Ecologico-Sociale"
 */

declare(strict_types=1);

require_once __DIR__ . '/bootstrap.php';
require_once __DIR__ . '/email-engine.php';

$viewDocument = isset($_GET['view']) && $_GET['view'] === 'document';
$submitted = false;
$userEmail = '';
$userName = '';
$resultClubs = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim(strtolower((string)($_POST['email'] ?? '')));
    $nome = trim((string)($_POST['nome'] ?? ''));
    $city = trim((string)($_POST['citta'] ?? ''));

    if ($email && filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $userEmail = $email;
        $userName = $nome ?: 'Amico/a';
        $submitted = true;

        if ($city) {
            $pdo = db();
            $stmt = $pdo->prepare("
                SELECT sic_id, entity_name, region, province, address, meeting_day, meeting_time 
                FROM network_entities 
                WHERE province LIKE ? OR region LIKE ? OR address LIKE ? OR entity_name LIKE ?
                LIMIT 3
            ");
            $like = '%' . $city . '%';
            $stmt->execute([$like, $like, $like, $like]);
            $resultClubs = $stmt->fetchAll(PDO::FETCH_ASSOC);
        }

        email_os_track_event('lead_created', $email, [
            'nome' => $nome,
            'citta' => $city,
            'magnet_id' => 'guida_7_giorni',
            'clubs_found' => count($resultClubs),
            'source' => 'guida_gratuita_landing'
        ]);

        email_os_enroll_contact($email, $nome, '', 'guida_7_giorni');
    }
}

// Vista documento stampabile / PDF
if ($viewDocument):
?>
<!doctype html>
<html lang="it">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>I Primi 7 Giorni: Guida di Orientamento per la Famiglia · Metodo Hudolin</title>
  <style>
    @page { size: A4; margin: 18mm 16mm; }
    body { font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif; color: #111827; line-height: 1.6; margin: 0; padding: 24px; background: #fafafa; }
    .doc-container { max-width: 780px; margin: 0 auto; background: #ffffff; padding: 48px; border-radius: 12px; box-shadow: 0 4px 20px rgba(0,0,0,0.08); }
    .doc-head { border-bottom: 2px solid #D4AF37; padding-bottom: 20px; margin-bottom: 28px; }
    .doc-tag { font-size: 11px; font-weight: 800; letter-spacing: 0.08em; text-transform: uppercase; color: #854d0e; background: #fef08a; padding: 4px 10px; border-radius: 4px; display: inline-block; margin-bottom: 8px; }
    h1 { font-size: 26px; color: #0f172a; margin: 8px 0 12px; line-height: 1.25; }
    .subtitle { font-size: 15px; color: #475569; font-weight: 500; margin-bottom: 0; }
    .day-box { margin: 24px 0; padding: 20px; border-left: 4px solid #D4AF37; background: #fdfaf0; border-radius: 0 8px 8px 0; }
    .day-title { font-size: 17px; font-weight: 800; color: #78350f; margin-bottom: 8px; display: flex; align-items: center; gap: 8px; }
    .quote-box { background: #f1f5f9; border-radius: 8px; padding: 16px 20px; font-style: italic; color: #334155; margin: 24px 0; }
    .cta-box { background: #0b0f19; color: #ffffff; padding: 24px; border-radius: 10px; margin-top: 36px; text-align: center; }
    .btn-print { background: #D4AF37; color: #000000; border: none; font-weight: 800; font-size: 15px; padding: 12px 24px; border-radius: 8px; cursor: pointer; margin-bottom: 20px; display: inline-flex; align-items: center; gap: 8px; }
    @media print {
      body { background: #ffffff; padding: 0; }
      .doc-container { box-shadow: none; padding: 0; }
      .no-print { display: none !important; }
    }
  </style>
</head>
<body>
  <div class="no-print" style="max-width: 780px; margin: 0 auto 16px; display: flex; justify-content: space-between; align-items: center;">
    <a href="guida-gratuita.php" style="color: #475569; text-decoration: none; font-size: 14px; font-weight: 600;">← Torna al Portale</a>
    <button class="btn-print" onclick="window.print()">Stampa o Salva in PDF</button>
  </div>

  <div class="doc-container">
    <div class="doc-head">
      <span class="doc-tag">GUIDA PRATICA DI PRIMO SOCCORSO RELAZIONALE</span>
      <h1>I Primi 7 Giorni: Orientamento per la Famiglia</h1>
      <p class="subtitle">Cosa fare stasera, cosa non dire mai e come riaprire un dialogo vero secondo il Metodo Ecologico-Sociale di Vladimir Hudolin.</p>
    </div>

    <div class="quote-box">
      "L'alcolismo non è una tara genetica né una malattia isolata del singolo. È uno stile di vita e una sofferenza della famiglia e della comunità. La liberazione inizia quando smettiamo di cercare colpevoli e ci sediamo insieme in un cerchio."<br>
      <small style="font-weight: 700; color: #0f172a; font-style: normal;">— Prof. Vladimir Hudolin, Fondatore dei Club Alcologici Territoriali</small>
    </div>

    <div class="day-box">
      <div class="day-title">GIORNO 1 · Sospendere l'Inquisizione e il Ricatto</div>
      <p style="margin: 0; font-size: 14px; color: #334155;">
        La prima reazione spontanea è cercare le bottiglie nascoste, contare i bicchieri o minacciare ultimatum. Questi comportamenti aumentano il segreto, la vergogna e la ribellione. Stasera dichiara semplicemente: <em>«Vedo la tua sofferenza e sento la mia. Ti voglio bene, ma non posso più far finta di non vedere. Possiamo chiedere aiuto insieme».</em>
      </p>
    </div>

    <div class="day-box">
      <div class="day-title">GIORNO 2 · Separare la Persona dal Comportamento</div>
      <p style="margin: 0; font-size: 14px; color: #334155;">
        La persona che ami non è la dipendenza. La dipendenza è una nebbia biochimica e psicologica che ne offusca la lucidità. Parlare con una persona quando è sotto effetto dell'alcol è inutile e dannoso: rimanda qualsiasi confronto al mattino o al momento di massima calma e lucidità.
      </p>
    </div>

    <div class="day-box">
      <div class="day-title">GIORNO 3 · Uscire dall'Isolamento e dalla Vergogna Domestica</div>
      <p style="margin: 0; font-size: 14px; color: #334155;">
        La dipendenza si nutre del silenzio. Spesso la famiglia nasconde la situazione ad amici e parenti per paura del giudizio. Ricorda che in Italia oltre 400.000 famiglie vivono lo stesso identico dramma. Non sei solo/a e non hai colpe da scontare.
      </p>
    </div>

    <div class="day-box">
      <div class="day-title">GIORNO 4 · Comprendere che la Famiglia Può Andare al Club Anche da Sola</div>
      <p style="margin: 0; font-size: 14px; color: #334155;">
        Se il tuo famigliare nega o rifiuta di farsi aiutare, <strong>tu puoi comunque recarti al Club</strong>. Nel Metodo Hudolin la famiglia non è spettatrice, è protagonista. Spesso quando la famiglia inizia a frequentare il Club e a cambiare il proprio atteggiamento emotivo, la persona con il problema sceglie di unirsi spontaneamente.
      </p>
    </div>

    <div class="day-box">
      <div class="day-title">GIORNO 5 · Trovare la Sedia Libera più Vicina a Casa</div>
      <p style="margin: 0; font-size: 14px; color: #334155;">
        Nei Club Territoriali non ci sono registri esposti, cartelle cliniche né costi. L'accoglienza è immediata. Consulta la mappa mondiale su <strong>oltre.social</strong> o <strong>dependex.social</strong> per individuare il giorno e l'orario di riunione più comodo per te.
      </p>
    </div>

    <div class="day-box">
      <div class="day-title">GIORNO 6 · Il Primo Incontro nel Cerchio</div>
      <p style="margin: 0; font-size: 14px; color: #334155;">
        Al primo incontro nessuno ti chiederà di parlare per forza se non te la senti. Puoi semplicemente ascoltare le storie di chi ci è già passato e ne è uscito da 5, 10 o 20 anni. Vedere con i tuoi occhi che la libertà e la serenità familiare sono possibili è la medicina più potente.
      </p>
    </div>

    <div class="day-box">
      <div class="day-title">GIORNO 7 · Un Giorno alla Volta: La Nuova Routine</div>
      <p style="margin: 0; font-size: 14px; color: #334155;">
        Non si decide la sobrietà "per sempre": si decide di vivere con lucidità la giornata di oggi. Con il supporto settimanale del Club e del Servitore-Insegnante, la famiglia ricostruisce la fiducia passo dopo passo, una settimana alla volta.
      </p>
    </div>

    <div class="cta-box no-print">
      <h3 style="color: #D4AF37; margin: 0 0 10px;">Hai bisogno di un consiglio immediato o vuoi trovare il Club più vicino?</h3>
      <p style="font-size: 14px; color: #cbd5e1; margin-bottom: 18px;">Consulta la rete viva di 542 Club territoriali e parla in anonimato con un facilitatore.</p>
      <a href="world-club-explorer.php" style="background: #D4AF37; color: #000000; font-weight: 800; text-decoration: none; padding: 10px 20px; border-radius: 8px; display: inline-block;">Trova il tuo Club sulla Mappa 3D</a>
    </div>

    <div style="margin-top: 32px; font-size: 12px; color: #94a3b8; text-align: center; border-top: 1px solid #e2e8f0; padding-top: 16px;">
      Documento divulgativo a cura della rete <strong>DEPENDEX · OLTRE</strong> · Metodo Ecologico-Sociale del Prof. Vladimir Hudolin.
    </div>
  </div>
</body>
</html>
<?php
exit;
endif;

// Vista standard Landing Page
$pageTitle = 'Guida Gratuita per la Famiglia · I Primi 7 Giorni · DEPENDEX';
$metaDesc = 'Scarica gratis la Guida di Orientamento per la Famiglia: cosa fare stasera, cosa non dire mai e come trovare una sedia libera nei Club Alcologici Territoriali.';
require '_header.php';
?>

<div class="container py-5" style="max-width: 960px;">

  <?php if ($submitted): ?>
    <!-- RISULTATO DI DOWNLOAD IMMEDIATO -->
    <div class="lux-metallic-card p-4 p-md-5 text-center mb-5" style="border: 2px solid var(--neon-gold); border-radius: 24px; background: rgba(12, 16, 28, 0.95);">
      <div style="display: inline-flex; align-items: center; justify-content: center; width: 72px; height: 72px; border-radius: 50%; background: rgba(212,175,55,0.15); border: 1px solid var(--neon-gold); color: var(--neon-gold); margin-bottom: 20px;">
        <?=dx_icon('check-circle', '', 40)?>
      </div>

      <h1 style="font-family: var(--font-serif); font-size: clamp(1.8rem, 4vw, 2.6rem); color: #FFFFFF; margin-bottom: 12px;">
        La tua Guida è Pronta, <?=h($userName)?>!
      </h1>

      <p style="color: #cbd5e1; font-size: 1.1rem; line-height: 1.6; max-width: 680px; margin: 0 auto 28px;">
        Abbiamo registrato la tua richiesta per <strong><?=h($userEmail)?></strong>. Puoi iniziare a leggerla o stamparla immediatamente cliccando qui sotto:
      </p>

      <div style="display: flex; justify-content: center; gap: 16px; flex-wrap: wrap; margin-bottom: 36px;">
        <a href="guida-gratuita.php?view=document" target="_blank" class="btn primary" style="padding: 14px 32px; font-size: 1.05rem; border-radius: 14px; text-decoration: none;">
          <?=dx_icon('book-open', '', 18)?>
          <span style="margin-left: 8px;">Leggi e Stampa la Guida in PDF</span>
        </a>
        <a href="world-club-explorer.php" class="btn-rainbow-outline" style="padding: 14px 28px; font-size: 1.05rem; border-radius: 14px; text-decoration: none;">
          <?=dx_icon('compass', '', 18)?>
          <span style="margin-left: 8px;">Trova il Club più Vicino</span>
        </a>
      </div>

      <?php if (!empty($resultClubs)): ?>
        <div style="text-align: left; background: rgba(16, 20, 34, 0.9); border: 1px solid rgba(0, 212, 255, 0.35); border-radius: 16px; padding: 24px; margin-top: 24px;">
          <h3 style="color: var(--neon-cyan); margin-top: 0; font-size: 1.2rem; display: flex; align-items: center; gap: 8px;">
            <?=dx_icon('map-pin', '', 18)?> Club territoriali identificati nella tua zona:
          </h3>
          <?php foreach ($resultClubs as $c): ?>
            <div style="border-bottom: 1px solid rgba(255,255,255,0.08); padding: 12px 0;">
              <div style="font-weight: 800; color: #FFFFFF;"><?=h($c['entity_name'])?></div>
              <div style="font-size: 0.9rem; color: #cbd5e1; margin-top: 2px;">
                <?=h($c['address'])?> (<?=h($c['province'])?>, <?=h($c['region'])?>)
              </div>
              <div style="font-size: 0.85rem; color: var(--neon-gold); margin-top: 4px;">
                Incontri: <strong><?=h($c['meeting_day'] ?? 'Settimanale')?></strong> · Ingresso libero e gratuito
              </div>
            </div>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>
    </div>

  <?php else: ?>

    <!-- HERO PRESENTAZIONE GUIDA GRATUITA -->
    <div class="text-center mb-5">
      <div class="badge-neon-rainbow mb-3">
        <span class="dot"></span>
        <span class="text-rainbow">RISORSA GRATUITA DI PRIMO SOCCORSO RELAZIONALE</span>
      </div>
      <h1 style="font-family: var(--font-serif); font-size: clamp(2rem, 4.5vw, 3.2rem); font-weight: 900; color: #FFFFFF; line-height: 1.2; margin-bottom: 16px;">
        I Primi 7 Giorni: Guida Pratica per la Famiglia
      </h1>
      <p style="font-size: 1.15rem; color: #cbd5e1; max-width: 760px; margin: 0 auto; line-height: 1.7;">
        Quando una persona cara vive un problema di dipendenza, la famiglia si sente smarrita e impotente. Questa guida raccoglie <strong>le 7 indicazioni concrete del Metodo Hudolin</strong> per disinnescare i conflitti, ritrovare la lucidità e sapere esattamente come muovere il primo passo.
      </p>
    </div>

    <div class="row g-4 align-items-stretch mb-5">
      <!-- COLONNA CONTENUTI -->
      <div class="col-md-6">
        <div class="lux-metallic-card p-4 h-100" style="background: rgba(12, 16, 28, 0.9); border: 1px solid rgba(212,175,55,0.3); border-radius: 20px;">
          <h3 style="color: var(--neon-gold); font-size: 1.25rem; font-family: var(--font-serif); margin-top: 0; margin-bottom: 16px;">
            Cosa troverai all'interno della guida:
          </h3>
          <ul style="list-style: none; padding: 0; margin: 0; display: flex; flex-direction: column; gap: 14px; font-size: 0.95rem; color: #e2e8f0;">
            <li style="display: flex; gap: 10px; align-items: flex-start;">
              <span style="color: var(--neon-gold); font-size: 1.1rem; line-height: 1;">✔</span>
              <span><strong>Cosa non dire mai stasera:</strong> come interrompere il ciclo di accuse, ricatti e pentimenti sterili.</span>
            </li>
            <li style="display: flex; gap: 10px; align-items: flex-start;">
              <span style="color: var(--neon-cyan); font-size: 1.1rem; line-height: 1;">✔</span>
              <span><strong>Separare la persona dalla dipendenza:</strong> comprendere cosa accade a livello biochimico ed emotivo.</span>
            </li>
            <li style="display: flex; gap: 10px; align-items: flex-start;">
              <span style="color: var(--neon-green); font-size: 1.1rem; line-height: 1;">✔</span>
              <span><strong>Il Club anche senza di lui/lei:</strong> perché la famiglia può iniziare il percorso anche se la persona nega.</span>
            </li>
            <li style="display: flex; gap: 10px; align-items: flex-start;">
              <span style="color: var(--neon-violet); font-size: 1.1rem; line-height: 1;">✔</span>
              <span><strong>Cosa succede al primo incontro:</strong> privacy totale, zero formalità e come funziona il cerchio.</span>
            </li>
          </ul>

          <div style="margin-top: 24px; padding-top: 18px; border-top: 1px solid rgba(255,255,255,0.08); font-size: 0.85rem; color: #94a3b8;">
            <?=dx_icon('shield-check', '', 16)?> Documento curato dai facilitatori e servitori della rete ecologico-sociale.
          </div>
        </div>
      </div>

      <!-- COLONNA FORM DI SCARICAMENTO -->
      <div class="col-md-6">
        <div class="lux-metallic-card p-4 p-md-5 h-100" style="background: rgba(12, 16, 28, 0.95); border: 2px solid rgba(0, 212, 255, 0.4); border-radius: 20px; box-shadow: 0 0 30px rgba(0, 212, 255, 0.15);">
          <div style="font-size: 0.78rem; font-weight: 800; color: var(--neon-cyan); letter-spacing: 0.08em; text-transform: uppercase; margin-bottom: 8px;">
            DOWNLOAD IMMEDIATO IN PDF
          </div>
          <h2 style="font-size: 1.5rem; color: #FFFFFF; font-family: var(--font-serif); margin-top: 0; margin-bottom: 20px;">
            Ricevi e scarica subito la tua copia
          </h2>

          <form method="POST" action="guida-gratuita.php">
            <div style="margin-bottom: 16px;">
              <label style="display: block; font-size: 0.88rem; color: #cbd5e1; margin-bottom: 6px; font-weight: 600;">Il tuo nome o nickname:</label>
              <input type="text" name="nome" placeholder="Come preferisci essere chiamato/a" style="width: 100%; padding: 12px 14px; background: rgba(2, 6, 23, 0.8); border: 1px solid #334155; border-radius: 10px; color: #ffffff; font-size: 0.95rem;">
            </div>

            <div style="margin-bottom: 16px;">
              <label style="display: block; font-size: 0.88rem; color: #cbd5e1; margin-bottom: 6px; font-weight: 600;">Il tuo indirizzo email riservato (*):</label>
              <input type="email" name="email" required placeholder="Inserisci la tua email..." style="width: 100%; padding: 12px 14px; background: rgba(2, 6, 23, 0.8); border: 1px solid #334155; border-radius: 10px; color: #ffffff; font-size: 0.95rem;">
            </div>

            <div style="margin-bottom: 20px;">
              <label style="display: block; font-size: 0.88rem; color: #cbd5e1; margin-bottom: 6px; font-weight: 600;">Città o Provincia (opzionale, per indicare i Club vicini):</label>
              <input type="text" name="citta" placeholder="Es. Milano, Roma, Padova, Bologna..." style="width: 100%; padding: 12px 14px; background: rgba(2, 6, 23, 0.8); border: 1px solid #334155; border-radius: 10px; color: #ffffff; font-size: 0.95rem;">
            </div>

            <div style="margin-bottom: 24px;">
              <label style="display: flex; align-items: flex-start; gap: 8px; color: #94a3b8; font-size: 0.8rem; line-height: 1.4; cursor: pointer;">
                <input type="checkbox" name="privacy_accepted" value="1" required checked style="accent-color: var(--neon-cyan); width: 16px; height: 16px; margin-top: 2px;">
                <span>Accetto l'Informativa Privacy. Riservatezza 100% garantita, nessun dato ceduto a terzi. Disiscrizione sempre disponibile in 1 click.</span>
              </label>
            </div>

            <button type="submit" class="btn primary" style="width: 100%; min-height: 50px; font-size: 1.05rem; font-weight: 800; border-radius: 12px;">
              <?=dx_icon('sparkles', '', 18)?>
              <span style="margin-left: 8px;">Scarica la Guida Gratuita</span>
            </button>
          </form>
        </div>
      </div>
    </div>

  <?php endif; ?>

</div>

<?php require '_footer.php'; ?>
