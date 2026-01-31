<?php
return [
    'db' => 'db',
    'host' => '127.0.0.1',
    'port' => 3306,
    'user' => 'user',     // имя пользователя для логина в БД
    'pass' => 'pass',     // пароль
    'options' => [        // массив параметров для инициализации подключения к БД
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]
];
