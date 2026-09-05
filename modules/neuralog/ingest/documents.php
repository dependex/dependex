<?php
/* ============================================================================
   COMPANY BRAIN — ingest/documents.php
   Estrattori di testo, senza una sola dipendenza esterna:
     - testo semplice: md, txt, csv, json, yml, sql, log, php, js, css, xml...
     - html: strip_tags + entita' decodificate
     - docx/xlsx/pptx: ZipArchive nativa (parte di PHP), XML ripulito
     - pdf: pdftotext se il sistema ce l'ha, altrimenti estrazione best-effort
            dai flussi non compressi (dichiarata come parziale)
     - rtf/xls binari: solo i caratteri stampabili (meglio di niente)
   Se un formato non e' estraibile, si ritorna stringa vuota: il chiamante lo
   registra come "visto e vuoto" e non lo ripropone in eterno.
============================================================================ */
require_once __DIR__ . '/../core/text.php';

function brain_doc_plain_ext(): array {
    return ['txt','md','markdown','csv','tsv','json','yml','yaml','sql','log','ini','conf','php','js','css','sol','py','sh','xml','svg'];
}

/** Testo estratto da un file. Stringa vuota = non estraibile. */
function brain_extract_text(string $path, ?string $ext = null): string {
    if (!is_file($path) || !is_readable($path)) { return ''; }
    $ext = strtolower($ext ?? pathinfo($path, PATHINFO_EXTENSION));

    if (in_array($ext, brain_doc_plain_ext(), true)) {
        return brain_clean_text((string)@file_get_contents($path));
    }
    if (in_array($ext, ['html', 'htm'], true)) {
        return brain_html_to_text((string)@file_get_contents($path));
    }
    if (in_array($ext, ['docx', 'xlsx', 'pptx'], true)) {
        return brain_ooxml_to_text($path, $ext);
    }
    if ($ext === 'pdf') {
        return brain_pdf_to_text($path);
    }
    if (in_array($ext, ['rtf', 'xls', 'doc'], true)) {
        $r = (string)@file_get_contents($path, false, null, 0, 4 * 1024 * 1024);
        return brain_clean_text((string)preg_replace('/[^\x20-\x7E\r\n\t\xC0-\xFF]/', ' ', $r));
    }
    return '';
}

function brain_html_to_text(string $html): string {
    $html = (string)preg_replace('#<(script|style)\b[^>]*>.*?</\1>#is', ' ', $html);
    $txt  = html_entity_decode(strip_tags($html), ENT_QUOTES | ENT_HTML5, 'UTF-8');
    return brain_clean_text($txt);
}

/** docx/xlsx/pptx via ZipArchive: nessuna libreria, solo PHP di serie. */
function brain_ooxml_to_text(string $path, string $ext): string {
    if (!class_exists('ZipArchive')) { return ''; }
    $z = new ZipArchive();
    if ($z->open($path) !== true) { return ''; }
    $parts = [];
    try {
        if ($ext === 'docx') {
            $x = $z->getFromName('word/document.xml');
            if ($x !== false) { $parts[] = (string)preg_replace('#</w:p>#', "\n", $x); }
        } elseif ($ext === 'xlsx') {
            $shared = [];
            $ss = $z->getFromName('xl/sharedStrings.xml');
            if ($ss !== false && preg_match_all('#<t[^>]*>(.*?)</t>#s', $ss, $m)) {
                foreach ($m[1] as $v) { $shared[] = html_entity_decode(strip_tags($v), ENT_QUOTES, 'UTF-8'); }
            }
            $parts[] = implode(' ', $shared);
            for ($i = 1; $i <= 30; $i++) {
                $sheet = $z->getFromName('xl/worksheets/sheet' . $i . '.xml');
                if ($sheet === false) { continue; }
                if (preg_match_all('#<c[^>]*t="s"[^>]*><v>(\d+)</v>#', $sheet, $m2)) {
                    foreach ($m2[1] as $idx) { if (isset($shared[(int)$idx])) { $parts[] = $shared[(int)$idx]; } }
                }
                if (preg_match_all('#<c[^>]*(?!t="s")[^>]*><v>([^<]+)</v>#', $sheet, $m3)) {
                    $parts[] = implode(' ', array_slice($m3[1], 0, 2000));
                }
            }
        } else { // pptx
            for ($i = 1; $i <= 60; $i++) {
                $s = $z->getFromName('ppt/slides/slide' . $i . '.xml');
                if ($s === false) { continue; }
                $parts[] = (string)preg_replace('#</a:p>#', "\n", $s);
            }
        }
    } catch (Throwable $e) { /* file rotto: si ritorna quel che c'e' */ }
    $z->close();
    $txt = implode("\n", $parts);
    $txt = (string)preg_replace('/<[^>]+>/', ' ', $txt);
    return brain_clean_text(html_entity_decode($txt, ENT_QUOTES | ENT_HTML5, 'UTF-8'));
}

/** pdftotext quando c'e'; altrimenti estrazione parziale, dichiarata. */
function brain_pdf_to_text(string $path): string {
    if (function_exists('exec') && !in_array('exec', array_map('trim', explode(',', (string)ini_get('disable_functions'))), true)) {
        $tmp = @tempnam(sys_get_temp_dir(), 'brainpdf');
        if ($tmp !== false) {
            $out = []; $rc = 1;
            @exec('pdftotext -q -enc UTF-8 ' . escapeshellarg($path) . ' ' . escapeshellarg($tmp) . ' 2>/dev/null', $out, $rc);
            if ($rc === 0 && is_file($tmp)) {
                $t = (string)@file_get_contents($tmp);
                @unlink($tmp);
                if (trim($t) !== '') { return brain_clean_text($t); }
            }
            @unlink($tmp);
        }
    }
    $raw = (string)@file_get_contents($path, false, null, 0, 8 * 1024 * 1024);
    if ($raw === '') { return ''; }
    $t = '';
    if (preg_match_all('/\((?:[^()\\\\]|\\\\.){2,}\)/s', $raw, $m)) {
        foreach ($m[0] as $s) {
            $s = substr($s, 1, -1);
            $t .= str_replace(['\\(', '\\)', '\\\\'], ['(', ')', '\\'], $s) . ' ';
        }
    }
    $t = brain_clean_text($t);
    return $t !== '' ? $t : '';
}
