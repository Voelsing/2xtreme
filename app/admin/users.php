<?php
require_once __DIR__.'/../core/bootstrap.php';
requirePerm($perms,'user.manage');

if($_SERVER['REQUEST_METHOD']==='POST'){
    checkCsrfOrFail();
    $id = (int)($_POST['id'] ?? 0);
    $username = trim($_POST['username'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $roles = $_POST['roles'] ?? [];

    if($id){
        if($password !== ''){
            $hash = password_hash($password, PASSWORD_DEFAULT);
            $st = $conn->prepare("UPDATE user SET username=?, email=?, password_hash=? WHERE id=?");
            $st->bind_param('sssi', $username, $email, $hash, $id);
        } else {
            $st = $conn->prepare("UPDATE user SET username=?, email=? WHERE id=?");
            $st->bind_param('ssi', $username, $email, $id);
        }
        $st->execute();
        $st->close();
    } else {
        $hash = password_hash($password, PASSWORD_DEFAULT);
        $st = $conn->prepare("INSERT INTO user (username,email,password_hash) VALUES (?,?,?)");
        $st->bind_param('sss', $username, $email, $hash);
        $st->execute();
        $id = $st->insert_id;
        $st->close();
    }

    $del = $conn->prepare("DELETE FROM user_role WHERE user_id=?");
    $del->bind_param('i', $id);
    $del->execute();
    $del->close();

    if(!empty($roles)){
        $ins = $conn->prepare("INSERT INTO user_role (user_id,role_id) VALUES (?,?)");
        foreach($roles as $rid){
            $rid = (int)$rid;
            $ins->bind_param('ii', $id, $rid);
            $ins->execute();
        }
        $ins->close();
    }

    header('Location: users.php');
    exit;
}

$users = $conn->query(
    "SELECT u.id,u.username,u.email, GROUP_CONCAT(r.name SEPARATOR ', ') roles " .
    "FROM user u " .
    "LEFT JOIN user_role ur ON ur.user_id=u.id " .
    "LEFT JOIN role r ON r.id=ur.role_id " .
    "GROUP BY u.id ORDER BY u.id"
)->fetch_all(MYSQLI_ASSOC);

$editId = (int)($_GET['edit'] ?? 0);
$editUser = null;
$editRoles = [];
if($editId){
    $st = $conn->prepare("SELECT id,username,email FROM user WHERE id=?");
    $st->bind_param('i', $editId);
    $st->execute();
    $editUser = $st->get_result()->fetch_assoc();
    $st->close();
    if($editUser){
        $st = $conn->prepare("SELECT role_id FROM user_role WHERE user_id=?");
        $st->bind_param('i', $editId);
        $st->execute();
        $editRoles = array_column($st->get_result()->fetch_all(MYSQLI_ASSOC), 'role_id');
        $st->close();
    }
}

$allRoles = $conn->query("SELECT id,name FROM role ORDER BY name")->fetch_all(MYSQLI_ASSOC);

$title='Users';
require __DIR__.'/../core/header.php';
?>
<h1>Users</h1>

<table border="1" cellpadding="4">
<tr><th>ID</th><th>Username</th><th>Email</th><th>Roles</th><th></th></tr>
<?php foreach($users as $u): ?>
<tr>
  <td><?= (int)$u['id'] ?></td>
  <td><?= h($u['username']) ?></td>
  <td><?= h($u['email']) ?></td>
  <td><?= h($u['roles']) ?></td>
  <td><a href="?edit=<?= (int)$u['id'] ?>">edit</a></td>
 </tr>
<?php endforeach; ?>
</table>

<h2><?= $editUser ? 'Edit User' : 'New User' ?></h2>
<form method="post">
  <input type="hidden" name="csrf" value="<?=h(csrfToken())?>">
  <?php if($editUser): ?>
    <input type="hidden" name="id" value="<?= (int)$editUser['id'] ?>">
  <?php endif; ?>
  <label>Username <input name="username" value="<?=h($editUser['username'] ?? '')?>"></label><br>
  <label>Email <input name="email" value="<?=h($editUser['email'] ?? '')?>"></label><br>
  <label>Password <input type="password" name="password"></label><br>
  <label>Roles
    <select name="roles[]" multiple size="5">
      <?php foreach($allRoles as $r): ?>
        <option value="<?=$r['id'] ?>" <?= in_array($r['id'], $editRoles) ? 'selected' : '' ?>><?= h($r['name']) ?></option>
      <?php endforeach; ?>
    </select>
  </label><br>
  <button type="submit">Save</button>
</form>

<?php require __DIR__.'/../core/footer.php'; ?>

