<?php

declare(strict_types=1);

function getQuestions($pdo, $quizeId)
{
    $questions = [];

    try {
        // запросим все данные по вопросам  пользователя
        $sql = "SELECT * FROM q_questions WHERE quizeId = :quizeId";
        $sth = $pdo->prepare($sql, [PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY]);
        $result = $sth->execute(['quizeId' => $quizeId]);

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
        $sql = "SELECT id, quizeId, questionId, variant FROM q_variants WHERE quizeId = :quizeId";
        $sth2 = $pdo->prepare($sql, [PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY]);
        $result2 = $sth2->execute(['quizeId' => $quizeId]);
        if ($result2) {
            // пройдемся по всем полученным данным и заполним массив с ответами пользователя
            while (($row = $sth2->fetch(PDO::FETCH_ASSOC)) !== false) {
                $questionId = $row['questionId'];
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
