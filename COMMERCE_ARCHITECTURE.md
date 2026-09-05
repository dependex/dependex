# 🏛️ UNIVERSAL COMMERCE & CHECKOUT ARCHITECTURE
### Central Commerce Engine for the Entire Mirco Pregnolato Universe
*Core Architecture · Multi-Domain Headless Checkout · PayPal Live Integration · Source of Truth Pricing*

---

## 1. VISION & NORTH STAR
Un'unica infrastruttura commerce centralizzata per tutti i brand, business, offerte e domini dell'universo Mirco Pregnolato (`dependex.social`, `mircopregnolato.it`, `oltre.social`, `mywallet.business`, `betterway.agency`, `neuralog.pro`):
```
OFFERTA SU QUALSIASI SITO → ADD TO CART / BUY NOW → UNIVERSAL CART → CHECKOUT → PAYPAL / CARTA → CONFERMA → ORDINE → DELIVERY
```

Nessuna duplicazione di codice o checkout separato per ogni sito. Tutti i portali si collegano all'infrastruttura centrale tramite l'**SDK JavaScript universale** (`universal-cart-sdk.js`) o le **API REST headless** (`api-cart.php`, `api-checkout.php`).

---

## 2. SECURITY & SOURCE OF TRUTH (REGOLA INVIOLABILE)
- **Prezzi e Condizioni Server-Side**: Il browser o l'app inviano esclusivamente l'`offer_id`. Prezzo, valuta, IVA/tasse e condizioni di erogazione vengono recuperati server-side dalla tabella `commerce_offers` (Source of Truth). Nessun prezzo inviato dal frontend viene preso in considerazione.
- **Verifica Pagamento Obbligatoria**: Un ordine **NON viene MAI considerato pagato** in base a un semplice redirect del client. Lo stato `PAID` viene concesso solo a seguito di:
  1. Chiamata API server-side di cattura ordine PayPal (`/v2/checkout/orders/{id}/capture`) con codice `201/200` e stato `COMPLETED`.
  2. Verifica di esatta corrispondenza tra importo registrato e importo catturato (`abs($paid - $total) < 0.05`).
  3. Verifica valuta (`EUR`).
  4. Webhook PayPal crittograficamente verificato tramite API PayPal e controllo di idempotenza su tabella `commerce_payment_events`.
- **Nessun Segreto Versionato**: Le credenziali PayPal Live (Client ID e Secret) risiedono esclusivamente in `.env` protetto e ignorato da Git. Nessuna chiave è esposta nel client JS o nel repository.

---

## 3. MULTI-DOMAIN CATALOG & DATA MODEL
Il modello dati risiede su SQLite con WAL mode (`data/acat_community.sqlite`), pienamente strutturato con chiavi uniche e indici per alta scalabilità:

| Entità | Tabella | Descrizione |
|---|---|---|
| **BUSINESS** | `commerce_businesses` | Brand / Progetto (`biz_dependex`, `biz_mircopregnolato`, `biz_mywallet`, `biz_betterway`, `biz_neuralog`) |
| **PRODUCT** | `commerce_products` | Prodotto astratto (corso, servizio, digitale, abbonamento) con SKU e default fulfillment |
| **OFFER** | `commerce_offers` | **Source of Truth** prezzi, tier M.A.G.I.C., bonus, garanzie e billing interval |
| **CART** | `commerce_carts` | Carrello sessione multi-brand con token persistente |
| **CART_ITEM** | `commerce_cart_items` | Singolo articolo nel carrello con quantità e prezzo server-verified |
| **CUSTOMER** | `commerce_customers` | Anagrafica cliente con dati fiscali e consensi legali |
| **ORDER** | `commerce_orders` | Ordine con numero univoco (es. `DX-20260905-XXXXXX`) e stati `PENDING`, `PAID`, `FULFILLED` |
| **ORDER_ITEM** | `commerce_order_items` | Snapshot immutabile degli articoli acquistati |
| **PAYMENT** | `commerce_payments` | Record del pagamento PayPal con ID transazione, importo e dati del pagatore |
| **PAYMENT_EVENT** | `commerce_payment_events` | Registro webhook ed eventi idempotenti per evitare doppi accrediti |
| **SUBSCRIPTION** | `commerce_subscriptions` | Gestione ricorrente / membership attiva o cancellata |
| **COUPON** | `commerce_coupons` | Codici promo con controllo spesa minima e scadenza |
| **FULFILLMENT** | `commerce_fulfillments` | Consegna asset digitali, token di sblocco e accredito DRX loyalty |
| **AUDIT_EVENT** | `commerce_audit_events` | Tracciamento immutabile di ogni transazione con IP e User-Agent |

