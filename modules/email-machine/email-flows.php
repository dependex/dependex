<?php
/* ============================================================================
   DR EMAIL FLOWS — flussi da 12 email ciascuno (voce Destino Randagio)

   Regole di voce (da 05_MARKETING_ED_EMAIL): il marketing del Branco ATTRAE,
   non interrompe. Niente "compra subito", niente urgenza falsa, niente
   promesse di guadagno o "investi". CTA ammesse: Ascolta · Scopri · Continua
   la storia · Entra nel Branco · Lascia la tua voce · La scelta resta tua.

   Ogni flusso e una lista ordinata di 12 email: ['ogg'=>..., 'corpo'=>...].
   Placeholder: {nome} {link} {festa} {sconto}. Il corpo e testo/markdown-lite;
   dr_flow_html() lo avvolge nel template email del sito se presente.

   Cadenza consigliata (giorni dall'ingresso nel flusso), la applica cron_emails:
     welcome/nurture: [0,2,4,7,10,14,18,23,28,34,41,50]
     kit_onboarding : [0,1,3,6,10,15,21,30,45,60,75,90]
     winback        : [0,3,7,12,18,25,33,42,52,63,75,90]
============================================================================ */

/* ESTENSIONE stagionale/ricorrente (AGGIUNGE flussi, non tocca gli esistenti):
   dr_email_flows_stagionali(), dr_flow_cadenze_stagionali(), dr_flow_cta_stagionali(),
   dr_stagione_meteo(), dr_mese_it(). Caricata qui cosi' e' visibile ovunque
   email-flows.php e' incluso (mailer.php in primis). */
if(is_file(__DIR__.'/email-flows-stagionali.php')) require_once __DIR__.'/email-flows-stagionali.php';

/* ESTENSIONE extra (AGGIUNGE flussi, non tocca gli esistenti):
   dr_email_flows_extra(), dr_flow_cadenze_extra(), dr_flow_cta_extra().
   Stesso identico aggancio degli stagionali. */
if(is_file(__DIR__.'/email-flows-extra.php')) require_once __DIR__.'/email-flows-extra.php';

/* ESTENSIONE webinar (flussi per i registrati al webinar 6 set):
   dr_email_flows_webinar(), dr_flow_cadenze_webinar(), dr_flow_cta_webinar().
   Stesso identico aggancio di extra/stagionali. */
if(is_file(__DIR__.'/email-flows-webinar.php')) require_once __DIR__.'/email-flows-webinar.php';

