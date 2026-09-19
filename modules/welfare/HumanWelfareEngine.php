<?php
/**
 * DEPENDEX.SOCIAL — HUMAN WELFARE OS 4.0
 * modules/welfare/HumanWelfareEngine.php
 *
 * Motore di orientamento umano integrato:
 * Hudolin (relazione/comunità) + Maslow (bisogni) + Ruota della Vita (fotografia privata)
 * + Tradizioni vediche (Dharma, Artha, Kama, Moksha) + 7 Dimensioni dell'Energia del Benessere.
 *
 * VINCOLO DEONTOLOGICO:
 * Non è una diagnosi medica o clinica né un sistema di ranking o classificazione della persona.
 * Strumento di consapevolezza e orientamento: "Da dove vuoi iniziare?" -> "Partiamo da lì".
 */

class HumanWelfareEngine {

    /**
     * Le 7 Dimensioni dell'Energia del Benessere (Welfare Energy Dimensions)
     */
    public static function getWelfareDimensions(): array {
        return [
            'radicamento' => [
                'id' => 'radicamento',
                'level' => 1,
                'name' => 'Radicamento & Sicurezza',
                'color' => '#ff3344',
                'tagline' => 'Sicurezza, casa, bisogni primari e territorio.',
                'question' => 'Senti che le basi della tua quotidianità o la tua casa hanno bisogno di maggiore stabilità?',
                'hudolin_aspect' => 'Territorio & Accoglienza di Base',
                'hudolin_pillar' => 'Territorio & Accoglienza di Base',
                'maslow_level' => 'Bisogni fisiologici e di sicurezza',
                'maslow_need' => 'Bisogni fisiologici e di sicurezza',
                'vedic_aim' => 'Artha (Sostenibilità materiale e stabilità)',
                'response_manifesto' => 'Questa parte della tua vita oggi chiede attenzione. Partiamo da lì: ritrovare un terreno solido sotto i piedi è il primo passo per ogni percorso.',
                'facets' => ['Casa e dimora', 'Sicurezza materiale', 'Territorio locale', 'Basi quotidiane'],
                'resources' => [
                    ['title' => 'Trova il Club più vicino nel tuo territorio', 'url' => 'world-club-explorer.php', 'icon' => 'map-pin'],
                    ['title' => 'Guida pratica di orientamento per la casa e la famiglia', 'url' => 'guida-gratuita.php', 'icon' => 'book-open'],
                    ['title' => 'Sportello di ascolto riservato e anonimo', 'url' => 'parla-con-noi.php', 'icon' => 'message-circle']
                ]
            ],
            'vitalita' => [
                'id' => 'vitalita',
                'level' => 2,
                'name' => 'Vitalità & Salute',
                'color' => '#ff7700',
                'tagline' => 'Energia quotidiana, rigenerazione fisica e mentale.',
                'question' => 'Senti il bisogno di ritrovare energia fisica, lucidità e liberarti dal peso della stanchezza o del bere?',
                'hudolin_aspect' => 'Cambiamento dello Stile di Vita',
                'hudolin_pillar' => 'Cambiamento dello Stile di Vita',
                'maslow_level' => 'Benessere biologico ed energetico',
                'maslow_need' => 'Benessere biologico ed energetico',
                'vedic_aim' => 'Kama (Vitalità ed esperienza positiva della vita)',
                'response_manifesto' => 'La vitalità non si riconquista con la forza di volontà isolata, ma con il ritmo e la vicinanza di persone che vivono la stessa rinascita. Partiamo da lì.',
                'facets' => ['Salute fisica', 'Lucidità mentale', 'Ritmo sonno-veglia', 'Energia quotidiana'],
                'resources' => [
                    ['title' => 'Dashboard di automonitoraggio e giorni di lucidità', 'url' => 'dashboard.php', 'icon' => 'bar-chart-2'],
                    ['title' => 'Esercizio di de-escalation rapida e calma 4-7-8', 'url' => 'offline.html', 'icon' => 'shield'],
                    ['title' => 'Testimonianze di chi ha ritrovato la salute al Club', 'url' => 'recensioni.php', 'icon' => 'heart']
                ]
            ],
            'autonomia' => [
                'id' => 'autonomia',
                'level' => 3,
                'name' => 'Autonomia & Responsabilità',
                'color' => '#ffd700',
                'tagline' => 'Scelta consapevole, confini sani, autodeterminazione.',
                'question' => 'Vorresti sentirti di nuovo protagonista delle tue scelte e del tuo tempo, senza dipendere da abitudini forzate?',
                'hudolin_aspect' => 'Autodeterminazione & Ruolo Attivo',
                'hudolin_pillar' => 'Autodeterminazione & Ruolo Attivo',
                'maslow_level' => 'Autostima e rispetto di sé',
                'maslow_need' => 'Autostima e rispetto di sé',
                'vedic_aim' => 'Dharma (Responsabilità e direzione personale)',
                'response_manifesto' => 'Essere liberi significa poter scegliere senza sensi di colpa. Nel Club ognuno è responsabile del proprio cammino e nessuno giudica le cadute.',
                'facets' => ['Autodeterminazione', 'Lavoro e impegni', 'Gestione del tempo', 'Confini personali'],
                'resources' => [
                    ['title' => 'Ruota della Vita: esplora le tue priorità personali', 'url' => 'ruota-della-vita.php', 'icon' => 'compass'],
                    ['title' => 'Il Metodo Hudolin: come funziona il cambiamento responsabile', 'url' => 'metodo.php', 'icon' => 'info'],
                    ['title' => 'Diario personale dei 90 giorni di crescita', 'url' => 'offers.php', 'icon' => 'book']
                ]
            ],
            'relazione' => [
                'id' => 'relazione',
                'level' => 4,
                'name' => 'Relazione & Famiglia',
                'color' => '#00ff77',
                'tagline' => 'Legami sinceri, accoglienza multifamiliare, fine della solitudine.',
                'question' => 'C\'è una tensione o una distanza con la famiglia o senti la solitudine di non avere chi ti capisca?',
                'hudolin_aspect' => 'Comunità Multifamiliare & Cerchio dei 90 min',
                'hudolin_pillar' => 'Comunità Multifamiliare & Cerchio dei 90 min',
                'maslow_level' => 'Appartenenza e amore relazionale',
                'maslow_need' => 'Appartenenza e amore relazionale',
                'vedic_aim' => 'Kama & Dharma (Legami d\'amore e reciprocità)',
                'response_manifesto' => 'La solitudine è la stanza dove le dipendenze mettono radici. Il Club nasce per rompere questo isolamento: non serve che parli, puoi venire anche solo ad ascoltare.',
                'facets' => ['Famiglia', 'Amicizie sincere', 'Cerchio dei pari', 'Reciprocità affettiva'],
                'resources' => [
                    ['title' => 'Cosa succede in un incontro del Club: risposte alle tue domande', 'url' => 'domande-frequenti.php', 'icon' => 'help-circle'],
                    ['title' => 'Partecipa al cerchio multifamiliare più vicino', 'url' => 'world-club-explorer.php', 'icon' => 'users'],
                    ['title' => 'Guida per i familiari che vogliono capire come aiutare', 'url' => 'guida-gratuita.php', 'icon' => 'file-text']
                ]
            ],
            'espressione' => [
                'id' => 'espressione',
                'level' => 5,
                'name' => 'Espressione & Ascolto',
                'color' => '#00d4ff',
                'tagline' => 'Comunicazione libera, parola sincera, capacità di chiedere aiuto.',
                'question' => 'Ti tieni tutto dentro per paura di essere giudicato o non sai come esprimere quello che stai passando?',
                'hudolin_aspect' => 'Circolarità della Parola & Riservatezza',
                'hudolin_pillar' => 'Circolarità della Parola & Riservatezza',
                'maslow_level' => 'Espressione autentica e dignità',
                'maslow_need' => 'Espressione autentica e dignità',
                'vedic_aim' => 'Satya (Verità e trasparenza comunicativa)',
                'response_manifesto' => 'Non devi avere già le parole giuste. Al Club si impara che anche il silenzio viene rispettato e accolto con dolcezza.',
                'facets' => ['Ascolto empatico', 'Condivisione autentica', 'Verità e trasparenza', 'Chiedere aiuto'],
                'resources' => [
                    ['title' => 'Scrivi un messaggio riservato e anonimo alla nostra segreteria', 'url' => 'parla-con-noi.php', 'icon' => 'message-square'],
                    ['title' => 'Le domande che forse ti vergogni a fare', 'url' => 'domande-frequenti.php', 'icon' => 'help-circle'],
                    ['title' => 'Telefono Verde Alcol Nazionale (800 632 000)', 'url' => 'tel:800632000', 'icon' => 'phone']
                ]
            ],
            'consapevolezza' => [
                'id' => 'consapevolezza',
                'level' => 6,
                'name' => 'Consapevolezza & Lucidità',
                'color' => '#3a55ff',
                'tagline' => 'Comprensione delle connessioni, studio, chiarezza mentale.',
                'question' => 'Vuoi capire le radici dei comportamenti e come l\'ambiente sociale influenza le nostre abitudini?',
                'hudolin_aspect' => 'Ecologia Sociale & Educazione Continua',
                'hudolin_pillar' => 'Ecologia Sociale & Educazione Continua',
                'maslow_level' => 'Bisogni cognitivi e di autorealizzazione',
                'maslow_need' => 'Bisogni cognitivi e di autorealizzazione',
                'vedic_aim' => 'Jnana (Conoscenza e discernimento)',
                'response_manifesto' => 'Capire come funzionano i condizionamenti toglie il senso di colpa e restituisce chiarezza mentale. La conoscenza è uno scudo comunitario.',
                'facets' => ['Studio e comprensione', 'Ecologia sociale', 'Riflessione critica', 'Visione d\'insieme'],
                'resources' => [
                    ['title' => 'Piramide di Maslow Hudolin: esplora i 5 livelli', 'url' => 'piramide-maslow.php', 'icon' => 'layers'],
                    ['title' => 'Academy di Formazione e Corsi di Sensibilizzazione', 'url' => 'academy-public.php', 'icon' => 'award'],
                    ['title' => 'Manuali e testi di approfondimento del Metodo', 'url' => 'offers.php', 'icon' => 'book-open']
                ]
            ],
            'significato' => [
                'id' => 'significato',
                'level' => 7,
                'name' => 'Significato & Servizio',
                'color' => '#b829ff',
                'tagline' => 'Valori profondi, vocazione, servizio disinteressato alla comunità.',
                'question' => 'Cerchi un senso più profondo e il desiderio di trasformare la tua esperienza in aiuto per gli altri?',
                'hudolin_aspect' => 'Servitore-Insegnante & Solidarietà Comunitaria',
                'hudolin_pillar' => 'Servitore-Insegnante & Solidarietà Comunitaria',
                'maslow_level' => 'Trascendenza e contributo agli altri',
                'maslow_need' => 'Trascendenza e contributo agli altri',
                'vedic_aim' => 'Moksha & Seva (Servizio disinteressato e liberazione)',
                'response_manifesto' => 'La vera serenità sboccia quando la sofferenza attraversata diventa una risorsa preziosa per chi sta muovendo i primi passi. Nessuno si salva da solo.',
                'facets' => ['Scopo e vocazione', 'Servizio alla comunità', 'Valori etici', 'Solidarietà e dono'],
                'resources' => [
                    ['title' => 'Diventare Servitore-Insegnante: i corsi sul territorio', 'url' => 'evento-ottobre-taglio-di-po.php', 'icon' => 'sun'],
                    ['title' => 'Vivi la Comunità: eventi e interclub aperti', 'url' => 'events-public.php', 'icon' => 'calendar'],
                    ['title' => 'Storie di cambiamento e testimonianze di rinascita', 'url' => 'storie.php', 'icon' => 'feather']
                ]
            ]
        ];
    }

