<?php
/* ============================================================================
   DASHBOARD-ADMIN — PONTE VERSO LA DASHBOARD UNICA (2026-08-11)
   La vecchia dashboard è archiviata in genesys/_old-dashboard-admin.php.
   LA dashboard admin è UNA sola: admin.php (DR Admin Command Center).
   Questo ponte esiste perché 15+ pagine e vecchi link puntano ancora qui:
   chiunque arrivi viene portato alla dashboard unica, con la key preservata.
============================================================================ */
$q = !empty($_GET['key']) ? ('?key='.rawurlencode((string)$_GET['key'])) : '';
header('Location: admin.php'.$q, true, 301);
exit;
