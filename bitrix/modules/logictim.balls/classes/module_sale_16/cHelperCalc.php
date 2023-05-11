<?
class cHelperCalc
{
	public static function GetCategoriesTree($bonusCatProp = 'UF_LOGICTIM_BONUS')
	{
		CModule::IncludeModule('iblock');
		CModule::IncludeModule("catalog");
		
		//take id of catalogs, and offers
		$dbCatalogs = CIBlock::GetList(
			Array(), 
			Array(
				'ACTIVE'=>'Y', 
			), false
		);
		//take only torgoviy catalog
		while($arCatalog = $dbCatalogs->Fetch())
		{
			$catDb = CCatalog::GetByID($arCatalog["ID"]);
			if($catDb) {
				$catalogsId[] = $catDb["ID"];
				}
		}
		
		$category_tree = array();
		$category_list = array();
		$arrCat = Array();
		
		foreach($catalogsId as $iblock_id):
			$res = CIBlockSection::GetList(Array(),Array('IBLOCK_ID'=>$iblock_id,'ACTIVE'=>'Y'),false,Array('ID','NAME','CODE','DEPTH_LEVEL','IBLOCK_SECTION_ID',$bonusCatProp,'UF_LOGICTIM_BONUS_NO', 'UF_LOGICTIM_BONUS_NP'));
			while ($arr = $res->fetch())
			{
				$arrCat[$arr['ID']] = $arr;
			}
		endforeach;
		$category_list = $arrCat;
		
		//Build tree categories
		
		foreach($arrCat as $id=>&$node):
			   if($node['IBLOCK_SECTION_ID'] == '') 
			   { // root node
				   $category_tree[$id] = &$node;
			   } 
			   else 
			   { // sub node
				   if(!isset($arrCat[$node['IBLOCK_SECTION_ID']]['CHILD']))
				   {
					   $arrCat[$node['IBLOCK_SECTION_ID']]['CHILD'] = array();
				   }
				   if($node[$bonusCatProp] == '')
				   {
					  $node[$bonusCatProp] =  $arrCat[$node['IBLOCK_SECTION_ID']][$bonusCatProp];
					  $category_list[$id][$bonusCatProp] = $arrCat[$node['IBLOCK_SECTION_ID']][$bonusCatProp];
				   }
				   if($node['UF_LOGICTIM_BONUS_NO'] == '' || $node['UF_LOGICTIM_BONUS_NO'] == 0)
				   {
					  $node['UF_LOGICTIM_BONUS_NO'] =  $arrCat[$node['IBLOCK_SECTION_ID']]["UF_LOGICTIM_BONUS_NO"];
					  $category_list[$id]['UF_LOGICTIM_BONUS_NO'] = $arrCat[$node['IBLOCK_SECTION_ID']]["UF_LOGICTIM_BONUS_NO"];
				   }
				   if($node['UF_LOGICTIM_BONUS_NP'] == '' || $node['UF_LOGICTIM_BONUS_NP'] == 0)
				   {
					  $node['UF_LOGICTIM_BONUS_NP'] =  $arrCat[$node['IBLOCK_SECTION_ID']]["UF_LOGICTIM_BONUS_NP"];
					  $category_list[$id]['UF_LOGICTIM_BONUS_NP'] = $arrCat[$node['IBLOCK_SECTION_ID']]["UF_LOGICTIM_BONUS_NP"];
				   }
				   $arrCat[$node['IBLOCK_SECTION_ID']]['CHILD'][$id] = &$node;
			   }
		endforeach;
		
		//echo '<PRE>';print_r($category_tree);echo '</PRE>';
		//echo '<PRE>';print_r($category_list);echo '</PRE>';
		
		$arCategoriesTree = array("CATEGORY_TREE" => $category_tree, "CATEGORY_LIST" => $category_list);
		
		return $arCategoriesTree;
		
	}
	
