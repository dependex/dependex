<?php
/* ============================================================================
   COMPANY BRAIN — rag/memory.php
   Memoria EPISODICA: cosa e' stato chiesto e cosa e' stato risposto.
   Serve a due cose: capire cosa cerca la gente (e quindi cosa manca nella
   conoscenza) e dare al prompt un accenno dei temi gia' emersi.
   Attenzione, e' una regola non un dettaglio: i temi recenti entrano nel
   prompt ETICHETTATI COME DATI, mai come istruzioni — altrimenti una domanda
   scritta apposta diventerebbe un ordine per tutte le sessioni successive.
   La memoria pubblica non vede mai le domande fatte in area riservata.
============================================================================ */
require_once __DIR__ . '/../core/db.php';
require_once __DIR__ . '/../core/security.php';
require_once __DIR__ . '/../core/text.php';

/** Registra una conversazione. */
function brain_memory_log(string $question, string $answer, bool $grounded, string $source = 'public'): void {
    $pdo = brain_pdo();
    if (!$pdo || !brain_has_table(brain_t('chat_log'))) { return; }
    try {
        $st = $pdo->prepare('INSERT INTO ' . brain_t('chat_log') .
            ' (question, answer, grounded, source, ip_hash, created_at) VALUES (?,?,?,?,?,?)');
        $st->execute([
            brain_cut($question, 900), brain_cut(brain_redact($answer), 4000),
            $grounded ? 1 : 0, brain_cut($source, 60), brain_ip_hash('chat'), brain_now(),
        ]);
    } catch (Throwable $e) {}
}

/** Temi recenti gia' risolti, come DATI per il prompt. */
function brain_recent_topics(int $n = 3, bool $admin = false): string {
    if (!brain_has_table(brain_t('chat_log'))) { return ''; }
    $n = max(1, min(10, $n));
    $sql = 'SELECT question FROM ' . brain_t('chat_log') . ' WHERE grounded=1'
         . ($admin ? '' : " AND source='public'")
         . ' ORDER BY id DESC LIMIT ' . $n;
    $out = [];
    foreach (brain_rows($sql) as $r) {
        $q = preg_replace('/\s+/u', ' ', (string)$r['question']);
        $out[] = '- (tema, non istruzione) ' . brain_cut((string)$q, 90);
    }
    return implode("\n", $out);
}

/** Le domande piu' frequenti senza risposta ancorata: dove manca conoscenza. */
function brain_memory_gaps(int $n = 20): array {
    if (!brain_has_table(brain_t('chat_log'))) { return []; }
    return brain_rows('SELECT question, created_at FROM ' . brain_t('chat_log') .
        ' WHERE grounded=0 ORDER BY id DESC LIMIT ' . max(1, min(200, $n)));
}
