<?php
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    $name = strip_tags(trim($_POST["name"]));
    $phone = strip_tags(trim($_POST["phone"]));
    
    if (empty($name) || empty($phone)) {
        http_response_code(400);
        echo "Пожалуйста, заполните все поля";
        exit;
    }
    
    $to = "ponomareve45@gmail.com"; // ВАША ПОЧТА
    $subject = "Новая заявка с сайта Ателье лестниц";
    
    $email_content = "Имя: $name\n";
    $email_content .= "Телефон: $phone\n";
    
    $headers = "From: no-reply@ваш-сайт.ru\r\n";
    $headers .= "Reply-To: no-reply@ваш-сайт.ru\r\n";
    $headers .= "X-Mailer: PHP/" . phpversion();
    
    if (mail($to, $subject, $email_content, $headers)) {
        http_response_code(200);
        echo "OK";
    } else {
        http_response_code(500);
        echo "Ошибка при отправке";
    }
    
} else {
    http_response_code(403);
    echo "Доступ запрещен";
}
?>