<?php
/* ============================================================================
   COMPANY BRAIN — core/text.php
   Normalizzazione, tokenizzazione, stopwords, elisioni, sinonimi: TUTTO viene
   dai pacchetti lingua in config (text.lang_packs). Nel motore non c'e' una
   sola regola specifica di una lingua: cambi il pacchetto, cambia la lingua.
============================================================================ */
require_once __DIR__ . '/config.php';

/** Pacchetti lingua uniti (fold, stopwords, elisioni, sinonimi). */
function brain_lang_pack(): array {
    static $pack = null;
    if ($pack !== null) { return $pack; }
    $pack = ['fold' => [], 'stopwords' => [], 'elision_prefixes' => [], 'synonyms' => []];
    foreach ((array)brain_cfg('text.lang_packs', []) as $rel) {
        $j = brain_json_file((string)$rel);
        if (!$j) { continue; }
        if (!empty($j['fold']) && is_array($j['fold'])) { $pack['fold'] += $j['fold']; }
        foreach (['stopwords', 'elision_prefixes'] as $k) {
            if (!empty($j[$k]) && is_array($j[$k])) { $pack[$k] = array_merge($pack[$k], $j[$k]); }
        }
        if (!empty($j['synonyms']) && is_array($j['synonyms'])) {
            foreach ($j['synonyms'] as $key => $list) {
                $key = brain_normalize((string)$key);
                $pack['synonyms'][$key] = array_merge($pack['synonyms'][$key] ?? [], (array)$list);
            }
        }
    }
    $pack['stopwords']        = array_flip(array_map('mb_strtolower', $pack['stopwords']));
    $pack['elision_prefixes'] = array_flip(array_map('mb_strtolower', $pack['elision_prefixes']));
    /* INDICE BIDIREZIONALE dei sinonimi: chi scrive "costo" deve trovare anche
       "prezzo", non solo il contrario. Ogni parola del gruppo punta a tutto il
       gruppo (chiave compresa). */
    $pack['syn_index'] = [];
    foreach ($pack['synonyms'] as $key => $list) {
        $group = array_values(array_unique(array_merge([$key], array_map('strval', (array)$list))));
        foreach ($group as $member) {
            $m = brain_normalize($member);
            if ($m === '') { continue; }
            $pack['syn_index'][$m] = array_values(array_unique(array_merge($pack['syn_index'][$m] ?? [], $group)));
        }
    }
    return $pack;
}

/** Minuscolo + piegatura accenti + spazi compressi. Nessuna regola hardcoded. */
function brain_normalize(string $s): string {
    $s = mb_strtolower($s);
    if (brain_cfg('text.fold_accents', true)) {
        $fold = brain_lang_pack()['fold'];
        if ($fold) { $s = strtr($s, $fold); }
    }
    $s = preg_replace('/\s+/u', ' ', $s);
    return trim((string)$s);
}

/**
 * Tokenizza: separa su spazi E su apostrofi/elisioni (altrimenti "l'orario"
 * diventa "lorario", stringa che nel testo non compare mai e il match fallisce
 * sempre). I prefissi di elisione lunghi (dell, nell, sull...) sono scartati:
 * hanno frequenza altissima e portano solo rumore.
 */
function brain_tokens(string $s, bool $dropStop = true): array {
    $pack = brain_lang_pack();
    $min  = (int)brain_cfg('text.min_token_len', 3);
    $max  = (int)brain_cfg('text.max_token_len', 32);
    $s    = brain_normalize($s);
    $parts = preg_split('/[^\p{L}\p{N}]+/u', $s, -1, PREG_SPLIT_NO_EMPTY) ?: [];
    $out = [];
    foreach ($parts as $w) {
        $len = mb_strlen($w);
        if ($len < $min || $len > $max) { continue; }
        if (isset($pack['elision_prefixes'][$w])) { continue; }
        if ($dropStop && isset($pack['stopwords'][$w])) { continue; }
        $out[$w] = 1;
    }
    return array_keys($out);
}

/**
 * Termini di ricerca con espansione multi-query dai sinonimi di config.
 * Deterministica: nessuna chiamata esterna, stesso input = stesso output.
 */
