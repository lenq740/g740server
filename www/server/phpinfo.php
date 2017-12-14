<?php
session_start();
// Утилита phpinfo, используется для отладки
header("Content-type: text/html; charset=utf-8");
header("Cache-Control: no-store, no-cache, must-revalidate");
header("Cache-Control: post-check=0, pre-check=0", false);
echo '<h2>QUERY_STRING</h2>';
echo $_SERVER["QUERY_STRING"];
echo '<h2>PhpInfo</h2>';
phpinfo();
?>
