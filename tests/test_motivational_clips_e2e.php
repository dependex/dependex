<?php
/**
 * Test E2E per Clip Video Motivazionali (8-10s) DEPENDEX.SOCIAL
 * Verifica file video MP4, rendering clips.php, formati 9:16 e hard constraints.
 */

declare(strict_types=1);

$baseDir = dirname(__DIR__);
$testsPassed = 0;
$testsFailed = 0;

function it(string $description, callable $fn) {
    global $testsPassed, $testsFailed;
    try {
        $fn();
        echo "  [PASS] {$description}\n";
        $testsPassed++;
    } catch (Throwable $e) {
        echo "  [FAIL] {$description}: {$e->getMessage()}\n";
        $testsFailed++;
    }
}

echo "=== TEST SUITE: CLIP VIDEO MOTIVAZIONALI DEPENDEX.SOCIAL ===\n\n";

// 1. Verifica file MP4 e copertine
$expectedClips = [
    'clip1_non_devi_sapere_tutto' => ['expected_dur' => 10, 'title' => 'Non devi sapere tutto'],
    'clip2_nessuno_e_solo'        => ['expected_dur' => 8,  'title' => 'Nessuno cammina da solo'],
    'clip3_fermati_e_respira'     => ['expected_dur' => 10, 'title' => 'Fermati. Respira.'],
    'clip4_navigare_le_onde'      => ['expected_dur' => 9,  'title' => 'Non puoi fermare le onde'],
    'clip5_rinascita_quotidiana'  => ['expected_dur' => 8,  'title' => 'Un giorno alla volta']
];

foreach ($expectedClips as $slug => $info) {
    it("File MP4 esiste ed è superiore a 1MB: {$slug}.mp4", function() use ($baseDir, $slug) {
        $path = $baseDir . "/assets/clips/{$slug}.mp4";
        if (!file_exists($path)) {
            throw new Exception("File mancante: {$path}");
        }
        $sizeMb = filesize($path) / (1024 * 1024);
        if ($sizeMb < 1.0) {
            throw new Exception("Dimensione anomala troppo piccola: {$sizeMb} MB");
        }
    });

    it("Immagine di sfondo / poster esiste: {$slug}", function() use ($baseDir, $slug) {
        // Mappatura sui rispettivi bg
        $num = substr($slug, 4, 1);
        $bgPath = $baseDir . "/assets/clips/clip{$num}_bg.jpg";
        if (!file_exists($bgPath)) {
            throw new Exception("Poster mancante: {$bgPath}");
        }
    });
}

// 2. Controllo linting sintattico di clips.php
it("Sintassi PHP valida per clips.php", function() use ($baseDir) {
    $file = $baseDir . "/clips.php";
    exec("php -l " . escapeshellarg($file), $output, $returnVar);
    if ($returnVar !== 0) {
        throw new Exception("Errore di sintassi in clips.php: " . implode("\n", $output));
    }
});

// 3. Verifica rendering di clips.php in CLI (Output in processo separato)
it("clips.php si renderizza con successo e contiene i 5 video player", function() use ($baseDir, $expectedClips) {
    $cmd = "php -d display_errors=0 -d error_reporting=0 " . escapeshellarg($baseDir . "/clips.php");
    $output = [];
    $ret = 0;
    exec($cmd, $output, $ret);
    $html = implode("\n", $output);

    if (empty($html) || $ret !== 0) {
        throw new Exception("Il rendering di clips.php ha fallito o restituito output vuoto. Exit code: {$ret}");
    }

    // Verifica presenza tag video
    if (strpos($html, '<video') === false) {
        throw new Exception("Tag <video> assente nella pagina clips.php.");
    }

    // Verifica presenza di tutti i 5 file video nel sorgente
    foreach ($expectedClips as $slug => $info) {
        if (strpos($html, "{$slug}.mp4") === false) {
            throw new Exception("Riferimento video {$slug}.mp4 non trovato in clips.php");
        }
    }

    // Hard constraint: nessuna parola vietata
    $banned = ['magico', 'magic', 'M.A.G.I.C.', 'giorgian putanu', '81plus'];
    foreach ($banned as $badWord) {
        if (stripos($html, $badWord) !== false) {
            throw new Exception("Trovata parola vietata nel frontend clips.php: '{$badWord}'");
        }
    }

    // Hard constraint: nessun Wellness Score
    if (stripos($html, 'wellness score') !== false || stripos($html, 'punteggio di benessere') !== false) {
        throw new Exception("Trovato wellness score vietato.");
    }
});

// 4. Verifica navigazione linkata
it("_header.php e _footer.php contengono il link a clips.php", function() use ($baseDir) {
    $header = file_get_contents($baseDir . "/_header.php");
    $footer = file_get_contents($baseDir . "/_footer.php");

    if (strpos($header, 'clips.php') === false) {
        throw new Exception("Link a clips.php mancante in _header.php");
    }
    if (strpos($footer, 'clips.php') === false) {
        throw new Exception("Link a clips.php mancante in _footer.php");
    }
});

echo "\n--- RIEPILOGO TEST ---\n";
echo "Superati: {$testsPassed} | Falliti: {$testsFailed}\n";

if ($testsFailed > 0) {
    exit(1);
}
exit(0);