function dr_email_flows(){
  return [

  /* ===================================================================== 1
     WELCOME — chi si iscrive/lead, non ancora membro. Obiettivo: far
     innamorare del mondo DR e portare al KIT del Branco. */
  'welcome' => [
   ['ogg'=>'Benvenuto nel Delta, {nome}',
    'corpo'=>"Ciao {nome}, sono Destino Randagio.\nNon sei finito qui per caso: il Branco chiama chi si sente un po' randagio.\nNei prossimi giorni ti racconto da dove vengo, la musica che cura e cos'e davvero il Branco.\nOggi solo questo: ascolta il primo capitolo. {link}\nLa scelta resta tua."],
   ['ogg'=>'Da dove vengo (Capitolo I)',
    'corpo'=>"Il Po, la nebbia, il fango. Sono nato dal Delta e dalla voglia di non restare indietro.\nDestino Randagio non e un cantante: e un modo di stare al mondo.\nScopri la storia, con calma. {link}"],
   ['ogg'=>'Il lupo e il fiume (Capitolo II)',
    'corpo'=>"Un lupo solo non sopravvive all'inverno. Un branco si.\nEcco perche esiste Il Branco: una famiglia che si sceglie.\nContinua la storia. {link}"],
   ['ogg'=>'La musica che cura',
    'corpo'=>"Ogni album e un capitolo. NOMADS, D.E.L.T.A., e quelli che verranno.\nMettiti le cuffie e scegli il tuo. {link}\nSe una canzone ti resta dentro, e gia iniziato qualcosa."],
   ['ogg'=>'Una canzone scritta per te',
    'corpo'=>"C'e chi ci ha chiesto una canzone su misura: per una persona, un momento, un addio.\nLa scrivo io, sulla tua storia. Scopri come. {link}\nNessuna fretta: la scelta resta tua."],
   ['ogg'=>'Cosa puoi indossare del Delta',
    'corpo'=>"Il wear del Branco non e merchandising: e un segno di riconoscimento.\nDai un'occhiata a wear e gadget. {link}"],
   ['ogg'=>'Il KIT del Branco: la porta',
    'corpo'=>"Nel Branco si entra in un modo solo: col KIT.\nDentro c'e roba vera + lo status di Membro: giochi, DRX, NFT, Experience, la Family.\nScopri cosa contiene. {link}"],
   ['ogg'=>'Cosa cambia quando entri',
    'corpo'=>"Da fuori vedi un artista. Da dentro vivi una community: ranghi, missioni, il tuo Branco che cresce.\nTi mostro com'e dentro. {link}"],
   ['ogg'=>'Le voci del Branco',
    'corpo'=>"Non credere a me: ascolta chi c'e gia.\nStorie vere di chi ha trovato il suo posto nel Delta. {link}"],
   ['ogg'=>'Gli NFT del Branco',
    'corpo'=>"Le opere del Delta diventano collezione: pezzi unici, alcuni introvabili.\nGuarda la galleria. {link}\nNiente promesse di guadagno: qui si colleziona per appartenenza, non per speculare."],
   ['ogg'=>'Perche adesso',
    'corpo'=>"Il Branco e piccolo, e questo e il bello: chi entra ora costruisce.\nSe senti che e il tuo momento, la porta e il KIT. {link}"],
   ['ogg'=>'La scelta resta tua, {nome}',
    'corpo'=>"Ti ho raccontato il Delta. Ora decidi tu.\nSe vuoi entrare nel Branco, ti aspetto col KIT. {link}\nSe non e il momento, resta comunque: continueremo a raccontarti.\n— Destino Randagio"],
  ],

  /* ===================================================================== 2
     KIT ONBOARDING — dopo l'acquisto del KIT. Obiettivo: attivazione,
     primo valore, abitudine, upgrade e referral. */
  'kit_onboarding' => [
   ['ogg'=>'Sei nel Branco, {nome}',
    'corpo'=>"Benvenuto, fratello. Da oggi non sei un cliente: sei un Membro.\nEntra nella tua area e sistema il profilo. {link}\nTi guido io, passo dopo passo."],
   ['ogg'=>'Il primo passo: il tuo wallet',
    'corpo'=>"Nel Branco si vive di DRX: li guadagni con le azioni e li spendi in sconti e premi.\nApri il wallet e prendi i primi DRX. {link}"],
   ['ogg'=>'Gioca (e vinci davvero)',
    'corpo'=>"La sala giochi non e un passatempo: quello che vinci finisce nel tuo wallet.\nProva la Ruota del Destino. {link}"],
   ['ogg'=>'Il tuo rango',
    'corpo'=>"Nel Branco si sale: da Randagio fino a Leggenda del Delta.\nGuarda a che punto sei e cosa sblocchi salendo. {link}"],
   ['ogg'=>'Le missioni del mese',
    'corpo'=>"Ogni mese ci sono missioni: piccole azioni che danno DRX e ti fanno crescere.\nApri le missioni aperte. {link}"],
   ['ogg'=>'Invita e fai crescere il tuo Branco',
    'corpo'=>"Il modo piu veloce per crescere e portare altri randagi.\nEcco il tuo link personale: condividilo dove vuoi. {link}\nOgni persona che entra fa piu forte il tuo Branco."],
   ['ogg'=>'Colleziona il Delta (NFT)',
    'corpo'=>"Da Membro puoi accedere agli NFT del Branco, anche i pezzi gated.\nGuarda cosa puoi collezionare. {link}"],
   ['ogg'=>'La tua musica',
    'corpo'=>"Album, uscite, e la canzone su misura: da dentro hai accesso a tutto.\nScegli il prossimo capitolo. {link}"],
   ['ogg'=>'Le Experience di viaggio',
    'corpo'=>"Il Branco esce anche dallo schermo: le rotte di Stray Nomads.\nScopri la prossima. {link}"],
   ['ogg'=>'La DAO: la tua voce conta',
    'corpo'=>"Nel Branco si decide insieme. Entra nella DAO e vota le prossime scelte. {link}"],
   ['ogg'=>'Fai salire il tuo livello',
    'corpo'=>"Se il Branco ti sta piacendo, c'e un gradino sopra: piu vantaggi, piu voce.\nGuarda gli upgrade di membership. {link}\nLa scelta resta tua."],
   ['ogg'=>'Un mese dentro, {nome}',
    'corpo'=>"Sei nel Branco da un po'. Guarda quanto sei cresciuto: DRX, rango, il tuo Branco.\nContinua cosi. {link}\nUn randagio non resta mai indietro. — Destino Randagio"],
  ],

  /* ===================================================================== 3
     NURTURE NFT — interessati agli NFT ma non hanno ancora collezionato. */
  'nurture_nft' => [
   ['ogg'=>'Cos\'e un NFT del Branco (davvero)',
    'corpo'=>"Dimentica la speculazione. Un NFT del Branco e un pezzo del Delta che possiedi davvero.\nGuarda la galleria, senza impegno. {link}"],
   ['ogg'=>'Dal fango alle stelle',
    'corpo'=>"Ogni opera nasce da un'immagine e diventa un video animato con cornice oro e corona.\nGuarda come sono fatte. {link}"],
   ['ogg'=>'I tier: dal Comune al Genesis',
    'corpo'=>"Comuni, Epici, Leggendari e i rarissimi Genesis. Piu sali, piu il pezzo e unico.\nScopri i livelli. {link}"],
   ['ogg'=>'L\'Unicorno',
    'corpo'=>"C'e un pezzo che esce una volta sola, senza prezzo: si trova solo alla Ruota.\nTi racconto la sua storia. {link}"],
   ['ogg'=>'A cosa serve possederne uno',
    'corpo'=>"Non e solo un'immagine: alcuni NFT sbloccano staking in DRX, accessi e vantaggi nel Branco.\nGuarda cosa fanno. {link}\nNessuna promessa di rendimento: e appartenenza, non finanza."],
   ['ogg'=>'Le collezioni stagionali',
    'corpo'=>"Ci sono pezzi che escono solo alle feste: Natale, Pasqua, Halloween. Limited, e poi spariscono.\nGuarda quali sono in arrivo. {link}"],
   ['ogg'=>'Come si mintano (facile)',
    'corpo'=>"Niente gerghi: ti spiego passo passo come si prende un NFT del Branco.\nLeggi la guida. {link}"],
   ['ogg'=>'Sono sicuri?',
    'corpo'=>"Rete Polygon, wallet tuo, tutto verificabile. Ti spiego come funziona in parole semplici. {link}"],
   ['ogg'=>'Le voci di chi colleziona',
    'corpo'=>"Chi ha gia un pezzo del Delta racconta perche. {link}"],
   ['ogg'=>'Il modo giusto per entrare',
    'corpo'=>"Molti pezzi migliori sono riservati ai Membri del Branco.\nCol KIT sblocchi tutto. {link}"],
   ['ogg'=>'Il tuo primo pezzo',
    'corpo'=>"Se uno ti e rimasto in mente, e quello giusto.\nGuardalo di nuovo. {link}\nLa scelta resta tua."],
   ['ogg'=>'Un pezzo di Delta, per sempre',
    'corpo'=>"Gli album passano, i pezzi restano.\nScegli il tuo. {link}\n— Destino Randagio"],
  ],

  /* ===================================================================== 4
     NURTURE MUSICA/ALBUM — ascoltatori, verso album e canzone su misura. */
  'nurture_musica' => [
   ['ogg'=>'La colonna sonora del Delta',
    'corpo'=>"Ogni album e un capitolo di una storia sola: la mia, e forse un po' la tua.\nInizia da dove vuoi. {link}"],
   ['ogg'=>'NOMADS',
    'corpo'=>"Dodici tracce per chi non trova pace in un posto solo.\nAscolta NOMADS. {link}"],
   ['ogg'=>'D.E.L.T.A.',
    'corpo'=>"Dove l'acqua incontra il mare, finisce una cosa e ne inizia un'altra.\nScopri D.E.L.T.A. {link}"],
   ['ogg'=>'La canzone che ti somiglia',
    'corpo'=>"A volte una canzone gia scritta non basta. Serve la tua.\nTe la scrivo io, sulla tua storia. {link}"],
   ['ogg'=>'Un regalo che non si dimentica',
    'corpo'=>"Una canzone su misura per una persona: un compleanno, un amore, un addio.\nScopri come funziona. {link}"],
   ['ogg'=>'Come nasce una traccia',
    'corpo'=>"Ti porto dietro le quinte: come nasce una canzone del Delta. {link}"],
   ['ogg'=>'Ascolta col Branco',
    'corpo'=>"La musica e piu bella condivisa. Nel Branco si ascolta insieme, si commenta, si vive.\nGuarda com'e dentro. {link}"],
   ['ogg'=>'Le uscite in anteprima',
    'corpo'=>"I Membri sentono le uscite prima di tutti.\nEcco come averle in anteprima. {link}"],
   ['ogg'=>'La tua playlist del Delta',
    'corpo'=>"Segui Destino Randagio dove ascolti musica: Spotify, Apple, YouTube. {link}"],
   ['ogg'=>'Indossa la tua canzone',
    'corpo'=>"Dall'album al wear: porta con te il capitolo che ti ha colpito. {link}"],
   ['ogg'=>'Il capitolo che ti e rimasto',
    'corpo'=>"C'e una traccia che ti e entrata dentro. Riascoltala. {link}\nLa scelta resta tua."],
   ['ogg'=>'La storia continua',
    'corpo'=>"Ogni album chiama il prossimo. E il Branco cammina.\nEntra e continua la storia. {link}\n— Destino Randagio"],
  ],

  /* ===================================================================== 5
     WIN-BACK — membership scaduta o inattivi da tempo. Tono caldo, mai colpa. */
  'winback' => [
   ['ogg'=>'Ci sei mancato, {nome}',
    'corpo'=>"Il Branco non dimentica chi ne ha fatto parte.\nLa porta e ancora aperta, quando vuoi. {link}"],
   ['ogg'=>'Cos\'e cambiato mentre non c\'eri',
    'corpo'=>"Nuovi giochi, nuove uscite, nuove Experience.\nGuarda cosa ti sei perso (e cosa puoi ancora prendere). {link}"],
   ['ogg'=>'I tuoi DRX ti aspettano',
    'corpo'=>"Il tuo wallet e ancora li, con quello che avevi.\nRientra e riprendi da dove eri. {link}"],
   ['ogg'=>'Il tuo rango non e sparito',
    'corpo'=>"Quello che hai costruito resta. Basta poco per rimetterti in cammino. {link}"],
   ['ogg'=>'Il tuo Branco senza di te',
    'corpo'=>"Le persone che hai portato sono ancora qui.\nTorna a guidarle. {link}"],
   ['ogg'=>'Una porta piu leggera',
    'corpo'=>"Rientrare e semplice: riattivi e sei di nuovo dentro, con tutto.\nEcco come. {link}"],
   ['ogg'=>'Le voci di chi e tornato',
    'corpo'=>"Non sei l'unico ad essersi allontanato. Ascolta chi e rientrato. {link}"],
   ['ogg'=>'La stagione giusta',
    'corpo'=>"Se c'e un momento per tornare, e adesso: il Branco e in movimento.\nGuarda cosa bolle. {link}"],
   ['ogg'=>'Un pensiero per te',
    'corpo'=>"Come Membro di ritorno, ti riservo un ingresso morbido.\nDettagli qui. {link}"],
   ['ogg'=>'Il Delta non ti ha dimenticato',
    'corpo'=>"La musica va avanti, ma il tuo posto e rimasto vuoto.\nRiprendilo. {link}"],
   ['ogg'=>'Nessuna fretta, {nome}',
    'corpo'=>"Non ti spingo. Ti dico solo che qui c'e ancora casa.\nLa scelta resta tua. {link}"],
   ['ogg'=>'La porta resta aperta',
    'corpo'=>"Chiudo qui, ma la porta no.\nQuando vuoi, il Branco ti riaccoglie. {link}\n— Destino Randagio"],
  ],

  /* ===================================================================== 6
     LANCIO ALBUM — arruolati quando esce un album nuovo (watcher).
     Arco: curiosita -> identita -> ascolto -> prova -> scarsita vera (tiratura)
     -> sorpresa. Placeholder extra: {album}. Prezzi reali album 24,90-29,90. */
  'lancio_album' => [
   ['ogg'=>'Sta per uscire qualcosa dal Delta',
    'corpo'=>"{nome}, c'e un nuovo capitolo in arrivo: {album}.\nNasce dallo stesso fango di sempre, ma suona diverso.\nTieni le cuffie pronte. {link}"],
   ['ogg'=>'La storia dietro {album}',
    'corpo'=>"Ogni album parte da un momento vero. Questo e nato da una notte che non passava.\nTi racconto da dove viene, prima che lo ascolti. {link}"],
   ['ogg'=>'Un primo assaggio',
    'corpo'=>"Non ti faccio aspettare a mani vuote: ecco un pezzo di {album} in anteprima.\nSe una nota ti resta dentro, e gia iniziato qualcosa. {link}"],
   ['ogg'=>'{album} e fuori. Ascoltalo ora',
    'corpo'=>"Ci siamo: {album} e uscito.\nMettiti comodo e ascoltalo dall'inizio alla fine, come va ascoltato. {link}"],
   ['ogg'=>'Dentro l\'album',
    'corpo'=>"Ogni traccia e una stanza. Ti apro le porte una a una: temi, parole, perche.\nScopri cosa c'e dentro {album}. {link}"],
   ['ogg'=>'Le prime voci del Branco',
    'corpo'=>"Chi l'ha gia ascoltato ha iniziato a raccontarlo. Storie vere, non recensioni finte.\nLeggi cosa dice il Branco. {link}"],
   ['ogg'=>'L\'edizione fisica di {album}',
    'corpo'=>"C'e chi vuole tenerlo in mano. Per loro esiste l'edizione fisica a tiratura limitata.\nQuando le copie finiscono, non si ristampano. {link}"],
   ['ogg'=>'La finestra dell\'edizione si chiude',
    'corpo'=>"L'edizione fisica di {album} resta aperta solo dentro la sua finestra.\nNiente conto alla rovescia finto: e una tiratura vera, e quando e chiusa e chiusa. {link}\nLa scelta resta tua."],
   ['ogg'=>'Un extra, solo per chi c\'era',
    'corpo'=>"Per chi ha ascoltato {album} fino in fondo ho lasciato qualcosa in piu: un dietro le quinte del Delta.\nGuardalo. {link}"],
   ['ogg'=>'Gli album, dal Branco, prima di tutti',
    'corpo'=>"Nel Branco le uscite si sentono in anteprima, sempre.\nSe {album} ti e entrato dentro, dentro il Branco succede a ogni album. {link}"],
   ['ogg'=>'Porta {album} con te',
    'corpo'=>"Dall'ascolto al wear: il capitolo che ti ha colpito puoi anche indossarlo, o farne la tua canzone su misura (29,90).\nScegli come tenertelo. {link}"],
   ['ogg'=>'Fai ascoltare {album} a un randagio',
    'corpo'=>"Se un pezzo ti e rimasto, c'e qualcuno che deve sentirlo.\nGiralo a chi si sente un po' randagio come te. {link}\n— Destino Randagio"],
  ],

  /* ===================================================================== 7
     LANCIO PRODOTTO — drop Printful/merch (watcher).
     Arco promo onesto: semina -> valore -> vendi. Sconto membro REALE in euro,
     mai sotto costo (guardia margine dr-feste). Placeholder extra: {prodotto}. */
  'lancio_prodotto' => [
   ['ogg'=>'Sta arrivando un pezzo nuovo',
    'corpo'=>"{nome}, il Branco sta per tirare fuori qualcosa: {prodotto}.\nNon merchandising: un segno di riconoscimento. {link}"],
   ['ogg'=>'La storia di {prodotto}',
    'corpo'=>"Ogni pezzo del Branco porta un pezzo di Delta addosso.\nTi racconto cosa c'e dietro {prodotto} prima che lo veda. {link}"],
   ['ogg'=>'Primo sguardo',
    'corpo'=>"Ecco {prodotto} da vicino: come e fatto, come sta addosso.\nDagli un'occhiata. {link}"],
   ['ogg'=>'{prodotto} e disponibile',
    'corpo'=>"Da adesso lo puoi prendere.\nGuarda taglie e varianti e scegli il tuo. {link}"],
   ['ogg'=>'Di cosa e fatto',
    'corpo'=>"Materiali, vestibilita, cura: niente sorprese. Ti dico tutto quello che c'e da sapere.\nLeggi i dettagli di {prodotto}. {link}"],
   ['ogg'=>'Come lo porta il Branco',
    'corpo'=>"Non credere a me: guarda come lo indossa chi c'e gia.\nScorri le foto del Branco. {link}"],
   ['ogg'=>'Il tuo prezzo da Membro',
    'corpo'=>"Da Membro del Branco {prodotto} ti costa meno, davvero, in euro: lo sconto e reale e non scende mai sotto il giusto.\nGuarda il tuo prezzo. {link}"],
   ['ogg'=>'La finestra del drop',
    'corpo'=>"Questo drop vive dentro la sua finestra: e un'edizione, non uno scaffale infinito.\nQuando la finestra si chiude, si chiude per davvero. {link}\nLa scelta resta tua."],
   ['ogg'=>'Come arriva a casa',
    'corpo'=>"Stampa su ordine, spedizione tracciata, reso semplice.\nEcco come funziona quando ordini {prodotto}. {link}"],
   ['ogg'=>'Il pezzo esclusivo del Branco',
    'corpo'=>"Alcune cose le puo prendere solo chi e dentro. Non e un vezzo: e appartenenza vera.\nGuarda cosa e riservato al Branco. {link}"],
   ['ogg'=>'Completa il tuo segno',
    'corpo'=>"Un pezzo chiama l'altro: {prodotto} sta bene con il resto del wear del Delta.\nCompleta il tuo look del Branco. {link}"],
   ['ogg'=>'Mostralo, tagga il Branco',
    'corpo'=>"Quando ti arriva, faccelo vedere: taggaci e finisci nella galleria del Branco.\nIndossa il Delta e mostralo. {link}\n— Destino Randagio"],
  ],

  /* ===================================================================== 8
     REFERRAL / PASSAPAROLA — per i Membri. Reciprocita: DRX di benvenuto a
     ENTRAMBI; il reward che scala arriva solo su ACQUISTI VERI dell'invitato
     (mai sul puro reclutamento — L.173/2005). Nessuna promessa di guadagno. */
  'referral' => [
   ['ogg'=>'Il Branco cresce a due a due',
    'corpo'=>"{nome}, un Branco non si allarga da solo: si allarga quando qualcuno porta qualcun altro.\nTi mostro come si fa. {link}"],
   ['ogg'=>'Un invito e un regalo, non una richiesta',
    'corpo'=>"Qui non si \"recluta\": si regala una tribu a chi si e sentito invisibile.\nEcco perche l'invito, nel Branco, e un dono. {link}"],
   ['ogg'=>'Il tuo link personale',
    'corpo'=>"Questo e il tuo link: e legato al tuo nome nel Branco.\nCondividilo dove vuoi, con chi vuoi. {link}"],
   ['ogg'=>'Come funziona, in un minuto',
    'corpo'=>"Chi entra col tuo link ricomincia da te.\nTe lo spiego in un minuto, senza gerghi. {link}"],
   ['ogg'=>'Cosa ricevete tutti e due',
    'corpo'=>"Quando un randagio entra col tuo invito, ricevete DRX di benvenuto tutti e due.\nNessuna promessa di guadagno: sono DRX interni, energia del Branco. {link}"],
   ['ogg'=>'Le storie di chi ha portato il Branco',
    'corpo'=>"C'e chi ha portato un amico, un fratello, un'intera compagnia.\nAscolta come e andata. {link}"],
   ['ogg'=>'La card del tuo rango, fatta per essere mostrata',
    'corpo'=>"Il sito ti crea una card col tuo nome e il tuo rango: e fatta per essere postata.\nGenerala e falla girare. {link}"],
   ['ogg'=>'Il momento giusto per invitare',
    'corpo'=>"Quando esce un album o si apre una finestra promo vera, e il momento in cui un invito vale di piu.\nApprofitta della prossima uscita. {link}"],
   ['ogg'=>'Un grazie in piu quando entra davvero',
    'corpo'=>"Se il tuo invitato prende il suo posto nel Branco, arriva un reward in piu — legato al suo passo vero, non al numero di inviti.\nGuarda come cresce. {link}"],
   ['ogg'=>'Il tuo Branco che cresce',
    'corpo'=>"Guarda la tua rete allargarsi: ogni persona che entra rende piu forte chi c'era prima.\nVedi il tuo Branco. {link}"],
   ['ogg'=>'Chi ancora deve sentire questa musica',
    'corpo'=>"Pensa a chi ami la musica vera, a chi colleziona, a chi cerca una casa.\nPorta la persona giusta al posto giusto. {link}"],
   ['ogg'=>'Sopra uno, il Branco si moltiplica',
    'corpo'=>"Se ognuno porta piu di una persona, il Branco non cresce: si moltiplica.\nContinua a tenere aperta la porta. {link}\n— Destino Randagio"],
  ],

  /* ===================================================================== 9
     COMPLEANNO — 12 email (decisione Mirco: ogni flusso a 12). Parte il giorno
     del compleanno. E1 = auguri + regalo DRX nel wallet (COPY VERBATIM COWORK).
     E2-E12 = nurture del "mese di compleanno": si sblocca un REGALO/PROMO
     personale reale (sconto dedicato + bonus DRX) valido per la finestra
     compleanno (~30gg), che accompagna l'utente sui prodotti del Branco.
     SCARSITA' VERA (il mese e' reale, "nessuna fretta, e' il tuo mese").
     NOTA: E2-E12 DRAFTATE DA CODE in voce DR — COWORK puo' rifinirle. */
  'compleanno' => [
   /* E1 — COPY VERBATIM COWORK */
   ['ogg'=>'Buon compleanno, {nome}',
    'corpo'=>"Oggi il Branco fa festa per te. Ti ho lasciato un regalo nel wallet: qualche DRX per il tuo giorno. Apri e festeggia. {link} Un randagio non e mai solo. — Destino Randagio"],
   /* E2 — spiega la promo di compleanno sbloccata */
   ['ogg'=>'Il tuo regalo di compleanno ti aspetta',
    'corpo'=>"{nome}, oltre agli auguri il Branco ti sblocca un regalo vero: un occhio di riguardo dedicato al tuo compleanno, valido per tutto il tuo mese.\nNessuna fretta: e il tuo mese, te lo godi con calma.\nScopri cosa puoi farci. {link}"],
   /* E3 — album */
   ['ogg'=>'Festeggia con la colonna sonora del Delta',
    'corpo'=>"Un compleanno vuole la sua musica.\nCol regalo di compleanno scegli l'album che ti somiglia e portalo con te. {link}"],
   /* E4 — wear */
   ['ogg'=>'Un pezzo di Branco addosso',
    'corpo'=>"Il tuo mese e anche il momento giusto per indossare il Delta: il wear del Branco, col tuo occhio di riguardo di compleanno.\nDai un'occhiata. {link}"],
   /* E5 — membership/KIT */
   ['ogg'=>'Prendi il tuo posto nel Branco',
    'corpo'=>"Se il Branco ti sta piacendo, il compleanno e il momento buono per entrare col KIT — e in questo mese pesa meno.\nLa scelta resta tua. {link}"],
   /* E6 — NFT */
   ['ogg'=>'Regalati un pezzo del Delta',
    'corpo'=>"C'e chi, per il compleanno, si regala qualcosa che resta: un NFT del Branco.\nGuarda la galleria, col tuo occhio di riguardo. {link}\nNiente promesse di guadagno: si colleziona per appartenenza."],
   /* E7 — canzone su misura */
   ['ogg'=>'La canzone che racconta te',
    'corpo'=>"Per il tuo compleanno puoi regalarti (o regalare) una canzone su misura: la tua storia, in musica.\nScopri come. {link}"],
   /* E8 — DRX/wallet */
   ['ogg'=>'Il tuo wallet e piu pieno',
    'corpo'=>"Ricordi i DRX che ti ho messo nel wallet per il compleanno?\nCon quelli e il regalo del tuo mese sali di rango piu in fretta. Guarda dove sei. {link}"],
   /* E9 — Experience */
   ['ogg'=>'Un anno nuovo, una rotta nuova',
    'corpo'=>"Un anno nuovo di te merita una strada nuova: le Experience di Stray Nomads.\nGuarda la prossima. {link}"],
   /* E10 — reminder soft (meta' mese) */
   ['ogg'=>'Il tuo mese di festa e a meta',
    'corpo'=>"Il tuo mese di compleanno e arrivato a meta, e il regalo e ancora li.\nNessuna urgenza: solo un promemoria gentile. {link}"],
   /* E11 — voci del Branco / auguri */
   ['ogg'=>'Non festeggi da solo',
    'corpo'=>"Il Branco fa il tifo per te.\nSe ti va, festeggia con noi e continua la storia. {link}"],
   /* E12 — ultimo promemoria onesto (chiusura finestra) */
   ['ogg'=>'Il tuo mese di festa sta finendo',
    'corpo'=>"{nome}, il tuo mese di compleanno si chiude tra poco, e con lui il regalo dedicato.\nNessun countdown finto: quando il mese finisce, finisce.\nSe vuoi usarlo, questo e il momento. La scelta resta tua. {link}\n— Destino Randagio"],
  ],

  /* ===================================================================== 10
     EVENTI / STRAY NOMADS / LIVE — iscrizione a un evento (raduno, listening
     party, reveal, viaggio). Arco: conferma -> nurture pre -> reminder ->
     giorno -> post + recensione. Placeholder extra: {evento}. */
  'eventi' => [
   ['ogg'=>'C\'e un evento nel Branco',
    'corpo'=>"{nome}, il Branco esce dallo schermo: {evento}.\nTi va di esserci? Guarda di cosa si tratta. {link}"],
   ['ogg'=>'Cos\'e {evento}',
    'corpo'=>"Non un evento qualunque: una rotta del Branco.\nTi racconto cosa vivrai. {link}"],
   ['ogg'=>'Il tuo posto e confermato',
    'corpo'=>"Ci sei: il tuo posto per {evento} e segnato.\nTieni d'occhio questa casella, ti mando tutto il necessario. {link}"],
   ['ogg'=>'I dettagli pratici',
    'corpo'=>"Quando, dove, come arrivare, cosa portare.\nEcco tutto quello che ti serve per {evento}. {link}"],
   ['ogg'=>'Chi ci sara',
    'corpo'=>"Non sarai solo: il Branco si raduna.\nGuarda chi vivra {evento} con te. {link}"],
   ['ogg'=>'Manca poco, preparati',
    'corpo'=>"Ci siamo quasi. Prenditi un minuto per prepararti a {evento}.\nRipassa i dettagli. {link}"],
   ['ogg'=>'Domani si parte',
    'corpo'=>"{evento} e domani.\nUltimo ripasso e poi ci vediamo. {link}"],
   ['ogg'=>'E oggi: ci siamo',
    'corpo'=>"Oggi e il giorno di {evento}.\nCi vediamo dall'altra parte del fiume. {link}"],
   ['ogg'=>'Grazie per esserci stato',
    'corpo'=>"{evento} e andato, e tu c'eri.\nGrazie: il Branco e piu forte quando si guarda in faccia. {link}"],
   ['ogg'=>'Le voci e le foto del Branco',
    'corpo'=>"Rivivi {evento}: foto, momenti, le voci di chi c'era.\nGuarda com'e stato. {link}"],
   ['ogg'=>'Il prossimo passo',
    'corpo'=>"Se {evento} ti e rimasto dentro, c'e gia il prossimo giro all'orizzonte.\nResta pronto. {link}"],
   ['ogg'=>'Racconta {evento}, porta il Branco',
    'corpo'=>"La cosa piu bella e chiamare qualcuno al prossimo giro.\nRacconta {evento} e porta un randagio con te. {link}\n— Destino Randagio"],
  ],

  /* ===================================================================== 11
     PROFILE INCOMPLETE — reminder OPERATIVO di completamento profilo (NON e' un
     flusso di vendita: e' come il carrello abbandonato). Enterprise-spec Mirco
     2026-07-25: 7 email + 1 email di CONFERMA (template 'profile_reward_ok' in
     mailer.php, agganciata dal reward engine). Si spegne da solo appena
     profile_complete=1 (STOP nel motore). Trigger 'profile_incomplete'.

     PREMIO: 1.000 DRX FLAT al completamento (non piu' a step). L'accredito lo
     fa un ALTRO agente (dr_profile_reward); qui la copy PROMETTE 1.000 DRX e
     spiega cosa sono: credito-premio interno per sconti/vantaggi su Destino
     Randagio — MAI denaro, MAI investimento, MAI rendimento.

     Voce DR + neuro-copy/PNL ETICA: goal-gradient (sei quasi alla fine),
     effetto-completamento, appartenenza al Branco, micro-impegni, reciprocita',
     avversione alla perdita CON MISURA. VIETATI: urgenza/scarsita' falsa,
     countdown finti, guadagno garantito, "investi".
     Placeholder: {nome} {link} {missing_fields} {percentuale} {reward_amount}.
     Cadenza (giorni): [0,2,5,10,20,35,60]. */
  'profile_incomplete' => [
   /* E1 — appartenenza + reciprocita' + premio + progresso */
   ['ogg'=>'Il tuo profilo e quasi pronto: ti aspettano {reward_amount} DRX',
    'corpo'=>"Ciao {nome}, sei gia dentro il Branco: ti manca solo l'ultimo tratto del profilo.\nQuando lo chiudi, ti accreditiamo {reward_amount} DRX nel tuo wallet — credito-premio del Branco, per sconti e vantaggi su Destino Randagio.\nSei gia al {percentuale}%: l'ultimo passo e sempre il piu corto.\nCompleta il profilo. {link}\nLa scelta resta tua."],
   /* E2 — goal-gradient: % + campi mancanti */
   ['ogg'=>'Ti manca davvero poco per ricevere {reward_amount} DRX',
    'corpo'=>"{nome}, sei al {percentuale}%.\nTi restano solo {missing_fields} campi e i {reward_amount} DRX entrano nel wallet.\nFunziona cosi', nel Branco: piu ti conosce, piu puo darti — sconti dedicati, anteprime, la tua card.\nChiudi il profilo adesso che sei in corsa. {link}"],
   /* E3 — anticipazione + cosa sono i DRX (mai investimento/rendimento) */
   ['ogg'=>'I tuoi {reward_amount} DRX sono ancora qui',
    'corpo'=>"Nessuna fretta inventata: i tuoi {reward_amount} DRX restano nel Branco, ti aspettano.\nMettiamoli in chiaro: i DRX sono credito-premio interno. Li usi per sconti e vantaggi dentro Destino Randagio. Non sono soldi, non sono un investimento, non promettono nessun rendimento.\nAppena completi il profilo, entrano nel tuo wallet. {link}"],
   /* E4 — identita' / appartenenza (Passaporto del Branco) */
   ['ogg'=>'Completa il tuo Passaporto del Branco',
    'corpo'=>"Il profilo non e un modulo: e il tuo Passaporto del Branco — il tuo SIC-ID, il nome che porti qui dentro.\nUn randagio riconosciuto e un randagio che conta: vota nella DAO, ha la sua card, riceve gli auguri col regalo del compleanno.\nPrenditi il tuo posto per intero. {link}"],
   /* E5 — solo i campi mancanti, completamento rapido */
   ['ogg'=>'{nome}, mancano solo {missing_fields} dati',
    'corpo'=>"Te lo rendo semplice: mancano solo {missing_fields} dati e hai finito.\nDue minuti, non di piu. Poi i {reward_amount} DRX entrano nel wallet e il profilo e completo al 100%.\nFinisci ora, sei a un passo. {link}"],
   /* E6 — avversione alla perdita CON MISURA */
   ['ogg'=>'Il tuo profilo e rimasto a meta',
    'corpo'=>"{nome}, hai gia fatto la parte piu difficile: sei al {percentuale}%.\nSarebbe un peccato lasciare li quel lavoro — e i {reward_amount} DRX che ti spettano — per {missing_fields} campi.\nNon te lo dico per fretta: te lo dico perche il tuo posto e gia pronto, manca solo la tua firma.\nCompleta il profilo. {link}"],
   /* E7 — chiusura, controllo all'utente */
   ['ogg'=>'Quando vuoi, i tuoi {reward_amount} DRX ti aspettano',
    'corpo'=>"Questa e l'ultima che ti scrivo sul profilo.\nNessuna insistenza: il Branco cammina al tuo passo. Quando vorrai chiuderlo, i {reward_amount} DRX saranno li, pronti nel tuo wallet.\nLa scelta resta tua, {nome}. {link}\n— Destino Randagio"],
  ],

  ];
}

