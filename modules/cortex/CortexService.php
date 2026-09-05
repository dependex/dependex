<?php
declare(strict_types=1);

namespace Dependex\Cortex;

use PDO;
use Throwable;

class CortexService {
    private PDO $db;
    private string $cortexApiUrl;
    private string $contextDir;
    private string $graphDir;
    private string $learningsDir;

    public function __construct(?PDO $db = null) {
        if ($db === null) {
            require_once __DIR__ . '/../../bootstrap.php';
            $this->db = db();
        } else {
            $this->db = $db;
        }

        $this->cortexApiUrl = (string)($_ENV['CORTEX_API_URL'] ?? 'http://127.0.0.1:8081/api');
        $this->contextDir = __DIR__ . '/../../cortex/memory/context/';
        $this->graphDir = __DIR__ . '/../../cortex/memory/graph/';
        $this->learningsDir = __DIR__ . '/../../cortex/memory/learnings/';
    }

    public function processMessage(string $message, ?string $userSicId = null, array $extra = []): array {
        $clean = trim($message);
        if ($clean === '') {
            return ['success' => false, 'error' => 'Messaggio vuoto'];
        }

        // Try Python API if running
        $apiResult = $this->callPythonApi('/chat', [
            'message' => $clean,
            'user_sic_id' => $userSicId,
            'session_id' => session_id() ?: ('ses_' . bin2hex(random_bytes(8)))
        ]);

        if ($apiResult !== null && !empty($apiResult['success'])) {
            return $apiResult;
        }

        // Fallback locale nativo PHP con ancoraggio alla conoscenza del grafo & registry
        return $this->processLocalFallback($clean, $userSicId, $extra);
    }

