<?php
/**
 * DEPENDEX.SOCIAL — OMNI-WELFARE GAMIFICATION ENGINE 6.0
 * modules/gamification/OmniWelfareGamificationEngine.php
 *
 * Life Playground & Gamification for Humans:
 * - Accesso esperienziale: "Come ti senti oggi?" / "Cosa vuoi esplorare?"
 * - Micro-missioni senza stress (30s, 60s, 2m, 10m)
 * - Nessuna gamificazione della sofferenza (zero punti per dolore o ansia)
 * - Separazione rigorosa delle fonti (Hudolin, H+, ABC, BetterWay, Veda, Maslow, Ruota, BEWAY)
 * - Livelli di partecipazione alla piattaforma (non di valore umano)
 * - Screen Off -> Life On (azioni nel mondo reale)
 */

class OmniWelfareGamificationEngine {

    /**
     * Portali esperienziali: "Come ti senti oggi?" / "Cosa vuoi esplorare?"
     */
    public static function getFeelingEntries(): array {
        return [
            'stare_meglio' => [
                'id' => 'stare_meglio',
                'emoji' => '🙂',
                'label' => 'Voglio stare meglio',
                'tagline' => 'Un piccolo passo leggero per ritrovare serenità.',
                'recommended_quests' => ['respiro_60s', 'tre_carte_gratitudine', 'partecipa_cerchio']
            ],
            'equilibrio' => [
                'id' => 'equilibrio',
                'emoji' => '🌱',
                'label' => 'Voglio ritrovare equilibrio',
                'tagline' => 'Centratura tra corpo, mente e relazioni quotidiane.',
                'recommended_quests' => ['radicamento_presente', 'ruota_esplorazione', 'natura_10min']
            ],
            'rallentare' => [
                'id' => 'rallentare',
                'emoji' => '🧘',
                'label' => 'Voglio rallentare',
                'tagline' => 'Staccare il pilota automatico e respirare con calma.',
                'recommended_quests' => ['ferma_tutto_60s', 'respiro_60s', 'suono_2min']
            ],
            'respirare' => [
                'id' => 'respirare',
                'emoji' => '🌬️',
                'label' => 'Voglio respirare',
                'tagline' => 'La semplicità del ritmo: espira, pausa, inspira.',
                'recommended_quests' => ['respiro_60s', 'h_plus_respiro', 'radicamento_presente']
            ],
            'connessione' => [
                'id' => 'connessione',
                'emoji' => '❤️',
                'label' => 'Voglio sentirmi più connesso',
                'tagline' => 'Rompere l\'isolamento e ritrovare il calore delle persone.',
                'recommended_quests' => ['scrivi_a_qualcuno', 'partecipa_cerchio', 'tre_carte_gratitudine']
            ],
            'direzione' => [
                'id' => 'direzione',
                'emoji' => '🧭',
                'label' => 'Voglio capire dove sto andando',
                'tagline' => 'Dare senso alle scelte, ai doveri e alla vocazione.',
                'recommended_quests' => ['carte_dharma', 'ikigai_card_game', 'abc_trasformazione']
            ],
            'energia' => [
                'id' => 'energia',
                'emoji' => '⚡',
                'label' => 'Voglio più energia',
                'tagline' => 'Rigenerazione vitale, movimento libero e aria aperta.',
                'recommended_quests' => ['natura_10min', 'movimento_corpo', 'h_plus_respiro']
            ],
            'chiarezza' => [
                'id' => 'chiarezza',
                'emoji' => '🧠',
                'label' => 'Voglio fare chiarezza',
                'tagline' => 'Sbrogliare i pensieri confusi e guardare le cose con lucidità.',
                'recommended_quests' => ['ferma_tutto_60s', 'abc_trasformazione', 'ruota_esplorazione']
            ],
            'conoscere_persone' => [
                'id' => 'conoscere_persone',
                'emoji' => '🤝',
                'label' => 'Voglio conoscere persone',
                'tagline' => 'Una comunità reale e accogliente senza etichette.',
                'recommended_quests' => ['partecipa_cerchio', 'parla_con_un_volontario', 'scrivi_a_qualcuno']
            ],
            'cambiamento' => [
                'id' => 'cambiamento',
                'emoji' => '🎯',
                'label' => 'Voglio cambiare qualcosa',
                'tagline' => 'Un piccolo passo concreto alla volta, senza promesse impossibili.',
                'recommended_quests' => ['abc_trasformazione', 'partecipa_cerchio', 'ruota_esplorazione']
            ],
            'surfare_vita' => [
                'id' => 'surfare_vita',
                'emoji' => '🌊',
                'label' => 'Voglio imparare a surfarmi la vita',
                'tagline' => 'Non puoi fermare le onde, ma puoi imparare a navigarle.',
                'recommended_quests' => ['betterway_surf', 'respiro_60s', 'radicamento_presente']
            ]
        ];
    }

