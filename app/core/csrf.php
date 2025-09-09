
<?php
function csrfToken(): string {
  if (empty($_SESSION['csrf'])) $_SESSION['csrf'] = bin2hex(random_bytes(32));
  return $_SESSION['csrf'];
}
function checkCsrfOrFail(): void {
  $sent = $_POST['csrf'] ?? $_GET['csrf'] ?? '';
  if (!hash_equals($_SESSION['csrf'] ?? '', $sent)) {
    http_response_code(419); exit('csrf');
  }
}
