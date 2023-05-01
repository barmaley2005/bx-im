<?
namespace DevBx\Core;

class Image
{

	/**
	 * @var array
	 */
	protected $images = array();

	/**
	 * @var int
	 */
	protected $width;

	/**
	 * @var int
	 */
	protected $height;

    /**
     * @var int
     */
    protected $method = BX_RESIZE_IMAGE_PROPORTIONAL;

    /**
     * @var bool|array
     */
    protected $watermark = false;

    /**
     * @var bool|array
     */
    protected $filters = false;
	/**
	 * @var array
	 */
	protected $errors = array();

	/**
	 * Slider constructor.
	 * @param array $arOptions
	 */
	public function __construct($arOptions)
	{
	    $this->setOptions($arOptions);
	}

	public function setOptions($arOptions)
    {
        if(isset($arOptions['width']))
            $this->width = $arOptions['width'];

        if(isset($arOptions['height']))
            $this->height = $arOptions['height'];

        $this->method = isset($arOptions['method']) ? $arOptions['method'] : BX_RESIZE_IMAGE_PROPORTIONAL;

        $this->watermark = isset($arOptions['watermark']) ? $arOptions['watermark'] : false;

        $this->filters = isset($arOptions['filters']) ? $arOptions['filters'] : false;

        return $this;
    }
	
	public function addImage($image)
	{
		if(!empty($image))
			$this->images[] = $image;
		else {
			$this->errors[] = 'Empty image';
		}
		
		return true;
	}
	
	public function addImagesList($arImages)
	{
		if(is_array($arImages)){
			foreach ($arImages as $image){
				$this->addImage($image);
			}
		} else {
			$this->errors[] = 'Empty slides list';
		}
		
		return true;
	}

	public function getFirstResized()
	{
		if(
			intval($this->width) > 0 && intval($this->height) > 0
		) {
			foreach ($this->images as $key => $img) {
				$result = \DevBx\Core\Files::universalResize(
				    $img,
                    array(
                        'width' => $this->width,
                        'height' => $this->height,
                        'method' => $this->method,
                        'waterMark' => $this->watermark,
                        'filters' => $this->filters,
                    )
                );
				if(is_array($result)){
					return $result;
				}
			}
		} else {
			$this->errors[] = 'Check sizes';
		}

		return false;
	}

	public function getAllResized($onlySrc = false)
	{
		if(
			intval($this->width) > 0 && intval($this->height) > 0
		) {
			$arResult = array();
			foreach ($this->images as $key => $img) {

				$result = \DevBx\Core\Files::universalResize(
					$img,
					array(
						'width' => $this->width,
						'height' => $this->height,
						'method' => $this->method,
                        'waterMark' => $this->watermark,
                        'filters' => $this->filters,
					)
				);
				if(is_array($result)){
				    if ($onlySrc)
                        $arResult[] = $result["SRC"]; else
					    $arResult[] = $result;
				}
			}

			return $arResult;
		} else {
			$this->errors[] = 'Check sizes';
		}

		return false;
	}

	public function getFirstOriginal()
	{

		foreach ($this->images as $key => $img) {
			$result = \DevBx\Core\Files::getOriginal($img);
			if(is_array($result)){
				return $result;
			}
		}

		return false;
	}

	public function getAllOriginal($onlySrc = false)
    {
        $arResult = array();

        foreach ($this->images as $key => $img) {
            $result = \DevBx\Core\Files::getOriginal($img);

            if(is_array($result)){

                if ($onlySrc)
                    $arResult[] = $result["SRC"]; else
                    $arResult[] = $result;
            }
        }

        return $arResult;

    }

    public static function getNoPhoto($width = 150, $height = 150)
    {
        static $cache = array();

        $width = intval($width);
        if ($width<=0)
            $width = 150;

        $height = intval($height);
        if ($height<=0)
            $height = 150;

        $cacheId = $width.'_'.$height;
        if (array_key_exists($cacheId, $cache))
            return $cache[$cacheId];

        if (!defined('SITE_TEMPLATE_PATH'))
            return false;

        if (!file_exists(SITE_TEMPLATE_PATH.'/img/no_photo.jpg'))
            return false;

        $image = new Image(array(
            'width' => $width,
            'height' => $height,
            'method' => BX_RESIZE_IMAGE_PROPORTIONAL,
        ));

        $image->addImage(SITE_TEMPLATE_PATH.'/img/no_photo.jpg');

        $cache[$cacheId] = $image->getFirstResized();
        return $cache[$cacheId];
    }

    public static function array_key_first($arr)
    {
        foreach($arr as $key => $unused) {
            return $key;
        }
        return NULL;
    }

    public static function resizeArray(&$arItems, $propName, $arParams)
    {
        if (func_num_args() > 3)
        {
            $width = func_get_arg(2);
            $height = func_get_arg(3);
            $method = func_get_arg(4);
            $noPhoto = func_get_arg(5);
            $bCallback = false;
        } else
        {
            if (!is_array($arParams))
                throw new \Bitrix\Main\ArgumentException('arParams must be array');

            $width = $arParams["width"];
            $height = $arParams["height"];
            $method = isset($arParams["method"]) ? $arParams["method"] : BX_RESIZE_IMAGE_PROPORTIONAL;
            $noPhoto = isset($arParams["nophoto"]) ? $arParams["nophoto"] : false;
            $bCallback = isset($arParams["callback"]);
            if ($bCallback && !is_callable($arParams["callback"]))
                throw new \Bitrix\Main\ArgumentException('callback not callable');
        }

        $width = intval($width);
        $height = intval($height);

        if ($width<=0)
            throw new \Bitrix\Main\ArgumentException('invalid width');

        if ($height<=0)
            throw new \Bitrix\Main\ArgumentException('invalid height');

        if ($noPhoto === true)
        {
            $noPhoto = static::getNoPhoto($width, $height);
        }

        foreach ($arItems as &$arItem)
        {
            if ($propName === false)
            {
                $imgProp = &$arItem;
            } else
            {
                $imgProp = &$arItem[$propName];
            }

            if (is_array($imgProp) && static::array_key_first($imgProp) === 0)
            {
                foreach ($imgProp as $key=>$val)
                {
                    $imgProp[$key] = \DevBx\Core\Files::resizeImage($val, $width, $height, $method);
                    if (!is_array($imgProp[$key]) || empty($imgProp[$key]))
                        $imgProp[$key] = $noPhoto;
                }
            } else {

                if (is_array($imgProp))
                {
                    if ($imgProp['ID']>0)
                    {
                        $imgProp = \DevBx\Core\Files::resizeImage($imgProp['ID'], $width, $height, $method);
                    }
                } else
                {
                    $imgProp = \DevBx\Core\Files::resizeImage($imgProp, $width, $height, $method);
                }

                if (!is_array($imgProp) || empty($imgProp))
                    $imgProp = $noPhoto;
            }
        }
    }

    public static function getFirstResizedFromArray($arItem, $arProp, $width, $height, $method = BX_RESIZE_IMAGE_PROPORTIONAL, $noPhoto = false)
    {
        $i = new static(array("width"=>$width, "height"=>$height, "method"=>$method));

        foreach ($arProp as $prop)
        {
            if (!empty($arItem[$prop]))
                $i->addImage($arItem[$prop]);
        }

        $res = $i->getFirstResized();

        if (is_array($res))
            return $res;

        if ($noPhoto === true)
        {
            return static::getNoPhoto($width, $height);
        }

        return false;
    }
}