    /**
     * Libreria delle Micro-Missioni (Micro-Quests)
     * Durate suggerite: da 30 secondi a 10 minuti
     */
    public static function getMicroQuests(): array {
        return [
            'ferma_tutto_60s' => [
                'id' => 'ferma_tutto_60s',
                'title' => 'Ferma Tutto (60 Secondi)',
                'duration' => '1 minuto',
                'category' => 'Presenza',
                'source_method' => 'Consapevolezza Somatica',
                'instruction' => 'Fermati esattamente dove sei. Chiudi gli occhi per 60 secondi se ti va. Cosa noti nel tuo corpo adesso?',
                'options' => ['Leggero', 'Neutro', 'Carico / Teso'],
                'reflection' => 'Notare come stai è già il primo atto di cura verso te stesso.'
            ],
            'respiro_60s' => [
                'id' => 'respiro_60s',
                'title' => '60 Secondi di Respiro Calmo',
                'duration' => '1 minuto',
                'category' => 'Respiro',
                'source_method' => 'Pratiche di Respirazione Consapevole',
                'instruction' => 'Espira lentamente... lascia una pausa naturale... inspira dolcemente... ripeti.',
                'notice' => 'Se qualsiasi pratica di respiro ti provoca disagio, interrompila e respira al tuo ritmo naturale.',
                'reflection' => 'Il respiro è il ponte immediato tra la mente e il corpo.'
            ],
            'radicamento_presente' => [
                'id' => 'radicamento_presente',
                'title' => 'Radicamento: Sei Qui',
                'duration' => '90 secondi',
                'category' => 'Radicamento',
                'source_method' => 'Grounding & Mindfulness',
                'instruction' => 'Guardati intorno: nota 1 cosa che vedi chiaramente, 1 suono che ascolti, e il punto di contatto dei piedi con il pavimento.',
                'reflection' => 'Non sei nei tuoi pensieri passati o futuri: sei qui, in questo spazio, al sicuro.'
            ],
            'tre_carte_gratitudine' => [
                'id' => 'tre_carte_gratitudine',
                'title' => 'Le Tre Carte della Gratitudine',
                'duration' => '2 minuti',
                'category' => 'Cuore & Relazione',
                'source_method' => 'Psicologia Positiva & Tradizioni Sapienzali',
                'instruction' => 'Scegli una carta e scrivi mentalmente o su un foglio una sola parola: 1) Una Persona a cui vuoi bene, 2) Una Cosa semplice di oggi, 3) Un Momento di pace.',
                'cards' => ['Una Persona', 'Una Cosa', 'Un Momento'],
                'reflection' => 'La gratitudine non cancella le difficoltà, ma allarga lo spazio della serenità.'
            ],
            'scrivi_a_qualcuno' => [
                'id' => 'scrivi_a_qualcuno',
                'title' => 'Scrivi a una Persona Cara',
                'duration' => '2 minuti (Screen Off -> Life On)',
                'category' => 'Relazione Reale',
                'source_method' => 'Welfare Relazionale',
                'instruction' => 'Pensa a una persona amica o familiare che non senti da un po\'. Mandale un messaggio semplice: "Ciao, ti stavo pensando e volevo mandarti un saluto caloroso".',
                'reflection' => 'Un ponte relazionale si riapre spesso con una sola frase autentica.'
            ],
            'natura_10min' => [
                'id' => 'natura_10min',
                'title' => '10 Minuti Fuori: Aria Aperta',
                'duration' => '10 minuti (Screen Off -> Life On)',
                'category' => 'Natura & Territorio',
                'source_method' => 'Riconnessione Ecologica / Delta del Po',
                'instruction' => 'Esci all\'aperto e cammina per 10 minuti senza cuffie né cellulare in mano. Guarda la luce, gli alberi, il cielo.',
                'reflection' => 'La natura rigenera la capacità di attenzione e abbassa i ritmi interiori.'
            ],
            'ikigai_card_game' => [
                'id' => 'ikigai_card_game',
                'title' => 'Gioco Maieutico dell\'Ikigai',
                'duration' => '5 minuti',
                'category' => 'Vocazione & Scopo',
                'source_method' => 'Tradizione Giapponese Ikigai',
                'instruction' => 'Osserva i 4 cerchi dell\'esperienza: Ciò che ami fare · Ciò in cui hai talento · Ciò che serve agli altri · Ciò che ti dà sostentamento.',
                'reflection' => 'L\'Ikigai non è una formula rigida, ma la ricerca dell\'armonia tra ciò che sei e ciò che offri.'
            ],
            'abc_trasformazione' => [
                'id' => 'abc_trasformazione',
                'title' => 'Metodo ABC: 4 Passi di Trasformazione',
                'duration' => '3 minuti',
                'category' => 'Crescita Personale',
                'source_method' => 'Metodo ABC — Mirco Pregnolato (mircopregnolato.it)',
                'instruction' => '1. Ricerca (cosa sto vivendo?) → 2. Trasformazione (cosa posso cambiare?) → 3. Pianificazione (prossimo micro-passo) → 4. Valorizzazione (cosa ho appreso?).',
                'reflection' => 'Ogni trasformazione complessa è una successione di piccoli passi pianificati con gentilezza.'
            ],
            'h_plus_respiro' => [
                'id' => 'h_plus_respiro',
                'title' => 'Metodo H+: Centratura Somatica',
                'duration' => '3 minuti',
                'category' => 'Benessere Somatico',
                'source_method' => 'Metodo H+ — Mirco Pregnolato (mircopregnolato.it)',
                'instruction' => 'Porta una mano sul petto e una sull\'addome. Ascolta il movimento del respiro sotto i palmi senza forzarlo.',
                'reflection' => 'Riconnettersi al corpo spegne la ruminazione mentale.'
            ],
            'betterway_surf' => [
                'id' => 'betterway_surf',
                'title' => 'Life Surf: Navigare le Onde della Vita',
                'duration' => '2 minuti',
                'category' => 'Mindset & Resilienza',
                'source_method' => 'BetterWay — Mirco Pregnolato (mircopregnolato.it)',
                'instruction' => 'Immagina la difficoltà attuale come un\'onda del mare. Non puoi fermarla con la forza: puoi piegare le ginocchia, respirare e mantener l\'equilibrio.',
                'reflection' => 'Resilienza non è combattere contro la corrente, ma imparare a cavalcarla.'
            ],
            'partecipa_cerchio' => [
                'id' => 'partecipa_cerchio',
                'title' => 'La Comunità Reale: Incontro di 90 Minuti',
                'duration' => '90 minuti settimanali',
                'category' => 'Comunità Multifamiliare',
                'source_method' => 'Metodo Hudolin / Rete Club CAT',
                'instruction' => 'Partecipa a una riunione del Club Alcologico Territoriale. Gratuita, anonima, in cerchio. Non c\'è obbligo di parlare, puoi solo ascoltare.',
                'reflection' => 'Nel cerchio tra pari scopri che la tua storia è accolta e compresa.'
            ]
        ];
    }

