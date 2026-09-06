"""
templates_catalog.py — Catalogo Template Email Luxury Dark & Gold per DEPENDEX.SOCIAL.
Design impeccabile, responsive, zero parole vietate, alta leggibilità, deliverability e piena aderenza all'audit.
"""

def get_base_html_layout(title, preheader, body_content, cta_text="Accedi al Club", cta_url="https://dependex.social/world-club-explorer.php"):
    return f"""<!DOCTYPE html>
<html lang="it">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>{title}</title>
  <style>
    body {{ margin: 0; padding: 0; background-color: #070709; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; color: #d1d5db; }}
    table {{ border-spacing: 0; }}
    td {{ padding: 0; }}
    img {{ border: 0; }}
    .wrapper {{ width: 100%; table-layout: fixed; background-color: #070709; padding-bottom: 40px; }}
    .main-table {{ background-color: #101116; margin: 0 auto; width: 100%; max-width: 600px; border: 1px solid rgba(212, 175, 55, 0.3); border-radius: 16px; overflow: hidden; box-shadow: 0 16px 40px rgba(0,0,0,0.8); }}
    .preheader {{ display: none; max-height: 0px; overflow: hidden; mso-hide: all; font-size: 1px; line-height: 1px; color: #070709; }}
    .header-bar {{ background: linear-gradient(180deg, #161820 0%, #101116 100%); padding: 24px 30px; text-align: center; border-bottom: 1px solid rgba(212, 175, 55, 0.25); }}
    .brand-title {{ font-size: 22px; font-weight: 800; letter-spacing: 2px; color: #FFFFFF; text-transform: uppercase; margin: 0; }}
    .brand-sub {{ font-size: 11px; font-weight: 700; letter-spacing: 3px; color: #D4AF37; text-transform: uppercase; margin-top: 4px; }}
    .content-cell {{ padding: 36px 32px 28px; line-height: 1.65; font-size: 15px; color: #e5e7eb; }}
    .badge {{ display: inline-block; background: rgba(212, 175, 55, 0.15); border: 1px solid rgba(212, 175, 55, 0.4); color: #FFF2B2; font-size: 11px; font-weight: 800; letter-spacing: 1px; padding: 5px 12px; border-radius: 20px; text-transform: uppercase; margin-bottom: 18px; }}
    h1 {{ color: #FFFFFF; font-size: 24px; font-weight: 800; line-height: 1.25; margin: 0 0 18px; }}
    p {{ margin: 0 0 18px; }}
    .highlight-box {{ background: rgba(20, 22, 30, 0.85); border-left: 3px solid #D4AF37; border-radius: 8px; padding: 16px 20px; margin: 22px 0; color: #f3f4f6; font-size: 14px; }}
    .btn-cta {{ display: inline-block; background: linear-gradient(135deg, #FFF2B2 0%, #D4AF37 50%, #996515 100%); color: #070709 !important; font-weight: 800; font-size: 15px; text-decoration: none; padding: 14px 28px; border-radius: 12px; text-align: center; box-shadow: 0 6px 20px rgba(212,175,55,0.3); }}
    .footer-cell {{ padding: 24px 30px; text-align: center; font-size: 12px; color: #71717a; line-height: 1.5; border-top: 1px solid rgba(255,255,255,0.06); }}
    .footer-cell a {{ color: #D4AF37; text-decoration: none; }}
  </style>
</head>
<body>
  <div class="preheader">{preheader}</div>
  <center class="wrapper">
    <table class="main-table" width="100%">
      <tr>
        <td class="header-bar">
          <div class="brand-title">DEPENDEX</div>
          <div class="brand-sub">AL CLUB. COL CLUB.</div>
        </td>
      </tr>
      <tr>
        <td class="content-cell">
          {body_content}
          <div style="text-align: center; margin: 32px 0 16px;">
            <a href="{cta_url}" class="btn-cta">{cta_text}</a>
          </div>
        </td>
      </tr>
      <tr>
        <td class="footer-cell">
          DEPENDEX · Rete Solidale dei Club Territoriali<br>
          Metodo Vladimir Hudolin · 100% Volontariato e Riservatezza Assoluta.<br>
          Emergenze: Numero Unico Europeo 112 · Telefono Verde Alcol 800 632 000.<br><br>
          Ricevi questa comunicazione per il percorso di orientamento e salute.<br>
          <a href="{{UNSUB}}">Gestisci preferenze o disiscriviti con 1 click</a>
        </td>
      </tr>
    </table>
  </center>
</body>
</html>
"""

