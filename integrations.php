<?php
/**
 * DEPENDEX.SOCIAL - Module Adapters & System Integrations
 * Protocollo Karpathy: SPEC -> VERIFIER -> ENVIRONMENT
 * Microservice Architecture, Adapter Pattern, Mobile-First
 */
require_once 'bootstrap.php';
$u = require_login();

$adapters = db()->query("SELECT * FROM integration_adapters ORDER BY id ASC")->fetchAll();

$pageTitle = 'Integrazioni Modulari & Adapter · Dependex';
$metaDesc = 'Interfacce di integrazione modulare e adapter architetturali pronti per l\'espansione dell\'ecosistema.';
require '_header.php';
?>

<section class="section-head">
    <div>
        <span class="eyebrow">Architecture OS · Moduli di Sistema</span>
        <h1>Integrazioni Modulari & Adapter</h1>
        <p>L'architettura Dependex è concepita a componenti modulari disaccoppiati. Ciascun motore adotta l'Adapter Pattern per consentire l'estensione senza modifiche al core applicativo.</p>
    </div>
</section>

<div class="course-list" style="margin-top: 2rem;">
    <?php foreach ($adapters as $a): 
        $cfg = json_decode($a['config_json'] ?? '{}', true);
    ?>
    <article class="course">
        <div style="display: flex; justify-content: space-between; align-items: baseline; flex-wrap: wrap; gap: 0.5rem;">
            <span class="course-cat" style="background: <?= $a['status'] === 'READY' ? 'rgba(34,197,94,0.15)' : 'rgba(96,165,250,0.15)' ?>; color: <?= $a['status'] === 'READY' ? '#4ade80' : '#60a5fa' ?>;">
                <?= h($a['status']) ?>
            </span>
            <span style="font-size: 0.8rem; font-family: monospace; opacity: 0.75;">
                v<?= h($a['interface_version']) ?>
            </span>
        </div>

        <h3 style="margin-top: 0.5rem;"><?= h($a['label']) ?></h3>
        <p style="font-size: 0.9rem; opacity: 0.85;">
            Namespace: <code><?= h($a['code']) ?></code><br>
            Adapter Endpoint: <code><?= h($cfg['adapter'] ?? 'api/adapter.php') ?></code>
        </p>

        <div style="margin-top: 1rem; padding-top: 0.75rem; border-top: 1px solid rgba(255,255,255,0.08); font-size: 0.85rem; opacity: 0.8;">
            Interfaccia conforme a specifiche di sicurezza e validazione crittografica SIC-ID.
        </div>
    </article>
    <?php endforeach; ?>
</div>

<div style="margin-top: 2.5rem; display: flex; gap: 1rem; flex-wrap: wrap;">
    <a href="club-admin.php" class="btn secondary">Pannello Club Admin</a>
    <a href="vault-admin.php" class="btn secondary">Vault Admin</a>
    <a href="finance.php" class="btn secondary">Tesoreria</a>
</div>

<?php require '_footer.php'; ?>