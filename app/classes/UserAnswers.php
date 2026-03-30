<?php

class UserAnswers
{
    /** @var int */
    public $id;
    /** @var int */
    public $userId;
    /** @var int */
    public $questionId;
    /** @var int */
    public $variantId;
    /** @var string */
    public $created;

    /** @var PDO $pdo - объект для работы с базой данных */
    public static $pdo;

    public function __construct(PDO $pdo = null)
    {
        if ($pdo !== null) {
            self::$pdo = $pdo;
        }
    }

    public function setCreated($created = '')
    {
        $this->created = $created == '' ? date('Y-m-d H:i:s') : $created;
        return $this;
    }

    public static function getUserAnswers($userId, $quiz, $pdo = null)
    {
        $userAnswers = [];
        $questions = $quiz->getQuestions();
        try {
            $pdo = $pdo ?? self::$pdo;
            // запросим все данные по ответам пользователя
            $sql = "SELECT * FROM q_useranswers WHERE userId = :userId";
            $sth = $pdo->prepare($sql, [PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY]);
            $sth->setFetchMode(PDO::FETCH_CLASS, 'UserAnswers', [$pdo]);
            $result = $sth->execute(['userId' => $userId]);

            if ($result) {
                // пройдемся по всем полученным данным и заполним массив с ответами пользователя
                while (($userAnswer = $sth->fetch()) !== false) {
                    $question = $quiz->getQuestion($userAnswer->questionId);

                    // если вопроса с указанным кодом нет, то пропустим
                    if (
                        !$question
                        || empty($question->variants[$userAnswer->variantId])
                    ) {
                        continue;
                    }

                    $userAnswers[$userAnswer->questionId] = $userAnswer; // $answerId;
                }
            }
        } catch (PDOException $e) {
            // сюда попадем если будет какая-то ошибка при работе с БД
            appLog('Database error: ' . $e->getMessage() . '. File: ' . $e->getFile() . ' Line: ' . $e->getLine(), 'error');
            die('Шеф, с базой непонятки');
        } catch (Exception $e) {
            // сюда попадем если будет какая-то не предвиденная ошибка (исключение)
            appLog('Unexpected error: ' . $e->getMessage() . '. File: ' . $e->getFile() . ' Line: ' . $e->getLine(), 'error');
            die('Шеф, все пропало.');
        }

        return $userAnswers;
    }

    public static function getAllUserAnswers($userId, $pdo = null)
    {
        $userAnswers = [];
        try {
            $pdo = $pdo ?? self::$pdo;
            // запросим все данные по ответам пользователя
            $sql = "SELECT * FROM q_useranswers WHERE userId = :userId";
            $sth = $pdo->prepare($sql, [PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY]);
            $sth->setFetchMode(PDO::FETCH_CLASS, 'UserAnswers', [$pdo]);

            $result = $sth->execute(['userId' => $userId]);

            if ($result) {
                // пройдемся по всем полученным данным и заполним массив с ответами пользователя
                $userAnswers = $sth->fetchAll(PDO::FETCH_CLASS, 'UserAnswers', [$pdo]);
            }
        } catch (PDOException $e) {
            // сюда попадем если будет какая-то ошибка при работе с БД
            appLog('Database error: ' . $e->getMessage() . '. File: ' . $e->getFile() . ' Line: ' . $e->getLine(), 'error');
            die('Шеф, с базой непонятки');
        } catch (Exception $e) {
            // сюда попадем если будет какая-то не предвиденная ошибка (исключение)
            appLog('Unexpected error: ' . $e->getMessage() . '. File: ' . $e->getFile() . ' Line: ' . $e->getLine(), 'error');
            die('Шеф, все пропало.');
        }

        return $userAnswers;
    }

    public function save($pdo = null)
    {
        $pdo = $pdo ?? self::$pdo;
        if ($this->id > 0) {
            // если id больше 0, то это значит, что ответ уже есть в БД и нам нужно его обновить
            $sql = "UPDATE q_useranswers SET variantId=:variantId WHERE id=:answerId AND userId=:userId";
            $sth = $pdo->prepare($sql);
            $result = $sth->execute([
                'userId' => $this->userId,
                'answerId' => $this->id,
                'variantId' => $this->variantId
            ]);
        } else {
            // если id равен 0, то это значит, что ответа еще нет в БД и нам нужно его добавить
            $sql = "INSERT INTO q_useranswers (userId, questionId, variantId) VALUES (:userId,:questionId,:variantId)";
            $sth = $pdo->prepare($sql);
            $result = $sth->execute([
                'userId' => $this->userId,
                'questionId' => $this->questionId,
                'variantId' => $this->variantId
            ]);
            if ($result) {
                $this->id = $pdo->lastInsertId();
            }
        }
        return $result;
    }

    public function delete($pdo = null)
    {
        $pdo = $pdo ?? self::$pdo;
        if ($this->id > 0) {
            $sql = "DELETE FROM q_useranswers WHERE id=:answerId AND userId=:userId";
            $sth = $pdo->prepare($sql);
            return $sth->execute([
                'userId' => $this->userId,
                'answerId' => $this->id
            ]);
        }
        return false;
    }
}
