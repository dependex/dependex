<?php
/* ============================================================================
   COMPANY BRAIN — quality/feedback.php
   Chi legge una risposta puo' votare il neurone usato (utile / non utile) e
   lasciare una correzione in testo libero.
   Due regole:
     - il voto sposta feedback_score di poco, e il reranker lo legge CLAMPATO:
       nessuno puo' ribaltare il motore a furia di clic;
     - la correzione resta SEMPRE testo grezzo non pubblicato. Niente arriva
       agli utenti senza revisione umana.
   Un voto per nodo, per impronta IP, per giorno (indice unico).
============================================================================ */
require_once __DIR__ . '/../core/db.php';
require_once __DIR__ . '/../core/security.php';
require_once __DIR__ . '/../core/log.php';

/** Registra un voto. Ritorna un esito descrittivo. */
function brain_feedback_vote(string $nodeId, int $vote, string $question = '', string $correction = ''): array {
    $pdo = brain_pdo();
    if (!$pdo || !brain_has_table(brain_t('feedback'))) { return ['ok' => false, 'error' => 'schema non installato']; }
    if ($nodeId === '' || !in_array($vote, [1, -1], true)) { return ['ok' => false, 'error' => 'node_id e vote(1|-1) obbligatori']; }
    if (!brain_scalar('SELECT 1 FROM ' . brain_t('nodes') . ' WHERE id=? LIMIT 1', [$nodeId], null)) {
        return ['ok' => false, 'error' => 'nodo inesistente'];
    }
    $day = gmdate('Y-m-d');
    try {
        $st = $pdo->prepare('INSERT INTO ' . brain_t('feedback') .
            ' (node_id, vote, question, correction, ip_hash, day, created_at) VALUES (?,?,?,?,?,?,?)');
        $st->execute([$nodeId, $vote, brain_cut($question, 300), brain_cut($correction, 1500),
                      brain_ip_hash($nodeId), $day, brain_now()]);
    } catch (Throwable $e) {
        $m = $e->getMessage();
        /* solo la violazione dell'indice unico e' un "hai gia' votato oggi";
           ogni altro errore torna un errore vero, cosi' un voto perso non
           viene spacciato per successo. */
        if (stripos($m, 'unique') !== false || stripos($m, 'constraint') !== false || stripos($m, 'duplicate') !== false) {
            return ['ok' => true, 'already_voted' => true];
        }
        return ['ok' => false, 'error' => 'database occupato, riprova'];
    }
    brain_exec('UPDATE ' . brain_t('nodes') . ' SET feedback_score = COALESCE(feedback_score,0) + ? WHERE id=?', [$vote, $nodeId]);
    brain_activity('feedback', $nodeId . ' voto:' . $vote . ($correction !== '' ? ' (+correzione)' : ''));
    $score = (int)brain_scalar('SELECT COALESCE(feedback_score,0) FROM ' . brain_t('nodes') . ' WHERE id=?', [$nodeId], 0);
    return ['ok' => true, 'node_id' => $nodeId, 'feedback_score' => $score];
}

/** Le correzioni ricevute (SOLO admin: e' testo non rivisto di sconosciuti). */
function brain_feedback_list(int $n = 50): array {
    if (!brain_has_table(brain_t('feedback'))) { return []; }
    return brain_rows('SELECT id, node_id, vote, question, correction, created_at FROM ' . brain_t('feedback') .
        ' ORDER BY id DESC LIMIT ' . max(1, min(200, $n)));
}

/** I nodi piu' bocciati: dove la conoscenza e' sbagliata o manca. */
function brain_feedback_worst(int $n = 10): array {
    if (!brain_has_table(brain_t('nodes'))) { return []; }
    return brain_rows('SELECT id, title, path, feedback_score FROM ' . brain_t('nodes') .
        ' WHERE COALESCE(feedback_score,0) < 0 ORDER BY feedback_score ASC LIMIT ' . max(1, min(100, $n)));
}
