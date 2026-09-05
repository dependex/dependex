<?php
/* ============================================================================
   STELLA NETWORK — questo indirizzo adesso porta al tool unico.
   Destino Randagio · 2026-08-15 · Cowork

   PERCHE' NON C'E' PIU' UNA PAGINA STELLA
   Albero dal basso, laterale e stella erano tre pagine che leggevano gli
   stessi dati con la stessa libreria: tre copie della stessa logica, e ogni
   volta che si aggiungeva una funzione bisognava aggiungerla tre volte (e
   una delle tre restava sempre indietro). Ora e' un tool solo,
   albero-network.php, con tre viste.

   Questo file resta perche' il suo indirizzo e' gia' pubblicato — nella
   dashboard admin e nei vecchi link. Non e' un rifiuto: e' un rimando, con
   la chiave passata avanti se c'era.

   Il file vecchio non e' stato buttato: e' conservato fuori dal sito.
============================================================================ */
$key = isset($_GET['key']) ? (string)$_GET['key'] : '';
$q   = 'albero-network.php?forma=stella' . ($key !== '' ? '&key=' . rawurlencode($key) : '');
header('Location: ' . $q, true, 302);
header('Cache-Control: no-store');
/* se il browser ignorasse il redirect, almeno un link ci deve essere */
echo '<!DOCTYPE html><meta charset="utf-8"><meta name="robots" content="noindex,nofollow">'
   . '<title>Stella Network</title>'
   . '<body style="background:#0a0908;color:#f2e9d8;font-family:Georgia,serif;padding:40px">'
   . '<p>La Stella ora e\' una vista del tool unico.</p>'
   . '<p><a style="color:#d9b45a" href="' . htmlspecialchars($q, ENT_QUOTES, 'UTF-8') . '">Vai alla Stella &rarr;</a></p>';