function brain_query_terms(string $q, ?int $limit = null): array {
    return array_keys(brain_query_terms_map($q, $limit));
}

/**
 * Termini con il loro PESO: le parole scritte davvero dall'utente valgono 1,
 * i sinonimi aggiunti dal motore valgono meno (rag.synonym_weight). Serve a
 * non far vincere il documento "vicino di concetto" su quello che risponde
 * esattamente alla domanda: l'espansione allarga il recupero, non decide.
 * @return array<string,float>
 */
function brain_query_terms_map(string $q, ?int $limit = null): array {
    $limit = $limit ?? (int)brain_cfg('rag.max_terms', 16);
    $synW  = (float)brain_cfg('rag.synonym_weight', 0.45);
    $pack  = brain_lang_pack();
    $terms = [];
    foreach (brain_tokens($q) as $t) { $terms[$t] = 1.0; }
    $seed = array_keys($terms);
    /* espansione per parola (indice bidirezionale) */
    foreach ($seed as $t) {
        foreach ($pack['syn_index'][$t] ?? [] as $s) {
            foreach (brain_tokens((string)$s, false) as $x) {
                if (!isset($terms[$x])) { $terms[$x] = $synW; }
            }
        }
    }
    /* espansione per chiave multi-parola ("customer care", "world node"...) */
    $norm = brain_normalize($q);
    foreach ($pack['syn_index'] as $key => $group) {
        if (mb_strpos($key, ' ') === false || mb_strpos($norm, $key) === false) { continue; }
        foreach ($group as $s) {
            foreach (brain_tokens((string)$s, false) as $x) {
                if (!isset($terms[$x])) { $terms[$x] = $synW; }
            }
        }
    }
    arsort($terms);                                    // i termini originali per primi
    return array_slice($terms, 0, max(1, $limit), true);
}

/** Parole chiave di un testo: frequenza, senza stopwords, lunghezza da config. */
function brain_keywords(string $text, ?int $n = null): array {
    $n    = $n ?? (int)brain_cfg('graph.max_keywords', 8);
    $minL = (int)brain_cfg('text.keyword_min_len', 5);
    $maxL = (int)brain_cfg('text.keyword_max_len', 24);
    $pack = brain_lang_pack();
    $freq = [];
    $norm = brain_normalize($text);
    foreach (preg_split('/[^\p{L}\p{N}]+/u', $norm, -1, PREG_SPLIT_NO_EMPTY) ?: [] as $w) {
        $len = mb_strlen($w);
        if ($len < $minL || $len > $maxL) { continue; }
        if (isset($pack['stopwords'][$w]) || isset($pack['elision_prefixes'][$w])) { continue; }
        if (ctype_digit($w)) { continue; }
        $freq[$w] = ($freq[$w] ?? 0) + 1;
    }
    arsort($freq);
    return array_slice(array_keys($freq), 0, max(0, $n));
}

/** Slug sicuro per id e nomi file. */
function brain_slug(string $s, int $max = 60): string {
    $s = brain_normalize($s);
    $s = preg_replace('/[^a-z0-9]+/u', '-', $s);
    $s = trim((string)$s, '-');
    if ($s === '') { $s = 'x'; }
    return mb_substr($s, 0, $max);
}

/** Ripulisce testo grezzo: UTF-8 valido, niente blob base64, spazi normali. */
function brain_clean_text(string $txt): string {
    $s8 = @iconv('UTF-8', 'UTF-8//IGNORE', $txt);
    if ($s8 !== false && $s8 !== '') { $txt = $s8; }
    $txt = preg_replace('/[A-Za-z0-9+\/]{200,}/', ' ', $txt);      // blob base64/hex
    $txt = preg_replace('/[ \t]+/', ' ', (string)$txt);
    $txt = preg_replace('/\n{3,}/', "\n\n", (string)$txt);
    return trim((string)$txt);
}

/** Taglia a lunghezza mantenendo UTF-8 valido. */
function brain_cut(string $s, int $len): string {
    return mb_substr($s, 0, max(0, $len));
}