	public static function OrderBonus($basketItems=0, $order_sum=0, $cart_sum=0, $delivery_sum=0, $pay_bonus=0, $order_id=0, $params = array())
	{
		$arBonus = array();
		$round = (int)COption::GetOptionString("logictim.balls", "BONUS_ROUND", 2);
		$bonusMetod = (int)COption::GetOptionString("logictim.balls", "BONUS_METOD", 5); //Sposob nachisleniya bonusov
		
		$arBonus = cHelperCalc::CartBonus($basketItems, $order_id, $params);
		
		//-----------Nachislenie bonusov po metodu 5 (bonusu na tovari)--------------//
		if($bonusMetod == 5):
			
			//Formiruem commentariy dlya zapisi v operaciyu nachisleniya
			if($arBonus["ALL_BONUS"] > 0):
				$bonusType = (int)COption::GetOptionString("logictim.balls", "BONUS_FOR_PRODUCT_TYPE", 1);
				$detailInfo = '';
				foreach($arBonus["ITEMS"] as $item):
					if($bonusType == 1 && $item["BONUS"] > 0)
					$detailInfo .= 'product_id='.$item["ID"].' '.$item["NAME"].' bonus = '.$item["BONUS"].'% x '.$item["PRICE"]["DISCOUNT_PRICE"].' x '.$item["QUANTITY"].' = '.$item["ADD_BONUS"]."\n";
					if($bonusType == 2 && $item["BONUS"] > 0)
					$detailInfo .= 'product_id='.$item["ID"].' '.$item["NAME"].' bonus = '.$item["BONUS"].' x '.$item["QUANTITY"].' = '.$item["ADD_BONUS"]."\n";
				endforeach;
			endif;
		
		//-----------Nachislenie bonusov po metodu 5 (bonusu na tovari)--------------//
		
		
		
		//-----------Nachislenie bonusov po metodu 6 (bonusu na zakaz)--------------//
		elseif($bonusMetod == 6):
			$bonusProc = (float)COption::GetOptionString("logictim.balls", "BONUS_PROC", 10);
			
			//Korrektirovka ot summi zakazov
			if($arBonus["ORDER_SUM_RATE"]["SUM_RANGE_TYPE"] > 0 && $arBonus["ORDER_SUM_RATE"]["SUM_RATE"] > 0)
			{
				$bonusProc = $bonusProc + $arBonus["ORDER_SUM_RATE"]["SUM_RATE"];
			}
			
			$detailInfo = '';
			
			if(COption::GetOptionString("logictim.balls", "BONUS_FOR_DELIVERY", 'N') == 'Y')
			{
				$NachslSum = $order_sum; $order_sum_metod = $order_sum;
			}
			else
			{
				$NachslSum = $cart_sum; $order_sum_metod = $cart_sum;
			}
			
			//Esli Ne nachisljat' bonusy na tovary so skidkoj
			if(COption::GetOptionString("logictim.balls", "BONUS_MINUS_DISCOUNT_PROD", 'N') == 'Y'):
				$MinusSum = 0;
				foreach($basketItems as $arItem):
						if($arItem["DISCOUNT_PRICE"] > 0) 
							$MinusSum = $MinusSum + $arItem["PRICE"] * $arItem["QUANTITY"];
				endforeach;
				$NachslSum = $NachslSum - $MinusSum;
				$detailInfo .= '-'.$MinusSum;
			endif;
			
			//Esli Ne nachisljat' bonusy na summu, oplachennuju ballami
			if(COption::GetOptionString("logictim.balls", "BONUS_MINUS_BONUS", 'N') == 'Y'):
				$NachslSum = $NachslSum - $pay_bonus;
				$detailInfo .= '-'.$pay_bonus;
			endif;
			
			$allBonus = $NachslSum * $bonusProc / 100;
			
			$detailInfo = '('.$order_sum_metod.$detailInfo.')'.' * '.$bonusProc.'%';
			
			$arBonus["ALL_BONUS"] = round($allBonus, $round);
			
		endif;
		//-----------Nachislenie bonusov po metodu 6 (bonusu na zakaz)--------------//
		
		$arBonus["COMMENT_FOR_OPERATION"] = $detailInfo;
		$arBonus["ORDER_INFO"] = array("ORDER_SUM" => $order_sum, "CART_SUM" => $cart_sum, "DELIVERY_SUM" => $delivery_sum, "PAY_BONUS" => $pay_bonus, "ORDER_ID" => $order_id);
		
		//SOBITIE POSLE RASCHETA BONUSOV
		$event = new Bitrix\Main\Event("logictim.balls", "AfterCalculateOrderBonus", $arBonus);
		$event->send();
		if($event->getResults())
		{
			foreach ($event->getResults() as $eventResult):
				$arBonus = $eventResult->getParameters();
			endforeach;
		}
		//SOBITIE POSLE RASCHETA BONUSOV
		
		return $arBonus;
	}
	
