<?php
/* ============================================================
   Destino Randagio — TEMPLATE EMAIL (HTML, inline CSS, neuro-copy)
   Tipi: welcome · purchase · newsletter · promo · song-ready · abandoned
   Uso:  require_once 'email-templates.php';
         dr_send_html($to,$subj, dr_email_welcome('Mirco'));
   ============================================================ */

if(!defined('DR_SITE')) define('DR_SITE','https://destinorandagio.it');
/* Logo di testata: PNG assoluto (le email non renderizzano il webp in modo
   affidabile). Override possibile da .env: DR_EMAIL_LOGO. */
if(!defined('DR_LOGO')){
  $__logo = function_exists('dr_env') ? dr_env('DR_EMAIL_LOGO','') : '';
  define('DR_LOGO', $__logo ?: DR_SITE.'/assets/LOGO%20DR%20Corona%20ok.png');
}
/* palette email brand: nero, oro, bianco */
if(!defined('DR_NERO')) define('DR_NERO','#0D0D0D');
if(!defined('DR_ORO'))  define('DR_ORO','#D4AF37');
if(!defined('DR_BIANCO')) define('DR_BIANCO','#FFFFFF');

/* --- da HTML a testo semplice (fallback plain-text per multipart/alternative) --- */
function dr_html_to_text($html){
  $t = (string)$html;
  $t = preg_replace('/<(style|script)[^>]*>.*?<\/\1>/is','',$t);
  /* i bottoni/link: tieni "testo (url)" */
  $t = preg_replace_callback('/<a[^>]+href="([^"]+)"[^>]*>(.*?)<\/a>/is',function($m){
    $u=html_entity_decode(strip_tags($m[1]),ENT_QUOTES,'UTF-8'); $x=trim(html_entity_decode(strip_tags($m[2]),ENT_QUOTES,'UTF-8'));
    if($u==='' || strpos($u,'{{')!==false) return $x; return $x.' ( '.$u.' )';
  },$t);
  $t = preg_replace('/<\/(p|div|tr|h1|h2|h3|li|table)>/i',"\n",$t);
  $t = preg_replace('/<br\s*\/?>/i',"\n",$t);
  $t = strip_tags($t);
  $t = html_entity_decode($t,ENT_QUOTES,'UTF-8');
  $t = preg_replace('/[ \t]+/',' ',$t);
  $t = preg_replace('/\n{3,}/',"\n\n",$t);
  return trim($t);
}

/* --- invio HTML (multipart/alternative: text + html per deliverability) --- */
function dr_send_html($to,$subject,$html,$from='info@dependex.social'){
  /* placeholder unsub residuo -> pagina generica (i flussi lo sostituiscono con token in mkt_wrap) */
  $html = str_replace('{{UNSUB}}', DR_SITE.'/unsubscribe.php', $html);
  $text = dr_html_to_text($html);
  $b = 'dr'.bin2hex(random_bytes(8));
  $h ="MIME-Version: 1.0\r\n";
  $h.="From: Destino Randagio <$from>\r\n";
  $h.="Reply-To: $from\r\n";
  $h.="Content-Type: multipart/alternative; boundary=\"$b\"\r\n";
  $body ="--$b\r\nContent-Type: text/plain; charset=UTF-8\r\nContent-Transfer-Encoding: 8bit\r\n\r\n".$text."\r\n\r\n";
  $body.="--$b\r\nContent-Type: text/html; charset=UTF-8\r\nContent-Transfer-Encoding: 8bit\r\n\r\n".$html."\r\n\r\n--$b--";
  return @mail($to,'=?UTF-8?B?'.base64_encode($subject).'?=',$body,$h);
}

/* ============================================================
   LAYOUT PREMIUM — bulletproof, table-based, CSS inline, 600px.
   UN template parametrico: header corona + rule oro, HERO opz,
   corpo elegante serif, CTA VML-safe, PRODUCT CARD opz, divider
   ornamentale, footer con UNSUB visibile ({{UNSUB}} = token o
   fallback). Compatibile Gmail/Outlook(mso)/Apple Mail; dark-safe.
   Firma: dr_email_layout($preheader,$bodyHtml,$opts) — retro-compat
   a 2 argomenti. $opts: hero_img, hero_title, unsub, from_label.
   ============================================================ */
