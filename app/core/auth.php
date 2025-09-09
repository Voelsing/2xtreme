
<?php
function findUserByUsername(mysqli $c, string $u) {
  $st = $c->prepare("SELECT id,password_hash,is_active,email_verified_at FROM user WHERE username=?");
  $st->bind_param('s', $u); $st->execute();
  return $st->get_result()->fetch_assoc();
}
function verifyPassword(string $hash, string $plain): bool {
  return password_verify($plain, $hash);
}
function recordAttempt(mysqli $c, string $u, bool $ok): void {
  $st=$c->prepare("INSERT INTO login_attempt (username,ip,succeeded) VALUES (?,?,?)");
  $ip=ipBin(); $i=$ok?1:0; $st->bind_param('sbi',$u,$ip,$i); $st->execute(); $st->close();
}
function tooManyFails(mysqli $c, string $u, int $n=5, int $min=15): bool {
  $st=$c->prepare("SELECT COUNT(*) cnt FROM login_attempt WHERE username=? AND succeeded=0 AND created_at>DATE_SUB(UTC_TIMESTAMP(), INTERVAL ? MINUTE)");
  $st->bind_param('si',$u,$min); $st->execute(); $cnt=$st->get_result()->fetch_assoc()['cnt']??0; return $cnt>=$n;
}
function clearLoginAttempts(mysqli $c, string $u): void {
  $st=$c->prepare("DELETE FROM login_attempt WHERE username=?");
  $st->bind_param('s',$u); $st->execute(); $st->close();
}