    /**
     * Tassonomia rigorosa e attribuzione trasparente delle fonti (Nessun minestrone)
     */
    public static function getMethodTaxonomy(): array {
        return [
            'hudolin' => [
                'name' => 'Metodo Hudolin (Approccio Ecologico-Sociale)',
                'focus' => 'Comunità multifamiliare, Club CAT, sobrietà condivisa, cerchio dei 90 minuti tra pari.',
                'role_in_dependex' => 'Comunità multifamiliare, Club CAT, sobrietà condivisa, cerchio dei 90 minuti tra pari.',
                'attribution' => 'Prof. Vladimir Hudolin (1922-1996) e Rete Nazionale dei Club Alcologici Territoriali.',
                'source' => 'Prof. Vladimir Hudolin (1922-1996) e Rete Nazionale dei Club Alcologici Territoriali.',
                'what_it_is_not' => 'Non è un trattamento medico-ospedaliero né un corso a pagamento.'
            ],
            'h_plus' => [
                'name' => 'Metodo H+ (Benessere Somatico & Bioenergetico)',
                'focus' => 'Consapevolezza corporea, respiro diaframmatico, de-escalation dello stress quotidiano.',
                'role_in_dependex' => 'Consapevolezza corporea, respiro diaframmatico, de-escalation dello stress quotidiano.',
                'attribution' => 'Mirco Pregnolato — Metodo H+ (mircopregnolato.it).',
                'source' => 'Mirco Pregnolato — Metodo H+ (mircopregnolato.it).',
                'what_it_is_not' => 'Non è psicoterapia o medicina convenzionale.'
            ],
            'abc' => [
                'name' => 'Metodo ABC (Ricerca, Trasformazione, Pianificazione, Valorizzazione)',
                'focus' => 'Processo strutturato di micro-obiettivi ed evoluzione personale.',
                'role_in_dependex' => 'Processo strutturato di micro-obiettivi ed evoluzione personale.',
                'attribution' => 'Mirco Pregnolato — Metodo ABC (mircopregnolato.it).',
                'source' => 'Mirco Pregnolato — Metodo ABC (mircopregnolato.it).',
                'what_it_is_not' => 'Non promette scorciatoie o guarigioni miracolose.'
            ],
            'betterway' => [
                'name' => 'BetterWay (6 Pilastri di Crescita)',
                'focus' => 'Lifecoaching, Mindset, Spiritualità laica, Masterclass, Economia etica, Team Building.',
                'role_in_dependex' => 'Lifecoaching, Mindset, Spiritualità laica, Masterclass, Economia etica, Team Building.',
                'attribution' => 'Mirco Pregnolato — BetterWay (mircopregnolato.it).',
                'source' => 'Mirco Pregnolato — BetterWay (mircopregnolato.it).',
                'what_it_is_not' => 'Non è un sostituto del Club Hudolin.'
            ],
            'veda' => [
                'name' => 'Tradizioni Vediche & Orientali (Principi Universali)',
                'focus' => 'Dharma (vocazione/responsabilità), Artha (risorse), Kama (vitalità), Moksha (libertà interiore).',
                'role_in_dependex' => 'Dharma (vocazione/responsabilità), Artha (risorse), Kama (vitalità), Moksha (libertà interiore).',
                'attribution' => 'Filosofia sapienzale e tradizioni orientali universali ad uso etico e di auto-riflessione.',
                'source' => 'Filosofia sapienzale e tradizioni orientali universali ad uso etico e di auto-riflessione.',
                'what_it_is_not' => 'Non è religione confessionale né dottrina imposta.'
            ],
            'maslow' => [
                'name' => 'Modello dei Bisogni di Maslow',
                'focus' => 'Orientamento ai bisogni fondamentali dalla sicurezza alla trascendenza nel servizio.',
                'role_in_dependex' => 'Orientamento ai bisogni fondamentali dalla sicurezza alla trascendenza nel servizio.',
                'attribution' => 'Abraham Maslow rivisitato come mappa di orientamento maieutico.',
                'source' => 'Abraham Maslow rivisitato come mappa di orientamento maieutico.',
                'what_it_is_not' => 'Non è una scala di valore umano.'
            ],
            'ruota_vita' => [
                'name' => 'Ruota della Vita',
                'focus' => 'Fotografia privata a 12 raggi per osservare le aree della propria vita senza giudizio.',
                'role_in_dependex' => 'Fotografia privata a 12 raggi per osservare le aree della propria vita senza giudizio.',
                'attribution' => 'Strumento di coaching e consapevolezza personale non diagnostico.',
                'source' => 'Strumento di coaching e consapevolezza personale non diagnostico.',
                'what_it_is_not' => 'Non è un test diagnostico o pagella personale.'
            ],
            'beway' => [
                'name' => 'BEWAY.LIFE (Viaggi Esperienziali & Territorio)',
                'focus' => 'Riconnessione nella natura, eventi esperienziali nel Delta del Po e rigenerazione.',
                'role_in_dependex' => 'Riconnessione nella natura, eventi esperienziali nel Delta del Po e rigenerazione.',
                'attribution' => 'BEWAY.LIFE — Piattaforma esperienziale e benessere territoriale (beway.life).',
                'source' => 'BEWAY.LIFE — Piattaforma esperienziale e benessere territoriale (beway.life).',
                'what_it_is_not' => 'Non è turismo di massa né sanatorio.'
            ]
        ];
    }

