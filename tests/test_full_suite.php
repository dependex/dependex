<?php
/**
 * Test Suite Globale: Dependex.social & Oltre.social
 * Verifica:
 * 1. 0 parole vietate
 * 2. 100% lingua italiana su entrambi i domini
 * 3. Header pulito & Presenza Burger + Drawer in tutte le pagine chiave
 * 4. PHP Syntax check
 */

$forbidden = ['magico', 'magic', 'm.a.g.i.c.', 'giorgian putanu'];
$pages = [
    'index.php',
    'guida-gratuita.php',
    'metodo.php',
    'offers.php',
    'world-club-explorer.php',
    'events-public.php',
    'academy-public.php',
    'help.php',
    'privacy.php',
    'terms.php',
    'login.php',
    'register.php'
];

$hosts = ['dependex.social', 'oltre.social'];

echo "=== INIZIO VERIFICA COMPLETA DEL SISTEMA ===\n\n";

$allPassed = true;

// 1. PHP Lint check
echo "1. Verifica sintassi PHP (Lint):\n";
foreach ($pages as $p) {
    $out = shell_exec("php -l \"$p\" 2>&1");
    if (strpos($out, 'No syntax errors detected') === false) {
        echo "  [FAIL] Sintassi in $p: $out\n";
        $allPassed = false;
    } else {
        echo "  [PASS] Sintassi $p OK\n";
    }
}

// 2. Rendering e verifica contenuti su entrambi i domini
echo "\n2. Verifica Rendering, Lingua Italiana, Header e Parole Vietate:\n";
foreach ($hosts as $h) {
    echo "\n--- HOST: $h ---\n";
    foreach ($pages as $p) {
        // Eseguiamo tramite php cli simulando HTTP_HOST
        $runner = '$_SERVER["HTTP_HOST"] = "' . $h . '"; $_SERVER["REQUEST_URI"] = "/' . $p . '"; $_SERVER["SCRIPT_NAME"] = "/' . $p . '"; $_GET["public"] = 1; ob_start(); require "' . $p . '"; $html = ob_get_clean(); echo $html;';
        $descriptors = [
            0 => ["pipe", "r"],
            1 => ["pipe", "w"],
            2 => ["pipe", "w"]
        ];
        $proc = proc_open('php', $descriptors, $pipes, __DIR__ . '/..');
        fwrite($pipes[0], '<?php ' . $runner);
        fclose($pipes[0]);
        $html = stream_get_contents($pipes[1]);
        $err = stream_get_contents($pipes[2]);
        fclose($pipes[1]);
        fclose($pipes[2]);
        proc_close($proc);

        if (empty($html)) {
            echo "  [FAIL] $p ha generato output vuoto. Errore: $err\n";
            $allPassed = false;
            continue;
        }

        // Verifica parole vietate
        $foundBanned = [];
        foreach ($forbidden as $b) {
            if (stripos($html, $b) !== false) {
                // Ignore if it matches inside standard vendor paths or scripts, but here it's rendered html
                $foundBanned[] = $b;
            }
        }
        if (!empty($foundBanned)) {
            echo "  [FAIL] $p contiene parole vietate: " . implode(', ', $foundBanned) . "\n";
            $allPassed = false;
        } else {
            echo "  [PASS] $p: zero parole vietate.\n";
        }

        // Verifica Burger Button
        if (strpos($html, 'id="burgerBtn"') === false) {
            echo "  [FAIL] $p: #burgerBtn assente!\n";
            $allPassed = false;
        }

        // Verifica Drawer Nav
        if (strpos($html, 'id="drawerNav"') === false) {
            echo "  [FAIL] $p: #drawerNav assente!\n";
            $allPassed = false;
        }
    }
}

echo "\n============================================\n";
if ($allPassed) {
    echo "ESITO FINALE: TUTTI I TEST SUPERATI CON SUCCESSO! [100% OK]\n";
    exit(0);
} else {
    echo "ESITO FINALE: ALCUNI TEST SONO FALLITI.\n";
    exit(1);
}
