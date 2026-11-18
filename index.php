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
    background: #030000;
    font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Arial, sans-serif;
    color: #f4f4f5;
    padding: 20px;
    position: relative;
    overflow-x: hidden;
  }

  /* Fiery abstract smoke / magma wave background matching the image */
  .bg-smoke {
    position: fixed;
    top: 0;
    left: 0;
    width: 100vw;
    height: 100vh;
    z-index: 0;
    pointer-events: none;
    background: 
      radial-gradient(circle at 75% 30%, rgba(255, 69, 0, 0.35) 0%, rgba(180, 30, 0, 0.12) 35%, transparent 65%),
      radial-gradient(circle at 20% 70%, rgba(220, 50, 10, 0.25) 0%, rgba(120, 20, 0, 0.1) 40%, transparent 70%),
      radial-gradient(circle at 85% 85%, rgba(255, 100, 0, 0.2) 0%, transparent 50%),
      linear-gradient(135deg, rgba(15, 2, 0, 0.95) 0%, rgba(3, 0, 0, 1) 100%);
  }

  /* Dynamic ember particle effect */
  .ember {
    position: fixed;
    background: #ff5722;
    border-radius: 50%;
    z-index: 0;
    pointer-events: none;
    box-shadow: 0 0 8px #ff5722, 0 0 15px #ff9800;
    animation: floatUp linear infinite;
  }

  @keyframes floatUp {
    0% {
      transform: translateY(100vh) translateX(0) scale(0.5);
      opacity: 0;
    }
    20% { opacity: 0.8; }
    80% { opacity: 0.6; }
    100% {
      transform: translateY(-10vh) translateX(40px) scale(1.2);
      opacity: 0;
    }
  }

  .card {
    position: relative;
    z-index: 1;
    width: 480px;
    max-width: 100%;
    background: rgba(15, 8, 8, 0.75);
    backdrop-filter: blur(24px);
    -webkit-backdrop-filter: blur(24px);
    border: 1px solid rgba(255, 87, 34, 0.22);
    padding: 38px 32px;
    border-radius: 24px;
    box-shadow: 0 30px 60px rgba(0, 0, 0, 0.9), 0 0 35px rgba(255, 69, 0, 0.12), inset 0 1px 0 rgba(255, 150, 100, 0.15);
    text-align: center;
  }
  h1 {
    margin: 0 0 24px;
    font-size: 26px;
    font-weight: 700;
    color: #ffffff;
    letter-spacing: -0.02em;
    text-shadow: 0 2px 10px rgba(255, 69, 0, 0.3);
  }
  .section {
    background: rgba(25, 12, 10, 0.7);
    border: 1px solid rgba(255, 87, 34, 0.15);
    border-radius: 16px;
    padding: 20px;
    margin-bottom: 20px;
    text-align: left;
    box-shadow: 0 4px 20px rgba(0,0,0,0.4);
  }
  .section-title {
    font-size: 11px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.08em;
    color: #ff7043;
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
    box-shadow: 0 4px 15px rgba(0, 0, 0, 0.4);
  }
  button:hover { transform: translateY(-1px); opacity: 0.95; }
  button:active { transform: translateY(1px); }

  .btn-on { background: linear-gradient(135deg, #2e7d32, #1b5e20); box-shadow: 0 4px 15px rgba(46, 125, 50, 0.3); }
  .btn-off { background: linear-gradient(135deg, #c62828, #b71c1c); box-shadow: 0 4px 15px rgba(198, 40, 40, 0.3); }
  .btn-bright { 
    background: linear-gradient(135deg, #ff5722, #d84315); 
    margin-top: 14px; 
    width: 100%; 
    box-shadow: 0 4px 20px rgba(255, 87, 34, 0.4);
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
    accent-color: #ff5722;
    cursor: pointer;
    height: 6px;
    background: #3a1510;
    border-radius: 3px;
  }
  .slider-val {
    min-width: 48px;
    font-weight: 700;
    color: #ff7043;
    font-size: 15px;
    text-align: right;
  }

  .temp-display {
    margin-top: 14px;
    padding: 16px;
    border-radius: 12px;
    background: rgba(10, 4, 3, 0.75);
    border: 1px solid rgba(255, 87, 34, 0.3);
    text-align: center;
  }
  .temp-main {
    font-size: 32px;
    font-weight: 800;
    color: #ffffff;
    letter-spacing: -0.02em;
    text-shadow: 0 0 10px rgba(255, 87, 34, 0.4);
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
  .success { background: rgba(46, 125, 50, 0.2); color: #81c784; border: 1px solid rgba(46, 125, 50, 0.4); }
  .error { background: rgba(198, 40, 40, 0.2); color: #e57373; border: 1px solid rgba(198, 40, 40, 0.4); }

  .footer {
    margin-top: 22px;
    font-size: 12px;
    color: #71717a;
  }
  .footer a {
    color: #ff7043;
    text-decoration: none;
    font-weight: 600;
  }
  .footer a:hover { text-decoration: underline; }
</style>
</head>
<body>
<div class="bg-smoke"></div>

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
// Generate floating fiery embers
for (let i = 0; i < 25; i++) {
  const ember = document.createElement('div');
  ember.className = 'ember';
  ember.style.left = Math.random() * 100 + 'vw';
  ember.style.width = (Math.random() * 3 + 1) + 'px';
  ember.style.height = ember.style.width;
  ember.style.animationDuration = (Math.random() * 6 + 4) + 's';
  ember.style.animationDelay = (Math.random() * 5) + 's';
  document.body.appendChild(ember);
}

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
