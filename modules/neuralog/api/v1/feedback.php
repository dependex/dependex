<?php
/* ============================================================================
   COMPANY BRAIN — api/v1/feedback.php
   Votare e' pubblico (POST, un voto per nodo/impronta/giorno).
   Leggere le correzioni e' SOLO admin: e' testo non rivisto scritto da
   sconosciuti, non deve finire davanti a nessuno senza revisione.
   POST node_id=...&vote=1|-1[&question=...&correction=...]
   GET  ?list=1  (admin)
============================================================================ */
require_once __DIR__ . '/_boot.php';
$admin = brain_api_gate('feedback');

if (brain_param('list', '') !== '') {
    if (!$admin) { brain_json(['ok' => false, 'error' => 'forbidden'], 403); }
    brain_json(['ok' => true, 'items' => brain_feedback_list((int)brain_param('n', 50))]);
}

brain_require_post();
if (!brain_csrf_ok()) { brain_json(['ok' => false, 'error' => 'token mancante'], 403); }

$r = brain_feedback_vote(
    (string)brain_param('node_id', ''),
    (int)brain_param('vote', 0),
    (string)brain_param('question', ''),
    (string)brain_param('correction', '')
);
brain_json($r, empty($r['ok']) ? 400 : 200);
