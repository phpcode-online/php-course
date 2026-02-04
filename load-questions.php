<?php

declare(strict_types=1);

$pdo = require_once 'pdo.inc.php';

$questions = [
    0 => [
        'question' => 'Программировать легко?',
        'variants' => [
            '1' => 'Да',
            '2' => 'Нет',
            '3' => 'Для кого как',
            '4' => 'Что это такое?',
        ]
    ],
    1 => [
        'question' => 'Кем работаете?',
        'variants' => [
            '1' => 'Маркетолог',
            '2' => 'Менеджер по продажам',
            '3' => 'Руководитель',
            '4' => 'Дизайнер',
            '5' => 'Актер',
            '6' => 'Сторож',
            '7' => 'Студент',
        ]
    ]
];

try {
    $quizeId = 1;
    // Так как опрос у нас один, то создадим его с кодом 1
    $sql = "INSERT INTO q_quizes (id, name) VALUES(:quizeid, :name)";
    $sth = $pdo->prepare($sql);
    $res = $sth->execute([
        'quizeid' => $quizeId,
        'name' => 'Опрос №1 о программировании'
    ]);
    if (! $res || $res->rowCount() == 0) {
        die("Опрос не был добавлен\n");
    }

    // пробежимся по всем вопросам и добавим их
    foreach ($questions as $questionId => $question) {
        // вставляем вопросы
        $sql = "INSERT INTO q_questions (id, quizeid, question) VALUES(:questionid, :quizeid, :question)";
        $sth = $pdo->prepare($sql);
        $res = $sth->execute([
            'quizeid' => $quizeId,
            'questionid' => $questionId,
            'question' => $question['question']
        ]);
        if (! $res || $res->rowCount() == 0) {
            die("Вопрос " . $question['question'] . " не был добавлен\n");
        }

        // пробежимся по всем вариантам и добавим их
        foreach ($question['variants'] as $variantId => $variant) {
            // вставляем варианты
            $sql = "INSERT INTO q_variants (id, quizeid, questionid, variant) VALUES(:quizeid, :questionid, :variant)";
            $sth = $pdo->prepare($sql);
            $res = $sth->execute([
                'quizeid' => $quizeId,
                'questionid' => $questionId,
                'variant' => $variant
            ]);

            if (! $res || $res->rowCount() == 0) {
                die("Вариант [" . $variant . "] для вопроса " . $question['question'] . " не был добавлен\n");
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
