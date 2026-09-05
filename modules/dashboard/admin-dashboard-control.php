<?php
/* ============================================================================
   ADMIN — CONTROLLO DASHBOARD UTENTE · 2026-08-12 · Cowork
   destinazione: genesys/admin-dashboard-control.php

   Accende/spegne le sezioni di dashboard-user.php per singolo utente, per
   gruppo o per tutti, raggruppate in fasi, con log di ogni modifica.
   Gate: role=admin (sessione), come admin-network-posti.php -> altrimenti
   accedi.php. Vedi genesys/dashboard-control-lib.php per la logica vera:
   qui c'e' solo la pagina (guscio HTML + dati iniziali) e il JS fa il resto
   via genesys/dashboard-control-api.php.
============================================================================ */
if (session_status() === PHP_SESSION_NONE) @session_start();
require __DIR__.'/../db.php';
require_once __DIR__.'/dashboard-control-lib.php';

$isAdmin = (($_SESSION['role']??'')==='admin') && !empty($_SESSION['uid']);
if(!$isAdmin){ header('Location: ../accedi.php'); exit; }

dashctl_schema($pdo);
if(empty($_SESSION['csrf_dashctl'])) $_SESSION['csrf_dashctl']=bin2hex(random_bytes(16));
$CSRF = $_SESSION['csrf_dashctl'];

