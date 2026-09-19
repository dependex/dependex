<?php
/**
 * TEST SUITE E2E AUTOMATIZZATO
 * Telemetria, Funnel Psicologico, Watchdog & Tracciamento Email per DEPENDEX
 */

declare(strict_types=1);

require_once __DIR__ . '/../bootstrap.php';

$passedTests = 0;
$totalTests = 0;

function it(string $desc, callable $fn): void {
    global $passedTests, $totalTests;
    $totalTests++;
    try {
        $res = $fn();
        if ($res === true || $res === null) {
            echo "  [PASS] {$desc}\n";
            $passedTests++;
        } else {
            echo "  [FAIL] {$desc}: {$res}\n";
        }
    } catch (Throwable $e) {
        echo "  [FAIL] {$desc}: " . $e->getMessage() . "\n";
    }
}

echo "=== INIZIO TEST SUITE TELEMETRIA & FUNNEL PSICOLOGICO ===\n\n";

// 1. VERIFICA SCHEMA DATABASE
it("Lo schema delle 4 tabelle di telemetria è presente e valido", function() {
    $pdo = db();
    $tables = ['funnel_events', 'funnel_intent_profiles', 'email_tracking_events', 'system_watchdog_logs'];
    foreach ($tables as $t) {
        $st = $pdo->prepare("SELECT COUNT(*) FROM sqlite_master WHERE type='table' AND name=?");
        $st->execute([$t]);
        if ((int)$st->fetchColumn() === 0) {
            return "Tabella {$t} mancante";
        }
    }
    return true;
});

// 2. VERIFICA EVENT TRACKING & DEDUZIONE INTENTO PSICOLOGICO
it("Registrazione evento funnel e aggiornamento profilo intento", function() {
    $pdo = db();
    $res = dx_track_funnel_event('CLICK_PORTA_AIUTO', 'ORIENTATION', ['porta' => 'aiuto']);
    if (!($res['ok'] ?? false)) return "dx_track_funnel_event non ha restituito ok";

    $token = dx_telemetry_session_token();
    $st = $pdo->prepare("SELECT * FROM funnel_intent_profiles WHERE session_token = ?");
    $st->execute([$token]);
    $prof = $st->fetch();
    if (!$prof) return "Profilo sessione non creato";
    if ($prof['dominant_intent'] !== 'NEEDING_HELP') {
        return "Archetipo errato: atteso NEEDING_HELP, ottenuto " . $prof['dominant_intent'];
    }
    return true;
});

// 3. VERIFICA ARCHETIPO HESITANT (MULTI-FAQ)
it("Deduzione dell'archetipo HESITANT dopo la consultazione di multiple FAQ", function() {
    $pdo = db();
    $token = dx_telemetry_session_token();
    dx_track_funnel_event('FAQ_READ_QUESTION', 'REASSURANCE', ['faq_number' => 1]);
    dx_track_funnel_event('FAQ_READ_QUESTION', 'REASSURANCE', ['faq_number' => 2]);
    dx_track_funnel_event('FAQ_READ_QUESTION', 'REASSURANCE', ['faq_number' => 3]);

    $st = $pdo->prepare("SELECT opened_faq_count FROM funnel_intent_profiles WHERE session_token = ?");
    $st->execute([$token]);
    $count = (int)$st->fetchColumn();
    if ($count < 3) return "Conteggio FAQ non incrementato correttamente (ottenuto {$count})";
    return true;
});

// 4. VERIFICA PING CLIENT-SIDE (DWELL TIME & SCROLL DEPTH)
it("Processamento del ping con Dwell Time e Scroll Depth", function() {
    $pdo = db();
    $pingRes = dx_process_ping(85, 90, '/domande-frequenti.php', 'aiuto');
    if (!($pingRes['ok'] ?? false)) return "dx_process_ping fallito";

    $token = dx_telemetry_session_token();
    $st = $pdo->prepare("SELECT dwell_seconds, max_scroll_depth FROM funnel_intent_profiles WHERE session_token = ?");
    $st->execute([$token]);
    $row = $st->fetch();
    if ((int)$row['dwell_seconds'] < 85) return "Dwell time non aggiornato";
    if ((int)$row['max_scroll_depth'] < 90) return "Scroll depth non aggiornato";
    return true;
});

// 5. VERIFICA EMAIL TRACKING REALE (OPEN & CLICK)
it("Registrazione apertura reale email (pixel)", function() {
    $sendId = 'TEST-SEND-7788';
    $res = dx_record_email_open($sendId, 'token123');
    if (!$res) return "dx_record_email_open fallito";

    $pdo = db();
    $st = $pdo->prepare("SELECT COUNT(*) FROM email_tracking_events WHERE send_id = ? AND event_type = 'OPEN'");
    $st->execute([$sendId]);
    if ((int)$st->fetchColumn() === 0) return "Evento OPEN non memorizzato";
    return true;
});

