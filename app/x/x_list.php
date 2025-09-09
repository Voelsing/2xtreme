
<?php
require_once __DIR__.'/../core/bootstrap.php';
$q=trim($_GET['query']??''); $status=$_GET['status']??''; $page=max(1,(int)($_GET['page']??1));
$per=20; $off=($page-1)*$per;
$scope = can($perms,'x.view_any') ? "is_deleted=0" : (can($perms,'x.view_own') ? "is_deleted=0 AND owner_id=?" : null);
if ($scope===null){ http_response_code(403); exit('forbidden'); }
$sql="SELECT SQL_CALC_FOUND_ROWS id,owner_id,title,status,created_at,updated_at FROM x WHERE $scope";
$types=''; $params=[];
if ($q!==''){ $sql.=" AND (title LIKE CONCAT('%',?,'%') OR description LIKE CONCAT('%',?,'%'))"; $types.='ss'; $params[]=$q; $params[]=$q; }
if ($status!==''){ $sql.=" AND status=?"; $types.='s'; $params[]=$status; }
if (strpos($scope,'owner_id=?')!==false){ $types.='i'; $params[]=$userId; }
$sql.=" ORDER BY COALESCE(updated_at,created_at) DESC LIMIT ? OFFSET ?"; $types.='ii'; $params[]=$per; $params[]=$off;
$st=$conn->prepare($sql);
$st->bind_param($types, ...$params);
$st->execute();
$rows=$st->get_result()->fetch_all(MYSQLI_ASSOC); $st->close();
$total=$conn->query("SELECT FOUND_ROWS() total")->fetch_assoc()['total'] ?? 0;
?>
<?php $title='x Liste'; require __DIR__.'/../core/header.php'; ?>
<h1>x Liste</h1>
<form>
  <input name="query" value="<?=h($q)?>">
  <select name="status">
    <option value="">alle</option>
    <?php foreach(['draft','open','in_progress','done','archived'] as $s): ?>
      <option value="<?=$s?>" <?=$status===$s?'selected':''?>><?=$s?></option>
    <?php endforeach; ?>
  </select>
  <button>Suche</button>
</form>
<p><a href="x_new.php">Neu</a></p>
<table border="1" cellpadding="4">
<tr><th>ID</th><th>Titel</th><th>Status</th><th>Stand</th></tr>
<?php foreach($rows as $r): ?>
<tr>
  <td><?= (int)$r['id'] ?></td>
  <td><a href="x_view.php?id=<?=$r['id']?>"><?=h($r['title'])?></a></td>
  <td><?=h($r['status'])?></td>
  <td><?=h($r['updated_at'] ?: $r['created_at'])?></td>
</tr>
<?php endforeach; ?>
</table>
<p>Gesamt: <?=$total?></p>
<?php require __DIR__.'/../core/footer.php'; ?>
