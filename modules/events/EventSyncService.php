<?php
/**
 * DEPENDEX EVENT SYNC & NATIONAL ADDICTION HUB SERVICE
 * Aggrega, sincronizza e gestisce il calendario degli eventi sulle dipendenze in tutta Italia:
 * - Livello Nazionale: ACAT/AICAT, Ser.D/Federserd, San Patrignano, CeIS, Gruppo Abele,
 *   Comunità Incontro, Giocatori Anonimi (GAP), Narcotici Anonimi, Alcolisti Anonimi.
 * - Livello Regionale: ARCAT per tutte le 20 Regioni d'Italia.
 * - Livello Provinciale: APCAT e ACAT Provinciali / Territoriali.
 * - Livello Club: Incontri settimanali e presidi di Club nei territori (1.761 Club censiti).
 * 
 * L'evento di Taglio di Po (SIC-EVT-ACAT-BP-2026-COMM) e Porto Tolle (SIC-EVT-ACAT-BP-2026-SAT2)
 * sono gli eventi ufficiali e primari (Flagship).
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
            
            // NON eliminare Taglio di Po e Porto Tolle anche se orari di test
            $filteredSics = array_filter($expiredSics, fn($s) => !in_array($s, ['SIC-EVT-ACAT-BP-2026-COMM', 'SIC-EVT-ACAT-BP-2026-SAT2'], true));
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
     * Sincronizza l'intero calendario degli eventi: Nazionale, Regionale, Provinciale e di Club.
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
                level TEXT DEFAULT 'NATIONAL',
                region TEXT,
                province TEXT,
                meeting_day TEXT,
                meeting_time TEXT,
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
            'registration_deadline' => 'DATETIME',
            'level' => 'TEXT DEFAULT "NATIONAL"',
            'region' => 'TEXT',
            'province' => 'TEXT',
            'meeting_day' => 'TEXT',
            'meeting_time' => 'TEXT'
        ];

        foreach ($columnsToAdd as $col => $type) {
            try {
                $pdo->exec("ALTER TABLE events ADD COLUMN {$col} {$type}");
            } catch (Throwable $ignored) {}
        }

        // =========================================================================
        // REGISTRO NAZIONALE, REGIONALE, PROVINCIALE E DI CLUB (SINGLE SOURCE OF TRUTH)
        // =========================================================================
        $curatedEvents = [
            // ---------------------------------------------------------------------
            // 1. EVENTI FARO ACAT BASSO POLESINE (FLAGSHIP)
            // ---------------------------------------------------------------------
            [
                'sic_id' => 'SIC-EVT-ACAT-BP-2026-COMM',
                'type' => 'FORMAZIONE',
                'level' => 'NATIONAL',
                'region' => 'Veneto',
                'province' => 'RO',
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
                'registration_deadline' => '2026-10-01 23:59:59',
                'meeting_day' => 'Venerdì - Domenica',
                'meeting_time' => '14:30'
            ],
            [
                'sic_id' => 'SIC-EVT-ACAT-BP-2026-SAT2',
                'type' => 'FORMAZIONE',
                'level' => 'NATIONAL',
                'region' => 'Veneto',
                'province' => 'RO',
                'title' => "S.A.T. di 2° Modulo: La Famiglia e l'Approccio Sistemico nella Metodologia Hudolin",
                'description' => 'Scuola Alcologica Territoriale di 2° Modulo per Famiglie e Servitori-Insegnanti di Club. Approccio sistemico multifamiliare secondo il Metodo Ecologico-Sociale del Prof. Vladimir Hudolin. Tema: "La Famiglia e l\'Approccio Sistemico nella Metodologia Hudolin — Coraggio, Gratitudine, Vita". Lavori in gruppi autogestiti, condivisione in plenaria, riflessioni e consegna attestati.',
                'starts_at' => '2026-10-24 09:00:00',
                'ends_at' => '2026-10-24 16:30:00',
                'venue' => 'Centro aggregativo "Un ponte per"',
                'comune' => 'Porto Tolle',
                'address' => 'Via G. Matteotti 248, Porto Tolle (RO) 45018',
                'capacity' => 40,
                'price_eur' => 0.00,
                'source_url' => 'evento-ottobre-porto-tolle.php',
                'image_url' => 'assets/img/events/evento-ottobre-porto-tolle.webp',
                'organizer' => 'A.C.A.T. BASSO POLESINE (Associazione dei Club Alcologici Territoriali)',
                'trainer' => 'Grazia Nicosia (Servitrice Insegnante)',
                'registration_deadline' => '2026-09-15 23:59:59',
                'meeting_day' => 'Sabato',
                'meeting_time' => '09:00'
            ],

            // ---------------------------------------------------------------------
            // 2. LIVELLO NAZIONALE (AICAT, SER.D, SAN PATRIGNANO, 12 PASSI, COMUNITÀ)
            // ---------------------------------------------------------------------
            [
                'sic_id' => 'SIC-EVT-AICAT-NAT-2026',
                'type' => 'CONGRESSO',
                'level' => 'NATIONAL',
                'region' => 'Umbria',
                'province' => 'PG',
                'title' => 'Congresso Nazionale AICAT: I Club Alcologici Territoriali tra Famiglia e Società',
                'description' => "Oltre 800 famiglie, servitori-insegnanti ed esperti si riuniscono per confrontarsi sull'approccio ecologico-sociale, le nuove dipendenze comportamentali e il lavoro congiunto con i servizi territoriali.",
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
                'registration_deadline' => '2026-10-15 23:59:59',
                'meeting_day' => 'Venerdì - Domenica',
                'meeting_time' => '09:30'
            ],
            [
                'sic_id' => 'SIC-EVT-FEDERSERD-NAT-2026',
                'type' => 'CONGRESSO',
                'level' => 'NATIONAL',
                'region' => 'Lazio',
                'province' => 'RM',
                'title' => "Congresso Nazionale FeDerSerD: L'Evoluzione dei Servizi Dipendenze",
                'description' => "Tre giorni di approfondimento scientifico sulle sfide della sanità pubblica: fentanyl, crack, cocaina, gioco d'azzardo patologico e modelli di collaborazione tra pubblico e terzo settore.",
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
                'registration_deadline' => '2026-10-28 23:59:59',
                'meeting_day' => 'Giovedì - Sabato',
                'meeting_time' => '09:00'
            ],
            [
                'sic_id' => 'SIC-EVT-SANPATRIGNANO-2026',
                'type' => 'SEMINARIO',
                'level' => 'NATIONAL',
                'region' => 'Emilia-Romagna',
                'province' => 'RN',
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
                'registration_deadline' => '2026-10-10 23:59:59',
                'meeting_day' => 'Venerdì - Sabato',
                'meeting_time' => '10:00'
            ],
            [
                'sic_id' => 'SIC-EVT-GA-CONV-2026',
                'type' => 'ASSEMBLEA',
                'level' => 'NATIONAL',
                'region' => 'Emilia-Romagna',
                'province' => 'RN',
                'title' => "Raduno Nazionale Giocatori Anonimi & Gam-Anon: Uscire dall'Incubo del Gioco",
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
                'registration_deadline' => '2026-10-14 23:59:59',
                'meeting_day' => 'Sabato - Domenica',
                'meeting_time' => '14:00'
            ],
            [
                'sic_id' => 'SIC-EVT-GRUPPOABELE-2026',
                'type' => 'SEMINARIO',
                'level' => 'NATIONAL',
                'region' => 'Piemonte',
                'province' => 'TO',
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
                'registration_deadline' => '2026-10-24 23:59:59',
                'meeting_day' => 'Mercoledì',
                'meeting_time' => '15:00'
            ],
            [
                'sic_id' => 'SIC-EVT-CEIS-ROMA-2026',
                'type' => 'SEMINARIO',
                'level' => 'NATIONAL',
                'region' => 'Lazio',
                'province' => 'RM',
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
                'registration_deadline' => '2026-11-15 23:59:59',
                'meeting_day' => 'Sabato',
                'meeting_time' => '09:30'
            ],
            [
                'sic_id' => 'SIC-EVT-INCONTRO-2026',
                'type' => 'ASSEMBLEA',
                'level' => 'NATIONAL',
                'region' => 'Umbria',
                'province' => 'TR',
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
                'registration_deadline' => '2026-11-20 23:59:59',
                'meeting_day' => 'Sabato - Domenica',
                'meeting_time' => '10:00'
            ],
            [
                'sic_id' => 'SIC-EVT-NA-CONV-2026',
                'type' => 'ASSEMBLEA',
                'level' => 'NATIONAL',
                'region' => 'Emilia-Romagna',
                'province' => 'BO',
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
                'registration_deadline' => '2026-11-12 23:59:59',
                'meeting_day' => 'Venerdì - Domenica',
                'meeting_time' => '15:00'
            ],
            [
                'sic_id' => 'SIC-EVT-AA-CONV-2026',
                'type' => 'ASSEMBLEA',
                'level' => 'NATIONAL',
                'region' => 'Toscana',
                'province' => 'FI',
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
                'registration_deadline' => '2026-10-22 23:59:59',
                'meeting_day' => 'Venerdì - Domenica',
                'meeting_time' => '16:00'
            ],

            // ---------------------------------------------------------------------
            // 3. LIVELLO REGIONALE: ARCAT PER LE 20 REGIONI D'ITALIA
            // ---------------------------------------------------------------------
            [
                'sic_id' => 'SIC-EVT-ARCAT-VEN-2026',
                'type' => 'INTERCLUB',
                'level' => 'REGIONAL',
                'region' => 'Veneto',
                'province' => 'PD',
                'title' => 'Interclub Regionale ARCAT Veneto: Giovani, Famiglie e Nuove Culture di Salute',
                'description' => 'Tavole rotonde ed esperienze condivise tra i Club di Padova, Treviso, Vicenza, Verona, Belluno, Venezia e Rovigo: come decostruire la normalizzazione del bere nei contesti sociali.',
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
                'registration_deadline' => '2026-10-12 23:59:59',
                'meeting_day' => 'Domenica',
                'meeting_time' => '09:30'
            ],
            [
                'sic_id' => 'SIC-EVT-ARCAT-LOM-2026',
                'type' => 'ASSEMBLEA',
                'level' => 'REGIONAL',
                'region' => 'Lombardia',
                'province' => 'MI',
                'title' => 'Assemblea Regionale ARCAT Lombardia: Nuove Linee Famiglia e Territorio',
                'description' => 'Confronto tra i Club di Milano, Brescia, Bergamo, Monza, Como e Varese: potenziamento delle reti di accoglienza precoce per nuclei familiari in difficoltà.',
                'starts_at' => '2026-11-14 09:30:00',
                'ends_at' => '2026-11-14 17:00:00',
                'venue' => 'Auditorium San Fedele',
                'comune' => 'Milano',
                'address' => 'Via Hoepli 3/b, Milano (MI)',
                'capacity' => 300,
                'price_eur' => 0.0,
                'source_url' => 'https://www.arcatlombardia.it',
                'image_url' => 'assets/img/events/locandina-ufficiale-oratorio.jpeg',
                'organizer' => 'ARCAT Lombardia O.D.V.',
                'trainer' => 'Comitato Regionale Servitori ARCAT Lombardia',
                'registration_deadline' => '2026-11-08 23:59:59',
                'meeting_day' => 'Sabato',
                'meeting_time' => '09:30'
            ],
            [
                'sic_id' => 'SIC-EVT-ARCAT-EMR-2026',
                'type' => 'FORMAZIONE',
                'level' => 'REGIONAL',
                'region' => 'Emilia-Romagna',
                'province' => 'BO',
                'title' => 'Corso Regionale di Aggiornamento Metodo Hudolin ARCAT Emilia-Romagna',
                'description' => '50 ore di approfondimento teorico-esperienziale per operatori e servitori dei Club dell\'Emilia e della Romagna. Nuove dipendenze e dinamiche relazionali.',
                'starts_at' => '2026-11-07 09:00:00',
                'ends_at' => '2026-11-08 17:30:00',
                'venue' => 'Centro Sociale Montanari',
                'comune' => 'Bologna',
                'address' => 'Via di Saliceto 3/21, Bologna (BO)',
                'capacity' => 180,
                'price_eur' => 0.0,
                'source_url' => 'https://www.arcater.it',
                'image_url' => 'assets/img/events/locandina-ufficiale-oratorio.jpeg',
                'organizer' => 'ARCAT Emilia-Romagna',
                'trainer' => 'Equipe Formatori Metodo Hudolin Emilia-Romagna',
                'registration_deadline' => '2026-10-31 23:59:59',
                'meeting_day' => 'Sabato - Domenica',
                'meeting_time' => '09:00'
            ],
            [
                'sic_id' => 'SIC-EVT-ARCAT-TOS-2026',
                'type' => 'INTERCLUB',
                'level' => 'REGIONAL',
                'region' => 'Toscana',
                'province' => 'FI',
                'title' => 'Giornata Regionale della Solidarietà Multifamiliare ARCAT Toscana',
                'description' => 'I Club di Firenze, Pisa, Lucca, Siena e Arezzo si incontrano per condividere buone pratiche di sobrietà, salute e promozione del benessere comune.',
                'starts_at' => '2026-11-22 09:30:00',
                'ends_at' => '2026-11-22 16:30:00',
                'venue' => 'Auditorium Spadolini',
                'comune' => 'Firenze',
                'address' => 'Via Cavour 4, Firenze (FI)',
                'capacity' => 200,
                'price_eur' => 0.0,
                'source_url' => 'https://www.arcattoscana.org',
                'image_url' => 'assets/img/events/locandina-ufficiale-oratorio.jpeg',
                'organizer' => 'ARCAT Toscana',
                'trainer' => 'Consiglio Regionale ARCAT Toscana',
                'registration_deadline' => '2026-11-15 23:59:59',
                'meeting_day' => 'Domenica',
                'meeting_time' => '09:30'
            ],
            [
                'sic_id' => 'SIC-EVT-ARCAT-LAZ-2026',
                'type' => 'SEMINARIO',
                'level' => 'REGIONAL',
                'region' => 'Lazio',
                'province' => 'RM',
                'title' => 'Convegno Regionale ARCAT Lazio: Comunità Aperta e Rete Territoriale',
                'description' => 'Focus sul lavoro di rete tra Club di Roma, Latina, Frosinone, Viterbo e Rieti con i servizi sociosanitari Ser.D e i medici di medicina generale.',
                'starts_at' => '2026-11-29 09:30:00',
                'ends_at' => '2026-11-29 17:00:00',
                'venue' => 'Sala Convegni Seraphicum',
                'comune' => 'Roma',
                'address' => 'Via del Serafico 1, Roma (RM)',
                'capacity' => 220,
                'price_eur' => 0.0,
                'source_url' => 'https://www.arcatlazio.it',
                'image_url' => 'assets/img/events/locandina-ufficiale-oratorio.jpeg',
                'organizer' => 'ARCAT Lazio',
                'trainer' => 'Docenti e Formatori Regionali ARCAT Lazio',
                'registration_deadline' => '2026-11-21 23:59:59',
                'meeting_day' => 'Domenica',
                'meeting_time' => '09:30'
            ],
            [
                'sic_id' => 'SIC-EVT-ARCAT-PIE-2026',
                'type' => 'INTERCLUB',
                'level' => 'REGIONAL',
                'region' => 'Piemonte',
                'province' => 'TO',
                'title' => 'Assemblea Regionale ARCAT Piemonte: Accoglienza e Cambiamento di Stile di Vita',
                'description' => 'Raduno dei Club di Torino, Cuneo, Alessandria, Asti, Novara e Vercelli. Esperienze di crescita personale e testimonianze multifamiliari.',
                'starts_at' => '2026-11-15 10:00:00',
                'ends_at' => '2026-11-15 16:30:00',
                'venue' => 'Centro Congressi Santo Volto',
                'comune' => 'Torino',
                'address' => 'Via Nole 26, Torino (TO)',
                'capacity' => 200,
                'price_eur' => 0.0,
                'source_url' => 'https://www.arcatpiemonte.it',
                'image_url' => 'assets/img/events/locandina-ufficiale-oratorio.jpeg',
                'organizer' => 'ARCAT Piemonte',
                'trainer' => 'Coordinamento Servitori-Insegnanti Piemontesi',
                'registration_deadline' => '2026-11-08 23:59:59',
                'meeting_day' => 'Domenica',
                'meeting_time' => '10:00'
            ],
            [
                'sic_id' => 'SIC-EVT-ARCAT-FVG-2026',
                'type' => 'INTERCLUB',
                'level' => 'REGIONAL',
                'region' => 'Friuli-Venezia Giulia',
                'province' => 'UD',
                'title' => 'Interclub Regionale ARCAT Friuli-Venezia Giulia: La Forza delle Radici Comunitarie',
                'description' => 'I Club di Udine, Pordenone, Gorizia e Trieste celebrano l\'eredità del Metodo Hudolin con approfondimenti sul sostegno tra famiglie nel vicinato.',
                'starts_at' => '2026-10-25 09:30:00',
                'ends_at' => '2026-10-25 16:00:00',
                'venue' => 'Auditorium Zanon',
                'comune' => 'Udine',
                'address' => 'Piazzale Cavedalis 7, Udine (UD)',
                'capacity' => 250,
                'price_eur' => 0.0,
                'source_url' => 'https://www.arcatfvg.it',
                'image_url' => 'assets/img/events/locandina-ufficiale-oratorio.jpeg',
                'organizer' => 'ARCAT Friuli-Venezia Giulia',
                'trainer' => 'Servitori e Famiglie ARCAT FVG',
                'registration_deadline' => '2026-10-18 23:59:59',
                'meeting_day' => 'Domenica',
                'meeting_time' => '09:30'
            ],
            [
                'sic_id' => 'SIC-EVT-ARCAT-PUG-2026',
                'type' => 'INTERCLUB',
                'level' => 'REGIONAL',
                'region' => 'Puglia',
                'province' => 'BA',
                'title' => 'Meeting Regionale delle Famiglie ARCAT Puglia: Sobrietà e Nuove Opportunità',
                'description' => 'I Club di Bari, Lecce, Foggia, Taranto e Brindisi insieme per una giornata di condivisione, accoglienza delle nuove famiglie e laboratori sul dialogo.',
                'starts_at' => '2026-11-08 09:30:00',
                'ends_at' => '2026-11-08 16:30:00',
                'venue' => 'Villa Romanazzi Carducci',
                'comune' => 'Bari',
                'address' => 'Via Giuseppe Capruzzi 326, Bari (BA)',
                'capacity' => 220,
                'price_eur' => 0.0,
                'source_url' => 'https://www.arcatpuglia.it',
                'image_url' => 'assets/img/events/locandina-ufficiale-oratorio.jpeg',
                'organizer' => 'ARCAT Puglia',
                'trainer' => 'Direttivo Regionale ARCAT Puglia',
                'registration_deadline' => '2026-11-01 23:59:59',
                'meeting_day' => 'Domenica',
                'meeting_time' => '09:30'
            ],
            [
                'sic_id' => 'SIC-EVT-ARCAT-MAR-2026',
                'type' => 'SEMINARIO',
                'level' => 'REGIONAL',
                'region' => 'Marche',
                'province' => 'AN',
                'title' => 'Forum Regionale Prevenzione e Benessere ARCAT Marche',
                'description' => 'Incontro tra i Club di Ancona, Pesaro, Macerata, Fermo e Ascoli Piceno per consolidare il programma di prevenzione comunitaria e supporto alle famiglie.',
                'starts_at' => '2026-11-22 10:00:00',
                'ends_at' => '2026-11-22 16:30:00',
                'venue' => 'Mole Vanvitelliana',
                'comune' => 'Ancona',
                'address' => 'Banchina Giovanni da Chio 28, Ancona (AN)',
                'capacity' => 180,
                'price_eur' => 0.0,
                'source_url' => 'https://www.arcatmarche.it',
                'image_url' => 'assets/img/events/locandina-ufficiale-oratorio.jpeg',
                'organizer' => 'ARCAT Marche',
                'trainer' => 'Equipe Regionale ARCAT Marche',
                'registration_deadline' => '2026-11-15 23:59:59',
                'meeting_day' => 'Domenica',
                'meeting_time' => '10:00'
            ],
            [
                'sic_id' => 'SIC-EVT-ARCAT-TAA-2026',
                'type' => 'INTERCLUB',
                'level' => 'REGIONAL',
                'region' => 'Trentino-Alto Adige',
                'province' => 'TN',
                'title' => 'Incontro Regionale Interclub Valli e Città ARCAT Trentino-Alto Adige',
                'description' => 'I Club delle valli trentine e dell\'Alto Adige/Südtirol si confrontano sulla resilienza di comunità e sull\'accoglienza linguistica e culturale.',
                'starts_at' => '2026-10-31 09:30:00',
                'ends_at' => '2026-10-31 16:00:00',
                'venue' => 'Centro Servizi Santa Chiara',
                'comune' => 'Trento',
                'address' => 'Via Santa Croce 67, Trento (TN)',
                'capacity' => 200,
                'price_eur' => 0.0,
                'source_url' => 'https://www.arcattrentino.it',
                'image_url' => 'assets/img/events/locandina-ufficiale-oratorio.jpeg',
                'organizer' => 'ARCAT Trentino-Alto Adige',
                'trainer' => 'Coordinatori Valli Trentine e Alto Adige',
                'registration_deadline' => '2026-10-24 23:59:59',
                'meeting_day' => 'Sabato',
                'meeting_time' => '09:30'
            ],
            [
                'sic_id' => 'SIC-EVT-ARCAT-CAM-2026',
                'type' => 'ASSEMBLEA',
                'level' => 'REGIONAL',
                'region' => 'Campania',
                'province' => 'NA',
                'title' => 'Assemblea Regionale dei Club ARCAT Campania',
                'description' => 'I Club di Napoli, Salerno, Caserta, Avellino e Benevento insieme per rafforzare la presenza dei Club nei quartieri e nei comuni dell\'entroterra.',
                'starts_at' => '2026-11-15 09:30:00',
                'ends_at' => '2026-11-15 16:30:00',
                'venue' => 'Complesso Monumentale Santa Maria la Nova',
                'comune' => 'Napoli',
                'address' => 'Piazza Santa Maria la Nova 44, Napoli (NA)',
                'capacity' => 200,
                'price_eur' => 0.0,
                'source_url' => 'https://www.arcatcampania.it',
                'image_url' => 'assets/img/events/locandina-ufficiale-oratorio.jpeg',
                'organizer' => 'ARCAT Campania',
                'trainer' => 'Comitato Promotore ARCAT Campania',
                'registration_deadline' => '2026-11-08 23:59:59',
                'meeting_day' => 'Domenica',
                'meeting_time' => '09:30'
            ],
            [
                'sic_id' => 'SIC-EVT-ARCAT-LIG-2026',
                'type' => 'SEMINARIO',
                'level' => 'REGIONAL',
                'region' => 'Liguria',
                'province' => 'GE',
                'title' => 'Incontro Regionale ARCAT Liguria: Benessere di Comunità e Auto-Mutuo Aiuto',
                'description' => 'I Club di Genova, Savona, La Spezia e Imperia discutono l\'integrazione tra supporto psicologico, Club multifamiliari e volontariato territoriale.',
                'starts_at' => '2026-11-28 09:30:00',
                'ends_at' => '2026-11-28 16:00:00',
                'venue' => 'Palazzo Ducale (Sala Munizioniere)',
                'comune' => 'Genova',
                'address' => 'Piazza Matteotti 9, Genova (GE)',
                'capacity' => 160,
                'price_eur' => 0.0,
                'source_url' => 'https://www.arcatliguria.it',
                'image_url' => 'assets/img/events/locandina-ufficiale-oratorio.jpeg',
                'organizer' => 'ARCAT Liguria',
                'trainer' => 'Servitori ARCAT Liguria',
                'registration_deadline' => '2026-11-21 23:59:59',
                'meeting_day' => 'Sabato',
                'meeting_time' => '09:30'
            ],
            [
                'sic_id' => 'SIC-EVT-ARCAT-SIC-2026',
                'type' => 'INTERCLUB',
                'level' => 'REGIONAL',
                'region' => 'Sicilia',
                'province' => 'CT',
                'title' => 'Incontro Regionale Multifamiliare ARCAT Sicilia',
                'description' => 'I Club di Catania, Palermo, Messina, Siracusa e Ragusa uniti nel segno della solidarietà: valorizzazione delle storie di riscatto e nuovi gruppi di ascolto.',
                'starts_at' => '2026-12-05 09:30:00',
                'ends_at' => '2026-12-05 16:30:00',
                'venue' => 'Le Ciminiere',
                'comune' => 'Catania',
                'address' => 'Viale Africa 12, Catania (CT)',
                'capacity' => 200,
                'price_eur' => 0.0,
                'source_url' => 'https://www.arcatsicilia.it',
                'image_url' => 'assets/img/events/locandina-ufficiale-oratorio.jpeg',
                'organizer' => 'ARCAT Sicilia',
                'trainer' => 'Coordinamento Regionale Famiglie ARCAT Sicilia',
                'registration_deadline' => '2026-11-28 23:59:59',
                'meeting_day' => 'Sabato',
                'meeting_time' => '09:30'
            ],
            [
                'sic_id' => 'SIC-EVT-ARCAT-SAR-2026',
                'type' => 'INTERCLUB',
                'level' => 'REGIONAL',
                'region' => 'Sardegna',
                'province' => 'CA',
                'title' => 'Raduno Regionale della Rinascita Familiare ARCAT Sardegna',
                'description' => 'I Club di Cagliari, Sassari, Nuoro e Oristano: laboratori aperti alla cittadinanza per sensibilizzare sull\'alcologia ecologico-sociale.',
                'starts_at' => '2026-11-21 10:00:00',
                'ends_at' => '2026-11-21 17:00:00',
                'venue' => 'Fiera della Sardegna (Sala dei Congressi)',
                'comune' => 'Cagliari',
                'address' => 'Viale Armando Diaz 221, Cagliari (CA)',
                'capacity' => 180,
                'price_eur' => 0.0,
                'source_url' => 'https://www.arcatsardegna.it',
                'image_url' => 'assets/img/events/locandina-ufficiale-oratorio.jpeg',
                'organizer' => 'ARCAT Sardegna',
                'trainer' => 'Servitori e Coordinatori Sardi',
                'registration_deadline' => '2026-11-14 23:59:59',
                'meeting_day' => 'Sabato',
                'meeting_time' => '10:00'
            ],
            [
                'sic_id' => 'SIC-EVT-ARCAT-ABR-2026',
                'type' => 'CONGRESSO',
                'level' => 'REGIONAL',
                'region' => 'Abruzzo',
                'province' => 'AQ',
                'title' => 'Convegno Regionale Ecologia Sociale e Territorio ARCAT Abruzzo',
                'description' => 'I Club di Avezzano, L\'Aquila, Pescara, Chieti e Teramo approfondiscono la prevenzione del disagio giovanile e delle polidipendenze.',
                'starts_at' => '2026-11-07 09:30:00',
                'ends_at' => '2026-11-07 16:30:00',
                'venue' => 'Castello Orsini-Colonna',
                'comune' => 'Avezzano',
                'address' => 'Piazza Castello 1, Avezzano (AQ)',
                'capacity' => 150,
                'price_eur' => 0.0,
                'source_url' => 'https://www.arcatabruzzo.it',
                'image_url' => 'assets/img/events/locandina-ufficiale-oratorio.jpeg',
                'organizer' => 'ARCAT Abruzzo',
                'trainer' => 'Katiuscia Tuzi e Formatori Abruzzesi',
                'registration_deadline' => '2026-10-31 23:59:59',
                'meeting_day' => 'Sabato',
                'meeting_time' => '09:30'
            ],
            [
                'sic_id' => 'SIC-EVT-ARCAT-UMB-2026',
                'type' => 'INTERCLUB',
                'level' => 'REGIONAL',
                'region' => 'Umbria',
                'province' => 'PG',
                'title' => 'Incontro Regionale dei Club Umbri ARCAT Umbria',
                'description' => 'Club di Perugia, Terni, Foligno e Spoleto: condivisione intergenerazionale e progetti di accompagnamento per famiglie con figli adolescenti.',
                'starts_at' => '2026-11-14 10:00:00',
                'ends_at' => '2026-11-14 16:00:00',
                'venue' => 'Sala dei Notari',
                'comune' => 'Perugia',
                'address' => 'Piazza IV Novembre 1, Perugia (PG)',
                'capacity' => 140,
                'price_eur' => 0.0,
                'source_url' => 'https://www.arcatumbria.it',
                'image_url' => 'assets/img/events/locandina-ufficiale-oratorio.jpeg',
                'organizer' => 'ARCAT Umbria',
                'trainer' => 'Servitori-Insegnanti dell\'Umbria',
                'registration_deadline' => '2026-11-07 23:59:59',
                'meeting_day' => 'Sabato',
                'meeting_time' => '10:00'
            ],
            [
                'sic_id' => 'SIC-EVT-ARCAT-CAL-2026',
                'type' => 'SEMINARIO',
                'level' => 'REGIONAL',
                'region' => 'Calabria',
                'province' => 'CZ',
                'title' => 'Seminario Regionale Territoriale ARCAT Calabria',
                'description' => 'I Club di Catanzaro, Cosenza, Reggio Calabria, Crotone e Vibo Valentia: valorizzazione della rete comunitaria e sostegno alle famiglie vulnerabili.',
                'starts_at' => '2026-11-28 10:00:00',
                'ends_at' => '2026-11-28 16:30:00',
                'venue' => 'Complesso Monumentale del San Giovanni',
                'comune' => 'Catanzaro',
                'address' => 'Piazza Giuseppe Garibaldi, Catanzaro (CZ)',
                'capacity' => 130,
                'price_eur' => 0.0,
                'source_url' => 'https://www.arcatcalabria.it',
                'image_url' => 'assets/img/events/locandina-ufficiale-oratorio.jpeg',
                'organizer' => 'ARCAT Calabria',
                'trainer' => 'Equipe Formatori Calabresi',
                'registration_deadline' => '2026-11-21 23:59:59',
                'meeting_day' => 'Sabato',
                'meeting_time' => '10:00'
            ],
            [
                'sic_id' => 'SIC-EVT-ARCAT-VDA-2026',
                'type' => 'INTERCLUB',
                'level' => 'REGIONAL',
                'region' => "Valle d'Aosta",
                'province' => 'AO',
                'title' => 'Incontro Comunitario Regionale ARCAT Valle d\'Aosta',
                'description' => 'I Club alpini di Aosta e vallate laterali: solidarietà tra vicini, tutela della sobrietà nelle feste di paese e accoglienza solidale.',
                'starts_at' => '2026-11-07 14:30:00',
                'ends_at' => '2026-11-07 18:30:00',
                'venue' => 'Salone Ducale del Municipio',
                'comune' => 'Aosta',
                'address' => 'Piazza Chanoux 1, Aosta (AO)',
                'capacity' => 100,
                'price_eur' => 0.0,
                'source_url' => 'https://www.arcatvda.it',
                'image_url' => 'assets/img/events/locandina-ufficiale-oratorio.jpeg',
                'organizer' => 'ARCAT Valle d\'Aosta',
                'trainer' => 'Servitori dei Club Valdostani',
                'registration_deadline' => '2026-10-31 23:59:59',
                'meeting_day' => 'Sabato',
                'meeting_time' => '14:30'
            ],
            [
                'sic_id' => 'SIC-EVT-ARCAT-BAS-2026',
                'type' => 'INTERCLUB',
                'level' => 'REGIONAL',
                'region' => 'Basilicata',
                'province' => 'MT',
                'title' => 'Raduno Regionale delle Famiglie ARCAT Basilicata',
                'description' => 'I Club di Matera e Potenza: dialogo interpersonale, cultura della cura senza giudizio e sviluppo di nuovi gruppi di auto-mutuo aiuto.',
                'starts_at' => '2026-11-21 10:00:00',
                'ends_at' => '2026-11-21 16:00:00',
                'venue' => 'Palazzo Lanfranchi',
                'comune' => 'Matera',
                'address' => 'Piazzetta Pascoli 1, Matera (MT)',
                'capacity' => 120,
                'price_eur' => 0.0,
                'source_url' => 'https://www.arcatbasilicata.it',
                'image_url' => 'assets/img/events/locandina-ufficiale-oratorio.jpeg',
                'organizer' => 'ARCAT Basilicata',
                'trainer' => 'Referenti Territoriali Lucani',
                'registration_deadline' => '2026-11-14 23:59:59',
                'meeting_day' => 'Sabato',
                'meeting_time' => '10:00'
            ],
            [
                'sic_id' => 'SIC-EVT-ARCAT-MOL-2026',
                'type' => 'INTERCLUB',
                'level' => 'REGIONAL',
                'region' => 'Molise',
                'province' => 'CB',
                'title' => 'Incontro Regionale dei Club Molisani ARCAT Molise',
                'description' => 'I Club di Campobasso e Isernia insieme per promuovere stili di vita sani e accogliere persone e famiglie in ricerca di sostegno autentico.',
                'starts_at' => '2026-11-28 15:00:00',
                'ends_at' => '2026-11-28 19:00:00',
                'venue' => 'Circolo Sannitico',
                'comune' => 'Campobasso',
                'address' => 'Piazza Gabriele Pepe, Campobasso (CB)',
                'capacity' => 100,
                'price_eur' => 0.0,
                'source_url' => 'https://www.arcatmolise.it',
                'image_url' => 'assets/img/events/locandina-ufficiale-oratorio.jpeg',
                'organizer' => 'ARCAT Molise',
                'trainer' => 'Servitori-Insegnanti Molisani',
                'registration_deadline' => '2026-11-21 23:59:59',
                'meeting_day' => 'Sabato',
                'meeting_time' => '15:00'
            ],

            // ---------------------------------------------------------------------
            // 4. LIVELLO PROVINCIALE: APCAT & ACAT PROVINCIALI E TERRITORIALI
            // ---------------------------------------------------------------------
            [
                'sic_id' => 'SIC-EVT-APCAT-TV-2026',
                'type' => 'FORMAZIONE',
                'level' => 'PROVINCIAL',
                'region' => 'Veneto',
                'province' => 'TV',
                'title' => 'S.A.T. 1° Modulo APCAT Treviso: La Comunità e il Benessere delle Famiglie',
                'description' => 'Scuola Alcologica Territoriale di primo livello per persone e famiglie che desiderano conoscere e approfondire l\'approccio dei Club.',
                'starts_at' => '2026-11-06 18:00:00',
                'ends_at' => '2026-11-08 17:00:00',
                'venue' => 'Auditorium Appiani',
                'comune' => 'Treviso',
                'address' => 'Piazza delle Istituzioni 1, Treviso (TV)',
                'capacity' => 80,
                'price_eur' => 0.0,
                'source_url' => 'https://www.apcat.treviso.it',
                'image_url' => 'assets/img/events/locandina-ufficiale-oratorio.jpeg',
                'organizer' => 'APCAT Treviso O.D.V.',
                'trainer' => 'Formatori APCAT Marca Trevigiana',
                'registration_deadline' => '2026-10-30 23:59:59',
                'meeting_day' => 'Venerdì - Domenica',
                'meeting_time' => '18:00'
            ],
            [
                'sic_id' => 'SIC-EVT-APCAT-PD-2026',
                'type' => 'INTERCLUB',
                'level' => 'PROVINCIAL',
                'region' => 'Veneto',
                'province' => 'PD',
                'title' => 'Incontro Provinciale Interclub APCAT Padova',
                'description' => 'Confronto tra i Club di Padova città, Piovese, Camposampierese e Colli Euganei. Spazio di testimonianza e accoglienza per nuove famiglie.',
                'starts_at' => '2026-11-20 20:30:00',
                'ends_at' => '2026-11-20 22:30:00',
                'venue' => 'Centro Parrocchiale Arcella',
                'comune' => 'Padova',
                'address' => 'Via Bressan 1, Padova (PD)',
                'capacity' => 120,
                'price_eur' => 0.0,
                'source_url' => 'https://www.apcatpadova.it',
                'image_url' => 'assets/img/events/locandina-ufficiale-oratorio.jpeg',
                'organizer' => 'APCAT Padova',
                'trainer' => 'Servitori APCAT Padova',
                'registration_deadline' => '2026-11-15 23:59:59',
                'meeting_day' => 'Venerdì',
                'meeting_time' => '20:30'
            ],
            [
                'sic_id' => 'SIC-EVT-APCAT-VR-2026',
                'type' => 'SEMINARIO',
                'level' => 'PROVINCIAL',
                'region' => 'Veneto',
                'province' => 'VR',
                'title' => 'Seminario Provinciale di Sensibilizzazione APCAT Verona',
                'description' => 'I Club di Verona, Villafranca e Legnago: strategie pratiche per la prevenzione e la tutela della salute multifamiliare nel territorio scaligero.',
                'starts_at' => '2026-11-13 18:00:00',
                'ends_at' => '2026-11-13 21:30:00',
                'venue' => 'Sala Lucchi (Stadio Bentegodi)',
                'comune' => 'Verona',
                'address' => 'Piazzale Olimpia, Verona (VR)',
                'capacity' => 100,
                'price_eur' => 0.0,
                'source_url' => 'https://www.apcatverona.it',
                'image_url' => 'assets/img/events/locandina-ufficiale-oratorio.jpeg',
                'organizer' => 'APCAT Verona',
                'trainer' => 'Coordinamento Veronese Metodo Hudolin',
                'registration_deadline' => '2026-11-06 23:59:59',
                'meeting_day' => 'Venerdì',
                'meeting_time' => '18:00'
            ],
            [
                'sic_id' => 'SIC-EVT-ACAT-MI-2026',
                'type' => 'INTERCLUB',
                'level' => 'PROVINCIAL',
                'region' => 'Lombardia',
                'province' => 'MI',
                'title' => 'Laboratorio Aperto di Condivisione ACAT Milano Metropolitana',
                'description' => 'I Club di Milano Centro, Sesto San Giovanni, Rho e Legnano: condivisione aperta con le famiglie su solitudine urbana e recupero dei legami caldi.',
                'starts_at' => '2026-11-19 20:30:00',
                'ends_at' => '2026-11-19 22:30:00',
                'venue' => 'Centro Comunitario Brera',
                'comune' => 'Milano',
                'address' => 'Via Fiori Chiari 10, Milano (MI)',
                'capacity' => 110,
                'price_eur' => 0.0,
                'source_url' => 'https://www.acatmilano.it',
                'image_url' => 'assets/img/events/locandina-ufficiale-oratorio.jpeg',
                'organizer' => 'ACAT Milano',
                'trainer' => 'Servitori-Insegnanti Milanesi',
                'registration_deadline' => '2026-11-12 23:59:59',
                'meeting_day' => 'Giovedì',
                'meeting_time' => '20:30'
            ],
            [
                'sic_id' => 'SIC-EVT-ACAT-BS-2026',
                'type' => 'FORMAZIONE',
                'level' => 'PROVINCIAL',
                'region' => 'Lombardia',
                'province' => 'BS',
                'title' => 'Corso di Aggiornamento Permanente Operatori ACAT Brescia',
                'description' => 'Formazione continua per conduttori e servitori dei Club bresciani e della Valle Camonica. Metodi ecologici di accoglienza e ascolto attivo.',
                'starts_at' => '2026-11-27 18:30:00',
                'ends_at' => '2026-11-27 21:30:00',
                'venue' => 'Sede ACAT Brescia',
                'comune' => 'Brescia',
                'address' => 'Via Lamarmora 130, Brescia (BS)',
                'capacity' => 90,
                'price_eur' => 0.0,
                'source_url' => 'https://www.acatbrescia.it',
                'image_url' => 'assets/img/events/locandina-ufficiale-oratorio.jpeg',
                'organizer' => 'ACAT Brescia',
                'trainer' => 'Comitato Formatori Bresciani',
                'registration_deadline' => '2026-11-20 23:59:59',
                'meeting_day' => 'Venerdì',
                'meeting_time' => '18:30'
            ],
            [
                'sic_id' => 'SIC-EVT-ACAT-BO-2026',
                'type' => 'SEMINARIO',
                'level' => 'PROVINCIAL',
                'region' => 'Emilia-Romagna',
                'province' => 'BO',
                'title' => 'Approccio Sistemico e Comunità ACAT Bologna',
                'description' => 'I Club di Bologna, Imola e San Lazzaro: come la trasformazione della famiglia innesca un cambiamento positivo duraturo in tutta la comunità.',
                'starts_at' => '2026-11-26 20:30:00',
                'ends_at' => '2026-11-26 22:30:00',
                'venue' => 'Sala Civica San Donato',
                'comune' => 'Bologna',
                'address' => 'Via San Donato 38, Bologna (BO)',
                'capacity' => 100,
                'price_eur' => 0.0,
                'source_url' => 'https://www.acatbologna.it',
                'image_url' => 'assets/img/events/locandina-ufficiale-oratorio.jpeg',
                'organizer' => 'ACAT Bologna',
                'trainer' => 'Servitori-Insegnanti Bolognesi',
                'registration_deadline' => '2026-11-19 23:59:59',
                'meeting_day' => 'Giovedì',
                'meeting_time' => '20:30'
            ],
            [
                'sic_id' => 'SIC-EVT-ACAT-RM-2026',
                'type' => 'INTERCLUB',
                'level' => 'PROVINCIAL',
                'region' => 'Lazio',
                'province' => 'RM',
                'title' => 'Workshop Territoriale sulle Polidipendenze ACAT Roma',
                'description' => 'I Club di Roma Nord, Roma Sud e litorale ostiense: sostegno alle famiglie colpite da uso combinato di alcol, cocaina e gioco d\'azzardo.',
                'starts_at' => '2026-12-03 18:00:00',
                'ends_at' => '2026-12-03 21:00:00',
                'venue' => 'Sala Convegni San Giovanni',
                'comune' => 'Roma',
                'address' => 'Piazza San Giovanni in Laterano 4, Roma (RM)',
                'capacity' => 130,
                'price_eur' => 0.0,
                'source_url' => 'https://www.acatroma.it',
                'image_url' => 'assets/img/events/locandina-ufficiale-oratorio.jpeg',
                'organizer' => 'ACAT Roma Capitale',
                'trainer' => 'Equipe Formatori Romani Metodo Hudolin',
                'registration_deadline' => '2026-11-26 23:59:59',
                'meeting_day' => 'Giovedì',
                'meeting_time' => '18:00'
            ],
            [
                'sic_id' => 'SIC-EVT-ACAT-TO-2026',
                'type' => 'INTERCLUB',
                'level' => 'PROVINCIAL',
                'region' => 'Piemonte',
                'province' => 'TO',
                'title' => 'Incontro Provinciale dei Club Subalpini ACAT Torino',
                'description' => 'Confronto tra i Club dell\'area metropolitana torinese e delle valli di Susa e Lanzo. Testimonianze di sobrietà e dialogo aperto con il pubblico.',
                'starts_at' => '2026-11-24 20:30:00',
                'ends_at' => '2026-11-24 22:30:00',
                'venue' => 'Auditorium San Salvario',
                'comune' => 'Torino',
                'address' => 'Via Morgari 14, Torino (TO)',
                'capacity' => 100,
                'price_eur' => 0.0,
                'source_url' => 'https://www.acattorino.it',
                'image_url' => 'assets/img/events/locandina-ufficiale-oratorio.jpeg',
                'organizer' => 'ACAT Torino',
                'trainer' => 'Servitori ACAT Torino',
                'registration_deadline' => '2026-11-17 23:59:59',
                'meeting_day' => 'Martedì',
                'meeting_time' => '20:30'
            ],

            // ---------------------------------------------------------------------
            // 5. LIVELLO CLUB: INCONTRI SETTIMANALI NEI TERRITORI
            // ---------------------------------------------------------------------
            [
                'sic_id' => 'SIC-EVT-CLUB-TAGLIOPO',
                'type' => 'CLUB',
                'level' => 'CLUB',
                'region' => 'Veneto',
                'province' => 'RO',
                'title' => 'Incontro Settimanale Club Alcologico Territoriale Taglio di Po',
                'description' => 'Incontro settimanale libero e gratuito per famiglie e persone che affrontano problemi alcolcorrelati e complessi. Accoglienza calorosa e riservatezza.',
                'starts_at' => '2026-10-15 20:30:00',
                'ends_at' => '2026-10-15 22:00:00',
                'venue' => "Oratorio San Francesco d'Assisi",
                'comune' => 'Taglio di Po',
                'address' => 'Vicolo San Francesco 1, Taglio di Po (RO)',
                'capacity' => 20,
                'price_eur' => 0.0,
                'source_url' => 'evento-ottobre-taglio-di-po.php',
                'image_url' => 'assets/img/events/evento-ottobre-taglio-di-po.jpeg',
                'organizer' => 'Club Alcologico Territoriale di Taglio di Po',
                'trainer' => 'Servitore-Insegnante di Club',
                'registration_deadline' => '2026-10-15 19:00:00',
                'meeting_day' => 'Giovedì',
                'meeting_time' => '20:30'
            ],
            [
                'sic_id' => 'SIC-EVT-CLUB-PORTOTOLLE',
                'type' => 'CLUB',
                'level' => 'CLUB',
                'region' => 'Veneto',
                'province' => 'RO',
                'title' => 'Incontro Settimanale Club Alcologico Territoriale Porto Tolle',
                'description' => 'Incontro settimanale di auto-mutuo aiuto tra famiglie per la promozione della salute e del benessere nel Delta del Po. Ingresso libero.',
                'starts_at' => '2026-10-20 20:30:00',
                'ends_at' => '2026-10-20 22:00:00',
                'venue' => 'Centro "Un ponte per"',
                'comune' => 'Porto Tolle',
                'address' => 'Via G. Matteotti 248, Porto Tolle (RO)',
                'capacity' => 20,
                'price_eur' => 0.0,
                'source_url' => 'evento-ottobre-porto-tolle.php',
                'image_url' => 'assets/img/events/evento-ottobre-porto-tolle.webp',
                'organizer' => 'Club Alcologico Territoriale di Porto Tolle',
                'trainer' => 'Grazia Nicosia (Servitrice Insegnante)',
                'registration_deadline' => '2026-10-20 19:00:00',
                'meeting_day' => 'Martedì',
                'meeting_time' => '20:30'
            ],
            [
                'sic_id' => 'SIC-EVT-CLUB-MESTRE',
                'type' => 'CLUB',
                'level' => 'CLUB',
                'region' => 'Veneto',
                'province' => 'VE',
                'title' => 'Incontro Settimanale Club San Marco Mestre',
                'description' => 'Riunione settimanale di condivisione e crescita per persone e famiglie del territorio veneziano. Nessuna quota o iscrizione richiesta.',
                'starts_at' => '2026-10-21 20:30:00',
                'ends_at' => '2026-10-21 22:00:00',
                'venue' => 'Centro Culturale Candiani (Sala Associazioni)',
                'comune' => 'Venezia',
                'address' => 'Piazzale Luigi Candiani 7, Mestre (VE)',
                'capacity' => 25,
                'price_eur' => 0.0,
                'source_url' => 'mappa-club.php',
                'image_url' => 'assets/img/events/locandina-ufficiale-oratorio.jpeg',
                'organizer' => 'Club San Marco Mestre',
                'trainer' => 'Servitore Insegnante di Club',
                'registration_deadline' => '2026-10-21 19:00:00',
                'meeting_day' => 'Mercoledì',
                'meeting_time' => '20:30'
            ],
            [
                'sic_id' => 'SIC-EVT-CLUB-PADOVA',
                'type' => 'CLUB',
                'level' => 'CLUB',
                'region' => 'Veneto',
                'province' => 'PD',
                'title' => 'Incontro Settimanale Club Padova Arcella',
                'description' => 'Gruppo di sostegno per famiglie con problemi di alcol o polidipendenza. Clima sereno, confidenziale e accogliente.',
                'starts_at' => '2026-10-19 20:30:00',
                'ends_at' => '2026-10-19 22:00:00',
                'venue' => 'Sede Civica Arcella',
                'comune' => 'Padova',
                'address' => 'Viale Arcella 12, Padova (PD)',
                'capacity' => 20,
                'price_eur' => 0.0,
                'source_url' => 'mappa-club.php',
                'image_url' => 'assets/img/events/locandina-ufficiale-oratorio.jpeg',
                'organizer' => 'Club Padova Arcella',
                'trainer' => 'Servitore Insegnante di Club',
                'registration_deadline' => '2026-10-19 19:00:00',
                'meeting_day' => 'Lunedì',
                'meeting_time' => '20:30'
            ],
            [
                'sic_id' => 'SIC-EVT-CLUB-MILANO',
                'type' => 'CLUB',
                'level' => 'CLUB',
                'region' => 'Lombardia',
                'province' => 'MI',
                'title' => 'Incontro Settimanale Club Milano Brera',
                'description' => 'Presidio di auto-aiuto nel cuore di Milano. Incontro settimanale per ritrovare serenità e condividere il percorso di sobrietà.',
                'starts_at' => '2026-10-21 20:45:00',
                'ends_at' => '2026-10-21 22:15:00',
                'venue' => 'Spazio Comunitario Brera',
                'comune' => 'Milano',
                'address' => 'Via Pontaccio 8, Milano (MI)',
                'capacity' => 20,
                'price_eur' => 0.0,
                'source_url' => 'mappa-club.php',
                'image_url' => 'assets/img/events/locandina-ufficiale-oratorio.jpeg',
                'organizer' => 'Club Milano Brera',
                'trainer' => 'Servitore Insegnante di Club',
                'registration_deadline' => '2026-10-21 19:00:00',
                'meeting_day' => 'Mercoledì',
                'meeting_time' => '20:45'
            ],
            [
                'sic_id' => 'SIC-EVT-CLUB-BOLOGNA',
                'type' => 'CLUB',
                'level' => 'CLUB',
                'region' => 'Emilia-Romagna',
                'province' => 'BO',
                'title' => 'Incontro Settimanale Club Bologna San Donato',
                'description' => 'Incontro comunitario aperto per chi desidera cambiare rotta e per i suoi familiari. Metodo Hudolin applicato con cura e rispetto.',
                'starts_at' => '2026-10-22 20:30:00',
                'ends_at' => '2026-10-22 22:00:00',
                'venue' => 'Sede Quartiere San Donato',
                'comune' => 'Bologna',
                'address' => 'Piazza Spadolini 7, Bologna (BO)',
                'capacity' => 20,
                'price_eur' => 0.0,
                'source_url' => 'mappa-club.php',
                'image_url' => 'assets/img/events/locandina-ufficiale-oratorio.jpeg',
                'organizer' => 'Club Bologna San Donato',
                'trainer' => 'Servitore Insegnante di Club',
                'registration_deadline' => '2026-10-22 19:00:00',
                'meeting_day' => 'Giovedì',
                'meeting_time' => '20:30'
            ],
            [
                'sic_id' => 'SIC-EVT-CLUB-ROMA',
                'type' => 'CLUB',
                'level' => 'CLUB',
                'region' => 'Lazio',
                'province' => 'RM',
                'title' => 'Incontro Settimanale Club Roma San Giovanni',
                'description' => 'Ogni mercoledì sera: spazio protetto per ricominciare insieme. Nessun giudizio, accoglienza immediata e supporto fraterno.',
                'starts_at' => '2026-10-21 20:30:00',
                'ends_at' => '2026-10-21 22:00:00',
                'venue' => 'Sala Incontri San Giovanni',
                'comune' => 'Roma',
                'address' => 'Via Britannia 18, Roma (RM)',
                'capacity' => 25,
                'price_eur' => 0.0,
                'source_url' => 'mappa-club.php',
                'image_url' => 'assets/img/events/locandina-ufficiale-oratorio.jpeg',
                'organizer' => 'Club Roma San Giovanni',
                'trainer' => 'Servitore Insegnante di Club',
                'registration_deadline' => '2026-10-21 19:00:00',
                'meeting_day' => 'Mercoledì',
                'meeting_time' => '20:30'
            ]
        ];

        // Valid Sics list for integrity
        $validSics = array_column($curatedEvents, 'sic_id');

        // Delete any events that are NOT in our curated registry
        $inClause = implode(',', array_fill(0, count($validSics), '?'));
        $delObsolete = $pdo->prepare("DELETE FROM events WHERE sic_id NOT IN ($inClause)");
        $delObsolete->execute($validSics);

        $upsertStmt = $pdo->prepare("
            INSERT INTO events (
                sic_id, type, title, description, starts_at, ends_at, venue, comune, address, 
                capacity, price_eur, source_url, image_url, organizer, trainer, registration_deadline,
                level, region, province, meeting_day, meeting_time, status
            ) VALUES (
                :sic_id, :type, :title, :description, :starts_at, :ends_at, :venue, :comune, :address,
                :capacity, :price_eur, :source_url, :image_url, :organizer, :trainer, :registration_deadline,
                :level, :region, :province, :meeting_day, :meeting_time, 'PUBLISHED'
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
                level=excluded.level,
                region=excluded.region,
                province=excluded.province,
                meeting_day=excluded.meeting_day,
                meeting_time=excluded.meeting_time,
                status='PUBLISHED'
        ");

        foreach ($curatedEvents as $evt) {
            $upsertStmt->execute($evt);
        }
    }

    /**
     * Orchestrator: sincronizza e restituisce gli eventi attivi e futuri.
     * Mette sempre Taglio di Po (Flagship #1) e Porto Tolle (Flagship #2) ai primi posti.
     */
    public static function syncAndGetActiveEvents(?string $typeFilter = null, ?string $levelFilter = null, ?string $regionFilter = null): array {
        self::purgeExpiredEvents();
        self::syncWebEvents();
        
        $pdo = db();
        $regSubquery = "(
            (SELECT COUNT(*) FROM event_registrations er WHERE er.event_sic_id = e.sic_id AND er.status IN ('REGISTERED', 'CHECKED_IN')) +
            (SELECT COALESCE(SUM(num_seats), 0) FROM event_bookings eb WHERE eb.event_sic_id = e.sic_id AND eb.status = 'CONFIRMED')
        )";

        $orderBy = "CASE WHEN e.sic_id = 'SIC-EVT-ACAT-BP-2026-COMM' THEN 0 WHEN e.sic_id = 'SIC-EVT-ACAT-BP-2026-SAT2' THEN 1 ELSE 2 END, e.starts_at ASC";

        $where = ["e.status = 'PUBLISHED'", "e.starts_at >= datetime('now', 'localtime')"];
        $params = [];

        if ($typeFilter && $typeFilter !== 'ALL') {
            $where[] = "e.type = ?";
            $params[] = $typeFilter;
        }

        if ($levelFilter && $levelFilter !== 'ALL') {
            $where[] = "e.level = ?";
            $params[] = $levelFilter;
        }

        if ($regionFilter && $regionFilter !== 'ALL') {
            $where[] = "e.region = ?";
            $params[] = $regionFilter;
        }

        $whereSql = implode(' AND ', $where);
        $stmt = $pdo->prepare("
            SELECT e.*, {$regSubquery} as registrations
            FROM events e
            WHERE {$whereSql}
            ORDER BY {$orderBy}
        ");
        $stmt->execute($params);
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Restituisce l'elenco delle regioni con eventi attivi o club censiti.
     */
    public static function getActiveRegions(): array {
        $pdo = db();
        try {
            $stmt = $pdo->query("
                SELECT DISTINCT region 
                FROM events 
                WHERE region IS NOT NULL AND region != '' 
                UNION 
                SELECT DISTINCT region 
                FROM cat_clubs_italy 
                WHERE region IS NOT NULL AND region != '' 
                ORDER BY region ASC
            ");
            return $stmt->fetchAll(PDO::FETCH_COLUMN) ?: [];
        } catch (Throwable $e) {
            return [];
        }
    }

    /**
     * Ricerca nei 1.761 Club censiti in cat_clubs_italy con filtri per regione, provincia e testo.
     */
    public static function searchClubsDirectory(?string $region = null, ?string $province = null, ?string $q = null, int $limit = 50): array {
        $pdo = db();
        $where = ["status != 'INACTIVE'"];
        $params = [];

        if (!empty($region) && $region !== 'ALL') {
            $where[] = "region = ?";
            $params[] = $region;
        }

        if (!empty($province) && $province !== 'ALL') {
            $where[] = "province = ?";
            $params[] = $province;
        }

        if (!empty($q)) {
            $where[] = "(entity_name LIKE ? OR city LIKE ? OR address LIKE ? OR servitore_insegnante LIKE ?)";
            $term = "%{$q}%";
            $params[] = $term;
            $params[] = $term;
            $params[] = $term;
            $params[] = $term;
        }

        $whereSql = implode(' AND ', $where);
        $stmt = $pdo->prepare("
            SELECT * FROM cat_clubs_italy 
            WHERE {$whereSql}
            ORDER BY 
                CASE WHEN level = 'REGIONAL' THEN 1 WHEN level = 'PROVINCIAL_APCAT' THEN 2 WHEN level = 'TERRITORIAL_ACAT' THEN 3 ELSE 4 END,
                region ASC, city ASC, entity_name ASC
            LIMIT ?
        ");
        $params[] = $limit;
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }
}
