<?php
require_once __DIR__.'/../core/bootstrap.php'; checkCsrfOrFail();
$xId=(int)($_POST['x_id']??0);
if ($xId<=0 || empty($_FILES['file']['name'])){ http_response_code(422); exit('bad'); }
$ok=$conn->prepare("SELECT owner_id FROM x WHERE id=? AND is_deleted=0"); $ok->bind_param('i',$xId);
$ok->execute(); if(!$ok->fetch()){ http_response_code(404); exit('not found'); } $ok->close();
$dir=__DIR__.'/../../uploads/x/'.$xId; if (!is_dir($dir)) { mkdir($dir,0775,true); }
$orig=basename($_FILES['file']['name']); $tmp=$_FILES['file']['tmp_name'];
$mime=mime_content_type($tmp)?:'application/octet-stream'; $size=(int)$_FILES['file']['size'];
$stored=$dir.'/'.uniqid('f_',true).'_'.preg_replace('/[^a-zA-Z0-9._-]/','_',$orig);
if (!move_uploaded_file($tmp,$stored)){ http_response_code(500); exit('upload'); }
$rel='uploads/x/'.$xId.'/'.basename($stored);
$st=$conn->prepare("INSERT INTO x_file (x_id,uploaded_by,original_name,stored_path,mime_type,size_bytes) VALUES (?,?,?,?,?,?)");
$st->bind_param('iisssi',$xId,$userId,$orig,$rel,$mime,$size); $st->execute(); $st->close();
$h=$conn->prepare("INSERT INTO x_history (x_id,actor_id,action,field,new_value) VALUES (?,?,?,?,?)");
$a='file_added'; $f='file'; $nv=$orig; $h->bind_param('iisss',$xId,$userId,$a,$f,$nv); $h->execute(); $h->close();
audit($conn,$userId,'x.file.add','x',(string)$xId,['file'=>$orig]);
header("Location: x_view.php?id=".$xId."#files");
exit;