/* ============================================================================
   MULTILINGUA — PROFILE INCOMPLETE in INGLESE (voce DR, non traduzione letterale)
   Struttura coerente con dr-lang.php (base 'it', fallback 'it'). Stessa spec:
   7 email, stessi placeholder {nome} {link} {missing_fields} {percentuale}
   {reward_amount}, stessa voce (goal-gradient, appartenenza, niente urgenza
   falsa, niente "investi"/"guadagno garantito"). Curata, non Google-translate.
============================================================================ */
function dr_email_profile_incomplete_en(){
  return [
   /* E1 — belonging + reciprocity + reward + progress */
   ['ogg'=>'Your profile is almost done: {reward_amount} DRX are waiting',
    'corpo'=>"Hi {nome}, you're already inside the Pack: only the last stretch of your profile is missing.\nWhen you close it, we credit {reward_amount} DRX to your wallet — Pack reward-credit, for discounts and perks on Destino Randagio.\nYou're already at {percentuale}%: the last step is always the shortest.\nComplete your profile. {link}\nThe choice stays yours."],
   /* E2 — goal-gradient: % + missing fields */
   ['ogg'=>'You\'re just a step away from {reward_amount} DRX',
    'corpo'=>"{nome}, you're at {percentuale}%.\nOnly {missing_fields} fields left and the {reward_amount} DRX land in your wallet.\nThat's how the Pack works: the more it knows you, the more it can give you — dedicated discounts, early access, your own card.\nClose your profile now, while you're on a roll. {link}"],
   /* E3 — anticipation + what DRX are (never investment/return) */
   ['ogg'=>'Your {reward_amount} DRX are still here',
    'corpo'=>"No made-up rush: your {reward_amount} DRX stay in the Pack, waiting for you.\nLet's be clear: DRX are internal reward-credit. You use them for discounts and perks inside Destino Randagio. They're not money, not an investment, and promise no return.\nThe moment you complete your profile, they enter your wallet. {link}"],
   /* E4 — identity / belonging (Pack Passport) */
   ['ogg'=>'Complete your Pack Passport',
    'corpo'=>"Your profile isn't a form: it's your Pack Passport — your SIC-ID, the name you carry in here.\nA recognised stray is a stray who counts: votes in the DAO, has their own card, gets birthday wishes with the gift.\nTake your place in full. {link}"],
   /* E5 — only the missing fields, quick finish */
   ['ogg'=>'{nome}, only {missing_fields} details left',
    'corpo'=>"I'll make it easy: only {missing_fields} details are missing and you're done.\nTwo minutes, no more. Then the {reward_amount} DRX enter your wallet and your profile hits 100%.\nFinish now, you're one step away. {link}"],
   /* E6 — loss aversion WITH MEASURE */
   ['ogg'=>'Your profile stopped halfway',
    'corpo'=>"{nome}, you've already done the hard part: you're at {percentuale}%.\nIt would be a shame to leave that work — and the {reward_amount} DRX you've earned — for {missing_fields} fields.\nI'm not saying it to rush you: I'm saying it because your place is ready, only your signature is missing.\nComplete your profile. {link}"],
   /* E7 — closing, control to the user */
   ['ogg'=>'Whenever you want, your {reward_amount} DRX are waiting',
    'corpo'=>"This is the last time I'll write to you about the profile.\nNo pushing: the Pack walks at your pace. Whenever you decide to close it, the {reward_amount} DRX will be there, ready in your wallet.\nThe choice stays yours, {nome}. {link}\n— Destino Randagio"],
  ];
}

