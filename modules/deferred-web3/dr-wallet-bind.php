<?php
/* ============================================================================
   WALLET BIND — collega l'indirizzo Polygon dell'utente (self-custody).
   NESSUNA chiave privata/seed passa dal server (regola d'oro). Solo l'indirizzo
   pubblico 0x..., univoco (un wallet = un account). L'utente deve essere loggato.
   Destino Randagio · 2026-08-10 · Cowork.
============================================================================ */
require_once __DIR__.'/db.php';
@require_once __DIR__.'/inc/dr-antifraud.php';
$pdo = $GLOBALS['pdo'] ?? ($pdo ?? null);
header('Content-Type: application/json; charset=utf-8');
if(session_status()!==PHP_SESSION_ACTIVE) @session_start();
if(!($pdo instanceof PDO)){ http_response_code(500); exit('{"ok":false,"err":"no-db"}'); }

$uid=(int)($_SESSION['uid']??0);
if($uid<=0){ http_response_code(403); exit('{"ok":false,"err":"login-richiesto"}'); }

$addr = trim((string)($_POST['address'] ?? $_GET['address'] ?? ''));
if(!preg_match('/^0x[a-fA-F0-9]{40}$/',$addr)){ echo json_encode(['ok'=>false,'err'=>'indirizzo Polygon non valido (0x + 40 esadecimali)']); exit; }

/* unicita': un wallet = un account */
if(function_exists('af_wallet_taken') && af_wallet_taken($pdo,$addr,$uid)){ echo json_encode(['ok'=>false,'err'=>'wallet gia\' collegato a un altro account']); exit; }
try{ if(function_exists('af_migra')) af_migra($pdo); }catch(Throwable $e){}

try{ $pdo->prepare("UPDATE users SET wallet=? WHERE id=?")->execute([$addr,$uid]); }
catch(Throwable $e){ echo json_encode(['ok'=>false,'err'=>'wallet gia\' in uso']); exit; }
if(function_exists('af_touch')){ try{ af_touch($pdo,($_SERVER['REMOTE_ADDR']??''),'',$uid,'wallet_bind'); }catch(Throwable $e){} }

echo json_encode(['ok'=>true,'wallet'=>$addr,'polygonscan'=>'https://polygonscan.com/address/'.$addr], JSON_UNESCAPED_UNICODE);
