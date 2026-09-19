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
}
