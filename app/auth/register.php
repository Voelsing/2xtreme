<?php
require_once __DIR__.'/../core/db.php';
require_once __DIR__.'/../core/session.php';
require_once __DIR__.'/../core/csrf.php';
require_once __DIR__.'/../core/security.php';
require_once __DIR__.'/../core/audit.php';

// allow registration only if no user exists yet
$cntRes=$conn->query("SELECT COUNT(*) cnt FROM user");
$hasUser=((int)($cntRes->fetch_assoc()['cnt']??0))>0;
if($hasUser){ http_response_code(403); exit('forbidden'); }

if($_SERVER['REQUEST_METHOD']==='POST'){
  checkCsrfOrFail();
  $u=trim($_POST['username']??'');
  $e=trim($_POST['email']??'');
  $p=$_POST['password']??'';
  if($u===''||$e===''||$p===''){ http_response_code(422); exit('missing'); }
  $hash=password_hash($p,PASSWORD_DEFAULT);
  $conn->begin_transaction();
  try{
    $st=$conn->prepare("INSERT INTO user (username,email,password_hash,email_verified_at) VALUES (?,?,?,UTC_TIMESTAMP())");
    $st->bind_param('sss',$u,$e,$hash);
    $st->execute();
    $uid=$st->insert_id; $st->close();
    $role=1;
    $st=$conn->prepare("INSERT INTO user_role (user_id,role_id) VALUES (?,?)");
    $st->bind_param('ii',$uid,$role);
    $st->execute(); $st->close();
    $conn->commit();
    audit($conn,$uid,'user.register','user',(string)$uid,null);
  }catch(Throwable $ex){
    $conn->rollback();
    http_response_code(500); exit('error');
  }
  session_regenerate_id(true);
  $_SESSION['user_id']=(int)$uid;
    header('Location: '.BASE_URL.'/app/x/x_list.php'); exit;
}
?>
<?php $title='Register'; require __DIR__.'/../core/header.php'; ?>
<form method="post">
  <input type="hidden" name="csrf" value="<?=htmlspecialchars(csrfToken(),ENT_QUOTES)?>">
  <label>Username <input name="username" required></label><br>
  <label>Email <input type="email" name="email" required></label><br>
  <label>Password <input type="password" name="password" required></label><br>
  <button type="submit">Register</button>
</form>
<?php require __DIR__.'/../core/footer.php'; ?>