    private function processLocalFallback(string $task, ?string $userSicId, array $extra): array {
        $taskLower = mb_strtolower($task);
        $agent = 'support';
        $taskType = 'support';

        if (preg_match('/(impronta|contesto|identit|valori)/u', $taskLower)) {
            $agent = 'impronta';
            $taskType = 'impronta';
        } elseif (preg_match('/(mappa|automazion|tesoro|processi|sprech)/u', $taskLower)) {
            $agent = 'mappa_tesoro';
            $taskType = 'mappa_tesoro';
        } elseif (preg_match('/(ingranagg|skill|flusso)/u', $taskLower)) {
            $agent = 'primo_ingranaggio';
            $taskType = 'primo_ingranaggio';
        } elseif (preg_match('/(articol|blog|scriver|newsletter|post)/u', $taskLower)) {
            $agent = 'content';
            $taskType = 'content';
        } elseif (preg_match('/(preventiv|offert|quot|donazion|sostegn)/u', $taskLower)) {
            $agent = 'sales';
            $taskType = 'sales';
        } elseif (preg_match('/(analis|report|dati|statistich|kpi|metric)/u', $taskLower)) {
            $agent = 'analytics';
            $taskType = 'analytics';
        } elseif (preg_match('/(web3|blockchain|wallet|dao|attestat|crypto)/u', $taskLower)) {
            $agent = 'web3';
            $taskType = 'web3';
        }

        // Genera risposta ancorata in base all'agente e al contesto
        $message = match($agent) {
            'impronta' => "🧠 **Impronta CORTEX**\n\nI 5 file di contesto aziendale (identità, offerta, clienti, tono di voce, come lavoriamo) sono sincronizzati. Posso guidarti a espandere le definizioni operative.",
            'mappa_tesoro' => "🗺️ **Mappa del Tesoro**\n\nAutomazioni mappate ad alto valore: onboarding dei soci dei Club, reporting settimanale e riconciliazione automatica del censimento globale.",
            'primo_ingranaggio' => "⚙️ **Primo Ingranaggio**\n\nSkill attiva: sincronizzazione e validazione geocodifica del Registro Club verso la World Map 2D/3D.",
            'content' => "📝 **Content Engine**\n\nPosso redigere comunicati, riflessioni per il Diario del Club o newsletter per le famiglie seguendo il tono empatico e accogliente.",
            'sales' => "💼 **Sostegno & Risorse**\n\nInformazioni su quote Club, percorsi formativi per Servitori-Insegnanti e raccolte fondi etiche per progetti territoriali.",
            'analytics' => "📊 **Metriche di Rete**\n\nOltre 540 entità mappate in 36 Paesi, con decine di Club attivi e monitoraggio dei percorsi di sobrietà.",
            'web3' => "⛓️ **Registro Digitale Web3**\n\nCertificazione su registro immutabile per attestati di formazione e tracciamento trasparente della tesoreria di comunità.",
            default => "🤝 **Supporto CORTEX**\n\nSono il cervello AI di DEPENDEX. Conosco il metodo Hudolin, i Club territoriali e gli strumenti dell'ecosistema. Come posso aiutarti?"
        };

        // Salva interazione in SQLite
        try {
            $st = $this->db->prepare('
                INSERT INTO cortex_interactions (user_sic_id, task, task_type, agent, input_data, result, created_at)
                VALUES (?, ?, ?, ?, ?, ?, CURRENT_TIMESTAMP)
            ');
            $st->execute([
                $userSicId,
                $task,
                $taskType,
                $agent,
                json_encode(['user_sic_id' => $userSicId, 'fallback' => true], JSON_UNESCAPED_UNICODE),
                json_encode(['message' => $message], JSON_UNESCAPED_UNICODE)
            ]);
        } catch (Throwable $e) {}

        return [
            'success' => true,
            'agent' => $agent,
            'task_type' => $taskType,
            'message' => $message,
            'offline' => true
        ];
    }

    public function getStats(): array {
        $stats = [
            'total_interactions' => 0,
            'today_interactions' => 0,
            'positive_feedback' => 0,
            'top_agents' => []
        ];

        try {
            $stats['total_interactions'] = (int)$this->db->query('SELECT COUNT(*) FROM cortex_interactions')->fetchColumn();
            $stats['today_interactions'] = (int)$this->db->query("SELECT COUNT(*) FROM cortex_interactions WHERE DATE(created_at) = DATE('now')")->fetchColumn();
            $stats['positive_feedback'] = (int)$this->db->query("SELECT COUNT(*) FROM cortex_interactions WHERE feedback > 0")->fetchColumn();

            $st = $this->db->query("
                SELECT agent, COUNT(*) as count
                FROM cortex_interactions
                WHERE agent IS NOT NULL
                GROUP BY agent
                ORDER BY count DESC
                LIMIT 5
            ");
            $stats['top_agents'] = $st->fetchAll();
        } catch (Throwable $e) {}

        return $stats;
    }

    public function getKnowledgeStatus(): array {
        $contextFiles = 0;
        if (is_dir($this->contextDir)) {
            $files = glob($this->contextDir . '*.md');
            $contextFiles = is_array($files) ? count($files) : 0;
        }

        $graphNodes = 0;
        $nodesFile = $this->graphDir . 'nodes.json';
        if (is_file($nodesFile)) {
            $content = file_get_contents($nodesFile);
            $data = json_decode((string)$content, true);
            $graphNodes = is_array($data) ? count($data) : 0;
        }

        $learnings = 0;
        if (is_dir($this->learningsDir)) {
            $files = glob($this->learningsDir . '*.json');
            $learnings = is_array($files) ? count($files) : 0;
        }

        return [
            'context_files' => $contextFiles,
            'graph_nodes' => $graphNodes,
            'learnings' => $learnings,
            'last_update' => date('Y-m-d H:i:s')
        ];
    }

    public function getRecentInteractions(int $limit = 10): array {
        try {
            $st = $this->db->prepare('
                SELECT id, user_sic_id, task, task_type, agent, result, feedback, created_at
                FROM cortex_interactions
                ORDER BY created_at DESC
                LIMIT ?
            ');
            $st->execute([$limit]);
            return $st->fetchAll();
        } catch (Throwable $e) {
            return [];
        }
    }

    public function triggerLearning(): array {
        // 1. Try FastAPI endpoint if service is running
        $apiRes = $this->callPythonApi('/learn', []);
        if (is_array($apiRes) && !empty($apiRes['success'])) {
            return $apiRes;
        }

        // 2. Fallback: run Python autonomous_learning script via CLI
        $pythonScript = dirname(__DIR__, 2) . '/cortex/core/autonomous_learning.py';
        if (is_file($pythonScript)) {
            $cmd = 'python ' . escapeshellarg($pythonScript);
            $output = [];
            $code = 0;
            @exec($cmd, $output, $code);
            if ($code === 0 && !empty($output)) {
                $decoded = json_decode(implode("\n", $output), true);
                if (is_array($decoded)) {
                    return $decoded;
                }
            }
        }

        // 3. Native PHP fallback: mark processed interactions and record knowledge
        try {
            $unprocessed = $this->db->query("
                SELECT id, task, result, feedback
                FROM cortex_interactions
                WHERE processed = 0
                ORDER BY created_at DESC
                LIMIT 50
            ")->fetchAll();

            $learnedCount = 0;
            foreach ($unprocessed as $row) {
                if (!empty($row['feedback']) && (int)$row['feedback'] > 0) {
                    $ins = $this->db->prepare("
                        INSERT INTO cortex_knowledge (concept, content, source_type, source_id, confidence)
                        VALUES (?, ?, 'interaction_feedback', ?, 1.0)
                    ");
                    $ins->execute([
                        mb_substr($row['task'], 0, 50),
                        $row['result'],
                        (string)$row['id']
                    ]);
                    $learnedCount++;
                }
                $this->db->prepare("UPDATE cortex_interactions SET processed = 1 WHERE id = ?")->execute([$row['id']]);
            }

            return [
                'success' => true,
                'status' => 'completed',
                'message' => 'Ciclo di apprendimento nativo completato',
                'learned_items' => $learnedCount,
                'processed' => count($unprocessed)
            ];
        } catch (Throwable $e) {
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    private function callPythonApi(string $endpoint, array $payload): ?array {
        if (!function_exists('curl_init')) return null;

        $ch = curl_init($this->cortexApiUrl . $endpoint);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
        curl_setopt($ch, CURLOPT_TIMEOUT, 3);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 1);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode === 200 && is_string($response)) {
            $data = json_decode($response, true);
            if (is_array($data)) return $data;
        }
        return null;
    }
}

class_alias(CortexService::class, 'App\Services\CortexService');
