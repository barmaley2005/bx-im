<?

namespace DevBx\Core;

class Files
{
    /**
     * @var \CFile
     */
    static $CFile;

    /**
     * Возвращает массив картинки ужатой до указанных размеров
     * @param int|array $image
     * @param int $width
     * @param int $height
     * @param int $method
     * @param array $addParams
     * @return bool|array
     */
    public static function resizeImage($image, $width, $height, $method = BX_RESIZE_IMAGE_PROPORTIONAL, $addParams = array())
    {
        $arImage = false;

        if (is_array($image) || intval($image) > 0) {

            if (!is_array($image) && intval($image) > 0)
            {
                if (!$image = \CFile::GetFileArray($image))
                    return false;
            }


            $arImage = \CFile::ResizeImageGet(
                $image,
                array('width' => $width, 'height' => $height),
                $method,
                true
            );

            $arImage = array_merge($image, array_change_key_case($arImage, CASE_UPPER));
            foreach ($image as $key=>$val)
            {
                if (array_key_exists($key, $arImage))
                    $arImage["ORG_".$key] = $val;
            }

            if (is_array($addParams) && count($addParams) > 0) {
                foreach ($addParams as $code => $value) {
                    if (!isset($arImage[$code])) {
                        $arImage[$code] = $value;
                    }
                }
            }
        }

        return $arImage;
    }

    public static function getOriginal($image)
    {
        $arImage = false;

        if (is_array($image)) {
            $arImage = $image;
            if (!empty($arImage['UNSAFE_SRC']))
                $arImage['src'] = $arImage['UNSAFE_SRC'];
            elseif (!empty($arImage['SRC']))
                $arImage['src'] = $arImage['SRC'];

        } elseif (intval($image) > 0) {
            $arImage = \CFile::GetFileArray($image);
            if (!empty($arImage['SRC']))
                $arImage['src'] = $arImage['SRC'];
        } else {
            if (file_exists($_SERVER['DOCUMENT_ROOT'] . $image)) {
                $arImage = array(
                    'src' => $image,
                    'SRC' => $image,
                );
            }
        }

        return $arImage;
    }

    /**
     * @param $image
     * @return bool|mixed
     */
    public static function getPathForImage($image)
    {
        $sourceFile = false;
        switch (gettype($image)) {
            case 'integer':
                $image = self::$CFile->GetFileArray($image);
                if (is_array($image)) {
                    $sourceFile = $image['SRC'];
                }
                break;

            case 'string':
                if (is_file($image)) {
                    $sourceFile = $image;
                } elseif (is_file($_SERVER['DOCUMENT_ROOT'] . $image)) {
                    $sourceFile = $_SERVER['DOCUMENT_ROOT'] . $image;
                }
                break;

            case 'array':
                if (isset($image['SRC'])) {
                    if (is_file($image['SRC'])) {
                        $sourceFile = $image['SRC'];
                    } elseif (is_file($_SERVER['DOCUMENT_ROOT'] . $image['SRC'])) {
                        $sourceFile = $_SERVER['DOCUMENT_ROOT'] . $image['SRC'];
                    }
                }
                break;
        }

        if (is_file($sourceFile)) {
            return str_replace($_SERVER['DOCUMENT_ROOT'], '', $sourceFile);
        }

        return false;
    }

    public static function getPathForImages($arImages)
    {
        $arResult = array();
        foreach ($arImages as $image) {
            if (is_array($image)) {
                $arResult = array_unique(array_merge($arResult, static::getPathForImages($image)));
            } else {
                if ($res = static::getPathForImage($image))
                    $arResult[] = $res;
            }
        }

        return $arResult;
    }

    /**
     * @param int|string|array $image
     * @param array $params
     * @return array|bool
     */
    public static function universalResize($image, $params)
    {
        $arImage = array();

        $paramsValid = self::checkParams($params);
        if (!$paramsValid) {
            return $arImage;
        }

        if (empty(self::$CFile)) {
            self::$CFile = new \CFile();
        }

        if (!$arOrg = static::getOriginal($image))
            return false;

        $sourceFile = $_SERVER['DOCUMENT_ROOT'] . $arOrg['src'];
        $arImage["ORG_SRC"] = $arOrg['src'];

        if (is_file($sourceFile)) {
            $destinationFile = self::makeDestPath($sourceFile, $params);

            if (is_file($destinationFile)) {
                $arImage['SRC'] = $destinationFile;
                $arImage['src'] = $destinationFile;
            } else {
                self::$CFile->ResizeImageFile(
                    $sourceFile,
                    $destinationFile,
                    $arSize = array(
                        'width' => $params['width'],
                        'height' => $params['height']
                    ),
                    isset($params['method']) ? $params['method'] : BX_RESIZE_IMAGE_PROPORTIONAL,
                    isset($params['waterMark']) ? $params['waterMark'] : array(),
                    isset($params['jpgQuality']) ? $params['jpgQuality'] : false,
                    isset($params['filters']) ? $params['filters'] : false
                );

                $arImage['SRC'] = $destinationFile;
                $arImage['src'] = $destinationFile;
            }
        }

        if (!empty($arImage)) {
            if (isset($params['add']) && is_array($params['add'])) {
                $arImage = array_merge($arImage, $params['add']);
            }

            $arImage['SRC'] = str_replace($_SERVER['DOCUMENT_ROOT'], '', $arImage['SRC']);
            $arImage['src'] = str_replace($_SERVER['DOCUMENT_ROOT'], '', $arImage['SRC']);
            $arImage['ORIGINAL'] = str_replace($_SERVER['DOCUMENT_ROOT'], '', $sourceFile);
        } else {
            $arImage = false;
        }

        return $arImage;
    }

    /**
     * @param string $sourceFile
     * @param array $params
     * @return string
     */
    protected static function makeDestPath($sourceFile, $params)
    {
        $destFolder = $_SERVER['DOCUMENT_ROOT'] . '/upload/resize_cache/custom';
        if (isset($params['folder'])) {
            $destFolder = $params['folder'];
        }

        if (isset($params['waterMark']))
        {
            $destFolder .= '/'.md5(serialize($params['waterMark']));
        }

        if (isset($params['filename'])) {
            $fileName = $params['width'] . '_' . $params['height'] . '_' . $params['filename'];
        } else {
            $path = pathinfo($sourceFile);
            $fileName = $params['width'] . '_' . $params['height'] . '_' . $path['basename'];
        }

        if (isset($params['method'])) {
            $fileName = $params['method'] . '_' . $fileName;
        }

        $fileName = str_replace(' ', '_', $fileName);

        return $destFolder . '/' . $fileName;
    }

    /**
     * @param array $params
     * @return bool
     */
    protected static function checkParams(&$params)
    {
        $paramsValid = true;

        if ($paramsValid && !isset($params['width'])) {
            $paramsValid = false;

            $params['width'] = intval($params['width']);
            if ($params['width'] < 1)
                $paramsValid = false;
        }

        if ($paramsValid && !isset($params['height'])) {
            $paramsValid = false;

            $params['height'] = intval($params['height']);
            if ($params['height'] < 1)
                $paramsValid = false;
        }

        return $paramsValid;
    }

}