	public static function CartBonus($arElements, $order_id = 0, $params = array())
	{
		if($order_id > 0)
		{
			$order = Bitrix\Sale\Order::load($order_id);
			$user_id = $order->getUserId();
		}
		else
		{
			global $USER;
			$user_id = $USER->GetID();
		}
		
		if(COption::GetOptionString('logictim.balls', 'MODULE_VERSION', '4') >= 4)
			return $arBonus = \Logictim\Balls\CalcBonus::getBonus($arElements, array("TYPE"=>'cart', "PROFILE_TYPE" => 'order'));
		
		
		$UserBonusSystemDostup = cHelper::UserBonusSystemDostup($user_id);
		if($UserBonusSystemDostup != 'Y')
			return;
			
		//Konstanti
		$bonusElProp = 'LOGICTIM_BONUS_BALLS'; //Iz kakogo svoystva berem bonusi dlya elementov
		$bonusCatProp = 'UF_LOGICTIM_BONUS'; //Iz kakogo svoystva berem bonusi dlya categoriy
		$moduleParams = array(
								"ROUND" => (int)COption::GetOptionString("logictim.balls", "BONUS_ROUND", 2),
								"BONUS_ALL_PRODUCTS" => (float)COption::GetOptionString("logictim.balls", "BONUS_ALL_PRODUCTS", 0)
							);
			
		//Zavisimost ot summi oplachennih zakazov
		$ordersSumRangeType = (int)COption::GetOptionString("logictim.balls", "ORDERS_SUM_RANGE_TYPE", 0);
		if($ordersSumRangeType > 0)
		{
			$orderSumRate = CHelper::OrdersSumRate('', $order_id, $params);
			$arrBonus["ORDER_SUM_RATE"] = array("SUM_RANGE_TYPE" => $ordersSumRangeType, "SUM_RATE" => $orderSumRate);
		}
		
		//Zavisimost ot summi tekushey korzini
		$cartSumRangeType = (int)COption::GetOptionString("logictim.balls", "CART_SUM_RANGE_TYPE", 0);
		if($cartSumRangeType > 0)
		{
			$cartSumRate = cHelper::CartSumRate($user_id, $order_id, $arElements, $params);
			$arrBonus["CART_SUM_RATE"] = array("SUM_RANGE_TYPE" => $cartSumRangeType, "SUM_RATE" => $cartSumRate);
		}
		
		//SOBITIE DO RASCHETA BONUSOV
		$event = new Bitrix\Main\Event("logictim.balls", "BeforeGetBonusList", 
										array(
											"FUNCTION"=>"CartBonus", 
											"ORDER_ID"=>$order_id,
											"USER_ID"=>$user_id,
											"BONUS_PROP"=>$bonusElProp,
											"BONUS_CAT_PROP"=>$bonusCatProp, 
											"ORDER_SUM_RATE"=>$orderSumRate,
											"CART_SUM_RATE"=>$cartSumRate,
											"MODULE_PARAMS"=>$moduleParams
											)
									);
		$event->send();
		if($event->getResults())
		{
			foreach ($event->getResults() as $eventResult):
				$eventReultParams = $eventResult->getParameters();
				$bonusElProp = $eventReultParams["BONUS_PROP"];
				$bonusCatProp = $eventReultParams["BONUS_CAT_PROP"];
				$order_id = $eventReultParams["ORDER_ID"];
				$user_id = $eventReultParams["USER_ID"];
				$arrBonus["CART_SUM_RATE"] = $cartSumRate = $eventReultParams["CART_SUM_RATE"];
				$arrBonus["ORDER_SUM_RATE"] = $orderSumRate = $eventReultParams["ORDER_SUM_RATE"];
				$moduleParams = $eventReultParams["MODULE_PARAMS"];
			endforeach;
		}
		//SOBITIE DO RASCHETA BONUSOV
		
		CModule::IncludeModule("iblock");
		$arCategoryTree = cHelperCalc::GetCategoriesTree($bonusCatProp);
		$category_list = $arCategoryTree["CATEGORY_LIST"];
		
		$arProductList = array();
		$arProductsId = array();
		
		foreach($arElements as $item):
			$arItem = array();
			
			$arItem["ID"] = $item["PRODUCT_ID"];
			$arItem["BASKET_ITEM_ID"] = $item["ID"];
			$arItem["NAME"] = $item["NAME"];
			$arItem["QUANTITY"] = $item["QUANTITY"];
			
			$arItem["PRICE"]["FULL_PRICE"] = $item["BASE_PRICE"];
			$arItem["PRICE"]["DISCOUNT_PRICE"] = $item["PRICE"];
			$arItem["PRICE"]["DISCOUNT"] = $item["DISCOUNT_PRICE"];
			
			//Poluchaem id tovara po predlogeniyu
			$mxResult = CCatalogSku::GetProductInfo($item["PRODUCT_ID"]);
			if (is_array($mxResult))
			{
				$product_id = $mxResult['ID'];
				
				$arProductsId[] = $product_id;
				$arProductList[$product_id]["ID"] = $product_id;
				$arProductList[$product_id]["OFFERS"][] = $item["PRODUCT_ID"];
				
				$arItem["OFFER"] = 'Y';
				$arItem["MAIN_PRODUCT"]["ID"] = $product_id;
				//$offer_prod_link[$product_id] = $arItems["PRODUCT_ID"];
			}
			else
			{
				//'??? ?? ???????? ???????????';
			}
			
			$arProductsId[] = $item["PRODUCT_ID"];
			$arProductList[$item["PRODUCT_ID"]] = $arItem;
		endforeach;
		
		//Poluchaem bonusi iz svoystva tovarov
		if(!empty($arProductsId)):
			$DBproducts = CIBlockElement::GetList(
							array("ID"=>"ASC"), 
							array("ID" => $arProductsId), 
							false, 
							array("nPageSize"=>PHP_INT_MAX), 
							array("ID", "IBLOCK_ID", "NAME", "IBLOCK_SECTION_ID", "PROPERTY_".$bonusElProp, "PROPERTY_LOGICTIM_BONUS_NO", "PROPERTY_LOGICTIM_BONUS_NO_PAY")
							);
			while($el = $DBproducts->GetNextElement())
			{
				$arProd = $el->GetFields();
				if($arProductList[$arProd["ID"]]["OFFER"] != 'Y')
				{
					$arProductList[$arProd["ID"]]["PROPERTY_BONUS"] = $arProd["PROPERTY_".$bonusElProp."_VALUE"];
					$arProductList[$arProd["ID"]]["PROPERTY_BONUS_NO"] = $arProd["PROPERTY_LOGICTIM_BONUS_NO_VALUE"];
					$arProductList[$arProd["ID"]]["PROPERTY_BONUS_NO_PAY"] = $arProd["PROPERTY_LOGICTIM_BONUS_NO_PAY_VALUE"];
					$arProductList[$arProd["ID"]]["IBLOCK_SECTION_ID"] = $arProd["IBLOCK_SECTION_ID"];
					$arProductList[$arProd["ID"]]["IBLOCK_SECTION"]["PROPERTY_BONUS"] = $category_list[$arProd["IBLOCK_SECTION_ID"]][$bonusCatProp];
					$arProductList[$arProd["ID"]]["IBLOCK_SECTION"]["PROPERTY_BONUS_NO"] = $category_list[$arProd["IBLOCK_SECTION_ID"]]["UF_LOGICTIM_BONUS_NO"];
					$arProductList[$arProd["ID"]]["IBLOCK_SECTION"]["PROPERTY_BONUS_NO_PAY"] = $category_list[$arProd["IBLOCK_SECTION_ID"]]["UF_LOGICTIM_BONUS_NP"];
					$arProductList[$arProd["ID"]]["IBLOCK_SECTION"]["NAME"] = $category_list[$arProd["IBLOCK_SECTION_ID"]]["NAME"];
					foreach($arProductList[$arProd["ID"]]["OFFERS"] as $offer):
						
						$arProductList[$offer]["MAIN_PRODUCT"]["PROPERTY_BONUS"] = $arProd["PROPERTY_".$bonusElProp."_VALUE"];
						$arProductList[$offer]["MAIN_PRODUCT"]["PROPERTY_BONUS_NO"] = $arProd["PROPERTY_LOGICTIM_BONUS_NO_VALUE"];
						$arProductList[$offer]["MAIN_PRODUCT"]["PROPERTY_BONUS_NO_PAY"] = $arProd["PROPERTY_LOGICTIM_BONUS_NO_PAY_VALUE"];
					
						$arProductList[$offer]["MAIN_PRODUCT"]["IBLOCK_SECTION"]["ID"] = $arProd["IBLOCK_SECTION_ID"];
						$arProductList[$offer]["MAIN_PRODUCT"]["IBLOCK_SECTION"]["PROPERTY_BONUS"] = $category_list[$arProd["IBLOCK_SECTION_ID"]][$bonusCatProp];
						$arProductList[$offer]["MAIN_PRODUCT"]["IBLOCK_SECTION"]["PROPERTY_BONUS_NO"] = $category_list[$arProd["IBLOCK_SECTION_ID"]]["UF_LOGICTIM_BONUS_NO"];
						$arProductList[$offer]["MAIN_PRODUCT"]["IBLOCK_SECTION"]["PROPERTY_BONUS_NO_PAY"] = $category_list[$arProd["IBLOCK_SECTION_ID"]]["UF_LOGICTIM_BONUS_NP"];
						$arProductList[$offer]["MAIN_PRODUCT"]["IBLOCK_SECTION"]["NAME"] = $category_list[$arProd["IBLOCK_SECTION_ID"]]["NAME"];
						
					endforeach;
				}
				else //if offer
				{
					$arProductList[$arProd["ID"]]["PROPERTY_BONUS"] = $arProd["PROPERTY_".$bonusElProp."_VALUE"];
					$arProductList[$arProd["ID"]]["PROPERTY_BONUS_NO"] = $arProd["PROPERTY_LOGICTIM_BONUS_NO_VALUE"];
					$arProductList[$arProd["ID"]]["PROPERTY_BONUS_NO_PAY"] = $arProd["PROPERTY_LOGICTIM_BONUS_NO_PAY_VALUE"];
				}
				//echo '<pre>'; print_r($arProd); echo '</pre>';
			} //while
		endif;
		
		$bonusMetod = (int)COption::GetOptionString("logictim.balls", "BONUS_METOD", 5); //Sposob nachisleniya bonusov
		$round = (int)COption::GetOptionString("logictim.balls", "BONUS_ROUND", 2);
		$arProductListCart = array();
		$all_sum = 0;
		foreach($arElements as $item):
			$arItem = $arProductList[$item["PRODUCT_ID"]];
			
			$arBonus = cHelperCalc::GetBonusItem($arItem, $bonusMetod, $round, $moduleParams, array("SUM_RANGE_TYPE" => $ordersSumRangeType, "SUM_RATE" => $orderSumRate), $arrBonus["CART_SUM_RATE"]);
			
			if(!isset($arItem["QUANTITY"]))
		  		$arItem["QUANTITY"] = 1;
			
			$bonus = $arBonus["ADD_BONUS"];
			$arItem["BONUS"] = $arBonus["BONUS"];
			
			//Esli tip nachisleniya na summu zakaza, to jkruglyaem itogovuyu cifru za poziciyu, a ne za shtuku
			if($bonusMetod == 6)
			{
				//$arItem["ADD_BONUS_UNIT"] = round($bonus, $round); //skrivaem pokaz bonusov za edinicu, chtobi ne putati pol'zovateley
				$arItem["ADD_BONUS"] = round($bonus * $arItem["QUANTITY"], $round);
				$all_sum = $all_sum + $arItem["ADD_BONUS"];
			}
			else
			{
				$arItem["ADD_BONUS_UNIT"] = $bonus;
				$arItem["ADD_BONUS"] = $bonus * $arItem["QUANTITY"];
				$all_sum = $all_sum + $arItem["ADD_BONUS"];
			}
			$arProductListCart[$item["PRODUCT_ID"]] = $arItem;
		endforeach;
		
		
		$arrBonus["ALL_BONUS"] = $all_sum;
		$arrBonus["ITEMS"] = $arProductListCart;
		
		//echo '<pre>'; print_r($arrBonus); echo '</pre>';
		return $arrBonus;
	}
	
