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
    
    try {
        // Объявляем имя чата
        $rabbit->queue('chat2');
        // Пытаемся получить новые сообщения
        $rabbit->get();
    } catch (Exception $e) {}
    