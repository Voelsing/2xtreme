<?php
require_once __DIR__.'/../core/bootstrap.php'; requirePerm($perms,'user.manage');

if ($_SERVER['REQUEST_METHOD']==='POST') {
  checkCsrfOrFail();
  $username=trim($_POST['username']??'');
  $email=trim($_POST['email']??'');
  $roles=$_POST['roles']??[];
  $password=$_POST['password']??'';
  if($username===''||$email===''){ http_response_code(422); exit('missing'); }
  if (($_POST['id']??'')!=='') {
      $id=(int)$_POST['id'];
      $st=$conn->prepare("UPDATE user SET username=?, email=? WHERE id=?");
      $st->bind_param('ssi',$username,$email,$id); $st->execute(); $st->close();
      if($password!==''){
          $hash=password_hash($password,PASSWORD_DEFAULT);
          $st=$conn->prepare("UPDATE user SET password_hash=? WHERE id=?");
          $st->bind_param('si',$hash,$id); $st->execute(); $st->close();
      }
  } else {
      if($password===''){ http_response_code(422); exit('missing'); }
      $hash=password_hash($password,PASSWORD_DEFAULT);
      $verified=date('Y-m-d H:i:s');
      $st=$conn->prepare("INSERT INTO user (username,password_hash,email,email_verified_at) VALUES (?,?,?,?)");
      $st->bind_param('ssss',$username,$hash,$email,$verified); $st->execute(); $st->close();
      $id=$conn->insert_id;
  }
  $st=$conn->prepare("DELETE FROM user_role WHERE user_id=?"); $st->bind_param('i',$id); $st->execute(); $st->close();
  if (is_array($roles)) {
    foreach($roles as $rid){
        $rid=(int)$rid;
        $st=$conn->prepare("INSERT INTO user_role (user_id,role_id) VALUES (?,?)");
        $st->bind_param('ii',$id,$rid); $st->execute(); $st->close();
    }
  }
  header('Location: users.php'); exit;
}

$users=$conn->query("SELECT u.id,u.username,u.email,GROUP_CONCAT(r.name ORDER BY r.name SEPARATOR ', ') roles FROM user u LEFT JOIN user_role ur ON ur.user_id=u.id LEFT JOIN role r ON r.id=ur.role_id GROUP BY u.id ORDER BY u.id")->fetch_all(MYSQLI_ASSOC);
$allRoles=$conn->query("SELECT id,name FROM role ORDER BY name")->fetch_all(MYSQLI_ASSOC);
$edit=null; $editRoles=[];
if(isset($_GET['edit'])) {
    $id=(int)$_GET['edit'];
    $st=$conn->prepare("SELECT id,username,email FROM user WHERE id=?");
    $st->bind_param('i',$id); $st->execute();
    $edit=$st->get_result()->fetch_assoc(); $st->close();
    $st=$conn->prepare("SELECT role_id FROM user_role WHERE user_id=?");
    $st->bind_param('i',$id); $st->execute();
    $editRoles=array_column($st->get_result()->fetch_all(MYSQLI_ASSOC),'role_id');
    $st->close();
}
?>
<!doctype html><html><body>
<h1>Users</h1>
<table border="1">
<tr><th>ID</th><th>Username</th><th>Email</th><th>Roles</th><th>Actions</th></tr>
<?php foreach($users as $u): ?>
<tr>
  <td><?=h($u['id'])?></td>
  <td><?=h($u['username'])?></td>
  <td><?=h($u['email'])?></td>
  <td><?=h($u['roles'])?></td>
  <td><a href="?edit=<?=$u['id']?>">Edit</a></td>
</tr>
<?php endforeach; ?>
</table>
<hr>
<form method="post">
<input type="hidden" name="csrf" value="<?=h(csrfToken())?>">
<input type="hidden" name="id" value="<?=h($edit['id'] ?? '')?>">
<label>Username <input name="username" value="<?=h($edit['username'] ?? '')?>"></label><br>
<label>Email <input type="email" name="email" value="<?=h($edit['email'] ?? '')?>"></label><br>
<label>Password <input type="password" name="password"></label><br>
<label>Roles <select name="roles[]" multiple size="5">
<?php foreach($allRoles as $r): $sel=in_array($r['id'],$editRoles) ? ' selected' : ''; ?>
<option value="<?=$r['id']?>"<?=$sel?>><?=h($r['name'])?></option>
<?php endforeach; ?>
</select></label><br>
<button type="submit"><?= $edit ? 'Update' : 'Create' ?></button>
</form>
</body></html>