    /**
     * I 4 Scopi Universali della Vita (Filosofia di Direzione Umana)
     */
    public static function getLifePurposes(): array {
        return [
            'dharma' => [
                'id' => 'dharma',
                'name' => 'Dharma · Direzione & Vocazione',
                'term' => 'DHARMA',
                'title' => 'Dharma · Direzione & Vocazione',
                'meaning' => 'Responsabilità, coerenza personale, ruolo utile nella famiglia e nella società.',
                'description' => 'La direzione etica che senti coerente con ciò che sei: dovere verso te stesso, i tuoi cari e la comunità.',
                'prompt' => 'Qual è la direzione che senti più coerente con la tua vera natura?',
                'hudolin_connection' => 'Responsabilità e ruolo attivo nel cerchio della vita'
            ],
            'artha' => [
                'id' => 'artha',
                'name' => 'Artha · Sostenibilità & Risorse',
                'term' => 'ARTHA',
                'title' => 'Artha · Sostenibilità & Risorse',
                'meaning' => 'Stabilità economica, lavoro dignitoso, sicurezza quotidiana per sé e per i propri cari.',
                'description' => 'I mezzi materiali e relazionali necessari per vivere con serenità e proteggere la famiglia.',
                'prompt' => 'Quali risorse pratiche ti servono per vivere con tranquillità?',
                'hudolin_connection' => 'Sicurezza quotidiana, dimora e stabilità territoriale'
            ],
            'kama' => [
                'id' => 'kama',
                'name' => 'Kama · Gioia & Vitalità Affettiva',
                'term' => 'KAMA',
                'title' => 'Kama · Gioia & Vitalità Affettiva',
                'meaning' => 'Piacere dell\'esperienza, affetti sani, rigenerazione, legami caldi e vitali.',
                'description' => 'La bellezza delle relazioni genuine, dell\'arte, dell\'amicizia e della vitalità sobria condivisa.',
                'prompt' => 'Cosa nutre la tua gioia di vivere e la tua energia quotidiana?',
                'hudolin_connection' => 'Empatia, calore dell\'incontro umano e affettività rigenerata'
            ],
            'moksha' => [
                'id' => 'moksha',
                'name' => 'Moksha · Libertà Interiore & Serenità',
                'term' => 'MOKSHA',
                'title' => 'Moksha · Libertà Interiore & Serenità',
                'meaning' => 'Emancipazione dalle dipendenze, pace della mente, senso di appartenenza a qualcosa di più grande.',
                'description' => 'La liberazione dai condizionamenti tossici, dalla solitudine e la pace con la propria storia.',
                'prompt' => 'Da cosa vorresti sentirti veramente libero e pacificato oggi?',
                'hudolin_connection' => 'Sobrietà interiore e solidarietà comunitaria permanente'
            ]
        ];
    }

