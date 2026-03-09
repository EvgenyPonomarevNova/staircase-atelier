<?php
// ============================================
// ОТПРАВКА ЧЕРЕЗ SMTP ДЖИНО - PHPMailer
// ПОЛНОСТЬЮ РАБОЧАЯ ВЕРСИЯ С ЗАЩИТОЙ ОТ СПАМА
// ============================================

// Отключаем вывод ошибок (включаем только для отладки)
error_reporting(0);
ini_set('display_errors', 0);

// Проверяем наличие PHPMailer
$phpmailer_files = [
    'PHPMailer/src/Exception.php',
    'PHPMailer/src/PHPMailer.php',
    'PHPMailer/src/SMTP.php'
];

foreach ($phpmailer_files as $file) {
    if (!file_exists($file)) {
        http_response_code(500);
        die("Ошибка сервера");
    }
}

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'PHPMailer/src/Exception.php';
require 'PHPMailer/src/PHPMailer.php';
require 'PHPMailer/src/SMTP.php';

// Получаем данные из формы
$name = trim($_POST['name'] ?? '');
$phone = trim($_POST['phone'] ?? '');

if (empty($name) || empty($phone)) {
    http_response_code(400);
    die("Пожалуйста, заполните все поля");
}

// Очищаем от вредоносного кода
$name = htmlspecialchars($name, ENT_QUOTES, 'UTF-8');
$phone = htmlspecialchars($phone, ENT_QUOTES, 'UTF-8');

$mail = new PHPMailer(true);

try {
    // ===== НАСТРОЙКИ SMTP =====
    $mail->isSMTP();
    $mail->Host       = 'smtp.jino.ru';        // БЕЗ ssl://
    $mail->Port       = 587;                    // Порт STARTTLS (из вашего скрина)
    $mail->SMTPAuth   = true;
    $mail->Username   = 'info@s7pro.ru';
    $mail->Password   = '6UMnht2de';
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS; // STARTTLS для порта 587
    $mail->CharSet    = 'UTF-8';
    $mail->Encoding   = 'base64';
    
    // ===== ДОПОЛНИТЕЛЬНЫЕ НАСТРОЙКИ ДЛЯ АНТИСПАМА =====
    $mail->SMTPKeepAlive = true;
    $mail->Priority = 1; // Высокий приоритет
    $mail->addCustomHeader('X-Priority', '1');
    $mail->addCustomHeader('X-MSMail-Priority', 'High');
    $mail->addCustomHeader('Importance', 'High');
    $mail->addCustomHeader('X-MAILER', 'PHP/' . phpversion());
    
    // ===== ОТПРАВИТЕЛЬ =====
    $mail->setFrom('info@s7pro.ru', 'Лестницы.про');
    $mail->addReplyTo('info@s7pro.ru', 'Лестницы.про');
    $mail->ReturnPath = 'info@s7pro.ru';
    
    // ===== ПОЛУЧАТЕЛЬ =====
    $mail->addAddress('s7pro.ru@gmail.com');
    
    // ===== ТЕМА =====
    $mail->Subject = '=?utf-8?B?' . base64_encode('Новая заявка с сайта Лестницы.про') . '?=';
    
    // ===== ТЕЛО ПИСЬМА (КРАСИВЫЙ HTML) =====
    $currentDate = date('d.m.Y H:i');
    $ip = $_SERVER['REMOTE_ADDR'];
    
    $htmlBody = <<<HTML
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Новая заявка</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: 'Segoe UI', Arial, sans-serif;
            background-color: #f4f4f4;
            line-height: 1.6;
        }
        .container {
            max-width: 600px;
            margin: 20px auto;
            background: #ffffff;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        }
        .header {
            background: #1a1a1a;
            padding: 30px 20px;
            text-align: center;
            border-bottom: 3px solid #c49a6c;
        }
        .header h1 {
            color: #c49a6c;
            font-weight: 300;
            font-size: 28px;
            margin: 0;
            letter-spacing: 1px;
        }
        .content {
            padding: 30px;
        }
        .info-table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
        }
        .info-table tr {
            border-bottom: 1px solid #e0e0e0;
        }
        .info-table td {
            padding: 15px 10px;
        }
        .info-table td:first-child {
            font-weight: 600;
            color: #1a1a1a;
            width: 40%;
        }
        .info-table td:last-child {
            color: #555;
        }
        .highlight {
            color: #c49a6c;
            font-weight: 600;
        }
        .footer {
            background: #f9f9f9;
            padding: 20px;
            text-align: center;
            color: #888;
            font-size: 13px;
            border-top: 1px solid #e0e0e0;
        }
        .logo {
            font-size: 24px;
            text-transform: uppercase;
            letter-spacing: 3px;
            color: #ffffff;
        }
        .logo span {
            color: #c49a6c;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <div class="logo">лестницы<span>.pro</span></div>
        </div>
        <div class="content">
            <h2 style="color: #1a1a1a; margin-bottom: 20px; font-weight: 300;">📋 Новая заявка с сайта</h2>
            <table class="info-table">
                <tr>
                    <td>👤 Имя:</td>
                    <td><span class="highlight">$name</span></td>
                </tr>
                <tr>
                    <td>📞 Телефон:</td>
                    <td><span class="highlight">$phone</span></td>
                </tr>
                <tr>
                    <td>🌐 IP адрес:</td>
                    <td>$ip</td>
                </tr>
                <tr>
                    <td>📅 Дата и время:</td>
                    <td>$currentDate</td>
                </tr>
                <tr>
                    <td>📧 Отправлено с:</td>
                    <td>info@s7pro.ru</td>
                </tr>
            </table>
        </div>
        <div class="footer">
            <p>© Лестницы.pro — производство лестниц и обшивка деревом и плиткой</p>
            <p style="margin-top: 5px;">Москва, ул. Промышленная, д.3 | +7 915 39 54 555</p>
        </div>
    </div>
</body>
</html>
HTML;

    // Текстовая версия для старых почтовых клиентов
    $textBody = "Новая заявка с сайта Лестницы.про\n\n";
    $textBody .= "Имя: $name\n";
    $textBody .= "Телефон: $phone\n";
    $textBody .= "IP: $ip\n";
    $textBody .= "Дата: $currentDate\n";
    $textBody .= "Отправлено с: info@s7pro.ru";

    $mail->isHTML(true);
    $mail->Body = $htmlBody;
    $mail->AltBody = $textBody;

    // Добавляем дополнительные заголовки для антиспама
    $mail->addCustomHeader('List-Unsubscribe', '<mailto:info@s7pro.ru?subject=unsubscribe>');
    
    // Отправляем
    $mail->send();
    echo "OK";

} catch (Exception $e) {
    // Логируем ошибку, но пользователю показываем общее сообщение
    error_log("Mail Error: " . $e->getMessage());
    http_response_code(500);
    echo "Ошибка при отправке. Пожалуйста, попробуйте позже.";
}
?>