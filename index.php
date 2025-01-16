<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Zaawansowana CAPTCHA</title>
    <style>
        :root {
            --bg-primary: #0a0f1d;
            --bg-secondary: #151b31;
            --text-primary: #ffffff;
            --text-secondary: #94a3b8;
            --accent-1: #3b82f6;
            --accent-2: #8b5cf6;
            --error: #ef4444;
            --success: #10b981;
            --border: #1e293b;
            --gradient-1: linear-gradient(135deg, #60a5fa, #a78bfa);
            --gradient-2: linear-gradient(135deg, rgba(30, 41, 59, 0.7) 0%, rgba(15, 23, 42, 0.8) 100%);
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            -webkit-tap-highlight-color: transparent;
        }

        body {
            background: radial-gradient(circle at top right, #1a237e 0%, var(--bg-primary) 100%);
            color: var(--text-primary);
            font-family: 'Inter', system-ui, -apple-system, sans-serif;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
            line-height: 1.6;
        }

        .captcha-container {
            width: 100%;
            max-width: 420px;
            background: var(--gradient-2);
            padding: clamp(24px, 5vw, 40px);
            border-radius: 24px;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.1);
            position: relative;
            overflow: hidden;
            transform: translateZ(0);
        }

        .captcha-container::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 2px;
            background: var(--gradient-1);
        }

        .captcha-header {
            text-align: center;
            margin-bottom: clamp(24px, 4vw, 32px);
            position: relative;
        }

        .captcha-header h2 {
            font-size: clamp(24px, 5vw, 32px);
            font-weight: 800;
            background: var(--gradient-1);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            margin-bottom: 8px;
            letter-spacing: -0.02em;
        }

        .captcha-header p {
            color: var(--text-secondary);
            font-size: clamp(14px, 3vw, 16px);
            max-width: 280px;
            margin: 0 auto;
        }

        .captcha-box {
            background: rgba(2, 6, 23, 0.7);
            padding: clamp(20px, 4vw, 32px);
            margin: clamp(20px, 4vw, 32px) 0;
            border-radius: 16px;
            position: relative;
            overflow: hidden;
            border: 1px solid var(--border);
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
            box-shadow: 0 0 30px rgba(0, 0, 0, 0.2);
            cursor: default;
            user-select: none;
        }

        .captcha-box:hover {
            transform: translateY(-2px) scale(1.01);
            border-color: var(--accent-2);
        }

        .captcha-text {
            font-family: 'Fira Code', monospace;
            font-size: clamp(28px, 6vw, 40px);
            letter-spacing: clamp(8px, 2vw, 16px);
            font-weight: 600;
            color: var(--text-primary);
            text-align: center;
            text-shadow: 0 0 20px rgba(139, 92, 246, 0.5),
                         0 0 40px rgba(139, 92, 246, 0.3);
            position: relative;
            z-index: 2;
            padding: 8px 0;
            display: flex;
            justify-content: center;
            gap: 4px;
        }

        .captcha-text span {
            display: inline-block;
            transition: all 0.3s ease;
        }

        .captcha-text span:hover {
            transform: scale(1.1) rotate(-5deg);
        }

        .captcha-overlay {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: linear-gradient(45deg,
                rgba(139, 92, 246, 0.1) 25%,
                transparent 25%,
                transparent 50%,
                rgba(139, 92, 246, 0.1) 50%,
                rgba(139, 92, 246, 0.1) 75%,
                transparent 75%,
                transparent);
            background-size: 4px 4px;
            animation: moveBackground 20s linear infinite;
            opacity: 0.3;
            pointer-events: none;
        }

        .input-wrapper {
            position: relative;
            margin-bottom: 16px;
        }

        .input-field {
            width: 100%;
            padding: clamp(14px, 3vw, 18px) clamp(16px, 3vw, 20px);
            background: rgba(2, 6, 23, 0.7);
            border: 1px solid var(--border);
            border-radius: 12px;
            color: var(--text-primary);
            font-size: clamp(14px, 3vw, 16px);
            transition: all 0.3s ease;
        }

        .input-field:focus {
            outline: none;
            border-color: var(--accent-2);
            box-shadow: 0 0 0 3px rgba(139, 92, 246, 0.2);
        }

        .input-strength {
            height: 2px;
            background: var(--border);
            position: absolute;
            bottom: -4px;
            left: 0;
            right: 0;
            border-radius: 2px;
            overflow: hidden;
        }

        .input-strength::after {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            height: 100%;
            width: 0%;
            background: var(--gradient-1);
            transition: width 0.3s ease;
        }

        .input-field:valid + .input-strength::after {
            width: 100%;
        }

        .button-group {
            display: grid;
            gap: 12px;
            margin-bottom: 16px;
        }

        .button {
            width: 100%;
            padding: clamp(14px, 3vw, 18px);
            border: none;
            border-radius: 12px;
            font-size: clamp(14px, 3vw, 16px);
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }

        .button.primary {
            background: var(--gradient-1);
            color: white;
        }

        .button.secondary {
            background: transparent;
            border: 1px solid var(--accent-2);
            color: var(--accent-2);
        }

        .button::after {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: linear-gradient(to right, transparent, rgba(255, 255, 255, 0.1), transparent);
            transform: translateX(-100%);
            transition: transform 0.5s ease;
        }

        .button:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(139, 92, 246, 0.2);
        }

        .button:hover::after {
            transform: translateX(100%);
        }

        .message {
            padding: clamp(14px, 3vw, 18px);
            border-radius: 12px;
            margin-top: 16px;
            display: none;
            text-align: center;
            font-weight: 500;
            animation: fadeIn 0.3s ease;
            font-size: clamp(14px, 3vw, 16px);
        }

        .error-message {
            background: rgba(239, 68, 68, 0.1);
            color: var(--error);
            border: 1px solid rgba(239, 68, 68, 0.2);
        }

        .success-message {
            background: rgba(16, 185, 129, 0.1);
            color: var(--success);
            border: 1px solid rgba(16, 185, 129, 0.2);
        }

        .timer {
            position: absolute;
            top: 12px;
            right: 12px;
            color: var(--text-secondary);
            font-size: clamp(12px, 2.5vw, 14px);
            background: rgba(2, 6, 23, 0.6);
            padding: 4px 8px;
            border-radius: 6px;
            backdrop-filter: blur(4px);
            display: flex;
            align-items: center;
            gap: 4px;
        }

        .timer::before {
            content: '';
            width: 8px;
            height: 8px;
            background: var(--accent-2);
            border-radius: 50%;
            animation: pulse 1s infinite;
        }

        .progress-ring {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            width: 120px;
            height: 120px;
        }

        .progress-ring__circle {
            stroke: var(--accent-2);
            stroke-width: 4;
            fill: transparent;
            r: 52;
            cx: 60;
            cy: 60;
            transform: rotate(-90deg);
            transform-origin: 50% 50%;
            transition: all 0.3s ease;
        }

        @keyframes moveBackground {
            0% { background-position: 0 0; }
            100% { background-position: 50px 50px; }
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(-10px); }
            to { opacity: 1; transform: translateY(0); }
        }

        @keyframes pulse {
            0% { transform: scale(1); opacity: 1; }
            50% { transform: scale(1.2); opacity: 0.5; }
            100% { transform: scale(1); opacity: 1; }
        }

        @media (max-width: 480px) {
            .captcha-container {
                margin: 10px;
            }
            
            .captcha-box {
                padding: 20px;
            }
            
            .button-group {
                grid-template-columns: 1fr;
            }
        }

        .security-info {
            position: absolute;
            bottom: 12px;
            left: 12px;
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 12px;
            color: var(--text-secondary);
            opacity: 0.7;
        }

        .security-level {
            width: 8px;
            height: 8px;
            border-radius: 50%;
            background: var(--success);
        }

        .security-level.medium {
            background: #f59e0b;
        }

        .security-level.low {
            background: var(--error);
        }
    </style>
