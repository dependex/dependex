<?php
/**
 * HUB VIAGGI ESPERIENZIALI & RITIRI TRASFORMATIVI · BEWAY.LIFE x DEPENDEX
 * Crociere a tema con workshop, ritiri alpini ed esperienze di riconnessione naturale
 */

declare(strict_types=1);

require_once 'bootstrap.php';

$pageTitle = 'Viaggi Esperienziali, Ritiri & Crociere a Tema · BEWAY.LIFE x DEPENDEX';
$metaDesc = 'Vivi la trasformazione in contesti straordinari: la Grande Crociera della Rinascita nel Mediterraneo con Workshop e Masterclass, ritiri immersivi nelle Dolomiti e cammini di consapevolezza.';
$breadcrumbs = [
    'Home' => '/',
    'Viaggi Esperienziali' => 'viaggi-esperienziali.php'
];
$pageSchemaJson = [
    "@context" => "https://schema.org",
    "@type" => "ItemList",
    "name" => "Viaggi Esperienziali & Ritiri Sobrietà BEWAY.LIFE x DEPENDEX",
    "description" => $metaDesc,
    "itemListElement" => [
        [
            "@type" => "ListItem",
            "position" => 1,
            "name" => "La Grande Crociera della Rinascita: Masterclass & Workshop in Mare Aperto",
            "url" => "https://" . ($brand['domain'] ?? 'dependex.social') . "/crociera-benessere-masterclass.php"
        ],
        [
            "@type" => "ListItem",
            "position" => 2,
            "name" => "Ritiro Forestale & Biohacking Dolomiti: Reset Neurovegetativo",
            "url" => "https://" . ($brand['domain'] ?? 'dependex.social') . "/viaggi-esperienziali.php#ritiro-dolomiti-2026"
        ]
    ]
];
require '_header.php';

