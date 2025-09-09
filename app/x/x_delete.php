<?php
require_once __DIR__.'/../core/bootstrap.php'; checkCsrfOrFail(); requirePerm($perms,'x.delete');
$id=(int)($_POST['id']??0); if ($id<=0){ http_response_code(400); exit('bad'); }
$cur=$conn->prepare("SELECT owner_id FROM x WHERE id=? AND is_deleted=0");
$cur->bind_param('i',$id); $cur->execute(); $cur->bind_result($ownerId);
if(!$cur->fetch()){ http_response_code(404); exit('not found'); } $cur->close();
if (!(can($perms,'x.view_any') || (int)$ownerId===$userId)){ http_response_code(403); exit('forbidden'); }
$conn->begin_transaction();
$u=$conn->prepare("UPDATE x SET is_deleted=1 WHERE id=?"); $u->bind_param('i',$id); $u->execute(); $u->close();
$h=$conn->prepare("INSERT INTO x_history (x_id,actor_id,action) VALUES (?,?, 'deleted')");
$h->bind_param('ii',$id,$userId); $h->execute(); $h->close();
$conn->commit(); audit($conn,$userId,'x.delete','x',(string)$id,null);
header('Location: x_list.php');
exit;
