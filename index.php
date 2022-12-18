<?php

require_once('KupiKupiPayApi.php');

$api = new KupiKupiPayApi(
    'M2hULcD5OiYGkmSK',  //UID платформы
    '9euSZoNaK8OlxvWP'   //Ваш Secret_Key
);

var_dump($api->balance(['phone'=>'79000000001']));