<?php
/* ============================================================================
   COMPANY BRAIN — rag/context.php
   Il blocco di CONTESTO che si passa al modello: righe corte, ognuna con la
   sua FONTE tra parentesi quadre, cosi' la risposta puo' citare e chi legge
   puo' andare a controllare. Verificabile batte persuasivo.
============================================================================ */
require_once __DIR__ . '/../core/text.php';

/** Etichetta breve e leggibile di un nodo (la "fonte" da citare). */
function brain_source_label(array $row): string {
    $p = trim((string)($row['path'] ?? ''));
    if ($p !== '') { return basename($p); }
    $t = trim((string)($row['title'] ?? ''));
    return $t !== '' ? $t : (string)($row['id'] ?? 'nodo');
}

/** Blocco di contesto testuale. */
function brain_context_block(array $rows, ?int $chars = null): string {
    $chars = $chars ?? (int)brain_cfg('rag.context_chars', 800);
    $out = [];
    foreach ($rows as $r) {
        $c = trim((string)($r['content'] ?? ''));
        if ($c === '') { continue; }
        $c = preg_replace('/\s+/u', ' ', $c);
        $out[] = '• [' . brain_source_label($r) . '] ' . brain_cut((string)$c, $chars);
    }
    return implode("\n", $out);
}

/** Le fonti in forma strutturata (per una API o per i pulsanti di feedback). */
function brain_context_sources(array $rows): array {
    $out = [];
    foreach ($rows as $r) {
        $out[] = [
            'node_id' => (string)($r['id'] ?? ''),
            'label'   => brain_source_label($r),
            'path'    => (string)($r['path'] ?? ''),
            'score'   => round((float)($r['score'] ?? 0), 3),
            'via_link' => !empty($r['via_link']),
        ];
    }
    return $out;
}
