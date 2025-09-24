<?php
session_start();
echo '<h1>Session Test</h1>';
echo '<pre>';
print_r([
    'SESSION' => $_SESSION,
    'COOKIE' => $_COOKIE,
    'session_id' => session_id(),
    'session_status' => session_status()
]);
echo '</pre>';
?>