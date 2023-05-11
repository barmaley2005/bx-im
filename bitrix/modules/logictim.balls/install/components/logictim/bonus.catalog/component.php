<? if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

if(CModule::IncludeModule('logictim.balls')):
	$arParams["RAND"] = rand();
	
	$ajaxParamComponent = $arParams["AJAX"];
	$ajaxParamModule = COption::GetOptionString("logictim.balls", "AJAX_IN_CATALOG", 'N');
	if($ajaxParamComponent == 'Y' || $ajaxParamModule == 'Y')
		$arParams["AJAX"] = 'Y';
	else
		$arParams["AJAX"] = 'N';
	
	$arItems = [];
	
	if(!empty($arParams["ITEMS"])):
		foreach($arParams["ITEMS"] as $arItem):
			$arOffers = array();
			if(!empty($arItem["OFFERS"]))
			{
				foreach($arItem["OFFERS"] as $arOffer):
					$arOffers[] = array("ID"=>$arOffer["ID"], "MIN_PRICE"=>$arOffer["MIN_PRICE"], "ITEM_PRICES"=>$arOffer["ITEM_PRICES"]);
				endforeach;
			}
			if(isset($arItem["ID"]))
				$arItems[] = array("ID"=>$arItem["ID"], "OFFERS"=>$arOffers, "MIN_PRICE"=>$arItem["MIN_PRICE"], "ITEM_PRICES"=>$arItem["ITEM_PRICES"]);
		endforeach;
	endif;

	$arResult["ITEMS"] = $arItems;
	$arResult["TEXT"]["TEXT_BONUS_FOR_ITEM"] = COption::GetOptionString("logictim.balls", "TEXT_BONUS_FOR_ITEM", '');
	
	if($arParams["AJAX"] != 'Y' && !empty($arItems)):
		$arResult["ITEMS_BONUS"] = cHelperCalc::CatalogBonus(array("ITEMS" => $arItems));
	endif;
	
	$this->IncludeComponentTemplate();

endif;
?>