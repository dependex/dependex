<?php
/* ============================================================================
   DR-CHAT-KB — Knowledge & sistema vendita per la Chat AI del sito

   Estende (non sostituisce) branco-config.php. Aggiunge:
   - dr_chat_catalogo()  : catalogo prodotti + prezzi, iniettato nel system
   - dr_chat_promo()     : riga promo LIVE dal motore festivita (dr-feste.php)
   - dr_chat_system_site(): system prompt del bot pubblico, potenziato alla
                            conversione verso MEMBERSHIP / KIT / NFT
   - dr_admin_system()   : system prompt del bot ADMIN (assistente operativo)

   Uso in chat.php:
     require_once __DIR__.'/dr-chat-kb.php';
     $sys = ($bot==='admin') ? dr_admin_system($ctxAdmin) : dr_chat_system_site();
   Il bot 'branco' (Noemi) resta com'e in branco-config.php.
============================================================================ */

/* Catalogo sintetico con prezzi noti. NON inventare numeri: se un prezzo non
   e qui, il bot rimanda alla pagina o a info@dependex.social. */
function dr_chat_catalogo(){
  /* NFT: tier e prezzi dal catalogo REALE (nft-catalogo.php), mai hardcoded,
     cosi' il bot non cita numeri vecchi se cambi i prezzi. */
  @include_once __DIR__.'/nft-catalogo.php';
  $nftline = "- NFT del Branco (nft.html): tier e prezzi sulla pagina nft.html.\n";
  if(function_exists('nft_ordine') && function_exists('nft_tier')){
    $parts=[];
    foreach(nft_ordine() as $t){
      $c=nft_tier($t); if(!$c) continue;
      $eu=$c['euro']??null;
      $parts[]=$t.($eu!==null ? (' '.$eu.'EUR') : ' (1/1, non in vendita)');
    }
    if($parts) $nftline = "- NFT del Branco (nft.html): ".implode(' · ',$parts).
      ". Stagionali LIMITED (Xmas 25/12, Easter Pasqua, Halloween 31/10): pre-reveal con countdown, mint riservato ai Membri.\n";
  }
  return
  "CATALOGO DESTINO RANDAGIO (prezzi reali dove indicati):\n".
  "- KIT del Branco (branco.html): l'UNICA porta d'ingresso al Branco. Bundle di prodotti fisici reali + status di Membro (giochi con vincite in DRX, NFT gated, Experience, DAO, missioni, ranghi). E il prodotto n.1 da spingere.\n".
  "- Membership (membership.html): 3 livelli — Anima Randagia, Guardiano del Delta, Leggenda del Delta. Ricorrente. Da proporre subito dopo/insieme al KIT.\n".
  $nftline.
  "- Canzone su misura (song.html): 59,90 EUR. Scritta su misura sulla storia della persona. Regalo fortissimo.\n".
  "- Album digitali (albums.html): NOMADS, D.E.L.T.A. e altri capitoli.\n".
  "- Wear & Gadget (shop.html): prodotti Printful; alcuni ESCLUSIVI riservati ai Membri.\n".
  "- Carte & Cashback (carte.html), Experience di viaggio Stray Nomads (stray-nomads.php), Blog (blog.php), Storia (storia.html), Calendario dell'Avvento e giochi (game.php).\n".
  "DRX = moneta interna (non convertibile in euro): si guadagna con le azioni e si spende in sconti e premi. Mai chiamarla investimento.";
}

/* Riga promo LIVE: se c'e una festivita/promo attiva la annuncia (con lo sconto). */
function dr_chat_promo($ts=null){
  if(!function_exists('feste_attiva')){ @require_once __DIR__.'/dr-feste.php'; }
  if(!function_exists('feste_attiva')) return '';
  $f = feste_attiva($ts);
  if(!$f) return '';
  $sc = (int)round(($f['sconto'] ?? 0)*100);
  $cal = !empty($f['calendario']) ? " E' attivo il calendario stagionale '{$f['calendario']}'." : '';
  return "PROMO ATTIVA ORA: {$f['nome']}".($sc>0?" — fino al {$sc}% su prodotti selezionati (gear/wear max 20%)":"").".".$cal.
         " Menziona la promo con naturalezza quando e utile a far agire, senza inventare urgenza falsa.";
}

/* System prompt del bot PUBBLICO, potenziato alla conversione. */
function dr_chat_system_site(){
  $base = defined('BOT_SYSTEM') ? BOT_SYSTEM : "Sei Destino Randagio, la voce del Branco.";
  return $base."\n\n".dr_chat_catalogo()."\n\n".dr_chat_promo()."\n\n".
  "PRIORITA DI CONVERSIONE (in ordine): 1) KIT del Branco (la porta), 2) Membership, 3) NFT. ".
  "Metodo: una domanda per volta, ascolta il bisogno, crea desiderio con la storia del Delta, poi proponi UN passo concreto col link giusto. ".
  "Se e visitatore: portalo verso il KIT (entra nel Branco). Se e gia Membro: proponi upgrade membership, NFT, Experience, e invitare altri col referral. ".
  "CTA ammesse: Ascolta · Scopri · Continua la storia · Entra nel Branco · Lascia la tua voce · La scelta resta tua. ".
  "VIETATO: 'compra subito', urgenza falsa, 'guadagno garantito', 'investi', promesse finanziarie. Non inventare dati o prezzi non nel catalogo: rimanda a info@dependex.social. Poche emoji.";
}

/* System prompt del bot ADMIN (dashboard admin): assistente operativo, NON vendita. */
function dr_admin_system($ctx=[]){
  $oggi = date('Y-m-d');
  return
  "Sei l'assistente ADMIN di Destino Randagio (uso interno di Mirco). Data: $oggi. ".
  "Conosci l'architettura del sito: membership, KIT, NFT, DRX/81X, giochi, economia, referral 8 livelli, Printful, PayPal, email machine, Telegram, festivita (dr-feste.php), watcher (dr-log.php). ".
  "SCOPO: aiutare a gestire e capire i dati — KPI (visite, iscritti, membri, ordini, DRX in circolo, promo attive), spiegare come funziona un pezzo del sistema, suggerire azioni operative, preparare testi/annunci. ".
  "Rispondi conciso e operativo. NON eseguire azioni che muovono soldi o firmano: quelle le fa Mirco. Se manca un dato, dillo e indica dove si trova (quale pagina admin / quale funzione). Niente linguaggio da venditore: qui si lavora.".
  (isset($ctx['nota']) ? "\nContesto: ".$ctx['nota'] : '');
}
