<?php

class UserAnswers
{
    private $id;
    private $userId;
    private $questionId;
    private $variantId;
    private $created;

    // @param PDO $pdo - объект для работы с базой данных
    private static $pdo;

    public function __construct($params = [], $pdo = null)
    {
        if (isset($params['id'])) {
            $this->id = $params['id'];
        }

        if (isset($params['userId'])) {
            $this->userId = $params['userId'];
        }

        if (isset($params['questionId'])) {
            $this->questionId = $params['questionId'];
        }

        if (isset($params['variantId'])) {
            $this->variantId = $params['variantId'];
        }

        if (isset($params['created'])) {
            $this->created = $params['created'];
        }

        if ($pdo !== null) {
            self::setPdo($pdo);
        }
    }

    public static function setPdo(PDO $pdo)
    {
        self::$pdo = $pdo;
    }

    public static function getPdo()
    {
        return self::$pdo;
    }

    public function getQuestionId()
    {
        return $this->questionId;
    }

    public function setQuestionId($questionId)
    {
        $this->questionId = $questionId;
        return $this;
    }

    public function getVariantId()
    {
        return $this->variantId;
    }

    public function setVariantId($variantId)
    {
        $this->variantId = $variantId;
        return $this;
    }

    public function getId()
    {
        return $this->id;
    }

    public function setId($id)
    {
        $this->id = $id;
        return $this;
    }

    public function getUserId()
    {
        return $this->userId;
    }

    public function setUserId($userId)
    {
        $this->userId = $userId;
        return $this;
    }

    public function getCreated()
    {
        return $this->created;
    }

    public function setCreated($created = '')
    {
        $this->created = $created == '' ? date('Y-m-d H:i:s') : $created;
        return $this;
    }

    public static function getUserAnswers($userId, $questions, $pdo = null)
    {
        $userAnswers = [];
        try {
            $pdo = $pdo ?? self::getPdo();
            UserAnswers::setPdo($pdo);
            // запросим все данные по ответам пользователя
            $sql = "SELECT * FROM q_useranswers WHERE userId = :userId";
            $sth = $pdo->prepare($sql, [PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY]);
            $sth->setFetchMode(PDO::FETCH_CLASS | PDO::FETCH_PROPS_LATE, 'UserAnswers', [[], $pdo]);
            $result = $sth->execute(['userId' => $userId]);

            if ($result) {
                // пройдемся по всем полученным данным и заполним массив с ответами пользователя
                while (($userAnswer = $sth->fetch()) !== false) {
                    $questionId = $userAnswer->getQuestionId();
                    $answerId = $userAnswer->getVariantId();

                    // если вопроса с указанным кодом нет, то пропустим
                    if (
                        empty($questions[$questionId])
                        || empty($questions[$questionId]['variants'][$answerId])
                    ) {
                        continue;
                    }

                    $userAnswers[$questionId] = $answerId;
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
            $pdo = $pdo ?? self::getPdo();
            // запросим все данные по ответам пользователя
            $sql = "SELECT * FROM q_useranswers WHERE userId = :userId";
            $sth = $pdo->prepare($sql, [PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY]);
            $sth->setFetchMode(PDO::FETCH_CLASS | PDO::FETCH_PROPS_LATE, 'UserAnswers', [[], $pdo]);

            $result = $sth->execute(['userId' => $userId]);

            if ($result) {
                // пройдемся по всем полученным данным и заполним массив с ответами пользователя
                $userAnswers = $sth->fetchAll(PDO::FETCH_CLASS | PDO::FETCH_PROPS_LATE, 'UserAnswers', [[], $pdo]);
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
        $pdo = $pdo ?? self::getPdo();
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
        $pdo = $pdo ?? self::getPdo();
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
