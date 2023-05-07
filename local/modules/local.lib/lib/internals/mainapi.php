<?php

namespace Local\Lib\Internals;

use Bitrix\Main;
use Local\Lib\Api;

class MainApi {

    public static function registerForm(Api $context, Main\Type\ParameterDictionary $params)
    {
        global $APPLICATION;

        $APPLICATION->RestartBuffer();

        $APPLICATION->IncludeComponent("bitrix:main.register", "popup", array(
            "AUTH" => "Y",    // Автоматически авторизовать пользователей
            "REQUIRED_FIELDS" => "",    // Поля, обязательные для заполнения
            "SET_TITLE" => "N",    // Устанавливать заголовок страницы
            "SHOW_FIELDS" => array(    // Поля, которые показывать в форме
                0 => "EMAIL",
                1 => "NAME",
                2 => "LAST_NAME",
                3 => "PERSONAL_PHONE",
            ),
            "SUCCESS_PAGE" => "",    // Страница окончания регистрации
            "USER_PROPERTY" => "",    // Показывать доп. свойства
            "USER_PROPERTY_NAME" => "",    // Название блока пользовательских свойств
            "USE_BACKURL" => "N",    // Отправлять пользователя по обратной ссылке, если она есть
        ),
            false
        );

        die();
    }

    public static function authForm(Api $context, Main\Type\ParameterDictionary $params)
    {
        global $APPLICATION;

        $APPLICATION->RestartBuffer();

        $APPLICATION->IncludeComponent("bitrix:main.auth.form", "popup", array(
            "AUTH_FORGOT_PASSWORD_URL" => "",    // Страница для восстановления пароля
            "AUTH_REGISTER_URL" => "",    // Страница для регистрации
            "AUTH_SUCCESS_URL" => "",    // Страница после успешной авторизации
        ),
            false
        );

        die();
    }

    public static function registerApi(Api $api)
    {
        $api->registerApi('registerForm', array(__CLASS__, 'registerForm'));
        $api->registerApi('authForm', array(__CLASS__, 'authForm'));

    }
}