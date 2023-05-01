<?php

namespace DevBx\Core\ValueType;

class StringType extends BaseType {
    public static function getType() {
        return 'STRING';
    }

    public static function showValue($params)
    {
        $attrs = array(
            'type' => 'text',
            'name' => $params['VARIABLE_NAME'],
        );

        $value = $params['VALUE'];

        if (!empty($value))
            $attrs['value'] = $value;

        if ($params['SIZE']>0)
        {
            $attrs['size'] = $params['SIZE'];
        }

        $htmlStr = '<input';

        foreach ($attrs as $k=>$v)
        {
            $htmlStr .= ' '.$k.'="'.htmlspecialcharsbx($v).'"';
        }

        $htmlStr .= '>';

        echo $htmlStr;
    }

}