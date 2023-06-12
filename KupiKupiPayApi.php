<?php

class KupiKupiPayApi
{
    private string $env = 'production'; // production|development - среда разработки
    private $api_url;
    private $platformUid;
    private $secretKey;
    private $errorCode;
    private $message;
    private $response;

    public function __construct(string $platformUid, string $secretKey)
    {
        // В зависимости от среды будет изменен URL-адрес отправки запросов
        $this->api_url     = 'https://' . ($this->env != 'production'?'dev':'') . 'payapi.kupi-kupi.shop/api/v1/transfer/';
        $this->platformUid = $platformUid;
        $this->secretKey   = $secretKey;
    }

    public function __get(string $name)
    {
        switch ($name) {
            case 'error':
                return $this->errorCode;
            case 'message':
                return $this->message;
            case 'response':
                return htmlentities($this->response);
            default:
                if ($this->response) {
                    if ($json = json_decode($this->response, true)) {
                        foreach ($json as $key => $value) {
                            if (strtolower($name) == strtolower($key)) {
                                return $json[$key];
                            }
                        }
                    }
                }

                return false;
        }
    }

    /**
     * Метод отвечает за отправку запроса на перевод баллов клиента
     * в счет оплаты товаров или услуг. Сайт продавца получает
     * ссылку на форму подтверждения перевода и
     * должен перенаправить по ней покупателя.
     *
     * @param array $args
     * @return mixed|null
     */
    public function init(array $args)
    {
        return $this->buildQuery('init', $args);
    }

    /**
     * Метод получения баланса пользоватея,
     * необходимо передать номер телефона пользователя
     * в цифровом формате (пример: 79000000000)
     *
     * @param int $phone
     * @return mixed|null
     */
    public function balance(int $phone)
    {
        return $this->buildQuery('balance', ['phone'=>$phone]);
    }

    /**
     * Метод возвращает статус заказа в кешбэк-сервисе «Купи-Купи»
     *
     * @param array $args
     * @return mixed|null
     */
    public function checkout(array $args)
    {
        return $this->buildQuery('checkout', $args);
    }

    /**
     * Метод подтверждения транзакции смс-кодом
     *
     * @param int $phone
     * @param int $code
     * @return mixed|null
     */
    public function confirmation(int $phone, int $code)
    {
        return $this->buildQuery('confirmation', ['phone' => $phone, 'code' => $code]);
    }

    /**
     * Метод для регистрации чека пользователя
     *
     * @param array $args
     * @return mixed|null
     */
    public function receipt(array $args)
    {
        return $this->buildQuery('receipt', $args);
    }

    public function buildQuery(string $path, array $args)
    {
        if (!array_key_exists('platformUid', $args)) {
            $args['platformUid'] = $this->platformUid;
        }
        if (!array_key_exists('token', $args)) {
            $args['token'] = $this->_genToken($args);
        }

        return $this->_sendRequest($path, $args);
    }

    private function _genToken(array $args)
    {
        $token = '';
        $args['secretKey'] = $this->secretKey;
        ksort($args);

        foreach ($args as $arg) {
            if (!is_array($arg)) {
                $token .= $arg;
            }
        }
        $token = hash('sha256', $token);

        return $token;
    }

    private function _sendRequest(string $url, array $data)
    {
        $dataString = json_encode($data);

        try {

            if ($curl = curl_init()) {

                curl_setopt($curl, CURLOPT_POST, 1);
                curl_setopt($curl, CURLOPT_POSTFIELDS, $dataString);
                curl_setopt($curl, CURLOPT_URL, $this->api_url . $url);
                curl_setopt($curl, CURLOPT_HTTPHEADER, [
                    'Content-Type: application/json',
                    'X-Requested-With: XMLHttpRequest',
                    'Content-Length: ' . strlen($dataString)
                ]);
                curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
                curl_setopt($curl, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);

                $result = curl_exec($curl);

                curl_close($curl);

                $this->response = $result;

                $json = json_decode($result,true);

                if ($json['errorCode'] ?? 0) {
                    $this->message = $json['message'];
                }

                return $json;
            } else {
                throw new HttpException('Can not create connection to ' . $url . ' with args ' . $dataString, 404);
            }

        } catch (Exception $e) {
            echo $e->getMessage(); die();
        }
    }
}