    /**
     * Livelli di Partecipazione alla Piattaforma (NON di Valore Umano)
     */
    public static function getParticipationLevels(): array {
        return [
            1 => ['title' => 'Esploratore', 'desc' => 'Ha iniziato a guardarsi intorno con curiosità.'],
            2 => ['title' => 'Osservatore', 'desc' => 'Ha sperimentato la prima micro-pratica di consapevolezza.'],
            3 => ['title' => 'Praticante', 'desc' => 'Integra piccoli respiri e riflessioni nella quotidianità.'],
            4 => ['title' => 'Partecipante', 'desc' => 'Vive la comunità reale partecipando agli incontri di Club o eventi.'],
            5 => ['title' => 'Contributore', 'desc' => 'Condivide una storia, aiuta la logistica o supporta un pari.'],
            6 => ['title' => 'Facilitatore', 'desc' => 'Si forma come Servitore-Insegnante e accompagna la comunità.']
        ];
    }

    /**
     * Risolve le micro-esperienze consigliate in base a come si sente la persona
     */
    public static function resolveExperienceByFeeling(string $feelingKey): array {
        $entries = self::getFeelingEntries();
        $selected = $entries[$feelingKey] ?? $entries['stare_meglio'];
        $allQuests = self::getMicroQuests();

        $recommended = [];
        foreach ($selected['recommended_quests'] as $qId) {
            if (isset($allQuests[$qId])) {
                $recommended[] = $allQuests[$qId];
            }
        }

        // Tassativamente massimo 3 opzioni per evitare l'overwhelm
        return [
            'feeling' => $selected,
            'quests' => array_slice($recommended, 0, 3)
        ];
    }