/* Copy per lingua del flusso profilo. 'en' -> EN, tutto il resto -> IT (fonte
   canonica dr_email_flows). Usata da mkt_render_flow (mailer.php). */
function dr_email_profile_incomplete($lang='it'){
  if(strtolower((string)$lang)==='en') return dr_email_profile_incomplete_en();
  $f = dr_email_flows();
  return $f['profile_incomplete'] ?? [];
}

/* Blocco EN per la conferma 'profile_reward_ok' (usato da mkt_render in
   mailer.php quando la lingua del contatto e' 'en'). Ritorna testi gia' pronti,
   il renderer li avvolge nel layout. Placeholder gia' risolti dal chiamante. */
function dr_email_profile_reward_ok_en($n, $rw){
  return [
    'subject' => 'Done: '.$rw.' DRX credited to your wallet',
    'pre'     => 'Your '.$rw.' DRX are in your wallet',
    'h1'      => 'Done, '.$n.': profile complete',
    'intro'   => 'You made it: your Pack Passport is complete. As promised, I credited <b style="color:#D4AF37">'.$rw.' DRX</b> to your wallet.',
    'note'    => 'They\'re Pack reward-credit: you use them for discounts and perks inside Destino Randagio. They\'re not money, not an investment, and produce no return.',
    'cta1'    => 'Open my wallet →',
    'cta2'    => 'See where to spend DRX →',
    'before'  => 'Balance before:',
    'plus'    => ' DRX credited',
    'after'   => 'New balance:',
  ];
}

