<?php

declare(strict_types=1);

function getQuestions($pdo, $quizeId)
{
    $questions = [];

    try {
        // запросим все данные по вопросам  пользователя
        $sql = "SELECT * FROM q_questions WHERE quizeid = :quizeid";
        $sth = $pdo->prepare($sql, [PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY]);
        $result = $sth->execute(['quizeid' => $quizeId]);

        if ($result) {
            // пройдемся по всем полученным данным и заполним массив с ответами пользователя
            while (($row = $sth->fetch(PDO::FETCH_ASSOC)) !== false) {
                $questionId = $row['id'];
                $questions[$questionId] = [
                    'question' => $row['question'],
                    'variants' => []
                ];
            }
        }


        // запросим все данные по вариантам ответов пользователя
        $sql = "SELECT id, quizeid, questionid, variant FROM q_variants WHERE quizeid = :quizeid";
        $sth2 = $pdo->prepare($sql, [PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY]);
        $result2 = $sth2->execute(['quizeid' => $quizeId]);
        if ($result2) {
            // пройдемся по всем полученным данным и заполним массив с ответами пользователя
            while (($row = $sth2->fetch(PDO::FETCH_ASSOC)) !== false) {
                $questionId = $row['questionid'];
                $variantId = $row['id'];
                if (isset($questions[$questionId])) {
                    $questions[$questionId]['variants'][$variantId] = $row['variant'];
                }
            }
        }
    } catch (PDOException $e) {
        // сюда попадем если будет какая-то ошибка при работе с БД
        die('Шеф, с базой непонятки: ' . $e->getMessage() . '. File: ' . $e->getFile() . ' Line: ' . $e->getLine());
    } catch (Exception $e) {
        // сюда попадем если будет какая-то не предвиденная ошибка (исключение)
        die('Шеф, все пропало: ' . $e->getMessage() . '. File: ' . $e->getFile() . ' Line: ' . $e->getLine());
    }

    return $questions;
}

return getQuestions($pdo, 1);