    /**
     * THE DEPENDEX COMPASS: Le 9 Dimensioni della Vita attorno all'IO
     * Centro: IO
     * Periferia: Corpo, Mente, Relazioni, Famiglia, Lavoro, Risorse, Comunità, Significato, Territorio
     */
    public static function getCompassDimensions(): array {
        return [
            'corpo' => [
                'id' => 'corpo',
                'name' => 'Corpo',
                'icon' => '🫁',
                'color' => '#00f0ff',
                'guarda' => 'Il corpo è la tua prima casa. Ascolta le sue tensioni senza giudizio.',
                'gioca' => 'Fai un respiro diaframmatico profondo rilasciando le spalle per 60 secondi.',
                'agisci' => 'Bevi un bicchiere d\'acqua con calma o fai una breve camminata a piedi scalzi.'
            ],
            'mente' => [
                'id' => 'mente',
                'name' => 'Mente',
                'icon' => '🧠',
                'color' => '#93c5fd',
                'guarda' => 'I pensieri sono come nuvole nel cielo: passano, non definiscono chi sei.',
                'gioca' => 'Scrivi su un foglio il pensiero più pesante che hai oggi e poi piegalo.',
                'agisci' => 'Concediti 5 minuti di silenzio staccando tutte le notifiche digitali.'
            ],
            'relazioni' => [
                'id' => 'relazioni',
                'name' => 'Relazioni',
                'icon' => '🤝',
                'color' => '#f43f5e',
                'guarda' => 'Siamo esseri relazionali. La solitudine si dissolve nell\'incontro autentico.',
                'gioca' => 'Pensa a una persona che stimi e trova una cosa bella di lei.',
                'agisci' => 'Scrivi o chiama quella persona per dirle semplicemente: "Ti ho pensato".'
            ],
            'famiglia' => [
                'id' => 'famiglia',
                'name' => 'Famiglia',
                'icon' => '🏠',
                'color' => '#fbbf24',
                'guarda' => 'La famiglia è un sistema vivo. Quando uno si muove, tutto il sistema si adatta.',
                'gioca' => 'Oggi ascolta un familiare per 3 minuti consecutivi senza interrompere o dare consigli.',
                'agisci' => 'Condividi un momento sereno a tavola senza schermi accesi.'
            ],
            'lavoro' => [
                'id' => 'lavoro',
                'name' => 'Lavoro & Vocazione',
                'icon' => '💼',
                'color' => '#a78bfa',
                'guarda' => 'Il lavoro acquista valore quando è utile alla comunità e rispettoso della persona.',
                'gioca' => 'Identifica un compito lavorativo fatto bene oggi e riconoscitene il merito.',
                'agisci' => 'Ringrazia un collega o collaboratore per il suo supporto quotidiano.'
            ],
            'risorse' => [
                'id' => 'risorse',
                'name' => 'Risorse & Sostenibilità',
                'icon' => '⚖️',
                'color' => '#34d399',
                'guarda' => 'Le risorse materiali sono strumenti di serenità e servizio, non fini a se stesse.',
                'gioca' => 'Fai una lista delle 3 cose materiali più semplici che oggi ti danno comfort.',
                'agisci' => 'Evita un acquisto impulsivo e destina quell\'energia a una passeggiata.'
            ],
            'comunita' => [
                'id' => 'comunita',
                'name' => 'Comunità & Club',
                'icon' => '👥',
                'color' => '#38bdf8',
                'guarda' => 'Nel cerchio del Club nessuno è solo, nessuno giudica e ogni storia ha dignità.',
                'gioca' => 'Cerca dove si trova il Club Alcologico Territoriale più vicino a te sulla mappa.',
                'agisci' => 'Partecipa all\'incontro settimanale di 90 minuti del Club CAT.'
            ],
            'significato' => [
                'id' => 'significato',
                'name' => 'Significato & Valori',
                'icon' => '✨',
                'color' => '#ffd700',
                'guarda' => 'Il significato della vita non si trova pronto: si costruisce giorno dopo giorno.',
                'gioca' => 'Qual è il valore che vorresti guidasse la tua giornata di oggi?',
                'agisci' => 'Fai una piccola azione concreta coerente con quel valore.'
            ],
            'territorio' => [
                'id' => 'territorio',
                'name' => 'Territorio & Natura',
                'icon' => '🌿',
                'color' => '#4ade80',
                'guarda' => 'La terra del Delta del Po ci ricorda che il fiume trova sempre la strada per il mare.',
                'gioca' => 'Guarda il cielo o gli alberi per 2 minuti osservando i dettagli della luce.',
                'agisci' => 'Fai una camminata all\'aria aperta respirando a pieni polmoni.'
            ]
        ];
    }

