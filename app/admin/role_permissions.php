<?php
require_once __DIR__.'/../core/bootstrap.php';
requirePerm($perms,'user.manage');

if($_SERVER['REQUEST_METHOD']==='POST'){
    checkCsrfOrFail();
    $roleId = (int)($_POST['role_id'] ?? 0);
    $permsSel = $_POST['permissions'] ?? [];

    $del = $conn->prepare("DELETE FROM role_permission WHERE role_id=?");
    $del->bind_param('i', $roleId);
    $del->execute();
    $del->close();

    if(!empty($permsSel)){
        $ins = $conn->prepare("INSERT INTO role_permission (role_id,permission_id) VALUES (?,?)");
        foreach($permsSel as $pid){
            $pid = (int)$pid;
            $ins->bind_param('ii', $roleId, $pid);
            $ins->execute();
        }
        $ins->close();
    }

    header('Location: role_permissions.php?role_id='.$roleId);
    exit;
}

$rolePerms = $conn->query(
    "SELECT r.id,r.name, GROUP_CONCAT(p.code SEPARATOR ', ') perms " .
    "FROM role r " .
    "LEFT JOIN role_permission rp ON rp.role_id=r.id " .
    "LEFT JOIN permission p ON p.id=rp.permission_id " .
    "GROUP BY r.id ORDER BY r.id"
)->fetch_all(MYSQLI_ASSOC);

$roleId = (int)($_GET['role_id'] ?? 0);
$roles = $conn->query("SELECT id,name FROM role ORDER BY name")->fetch_all(MYSQLI_ASSOC);
$permissions = $conn->query("SELECT id,code FROM permission ORDER BY code")->fetch_all(MYSQLI_ASSOC);
$selected = [];
if($roleId){
    $st = $conn->prepare("SELECT permission_id FROM role_permission WHERE role_id=?");
    $st->bind_param('i', $roleId);
    $st->execute();
    $selected = array_column($st->get_result()->fetch_all(MYSQLI_ASSOC), 'permission_id');
    $st->close();
}

$title='Role Permissions';
require __DIR__.'/../core/header.php';
?>
<h1>Role Permissions</h1>

<table border="1" cellpadding="4">
<tr><th>Role</th><th>Permissions</th><th></th></tr>
<?php foreach($rolePerms as $rp): ?>
<tr>
  <td><?= h($rp['name']) ?></td>
  <td><?= h($rp['perms']) ?></td>
  <td><a href="?role_id=<?= (int)$rp['id'] ?>">edit</a></td>
</tr>
<?php endforeach; ?>
</table>

<h2>Assign Permissions</h2>
<form method="post">
  <input type="hidden" name="csrf" value="<?=h(csrfToken())?>">
  <label>Role
    <select name="role_id">
      <?php foreach($roles as $r): ?>
        <option value="<?=$r['id']?>" <?= $roleId===$r['id']?'selected':''?>><?=h($r['name'])?></option>
      <?php endforeach; ?>
    </select>
  </label><br>
  <label>Permissions
    <select name="permissions[]" multiple size="5">
      <?php foreach($permissions as $p): ?>
        <option value="<?=$p['id']?>" <?= in_array($p['id'], $selected) ? 'selected' : '' ?>><?=h($p['code'])?></option>
      <?php endforeach; ?>
    </select>
  </label><br>
  <button type="submit">Save</button>
</form>

<?php require __DIR__.'/../core/footer.php'; ?>

