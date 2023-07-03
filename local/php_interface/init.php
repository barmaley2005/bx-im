<?
define('VUEJS_DEBUG', true);
define('SMS_AUTH_TEST', true);
define('SMS_AUTH_TEST_CODE', '7777');

include_once __DIR__.'/logger.php';

AddEventHandler("iblock", "OnAfterIBlockElementUpdate", "DoIBlockAfterSave");
AddEventHandler("iblock", "OnAfterIBlockElementAdd", "DoIBlockAfterSave");
AddEventHandler("catalog", "OnPriceAdd", "DoIBlockAfterSave");
AddEventHandler("catalog", "OnPriceUpdate", "DoIBlockAfterSave");
function DoIBlockAfterSave($arg1, $arg2 = false)
{
	$ELEMENT_ID = false;
	$IBLOCK_ID = false;
	$OFFERS_IBLOCK_ID = false;
	$OFFERS_PROPERTY_ID = false;
	if (CModule::IncludeModule('currency'))
		$strDefaultCurrency = CCurrency::GetBaseCurrency();
	
	//Check for catalog event
	if(is_array($arg2) && $arg2["PRODUCT_ID"] > 0)
	{
		//Get iblock element
		$rsPriceElement = CIBlockElement::GetList(
			array(),
			array(
				"ID" => $arg2["PRODUCT_ID"],
			),
			false,
			false,
			array("ID", "IBLOCK_ID")
		);
		if($arPriceElement = $rsPriceElement->Fetch())
		{
			$arCatalog = CCatalog::GetByID($arPriceElement["IBLOCK_ID"]);
			if(is_array($arCatalog))
			{
				//Check if it is offers iblock
				if($arCatalog["OFFERS"] == "Y")
				{
					//Find product element
					$rsElement = CIBlockElement::GetProperty(
						$arPriceElement["IBLOCK_ID"],
						$arPriceElement["ID"],
						"sort",
						"asc",
						array("ID" => $arCatalog["SKU_PROPERTY_ID"])
					);
					$arElement = $rsElement->Fetch();
					if($arElement && $arElement["VALUE"] > 0)
					{
						$ELEMENT_ID = $arElement["VALUE"];
						$IBLOCK_ID = $arCatalog["PRODUCT_IBLOCK_ID"];
						$OFFERS_IBLOCK_ID = $arCatalog["IBLOCK_ID"];
						$OFFERS_PROPERTY_ID = $arCatalog["SKU_PROPERTY_ID"];
					}
				}
				//or iblock which has offers
				elseif($arCatalog["OFFERS_IBLOCK_ID"] > 0)
				{
					$ELEMENT_ID = $arPriceElement["ID"];
					$IBLOCK_ID = $arPriceElement["IBLOCK_ID"];
					$OFFERS_IBLOCK_ID = $arCatalog["OFFERS_IBLOCK_ID"];
					$OFFERS_PROPERTY_ID = $arCatalog["OFFERS_PROPERTY_ID"];
				}
				//or it's regular catalog
				else
				{
					$ELEMENT_ID = $arPriceElement["ID"];
					$IBLOCK_ID = $arPriceElement["IBLOCK_ID"];
					$OFFERS_IBLOCK_ID = false;
					$OFFERS_PROPERTY_ID = false;
				}
			}
		}
	}
	//Check for iblock event
	elseif(is_array($arg1) && $arg1["ID"] > 0 && $arg1["IBLOCK_ID"] > 0)
	{
		//Check if iblock has offers
		$arOffers = CIBlockPriceTools::GetOffersIBlock($arg1["IBLOCK_ID"]);
		if(is_array($arOffers))
		{
			$ELEMENT_ID = $arg1["ID"];
			$IBLOCK_ID = $arg1["IBLOCK_ID"];
			$OFFERS_IBLOCK_ID = $arOffers["OFFERS_IBLOCK_ID"];
			$OFFERS_PROPERTY_ID = $arOffers["OFFERS_PROPERTY_ID"];
		}
	}

	if($ELEMENT_ID)
	{
		static $arPropCache = array();
		if(!array_key_exists($IBLOCK_ID, $arPropCache))
		{
			//Check for MINIMAL_PRICE property
			$rsProperty = CIBlockProperty::GetByID("MINIMUM_PRICE", $IBLOCK_ID);
			$arProperty = $rsProperty->Fetch();
			if($arProperty)
				$arPropCache[$IBLOCK_ID] = $arProperty["ID"];
			else
				$arPropCache[$IBLOCK_ID] = false;
		}

		if($arPropCache[$IBLOCK_ID])
		{
			//Compose elements filter
			if($OFFERS_IBLOCK_ID)
			{
				$rsOffers = CIBlockElement::GetList(
					array(),
					array(
						"IBLOCK_ID" => $OFFERS_IBLOCK_ID,
						"PROPERTY_".$OFFERS_PROPERTY_ID => $ELEMENT_ID,
					),
					false,
					false,
					array("ID")
				);
				while($arOffer = $rsOffers->Fetch())
					$arProductID[] = $arOffer["ID"];
					
				if (!is_array($arProductID))
					$arProductID = array($ELEMENT_ID);
			}
			else
				$arProductID = array($ELEMENT_ID);

			$minPrice = false;
			$maxPrice = false;
			//Get prices
			$rsPrices = CPrice::GetList(
				array(),
				array(
					"PRODUCT_ID" => $arProductID,
				)
			);
			while($arPrice = $rsPrices->Fetch())
			{
				if (CModule::IncludeModule('currency') && $strDefaultCurrency != $arPrice['CURRENCY'])
					$arPrice["PRICE"] = CCurrencyRates::ConvertCurrency($arPrice["PRICE"], $arPrice["CURRENCY"], $strDefaultCurrency);
				
				$PRICE = $arPrice["PRICE"];

				if($minPrice === false || $minPrice > $PRICE)
					$minPrice = $PRICE;

				if($maxPrice === false || $maxPrice < $PRICE)
					$maxPrice = $PRICE;
			}

			//Save found minimal price into property
			if($minPrice !== false)
			{
				CIBlockElement::SetPropertyValuesEx(
					$ELEMENT_ID,
					$IBLOCK_ID,
					array(
						"MINIMUM_PRICE" => $minPrice,
						"MAXIMUM_PRICE" => $maxPrice,
					)
				);
			}
		}
	}
}

