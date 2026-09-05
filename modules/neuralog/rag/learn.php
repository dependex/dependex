<?php
/* ============================================================================
   COMPANY BRAIN — rag/learn.php
   Apprendimento continuo, con il freno tirato: una risposta ANCORATA al
   contesto diventa un nodo, ma nasce SEMPRE riservato e in stato 'pending'.
   Nessun contenuto generato arriva al pubblico senza che un umano lo abbia
   promosso. E' la differenza fra un cervello che cresce e un cervello che si
   avvelena da solo.
============================================================================ */
require_once __DIR__ . '/../core/db.php';
require_once __DIR__ . '/../core/log.php';
require_once __DIR__ . '/../graph/nodes.php';
require_once __DIR__ . '/../graph/entities.php';
require_once __DIR__ . '/../graph/autolink.php';

/** Impara da una risposta ancorata. Ritorna l'id del nodo o ''. */
function brain_learn(string $question, string $answer, bool $grounded): string {
    if (!brain_cfg('learn.enabled', true) || !$grounded) { return ''; }
    $question = trim($question);
    $answer   = trim($answer);
    if (mb_strlen($question) < (int)brain_cfg('learn.min_question_len', 6)) { return ''; }
    if (mb_strlen($answer)   < (int)brain_cfg('learn.min_answer_len', 40))  { return ''; }
    if (brain_looks_secret($answer)) { return ''; }

    $id = 'learn-' . substr(hash('sha256', brain_normalize($question)), 0, 16);
    $content = "DOMANDA: " . brain_cut($question, 300) . "\nRISPOSTA: " . brain_cut($answer, 1500);
    brain_node_put([
        'id'           => $id,
        'section'      => 'learned',
        'weight'       => 2,
        'path'         => 'learned/' . brain_slug($question, 44),
        'title'        => brain_cut($question, 120),
        'content'      => $content,
        'visibility'   => (string)brain_cfg('learn.visibility', 'admin'),
        'source'       => 'chat',
        'review_state' => (string)brain_cfg('learn.review_state', 'pending'),
    ]);
    brain_entities_link($id, $content);
    $hub = brain_hub_for('chat');
    if ($hub !== '') { brain_link($id, $hub, 'hub'); }
    brain_activity('learn', $id);
    return $id;
}

/** Elenco dei nodi appresi in attesa di revisione. */
function brain_learn_pending(int $n = 50): array {
    return brain_rows('SELECT id, title, path, content, created_at FROM ' . brain_t('nodes') .
        " WHERE section='learned' AND review_state='pending' ORDER BY created_at DESC LIMIT " . max(1, min(200, $n)));
}

/** Promozione (gesto umano): approvato -> visibile al pubblico. */
function brain_learn_approve(string $id, bool $makePublic = false): bool {
    $ok = brain_exec('UPDATE ' . brain_t('nodes') . " SET review_state='approved', updated_at=? WHERE id=?",
                     [brain_now(), $id]) >= 0;
    if ($ok && $makePublic) { brain_node_set_visibility($id, 'public'); }
    if ($ok) { brain_activity('learn-approve', $id . ($makePublic ? ' (pubblico)' : '')); }
    return $ok;
}

/** Rifiuto: il nodo sparisce con le sue sinapsi. */
function brain_learn_reject(string $id): bool {
    brain_links_drop_for([$id]);
    brain_entities_drop_for([$id]);
    $ok = brain_exec('DELETE FROM ' . brain_t('nodes') . ' WHERE id=?', [$id]) >= 0;
    if ($ok) { brain_activity('learn-reject', $id); }
    return $ok;
}
