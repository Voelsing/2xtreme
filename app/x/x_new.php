
<?php require_once __DIR__.'/../core/bootstrap.php'; ?>
<?php $title='x anlegen'; require __DIR__.'/../core/header.php'; ?>
<h1>x anlegen</h1>
<form method="post" action="x_create.php">
  <input type="hidden" name="csrf" value="<?=htmlspecialchars(csrfToken(),ENT_QUOTES)?>">
  <div class="mb-3">
    <label class="form-label">Titel</label>
    <input name="title" class="form-control">
  </div>
  <div class="mb-3">
    <label class="form-label">Beschreibung</label>
    <textarea name="description" class="form-control"></textarea>
  </div>
  <button type="submit" class="btn btn-primary w-100">Speichern</button>
</form>
<?php require __DIR__.'/../core/footer.php'; ?>
