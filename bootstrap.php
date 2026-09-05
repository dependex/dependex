<?php
require_once __DIR__.'/config.php';

function db(): PDO { static $pdo=null;if($pdo instanceof PDO)return $pdo;if(!extension_loaded('pdo_sqlite'))throw new RuntimeException('PDO_SQLite non disponibile.');$pdo=new PDO('sqlite:'.DB_PATH,null,null,[PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION,PDO::ATTR_DEFAULT_FETCH_MODE=>PDO::FETCH_ASSOC]);$pdo->exec('PRAGMA foreign_keys=ON; PRAGMA journal_mode=WAL; PRAGMA synchronous=NORMAL;');return $pdo; }
function h(?string $v): string {return htmlspecialchars((string)$v,ENT_QUOTES,'UTF-8');}
const SIC_ALFA='0123456789ABCDEFGHJKMNPQRSTVWXYZ';
function sic_val(string $c): int {$c=strtoupper($c);if($c==='I'||$c==='L')$c='1';if($c==='O')$c='0';$p=strpos(SIC_ALFA,$c);return $p===false?-1:$p;}
function sic_check(string $d): string {$n=32;$s=0;$f=2;for($i=strlen($d)-1;$i>=0;$i--){$v=sic_val($d[$i]);if($v<0)throw new InvalidArgumentException('SIC non valido');$a=$f*$v;$f=$f===2?1:2;$s+=intdiv($a,$n)+($a%$n);}return SIC_ALFA[($n-($s%$n))%$n];}
function sic_id(string $seed=''): string {$b=hash('sha256',random_bytes(16),true);$bits='';for($i=0;$i<10;$i++)$bits.=str_pad(decbin(ord($b[$i])),8,'0',STR_PAD_LEFT);$d='';for($i=0;$i<16;$i++)$d.=SIC_ALFA[bindec(substr($bits,$i*5,5))];return 'SIC-'.substr($d,0,8).'-'.substr($d,8,8).'-'.sic_check($d);}
function csrf_token(): string {if(empty($_SESSION[CSRF_KEY]))$_SESSION[CSRF_KEY]=bin2hex(random_bytes(24));return $_SESSION[CSRF_KEY];}
function csrf_check(): void {if(!isset($_POST[CSRF_KEY])||!hash_equals($_SESSION[CSRF_KEY]??'',(string)$_POST[CSRF_KEY])){http_response_code(419);exit('Sessione scaduta. Riprova.');}}
function current_user(): ?array {if(empty($_SESSION['user_sic_id']))return null;$st=db()->prepare('SELECT * FROM users WHERE sic_id=? AND status="ACTIVE"');$st->execute([$_SESSION['user_sic_id']]);return $st->fetch()?:null;}
function require_login(): array {$u=current_user();if(!$u){header('Location: login.php');exit;}return $u;}
function audit(?string $actor,string $action,?string $target=null,array $meta=[]): void {$ip=$_SERVER['REMOTE_ADDR']??'';$st=db()->prepare('INSERT INTO audit_log(sic_id,actor_sic_id,action,target_sic_id,ip_hash,metadata_json) VALUES(?,?,?,?,?,?)');$st->execute([sic_id(),$actor,$action,$target,hash('sha256',$ip),json_encode($meta,JSON_UNESCAPED_UNICODE)]);}
function user_roles(string $userSic): array {$st=db()->prepare('SELECT role_code,scope_sic_id FROM user_roles WHERE user_sic_id=? AND status="ACTIVE" AND (valid_to IS NULL OR valid_to>CURRENT_TIMESTAMP)');$st->execute([$userSic]);return $st->fetchAll();}
function has_role(string $userSic,array|string $roles): bool {$roles=(array)$roles;$got=array_column(user_roles($userSic),'role_code');return (bool)array_intersect($roles,$got);}
function is_admin(string $userSic): bool {return has_role($userSic,['CLUB_ADMIN','TERRITORIAL_ADMIN','PROVINCIAL_ADMIN','REGIONAL_ADMIN','NATIONAL_ADMIN','INTERNATIONAL_ADMIN','CONTINENTAL_ADMIN','WORLD_ADMIN','SUPERADMIN']);}
function require_admin(): array {$u=require_login();if(!is_admin($u['sic_id'])){http_response_code(403);exit('Accesso amministrativo non autorizzato.');}return $u;}
function rank_for_drx(float $drx): string {$st=db()->prepare('SELECT name FROM ranks WHERE threshold_drx<=? ORDER BY threshold_drx DESC LIMIT 1');$st->execute([$drx]);return $st->fetchColumn()?:'SEME';}
function drx_setting(string $key,float $fallback=0): float {$st=db()->prepare('SELECT value FROM drx_settings WHERE key=?');$st->execute([$key]);$v=$st->fetchColumn();return $v===false?$fallback:(float)$v;}
function drx_totals(string $userSic): array {$st=db()->prepare("SELECT COALESCE(SUM(amount),0) total,COALESCE(SUM(CASE WHEN rank_eligible=1 THEN amount ELSE 0 END),0) qualifying FROM drx_ledger WHERE user_sic_id=? AND status='POSTED'");$st->execute([$userSic]);$r=$st->fetch()?:['total'=>0,'qualifying'=>0];return ['total'=>(float)$r['total'],'qualifying'=>(float)$r['qualifying']];}
function drx_post(?string $userSic,?string $clubSic,float $amount,string $sourceType,bool $rankEligible,string $idempotencyKey,?string $sourceSic=null,array $meta=[]): array {
    $pdo=db();$pdo->beginTransaction();
    try{
        $q=$pdo->prepare('SELECT ledger_sic_id FROM drx_idempotency WHERE idempotency_key=?');$q->execute([$idempotencyKey]);
        if($old=$q->fetchColumn()){$pdo->commit();return ['ok'=>true,'duplicate'=>true,'sic_id'=>$old];}
        $oldRank=null;if($userSic){$r=$pdo->prepare('SELECT rank_name FROM users WHERE sic_id=?');$r->execute([$userSic]);$oldRank=(string)($r->fetchColumn()?:'SEME');}
        $ls=sic_id();
        $pdo->prepare('INSERT INTO drx_ledger(sic_id,user_sic_id,club_sic_id,amount,source_type,source_sic_id,rank_eligible,status,idempotency_key,metadata_json,source_date) VALUES(?,?,?,?,?,?,?,?,?,?,DATE("now"))')->execute([$ls,$userSic,$clubSic,$amount,$sourceType,$sourceSic,$rankEligible?1:0,'POSTED',$idempotencyKey,json_encode($meta,JSON_UNESCAPED_UNICODE)]);
        $pdo->prepare('INSERT INTO drx_idempotency(idempotency_key,ledger_sic_id) VALUES(?,?)')->execute([$idempotencyKey,$ls]);
        $rankChanged=null;
        if($userSic){
            $tot=drx_totals($userSic);$newRank=rank_for_drx($tot['qualifying']);
            $pdo->prepare('UPDATE users SET drx_balance=?,rank_name=? WHERE sic_id=?')->execute([$tot['total'],$newRank,$userSic]);
            if($oldRank!==$newRank){
                $rankChanged=$newRank;
                $rs=$pdo->prepare('SELECT sic_id FROM ranks WHERE name=?');$rs->execute([$newRank]);$rankSic=$rs->fetchColumn()?:null;
                $pdo->prepare('INSERT INTO rank_events(sic_id,owner_type,owner_sic_id,old_rank,new_rank,qualifying_drx,source) VALUES(?,?,?,?,?,?,?)')->execute([sic_id(),'USER',$userSic,$oldRank,$newRank,$tot['qualifying'],$sourceType]);
                $cert=sic_id();$tok=verify_token();
                $pdo->prepare('INSERT INTO certificates(sic_id,owner_sic_id,certificate_type,title,source_sic_id,verify_token,metadata_json) VALUES(?,?,?,?,?,?,?)')->execute([$cert,$userSic,'RANK','Rank '.$newRank,$rankSic,$tok,json_encode(['rank'=>$newRank,'qualifying_drx'=>$tot['qualifying']],JSON_UNESCAPED_UNICODE)]);
            }
        }
        $pdo->commit();return ['ok'=>true,'duplicate'=>false,'sic_id'=>$ls,'rank_changed'=>$rankChanged];
    }catch(Throwable $e){if($pdo->inTransaction())$pdo->rollBack();throw $e;}
}
function daily_access_claim(string $userSic): array {$date=date('Y-m-d');$reward=drx_setting('daily_access_drx',1);$pdo=db();try{$pdo->prepare('INSERT INTO daily_access(sic_id,user_sic_id,access_date,drx_awarded) VALUES(?,?,?,?)')->execute([sic_id(),$userSic,$date,$reward]);drx_post($userSic,null,$reward,'DAILY_ACCESS',true,'daily:'.$userSic.':'.$date,null,['date'=>$date]);return ['awarded'=>$reward,'new'=>true];}catch(PDOException $e){if(str_contains(strtolower($e->getMessage()),'unique'))return ['awarded'=>0,'new'=>false];throw $e;}}
function sobriety_sync(string $userSic): array {
    $u=db()->prepare('SELECT sobriety_start_date FROM users WHERE sic_id=?');$u->execute([$userSic]);$start=$u->fetchColumn();
    if(!$start)return ['days'=>0,'awarded'=>0,'milestones'=>[]];
    $today=new DateTimeImmutable('today');$sd=new DateTimeImmutable($start);if($sd>$today)return ['days'=>0,'awarded'=>0,'milestones'=>[]];
    $days=$sd->diff($today)->days+1;$st=db()->prepare('SELECT last_awarded_date,total_awarded_days FROM sobriety_accrual_state WHERE user_sic_id=?');$st->execute([$userSic]);$r=$st->fetch();
    $last=$r&&$r['last_awarded_date']?new DateTimeImmutable($r['last_awarded_date']):$sd->modify('-1 day');$from=$last->modify('+1 day');$awardDays=$from<=$today?$from->diff($today)->days+1:0;
    if($awardDays>0){
        $reward=$awardDays*drx_setting('sobriety_day_drx',1);$key='sobriety:'.$userSic.':'.$from->format('Ymd').':'.$today->format('Ymd');
        drx_post($userSic,null,$reward,'SOBRIETY_DAY',true,$key,null,['from'=>$from->format('Y-m-d'),'to'=>$today->format('Y-m-d'),'days'=>$awardDays]);
        db()->prepare('INSERT INTO sobriety_accrual_state(user_sic_id,last_awarded_date,total_awarded_days) VALUES(?,?,?) ON CONFLICT(user_sic_id) DO UPDATE SET last_awarded_date=excluded.last_awarded_date,total_awarded_days=total_awarded_days+excluded.total_awarded_days,updated_at=CURRENT_TIMESTAMP')->execute([$userSic,$today->format('Y-m-d'),$awardDays]);
    }
    db()->prepare('INSERT INTO sobriety_records(sic_id,user_sic_id,start_date,current_streak,lifetime_days) VALUES(?,?,?,?,?) ON CONFLICT(user_sic_id) DO UPDATE SET start_date=excluded.start_date,current_streak=excluded.current_streak,lifetime_days=MAX(lifetime_days,excluded.lifetime_days),updated_at=CURRENT_TIMESTAMP')->execute([sic_id(),$userSic,$start,$days,$days]);
    $milestones=[];$ms=db()->prepare('SELECT * FROM sobriety_milestones WHERE days<=? ORDER BY days');$ms->execute([$days]);
    foreach($ms->fetchAll() as $m){
        $source='SOBRIETY-'.$m['days'];$chk=db()->prepare("SELECT sic_id FROM achievements WHERE owner_type='USER' AND owner_sic_id=? AND achievement_type='SOBRIETY_MILESTONE' AND source_sic_id=?");$chk->execute([$userSic,$source]);
        if(!$chk->fetchColumn()){
            $as=sic_id();db()->prepare('INSERT INTO achievements(sic_id,owner_type,owner_sic_id,achievement_type,title,source_sic_id) VALUES(?,?,?,?,?,?)')->execute([$as,'USER',$userSic,'SOBRIETY_MILESTONE',$m['days'].' giorni · '.$m['title'],$source]);
            if((int)$m['drx_reward']>0)drx_post($userSic,null,(float)$m['drx_reward'],'SOBRIETY_MILESTONE',true,'milestone:'.$userSic.':'.$m['days'],$source,['days'=>(int)$m['days'],'title'=>$m['title']]);
            if((int)$m['certificate_enabled']===1)issue_certificate($userSic,'SOBRIETY_MILESTONE',$m['days'].' giorni · '.$m['title'],$source,['days'=>(int)$m['days'],'title'=>$m['title']]);
            $milestones[]=(int)$m['days'];
        }
    }
    return ['days'=>$days,'awarded'=>$awardDays,'milestones'=>$milestones];
}
function complete_mission(string $userSic,string $missionSic): array {$st=db()->prepare('SELECT * FROM missions WHERE sic_id=? AND status="ACTIVE"');$st->execute([$missionSic]);$m=$st->fetch();if(!$m)throw new RuntimeException('Missione non disponibile');$rule=strtoupper((string)($m['repeat_rule']??'ONCE'));$period=$rule==='DAILY'?date('Y-m-d'):($rule==='WEEKLY'?date('o-W'):($rule==='MONTHLY'?date('Y-m'):'once'));$key='mission:'.$userSic.':'.$missionSic.':'.$period;$cs=sic_id();try{db()->prepare('INSERT INTO mission_completions(sic_id,mission_sic_id,user_sic_id,verified,drx_awarded,completion_key,completed_at) VALUES(?,?,?,?,?,?,CURRENT_TIMESTAMP)')->execute([$cs,$missionSic,$userSic,1,(int)$m['drx_reward'],$key]);}catch(PDOException $e){if(str_contains(strtolower($e->getMessage()),'unique'))return ['ok'=>true,'drx'=>0,'duplicate'=>true];throw $e;}drx_post($userSic,null,(float)$m['drx_reward'],'MISSION',true,$key,$missionSic,['period'=>$period]);return ['ok'=>true,'drx'=>(int)$m['drx_reward']];}
function trusted_device_user(string $email): ?array {$cookie=$_COOKIE[TRUSTED_DEVICE_COOKIE]??'';if(!$cookie||!str_contains($cookie,'.'))return null;[$deviceSic,$secret]=explode('.',$cookie,2);$st=db()->prepare('SELECT td.*,u.* FROM trusted_devices td JOIN users u ON u.sic_id=td.user_sic_id WHERE td.sic_id=? AND u.email=? COLLATE NOCASE AND td.revoked_at IS NULL AND td.expires_at>CURRENT_TIMESTAMP');$st->execute([$deviceSic,$email]);$row=$st->fetch();return $row&&password_verify($secret,$row['token_hash'])?$row:null;}
function make_trusted_device(string $userSic): void {$deviceSic=sic_id();$secret=bin2hex(random_bytes(24));$expires=(new DateTime('+'.TRUSTED_DEVICE_DAYS.' days'))->format('Y-m-d H:i:s');db()->prepare('INSERT INTO trusted_devices(sic_id,user_sic_id,token_hash,label,last_seen_at,expires_at) VALUES(?,?,?,?,CURRENT_TIMESTAMP,?)')->execute([$deviceSic,$userSic,password_hash($secret,PASSWORD_DEFAULT),'Dispositivo browser',$expires]);setcookie(TRUSTED_DEVICE_COOKIE,$deviceSic.'.'.$secret,['expires'=>time()+TRUSTED_DEVICE_DAYS*86400,'path'=>'/','secure'=>(!empty($_SERVER['HTTPS'])),'httponly'=>true,'samesite'=>'Lax']);}
function create_recovery_code(): string {$a='ABCDEFGHJKLMNPQRSTUVWXYZ23456789';$o='';for($i=0;$i<16;$i++)$o.=$a[random_int(0,strlen($a)-1)];return implode('-',str_split($o,4));}
function club_for_user(string $userSic): ?array {$st=db()->prepare('SELECT ne.* FROM club_memberships cm JOIN network_entities ne ON ne.sic_id=cm.club_sic_id WHERE cm.user_sic_id=? AND cm.status="ACTIVE" ORDER BY cm.joined_at DESC LIMIT 1');$st->execute([$userSic]);return $st->fetch()?:null;}
function rate_limit(string $bucket,int $limit=10,int $window=300): bool {$now=time();$pdo=db();$st=$pdo->prepare('SELECT * FROM security_rate_limits WHERE bucket=?');$st->execute([$bucket]);$r=$st->fetch();if(!$r){$pdo->prepare('INSERT INTO security_rate_limits(bucket,hits,window_start) VALUES(?,1,?)')->execute([$bucket,$now]);return true;}if((int)$r['blocked_until']>$now)return false;if($now-(int)$r['window_start']>$window){$pdo->prepare('UPDATE security_rate_limits SET hits=1,window_start=?,blocked_until=0 WHERE bucket=?')->execute([$now,$bucket]);return true;}$hits=(int)$r['hits']+1;$blocked=$hits>$limit?$now+$window:0;$pdo->prepare('UPDATE security_rate_limits SET hits=?,blocked_until=? WHERE bucket=?')->execute([$hits,$blocked,$bucket]);return $hits<=$limit;}

