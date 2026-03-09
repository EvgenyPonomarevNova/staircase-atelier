<?php
echo "<h2>Проверка отправки почты на Джино</h2>";

$to = "ponomareve45@gmail.com";
$subject = "Тест почты с s7pro.ru";
$message = "Это тестовое письмо с вашего хостинга. Время: " . date('d.m.Y H:i:s');
$headers = "From: test@s7pro.ru\r\n";
$headers .= "Reply-To: test@s7pro.ru\r\n";

if (mail($to, $subject, $message, $headers)) {
    echo "<p style='color: green;'>✅ Письмо успешно отправлено на $to</p>";
    echo "<p>Проверьте папку 'Спам', если письмо не приходит в течение 5 минут.</p>";
} else {
    echo "<p style='color: red;'>❌ Ошибка отправки. Проверьте настройки хостинга.</p>";
}

echo "<hr>";
echo "<h3>Информация о сервере:</h3>";
echo "PHP версия: " . phpversion() . "<br>";
echo "Домен: " . $_SERVER['HTTP_HOST'] . "<br>";
echo "IP сервера: " . $_SERVER['SERVER_ADDR'] . "<br>";
?>