    /**
     * I SETTE MONDI DEL WELFARE (Framework simbolico DEPENDEX a 7 dimensioni)
     */
    public static function getSevenWorlds(): array {
        return [
            1 => [
                'id' => 'radicamento',
                'name' => 'Mondo 1 — Radicamento',
                'focus' => 'Casa, sicurezza materiale, stabilità, corpo e territorio.',
                'color' => '#ef4444',
                'icon' => '🌱',
                'practice' => 'Esercizio "Sei Qui": tocca il terreno e stabilisci il tuo appoggio sicuro.'
            ],
            2 => [
                'id' => 'vitalita',
                'name' => 'Mondo 2 — Vitalità',
                'focus' => 'Movimento fluido, piacere sano della vita, creatività corporea.',
                'color' => '#f97316',
                'icon' => '🔥',
                'practice' => 'Movimento libero per 2 minuti lasciando sciogliere le rigidità del bacino e delle braccia.'
            ],
            3 => [
                'id' => 'autonomia',
                'name' => 'Mondo 3 — Autonomia',
                'focus' => 'Scelta consapevole, responsabilità personale, confini sani.',
                'color' => '#eab308',
                'icon' => '⚡',
                'practice' => 'Dì un "no" gentile a una richiesta non urgente per proteggere la tua quiete.'
            ],
            4 => [
                'id' => 'relazione',
                'name' => 'Mondo 4 — Relazione',
                'focus' => 'Famiglia, amicizia sincera, incontro tra pari nel cerchio di comunità.',
                'color' => '#22c55e',
                'icon' => '❤️',
                'practice' => 'Ascolto empatico senza giudizio: ascolta qualcuno senza dare pareri non richiesti.'
            ],
            5 => [
                'id' => 'espressione',
                'name' => 'Mondo 5 — Espressione',
                'focus' => 'Parola autentica, verità interiore, ascolto e creatività comunicativa.',
                'color' => '#06b6d4',
                'icon' => '🗣️',
                'practice' => 'Esprimi chiaramente come ti senti con una persona di cui ti fidi.'
            ],
            6 => [
                'id' => 'consapevolezza',
                'name' => 'Mondo 6 — Consapevolezza',
                'focus' => 'Osservazione limpida, studio, attenzione aperta, senso di coerenza.',
                'color' => '#6366f1',
                'icon' => '👁️',
                'practice' => '2 minuti di silenzio: osserva i suoni della stanza come spettatore sereno.'
            ],
            7 => [
                'id' => 'significato',
                'name' => 'Mondo 7 — Significato',
                'focus' => 'Valori guida, trascendenza, servizio al bene comune e pace interiore.',
                'color' => '#a855f7',
                'icon' => '✨',
                'practice' => 'Fai una piccola azione di volontariato o solidarietà senza aspettarti nulla in cambio.'
            ]
        ];
    }

