<?php

namespace Local\Lib\Yandex;

use Bitrix\Main\Config\Option;
use Bitrix\Main\Error;
use Bitrix\Main\Result;
use Bitrix\Main\Web\Json;

class Token {

    public static function getIAMToken()
    {
        static $IAMToken = false;

        $result = new Result();

        if ($IAMToken)
        {
            $result->setData(array('token'=>$IAMToken));
            return $result;
        }

        $OAuthToken = Option::get('local.lib', 'YANDEX_CLOUD_TOKEN');

        if (empty($OAuthToken))
        {
            return $result->addError(new Error('Token is empty'));
        }

        $http = new \Bitrix\Main\Web\HttpClient();
        $http->setVersion('1.1');

        $request = array(
            'yandexPassportOauthToken' => $OAuthToken
        );

        $response = $http->post('https://iam.api.cloud.yandex.net/iam/v1/tokens', Json::encode($request));

        try {
            $response = Json::decode($response);
        } catch (\Exception $e)
        {
            return $result->addError(new Error($e->getMessage()));
        }

        if ($http->getStatus() !== 200)
        {
            return $result->addError(new Error($response['message'],$response['code'], $response['details']));
        }

        $IAMToken = $response['iamToken'];

        $result->setData(array('token'=>$IAMToken));
        return $result;
    }

}