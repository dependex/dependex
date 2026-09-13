<?php
/**
 * SEED SCRIPT: Mirco Pregnolato Books, Beway.life & Experiential Travel / Cruises
 */

declare(strict_types=1);

require_once __DIR__ . '/../bootstrap.php';

$dbs = [
    __DIR__ . '/../data/acat_community.sqlite',
    __DIR__ . '/../data/dependex.db'
];

foreach ($dbs as $dbPath) {
    if (!file_exists($dbPath)) {
        continue;
    }
    $pdo = new PDO('sqlite:' . $dbPath);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Verifica presenza tabelle commerce
    $tblCheck = $pdo->query("SELECT name FROM sqlite_master WHERE type='table' AND name='commerce_businesses'")->fetch();
    if (!$tblCheck) {
        continue;
    }
    echo "--- Sincronizzazione Database Commerce: $dbPath ---\n";

    // 1. Inserimento Business beway.life se non presente
    $checkBiz = $pdo->prepare("SELECT id FROM commerce_businesses WHERE id = 'biz_beway'");
    $checkBiz->execute();
    if (!$checkBiz->fetch()) {
        $insBiz = $pdo->prepare("INSERT INTO commerce_businesses (id, code, name, domain, default_currency, active) VALUES (?, ?, ?, ?, ?, 1)");
        $insBiz->execute(['biz_beway', 'BEWAY', 'BEWAY.LIFE · Viaggi Esperienziali & Lifestyle', 'beway.life', 'EUR']);
        echo "  [+] Aggiunto business: biz_beway (beway.life)\n";
    }

    // 2. Registrazione Prodotti Libri Mirco Pregnolato
    $books = [
        [
            'id' => 'prd_book_famiglia',
            'business_id' => 'biz_dependex',
            'name' => 'Quaderno della Famiglia',
            'description' => 'Strumento di lavoro, riflessione e dialogo nel percorso di cambiamento e sobrietà familiare. 172 pagine KDP 6x9".',
            'sku' => 'BK-FAMIGLIA-01',
            'default_price' => 14.90
        ],
        [
            'id' => 'prd_book_diario_club',
            'business_id' => 'biz_dependex',
            'name' => 'Il Diario del Club: 90 Giorni di Rinascita e Sobrietà',
            'description' => 'Il diario operativo per i membri e le famiglie dei Club Alcologici Territoriali. 223 pagine KDP 6x9".',
            'sku' => 'BK-CLUB90-02',
            'default_price' => 14.90
        ],
        [
            'id' => 'prd_book_servitore',
            'business_id' => 'biz_dependex',
            'name' => 'Diario Servitore Insegnante',
            'description' => 'Guida pratica e operativa per il facilitatore e servitore-insegnante di Club. 112 pagine KDP 6x9".',
            'sku' => 'BK-SERVITORE-03',
            'default_price' => 14.90
        ],
        [
            'id' => 'prd_book_crescita_esp',
            'business_id' => 'biz_mircopregnolato',
            'name' => 'Il Mio Diario di Crescita Esponenziale',
            'description' => 'Percorso quotidiano di potenziamento personale, abitudini e trasformazione profonda. 386 pagine KDP 6x9".',
            'sku' => 'BK-CRESCITA-04',
            'default_price' => 19.90
        ],
        [
            'id' => 'prd_book_sat_trilogia',
            'business_id' => 'biz_dependex',
            'name' => 'Trilogia SAT (Scuola Alcologica Territoriale)',
            'description' => 'Workbook ufficiali SAT I (8 Incontri), SAT II (Consolidamento) e SAT III (Leadership Comunitaria).',
            'sku' => 'BK-SAT-TRILOGY-05',
            'default_price' => 24.90
        ],
        [
            'id' => 'prd_book_52_settimane',
            'business_id' => 'biz_mircopregnolato',
            'name' => '52 Settimane di Cambiamento Workbook',
            'description' => 'Percorso annuale guidato di trasformazione, salute olistica e tracciamento continuo. 237 pagine KDP 6x9".',
            'sku' => 'BK-52WEEKS-06',
            'default_price' => 14.90
        ],
        // Prodotti Viaggi Esperienziali
        [
            'id' => 'prd_travel_crociera',
            'business_id' => 'biz_beway',
            'name' => 'Grande Crociera della Rinascita & Sobrietà',
            'description' => '8 Giorni / 7 Notti nel Mediterraneo. Masterclass e Workshop intensivi in mare aperto con Mirco Pregnolato. Formula 100% analcolica d\'eccellenza.',
            'sku' => 'TRV-CRUISE-MED-2026',
            'default_price' => 890.00
        ],
        [
            'id' => 'prd_travel_dolomiti',
            'business_id' => 'biz_beway',
            'name' => 'Ritiro Forestale & Biohacking Dolomiti',
            'description' => '3 Giorni immersivi nella natura alpina per il reset del sistema nervoso, sonno riparatore e mindfulness.',
            'sku' => 'TRV-DOLOMITI-2026',
            'default_price' => 450.00
        ],
        [
            'id' => 'prd_travel_delta_po',
            'business_id' => 'biz_beway',
            'name' => 'Cammino del Delta del Po & Respiro della Natura',
            'description' => '2 Giorni nel Parco del Delta del Po. Connessione, natura, silenzio e condivisione esperienziale.',
            'sku' => 'TRV-DELTA-PO-2026',
            'default_price' => 190.00
        ]
    ];

    foreach ($books as $b) {
        $chkPrd = $pdo->prepare("SELECT id FROM commerce_products WHERE id = ?");
        $chkPrd->execute([$b['id']]);
        if (!$chkPrd->fetch()) {
            $insPrd = $pdo->prepare("INSERT INTO commerce_products (id, business_id, sku, name, description, default_price, currency, active) VALUES (?, ?, ?, ?, ?, ?, 'EUR', 1)");
            $insPrd->execute([$b['id'], $b['business_id'], $b['sku'], $b['name'], $b['description'], $b['default_price']]);
            echo "  [+] Registrato prodotto: {$b['id']}\n";
        }
    }

    // 3. Registrazione Offerte a 3 TIER per i libri e viaggi
    $offers = [
        // Quaderno della Famiglia
        [
            'id' => 'off_bk_famiglia_dig', 'business_id' => 'biz_dependex', 'product_id' => 'prd_book_famiglia',
            'offer_code' => 'BK-FAM-T1-DIG', 'title' => 'Quaderno della Famiglia · PDF Operativo',
            'subtitle' => 'Tier 1: Edizione Digitale ad Alta Definizione (Download Immediato)',
            'description' => 'Il file PDF ufficiale pronto per la stampa o l\'utilizzo su tablet/PC. Include tutte le schede di dialogo familiare.',
            'price' => 14.90, 'offer_tier' => 1, 'badge' => 'DIGITALE'
        ],
        [
            'id' => 'off_bk_famiglia_kdp', 'business_id' => 'biz_dependex', 'product_id' => 'prd_book_famiglia',
            'offer_code' => 'BK-FAM-T2-KDP', 'title' => 'Quaderno della Famiglia · Cartaceo Ufficiale Amazon',
            'subtitle' => 'Tier 2: Copertina Flessibile KDP 6x9" Carta Crema',
            'description' => 'Stampa tipografica ufficiale Amazon KDP con spedizione rapida Prime a domicilio.',
            'price' => 24.90, 'offer_tier' => 2, 'badge' => 'AMAZON PRIME'
        ],
        [
            'id' => 'off_bk_famiglia_bundle', 'business_id' => 'biz_dependex', 'product_id' => 'prd_book_famiglia',
            'offer_code' => 'BK-FAM-T3-BDL', 'title' => 'Quaderno della Famiglia · Masterclass Bundle',
            'subtitle' => 'Tier 3: Libro Cartaceo + PDF Operativo + Video Masterclass Famiglia',
            'description' => 'Il pacchetto formativo completo: copia cartacea a domicilio, PDF immediato e masterclass video di Mirco Pregnolato sulle dinamiche di coppia e famiglia.',
            'price' => 49.00, 'offer_tier' => 3, 'badge' => 'BUNDLE COMPLETO'
        ],

        // Il Diario del Club
        [
            'id' => 'off_bk_club_dig', 'business_id' => 'biz_dependex', 'product_id' => 'prd_book_diario_club',
            'offer_code' => 'BK-CLB-T1-DIG', 'title' => 'Il Diario del Club: 90 Giorni · PDF Operativo',
            'subtitle' => 'Tier 1: Edizione Digitale Interattiva (90 Giorni di Schede)',
            'description' => 'Download immediato del diario in formato PDF con schede giornaliere compilabili.',
            'price' => 14.90, 'offer_tier' => 1, 'badge' => 'DIGITALE'
        ],
        [
            'id' => 'off_bk_club_kdp', 'business_id' => 'biz_dependex', 'product_id' => 'prd_book_diario_club',
            'offer_code' => 'BK-CLB-T2-KDP', 'title' => 'Il Diario del Club: 90 Giorni · Cartaceo Amazon KDP',
            'subtitle' => 'Tier 2: Edizione Cartacea 223 Pagine Amazon KDP',
            'description' => 'Volume rilegato di 223 pagine, copertina satinata e carta crema per accompagnare il percorso dei primi 3 mesi.',
            'price' => 24.90, 'offer_tier' => 2, 'badge' => 'AMAZON PRIME'
        ],
        [
            'id' => 'off_bk_club_bundle', 'business_id' => 'biz_dependex', 'product_id' => 'prd_book_diario_club',
            'offer_code' => 'BK-CLB-T3-BDL', 'title' => 'Il Diario del Club · Bundle Rinascita Totale',
            'subtitle' => 'Tier 3: Diario Cartaceo + PDF + Percorso Audio 90 Giorni',
            'description' => 'Diario cartaceo, versione digitale e 12 pillole audio settimanali di orientamento alla sobrietà.',
            'price' => 49.00, 'offer_tier' => 3, 'badge' => 'BEST SELLER'
        ],

        // Diario Servitore Insegnante
        [
            'id' => 'off_bk_serv_dig', 'business_id' => 'biz_dependex', 'product_id' => 'prd_book_servitore',
            'offer_code' => 'BK-SRV-T1-DIG', 'title' => 'Diario Servitore Insegnante · PDF Strumentale',
            'subtitle' => 'Tier 1: Guida Digitale di Conduzione del Cerchio',
            'description' => 'Formato digitale scaricabile con schede di accoglienza, deontologia e gestione del gruppo.',
            'price' => 14.90, 'offer_tier' => 1, 'badge' => 'DIGITALE'
        ],
        [
            'id' => 'off_bk_serv_kdp', 'business_id' => 'biz_dependex', 'product_id' => 'prd_book_servitore',
            'offer_code' => 'BK-SRV-T2-KDP', 'title' => 'Diario Servitore Insegnante · Cartaceo Amazon KDP',
            'subtitle' => 'Tier 2: Manuale Pratico Formato Tascabile KDP',
            'description' => 'Edizione cartacea KDP per il facilitatore di Club, sempre a portata di mano durante gli incontri.',
            'price' => 19.90, 'offer_tier' => 2, 'badge' => 'AMAZON PRIME'
        ],
        [
            'id' => 'off_bk_serv_bundle', 'business_id' => 'biz_dependex', 'product_id' => 'prd_book_servitore',
            'offer_code' => 'BK-SRV-T3-BDL', 'title' => 'Diario Servitore Insegnante · Academy Pass',
            'subtitle' => 'Tier 3: Libro Cartaceo + Accesso Masterclass Servitori',
            'description' => 'Include il manuale cartaceo e l\'accesso ai moduli formativi dell\'Academy per servitori-insegnanti.',
            'price' => 49.00, 'offer_tier' => 3, 'badge' => 'FORMAZIONE'
        ],

        // Diario di Crescita Esponenziale
        [
            'id' => 'off_bk_crescita_dig', 'business_id' => 'biz_mircopregnolato', 'product_id' => 'prd_book_crescita_esp',
            'offer_code' => 'BK-CRE-T1-DIG', 'title' => 'Il Mio Diario di Crescita Esponenziale · PDF 386p',
            'subtitle' => 'Tier 1: Edizione Digitale Annuale Completa',
            'description' => 'Download immediato del diario di 386 pagine con tutte le 365 schede di evoluzione personale.',
            'price' => 19.90, 'offer_tier' => 1, 'badge' => 'DIGITALE'
        ],
        [
            'id' => 'off_bk_crescita_kdp', 'business_id' => 'biz_mircopregnolato', 'product_id' => 'prd_book_crescita_esp',
            'offer_code' => 'BK-CRE-T2-KDP', 'title' => 'Il Mio Diario di Crescita Esponenziale · Cartaceo Amazon',
            'subtitle' => 'Tier 2: Volume Rilegato di Pregio KDP 386 Pagine',
            'description' => 'Il compagno di un intero anno di evoluzione: stampa Amazon KDP su carta crema di alta grammatura.',
            'price' => 29.90, 'offer_tier' => 2, 'badge' => 'AMAZON PRIME'
        ],
        [
            'id' => 'off_bk_crescita_bundle', 'business_id' => 'biz_mircopregnolato', 'product_id' => 'prd_book_crescita_esp',
            'offer_code' => 'BK-CRE-T3-BDL', 'title' => 'Crescita Esponenziale · Executive Masterclass Bundle',
            'subtitle' => 'Tier 3: Libro Cartaceo + PDF + Masterclass Strategica con Mirco Pregnolato',
            'description' => 'Libro cartaceo a domicilio, diario in PDF e video masterclass esclusiva sui 5 pilastri della trasformazione radicale.',
            'price' => 69.00, 'offer_tier' => 3, 'badge' => 'EXECUTIVE'
        ],

        // Trilogia SAT
        [
            'id' => 'off_bk_sat_dig', 'business_id' => 'biz_dependex', 'product_id' => 'prd_book_sat_trilogia',
            'offer_code' => 'BK-SAT-T1-DIG', 'title' => 'Trilogia SAT Workbook (SAT I, II, III) · PDF Suite',
            'subtitle' => 'Tier 1: Tutti i 3 Workbook SAT in Formato Digitale',
            'description' => 'I tre tomi formativi delle Scuole Alcologiche Territoriali in download immediato.',
            'price' => 24.90, 'offer_tier' => 1, 'badge' => 'DIGITALE'
        ],
        [
            'id' => 'off_bk_sat_kdp', 'business_id' => 'biz_dependex', 'product_id' => 'prd_book_sat_trilogia',
            'offer_code' => 'BK-SAT-T2-KDP', 'title' => 'Trilogia SAT Workbook · 3 Volumi Cartacei KDP',
            'subtitle' => 'Tier 2: Cofanetto 3 Volumi Stampati Amazon KDP',
            'description' => 'I tre manuali stampati per operatori, famiglie e corsisti delle Scuole Territoriali.',
            'price' => 39.90, 'offer_tier' => 2, 'badge' => 'AMAZON PRIME'
        ],
        [
            'id' => 'off_bk_sat_bundle', 'business_id' => 'biz_dependex', 'product_id' => 'prd_book_sat_trilogia',
            'offer_code' => 'BK-SAT-T3-BDL', 'title' => 'Trilogia SAT · Formazione Ufficiale & Certificazione',
            'subtitle' => 'Tier 3: 3 Volumi Cartacei + PDF + Moduli Didattici SAT',
            'description' => 'Materiali completi e accesso ai quiz di verifica per il rilascio dell\'attestato di frequenza del corso di sensibilizzazione.',
            'price' => 79.00, 'offer_tier' => 3, 'badge' => 'CERTIFICAZIONE'
        ],

        // 52 Settimane di Cambiamento
        [
            'id' => 'off_bk_52w_dig', 'business_id' => 'biz_mircopregnolato', 'product_id' => 'prd_book_52_settimane',
            'offer_code' => 'BK-52W-T1-DIG', 'title' => '52 Settimane di Cambiamento · PDF Workbook',
            'subtitle' => 'Tier 1: Percorso Annuale Digitale Stampabile',
            'description' => '52 settimane di esercizi guidati, riflessioni e bilancio settimanale in PDF ad alta risoluzione.',
            'price' => 14.90, 'offer_tier' => 1, 'badge' => 'DIGITALE'
        ],
        [
            'id' => 'off_bk_52w_kdp', 'business_id' => 'biz_mircopregnolato', 'product_id' => 'prd_book_52_settimane',
            'offer_code' => 'BK-52W-T2-KDP', 'title' => '52 Settimane di Cambiamento · Cartaceo KDP 237p',
            'subtitle' => 'Tier 2: Volume Cartaceo 237 Pagine Amazon KDP',
            'description' => 'Edizione cartacea elegante per pianificare la propria vita sobria settimana dopo settimana.',
            'price' => 24.90, 'offer_tier' => 2, 'badge' => 'AMAZON PRIME'
        ],
        [
            'id' => 'off_bk_52w_bundle', 'business_id' => 'biz_mircopregnolato', 'product_id' => 'prd_book_52_settimane',
            'offer_code' => 'BK-52W-T3-BDL', 'title' => '52 Settimane · Annual Habit Masterclass Bundle',
            'subtitle' => 'Tier 3: Libro Cartaceo + PDF + 12 Webinar Mensili Registrati',
            'description' => 'Il supporto per non mollare mai: libro a casa, PDF e accesso alle lezioni mensili di consolidamento abitudini.',
            'price' => 59.00, 'offer_tier' => 3, 'badge' => 'PREMIUM'
        ],

        // OFFERTE CROCIERA A TEMA & VIAGGI ESPERIENZIALI (Beway.life x Dependex)
        [
            'id' => 'off_cruise_smart', 'business_id' => 'biz_beway', 'product_id' => 'prd_travel_crociera',
            'offer_code' => 'CRUISE-TIER-1', 'title' => 'Crociera Rinascita · Cabina Smart Interna + Workshop',
            'subtitle' => 'Tier 1: Soggiorno Completo 8 Giorni / 7 Notti + Pass Workshop Sobrietà',
            'description' => 'Cabina doppia interna elegante, pensione completa sobria con bevande salutari e mocktail inclusi, accesso a tutti i workshop pomeridiani del Metodo Hudolin ed evoluzione personale.',
            'price' => 890.00, 'offer_tier' => 1, 'badge' => 'SMART'
        ],
        [
            'id' => 'off_cruise_smart_deposit', 'business_id' => 'biz_beway', 'product_id' => 'prd_travel_crociera',
            'offer_code' => 'CRUISE-DEP-1', 'title' => 'Caparra Prenotazione · Cabina Smart Interna',
            'subtitle' => 'Blocca il tuo posto in cabina smart con caparra confirmatoria (saldo 30 gg prima)',
            'description' => 'Acconto di prenotazione per cabina smart. Saldo restante rateizzabile o versabile fino a 30 giorni prima della partenza.',
            'price' => 190.00, 'offer_tier' => 1, 'badge' => 'CAPARRA'
        ],
        [
            'id' => 'off_cruise_comfort', 'business_id' => 'biz_beway', 'product_id' => 'prd_travel_crociera',
            'offer_code' => 'CRUISE-TIER-2', 'title' => 'Crociera Rinascita · Cabina Balcone Vista Mare + Masterclass Full Pass',
            'subtitle' => 'Tier 2: Cabina Privata con Balcone Panoramico + Tutti i Laboratori Esclusivi',
            'description' => 'Cabina con balcone sul mare, pensione completa sobria premium, sessioni di biohacking e respirazione dell\'alba, Masterclass intensiva con Mirco Pregnolato e kit libri autografato.',
            'price' => 1290.00, 'offer_tier' => 2, 'badge' => 'CONSIGLIATO'
        ],
        [
            'id' => 'off_cruise_comfort_deposit', 'business_id' => 'biz_beway', 'product_id' => 'prd_travel_crociera',
            'offer_code' => 'CRUISE-DEP-2', 'title' => 'Caparra Prenotazione · Cabina Comfort Balcone',
            'subtitle' => 'Blocca la tua cabina balcone vista mare con caparra confirmatoria',
            'description' => 'Acconto di prenotazione per cabina comfort balcone. Saldo restante rateizzabile o versabile fino a 30 giorni prima della partenza.',
            'price' => 290.00, 'offer_tier' => 2, 'badge' => 'CAPARRA'
        ],
        [
            'id' => 'off_cruise_suite_vip', 'business_id' => 'biz_beway', 'product_id' => 'prd_travel_crociera',
            'offer_code' => 'CRUISE-TIER-3', 'title' => 'Crociera Rinascita · Gran Suite VIP + Mentoring Esclusivo 1-a-1',
            'subtitle' => 'Tier 3: Esperienza VIP di Trasformazione Radicale in Gran Suite',
            'description' => 'Lussuosa Gran Suite con terrazzo privato, servizio maggiordomo 24/7, formula tutto incluso, 3 sessioni di mentoring strategico individuale 1-a-1 con Mirco Pregnolato durante la navigazione, accesso prioritario e tutti i 6 libri in omaggio.',
            'price' => 1990.00, 'offer_tier' => 3, 'badge' => 'VIP ESCLUSIVO'
        ],
        [
            'id' => 'off_cruise_suite_deposit', 'business_id' => 'biz_beway', 'product_id' => 'prd_travel_crociera',
            'offer_code' => 'CRUISE-DEP-3', 'title' => 'Caparra Prenotazione · Gran Suite VIP',
            'subtitle' => 'Blocca la Gran Suite VIP con caparra confirmatoria',
            'description' => 'Acconto per Gran Suite VIP (disponibilità limitata a soli 6 alloggi). Saldo fino a 30 giorni prima della partenza.',
            'price' => 490.00, 'offer_tier' => 3, 'badge' => 'CAPARRA'
        ],

        // Altri Viaggi Esperienziali
        [
            'id' => 'off_trv_dolomiti', 'business_id' => 'biz_beway', 'product_id' => 'prd_travel_dolomiti',
            'offer_code' => 'TRV-DOL-FULL', 'title' => 'Ritiro Forestale & Biohacking Dolomiti (3 Giorni)',
            'subtitle' => 'Soggiorno Bio-Hotel + Forest Bathing + Workshop Neuroscienze della Sobrietà',
            'description' => 'Weekend immersivo nelle valli dolomitiche. Include pernottamento, alimentazione detox a km0 e percorsi guidati.',
            'price' => 450.00, 'offer_tier' => 1, 'badge' => 'RITIRO NATURA'
        ],
        [
            'id' => 'off_trv_delta_po', 'business_id' => 'biz_beway', 'product_id' => 'prd_travel_delta_po',
            'offer_code' => 'TRV-DELTA-FULL', 'title' => 'Cammino del Delta del Po & Respiro della Natura (2 Giorni)',
            'subtitle' => 'Weekend esperienziale nel Parco del Delta del Po in sinergia con l\'evento di Taglio di Po',
            'description' => '2 giorni di cammino meditativo, escursione in barca alle foci e cerchio di ascolto al tramonto tra le lagune.',
            'price' => 190.00, 'offer_tier' => 1, 'badge' => 'CAMMINO'
        ]
    ];

    foreach ($offers as $o) {
        $chkOff = $pdo->prepare("SELECT id FROM commerce_offers WHERE id = ?");
        $chkOff->execute([$o['id']]);
        if (!$chkOff->fetch()) {
            $insOff = $pdo->prepare("
                INSERT INTO commerce_offers 
                (id, business_id, product_id, offer_code, title, subtitle, description, price, currency, offer_tier, badge, active) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'EUR', ?, ?, 1)
            ");
            $insOff->execute([
                $o['id'], $o['business_id'], $o['product_id'], $o['offer_code'],
                $o['title'], $o['subtitle'], $o['description'], $o['price'],
                $o['offer_tier'], $o['badge']
            ]);
            echo "  [+] Creata offerta: {$o['id']} ({$o['price']} EUR)\n";
        } else {
            // Aggiorna dettagli se già esistente
            $updOff = $pdo->prepare("
                UPDATE commerce_offers 
                SET title = ?, subtitle = ?, description = ?, price = ?, offer_tier = ?, badge = ?, active = 1
                WHERE id = ?
            ");
            $updOff->execute([
                $o['title'], $o['subtitle'], $o['description'], $o['price'],
                $o['offer_tier'], $o['badge'], $o['id']
            ]);
            echo "  [*] Aggiornata offerta: {$o['id']} ({$o['price']} EUR)\n";
        }
    }
}

echo "\n=== SINCRONIZZAZIONE PRODOTTI, TIER E OFFERTE COMPLETATA CON SUCCESSO! ===\n";