/* ---------- RE-PERMISSION / OPT-IN (GDPR) ----------
   ECCEZIONE DOCUMENTATA alla regola dei 12: i contatti importati con
   status='da_confermare' (source 'contatto', base non di legittimo interesse)
   NON ricevono marketing. Prima ricevono SOLO questa mini-sequenza onesta di
   opt-in (1-2 email, nessuna vendita): chiedono il permesso e basta. Chi
   conferma passa a status='attivo' (via optin.php) ed entra nei flussi veri.
   Chi non conferma resta fuori: reputazione del dominio protetta. */
function dr_email_repermission(){
  /* COPY VERBATIM COWORK (spec 7.1). 5 email. from=info@ list=repermission tag=L1.
     GDPR: e' l'UNICO flusso ammesso sui lead source=81plus-import / da_confermare.
     Chi clicca una CTA in E1-E4 -> consenso=optin_confirmed (via optin.php) ed entra
     nei nurture; chi non clicca dopo la E5 -> status=silente, stop invii.
     Campi opzionali per email: 'pre' (preheader), 'cta' => ['txt','url']. */
  return [
   ['ogg'=>'{nome}, ci siamo conosciuti nel posto sbagliato',
    'pre'=>'Una cosa nuova, e la scelta e tua',
    'corpo'=>"Ciao {nome},\nil tuo contatto e arrivato dal mondo 81+/della nostra rete. Nel frattempo e nato qualcosa di diverso: Destino Randagio — musica, storie e una community, Il Branco, per chi si sente un po' randagio.\nNon voglio scriverti se non ti va. Se ti incuriosisce, resta con un clic. Se no, nessun problema.\n{link}\nLa scelta resta tua. — Destino Randagio",
    'cta'=>['txt'=>'Si, raccontami di piu']],
   ['ogg'=>'Da dove vengo (in 2 minuti)',
    'corpo'=>"Il Po, la nebbia, il fango. Sono nato dal Delta e dalla voglia di non restare indietro.\nSe una storia cosi ti parla, questo e il Branco. Ascolta il primo capitolo.\n{link}\nSe non fa per te, in fondo trovi un clic per non ricevere piu nulla.",
    'cta'=>['txt'=>'Ascolta il primo capitolo']],
   ['ogg'=>'Cosa troveresti dentro',
    'corpo'=>"Musica che cura, una community vera, e roba concreta (non promesse).\nTi mostro com'e il Branco, senza impegno.\n{link}\nConfermi che vuoi restare aggiornato? Basta un clic.",
    'cta'=>['txt'=>'Voglio restare nel giro']],
   ['ogg'=>'{nome}, l\'ultima che ti scrivo (se non rispondi)',
    'corpo'=>"Rispetto il tuo tempo. Se non mi dici che vuoi restare, smetto di scriverti — e giusto cosi.\nSe invece il Delta ti ha incuriosito, resta con un clic.\n{link}",
    'cta'=>['txt'=>'Resto nel Branco']],
   ['ogg'=>'Ti saluto (porta aperta)',
    'corpo'=>"Questa e l'ultima. Ti tolgo dagli invii, come promesso.\nSe un giorno vorrai, la porta del Branco resta aperta: destinorandagio.it.\nUn randagio non e mai solo. — Destino Randagio",
    'cta'=>['txt'=>'Entra quando vuoi','url'=>'https://destinorandagio.it']],
  ];
}