function verify_token(): string {return bin2hex(random_bytes(16));}
function issue_certificate(string $ownerSic,string $type,string $title,?string $sourceSic=null,array $meta=[]): string {$st=db()->prepare('SELECT sic_id FROM certificates WHERE owner_sic_id=? AND certificate_type=? AND source_sic_id IS ? LIMIT 1');$st->execute([$ownerSic,$type,$sourceSic]);if($old=$st->fetchColumn())return (string)$old;$sic=sic_id();$tok=verify_token();db()->prepare('INSERT INTO certificates(sic_id,owner_sic_id,certificate_type,title,source_sic_id,verify_token,metadata_json) VALUES(?,?,?,?,?,?,?)')->execute([$sic,$ownerSic,$type,$title,$sourceSic,$tok,json_encode($meta,JSON_UNESCAPED_UNICODE)]);return $sic;}
function user_rank_refresh(string $userSic,string $source='SYSTEM'): string {$tot=drx_totals($userSic);$new=rank_for_drx($tot['qualifying']);$st=db()->prepare('SELECT rank_name FROM users WHERE sic_id=?');$st->execute([$userSic]);$old=(string)($st->fetchColumn()?:'SEME');if($new!==$old){db()->prepare('UPDATE users SET rank_name=? WHERE sic_id=?')->execute([$new,$userSic]);db()->prepare('INSERT INTO rank_events(sic_id,owner_type,owner_sic_id,old_rank,new_rank,qualifying_drx,source) VALUES(?,"USER",?,?,?,?,?)')->execute([sic_id(),$userSic,$old,$new,$tot['qualifying'],$source]);issue_certificate($userSic,'RANK','Rank '.$new,null,['rank'=>$new,'qualifying_drx'=>$tot['qualifying']]);}return $new;}
function club_rank_snapshot(string $clubSic): array {
    $pdo=db();
    $q=$pdo->prepare("SELECT COALESCE(SUM(amount),0) FROM drx_ledger WHERE club_sic_id=? AND rank_eligible=1 AND status='POSTED'");$q->execute([$clubSic]);$drx=(float)$q->fetchColumn();
    $m=$pdo->prepare('SELECT COUNT(*) FROM club_memberships WHERE club_sic_id=? AND status="ACTIVE"');$m->execute([$clubSic]);$families=(int)$m->fetchColumn();
    $ev=$pdo->prepare("SELECT COUNT(*) FROM events WHERE owner_scope_sic_id=? AND status IN ('PUBLISHED','COMPLETED') AND COALESCE(starts_at,created_at)>=datetime('now','-365 days')");$ev->execute([$clubSic]);$events=(int)$ev->fetchColumn();
    $ci=$pdo->prepare("SELECT COUNT(*) FROM event_registrations er JOIN events e ON e.sic_id=er.event_sic_id WHERE e.owner_scope_sic_id=? AND er.status='CHECKED_IN' AND COALESCE(er.checked_in_at,er.created_at)>=datetime('now','-365 days')");$ci->execute([$clubSic]);$checkins=(int)$ci->fetchColumn();
    $ac=$pdo->prepare("SELECT COUNT(*) FROM academy_enrollments ae JOIN club_memberships cm ON cm.user_sic_id=ae.user_sic_id WHERE cm.club_sic_id=? AND cm.status='ACTIVE' AND ae.status='COMPLETED' AND ae.completed_at>=datetime('now','-365 days')");$ac->execute([$clubSic]);$academy=(int)$ac->fetchColumn();
    $vh=$pdo->prepare("SELECT COALESCE(SUM(va.hours),0) FROM volunteer_actions va JOIN club_memberships cm ON cm.user_sic_id=va.user_sic_id WHERE cm.club_sic_id=? AND cm.status='ACTIVE' AND va.verified=1 AND va.created_at>=datetime('now','-365 days')");$vh->execute([$clubSic]);$volHours=(float)$vh->fetchColumn();
    $pre=$families>=8&&$families<12;$reqMult=$families>=12;
    $hudolin=0.0;if($families>=2&&$families<=12)$hudolin+=55;if($families>0&&$families<8)$hudolin+=10;if($pre)$hudolin+=15;if($reqMult)$hudolin=max(20,$hudolin-30);$hudolin=min(100,$hudolin);
    $requirements=$pdo->query('SELECT * FROM club_rank_requirements ORDER BY threshold_drx')->fetchAll();$rank='SEME';
    foreach($requirements as $r){if($drx>=(float)$r['threshold_drx']&&$families>=(int)$r['min_active_families']&&$events>=(int)$r['min_events_365']&&$checkins>=(int)$r['min_checkins_365']&&$academy>=(int)$r['min_academy_completions_365']&&$volHours>=(float)$r['min_volunteer_hours_365']&&$hudolin>=(float)$r['min_hudolin_compliance'])$rank=$r['rank_name'];}
    $score=max(0,min(100,round(min(25,$drx/40000)+min(15,$events*1.5)+min(15,$checkins/4)+min(10,$academy*2)+min(10,$volHours/5)+$hudolin*.25,1)));
    $pdo->prepare('INSERT INTO club_compliance_snapshots(sic_id,club_sic_id,family_count,pre_multiplication,multiplication_required,compliance_score,details_json) VALUES(?,?,?,?,?,?,?)')->execute([sic_id(),$clubSic,$families,$pre?1:0,$reqMult?1:0,$score,json_encode(['qualifying_drx'=>$drx,'rank'=>$rank,'events_365'=>$events,'checkins_365'=>$checkins,'academy_completions_365'=>$academy,'volunteer_hours_365'=>$volHours,'hudolin_compliance'=>$hudolin],JSON_UNESCAPED_UNICODE)]);
    $last=$pdo->prepare('SELECT rank_name FROM club_rank_history WHERE club_sic_id=? ORDER BY awarded_at DESC,id DESC LIMIT 1');$last->execute([$clubSic]);$old=$last->fetchColumn()?:'SEME';
    if($rank!==$old){$rs=sic_id();$metrics=['families'=>$families,'events'=>$events,'checkins'=>$checkins,'academy'=>$academy,'volunteer_hours'=>$volHours,'hudolin_compliance'=>$hudolin];$pdo->prepare('INSERT OR IGNORE INTO club_rank_history(sic_id,club_sic_id,rank_name,qualifying_drx,metrics_json) VALUES(?,?,?,?,?)')->execute([$rs,$clubSic,$rank,$drx,json_encode($metrics,JSON_UNESCAPED_UNICODE)]);$pdo->prepare('INSERT INTO rank_events(sic_id,owner_type,owner_sic_id,old_rank,new_rank,qualifying_drx,source) VALUES(? ,"CLUB",?,?,?,?,?)')->execute([sic_id(),$clubSic,$old,$rank,$drx,'CLUB_RANK_ENGINE']);issue_certificate($clubSic,'CLUB_RANK','Club Rank '.$rank,$rs,['rank'=>$rank,'qualifying_drx'=>$drx,'metrics'=>$metrics]);}
    return ['rank'=>$rank,'drx'=>$drx,'families'=>$families,'events_365'=>$events,'checkins_365'=>$checkins,'academy_completions_365'=>$academy,'volunteer_hours_365'=>$volHours,'hudolin_compliance'=>$hudolin,'pre_multiplication'=>$pre,'multiplication_required'=>$reqMult,'compliance_score'=>$score];
}
function create_club_multiplication(string $originClub,string $newName,string $actorSic): string {$o=db()->prepare('SELECT * FROM network_entities WHERE sic_id=?');$o->execute([$originClub]);$origin=$o->fetch();if(!$origin)throw new RuntimeException('Club origine non trovato');$new=sic_id();db()->beginTransaction();try{db()->prepare('INSERT INTO network_entities(sic_id,level,entity_name,country,region,province,comune,address,parent_name,parent_sic_id,verification_status,active_status,site_scope,map_enabled,network_enabled) VALUES(?,"CLUB",?,?,?,?,?,?,?,? ,"CREATED_IN_PLATFORM","ACTIVE",?,1,1)')->execute([$new,$newName,$origin['country'],$origin['region'],$origin['province'],$origin['comune'],$origin['address'],$origin['parent_name'],$origin['parent_sic_id'],$origin['site_scope']]);db()->prepare('INSERT INTO club_multiplications(sic_id,origin_club_sic_id,new_club_sic_id,planned_at,completed_at,status,notes) VALUES(?,?,?,CURRENT_TIMESTAMP,CURRENT_TIMESTAMP,"COMPLETED",?)')->execute([sic_id(),$originClub,$new,'Moltiplicazione digitale registrata']);audit($actorSic,'CLUB_MULTIPLICATION',$new,['origin'=>$originClub]);db()->commit();return $new;}catch(Throwable $e){if(db()->inTransaction())db()->rollBack();throw $e;}}

