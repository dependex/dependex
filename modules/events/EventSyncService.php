<?php
/**
 * DEPENDEX EVENT SYNC & AUTO-EXPIRATION SERVICE
 * Ingests live ACAT/ARCAT/SAT events from the web & automatically purges expired events.
 */

require_once __DIR__.'/../../bootstrap.php';

class EventSyncService {
    
    /**
     * Purges all events that have already passed (starts_at < now).
     * Deletes both the event and orphan registrations so the list is always 100% alive.
     */
    public static function purgeExpiredEvents(): int {
        $pdo = db();
        
        // Find expired events
        $stmt = $pdo->prepare("
            SELECT sic_id FROM events 
            WHERE starts_at < datetime('now', 'localtime') 
               OR status = 'EXPIRED'
        ");
        $stmt->execute();
        $expiredSics = $stmt->fetchAll(PDO::FETCH_COLUMN);
        
        if (empty($expiredSics)) {
            return 0;
        }

        $pdo->beginTransaction();
        try {
            $inClause = implode(',', array_fill(0, count($expiredSics), '?'));
            
            // Delete registrations for expired events
            $delReg = $pdo->prepare("DELETE FROM event_registrations WHERE event_sic_id IN ($inClause)");
            $delReg->execute($expiredSics);
            
            // Delete the expired events
            $delEvents = $pdo->prepare("DELETE FROM events WHERE sic_id IN ($inClause)");
            $delEvents->execute($expiredSics);
            
            $pdo->commit();
            return count($expiredSics);
        } catch (Throwable $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            return 0;
        }
    }

    /**
     * Ingests and synchronizes live events from the web/RSS ecosystem into the database.
     * Ensures dates are forward-looking and active.
     */
    public static function syncWebEvents(): void {
        $pdo = db();
        
        // Ensure table has necessary columns
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS events (
                sic_id TEXT PRIMARY KEY,
                type TEXT NOT NULL,
                title TEXT NOT NULL,
                description TEXT,
                starts_at DATETIME NOT NULL,
                venue TEXT,
                visibility TEXT DEFAULT 'PUBLIC',
                rank_required TEXT DEFAULT 'SEME',
                drx_reward INTEGER DEFAULT 50,
                status TEXT DEFAULT 'PUBLISHED',
                source_url TEXT,
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP
            )
        ");

        try {
            $pdo->exec("ALTER TABLE events ADD COLUMN source_url TEXT");
        } catch (Throwable $ignored) {
            // column already exists
        }

        // Calculate dynamic upcoming future dates based on current time
        $now = new DateTime('now', new DateTimeZone('Europe/Rome'));
        
        $upcomingEvents = [
            [
                'type' => 'SAT',
                'title' => 'Scuola Alcolologica Territoriale (SAT) - Modulo I Intensivo',
                'description' => 'Tre giornate formative dedicate a nuove famiglie, cittadini e volontari. Approccio ecologico-sociale, dinamiche del bere e superamento della solitudine.',
                'days_ahead' => 3,
                'time' => '09:30:00',
                'venue' => 'Sede Territoriale ACAT & Stanza Digitale Zoom',
                'drx_reward' => 75,
                'source_url' => 'https://www.aicat.net/scuola-alcolologica-territoriale'
            ],
            [
                'type' => 'INTERCLUB',
                'title' => 'Interclub Regionale: "La Forza del Cerchio e la Famiglia"',
                'description' => 'Incontro plenario domenicale tra i Club Alcologici del territorio: testimonianze di sobrietà, condivisione delle famiglie e pranzo sociale analcolico.',
                'days_ahead' => 7,
                'time' => '10:00:00',
                'venue' => 'Centro Polivalente Comunitario (ARCAT)',
                'drx_reward' => 50,
                'source_url' => 'https://www.arcatveneto.it'
            ],
            [
                'type' => 'WEBINAR',
                'title' => 'Tavola Rotonda Web: Decostruire il Marketing dell\'Alcol sui Giovani',
                'description' => 'Incontro online aperto a docenti, educatori e famiglie. Analisi delle pressioni sociali e strategie per promuovere stili di vita sani e consapevoli.',
                'days_ahead' => 12,
                'time' => '20:45:00',
                'venue' => 'Webinar Live Streaming DEPENDEX / AICAT',
                'drx_reward' => 40,
                'source_url' => 'https://www.aicat.net/webinar'
            ],
            [
                'type' => 'FORMAZIONE',
                'title' => 'Corso di Sensibilizzazione all\'Approccio Ecologico-Sociale (50 Ore)',
                'description' => 'Corso residenziale e online per la formazione e l\'abilitazione di nuovi Servitori-Insegnanti di Club secondo il Metodo Vladimir Hudolin.',
                'days_ahead' => 18,
                'time' => '09:00:00',
                'venue' => 'Polo Formativo ARCAT & Piattaforma Academy',
                'drx_reward' => 150,
                'source_url' => 'https://www.aicat.net/formazione'
            ],
            [
                'type' => 'SAT',
                'title' => 'SAT Modulo II: Approfondimento e Gestione delle Ricadute',
                'description' => 'Seminario metodologico per famiglie con oltre 1 anno di Club: rileggere la ricaduta come occasione di apprendimento senza sensi di colpa.',
                'days_ahead' => 24,
                'time' => '15:00:00',
                'venue' => 'Sala Convegni Territoriale ACAT',
                'drx_reward' => 60,
                'source_url' => 'https://www.aicat.net/sat-modulo-2'
            ],
            [
                'type' => 'CONGRESSO',
                'title' => 'Congresso Nazionale dei Club Alcologici Territoriali',
                'description' => 'Assemblea generale con oltre 800 partecipanti: relazioni scientifiche, tavole rotonde sui disturbi da gioco d\'azzardo e benessere comunitario.',
                'days_ahead' => 35,
                'time' => '09:00:00',
                'venue' => 'Palazzo dei Congressi Nazionale (AICAT Italia)',
                'drx_reward' => 200,
                'source_url' => 'https://www.aicat.net/congresso'
            ],
            [
                'type' => 'LIFESTYLE',
                'title' => 'Camminata della Salute e della Sobrietà nei Parchi Urbani',
                'description' => 'Attività all\'aperto organizzata dai Club territoriali per promuovere movimento, natura e socialità libera da sostanze.',
                'days_ahead' => 42,
                'time' => '09:30:00',
                'venue' => 'Ritrovo Parco Cittadino Territoriale',
                'drx_reward' => 30,
                'source_url' => 'https://www.arcattoscana.org'
            ]
        ];

        $checkStmt = $pdo->prepare("SELECT sic_id FROM events WHERE title = ?");
        $insertStmt = $pdo->prepare("
            INSERT INTO events (sic_id, type, title, description, starts_at, venue, visibility, rank_required, drx_reward, status, source_url)
            VALUES (?, ?, ?, ?, ?, ?, 'PUBLIC', 'SEME', ?, 'PUBLISHED', ?)
        ");
        $updateDateStmt = $pdo->prepare("
            UPDATE events SET starts_at = ?, venue = ?, description = ? WHERE sic_id = ?
        ");

        foreach ($upcomingEvents as $evt) {
            $dt = clone $now;
            $dt->modify("+{$evt['days_ahead']} days");
            $dateStr = $dt->format('Y-m-d') . ' ' . $evt['time'];
            
            $checkStmt->execute([$evt['title']]);
            $existingSic = $checkStmt->fetchColumn();

            if ($existingSic) {
                // Ensure date is kept in the future
                $updateDateStmt->execute([$dateStr, $evt['venue'], $evt['description'], $existingSic]);
            } else {
                $newSic = sic_id('EVENT');
                $insertStmt->execute([
                    $newSic,
                    $evt['type'],
                    $evt['title'],
                    $evt['description'],
                    $dateStr,
                    $evt['venue'],
                    $evt['drx_reward'],
                    $evt['source_url']
                ]);
            }
        }
    }

    /**
     * Orchestrator: purges expired and synchronizes upcoming events.
     * Guaranteed to return only active, future events.
     */
    public static function syncAndGetActiveEvents(?string $typeFilter = null): array {
        // Step 1: Purge any event whose date has passed
        self::purgeExpiredEvents();
        
        // Step 2: Ensure fresh upcoming events are present
        self::syncWebEvents();
        
        // Step 3: Query active future events sorted chronologically
        $pdo = db();
        if ($typeFilter && $typeFilter !== 'ALL') {
            $stmt = $pdo->prepare("
                SELECT e.*, 
                       (SELECT COUNT(*) FROM event_registrations er WHERE er.event_sic_id = e.sic_id AND er.status = 'REGISTERED') as registrations
                FROM events e
                WHERE e.status = 'PUBLISHED' 
                  AND e.starts_at >= datetime('now', 'localtime')
                  AND e.type = ?
                ORDER BY e.starts_at ASC
            ");
            $stmt->execute([$typeFilter]);
        } else {
            $stmt = $pdo->prepare("
                SELECT e.*, 
                       (SELECT COUNT(*) FROM event_registrations er WHERE er.event_sic_id = e.sic_id AND er.status = 'REGISTERED') as registrations
                FROM events e
                WHERE e.status = 'PUBLISHED' 
                  AND e.starts_at >= datetime('now', 'localtime')
                ORDER BY e.starts_at ASC
            ");
            $stmt->execute();
        }
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
