#!/usr/bin/env php
<?php
/**
 * CORTEX — Autonomous Learning CLI Runner
 * Executes periodic cognitive learning, concept extraction, and insights generation.
 */

$root = dirname(__DIR__);
require_once $root . '/bootstrap.php';
require_once $root . '/modules/cortex/cortex_service.php';

echo "🧠 [CORTEX] Avvio ciclo di autoapprendimento...\n";

$cortex = new \Dependex\Cortex\CortexService();
$res = $cortex->triggerLearning();

if (!empty($res['success'])) {
    echo "✅ [CORTEX] Apprendimento completato con successo: " . ($res['message'] ?? 'OK') . "\n";
} else {
    echo "⚠️ [CORTEX] Nota o errore: " . ($res['error'] ?? 'Impossibile completare il ciclo') . "\n";
}

$status = $cortex->getKnowledgeStatus();
$stats = $cortex->getStats();

echo "\n--- STATO CONOSCENZA CORTEX ---\n";
echo "Nodi nel grafo: " . ($status['graph_nodes'] ?? 0) . "\n";
echo "Pillastri di contesto: " . ($status['context_files'] ?? 0) . "\n";
echo "File di apprendimento: " . ($status['learnings'] ?? 0) . "\n";
echo "Interazioni totali: " . ($stats['total_interactions'] ?? 0) . "\n";
echo "Interazioni oggi: " . ($stats['today_interactions'] ?? 0) . "\n";
echo "Ultimo aggiornamento: " . ($status['last_update'] ?? 'N/A') . "\n";
echo "-------------------------------\n";
