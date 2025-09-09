
<?php
function requireLogin(): void {
  if (!isset($_SESSION['user_id'])) { header('Location: /app/auth/login.php'); exit; }
}
function userPermissions(mysqli $c, int $userId): array {
  $sql = "SELECT p.code FROM user_role ur
          JOIN role_permission rp ON rp.role_id = ur.role_id
          JOIN permission p ON p.id = rp.permission_id
          WHERE ur.user_id = ?";
  $st = $c->prepare($sql); $st->bind_param('i', $userId);
  $st->execute(); $res = $st->get_result();
  return array_unique(array_column($res->fetch_all(MYSQLI_ASSOC), 'code'));
}
function can(array $perms, string $code): bool { return in_array($code, $perms, true); }
function requirePerm(array $perms, string $code): void {
  if (!can($perms, $code)) { http_response_code(403); exit('forbidden'); }
}
