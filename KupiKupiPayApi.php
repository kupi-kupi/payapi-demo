<?php
class KupiKupiPayApi
{
    private $api_url;
    private $platformUid;
    private $secretKey;

    public function __construct($platformUid, $secretKey)
    {
        $this->api_url = 'https://devpayapi.kupi-kupi.shop/api/v1/transfer/';
        $this->platformUid = $platformUid;
        $this->secretKey = $secretKey;
    }

    public function init($args)
    {
        return $this->buildQuery('Init', $args);
    }

    public function balance($args)
    {
        return $this->buildQuery('balance', $args);
    }

    public function checkout($args)
    {
        return $this->buildQuery('checkout', $args);
    }

    public function buildQuery($path, $args)
    {
        $url = $this->api_url;
        if (is_array($args)) {
            if (!array_key_exists('platformUid', $args)) {
                $args['platformUid'] = $this->platformUid;
            }
            if (!array_key_exists('token', $args)) {
                $args['token'] = $this->_genToken($args);
            }
        }
        $url = $this->_combineUrl($url, $path);

        //var_dump($args); exit();

        return $this->_sendRequest($url, $args);
    }

    private function _genToken($args)
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

    /**
     * Combines parts of URL. Simply gets all parameters and puts '/' between
     *
     * @return string
     */
    private function _combineUrl()
    {
        $args = func_get_args();
        $url = '';
        foreach ($args as $arg) {
            if (is_string($arg)) {
                if ($arg[strlen($arg) - 1] !== '/') {
                    $arg .= '/';
                }
                $url .= $arg;
            } else {
                continue;
            }
        }

        return $url;
    }

    private function _sendRequest($url, $data)
    {
        //https://weichie.com/blog/curl-api-calls-with-php/

        $dataString = json_encode($data);

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $dataString);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'Content-Length: ' . strlen($dataString)
        ]);

        $result = curl_exec($ch);
        var_dump($result); exit();
        $result = json_decode($result);
    }

    /**
     * Main method. Call API with params
     *
     * @param $api_url
     * @param $args
     * @return bool|string
     * @throws HttpException
     */
    private function _sendRequest1($api_url, $args)
    {
        if (is_array($args)) {
            $args = json_encode($args);
        }

        if ($curl = curl_init()) {
            curl_setopt($curl, CURLOPT_URL, $api_url);
            curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($curl, CURLOPT_POST, true);
            curl_setopt($curl, CURLOPT_POSTFIELDS, $args);
            curl_setopt($curl, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);

            $out = curl_exec($curl);

            $json = json_decode($out,true);

            var_dump($json); exit();

            if ($json) {
                if (@$json->ErrorCode !== "0") {
                    $this->error = @$json->Details;
                } else {
                    $this->paymentUrl = @$json->PaymentURL;
                    $this->paymentId = @$json->PaymentId;
                    $this->status = @$json->Status;
                }
            }

            curl_close($curl);

            return $out;
        } else {
            throw new HttpException('Can not create connection to ' . $api_url . ' with args ' . $args, 404);
        }
    }
}