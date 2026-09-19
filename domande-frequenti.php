<?php
require_once __DIR__ . '/bootstrap.php';

$pageTitle = 'Le Domande che Forse Ti Vergogni a Fare · DEPENDEX Comunità Club';
$metaDesc = 'Non devi sapere già tutto. Risposte chiare, oneste e senza giudizio per chi cerca aiuto, una comunità o vuole capire come funziona un Club Alcologico Territoriale.';
$canonicalUrl = 'https://' . ($brand['domain'] ?? 'dependex.social') . '/domande-frequenti.php';

$faqList = [
    [
        'q' => 'E se non bevo più o bevo solo ogni tanto?',
        'a' => 'Non serve avere una "dipendenza conclamata" o essere in una situazione estrema per entrare in un Club. Il Club è una comunità di crescita e protezione del benessere. Chiunque senta il desiderio di chiarire il proprio rapporto con le bevande alcoliche, o di consolidare una scelta di sobrietà già intrapresa, trova una sedia pronta e persone felici di ascoltare.'
    ],
    [
        'q' => 'E se beve solo mio marito, mia moglie o mio figlio?',
        'a' => 'Il Metodo Hudolin considera il bere come un comportamento che tocca l’intera ecologia familiare. Puoi venire al Club anche da solo/a, come familiare. Spesso, il cambiamento di serenità e di consapevolezza che un familiare sperimenta al Club apre la strada perché anche chi beve scelga, spontaneamente, di partecipare.'
    ],
    [
        'q' => 'E se vengo all’incontro ma non me la sento di parlare?',
        'a' => 'Nessuno ti metterà al centro della stanza e nessuno ti forzerà mai a parlare. Puoi semplicemente entrare, sederti in cerchio, ascoltare le storie degli altri e prendere un caffè o una tisana. Puoi rimanere in silenzio per tutti gli incontri di cui hai bisogno. Parlerai solo quando e se sentirai che è il momento giusto.'
    ],
    [
        'q' => 'E se ho già provato altre volte con medici o comunità ed è andata male?',
        'a' => 'Il Club non è un tribunale né un ospedale: qui non esistono cartelle cliniche, non si registrano "ricadute" come fallimenti e nessuno ti toglierà la parola. Nel cammino di vita ci sono curve e ripartenze. Al Club non interessa il passato, ma la possibilità che decidiamo di costruire insieme a partire da oggi.'
    ],
    [
        'q' => 'E se mi vergogno che qualcuno mi veda entrare o che la gente parli?',
        'a' => 'La riservatezza all’interno del cerchio è assoluta: ciò che viene detto nel Club resta nel Club. Inoltre, la vergogna è la prima emozione comune che ha provato ognuna delle persone sedute in cerchio prima di te. Quando varcherai la porta scoprirai che nessuno ti guarda con curiosità morbosa, ma solo con il calore di chi riconosce il tuo stesso coraggio.'
    ],
    [
        'q' => 'E se penso di non avere davvero un problema grave?',
        'a' => 'Non serve "toccare il fondo" per scegliere di vivere meglio. Anzi, prima si incontra una comunità, minore è la sofferenza necessaria. Non devi definirti "alcolista" o affibbiarti etichette per partecipare. Al Club si parla di vita, di relazioni, di tensioni quotidiane e di libertà.'
    ],
    [
        'q' => 'Posso venire da solo o devo per forza portare qualcuno?',
        'a' => 'Puoi venire esattamente come sei: da solo, in coppia, con un amico di fiducia o con l’intera famiglia. Se vieni da solo sarai accolto dalla famiglia del Club; se vieni accompagnato, chi ti sta vicino troverà uno spazio sicuro per esprimersi e respirare.'
    ],
    [
        'q' => 'Possono venire anche i miei figli?',
        'a' => 'Sì. I Club accolgono le famiglie nella loro totalità. Spesso la presenza dei figli, grandi o piccoli, riporta verità, spontaneità e amore nel cerchio. Negli incontri si impara un nuovo modo di parlarsi a casa.'
    ],
    [
        'q' => 'Mi devo impegnare a frequentare per sempre o a firmare qualcosa?',
        'a' => 'Assolutamente no. Non ci sono contratti, registri anagrafici, tessere obbligatorie né promesse solenni. Ci si incontra una volta alla settimana per circa un’ora e mezza. Vieni per la prima volta, vedi come ti fa stare, e poi decidi tu liberamente il tuo prossimo passo.'
    ],
    [
        'q' => 'Quanto costa partecipare al Club?',
        'a' => 'La partecipazione al Club è completamente gratuita e senza fini di lucro. I servitori-insegnanti svolgono la propria opera a titolo volontario di solidarietà. Nessuno ti chiederà denaro per sederti in cerchio.'
    ],
    [
        'q' => 'Mi giudicheranno per gli errori che ho commesso?',
        'a' => 'Nel Club è abolito ogni giudizio. Non ci sono professori né giudici: siamo tutti esseri umani che hanno conosciuto la fragilità e che hanno scelto di aiutarsi reciprocamente. Ciò che troverai è ascolto empatico e profondo rispetto.'
    ]
];

