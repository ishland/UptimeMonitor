<?php
$serveraddr = "127.0.0.1";
$serverport = 3406;

$content = file_get_contents('php://input');

$sock = fsockopen($serveraddr, $serverport);
fputs($sock, $content);
echo fpassthru($sock);
fclose($sock);
