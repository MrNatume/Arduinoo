<?php
require_once __DIR__ . '/config.php';

$port = strtoupper(trim(ARDUINO_PORT));
$output = [];
$exitCode = 0;

$command = 'mode ' . $port .
           ' BAUD=' . (int) ARDUINO_BAUD .
           ' PARITY=N DATA=8 STOP=1 XON=OFF';

exec($command . ' 2>&1', $output, $exitCode);

$device = '\\\\.\\' . $port;

$handle = @fopen($device, 'w+b');
$opened = ($handle !== false);

if ($opened) {
    fclose($handle);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Arduino COM Port Test</title>
<style>
body{font-family:Arial,sans-serif;background:#f1f5f9;padding:30px}
.box{max-width:850px;margin:auto;background:white;padding:25px;border-radius:12px;box-shadow:0 6px 20px rgba(0,0,0,.08)}
pre{background:#111827;color:#e5e7eb;padding:18px;border-radius:8px;white-space:pre-wrap}
a{color:#2563eb;font-weight:bold;text-decoration:none}
</style>
</head>
<body>
<div class="box">
<h2>Arduino COM Port Diagnostic</h2>
<pre><?php
echo "Configured port: $port\n";
echo "MODE exit code: $exitCode\n";
echo "MODE output:\n" . implode("\n", $output) . "\n\n";

if ($opened) {
    echo "RESULT: PHP successfully opened $port.\n";
    echo "The COM port is accessible by PHP.\n";
} else {
    echo "RESULT: PHP could NOT open $port.\n";
    echo "Close Arduino Serial Monitor and other serial programs.\n";
}
?></pre>
<a href="index.php">&larr; Back to Arduino Controller</a>
</div>
</body>
</html>
