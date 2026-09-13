<?php
/**
 * DEPENDEX EVENT SYNC & NATIONAL ADDICTION HUB SERVICE
 * Aggrega, sincronizza e gestisce il calendario degli eventi sulle dipendenze in tutta Italia:
 * ACAT/AICAT (Metodo Hudolin), Ser.D/Federserd, San Patrignano, CeIS, Gruppo Abele,
 * Comunità Incontro, Giocatori Anonimi (GAP), Narcotici Anonimi, Alcolisti Anonimi.
 * 
 * L'evento di Taglio di Po (SIC-EVT-ACAT-BP-2026-COMM) è l'evento ufficiale e primario (Flagship).
 */

declare(strict_types=1);

require_once __DIR__ . '/../../bootstrap.php';

class EventSyncService {
    
    /**
     * Elimina gli eventi già scaduti (starts_at < now).
     */
    public static function purgeExpiredEvents(): int {
        $pdo = db();
        
        $stmt = $pdo->prepare("
            SELECT sic_id FROM events 
            WHERE starts_at < datetime('now', 'localtime') 
               OR status = 'EXPIRED'
        ");
        $stmt->execute();
        $expiredSics = $stmt->fetchAll(PDO::FETCH_COLUMN);
        
        if (empty($expiredSics)) {
            return 0;
        }

        $pdo->beginTransaction();
        try {
            $inClause = implode(',', array_fill(0, count($expiredSics), '?'));
            
            $delReg = $pdo->prepare("DELETE FROM event_registrations WHERE event_sic_id IN ($inClause)");
            $delReg->execute($expiredSics);
            
            // NON eliminare Taglio di Po anche se orari di test
            $filteredSics = array_filter($expiredSics, fn($s) => $s !== 'SIC-EVT-ACAT-BP-2026-COMM');
            if (!empty($filteredSics)) {
                $inFiltered = implode(',', array_fill(0, count($filteredSics), '?'));
                $delEvents = $pdo->prepare("DELETE FROM events WHERE sic_id IN ($inFiltered)");
                $delEvents->execute(array_values($filteredSics));
            }
            
            $pdo->commit();
            return count($expiredSics);
        } catch (Throwable $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            return 0;
        }
    }

    /**
     * Sincronizza l'evento Flagship di Taglio di Po e gli eventi dell'Hub Nazionale Dipendenze.
     */
    public static function syncWebEvents(): void {
        $pdo = db();
        
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS events (
                sic_id TEXT PRIMARY KEY,
                type TEXT NOT NULL,
                title TEXT NOT NULL,
                description TEXT,
                starts_at DATETIME NOT NULL,
                ends_at DATETIME,
                venue TEXT,
                comune TEXT,
                address TEXT,
                visibility TEXT DEFAULT 'PUBLIC',
                rank_required TEXT DEFAULT 'SEME',
                drx_reward INTEGER DEFAULT 50,
                status TEXT DEFAULT 'PUBLISHED',
                capacity INTEGER DEFAULT 30,
                price_eur REAL DEFAULT 0.0,
                source_url TEXT,
                image_url TEXT,
                organizer TEXT,
                trainer TEXT,
                registration_deadline DATETIME,
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP
            )
        ");

        $columnsToAdd = [
            'ends_at' => 'DATETIME',
            'comune' => 'TEXT',
            'address' => 'TEXT',
            'capacity' => 'INTEGER DEFAULT 30',
            'price_eur' => 'REAL DEFAULT 0.0',
            'source_url' => 'TEXT',
            'image_url' => 'TEXT',
            'organizer' => 'TEXT',
            'trainer' => 'TEXT',
            'registration_deadline' => 'DATETIME'
        ];

        foreach ($columnsToAdd as $col => $type) {
            try {
                $pdo->exec("ALTER TABLE events ADD COLUMN {$col} {$type}");
            } catch (Throwable $ignored) {}
        }

        // =========================================================================
        // 1. EVENTO UFFICIALE FLAGSHIP: TAGLIO DI PO (9-10-11 OTTOBRE 2026)
        // =========================================================================
        $nationalEvents = [
            [
                'sic_id' => 'SIC-EVT-ACAT-BP-2026-COMM',
                'type' => 'FORMAZIONE',
                'title' => 'A Scuola di Comunicazione e Resilienza — 1° Livello',
                'description' => 'Impara a comunicare senza litigare e a non farti caricare dai problemi degli altri. Corso di formazione esperienziale rivolto a chi vive in famiglia una situazione di dipendenza, operatori, volontari e membri dei Club Alcologici Territoriali. Tre giornate con Adelmo Di Salvatore per acquisire strumenti pratici da usare già dal lunedì.',
                'starts_at' => '2026-10-09 14:30:00',
                'ends_at' => '2026-10-11 13:00:00',
                'venue' => "Oratorio San Francesco d'Assisi",
                'comune' => 'Taglio di Po',
                'address' => 'Vicolo San Francesco 1, Taglio di Po (RO)',
                'capacity' => 30,
                'price_eur' => 10.00,
                'source_url' => 'evento-ottobre-taglio-di-po.php',
                'image_url' => 'assets/img/events/evento-ottobre-taglio-di-po.jpeg',
                'organizer' => 'ACAT Basso Polesine O.D.V. & Coordinamento A.C.A.T. Polesane',
                'trainer' => 'Adelmo Di Salvatore (Psichiatra, Psicoterapeuta, Formatore Metodo Hudolin)',
                'registration_deadline' => '2026-10-01 23:59:59'
            ],
            // 2. AICAT NAZIONALE
            [
                'sic_id' => 'SIC-EVT-AICAT-NAT-2026',
                'type' => 'CONGRESSO',
                'title' => 'Congresso Nazionale AICAT: I Club Alcologici Territoriali tra Famiglia e Società',
                'description' => 'Oltre 800 famiglie, servitori-insegnanti ed esperti si riuniscono per confrontarsi sull\'approccio ecologico-sociale, le nuove dipendenze comportamentali e il lavoro congiunto con i servizi territoriali.',
                'starts_at' => '2026-10-23 09:30:00',
                'ends_at' => '2026-10-25 13:00:00',
                'venue' => 'Centro Convegni Domus Pacis',
                'comune' => 'Assisi',
                'address' => 'Piazza Porziuncola 1, Santa Maria degli Angeli (PG)',
                'capacity' => 800,
                'price_eur' => 0.0,
                'source_url' => 'https://www.aicat.net',
                'image_url' => 'assets/img/events/locandina-ufficiale-oratorio.jpeg',
                'organizer' => 'AICAT (Associazione Italiana Club Alcologici Territoriali)',
                'trainer' => 'Consiglio Direttivo Nazionale AICAT & Comitato Scientifico',
                'registration_deadline' => '2026-10-15 23:59:59'
            ],
            // 3. FEDERSERD & ASL SANITÀ PUBBLICA
            [
                'sic_id' => 'SIC-EVT-FEDERSERD-NAT-2026',
                'type' => 'CONGRESSO',
                'title' => 'Congresso Nazionale FeDerSerD: L\'Evoluzione dei Servizi Dipendenze',
                'description' => 'Tre giorni di approfondimento scientifico sulle sfide della sanità pubblica: fentanyl, crack, cocaina, gioco d\'azzardo patologico e modelli di collaborazione tra pubblico e terzo settore.',
                'starts_at' => '2026-11-05 09:00:00',
                'ends_at' => '2026-11-07 17:00:00',
                'venue' => 'Auditorium del Massimo',
                'comune' => 'Roma',
                'address' => 'Via Massimiliano Massimo 1, Roma (RM)',
                'capacity' => 500,
                'price_eur' => 0.0,
                'source_url' => 'https://www.federserd.it',
                'image_url' => 'assets/img/events/locandina-ufficiale-oratorio.jpeg',
                'organizer' => 'FeDerSerD (Federazione Italiana degli Operatori dei Dipartimenti e dei Servizi delle Dipendenze)',
                'trainer' => 'Coordinamento Scientifico Nazionale FeDerSerD',
                'registration_deadline' => '2026-10-28 23:59:59'
            ],
            // 4. SAN PATRIGNANO
            [
                'sic_id' => 'SIC-EVT-SANPATRIGNANO-2026',
                'type' => 'SEMINARIO',
                'title' => 'WeFree Days San Patrignano: Prevenzione, Lavoro e Rinascita',
                'description' => 'Il grande evento dedicato a migliaia di studenti e famiglie: laboratori teatrali, incontri con testimonial, dibattiti e storie di riscatto dalla tossicodipendenza.',
                'starts_at' => '2026-10-16 10:00:00',
                'ends_at' => '2026-10-17 18:00:00',
                'venue' => 'Comunità San Patrignano',
                'comune' => 'Coriano',
                'address' => 'Via San Patrignano 53, Coriano (RN)',
                'capacity' => 1200,
                'price_eur' => 0.0,
                'source_url' => 'https://www.sanpatrignano.org',
                'image_url' => 'assets/img/events/locandina-ufficiale-oratorio.jpeg',
                'organizer' => 'Fondazione San Patrignano',
                'trainer' => 'Equipe Educativa San Patrignano & Esperti di Prevenzione',
                'registration_deadline' => '2026-10-10 23:59:59'
            ],
            // 5. ARCAT VENETO INTERCLUB
            [
                'sic_id' => 'SIC-EVT-ARCAT-VEN-2026',
                'type' => 'INTERCLUB',
                'title' => 'Interclub Regionale ARCAT Veneto: Giovani, Famiglie e Nuove Culture di Salute',
                'description' => 'Tavole rotonde ed esperienze condivise tra i Club di Padova, Treviso, Vicenza, Verona, Belluno e Rovigo: come decostruire la normalizzazione del bere nei contesti sociali.',
                'starts_at' => '2026-10-18 09:30:00',
                'ends_at' => '2026-10-18 16:30:00',
                'venue' => 'Sala Fornace Carotta',
                'comune' => 'Padova',
                'address' => 'Piazza Pietro Carotta 1, Padova (PD)',
                'capacity' => 250,
                'price_eur' => 0.0,
                'source_url' => 'https://www.arcatveneto.it',
                'image_url' => 'assets/img/events/locandina-ufficiale-oratorio.jpeg',
                'organizer' => 'ARCAT Veneto O.D.V.',
                'trainer' => 'Coordinatori e Servitori-Insegnanti ARCAT Veneto',
                'registration_deadline' => '2026-10-12 23:59:59'
            ],
            // 6. GIOCATORI ANONIMI (GAP & LUDOPATIA)
            [
                'sic_id' => 'SIC-EVT-GA-CONV-2026',
                'type' => 'ASSEMBLEA',
                'title' => 'Raduno Nazionale Giocatori Anonimi & Gam-Anon: Uscire dall\'Incubo del Gioco',
                'description' => 'Incontro nazionale a porte aperte per giocatori compulsivi e familiari: testimonianze di libertà da debiti, isolamento e menzogne attraverso il programma dei 12 Passi.',
                'starts_at' => '2026-10-17 14:00:00',
                'ends_at' => '2026-10-18 13:00:00',
                'venue' => 'Palacongressi di Bellaria Igea Marina',
                'comune' => 'Rimini',
                'address' => 'Via Panzini 18, Bellaria Igea Marina (RN)',
                'capacity' => 400,
                'price_eur' => 0.0,
                'source_url' => 'https://www.giocatorianonimi.org',
                'image_url' => 'assets/img/events/locandina-ufficiale-oratorio.jpeg',
                'organizer' => 'Giocatori Anonimi & Gam-Anon Italia',
                'trainer' => 'Servizio Nazionale G.A. Italia',
                'registration_deadline' => '2026-10-14 23:59:59'
            ],
            // 7. GRUPPO ABELE (DIPENDENZE DIGITALI)
            [
                'sic_id' => 'SIC-EVT-GRUPPOABELE-2026',
                'type' => 'SEMINARIO',
                'title' => 'Trappole Digitali, Schermi e Giovani: Corso di Formazione per Genitori ed Educatori',
                'description' => 'Come riconoscere il confine tra uso fisiologico e dipendenza da smartphone, social media, videogiochi e dopamina veloce. Strumenti educativi per la disconnessione consapevole.',
                'starts_at' => '2026-10-28 15:00:00',
                'ends_at' => '2026-10-28 19:00:00',
                'venue' => 'Fabbrica delle E (Gruppo Abele)',
                'comune' => 'Torino',
                'address' => 'Corso Trapani 91/b, Torino (TO)',
                'capacity' => 150,
                'price_eur' => 0.0,
                'source_url' => 'https://www.gruppoabele.org',
                'image_url' => 'assets/img/events/locandina-ufficiale-oratorio.jpeg',
                'organizer' => 'Gruppo Abele Onlus',
                'trainer' => 'Equipe Dipendenze Comportamentali Gruppo Abele',
                'registration_deadline' => '2026-10-24 23:59:59'
            ],
            // 8. CEIS DON MARIO PICCHI (ROMA)
            [
                'sic_id' => 'SIC-EVT-CEIS-ROMA-2026',
                'type' => 'SEMINARIO',
                'title' => 'Seminario CeIS: Progetto Uomo e Nuovi Scenari di Trattamento delle Dipendenze',
                'description' => 'Riflessione interdisciplinare con medici, psicologi e assistenti sociali sui percorsi di accoglienza integrata tra famiglia e comunità residenziale.',
                'starts_at' => '2026-11-21 09:30:00',
                'ends_at' => '2026-11-21 17:30:00',
                'venue' => 'Sede Centrale CeIS Don Mario Picchi',
                'comune' => 'Roma',
                'address' => 'Via Appia Nuova 1251, Roma (RM)',
                'capacity' => 200,
                'price_eur' => 0.0,
                'source_url' => 'https://www.ceis.it',
                'image_url' => 'assets/img/events/locandina-ufficiale-oratorio.jpeg',
                'organizer' => 'Centro Italiano di Solidarietà Don Mario Picchi',
                'trainer' => 'Direzione Scientifica CeIS Roma',
                'registration_deadline' => '2026-11-15 23:59:59'
            ],
            // 9. COMUNITÀ INCONTRO (DON GELMINI)
            [
                'sic_id' => 'SIC-EVT-INCONTRO-2026',
                'type' => 'ASSEMBLEA',
                'title' => 'Incontro Nazionale Comunità Incontro: Accogliere per Ricostruire',
                'description' => 'Convegno e raduno annuale a Molino Silla di Amelia: focus sul contrasto a crack, eroina sintetica e farmaci oppioidi con testimonianze dirette.',
                'starts_at' => '2026-11-28 10:00:00',
                'ends_at' => '2026-11-29 16:00:00',
                'venue' => 'Centro Molino Silla',
                'comune' => 'Amelia',
                'address' => 'Località Molino Silla, Amelia (TR)',
                'capacity' => 600,
                'price_eur' => 0.0,
                'source_url' => 'https://www.comunitaincontro.org',
                'image_url' => 'assets/img/events/locandina-ufficiale-oratorio.jpeg',
                'organizer' => 'Comunità Incontro Onlus',
                'trainer' => 'Equipe Molino Silla & Testimonial del Recupero',
                'registration_deadline' => '2026-11-20 23:59:59'
            ],
            // 10. NARCOTICI ANONIMI (NA ITALIA)
            [
                'sic_id' => 'SIC-EVT-NA-CONV-2026',
                'type' => 'ASSEMBLEA',
                'title' => 'Convention Nazionale Narcotici Anonimi: Vivere Puliti Un Giorno alla Volta',
                'description' => 'Oltre 500 membri in recupero da ogni regione italiana: workshop tematici, riunioni aperte e sostegno fraterno per chiunque voglia uscire da qualsiasi sostanza.',
                'starts_at' => '2026-11-20 15:00:00',
                'ends_at' => '2026-11-22 13:00:00',
                'venue' => 'Savoia Hotel Regency',
                'comune' => 'Bologna',
                'address' => 'Via del Pilastro 2, Bologna (BO)',
                'capacity' => 500,
                'price_eur' => 0.0,
                'source_url' => 'https://www.na-italia.org',
                'image_url' => 'assets/img/events/locandina-ufficiale-oratorio.jpeg',
                'organizer' => 'Narcotici Anonimi Italia',
                'trainer' => 'Comitato di Servizio Nazionale NA',
                'registration_deadline' => '2026-11-12 23:59:59'
            ],
            // 11. ALCOLISTI ANONIMI (AA ITALIA)
            [
                'sic_id' => 'SIC-EVT-AA-CONV-2026',
                'type' => 'ASSEMBLEA',
                'title' => 'Raduno Nazionale Alcolisti Anonimi: Dalla Solitudine alla Serenità Condivisa',
                'description' => 'Assemblea annuale e riunioni di condivisione: il programma dei 12 Passi al servizio di uomini e donne per smettere di bere e mantenere una sobrietà duratura.',
                'starts_at' => '2026-10-30 16:00:00',
                'ends_at' => '2026-11-01 13:00:00',
                'venue' => 'Palazzo dei Congressi di Firenze',
                'comune' => 'Firenze',
                'address' => 'Piazza Adua 1, Firenze (FI)',
                'capacity' => 700,
                'price_eur' => 0.0,
                'source_url' => 'https://www.alcolistianonimiitalia.it',
                'image_url' => 'assets/img/events/locandina-ufficiale-oratorio.jpeg',
                'organizer' => 'Alcolisti Anonimi Italia',
                'trainer' => 'Struttura Generale di Servizio A.A. Italia',
                'registration_deadline' => '2026-10-22 23:59:59'
            ]
        ];

        // Valid Sics list for integrity
        $validSics = array_column($nationalEvents, 'sic_id');

        // Delete any events that are NOT in our curated national registry
        $inClause = implode(',', array_fill(0, count($validSics), '?'));
        $delObsolete = $pdo->prepare("DELETE FROM events WHERE sic_id NOT IN ($inClause)");
        $delObsolete->execute($validSics);

        $upsertStmt = $pdo->prepare("
            INSERT INTO events (
                sic_id, type, title, description, starts_at, ends_at, venue, comune, address, 
                capacity, price_eur, source_url, image_url, organizer, trainer, registration_deadline, status
            ) VALUES (
                :sic_id, :type, :title, :description, :starts_at, :ends_at, :venue, :comune, :address,
                :capacity, :price_eur, :source_url, :image_url, :organizer, :trainer, :registration_deadline, 'PUBLISHED'
            )
            ON CONFLICT(sic_id) DO UPDATE SET
                type=excluded.type,
                title=excluded.title,
                description=excluded.description,
                starts_at=excluded.starts_at,
                ends_at=excluded.ends_at,
                venue=excluded.venue,
                comune=excluded.comune,
                address=excluded.address,
                capacity=excluded.capacity,
                price_eur=excluded.price_eur,
                source_url=excluded.source_url,
                image_url=excluded.image_url,
                organizer=excluded.organizer,
                trainer=excluded.trainer,
                registration_deadline=excluded.registration_deadline,
                status='PUBLISHED'
        ");

        foreach ($nationalEvents as $evt) {
            $upsertStmt->execute($evt);
        }
    }

    /**
     * Orchestrator: sincronizza e restituisce gli eventi attivi e futuri.
     * Mette sempre Taglio di Po (Flagship) al primo posto.
     */
    public static function syncAndGetActiveEvents(?string $typeFilter = null): array {
        self::purgeExpiredEvents();
        self::syncWebEvents();
        
        $pdo = db();
        $regSubquery = "(
            (SELECT COUNT(*) FROM event_registrations er WHERE er.event_sic_id = e.sic_id AND er.status IN ('REGISTERED', 'CHECKED_IN')) +
            (SELECT COALESCE(SUM(num_seats), 0) FROM event_bookings eb WHERE eb.event_sic_id = e.sic_id AND eb.status = 'CONFIRMED')
        )";

        $orderBy = "CASE WHEN e.sic_id = 'SIC-EVT-ACAT-BP-2026-COMM' THEN 0 ELSE 1 END, e.starts_at ASC";

        if ($typeFilter && $typeFilter !== 'ALL') {
            $stmt = $pdo->prepare("
                SELECT e.*, {$regSubquery} as registrations
                FROM events e
                WHERE e.status = 'PUBLISHED' 
                  AND e.starts_at >= datetime('now', 'localtime')
                  AND e.type = ?
                ORDER BY {$orderBy}
            ");
            $stmt->execute([$typeFilter]);
        } else {
            $stmt = $pdo->prepare("
                SELECT e.*, {$regSubquery} as registrations
                FROM events e
                WHERE e.status = 'PUBLISHED' 
                  AND e.starts_at >= datetime('now', 'localtime')
                ORDER BY {$orderBy}
            ");
            $stmt->execute();
        }
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
