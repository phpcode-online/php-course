<?php

declare(strict_types=1);

// путь до файлов с логикой
$appRoot = dirname($_SERVER['DOCUMENT_ROOT']) . DIRECTORY_SEPARATOR . 'app' . DIRECTORY_SEPARATOR;
// санируем данные
$uri = preg_replace('/[^a-zA-Z0-9\/\.]/iu', '', $_SERVER['REQUEST_URI']);
$uri = preg_replace('/\.\.+/iu', '', $uri);

// анализируем путь запроса для подключения нужного скрипта с логикой
if ($uri == '/') {
    $actionScript =  $appRoot . 'actions/index.php';
} elseif (substr($uri, -1) == '/') {
    $actionScript = $appRoot . 'actions/' . substr($uri, 0, -1) . '.php';
} elseif (substr($uri, -4) == '.php') {
    $actionScript = $appRoot . 'actions/' . $uri;
} else {
    die('File Not Found.');
}

if (! file_exists($actionScript)) {
    die('File Not found: ' . $actionScript);
}

require_once $actionScript;