</head>
<body>
    <div class="captcha-container">
        <div class="captcha-header">
            <h2>Weryfikacja</h2>
            <p>Przepisz wyświetlony kod, aby kontynuować</p>
        </div>

        <div class="captcha-box">
            <div class="captcha-overlay"></div>
            <div class="captcha-text" id="captchaText"></div>
            <div class="timer" id="timer">30s</div>
        </div>

        <input type="text" id="captchaInput" class="input-field" placeholder="Wprowadź kod" autocomplete="off">
        
        <div class="button-group">
            <button class="button primary" onclick="validateCaptcha()">Zweryfikuj</button>
            <button class="button secondary" onclick="generateCaptcha()">Nowy kod</button>
        </div>

        <div class="message error-message" id="errorMessage">
            Nieprawidłowy kod. Spróbuj ponownie.
        </div>
        <div class="message success-message" id="successMessage">
            Weryfikacja zakończona sukcesem!
        </div>
    </div>

<script>// Globalne zmienne stanu
let captchaValue = '';
let timer;
let timeLeft;
let mouseMovements = 0;
let keyPressPatterns = [];
let lastKeyPressTime = 0;
let attempts = 0;
let isBlocked = false;
let blockTimeout;
let lastGeneratedTime = 0;
let challengeStartTime = 0;
let userBehaviorScore = 100;

