<?php
/* ============================================================================
   COMPANY BRAIN — rag/rerank.php
   Secondo stadio, dopo il recupero. E' un reranker EURISTICO, non un modello
   neurale: qui non c'e' GPU ne' servizio esterno, e dirlo e' piu' utile che
   fingere il contrario. Quattro segnali economici:
     1) CENTRALITA'  — quante sinapsi ha il nodo (un nodo molto connesso e'
                       spesso il rappresentante giusto di un tema)
     2) FEEDBACK     — voti reali degli utenti, CLAMPATI: pochi voti non
                       possono ribaltare il risultato
     3) FRASE ESATTA — la domanda intera che compare cosi' com'e' batte sempre
                       un match sparso di termini singoli
     4) FRESCHEZZA   — decadimento dolce sull'ultimo aggiornamento
   Se le tabelle/colonne non ci sono, i segnali valgono 0 e resta il punteggio
   lessicale: additivo, mai distruttivo.
============================================================================ */
require_once __DIR__ . '/../core/db.php';
require_once __DIR__ . '/../core/text.php';
require_once __DIR__ . '/../graph/links.php';

function brain_rerank(array &$rows, string $query): void {
    if (!$rows) { return; }
    $ids = array_values(array_unique(array_column($rows, 'id')));
    if (!$ids) { return; }

    $deg = brain_has_table(brain_t('links')) ? brain_degrees($ids) : [];

    $cFac  = (float)brain_cfg('rag.centrality_factor', 0.4);
    $fFac  = (float)brain_cfg('rag.feedback_factor', 0.3);
    $fClamp= (float)brain_cfg('rag.feedback_clamp', 2.0);
    $pBon  = (float)brain_cfg('rag.exact_phrase_bonus', 3.0);
    $rFac  = (float)brain_cfg('rag.recency_factor', 0.4);
    $half  = max(1, (int)brain_cfg('rag.recency_half_life_days', 90));

    $phrase   = brain_normalize($query);
    $phraseOk = mb_strlen($phrase) >= 6;
    $now = time();

    foreach ($rows as &$r) {
        $bonus = 0.0;
        $d = (int)($deg[$r['id']] ?? 0);
        $bonus += log(1 + $d) * $cFac;
        $f = (float)($r['feedback_score'] ?? 0);
        $bonus += max(-$fClamp, min($fClamp, $f * $fFac));
        if ($phraseOk && mb_strpos(brain_normalize((string)($r['content'] ?? '')), $phrase) !== false) { $bonus += $pBon; }
        $upd = (string)($r['updated_at'] ?? '');
        if ($upd !== '' && $rFac > 0) {
            $ts = strtotime($upd . ' UTC');
            if ($ts) {
                $days = max(0.0, ($now - $ts) / 86400);
                $bonus += $rFac * pow(0.5, $days / $half);
            }
        }
        $r['score'] = (float)($r['score'] ?? 0) + $bonus;
        $r['rerank_bonus'] = round($bonus, 4);
    }
    unset($r);
    usort($rows, static function ($a, $b) { return $b['score'] <=> $a['score']; });
}
