<?php
/**
 * DEPENDEX — NATIONAL ADDICTION HUB & RSS TICKER SERVICE
 * Aggrega e sincronizza le notizie e gli eventi da tutta Italia:
 * - Livello Nazionale: ACAT/ARCAT/AICAT (Metodo Hudolin), Ser.D/Federserd, San Patrignano, CeIS, 
 *   Gruppo Abele, Comunità Incontro, Alcolisti Anonimi, Narcotici Anonimi, 
 *   Giocatori Anonimi (GAP), Dipendenze Comportamentali e Digitali.
 * - Livello Regionale: ARCAT per le 20 Regioni d'Italia.
 * - Livello Provinciale: APCAT e ACAT Provinciali e Territoriali.
 * - Livello Club: Incontri settimanali di Club nei territori (1.761 Club d'Italia).
 */

declare(strict_types=1);

require_once __DIR__ . '/../../bootstrap.php';

class AcatNewsService {
    private static ?PDO $db = null;

    private static function initDb(): PDO {
        if (self::$db !== null) return self::$db;
        self::$db = db();
        self::$db->exec("
            CREATE TABLE IF NOT EXISTS acat_news_feed (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                guid TEXT UNIQUE,
                tag TEXT NOT NULL,
                tag_label TEXT NOT NULL,
                title TEXT NOT NULL,
                summary TEXT NOT NULL,
                source_name TEXT NOT NULL,
                source_url TEXT NOT NULL,
                published_date TEXT NOT NULL,
                is_pinned INTEGER DEFAULT 0,
                level TEXT DEFAULT 'NAZIONALE',
                region TEXT,
                province TEXT,
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP
            )
        ");
        $cols = [
            'is_pinned' => 'INTEGER DEFAULT 0',
            'level' => 'TEXT DEFAULT "NAZIONALE"',
            'region' => 'TEXT',
            'province' => 'TEXT'
        ];
        foreach ($cols as $col => $type) {
            try {
                self::$db->exec("ALTER TABLE acat_news_feed ADD COLUMN {$col} {$type}");
            } catch (Throwable $ignored) {}
        }
        return self::$db;
    }

    /**
     * Sincronizza le news e gli eventi del network nazionale, regionale, provinciale e di club.
     */
    public static function syncCuratedNews(): void {
        $pdo = self::initDb();
        
        $items = [
            // -------------------------------------------------------------
            // PINNED 1: TAGLIO DI PO OFFICIAL EVENT (FLAGSHIP)
            // -------------------------------------------------------------
            [
                'guid' => 'taglio-di-po-ottobre-2026-official',
                'tag' => 'IN EVIDENZA',
                'tag_label' => 'EVENTO UFFICIALE · TAGLIO DI PO',
                'title' => '9-10-11 Ottobre: A Scuola di Comunicazione e Resilienza',
                'summary' => 'Tre giornate esperienziali con Adelmo Di Salvatore per famiglie, soci ACAT e operatori: imparare a non farsi travolgere dai problemi altrui. Iscrizioni aperte a 10€.',
                'source_name' => 'ACAT Basso Polesine & DEPENDEX',
                'source_url' => 'evento-ottobre-taglio-di-po.php',
                'published_date' => '09-11 Ott 2026',
                'is_pinned' => 1,
                'level' => 'NAZIONALE',
                'region' => 'Veneto',
                'province' => 'RO'
            ],
            // -------------------------------------------------------------
            // PINNED 2: PORTO TOLLE OFFICIAL EVENT (FLAGSHIP)
            // -------------------------------------------------------------
            [
                'guid' => 'porto-tolle-ottobre-2026-sat2-official',
                'tag' => 'IN EVIDENZA',
                'tag_label' => 'EVENTO UFFICIALE · PORTO TOLLE',
                'title' => '24 Ottobre: S.A.T. di 2° Modulo — Metodologia Hudolin',
                'summary' => 'Scuola Alcologica Territoriale di Aggiornamento per Famiglie e Servitori-Insegnanti. Relatrice Grazia Nicosia. Centro "Un ponte per" a Porto Tolle. Iscrizioni Gratuite.',
                'source_name' => 'ACAT Basso Polesine & DEPENDEX',
                'source_url' => 'evento-ottobre-porto-tolle.php',
                'published_date' => '24 Ott 2026',
                'is_pinned' => 1,
                'level' => 'NAZIONALE',
                'region' => 'Veneto',
                'province' => 'RO'
            ],

            // -------------------------------------------------------------
            // LIVELLO NAZIONALE
            // -------------------------------------------------------------
            [
                'guid' => 'aicat-congresso-nazionale-2026',
                'tag' => 'AICAT',
                'tag_label' => 'AICAT Nazionale',
                'title' => 'Congresso Nazionale dei Club Alcologici Territoriali',
                'summary' => 'Focus sulle sfide dell\'approccio ecologico-sociale: dal disagio giovanile al gioco d\'azzardo patologico, con oltre 800 famiglie ed esperti da tutta Italia.',
                'source_name' => 'AICAT Italia',
                'source_url' => 'https://www.aicat.net',
                'published_date' => '18 Set 2026',
                'is_pinned' => 0,
                'level' => 'NAZIONALE',
                'region' => 'Umbria',
                'province' => 'PG'
            ],
            [
                'guid' => 'federserd-linee-guida-2026',
                'tag' => 'SER.D & ASL',
                'tag_label' => 'Federserd · Sanità Pubblica',
                'title' => 'Nuove Linee Guida Ser.D: Integrazione Territorio e Comunità',
                'summary' => 'Protocollo d\'intesa tra servizi pubblici per le tossicodipendenze, centri di salute mentale e associazioni di auto-aiuto per la presa in carico precoce.',
                'source_name' => 'Federserd Italia',
                'source_url' => 'https://www.federserd.it',
                'published_date' => '14 Set 2026',
                'is_pinned' => 0,
                'level' => 'NAZIONALE',
                'region' => 'Lazio',
                'province' => 'RM'
            ],
            [
                'guid' => 'san-patrignano-formazione-giovani',
                'tag' => 'COMUNITÀ',
                'tag_label' => 'San Patrignano',
                'title' => 'Laboratori di Mestiere e Ripartenza per 1.000 Ragazzi',
                'summary' => 'Presentati i dati di reinserimento lavorativo 2026: l\'autonomia professionale come pilastro insostituibile per uscire definitivamente dalla dipendenza.',
                'source_name' => 'Comunità San Patrignano',
                'source_url' => 'https://www.sanpatrignano.org',
                'published_date' => '12 Set 2026',
                'is_pinned' => 0,
                'level' => 'NAZIONALE',
                'region' => 'Emilia-Romagna',
                'province' => 'RN'
            ],
            [
                'guid' => 'gruppo-abele-sportello-dipendenze-digitali',
                'tag' => 'DIGITAL ADDICTION',
                'tag_label' => 'Gruppo Abele · Torino',
                'title' => 'Aperto il Presidio d\'Ascolto per Iperconnessione e Giovani',
                'summary' => 'Consulenza psicologica e gruppi per genitori su gaming compulsivo, isolamento sociale e gestione consapevole dello smartphone in famiglia.',
                'source_name' => 'Gruppo Abele Onlus',
                'source_url' => 'https://www.gruppoabele.org',
                'published_date' => '10 Set 2026',
                'is_pinned' => 0,
                'level' => 'NAZIONALE',
                'region' => 'Piemonte',
                'province' => 'TO'
            ],
            [
                'guid' => 'ceis-don-picchi-famiglie-in-rete',
                'tag' => 'COMUNITÀ',
                'tag_label' => 'CeIS Don Picchi · Roma',
                'title' => 'Progetto Famiglie in Rete: Sostegno ai Genitori',
                'summary' => 'Ciclo di incontri quindicinali gratuiti per comprendere i segnali d\'allarme del consumo di sostanze e ristabilire il dialogo in casa.',
                'source_name' => 'CeIS Roma',
                'source_url' => 'https://www.ceis.it',
                'published_date' => '08 Set 2026',
                'is_pinned' => 0,
                'level' => 'NAZIONALE',
                'region' => 'Lazio',
                'province' => 'RM'
            ],
            [
                'guid' => 'comunita-incontro-molino-silla',
                'tag' => 'COMUNITÀ',
                'tag_label' => 'Comunità Incontro · Amelia',
                'title' => 'Inaugurazione Nuovo Modulo Riabilitativo Molino Silla',
                'summary' => 'Accoglienza h24 e percorsi comunitari senza farmaci sostitutivi per giovani affetti da crack, cocaina e polidipendenze.',
                'source_name' => 'Comunità Incontro',
                'source_url' => 'https://www.comunitaincontro.org',
                'published_date' => '05 Set 2026',
                'is_pinned' => 0,
                'level' => 'NAZIONALE',
                'region' => 'Umbria',
                'province' => 'TR'
            ],
            [
                'guid' => 'giocatori-anonimi-campagna-nazionale-azzardo',
                'tag' => 'AZZARDO & GAP',
                'tag_label' => 'Giocatori Anonimi Italia',
                'title' => 'Linea Verde Nazionale Gioco d\'Azzardo: Richieste di Aiuto',
                'summary' => 'Slot online, trading speculativo e scommesse sportive: la rete dei gruppi di auto-aiuto lancia la guida pratica per il blocco dei conti e la tutela legale.',
                'source_name' => 'Giocatori Anonimi',
                'source_url' => 'https://www.giocatorianonimi.org',
                'published_date' => '02 Set 2026',
                'is_pinned' => 0,
                'level' => 'NAZIONALE',
                'region' => 'Emilia-Romagna',
                'province' => 'RN'
            ],
            [
                'guid' => 'narcotici-anonimi-convention-nazionale',
                'tag' => 'AUTO-AIUTO',
                'tag_label' => 'Narcotici Anonimi (NA)',
                'title' => 'Convention Nazionale: "Un Giorno alla Volta per la Libertà"',
                'summary' => 'Oltre 60 gruppi locali attivi da Nord a Sud Italia. Condivisione dei 12 Passi di recupero per chiunque abbia il desiderio di smettere di usare droghe.',
                'source_name' => 'NA Italia',
                'source_url' => 'https://www.na-italia.org',
                'published_date' => '30 Ago 2026',
                'is_pinned' => 0,
                'level' => 'NAZIONALE',
                'region' => 'Emilia-Romagna',
                'province' => 'BO'
            ],
            [
                'guid' => 'alcolisti-anonimi-servizio-telefonico-h24',
                'tag' => 'ALCOLISMO',
                'tag_label' => 'Alcolisti Anonimi (AA)',
                'title' => 'Help Line H24: Il Primo Passo verso la Sobrietà Condivisa',
                'summary' => 'Centinaia di riunioni settimanali aperte in tutte le province italiane. Nessuna quota o iscrizione: basta il desiderio di smettere di bere.',
                'source_name' => 'A.A. Italia',
                'source_url' => 'https://www.alcolistianonimiitalia.it',
                'published_date' => '27 Ago 2026',
                'is_pinned' => 0,
                'level' => 'NAZIONALE',
                'region' => 'Toscana',
                'province' => 'FI'
            ],

            // -------------------------------------------------------------
            // LIVELLO REGIONALE (ARCAT)
            // -------------------------------------------------------------
            [
                'guid' => 'arcat-veneto-interclub-autunno',
                'tag' => 'REGIONALE',
                'tag_label' => 'ARCAT Veneto',
                'title' => 'Interclub Regionale Veneto: Sobrietà e Nuove Culture',
                'summary' => 'Incontro dei Club di Padova, Treviso, Vicenza, Verona, Belluno, Venezia e Rovigo: tavole rotonde sui giovani e la promozione della salute nella comunità locale.',
                'source_name' => 'ARCAT Veneto',
                'source_url' => 'events-public.php#regionale',
                'published_date' => '18 Ott 2026',
                'is_pinned' => 0,
                'level' => 'REGIONALE',
                'region' => 'Veneto',
                'province' => 'PD'
            ],
            [
                'guid' => 'arcat-lombardia-assemblea-delegati',
                'tag' => 'REGIONALE',
                'tag_label' => 'ARCAT Lombardia',
                'title' => 'Assemblea Regionale dei Club di Milano, Brescia e Bergamo',
                'summary' => 'Rendicontazione delle attività territoriali e approvazione della nuova guida operativa per l\'accoglienza delle famiglie colpite da polidipendenza.',
                'source_name' => 'ARCAT Lombardia',
                'source_url' => 'events-public.php#regionale',
                'published_date' => '14 Nov 2026',
                'is_pinned' => 0,
                'level' => 'REGIONALE',
                'region' => 'Lombardia',
                'province' => 'MI'
            ],
            [
                'guid' => 'arcat-emilia-romagna-sensibilizzazione',
                'tag' => 'REGIONALE',
                'tag_label' => 'ARCAT Emilia-Romagna',
                'title' => 'Corso di Formazione per Operatori di Club a Bologna',
                'summary' => '50 ore di approfondimento teorico-esperienziale sul Metodo Hudolin con rilascio attestati per la conduzione dei gruppi familiari.',
                'source_name' => 'ARCAT Emilia-Romagna',
                'source_url' => 'events-public.php#regionale',
                'published_date' => '07 Nov 2026',
                'is_pinned' => 0,
                'level' => 'REGIONALE',
                'region' => 'Emilia-Romagna',
                'province' => 'BO'
            ],
            [
                'guid' => 'arcat-toscana-solidarieta',
                'tag' => 'REGIONALE',
                'tag_label' => 'ARCAT Toscana',
                'title' => 'Giornata Regionale della Solidarietà Multifamiliare',
                'summary' => 'I Club di Firenze, Pisa, Lucca, Siena e Arezzo si incontrano all\'Auditorium Spadolini per diffondere la cultura della sobrietà.',
                'source_name' => 'ARCAT Toscana',
                'source_url' => 'events-public.php#regionale',
                'published_date' => '22 Nov 2026',
                'is_pinned' => 0,
                'level' => 'REGIONALE',
                'region' => 'Toscana',
                'province' => 'FI'
            ],
            [
                'guid' => 'arcat-lazio-comunita',
                'tag' => 'REGIONALE',
                'tag_label' => 'ARCAT Lazio',
                'title' => 'Convegno Regionale: Comunità Aperta e Rete Territoriale',
                'summary' => 'Integrazione operativa tra i Club di Roma, Latina, Frosinone e Viterbo con i dipartimenti per le dipendenze delle ASL.',
                'source_name' => 'ARCAT Lazio',
                'source_url' => 'events-public.php#regionale',
                'published_date' => '29 Nov 2026',
                'is_pinned' => 0,
                'level' => 'REGIONALE',
                'region' => 'Lazio',
                'province' => 'RM'
            ],
            [
                'guid' => 'arcat-piemonte-accoglienza',
                'tag' => 'REGIONALE',
                'tag_label' => 'ARCAT Piemonte',
                'title' => 'Raduno Regionale dei Club del Piemonte a Torino',
                'summary' => 'Accoglienza e cambiamento dello stile di vita: confronto tra le famiglie di Torino, Cuneo, Novara e Vercelli al Santo Volto.',
                'source_name' => 'ARCAT Piemonte',
                'source_url' => 'events-public.php#regionale',
                'published_date' => '15 Nov 2026',
                'is_pinned' => 0,
                'level' => 'REGIONALE',
                'region' => 'Piemonte',
                'province' => 'TO'
            ],
            [
                'guid' => 'arcat-fvg-interclub',
                'tag' => 'REGIONALE',
                'tag_label' => 'ARCAT Friuli-VG',
                'title' => 'Interclub Regionale a Udine: La Forza delle Relazioni',
                'summary' => 'I Club di Udine, Pordenone, Gorizia e Trieste riuniti all\'Auditorium Zanon per valorizzare l\'approccio ecologico-sociale.',
                'source_name' => 'ARCAT FVG',
                'source_url' => 'events-public.php#regionale',
                'published_date' => '25 Ott 2026',
                'is_pinned' => 0,
                'level' => 'REGIONALE',
                'region' => 'Friuli-Venezia Giulia',
                'province' => 'UD'
            ],
            [
                'guid' => 'arcat-puglia-meeting',
                'tag' => 'REGIONALE',
                'tag_label' => 'ARCAT Puglia',
                'title' => 'Meeting Regionale delle Famiglie dei Club a Bari',
                'summary' => 'Tavole rotonde ed esperienze condivise tra i Club di Bari, Foggia, Lecce e Taranto presso Villa Romanazzi Carducci.',
                'source_name' => 'ARCAT Puglia',
                'source_url' => 'events-public.php#regionale',
                'published_date' => '08 Nov 2026',
                'is_pinned' => 0,
                'level' => 'REGIONALE',
                'region' => 'Puglia',
                'province' => 'BA'
            ],
            [
                'guid' => 'arcat-campania-assemblea',
                'tag' => 'REGIONALE',
                'tag_label' => 'ARCAT Campania',
                'title' => 'Assemblea Regionale dei Club a Napoli: Presidi di Salute',
                'summary' => 'Rafforzamento dei Club nei quartieri e nei comuni campani per offrire un approdo sicuro e gratuito a tutte le famiglie.',
                'source_name' => 'ARCAT Campania',
                'source_url' => 'events-public.php#regionale',
                'published_date' => '15 Nov 2026',
                'is_pinned' => 0,
                'level' => 'REGIONALE',
                'region' => 'Campania',
                'province' => 'NA'
            ],
            [
                'guid' => 'arcat-sicilia-multifamiliare',
                'tag' => 'REGIONALE',
                'tag_label' => 'ARCAT Sicilia',
                'title' => 'Incontro Regionale Multifamiliare alle Ciminiere di Catania',
                'summary' => 'I Club di Catania, Palermo, Messina e Siracusa uniti per promuovere la salute di comunità e la solidarietà tra famiglie.',
                'source_name' => 'ARCAT Sicilia',
                'source_url' => 'events-public.php#regionale',
                'published_date' => '05 Dic 2026',
                'is_pinned' => 0,
                'level' => 'REGIONALE',
                'region' => 'Sicilia',
                'province' => 'CT'
            ],

            // -------------------------------------------------------------
            // LIVELLO PROVINCIALE (APCAT & ACAT)
            // -------------------------------------------------------------
            [
                'guid' => 'apcat-treviso-sat1',
                'tag' => 'PROVINCIALE',
                'tag_label' => 'APCAT Treviso',
                'title' => 'S.A.T. 1° Modulo Marca Trevigiana all\'Auditorium Appiani',
                'summary' => 'Scuola Alcologica Territoriale di primo livello per famiglie e nuovi servitori. Approccio ecologico e ascolto empatico.',
                'source_name' => 'APCAT Treviso',
                'source_url' => 'events-public.php#provinciale',
                'published_date' => '06 Nov 2026',
                'is_pinned' => 0,
                'level' => 'PROVINCIALE',
                'region' => 'Veneto',
                'province' => 'TV'
            ],
            [
                'guid' => 'apcat-padova-interclub',
                'tag' => 'PROVINCIALE',
                'tag_label' => 'APCAT Padova',
                'title' => 'Incontro Provinciale Interclub all\'Arcella di Padova',
                'summary' => 'I Club padovani si confrontano su inclusione giovanile e accoglienza solidale. Porte aperte a tutta la cittadinanza.',
                'source_name' => 'APCAT Padova',
                'source_url' => 'events-public.php#provinciale',
                'published_date' => '20 Nov 2026',
                'is_pinned' => 0,
                'level' => 'PROVINCIALE',
                'region' => 'Veneto',
                'province' => 'PD'
            ],
            [
                'guid' => 'apcat-verona-sensibilizzazione',
                'tag' => 'PROVINCIALE',
                'tag_label' => 'APCAT Verona',
                'title' => 'Seminario di Sensibilizzazione alla Sala Lucchi di Verona',
                'summary' => 'Prevenzione e tutela della salute familiare nel territorio scaligero: incontro aperto con i conduttori dei Club.',
                'source_name' => 'APCAT Verona',
                'source_url' => 'events-public.php#provinciale',
                'published_date' => '13 Nov 2026',
                'is_pinned' => 0,
                'level' => 'PROVINCIALE',
                'region' => 'Veneto',
                'province' => 'VR'
            ],
            [
                'guid' => 'acat-milano-laboratorio',
                'tag' => 'PROVINCIALE',
                'tag_label' => 'ACAT Milano',
                'title' => 'Laboratorio Aperto di Condivisione Metropolitana a Brera',
                'summary' => 'I Club di Milano Centro, Sesto e Rho affrontano il tema della solitudine urbana e dei legami di sostegno caldi.',
                'source_name' => 'ACAT Milano',
                'source_url' => 'events-public.php#provinciale',
                'published_date' => '19 Nov 2026',
                'is_pinned' => 0,
                'level' => 'PROVINCIALE',
                'region' => 'Lombardia',
                'province' => 'MI'
            ],
            [
                'guid' => 'acat-brescia-aggiornamento',
                'tag' => 'PROVINCIALE',
                'tag_label' => 'ACAT Brescia',
                'title' => 'Corso d\'Aggiornamento Permanente Operatori in Via Lamarmora',
                'summary' => 'Formazione continua per servitori e conduttori dei Club bresciani e della Valle Camonica. Metodi ecologici di cura.',
                'source_name' => 'ACAT Brescia',
                'source_url' => 'events-public.php#provinciale',
                'published_date' => '27 Nov 2026',
                'is_pinned' => 0,
                'level' => 'PROVINCIALE',
                'region' => 'Lombardia',
                'province' => 'BS'
            ],
            [
                'guid' => 'acat-bologna-seminario',
                'tag' => 'PROVINCIALE',
                'tag_label' => 'ACAT Bologna',
                'title' => 'Seminario sull\'Approccio Sistemico a San Donato',
                'summary' => 'I Club bolognesi presentano le evidenze del cambiamento multifamiliare duraturo nel contrasto all\'isolamento.',
                'source_name' => 'ACAT Bologna',
                'source_url' => 'events-public.php#provinciale',
                'published_date' => '26 Nov 2026',
                'is_pinned' => 0,
                'level' => 'PROVINCIALE',
                'region' => 'Emilia-Romagna',
                'province' => 'BO'
            ],
            [
                'guid' => 'acat-roma-workshop',
                'tag' => 'PROVINCIALE',
                'tag_label' => 'ACAT Roma',
                'title' => 'Workshop Territoriale sulle Polidipendenze a San Giovanni',
                'summary' => 'Focus sul sostegno alle famiglie colpite da uso combinato di alcol e dipendenze comportamentali nella Capitale.',
                'source_name' => 'ACAT Roma',
                'source_url' => 'events-public.php#provinciale',
                'published_date' => '03 Dic 2026',
                'is_pinned' => 0,
                'level' => 'PROVINCIALE',
                'region' => 'Lazio',
                'province' => 'RM'
            ],
            [
                'guid' => 'acat-torino-subalpini',
                'tag' => 'PROVINCIALE',
                'tag_label' => 'ACAT Torino',
                'title' => 'Incontro Provinciale dei Club Subalpini a San Salvario',
                'summary' => 'Condivisione aperta tra i Club dell\'area torinese e delle valli alpine su sobrietà e benessere di vicinato.',
                'source_name' => 'ACAT Torino',
                'source_url' => 'events-public.php#provinciale',
                'published_date' => '24 Nov 2026',
                'is_pinned' => 0,
                'level' => 'PROVINCIALE',
                'region' => 'Piemonte',
                'province' => 'TO'
            ],

            // -------------------------------------------------------------
            // LIVELLO CLUB: INCONTRI SETTIMANALI NEI TERRITORI
            // -------------------------------------------------------------
            [
                'guid' => 'club-taglio-di-po-settimanale',
                'tag' => 'CLUB',
                'tag_label' => 'Club · Taglio di Po (RO)',
                'title' => 'Incontro Settimanale Giovedì ore 20:30 all\'Oratorio San Francesco',
                'summary' => 'Accoglienza libera e gratuita per famiglie e persone che affrontano problemi alcolcorrelati. Spazio confidenziale e protetto.',
                'source_name' => 'Club Taglio di Po',
                'source_url' => 'evento-ottobre-taglio-di-po.php',
                'published_date' => 'Ogni Giovedì',
                'is_pinned' => 0,
                'level' => 'CLUB',
                'region' => 'Veneto',
                'province' => 'RO'
            ],
            [
                'guid' => 'club-porto-tolle-settimanale',
                'tag' => 'CLUB',
                'tag_label' => 'Club · Porto Tolle (RO)',
                'title' => 'Incontro Settimanale Martedì ore 20:30 al Centro "Un ponte per"',
                'summary' => 'Auto-mutuo aiuto multifamiliare nel Delta del Po guidato da Grazia Nicosia. Sostegno caloroso per rimettere in cammino la vita.',
                'source_name' => 'Club Porto Tolle',
                'source_url' => 'evento-ottobre-porto-tolle.php',
                'published_date' => 'Ogni Martedì',
                'is_pinned' => 0,
                'level' => 'CLUB',
                'region' => 'Veneto',
                'province' => 'RO'
            ],
            [
                'guid' => 'club-mestre-settimanale',
                'tag' => 'CLUB',
                'tag_label' => 'Club · Mestre San Marco (VE)',
                'title' => 'Incontro Settimanale Mercoledì ore 20:30 al Candiani',
                'summary' => 'Riunione settimanale aperta a tutti per condividere passi di serenità e libertà da alcol e farmaci nella terraferma veneziana.',
                'source_name' => 'Club San Marco Mestre',
                'source_url' => 'events-public.php#club',
                'published_date' => 'Ogni Mercoledì',
                'is_pinned' => 0,
                'level' => 'CLUB',
                'region' => 'Veneto',
                'province' => 'VE'
            ],
            [
                'guid' => 'club-padova-arcella-settimanale',
                'tag' => 'CLUB',
                'tag_label' => 'Club · Padova Arcella (PD)',
                'title' => 'Incontro Settimanale Lunedì ore 20:30 in Sede Civica',
                'summary' => 'Gruppo di sostegno per famiglie con problemi di dipendenza. Clima familiare sereno e totale riservatezza.',
                'source_name' => 'Club Padova Arcella',
                'source_url' => 'events-public.php#club',
                'published_date' => 'Ogni Lunedì',
                'is_pinned' => 0,
                'level' => 'CLUB',
                'region' => 'Veneto',
                'province' => 'PD'
            ],
            [
                'guid' => 'club-milano-brera-settimanale',
                'tag' => 'CLUB',
                'tag_label' => 'Club · Milano Brera (MI)',
                'title' => 'Incontro Settimanale Mercoledì ore 20:45 in Via Pontaccio',
                'summary' => 'Presidio di sobrietà e dialogo nel centro di Milano. Non sei solo: ogni settimana le famiglie si ritrovano per crescere insieme.',
                'source_name' => 'Club Milano Brera',
                'source_url' => 'events-public.php#club',
                'published_date' => 'Ogni Mercoledì',
                'is_pinned' => 0,
                'level' => 'CLUB',
                'region' => 'Lombardia',
                'province' => 'MI'
            ],
            [
                'guid' => 'club-bologna-sandonato-settimanale',
                'tag' => 'CLUB',
                'tag_label' => 'Club · Bologna San Donato (BO)',
                'title' => 'Incontro Settimanale Giovedì ore 20:30 in Piazza Spadolini',
                'summary' => 'Accoglienza fraterna e confronto positivo per chi cerca un punto fermo a Bologna. Metodo Hudolin gratuito e aperto.',
                'source_name' => 'Club Bologna San Donato',
                'source_url' => 'events-public.php#club',
                'published_date' => 'Ogni Giovedì',
                'is_pinned' => 0,
                'level' => 'CLUB',
                'region' => 'Emilia-Romagna',
                'province' => 'BO'
            ],
            [
                'guid' => 'club-roma-sangiovanni-settimanale',
                'tag' => 'CLUB',
                'tag_label' => 'Club · Roma San Giovanni (RM)',
                'title' => 'Incontro Settimanale Mercoledì ore 20:30 in Via Britannia',
                'summary' => 'Un ambiente protetto per ritrovare equilibrio e serenità familiare a Roma. Partecipazione gratuita e continuativa.',
                'source_name' => 'Club Roma San Giovanni',
                'source_url' => 'events-public.php#club',
                'published_date' => 'Ogni Mercoledì',
                'is_pinned' => 0,
                'level' => 'CLUB',
                'region' => 'Lazio',
                'province' => 'RM'
            ],
            [
                'guid' => 'club-torino-sansalvario-settimanale',
                'tag' => 'CLUB',
                'tag_label' => 'Club · Torino San Salvario (TO)',
                'title' => 'Incontro Settimanale Giovedì ore 20:30 in Via Morgari',
                'summary' => 'Gruppo di auto-aiuto nel cuore di Torino. Esperienze vere di persone e famiglie che hanno scelto un nuovo stile di vita.',
                'source_name' => 'Club Torino San Salvario',
                'source_url' => 'events-public.php#club',
                'published_date' => 'Ogni Giovedì',
                'is_pinned' => 0,
                'level' => 'CLUB',
                'region' => 'Piemonte',
                'province' => 'TO'
            ],
            [
                'guid' => 'club-napoli-vomero-settimanale',
                'tag' => 'CLUB',
                'tag_label' => 'Club · Napoli Vomero (NA)',
                'title' => 'Incontro Settimanale Lunedì ore 19:30 al Vomero',
                'summary' => 'Spazio accogliente per famiglie e giovani a Napoli. Condivisione democratica e accompagnamento verso una vita piena.',
                'source_name' => 'Club Napoli Vomero',
                'source_url' => 'events-public.php#club',
                'published_date' => 'Ogni Lunedì',
                'is_pinned' => 0,
                'level' => 'CLUB',
                'region' => 'Campania',
                'province' => 'NA'
            ],
            [
                'guid' => 'club-bari-poggiofranco-settimanale',
                'tag' => 'CLUB',
                'tag_label' => 'Club · Bari Poggiofranco (BA)',
                'title' => 'Incontro Settimanale Martedì ore 19:30 a Bari',
                'summary' => 'Le famiglie dei Club baresi accolgono chiunque senta il bisogno di fermarsi e ripartire senza alcol e sostanze.',
                'source_name' => 'Club Bari Poggiofranco',
                'source_url' => 'events-public.php#club',
                'published_date' => 'Ogni Martedì',
                'is_pinned' => 0,
                'level' => 'CLUB',
                'region' => 'Puglia',
                'province' => 'BA'
            ]
        ];

        $stmt = $pdo->prepare("
            INSERT INTO acat_news_feed (guid, tag, tag_label, title, summary, source_name, source_url, published_date, is_pinned, level, region, province)
            VALUES (:guid, :tag, :tag_label, :title, :summary, :source_name, :source_url, :published_date, :is_pinned, :level, :region, :province)
            ON CONFLICT(guid) DO UPDATE SET
                tag=excluded.tag,
                tag_label=excluded.tag_label,
                title=excluded.title,
                summary=excluded.summary,
                source_name=excluded.source_name,
                source_url=excluded.source_url,
                published_date=excluded.published_date,
                is_pinned=excluded.is_pinned,
                level=excluded.level,
                region=excluded.region,
                province=excluded.province
        ");

        foreach ($items as $item) {
            $stmt->execute($item);
        }
    }

    /**
     * Ritorna le card per il Ticker News (pinned in prima posizione, poi ordine ID)
     */
    public static function getLatestCards(int $limit = 48, ?string $levelFilter = null): array {
        $pdo = self::initDb();
        self::syncCuratedNews();
        
        $where = [];
        $params = [];
        if (!empty($levelFilter) && $levelFilter !== 'ALL') {
            $where[] = "level = ?";
            $params[] = $levelFilter;
        }
        
        $whereSql = !empty($where) ? "WHERE " . implode(' AND ', $where) : "";
        $stmt = $pdo->prepare("
            SELECT * FROM acat_news_feed 
            {$whereSql}
            ORDER BY is_pinned DESC, 
                     CASE WHEN guid = 'taglio-di-po-ottobre-2026-official' THEN 1 
                          WHEN guid = 'porto-tolle-ottobre-2026-sat2-official' THEN 2 
                          ELSE 3 END, 
                     id ASC 
            LIMIT ?
        ");
        $params[] = $limit;
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
