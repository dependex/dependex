<?php
/* ============================================================================
   COMPANY BRAIN — brain.php
   UNICO PUNTO D'INGRESSO. Chi ospita il modulo scrive una riga:

       require_once __DIR__.'/company-brain/brain.php';

   e ha a disposizione tutta l'API. Se l'applicazione ha gia' un suo PDO:

       $GLOBALS['BRAIN_PDO'] = $pdo;                 // prima del require
       // oppure  function brain_host_pdo(): PDO { global $pdo; return $pdo; }
       // e per la sessione admin:
       // function brain_host_is_admin(): bool { return !empty($_SESSION['admin']); }

   Nessun dato di dominio vive qui dentro: tutto sta in config/.
============================================================================ */
if (defined('BRAIN_LOADED')) { return; }
define('BRAIN_LOADED', 1);

if (version_compare(PHP_VERSION, '8.1.0', '<')) {
    trigger_error('Company Brain richiede PHP 8.1 o superiore (trovato ' . PHP_VERSION . ')', E_USER_WARNING);
}

require_once __DIR__ . '/core/config.php';
require_once __DIR__ . '/core/db.php';
require_once __DIR__ . '/core/schema.php';
require_once __DIR__ . '/core/text.php';
require_once __DIR__ . '/core/security.php';
require_once __DIR__ . '/core/log.php';

require_once __DIR__ . '/graph/nodes.php';
require_once __DIR__ . '/graph/links.php';
require_once __DIR__ . '/graph/entities.php';
require_once __DIR__ . '/graph/hub.php';
require_once __DIR__ . '/graph/autolink.php';

require_once __DIR__ . '/ingest/chunker.php';
require_once __DIR__ . '/ingest/documents.php';
require_once __DIR__ . '/ingest/text.php';
require_once __DIR__ . '/ingest/files.php';
require_once __DIR__ . '/ingest/url.php';

require_once __DIR__ . '/rag/retrieve.php';
require_once __DIR__ . '/rag/rerank.php';
require_once __DIR__ . '/rag/context.php';
require_once __DIR__ . '/rag/prompt.php';
require_once __DIR__ . '/rag/memory.php';
require_once __DIR__ . '/rag/learn.php';

require_once __DIR__ . '/quality/health.php';
require_once __DIR__ . '/quality/eval.php';
require_once __DIR__ . '/quality/feedback.php';
require_once __DIR__ . '/quality/reconcile.php';

/* --------------------------------------------------------------------------
   API di comodo: le cinque cose che un'applicazione ospite vuole davvero.
-------------------------------------------------------------------------- */

/** Installa/aggiorna lo schema. Sicuro da chiamare ad ogni avvio. */
function brain_install(): array { return brain_schema_install(); }

/**
 * Domanda -> pacchetto pronto (contesto, fonti, prompt di sistema).
 * Non chiama nessun modello: la chiamata al modello resta dell'ospite.
 */
function brain_ask(string $question, array $opts = []): array {
    $pack = brain_ask_prepare($question, $opts + ['use_memory' => true]);
    if (!empty($opts['log'])) {
        brain_memory_log($question, '', $pack['grounded'], !empty($opts['admin']) ? 'admin' : 'public');
    }
    return $pack;
}

/** Registra la risposta finale del modello: memoria + eventuale apprendimento. */
function brain_ask_complete(string $question, string $answer, bool $grounded, array $opts = []): array {
    brain_memory_log($question, $answer, $grounded, !empty($opts['admin']) ? 'admin' : 'public');
    $learned = brain_learn($question, $answer, $grounded);
    return ['ok' => true, 'learned_node' => $learned];
}

/** Ricerca diretta nel cervello (senza prompt). */
function brain_search(string $q, array $opts = []): array { return brain_retrieve($q, $opts); }

/** Stato sintetico per una dashboard. */
function brain_stats(): array {
    return [
        'ok' => true,
        'version' => brain_version(),
        'schema_version' => (int)brain_meta_get('schema_version', 0),
        'driver' => brain_driver(),
        'counts' => brain_counts(),
        'by_visibility' => brain_group_count('visibility'),
        'rev' => brain_rev(),
        'last_eval' => brain_rows('SELECT ran_at, hit_rate, mrr FROM ' . brain_t('eval_runs') . ' ORDER BY id DESC LIMIT 1'),
    ];
}