	public static function CatalogBonus($arResult)
	{
		if(!isset($arResult["ITEMS"])) //For catalog.element
			$arResult["ITEMS"][0] = $arResult;
			
		if(COption::GetOptionString('logictim.balls', 'MODULE_VERSION', '3') > 3)
		{
			return $arBonus = \Logictim\Balls\CalcBonus::getBonus($arResult["ITEMS"], array("TYPE"=>'catalog', "PROFILE_TYPE" => 'order'));
		}
				
		global $USER;
		$UserBonusSystemDostup = cHelper::UserBonusSystemDostup('');
		if($UserBonusSystemDostup != 'Y')
			return;
		
		//Konstanti
		$bonusElProp = 'LOGICTIM_BONUS_BALLS'; //Iz kakogo svoystva berem bonusi dlya elementov
		$bonusCatProp = 'UF_LOGICTIM_BONUS'; //Iz kakogo svoystva berem bonusi dlya categoriy	
		$user_id = $USER->GetID();
		$moduleParams = array(
								"ROUND" => (int)COption::GetOptionString("logictim.balls", "BONUS_ROUND", 2),
								"BONUS_ALL_PRODUCTS" => (float)COption::GetOptionString("logictim.balls", "BONUS_ALL_PRODUCTS", 0)
							);
		
		//Zavisimost ot summi oplachennih zakazov
		$ordersSumRangeType = (int)COption::GetOptionString("logictim.balls", "ORDERS_SUM_RANGE_TYPE", 0);
		if($ordersSumRangeType > 0)
		{
			$orderSumRate = CHelper::OrdersSumRate($user_id,  $order_id = 0, $params = array());
		}
		
		//SOBITIE DO RASCHETA BONUSOV
		$event = new Bitrix\Main\Event("logictim.balls", "BeforeGetBonusList", 
										array(
											"FUNCTION"=>"CatalogBonus", 
											"USER_ID"=>$user_id,
											"BONUS_PROP"=>$bonusElProp,
											"BONUS_CAT_PROP"=>$bonusCatProp, 
											"ORDER_SUM_RATE"=>$orderSumRate,
											"MODULE_PARAMS"=>$moduleParams
											)
									);
		$event->send();
		if($event->getResults())
		{
			foreach ($event->getResults() as $eventResult):
				$eventReultParams = $eventResult->getParameters();
				$bonusElProp = $eventReultParams["BONUS_PROP"];
				$bonusCatProp = $eventReultParams["BONUS_CAT_PROP"];
				$user_id = $eventReultParams["USER_ID"];
				$arrBonus["ORDER_SUM_RATE"] = $orderSumRate = $eventReultParams["ORDER_SUM_RATE"];
				$moduleParams = $eventReultParams["MODULE_PARAMS"];
			endforeach;
		}
		//SOBITIE DO RASCHETA BONUSOV
			
		$arCategoryTree = cHelperCalc::GetCategoriesTree($bonusCatProp);
		$category_list = $arCategoryTree["CATEGORY_LIST"];
		
		$arProductList = array();
		$arProductsId = array();
		
		//unset($arResult["ITEMS"][0]["PROPERTIES"]);
		
		
		//PEREBIRAEM TOVARI
		foreach($arResult["ITEMS"] as $item):
			$arItem = array();
			
			
			$arItem["ID"] = $item["ID"];
			//$arItem["IBLOCK_SECTION_ID"] = $item["~IBLOCK_SECTION_ID"];  //Ne isp
			
			$arItem["IBLOCK_SECTION"]["ID"] = $item["~IBLOCK_SECTION_ID"];
			$arItem["IBLOCK_SECTION"]["PROPERTY_BONUS"] = $category_list[$item["~IBLOCK_SECTION_ID"]][$bonusCatProp];
			$arItem["IBLOCK_SECTION"]["PROPERTY_BONUS_NO"] = $category_list[$item["~IBLOCK_SECTION_ID"]]["UF_LOGICTIM_BONUS_NO"];
			$arItem["IBLOCK_SECTION"]["NAME"] = $category_list[$item["~IBLOCK_SECTION_ID"]]["NAME"];
			
			$arItem["PRICE"]["FULL_PRICE"] = $item["MIN_PRICE"]["VALUE"];
			$arItem["PRICE"]["DISCOUNT_PRICE"] = $item["MIN_PRICE"]["DISCOUNT_VALUE"];
			$arItem["PRICE"]["DISCOUNT"] = $item["MIN_PRICE"]["DISCOUNT_DIFF"];
			if(!empty($item["ITEM_PRICES"]) && count($item["ITEM_PRICES"]) > 1)
				$arItem["PRICE_MATRIX"] = $item["ITEM_PRICES"];
			
			//Esli infa po bonusam est v massive arresult, to berem ee, esli net, to vibiraem iz bazi
			if(isset($item["PROPERTIES"][$bonusElProp]) && isset($item["PROPERTIES"]["LOGICTIM_BONUS_NO"]))
			{
				$arItem["PROPERTY_BONUS"] = $item["PROPERTIES"][$bonusElProp]["VALUE"];
				$arItem["PROPERTY_BONUS_NO"] = $item["PROPERTIES"]["LOGICTIM_BONUS_NO"]["VALUE"];
			}
			else
			{
				$arProductsId[] = $item["ID"];
			}
			
			
			$arProductList[$item["ID"]] = $arItem;
				
				//Perebiraem offersi
				if(!empty($item["OFFERS"])):
					foreach($item["OFFERS"] as $offer):
						$arOffer = array();
						
						$arOffer["ID"] = $offer["ID"];
						$arOffer["OFFER"] = 'Y';
						$arOffer["MAIN_PRODUCT"]["ID"] = $item["ID"];
						
						$arOffer["PRICE"]["FULL_PRICE"] = $offer["MIN_PRICE"]["VALUE"];
						$arOffer["PRICE"]["DISCOUNT_PRICE"] = $offer["MIN_PRICE"]["DISCOUNT_VALUE"];
						$arOffer["PRICE"]["DISCOUNT"] = $offer["MIN_PRICE"]["DISCOUNT_DIFF"];
						
						if(!empty($offer["ITEM_PRICES"]) && count($offer["ITEM_PRICES"]) > 1)
							$arOffer["PRICE_MATRIX"] = $offer["ITEM_PRICES"];
						
						
						if(isset($arItem["PROPERTY_BONUS"]) && isset($arItem["PROPERTY_BONUS_NO"]))
						{
							$arOffer["MAIN_PRODUCT"]["PROPERTY_BONUS"] = $arItem["PROPERTY_BONUS"];
							$arOffer["MAIN_PRODUCT"]["PROPERTY_BONUS_NO"] = $arItem["PROPERTY_BONUS_NO"];
						}
						
						if(isset($offer["PROPERTIES"][$bonusElProp]) && isset($offer["PROPERTIES"]["LOGICTIM_BONUS_NO"]))
						{
							$arOffer["PROPERTY_BONUS"] = $offer["PROPERTIES"][$bonusElProp]["VALUE"];
							$arOffer["PROPERTY_BONUS_NO"] = $offer["PROPERTIES"]["LOGICTIM_BONUS_NO"]["VALUE"];
						}
						else
						{
							$arProductsId[] = $arOffer["ID"];
						}

						$arOffer["MAIN_PRODUCT"]["IBLOCK_SECTION"]["ID"] = $item["~IBLOCK_SECTION_ID"];
						$arOffer["MAIN_PRODUCT"]["IBLOCK_SECTION"]["PROPERTY_BONUS"] = $category_list[$item["~IBLOCK_SECTION_ID"]][$bonusCatProp];
						$arOffer["MAIN_PRODUCT"]["IBLOCK_SECTION"]["PROPERTY_BONUS_NO"] = $category_list[$item["~IBLOCK_SECTION_ID"]]["UF_LOGICTIM_BONUS_NO"];
						$arOffer["MAIN_PRODUCT"]["IBLOCK_SECTION"]["NAME"] = $category_list[$item["~IBLOCK_SECTION_ID"]]["NAME"];
						
						$arProductList[$item["ID"]]["OFFERS"][] = $offer["ID"];
						$arProductList[$offer["ID"]] = $arOffer;
					endforeach;
				endif; //offers
				
			
		endforeach; //$arResult["ITEMS"]
		
		//Poluchaem bonusi iz svoystva tovarov
		if(!empty($arProductsId)):
			$DBproducts = CIBlockElement::GetList(
										array("ID"=>"ASC"), 
										array("ID" => $arProductsId), 
										false, 
										array("nPageSize"=>PHP_INT_MAX), 
										array("ID", "IBLOCK_ID", "NAME", "IBLOCK_SECTION_ID", "PROPERTY_".$bonusElProp, "PROPERTY_LOGICTIM_BONUS_NO")
										);
			while($el = $DBproducts->GetNextElement())
			{
				$arProd = $el->GetFields();
				//echo '<pre>'; print_r($arProd); echo '</pre>';
				$arProductList[$arProd["ID"]]["PROPERTY_BONUS"] = $arProd["PROPERTY_".$bonusElProp."_VALUE"];
				$arProductList[$arProd["ID"]]["PROPERTY_BONUS_NO"] = $arProd["PROPERTY_LOGICTIM_BONUS_NO_VALUE"];
				if(isset($arProductList[$arProd["ID"]]["OFFERS"]))
				{
					foreach($arProductList[$arProd["ID"]]["OFFERS"] as $offer):
						$arProductList[$offer]["MAIN_PRODUCT"]["PROPERTY_BONUS"] = $arProd["PROPERTY_".$bonusElProp."_VALUE"];
						$arProductList[$offer]["MAIN_PRODUCT"]["PROPERTY_BONUS_NO"] = $arProd["PROPERTY_LOGICTIM_BONUS_NO_VALUE"];
					endforeach;
				}
			}
		endif;
		
		
		$bonusMetod = (int)COption::GetOptionString("logictim.balls", "BONUS_METOD", 5); //Sposob nachisleniya bonusov
		$round = (int)COption::GetOptionString("logictim.balls", "BONUS_ROUND", 2);
		
		foreach($arProductList as $arItem):
			$arBonus = cHelperCalc::GetBonusItem($arItem, $bonusMetod, $round, $moduleParams, array("SUM_RANGE_TYPE" => $ordersSumRangeType, "SUM_RATE" => $orderSumRate));
			if($bonusMetod == 6)
				$bonus = round($arBonus["ADD_BONUS"], $round);
			else
				$bonus = $arBonus["ADD_BONUS"];
			$arProductList[$arItem["ID"]]["BONUS"] = $arBonus["BONUS"];
			$arProductList[$arItem["ID"]]["ADD_BONUS"] = $bonus;
			$arProductList[$arItem["ID"]]["VIEW_BONUS"] = $bonus;
			
			if(isset($arBonus["PRICE_MATRIX"]))
				$arProductList[$arItem["ID"]]["PRICE_MATRIX"] = $arBonus["PRICE_MATRIX"];
			
			if($arItem["OFFER"] == 'Y' && $bonus > $arProductList[$arItem["MAIN_PRODUCT"]["ID"]]["VIEW_BONUS"])
			{
				$arProductList[$arItem["MAIN_PRODUCT"]["ID"]]["VIEW_BONUS"] = $bonus;
			}
		endforeach;
		
		
		
		//echo '<pre>'; print_r($categoryTree); echo '</pre>';
		//echo '<pre>'; print_r($arProductsId); echo '</pre>';
		//echo '<pre>'; print_r($arProductList); echo '</pre>';
		//echo '<pre>'; print_r($arResult); echo '</pre>';
		
		return $arProductList;
	}
	