$trips = [
    [
        'id' => 'crociera-mediterraneo-2026',
        'code' => 'CRUISE-MED-01',
        'badge' => 'FLAGSHIP · CROCIERA A TEMA',
        'badge_color' => 'var(--neon-gold)',
        'title' => 'La Grande Crociera della Rinascita: Masterclass & Workshop in Mare Aperto',
        'subtitle' => '8 Giorni / 7 Notti nel Mediterraneo · 100% Formula Analcolica d\'Autore · Mentoring con Mirco Pregnolato',
        'description' => 'Un\'esperienza navale unica al mondo. Naviga tra le meraviglie del Mediterraneo (Civitavecchia, Santorini, Mykonos, Atene, Kotor) immergendoti in un ecosistema protetto e stimolante. Ogni giorno comprende risveglio bioenergetico all\'alba, workshop intensivi del Metodo Hudolin ed evoluzione personale con Mirco Pregnolato, alimentazione gourmet vitale e cerchi di ascolto al tramonto sul ponte nave.',
        'dates' => 'Partenze: Maggio / Ottobre 2026 · 8 Giorni / 7 Notti',
        'location' => 'Mediterraneo Orientale & Isole Greche (Partenza Civitavecchia / Venezia)',
        'capacity' => 'Posti Limitati (Massimo 40 partecipanti selezionati per il gruppo Masterclass)',
        'price_from' => 'Da 890 € a persona (Caparra 190 €)',
        'features' => [
            'Formula 100% analcolica con Mocktail Bar d\'autore & infusi bioadattogeni',
            'Workshop quotidiani: Dalla Dipendenza alla Crescita Esponenziale',
            'Sessioni pratiche di Biohacking, Coerenza Cardiaca e Sonno Riparatore',
            'Laboratori speciali per coppie, famiglie e crescita individuale',
            'Tutti i pasti gourmet in pensione completa inclusi'
        ],
        'page_url' => 'crociera-benessere-masterclass.php',
        'cta_label' => 'Scopri la Crociera & Prenota il Tuo Posto',
        'is_featured' => true
    ],
    [
        'id' => 'ritiro-dolomiti-2026',
        'code' => 'RTR-DOL-02',
        'badge' => 'RITIRO IN NATURA · 3 GIORNI',
        'badge_color' => 'var(--neon-green)',
        'title' => 'Ritiro Forestale & Biohacking Dolomiti: Reset Neurovegetativo',
        'subtitle' => '3 Giorni / 2 Notti in Eco-Resort Alpino · Forest Bathing & Decompressione Nervosa',
        'description' => 'Immergiti nella quiete incontaminata dei boschi alpini per ripulire il sistema dopaminergico e ritrovare la lucidità biologica. Comprende pratiche guidate di respirazione in quota, camminate a piedi nudi nel silenzio, cucina a km0 ricca di micronutrienti e seminari serali sulla neurobiologia del benessere.',
        'dates' => 'Giugno / Settembre 2026 · Weekend da Venerdì a Domenica',
        'location' => 'Val Badia / Dolomiti (Trentino-Alto Adige)',
        'capacity' => 'Massimo 18 partecipanti per garantire totale intimità',
        'price_from' => '450 € tutto compreso',
        'features' => [
            'Soggiorno 2 notti in camera singola/doppia in bio-hotel certificato',
            'Pensione completa con alimentazione antinfiammatoria a km0',
            'Sessioni quotidiane di Forest Therapy e cammino consapevole',
            'Workshop pratico: Dominare lo Stress e Disattivare il Cortisolo',
            'Kit con diario di bordo e protocollo di mantenimento a casa'
        ],
        'page_url' => 'checkout.php?offer_id=off_trv_dolomiti',
        'cta_label' => 'Iscriviti al Ritiro Dolomiti (450 €)',
        'is_featured' => false
    ],
    [
        'id' => 'cammino-delta-po-2026',
        'code' => 'TRV-DELTA-03',
        'badge' => 'CAMMINO & RESPIRO · 2 GIORNI',
        'badge_color' => 'var(--neon-cyan)',
        'title' => 'Cammino del Delta del Po: Il Respiro dell\'Acqua & Rinascita',
        'subtitle' => '2 Giorni tra Lagune, Foci e Silenzio · In sinergia con il Convegno di Taglio di Po',
        'description' => 'Un\'esperienza a contatto con la terra e l\'acqua primordiale nel Parco Regionale del Delta del Po. Camminate lente tra canneti e lagune, navigazione silenziosa all\'imbrunire, meditazione del respiro e cerchio di condivisione comunitario attorno al fuoco serale.',
        'dates' => 'Ottobre 2026 (Weekend del Convegno di Taglio di Po)',
        'location' => 'Parco del Delta del Po (Rovigo - Veneto)',
        'capacity' => 'Massimo 25 partecipanti',
        'price_from' => '190 € a persona',
        'features' => [
            '1 notte in agriturismo storico immerso nella natura del Delta',
            'Pranzi e cene tipiche della tradizione polesana a km0',
            'Escursione guidata in barca elettrica nei canneti protetti',
            'Cerchio di confronto esperienziale condotto da servitori esperti',
            'Integrazione diretta con il Convegno Interclub di Taglio di Po'
        ],
        'page_url' => 'checkout.php?offer_id=off_trv_delta_po',
        'cta_label' => 'Partecipa al Cammino del Delta (190 €)',
        'is_featured' => false
    ]
];
?>

