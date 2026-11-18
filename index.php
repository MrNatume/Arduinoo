<?php
session_start();
require_once __DIR__ . '/config.php';

$brightness = $_SESSION['brightness'] ?? 0;
$tempC = $_SESSION['temp_c'] ?? null;
$tempF = $_SESSION['temp_f'] ?? null;
$percent = round(($brightness / 255) * 100);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Arduino IoT Controller</title>
<style>
  * { box-sizing: border-box; }
  body {
    margin: 0;
    min-height: 100vh;
    display: flex;
    align-items: center;
    justify-content: center;
    background: #09090b;
    font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Arial, sans-serif;
    color: #f4f4f5;
    padding: 20px;
    position: relative;
    overflow-x: hidden;
  }
  body::before {
    content: '';
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    width: 650px;
    height: 650px;
    background: radial-gradient(circle, rgba(249,115,22,0.14) 0%, rgba(0,0,0,0) 70%);
    z-index: 0;
    pointer-events: none;
  }
  .card {
    position: relative;
    z-index: 1;
    width: 480px;
    max-width: 100%;
    background: rgba(18, 18, 22, 0.85);
    backdrop-filter: blur(20px);
    border: 1px solid rgba(255, 255, 255, 0.08);
    padding: 38px 32px;
    border-radius: 24px;
    box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.8), inset 0 1px 0 rgba(255, 255, 255, 0.1);
    text-align: center;
  }
  h1 {
    margin: 0 0 24px;
    font-size: 26px;
    font-weight: 700;
    color: #ffffff;
    letter-spacing: -0.02em;
  }
  .section {
    background: rgba(24, 24, 27, 0.75);
    border: 1px solid rgba(255, 255, 255, 0.06);
    border-radius: 16px;
    padding: 20px;
    margin-bottom: 20px;
    text-align: left;
    box-shadow: 0 4px 20px rgba(0,0,0,0.2);
  }
  .section-title {
    font-size: 11px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.08em;
    color: #f97316;
    margin-bottom: 14px;
  }
  .buttons {
    display: flex;
    gap: 12px;
  }
  button {
    border: 0;
    border-radius: 12px;
    padding: 14px 20px;
    color: #ffffff;
    font-size: 14px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.2s ease;
    flex: 1;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.3);
  }
  button:hover { transform: translateY(-1px); opacity: 0.92; }
  button:active { transform: translateY(1px); }

  .btn-on { background: linear-gradient(135deg, #16a34a, #15803d); }
  .btn-off { background: linear-gradient(135deg, #dc2626, #b91c1c); }
  .btn-bright { 
    background: linear-gradient(135deg, #f97316, #ea580c); 
    margin-top: 14px; 
    width: 100%; 
    box-shadow: 0 4px 20px rgba(249, 115, 22, 0.35);
  }
  .btn-temp { 
    background: linear-gradient(135deg, #0284c7, #0369a1); 
    width: 100%; 
    box-shadow: 0 4px 20px rgba(2, 132, 199, 0.3);
  }

  .slider-row {
    display: flex;
    align-items: center;
    gap: 14px;
  }
  input[type="range"] {
    flex: 1;
    accent-color: #f97316;
    cursor: pointer;
    height: 6px;
    background: #27272a;
    border-radius: 3px;
  }
  .slider-val {
    min-width: 48px;
    font-weight: 700;
    color: #f97316;
    font-size: 15px;
    text-align: right;
  }

  .temp-display {
    margin-top: 14px;
    padding: 16px;
    border-radius: 12px;
    background: rgba(9, 9, 11, 0.6);
    border: 1px solid rgba(249, 115, 22, 0.2);
    text-align: center;
    display: <?php echo ($tempC !== null) ? 'block' : 'none'; ?>;
  }
  .temp-main {
    font-size: 32px;
    font-weight: 800;
    color: #ffffff;
    letter-spacing: -0.02em;
  }
  .temp-sub {
    font-size: 13px;
    color: #a1a1aa;
    margin-top: 4px;
  }

  .message {
    margin-top: 18px;
    padding: 14px;
    border-radius: 12px;
    font-size: 13px;
    font-weight: 600;
    display: none;
  }
  .success { background: rgba(22, 163, 74, 0.15); color: #4ade80; border: 1px solid rgba(22, 163, 74, 0.3); }
  .error { background: rgba(220, 38, 38, 0.15); color: #f87171; border: 1px solid rgba(220, 38, 38, 0.3); }

  .footer {
    margin-top: 22px;
    font-size: 12px;
    color: #71717a;
  }
  .footer a {
    color: #f97316;
    text-decoration: none;
    font-weight: 600;
  }
  .footer a:hover { text-decoration: underline; }
</style>
</head>
<body>
<div class="card">
  <h1>Controller</h1>

  <div class="section">
    <div class="section-title">LED Power Control</div>
    <div class="buttons">
      <button class="btn-on" type="button" onclick="sendAction('ON')">Turn ON</button>
      <button class="btn-off" type="button" onclick="sendAction('OFF')">Turn OFF</button>
    </div>
  </div>

  <div class="section">
    <div class="section-title">LED Brightness</div>
    <div class="slider-row">
      <input type="range" id="brightnessSlider" name="brightness" min="0" max="255"
             value="<?php echo htmlspecialchars((string) $brightness); ?>"
             oninput="updateBrightnessLabel(this.value)">
      <span class="slider-val" id="brightnessLabel"><?php echo $percent; ?>%</span>
    </div>
    <button class="btn-bright" type="button" onclick="sendBrightness()">Set Brightness</button>
  </div>

  <div class="section">
    <div class="section-title">Temperature Sensor</div>
    <button class="btn-temp" type="button" onclick="sendAction('TEMP')">Temperature</button>
    
    <div class="temp-display" id="tempContainer" style="<?php echo ($tempC !== null) ? '' : 'display:none;'; ?>">
      <div class="temp-main" id="tempCelsius"><?php echo htmlspecialchars($tempC ?? '0'); ?> &deg;C</div>
      <div class="temp-sub" id="tempFahrenheit"><?php echo htmlspecialchars($tempF ?? '0'); ?> &deg;F</div>
    </div>
  </div>

  <div class="message" id="responseMessage"></div>

  <div class="footer">
    Close Arduino Serial Monitor before using. &bull; <a href="test_com_port.php">Test COM Port</a>
  </div>
</div>

<script>
function updateBrightnessLabel(val) {
  const pct = Math.round((val / 255) * 100);
  document.getElementById('brightnessLabel').textContent = pct + '%';
}

function showMessage(text, isSuccess) {
  const msgBox = document.getElementById('responseMessage');
  msgBox.textContent = text;
  msgBox.className = 'message ' + (isSuccess ? 'success' : 'error');
  msgBox.style.display = 'block';
  setTimeout(() => {
    msgBox.style.display = 'none';
  }, 4000);
}

function sendAction(action) {
  const formData = new FormData();
  formData.append('action', action);

  fetch('control.php', {
    method: 'POST',
    body: formData
  })
  .then(response => response.json())
  .then(data => {
    showMessage(data.message, data.success);
    if (data.success) {
      if (action === 'ON' || action === 'OFF') {
        document.getElementById('brightnessSlider').value = data.brightness;
        updateBrightnessLabel(data.brightness);
      }
      if (action === 'TEMP') {
        document.getElementById('tempCelsius').innerHTML = data.temp_c + ' &deg;C';
        document.getElementById('tempFahrenheit').innerHTML = data.temp_f + ' &deg;F';
        document.getElementById('tempContainer').style.display = 'block';
      }
    }
  })
  .catch(error => {
    showMessage('Communication error.', false);
  });
}

function sendBrightness() {
  const val = document.getElementById('brightnessSlider').value;
  const formData = new FormData();
  formData.append('action', 'BRIGHTNESS');
  formData.append('brightness', val);

  fetch('control.php', {
    method: 'POST',
    body: formData
  })
  .then(response => response.json())
  .then(data => {
    showMessage(data.message, data.success);
  })
  .catch(error => {
    showMessage('Communication error.', false);
  });
}
</script>
</body>
</html>
