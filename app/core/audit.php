
<?php
function audit(mysqli $c, ?int $uid, string $action, ?string $stype=null, ?string $sid=null, $data=null): void {
  $json = $data !== null ? json_encode($data, JSON_UNESCAPED_UNICODE) : null;
  $sql = "INSERT INTO audit_log (user_id,action,subject_type,subject_id,ip,ua,data) VALUES (?,?,?,?,?,?,?)";
  $st = $c->prepare($sql);
  $ip = ipBin(); $ua = userAgent();
  $st->bind_param('isssbsb', $uid, $action, $stype, $sid, $ip, $ua, $json);
  if ($json !== null) $st->send_long_data(6, $json);
  $st->execute(); $st->close();
}
