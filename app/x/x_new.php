
<?php require_once __DIR__.'/../core/bootstrap.php'; ?>
<?php $title='x anlegen'; require __DIR__.'/../core/header.php'; ?>
<h1>x anlegen</h1>
<form method="post" action="x_create.php">
  <input type="hidden" name="csrf" value="<?=htmlspecialchars(csrfToken(),ENT_QUOTES)?>">
  <label>Titel <input name="title"></label><br>
  <label>Beschreibung <textarea name="description"></textarea></label><br>
  <button type="submit">Speichern</button>
</form>
<?php require __DIR__.'/../core/footer.php'; ?>