function dch($v){ return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8'); }

$sections = dashctl_sections($pdo);
$fasi     = dashctl_fasi($pdo);
$recenti  = dashctl_search_users($pdo,'',20); // ultimi 20 iscritti, vista di partenza
?><!doctype html><html lang="it"><head><meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>Controllo Dashboard User — Destino Randagio</title>
<link rel="stylesheet" href="../assets/css/admin-dashboard-control.css">
</head><body>
<div class="dc-wrap">
  <header class="dc-h">
    <h1>🎛️ Controllo Dashboard User</h1>
    <p>Accendi/spegni le sezioni della dashboard di ogni Pioniere — per uno, per gruppo o per tutti.</p>
  </header>

  <div class="dc-toolbar">
    <input id="dcSearch" type="text" placeholder="🔍 Cerca per SIC-ID, email o nome...">
    <button id="dcBtnSearch" class="dc-btn">Cerca</button>
    <button id="dcBtnFiltri" class="dc-btn dc-btn-ghost">Filtri gruppo ▾</button>
    <button id="dcBtnFasi" class="dc-btn dc-btn-ghost">Gestione Fasi ▾</button>
  </div>

  <div id="dcFiltriBox" class="dc-box" style="display:none">
    <div class="dc-grid4">
      <label>Rank minimo <input id="fRank" type="number" min="0" placeholder="es. 3"></label>
      <label>Membership
        <select id="fMembership"><option value="">— tutti —</option><option value="active">Attiva</option><option value="none">Nessuna</option></select>
      </label>
      <label>Ruolo <input id="fRole" placeholder="es. user"></label>
      <label>Registrato dal <input id="fDa" type="date"></label>
      <label>al <input id="fA" type="date"></label>
      <label>Diretti di (uid) <input id="fDiretti" type="number"></label>
    </div>
    <button id="dcBtnApplicaFiltro" class="dc-btn">Trova utenti</button>
    <div id="dcFiltroRisultato" class="dc-muted"></div>
  </div>

  <div id="dcFasiBox" class="dc-box" style="display:none">
    <table class="dc-t">
      <thead><tr><th>Fase</th><th>Stato</th><th>Sezioni</th><th>Azioni</th></tr></thead>
      <tbody>
      <?php foreach($fasi as $f): $secF = array_filter($sections, fn($s)=>(int)$s['fase_id']===(int)$f['id']); ?>
        <tr>
          <td><b><?=dch($f['name'])?></b><div class="dc-muted"><?=dch($f['description'])?></div></td>
          <td>
            <select class="dc-fase-status" data-fase="<?=$f['id']?>">
              <?php foreach(['pianificata'=>'Pianificata','in_corso'=>'In corso','completata'=>'Completata'] as $k=>$lbl): ?>
                <option value="<?=$k?>" <?=$f['status']===$k?'selected':''?>><?=$lbl?></option>
              <?php endforeach; ?>
            </select>
          </td>
          <td><?=implode(', ', array_map(fn($s)=>dch($s['icon'].' '.$s['name']), $secF))?></td>
          <td>
            <button class="dc-btn dc-btn-sm dc-fase-toggle" data-fase="<?=$f['id']?>" data-active="1">Attiva tutta</button>
            <button class="dc-btn dc-btn-sm dc-btn-ghost dc-fase-toggle" data-fase="<?=$f['id']?>" data-active="0">Spegni tutta</button>
          </td>
        </tr>
      <?php endforeach; ?>
      </tbody>
    </table>
  </div>

  <div id="dcUserPanel" class="dc-box" style="display:none">
    <div class="dc-user-card">
      <div class="dc-user-id">
        <b id="dcUNome">—</b>
        <span id="dcUMeta" class="dc-muted"></span>
      </div>
    </div>
    <table class="dc-t">
      <thead><tr><th>Sezione</th><th>Stato</th><th>Fase</th><th>Azioni</th></tr></thead>
      <tbody id="dcSectionRows"></tbody>
    </table>
  </div>

  <div id="dcBulkBar" class="dc-bulkbar" style="display:none">
    <span id="dcBulkCount">0 utenti selezionati</span>
    <select id="dcBulkSlug">
      <?php foreach($sections as $s): if(!empty($s['is_locked'])) continue; ?>
        <option value="<?=dch($s['slug'])?>"><?=dch($s['icon'].' '.$s['name'])?></option>
      <?php endforeach; ?>
    </select>
    <button class="dc-btn" id="dcBulkOn">Attiva sul gruppo</button>
    <button class="dc-btn dc-btn-ghost" id="dcBulkOff">Spegni sul gruppo</button>
  </div>

  <div class="dc-box">
    <h2>📋 Log Modifiche</h2>
    <div class="dc-toolbar">
      <input id="logDa" type="date"> <input id="logA" type="date">
      <button id="dcBtnLog" class="dc-btn dc-btn-sm">Filtra</button>
      <a class="dc-btn dc-btn-sm dc-btn-ghost" href="dashboard-control-api.php?act=export&formato=csv">Esporta CSV</a>
      <a class="dc-btn dc-btn-sm dc-btn-ghost" href="dashboard-control-api.php?act=export&formato=json">Esporta JSON</a>
    </div>
    <table class="dc-t">
      <thead><tr><th>Data</th><th>Admin</th><th>Utente</th><th>Azione</th><th>Sezione/Fase</th><th>Da → A</th></tr></thead>
      <tbody id="dcLogRows"></tbody>
    </table>
  </div>

  <div id="dcSuggerimenti" class="dc-box">
    <h2>Ultimi iscritti</h2>
    <table class="dc-t">
      <tbody>
        <?php foreach($recenti as $u): ?>
          <tr class="dc-quickuser" data-uid="<?=$u['id']?>">
            <td><b><?=dch($u['full_name'] ?: $u['username'])?></b></td>
            <td class="dc-muted"><?=dch($u['sic'] ?: $u['email'])?></td>
            <td><button class="dc-btn dc-btn-sm" data-uid="<?=$u['id']?>" data-name="<?=dch($u['full_name'] ?: $u['username'])?>">Gestisci</button></td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>

<script>window.DC_CSRF = <?=json_encode($CSRF)?>; window.DC_SECTIONS = <?=json_encode($sections, JSON_UNESCAPED_UNICODE)?>;</script>
<script src="../assets/js/admin-dashboard-control.js"></script>
</body></html>
