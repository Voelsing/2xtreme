<?php
require_once __DIR__.'/../core/bootstrap.php';
requirePerm($perms,'x.create');
checkCsrfOrFail();
$title=trim($_POST['title']??''); $desc=trim($_POST['description']??'');
if ($title===''){ http_response_code(422); exit('Titel erforderlich'); }
$st=$conn->prepare("INSERT INTO x (owner_id,title,description) VALUES (?,?,?)");
$st->bind_param('iss',$userId,$title,$desc); $st->execute(); $id=$st->insert_id; $st->close();
$h=$conn->prepare("INSERT INTO x_history (x_id,actor_id,action,field,new_value) VALUES (?,?,?,?,?)");
$act='created'; $fld='title'; $nv=$title; $h->bind_param('iisss',$id,$userId,$act,$fld,$nv); $h->execute(); $h->close();
audit($conn,$userId,'x.create','x',(string)$id,['title'=>$title]);
header("Location: x_view.php?id=".$id);
exit;