function user_club_sic(string $userSic): ?string {$st=db()->prepare('SELECT club_sic_id FROM club_memberships WHERE user_sic_id=? AND status="ACTIVE" ORDER BY joined_at DESC LIMIT 1');$st->execute([$userSic]);return $st->fetchColumn()?:null;}
function dao_scope_access(string $userSic,?string $scope): bool {if(!$scope)return true;if(has_role($userSic,'SUPERADMIN'))return true;$c=user_club_sic($userSic);if($c===$scope)return true;foreach(user_roles($userSic) as $r)if(($r['scope_sic_id']??null)===$scope)return true;return false;}
function dao_can_create_global(string $userSic): bool {return has_role($userSic,['NATIONAL_ADMIN','INTERNATIONAL_ADMIN','CONTINENTAL_ADMIN','WORLD_ADMIN','SUPERADMIN']);}

function site_mode(): string {$host=strtolower($_SERVER['HTTP_HOST']??'oltre.social');return str_contains($host,'dependex.social')?'DEPENDEX':'OLTRE';}
function supported_locales(): array {return ['it','en','es','pt','fr','de','hr','sr','sl','ro','ru'];}
function site_locale(): string {$req=strtolower((string)($_GET['lang']??''));if(in_array($req,supported_locales(),true)){setcookie('oltre_locale',$req,['expires'=>time()+31536000,'path'=>'/','samesite'=>'Lax']);return $req;}$cookie=strtolower((string)($_COOKIE['oltre_locale']??''));if(in_array($cookie,supported_locales(),true))return $cookie;return site_mode()==='OLTRE'?'it':'en';}
function tr(string $key,?string $fallback=null): string {static $cache=[];$loc=site_locale();if(!isset($cache[$loc])){$f=__DIR__.'/locales/'.$loc.'.json';$cache[$loc]=is_file($f)?(json_decode((string)file_get_contents($f),true)?:[]):[];}return (string)($cache[$loc][$key]??$fallback??$key);}
function site_brand(): array {return ['name'=>'DEPENDEX','subtitle'=>'AL CLUB. COL CLUB.','domain'=>(site_mode()==='DEPENDEX'?'dependex.social':'oltre.social'),'email'=>'info@dependex.social'];}

