<?php

require_once('KupiKupiPayApi.php');

$api = new KupiKupiPayApi(
    'M2hULcD5OiYGkmSK',  // UID платформы
    'yIKirCvplm0N1DOg'   // Ваш Secret_Key
);

// Метод получения баланса пользоватея
// var_dump($api->balance(79000000001));

// Метод отвечает за отправку запроса на перевод баллов
/*var_dump(
    $api->init(
        [
            'phone'   => 79000000000, // Телефон пользователя
            'orderId' => rand(1000, 9999), // ID-заказа на Вашем сайте
            'amount'  => rand(100, 10000) // Сумма заказа в копейках
        ]
    )
);*/

// Метод возвращает статус заказа в кешбэк-сервисе «Купи-Купи»
/*var_dump(
    $api->checkout(
        [
            'orderId' => 9921, // ID-заказа на Вашем сайте
            'amount'  => 972 // Сумма заказа в копейках
        ]
    )
);*/