/* ---------- VOCI DEL BRANCO (richiesta recensione, multi-trigger) ----------
   COPY VERBATIM COWORK (spec 7.6) + subject canonici (COPY_VOCI_EMAIL.md).
   Un flusso a 2 step nel motore: step0 = la richiesta (variante scelta da
   ctx['voce_kind']: album|merch|fumetto|canzone|viaggio), step1 = reminder
   unico (+7gg) e poi STOP. Placeholder: {nome} {titolo} {link}.
   Nota FISSA in fondo a ogni email (aggiunta dal renderer). REWARD: 100 DRX
   idempotente, indipendente dal voto — l'accredito avviene alla submission
   su /lascia-la-tua-voce (fuori dalla macchina email). */
function dr_email_voci(){
  return [
   'album'   => ['ogg'=>'Hai ascoltato il viaggio fino in fondo?',
     'corpo'=>"{nome}, cosa ti ha lasciato {titolo}? Il Branco cresce con le voci vere. Raccontala in due righe. {link}"],
   'merch'   => ['ogg'=>'Ora il simbolo del Branco e arrivato da te',
     'corpo'=>"Ti e arrivato {titolo}? Dicci com'e, per davvero. La tua voce aiuta chi verra dopo. {link}"],
   'fumetto' => ['ogg'=>'Hai letto l\'ultima tavola?',
     'corpo'=>"Hai letto la tavola? Lascia la tua impressione sul Branco. {link}"],
   'canzone' => ['ogg'=>'La tua canzone ora e tua. Com\'e stato riceverla?',
     'corpo'=>"La tua canzone su misura e nelle tue mani. Raccontaci cosa hai provato. {link}"],
   'viaggio' => ['ogg'=>'Il viaggio e finito. La storia no',
     'corpo'=>"Sei tornato dalla rotta? Racconta com'e stato camminare col Branco. {link}"],
   'reminder'=> ['ogg'=>'La tua voce manca ancora al Branco',
     'corpo'=>"Solo un promemoria gentile: la tua voce su {titolo} vale ancora. Nessuna fretta. {link}"],
  ];
}
/* Nota fissa (reward) stampata in fondo a ogni email 'voci_del_branco'. */
function dr_voci_nota(){
  return "Ricevi 100 DRX per ogni recensione sincera e verificata. Il premio non dipende dal voto: conta la tua onesta.";
}