function vault_pool_balance(string $pool): float {$st=db()->prepare("SELECT COALESCE(SUM(CASE WHEN direction='IN' THEN amount ELSE -amount END),0) FROM drx_vault_ledger WHERE pool=?");$st->execute([$pool]);return (float)$st->fetchColumn();}
function vault_reserve_sync(): float {$pdo=db();$expired=$pdo->query("SELECT * FROM drx_entitlements WHERE status='CLAIMABLE' AND claim_deadline IS NOT NULL AND claim_deadline<CURRENT_TIMESTAMP")->fetchAll();foreach($expired as $e){$pdo->beginTransaction();try{$pdo->prepare("UPDATE drx_entitlements SET status='RESERVED',reserved_at=CURRENT_TIMESTAMP WHERE sic_id=? AND status='CLAIMABLE'")->execute([$e['sic_id']]);$pdo->prepare('INSERT INTO drx_vault_ledger(sic_id,pool,direction,amount,source_type,source_sic_id,metadata_json) VALUES(?,"RESERVE","IN",?,?,?,?)')->execute([sic_id(),$e['amount'],'UNCLAIMED_RESERVE',$e['sic_id'],json_encode(['user_sic_id'=>$e['user_sic_id']],JSON_UNESCAPED_UNICODE)]);$pdo->commit();}catch(Throwable $x){if($pdo->inTransaction())$pdo->rollBack();throw $x;}}return vault_pool_balance('RESERVE');}
function vault_community_allocate(float $amount,string $sourceType,string $actorSic): string {if($amount<=0)throw new InvalidArgumentException('Importo non valido');$sic=sic_id();db()->prepare('INSERT INTO drx_vault_ledger(sic_id,pool,direction,amount,source_type,source_sic_id,metadata_json) VALUES(?,"COMMUNITY","IN",?,?,?,?)')->execute([$sic,$amount,$sourceType,$actorSic,json_encode(['actor'=>$actorSic])]);return $sic;}
function entitlement_claim(string $userSic,string $entitlementSic): array {$pdo=db();$st=$pdo->prepare('SELECT * FROM drx_entitlements WHERE sic_id=? AND user_sic_id=?');$st->execute([$entitlementSic,$userSic]);$e=$st->fetch();if(!$e)throw new RuntimeException('Reward non trovato');if($e['status']==='CLAIMED')return ['duplicate'=>true];$wasReserved=$e['status']==='RESERVED';if(!in_array($e['status'],['CLAIMABLE','RESERVED'],true))throw new RuntimeException('Reward non reclamabile');$r=drx_post($userSic,null,(float)$e['amount'],$e['source_type']?:'ENTITLEMENT',(bool)$e['rank_eligible'],'claim:'.$entitlementSic,$e['source_sic_id']);$pdo->prepare("UPDATE drx_entitlements SET status='CLAIMED',claimed_at=CURRENT_TIMESTAMP,ledger_sic_id=? WHERE sic_id=?")->execute([$r['sic_id'],$entitlementSic]);if($wasReserved)$pdo->prepare('INSERT INTO drx_vault_ledger(sic_id,pool,direction,amount,source_type,source_sic_id) VALUES(?,"RESERVE","OUT",?,?,?)')->execute([sic_id(),$e['amount'],'LATE_CLAIM',$entitlementSic]);return $r;}


