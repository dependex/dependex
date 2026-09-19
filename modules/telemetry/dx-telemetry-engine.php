<?php
/* =====================================================================
   DEPENDEX · TELEMETRIA & FUNNEL PSICOLOGICO ENGINE (GDPR-FIRST)
   Osservabilità completa: Dwell Time, Scroll Depth, Porte d'Ingresso,
   Tracciamento Aperture/Click Email e Watchdog di Sistema.
   Zero PII in chiaro: IP hash con SHA-256 + salt.
   ===================================================================== */

if (!function_exists('dx_telemetry_schema')) {
    function dx_telemetry_schema(PDO $pdo): void {
        // 1. Tabella eventi granulari del funnel psicologico
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS funnel_events (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                session_token TEXT NOT NULL,
                user_sic_id TEXT,
                page TEXT NOT NULL,
                funnel_stage TEXT NOT NULL,
                action TEXT NOT NULL,
                event_data TEXT,
                ip_hash TEXT NOT NULL,
                user_agent_short TEXT,
                created_at INTEGER NOT NULL,
                created_iso TEXT NOT NULL
            )
        ");

        // 2. Tabella profili di intento psicologico per sessione
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS funnel_intent_profiles (
                session_token TEXT PRIMARY KEY,
                user_sic_id TEXT,
                dominant_intent TEXT DEFAULT 'CURIOUS',
                current_stage TEXT DEFAULT 'ORIENTATION',
                porta_selected TEXT,
                total_events INTEGER DEFAULT 1,
                dwell_seconds INTEGER DEFAULT 0,
                max_scroll_depth INTEGER DEFAULT 0,
                opened_faq_count INTEGER DEFAULT 0,
                searched_clubs_count INTEGER DEFAULT 0,
                gamification_played INTEGER DEFAULT 0,
                contact_attempted INTEGER DEFAULT 0,
                contact_completed INTEGER DEFAULT 0,
                first_seen INTEGER NOT NULL,
                last_seen INTEGER NOT NULL,
                ip_hash TEXT NOT NULL
            )
        ");

        // 3. Tabella tracciamento reale email (open pixel & click redirect)
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS email_tracking_events (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                send_id TEXT NOT NULL,
                event_type TEXT NOT NULL,
                target_url TEXT,
                tag TEXT,
                ip_hash TEXT NOT NULL,
                user_agent_short TEXT,
                created_at INTEGER NOT NULL,
                created_iso TEXT NOT NULL
            )
        ");

        // 4. Tabella Watchdog di Sistema (errori, heartbeat, metriche anomale)
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS system_watchdog_logs (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                component TEXT NOT NULL,
                severity TEXT DEFAULT 'INFO',
                status TEXT NOT NULL,
                message TEXT NOT NULL,
                metrics_json TEXT,
                created_at INTEGER NOT NULL,
                created_iso TEXT NOT NULL
            )
        ");

        // Indici per query ad alte prestazioni
        $indices = [
            "CREATE INDEX IF NOT EXISTS idx_funnel_sess ON funnel_events(session_token)",
            "CREATE INDEX IF NOT EXISTS idx_funnel_stage ON funnel_events(funnel_stage)",
            "CREATE INDEX IF NOT EXISTS idx_funnel_action ON funnel_events(action)",
            "CREATE INDEX IF NOT EXISTS idx_funnel_created ON funnel_events(created_at)",
            "CREATE INDEX IF NOT EXISTS idx_intent_dom ON funnel_intent_profiles(dominant_intent)",
            "CREATE INDEX IF NOT EXISTS idx_email_track_send ON email_tracking_events(send_id)",
            "CREATE INDEX IF NOT EXISTS idx_email_track_type ON email_tracking_events(event_type)",
            "CREATE INDEX IF NOT EXISTS idx_watchdog_sev ON system_watchdog_logs(severity)",
            "CREATE INDEX IF NOT EXISTS idx_watchdog_time ON system_watchdog_logs(created_at)"
        ];
        foreach ($indices as $idxSql) {
            try { $pdo->exec($idxSql); } catch (Throwable $e) {}
        }
    }
}

// Inizializzazione schema automatica
if (function_exists('db')) {
    try {
        dx_telemetry_schema(db());
    } catch (Throwable $e) {}
}