    /**
     * DAILY DEPENDEX: "Una cosa al giorno"
     * Micro-esperienza del giorno dinamica (mai obbligatoria)
     */
    public static function getDailyExperience(): array {
        $todaySeed = (int)date('z'); // Giorno dell'anno da 0 a 365
        $pool = [
            [
                'title' => 'Un minuto di respiro consapevole',
                'category' => 'Respiro',
                'duration' => '60 secondi',
                'prompt' => 'Oggi concediti un minuto: 4 secondi inspira, 2 secondi pausa, 4 secondi espira.',
                'action_label' => 'Avvia Respiro',
                'target_id' => 'breath-tool'
            ],
            [
                'title' => 'Una parola a una persona cara',
                'category' => 'Relazione',
                'duration' => '2 minuti',
                'prompt' => 'Scrivi un messaggio sincero a qualcuno che non senti da tempo: "Ti ho pensato oggi".',
                'action_label' => 'Scrivi Ora',
                'target_id' => 'gratitude-tool'
            ],
            [
                'title' => 'Dieci minuti di cielo e passi',
                'category' => 'Natura & Territorio',
                'duration' => '10 minuti',
                'prompt' => 'Esci all\'aria aperta senza cuffie: nota tre colori naturali che normalmente ignori.',
                'action_label' => 'Esplora Fuori',
                'target_id' => 'quests-grid-tool'
            ],
            [
                'title' => 'Una piccola gratitudine privata',
                'category' => 'Mindfulness',
                'duration' => '90 secondi',
                'prompt' => 'Scrivi sul tuo dispositivo una cosa semplice accaduta oggi che ti ha fatto piacere.',
                'action_label' => 'Scrivi Carta',
                'target_id' => 'gratitude-tool'
            ],
            [
                'title' => 'Ascolto del cerchio tra pari',
                'category' => 'Comunità Hudolin',
                'duration' => '90 minuti',
                'prompt' => 'Trova il Club più vicino sulla mappa: nel cerchio non ci sono etichette, solo persone.',
                'action_label' => 'Trova Club',
                'target_id' => 'world-club-explorer.php'
            ]
        ];
        $selected = $pool[$todaySeed % count($pool)];
        $selected['date_formatted'] = date('d/m/Y');
        return $selected;
    }

