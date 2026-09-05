<?php
/** DEPENDEX host bridge for supplied universal modules. */
require_once __DIR__.'/../bootstrap.php';
$GLOBALS['BRAIN_PDO']=db();
function brain_host_pdo(){ return db(); }
function brain_host_is_admin(){ $u=current_user(); if(!$u)return false; foreach(user_roles($u['sic_id']) as $r){ if(in_array($r['role_code'],['SUPERADMIN','WORLD_ADMIN','CONTINENTAL_ADMIN','NATIONAL_ADMIN','REGIONAL_ADMIN'])) return true; } return false; }
function dependex_sic_for_entity(string $sic): ?array { $st=db()->prepare('SELECT * FROM dependex_world_registry WHERE sic_id=?');$st->execute([$sic]);return $st->fetch()?:null; }
