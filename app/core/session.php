
<?php
session_name('user_session');
session_start([
  'cookie_secure'   => false,
  'cookie_httponly' => true,
  'cookie_samesite' => 'Strict',
]);
