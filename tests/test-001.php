<?php

declare(strict_types=1);

define('DEBUG', true);

require dirname(__DIR__)  . DIRECTORY_SEPARATOR . 'app' . DIRECTORY_SEPARATOR . 'autoload.inc.php';

// попробуем создать несуществующий класс
$object = new App\NotExistsClass();