// Konfiguracja zabezpieczeń z bardziej realistycznymi progami
const securityConfig = {
    maxAttempts: 5,                // Zwiększona liczba dozwolonych prób
    blockDuration: 15000,          // Skrócony czas blokady do 15 sekund
    minTimeBetweenAttempts: 500,   // Zmniejszone minimalne opóźnienie między próbami
    minMouseMovements: 2,          // Zmniejszona wymagana liczba ruchów myszy
    maxResponseTime: 30000,        // Zwiększony maksymalny czas odpowiedzi
    minResponseTime: 50,           // Zmniejszony minimalny czas odpowiedzi
    minBehaviorScore: 30,          // Obniżony próg punktacji zachowania
    keyPressThreshold: 20          // Próg dla wykrywania podejrzanych wzorców klawiatury
};

// Inicjalizacja detektorów zachowania
function initializeBehaviorDetectors() {
    // Detekcja ruchów myszy
    document.addEventListener('mousemove', (e) => {
        if (!isBlocked) {
            mouseMovements++;
        }
    });

    // Detekcja wzorców klawiatury z lepszą tolerancją
    document.getElementById('captchaInput').addEventListener('keydown', (e) => {
        if (!isBlocked) {
            const currentTime = Date.now();
            if (lastKeyPressTime !== 0) {
                const timeDiff = currentTime - lastKeyPressTime;
                keyPressPatterns.push(timeDiff);
                
                // Wykrywanie wzorców z większą tolerancją
                if (keyPressPatterns.length > 5) {
                    const patterns = keyPressPatterns.slice(-6);
                    const allSimilar = patterns.every((time, i, arr) => 
                        i === 0 || Math.abs(time - arr[i-1]) < securityConfig.keyPressThreshold
                    );
                    if (allSimilar) {
                        decreaseBehaviorScore(10, "Wykryto powtarzający się wzorzec");
                    }
                }
            }
            lastKeyPressTime = currentTime;
        }
    });

    // Podstawowe zabezpieczenia
    document.getElementById('captchaText').addEventListener('copy', (e) => {
        e.preventDefault();
        decreaseBehaviorScore(15, "Próba kopiowania");
    });

    document.getElementById('captchaInput').addEventListener('paste', (e) => {
        e.preventDefault();
        decreaseBehaviorScore(15, "Próba wklejenia");
    });
}

// Funkcja zmniejszająca punktację z progiem
function decreaseBehaviorScore(amount, reason) {
    const previousScore = userBehaviorScore;
    userBehaviorScore = Math.max(0, userBehaviorScore - amount);
    
    // Blokuj tylko przy znaczącym spadku punktacji
    if (previousScore >= securityConfig.minBehaviorScore && 
        userBehaviorScore < securityConfig.minBehaviorScore) {
        blockAccess("Przekroczono próg bezpieczeństwa");
    }
}

// Funkcja blokująca z łagodniejszymi parametrami
function blockAccess(reason) {
    isBlocked = true;
    const errorMessage = document.getElementById('errorMessage');
    errorMessage.textContent = `${reason}. Odczekaj ${securityConfig.blockDuration/1000} sekund.`;
    errorMessage.style.display = 'block';
    
    document.getElementById('captchaInput').disabled = true;
    
    clearTimeout(blockTimeout);
    blockTimeout = setTimeout(() => {
        isBlocked = false;
        document.getElementById('captchaInput').disabled = false;
        resetCaptchaState();
        generateCaptcha();
    }, securityConfig.blockDuration);
}

// Resetowanie stanu z zachowaniem części punktacji
function resetCaptchaState() {
    document.getElementById('captchaInput').value = '';
    document.getElementById('errorMessage').style.display = 'none';
    document.getElementById('successMessage').style.display = 'none';
    mouseMovements = 0;
    keyPressPatterns = [];
    userBehaviorScore = Math.min(userBehaviorScore + 20, 100); // Stopniowe przywracanie punktacji
    attempts = 0;
}

