<?php

namespace DevBx\Forms\Internals;

use Bitrix\Main;

class Utils
{
    public static function arrayToJSCamel($data): array
    {
        $result = [];

        foreach ($data as $k => $v) {
            $k = lcfirst(Main\Text\StringHelper::snake2camel($k));

            if (isset($result[$k]))
                throw new Main\SystemException('camel key already exists "' . $k . '"');

            if (is_array($v)) {
                $v = static::arrayToJSCamel($v);
            }

            if ($v instanceof Main\Type\Date) {
                $v = $v->getTimestamp() * 1000;
            }

            if (is_object($v)) {
                if (is_callable(array($v, 'toArray'))) {
                    try {
                        $v = call_user_func(array($v, 'toArray'));
                        if (!is_array($v)) {
                            $v = 'invalid function result toArray';
                        }
                    } catch (\Exception $e) {
                        $v = 'Exception: ' . $e->getMessage();
                    }

                    $v = static::arrayToJSCamel($v);
                } elseif (is_callable(array($v, 'toString'))) {
                    try {
                        $v = call_user_func(array($v, 'toString'));
                    } catch (\Exception $e) {
                        $v = 'Exception: ' . $e->getMessage();
                    }
                } else {
                    $v = 'object (' . get_class($v) . ')';
                }
            }

            $result[$k] = $v;
        }

        return $result;
    }

    public static function sendJsonAnswer($data)
    {
        $response = Main\Context::getCurrent()->getResponse();
        $response->addHeader("Content-Type", "application/json; charset=UTF-8");

        $response->flush(Main\Web\Json::encode($data));
    }
}