<?php
require_once 'bootstrap.php';$u=require_admin();
if(!has_role($u['sic_id'],['NATIONAL_ADMIN','INTERNATIONAL_ADMIN','CONTINENTAL_ADMIN','WORLD_ADMIN','SUPERADMIN'])){http_response_code(403);exit('Accesso non autorizzato.');}
$rows=db()->query("SELECT subject_code,resource,action,effect FROM acl_permissions WHERE subject_type='ROLE' ORDER BY subject_code,resource,action")->fetchAll();
$pageTitle='ACL & Permessi';require '_header.php';?>
<section class="section-head"><div><span class="eyebrow">Security / RBAC / Scope</span><h1>ACL & Permessi</h1><p>I ruoli amministrativi governano il proprio ramo organizzativo. Diario, chat, assessment e dati sensibili restano esclusi dai privilegi gerarchici generali.</p></div></section>
<section class="card"><div class="table-wrap"><table><thead><tr><th>Ruolo</th><th>Risorsa</th><th>Azione</th><th>Effetto</th></tr></thead><tbody>
<?php foreach($rows as $r):?><tr><td><?=h($r['subject_code'])?></td><td><?=h($r['resource'])?></td><td><?=h($r['action'])?></td><td><?=h($r['effect'])?></td></tr><?php endforeach;?>
</tbody></table></div></section><?php require '_footer.php';?>