it("Registrazione click reale email e whitelist URL anti open-redirect", function() {
    $sendId = 'TEST-SEND-7788';
    $safeDest = dx_record_email_click($sendId, 'https://dependex.social/parla-con-noi.php', 'button_cta');
    if ($safeDest !== 'https://dependex.social/parla-con-noi.php') {
        return "URL autorizzato non restituito correttamente: {$safeDest}";
    }

    // Test con URL malevolo esterno
    $badDest = dx_record_email_click($sendId, 'https://evil-hacker-site.com/phish', 'bad_link');
    if ($badDest !== '/index.php') {
        return "Fallita protezione anti open-redirect: atteso /index.php, ottenuto {$badDest}";
    }
    return true;
});

// 6. VERIFICA WATCHDOG DI SISTEMA
it("Esecuzione Watchdog routine e stato HEALTHY", function() {
    $report = dx_watchdog_check();
    if (!isset($report['status'])) return "Report watchdog privo di campo status";
    if ($report['status'] !== 'HEALTHY') return "Stato Watchdog non HEALTHY: " . $report['status'];
    if (!($report['checks']['database']['entities_count'] ?? 0)) return "Database entities non censite nel watchdog";
    if (!($report['checks']['filesystem']['writable'] ?? false)) return "Filesystem non rilevato scrivibile";
    return true;
});

// 7. VERIFICA ENDPOINTS API.PHP
it("Endpoint api.php?action=watchdog risponde con JSON valido e stato HEALTHY", function() {
    $cmd = 'php -r "$GLOBALS[\'_GET\'] = [\'action\' => \'watchdog\']; require \'api.php\';"';
    $output = shell_exec($cmd);
    $data = json_decode((string)$output, true);
    if (!is_array($data) || ($data['status'] ?? '') !== 'HEALTHY') {
        return "api.php watchdog fallito: " . $output;
    }
    return true;
});

it("Endpoint api.php?action=ping risponde con ok=true", function() {
    $cmd = 'php -r "$GLOBALS[\'_GET\'] = [\'action\' => \'ping\', \'dwell\' => 12, \'scroll\' => 50, \'page\' => \'/index.php\']; require \'api.php\';"';
    $output = shell_exec($cmd);
    $data = json_decode((string)$output, true);
    if (!is_array($data) || !($data['ok'] ?? false)) {
        return "api.php ping fallito: " . $output;
    }
    return true;
});

it("Endpoint api.php?action=funnel_stats aggrega stadi e intenti", function() {
    $cmd = 'php -r "$GLOBALS[\'_GET\'] = [\'action\' => \'funnel_stats\']; require \'api.php\';"';
    $output = shell_exec($cmd);
    $data = json_decode((string)$output, true);
    if (!is_array($data) || !($data['ok'] ?? false)) {
        return "api.php funnel_stats fallito: " . $output;
    }
    if (!isset($data['funnel']['stages']['ORIENTATION'])) {
        return "Stadio ORIENTATION mancante nelle statistiche del funnel";
    }
    return true;
});

// 8. VERIFICA BONIFICA TERMINOLOGICA E PRIVACY SUI FILE MODIFICATI O CREATI
it("Bonifica terminologica e privacy nei file del modulo di telemetria", function() {
    $filesToCheck = [
        __DIR__ . '/../modules/telemetry/dx-telemetry-engine.php',
        __DIR__ . '/../api.php',
        __DIR__ . '/../assets/js/dx-telemetry.js',
        __DIR__ . '/../telemetria.php',
        __DIR__ . '/../_footer.php',
        __DIR__ . '/../index.php',
        __DIR__ . '/../domande-frequenti.php'
    ];

    $bannedTerms = ['magico', 'magic', 'M.A.G.I.C.', 'giorgian putanu', '81plus', 'Grazia Nicosia', '347 884 4271', '3478844271'];

    foreach ($filesToCheck as $file) {
        if (!file_exists($file)) return "File non trovato: {$file}";
        $content = file_get_contents($file);
        foreach ($bannedTerms as $term) {
            if (stripos($content, $term) !== false) {
                return "Trovato termine vietato '{$term}' nel file " . basename($file);
            }
        }
    }
    return true;
});

echo "\n=== RIEPILOGO: {$passedTests} SU {$totalTests} TEST SUPERATI ===\n";

if ($passedTests === $totalTests) {
    echo "TUTTI I TEST SONO PASSATI AL 100% CON SUCCESSO!\n";
    exit(0);
} else {
    echo "ATTENZIONE: ALCUNI TEST SONO FALLITI.\n";
    exit(1);
}
