
<?php
require_once __DIR__.'/db.php';
require_once __DIR__.'/session.php';
require_once __DIR__.'/csrf.php';
require_once __DIR__.'/security.php';
require_once __DIR__.'/audit.php';
require_once __DIR__.'/rbac.php';
requireLogin();
$userId = (int)($_SESSION['user_id'] ?? 0);
$perms = userPermissions($conn, $userId);
function h($s){return htmlspecialchars((string)$s, ENT_QUOTES|ENT_SUBSTITUTE, 'UTF-8');}