function entity_parent_sic(string $sic): ?string {
    $pdo=db();
    foreach([['dependex_world_registry','parent_sic_id'],['network_entities','parent_sic_id']] as [$table,$col]){
        $st=$pdo->prepare("SELECT $col FROM $table WHERE sic_id=? LIMIT 1");$st->execute([$sic]);
        $p=$st->fetchColumn();if($p!==false&&$p!==null&&$p!=='')return (string)$p;
    }
    return null;
}
function scope_is_descendant_or_self(string $target,string $ancestor,int $maxDepth=16): bool {
    if($target===$ancestor)return true;$seen=[];$cur=$target;
    for($i=0;$i<$maxDepth;$i++){if(isset($seen[$cur]))return false;$seen[$cur]=1;$p=entity_parent_sic($cur);if(!$p)return false;if($p===$ancestor)return true;$cur=$p;}
    return false;
}
function acl_action_matches(string $granted,string $requested): bool {
    if($granted==='*'||$granted===$requested)return true;
    if(str_starts_with($granted,'MANAGE_'))return in_array($requested,['READ','CREATE','UPDATE','DELETE','MANAGE'],true);
    if(str_starts_with($granted,'READ_'))return $requested==='READ';
    if(str_starts_with($granted,'WRITE_'))return in_array($requested,['CREATE','UPDATE','WRITE'],true);
    if(str_starts_with($granted,'CREATE_'))return $requested==='CREATE';
    return false;
}
function acl_scope_matches(string $grantAction,?string $grantScope,?string $targetScope,string $roleScope=''): bool {
    if($targetScope===null)return $grantScope===null && !str_contains($grantAction,'_SCOPE') && !str_contains($grantAction,'_DESCENDANTS');
    $base=$grantScope?:($roleScope?:null);
    if(str_ends_with($grantAction,'_SCOPE'))return $base!==null&&$targetScope===$base;
    if(str_ends_with($grantAction,'_DESCENDANTS'))return $base!==null&&scope_is_descendant_or_self($targetScope,$base);
    return $grantScope===null || ($base!==null&&$targetScope===$base);
}
function acl_can(string $userSic,string $resource,string $requested='READ',?string $targetScope=null,?string $ownerUserSic=null): bool {
    if($ownerUserSic&&$ownerUserSic===$userSic&&in_array($resource,['profile','journal','assessment','wallet'],true))return true;
    $roles=user_roles($userSic);if(!$roles)return false;$pdo=db();
    foreach($roles as $ur){
        $role=(string)$ur['role_code'];$roleScope=(string)($ur['scope_sic_id']??'');
        if($role==='SUPERADMIN')return true;
        $st=$pdo->prepare("SELECT * FROM acl_permissions WHERE subject_type='ROLE' AND subject_code=? ORDER BY CASE effect WHEN 'DENY' THEN 0 ELSE 1 END");
        $st->execute([$role]);
        foreach($st->fetchAll() as $p){
            if($p['resource']!=='*'&&$p['resource']!==$resource)continue;
            if($p['action']!=='*'&&!acl_action_matches((string)$p['action'],$requested))continue;
            if($p['effect']==='DENY')return false;
            if($p['effect']==='ALLOW'&&acl_scope_matches((string)$p['action'],$p['scope_sic_id']?:null,$targetScope,$roleScope))return true;
        }
    }
    return false;
}
function require_acl(string $resource,string $action='READ',?string $scope=null,?string $ownerUserSic=null): array {
    $u=require_login();
    if(!acl_can($u['sic_id'],$resource,$action,$scope,$ownerUserSic)){audit($u['sic_id'],'ACL_DENY',$scope,['resource'=>$resource,'action'=>$action]);http_response_code(403);exit('Accesso non autorizzato.');}
    return $u;
}

