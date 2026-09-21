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

    /**
     * SALUTOGENESI: Sense of Coherence (Aaron Antonovsky)
     * Comprensibilità, Gestibilità, Significato
     */
    public static function getSenseOfCoherence(): array {
        return [
            'comprensibilita' => [
                'id' => 'comprensibilita',
                'title' => 'Comprensibilità',
                'question' => 'Capisco cosa sta succedendo?',
                'principle' => 'Fornire trasparenza sui meccanismi della dipendenza, del bere e del cambiamento ecologico-sociale, togliendo la nebbia e la vergogna.'
            ],
            'gestibilita' => [
                'id' => 'gestibilita',
                'title' => 'Gestibilità',
                'question' => 'Ho o posso trovare risorse per affrontarlo?',
                'principle' => 'Rendere accessibili strumenti pratici, supporto comunitario e la presenza calda di persone che hanno già fatto il cammino.'
            ],
            'significato' => [
                'id' => 'significato',
                'title' => 'Significato',
                'question' => 'Perché vale la pena occuparmene?',
                'principle' => 'Ritrovare il valore della propria vita, la dignità, l\'amore dei familiari e la motivazione profonda a rinascere.'
            ]
        ];
    }

    /**
     * SELF-DETERMINATION THEORY (Deci & Ryan)
     * Autonomia, Competenza, Relazione
     */
    public static function getSelfDetermination(): array {
        return [
            'autonomia' => [
                'id' => 'autonomia',
                'name' => 'Autonomia',
                'description' => 'La persona sceglie liberamente il proprio cammino: mai imposizioni, mai paternalismo. "Se vuoi, puoi iniziare da qui".'
            ],
            'competenza' => [
                'id' => 'competenza',
                'name' => 'Competenza',
                'description' => 'La persona impara e sviluppa nuove abilità quotidiane per gestire le tensioni emotive senza ricorrere alle sostanze.'
            ],
            'relazione' => [
                'id' => 'relazione',
                'name' => 'Relazione',
                'description' => 'La persona si sente accolta, protetta e connessa ad altri esseri umani che condividono lo stesso percorso con amicizia e rispetto.'
            ]
        ];
    }

    /**
     * COM-B BEHAVIORAL ARCHITECTURE (Michie)
     * Capability, Opportunity, Motivation
     */
    public static function getComBAnalysis(string $action = 'club'): array {
        return [
            'action' => $action,
            'capability' => [
                'label' => 'Capacità (So cosa aspettarmi)',
                'description' => 'Spiegazione chiara: una riunione dura 90 minuti, non si è obbligati a parlare, non ci sono cattedre né prescrizioni.'
            ],
            'opportunity' => [
                'label' => 'Opportunità (È vicino e accessibile)',
                'description' => 'Presenza capillare di 322+ Club gratuiti sul territorio italiano, raggiungibili la sera senza burocrazia né costi.'
            ],
            'motivation' => [
                'label' => 'Motivazione (Vale la pena provare)',
                'description' => 'Storie reali di famiglie rinate, sollievo dalla solitudine e speranza concreta di ritrovare la serenità.'
            ]
        ];
    }

    /**
     * I 3 MICRO-JOURNEYS DI ORIENTAMENTO UMANO
     */
    public static function getMicroJourneys(): array {
        return [
            'non_so_da_dove_iniziare' => [
                'id' => 'non_so_da_dove_iniziare',
                'title' => 'Non so da dove iniziare',
                'tagline' => 'Accoglienza senza etichette per chi si sente sopraffatto o confuso.',
                'steps' => [
                    'Respira: non devi decidere tutto oggi né devi cambiare la tua vita in un giorno.',
                    'Scegli se preferisci leggere in silenzio o scambiare due parole con un volontario empatico.',
                    'Esplora la mappa dei Club o poni una domanda anonima senza alcun impegno.'
                ],
                'primary_cta' => ['label' => 'Esplora la Mappa del Benessere', 'url' => 'orientamento.php'],
                'secondary_cta' => ['label' => 'Leggi le Domande che vuoi fare', 'url' => 'domande-frequenti.php']
            ],
            'cerco_un_club' => [
                'id' => 'cerco_un_club',
                'title' => 'Cerco un Club vicino a me',
                'tagline' => 'Trova la comunità reale nel tuo comune o quartiere.',
                'steps' => [
                    'Inserisci la tua città, CAP o attiva la geolocalizzazione per vedere le sedi.',
                    'Scegli il giorno e l\'orario più comodo per te.',
                    'Presentati all\'incontro: puoi venire da solo o con chi ti vuole bene. Sarai accolto senza giudizio.'
                ],
                'primary_cta' => ['label' => 'Cerca un Club Territoriale', 'url' => 'cerca-club.php'],
                'secondary_cta' => ['label' => 'Come Funziona una Riunione', 'url' => 'come-funziona-il-club.php']
            ],
            'aiuto_una_persona' => [
                'id' => 'aiuto_una_persona',
                'title' => 'Voglio aiutare una persona cara',
                'tagline' => 'Orientamento per familiari, partner, amici e colleghi.',
                'steps' => [
                    'Non sei solo: l\'approccio Hudolin è multifamiliare e protegge prima di tutto i legami affettivi.',
                    'Puoi iniziare a frequentare il Club anche se la persona coinvolta rifiuta di venire o dice di non avere problemi.',
                    'Il sollievo e il cambiamento nell\'atmosfera di casa iniziano dalla tua presenza nel cerchio.'
                ],
                'primary_cta' => ['label' => 'Scarica la Guida Gratuita Famiglia', 'url' => 'guida-gratuita.php'],
                'secondary_cta' => ['label' => 'Parla con un Servitore Insegnante', 'url' => 'parla-con-noi.php']
            ]
        ];
    }

    /**
     * SMALL STEPS ENGINE: Restituisce massimo 3 prossimi passi per prevenire l'overwhelm
     */
    public static function getSmallSteps(string $areaId): array {
        $dim = self::getWelfareDimensions()[$areaId] ?? self::getWelfareDimensions()['radicamento'];
        
        $steps = [
            [
                'title' => 'Incontro al Club Locale',
                'description' => 'Unisciti a un cerchio settimanale di 90 minuti: gratuito, anonimo e aperto a tutti.',
                'url' => 'cerca-club.php',
                'cta' => 'Trova Club'
            ],
            [
                'title' => 'Ascolto Riservato',
                'description' => 'Scrivi o parla con un volontario preparato che ti ascolta senza giudicare.',
                'url' => 'parla-con-noi.php',
                'cta' => 'Parla con Noi'
            ],
            [
                'title' => 'Risorsa di Rinascita',
                'description' => $dim['resources'][0]['title'] ?? 'Guide gratuite e testimonianze di comunità.',
                'url' => $dim['resources'][0]['url'] ?? 'guida-gratuita.php',
                'cta' => 'Approfondisci'
            ]
        ];

        // Tassativamente massimo 3 passi (Small Steps Engine)
        return array_slice($steps, 0, 3);
    }

    /**
     * WELFARE COMPASS: Le 12 aree maieutiche della vita
     */
    public static function getWelfareCompassAreas(): array {
        return [
            'corpo' => ['name' => 'Corpo & Salute', 'question' => 'Come sta il tuo corpo e quale cura desidera oggi?'],
            'sicurezza' => ['name' => 'Sicurezza & Casa', 'question' => 'Senti che il tuo ambiente quotidiano è protetto e stabile?'],
            'relazioni' => ['name' => 'Relazioni & Amicizie', 'question' => 'Chi sono le persone con cui puoi essere autentico senza maschere?'],
            'autonomia' => ['name' => 'Autonomia & Scelta', 'question' => 'Quanto spazio senti di avere per decidere della tua vita?'],
            'espressione' => ['name' => 'Espressione & Parola', 'question' => 'Riesci a dire quello che provi senza timore del giudizio?'],
            'consapevolezza' => ['name' => 'Consapevolezza & Mente', 'question' => 'Cosa ti aiuta a mantenere lucidità e chiarezza interiore?'],
            'significato' => ['name' => 'Significato & Valori', 'question' => 'Qual è il senso o lo scopo che ti dà energia ogni mattina?'],
            'comunita' => ['name' => 'Comunità & Appartenenza', 'question' => 'In quale gruppo o comunità ti senti accolto e a casa?'],
            'lavoro' => ['name' => 'Lavoro & Dignità', 'question' => 'Come vivi le tue attività e la tua sostenibilità quotidiana?'],
            'famiglia' => ['name' => 'Famiglia & Legami', 'question' => 'Quale cura o riconciliazione chiedono i tuoi affetti più cari?'],
            'territorio' => ['name' => 'Territorio & Spazio', 'question' => 'Come vivi il rapporto con i luoghi in cui risiedi?'],
            'partecipazione' => ['name' => 'Partecipazione & Servizio', 'question' => 'Come desideri contribuire al benessere degli altri?']
        ];
    }

    /**
     * RACCOMANDAZIONI SPIEGABILI (Explainable Recommendations)
     */
    public static function explainRecommendation(string $type, string $itemName, string $userContext): string {
        return "Ti mostriamo {$itemName} perché hai dichiarato interesse per {$userContext}, in conformità con la scelta libera e non invasiva.";
    }

    /**
     * COMMUNITY CAPITAL OVERVIEW (Aggregato, senza classifiche individuali)
     */
    public static function getCommunityCapitalOverview(): array {
        return [
            'clubs_count' => '1.770+',
            'weekly_circles' => '1.770+',
            'annual_circle_hours' => '92.000+',
            'verified_stories' => '15+',
            'free_access' => '100%',
            'territorial_coverage' => 'Tutte le 20 regioni d\'Italia'
        ];
    }

    /**
     * CONTRIBUTION ENGINE: Dal ricevere al generare comunità
     */
    public static function getContributionPaths(): array {
        return [
            ['stage' => 'RICEVERE', 'desc' => 'Vieni al Club e sperimenta l\'accoglienza senza dover dare nulla in cambio.'],
            ['stage' => 'PARTECIPARE', 'desc' => 'Partecipa regolarmente e condividi la tua presenza nel cerchio.'],
            ['stage' => 'APPRENDERE', 'desc' => 'Approfondisci il Metodo e le dinamiche di crescita personale e familiare.'],
            ['stage' => 'CONTRIBUIRE', 'desc' => 'Sostieni il Club nella logistica, nell\'apertura della sala o nell\'accoglienza.'],
            ['stage' => 'AIUTARE', 'desc' => 'Metti a disposizione la tua esperienza di sobrietà per accompagnare chi è all\'inizio.'],
            ['stage' => 'GENERARE COMUNITÀ', 'desc' => 'Formati come Servitore-Insegnante e aiuta ad aprire nuovi Club sul territorio.']
        ];
    }
}