---

## 4. INTEGRAZIONE RAPIDA SU QUALSIASI SITO DELL'UNIVERSO

### Opzione A: Drop-in Embed SDK (Consigliata)
Inserisci questo tag `<script>` in qualsiasi pagina HTML/PHP di qualsiasi sito (`mircopregnolato.it`, `mywallet.business`, ecc.):

```html
<script src="https://dependex.social/assets/js/universal-cart-sdk.js" data-commerce-endpoint="https://dependex.social"></script>
```

#### 1. Buy Now (Checkout Istantaneo)
```html
<button data-dx-buy="off_starter_kit">
  Acquista Starter Kit Subito (€ 27)
</button>
```

#### 2. Add to Cart (Aggiungi al Carrello con Drawer)
```html
<button data-dx-cart-add="off_core_proto">
  Aggiungi al Carrello (€ 497)
</button>
```

#### 3. Chiamate programmatiche JavaScript
```javascript
// Aggiungi al carrello da codice
window.dxCommerce.addToCart('off_starter_kit', 1, { utm_source: 'linkedin', utm_campaign: 'promo_primavera' });

// Acquista subito con reindirizzamento
window.dxCommerce.buyNow('off_core_proto');

// Apri il cassetto laterale del carrello
window.dxCommerce.openDrawer();
```

---

## 5. API REST HEADLESS

### Cart API (`/api-cart.php`)
- `GET /api-cart.php?action=get`: Restituisce lo stato del carrello, articoli e totali.
- `POST /api-cart.php?action=add`: `{ "offer_id": "off_starter_kit", "quantity": 1 }`.
- `POST /api-cart.php?action=update`: `{ "item_id": "itm_...", "quantity": 2 }`.
- `POST /api-cart.php?action=remove`: `{ "item_id": "itm_..." }`.
- `POST /api-cart.php?action=apply_coupon`: `{ "code": "WELCOME10" }`.
- `POST /api-cart.php?action=clear`: Svuota il carrello.

### Checkout API (`/api-checkout.php`)
- `POST /api-checkout.php?action=create_paypal_order`:
  Valida i dati cliente, crea l'ordine interno `PENDING` e genera l'ordine PayPal REST v2 restituendo `paypal_order_id`.
- `POST /api-checkout.php?action=capture_paypal_order`:
  Esegue la cattura server-side su PayPal, verifica lo stato `COMPLETED`, valida importo e valuta, segna l'ordine come `PAID`, disattiva il carrello ed esegue il fulfillment immediato.

### Webhook PayPal (`/webhook-paypal.php`)
- Endpoint per ricezione eventi asincroni (`PAYMENT.CAPTURE.COMPLETED`, `CHECKOUT.ORDER.APPROVED`, `BILLING.SUBSCRIPTION.*`).
- Crittograficamente verificato e protetto contro replay attack.

---

## 6. PAGINE PUBBLICHE REALIZZATE
1. **Carrello Universale**: `https://dependex.social/cart.php`
2. **Checkout Sicuro**: `https://dependex.social/checkout.php`
3. **Conferma & Ricevuta Ordine**: `https://dependex.social/order-confirmation.php?order_id=...`
4. **Console Amministrazione Ordini**: `https://dependex.social/admin-orders.php`
5. **Catalogo Offerte M.A.G.I.C.**: `https://dependex.social/offers.php`

---

## 7. REPORT DI TEST E VERIFICA AUTOMATIZZATA
Eseguito test suite E2E completo (`tests/test_universal_commerce_e2e.php`):
- **19 su 19 test superati con successo**:
  - Verifica catalogo e offerte attive
  - Inizializzazione token carrello
  - Rifiuto manomissione prezzo frontend (autorità server-side verificata)
  - Carrello multi-brand cross-domain
  - Calcolo subtotale e totali
  - Applicazione coupon `WELCOME10` (sconto percentuale 10% applicato correttamente)
  - Risoluzione cliente con dati di fatturazione
  - Creazione ordine interno persistente
  - Integrazione PayPal REST API Live:
    - **OAuth2 Token generato**: `APP-2KJ57216CA3853319`
    - **Ordine PayPal Live creato**: ID PayPal `9TU12300F5254115V`
    - **Stato PayPal**: `CREATED`
  - Fulfillment digitale con generazione chiavi di accesso e accredito reward loyalty DRX
  - Audit log immutabile registrato