function daily_reflection(): array {
    $reflections = [
        ["quote" => "Il Club non è un luogo dove si guarisce, ma una comunità in cui si impara a cambiare stile di vita insieme.", "author" => "Prof. Vladimir Hudolin"],
        ["quote" => "Un giorno alla volta: la libertà di oggi è la radice solida della serenità di domani.", "author" => "Saggezza dei Club"],
        ["quote" => "La sobrietà non è solitudine o rinuncia, ma riscoperta autentica delle relazioni e dell'ascolto.", "author" => "Approccio Ecologico-Sociale"],
        ["quote" => "Non esistono problemi individuali senza risonanza comunitaria: la famiglia e il Club camminano uniti.", "author" => "Prof. Vladimir Hudolin"],
        ["quote" => "Ascolto e Legami Creano Orientamento e Libertà. Ognuno è custode del cammino dell'altro.", "author" => "OLTRE Community"],
        ["quote" => "Nel cerchio del Club ogni voce ha uguale valore e ogni silenzio viene rispettato.", "author" => "Metodo Hudolin"],
        ["quote" => "Accettare la propria vulnerabilità è il primo vero atto di coraggio e cambiamento.", "author" => "Comunità Territoriale"]
    ];
    $dayIndex = (int)date('z') % count($reflections);
    return $reflections[$dayIndex];
}

