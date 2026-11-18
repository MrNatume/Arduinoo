<?php
session_start();
require_once __DIR__ . '/config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    header('Content-Type: application/json');
    $action = $_POST['action'] ?? '';
    $success = false;
    $message = '';
    $brightness = $_SESSION['brightness'] ?? 0;
    $tempC = null;
    $tempF = null;

    if ($action === 'ON') {
        $result = sendArduinoCommand('1');
        $success = $result['success'];
        $message = $success ? 'LED turned ON.' : $result['message'];
        if ($success) {
            $_SESSION['brightness'] = 255;
            $brightness = 255;
        }
    } elseif ($action === 'OFF') {
        $result = sendArduinoCommand('0');
        $success = $result['success'];
        $message = $success ? 'LED turned OFF.' : $result['message'];
        if ($success) {
            $_SESSION['brightness'] = 0;
            $brightness = 0;
        }
    } elseif ($action === 'BRIGHTNESS') {
        $val = isset($_POST['brightness']) ? (int) $_POST['brightness'] : 0;
        $val = max(0, min(255, $val));
        $result = sendArduinoCommand('B' . $val);
        $success = $result['success'];
        $percent = round(($val / 255) * 100);
        $message = $success ? "LED brightness set to $percent%." : $result['message'];
        if ($success) {
            $_SESSION['brightness'] = $val;
            $brightness = $val;
        }
    } elseif ($action === 'TEMP') {
        $tempResult = getArduinoTemperature();
        $success = $tempResult['success'];
        if ($success) {
            $_SESSION['temp_c'] = $tempResult['celsius'];
            $_SESSION['temp_f'] = $tempResult['fahrenheit'];
            $tempC = $tempResult['celsius'];
            $tempF = $tempResult['fahrenheit'];
            $message = "Temperature retrieved successfully.";
        } else {
            $message = $tempResult['message'];
        }
    }

    echo json_encode([
        'success' => $success,
        'message' => $message,
        'brightness' => $brightness,
        'temp_c' => $tempC,
        'temp_f' => $tempF
    ]);
    exit;
}
?>
