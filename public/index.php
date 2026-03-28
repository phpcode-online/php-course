<?php

include dirname(__DIR__) . DIRECTORY_SEPARATOR . 'app' . DIRECTORY_SEPARATOR . 'autoload.inc.php';

// санируем (удаляем все лишние символы) URI, 
// который прислал браузер, чтобы не допустить атаки на сайт
$uri = preg_replace('/[^a-zA-Z0-9\/\.]/iu', '', $_SERVER['REQUEST_URI']);
$uri = preg_replace('/\.\.+/iu', '', $uri);

if ($uri == '/') {
    $actionName = 'Index';
} elseif (substr($uri, -1) == '/') {
    $actionName = ucfirst(trim(substr($uri, 0, -1), '/'));
} elseif (substr($uri, -4) == '.php') {
    $actionName = ucfirst(basename($uri, '.php'));
} else {
    die('Action Not Found.');
}

try {
    $actionClass = $actionName . 'Action';
    $action = new $actionClass;
    $action->run();
} catch (Exception $e) {
    // сюда попадем если будет какая-то не предвиденная ошибка (исключение)
    die('Шеф, все пропало: ' . $e->getMessage() . '. File: ' . $e->getFile() . ' Line: ' . $e->getLine());
}
