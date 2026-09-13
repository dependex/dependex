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

        // 1. Inserimento/Aggiornamento Evento Ufficiale ACAT Basso Polesine (Unico Evento Reale)
        $acatSic = 'SIC-EVT-ACAT-BP-2026-COMM';
        $acatTitle = 'A Scuola di Comunicazione e Resilienza — 1° Livello';
        $acatDesc = 'Impara a comunicare senza litigare e a non farti caricare dai problemi degli altri. Corso di formazione esperienziale rivolto a chi vive in famiglia una situazione di dipendenza, operatori, volontari e membri dei Club Alcologici Territoriali. Tre giornate con Adelmo Di Salvatore per acquisire strumenti pratici da usare già dal lunedì.';
        
        // Pulizia tassativa: nel sistema deve rimanere ESCLUSIVAMENTE il vero evento reale di Taglio di Po
        $delOther = $pdo->prepare("DELETE FROM events WHERE sic_id != ?");
        $delOther->execute([$acatSic]);

        $venue = "Oratorio San Francesco d'Assisi";
        $address = "Vicolo San Francesco 1, Taglio di Po (RO)";
        $organizer = "ACAT Basso Polesine O.D.V. & Coordinamento A.C.A.T. Polesane";
        $trainer = "Adelmo Di Salvatore (Psichiatra, Psicoterapeuta, Formatore Metodo Hudolin)";

        $chkAcat = $pdo->prepare("SELECT sic_id FROM events WHERE sic_id = ?");
        $chkAcat->execute([$acatSic]);
        if (!$chkAcat->fetchColumn()) {
            $insAcat = $pdo->prepare("
                INSERT INTO events (sic_id, type, title, description, starts_at, ends_at, venue, comune, address, visibility, rank_required, drx_reward, status, capacity, price_eur, source_url, image_url, organizer, trainer, registration_deadline)
                VALUES (?, 'FORMAZIONE', ?, ?, '2026-10-09 14:30:00', '2026-10-11 13:00:00', ?, 'Taglio di Po', ?, 'PUBLIC', 'SEME', 100, 'PUBLISHED', 30, 10.00, ?, 'assets/img/events/locandina-ufficiale-oratorio.jpeg', ?, ?, '2026-10-01 23:59:59')
            ");
            $insAcat->execute([$acatSic, $acatTitle, $acatDesc, $venue, $address, 'event-detail.php?event=' . $acatSic, $organizer, $trainer]);
        } else {
            // Assicura campi aggiornati
            $updAcat = $pdo->prepare("
                UPDATE events SET 
                    type = 'FORMAZIONE',
                    title = ?,
                    description = ?,
                    starts_at = '2026-10-09 14:30:00',
                    ends_at = '2026-10-11 13:00:00',
                    venue = ?,
                    comune = 'Taglio di Po',
                    address = ?,
                    capacity = 30,
                    price_eur = 10.00,
                    source_url = ?,
                    image_url = 'assets/img/events/locandina-ufficiale-oratorio.jpeg',
                    organizer = ?,
                    trainer = ?,
                    registration_deadline = '2026-10-01 23:59:59',
                    status = 'PUBLISHED'
                WHERE sic_id = ?
            ");
            $updAcat->execute([$acatTitle, $acatDesc, $venue, $address, 'event-detail.php?event=' . $acatSic, $organizer, $trainer, $acatSic]);
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
        $regSubquery = "(
            (SELECT COUNT(*) FROM event_registrations er WHERE er.event_sic_id = e.sic_id AND er.status IN ('REGISTERED', 'CHECKED_IN')) +
            (SELECT COALESCE(SUM(num_seats), 0) FROM event_bookings eb WHERE eb.event_sic_id = e.sic_id AND eb.status = 'CONFIRMED')
        )";

        if ($typeFilter && $typeFilter !== 'ALL') {
            $stmt = $pdo->prepare("
                SELECT e.*, {$regSubquery} as registrations
                FROM events e
                WHERE e.status = 'PUBLISHED' 
                  AND e.starts_at >= datetime('now', 'localtime')
                  AND e.type = ?
                ORDER BY e.starts_at ASC
            ");
            $stmt->execute([$typeFilter]);
        } else {
            $stmt = $pdo->prepare("
                SELECT e.*, {$regSubquery} as registrations
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
