<?php
function testPage(string $host) {
    $_SERVER['HTTP_HOST'] = $host;
    $_SERVER['SCRIPT_NAME'] = '/index.php';
    ob_start();
    include __DIR__ . '/../index.php';
    $html = ob_get_clean();

    echo "Testing index.php for {$host}:\n";

    // 1. Check Burger button in header
    if (strpos($html, 'id="burgerBtn"') !== false) {
        echo "  [PASS] Burger button present (#burgerBtn)\n";
    } else {
        echo "  [FAIL] Burger button missing\n";
    }

    // 2. Check no visible loose menu items in header
    if (strpos($html, 'class="register-pill"') === false && strpos($html, 'class="login-pill"') === false && strpos($html, 'class="lang-select"') === false) {
        echo "  [PASS] Header clean: no loose menu/login/register pills or lang select in topbar\n";
    } else {
        echo "  [FAIL] Header contains loose menu items\n";
    }

    // 3. Check burger drawer has items
    $navItems = ['world-club-explorer.php', 'world-map.php', 'metodo.php', 'events-public.php', 'academy-public.php', 'cortex.php', 'offers.php', 'help.php', 'privacy.php'];
    $allFound = true;
    foreach ($navItems as $item) {
        if (strpos($html, $item) === false) {
            echo "  [FAIL] Missing nav item in drawer: {$item}\n";
            $allFound = false;
        }
    }
    if ($allFound) {
        echo "  [PASS] All core menu items present in Burger Drawer\n";
    }

    // 4. Check banned words
    $banned = ['magico', 'magic', 'm.a.g.i.c', 'giorgian', 'putanu'];
    $foundBanned = false;
    foreach ($banned as $w) {
        if (stripos($html, $w) !== false) {
            echo "  [FAIL] Found banned word: {$w}\n";
            $foundBanned = true;
        }
    }
    if (!$foundBanned) {
        echo "  [PASS] Zero banned words in output\n";
    }
}

testPage('dependex.social');
testPage('oltre.social');
