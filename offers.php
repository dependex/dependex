<?php
/**
 * COLLANA EDITORIALE UFFICIALE · LIBRI AMAZON KDP DI MIRCO PREGNOLATO
 * Sistema di visualizzazione a 3 Tier (Digitale PDF, Cartaceo Amazon KDP, Bundle Formazione)
 */

declare(strict_types=1);

require_once 'bootstrap.php';

$pageTitle = 'Collana Libri Amazon KDP · Mirco Pregnolato & Metodo Hudolin';
$metaDesc = 'I libri, diari operativi e manuali di crescita e sobrietà scritti da Mirco Pregnolato e pubblicati su Amazon KDP. Scegli tra formato Digitale, Cartaceo Prime e Bundle Formazione.';
$breadcrumbs = [
    'Home' => '/',
    'Collana Libri KDP' => 'offers.php'
];

// Catalogo Completo dei Libri di Mirco Pregnolato con Copertine Reali e i 3 Tier di Prezzo
$booksCatalog = [
    [
        'id' => 'prd_book_famiglia',
        'code' => 'MP-KDP-01',
        'category' => 'FAMIGLIA & RELAZIONI',
        'title' => 'Quaderno della Famiglia',
        'subtitle' => 'Strumento di lavoro, riflessione e dialogo nel percorso di cambiamento e sobrietà',
        'description' => 'Un manuale empatico e operativo pensato per il nucleo familiare che affronta il cammino della sobrietà. Contiene schede guidate di dialogo settimanale, gestione dei conflitti, superamento del senso di colpa e ricostruzione del patto di fiducia domestico.',
        'pages' => '172 pagine · Formato 6x9" · Carta Crema',
        'cover_img' => 'assets/img/books/quaderno_famiglia_cover.webp',
        'color_theme' => 'card-neon-cyan',
        'badge_color' => 'var(--neon-cyan)',
        'amazon_url' => 'https://www.amazon.it/s?k=mirco+pregnolato+quaderno+della+famiglia',
        'tiers' => [
            [
                'tier' => 1,
                'offer_id' => 'off_bk_famiglia_dig',
                'name' => 'Digitale (PDF)',
                'price' => '14,90 €',
                'details' => 'Download immediato del PDF ad alta risoluzione, stampabile e compilabile su tablet.',
                'cta_type' => 'cart',
                'cta_label' => 'Scarica PDF (14,90 €)'
            ],
            [
                'tier' => 2,
                'offer_id' => 'off_bk_famiglia_kdp',
                'name' => 'Cartaceo KDP',
                'price' => '19,90 €',
                'details' => 'Volume cartaceo con copertina satinata di pregio. Spedizione rapida e reso garantito Amazon Prime.',
                'cta_type' => 'amazon',
                'cta_label' => 'Acquista su Amazon KDP'
            ],
            [
                'tier' => 3,
                'offer_id' => 'off_bk_famiglia_bundle',
                'name' => 'Bundle Formazione',
                'price' => '49,00 €',
                'details' => 'Volume Cartaceo KDP + PDF Operativo + Video Masterclass di Mirco Pregnolato sul dialogo in famiglia.',
                'cta_type' => 'cart',
                'cta_label' => 'Bundle Completo (49,00 €)'
            ]
        ]
    ],
    [
        'id' => 'prd_book_diario_club',
        'code' => 'MP-KDP-02',
        'category' => 'SOBRIETÀ & CLUB TERRITORIALI',
        'title' => 'Il Diario del Club: 90 Giorni di Rinascita e Sobrietà',
        'subtitle' => 'Guida pratica quotidiana, riflessioni del cerchio e tracciamento della sobrietà',
        'description' => 'Il compagno insostituibile per i membri dei Club Alcologici Territoriali e per chiunque desideri consolidare i primi 90 giorni di sobrietà. Include schede giornaliere di auto-osservazione, frasi del Prof. Hudolin e sezioni strutturate per gli incontri settimanali di Club.',
        'pages' => '223 pagine · Formato 6x9" · Rilegatura Brossura',
        'cover_img' => 'assets/img/books/diario_club_cover.webp',
        'color_theme' => 'card-neon-gold',
        'badge_color' => 'var(--neon-gold)',
        'amazon_url' => 'https://www.amazon.it/dp/B0HJ475K5G',
        'tiers' => [
            [
                'tier' => 1,
                'offer_id' => 'off_bk_club_dig',
                'name' => 'Digitale (PDF)',
                'price' => '14,90 €',
                'details' => 'PDF interattivo con le 90 schede giornaliere pronte all\'uso immediato.',
                'cta_type' => 'cart',
                'cta_label' => 'Scarica PDF (14,90 €)'
            ],
            [
                'tier' => 2,
                'offer_id' => 'off_bk_club_kdp',
                'name' => 'Cartaceo KDP',
                'price' => '19,90 €',
                'details' => '223 pagine stampate su carta crema anti-affaticamento. Disponibile su Amazon KDP con Prime.',
                'cta_type' => 'amazon',
                'cta_label' => 'Acquista su Amazon KDP'
            ],
            [
                'tier' => 3,
                'offer_id' => 'off_bk_club_bundle',
                'name' => 'Bundle Masterclass',
                'price' => '49,00 €',
                'details' => 'Volume Cartaceo + PDF + 12 Pillole Audio di approfondimento settimanale per i 3 mesi.',
                'cta_type' => 'cart',
                'cta_label' => 'Bundle Completo (49,00 €)'
            ]
        ]
    ],
    [
        'id' => 'prd_book_servitore',
        'code' => 'MP-KDP-03',
        'category' => 'GUIDA PER OPERATORI E FACILITATORI',
        'title' => 'Diario Servitore Insegnante',
        'subtitle' => 'La guida pratica e operativa per il facilitatore di Club e conduttore del cerchio',
        'description' => 'Tutto ciò che serve al servitore-insegnante: deontologia, gestione del silenzio, accoglienza di nuove famiglie, superamento delle resistenze, collaborazione con i Ser.D e coordinamento con le Scuole Alcologiche Territoriali.',
        'pages' => '112 pagine · Formato 6x9" · Tascabile Operativo',
        'cover_img' => 'assets/img/books/diario_servitore_cover.webp',
        'color_theme' => 'card-neon-green',
        'badge_color' => 'var(--neon-green)',
        'amazon_url' => 'https://www.amazon.it/s?k=mirco+pregnolato+diario+servitore+insegnante',
        'tiers' => [
            [
                'tier' => 1,
                'offer_id' => 'off_bk_serv_dig',
                'name' => 'Digitale (PDF)',
                'price' => '14,90 €',
                'details' => 'Protocollo completo di conduzione in formato PDF consultabile su smartphone e tablet.',
                'cta_type' => 'cart',
                'cta_label' => 'Scarica PDF (14,90 €)'
            ],
            [
                'tier' => 2,
                'offer_id' => 'off_bk_serv_kdp',
                'name' => 'Cartaceo KDP',
                'price' => '14,90 €',
                'details' => 'Manuale cartaceo tascabile KDP da portare sempre con sé ad ogni incontro di Club.',
                'cta_type' => 'amazon',
                'cta_label' => 'Acquista su Amazon KDP'
            ],
            [
                'tier' => 3,
                'offer_id' => 'off_bk_serv_bundle',
                'name' => 'Academy Pass',
                'price' => '49,00 €',
                'details' => 'Libro Cartaceo + PDF + Accesso all\'Academy Servitori Insegnanti con attestato di partecipazione.',
                'cta_type' => 'cart',
                'cta_label' => 'Bundle Formazione (49,00 €)'
            ]
        ]
    ],
    [
        'id' => 'prd_book_crescita_esp',
        'code' => 'MP-KDP-04',
        'category' => 'CRESCITA PERSONALE & TRASFORMAZIONE',
        'title' => 'Il Mio Diario di Crescita Esponenziale',
        'subtitle' => '365 Giorni di potenziamento personale, abitudini sobrie e trasformazione profonda',
        'description' => 'L\'opera monumentale di Mirco Pregnolato dedicata a chi non si accontenta della sola astinenza, ma vuole trasformare la propria vita in un capolavoro di chiarezza, disciplina e successo umano. 365 schede di lavoro quotidiano per forgiare una mente sovrana.',
        'pages' => '386 pagine · Formato 6x9" · Carta Crema Deluxe',
        'cover_img' => 'assets/img/books/diario_crescita_cover.webp',
        'color_theme' => 'card-neon-orange',
        'badge_color' => 'var(--neon-orange)',
        'amazon_url' => 'https://www.amazon.it/s?k=mirco+pregnolato+diario+crescita+esponenziale',
        'tiers' => [
            [
                'tier' => 1,
                'offer_id' => 'off_bk_crescita_dig',
                'name' => 'Digitale (PDF)',
                'price' => '19,90 €',
                'details' => '386 pagine di schede in alta definizione per il tuo journaling quotidiano digitale.',
                'cta_type' => 'cart',
                'cta_label' => 'Scarica PDF (19,90 €)'
            ],
            [
                'tier' => 2,
                'offer_id' => 'off_bk_crescita_kdp',
                'name' => 'Cartaceo KDP',
                'price' => '24,90 €',
                'details' => 'Elegante volume rilegato KDP di 386 pagine. Il tuo compagno per un intero anno.',
                'cta_type' => 'amazon',
                'cta_label' => 'Acquista su Amazon KDP'
            ],
            [
                'tier' => 3,
                'offer_id' => 'off_bk_crescita_bundle',
                'name' => 'Executive Bundle',
                'price' => '69,00 €',
                'details' => 'Volume Cartaceo + PDF + Masterclass Video Esclusiva di Mirco Pregnolato sui 5 pilastri della trasformazione.',
                'cta_type' => 'cart',
                'cta_label' => 'Executive Pass (69,00 €)'
            ]
        ]
    ],
    [
        'id' => 'prd_book_sat_trilogia',
        'code' => 'MP-KDP-05',
        'category' => 'FORMAZIONE ISTITUZIONALE SAT',
        'title' => 'Trilogia SAT (Scuola Alcologica Territoriale)',
        'subtitle' => 'I tre tomi ufficiali: SAT I (8 Incontri), SAT II (Consolidamento), SAT III (Leadership)',
        'description' => 'La suite formativa completa per corsisti, famiglie e operatori sociosanitari delle Scuole Territoriali. Copre tutti i passaggi: dall\'introduzione al metodo ecologico-sociale, al consolidamento delle abitudini sobrie, fino all\'impegno nella comunità.',
        'pages' => '3 Volumi Completi · Oltre 260 pagine complessive',
        'cover_img' => 'assets/img/books/sat_1_cover.webp',
        'color_theme' => 'card-neon-violet',
        'badge_color' => 'var(--neon-violet)',
        'amazon_url' => 'https://www.amazon.it/dp/B0HJ23231N',
        'tiers' => [
            [
                'tier' => 1,
                'offer_id' => 'off_bk_sat_dig',
                'name' => 'Digitale (PDF)',
                'price' => '24,90 €',
                'details' => 'Tutti e 3 i Workbook SAT (I, II e III) in formato digitale immediato.',
                'cta_type' => 'cart',
                'cta_label' => 'Scarica 3 PDF (24,90 €)'
            ],
            [
                'tier' => 2,
                'offer_id' => 'off_bk_sat_kdp',
                'name' => 'Cartaceo KDP',
                'price' => '19,90 €',
                'details' => 'Cofanetto di volumi cartacei Amazon KDP per docenti e corsisti.',
                'cta_type' => 'amazon',
                'cta_label' => 'Acquista su Amazon KDP'
            ],
            [
                'tier' => 3,
                'offer_id' => 'off_bk_sat_bundle',
                'name' => 'Corso & Certificato',
                'price' => '79,00 €',
                'details' => '3 Volumi Cartacei + PDF + Accesso ai test per l\'attestato formale di completamento corso.',
                'cta_type' => 'cart',
                'cta_label' => 'Suite Didattica (79,00 €)'
            ]
        ]
    ],
    [
        'id' => 'prd_book_52_settimane',
        'code' => 'MP-KDP-06',
        'category' => 'ABITUDINI & METODO CONTINUO',
        'title' => '52 Settimane di Cambiamento Workbook',
        'subtitle' => 'Percorso annuale guidato di rinascita, salute olistica e tracciamento continuo',
        'description' => 'Un anno intero suddiviso in 52 blocchi settimanali tematici: biochimica del corpo sobrio, riprogrammazione del sonno, gestione dello stress, cerchio delle amicizie e sviluppo di una nuova identità libera.',
        'pages' => '237 pagine · Formato 6x9" · Layout Settimanale',
        'cover_img' => 'assets/img/books/52_settimane_cover.webp',
        'color_theme' => 'card-neon-red',
        'badge_color' => 'var(--neon-red)',
        'amazon_url' => 'https://www.amazon.it/dp/B0HHZC3PZ2',
        'tiers' => [
            [
                'tier' => 1,
                'offer_id' => 'off_bk_52w_dig',
                'name' => 'Digitale (PDF)',
                'price' => '14,90 €',
                'details' => 'PDF operativo con le 52 settimane di esercizi pronto per la compilazione.',
                'cta_type' => 'cart',
                'cta_label' => 'Scarica PDF (14,90 €)'
            ],
            [
                'tier' => 2,
                'offer_id' => 'off_bk_52w_kdp',
                'name' => 'Cartaceo KDP',
                'price' => '19,90 €',
                'details' => '237 pagine rilegate Amazon KDP su carta crema per un anno di tracciamento.',
                'cta_type' => 'amazon',
                'cta_label' => 'Acquista su Amazon KDP'
            ],
            [
                'tier' => 3,
                'offer_id' => 'off_bk_52w_bundle',
                'name' => 'Habit Masterclass',
                'price' => '59,00 €',
                'details' => 'Libro Cartaceo + PDF + 12 Webinar mensili registrati sulle abitudini e neurobiologia.',
                'cta_type' => 'cart',
                'cta_label' => 'Annual Bundle (59,00 €)'
            ]
        ]
    ]
];


