<?php
require_once __DIR__.'/../core/bootstrap.php'; requirePerm($perms,'user.manage');

if ($_SERVER['REQUEST_METHOD']==='POST') {
  checkCsrfOrFail();
  $roleId=(int)($_POST['role_id'] ?? 0);
  $permIds=$_POST['permissions'] ?? [];
  $st=$conn->prepare("DELETE FROM role_permission WHERE role_id=?");
  $st->bind_param('i',$roleId); $st->execute(); $st->close();
  if (is_array($permIds)) {
    foreach($permIds as $pid){
      $pid=(int)$pid;
      $st=$conn->prepare("INSERT INTO role_permission (role_id,permission_id) VALUES (?,?)");
      $st->bind_param('ii',$roleId,$pid); $st->execute(); $st->close();
    }
  }
  header('Location: role_permissions.php?role='.$roleId); exit;
}

$roles=$conn->query("SELECT id,name FROM role ORDER BY name")->fetch_all(MYSQLI_ASSOC);
$permsAll=$conn->query("SELECT id,code FROM permission ORDER BY code")->fetch_all(MYSQLI_ASSOC);
$table=$conn->query("SELECT r.name,GROUP_CONCAT(p.code ORDER BY p.code SEPARATOR ', ') perms FROM role r LEFT JOIN role_permission rp ON rp.role_id=r.id LEFT JOIN permission p ON p.id=rp.permission_id GROUP BY r.id ORDER BY r.id")->fetch_all(MYSQLI_ASSOC);
$selectedRole=isset($_GET['role'])?(int)$_GET['role']:(($roles[0]['id']??0));
$selectedPerms=[];
if($selectedRole){
  $st=$conn->prepare("SELECT permission_id FROM role_permission WHERE role_id=?");
  $st->bind_param('i',$selectedRole); $st->execute();
  $selectedPerms=array_column($st->get_result()->fetch_all(MYSQLI_ASSOC),'permission_id');
  $st->close();
}
?>
<!doctype html><html><body>
<h1>Role Permissions</h1>
<table border="1">
<tr><th>Role</th><th>Permissions</th></tr>
<?php foreach($table as $row): ?>
<tr>
  <td><?=h($row['name'])?></td>
  <td><?=h($row['perms'])?></td>
</tr>
<?php endforeach; ?>
</table>
<hr>
<form method="post">
<input type="hidden" name="csrf" value="<?=h(csrfToken())?>">
<label>Role <select name="role_id">
<?php foreach($roles as $r): $sel=$r['id']==$selectedRole?' selected':''; ?>
<option value="<?=$r['id']?>"<?=$sel?>><?=h($r['name'])?></option>
<?php endforeach; ?>
</select></label><br>
<label>Permissions <select name="permissions[]" multiple size="5">
<?php foreach($permsAll as $p): $sel=in_array($p['id'],$selectedPerms)?' selected':''; ?>
<option value="<?=$p['id']?>"<?=$sel?>><?=h($p['code'])?></option>
<?php endforeach; ?>
</select></label><br>
<button type="submit">Save</button>
</form>
</body></html>
