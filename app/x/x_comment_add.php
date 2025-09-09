
<?php
require_once __DIR__.'/../core/bootstrap.php'; checkCsrfOrFail();
$xId=(int)($_POST['x_id']??0); $body=trim($_POST['body']??'');
if ($xId<=0||$body===''){ http_response_code(422); exit('bad'); }
$ok=$conn->prepare("SELECT owner_id FROM x WHERE id=? AND is_deleted=0"); $ok->bind_param('i',$xId);
$ok->execute(); if(!$ok->fetch()){ http_response_code(404); exit('not found'); } $ok->close();
$st=$conn->prepare("INSERT INTO x_comment (x_id,user_id,body) VALUES (?,?,?)");
$st->bind_param('iis',$xId,$userId,$body); $st->execute(); $st->close();
$h=$conn->prepare("INSERT INTO x_history (x_id,actor_id,action,field,new_value) VALUES (?,?,?,?,?)");
$a='comment_added'; $f='comment'; $h->bind_param('iisss',$xId,$userId,$a,$f,$body); $h->execute(); $h->close();
audit($conn,$userId,'x.comment','x',(string)$xId,null);
header("Location: x_view.php?id=".$xId."#comments");
