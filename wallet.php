<?php $u=require_once 'bootstrap.php';$u=require_login();$pageTitle='DRX Wallet';require '_header.php';?>
<section class="wallet-card"><span>Saldo DRX</span><strong><?=number_format((float)$u['drx_balance'],0,',','.')?></strong><small>Dialogo · Relazioni · eXperienza</small><a class="btn" href="vault.php">Community Vault</a></section>
<section class="card"><h2>Wallet custodial</h2><p>Web3 dietro, esperienza semplice davanti. DRX e NFT possono restare nel wallet custodial oppure essere trasferiti a un wallet personale compatibile quando la funzione è abilitata.</p><button class="btn" disabled>Trasferisci a wallet esterno</button></section>
<section class="card"><h2>Rank</h2><p><?=h(rank_for_drx((float)$u['drx_balance']))?></p></section>
<?php require '_footer.php';?>