    /**
     * WEEKLY DEPENDEX: "Una settimana, una dimensione"
     */
    public static function getWeeklyTheme(): array {
        $weekNumber = (int)date('W');
        $dimensions = ['Relazione & Ascolto', 'Respiro & Calma Somatica', 'Natura & Territorio', 'Comunità & Famiglia', 'Chiarezza & Significato'];
        $currentTheme = $dimensions[$weekNumber % count($dimensions)];

        return [
            'week_number' => $weekNumber,
            'theme' => $currentTheme,
            'days' => [
                1 => 'Lunedì: Osserva come ti senti all\'inizio della settimana.',
                2 => 'Martedì: Ascolta una persona senza interrompere.',
                3 => 'Mercoledì: Fai una pausa di 60 secondi di respiro diaframmatico.',
                4 => 'Giovedì: Ringrazia per una cosa semplice della giornata.',
                5 => 'Venerdì: Partecipa a un momento comunitario reale.',
                6 => 'Sabato: Cammina 10 minuti all\'aperto senza notifiche.',
                7 => 'Domenica: Rifletti con gratitudine sulla settimana trascorsa.'
            ]
        ];
    }

    /**
     * ONE BUTTON EXPERIENCE: "Inizia Ora"
     * Per chi non sa da dove cominciare: un'azione a sorpresa immediata
     */
    public static function getOneButtonExperience(): array {
        return [
            'title' => 'Inizia Ora · Ferma Tutto per 60 Secondi',
            'prompt' => 'Non devi capire nulla del sito. Fai un respiro profondo, rilassa la fronte e le spalle.',
            'action_url' => 'playground.php#breath-tool',
            'instruction' => 'Segui il cerchio visuale per un solo minuto. Poi decidi liberamente se proseguire.'
        ];
    }

    /**
     * IKIGAI A 4 CARTE: Intersezioni della Vocazione
     */
    public static function getIkigaiCards(): array {
        return [
            'love' => [
                'title' => 'Ciò che amo',
                'desc' => 'Le attività che faresti anche senza ricompensa, che ti fanno dimenticare il tempo.',
                'color' => '#f43f5e'
            ],
            'good_at' => [
                'title' => 'Ciò in cui riesco',
                'desc' => 'I tuoi talenti naturali, le abilità acquisite e ciò che gli altri ti riconoscono.',
                'color' => '#3b82f6'
            ],
            'world_needs' => [
                'title' => 'Ciò di cui il mondo ha bisogno',
                'desc' => 'I bisogni reali della tua comunità, della famiglia e delle persone attorno a te.',
                'color' => '#10b981'
            ],
            'paid_for' => [
                'title' => 'Ciò che può dare valore',
                'desc' => 'Le competenze attraverso cui puoi sostenerti dignitosamente e creare ricchezza etica.',
                'color' => '#f59e0b'
            ]
        ];
    }
}

