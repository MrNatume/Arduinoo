/* 
  Arduino LED Controller & Temperature Sensor (TMP36)
  - LED: PWM Pin 3 (with 200 ohm resistor)
  - Temperature Sensor: Analog Pin A0 (TMP36)
  
  Serial Commands:
    '1'        -> LED ON (Full brightness 255)
    '0'        -> LED OFF (Brightness 0)
    'B<0-255>' -> Set LED brightness (e.g. B128)
    'T'        -> Request Temperature reading
*/

const int LED_PIN = 3;
const int TEMP_PIN = A0;

int currentBrightness = 0;

void setup() {
  pinMode(LED_PIN, OUTPUT);
  analogWrite(LED_PIN, 0);
  
  Serial.begin(9600);
  delay(500);
  Serial.println("READY");
}

void loop() {
  if (Serial.available() > 0) {
    String input = Serial.readStringUntil('\n');
    input.trim();
    
    if (input.length() > 0) {
      char cmd = input.charAt(0);
      
      if (cmd == '1') {
        currentBrightness = 255;
        analogWrite(LED_PIN, currentBrightness);
        Serial.println("LED ON");
      }
      else if (cmd == '0') {
        currentBrightness = 0;
        analogWrite(LED_PIN, currentBrightness);
        Serial.println("LED OFF");
      }
      else if (cmd == 'B') {
        int val = input.substring(1).toInt();
        currentBrightness = constrain(val, 0, 255);
        analogWrite(LED_PIN, currentBrightness);
        Serial.print("BRIGHTNESS ");
        Serial.println(currentBrightness);
      }
      else if (cmd == 'T') {
        float temperatureC = readTemperatureC();
        float temperatureF = temperatureC * 9.0 / 5.0 + 32.0;
        Serial.print("TEMP:");
        Serial.print(temperatureC, 2);
        Serial.print("C:");
        Serial.print(temperatureF, 2);
        Serial.println("F");
      }
    }
  }
}

float readTemperatureC() {
  int sensorValue = analogRead(TEMP_PIN);
  float voltage = sensorValue * (5.0 / 1023.0);
  float temperatureC = (voltage - 0.5) * 100.0;
  return temperatureC;
}
