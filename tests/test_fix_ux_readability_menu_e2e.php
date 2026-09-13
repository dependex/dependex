<?php
/**
 * TEST E2E: FIX UX, LEGGIBILITÀ, ZERO EMOJI & MENU DESKTOP/MOBILE
 */
declare(strict_types=1);

require_once __DIR__ . '/../bootstrap.php';
require_once __DIR__ . '/../assets/icons.php';

echo "=== TEST SUITE: UX REFACTORING, ZERO EMOJI & TOPBAR MENU ===\n\n";

$passCount = 0;
$failCount = 0;

function assertCheck(string $label, bool $condition, string $details = ''): void {
    global $passCount, $failCount;
    if ($condition) {
        $passCount++;
        echo "  [PASS] {$label}\n";
    } else {
        $failCount++;
        echo "  [FAIL] {$label} - Details: {$details}\n";
    }
}

// 1. Rendering completo Home Page senza Fatal Error
echo "1. Verifica Rendering index.php:\n";
ob_start();
require __DIR__ . '/../index.php';
$homeHtml = ob_get_clean();

assertCheck("index.php renderizza completamente (lunghezza > 50KB)", strlen($homeHtml) > 50000, "Length was " . strlen($homeHtml));
assertCheck("index.php include il Ticker News RSS", strpos($homeHtml, 'dx-news-ticker-section') !== false);
assertCheck("index.php include il componente iscrizione Taglio di Po", strpos($homeHtml, 'fast-checkout-card') !== false);
assertCheck("index.php include il footer", strpos($homeHtml, 'site-footer') !== false);
assertCheck("index.php include lo script assets/js/app.js", strpos($homeHtml, 'assets/js/app.js') !== false);

// 2. Verifica Topbar Clean (Solo Contatori Visitatori & Live Users) e Menu Burger
echo "\n2. Verifica Topbar Clean (Solo Contatori) & Menu Burger:\n";
assertCheck("Topbar contiene contatore visitatori totali e utenti live", strpos($homeHtml, 'topbar-live-counters') !== false);
assertCheck("Topbar contiene badge visite", strpos($homeHtml, 'counter-badge-visits') !== false);
assertCheck("Topbar contiene badge online con pulse", strpos($homeHtml, 'counter-badge-live') !== false);
assertCheck("Topbar NON contiene voci di menu orizzontali desktop", strpos($homeHtml, 'topbar-desktop-nav') === false);
assertCheck("Menu conserva pulsante Carrello topbar", strpos($homeHtml, 'topbar-cart-btn') !== false);
assertCheck("Menu conserva burger button per drawer (#burgerBtn)", strpos($homeHtml, 'id="burgerBtn"') !== false);
assertCheck("Drawer menu contiene link a evento Taglio di Po", strpos($homeHtml, 'evento-ottobre-taglio-di-po.php') !== false);
assertCheck("Drawer menu contiene link a Hub Nazionale Eventi", strpos($homeHtml, 'events-public.php') !== false);
assertCheck("Drawer menu contiene link a Libri KDP offers.php", strpos($homeHtml, 'offers.php') !== false);
assertCheck("Drawer menu contiene link a viaggi-esperienziali.php", strpos($homeHtml, 'viaggi-esperienziali.php') !== false);
assertCheck("Drawer menu contiene link a metodo.php", strpos($homeHtml, 'metodo.php') !== false);
assertCheck("Drawer menu contiene link a world-club-explorer.php", strpos($homeHtml, 'world-club-explorer.php') !== false);

// 3. Verifica Zero Emoji nelle pagine pubbliche
echo "\n3. Verifica Rigorosa Zero Emoji (Solo Icone Vettoriali SVG):\n";
$pagesToCheck = [
    'index.php',
    'offers.php',
    'viaggi-esperienziali.php',
    'crociera-benessere-masterclass.php',
    'event-detail.php',
    'events-public.php',
    'metodo.php',
    '_header.php',
    '_footer.php',
    'templates/_event_fast_checkout.php'
];

$emojiPattern = '/[\x{10000}-\x{10FFFF}]/u';

foreach ($pagesToCheck as $p) {
    $filePath = __DIR__ . '/../' . $p;
    $content = file_get_contents($filePath);
    $hasEmoji = (bool)preg_match($emojiPattern, $content);
    assertCheck("Zero emoji nel file {$p}", !$hasEmoji, "Trovate emoji in {$p}");
}

// 4. Verifica Presenza Icone Vettoriali SVG con classi Arcobaleno/Neon
echo "\n4. Verifica Icone Vettoriali SVG Arcobaleno:\n";
assertCheck("Icona vettoriale lotus definita", function_exists('dx_icon') && strpos(dx_icon('lotus'), '<svg') !== false);
assertCheck("Icona vettoriale waves definita", strpos(dx_icon('waves'), '<svg') !== false);
assertCheck("Icona vettoriale mic definita", strpos(dx_icon('mic'), '<svg') !== false);
assertCheck("Icona vettoriale mountain definita", strpos(dx_icon('mountain'), '<svg') !== false);
assertCheck("Icona vettoriale ship definita", strpos(dx_icon('ship'), '<svg') !== false);
assertCheck("Icona vettoriale anchor definita", strpos(dx_icon('anchor'), '<svg') !== false);
assertCheck("Helper dx_rainbow_icon presente e funzionante", function_exists('dx_rainbow_icon') && strpos(dx_rainbow_icon('lotus', 'red'), 'text-neon-red') !== false);

// 5. Verifica Sintassi di tutti i file chiave
echo "\n5. Verifica Sintassi PHP Pagine Principali:\n";
foreach ($pagesToCheck as $p) {
    $filePath = __DIR__ . '/../' . $p;
    $res = exec("php -l " . escapeshellarg($filePath), $out, $ret);
    assertCheck("Nessun errore di sintassi in {$p}", $ret === 0);
}

// 6. Governance
echo "\n6. Verifica Conformita Governance:\n";
$bannedTokens = ['magico', 'magic', 'M.A.G.I.C.', 'giorgian putanu', '81plus'];
$totalBanned = 0;
foreach ($pagesToCheck as $p) {
    $content = strtolower(file_get_contents(__DIR__ . '/../' . $p));
    foreach ($bannedTokens as $tok) {
        if (strpos($content, strtolower($tok)) !== false) {
            $totalBanned++;
        }
    }
}
assertCheck("Zero violazioni terminologiche", $totalBanned === 0);

echo "\n=== RIEPILOGO TEST: {$passCount} PASS, {$failCount} FAIL ===\n";
exit($failCount > 0 ? 1 : 0);
