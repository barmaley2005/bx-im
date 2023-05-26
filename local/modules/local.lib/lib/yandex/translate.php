<?php

namespace Local\Lib\Yandex;

use Bitrix\Main\Config\Option;
use Bitrix\Main\Error;
use Bitrix\Main\Result;
use Bitrix\Main\Web\Json;

class Translate {

    const CACHE_PATH = '/yandex_cloud';
    const CACHE_TTL = 60*60*24;

    public static function translate($text, $format = 'PLAIN_TEXT', $sourceLangCode = 'ru', $targetLangCode = 'en')
    {
        $result = new Result();

        $cacheId = 'translate_'.serialize(func_get_args());
        $cachePath = static::CACHE_PATH;
        $cacheTtl = static::CACHE_TTL;
        $cache = false;

        if ($cacheTtl>0)
        {

            $cache = new \CPHPCache();
            if ($cache->InitCache($cacheTtl, $cacheId, $cachePath))
            {
                return $result->setData($cache->GetVars());
            }
        }

        $textIsArray = is_array($text);

        if (!$textIsArray)
            $text = array($text);

        $remoteResult = Token::getIAMToken();
        if (!$remoteResult->isSuccess()) {
            return $remoteResult;
        }

        $iamToken = $remoteResult->getData()['token'];

        $http = new \Bitrix\Main\Web\HttpClient();
        $http->setVersion('1.1');
        $http->setHeader('Authorization', 'Bearer '.$iamToken);

        $request = array(
            'sourceLanguageCode' => $sourceLangCode,
            'targetLanguageCode' => $targetLangCode,
            'format' => $format,
            'texts' => $text,
            'folderId' => Option::get('local.lib', 'YANDEX_CLOUD_FOLDER_ID'),
        );

        $response = $http->post('https://translate.api.cloud.yandex.net/translate/v2/translate', Json::encode($request));
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

        if (empty($response['translations']) || !is_array($response['translations']))
        {
            return $result->addError(new Error('Invalid response'));
        }

        $data = array();

        foreach ($response['translations'] as $ar)
        {
            if (!$textIsArray)
            {
                $data = $ar;
                break;
            }

            $data[] = $ar;
        }

        $result->setData($data);

        if ($cache)
        {
            $cache->StartDataCache($cacheTtl, $cacheId, $cachePath);
            $cache->EndDataCache($data);
        }

        return $result;
    }

}