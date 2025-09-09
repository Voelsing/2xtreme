<?php
require_once __DIR__.'/../core/session.php';
session_unset(); session_destroy();
header('Location: /app/auth/login.php');
exit;