    /**
     * Alias per getWelfareDimensions
     */
    public static function getDimensions(): array {
        return self::getWelfareDimensions();
    }

    /**
     * Cardini del Metodo Ecologico-Sociale di Hudolin
     */
    public static function getHudolinPillars(): array {
        return [
            'famiglia' => [
                'name' => 'Famiglia & Relazione',
                'description' => 'Non si cura una patologia individuale: si rigenerano i legami affettivi e comunicativi dell\'intero nucleo familiare.'
            ],
            'club' => [
                'name' => 'Incontro Circolare al Club',
                'description' => 'Incontro settimanale di 90 minuti tra pari, in cerchio, dove tutti hanno pari dignità e non esistono cattedre.'
            ],
            'servitore_insegnante' => [
                'name' => 'Servitore Insegnante',
                'description' => 'Un facilitatore della comunità, formato e motivato, che accompagna il gruppo con empatia e riservatezza.'
            ],
            'ecologia_sociale' => [
                'name' => 'Approccio Ecologico-Sociale',
                'description' => 'La salute è armonia tra persona, famiglia, cultura e ambiente. Il cambiamento trasforma l\'intera comunità locale.'
            ],
            'sobrieta_condivisa' => [
                'name' => 'Sobrietà Condivisa & Libertà',
                'description' => 'La sobrietà non è una rinuncia forzata o una punizione, ma una conquista gioiosa di libertà e consapevolezza.'
            ]
        ];
    }

