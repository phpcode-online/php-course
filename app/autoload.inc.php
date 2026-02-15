<?php
declare(strict_types=1);

function appLog($message, $file='log')
{
    $logPath = dirname(__DIR__) . '/logs/';
    if (!is_dir($logPath)) {
        mkdir($logPath, 0766, true);
    }

    $f = fopen($logPath . $file . '.txt', 'a');
    if ($f) {
        $ip = empty($_SERVER['REMOTE_ADDR']) ? '-' : $_SERVER['REMOTE_ADDR'];
        fwrite($f, '[' . date('Y-m-d H:i:s.u') . '], ' . $ip . ', ' . $message . "\n");
        fclose($f);
    } else {
        throw new Exception('Cannot open log file');
    }
}

/* выставляем уровень ошибок */
error_reporting(E_ALL);
/* выключаем вывод ошибок в стандартный вывод */
ini_set('display_errors', 0);

/* регистрируем обработчики исключений и ошибок */
set_error_handler('appError', E_ALL | E_USER_ERROR);
set_exception_handler('appException');

/**
 * Функция вешается как обработчик ошибок
 *
 * @param int $errno номер ошибки
 * @param string $errstr текст ошибки
 * @param string $errfile название файла в котором произошла ошибка
 *
 * @return bool
 */
function appError($errno, $errstr, $errfile = __FILE__, $errline = __LINE__, $errcontext = [])
{
    throw new \ErrorException($errstr, $errno, 1, $errfile, $errline);
}

/**
 * Функция вешается на исключения
 */
function appException(Throwable $e)
{
    $debugstr = $e->getTraceAsString();
    $err  = $e->getMessage() . "; file: " . $e->getFile() . "; line: " . $e->getLine() . "\r\ntrace: " . $debugstr . "\r\n";

    appLog($err, 'errors');

    die('Temprary error' . PHP_EOL);
}


spl_autoload_register('appAutoload');

/**
 * функция для автозагрузки классов
 * @param sting $className - название класса, который нужно загрузить
 **/
function appAutoload($className)
{
    $appRoot = __DIR__ . DIRECTORY_SEPARATOR;
    $classPath = $appRoot . 'classes' . DIRECTORY_SEPARATOR;

    if (defined('DEBUG')) { appLog('class path: ' . $classPath . ', className: ' . $className, 'debug'); }
    if (defined('DEBUG')) { appLog('class path with replace: ' . $classPath . str_replace('\\', '/', $className) . '.php', 'debug'); }

    /* Проверяем, есть ли класс в массиве для автозагрузки классов */
    if (file_exists($classPath . $className . '.php') ) {
        require_once $classPath . $className . '.php';
        return true;
    } elseif (strpos($className, '\\') !== false && file_exists($classPath . str_replace('\\', '/', $className) . '.php')) {
        require_once $classPath . str_replace('\\', '/', $className) . '.php';
        return true;
    } else {
        return;
    }
}
