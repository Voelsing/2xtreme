
<?php
require_once __DIR__.'/../core/bootstrap.php'; checkCsrfOrFail(); requirePerm($perms,'x.update');
$id=(int)($_POST['id']??0); if ($id<=0){ http_response_code(400); exit('bad'); }
$cur=$conn->prepare("SELECT owner_id,title,description,status,updated_at FROM x WHERE id=? AND is_deleted=0");
$cur->bind_param('i',$id); $cur->execute(); $cur->bind_result($ownerId,$oT,$oD,$oS,$dbU);
if(!$cur->fetch()){ http_response_code(404); exit('not found'); } $cur->close();
if (!(can($perms,'x.view_any') || ((int)$ownerId===$userId))) { http_response_code(403); exit('forbidden'); }
$dbEt=$dbU?strtotime($dbU):0; $cliEt=isset($_POST['updated_at_iso'])?strtotime($_POST['updated_at_iso']):0;
if ($dbEt && $cliEt && $cliEt < $dbEt){ http_response_code(409); exit('conflict'); }
$title=trim($_POST['title']??''); $desc=trim($_POST['description']??''); $status=$_POST['status']??$oS;
$allowed=['draft'=>['open'],'open'=>['in_progress','archived'],'in_progress'=>['done','archived'],'done'=>['archived'],'archived'=>[]];
if ($status!==$oS && !in_array($status,$allowed[$oS]??[],true)){ http_response_code(422); exit('bad status'); }
$st=$conn->prepare("UPDATE x SET title=?, description=?, status=? WHERE id=?");
$st->bind_param('sssi',$title,$desc,$status,$id); $st->execute(); $st->close();
$log=$conn->prepare("INSERT INTO x_history (x_id,actor_id,action,field,old_value,new_value) VALUES (?,?,?,?,?,?)");
if ($title!==$oT){ $a='updated'; $f='title'; $log->bind_param('iissss',$id,$userId,$a,$f,$oT,$title); $log->execute(); }
if ($desc!==$oD){ $a='updated'; $f='description'; $log->bind_param('iissss',$id,$userId,$a,$f,$oD,$desc); $log->execute(); }
if ($status!==$oS){ $a='status_change'; $f='status'; $log->bind_param('iissss',$id,$userId,$a,$f,$oS,$status); $log->execute(); }
$log->close(); audit($conn,$userId,'x.update','x',(string)$id,['title'=>$title,'status'=>$status]);
header("Location: x_view.php?id=".$id);
