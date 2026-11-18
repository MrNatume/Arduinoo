ARDUINO PHP LED & TEMPERATURE CONTROLLER
========================================

IMPORTANT FILES
---------------
index.php
control.php
config.php
test_com_port.php
arduino_led_controller.ino

WIRING (MATCHES CIRCUIT DIAGRAM)
--------------------------------
1. LED (PWM Brightness):
   - Digital Pin ~3 -> 200 ohm resistor -> LED Anode (+ / long leg)
   - LED Cathode (- / short leg) -> GND

2. TMP36 Temperature Sensor (flat side facing you, pins pointing down):
   - Left pin (VCC)   -> 5V
   - Middle pin (OUT) -> Analog Pin A0
   - Right pin (GND)  -> GND

INSTALL
-------
1. Copy this project folder into XAMPP htdocs, e.g.:
   C:\xampp\htdocs\arduino_php_led

2. Open config.php and set your actual Arduino COM port:
   define('ARDUINO_PORT', 'COM8');

3. Upload arduino_led_controller.ino to your Arduino Uno.

4. Close Arduino Serial Monitor (only one program can use the COM port at a time).

5. Start Apache in XAMPP.

6. Open in your browser:
   http://localhost/arduino_php_led/

DIAGNOSTIC
----------
Open:
   http://localhost/arduino_php_led/test_com_port.php

SERIAL COMMANDS
---------------
- 1        : Turn LED ON (255)
- 0        : Turn LED OFF (0)
- B<0-255> : Set LED PWM brightness (e.g. B128)
- T        : Read TMP36 temperature (returns TEMP:<C>C:<F>F)