$pageSchemaJson = [
    "@context" => "https://schema.org",
    "@type" => "FAQPage",
    "name" => "Le Domande che Forse Ti Vergogni a Fare · DEPENDEX",
    "description" => $metaDesc,
    "mainEntity" => array_map(function($f) {
        return [
            "@type" => "Question",
            "name" => $f['q'],
            "acceptedAnswer" => [
                "@type" => "Answer",
                "text" => $f['a']
            ]
        ];
    }, $faqList)
];

require '_header.php';
?>

<div class="container py-4 my-2">
  <!-- BREADCRUMB -->
  <nav aria-label="breadcrumb" class="mb-4">
    <ol class="breadcrumb" style="background: transparent; padding: 0; font-size: 0.88rem;">
      <li class="breadcrumb-item"><a href="index.php" style="color: var(--neon-cyan); text-decoration: none;">Home</a></li>
      <li class="breadcrumb-item active" aria-current="page" style="color: #94a3b8;">Domande Frequenti</li>
    </ol>
  </nav>

  <!-- HERO SEZIONE DOMANDE -->
  <header class="rainbow-border p-4 p-md-5 mb-5" style="background: rgba(11, 15, 27, 0.94); backdrop-filter: blur(20px);">
    <div class="row align-items-center g-4">
      <div class="col-lg-8">
        <div class="badge-neon-rainbow mb-3">
          <span class="dot"></span>
          <span style="color: #fde68a;">RASSICURAZIONE & VERITÀ SENZA GIUDIZIO</span>
        </div>
        <h1 style="font-family: var(--font-serif); font-size: clamp(2rem, 4vw, 3rem); font-weight: 800; color: #FFFFFF; line-height: 1.2; margin-bottom: 16px;">
          Le domande che forse<br>
          <span class="text-rainbow">ti vergogni a fare ad alta voce.</span>
        </h1>
        <p style="font-size: 1.15rem; color: #cbd5e1; line-height: 1.7; margin-bottom: 24px;">
          È normale avere paura, dubbi o timore del giudizio. Quando si pensa a una difficoltà legata all’alcol o alla vita, la mente si riempie di etichette. <strong>Qui non serve definirti né avere già tutte le risposte.</strong> Leggi con calma: troverai la verità semplice e umana su cosa significa entrare in un Club.
        </p>
        <div class="d-flex flex-wrap gap-3">
          <a href="mappa-club.php" class="btn-rainbow-neon">
            <?=dx_icon('map-pin', '', 18)?>
            <span style="margin-left: 8px;">Trova una Comunità Vicina</span>
          </a>
          <a href="parla-con-noi.php" class="btn-rainbow-outline" style="border-color: var(--neon-gold); color: #fff;">
            <?=dx_icon('message-circle', 'text-neon-gold', 18)?>
            <span style="margin-left: 8px;">Fai una Domanda Riservata</span>
          </a>
        </div>
      </div>
      <div class="col-lg-4 text-center d-none d-lg-block">
        <div class="p-4" style="background: rgba(255,255,255,0.03); border-radius: 16px; border: 1px solid rgba(255,255,255,0.08);">
          <div style="font-size: 3.5rem; margin-bottom: 12px; color: var(--neon-gold);">
            <?=dx_icon('heart-handshake', 'text-neon-gold', 56)?>
          </div>
          <h2 style="font-size: 1.15rem; color: #fff; font-weight: 700; margin-bottom: 8px;">Nessuna etichetta</h2>
          <p style="font-size: 0.88rem; color: #94a3b8; line-height: 1.6; margin: 0;">
            Non sei una cartella clinica, non sei un numero. Nel Club sei una persona con un nome, una storia e una comunità pronta ad accoglierti.
          </p>
        </div>
      </div>
    </div>
  </header>

  <!-- IL MANIFESTO DEL PRIMO PASSO -->
  <div class="p-4 mb-5" style="background: linear-gradient(135deg, rgba(16, 185, 129, 0.12), rgba(6, 182, 212, 0.12)); border: 1px solid rgba(16, 185, 129, 0.35); border-radius: 16px;">
    <div class="row align-items-center g-3">
      <div class="col-md-9">
        <h2 style="font-size: 1.25rem; color: #fff; font-weight: 700; margin-bottom: 6px;">
          <span style="color: var(--neon-green);"><?=dx_icon('check-circle', 'text-neon-green', 20)?></span>
          Non devi cambiare tutta la tua vita oggi.
        </h2>
        <p style="color: #cbd5e1; margin: 0; font-size: 0.95rem; line-height: 1.6;">
          Puoi semplicemente: fare una domanda · trovare un Club · conoscere qualcuno · andare a un incontro. <strong>Poi decidi tu.</strong>
        </p>
      </div>
      <div class="col-md-3 text-md-end">
        <a href="parla-con-noi.php" class="btn-rainbow-outline" style="border-color: var(--neon-green); color: #fff; font-size: 0.88rem;">
          Inizia da una domanda &rarr;
        </a>
      </div>
    </div>
  </div>

  <!-- LISTA FAQ INTERATTIVA -->
  <section class="mb-5">
    <div class="row g-4">
      <?php foreach ($faqList as $idx => $faq): ?>
      <div class="col-lg-6">
        <article class="p-4 h-100" style="background: rgba(15, 23, 42, 0.85); border: 1px solid rgba(255,255,255,0.08); border-radius: 16px; transition: transform 0.2s ease, border-color 0.2s ease;">
          <div class="d-flex align-items-start gap-3 mb-3">
            <div style="flex-shrink: 0; width: 36px; height: 36px; border-radius: 50%; background: rgba(255, 215, 0, 0.12); border: 1px solid rgba(255, 215, 0, 0.35); display: flex; align-items: center; justify-content: center; color: var(--neon-gold); font-weight: 800; font-size: 0.9rem;">
              <?=($idx + 1)?>
            </div>
            <h2 style="font-size: 1.15rem; color: #FFFFFF; font-weight: 700; margin: 0; line-height: 1.4;">
              &ldquo;<?=$faq['q']?>&rdquo;
            </h2>
          </div>
          <p style="color: #cbd5e1; font-size: 0.95rem; line-height: 1.7; margin: 0; padding-left: 48px;">
            <?=$faq['a']?>
          </p>
        </article>
      </div>
      <?php endforeach; ?>
    </div>
  </section>

  <!-- BANNER DAL DIGITALE AL REALE -->
  <section class="rainbow-border p-4 p-md-5 text-center my-5" style="background: rgba(8, 11, 20, 0.92);">
    <div style="max-width: 760px; margin: 0 auto;">
      <div style="display: inline-block; margin-bottom: 14px;">
        <?=dx_icon('globe', 'text-neon-cyan', 42)?>
      </div>
      <h2 style="font-family: var(--font-serif); font-size: 1.8rem; color: #fff; font-weight: 800; margin-bottom: 12px;">
        <span class="text-rainbow">DEPENDEX è digitale. La comunità è reale.</span>
      </h2>
      <p style="color: #94a3b8; font-size: 1.05rem; line-height: 1.7; margin-bottom: 24px;">
        Il nostro lavoro online non vuole sostituirsi alle relazioni umane, ma serve unicamente a farti trovare persone, famiglie e Club accoglienti nel mondo reale. <strong>Dal digitale al reale. Dalla persona alla comunità.</strong>
      </p>
      <div class="d-flex justify-content-center flex-wrap gap-3">
        <a href="mappa-club.php" class="btn-rainbow-neon">
          <?=dx_icon('map-pin', '', 18)?>
          <span style="margin-left: 8px;">Cerca il tuo Club sulla Mappa</span>
        </a>
        <a href="recensioni.php" class="btn-rainbow-outline" style="border-color: var(--neon-purple); color: #fff;">
          <?=dx_icon('star', 'text-neon-purple', 18)?>
          <span style="margin-left: 8px;">Leggi le Storie dei Membri</span>
        </a>
      </div>
    </div>
  </section>
</div>

<?php require '_footer.php'; ?>