// Opóźnienie z mniejszym zakresem
function addRandomDelay() {
    const delay = Math.random() * 200 + 100; // 100-300ms
    return new Promise(resolve => setTimeout(resolve, delay));
}

// Generowanie CAPTCHA z ulepszonymi efektami wizualnymi
async function generateCaptcha() {
    if (isBlocked) return;

    const currentTime = Date.now();
    if (currentTime - lastGeneratedTime < securityConfig.minTimeBetweenAttempts) {
        return;
    }
    
    lastGeneratedTime = currentTime;
    challengeStartTime = currentTime;
    
    await addRandomDelay();
    
    const chars = 'ABCDEFGHJKLMNPQRSTUVWXYZabcdefghijkmnpqrstuvwxyz23456789@#$%&';
    captchaValue = '';
    
    const length = Math.floor(Math.random() * 2) + 5; // 6-7 znaków
    for (let i = 0; i < length; i++) {
        captchaValue += chars.charAt(Math.floor(Math.random() * chars.length));
    }
    
    const captchaText = document.getElementById('captchaText');
    captchaText.innerHTML = '';
    
    [...captchaValue].forEach((char, index) => {
        const span = document.createElement('span');
        span.textContent = char;
        
        span.style.display = 'inline-block';
        span.style.transform = `
            rotate(${Math.random() * 16 - 8}deg)
            translateY(${Math.random() * 4 - 2}px)
            scale(${0.95 + Math.random() * 0.1})
        `;
        span.style.color = `hsl(${Math.random() * 360}, 60%, 75%)`;
        span.style.textShadow = '0 0 2px rgba(0,0,0,0.2)';
        span.style.transition = 'all 0.2s ease';
        span.style.margin = '0 1px';
        
        captchaText.appendChild(span);
    });

    resetCaptchaState();
    startTimer();
}

// Walidacja z lepszą tolerancją
async function validateCaptcha() {
    if (isBlocked) return;

    const input = document.getElementById('captchaInput').value;
    const timeTaken = Date.now() - challengeStartTime;

    if (timeTaken < securityConfig.minResponseTime) {
        decreaseBehaviorScore(15, "Zbyt szybka odpowiedź");
        return;
    }

    if (mouseMovements < securityConfig.minMouseMovements && attempts > 2) {
        decreaseBehaviorScore(10, "Mało interakcji");
        return;
    }

    attempts++;
    if (attempts > securityConfig.maxAttempts) {
        blockAccess("Przekroczono limit prób");
        return;
    }

    if (input === captchaValue) {
        handleSuccess();
    } else {
        handleFailure();
    }
}

// Obsługa sukcesu
function handleSuccess() {
    document.getElementById('successMessage').style.display = 'block';
    document.getElementById('errorMessage').style.display = 'none';
    userBehaviorScore = Math.min(userBehaviorScore + 10, 100); // Nagroda za poprawne rozwiązanie
    
    setTimeout(() => {
        resetCaptchaState();
        generateCaptcha();
    }, 1500);
}

// Obsługa niepowodzenia z łagodniejszymi konsekwencjami
function handleFailure() {
    document.getElementById('errorMessage').style.display = 'block';
    document.getElementById('successMessage').style.display = 'none';
    
    if (attempts > securityConfig.maxAttempts / 2) {
        addRandomDelay().then(generateCaptcha);
    } else {
        generateCaptcha();
    }
}

// Timer z dłuższym czasem
function startTimer() {
    clearInterval(timer);
    timeLeft = 30; // Zwiększony czas na rozwiązanie
    
    const timerElement = document.getElementById('timer');
    timer = setInterval(() => {
        timeLeft--;
        timerElement.textContent = `${timeLeft}s`;
        
        if (timeLeft <= 0) {
            clearInterval(timer);
            generateCaptcha();
        }
    }, 1000);
}

// Inicjalizacja systemu
function initializeCaptcha() {
    initializeBehaviorDetectors();
    generateCaptcha();
    
    document.getElementById('captchaInput').addEventListener('keypress', (e) => {
        if (!isBlocked && e.key === 'Enter') {
            validateCaptcha();
        }
    });
}

// Uruchomienie systemu
document.addEventListener('DOMContentLoaded', initializeCaptcha);
</script>
</body>
</html>