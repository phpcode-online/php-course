<?php

/**
 * обработка ответа от пользователя
 * все результаты храним в отдельных файлах в специальном каталоге.
 * если каталога нет, то создадим его.
 */

declare(strict_types=1);

class ProcessAction extends BaseAction
{
    public $questions;
    public $fullPath;

    public function __construct()
    {
        parent::__construct();

        if (! $this->cookieId || empty($_COOKIE['quize'])) {
            $abc = [
                '1', '2', '3', '4', '5', '6', '7', '8', '9', '0',
                'Q', 'W', 'E', 'R', 'T', 'Y', 'U', 'O','I', 'P',
                'A', 'S', 'D', 'F', 'G', 'H', 'J', 'K', 'L', 'Z',
                'X', 'C', 'V', 'B', 'N', 'M'
            ];
            // перемешаем элементы массива
            shuffle($abc);
            // объединим все элементы массива
            $abc = implode('', $abc);
            // возьмем 20 первых символов
            $abc = substr($abc, 0, 20);
            // добавим получившуюся строку к текущему времени с миллисекундами
            $this->cookieId = 'quize' . microtime(true) . $abc;

            // отправим куки браузеру (куки будет хранится в браузере 30 дней)
            setcookie("quize", $this->cookieId, time() + (3600 * 24 * 30), "/", "", false, true);

            $_COOKIE['quize'] = $this->cookieId;
        }
    }

    public function run()
    {
        // если ответа нет, то перенаправим пользователя на страницу с формой
        if (empty($_POST['answers'])) {
            header('Location: /');
            die(0);
        }

        // подключим файл с массивом вопросов
        $this->questions = $this->quiz->getQuestions();

        $this->fullPath = dirname($this->appPath) . DIRECTORY_SEPARATOR . 'cache';

        try {
            // если есть ответы от пользователя,
            // то запишем их в БД
            if (! empty($_POST['answers']) && is_array($_POST['answers'])) {

                // проверим, вдруг уже пользователь голосовал и тогда нам нужно изменить его предыдущее голосование.
                foreach (UserAnswers::getAllUserAnswers($this->cookieId, $this->pdo) as $userAnswer) {
                    // если пользователь уже отвечал на вопрос, то удалим его ответ из массива
                    if (isset($_POST['answers'][$userAnswer->questionId])) {
                        // если пользователь изменил ответ, то обновим его и в БД
                        if ($userAnswer->variantId != $_POST['answers'][$userAnswer->questionId]) {
                            $userAnswer->variantId = $_POST['answers'][$userAnswer->questionId];
                            $userAnswer->save($this->pdo);
                        }
                        unset($_POST['answers'][$userAnswer->questionId]);
                    } else {
                        $userAnswer->delete($this->pdo);
                    }
                }


                foreach ($_POST['answers'] as $questionId => $variantId) {
                    // все данные, которые приходят через интернет, нужно проверять на валидность
                    // поэтому переведем номера вопросов и вариантов ответов в целое число
                    $questionId = intval($questionId);
                    $variantId = intval($variantId);

                    $userAnswer = new UserAnswers([
                        'id' => 0,
                        'userId' => $this->cookieId,
                        'questionId' => $questionId,
                        'variantId' => $variantId,
                        'created' => date('Y-m-d H:i:s')
                    ], $this->pdo);
                    $userAnswer->save($this->pdo);
                    $answerId = $userAnswer->id;
                }

                if (file_exists($this->fullPath . DIRECTORY_SEPARATOR . 'vote.cache')) {
                    // удалим кеш, чтобы при следующем просмотре результатов
                    // данные пересчитались заново
                    unlink($this->fullPath . DIRECTORY_SEPARATOR . 'vote.cache');
                }
            }
            header('Location: /result/');
        } catch (PDOException $e) {
            // сюда попадем если будет какая-то ошибка при работе с БД
            die('Шеф, с базой непонятки: ' . $e->getMessage() . '. File: ' . $e->getFile() . ' Line: ' . $e->getLine());
        } catch (Exception $e) {
            // сюда попадем если будет какая-то не предвиденная ошибка (исключение)
            die('Шеф, все пропало: ' . $e->getMessage() . '. File: ' . $e->getFile() . ' Line: ' . $e->getLine());
        }
    }
}
