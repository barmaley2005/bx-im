<?php

namespace Local\Lib\Internals;

use Bitrix\Main;
use Bitrix\Sale\Location\Name\LocationTable;
use Local\Lib\Api;

class DaDataApi
{
    public static function getHttpClient()
    {
        $http = new \Bitrix\Main\Web\HttpClient();

        $token = $config = \Local\Lib\Config::getInstance()->dadataToken;

        $http->setHeaders(array(
            'content-type' => 'application/json',
            'accept' => 'application/json',
            'Authorization' => 'Token ' . $token,
        ));

        return $http;
    }

    public static function query($method, $query)
    {
        $cache = new \CPHPCache();

        if ($cache->InitCache(60 * 60, 'dadata_' . md5(serialize(func_get_args())), "/local.lib")) {
            return $cache->GetVars();
        }

        $h = static::getHttpClient();

        $response = $h->post($method, Main\Web\Json::encode($query));

        $result = json_decode($response, true);
        if (!is_array($result))
            throw new Main\SystemException('json error: ' . json_last_error_msg());

        if (!isset($result['error'])) {
            if ($cache->StartDataCache()) {
                $cache->EndDataCache($result);
            }
        }

        return $result;
    }

    public static function suggest($query, $count = 20, $locationCode = false)
    {
        $arQuery = array(
            'count' => $count,
            /*'from_bound' => array(
                'value' => 'street',
            ),
            'to_bound' => array(
                'value' => 'house',
            ),*/
            'restrict_value' => true,
            'query' => $query
        );

        if ($locationCode) {
            Main\Loader::includeModule('sale');

            $arLocation = \Bitrix\Sale\Location\LocationTable::getList([
                'filter' => [
                    '=CODE' => $locationCode,
                    '=NAME.LANGUAGE_ID' => LANGUAGE_ID,
                ],
                'select' => [
                    'LOCATION_NAME' => 'NAME.NAME'
                ]
            ])->fetch();

            if ($arLocation) {
                $arQuery['locations'][] = array(
                    'city' => mb_strtolower($arLocation['LOCATION_NAME']),
                );
            }
        }

        $response = static::query('https://suggestions.dadata.ru/suggestions/api/4_1/rs/suggest/address', $arQuery);

        if (!is_array($response))
            return ['error' => 'Invalid response'];

        if (!isset($response['suggestions'])) {
            if (isset($response['message']))
                return ['error' => $response['message']];

            return ['error' => 'Unknown error'];
        }

        return $response;
    }

    public static function findCompany($query)
    {
        $result = static::query('https://suggestions.dadata.ru/suggestions/api/4_1/rs/findById/party', [
            'query' => $query,
            //'type' =>'LEGAL',
        ]);

        if (!isset($result['suggestions']) || empty($result['suggestions']) || !is_array($result['suggestions']))
            return ['error' => 'не найдено'];

        return ['success' => true, 'result' => reset($result['suggestions'])];
    }

    public static function findBank($query)
    {
        $result = static::query('https://suggestions.dadata.ru/suggestions/api/4_1/rs/findById/bank', [
            'query' => $query,
        ]);

        if (!isset($result['suggestions']) || empty($result['suggestions']) || !is_array($result['suggestions']))
            return ['error' => 'не найдено'];


        return ['success' => true, 'result' => reset($result['suggestions'])];
    }


    public static function registerApi(Api $api)
    {
        $api->registerApi('suggest', array(__CLASS__, 'suggest'));
        $api->registerApi('findCompany', array(__CLASS__, 'findCompany'));
        $api->registerApi('findBank', array(__CLASS__, 'findBank'));
    }
}