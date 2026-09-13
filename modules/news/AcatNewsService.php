<?php
/**
 * DEPENDEX — NATIONAL ADDICTION HUB & RSS TICKER SERVICE
 * Aggrega e sincronizza le notizie e gli eventi da tutta Italia:
 * ACAT/ARCAT/AICAT (Metodo Hudolin), Ser.D/Federserd, San Patrignano, CeIS, 
 * Gruppo Abele, Comunità Incontro, Alcolisti Anonimi, Narcotici Anonimi, 
 * Giocatori Anonimi (GAP), Dipendenze Comportamentali e Digitali.
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
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP
            )
        ");
        try {
            self::$db->exec("ALTER TABLE acat_news_feed ADD COLUMN is_pinned INTEGER DEFAULT 0");
        } catch (Throwable $ignored) {
            // column already exists
        }
        return self::$db;
    }

    /**
     * Sincronizza le news e gli eventi del network nazionale dipendenze
     */
    public static function syncCuratedNews(): void {
        $pdo = self::initDb();
        
        $items = [
            // PINNED 1: TAGLIO DI PO OFFICIAL EVENT
            [
                'guid' => 'taglio-di-po-ottobre-2026-official',
                'tag' => 'IN EVIDENZA',
                'tag_label' => 'EVENTO UFFICIALE · TAGLIO DI PO',
                'title' => '9-10-11 Ottobre: A Scuola di Comunicazione e Resilienza',
                'summary' => 'Tre giornate esperienziali con Adelmo Di Salvatore per famiglie, soci ACAT e operatori: imparare a non farsi travolgere dai problemi altrui. Iscrizioni aperte a 10€.',
                'source_name' => 'ACAT Basso Polesine & DEPENDEX',
                'source_url' => 'evento-ottobre-taglio-di-po.php',
                'published_date' => '09-11 Ott 2026',
                'is_pinned' => 1
            ],
            // AICAT NAZIONALE
            [
                'guid' => 'aicat-congresso-nazionale-2026',
                'tag' => 'AICAT',
                'tag_label' => 'AICAT Nazionale',
                'title' => 'Congresso Nazionale dei Club Alcologici Territoriali',
                'summary' => 'Focus sulle sfide dell\'approccio ecologico-sociale: dal disagio giovanile al gioco d\'azzardo patologico, con oltre 800 famiglie ed esperti da tutta Italia.',
                'source_name' => 'AICAT Italia',
                'source_url' => 'https://www.aicat.net',
                'published_date' => '18 Set 2026',
                'is_pinned' => 0
            ],
            // FEDERSERD & SANITÀ PUBBLICA
            [
                'guid' => 'federserd-linee-guida-2026',
                'tag' => 'SER.D & ASL',
                'tag_label' => 'Federserd · Sanità Pubblica',
                'title' => 'Nuove Linee Guida Ser.D: Integrazione Territorio e Comunità',
                'summary' => 'Protocollo d\'intesa tra servizi pubblici per le tossicodipendenze, centri di salute mentale e associazioni di auto-aiuto per la presa in carico precoce.',
                'source_name' => 'Federserd Italia',
                'source_url' => 'https://www.federserd.it',
                'published_date' => '14 Set 2026',
                'is_pinned' => 0
            ],
            // SAN PATRIGNANO
            [
                'guid' => 'san-patrignano-formazione-giovani',
                'tag' => 'COMUNITÀ',
                'tag_label' => 'San Patrignano',
                'title' => 'Laboratori di Mestiere e Ripartenza per 1.000 Ragazzi',
                'summary' => 'Presentati i dati di reinserimento lavorativo 2026: l\'autonomia professionale come pilastro insostituibile per uscire definitivamente dalla dipendenza.',
                'source_name' => 'Comunità San Patrignano',
                'source_url' => 'https://www.sanpatrignano.org',
                'published_date' => '12 Set 2026',
                'is_pinned' => 0
            ],
            // GRUPPO ABELE (DON LUIGI CIOTTI)
            [
                'guid' => 'gruppo-abele-sportello-dipendenze-digitali',
                'tag' => 'DIGITAL ADDICTION',
                'tag_label' => 'Gruppo Abele · Torino',
                'title' => 'Aperto il Presidio d\'Ascolto per Iperconnessione e Giovani',
                'summary' => 'Consulenza psicologica e gruppi per genitori su gaming compulsivo, isolamento sociale e gestione consapevole dello smartphone in famiglia.',
                'source_name' => 'Gruppo Abele Onlus',
                'source_url' => 'https://www.gruppoabele.org',
                'published_date' => '10 Set 2026',
                'is_pinned' => 0
            ],
            // CEIS (CENTRO ITALIANO DI SOLIDARIETÀ)
            [
                'guid' => 'ceis-don-picchi-famiglie-in-rete',
                'tag' => 'COMUNITÀ',
                'tag_label' => 'CeIS Don Picchi · Roma',
                'title' => 'Progetto Famiglie in Rete: Sostegno ai Genitori di Tossicodipendenti',
                'summary' => 'Ciclo di incontri quindicinali gratuiti per comprendere i segnali d\'allarme del consumo di sostanze e ristabilire il dialogo in casa.',
                'source_name' => 'CeIS Roma',
                'source_url' => 'https://www.ceis.it',
                'published_date' => '08 Set 2026',
                'is_pinned' => 0
            ],
            // COMUNITÀ INCONTRO (DON PIERINO GELMINI)
            [
                'guid' => 'comunita-incontro-molino-silla',
                'tag' => 'COMUNITÀ',
                'tag_label' => 'Comunità Incontro · Amelia',
                'title' => 'Inaugurazione Nuovo Modulo Riabilitativo Molino Silla',
                'summary' => 'Accoglienza h24 e percorsi comunitari senza farmaci sostitutivi per giovani affetti da crack, cocaina e polidipendenze.',
                'source_name' => 'Comunità Incontro',
                'source_url' => 'https://www.comunitaincontro.org',
                'published_date' => '05 Set 2026',
                'is_pinned' => 0
            ],
            // GIOCATORI ANONIMI (GAP & LUDOPATIA)
            [
                'guid' => 'giocatori-anonimi-campagna-nazionale-azzardo',
                'tag' => 'AZZARDO & GAP',
                'tag_label' => 'Giocatori Anonimi Italia',
                'title' => 'Linea Verde Nazionale Gioco d\'Azzardo: +45% Richieste di Aiuto',
                'summary' => 'Slot online, trading speculativo e scommesse sportive: la rete dei gruppi di auto-aiuto lancia la guida pratica per il blocco dei conti e la tutela legale.',
                'source_name' => 'Giocatori Anonimi',
                'source_url' => 'https://www.giocatorianonimi.org',
                'published_date' => '02 Set 2026',
                'is_pinned' => 0
            ],
            // NARCOTICI ANONIMI
            [
                'guid' => 'narcotici-anonimi-convention-nazionale',
                'tag' => 'AUTO-AIUTO',
                'tag_label' => 'Narcotici Anonimi (NA)',
                'title' => 'Convention Nazionale: "Un Giorno alla Volta per la Libertà"',
                'summary' => 'Oltre 60 gruppi locali attivi da Nord a Sud Italia. Condivisione dei 12 Passi di recupero per chiunque abbia il desiderio di smettere di usare droghe.',
                'source_name' => 'NA Italia',
                'source_url' => 'https://www.na-italia.org',
                'published_date' => '30 Ago 2026',
                'is_pinned' => 0
            ],
            // ALCOLISTI ANONIMI
            [
                'guid' => 'alcolisti-anonimi-servizio-telefonico-h24',
                'tag' => 'ALCOLISMO',
                'tag_label' => 'Alcolisti Anonimi (AA)',
                'title' => 'Help Line H24: Il Primo Passo verso la Sobrietà Condivisa',
                'summary' => 'Centinaia di riunioni settimanali aperte in tutte le province italiane. Nessuna quota o iscrizione: basta il desiderio di smettere di bere.',
                'source_name' => 'A.A. Italia',
                'source_url' => 'https://www.alcolistianonimiitalia.it',
                'published_date' => '27 Ago 2026',
                'is_pinned' => 0
            ],
            // ARCAT VENETO
            [
                'guid' => 'arcat-veneto-interclub-autunno',
                'tag' => 'ARCAT',
                'tag_label' => 'ARCAT Veneto',
                'title' => 'Interclub Regionale Veneto: Sobrietà e Nuove Culture',
                'summary' => 'Incontro dei Club di Padova, Treviso, Vicenza, Verona e Rovigo: tavole rotonde sui giovani e la promozione della salute nella comunità locale.',
                'source_name' => 'ARCAT Veneto',
                'source_url' => 'https://www.arcatveneto.it',
                'published_date' => '24 Ago 2026',
                'is_pinned' => 0
            ],
            // ARCAT LOMBARDIA
            [
                'guid' => 'arcat-lombardia-assemblea-delegati',
                'tag' => 'ARCAT',
                'tag_label' => 'ARCAT Lombardia',
                'title' => 'Assemblea Regionale dei Club di Milano, Brescia e Bergamo',
                'summary' => 'Rendicontazione delle attività territoriali e approvazione della nuova guida operativa per l\'accoglienza delle famiglie colpite da polidipendenza.',
                'source_name' => 'ARCAT Lombardia',
                'source_url' => 'https://www.arcatlombardia.it',
                'published_date' => '20 Ago 2026',
                'is_pinned' => 0
            ],
            // ARCAT EMILIA-ROMAGNA
            [
                'guid' => 'arcat-emilia-romagna-sensibilizzazione',
                'tag' => 'ARCAT',
                'tag_label' => 'ARCAT Emilia-Romagna',
                'title' => 'Corso di Formazione per Operatori di Club a Bologna',
                'summary' => '50 ore di approfondimento teorico-esperienziale sul Metodo Hudolin con rilascio attestati per la conduzione dei gruppi familiari.',
                'source_name' => 'ARCAT Emilia-Romagna',
                'source_url' => 'https://www.arcater.it',
                'published_date' => '17 Ago 2026',
                'is_pinned' => 0
            ]
        ];

        $stmt = $pdo->prepare("
            INSERT INTO acat_news_feed (guid, tag, tag_label, title, summary, source_name, source_url, published_date, is_pinned)
            VALUES (:guid, :tag, :tag_label, :title, :summary, :source_name, :source_url, :published_date, :is_pinned)
            ON CONFLICT(guid) DO UPDATE SET
                tag=excluded.tag,
                tag_label=excluded.tag_label,
                title=excluded.title,
                summary=excluded.summary,
                source_name=excluded.source_name,
                source_url=excluded.source_url,
                published_date=excluded.published_date,
                is_pinned=excluded.is_pinned
        ");

        foreach ($items as $item) {
            $stmt->execute($item);
        }
    }

    /**
     * Ritorna le card per il Ticker News (pinned in prima posizione, poi ordine ID)
     */
    public static function getLatestCards(int $limit = 14): array {
        $pdo = self::initDb();
        self::syncCuratedNews();
        $stmt = $pdo->prepare("SELECT * FROM acat_news_feed ORDER BY is_pinned DESC, id ASC LIMIT ?");
        $stmt->execute([$limit]);
        return $stmt->fetchAll();
    }
}
