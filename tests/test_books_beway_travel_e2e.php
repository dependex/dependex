<?php
/**
 * E2E AUTOMATED TEST: Mirco Pregnolato Books (3 Tiers), Beway.life Integration & Experiential Cruises
 */

declare(strict_types=1);

echo "=== TEST SUITE: LIBRI 3 TIER, BEWAY.LIFE & CROCIERE ESPERIENZIALI ===\n\n";

$passCount = 0;
$failCount = 0;

function assertCheck(string $description, bool $condition, string $detail = ''): void {
    global $passCount, $failCount;
    if ($condition) {
        $passCount++;
        echo "  [PASS] $description\n";
    } else {
        $failCount++;
        echo "  [FAIL] $description" . ($detail ? " ($detail)" : '') . "\n";
    }
}

// 1. VERIFICA DATABASE COMMERCE (ACAT_COMMUNITY.SQLITE)
echo "1. Verifica Database Commerce:\n";
$dbPath = __DIR__ . '/../data/acat_community.sqlite';
$pdo = new PDO('sqlite:' . $dbPath);
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

$bewayBiz = $pdo->query("SELECT id, name, domain FROM commerce_businesses WHERE id = 'biz_beway'")->fetch(PDO::FETCH_ASSOC);
assertCheck("Presenza business 'biz_beway' nel database", !empty($bewayBiz));
assertCheck("Dominio associato corretto ('beway.life')", ($bewayBiz['domain'] ?? '') === 'beway.life');

$booksInDb = [
    'prd_book_famiglia',
    'prd_book_diario_club',
    'prd_book_servitore',
    'prd_book_crescita_esp',
    'prd_book_sat_trilogia',
    'prd_book_52_settimane',
    'prd_travel_crociera',
    'prd_travel_dolomiti',
    'prd_travel_delta_po'
];

foreach ($booksInDb as $pid) {
    $prd = $pdo->query("SELECT id, name FROM commerce_products WHERE id = '$pid'")->fetch(PDO::FETCH_ASSOC);
    assertCheck("Prodotto '$pid' registrato nel catalogo", !empty($prd), "Nome: " . ($prd['name'] ?? 'N/D'));
}

// Verifica Offerte Tiers Libri
$expectedOffers = [
    'off_bk_famiglia_dig', 'off_bk_famiglia_kdp', 'off_bk_famiglia_bundle',
    'off_bk_club_dig', 'off_bk_club_kdp', 'off_bk_club_bundle',
    'off_bk_serv_dig', 'off_bk_serv_kdp', 'off_bk_serv_bundle',
    'off_bk_crescita_dig', 'off_bk_crescita_kdp', 'off_bk_crescita_bundle',
    'off_bk_sat_dig', 'off_bk_sat_kdp', 'off_bk_sat_bundle',
    'off_bk_52w_dig', 'off_bk_52w_kdp', 'off_bk_52w_bundle',
    'off_cruise_smart', 'off_cruise_smart_deposit',
    'off_cruise_comfort', 'off_cruise_comfort_deposit',
    'off_cruise_suite_vip', 'off_cruise_suite_deposit'
];

foreach ($expectedOffers as $offId) {
    $off = $pdo->query("SELECT id, price, title FROM commerce_offers WHERE id = '$offId'")->fetch(PDO::FETCH_ASSOC);
    assertCheck("Offerta '$offId' presente con prezzo definito", !empty($off) && floatval($off['price']) > 0, "Prezzo: " . ($off['price'] ?? 0) . " EUR");
}

// 2. VERIFICA CATALOGO OFFERS.PHP
echo "\n2. Verifica Catalogo offers.php:\n";
$offersContent = file_get_contents(__DIR__ . '/../offers.php');
assertCheck("offers.php contiene 'Quaderno della Famiglia'", str_contains($offersContent, 'Quaderno della Famiglia'));
assertCheck("offers.php contiene 'Il Diario del Club: 90 Giorni'", str_contains($offersContent, 'Il Diario del Club: 90 Giorni'));
assertCheck("offers.php contiene 'Diario Servitore Insegnante'", str_contains($offersContent, 'Diario Servitore Insegnante'));
assertCheck("offers.php contiene 'Il Mio Diario di Crescita Esponenziale'", str_contains($offersContent, 'Il Mio Diario di Crescita Esponenziale'));
assertCheck("offers.php contiene 'Trilogia SAT'", str_contains($offersContent, 'Trilogia SAT'));
assertCheck("offers.php contiene '52 Settimane di Cambiamento'", str_contains($offersContent, '52 Settimane di Cambiamento'));
assertCheck("offers.php menziona Mirco Pregnolato come autore", str_contains($offersContent, 'Mirco Pregnolato'));
assertCheck("offers.php contiene i 3 Tier per i libri", str_contains($offersContent, "'tier' => 1") && str_contains($offersContent, "'tier' => 2") && str_contains($offersContent, "'tier' => 3"));
assertCheck("offers.php contiene link o riferimenti a beway.life e viaggi esperienziali", str_contains($offersContent, 'viaggi-esperienziali.php'));

// 3. VERIFICA COLLEGAMENTO BEWAY.LIFE NELLE PAGINE
echo "\n3. Verifica Integrazione beway.life:\n";
$headerContent = file_get_contents(__DIR__ . '/../_header.php');
assertCheck("_header.php contiene link a 'viaggi-esperienziali.php'", str_contains($headerContent, 'viaggi-esperienziali.php'));
assertCheck("_header.php contiene dicitura 'BEWAY.LIFE'", str_contains($headerContent, 'BEWAY.LIFE'));

$footerContent = file_get_contents(__DIR__ . '/../_footer.php');
assertCheck("_footer.php contiene link a 'beway.life'", str_contains($footerContent, 'beway.life'));
assertCheck("_footer.php contiene link a 'viaggi-esperienziali.php'", str_contains($footerContent, 'viaggi-esperienziali.php'));

