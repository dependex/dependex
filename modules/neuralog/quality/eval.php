<?php
/* ============================================================================
   COMPANY BRAIN — quality/eval.php
   Il banco di prova del recupero. Domande fisse, risposta attesa, e si chiama
   la FUNZIONE VERA del recupero (brain_retrieve), non una simulazione:
     - HIT-RATE: quante domande trovano l'atteso nei primi K
     - MRR-lite: 1/posizione del primo risultato giusto, media
   Ogni esecuzione finisce in eval_runs: la tendenza si legge nel tempo. E'
   l'unico modo onesto di dire "il motore e' migliorato": un numero che si
   rigioca dopo ogni modifica.
============================================================================ */
require_once __DIR__ . '/../core/db.php';
require_once __DIR__ . '/../core/schema.php';
require_once __DIR__ . '/../core/log.php';
require_once __DIR__ . '/../rag/retrieve.php';

/** Le domande: prima dalla tabella eval_questions, altrimenti dal file JSON. */
function brain_eval_questions(): array {
    $qs = [];
    if (brain_has_table(brain_t('eval_questions'))) {
        foreach (brain_rows('SELECT q, expected, tag FROM ' . brain_t('eval_questions') . ' WHERE active=1 ORDER BY id') as $r) {
            $qs[] = ['q' => (string)$r['q'], 'expected' => (string)$r['expected'], 'tag' => (string)($r['tag'] ?? '')];
        }
    }
    if ($qs) { return $qs; }
    $j = brain_json_file((string)brain_cfg('eval.benchmark', 'config/benchmark.sample.json'));
    foreach ((array)($j['questions'] ?? []) as $row) {
        if (!isset($row['q'], $row['expected'])) { continue; }
        $qs[] = ['q' => (string)$row['q'], 'expected' => (string)$row['expected'], 'tag' => (string)($row['tag'] ?? '')];
    }
    return $qs;
}

/** Importa il benchmark dal file JSON dentro la tabella (per modificarlo da UI). */
function brain_eval_import(): int {
    $pdo = brain_pdo();
    if (!$pdo || !brain_has_table(brain_t('eval_questions'))) { return 0; }
    $j = brain_json_file((string)brain_cfg('eval.benchmark', 'config/benchmark.sample.json'));
    $n = 0;
    brain_exec('DELETE FROM ' . brain_t('eval_questions'));
    foreach ((array)($j['questions'] ?? []) as $row) {
        if (!isset($row['q'], $row['expected'])) { continue; }
        try {
            $st = $pdo->prepare('INSERT INTO ' . brain_t('eval_questions') . ' (q, expected, tag, active, created_at) VALUES (?,?,?,1,?)');
            $st->execute([(string)$row['q'], (string)$row['expected'], (string)($row['tag'] ?? ''), brain_now()]);
            $n++;
        } catch (Throwable $e) {}
    }
    return $n;
}

/** Esegue il banco di prova. */
function brain_eval_run(array $opts = []): array {
    $topK = (int)($opts['top_k'] ?? brain_cfg('eval.top_k', 5));
    $topK = max(1, min(20, $topK));
    $questions = brain_eval_questions();
    if (!$questions) { return ['ok' => false, 'error' => 'nessuna domanda nel banco di prova']; }

    $rows = []; $hits = 0; $mrrSum = 0.0; $t0 = microtime(true);
    foreach ($questions as $q) {
        $res = brain_retrieve($q['q'], ['admin' => true, 'n' => max($topK, (int)brain_cfg('rag.top_k', 8))]);
        $expected = brain_normalize($q['expected']);
        $rank = 0;
        foreach ($res as $i => $r) {
            $hay = brain_normalize((string)($r['path'] ?? '') . ' ' . (string)($r['title'] ?? '') . ' ' . (string)($r['content'] ?? ''));
            if ($expected !== '' && mb_strpos($hay, $expected) !== false) { $rank = $i + 1; break; }
        }
        $ok = ($rank > 0 && $rank <= $topK);
        if ($ok) { $hits++; $mrrSum += 1.0 / $rank; }
        $rows[] = ['q' => $q['q'], 'expected' => $q['expected'], 'tag' => $q['tag'], 'found' => $ok, 'rank' => $rank ?: null];
    }
    $tot = count($questions);
    $out = [
        'ok' => true,
        'questions' => $tot,
        'hits' => $hits,
        'hit_rate' => $tot ? round($hits / $tot, 3) : 0.0,
        'mrr' => $tot ? round($mrrSum / $tot, 3) : 0.0,
        'top_k' => $topK,
        'ms' => (int)round((microtime(true) - $t0) * 1000),
        'detail' => $rows,
    ];

    $pdo = brain_pdo();
    if ($pdo && brain_has_table(brain_t('eval_runs'))) {
        try {
            $st = $pdo->prepare('INSERT INTO ' . brain_t('eval_runs') .
                ' (ran_at, questions, hits, hit_rate, mrr, detail) VALUES (?,?,?,?,?,?)');
            $st->execute([brain_now(), $tot, $hits, $out['hit_rate'], $out['mrr'], json_encode($rows, JSON_UNESCAPED_UNICODE)]);
        } catch (Throwable $e) {}
        /* igiene: solo le ultime N esecuzioni (un cron sbagliato non deve
           far crescere la tabella all'infinito) */
        $keep = max(10, (int)brain_cfg('eval.keep_runs', 200));
        $minId = (int)brain_scalar('SELECT MIN(id) FROM (SELECT id FROM ' . brain_t('eval_runs') . ' ORDER BY id DESC LIMIT ' . $keep . ') t', [], 0);
        if ($minId > 0) { brain_exec('DELETE FROM ' . brain_t('eval_runs') . ' WHERE id < ?', [$minId]); }
    }
    $out['trend'] = brain_rows('SELECT ran_at, hit_rate, mrr FROM ' . brain_t('eval_runs') . ' ORDER BY id DESC LIMIT 5');
    brain_activity('eval', 'hit_rate=' . $out['hit_rate'] . ' mrr=' . $out['mrr'] . ' n=' . $tot);
    return $out;
}