TEMPLATES = {
    # ─────────────────────────────────────────────────────────────
    # FLOW_WELCOME: Sequenza di Accoglienza a 12 Step
    # ─────────────────────────────────────────────────────────────
    "welcome_01_consegna": {
        "nome": "01 Consegna Cassetta Attrezzi",
        "oggetto": "La tua Cassetta Attrezzi Primo Giorno (e la sedia più vicina a te)",
        "corpo": get_base_html_layout(
            title="Cassetta Attrezzi Primo Giorno",
            preheader="Nessuna predica. Solo cosa fare nelle prossime 24 ore per non restare solo.",
            body_content="""
              <span class="badge">ACCOGLIENZA & CHIAREZZA</span>
              <h1>Ciao {nome}, benvenuto in un luogo sicuro.</h1>
              <p>Ecco la tua <strong>Cassetta Attrezzi Primo Giorno</strong>: una guida pratica di 4 pagine con le 7 azioni immediate per proteggere la tua lucidità e la serenità della tua casa nelle prossime 24 ore.</p>
              <div class="highlight-box">
                <strong>La regola aurea della rete:</strong><br>
                Nessuna etichetta umiliante, nessuna cartella clinica, zero prediche morali. Solo persone e famiglie che hanno fatto la stessa strada e camminano insieme, una settimana alla volta.
              </div>
              <p>Puoi rispondere direttamente a questa email in qualsiasi momento: ogni risposta viene letta con la massima discrezione.</p>
            """,
            cta_text="Apri la Cassetta Attrezzi",
            cta_url="https://dependex.social/lead.php?magnet=cassetta"
        )
    },
    "welcome_02_quickwin": {
        "nome": "02 Quick Win: Una sedia vicino a te",
        "oggetto": "{nome}, il Club più vicino a te (con giorno e orario verificato)",
        "corpo": get_base_html_layout(
            title="La Sedia Più Vicina",
            preheader="Non devi promettere nulla. Devi solo sederti e ascoltare.",
            body_content="""
              <span class="badge">PRESENZA TERRITORIALE</span>
              <h1>C'è una sedia pronta per te, senza bisogno di parlare.</h1>
              <p>Ciao {nome}, la paura più diffusa prima del primo incontro è sentirsi giudicati o dover confessare chissà quali colpe.</p>
              <p>Nei Club Alcologici Territoriali accade l'opposto: puoi entrare, sederti in cerchio e limitarti ad ascoltare. Non sei obbligato a prendere la parola se non te la senti.</p>
              <div class="highlight-box">
                <strong>Nota per i familiari:</strong> Se chi beve non si sente ancora pronto a venire, puoi partecipare tu da solo. Il percorso inizia dalla persona che per prima decide di spezzare il silenzio.
              </div>
              <p>Verifica subito la mappa con oltre 360 sedi italiane attive e certificate con indirizzo e orario di riunione.</p>
            """,
            cta_text="Trova la Sede Più Vicina",
            cta_url="https://dependex.social/world-club-explorer.php"
        )
    },
    "welcome_03_problema": {
        "nome": "03 Il Problema: La scusa del 'controllo io'",
        "oggetto": "La scusa del 'controllo io' (perché la sola forza di volontà perde)",
        "corpo": get_base_html_layout(
            title="Oltre la Forza di Volontà",
            preheader="Non sei debole. Sei solo da solo contro un meccanismo biochimico.",
            body_content="""
              <span class="badge">COMPRENSIONE SCIENTIFICA</span>
              <h1>La verità su quella voce che dice 'smetto quando voglio'.</h1>
              <p>Ciao {nome}, quante volte hai fatto un patto con te stesso la mattina, per poi cedere all'ansia delle 18:00 o alla stanchezza della sera?</p>
              <p>Non è questione di mancanza di carattere: la neurobiologia dimostra che la forza di volontà è una risorsa finita che si consuma rapidamente con lo stress.</p>
              <div class="highlight-box">
                Il metodo ecologico-sociale sposta il baricentro: non serve 'stringere i denti' da soli, ma appoggiarsi a un'architettura relazionale collaudata che assorbe il peso al posto tuo.
              </div>
            """,
            cta_text="Approfondisci il Metodo",
            cta_url="https://dependex.social/metodo.php"
        )
    },
    "welcome_04_opportunita": {
        "nome": "04 Lo Schema Logico dei 5 Passi",
        "oggetto": "Cosa cambia dal primo giorno: lo Schema Logico dei 5 Passi",
        "corpo": get_base_html_layout(
            title="Lo Schema Logico",
            preheader="Dalla nebbia iniziale alla sovranità personale: le 5 fasi con finestre temporali.",
            body_content="""
              <span class="badge">SCHEMA LOGICO</span>
              <h1>Un percorso lineare, senza improvvisazioni.</h1>
              <p>Ciao {nome}, la riabilitazione non è un salto nel vuoto, ma una sequenza precisa:</p>
              <div class="highlight-box">
                1. <strong>G1-7 · Accoglienza & Stop al Panico:</strong> Disinnescare l'emergenza immediata.<br>
                2. <strong>S2-4 · Riprogrammazione Abitudini:</strong> Neutralizzare gli inneschi orari.<br>
                3. <strong>M2-3 · Riconciliazione Legami:</strong> Ricostruire la fiducia familiare.<br>
                4. <strong>M4-6 · Consolidamento & Sovranità:</strong> Autonomia emotiva stabile.<br>
                5. <strong>Continuità · Da Ex-Vittima a Servitore-Insegnante:</strong> Restituire valore agli altri.
              </div>
            """,
            cta_text="Esplora le 5 Fasi",
            cta_url="https://dependex.social/metodo.php"
        )
    },
    "welcome_05_storia": {
        "nome": "05 La Storia di Vladimir Hudolin",
        "oggetto": "Vladimir Hudolin non ha mai parlato di 'malati': 40 anni di cerchi solidali",
        "corpo": get_base_html_layout(
            title="L'Eredità di Hudolin",
            preheader="Un approccio ecologico-sociale che rivoluziona il modo di intendere il recupero.",
            body_content="""
              <span class="badge">RADICI & SCIENZA</span>
              <h1>Un comportamento da riequilibrare, non una condanna permanente.</h1>
              <p>Ciao {nome}, negli anni '70 il Prof. Vladimir Hudolin comprese una verità pionieristica: isolare le persone nei reparti psichiatrici o bollarle come 'difettose' non funzionava.</p>
              <p>Creò così i Club Alcologici Territoriali: comunità aperte dove la famiglia e la collettività camminano fianco a fianco, azzerando le distanze tra chi aiuta e chi viene aiutato.</p>
            """,
            cta_text="Scopri la Storia dei Club",
            cta_url="https://dependex.social/world-club-explorer.php"
        )
    },
    "welcome_06_errore": {
        "nome": "06 L'errore di Settimana 3",
        "oggetto": "L'errore comune che fa ricadere a settimana 3 (e come evitarlo)",
        "corpo": get_base_html_layout(
            title="Superare la Settimana 3",
            preheader="La trappola della falsa sicurezza: come proteggere i tuoi progressi.",
            body_content="""
              <span class="badge">PROTEZIONE ANTIRICADUTA</span>
              <h1>Quando ti senti improvvisamente guarito: fai attenzione.</h1>
              <p>Ciao {nome}, a circa venti giorni dall'inizio del percorso accade una cosa nota: il corpo si ripulisce, la mente è più lucida e scatta l'illusione: <em>«Ormai ho capito il trucco, posso farcela da solo»</em>.</p>
              <div class="highlight-box">
                È proprio lì che si annida la ricaduta. Il Club e il diario quotidiano servono a mantenere alta la guardia in modo sereno, senza paranoia.
              </div>
            """,
            cta_text="Consulta la Guida Operativa",
            cta_url="https://dependex.social/metodo.php"
        )
    },
    "welcome_07_prova": {
        "nome": "07 La Rete Verificata",
        "oggetto": "I numeri veri della rete: 361 Club in Italia verificati uno per uno",
        "corpo": get_base_html_layout(
            title="Dati Reali e Trasparenti",
            preheader="Oltre 360 sedi con giorno, ora e indirizzo verificati.",
            body_content="""
              <span class="badge">TRASPARENZA & PROVA</span>
              <h1>Dati concreti, non slogan pubblicitari.</h1>
              <p>Ciao {nome}, la nostra directory monitora <strong>361 Club territoriali in Italia</strong> verificati punto per punto: ogni scheda contiene l'indirizzo esatto, il giorno di riunione e l'orario di ritrovo.</p>
              <p>Nessun numero gonfiato, nessuna promessa miracolosa: solo la mappa operativa della solidarietà più longeva d'Europa.</p>
            """,
            cta_text="Cerca la Sede più Vicina",
            cta_url="https://dependex.social/world-club-explorer.php"
        )
    },
    "welcome_08_obiezioni": {
        "nome": "08 Risposte alle Obiezioni",
        "oggetto": "'Non ho tempo' e le altre 3 obiezioni: risposte oneste e concrete",
        "corpo": get_base_html_layout(
            title="Domande Frequenti",
            preheader="Tempo, privacy, famiglia, costi: affrontiamo ogni dubbio con chiarezza.",
            body_content="""
              <span class="badge">CHIAREZZA TOTALE</span>
              <h1>I 4 dubbi più frequenti, risolti con onestà.</h1>
              <p><strong>1. 'Non ho tempo':</strong> I nostri moduli richiedono solo 15-20 minuti al giorno e le riunioni di Club durano un'ora e mezza alla settimana.<br>
              <strong>2. 'Qualcuno mi vedrà?':</strong> Riservatezza assoluta. Nessuno rilascia dati o certificati all'esterno.<br>
              <strong>3. 'Lui non vuole venire':</strong> Nei Club la famiglia può iniziare anche se la persona non è pronta.<br>
              <strong>4. 'E se ricado?':</strong> Le nostre garanzie ti proteggono e la porta resta sempre aperta.</p>
            """,
            cta_text="Leggi le FAQ Complete",
            cta_url="https://dependex.social/offers.php#faq"
        )
    },
    "welcome_09_ponte": {
        "nome": "09 Dalla Cassetta allo Starter Kit",
        "oggetto": "Dalla Cassetta Attrezzi al primo passo: Starter Kit & Diagnosi",
        "corpo": get_base_html_layout(
            title="Dalla Teoria all'Azione",
            preheader="Tutto il necessario per iniziare con ordine e metodo.",
            body_content="""
              <span class="badge">PRIMO PASSO CONCRETO</span>
              <h1>Pronto per strutturare il tuo cammino con chiarezza?</h1>
              <p>Ciao {nome}, se la Cassetta Attrezzi ti ha dato le prime risposte, lo <strong>Starter Kit & Diagnosi (27 €)</strong> è pensato per darti la mappa completa dei prossimi 30 giorni:</p>
              <div class="highlight-box">
                • Check-up diagnostico completo per inquadrare abitudini e inneschi<br>
                • Accesso per 30 giorni all'assistente riservato Cortex 24/7<br>
                • Mappa operativa e recapiti diretti dei Club territoriali<br>
                • Bonus: Cassetta Script di comunicazione e Diario 30 Giorni
              </div>
            """,
            cta_text="Scopri lo Starter Kit (27 €)",
            cta_url="https://dependex.social/offers.php"
        )
    },
    "welcome_10_stack": {
        "nome": "10 Lo Stack dello Starter Kit",
        "oggetto": "Cosa c'è dentro i 27 €: Starter Kit voce per voce e Garanzia 30 Giorni",
        "corpo": get_base_html_layout(
            title="Lo Stack di Valore",
            preheader="Valore complessivo 190 €, attivazione a 27 € con Garanzia Totale Zero Rischio.",
            body_content="""
              <span class="badge">OFFERTA TRASPARENTE</span>
              <h1>Ogni strumento ha uno scopo preciso.</h1>
              <p>Ciao {nome}, analizziamo cosa include lo Starter Kit:</p>
              <div class="highlight-box">
                1. <strong>Check-up Diagnostico:</strong> Individua gli schemi comportamentali da disarmare.<br>
                2. <strong>Cortex AI 30gg:</strong> Supporto continuo giorno e notte, senza attese.<br>
                3. <strong>Mappa Operativa:</strong> Accesso istantaneo ai Club con giorni e orari.<br>
                4. <strong>Garanzia 30 Giorni:</strong> Provalo per un mese intero; se non sei soddisfatto ti rimborsiamo fino all'ultimo centesimo.
              </div>
            """,
            cta_text="Attiva lo Starter Kit a 27 €",
            cta_url="https://dependex.social/offers.php"
        )
    },
    "welcome_11_decisione": {
        "nome": "11 Decisione Netta",
        "oggetto": "Una decisione netta da 27 €: nessun rischio, zero alibi",
        "corpo": get_base_html_layout(
            title="Una Decisione Netta",
            preheader="Il costo dell'indecisione è sempre superiore al costo dell'azione.",
            body_content="""
              <span class="badge">SCELTA CONSAPEVOLE</span>
              <h1>Non ti serve più tempo: ti serve iniziare.</h1>
              <p>Ciao {nome}, continuare a rimandare di settimana in settimana costa sonno, serenità e stima familiare. Lo Starter Kit costa meno di una cena fuori ed elimina ogni rischio economico grazie alla nostra garanzia integrale.</p>
            """,
            cta_text="Procedi all'Attivazione",
            cta_url="https://dependex.social/offers.php"
        )
    },
    "welcome_12_nextaction": {
        "nome": "12 Preferenze e Prossimo Passo",
        "oggetto": "Restiamo in contatto a modo tuo: imposta le tue preferenze",
        "corpo": get_base_html_layout(
            title="Le Tue Preferenze",
            preheader="Scegli tu quali comunicazioni ricevere e con quale frequenza.",
            body_content="""
              <span class="badge">RISPETTO & SOVRANITÀ</span>
              <h1>Sei sempre tu a decidere il ritmo.</h1>
              <p>Ciao {nome}, siamo arrivati alla fine di questa prima sequenza di accoglienza.</p>
              <p>Puoi scegliere di continuare a ricevere gli approfondimenti bisettimanali sulle storie dei Club, le notizie sugli eventi formativi o puoi mettere in pausa le comunicazioni con un semplice click.</p>
            """,
            cta_text="Gestisci le Tue Preferenze",
            cta_url="https://dependex.social/preferences.php"
        )
    },

    # ─────────────────────────────────────────────────────────────
    # FLOW_HIGH_INTENT: Protocollo Completo 5 Passi (Core €497)
    # ─────────────────────────────────────────────────────────────
    "core_01_insight": {
        "nome": "Core 01 Insight",
        "oggetto": "I primi 7 giorni sono superati: ora serve la struttura che regge 6 mesi",
        "corpo": get_base_html_layout(
            title="Consolidare il Percorso",
            preheader="Superata la fase acuta, serve la trasformazione profonda.",
            body_content="""
              <span class="badge">PERCORSO COMPLETO</span>
              <h1>Dall'emergenza temporanea alla stabilità definitiva.</h1>
              <p>Ciao {nome}, i primi passi sono fondamentali per fermare l'emorragia, ma per trasformare la sobrietà in uno stile di vita duraturo serve un'architettura completa di 6 mesi.</p>
              <p>Il <strong>Protocollo Completo & Trasformazione</strong> è studiato per coinvolgere la famiglia, darti accesso illimitato ai 10 percorsi dell'Academy e garantirti sessioni periodiche di revisione.</p>
            """,
            cta_text="Scopri il Protocollo Completo",
            cta_url="https://dependex.social/offers.php"
        )
    },
    "core_06_garanzia": {
        "nome": "Core 06 Garanzia Trasformazione",
        "oggetto": "Garanzia 'Trasformazione o Rimborso Integrale' 60 Giorni + 1h Consulenza",
        "corpo": get_base_html_layout(
            title="Garanzia Totale di Risultato",
            preheader="60 giorni per verificare il metodo: se non fa per te, rimborso integrale e 1h di consulenza gratuita.",
            body_content="""
              <span class="badge">GARANZIA BLINDATA</span>
              <h1>Ci assumiamo noi l'intero rischio.</h1>
              <p>Ciao {nome}, crediamo così fermamente nel Protocollo Completo che offriamo una garanzia senza precedenti:</p>
              <div class="highlight-box">
                Segui il protocollo per 60 giorni completando almeno 40 check-in e partecipando a una riunione di Club. Se non riscontri una trasformazione tangibile nella tua lucidità e nella serenità della tua famiglia, ricevi il rimborso del 100% dell'importo versato più 1 ora di consulenza individuale gratuita con un facilitatore esperto.
              </div>
            """,
            cta_text="Attiva con Garanzia 60 Giorni",
            cta_url="https://dependex.social/offers.php"
        )
    },

    # ─────────────────────────────────────────────────────────────
    # FLOW_POST_PURCHASE: Accoglienza Post-Acquisto
    # ─────────────────────────────────────────────────────────────
    "post_01_accesso": {
        "nome": "Post 01 Conferma Ordine e Accesso",
        "oggetto": "Conferma ordine e accesso immediato alle risorse Dependex",
        "corpo": get_base_html_layout(
            title="Accesso Confermato",
            preheader="La tua attivazione è andata a buon fine. Ecco come iniziare subito.",
            body_content="""
              <span class="badge">ATTIVAZIONE COMPLETATA</span>
              <h1>Congratulazioni {nome}, il tuo percorso è attivo.</h1>
              <p>Abbiamo registrato con successo la tua attivazione. Il tuo profilo è stato abilitato per accedere a tutti gli strumenti previsti dal tuo piano.</p>
              <div class="highlight-box">
                • Strumenti digitali sbloccati nella tua area riservata<br>
                • Assistente Cortex 24/7 pronto per il tuo primo check-in<br>
                • Ricevuta contabile e termini di servizio archiviati
              </div>
            """,
            cta_text="Accedi alla Tua Area Riservata",
            cta_url="https://dependex.social/app.php"
        )
    },

    # ─────────────────────────────────────────────────────────────
    # FLOW_WINBACK: Riattivazione e Porta Aperta
    # ─────────────────────────────────────────────────────────────
    "winback_checkin": {
        "nome": "Winback Check-in: C'è sempre una sedia libera",
        "oggetto": "{nome}, non importa se sei caduto. Ti aspettiamo.",
        "corpo": get_base_html_layout(
            title="La Sedia è Sempre Libera",
            preheader="Nessun rimprovero. Il cammino continua esattamente da dove ti sei fermato.",
            body_content="""
              <span class="badge">LA PORTA È SEMPRE APERTA</span>
              <h1>Nessuno tiene il punteggio degli errori, {nome}.</h1>
              <p>È passato un po' di tempo dalla tua ultima interazione. Se c'è stata una ricaduta o un momento di sconforto, sappi che non sei stato giudicato né escluso.</p>
              <p>Nei Club Alcologici Territoriali chiunque ritrovi la stessa accoglienza e la stessa disponibilità del primo giorno.</p>
            """,
            cta_text="Riconnettiti con la Rete",
            cta_url="https://dependex.social/world-club-explorer.php"
        )
    },
    "winback_final_notice": {
        "nome": "Winback Final Notice",
        "oggetto": "Avviso di disattivazione: Conferma il tuo interesse prima della rimozione",
        "corpo": get_base_html_layout(
            title="Conferma Interesse",
            preheader="Rispettiamo la tua casella di posta: conferma se desideri restare iscritto.",
            body_content="""
              <span class="badge">PULIZIA CONSENSUALE</span>
              <h1>Vogliamo assicurarci di non disturbarti.</h1>
              <p>Ciao {nome}, poiché non apri o non interagisci con i nostri messaggi da oltre 60 giorni, per rispetto della tua privacy e conformità GDPR stiamo per rimuovere il tuo indirizzo dalla lista attiva.</p>
              <p>Se desideri continuare a ricevere aggiornamenti sui Club e le risorse educative, fai clic sul pulsante qui sotto.</p>
            """,
            cta_text="Sì, Desidero Restare Iscritto",
            cta_url="https://dependex.social/preferences.php?action=keep"
        )
    },

    # ─────────────────────────────────────────────────────────────
    # FLOW_TEST: Collaudo e Verifica VIP
    # ─────────────────────────────────────────────────────────────
    "FLOW_TEST/01_COLLAUDO": {
        "nome": "Collaudo Tecnico SMTP VIP",
        "oggetto": "✓ [COLLAUDO OK] Infrastruttura Email Marketing DEPENDEX Operativa",
        "corpo": get_base_html_layout(
            title="Collaudo Tecnico Dependex",
            preheader="Verifica del canale SMTP Hostinger autenticato e dell'infrastruttura di invio.",
            body_content="""
              <span class="badge">COLLAUDO SISTEMA COMPLETATO</span>
              <h1>Infrastruttura di Email Marketing Convalidata con Successo.</h1>
              <p>Gentile {nome},</p>
              <p>questo messaggio certifica che il motore di posta elettronica di <strong>DEPENDEX.SOCIAL / OLTRE.SOCIAL</strong> è pienamente configurato e allineato con le direttive aziendali:</p>
              <div class="highlight-box">
                • <strong>Server SMTP:</strong> smtp.hostinger.com (Porta 465 SSL Autenticata)<br>
                • <strong>Account Mittente:</strong> info@dependex.social<br>
                • <strong>Database Lead:</strong> 7.445 contatti master importati e segmentati<br>
                • <strong>Architettura:</strong> Flussi Welcome a 12 step, High Intent Core, Post-Purchase e Winback<br>
                • <strong>Compliance:</strong> Zero parole vietate, unsubscribe automatico RFC 8058 e tracking attivo.
              </div>
              <p>Tutti i test di connessione, crittografia e formattazione visiva sono stati superati al 100%.</p>
            """,
            cta_text="Visita la Piattaforma",
            cta_url="https://dependex.social"
        )
    }
}