if ($_GET["type"] == "catalog" && $_GET["mode"] == "import") {
    if ($_SESSION["BX_CML2_IMPORT"]["zip"]) {
        $saveFilePath = $_SERVER['DOCUMENT_ROOT'] . '/logs/1c-import/' . date('Y.m.d H-i') . ' ' . pathinfo($_SESSION["BX_CML2_IMPORT"]["zip"], PATHINFO_BASENAME);
        CheckDirPath($saveFilePath);
        copy($_SESSION["BX_CML2_IMPORT"]["zip"], $saveFilePath);
    } else {

        if ($_SESSION["BX_CML2_IMPORT"]["TEMP_DIR"] <> '')
            $DIR_NAME = $_SESSION["BX_CML2_IMPORT"]["TEMP_DIR"];
        else
            $DIR_NAME = $_SERVER["DOCUMENT_ROOT"] . "/" . COption::GetOptionString("main", "upload_dir", "upload") . "/1c_catalog/";

        if (
            isset($_GET["filename"])
            && ($_GET["filename"] <> '')
            && ($DIR_NAME <> '')
        ) {
            $filename = preg_replace("#^(/tmp/|upload/1c/webdata)#", "", $_GET["filename"]);
            $filename = trim(str_replace("\\", "/", trim($filename)), "/");

            $io = CBXVirtualIo::GetInstance();
            $bBadFile = HasScriptExtension($filename)
                || IsFileUnsafe($filename)
                || !$io->ValidatePathString("/" . $filename);

            if (!$bBadFile) {
                $FILE_NAME = rel2abs($DIR_NAME, "/" . $filename);
                if ((mb_strlen($FILE_NAME) > 1) && ($FILE_NAME === "/" . $filename)) {
                    $ABS_FILE_NAME = $DIR_NAME . $filename;
                    if (file_exists($ABS_FILE_NAME)) {
                        $saveFilePath = $_SERVER['DOCUMENT_ROOT'] . '/logs/1c-import/' . date('Y.m.d H-i') . ' ' . pathinfo($ABS_FILE_NAME, PATHINFO_BASENAME);
                        CheckDirPath($saveFilePath);
                        copy($ABS_FILE_NAME, $saveFilePath);
                    }
                }
            }

        }
    }

    AddEventHandler("main", "OnEndBufferContent", function($content) {

        DevBxLogger::getInstance('1c-import/%Y-%m-%d %H-%i-%s response.log')->logVar($content);

    });
}