$schemaItems = [];
$pos = 1;
foreach ($booksCatalog as $bk) {
    $schemaItems[] = [
        "@type" => "ListItem",
        "position" => $pos++,
        "item" => [
            "@type" => "Book",
            "name" => $bk['title'],
            "headline" => $bk['subtitle'],
            "description" => $bk['description'],
            "author" => [
                "@type" => "Person",
                "name" => "Mirco Pregnolato"
            ],
            "publisher" => [
                "@type" => "Organization",
                "name" => "Amazon KDP & ACAT Basso Polesine"
            ],
            "offers" => [
                "@type" => "AggregateOffer",
                "priceCurrency" => "EUR",
                "lowPrice" => "14.90",
                "highPrice" => "69.00",
                "offerCount" => count($bk['tiers'])
            ]
        ]
    ];
}
$pageSchemaJson = [
    "@context" => "https://schema.org",
    "@type" => "ItemList",
    "name" => "Collana Libri Amazon KDP · Mirco Pregnolato",
    "description" => $metaDesc,
    "itemListElement" => $schemaItems
];

require '_header.php';
?>

<div class="container-169 py-4">

  <!-- ============================================================== -->
  <!-- HERO BANNER PANORAMICO (16:9 WIDESCREEN & MOBILE 9:16 ADAPTIVE) -->
  <!-- ============================================================== -->
  <div class="rainbow-panorama-banner mb-4">
    <img src="assets/img/rainbow-nebula-panorama.jpg" alt="Collana Editoriale Ufficiale Amazon KDP" style="max-height: 360px; aspect-ratio: 16 / 7; object-fit: cover; width: 100%; border-radius: 20px; box-shadow: var(--shadow);">
  </div>

  <section class="mb-5">
    <div class="badge-neon-rainbow mb-2">
      <span class="dot"></span>
      <span class="text-rainbow">COLLANA EDITORIALE UFFICIALE · SCRITTA DA MIRCO PREGNOLATO</span>
    </div>
    <h1 style="font-family: var(--font-serif); font-size: clamp(2rem, 4vw, 3rem); font-weight: 900; color: #FFFFFF; margin-bottom: 1rem;">
      I Libri, Diari Operativi & <span class="text-rainbow">Manuali Amazon KDP</span>
    </h1>
    <p style="font-size: 1.12rem; line-height: 1.7; color: #cbd5e1; max-width: 860px;">
      La produzione editoriale completa di <strong>Mirco Pregnolato</strong> a supporto delle famiglie, dei partecipanti e dei facilitatori dei <strong>Club Territoriali (Metodo Hudolin)</strong>. Scegli per ciascun libro il formato più adatto: <strong>Digitale ad alta risoluzione</strong>, <strong>Edizione Cartacea Amazon Prime</strong> o <strong>Bundle Formativo con Masterclass Audio/Video</strong>.
    </p>

    <!-- BANNER VETRINA MONOGRAFICA PUBBLICAZIONI KDP -->
    <div class="lux-metallic-card p-4 my-4" style="border: 1.5px solid var(--neon-gold); background: linear-gradient(135deg, rgba(255,215,0,0.12), rgba(0,240,255,0.08)); border-radius: 18px; display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 16px; box-shadow: 0 0 25px rgba(255,215,0,0.15);">
      <div style="display: flex; align-items: center; gap: 14px;">
        <div style="display: inline-flex; align-items: center; justify-content: center; width: 48px; height: 48px; border-radius: 14px; background: rgba(255, 215, 0, 0.2); border: 1.5px solid var(--neon-gold);">
          <?=dx_icon('book-open', 'text-neon-gold', 24)?>
        </div>
        <div>
          <b style="color: #FFFFFF; font-size: 1.1rem;">Vetrina Monografica Ufficiale: <span class="text-rainbow">Tutti i Volumi Hudolin & SAT</span></b>
          <p style="margin: 0; color: #cbd5e1; font-size: 0.9rem;">Visualizza le schede complete con copertine 3D ad alta definizione, descrizioni magnetiche estese, SAT I, SAT II, SAT III e acquisto diretto Amazon Prime.</p>
        </div>
      </div>
      <a href="pubblicazioni.php" class="btn primary" style="text-decoration: none; border-radius: 12px; white-space: nowrap; font-weight: 850; padding: 10px 18px;">
        <?=dx_icon('arrow-right', '', 16)?> <span>Vetrina Libri</span>
      </a>
    </div>

    <!-- BANNER PARTNERSHIP BEWAY.LIFE -->
    <div class="lux-metallic-card p-3 my-4" style="border: 1px solid rgba(0, 212, 255, 0.35); background: rgba(12, 16, 28, 0.92); border-radius: 16px; display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 16px;">
      <div style="display: flex; align-items: center; gap: 14px;">
        <div style="display: inline-flex; align-items: center; justify-content: center; width: 44px; height: 44px; border-radius: 12px; background: rgba(0, 212, 255, 0.15); border: 1px solid rgba(0, 212, 255, 0.35);">
          <?=dx_icon('ship', 'text-neon-cyan', 24)?>
        </div>
        <div>
          <b style="color: #FFFFFF; font-size: 1.05rem;">Dal Libro al Viaggio Trasformativo con <span class="text-rainbow">BEWAY.LIFE</span></b>
          <p style="margin: 0; color: #94a3b8; font-size: 0.88rem;">Vivi i principi dei libri in mare aperto: scopri la Grande Crociera della Rinascita & Sobrietà con Workshop e Masterclass esclusive.</p>
        </div>
      </div>
      <a href="viaggi-esperienziali.php" class="btn primary small" style="text-decoration: none; border-radius: 12px; white-space: nowrap; padding: 8px 16px;">
        <?=dx_icon('compass', '', 15)?> <span>Viaggi Esperienziali</span>
      </a>
    </div>
  </section>

  <!-- ============================================================== -->
  <!-- GRIGLIA LIBRI A 3 TIER                                          -->
  <!-- ============================================================== -->
  <section class="mb-5">
    <div class="grid-169-3col" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(min(100%, 340px), 1fr)); gap: 28px;">
      <?php foreach($booksCatalog as $b): ?>
        <article class="card <?=$b['color_theme']?> p-4" style="display: flex; flex-direction: column; justify-content: space-between; border-radius: 22px; background: rgba(12, 16, 26, 0.94); border: 1px solid rgba(255, 255, 255, 0.12); box-shadow: 0 12px 35px rgba(0,0,0,0.6);">
          
          <div>
            <!-- Header Card -->
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 14px; gap: 8px;">
              <span class="dx-ticker-badge" style="color: <?=$b['badge_color']?>; border-color: <?=$b['badge_color']?>; font-size: 0.76rem;">
                <?=$b['code']?> · <?=$b['category']?>
              </span>
              <span style="font-size: 0.72rem; font-weight: 800; color: #cbd5e1; background: rgba(255,255,255,0.08); padding: 4px 10px; border-radius: 8px; letter-spacing: 0.05em;">
                AUTORE: M. PREGNOLATO
              </span>
            </div>

            <!-- ANTEPRIMA COPERTINA & TITOLO -->
            <?php if (!empty($b['cover_img'])): ?>
              <div style="display: flex; gap: 16px; margin-bottom: 16px; align-items: flex-start;">
                <div style="width: 95px; flex-shrink: 0; border-radius: 8px; overflow: hidden; box-shadow: -4px 6px 14px rgba(0,0,0,0.7); border: 1px solid rgba(255,255,255,0.12);">
                  <img src="<?=h($b['cover_img'])?>" alt="Copertina <?=h($b['title'])?>" style="width: 100%; height: auto; display: block; aspect-ratio: 6/9; object-fit: cover;">
                </div>
                <div style="flex: 1; min-width: 0;">
                  <h2 style="color: #FFFFFF; font-family: var(--font-serif); font-size: 1.3rem; font-weight: 800; margin: 0 0 0.3rem; line-height: 1.25;">
                    <?=h($b['title'])?>
                  </h2>
                  <p style="color: <?=$b['badge_color']?>; font-size: 0.84rem; font-weight: 700; margin-bottom: 6px; line-height: 1.4;">
                    <?=h($b['subtitle'])?>
                  </p>
                  <div style="font-size: 0.78rem; color: #94a3b8; display: flex; align-items: center; gap: 6px;">
                    <?=dx_icon('book-open', '', 13)?>
                    <span><?=h($b['pages'])?></span>
                  </div>
                </div>
              </div>
            <?php else: ?>
              <!-- Titolo e Sottotitolo standard -->
              <h2 style="color: #FFFFFF; font-family: var(--font-serif); font-size: 1.45rem; font-weight: 800; margin: 0.5rem 0 0.4rem; line-height: 1.3;">
                <?=h($b['title'])?>
              </h2>
              <p style="color: <?=$b['badge_color']?>; font-size: 0.88rem; font-weight: 700; margin-bottom: 14px; line-height: 1.45;">
                <?=h($b['subtitle'])?>
              </p>
              <div style="font-size: 0.8rem; color: #94a3b8; margin-bottom: 18px; display: flex; align-items: center; gap: 8px;">
                <?=dx_icon('book-open', '', 14)?>
                <span><?=h($b['pages'])?></span>
              </div>
            <?php endif; ?>

            <!-- Sinossi -->
            <p style="color: #cbd5e1; font-size: 0.92rem; line-height: 1.6; margin-bottom: 16px;">
              <?=h($b['description'])?>
            </p>

            <!-- BOX DEI 3 TIER DI PRODOTTO -->
            <div style="background: rgba(8, 12, 22, 0.9); border-radius: 16px; padding: 14px; margin-bottom: 18px; border: 1px solid rgba(255,255,255,0.08);">
              <div style="font-size: 0.74rem; font-weight: 800; text-transform: uppercase; color: var(--gold-primary); letter-spacing: 0.08em; margin-bottom: 10px;">
                SCEGLI IL LIVELLO (TIER):
              </div>

              <div style="display: flex; flex-direction: column; gap: 10px;">
                <?php foreach($b['tiers'] as $t): ?>
                  <div style="border: 1px solid rgba(255,255,255,0.08); background: rgba(255,255,255,0.03); border-radius: 12px; padding: 10px 12px; transition: border-color 0.2s ease;">
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 4px;">
                      <b style="color: #FFFFFF; font-size: 0.88rem; display: flex; align-items: center; gap: 6px;">
                        <span style="display: inline-block; width: 8px; height: 8px; border-radius: 50%; background: <?=$b['badge_color']?>;"></span>
                        Tier <?=$t['tier']?>: <?=h($t['name'])?>
                      </b>
                      <span style="font-weight: 800; color: var(--neon-gold); font-size: 0.95rem;"><?=h($t['price'])?></span>
                    </div>
                    <p style="margin: 0 0 8px; font-size: 0.8rem; color: #94a3b8; line-height: 1.4;">
                      <?=h($t['details'])?>
                    </p>
                    
                    <div>
                      <?php if($t['cta_type'] === 'amazon'): ?>
                        <a href="<?=h($b['amazon_url'])?>" target="_blank" rel="noopener" class="btn small" style="width: 100%; text-decoration: none; border-radius: 8px; font-size: 0.82rem; font-weight: 700; background: #FF9900; color: #000; border: none; display: flex; align-items: center; justify-content: center; gap: 6px;">
                          <?=dx_icon('external-link', '', 13)?> <?=h($t['cta_label'])?>
                        </a>
                      <?php else: ?>
                        <a href="checkout.php?offer_id=<?=urlencode($t['offer_id'])?>" class="btn primary small" style="width: 100%; text-decoration: none; border-radius: 8px; font-size: 0.82rem; font-weight: 700; display: flex; align-items: center; justify-content: center; gap: 6px;">
                          <?=dx_icon('shopping-cart', '', 13)?> <?=h($t['cta_label'])?>
                        </a>
                      <?php endif; ?>
                    </div>
                  </div>
                <?php endforeach; ?>
              </div>
            </div>

          </div>

        </article>
      <?php endforeach; ?>
    </div>
  </section>

  <!-- ============================================================== -->
  <!-- MODALITA' DI PAGAMENTO SUPPORTATE (PAYPAL, CARTE & CRIPTO)     -->
  <!-- ============================================================== -->
  <section class="lux-metallic-card p-4 p-md-5 my-5" style="border: 1px solid rgba(0, 212, 255, 0.35); background: rgba(12, 16, 28, 0.94); border-radius: 22px;">
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 24px; align-items: center;">
      <div>
        <div class="badge-neon-rainbow mb-2" style="font-size: 0.72rem;">
          <span class="dot"></span>
          <span class="text-rainbow">CHECKOUT CRITTOGRAFATO SSL 256-BIT</span>
        </div>
        <h3 style="font-family: var(--font-serif); font-size: 1.5rem; color: #FFFFFF; margin-bottom: 0.5rem;">
          Massima Sicurezza & Libertà di Pagamento
        </h3>
        <p style="color: #cbd5e1; font-size: 0.95rem; line-height: 1.6; margin-bottom: 0;">
          Tutti i formati digitali e i bundle formativi sono protetti da crittografia end-to-end. Puoi completare l'ordine con Carta di Credito/Debito, circuito protetto <strong>PayPal Live</strong>, oppure tramite il wallet di tesoreria sovrana <strong>USDT Polygon</strong>.
        </p>
      </div>

      <div style="display: flex; flex-direction: column; gap: 10px; background: rgba(8, 12, 22, 0.85); padding: 18px; border-radius: 16px; border: 1px solid rgba(255,255,255,0.08);">
        <div style="display: flex; align-items: center; gap: 10px; color: #f8fafc; font-size: 0.88rem; font-weight: 600;">
          <span style="color: var(--neon-gold);"><?=dx_icon('check-circle', '', 16)?></span>
          <span>Carte Visa, Mastercard, Postepay & PayPal</span>
        </div>
        <div style="display: flex; align-items: center; gap: 10px; color: #f8fafc; font-size: 0.88rem; font-weight: 600;">
          <span style="color: var(--neon-cyan);"><?=dx_icon('check-circle', '', 16)?></span>
          <span>USDT Sovrano (Rete Polygon 0x3C32...250C)</span>
        </div>
        <div style="display: flex; align-items: center; gap: 10px; color: #f8fafc; font-size: 0.88rem; font-weight: 600;">
          <span style="color: var(--neon-green);"><?=dx_icon('check-circle', '', 16)?></span>
          <span>Amazon Prime: Consegna rapida & Reso garantito</span>
        </div>
      </div>
    </div>
  </section>

  <!-- ============================================================== -->
  <!-- SUPPORTO ORIENTAMENTO CORTEX                                   -->
  <!-- ============================================================== -->
  <section class="luxury-hero-card lux-metallic-card p-4 p-md-5 text-center my-5" style="border: 1px solid rgba(0, 212, 255, 0.35); border-radius: 22px;">
    <div class="badge-neon-rainbow mb-2">
      <span class="dot"></span>
      <span class="text-rainbow">INTELLIGENZA ARTIFICIALE & GUIDA ALLA LETTURA</span>
    </div>
    <h3 style="font-family: var(--font-serif); font-size: 1.8rem; color: #FFFFFF; margin-bottom: 0.75rem;">
      Non sai quale libro o diario fa al caso tuo in questo momento?
    </h3>
    <p style="color: #cbd5e1; max-width: 640px; margin: 0 auto 1.5rem; font-size: 1rem; line-height: 1.6;">
      Chiedi a <strong>Cortex</strong>, l'assistente cognitivo dell'ecosistema: esponi la tua situazione (famiglia, facilitazione o cammino personale) e ti indicherà il volume e il livello formativo più indicato.
    </p>
    <a href="cortex.php?q=<?=urlencode("Quale libro o diario di Mirco Pregnolato risponde meglio al mio momento attuale?")?>" class="btn primary" style="padding: 0 28px; text-decoration: none; border-radius: 14px;">
      <?=dx_icon('brain', '', 18)?>
      <span style="margin-left: 8px;">Chiedi Consiglio a Cortex</span>
    </a>
  </section>

</div> <!-- /.container-169 -->

<?php require '_footer.php'; ?>
