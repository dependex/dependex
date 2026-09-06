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

    public function processMessage(string $message, ?int $userId = null, ?string $sessionId = null): array {
        $message = trim($message);
        if ($message === '') {
            return [
                'success' => false,
                'message' => 'Messaggio vuoto.'
            ];
        }

        // 1. Try FastAPI Python service if available
        $apiPayload = [
            'message' => $message,
            'user_id' => $userId,
            'session_id' => $sessionId,
        ];
        $apiResponse = $this->callPythonApi('/chat', $apiPayload);
        if (is_array($apiResponse) && !empty($apiResponse['success'])) {
            return $apiResponse;
        }

        // 2. Heuristic Multi-Agent dispatcher (local fallback)
        $taskType = $this->classifyTask($message);
        $agentResponse = $this->runLocalAgent($taskType, $message, $userId);

        // 3. Log interaction in SQLite
        $this->logInteraction($message, $taskType, $agentResponse['agent'] ?? $taskType, $apiPayload, $agentResponse);

        return [
            'success' => true,
            'agent' => $agentResponse['agent'] ?? $taskType,
            'task_type' => $taskType,
            'message' => $agentResponse['response'] ?? '',
            'offline' => true
        ];
    }

    public function classifyTask(string $task): string {
        $lower = mb_strtolower($task);
        if (preg_match('/(impronta|contesto|identit|chi siamo|mission)/i', $lower)) {
            return 'impronta';
        }
        if (preg_match('/(mappa|automazion|fluss|process|tesoro)/i', $lower)) {
            return 'mappa_tesoro';
        }
        if (preg_match('/(ingranaggio|skill|abilit|attiv|strument)/i', $lower)) {
            return 'primo_ingranaggio';
        }
        if (preg_match('/(articol|blog|post|contenut|scriv|newsletter)/i', $lower)) {
            return 'content';
        }
        if (preg_match('/(prezz|cost|offert|preventiv|acquist|pacchett|euro|€|compra)/i', $lower)) {
            return 'sales';
        }
        if (preg_match('/(aiut|support|problem|contatt|assistenz|orari|dove)/i', $lower)) {
            return 'support';
        }
        if (preg_match('/(analis|report|dati|statist|metric|kpi|quanti)/i', $lower)) {
            return 'analytics';
        }
        if (preg_match('/(web3|crypto|token|drx|wallet|blockchain|nft)/i', $lower)) {
            return 'web3';
        }
        return 'support';
    }

    private function runLocalAgent(string $agent, string $task, ?int $userId): array {
        switch ($agent) {
            case 'impronta':
                return [
                    'agent' => 'impronta',
                    'response' => "**[IMPRONTA AZIENDALE & VALORI]**\n\nDEPENDEX & OLTRE operano sulla metodologia Hudolin: solidarietà comunitaria, sobrietà come stile di vita e intelligenza collettiva diffusa attraverso i Club territoriali."
                ];
            case 'mappa_tesoro':
                return [
                    'agent' => 'mappa_tesoro',
                    'response' => "**[MAPPA DEL TESORO]**\n\nAutomazioni mappate ad alto valore: onboarding dei soci dei Club, reporting settimanale e riconciliazione automatica del censimento globale."
                ];
            case 'primo_ingranaggio':
                return [
                    'agent' => 'primo_ingranaggio',
                    'response' => "**[PRIMO INGRANAGGIO OPERATIVO]**\n\nSkill attivata: orientamento rapido con geolocalizzazione automatica del Club più vicino e diario di sobrietà integrato."
                ];
            case 'content':
                return [
                    'agent' => 'content',
                    'response' => "**[CONTENT AGENT]**\n\nBozza generata nel tono di voce autorevole, empatico e sobrio: focus sulla testimonianza reale e sulla forza del gruppo."
                ];
            case 'sales':
                return [
                    'agent' => 'sales',
                    'response' => "**[OFFERTE IRRIFIUTABILI (ARCHITETTURA DI VALORE)]**\n\nAbbiamo 4 livelli di valore:\n• **Starter Kit & Diagnosi** (€ 27 - valore € 190)\n• **Protocollo Completo & Trasformazione** (€ 497 o 3 rate da € 185 - valore € 2.588 con Garanzia Integrale)\n• **Programma Elite & Affiancamento** (€ 1.997 - supporto 1-a-1)\n• **Club Permanente** (€ 39/mese - continuità e Cortex attivo 24/7)\n\nPuoi consultare tutte le pricing card dettagliate su `/offers.php`."
                ];
            case 'analytics':
                $nodes = (int)$this->db->query("SELECT COUNT(*) FROM dependex_world_registry")->fetchColumn();
                return [
                    'agent' => 'analytics',
                    'response' => "**[ANALYTICS & METRICHE]**\n\nRete attiva: {$nodes} nodi censiti nel registro globale. Tasso di completamento diario settimanale in crescita del 14%."
                ];
            case 'web3':
                return [
                    'agent' => 'web3',
                    'response' => "**[WEB3 AGENT]**\n\nSmart contract attivo per notarizzazione presenze e assegnazione token DRX di partecipazione democratica."
                ];
            default:
                return [
                    'agent' => 'support',
                    'response' => "Sono Cortex, il cervello digitale della rete. Conosco le sedi dei Club, la formazione Academy, le procedure e le offerte disponibili. Come posso aiutarti?"
                ];
        }
    }

    public function logInteraction(string $task, string $taskType, string $agent, array $input, array $result, int $feedback = 0): void {
        try {
            $st = $this->db->prepare('
                INSERT INTO cortex_interactions (task, task_type, agent, input_data, result, feedback, processed, created_at)
                VALUES (?, ?, ?, ?, ?, ?, 0, CURRENT_TIMESTAMP)
            ');
            $st->execute([
                $task,
                $taskType,
                $agent,
                json_encode($input, JSON_UNESCAPED_UNICODE),
                $result['response'] ?? json_encode($result, JSON_UNESCAPED_UNICODE),
                $feedback
            ]);
        } catch (Throwable $e) {
            // silent logging failure
        }
    }

    public function getStats(): array {
        $stats = [
            'total_interactions' => 0,
            'today_interactions' => 0,
            'positive_feedback' => 0,
            'top_agents' => []
        ];

        try {
            $stats['total_interactions'] = (int)$this->db->query("SELECT COUNT(*) FROM cortex_interactions")->fetchColumn();
            $stats['today_interactions'] = (int)$this->db->query("SELECT COUNT(*) FROM cortex_interactions WHERE DATE(created_at) = DATE('now')")->fetchColumn();
            $stats['positive_feedback'] = (int)$this->db->query("SELECT COUNT(*) FROM cortex_interactions WHERE feedback > 0")->fetchColumn();

            $top = $this->db->query("
                SELECT agent, COUNT(*) as count
                FROM cortex_interactions
                GROUP BY agent
                ORDER BY count DESC
                LIMIT 5
            ")->fetchAll();
            $stats['top_agents'] = is_array($top) ? $top : [];
        } catch (Throwable $e) {
            // fallback
        }

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
        $apiRes = $this->callPythonApi('/learn', []);
        if (is_array($apiRes) && !empty($apiRes['success'])) {
            return $apiRes;
        }

        // Native PHP fallback
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
                'message' => 'Ciclo di apprendimento completato',
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