function user_daily_checkin_status(string $userSic): ?array {
    $today = date('Y-m-d');
    $st = db()->prepare('SELECT * FROM checkins WHERE user_sic_id=? AND checkin_date=? LIMIT 1');
    $st->execute([$userSic, $today]);
    return $st->fetch() ?: null;
}

function next_sobriety_milestone(int $days): array {
    $st = db()->prepare('SELECT * FROM sobriety_milestones WHERE days > ? ORDER BY days ASC LIMIT 1');
    $st->execute([$days]);
    $next = $st->fetch();
    if (!$next) {
        return ['target_days' => $days, 'title' => 'Traguardo Supremo', 'remaining_days' => 0, 'progress_pct' => 100, 'drx_reward' => 0];
    }
    $prevSt = db()->prepare('SELECT MAX(days) FROM sobriety_milestones WHERE days <= ?');
    $prevSt->execute([$days]);
    $prevDays = (int)($prevSt->fetchColumn() ?: 0);
    $range = max(1, (int)$next['days'] - $prevDays);
    $done = max(0, $days - $prevDays);
    $pct = min(100, max(0, round(($done / $range) * 100)));
    return [
        'target_days' => (int)$next['days'],
        'title' => $next['title'],
        'remaining_days' => max(0, (int)$next['days'] - $days),
        'progress_pct' => (int)$pct,
        'drx_reward' => (int)$next['drx_reward']
    ];
}

function emergency_sos_contact(string $userSic): array {
    $club = club_for_user($userSic);
    if ($club) {
        return [
            'name' => $club['entity_name'],
            'address' => $club['address'] ?: ($club['comune'] ?? 'Sede territoriale'),
            'contact' => $club['public_contact'] ?: ($club['email'] ?? 'Club Territoriale'),
            'phone' => preg_replace('/[^0-9+]/', '', (string)($club['public_contact'] ?? '')) ?: null,
            'meeting' => trim(($club['meeting_day'] ?? '').' '.($club['meeting_time'] ?? '')) ?: 'Riunione settimanale'
        ];
    }
    return [
        'name' => 'Rete di Ascolto OLTRE / ACAT',
        'address' => 'Nazionale / Territoriale',
        'contact' => 'Cerca il Club più vicino nella mappa',
        'phone' => null,
        'meeting' => 'Disponibile nella mappa'
    ];
}
