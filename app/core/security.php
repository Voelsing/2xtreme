
<?php
function ipBin(): ?string {
  $ip = $_SERVER['REMOTE_ADDR'] ?? '';
  $bin = @inet_pton($ip);
  return $bin === False ? null : $bin;
}
function userAgent(): string {
  return substr($_SERVER['HTTP_USER_AGENT'] ?? '', 0, 255);
}
