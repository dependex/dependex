<?php
/**
 * DEPENDEX — BOOKS CATALOG SERVICE
 * Single Source of Truth per la Collana Editoriale KDP di Mirco Pregnolato (Metodo Hudolin, SAT, Famiglia, Club)
 * Conforme alla Governance AGENTS.md e Viewport Master Spec
 */

declare(strict_types=1);

namespace Dependex\Books;

class BooksCatalogService
{
    /**
     * Restituisce tutti i libri del catalogo con metadati completi, copertine, copy SEO magnetico e link Amazon
     *
     * @return array<int, array<string, mixed>>
     */
    public static function getAll(): array
    {
        return [
            [
                'id' => 'prd_book_52_settimane',
                'code' => 'MP-HUD-01',
                'series' => 'Metodo Hudolin · Stile di Vita',
                'category' => 'ABITUDINI & METODO CONTINUO',
                'filter_category' => 'hudolin',
                'title' => '52 Settimane di Cambiamento',
                'subtitle' => 'Workbook personale ecologico-sociale per osservare relazioni, scelte, stile di vita e piccoli passi',
                'author' => 'Mirco Pregnolato',
                'asin' => 'B0HHZC3PZ2',
                'amazon_url' => 'https://www.amazon.it/dp/B0HHZC3PZ2',
                'price_paperback' => '19,90 €',
                'price_digital' => '14,90 €',
                'price_bundle' => '59,00 €',
                'pages' => 237,
                'trim' => '6 × 9 pollici (15,24 × 22,86 cm)',
                'paper' => 'Carta crema deluxe, interno B/N, copertina opaca',
                'cover_webp' => 'assets/img/books/52_settimane_cover.webp',
                'cover_jpg' => 'assets/img/books/52_settimane_cover.jpg',
                'badge_color' => 'var(--neon-green)',
                'color_theme' => 'card-neon-green',
                'hook' => 'Un anno intero per osservare, nominare e rendere visibili i piccoli e grandi cambiamenti della tua vita nelle relazioni.',
                'synopsis' => '52 Settimane di Cambiamento è un workbook ecologico-sociale pensato per chi vuole guardare sé stesso dentro le relazioni, la famiglia e la comunità: senza giudizi, senza obblighi e senza promesse miracolose. Ogni settimana trovi 4 sezioni operative (Osservo, Io nelle relazioni, Cambiamento, Spazio libero). Ogni 4 settimane un checkpoint; alle settimane 13, 26, 39 e 52 la Ruota della Vita e la mappa delle relazioni.',
                'highlights' => [
                    '52 blocchi settimanali di auto-osservazione guidata',
                    '4 Ruote della Vita progressive (settimane 13, 26, 39, 52)',
                    'Domande maieutiche senza pressione da prestazione',
                    'Focus su relazioni, famiglia, ascolto e responsabilità condivisa'
                ],
                'target_audience' => 'Persone e famiglie che desiderano uno spazio intimo, ordinato e rispettoso per osservare il proprio percorso un giorno alla volta.',
                'not_for' => 'Non è una terapia clinica, non è un registro diagnostico e non assegna punteggi di benessere.'
            ],
            [
                'id' => 'prd_book_diario_club',
                'code' => 'MP-HUD-02',
                'series' => 'Metodo Hudolin · Vita di Club',
                'category' => 'SOBRIETÀ & CLUB TERRITORIALI',
                'filter_category' => 'club',
                'title' => 'Diario del Club',
                'subtitle' => '52 incontri · una storia comune',
                'author' => 'Mirco Pregnolato',
                'asin' => 'B0HJ475K5G',
                'amazon_url' => 'https://www.amazon.it/dp/B0HJ475K5G',
                'price_paperback' => '19,90 €',
                'price_digital' => '14,90 €',
                'price_bundle' => '49,00 €',
                'pages' => 223,
                'trim' => '6 × 9 pollici (15,24 × 22,86 cm)',
                'paper' => 'Carta crema, interno B/N, copertina brossurata',
                'cover_webp' => 'assets/img/books/diario_club_cover.webp',
                'cover_jpg' => 'assets/img/books/diario_club_cover.jpg',
                'badge_color' => 'var(--neon-gold)',
                'color_theme' => 'card-neon-gold',
                'hook' => 'Un Club si incontra ogni settimana. Questo diario custodisce ciò che conta perché nulla della storia comune vada perduto.',
                'synopsis' => 'Il Diario del Club è lo strumento operativo annuale pensato per accompagnare 52 incontri settimanali del Club Alcologico Territoriale. Raccoglie verbali, temi trattati, 52 Ruote della Vita del Club e 104 pagine libere di riflessione. Aiuta il Club, le famiglie e il verbalista a dare continuità al cammino.',
                'highlights' => [
                    '52 sezioni strutturate per ogni incontro settimanale',
                    '2 pagine libere a disposizione per ogni incontro',
                    '52 Ruote della Vita del Club per il confronto comunitario',
                    'Spazio riservato per le annotazioni del verbalista e facilitatore'
                ],
                'target_audience' => 'Membri dei Club Alcologici Territoriali, verbalisti, servitori-insegnanti e associazioni di mutuo-aiuto.',
                'not_for' => 'Non è una cartella clinica né un sistema di valutazione o giudizio dei membri.'
            ],
            [
                'id' => 'prd_book_sat_1',
                'code' => 'MP-HUD-03',
                'series' => 'Scuola Alcologica Territoriale · Modulo I',
                'category' => 'FORMAZIONE ISTITUZIONALE SAT',
                'filter_category' => 'sat',
                'title' => 'SAT I — Workbook',
                'subtitle' => '8 incontri per conoscere, comprendere, partecipare e portare il cambiamento nella vita quotidiana',
                'author' => 'Mirco Pregnolato',
                'asin' => 'B0HJ23231N',
                'amazon_url' => 'https://www.amazon.it/dp/B0HJ23231N',
                'price_paperback' => '19,90 €',
                'price_digital' => '14,90 €',
                'price_bundle' => '49,00 €',
                'pages' => 80,
                'trim' => '6 × 9 pollici (15,24 × 22,86 cm)',
                'paper' => 'Carta crema, interno B/N con schemi grafici',
                'cover_webp' => 'assets/img/books/sat_1_cover.webp',
                'cover_jpg' => 'assets/img/books/sat_1_cover.jpg',
                'badge_color' => 'var(--neon-cyan)',
                'color_theme' => 'card-neon-cyan',
                'hook' => 'Otto incontri. Un workbook operativo. Un percorso ecologico-sociale da vivere in prima persona, non un test da superare.',
                'synopsis' => 'Accompagna il Primo Modulo della Scuola Alcologica Territoriale (SAT I) attraverso 8 incontri tematici: salute e qualità della vita, alcol e problemi complessi, legislazione, famiglia, accoglienza nel Club, facilitazione e spiritualità antropologica. Coniuga teoria, riflessioni ed esercizi pratici per il lavoro di gruppo e in famiglia.',
                'highlights' => [
                    '8 sessioni formative complete del Modulo Base SAT',
                    'Mappe concettuali, schemi di sintesi e domande maieutiche',
                    'Attività da svolgere a casa in famiglia e lavori di gruppo',
                    'Prospettiva ecologico-sociale: Persona → Famiglia → Club → Comunità'
                ],
                'target_audience' => 'Famiglie e partecipanti al SAT I, corsisti delle Scuole Territoriali e nuovi partecipanti al Club.',
                'not_for' => 'Non è un manuale medico né una promessa di guarigione.'
            ],
            [
                'id' => 'prd_book_sat_2',
                'code' => 'MP-HUD-04',
                'series' => 'Scuola Alcologica Territoriale · Modulo II',
                'category' => 'FORMAZIONE ISTITUZIONALE SAT',
                'filter_category' => 'sat',
                'title' => 'SAT II — Workbook di aggiornamento',
                'subtitle' => 'Secondo Modulo della Scuola Alcologica Territoriale · Famiglie, Club, rete e comunità',
                'author' => 'Mirco Pregnolato',
                'asin' => 'WJ2S7ZSE8T7',
                'amazon_url' => 'https://www.amazon.it/s?k=mirco+pregnolato+sat+ii+workbook',
                'price_paperback' => '19,90 €',
                'price_digital' => '14,90 €',
                'price_bundle' => '49,00 €',
                'pages' => 107,
                'trim' => '6 × 9 pollici (15,24 × 22,86 cm)',
                'paper' => 'Carta crema, interno B/N, finitura opaca',
                'cover_webp' => 'assets/img/books/sat_2_cover.webp',
                'cover_jpg' => 'assets/img/books/sat_2_cover.jpg',
                'badge_color' => 'var(--neon-purple)',
                'color_theme' => 'card-neon-purple',
                'hook' => 'Il SAT I ti ha dato le basi. Il SAT II parte da un\'altra domanda: cosa abbiamo imparato vivendo davvero nel Club?',
                'synopsis' => 'Dedicato a chi ha già frequentato il modulo iniziale e partecipa alla vita del Club. Il percorso rilegge l\'esperienza vissuta, affronta la gestione delle difficoltà relazionali, rafforza la motivazione continua e stimola l\'apertura solidale verso la rete territoriale e le nuove famiglie.',
                'highlights' => [
                    'Approfondimento esperienziale per il secondo livello di scuola',
                    'Schede di revisione delle dinamiche relazionali e comunicative',
                    'Strumenti di consolidamento per la famiglia e per il Club',
                    'Focus su comunità, accoglienza delle ricadute e continuità'
                ],
                'target_audience' => 'Famiglie già attive nel Club, servitori-insegnanti in formazione avanzata e operatori di rete.',
                'not_for' => 'Non è un percorso clinico né una classificazione del rischio.'
            ],
            [
                'id' => 'prd_book_sat_3',
                'code' => 'MP-HUD-05',
                'series' => 'Scuola Alcologica Territoriale · Modulo III',
                'category' => 'FORMAZIONE ISTITUZIONALE SAT',
                'filter_category' => 'sat',
                'title' => 'SAT III — Workbook della Comunità',
                'subtitle' => 'Terzo Modulo della Scuola Alcologica Territoriale · Conoscere l\'alcol, comprendere la cultura, costruire comunità consapevoli',
                'author' => 'Mirco Pregnolato',
                'asin' => 'B0HJ2R6W19',
                'amazon_url' => 'https://www.amazon.it/dp/B0HJ2R6W19',
                'price_paperback' => '19,90 €',
                'price_digital' => '14,90 €',
                'price_bundle' => '49,00 €',
                'pages' => 79,
                'trim' => '6 × 9 pollici (15,24 × 22,86 cm)',
                'paper' => 'Carta crema, interno B/N, diagrammi di comunità',
                'cover_webp' => 'assets/img/books/sat_3_cover.webp',
                'cover_jpg' => 'assets/img/books/sat_3_cover.jpg',
                'badge_color' => 'var(--neon-orange)',
                'color_theme' => 'card-neon-orange',
                'hook' => 'Quanto di ciò che pensiamo sull\'alcol è evidenza e quanto è abitudine culturale? Uno strumento per l\'intera comunità.',
                'synopsis' => 'Un workbook aperto alla cittadinanza, alle scuole, al volontariato e agli enti locali. Articolato in 2 incontri da 2 ore ciascuno (Conoscere e Comunità), decostruisce i miti sul consumo di alcol, esamina l\'impatto socioculturale ed ecologico e offre linee guida per trasformare la comunità in un ambiente protettivo e solidale.',
                'highlights' => [
                    'Due moduli intensivi per cittadini, studenti, docenti e amministratori',
                    'Mappe territoriali e analisi critica dei messaggi culturali',
                    'Attività collaborative tra mito, esperienza ed evidenza scientifica',
                    'Proposte d\'azione civica e comunitaria sul territorio'
                ],
                'target_audience' => 'Cittadini, famiglie, associazioni civiche, docenti, giovani ed enti territoriali.',
                'not_for' => 'Non è riservato solo a chi ha vissuto problemi con l\'alcol.'
            ],
            [
                'id' => 'prd_book_famiglia',
                'code' => 'MP-HUD-06',
                'series' => 'Metodo Hudolin · Famiglia al Centro',
                'category' => 'FAMIGLIA & RELAZIONI',
                'filter_category' => 'famiglia',
                'title' => 'Quaderno della Famiglia',
                'subtitle' => '52 settimane nel Club · Un anno per osservare relazioni, cambiamenti, risorse e vita condivisa',
                'author' => 'Mirco Pregnolato',
                'asin' => 'WAPBJF4986J',
                'amazon_url' => 'https://www.amazon.it/s?k=mirco+pregnolato+quaderno+della+famiglia',
                'price_paperback' => '19,90 €',
                'price_digital' => '14,90 €',
                'price_bundle' => '49,00 €',
                'pages' => 172,
                'trim' => '6 × 9 pollici (15,24 × 22,86 cm)',
                'paper' => 'Carta crema, interno B/N, schede di dialogo',
                'cover_webp' => 'assets/img/books/quaderno_famiglia_cover.webp',
                'cover_jpg' => 'assets/img/books/quaderno_famiglia_cover.jpg',
                'badge_color' => 'var(--neon-cyan)',
                'color_theme' => 'card-neon-cyan',
                'hook' => 'Una famiglia può semplicemente attraversare un anno. Oppure può imparare a vederlo e a custodirlo insieme.',
                'synopsis' => 'Il soggetto di questo volume è NOI. Pensato per il nucleo familiare che cammina nel Club, questo quaderno guida 52 settimane di ascolto empatico, chiarimento dei bisogni e superamento dei sensi di colpa. Con 52 Ruote della Vita Familiare e 7 passi di ricostruzione della fiducia quotidiana.',
                'highlights' => [
                    '52 settimane di dialogo guidato senza accuse né recriminazioni',
                    '52 Ruote della Vita Familiare per fotografare il cammino',
                    'Spazi di ascolto per ciascun componente del nucleo',
                    'Strategie per custodire la memoria positiva dei cambiamenti'
                ],
                'target_audience' => 'Coppie, genitori, figli e nuclei familiari che camminano insieme verso un nuovo equilibrio.',
                'not_for' => 'Non è una terapia familiare clinica e non assegna colpe.'
            ],
            [
                'id' => 'prd_book_servitore',
                'code' => 'MP-HUD-07',
                'series' => 'Metodo Hudolin · Ruolo Operativo',
                'category' => 'GUIDA PER OPERATORI E FACILITATORI',
                'filter_category' => 'club',
                'title' => 'Diario del Servitore-Insegnante',
                'subtitle' => '52 incontri per osservare presenza, ascolto, autonomia, relazioni e formazione nel Club',
                'author' => 'Mirco Pregnolato',
                'asin' => 'MP-SERV-52',
                'amazon_url' => 'https://www.amazon.it/s?k=mirco+pregnolato+diario+servitore+insegnante',
                'price_paperback' => '14,90 €',
                'price_digital' => '14,90 €',
                'price_bundle' => '49,00 €',
                'pages' => 112,
                'trim' => '6 × 9 pollici (15,24 × 22,86 cm)',
                'paper' => 'Carta crema, interno B/N, tascabile operativo',
                'cover_webp' => 'assets/img/books/diario_servitore_cover.webp',
                'cover_jpg' => 'assets/img/books/diario_servitore_cover.jpg',
                'badge_color' => 'var(--neon-green)',
                'color_theme' => 'card-neon-green',
                'hook' => 'Non devi avere tutte le risposte. Devi imparare a esserci nel modo giusto: facilitare senza sostituirsi.',
                'synopsis' => 'Il taccuino di auto-formazione continua per il facilitatore di Club. Accompagna 52 incontri aiutando a osservare le dinamiche del cerchio, gestire i silenzi con rispetto, valorizzare l\'autonomia del Club ed evitare il burnout o la tentazione di mettersi al centro.',
                'highlights' => [
                    'Guida all\'auto-osservazione dopo ogni incontro settimanale',
                    'Checkpoint formativi ogni 13 settimane per la supervisione',
                    'Deontologia dell\'approccio ecologico-sociale e gestione del silenzio',
                    'Sviluppo dell\'ascolto senza etichette diagnostiche'
                ],
                'target_audience' => 'Servitori-insegnanti di Club Alcologici Territoriali e conduttori di cerchi di auto-mutuo-aiuto.',
                'not_for' => 'Non è un registro valutativo dei membri del Club.'
            ],
            [
                'id' => 'prd_book_crescita_esp',
                'code' => 'MP-HUD-08',
                'series' => 'Metodo Hudolin & Crescita Umana',
                'category' => 'CRESCITA PERSONALE & TRASFORMAZIONE',
                'filter_category' => 'crescita',
                'title' => 'Il Mio Diario di Crescita Esponenziale',
                'subtitle' => '365 Giorni di potenziamento personale, abitudini sobrie e trasformazione profonda',
                'author' => 'Mirco Pregnolato',
                'asin' => 'MP-CRES-365',
                'amazon_url' => 'https://www.amazon.it/s?k=mirco+pregnolato+diario+crescita+esponenziale',
                'price_paperback' => '24,90 €',
                'price_digital' => '19,90 €',
                'price_bundle' => '69,00 €',
                'pages' => 386,
                'trim' => '6 × 9 pollici (15,24 × 22,86 cm)',
                'paper' => 'Carta crema deluxe, volume monumentale 386 pagine',
                'cover_webp' => 'assets/img/books/diario_crescita_cover.webp',
                'cover_jpg' => 'assets/img/books/diario_crescita_cover.jpg',
                'badge_color' => 'var(--neon-orange)',
                'color_theme' => 'card-neon-orange',
                'hook' => '365 giorni per trasformare la sobrietà da semplice astinenza in una solida architettura di vita, chiarezza e vitalità.',
                'synopsis' => 'Un compagno di viaggio quotidiano per un intero anno. 365 schede strutturate per forgiare disciplina personale, monitorare il sonno e la vitalità corporea, coltivare relazioni autentiche e dare un significato duraturo al proprio cammino quotidiano.',
                'highlights' => [
                    '365 pagine giornaliere di journaling operativo e riflessione',
                    'Mappe trimestrali di orientamento e bilancio di vita',
                    'Integrazione tra approccio ecologico-sociale e abitudini virtuose',
                    'Formato premium per una custodia a lungo termine'
                ],
                'target_audience' => 'Persone determinate a consolidare il proprio stile di vita libero e realizzare il proprio potenziale umano.',
                'not_for' => 'Non promette scorciatoie né risultati istantanei senza impegno.'
            ]
        ];
    }

    /**
     * Ritorna i libri filtrati o limitati per il ticker
     */
    public static function getTickerItems(): array
    {
        return self::getAll();
    }
}
