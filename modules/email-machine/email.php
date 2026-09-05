<?php
/* ============================================================
   Destino Randagio — Email Machine (Hostinger, info@destinorandagio.it)
   Funzioni per: welcome, carrello abbandonato, newsletter, conferma ordine.
   Usa mail() di PHP (su Hostinger imposta l'email info@ come mittente reale).
   Per deliverability: configura SPF/DKIM su Hostinger per destinorandagio.it.
   ============================================================ */
define('DR_FROM', 'DEPENDEX <info@dependex.social>');

function dr_send($to, $subject, $html){
  $headers  = "MIME-Version: 1.0\r\n";
  $headers .= "Content-Type: text/html; charset=UTF-8\r\n";
  $headers .= "From: " . DR_FROM . "\r\n";
  $headers .= "Reply-To: info@dependex.social\r\n";
  $ok = @mail($to, '=?UTF-8?B?'.base64_encode($subject).'?=', $html, $headers);
  if (isset($GLOBALS['pdo'])) {
    try { $GLOBALS['pdo']->prepare("INSERT INTO emails_log(kind,dest,subject,status) VALUES(?,?,?,?)")
      ->execute(['send',$to,$subject,$ok?'sent':'fail']); } catch(Exception $e){}
  }
  return $ok;
}

function dr_wrap($title, $body, $cta_url='', $cta_txt=''){
  $btn = $cta_url ? "<a href='$cta_url' style='display:inline-block;background:#D4AF37;color:#160f00;font-weight:800;text-decoration:none;padding:12px 24px;border-radius:30px'>$cta_txt</a>" : '';
  return "<div style='background:#0A0A0A;color:#eee;font-family:Arial,sans-serif;padding:0;margin:0'>
    <div style='max-width:560px;margin:0 auto;padding:30px 24px'>
      <div style='text-align:center;color:#D4AF37;font-weight:900;font-size:22px;letter-spacing:2px'>♛ DESTINO RANDAGIO</div>
      <h1 style='color:#D4AF37;font-size:22px;margin:22px 0 12px'>$title</h1>
      <div style='color:#ddd;font-size:15px;line-height:1.7'>$body</div>
      <div style='text-align:center;margin:24px 0'>$btn</div>
      <div style='color:#666;font-size:12px;border-top:1px solid #333;padding-top:14px;margin-top:20px'>Destino Randagio · Labo Tecnic Studio · P.IVA IT01504180298 · <a href='https://destinorandagio.it' style='color:#D4AF37'>destinorandagio.it</a></div>
    </div></div>";
}

function dr_welcome($to){
  $b = "Sei entrato nel <b>Branco</b>. Da oggi ricevi in anteprima le uscite, i drop NFT e le promo riservate.<br><br><i>\"Dove eravamo liberi, torneremo ancora.\"</i>";
  return dr_send($to, "Benvenuto nel Branco ♛", dr_wrap("Benvenuto, sei del Branco", $b, "https://destinorandagio.it", "Scopri gli album"));
}

function dr_abandoned($to, $items){
  $list = '<ul>'; foreach($items as $it){ $list .= '<li>'.htmlspecialchars($it).'</li>'; } $list .= '</ul>';
  $b = "Hai lasciato qualcosa nel carrello. L'edizione è limitata: quando finisce, finisce.<br>$list Torna a prendere il tuo Destino.";
  return dr_send($to, "Hai lasciato il tuo Destino nel carrello ♛", dr_wrap("Il tuo carrello ti aspetta", $b, "https://destinorandagio.it/#shop", "Completa l'ordine"));
}

function dr_newsletter($to, $subject, $htmlbody){
  return dr_send($to, $subject, dr_wrap($subject, $htmlbody, "https://destinorandagio.it", "Vai al sito"));
}
