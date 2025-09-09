
<?php
require_once __DIR__.'/../core/bootstrap.php';
$id=(int)($_GET['id']??0); if ($id<=0){ http_response_code(400); exit('bad'); }
$st=$conn->prepare("SELECT id,owner_id,title,description,status,created_at,updated_at FROM x WHERE id=? AND is_deleted=0");
$st->bind_param('i',$id); $st->execute(); $x=$st->get_result()->fetch_assoc(); $st->close();
if (!$x){ http_response_code(404); exit('not found'); }
$mayRead = can($perms,'x.view_any') || (can($perms,'x.view_own') && (int)$x['owner_id']===$userId);
if (!$mayRead){ http_response_code(403); exit('forbidden'); }
?>
<!doctype html><html><body>
<h1>x #<?= (int)$x['id'] ?></h1>
<form method="post" action="x_update.php">
  <input type="hidden" name="csrf" value="<?=htmlspecialchars(csrfToken(),ENT_QUOTES)?>">
  <input type="hidden" name="id" value="<?= (int)$x['id'] ?>">
  <input type="hidden" name="updated_at_iso" value="<?= htmlspecialchars($x['updated_at'] ?? '', ENT_QUOTES) ?>">
  <label>Titel <input name="title" value="<?=h($x['title'])?>"></label><br>
  <label>Beschreibung <textarea name="description"><?=h($x['description'])?></textarea></label><br>
  <label>Status
    <select name="status">
      <?php foreach(['draft','open','in_progress','done','archived'] as $s): ?>
        <option value="<?=$s?>" <?=$x['status']===$s?'selected':''?>><?=$s?></option>
      <?php endforeach; ?>
    </select>
  </label><br>
  <button type="submit">Speichern</button>
</form>

<h2>Datei hochladen</h2>
<form method="post" action="x_file_upload.php" enctype="multipart/form-data">
  <input type="hidden" name="csrf" value="<?=htmlspecialchars(csrfToken(),ENT_QUOTES)?>">
  <input type="hidden" name="x_id" value="<?= (int)$x['id'] ?>">
  <input type="file" name="file">
  <button type="submit">Upload</button>
</form>

<h2>Kommentar</h2>
<form method="post" action="x_comment_add.php">
  <input type="hidden" name="csrf" value="<?=htmlspecialchars(csrfToken(),ENT_QUOTES)?>">
  <input type="hidden" name="x_id" value="<?= (int)$x['id'] ?>">
  <textarea name="body"></textarea><br>
  <button>Kommentieren</button>
</form>
</body></html>
