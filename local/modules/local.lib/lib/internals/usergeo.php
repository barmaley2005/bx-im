<?php

namespace Local\Lib\Internals;

use Bitrix\Main;
use DevBx\Core\Assert;
use Local\Lib\Api;
use Local\Lib\DB\UserGeoTable;

class UserGeo
{
    const SESSION_KEY = 'DEV_BX_USER_GEO';

    public static function addUserGeoStat(array $data)
    {
        global $USER;

        $statId = Main\Context::getCurrent()->getRequest()->getCookie('DEVBX_USER_GEO_ID');

        if (!empty($statId))
        {
            if (!UserGeoTable::getList([
                'filter' => [
                    '=USER_STAT_ID' => $statId
                ],
                'limit' => 1,
            ])->fetch())
            {
                $statId = false;
            }
        }

        if (empty($statId))
        {
            while (true)
            {
                $statId = md5(uniqid());

                if (!UserGeoTable::getList([
                    'filter' => [
                        '=USER_STAT_ID' => $statId
                    ],
                    'limit' => 1,
                ])->fetch())
                {
                    break;
                }
            }
        }

        $arFields = array(
            'USER_STAT_ID' => $statId,
            'ACCURACY' => $data['ACCURACY'],
            'LATITUDE' => $data['POSITION'][0],
            'LONGITUDE' => $data['POSITION'][1],
            'COUNTRY' => $data['COUNTRY'],
            'COUNTRY_CODE' => $data['COUNTRY_CODE'],
            'ADDRESS' => $data['ADDRESS'],
        );

        if (is_object($USER) && $USER->IsAuthorized())
        {
            $arFields['USER_ID'] = $USER->GetID();
        }

        $dbResult = UserGeoTable::add($arFields);

        if (!$dbResult->isSuccess())
            return $dbResult;

        $session = Main\Application::getInstance()->getKernelSession();

        $session[static::SESSION_KEY] = $arFields;

        $cookie = new Main\Web\Cookie('DEVBX_USER_GEO_ID', $statId);

        Main\Context::getCurrent()->getResponse()->addCookie($cookie);

        $result = new Main\Result();

        return $result;
    }

    public static function getUserData()
    {
        global $USER;

        $session = Main\Application::getInstance()->getKernelSession();

        if (isset($session[static::SESSION_KEY]))
        {
            return $session[static::SESSION_KEY];
        }

        $statId = Main\Context::getCurrent()->getRequest()->getCookie('DEVBX_USER_GEO_ID');

        $arFilter = array();

        if (!empty($statId))
        {
            $arFilter['=USER_STAT_ID'] = $statId;
        } else {
            if (!is_object($USER) || !$USER->IsAuthorized())
                return false;

            $arFilter['=USER_ID'] = $USER->GetID();
        }

        $arGeo = UserGeoTable::getList([
            'filter' => $arFilter,
            'order' => ['INSERT_DATE'=>'DESC'],
        ])->fetch();

        return $arGeo;
    }

    public static function OnEndBufferContent(&$content)
    {
        if (defined('ADMIN_SECTION') && ADMIN_SECTION)
            return;

        $request = \Bitrix\Main\Context::getCurrent()->getRequest();

        if ($request->isPost())
            return;

        $response = \Bitrix\Main\Context::getCurrent()->getResponse();

        if ($response->getHeaders()->getContentType() == 'application/json')
            return;

        $session = Main\Application::getInstance()->getKernelSession();

        if (isset($session[static::SESSION_KEY]))
            return;

        if (Main\Config\Option::get('local.lib', 'GEO_LOCATION', 'N') != 'Y')
            return;

        $arSite = unserialize(\Bitrix\Main\Config\Option::get('local.lib','GEO_SITE'), ['allowed_classes'=>false]);

        if (!is_array($arSite) || !in_array(SITE_ID, $arSite))
            return;

        if (Main\Config\Option::get('local.lib', 'INCLUDE_YANDEX_MAPS', 'N') == 'Y')
        {
            $apiKey = \Bitrix\Main\Config\Option::get('fileman', 'yandex_map_api_key');
            if ($apiKey)
            {
                $scheme = $request->isHttps() ? 'https' : 'http';
                $content .= '<script src="'.$scheme.'://api-maps.yandex.ru/2.1.50/?apikey='.$apiKey.'&load=package.full&lang=ru-RU"></script>';
            }
        }

        $apiUrl = Api::getApiUrl();

        $content .= <<<JS
<script>
    BX.ready(function() {

    function waitYmaps()
    {
        if (typeof ymaps === 'undefined')
            {
                setTimeout(waitYmaps, 1000);
                return;
            }
        
ymaps.ready(function() {
	ymaps.geolocation.get().then(function(result) {
        
        let userGeo = {
                accuracy: result.geoObjects.accuracy,
                position: result.geoObjects.position,
                country: result.geoObjects.get(0).getCountry(),
                countryCode: result.geoObjects.get(0).getCountryCode(),
                address: result.geoObjects.get(0).getAddressLine()  
        };
        
        BX.ajax({
            url: '$apiUrl',
            method: 'POST',
            data: Object.assign({
                sessid: BX.bitrix_sessid(),
                method: 'usergeo/saveUserData',
                userAgent: navigator.userAgent                
            }, userGeo)
        });
        
        BX.onCustomEvent("onUserGeo", [userGeo]);
	}).fail(function(result) {
        console.log('fail', result);        
	});
});
    };
    
    waitYmaps();
    });
</script>
JS;

    }

    public static function saveUserData(Api $context, Main\Type\ParameterDictionary $params)
    {
        $result = new Main\Result();

        $accuracy = Assert::expectTrimStringNotNull($params['accuracy'], 'accuracy');
        $position = Assert::expectNotEmptyArray($params['position'], 'position');
        $country = Assert::expectTrimStringNotNull($params['country'], 'country');
        $countryCode = Assert::expectTrimStringNotNull($params['countryCode'], 'countryCode');
        $address = Assert::expectTrimStringNotNull($params['address'], 'address');

        static::addUserGeoStat(array(
            'ACCURACY' => $accuracy,
            'POSITION' => $position,
            'COUNTRY' => $country,
            'COUNTRY_CODE' => $countryCode,
            'ADDRESS' => $address,
        ));

        return $result;
    }

    public static function registerApi(Api $api)
    {
        $api->registerApi('usergeo/saveUserData', array(__CLASS__, 'saveUserData'));
    }

}