<div class="container-169 py-4">

  <!-- ============================================================== -->
  <!-- HERO BANNER PANORAMICO BEWAY.LIFE x DEPENDEX (16:9 & 9:16)      -->
  <!-- ============================================================== -->
  <section class="mb-5 text-center">
    <div class="badge-neon-rainbow mb-3" style="font-size: 0.8rem; padding: 6px 18px;">
      <span class="dot"></span>
      <span class="text-rainbow">BEWAY.LIFE x DEPENDEX · THE CONSCIOUS TRAVEL & TRANSFORMATION HUB</span>
    </div>
    
    <h1 style="font-family: var(--font-serif); font-size: clamp(2.2rem, 5vw, 3.6rem); font-weight: 900; color: #FFFFFF; line-height: 1.15; margin-bottom: 1.2rem;">
      Viaggi Esperienziali, Ritiri Olistici & <br><span class="text-rainbow">Crociere a Tema in Mare Aperto</span>
    </h1>
    
    <p style="font-size: 1.18rem; line-height: 1.7; color: #cbd5e1; max-width: 880px; margin: 0 auto 2rem;">
      La sobrietà e la rinascita personale non sono privazione: sono l'inizio di una vita straordinaria, ricca di bellezza, avventura e connessione autentica. Attraverso l'alleanza tra <strong>DEPENDEX</strong> e l'ecosistema lifestyle <strong>BEWAY.LIFE</strong>, creiamo viaggi trasformativi protetti con <strong>Masterclass intensive, Workshop del Metodo Hudolin e crescita esponenziale guidata da Mirco Pregnolato</strong>.
    </p>

    <div style="display: flex; justify-content: center; gap: 14px; flex-wrap: wrap;">
      <a href="#crociera" class="btn primary" style="padding: 14px 32px; font-weight: 800; font-size: 1rem; border-radius: 14px; text-decoration: none;">
        <?=dx_icon('compass', '', 18)?> Scopri la Grande Crociera
      </a>
      <a href="#catalogo-viaggi" class="btn" style="padding: 14px 28px; font-weight: 700; font-size: 0.95rem; border-radius: 14px; border: 1px solid rgba(255,255,255,0.2); color: #fff; text-decoration: none;">
        <?=dx_icon('calendar', '', 18)?> Tutti i Viaggi in Programma
      </a>
    </div>
  </section>

  <!-- ============================================================== -->
  <!-- HERO BANNER IMMERSIVO CROCIERA                                 -->
  <!-- ============================================================== -->
  <div id="crociera" class="lux-metallic-card p-4 p-md-5 mb-5 rainbow-border" style="background: radial-gradient(circle at top right, rgba(0,212,255,0.12), rgba(12,16,28,0.96)); border-radius: 26px; box-shadow: 0 16px 50px rgba(0,0,0,0.8);">
    <div class="row align-items-center g-4">
      <div class="col-lg-7">
        <div class="badge-neon-rainbow mb-2">
          <span class="dot"></span>
          <span class="text-rainbow">AVVISO COMMUNITY · INIZIATIVA RESIDENZIALE CIURMA</span>
        </div>
        <h2 style="font-family: var(--font-serif); font-size: clamp(1.8rem, 3.5vw, 2.6rem); font-weight: 900; color: #FFFFFF; line-height: 1.2; margin-bottom: 1rem;">
          Iniziativa Residenziale CIURMA: <br><span class="text-rainbow">Percorso di Rinascita & Famiglie sul Mare</span>
        </h2>
        <p style="color: #cbd5e1; font-size: 1.05rem; line-height: 1.65; margin-bottom: 1.2rem;">
          8 giorni e 7 notti tra le isole più affascinanti del Mediterraneo. Un'esperienza immersiva al 100% analcolica, con cucina viva, alba sul ponte con pratiche respiratorie e <strong>accompagnamento intensivo tenuto da Mirco Pregnolato</strong> sui pilastri della trasformazione e della relazione d'aiuto.
        </p>

        <div style="background: rgba(0, 212, 255, 0.08); border-left: 3px solid var(--neon-cyan); padding: 10px 14px; border-radius: 0 10px 10px 0; margin-bottom: 1.2rem; font-size: 0.88rem; color: #e2e8f0;">
          <strong>Nota trasparenza:</strong> DEPENDEX è solo Advisor informativo. Le iscrizioni e il programma sono ospitati su <a href="https://mircopregnolato.it/ciurma.html" target="_blank" rel="noopener" style="color: var(--neon-cyan); font-weight: 800;">mircopregnolato.it</a>. La logistica nave è venduta in via esclusiva dall'Agenzia Viaggi partner autorizzata.
        </div>

        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 12px; margin-bottom: 1.8rem;">
          <div style="background: rgba(255,255,255,0.04); border: 1px solid rgba(255,255,255,0.08); border-radius: 12px; padding: 10px 14px;">
            <small style="color: var(--neon-gold); font-weight: 800; text-transform: uppercase; font-size: 0.72rem; letter-spacing: 0.05em; display: block;">ITINERARIO</small>
            <b style="color: #FFFFFF; font-size: 0.92rem;">Santorini · Mykonos · Atene · Kotor</b>
          </div>
          <div style="background: rgba(255,255,255,0.04); border: 1px solid rgba(255,255,255,0.08); border-radius: 12px; padding: 10px 14px;">
            <small style="color: var(--neon-cyan); font-weight: 800; text-transform: uppercase; font-size: 0.72rem; letter-spacing: 0.05em; display: block;">FORMULA</small>
            <b style="color: #FFFFFF; font-size: 0.92rem;">Famiglie & Conduttori</b>
          </div>
        </div>

        <div style="display: flex; gap: 14px; flex-wrap: wrap;">
          <a href="https://mircopregnolato.it/ciurma.html" target="_blank" rel="noopener" class="btn primary" style="padding: 12px 28px; font-weight: 800; border-radius: 12px; text-decoration: none; font-size: 0.98rem;">
            <?=dx_icon('external-link', '', 16)?> Iscrizioni su mircopregnolato.it
          </a>
          <a href="crociera-benessere-masterclass.php" class="btn" style="padding: 12px 22px; font-weight: 700; border-radius: 12px; border: 1px solid rgba(255,255,255,0.2); color: #fff; text-decoration: none; font-size: 0.92rem;">
            <?=dx_icon('compass', '', 16)?> Scheda Advisor
          </a>
          <a href="https://wa.me/393478844271?text=<?=urlencode('Buongiorno, desidero informazioni sull\'iniziativa residenziale CIURMA.')?>" target="_blank" rel="noopener" class="btn" style="background: #25D366; color: #000; font-weight: 800; border-radius: 12px; text-decoration: none; border: none; padding: 12px 20px; display: inline-flex; align-items: center; gap: 8px; font-size: 0.9rem;">
            <?=dx_icon('whatsapp', '', 16)?> Concierge Agenzia
          </a>
        </div>
      </div>

      <div class="col-lg-5 text-center">
        <div style="position: relative; border-radius: 20px; overflow: hidden; border: 1px solid rgba(0, 212, 255, 0.4); box-shadow: 0 12px 40px rgba(0,212,255,0.25);">
          <img src="assets/img/rainbow-nebula-panorama.jpg" alt="Crociera Rinascita in Mare Aperto" style="width: 100%; height: 320px; object-fit: cover;">
          <div style="position: absolute; bottom: 0; left: 0; right: 0; padding: 16px; background: linear-gradient(to top, rgba(8,12,22,0.95), transparent);">
            <span style="color: var(--neon-gold); font-weight: 800; font-size: 0.88rem; display: block;">POSTI LIMITATI · GRUPPO ESCLUSIVO</span>
            <small style="color: #cbd5e1; font-size: 0.78rem;">Cabine con caparra confirmatoria e rateizzabili</small>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- ============================================================== -->
  <!-- I 4 PILASTRI DEI VIAGGI BEWAY.LIFE x DEPENDEX                  -->
  <!-- ============================================================== -->
  <section class="mb-5">
    <div class="text-center mb-4">
      <div class="badge-neon-rainbow mb-2" style="font-size: 0.74rem;">
        <span class="dot"></span>
        <span class="text-rainbow">FILOSOFIA DEI VIAGGI TRASFORMATIVI</span>
      </div>
      <h2 style="font-family: var(--font-serif); font-size: 2rem; color: #FFFFFF; font-weight: 800;">
        Perché un Viaggio Esperienziale BEWAY.LIFE è Diverso da Tutto
      </h2>
    </div>

    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(260px, 1fr)); gap: 22px;">
      <div class="card card-neon-cyan p-4" style="background: rgba(12,16,26,0.92); border-radius: 18px; border: 1px solid rgba(255,255,255,0.1);">
        <div style="margin-bottom: 12px; display: inline-flex; align-items: center; justify-content: center; width: 48px; height: 48px; border-radius: 12px; background: rgba(0, 212, 255, 0.12); border: 1px solid rgba(0, 212, 255, 0.35);">
          <?=dx_icon('shield-check', 'text-neon-cyan', 26)?>
        </div>
        <h3 style="color: #FFFFFF; font-size: 1.15rem; font-weight: 800; margin-bottom: 8px;">Ambiente Libero da Stimoli Tossici</h3>
        <p style="color: #cbd5e1; font-size: 0.9rem; line-height: 1.6; margin: 0;">
          Nessuna pressione sociale al consumo. Nei nostri viaggi l'alcol e le abitudini nocive sono totalmente assenti, sostituiti da una cultura di eleganza sobria, mocktail botanici ed energia pura.
        </p>
      </div>

      <div class="card card-neon-gold p-4" style="background: rgba(12,16,26,0.92); border-radius: 18px; border: 1px solid rgba(255,255,255,0.1);">
        <div style="margin-bottom: 12px; display: inline-flex; align-items: center; justify-content: center; width: 48px; height: 48px; border-radius: 12px; background: rgba(255, 215, 0, 0.12); border: 1px solid rgba(255, 215, 0, 0.35);">
          <?=dx_icon('brain', 'text-neon-gold', 26)?>
        </div>
        <h3 style="color: #FFFFFF; font-size: 1.15rem; font-weight: 800; margin-bottom: 8px;">Masterclass con Mirco Pregnolato</h3>
        <p style="color: #cbd5e1; font-size: 0.9rem; line-height: 1.6; margin: 0;">
          Non semplici vacanze ma percorsi di evoluzione accelerata. Ogni giorno sessioni formative di gruppo e momenti di riflessione profonda per riprogrammare le abitudini e gli obiettivi di vita.
        </p>
      </div>

      <div class="card card-neon-green p-4" style="background: rgba(12,16,26,0.92); border-radius: 18px; border: 1px solid rgba(255,255,255,0.1);">
        <div style="margin-bottom: 12px; display: inline-flex; align-items: center; justify-content: center; width: 48px; height: 48px; border-radius: 12px; background: rgba(0, 255, 119, 0.12); border: 1px solid rgba(0, 255, 119, 0.35);">
          <?=dx_icon('heart-handshake', 'text-neon-green', 26)?>
        </div>
        <h3 style="color: #FFFFFF; font-size: 1.15rem; font-weight: 800; margin-bottom: 8px;">Il Cerchio dei Pari & Famiglia</h3>
        <p style="color: #cbd5e1; font-size: 0.9rem; line-height: 1.6; margin: 0;">
          Viaggerai accanto a persone che condividono i tuoi stessi valori di dignità, sobrietà e desiderio di crescita. Un contesto caldo, accogliente e privo di giudizio.
        </p>
      </div>

      <div class="card card-neon-violet p-4" style="background: rgba(12,16,26,0.92); border-radius: 18px; border: 1px solid rgba(255,255,255,0.1);">
        <div style="margin-bottom: 12px; display: inline-flex; align-items: center; justify-content: center; width: 48px; height: 48px; border-radius: 12px; background: rgba(184, 41, 255, 0.12); border: 1px solid rgba(184, 41, 255, 0.35);">
          <?=dx_icon('sparkles', 'text-neon-violet', 26)?>
        </div>
        <h3 style="color: #FFFFFF; font-size: 1.15rem; font-weight: 800; margin-bottom: 8px;">Biohacking & Riconnessione Biologica</h3>
        <p style="color: #cbd5e1; font-size: 0.9rem; line-height: 1.6; margin: 0;">
          Ripristino dei ritmi circadiani, coerenza cardiaca, respirazione guidata e alimentazione antinfiammatoria: il corpo torna ad essere il tempio della tua lucidità.
        </p>
      </div>
    </div>
  </section>

  <!-- ============================================================== -->
  <!-- CATALOGO VIAGGI ED ESPERIENZE ATTIVE                            -->
  <!-- ============================================================== -->
  <section id="catalogo-viaggi" class="mb-5">
    <div class="text-center mb-4">
      <div class="badge-neon-rainbow mb-2" style="font-size: 0.74rem;">
        <span class="dot"></span>
        <span class="text-rainbow">CALENDARIO ESPERIENZIALE UFFICIALE</span>
      </div>
      <h2 style="font-family: var(--font-serif); font-size: 2rem; color: #FFFFFF; font-weight: 800;">
        Tutti i Viaggi & Ritiri Disponibili
      </h2>
    </div>

    <div class="grid-169-3col" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(min(100%, 340px), 1fr)); gap: 28px;">
      <?php foreach($trips as $t): ?>
        <article class="card p-4" style="display: flex; flex-direction: column; justify-content: space-between; border-radius: 22px; background: rgba(12, 16, 26, 0.94); border: 1px solid rgba(255, 255, 255, 0.12); box-shadow: 0 12px 35px rgba(0,0,0,0.6);">
          <div>
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 12px; gap: 8px;">
              <span class="dx-ticker-badge" style="color: <?=$t['badge_color']?>; border-color: <?=$t['badge_color']?>; font-size: 0.74rem;">
                <?=$t['badge']?>
              </span>
              <span style="font-size: 0.72rem; font-weight: 800; color: #cbd5e1; background: rgba(255,255,255,0.08); padding: 4px 10px; border-radius: 8px;">
                <?=$t['code']?>
              </span>
            </div>

            <h3 style="color: #FFFFFF; font-family: var(--font-serif); font-size: 1.38rem; font-weight: 800; margin: 0.4rem 0 0.4rem; line-height: 1.3;">
              <?=h($t['title'])?>
            </h3>
            <p style="color: <?=$t['badge_color']?>; font-size: 0.86rem; font-weight: 700; margin-bottom: 12px; line-height: 1.45;">
              <?=h($t['subtitle'])?>
            </p>

            <p style="color: #cbd5e1; font-size: 0.9rem; line-height: 1.6; margin-bottom: 16px;">
              <?=h($t['description'])?>
            </p>

            <!-- Box Dettagli -->
            <div style="background: rgba(8, 12, 22, 0.85); border-radius: 14px; padding: 12px 14px; margin-bottom: 16px; border: 1px solid rgba(255,255,255,0.06); font-size: 0.84rem; display: flex; flex-direction: column; gap: 6px;">
              <div style="color: #f8fafc; display: flex; align-items: center; gap: 6px;"><?=dx_icon('calendar', 'text-neon-gold', 14)?> <strong>Date:</strong> <?=h($t['dates'])?></div>
              <div style="color: #f8fafc; display: flex; align-items: center; gap: 6px;"><?=dx_icon('map-pin', 'text-neon-red', 14)?> <strong>Luogo:</strong> <?=h($t['location'])?></div>
              <div style="color: var(--neon-gold); display: flex; align-items: center; gap: 6px;"><?=dx_icon('users', 'text-neon-cyan', 14)?> <strong>Capienza:</strong> <?=h($t['capacity'])?></div>
              <div style="color: var(--neon-cyan); font-weight: 800; font-size: 0.95rem; margin-top: 4px; display: flex; align-items: center; gap: 6px;">
                <?=dx_icon('tag', 'text-neon-green', 14)?> <?=h($t['price_from'])?>
              </div>
            </div>

            <!-- Inclusi -->
            <div style="margin-bottom: 18px;">
              <span style="font-size: 0.74rem; font-weight: 800; color: #94a3b8; text-transform: uppercase; letter-spacing: 0.05em; display: block; margin-bottom: 6px;">Cosa include l'esperienza:</span>
              <ul style="list-style: none; padding: 0; margin: 0; font-size: 0.82rem; color: #cbd5e1; display: flex; flex-direction: column; gap: 4px;">
                <?php foreach($t['features'] as $f): ?>
                  <li style="display: flex; align-items: flex-start; gap: 6px;">
                    <span style="color: <?=$t['badge_color']?>;">✓</span>
                    <span><?=h($f)?></span>
                  </li>
                <?php endforeach; ?>
              </ul>
            </div>
          </div>

          <div>
            <a href="<?=h($t['page_url'])?>" class="btn primary" style="width: 100%; border-radius: 12px; font-weight: 800; font-size: 0.92rem; text-decoration: none; display: flex; align-items: center; justify-content: center; gap: 8px;">
              <?=dx_icon('compass', '', 16)?> <?=h($t['cta_label'])?>
            </a>
          </div>
        </article>
      <?php endforeach; ?>
    </div>
  </section>

  <!-- ============================================================== -->
  <!-- BOX DIALOGO DIRETTO CON IL CONCIERGE                           -->
  <!-- ============================================================== -->
  <section class="lux-metallic-card p-4 p-md-5 my-5 text-center" style="border: 1px solid rgba(0, 212, 255, 0.35); background: rgba(12, 16, 28, 0.94); border-radius: 22px;">
    <div class="badge-neon-rainbow mb-2" style="font-size: 0.74rem;">
      <span class="dot"></span>
      <span class="text-rainbow">ASSISTENZA DEDICATA & PRENOTAZIONE CONFIDENZIALE</span>
    </div>
    <h3 style="font-family: var(--font-serif); font-size: 1.8rem; color: #FFFFFF; margin-bottom: 0.75rem;">
      Desideri assistenza personalizzata per il tuo viaggio o per la tua famiglia?
    </h3>
    <p style="color: #cbd5e1; max-width: 680px; margin: 0 auto 1.6rem; font-size: 1rem; line-height: 1.6;">
      Il nostro Concierge riservato <strong>BEWAY.LIFE</strong> risponde direttamente per illustrarti cabine, trasferimenti, piani pasto personalizzati e opzioni di rateizzazione della quota.
    </p>

    <div style="display: flex; justify-content: center; gap: 14px; flex-wrap: wrap;">
      <a href="https://wa.me/393478844271?text=<?=urlencode('Buongiorno, vorrei parlare con il concierge BEWAY.LIFE per informazioni sui viaggi esperienziali.')?>" target="_blank" rel="noopener" class="btn" style="background: #25D366; color: #000; font-weight: 800; padding: 12px 28px; border-radius: 12px; text-decoration: none; display: inline-flex; align-items: center; gap: 8px;">
        <?=dx_icon('whatsapp', '', 18)?> Parla con il Concierge (+39 347 884 4271)
      </a>
      <a href="mailto:info@beway.life" class="btn" style="border: 1px solid rgba(255,255,255,0.25); color: #FFF; padding: 12px 24px; border-radius: 12px; text-decoration: none; display: inline-flex; align-items: center; gap: 8px;">
        <?=dx_icon('mail', '', 18)?> Scrivi a info@beway.life
      </a>
    </div>
  </section>

</div> <!-- /.container-169 -->

<?php require '_footer.php'; ?>
