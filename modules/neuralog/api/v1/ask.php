<?php
/* ============================================================================
   COMPANY BRAIN — api/v1/ask.php
   Prepara il materiale RAG per una domanda: contesto, fonti e system prompt.
   NON chiama nessun modello: la chiamata al modello resta dell'applicazione
   ospite, che e' l'unica a sapere quale modello usare e con quale chiave.
   POST (o GET) /api/v1/ask.php?q=...
   Con &complete=1 + answer=... si registra la risposta finale (memoria +
   apprendimento in stato 'pending', mai pubblicato in automatico).
============================================================================ */
require_once __DIR__ . '/_boot.php';
$admin = brain_api_gate('ask');

$q = (string)brain_param('q', '');
if ($q === '') { brain_json(['ok' => false, 'error' => 'parametro q obbligatorio'], 400); }

if (brain_param('complete', '') !== '') {
    brain_require_post();
    $answer = (string)brain_param('answer', '');
    $grounded = (string)brain_param('grounded', '1') === '1';
    if ($answer === '') { brain_json(['ok' => false, 'error' => 'parametro answer obbligatorio'], 400); }
    $r = brain_ask_complete($q, $answer, $grounded, ['admin' => $admin]);
    brain_json($r);
}

$pack = brain_ask($q, ['admin' => $admin, 'n' => max(1, min(20, (int)brain_param('n', (int)brain_cfg('rag.top_k', 8))))]);
brain_json([
    'ok'       => true,
    'question' => $pack['question'],
    'grounded' => $pack['grounded'],
    'sources'  => $pack['sources'],
    'context'  => $pack['context'],
    'prompt'   => $pack['prompt'],
]);