/* ---------- FLUSSI "CORTI" versione COWORK (spec 7.2/7.3/7.5) ----------
   COPY VERBATIM. Contengono i placeholder {nome} {titolo} {data} {link} e la
   CTA per-email. Sono la versione COWORK di lancio_album (7), nuovo_prodotto
   (4) ed evento (3). Il registro DR (flussi_dr.php) decide quali agganciare:
   i flussi a 12 gia' esistenti (lancio_album/lancio_prodotto/eventi) restano
   attivi finche' Mirco non sceglie 7-vs-12 / 4-vs-12 / 3-vs-12 sul BUS. */
function dr_email_flows_short(){
  return [
   'lancio_album' => [
    ['ogg'=>'Sta per uscire qualcosa, {nome}','corpo'=>"Un nuovo capitolo del Delta e quasi pronto: {titolo}. Nei prossimi giorni te lo faccio sentire pezzo per pezzo. Tieni le cuffie a portata. {link}",'cta'=>['txt'=>'Guarda l\'anteprima']],
    ['ogg'=>'La storia dietro {titolo}','corpo'=>"Ogni album nasce da qualcosa di vero. Ti racconto da dove viene {titolo} e perche l'ho scritto. Scopri la storia. {link}",'cta'=>['txt'=>'Scopri la storia']],
    ['ogg'=>'{titolo} e fuori','corpo'=>"Ci siamo: {titolo} e disponibile. Mettiti comodo e lascialo scorrere dall'inizio alla fine. {link} La scelta resta tua.",'cta'=>['txt'=>'Ascolta ora']],
    ['ogg'=>'Il pezzo che sta girando di piu','corpo'=>"C'e una traccia di {titolo} che sta restando addosso a tutti. Senti se fa lo stesso a te. {link}",'cta'=>['txt'=>'Ascolta il pezzo']],
    ['ogg'=>'Portalo con te','corpo'=>"Se {titolo} ti e entrato dentro, c'e il wear e la versione da collezione del capitolo. Guarda. {link}",'cta'=>['txt'=>'Scopri il wear']],
    ['ogg'=>'Lascia la tua voce su {titolo}','corpo'=>"Cosa ti ha lasciato {titolo}? La tua recensione sincera vale 100 DRX (il premio non dipende dal voto). {link}",'cta'=>['txt'=>'Lascia la tua voce']],
    ['ogg'=>'Il capitolo continua nel Branco','corpo'=>"Chi e nel Branco sente le uscite prima e le vive dentro una community. Se {titolo} ti e piaciuto, questa e la porta. {link}",'cta'=>['txt'=>'Entra nel Branco']],
   ],
   'nuovo_prodotto' => [
    ['ogg'=>'Nuovo nel Delta: {titolo}','corpo'=>"E arrivato qualcosa di nuovo nello shop del Branco: {titolo}. Non e merchandising: e un segno di riconoscimento. Guardalo. {link}",'cta'=>['txt'=>'Guarda {titolo}']],
    ['ogg'=>'Perche l\'ho fatto','corpo'=>"Ogni pezzo del Branco ha un senso. Ti dico cosa c'e dietro {titolo} e per chi l'ho pensato. {link}",'cta'=>['txt'=>'Scopri']],
    ['ogg'=>'Riservato al Branco?','corpo'=>"Alcuni pezzi sono solo per i Membri. Se {titolo} e tra questi, col KIT lo sblocchi. {link}",'cta'=>['txt'=>'Entra nel Branco']],
    ['ogg'=>'Ultimo sguardo a {titolo}','corpo'=>"Te lo lascio qui, senza fretta. Se ti somiglia, e tuo. {link} La scelta resta tua. — Destino Randagio",'cta'=>['txt'=>'Vai allo shop']],
   ],
   'evento' => [
    ['ogg'=>'C\'e qualcosa in arrivo: {titolo}','corpo'=>"Il Branco si muove: {titolo}, il {data}. Ti racconto di cosa si tratta e come esserci. {link}",'cta'=>['txt'=>'Scopri l\'evento']],
    ['ogg'=>'Come partecipare a {titolo}','corpo'=>"Ecco tutto quello che ti serve per {titolo} del {data}. Prenota il tuo posto. {link}",'cta'=>['txt'=>'Prenota il posto']],
    ['ogg'=>'Ci vediamo a {titolo}','corpo'=>"Manca poco. Se ci sei, il Branco ti aspetta. {link} La scelta resta tua.",'cta'=>['txt'=>'Ci sono']],
   ],
  ];
}

/* ---------- FESTIVITA: mappa 12 feste chiave -> {ogg,corpo} ----------
   Fonte canonica UNICA della copy festivita (prima era duplicata dentro
   mailer.php). Il motore mkt_run_feste pesca la festa attiva da dr-feste.php
   (feste_attiva) e sceglie qui l'email giusta per chiave; se la chiave non c'e,
   usa dr_email_festa() come fallback dinamico. Placeholder: {nome} {sconto}. */
function dr_email_feste_map(){
  return [
   'natale'       => ['ogg'=>'Natale nel Branco, {nome}',
     'corpo'=>"Sotto il Delta il fango si copre di brina, e anche il randagio piu duro alza gli occhi al cielo.\nNatale e questo: fermarsi un attimo, guardare la strada fatta, scegliere chi portare con te.\nGuarda i doni del Branco. {link}"],
   'capodanno'    => ['ogg'=>'Un anno nuovo dal Delta, {nome}',
     'corpo'=>"Si volta pagina, ma il Branco resta lo stesso: la tua famiglia scelta.\nEntra nell'anno nuovo con noi. {link}"],
   'san_valentino'=> ['ogg'=>'San Valentino — regala una voce, {nome}',
     'corpo'=>"C'e chi compra fiori che appassiscono, e chi regala una canzone che resta.\nDi' quello che non hai mai detto, con la tua storia dentro (29,90). {link}"],
   'donna'        => ['ogg'=>'Festa della Donna, {nome}',
     'corpo'=>"Per chi tiene in piedi mezzo mondo senza chiedere grazie.\nUn pensiero del Delta che valga davvero. {link}"],
   'papa'         => ['ogg'=>'Festa del Papa, {nome}',
     'corpo'=>"Per chi ti ha insegnato a stare in piedi non con le parole, ma con l'esempio.\nUn regalo che sappia di rispetto, non di vetrina. {link}"],
   'mamma'        => ['ogg'=>'Festa della Mamma, {nome}',
     'corpo'=>"La prima che ha creduto in te quando nessuno lo faceva.\nNon un regalo qualunque: qualcosa che porti il vostro nome. {link}"],
   'pasqua'       => ['ogg'=>'Pasqua — il Branco rinasce, {nome}',
     'corpo'=>"Ogni inverno finisce. Ogni ferita, se la lavori, diventa musica.\nPasqua nel Delta e rinascita: si lascia il vecchio nel fango e si riparte piu fieri. {link}"],
   'ferragosto'   => ['ogg'=>'Ferragosto sul fiume, {nome}',
     'corpo'=>"Il Delta d'estate ha una luce che sa di liberta.\nPortala con te: qualcosa del Branco per questi giorni. {link}"],
   'halloween'    => ['ogg'=>'Halloween nel Delta, {nome}',
     'corpo'=>"Stanotte il Delta si veste di nebbia e i randagi escono allo scoperto.\nNessuna paura: il buio e casa nostra. {link}"],
   'ognissanti'   => ['ogg'=>'Ognissanti, {nome}',
     'corpo'=>"Un giorno per chi cammina ancora con noi, anche se non lo vediamo.\nIl Branco non dimentica nessuno. {link}"],
   'black_friday' => ['ogg'=>'Black Friday del Branco, {nome}',
     'corpo'=>"Niente giochi di prezzo, niente finti countdown.\nPer pochi giorni il Branco taglia come non fa mai — fino al {sconto}% in meno su prodotti selezionati. Quando la finestra si chiude, il prezzo risale davvero. {link}"],
   'cyber_monday' => ['ogg'=>'Cyber Monday — ultimo colpo, {nome}',
     'corpo'=>"Il weekend e passato, resta l'ultimo colpo.\nDopo, il prezzo torna quello di sempre. Il Branco non ripete due volte lo stesso invito. {link}"],
  ];
}

