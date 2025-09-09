
<?php
require_once __DIR__.'/../core/db.php';
require_once __DIR__.'/../core/session.php';
require_once __DIR__.'/../core/csrf.php';
require_once __DIR__.'/../core/security.php';
require_once __DIR__.'/../core/audit.php';
require_once __DIR__.'/../core/rbac.php';
require_once __DIR__.'/../core/auth.php';

if ($_SERVER['REQUEST_METHOD']==='POST') {
  checkCsrfOrFail();
  $u=trim($_POST['username']??''); $p=$_POST['password']??'';
  if ($u===''||$p===''){ http_response_code(422); exit('missing'); }
  if (tooManyFails($conn,$u)){ http_response_code(429); exit('rate'); }
  $user=findUserByUsername($conn,$u);
  $ok=$user && $user['is_active'] && $user['email_verified_at']!==null && verifyPassword($user['password_hash'],$p);
  recordAttempt($conn,$u,$ok);
  if (!$ok){ audit($conn,null,'login.fail','user',$u,null); http_response_code(401); exit('invalid'); }
  session_regenerate_id(true);
  $_SESSION['user_id']=(int)$user['id'];
  audit($conn,$user['id'],'login.success','user',(string)$user['id'],null);
  header('Location: /app/x/x_list.php'); exit;
}
?>
<?php $title='Login'; require __DIR__.'/../core/header.php'; ?>
<form method="post">
  <input type="hidden" name="csrf" value="<?=htmlspecialchars(csrfToken(),ENT_QUOTES)?>">
  <div class="mb-3">
    <label class="form-label">Username</label>
    <input name="username" autocomplete="username" class="form-control">
  </div>
  <div class="mb-3">
    <label class="form-label">Password</label>
    <input type="password" name="password" autocomplete="current-password" class="form-control">
  </div>
  <button type="submit" class="btn btn-primary w-100">Login</button>
</form>
<?php require __DIR__.'/../core/footer.php'; ?>