if ($_GET["type"] == "sale") {
    if($_GET["mode"] == "file" && strlen($_SESSION["BX_CML2_EXPORT"]["version"]) <= 0)// old version
    {
        if (
            isset($_GET["filename"])
            && ($_GET["filename"] <> '')
        ) {
            $DATA = file_get_contents("php://input");

            $filename = preg_replace("#^(/tmp/|upload/1c/webdata)#", "", $_GET["filename"]);
            $filename = trim(str_replace("\\", "/", trim($filename)), "/");

            $saveFilePath = $_SERVER['DOCUMENT_ROOT'] . '/logs/1c-export/' . date('Y.m.d H-i') . ' ' . pathinfo($filename, PATHINFO_BASENAME);
            CheckDirPath($saveFilePath);

            file_put_contents($saveFilePath, $DATA);
        }
    } elseif($_GET["mode"] == "import" && $_SESSION["BX_CML2_EXPORT"]["zip"] && strlen($_SESSION["BX_CML2_EXPORT"]["zip"]) > 1)
    {
        $saveFilePath = $_SERVER['DOCUMENT_ROOT'] . '/logs/1c-export/' . date('Y.m.d H-i') . ' ' . pathinfo($_SESSION["BX_CML2_EXPORT"]["zip"], PATHINFO_BASENAME);
        CheckDirPath($saveFilePath);
        copy($_SESSION["BX_CML2_EXPORT"]["zip"], $saveFilePath);
    } elseif ($_GET["mode"] == "import")
    {
        if ($_SESSION["BX_CML2_EXPORT"]["zip"]) {
            $saveFilePath = $_SERVER['DOCUMENT_ROOT'] . '/logs/1c-export/' . date('Y.m.d H-i') . ' ' . pathinfo($_SESSION["BX_CML2_EXPORT"]["zip"], PATHINFO_BASENAME);
            CheckDirPath($saveFilePath);
            copy($_SESSION["BX_CML2_EXPORT"]["zip"], $saveFilePath);
        } else {

            if ($_SESSION["BX_CML2_EXPORT"]["TEMP_DIR"] <> '')
                $DIR_NAME = $_SESSION["BX_CML2_EXPORT"]["TEMP_DIR"];
            else
                $DIR_NAME = $_SERVER["DOCUMENT_ROOT"] . "/" . COption::GetOptionString("main", "upload_dir", "upload") . "/1c_catalog/";

            if (
                isset($_GET["filename"])
                && ($_GET["filename"] <> '')
                && ($DIR_NAME <> '')
            ) {
                $filename = preg_replace("#^(/tmp/|upload/1c/webdata)#", "", $_GET["filename"]);
                $filename = trim(str_replace("\\", "/", trim($filename)), "/");

                $io = CBXVirtualIo::GetInstance();
                $bBadFile = HasScriptExtension($filename)
                    || IsFileUnsafe($filename)
                    || !$io->ValidatePathString("/" . $filename);

                if (!$bBadFile) {
                    $FILE_NAME = rel2abs($DIR_NAME, "/" . $filename);
                    if ((mb_strlen($FILE_NAME) > 1) && ($FILE_NAME === "/" . $filename)) {
                        $ABS_FILE_NAME = $DIR_NAME . $filename;
                        if (file_exists($ABS_FILE_NAME)) {
                            $saveFilePath = $_SERVER['DOCUMENT_ROOT'] . '/logs/1c-export/' . date('Y.m.d H-i') . ' ' . pathinfo($ABS_FILE_NAME, PATHINFO_BASENAME);
                            CheckDirPath($saveFilePath);
                            copy($ABS_FILE_NAME, $saveFilePath);
                        }
                    }
                }

            }
        }
    }

    if($_GET["mode"] == "query" || $_POST["mode"] == "query")
    {
        AddEventHandler("main", "OnEndBufferContent", function($content) {

            DevBxLogger::getInstance('1c-export/%Y-%m-%d %H-%i-%s response.log')->logVar($content);

        });
    }
}

?>