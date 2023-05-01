<?php

namespace DevBx\Core;

use Bitrix\Main;
use Bitrix\Main\Entity\DataManager;
use Bitrix\Main\Localization\Loc;

class Utils
{

    public static function numWord($value, $words, $show = true): string
    {
        $value = intval($value);
        $num = $value % 100;
        if ($num > 19) {
            $num = $num % 10;
        }

        $out = ($show) ? $value . ' ' : '';
        switch ($num) {
            case 1:
                $out .= $words[0];
                break;
            case 2:
            case 3:
            case 4:
                $out .= $words[1];
                break;
            default:
                $out .= $words[2];
                break;
        }

        return $out;
    }

    public static function formatIntervalDateTime(\Bitrix\Main\Type\DateTime $d)
    {
        $diffDate = clone $d;
        $diffDate->setTime(0,0,0);

        $diff = $diffDate->getDiff(new \Bitrix\Main\Type\DateTime());

        if ($diff->days == 0)
        {
            return $d->format(Loc::getMessage('DEVBX_CORE_UTILS_INTERVAL_TODAY'));
        }

        if ($diff->days == 1)
        {
            return $d->format(Loc::getMessage('DEVBX_CORE_UTILS_INTERVAL_YESTERDAY'));
        }

        if ($diff->days<7)
        {
            $day1 = Loc::getMessage('DEVBX_CORE_UTILS_INTERVAL_DAY_1');
            $day2_3 = Loc::getMessage('DEVBX_CORE_UTILS_INTERVAL_DAY_2_3');
            $day3_more = Loc::getMessage('DEVBX_CORE_UTILS_INTERVAL_DAY_3_MORE');

            return Loc::getMessage('DEVBX_CORE_UTILS_INTERVAL_DAYS_AGO', [
                '#DAYS#' => self::numWord($diff->days, [$day1, $day2_3, $day3_more]),
                '#TIME#' => $d->format('H:i'),
            ]);
        }

        return $d->toString();
    }

    public static function templateArrayReplace($ar, $template)
    {
        $search = [];
        $replace = [];

        foreach ($ar as $k=>$v)
        {
            $search[] = '#'.$k.'#';
            $replace[] = $v;
        }

        return str_replace($search, $replace, $template);
    }

    public static function prepareArrayForJS($value)
    {
        if (!is_array($value)) {

            if (is_object($value)) {

                if ($value instanceof \Bitrix\Main\Type\Date)
                {
                    return $value->getTimestamp()*1000;
                }

                return (string)$value;
            }

            return $value;
        }

        $result = [];

        foreach ($value as $k=>$v)
        {
            $result[$k] = static::prepareArrayForJS($v);
        }

        return $result;
    }
}
