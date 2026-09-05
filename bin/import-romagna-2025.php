#!/usr/bin/env php
<?php
require_once dirname(__DIR__).'/bootstrap.php';
$parent='SIC-8A06GY1M-GCQQNXPV-P';
$source='https://www.acatromagna.com/i-nostri-club-1';
$clubs=[
['San Piero in Bagno - Altosavio','San Piero in Bagno','Sede AVIS - Via Cesare Battisti, 72','Lunedì 18:30-20:00','Cristina','338 93 77 562','161'],
['Mercato Saraceno','Mercato Saraceno','Ospedale “Cappelli” - Sala Riunioni - 3° Piano','Martedì 19:00-20:30','Alma','339 195 55 72','158'],
['Santarcangelo di Romagna - Il gabbiano','Santarcangelo di Romagna','Via Andrea Costa, 30','Martedì 20:30-22:00','Lucia','329 89 82 331','20'],
['Martorano','Martorano','Sede Quartiere Ravennate - Via T. Galimberti, 75','Mercoledì 20:30-22:00','Carlo','0547 63 13 76 - 339 13 37 245','113'],
['Cesena Oltresavio','Cesena','Sede quartiere Oltresavio - Via Pistoia, 58','Mercoledì 20:30-22:00','Ivano','346 21 76 182','152'],
['Longiano','Longiano','Via Lettonia, 26 - Ponte Ospedaletto','Mercoledì 20:30-22:00','Romildo','392 88 46 004','159'],
['San Carlo','San Carlo','Sede Quartiere Vallesavio - Via Castiglione, 37','Giovedì 20:30-22:00','Alessandro','347 528 35 09','160'],
["Sant'Egidio","Sant'Egidio",'Parrocchia - Via Chiesa di Sant’Egidio, 110','Mercoledì 20:30-22:00','Andrea','338 534 20 02','112'],
['Forlimpopoli','Forlimpopoli',"Via Duca d’Aosta, 33",'Lunedì 20:30-22:00','Massimiliano','320 045 09 30','102'],
['Bellaria - Igea Marina','Bellaria-Igea Marina','Piazza Falcone Borsellino, 19','Giovedì 20:30-22:00','Maurizio','380 188 88 49','155'],
['Lugo','Lugo','Via Francesco Bosi, 32','Sabato 09:30-11:00','Etmond','366 299 6589','164'],
['Savignano s/R','Savignano sul Rubicone','Sede Casa delle Associazioni Villa Perticari','Mercoledì 20:00-21:30','Bruno','335 689 70 69','162'],
['Sarsina','Sarsina','Teatro Silvio Pellico - Via Roma, 3','Mercoledì 18:30-20:00','Milva','345 50 34 144','163'],
['Faenza - Circolo I Fiori','Faenza','Circolo I Fiori - Via di Sopra, 34','Martedì 21:00-22:30','Laura','333 31 10 255','86'],
['Faenza - Il Borgo','Faenza','Centro Sociale Il Borgo - Via Saviotti, 1','Giovedì 21:00-22:30','Laura','333 31 10 255','97'],
['Forlì - Club 22','Forlì','Via Orceoli, 15','Lunedì 20:30-22:00','Maria','347 15 69 279','22'],
['Forlì - Club 1','Forlì','Via Orceoli, 15','Mercoledì 20:30-22:00','Maria','347 15 69 279','1'],
['Ravenna','Ravenna','Via Oriani, 44','Giovedì 20:30-22:00','Giacomo','334 28 80 607','132'],
['Cesena Centro','Cesena','Sede Volonta Romagna - Via Serraglio, 18','Lunedì 20:30-22:00','Stefania','347 31 44 853','11'],
['Rimini','Rimini','Centro Alcool e Fumo - V.le Settembrini, 2','Sabato 09:30-11:00','Lucia','329 898 23 31','37'],
['San Giovanni in Marignano','San Giovanni in Marignano','Via Ferrara, 12','Lunedì 20:30-22:00','Giovanna','338 926 76 50','47'],
['Cervia','Cervia','Viale Abruzzi, 53 Pinarella','Martedì 20:30-22:00','Maurizio','347 437 00 91','165'],
];
$pdo=db();$added=0;$updated=0;
foreach($clubs as [$name,$city,$address,$meeting,$contactName,$phone,$number]){
    $existing=$pdo->prepare("SELECT sic_id FROM dependex_world_registry WHERE network_level='LOCAL_CLUB' AND country='Italy' AND city=? AND (entity_name=? OR entity_name LIKE ?)");
    $existing->execute([$city,$name,'%club n° '.$number.'%']);$sic=$existing->fetchColumn();
    if(!$sic){
        $sic=sic_id();
        $pdo->prepare("INSERT INTO dependex_world_registry(sic_id,entity_name,original_type,network_level,network_rank,rank_color,continent,country,region,province,city,address,geo_accuracy,status,parent_sic_id,source_url,source_type,language,meeting,public_contact,public_data_score,is_synthetic) VALUES(?,?,?,'LOCAL_CLUB',1,'#D4AF37','Europe','Italy','Emilia-Romagna','Forlì-Cesena',?,?,NULL,'ACTIVE_VERIFIED_2025',?,?,?,'it',?,?,95,0)")
            ->execute([$sic,$name.' (club n° '.$number.')','CAT',$city,$address,$parent,$source,'ACAT Romagna official 2025 directory image',$meeting,$contactName.' · '.$phone]);
        $pdo->prepare("INSERT OR IGNORE INTO dependex_world_edges(sic_id,parent_sic_id,child_sic_id,relation_type) VALUES(?,?,?,'PARENT_OF')")->execute([sic_id(),$parent,$sic]);
        $added++;
    }else{
        $pdo->prepare("UPDATE dependex_world_registry SET address=?,meeting=?,public_contact=?,source_url=?,source_type='ACAT Romagna official 2025 directory image',status='ACTIVE_VERIFIED_2025',updated_at=CURRENT_TIMESTAMP WHERE sic_id=?")
            ->execute([$address,$meeting,$contactName.' · '.$phone,$source,$sic]);$updated++;
    }
    $pdo->prepare("INSERT OR IGNORE INTO club_contacts(sic_id,club_sic_id,contact_type,label,value,is_public) VALUES(?,?,?,?,?,1)")->execute([sic_id(),$sic,'PHONE',$contactName,$phone]);
    $query=implode(', ',array_filter([$address,$city,'Emilia-Romagna','Italy']));
    $pdo->prepare("INSERT OR IGNORE INTO geocode_queue(sic_id,entity_sic_id,query_text,status) VALUES(?,?,?,'PENDING')")->execute([sic_id(),$sic,$query]);
    $pdo->prepare("INSERT OR IGNORE INTO research_tasks(sic_id,country,entity_sic_id,priority,task_type,query_text,reason) VALUES(?, 'Italy',?,'P0','GEOCODE_VERIFY',?,'Official 2025 ACAT Romagna address: verify coordinates')")->execute([sic_id(),$sic,$query]);
}
// Recompute parent counts
$pdo->prepare("UPDATE dependex_world_registry SET direct_children=(SELECT COUNT(*) FROM dependex_world_edges e WHERE e.parent_sic_id=dependex_world_registry.sic_id),network_descendants=(SELECT COUNT(*) FROM dependex_world_edges e WHERE e.parent_sic_id=dependex_world_registry.sic_id) WHERE sic_id=?")->execute([$parent]);
echo "added=$added updated=$updated\n";
