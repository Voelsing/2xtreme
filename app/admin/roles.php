<?php
require_once __DIR__.'/../core/bootstrap.php';
requirePerm($perms,'user.manage');

if($_SERVER['REQUEST_METHOD']==='POST'){
    checkCsrfOrFail();
    $id = (int)($_POST['id'] ?? 0);
    $name = trim($_POST['name'] ?? '');

    if($id){
        $st = $conn->prepare("UPDATE role SET name=? WHERE id=?");
        $st->bind_param('si', $name, $id);
        $st->execute();
        $st->close();
    } else {
        $st = $conn->prepare("INSERT INTO role (name) VALUES (?)");
        $st->bind_param('s', $name);
        $st->execute();
        $st->close();
    }

    header('Location: roles.php');
    exit;
}

$roles = $conn->query("SELECT id,name FROM role ORDER BY id")->fetch_all(MYSQLI_ASSOC);

$editId = (int)($_GET['edit'] ?? 0);
$editRole = null;
if($editId){
    $st = $conn->prepare("SELECT id,name FROM role WHERE id=?");
    $st->bind_param('i', $editId);
    $st->execute();
    $editRole = $st->get_result()->fetch_assoc();
    $st->close();
}

$title='Roles';
require __DIR__.'/../core/header.php';
?>
<h1>Roles</h1>

<table class="table table-striped">
<tr><th>ID</th><th>Name</th><th></th></tr>
<?php foreach($roles as $r): ?>
<tr>
  <td><?= (int)$r['id'] ?></td>
  <td><?= h($r['name']) ?></td>
  <td><a href="?edit=<?= (int)$r['id'] ?>" class="btn btn-sm btn-secondary">edit</a></td>
</tr>
<?php endforeach; ?>
</table>

<h2><?= $editRole ? 'Edit Role' : 'New Role' ?></h2>
<form method="post">
  <input type="hidden" name="csrf" value="<?=h(csrfToken())?>">
  <?php if($editRole): ?>
    <input type="hidden" name="id" value="<?= (int)$editRole['id'] ?>">
  <?php endif; ?>
  <div class="mb-3">
    <label class="form-label">Name</label>
    <input name="name" value="<?= h($editRole['name'] ?? '') ?>" class="form-control">
  </div>
  <button type="submit" class="btn btn-primary w-100">Save</button>
</form>

<?php require __DIR__.'/../core/footer.php'; ?>

