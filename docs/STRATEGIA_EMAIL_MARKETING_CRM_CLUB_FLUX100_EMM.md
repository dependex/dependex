# STRATEGIA DI EMAIL MARKETING & NURTURING ISTITUZIONALE (FLUX100 / EMM+)
**Target:** Rete Nazionale dei Club Alcologici Territoriali (CAT, APCAT, ACAT, ARCAT, AICAT)  
**Obiettivo:** Far conoscere e diffondere dependex.social come infrastruttura di servizio aperta e gratuita  
**Mittente Istituzionale Certificato:** `info@dependex.support` (SMTP Hostinger SSL 465)  
**Versione:** 1.0 — Settembre 2026

---

## 1. PRINCIPI FONDAMENTALI & DEONTOLOGIA (ZERO COMMERCIAL / TOTAL UTILITY)

1. **Servizio di Pubblica Utilità (L. 117/2017 - Terzo Settore):**
   - Le comunicazioni NON promuovono prodotti commerciali a pagamento.
   - Il focus è 100% sulla valorizzazione dell'ecologia sociale, sulla visibilità gratuita dei presidi territoriali del Metodo Hudolin e sul sostegno alle famiglie.
2. **Riconoscimento e Rispetto della Storia:**
   - Si riconosce il ruolo fondante dei servitori-insegnanti, delle famiglie e delle federazioni (AICAT, ARCAT, APCAT, ACAT).
   - `dependex.social` si pone come un amplificatore digitale gratuito a supporto del cerchio multifamiliare reale (*"Al Club. Col Club."*).
3. **Privacy-First & RFC 8058 Compliant:**
   - Nessun cookie invasivo o pixel di terze parti.
   - Header RFC 8058 (`List-Unsubscribe` e `List-Unsubscribe-Post`) con disiscrizione in 1-click su ogni email.
   - Token univoco sicuro per ogni Club che consente di aggiornare orari e indirizzo con 1 solo clic.

---

## 2. LA SEQUENZA DI NURTURING A 5 STEP (FLUX100 / EMM+)

### STEP 1: PRESENTAZIONE ISTITUZIONALE & VERIFICA SCHEDA (Giorno 1)
* **Oggetto:** Censimento Aperto 2026 Club Hudolin: verificate la scheda di {entity_name} su dependex.social
* **Preheader:** Abbiamo inserito il vostro Club nella mappa nazionale per orientare chi cerca aiuto nel vostro comune.
* **Gancio Psicologico:** Gratificazione, riconoscimento del lavoro sul territorio e desiderio di accuratezza delle informazioni.
* **Contenuto Chiave:**
  - Annuncio del primo censimento georeferenziato 2026 (395 Club e oltre 51.500 famiglie).
  - Link diretto 1-tap alla scheda pubblica del proprio Club (`/mappa-club.php?sic={sic_id}`).
  - Invito a verificare giorno di incontro ({meeting_day}), orario ({meeting_time}) e recapito.
* **CTA Primaria:** `[Verifica la Scheda del Tuo Club →]`
* **CTA Secondaria:** `[Segnala una variazione via email]`

---

### STEP 2: IL WIDGET "TROVA-CLUB" PER I SITI COMUNALI & ASSOCIATIVI (Giorno 4)
* **Oggetto:** Un servizio gratuito per il sito di {entity_name} e del vostro Comune: il Widget Trova-Club
* **Preheader:** Un componente leggero e sicuro per far trovare il vostro Club in 1 secondo a chi vive nella vostra provincia.
* **Gancio Psicologico:** Strumento operativo concreto pronto all'uso a zero costo e senza competenze tecniche.
* **Contenuto Chiave:**
  - Come il widget `/widget-club.php` può essere incorporato nel sito dell'APCAT/ACAT, nei portali comunali o delle parrocchie locali.
  - Funziona su qualsiasi smartphone, ha zero cookie pubblicitari e include il tasto per chiamarvi direttamente.
  - Snippet iframe pronto: `<iframe src="https://dependex.social/widget-club.php?q={province}" ...>`.
* **CTA Primaria:** `[Prova il Widget per la tua Provincia ({province}) →]`
* **CTA Secondaria:** `[Scarica la Guida Tecnica di Integrazione]`

---

