<?php
// ============================================
// ПОЛНАЯ ДИАГНОСТИКА ПОЧТЫ И СЕРВЕРА
// ============================================

// Включаем отображение всех ошибок
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Создаем лог-файл для записи
$logFile = __DIR__ . '/diagnostika.log';
$timeStamp = date('Y-m-d H:i:s');

// Функция для записи в лог
function writeLog($message) {
    global $logFile, $timeStamp;
    file_put_contents($logFile, "[$timeStamp] $message\n", FILE_APPEND);
}

// Начинаем вывод
echo "<!DOCTYPE html>
<html>
<head>
    <meta charset='UTF-8'>
    <title>Диагностика сервера</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; background: #f5f5f5; }
        .container { max-width: 800px; margin: 0 auto; background: white; padding: 20px; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        h1 { color: #333; border-bottom: 2px solid #c49a6c; padding-bottom: 10px; }
        h2 { color: #555; margin-top: 30px; }
        .success { background: #d4edda; color: #155724; padding: 10px; border-radius: 5px; margin: 10px 0; }
        .error { background: #f8d7da; color: #721c24; padding: 10px; border-radius: 5px; margin: 10px 0; }
        .warning { background: #fff3cd; color: #856404; padding: 10px; border-radius: 5px; margin: 10px 0; }
        .info { background: #d1ecf1; color: #0c5460; padding: 10px; border-radius: 5px; margin: 10px 0; }
        table { width: 100%; border-collapse: collapse; margin: 10px 0; }
        td, th { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background: #f2f2f2; }
        pre { background: #f4f4f4; padding: 10px; border-radius: 5px; overflow-x: auto; }
        .test-btn { background: #c49a6c; color: white; padding: 10px 20px; border: none; border-radius: 5px; cursor: pointer; font-size: 16px; }
        .test-btn:hover { background: #a0784c; }
    </style>
</head>
<body>
<div class='container'>
    <h1>🔍 Полная диагностика сервера</h1>
    <p><strong>Время проверки:</strong> " . date('d.m.Y H:i:s') . "</p>";

// ============================================
// 1. ИНФОРМАЦИЯ О СЕРВЕРЕ
// ============================================
echo "<h2>1. Информация о сервере</h2>";

$serverInfo = [
    'PHP версия' => phpversion(),
    'Домен' => $_SERVER['HTTP_HOST'] ?? 'не определен',
    'IP сервера' => $_SERVER['SERVER_ADDR'] ?? '127.0.0.1',
    'IP клиента' => $_SERVER['REMOTE_ADDR'] ?? 'не определен',
    'Документ root' => $_SERVER['DOCUMENT_ROOT'] ?? 'не определен',
    'Текущая папка' => __DIR__,
    'Операционная система' => PHP_OS,
    'Время сервера' => date('Y-m-d H:i:s')
];

echo "<table>";
foreach ($serverInfo as $key => $value) {
    echo "<tr><th>$key</th><td>$value</td></tr>";
}
echo "</table>";

// Проверка на локальный IP
if ($_SERVER['SERVER_ADDR'] == '127.0.0.1' || $_SERVER['SERVER_ADDR'] == '::1') {
    echo "<div class='warning'>⚠️ Сервер работает на локальном адресе (127.0.0.1). Это может влиять на отправку почты.</div>";
}

// ============================================
// 2. ПРОВЕРКА ПРАВ НА ЗАПИСЬ
// ============================================
echo "<h2>2. Проверка прав на запись</h2>";

$testFile = __DIR__ . '/test-write.tmp';
$canWrite = @file_put_contents($testFile, 'test') !== false;
if ($canWrite) {
    unlink($testFile);
    echo "<div class='success'>✅ В текущую папку можно писать</div>";
} else {
    echo "<div class='error'>❌ Нет прав на запись в текущую папку</div>";
}

// ============================================
// 3. НАСТРОЙКИ PHP ДЛЯ ПОЧТЫ
// ============================================
echo "<h2>3. Настройки почты в PHP</h2>";

$mailConfig = [
    'SMTP' => ini_get('SMTP'),
    'smtp_port' => ini_get('smtp_port'),
    'sendmail_from' => ini_get('sendmail_from'),
    'sendmail_path' => ini_get('sendmail_path'),
    'mail.force_extra_parameters' => ini_get('mail.force_extra_parameters')
];

echo "<table>";
foreach ($mailConfig as $key => $value) {
    $value = $value ?: '<em>не задано</em>';
    echo "<tr><th>$key</th><td>$value</td></tr>";
}
echo "</table>";

// Проверка наличия sendmail
if (ini_get('sendmail_path')) {
    echo "<div class='success'>✅ Sendmail настроен: " . ini_get('sendmail_path') . "</div>";
} else {
    echo "<div class='warning'>⚠️ Sendmail не настроен, используется SMTP</div>";
}

// ============================================
// 4. ТЕСТОВАЯ ОТПРАВКА ПИСЬМА
// ============================================
echo "<h2>4. Тестовая отправка письма</h2>";

$testEmail = "ponomareve45@gmail.com";
$subject = "=?utf-8?B?" . base64_encode("Тест с s7pro.ru") . "?=";
$message = "Это тестовое письмо с сервера.\n\n";
$message .= "Время: " . date('Y-m-d H:i:s') . "\n";
$message .= "IP: " . ($_SERVER['SERVER_ADDR'] ?? 'unknown') . "\n";
$message .= "Хост: " . ($_SERVER['HTTP_HOST'] ?? 'unknown') . "\n";

$headers = "From: test@s7pro.ru\r\n";
$headers .= "Reply-To: test@s7pro.ru\r\n";
$headers .= "X-Mailer: PHP/" . phpversion() . "\r\n";

$mailSent = @mail($testEmail, $subject, $message, $headers);

if ($mailSent) {
    echo "<div class='success'>✅ Письмо отправлено на $testEmail</div>";
    echo "<p>Проверьте почту (включая папку Спам). Письмо должно прийти в течение нескольких минут.</p>";
    writeLog("Тестовое письмо отправлено на $testEmail");
} else {
    echo "<div class='error'>❌ Ошибка отправки тестового письма</div>";
    writeLog("ОШИБКА отправки тестового письма");
}

// ============================================
// 5. ПРОВЕРКА ЛОГОВ
// ============================================
echo "<h2>5. Проверка логов ошибок</h2>";

$errorLog = ini_get('error_log');
if ($errorLog && file_exists($errorLog)) {
    echo "<div class='success'>✅ Файл лога ошибок: $errorLog</div>";
    $logSize = filesize($errorLog);
    echo "<p>Размер лога: " . round($logSize / 1024, 2) . " KB</p>";
    
    // Показываем последние 10 строк
    $lines = file($errorLog);
    $lastLines = array_slice($lines, -10);
    echo "<p><strong>Последние записи в логе:</strong></p>";
    echo "<pre>";
    foreach ($lastLines as $line) {
        echo htmlspecialchars($line);
    }
    echo "</pre>";
} else {
    echo "<div class='warning'>⚠️ Файл лога ошибок не найден или не указан</div>";
}

// ============================================
// 6. ТЕСТ ФУНКЦИИ FILE_GET_CONTENTS
// ============================================
echo "<h2>6. Проверка соединения с интернетом</h2>";

$testUrl = 'https://www.google.com';
$context = stream_context_create([
    'http' => [
        'timeout' => 5
    ]
]);

$connected = @file_get_contents($testUrl, false, $context);
if ($connected) {
    echo "<div class='success'>✅ Есть доступ в интернет</div>";
} else {
    echo "<div class='error'>❌ Нет доступа в интернет</div>";
}

// ============================================
// 7. СОЗДАНИЕ ПРОСТОГО ТЕСТОВОГО ФАЙЛА
// ============================================
echo "<h2>7. Создание тестового файла</h2>";

$testHtml = '<!DOCTYPE html>
<html>
<head><title>Тест</title></head>
<body>
<h1>Тестовая страница</h1>
<p>Если вы видите это - файлы создаются правильно.</p>
</body>
</html>';

$testFileCreated = @file_put_contents(__DIR__ . '/test-page.html', $testHtml);
if ($testFileCreated) {
    $testUrl = 'http://' . ($_SERVER['HTTP_HOST'] ?? 's7pro.ru') . '/test-page.html';
    echo "<div class='success'>✅ Создан тестовый файл: <a href='/test-page.html' target='_blank'>test-page.html</a></div>";
} else {
    echo "<div class='error'>❌ Не удалось создать тестовый файл</div>";
}

// ============================================
// 8. ИТОГОВЫЕ РЕКОМЕНДАЦИИ
// ============================================
echo "<h2>8. Рекомендации</h2>";

$recommendations = [];

if ($_SERVER['SERVER_ADDR'] == '127.0.0.1') {
    $recommendations[] = "⚠️ Сервер на локальном IP - используйте SMTP с аутентификацией";
}

if (!$mailSent) {
    $recommendations[] = "❌ Функция mail() не работает - нужно настроить SMTP через ящик на Джино";
    $recommendations[] = "📧 Создайте ящик info@s7pro.ru в панели Джино и настройте SMTP";
}

if ($canWrite) {
    $recommendations[] = "✅ Можно сохранять заявки в файл на случай проблем с почтой";
}

if (empty($recommendations)) {
    $recommendations[] = "✅ Все проверки пройдены! Почта должна работать.";
}

echo "<ul>";
foreach ($recommendations as $rec) {
    echo "<li>$rec</li>";
}
echo "</ul>";

// ============================================
// 9. КНОПКА ДЛЯ ТЕСТА ФОРМЫ
// ============================================
echo "<h2>9. Тест формы отправки</h2>";
echo "<form id='testForm' style='margin: 20px 0;'>";
echo "<input type='text' id='testName' placeholder='Имя' value='Тест' style='padding: 8px; margin-right: 10px;'>";
echo "<input type='text' id='testPhone' placeholder='Телефон' value='123456789' style='padding: 8px; margin-right: 10px;'>";
echo "<button type='button' class='test-btn' onclick='testSend()'>Отправить тест</button>";
echo "</form>";
echo "<div id='testResult'></div>";

echo "<script>
function testSend() {
    const name = document.getElementById('testName').value;
    const phone = document.getElementById('testPhone').value;
    const resultDiv = document.getElementById('testResult');
    
    resultDiv.innerHTML = 'Отправка...';
    
    fetch('send.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: 'name=' + encodeURIComponent(name) + '&phone=' + encodeURIComponent(phone)
    })
    .then(response => response.text())
    .then(data => {
        resultDiv.innerHTML = '<div class=\"' + (data.trim() === 'OK' ? 'success' : 'error') + '\">Ответ: ' + data + '</div>';
    })
    .catch(error => {
        resultDiv.innerHTML = '<div class=\"error\">Ошибка: ' + error + '</div>';
    });
}
</script>";

// ============================================
// 10. ФОРМА ДЛЯ БЫСТРОГО ТЕСТА
// ============================================
echo "<h2>10. Быстрый тест формы</h2>";
echo "<form method='POST' action='send.php' style='margin: 20px 0;'>";
echo "<input type='text' name='name' placeholder='Имя' value='Тест' style='padding: 8px; margin-right: 10px;'>";
echo "<input type='text' name='phone' placeholder='Телефон' value='123456789' style='padding: 8px; margin-right: 10px;'>";
echo "<button type='submit' class='test-btn'>Отправить через форму</button>";
echo "</form>";

echo "<hr>";
echo "<p><strong>Лог диагностики сохранен в файл:</strong> diagnostika.log</p>";
echo "<p><a href='diagnostika.log' target='_blank'>Просмотреть лог</a></p>";

echo "</div></body></html>";

// Записываем итог в лог
writeLog("Диагностика завершена. Тест почты: " . ($mailSent ? "успешно" : "ошибка"));
?>