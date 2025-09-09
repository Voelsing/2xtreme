
<?php
require_once __DIR__.'/../core/bootstrap.php';
$id=(int)($_GET['id']??0); if ($id<=0){ http_response_code(400); exit('bad'); }
$st=$conn->prepare("SELECT id,owner_id,title,description,status,created_at,updated_at FROM x WHERE id=? AND is_deleted=0");
$st->bind_param('i',$id); $st->execute(); $x=$st->get_result()->fetch_assoc(); $st->close();
if (!$x){ http_response_code(404); exit('not found'); }
$mayRead = can($perms,'x.view_any') || (can($perms,'x.view_own') && (int)$x['owner_id']===$userId);
if (!$mayRead){ http_response_code(403); exit('forbidden'); }

$fst=$conn->prepare("SELECT id, original_name, stored_path FROM x_file WHERE x_id=? ORDER BY created_at");
$fst->bind_param('i',$id); $fst->execute(); $files=$fst->get_result()->fetch_all(MYSQLI_ASSOC); $fst->close();

$cst=$conn->prepare("SELECT c.id, c.body, c.created_at, u.username FROM x_comment c JOIN user u ON c.user_id=u.id WHERE c.x_id=? ORDER BY c.created_at");
$cst->bind_param('i',$id); $cst->execute(); $comments=$cst->get_result()->fetch_all(MYSQLI_ASSOC); $cst->close();
?>
<?php $title='x #'.(int)$x['id']; require __DIR__.'/../core/header.php'; ?>
<h1>x #<?= (int)$x['id'] ?></h1>
<form method="post" action="x_update.php" class="mb-3">
  <input type="hidden" name="csrf" value="<?=htmlspecialchars(csrfToken(),ENT_QUOTES)?>">
  <input type="hidden" name="id" value="<?= (int)$x['id'] ?>">
  <input type="hidden" name="updated_at_iso" value="<?= htmlspecialchars($x['updated_at'] ?? '', ENT_QUOTES) ?>">
  <div class="mb-3">
    <label class="form-label">Titel</label>
    <input name="title" value="<?=h($x['title'])?>" class="form-control">
  </div>
  <div class="mb-3">
    <label class="form-label">Beschreibung</label>
    <textarea name="description" class="form-control"><?=h($x['description'])?></textarea>
  </div>
  <div class="mb-3">
    <label class="form-label">Status</label>
    <select name="status" class="form-select">
      <?php foreach(['draft','open','in_progress','done','archived'] as $s): ?>
        <option value="<?=$s?>" <?=$x['status']===$s?'selected':''?>><?=$s?></option>
      <?php endforeach; ?>
    </select>
  </div>
  <button type="submit" class="btn btn-primary w-100">Speichern</button>
</form>

<h2 id="files">Datei hochladen</h2>
<form method="post" action="x_file_upload.php" enctype="multipart/form-data" class="mb-3">
  <input type="hidden" name="csrf" value="<?=htmlspecialchars(csrfToken(),ENT_QUOTES)?>">
  <input type="hidden" name="x_id" value="<?= (int)$x['id'] ?>">
  <div class="mb-3">
    <input type="file" name="file" class="form-control">
  </div>
  <button type="submit" class="btn btn-secondary w-100">Upload</button>
</form>
<?php if ($files): ?>
<ul class="list-group mb-3">
  <?php foreach($files as $f): ?>
    <li class="list-group-item"><a href="../../<?=h($f['stored_path'])?>" download><?=h($f['original_name'])?></a></li>
  <?php endforeach; ?>
</ul>
<?php endif; ?>

<h2 id="comments">Kommentar</h2>
<form method="post" action="x_comment_add.php" class="mb-3">
  <input type="hidden" name="csrf" value="<?=htmlspecialchars(csrfToken(),ENT_QUOTES)?>">
  <input type="hidden" name="x_id" value="<?= (int)$x['id'] ?>">
  <div class="mb-3">
    <textarea name="body" class="form-control"></textarea>
  </div>
  <button class="btn btn-secondary w-100">Kommentieren</button>
</form>
<h2>Löschen</h2>
<form method="post" action="x_delete.php" onsubmit="return confirm('Wirklich löschen?');" class="mb-3">
  <input type="hidden" name="csrf" value="<?=htmlspecialchars(csrfToken(),ENT_QUOTES)?>">
  <input type="hidden" name="id" value="<?= (int)$x['id'] ?>">
  <button type="submit" class="btn btn-danger w-100">Löschen</button>
</form>
<?php if ($comments): ?>
<ul class="list-group">
  <?php foreach($comments as $c): ?>
    <li class="list-group-item"><strong><?=h($c['username'])?></strong> (<?=h($c['created_at'])?>): <?=nl2br(h($c['body']))?></li>
  <?php endforeach; ?>
</ul>
<?php endif; ?>
<?php require __DIR__.'/../core/footer.php'; ?>