### STEP 3: INTEROPERABILITÀ CON ASL, SER.D E SISTEMI GIS (Giorno 8)
* **Oggetto:** Rete aperta e trasparente: OpenData GeoJSON e Feed Territoriale a supporto dei servizi sanitari
* **Preheader:** Come dependex.social rende visibili i cerchi multifamiliari alle istituzioni sociosanitarie della regione {region}.
* **Gancio Psicologico:** Legittimazione istituzionale e integrazione virtuosa pubblico-volontariato.
* **Contenuto Chiave:**
  - Presentazione degli endpoint standard `/api-opendata-geojson.php` (RFC 7946) e `/api-feed-territorio.php` (Atom/GeoRSS).
  - Come medici di medicina generale, assistenti sociali e operatori Ser.D possono consultare la mappa aggiornata in tempo reale senza registrarsi.
* **CTA Primaria:** `[Esplora gli OpenData della Rete Territoriale →]`

---

### STEP 4: SOVRANITÀ DIGITALE & PWA ON-DEVICE PER LE FAMIGLIE (Giorno 13)
* **Oggetto:** Uno strumento digitale che invita a vivere la vita reale: la PWA di dependex.social
* **Preheader:** Nessun account richiesto, zero tracciamento, SOS vocale e promemoria locale del giorno del Club.
* **Gancio Psicologico:** Rassicurazione etica: la tecnologia non sostituisce l'incontro umano, ma lo protegge.
* **Contenuto Chiave:**
  - Descrizione delle funzionalità on-device: diario di sobrietà privato salvato solo nel telefono, SOS vocale guidato per i momenti di crisi, promemoria gentile della riunione settimanale.
  - Rispetto del principio *"Screen Off → Life On"*: la piattaforma invita sempre ad alzarsi dallo schermo e recarsi al Club.
* **CTA Primaria:** `[Scopri la PWA e le Risorse per le Famiglie →]`

---

### STEP 5: CONDIVISIONE EVENTI, INTERCLUB E COMUNITÀ DI PRATICA (Giorno 19)
* **Oggetto:** Diamo voce agli eventi, alle scuole e agli Interclub di {region} su dependex.social
* **Preheader:** Uno spazio comune gratuito per pubblicare iniziative, corsi di sensibilizzazione e testimonianze.
* **Gancio Psicologico:** Senso di appartenenza e reciprocità fraterna della comunità dei Club.
* **Contenuto Chiave:**
  - Invito ai servitori-insegnanti a inviare locandine e dettagli di Interclub, assemblee e corsi di formazione.
  - Spazio dedicato in `/events-public.php` e visibilità in tutta Italia.
  - Contatto diretto con la redazione volontaria di dependex.social (`info@dependex.support`).
* **CTA Primaria:** `[Segnala un Evento del Tuo Territorio →]`
* **CTA Secondaria:** `[Rispondi a questa email]`

---

## 3. PIANO DI WARM-UP & INVIO CONTROLLATO (SMTP HOSTINGER 465 SSL)

Per garantire la massima reputazione dell'IP e del dominio `dependex.support` ed evitare blocchi o spam filter di provider sensibili (es. Gmail, Libero, Virgilio, PEC), gli invii seguono questa cadenza:

| Fase | Giorni | Volume Giornaliero | Destinatari | Throttle |
| :--- | :--- | :--- | :--- | :--- |
| **Warm-up 1** | Giorno 1 - 3 | 25 email/die | Segreterie ARCAT/AICAT e province pilota (Veneto, FVG) | 6 sec |
| **Warm-up 2** | Giorno 4 - 7 | 50 email/die | APCAT provinciali e Club ad alta densità | 5 sec |
| **Regime** | Giorno 8+ | 80-100 email/die | Rete nazionale completa (395 Club) | 4 sec |

---

## 4. METRICHE CHIAVE (KPI DI SUCCESSO ETICO)

* **Delivery Rate:** > 98.5%
* **Open Rate:** > 48.0% (grazie alla personalizzazione precisa del nome del Club e del comune)
* **Click-to-Verify (Step 1):** > 22.0%
* **Widget Adoption Rate (Step 2):** > 12.0% delle associazioni provinciali/regionali
* **Unsubscribe Rate:** < 0.3%
* **Spam Complaints:** 0.00% (garantito dalla pertinenza istituzionale e dal footer trasparente)
