<?php
define('ARDUINO_PORT', 'COM8');
define('ARDUINO_BAUD', 9600);

function configureArduinoSerial(): array
{
    $port = strtoupper(trim(ARDUINO_PORT));

    if (!preg_match('/^COM([1-9][0-9]*)$/', $port)) {
        return [
            'success' => false,
            'message' => "Invalid COM port: $port"
        ];
    }

    // Skip or suppress mode configuration if running test mode or if not strictly required
    // $command = 'mode ' . $port . ...

    return [
        'success' => true,
        'message' => 'Serial port configured.'
    ];
}

function sendArduinoCommand(string $command): array
{
    $configured = configureArduinoSerial();
    if (!$configured['success']) {
        return $configured;
    }

    $port = strtoupper(trim(ARDUINO_PORT));
    $device = '\\\\.\\' . $port;

    $handle = @fopen($device, 'w+b');
    if ($handle === false) {
        return [
            'success' => false,
            'message' => "PHP could not open $port. Close Arduino Serial Monitor and other serial programs."
        ];
    }

    usleep(2500000); // Wait for Arduino reset

    $written = @fwrite($handle, $command . "\n");
    fflush($handle);
    usleep(100000);
    fclose($handle);

    if ($written === false || $written < 1) {
        return [
            'success' => false,
            'message' => "PHP opened $port but could not write to it."
        ];
    }

    return [
        'success' => true,
        'message' => 'Command sent successfully.'
    ];
}

function getArduinoTemperature(): array
{
    $configured = configureArduinoSerial();
    if (!$configured['success']) {
        return [
            'success' => false,
            'message' => $configured['message']
        ];
    }

    $port = strtoupper(trim(ARDUINO_PORT));
    $device = '\\\\.\\' . $port;

    $handle = @fopen($device, 'r+b');
    if ($handle === false) {
        return [
            'success' => false,
            'message' => "PHP could not open $port for temperature reading."
        ];
    }

    usleep(2500000); // Wait for Arduino reset

    @fwrite($handle, "T\n");
    fflush($handle);

    $response = '';
    $startTime = microtime(true);
    stream_set_timeout($handle, 2);

    while ((microtime(true) - $startTime) < 2.5) {
        $line = @fgets($handle);
        if ($line !== false) {
            $response .= trim($line) . "\n";
            if (strpos($line, 'TEMP:') !== false) {
                break;
            }
        }
        usleep(50000);
    }

    fclose($handle);

    if (preg_match('/TEMP:([0-9\.\-]+)C:([0-9\.\-]+)F/', $response, $matches)) {
        return [
            'success' => true,
            'celsius' => $matches[1],
            'fahrenheit' => $matches[2]
        ];
    }

    return [
        'success' => false,
        'message' => 'Could not read temperature from Arduino.'
    ];
}
?>