	public static function GetBonusItem($arItem, $bonusMetod, $round, $moduleParams, $arOrderSumRate = array(), $arCartSumRate = array()) //Get bonus for one item
	{
		//SOBITIE DO RASCHETA BONUSOV
		//call event
		$event = new Bitrix\Main\Event("logictim.balls", "BeforeGetBonusItem", $arItem);
		$event->send();
		//come back event
		if($event->getResults())
		{
			foreach ($event->getResults() as $eventResult):
				$arItem = $eventResult->getParameters();
			endforeach;
		}
		//SOBITIE DO RASCHETA BONUSOV
		
		$bonus = 0;
		
		//Poluchaem cenu, esli ee net v massive
		if(!isset($arItem["PRICE"]["DISCOUNT_PRICE"]))
		{
			$optimalPrice = cHelperCalc::GetOptimalPrice($arItem["ID"], 1);
			$arItem["PRICE"]["FULL_PRICE"] = $optimalPrice["RESULT_PRICE"]["BASE_PRICE"];
			$arItem["PRICE"]["DISCOUNT_PRICE"] = $optimalPrice["RESULT_PRICE"]["DISCOUNT_PRICE"];
			$arItem["PRICE"]["DISCOUNT"] = $optimalPrice["RESULT_PRICE"]["DISCOUNT"];
		}
		
		//Esli tip nachisleniya na tovari
		if($bonusMetod == 5)
		{
			if($arItem["PROPERTY_BONUS_NO"] == 'Y' 
				  || $arItem["IBLOCK_SECTION"]["PROPERTY_BONUS_NO"] == 1 
				  || $arItem["MAIN_PRODUCT"]["PROPERTY_BONUS_NO"] == 'Y'
				  || $arItem["MAIN_PRODUCT"]["IBLOCK_SECTION"]["PROPERTY_BONUS_NO"] == 1
				)
			  return $bonus;
			  
			//Esli Ne nachisljat' bonusy na tovary so skidkoj
			if(COption::GetOptionString("logictim.balls", "BONUS_MINUS_DISCOUNT_PROD_METOD_5", 'N') == 'Y' && $arItem["PRICE"]["DISCOUNT"] > 0)
			   return $bonus;
			   
			if(isset($moduleParams["BONUS_ALL_PRODUCTS"]))
				$allProduct = $moduleParams["BONUS_ALL_PRODUCTS"];
			else
				$allProduct = (float)COption::GetOptionString("logictim.balls", "BONUS_ALL_PRODUCTS", 0);
			
			if($allProduct > 0)
			{
				$bonus = $allProduct;
			}
			   
			if($arItem["OFFER"] == 'Y') //OFFER
			{
				if($arItem["MAIN_PRODUCT"]["IBLOCK_SECTION"]["PROPERTY_BONUS"] > 0)
				  $bonus = $arItem["MAIN_PRODUCT"]["IBLOCK_SECTION"]["PROPERTY_BONUS"];
				if($arItem["MAIN_PRODUCT"]["PROPERTY_BONUS"] > 0)
				  $bonus = $arItem["MAIN_PRODUCT"]["PROPERTY_BONUS"];
				if($arItem["PROPERTY_BONUS"] > 0)
				  $bonus = $arItem["PROPERTY_BONUS"];
			}
			else
			{
				if($arItem["IBLOCK_SECTION"]["PROPERTY_BONUS"] > 0)
				  $bonus = $arItem["IBLOCK_SECTION"]["PROPERTY_BONUS"];
				if($arItem["PROPERTY_BONUS"] > 0)
				  $bonus = $arItem["PROPERTY_BONUS"];
			}
			
			$arBonus["BONUS"] = $bonus;
			
			//Korrektirovka ot summi zakazov
			if($arOrderSumRate["SUM_RANGE_TYPE"] > 0 && $arOrderSumRate["SUM_RATE"] > 0)
				$arBonus["BONUS"] = $bonus = $bonus + $arOrderSumRate["SUM_RATE"];
			//Korrektirovka ot summi korzini
			if($arCartSumRate["SUM_RANGE_TYPE"] > 0 && $arCartSumRate["SUM_RATE"] > 0)
				$arBonus["BONUS"] = $bonus = $bonus + $arCartSumRate["SUM_RATE"];
			
			$bonusType = (int)COption::GetOptionString("logictim.balls", "BONUS_FOR_PRODUCT_TYPE", 1);
			if($bonusType == 1) //Esli tip nachisleniya - procenti
			{
			  $bonus = round($bonus * $arItem["PRICE"]["DISCOUNT_PRICE"] / 100, $round);
			  
			  //Esli matrica cen (rashirennoe upravlenie cenami)
			  if(isset($arItem["PRICE_MATRIX"]))
			  {
				  $priceMatrix = array();
				  foreach($arItem["PRICE_MATRIX"] as $onePrice):
				  	$onePrice["ADD_BONUS"] = round($arBonus["BONUS"] * $onePrice["PRICE"] / 100, $round);
				  	$priceMatrix[$onePrice["QUANTITY_HASH"]] = $onePrice;
				  endforeach;
				  $arBonus["PRICE_MATRIX"] = $priceMatrix;
			  }
			}
			if($bonusType == 2) //Esli tip nachisleniya - fix summa
			{
				$bonus = $bonus;
				//Esli matrica cen (rashirennoe upravlenie cenami)
				if(isset($arItem["PRICE_MATRIX"]))
				{
				  $priceMatrix = array();
				  foreach($arItem["PRICE_MATRIX"] as $onePrice):
					$onePrice["ADD_BONUS"] = $bonus;
					$priceMatrix[$onePrice["QUANTITY_HASH"]] = $onePrice;
				  endforeach;
				  $arBonus["PRICE_MATRIX"] = $priceMatrix;
				}
			}
		}
		
		//Esli tip nachisleniya na zakaz
		if($bonusMetod == 6)
		{
			//Esli Ne nachisljat' bonusy na tovary so skidkoj
			if(COption::GetOptionString("logictim.balls", "BONUS_MINUS_DISCOUNT_PROD", 'N') == 'Y' && $arItem["PRICE"]["DISCOUNT"] > 0)
				return $bonus;
				
			$bonusProc = (float)COption::GetOptionString("logictim.balls", "BONUS_PROC", 10);
			
			//Korrektirovka ot summi zakazov
			if($arOrderSumRate["SUM_RANGE_TYPE"] > 0 && $arOrderSumRate["SUM_RATE"] > 0)
				$bonusProc = $bonusProc + $arOrderSumRate["SUM_RATE"];
				
			$bonus = $bonusProc * $arItem["PRICE"]["DISCOUNT_PRICE"] / 100;
			
			//Esli matrica cen (rashirennoe upravlenie cenami)
			if(isset($arItem["PRICE_MATRIX"]))
			{
				$priceMatrix = array();
				foreach($arItem["PRICE_MATRIX"] as $onePrice):
					$onePrice["ADD_BONUS"] = $bonusProc * $onePrice["PRICE"] / 100;
					$priceMatrix[$onePrice["QUANTITY_HASH"]] = $onePrice;
				endforeach;
				$arBonus["PRICE_MATRIX"] = $priceMatrix;
			}
			
		}
		
		$arBonus["ADD_BONUS"] = $bonus;
		
		
		//SOBITIE POSLE RASCHETA BONUSOV
		//call event
		$event = new Bitrix\Main\Event("logictim.balls", "AfterGetBonusItem", $arBonus);
		$event->send();
		//come back event
		if($event->getResults())
		{
			foreach ($event->getResults() as $eventResult):
				$arItem = $eventResult->getParameters();
			endforeach;
		}
		//SOBITIE POSLE RASCHETA BONUSOV
		
		return $arBonus; 
		  
	}
	
	//Opredelenie optimalnoy ceni
	public static function GetOptimalPrice($ID, $quantity)
	{
		CModule::IncludeModule("catalog");
		if(!is_numeric($quantity))
			$quantity = 1;
		$arPrice = CCatalogProduct::GetOptimalPrice($ID, $quantity, array(), "N");
		
		return $arPrice;
	}
	  
}
?>