    /**
     * Risolve l'orientamento per una specifica area della vita
     */
    public static function orientate(string $areaId): array {
        $dimensions = self::getWelfareDimensions();
        $dim = $dimensions[$areaId] ?? $dimensions['radicamento'];
        
        return [
            'area_id' => $areaId,
            'dimension' => $dim,
            'user_statement' => "Questa parte della mia vita oggi chiede attenzione.",
            'dependex_response' => "Partiamo da lì.",
            'message' => "Partiamo da lì. Vediamo cosa può aiutarti a rimettere in movimento la tua vita e quali persone e comunità possono accompagnarti.",
            'community_steps' => [
                [
                    'step' => 1,
                    'title' => 'Incontro Settimanale al Club (CAT)',
                    'desc' => 'Partecipa a una riunione di 90 minuti. Gratuita, anonima, con o senza familiari.'
                ],
                [
                    'step' => 2,
                    'title' => 'Parla con un Servitore Insegnante',
                    'desc' => 'Un contatto riservato con un volontario esperto pronto ad ascoltare senza giudizio.'
                ],
                [
                    'step' => 3,
                    'title' => 'Risorsa di Rinascita Consigliata',
                    'desc' => $dim['resources'][0]['title'] ?? 'Guide e contenuti aperti del Metodo Hudolin'
                ]
            ],
            'resource_recommended' => $dim['resources'][0]['title'] ?? 'Guida gratuita di orientamento per la famiglia'
        ];
    }
}
