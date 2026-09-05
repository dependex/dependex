<?php
/* ============================================================================
   COMPANY BRAIN — ingest/chunker.php
   Spezza il testo in pezzi con SOVRAPPOSIZIONE: un concetto a cavallo fra due
   chunk resta leggibile in almeno uno dei due. Taglia su confini di riga
   quando puo', altrimenti a lunghezza. Tetto di chunk per documento da config.
============================================================================ */
require_once __DIR__ . '/../core/config.php';
require_once __DIR__ . '/../core/text.php';

function brain_chunk(string $text, ?int $size = null, ?int $overlap = null, ?int $max = null): array {
    $size    = $size    ?? (int)brain_cfg('ingest.chunk_chars', 1200);
    $overlap = $overlap ?? (int)brain_cfg('ingest.chunk_overlap', 150);
    $max     = $max     ?? (int)brain_cfg('ingest.max_chunks_per_file', 24);
    $size    = max(120, $size);
    $overlap = max(0, min($overlap, (int)floor($size / 2)));

    $text = brain_clean_text($text);
    if ($text === '') { return []; }

    $chunks = [];
    $buf = '';
    foreach (preg_split('/\R/u', $text) ?: [] as $line) {
        $buf .= $line . "\n";
        if (mb_strlen($buf) >= $size) {
            $chunks[] = rtrim($buf);
            $buf = $overlap > 0 ? mb_substr($buf, -$overlap) : '';
        }
        if ($max > 0 && count($chunks) >= $max) { break; }
    }
    if (trim($buf) !== '' && ($max <= 0 || count($chunks) < $max)) { $chunks[] = rtrim($buf); }

    /* RIGHE GIGANTI (JSON su una riga sola, CSV senza a-capo, minified...):
       un chunk piu' lungo del dovuto viene ritagliato a forza, sempre con
       sovrapposizione. Senza questo, un file su una riga sola diventava un
       unico neurone da 200 KB — inutile da recuperare e pesante da leggere. */
    $hard = [];
    foreach ($chunks as $c) {
        if (mb_strlen($c) <= (int)($size * 1.5)) { $hard[] = $c; continue; }
        $pos = 0; $len = mb_strlen($c);
        while ($pos < $len) {
            $hard[] = mb_substr($c, $pos, $size);
            $pos += max(1, $size - $overlap);
            if ($max > 0 && count($hard) >= $max) { break; }
        }
        if ($max > 0 && count($hard) >= $max) { break; }
    }
    $chunks = ($max > 0) ? array_slice($hard, 0, $max) : $hard;

    return array_values(array_filter($chunks, static function ($c) { return trim($c) !== ''; }));
}
