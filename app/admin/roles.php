<?php
require_once __DIR__.'/../core/bootstrap.php'; requirePerm($perms,'user.manage');

if ($_SERVER['REQUEST_METHOD']==='POST') {
  checkCsrfOrFail();
  $name=trim($_POST['name']??'');
  if ($name===''){ http_response_code(422); exit('missing'); }
  if (($_POST['id']??'')!=='') {
      $id=(int)$_POST['id'];
      $st=$conn->prepare("UPDATE role SET name=? WHERE id=?");
      $st->bind_param('si',$name,$id); $st->execute(); $st->close();
  } else {
      $st=$conn->prepare("INSERT INTO role (name) VALUES (?)");
      $st->bind_param('s',$name); $st->execute(); $st->close();
  }
  header('Location: roles.php'); exit;
}

$roles = $conn->query("SELECT id,name FROM role ORDER BY id")->fetch_all(MYSQLI_ASSOC);
$edit = null;
if (isset($_GET['edit'])) {
    $id=(int)$_GET['edit'];
    $st=$conn->prepare("SELECT id,name FROM role WHERE id=?");
    $st->bind_param('i',$id); $st->execute();
    $edit=$st->get_result()->fetch_assoc();
    $st->close();
}
?>
<!doctype html><html><body>
<h1>Roles</h1>
<table border="1">
<tr><th>ID</th><th>Name</th><th>Actions</th></tr>
<?php foreach($roles as $r): ?>
<tr>
  <td><?=h($r['id'])?></td>
  <td><?=h($r['name'])?></td>
  <td><a href="?edit=<?=$r['id']?>">Edit</a></td>
</tr>
<?php endforeach; ?>
</table>
<hr>
<form method="post">
<input type="hidden" name="csrf" value="<?=h(csrfToken())?>">
<input type="hidden" name="id" value="<?=h($edit['id'] ?? '')?>">
<label>Name <input name="name" value="<?=h($edit['name'] ?? '')?>"></label>
<button type="submit"><?= $edit ? 'Update' : 'Create' ?></button>
</form>
</body></html>
