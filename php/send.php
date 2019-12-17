<?php
    error_reporting(E_ALL ^ E_NOTICE);
    
    require "classes/Rabbit.class.php";
    
    // Объявляем экземпляр RabbitMQ
    $rabbit = new Rabbit([
        'host' => 'localhost',
        'port' => 5672,
        'vhost' => '/',
        'login' => 'guest',
        'password' => 'guest'
    ]);
    
    // Собираем входящие данные в массив
    // По правильному их нужно валидировать, но нам сейчас это ни к чему, мы же для себя всё делаем
    $array = [
        'name' => $_POST['name'],
        'message' => $_POST['message']
    ];
    
    try {
        // Объявляем имя чата
        $rabbit->queue('chat2');
        // Отправляем сообщение в очередь
        echo $rabbit->send($array);
    } catch (Exception $e) {}