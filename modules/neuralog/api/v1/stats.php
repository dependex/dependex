<?php
/* ============================================================================
   COMPANY BRAIN — api/v1/stats.php
   Contatori per una dashboard. SOLO admin: sapere quanto sa un'azienda e' gia'
   un'informazione.
============================================================================ */
require_once __DIR__ . '/_boot.php';
brain_api_gate('stats', true);

$out = brain_stats();
$out['knowledge_gaps'] = brain_memory_gaps(10);
$out['worst_nodes'] = brain_feedback_worst(5);
$out['pending_learned'] = count(brain_learn_pending(50));
brain_json($out);