/**
 * Normalizza e calcola l'hash anonimo dell'IP per conformità GDPR
 */
function dx_telemetry_ip_hash(): string {
    $ip = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
    return hash('sha256', $ip . 'dx_funnel_telemetry_salt_2026');
}

/**
 * Ottiene il token di sessione anonimo
 */
function dx_telemetry_session_token(): string {
    if (session_status() !== PHP_SESSION_ACTIVE) {
        @session_start();
    }
    $sid = session_id();
    if (empty($sid)) {
        $sid = hash('sha256', ($_SERVER['REMOTE_ADDR'] ?? '') . ($_SERVER['HTTP_USER_AGENT'] ?? '') . date('YmdH'));
    }
    return $sid;
}

/**
 * Rileva il tipo di dispositivo/browser in formato breve
 */
function dx_telemetry_user_agent(): string {
    $ua = $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown';
    if (stripos($ua, 'Mobile') !== false || stripos($ua, 'Android') !== false || stripos($ua, 'iPhone') !== false) {
        return 'Mobile';
    }
    if (stripos($ua, 'Tablet') !== false || stripos($ua, 'iPad') !== false) {
        return 'Tablet';
    }
    return 'Desktop';
}

/**
 * Registra un evento granulare nel funnel psicologico
 */
function dx_track_funnel_event(string $action, string $stage = 'ORIENTATION', array $meta = []): array {
    $pdo = db();
    $now = time();
    $iso = date('c', $now);
    $sessionToken = dx_telemetry_session_token();
    $userSic = $_SESSION['user_sic_id'] ?? null;
    $ipHash = dx_telemetry_ip_hash();
    $page = $_SERVER['REQUEST_URI'] ?? '/';
    $ua = dx_telemetry_user_agent();
    $jsonData = json_encode($meta, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

    try {
        $stmt = $pdo->prepare("
            INSERT INTO funnel_events (session_token, user_sic_id, page, funnel_stage, action, event_data, ip_hash, user_agent_short, created_at, created_iso)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");
        $stmt->execute([$sessionToken, $userSic, $page, $stage, $action, $jsonData, $ipHash, $ua, $now, $iso]);

        // Aggiorna o crea il profilo di intento per questa sessione
        dx_update_session_intent_profile($pdo, $sessionToken, $userSic, $ipHash, $stage, $action, $meta, $now);

        return ['ok' => true, 'action' => $action, 'stage' => $stage];
    } catch (Throwable $e) {
        return ['ok' => false, 'error' => $e->getMessage()];
    }
}

/**
 * Aggiorna il profilo di intento psicologico per la sessione corrente
 */
function dx_update_session_intent_profile(PDO $pdo, string $sessionToken, ?string $userSic, string $ipHash, string $stage, string $action, array $meta, int $now): void {
    // Legge profilo esistente
    $st = $pdo->prepare("SELECT * FROM funnel_intent_profiles WHERE session_token = ?");
    $st->execute([$sessionToken]);
    $prof = $st->fetch();

    $porta = $meta['porta'] ?? ($prof['porta_selected'] ?? null);
    $openedFaq = (int)($prof['opened_faq_count'] ?? 0);
    $searchedClubs = (int)($prof['searched_clubs_count'] ?? 0);
    $gamification = (int)($prof['gamification_played'] ?? 0);
    $contactAttempted = (int)($prof['contact_attempted'] ?? 0);
    $contactCompleted = (int)($prof['contact_completed'] ?? 0);

    if (stripos($action, 'FAQ') !== false) $openedFaq++;
    if (stripos($action, 'CLUB') !== false || stripos($action, 'MAP') !== false) $searchedClubs++;
    if (stripos($action, 'WHEEL') !== false || stripos($action, 'MASLOW') !== false || stripos($action, 'SOBRIETY') !== false) $gamification++;
    if (stripos($action, 'FORM_START') !== false || stripos($action, 'CLICK_PARLA') !== false) $contactAttempted++;
    if (stripos($action, 'FORM_SUBMIT') !== false || stripos($action, 'MESSAGE_SENT') !== false) $contactCompleted++;

    // Deduzione dell'intento psicologico dominante
    $dominantIntent = 'CURIOUS';
    if ($contactCompleted > 0 || $contactAttempted > 0 || $porta === 'aiuto') {
        $dominantIntent = 'NEEDING_HELP';
    } elseif ($porta === 'famiglia' || stripos(json_encode($meta), 'famigli') !== false) {
        $dominantIntent = 'FAMILY_SUPPORT';
    } elseif ($searchedClubs > 0 || $porta === 'club') {
        $dominantIntent = 'COMMUNITY_SEEKER';
    } elseif ($openedFaq >= 3) {
        $dominantIntent = 'HESITANT';
    } elseif ($gamification > 0 || $porta === 'capire') {
        $dominantIntent = 'METHOD_CURIOUS';
    }

    if (!$prof) {
        $ins = $pdo->prepare("
            INSERT INTO funnel_intent_profiles 
            (session_token, user_sic_id, dominant_intent, current_stage, porta_selected, total_events, dwell_seconds, max_scroll_depth, opened_faq_count, searched_clubs_count, gamification_played, contact_attempted, contact_completed, first_seen, last_seen, ip_hash)
            VALUES (?, ?, ?, ?, ?, 1, 0, 0, ?, ?, ?, ?, ?, ?, ?, ?)
        ");
        $ins->execute([$sessionToken, $userSic, $dominantIntent, $stage, $porta, $openedFaq, $searchedClubs, $gamification, $contactAttempted, $contactCompleted, $now, $now, $ipHash]);
    } else {
        $upd = $pdo->prepare("
            UPDATE funnel_intent_profiles SET
                dominant_intent = ?,
                current_stage = ?,
                porta_selected = COALESCE(?, porta_selected),
                total_events = total_events + 1,
                opened_faq_count = ?,
                searched_clubs_count = ?,
                gamification_played = ?,
                contact_attempted = ?,
                contact_completed = ?,
                last_seen = ?,
                user_sic_id = COALESCE(?, user_sic_id)
            WHERE session_token = ?
        ");
        $upd->execute([$dominantIntent, $stage, $porta, $openedFaq, $searchedClubs, $gamification, $contactAttempted, $contactCompleted, $now, $userSic, $sessionToken]);
    }
}

/**
 * Gestisce l'heartbeat / ping client-side per misurare dwell time e profondità di scroll
 */
function dx_process_ping(int $dwellSeconds, int $scrollDepth, string $page, ?string $activePorta = null): array {
    $pdo = db();
    $sessionToken = dx_telemetry_session_token();
    $now = time();
    $ipHash = dx_telemetry_ip_hash();

    try {
        $pdo->prepare("
            INSERT INTO funnel_intent_profiles (session_token, dwell_seconds, max_scroll_depth, porta_selected, first_seen, last_seen, ip_hash)
            VALUES (?, ?, ?, ?, ?, ?, ?)
            ON CONFLICT(session_token) DO UPDATE SET
                dwell_seconds = MAX(funnel_intent_profiles.dwell_seconds, excluded.dwell_seconds),
                max_scroll_depth = MAX(funnel_intent_profiles.max_scroll_depth, excluded.max_scroll_depth),
                porta_selected = COALESCE(excluded.porta_selected, funnel_intent_profiles.porta_selected),
                last_seen = excluded.last_seen
        ")->execute([$sessionToken, $dwellSeconds, $scrollDepth, $activePorta, $now, $now, $ipHash]);

        return ['ok' => true, 'session' => substr($sessionToken, 0, 10), 'dwell' => $dwellSeconds, 'scroll' => $scrollDepth];
    } catch (Throwable $e) {
        return ['ok' => false, 'error' => $e->getMessage()];
    }
}

/**
 * Traccia l'apertura reale di una email tramite pixel 1x1 GIF trasparente
 */
function dx_record_email_open(string $sendId, ?string $token = null): bool {
    $pdo = db();
    $now = time();
    $iso = date('c', $now);
    $ipHash = dx_telemetry_ip_hash();
    $ua = dx_telemetry_user_agent();

    try {
        $st = $pdo->prepare("
            INSERT INTO email_tracking_events (send_id, event_type, ip_hash, user_agent_short, created_at, created_iso)
            VALUES (?, 'OPEN', ?, ?, ?, ?)
        ");
        $st->execute([$sendId, $ipHash, $ua, $now, $iso]);

        // Se presente tabella leads / lead_events, sincronizziamo
        if (function_exists('dr_lead_event')) {
            @dr_lead_event($pdo, $sendId, 'apertura', 'email_open_pixel', 1, ['send_id' => $sendId]);
        }

        return true;
    } catch (Throwable $e) {
        return false;
    }
}

/**
 * Traccia il click reale in una email e convalida la destinazione (anti open-redirect)
 */
function dx_record_email_click(string $sendId, string $targetUrl, ?string $tag = null): string {
    $pdo = db();
    $now = time();
    $iso = date('c', $now);
    $ipHash = dx_telemetry_ip_hash();
    $ua = dx_telemetry_user_agent();

    // Validazione di sicurezza URL (solo domini autorizzati del network)
    $cleanUrl = filter_var($targetUrl, FILTER_VALIDATE_URL);
    $allowedHosts = ['dependex.social', 'www.dependex.social', 'oltre.social', 'www.oltre.social', 'localhost', '127.0.0.1'];
    
    $safeUrl = '/index.php';
    if ($cleanUrl) {
        $parsed = parse_url($cleanUrl);
        $host = strtolower($parsed['host'] ?? '');
        if (in_array($host, $allowedHosts, true) || empty($host)) {
            $safeUrl = $cleanUrl;
        }
    }

    try {
        $st = $pdo->prepare("
            INSERT INTO email_tracking_events (send_id, event_type, target_url, tag, ip_hash, user_agent_short, created_at, created_iso)
            VALUES (?, 'CLICK', ?, ?, ?, ?, ?, ?)
        ");
        $st->execute([$sendId, $safeUrl, $tag, $ipHash, $ua, $now, $iso]);

        if (function_exists('dr_lead_event')) {
            @dr_lead_event($pdo, $sendId, 'click', $safeUrl, 3, ['tag' => $tag]);
        }
    } catch (Throwable $e) {}

    return $safeUrl;
}

/**
 * Output di un pixel GIF trasparente 1x1 con header anti-cache
 */
function dx_output_transparent_pixel(): void {
    // 43 byte standard GIF89a 1x1 trasparente
    $gif1x1 = base64_decode('R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7');
    header('Content-Type: image/gif');
    header('Content-Length: ' . strlen($gif1x1));
    header('Cache-Control: no-cache, no-store, must-revalidate, private');
    header('Pragma: no-cache');
    header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
    echo $gif1x1;
    exit;
}

/**
 * Esegue il controllo di salute completo (Watchdog di sistema)
 */
function dx_watchdog_check(): array {
    $pdo = db();
    $now = time();
    $iso = date('c', $now);
    $checks = [];
    $isHealthy = true;

    // 1. Check SQLite Database Connectivity & Performance
    try {
        $t0 = microtime(true);
        $st = $pdo->query("SELECT COUNT(*) FROM network_entities");
        $entitiesCount = (int)$st->fetchColumn();
        $dbLatencyMs = round((microtime(true) - $t0) * 1000, 2);
        $checks['database'] = [
            'status' => 'OK',
            'entities_count' => $entitiesCount,
            'latency_ms' => $dbLatencyMs
        ];
    } catch (Throwable $e) {
        $isHealthy = false;
        $checks['database'] = ['status' => 'FAIL', 'error' => $e->getMessage()];
    }

    // 2. Check Tabelle Telemetria e Integrità
    try {
        $evCount = (int)$pdo->query("SELECT COUNT(*) FROM funnel_events")->fetchColumn();
        $intentCount = (int)$pdo->query("SELECT COUNT(*) FROM funnel_intent_profiles")->fetchColumn();
        $checks['telemetry'] = [
            'status' => 'OK',
            'funnel_events_count' => $evCount,
            'profiles_count' => $intentCount
        ];
    } catch (Throwable $e) {
        $isHealthy = false;
        $checks['telemetry'] = ['status' => 'FAIL', 'error' => $e->getMessage()];
    }

    // 3. Check Email Tracking Engine
    try {
        $trackCount = (int)$pdo->query("SELECT COUNT(*) FROM email_tracking_events")->fetchColumn();
        $checks['email_tracker'] = [
            'status' => 'OK',
            'total_tracking_records' => $trackCount
        ];
    } catch (Throwable $e) {
        $checks['email_tracker'] = ['status' => 'WARN', 'error' => $e->getMessage()];
    }

    // 4. Check Spazio Disco e Permessi di Scrittura
    try {
        $dbPath = defined('DB_PATH') ? DB_PATH : __DIR__ . '/../../data/acat_community.sqlite';
        $dbSizeMb = file_exists($dbPath) ? round(filesize($dbPath) / (1024 * 1024), 2) : 0;
        $isWritable = (file_exists($dbPath) && is_writable($dbPath)) || is_writable(dirname($dbPath));
        $checks['filesystem'] = [
            'status' => $isWritable ? 'OK' : 'FAIL',
            'db_size_mb' => $dbSizeMb,
            'writable' => $isWritable
        ];
        if (!$isWritable) $isHealthy = false;
    } catch (Throwable $e) {
        $checks['filesystem'] = ['status' => 'WARN', 'error' => $e->getMessage()];
    }

    $overallStatus = $isHealthy ? 'HEALTHY' : 'DEGRADED';

    // Registra nel log del Watchdog
    try {
        $pdo->prepare("
            INSERT INTO system_watchdog_logs (component, severity, status, message, metrics_json, created_at, created_iso)
            VALUES ('SYSTEM_CORE', ?, ?, ?, ?, ?, ?)
        ")->execute([
            $isHealthy ? 'INFO' : 'CRITICAL',
            $overallStatus,
            'Watchdog routine health check executed',
            json_encode($checks, JSON_UNESCAPED_UNICODE),
            $now,
            $iso
        ]);
    } catch (Throwable $e) {}

    return [
        'timestamp' => $iso,
        'status' => $overallStatus,
        'healthy' => $isHealthy,
        'checks' => $checks
    ];
}

/**
 * Calcola le metriche di conversione per ciascuno stadio del funnel
 */
function dx_get_funnel_stats(): array {
    $pdo = db();
    try {
        $st = $pdo->query("
            SELECT current_stage, COUNT(*) as count 
            FROM funnel_intent_profiles 
            GROUP BY current_stage
        ");
        $rows = $st->fetchAll();
        $stages = [
            'ORIENTATION' => 0,
            'REASSURANCE' => 0,
            'EXPLORATION' => 0,
            'ENGAGEMENT' => 0,
            'ACTION' => 0
        ];
        foreach ($rows as $r) {
            $k = strtoupper((string)$r['current_stage']);
            if (isset($stages[$k])) $stages[$k] = (int)$r['count'];
        }

        // Totali complessivi
        $totalProfiles = (int)$pdo->query("SELECT COUNT(*) FROM funnel_intent_profiles")->fetchColumn();
        $totalEvents = (int)$pdo->query("SELECT COUNT(*) FROM funnel_events")->fetchColumn();
        $avgDwell = (int)$pdo->query("SELECT AVG(dwell_seconds) FROM funnel_intent_profiles WHERE dwell_seconds > 0")->fetchColumn();

        return [
            'stages' => $stages,
            'total_profiles' => $totalProfiles,
            'total_events' => $totalEvents,
            'avg_dwell_seconds' => $avgDwell
        ];
    } catch (Throwable $e) {
        return ['stages' => [], 'total_profiles' => 0, 'total_events' => 0, 'avg_dwell_seconds' => 0];
    }
}

/**
 * Calcola la distribuzione degli archetipi psicologici dei visitatori
 */
function dx_get_psychological_intents(): array {
    $pdo = db();
    try {
        $st = $pdo->query("
            SELECT dominant_intent, COUNT(*) as count 
            FROM funnel_intent_profiles 
            GROUP BY dominant_intent 
            ORDER BY count DESC
        ");
        $results = [];
        foreach ($st->fetchAll() as $r) {
            $results[$r['dominant_intent']] = (int)$r['count'];
        }
        return $results;
    } catch (Throwable $e) {
        return [];
    }
}