/* Cadenze (giorni dall'ingresso nel flusso) per ogni flusso a 12 email.
   Le legge mailer.php (mkt_flow_spec) per costruire i delay degli step. */
function dr_flow_cadenze(){
  /* Cadenza profilo: NON piu' hardcode qui. La legge la fonte unica
     config/profile-reminder-flow.php (default + override admin da mkt_meta).
     Se il file non c'e' ancora, fallback al valore storico. */
  if(!function_exists('dr_profile_reminder_cadenza')){
    $__prf=__DIR__.'/config/profile-reminder-flow.php';
    if(is_file($__prf)) @require_once $__prf;
  }
  $cadProfilo = function_exists('dr_profile_reminder_cadenza')
    ? dr_profile_reminder_cadenza()
    : [0,2,5,10,20,35,60];
  $__cad = [
    'welcome'         => [0,2,4,7,10,14,18,23,28,34,41,50],
    'kit_onboarding'  => [0,1,3,6,10,15,21,30,45,60,75,90],
    'nurture_nft'     => [0,3,6,10,15,21,28,36,45,55,66,80],
    'nurture_musica'  => [0,3,6,10,15,21,28,36,45,55,66,80],
    'winback'         => [0,3,7,12,18,25,33,42,52,63,75,90],
    'lancio_album'    => [0,1,2,3,5,7,10,13,17,21,26,32],   /* drip da lancio, ~1 mese */
    'lancio_prodotto' => [0,2,4,6,9,12,16,20,25,30,36,42],
    'referral'        => [0,3,7,12,18,25,33,42,52,63,75,90],
    'compleanno'      => [0,1,3,5,7,10,13,16,19,22,26,30],  /* Mirco: mese di compleanno, 12 step */
    'eventi'          => [0,2,4,6,8,10,12,14,16,18,20,22],  /* countdown+post evento (12, resta attivo) */
    'repermission'    => [0,3,7,12,18],                     /* COWORK 7.1: repermission_L1, 5 email GDPR */
    'profile_incomplete' => $cadProfilo,                   /* reminder completamento profilo: cadenza da config/profile-reminder-flow.php (default [0,2,5,10,20,35,60]) */
    /* --- flussi CORTI COWORK (registro flussi_dr.php; live solo se Mirco sceglie) --- */
    'voci_del_branco' => [0,7],                             /* step0 richiesta + reminder +7gg poi STOP */
    'lancio_album_c'  => [-3,-1,0,1,3,6,10],                /* COWORK 7.2 (0=uscita) */
    'nuovo_prodotto'  => [0,2,5,9],                         /* COWORK 7.3 */
    'evento_c'        => [-7,-2,0],                         /* COWORK 7.5 */
  ];
  /* AGGIUNTA: cadenze dei flussi stagionali/ricorrenti (coprono l'anno) */
  if(function_exists('dr_flow_cadenze_stagionali')) $__cad = array_merge($__cad, dr_flow_cadenze_stagionali());
  /* AGGIUNTA: cadenze dei flussi extra (carrello, post-acquisto, VIP, web3, ...) */
  if(function_exists('dr_flow_cadenze_extra')) $__cad = array_merge($__cad, dr_flow_cadenze_extra());
  /* AGGIUNTA: cadenze dei flussi webinar (welcome/onboarding/profilo/pre/post/followup) */
  if(function_exists('dr_flow_cadenze_webinar')) $__cad = array_merge($__cad, dr_flow_cadenze_webinar());
  return $__cad;
}

/* CTA di default per flusso: [url, testo]. Il testo usa SOLO le CTA ammesse
   dalla voce DR (Ascolta/Scopri/Continua la storia/Entra nel Branco/La scelta
   resta tua...). Il chiamante puo sovrascrivere con ctx['cta_url']/ctx['cta_txt']. */
function dr_flow_cta($flow){
  if(!defined('DR_SITE')) define('DR_SITE','https://destinorandagio.it');
  $S=DR_SITE;
  $m=[
    'welcome'         => [$S.'/storia.html',        'Continua la storia'],
    'kit_onboarding'  => [$S.'/account.php',        'Entra nella tua Area'],
    'nurture_nft'     => [$S.'/nft.html',           'Scopri la galleria'],
    'nurture_musica'  => [$S.'/index.html#album',   'Ascolta ora'],
    'winback'         => [$S.'/account.php',         'Riprendi il tuo posto'],
    'lancio_album'    => [$S.'/index.html#album',    'Ascolta l\'album'],
    'lancio_prodotto' => [$S.'/shop.html',           'Scopri il drop'],
    'referral'        => [$S.'/account.php',          'Invita il tuo Branco'],
    'compleanno'      => [$S.'/account.php',          'Scopri il regalo'],
    'eventi'          => [$S.'/index.html',           'Scopri l\'evento'],
    'festivita'       => [$S.'/shop.html',            'Scopri la promo'],
    'repermission'    => [$S.'/optin.php',            'Si, voglio restare nel Branco'],
    'profile_incomplete' => [$S.'/account.php',       'Completa il tuo profilo'],
    'voci_del_branco' => [$S.'/lascia-la-tua-voce',   'Lascia la tua voce'],
    'evento'          => [$S.'/index.html',           'Scopri l\'evento'],
    'nuovo_prodotto'  => [$S.'/shop.html',            'Scopri'],
  ];
  /* AGGIUNTA: CTA dei flussi stagionali/ricorrenti */
  if(function_exists('dr_flow_cta_stagionali')) $m = array_merge($m, dr_flow_cta_stagionali($S));
  /* AGGIUNTA: CTA dei flussi extra */
  if(function_exists('dr_flow_cta_extra')) $m = array_merge($m, dr_flow_cta_extra($S));
  /* AGGIUNTA: CTA dei flussi webinar */
  if(function_exists('dr_flow_cta_webinar')) $m = array_merge($m, dr_flow_cta_webinar($S));
  return $m[$flow] ?? [$S, 'Scopri'];
}

/* ---- EMAIL FESTIVITA (dinamica): un solo template che pesca la festa attiva
   da dr-feste.php e monta oggetto+corpo col tema e lo sconto del momento. ---- */
function dr_email_festa($nome='{nome}', $ts=null){
  if(!function_exists('feste_attiva')){ @require_once __DIR__.'/dr-feste.php'; }
  $f = function_exists('feste_attiva') ? feste_attiva($ts) : null;
  if(!$f) return null; /* nessuna promo attiva: niente email festa */
  $festa = $f['nome'];
  $sc    = (int)round(($f['sconto'] ?? 0)*100);
  return [
    'ogg'  => "$festa nel Branco",
    'corpo'=> "Ciao $nome, e $festa e il Delta lo festeggia col Branco.\n".
              "In questi giorni trovi il meglio del mondo Destino Randagio con un occhio di riguardo".
              ($sc>0 ? " — fino al $sc% in meno su prodotti selezionati" : "").".\n".
              "Album, wear, NFT stagionali, KIT del Branco: guarda cosa e in promo ora. {link}\n".
              "Niente urgenza inventata: la festa dura quello che deve. La scelta resta tua.\n— Destino Randagio",
  ];
}

/* Renderer: avvolge una email {ogg,corpo} nel template HTML del sito se esiste. */
function dr_flow_html($email, $vars=[]){
  $ogg = strtr($email['ogg'],   $vars);
  $cor = strtr($email['corpo'], $vars);
  if(function_exists('dr_email_wrap')) return dr_email_wrap($ogg, nl2br(htmlspecialchars($cor)));
  $html = nl2br(htmlspecialchars($cor));
  return "<h2 style=\"font-family:Georgia,serif;color:#0D0D0D\">$ogg</h2><div style=\"font-family:Georgia,serif;color:#222;line-height:1.7\">$html</div>";
}