$indexContent = file_get_contents(__DIR__ . '/../index.php');
assertCheck("index.php contiene sezione BEWAY.LIFE x DEPENDEX", str_contains($indexContent, 'BEWAY.LIFE x DEPENDEX'));
assertCheck("index.php contiene link a 'crociera-benessere-masterclass.php'", str_contains($indexContent, 'crociera-benessere-masterclass.php'));

// 4. VERIFICA PAGINA HUB VIAGGI ESPERIENZIALI
echo "\n4. Verifica Hub viaggi-esperienziali.php:\n";
$travelContent = file_get_contents(__DIR__ . '/../viaggi-esperienziali.php');
assertCheck("viaggi-esperienziali.php esiste ed è leggibile", !empty($travelContent));
assertCheck("Contiene 'La Grande Crociera della Rinascita'", str_contains($travelContent, 'La Grande Crociera della Rinascita'));
assertCheck("Contiene 'Ritiro Forestale & Biohacking Dolomiti'", str_contains($travelContent, 'Ritiro Forestale & Biohacking Dolomiti'));
assertCheck("Contiene 'Cammino del Delta del Po'", str_contains($travelContent, 'Cammino del Delta del Po'));
assertCheck("Contiene link alla monografia della crociera", str_contains($travelContent, 'crociera-benessere-masterclass.php'));
assertCheck("Contiene contatto concierge BeWay (info@beway.life)", str_contains($travelContent, 'info@beway.life'));

// 5. VERIFICA PAGINA MONOGRAFICA CROCIERA A TEMA
echo "\n5. Verifica Monografia crociera-benessere-masterclass.php:\n";
$cruiseContent = file_get_contents(__DIR__ . '/../crociera-benessere-masterclass.php');
assertCheck("crociera-benessere-masterclass.php esiste ed è leggibile", !empty($cruiseContent));
assertCheck("Contiene itinerario giorno per giorno (8 Giorni / 7 Notti)", str_contains($cruiseContent, 'Giorno 1') && str_contains($cruiseContent, 'Giorno 8'));
assertCheck("Contiene Cabina Smart da 890,00 € (Caparra 190,00 €)", str_contains($cruiseContent, '890,00') && str_contains($cruiseContent, '190,00'));
assertCheck("Contiene Cabina Comfort da 1.290,00 € (Caparra 290,00 €)", str_contains($cruiseContent, '1.290,00') && str_contains($cruiseContent, '290,00'));
assertCheck("Contiene Gran Suite VIP da 1.990,00 € (Caparra 490,00 €)", str_contains($cruiseContent, '1.990,00') && str_contains($cruiseContent, '490,00'));
assertCheck("Contiene riferimento al mentoring individuale con Mirco Pregnolato", str_contains($cruiseContent, 'Mirco Pregnolato'));
assertCheck("Contiene wallet Polygon 0x3C320B3a0917fF44BF6551CDdee44402AFcF250C", str_contains($cruiseContent, '0x3C320B3a0917fF44BF6551CDdee44402AFcF250C'));

// 6. VERIFICA CARRELLO & CHECKOUT WEB3 (CART.PHP & CHECKOUT.PHP)
echo "\n6. Verifica Carrello & Checkout:\n";
$cartContent = file_get_contents(__DIR__ . '/../cart.php');
assertCheck("cart.php non è più bloccato da redirect forzato", !str_contains($cartContent, "header('Location: offers.php'"));
assertCheck("cart.php include UniversalCommerce", str_contains($cartContent, 'UniversalCommerce'));

$checkoutContent = file_get_contents(__DIR__ . '/../checkout.php');
assertCheck("checkout.php non è più bloccato da redirect forzato", !str_contains($checkoutContent, "header('Location: offers.php'"));
assertCheck("checkout.php contiene tab USDT Polygon", str_contains($checkoutContent, 'tab-pay-crypto') || str_contains($checkoutContent, 'payment-panel-crypto'));
assertCheck("checkout.php contiene wallet 0x3C320B3a0917fF44BF6551CDdee44402AFcF250C", str_contains($checkoutContent, '0x3C320B3a0917fF44BF6551CDdee44402AFcF250C'));

// 7. VERIFICA CONFORMITA' GOVERNANCE AGENTS.MD (PAROLE VIETATE)
echo "\n7. Verifica Conformita' Governance (Bonifica Lessicale):\n";
$bannedWords = ['magico', 'magic', 'M.A.G.I.C.', 'giorgian putanu', '81plus'];
$filesToCheck = [
    __DIR__ . '/../offers.php',
    __DIR__ . '/../viaggi-esperienziali.php',
    __DIR__ . '/../crociera-benessere-masterclass.php',
    __DIR__ . '/../cart.php',
    __DIR__ . '/../checkout.php',
    __DIR__ . '/../_header.php',
    __DIR__ . '/../_footer.php',
    __DIR__ . '/../index.php',
    __DIR__ . '/../events-public.php',
    __DIR__ . '/../bin/seed_books_travel_offers.php'
];

$bannedFound = 0;
foreach ($filesToCheck as $f) {
    if (!file_exists($f)) continue;
    $content = file_get_contents($f);
    foreach ($bannedWords as $bw) {
        if (stripos($content, $bw) !== false) {
            echo "  [FAIL] Trovata parola vietata '$bw' in " . basename($f) . "\n";
            $bannedFound++;
        }
    }
}
assertCheck("Zero parole vietate in tutti i file creati e modificati", $bannedFound === 0);

echo "\n=== RIEPILOGO TEST: $passCount PASS, $failCount FAIL ===\n";
exit($failCount > 0 ? 1 : 0);