function dr_email_layout($preheader,$bodyHtml,$opts=[]){
  $y=date('Y');
  $unsub = $opts['unsub'] ?? '{{UNSUB}}';           // i flussi (mkt_wrap) lo rimpiazzano col token
  $heroImg = $opts['hero_img'] ?? '';
  $heroTitle = $opts['hero_title'] ?? '';
  $fromLabel = $opts['from_label'] ?? '';
  $hero='';
  if($heroImg!==''){
    $hero='<tr><td style="padding:0" bgcolor="#0D0D0D" background="'.$heroImg.'">
      <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0"><tr>
        <td height="210" align="center" valign="middle" style="height:210px;background:rgba(8,6,2,.55);padding:0 24px">
          '.($heroTitle!=='' ? '<h1 style="margin:0;color:#D4AF37;font-family:Georgia,\'Times New Roman\',serif;font-weight:normal;font-size:30px;line-height:1.2;text-shadow:0 2px 8px #000">'.$heroTitle.'</h1>' : '&nbsp;').'
        </td></tr></table></td></tr>';
  }
  return '<!DOCTYPE html><html lang="it" xmlns="http://www.w3.org/1999/xhtml" xmlns:v="urn:schemas-microsoft-com:vml" xmlns:o="urn:schemas-microsoft-com:office:office"><head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1"><meta http-equiv="X-UA-Compatible" content="IE=edge">
<meta name="color-scheme" content="dark light"><meta name="supported-color-schemes" content="dark light">
<title>'.htmlspecialchars($opts['subject'] ?? 'Destino Randagio',ENT_QUOTES).'</title>
<!--[if mso]><style type="text/css">table,td{border-collapse:collapse;mso-table-lspace:0;mso-table-rspace:0} .serif{font-family:Georgia,\'Times New Roman\',serif !important}</style><![endif]--></head>
<body style="margin:0;padding:0;background:#0D0D0D;-webkit-text-size-adjust:100%;-ms-text-size-adjust:100%;">
<div style="display:none;max-height:0;overflow:hidden;opacity:0;mso-hide:all;font-size:1px;line-height:1px;color:#0D0D0D">'.htmlspecialchars($preheader,ENT_QUOTES).'&#8203;&#8203;&#8203;&#8203;&#8203;&#8203;&#8203;&#8203;</div>
<table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="background:#0D0D0D">
<tr><td align="center" style="padding:26px 12px">
  <table role="presentation" width="600" cellpadding="0" cellspacing="0" border="0" style="width:600px;max-width:100%;background:#12100a;border:1px solid rgba(212,175,55,.28)">
    <!-- HEADER -->
    <tr><td align="center" bgcolor="#0D0D0D" style="background:#0D0D0D;padding:26px 20px 14px">
      <img src="'.DR_LOGO.'" alt="Destino Randagio" width="120" style="display:block;width:120px;max-width:60%;height:auto;margin:0 auto 8px">
      <div style="color:#D4AF37;font-family:Georgia,serif;font-weight:normal;letter-spacing:4px;font-size:12px">DESTINO&nbsp;RANDAGIO&nbsp;&#9819;</div>
    </td></tr>
    <tr><td bgcolor="#0D0D0D" style="background:#0D0D0D;padding:0 40px"><table role="presentation" width="100%"><tr><td style="border-top:2px solid #D4AF37;line-height:1px;font-size:1px">&nbsp;</td></tr></table></td></tr>
    '.$hero.'
    <!-- CORPO -->
    <tr><td style="padding:30px 40px 24px;font-family:-apple-system,\'Segoe UI\',Arial,Helvetica,sans-serif;color:#EDE6D6;font-size:16px;line-height:1.7">
      '.$bodyHtml.'
    </td></tr>
    <!-- FOOTER -->
    <tr><td bgcolor="#0D0D0D" style="background:#0D0D0D;border-top:1px solid rgba(212,175,55,.22);padding:22px 34px;font-family:Arial,Helvetica,sans-serif;color:#8a7f68;font-size:11px;line-height:1.7;text-align:center">
      <div style="color:#D4AF37;font-family:Georgia,serif;letter-spacing:2px;font-size:13px">DESTINO RANDAGIO &middot; ITALY</div>
      <div style="color:#b8a878;font-style:italic;margin:2px 0 10px">dal fango alle stelle</div>
      Destino Randagio &middot; Via Mantovana 78, 45014 Porto Viro (RO) &middot; P.IVA IT01504180298<br>
      <a href="'.DR_SITE.'" style="color:#D4AF37;text-decoration:none">destinorandagio.it</a> &nbsp;&middot;&nbsp;
      <a href="'.DR_SITE.'/index.html#social" style="color:#8a7f68;text-decoration:none">Social</a> &nbsp;&middot;&nbsp;
      <a href="'.$unsub.'" style="color:#b8a878;text-decoration:underline">Disiscriviti</a><br>
      '.($fromLabel!=='' ? htmlspecialchars($fromLabel,ENT_QUOTES).'<br>' : '').'
      <span style="color:#6d6350">&copy; '.$y.' Destino Randagio &middot; Ricevi questa email perch&eacute; fai parte del Branco.</span>
    </td></tr>
  </table>
</td></tr></table></body></html>';
}

/* --- pulsante CTA oro BULLETPROOF (VML per Outlook) --- */
function dr_btn($text,$url){
  $u=htmlspecialchars($url,ENT_QUOTES);
  return '<table role="presentation" cellpadding="0" cellspacing="0" border="0" align="center" style="margin:26px auto"><tr><td align="center">
  <!--[if mso]><v:roundrect xmlns:v="urn:schemas-microsoft-com:vml" xmlns:w="urn:schemas-microsoft-com:office:word" href="'.$u.'" style="height:48px;v-text-anchor:middle;width:300px;" arcsize="50%" strokecolor="#D4AF37" fillcolor="#D4AF37"><w:anchorlock/><center style="color:#160f00;font-family:Arial,sans-serif;font-size:14px;font-weight:bold;letter-spacing:.5px">'.$text.'</center></v:roundrect><![endif]-->
  <!--[if !mso]><!-- --><a href="'.$u.'" style="display:inline-block;background:#D4AF37;padding:15px 34px;font-family:Arial,Helvetica,sans-serif;font-weight:bold;font-size:14px;color:#160f00;text-decoration:none;letter-spacing:.5px;border-radius:30px">'.$text.'</a><!--<![endif]-->
  </td></tr></table>';
}
function h1($t){ return '<h1 class="serif" style="color:#D4AF37;font-family:Georgia,\'Times New Roman\',serif;font-size:26px;margin:2px 0 16px;line-height:1.28;font-weight:normal">'.$t.'</h1>'; }
function gold($t){ return '<span style="color:#D4AF37">'.$t.'</span>'; }

/* --- DIVIDER ornamentale: rule oro con corona centrale, restrained --- */
function dr_divider(){
  return '<table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0"><tr><td align="center" style="padding:16px 0">
    <table role="presentation" cellpadding="0" cellspacing="0" border="0"><tr>
      <td width="110" style="width:110px;border-top:1px solid rgba(212,175,55,.4);line-height:1px;font-size:1px">&nbsp;</td>
      <td style="padding:0 12px;color:#D4AF37;font-size:15px">&#9819;</td>
      <td width="110" style="width:110px;border-top:1px solid rgba(212,175,55,.4);line-height:1px;font-size:1px">&nbsp;</td>
    </tr></table></td></tr></table>';
}

/* --- PRODUCT CARD opzionale (shop/album/nft): immagine+titolo+prezzo+mini-CTA --- */
function dr_email_product_card($img,$title,$price='',$url='',$cta='Scopri'){
  $u=htmlspecialchars($url?:DR_SITE,ENT_QUOTES);
  $priceHtml = $price!=='' ? '<div style="color:#FB6B00;font-weight:bold;font-size:17px;margin:4px 0 8px">'.htmlspecialchars($price).'</div>' : '';
  return '<table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="margin:18px 0;background:#151006;border:1px solid rgba(212,175,55,.25);border-radius:12px"><tr>
    <td style="padding:16px" align="center">
      '.($img?'<img src="'.htmlspecialchars($img,ENT_QUOTES).'" alt="'.htmlspecialchars($title,ENT_QUOTES).'" width="240" style="display:block;width:100%;max-width:240px;height:auto;border-radius:8px;margin:0 auto 12px">':'').'
      <div style="color:#EDE6D6;font-family:Georgia,serif;font-size:18px">'.htmlspecialchars($title).'</div>
      '.$priceHtml.'
      <a href="'.$u.'" style="display:inline-block;background:#D4AF37;color:#160f00;font-family:Arial,sans-serif;font-weight:bold;font-size:13px;text-decoration:none;padding:9px 22px;border-radius:24px;margin-top:6px">'.$cta.' &rarr;</a>
    </td></tr></table>';
}
/* HERO come blocco autonomo (per chi non usa $opts['hero_img']) */
function dr_email_hero($img,$title=''){ return $img; }

/* ============ 1) WELCOME ============ */
function dr_email_welcome($name='Randagio'){
  $b = h1('Benvenuto nel '.gold('Branco').', '.$name.'.').
   '<p>Non sei arrivato qui per caso. Chi trova Destino Randagio, di solito, sta cercando qualcosa: una spinta, una voce, un motivo per rialzarsi.</p>
    <p><b>Da oggi non sei più solo.</b> Fai parte di una storia che nasce dal fango del Delta del Po e punta dritta alle stelle. Ogni 20 giorni un nuovo album. Ogni settimana qualcosa di esclusivo. E per te, che sei entrato oggi, un vantaggio che gli altri non hanno.</p>
    <p style="background:rgba(212,175,55,.1);border:1px solid rgba(212,175,55,.4);border-radius:12px;padding:14px 16px;color:#ffe9a8">
    🎁 <b>Regalo di benvenuto:</b> ogni <b>100€</b> di spesa ti sblocca un buono <b>−15%</b> automatico. I soldi che spendi, tornano da te.</p>
    <p>Il primo passo? Scegli il tuo posto nel Branco.</p>'.
   dr_btn('Entra nel Branco →', DR_SITE.'/membership.html').
   '<p style="color:#9aa0a6;font-size:13px">Un randagio non chiede permesso. Prende il suo posto. — Destino Randagio ♛</p>';
  return dr_email_layout('Benvenuto nel Branco — il tuo regalo ti aspetta', $b);
}

/* ============ 2) PURCHASE ============ */
function dr_email_purchase($name,$items,$total,$ref){
  $rows='';
  foreach((array)$items as $it){
    $n=htmlspecialchars($it['name']??'Prodotto'); $p=number_format((float)($it['price']??0),2,',','.');
    $rows.='<tr><td style="padding:8px 0;border-bottom:1px solid #222;color:#ddd;font-size:14px">'.$n.'</td>
      <td align="right" style="padding:8px 0;border-bottom:1px solid #222;color:#D4AF37;font-size:14px;font-weight:bold">€'.$p.'</td></tr>';
  }
  $tot=number_format((float)$total,2,',','.');
  $b = h1('Ordine confermato. '.gold('Grazie').', '.$name.'.').
   '<p>Il tuo ordine <b>'.htmlspecialchars($ref).'</b> è confermato e in lavorazione. Hai appena preso un pezzo di Destino — e il Destino non delude.</p>
    <table role="presentation" width="100%" style="margin:16px 0">'.$rows.'
    <tr><td style="padding:12px 0 0;color:#fff;font-weight:bold;font-size:16px">Totale</td>
        <td align="right" style="padding:12px 0 0;color:#D4AF37;font-weight:bold;font-size:18px">€'.$tot.'</td></tr></table>
    <p style="background:rgba(38,161,123,.12);border:1px solid rgba(38,161,123,.4);border-radius:12px;padding:12px 16px;color:#7dffc0">
    ♻️ Questo acquisto fa crescere il tuo <b>cashback</b>: sei più vicino al prossimo buono −15%.</p>'.
   dr_btn('Vedi il mio ordine →', DR_SITE.'/account.php').
   '<p style="color:#9aa0a6;font-size:13px">Riceverai aggiornamenti sulla consegna. Per assistenza: info@dependex.social</p>';
  return dr_email_layout('Ordine '.$ref.' confermato — grazie dal Branco', $b);
}

/* ============ 3) NEWSLETTER ============ */
function dr_email_newsletter($name,$headline,$bodyHtml,$ctaText='Ascolta ora',$ctaUrl=null){
  $ctaUrl=$ctaUrl?:DR_SITE.'/index.html#album';
  $b = h1($headline).
   '<div style="color:#dcdcdc;font-size:15px;line-height:1.7">'.$bodyHtml.'</div>'.
   dr_btn($ctaText.' →',$ctaUrl).
   '<p style="color:#9aa0a6;font-size:13px">Ci vediamo dall\'altra parte del fiume. — DR ♛</p>';
  return dr_email_layout($headline, $b);
}

/* ============ 4) PROMO ============ */
function dr_email_promo($name,$offerTitle,$code,$percent=15,$ctaUrl=null,$deadline='48 ore'){
  $ctaUrl=$ctaUrl?:DR_SITE.'/shop.html';
  $b = h1('⚡ '.$offerTitle).
   '<p>'.$name.', questa è per te. E dura poco.</p>
    <div style="text-align:center;background:linear-gradient(135deg,#1c1608,#0e0b04);border:2px solid #D4AF37;border-radius:16px;padding:26px 18px;margin:18px 0">
      <div style="color:#fff;font-size:14px;letter-spacing:1px">SCONTO ESCLUSIVO</div>
      <div style="color:#D4AF37;font-size:52px;font-weight:900;line-height:1">-'.(int)$percent.'%</div>
      <div style="color:#ddd;font-size:13px;margin-top:6px">codice</div>
      <div style="display:inline-block;margin-top:6px;font-family:monospace;font-size:20px;letter-spacing:2px;color:#160f00;background:#D4AF37;border-radius:8px;padding:8px 18px;font-weight:bold">'.htmlspecialchars($code).'</div>
    </div>
    <p style="color:#ffce9e;text-align:center;font-weight:bold">🔥 Scade tra '.$deadline.'. Quando finisce, finisce.</p>'.
   dr_btn('Usa il codice ora →',$ctaUrl).
   '<p style="color:#9aa0a6;font-size:13px">Chi esita, resta a guardare. Chi agisce, entra nella storia.</p>';
  return dr_email_layout($offerTitle.' — codice '.$code.' (scade presto)', $b);
}

/* ============ 5) SONG READY ============ */
function dr_email_song_ready($name,$to_name,$v1,$v2=''){
  $links='<p><a href="'.$v1.'" style="color:#D4AF37;font-weight:bold">▶ Versione 1</a></p>'.($v2?'<p><a href="'.$v2.'" style="color:#D4AF37;font-weight:bold">▶ Versione 2</a></p>':'');
  $b = h1('La tua canzone per '.gold($to_name).' è pronta 🎶').
   '<p>'.$name.', ci abbiamo messo l\'anima. Ecco la tua Canzone su misura, in 2 versioni, solo tua.</p>'.$links.
   dr_btn('Scarica dall\'Area Riservata →', DR_SITE.'/account.php').
   '<p style="color:#9aa0a6;font-size:13px">Falla ascoltare a chi la merita. Le emozioni vere non si dimenticano.</p>';
  return dr_email_layout('La tua Canzone su misura è pronta', $b);
}

/* ============ 6) CARRELLO ABBANDONATO ============ */
function dr_email_abandoned($name,$ctaUrl=null){
  $ctaUrl=$ctaUrl?:DR_SITE.'/index.html#cart';
  $b = h1('Hai lasciato qualcosa nel Branco…').
   '<p>'.$name.', il tuo carrello ti aspetta ancora. Ma l\'edizione è limitata e i randagi veloci arrivano primi.</p>
    <p style="background:rgba(224,138,43,.14);border:1px solid rgba(224,138,43,.5);border-radius:12px;padding:12px 16px;color:#ffce9e">
    ⏳ Completa ora: il tuo cashback fedeltà inizia a contare dal primo acquisto.</p>'.
   dr_btn('Completa l\'ordine →',$ctaUrl).
   '<p style="color:#9aa0a6;font-size:13px">Un attimo di coraggio vale più di mille rimpianti.</p>';
  return dr_email_layout('Il tuo carrello ti aspetta — edizione limitata', $b);
}

/* ---- ANTEPRIMA browser: email-templates.php?preview=welcome ---- */
if(isset($_GET['preview'])){
  $demoItems=[['name'=>'Album VOL.8 · Dove il Fiume Rinasce','price'=>29.90],['name'=>'T-Shirt Emblema Oro · M','price'=>34.90]];
  switch($_GET['preview']){
    case 'purchase':   echo dr_email_purchase('Mirco',$demoItems,64.80,'DR-2026-0001'); break;
    case 'newsletter': echo dr_email_newsletter('Mirco','Vol.9 è nato dal Grande Fiume','<p>Nuovo album. Nuova ferita trasformata in musica. <b>Ascoltalo prima di tutti.</b></p>'); break;
    case 'promo':      echo dr_email_promo('Mirco','Solo per il Branco: −15% su tutto','DR15-BRANCO'); break;
    case 'song':       echo dr_email_song_ready('Mirco','Giulia','#','#'); break;
    case 'abandoned':  echo dr_email_abandoned('Mirco'); break;
    default:           echo dr_email_welcome('Mirco');
  }
  exit;
}
