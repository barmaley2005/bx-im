<?php

namespace Local\Lib;

class Image {

    public static function resizeImage($image, $resizeParams, $resizeType = BX_RESIZE_IMAGE_PROPORTIONAL, $filters = false)
    {
        if (!array_key_exists('FILE_NAME', $image) && array_key_exists('ID', $image))
        {
            $resizeImage = \CFile::ResizeImageGet(\CFile::GetFileArray($image['ID']), $resizeParams, $resizeType, true, $filters);
        } else {
            $resizeImage = \CFile::ResizeImageGet($image, $resizeParams, $resizeType, true, $filters);
        }

        if (!is_array($resizeImage))
            return $image;

        $result = array(
            'SRC' => $resizeImage['src'],
            'WIDTH' => $resizeImage['width'],
            'HEIGHT' => $resizeImage['height'],
            'FILE_SIZE' => $resizeImage['size'],
            'ORG_SRC' => $image['SRC'],
            'ORG_WIDTH' => $image['WIDTH'],
            'ORG_HEIGHT' => $image['HEIGHT'],
            'ORG_FILE_SIZE' => $image['FILE_SIZE'],
        );

        return array_merge($image, $result);
    }

    /**
     * Takes all arguments by pairs..
     * Odd arguments are arrays.
     * Even arguments are keys to lookup in these arrays.
     * Keys may be arrays. In this case function will try to dig deeper.
     * Returns first not empty element of a[k] pair.
     *
     * @param array $resizeParams \CFile::ResizeImageGet
     * @param array $a array to analyze
     * @param string|int $k key to lookup
     * @param mixed $a,... unlimited array/key pairs to go through
     * @return mixed|string
     */
    public static function resizeArray(array $resizeParams, array $params)
    {
        $result = [];

        $resizeType = $resizeParams['resizeType'] ?? BX_RESIZE_IMAGE_PROPORTIONAL;
        $filters = $resizeParams['filters'] ?? false;

        $argCount = count($params);

        for ($i = 0; $i < $argCount; $i += 2)
        {
            $anArray = &$params[$i];
            $key = $params[$i+1];

            if (is_array($anArray[$key]))
            {
                if (is_numeric(array_key_first($anArray[$key])))
                {
                    foreach ($anArray[$key] as $k=>$v)
                    {
                        if (!is_array($anArray[$key][$k]))
                        {
                            $anArray[$key][$k] = \CFile::GetFileArray($anArray[$key][$k]);;
                        }

                        if (is_array($anArray[$key][$k]))
                        {
                            $anArray[$key][$k] = static::resizeImage($anArray[$key][$k], $resizeParams, $resizeType, $filters);
                            $result[] = $anArray[$key][$k];
                        }
                    }

                } else {
                    $anArray[$key] = static::resizeImage($anArray[$key], $resizeParams, $resizeType, $filters);
                    $result[] = $anArray[$key];
                }
            } else {
                $anArray[$key] = \CFile::GetFileArray($anArray[$key]);

                if (is_array($anArray[$key]))
                {
                    $anArray[$key] = static::resizeImage($anArray[$key], $resizeParams, $resizeType, $filters);
                    $result[] = $anArray[$key];
                }
            }
        }

        return $result;
    }

}