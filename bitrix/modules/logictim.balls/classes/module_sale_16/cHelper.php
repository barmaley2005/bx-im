<?
class cHelper
{
	//Opredelyaem ID platezhnoy systemi dlya bonusov
	public static function PaySystemBonusId()
	{
		CModule::IncludeModule("sale");
		$paySystemBonus = CSalePaySystem::GetList(array(), array('CODE' => 'LOGICTIM_PAYMENT_BONUS'));
			while($ptype = $paySystemBonus->Fetch())
			{   
				$paySystemId = $ptype["ID"];
			}
		return $paySystemId;
	}
	
	//Opredelyaem vse platezhnie systemi dlya bonusov
	public static function PaySystemsBonus()
	{
		CModule::IncludeModule("sale");
		$arPaySystems = array();
		$paySystemBonus = CSalePaySystem::GetList(array(), array('CODE' => 'LOGICTIM_PAYMENT_BONUS'));
			while($ptype = $paySystemBonus->Fetch())
			{   
				$arPaySystems[] = $ptype["ID"];
			}
		return $arPaySystems;
	}
	
	//opredelyaem dostupnosti' modulya dlya opredelennogo saiya
	public static function CheckSiteDostup($site_id = '')
	{
		
		//take sites from module parameters
		$LogictimBonus_sites = unserialize(COption::GetOptionString("logictim.balls", "SITES", ''));
		
		if($LogictimBonus_sites && !empty($LogictimBonus_sites) && $site_id != '')
		{
			if(in_array($site_id, $LogictimBonus_sites))
				$dostup = 'Y';
			else
				$dostup = 'N';
		}
		else
			$dostup = 'Y';
			
		return $dostup;
	}
	
	//opredelyaem, dostupna li bonusnaya systema useru iz nastroek modulya
	public static function UserBonusSystemDostup($user_id)
	{
		if(COption::GetOptionString('logictim.balls', 'MODULE_VERSION', '4') >= 4)
			return 'Y';
		
		global $USER;
		if(!$user_id || $user_id == '')
		{
			if(is_object($USER))
				$user_id = $USER->GetID();
			else
				$user_id = '';
		}
		//take grups from module parameters
		$LogictimBonus_user_groups_type = (int)COption::GetOptionString("logictim.balls", "USER_GROUPS_TYPE", 1);
		$LogictimBonus_user_groups = unserialize(COption::GetOptionString("logictim.balls", "USER_GROUPS", ''));
		
		
		if($LogictimBonus_user_groups && !empty($LogictimBonus_user_groups) && $LogictimBonus_user_groups_type == 1) 
		{
			$groopDostupLogictimBonus = 'N';
			
			//take grups of user
			$arUserGroups = CUser::GetUserGroup($user_id);
			
			//proverka nalichiya razreshennih modulem grupp, sredi grupp usera
			foreach($LogictimBonus_user_groups as $LogictimBonus_user_group):
				if (in_array($LogictimBonus_user_group, $arUserGroups))
				{
					$groopDostupLogictimBonus = 'Y';
				}
			endforeach;
		}
		elseif($LogictimBonus_user_groups && !empty($LogictimBonus_user_groups) && $LogictimBonus_user_groups_type == 2)
		{
			$groopDostupLogictimBonus = 'Y';
			
			//take grups of user
			$arUserGroups = CUser::GetUserGroup($user_id);
			
			//proverka nalichiya razreshennih modulem grupp, sredi grupp usera
			foreach($LogictimBonus_user_groups as $LogictimBonus_user_group):
				if (in_array($LogictimBonus_user_group, $arUserGroups))
				{
					$groopDostupLogictimBonus = 'N';
				}
			endforeach;
		}
		else {
			$groopDostupLogictimBonus = 'Y';
		}
		
		//Proveryaem dostup po prinadlejnosti k saytu
		$rsUser = CUser::GetByID($user_id);
		$arUser = $rsUser->Fetch();
		if($arUser["LID"] != '' && cHelper::CheckSiteDostup($arUser["LID"]) != 'Y')
			$groopDostupLogictimBonus = 'N';
				
		return $groopDostupLogictimBonus;
	}
	
	//Poluchaem balans pol'zovatelya
	public static function UserBallance($user_id)
	{
			global $USER;
			if(!$user_id || $user_id == '')
				$user_id = $USER->GetID();
			if(cHelper::UserBonusSystemDostup($user_id) == 'Y'):
			
				//Esli ispol'zuetsya bonusniy schet modulya
				if(COption::GetOptionString("logictim.balls", "BONUS_BILL", '1') == 1)
				{
					$arParams["SELECT"] = array("UF_LOGICTIM_BONUS");
					$DBUserBonus = CUser::GetList(($by="ID"),($order="desc"),array("ID" => $user_id),$arParams);
					if($arUserBonus = $DBUserBonus->Fetch()) 
					{
						$userBonus = $arUserBonus["UF_LOGICTIM_BONUS"];
					}
				}
				//Esli ispol'zuetsya vnutrenniy schet bitrix
				else
				{
					CModule::IncludeModule("sale");
					//Opredelyaem valyutu
					$currency = COption::GetOptionString("logictim.balls", "BONUS_CURRENCY", 'RUB');
					
					$dbAccountCurrency = CSaleUserAccount::GetList(array(), array("USER_ID" => $user_id, "CURRENCY" => $currency), false, false, array());
					while($arAccountCurrency = $dbAccountCurrency->Fetch())
					{
						$userBonus = $arAccountCurrency["CURRENT_BUDGET"];
					}
				}
					
				
			endif;
			
			
			
			return $userBonus*1;
	}
	
	//Poluchaem okruglenniy balans pol'zovatelya
	public static function UserBallanceRound($user_id)
	{
		$userBonus = cHelper::UserBallance($user_id);
		//Okruglyaem do men'shego s uchetom razryadov
		$round = (int)COption::GetOptionString("logictim.balls", "BONUS_ROUND", 2);
		$roundIndex = 1;
		$x = 0;
		while($x++ < $round)
		{
			$roundIndex = $roundIndex * 10;
		}
		$userBonus = floor($userBonus * $roundIndex) / $roundIndex;
		
		return $userBonus*1;
	}
	
	
	//Poluchaem minimal'nuju summu oplaty bonusami (iz nastroek modulja)
	public static function MinBonusSum($order_sum, $order_cart_sum, $order_delivery_sum)
	{
		if(COption::GetOptionString('logictim.balls', 'MODULE_VERSION', '4') >= 4)
			return $minBonusSum = \Logictim\Balls\CalcBonus::MinBonusPayment(array("ORDER_SUM" => $order_sum, "CART_SUM" => $order_cart_sum, "DELIVERY_SUM" => $order_delivery_sum));
		
		$minBonusType = COption::GetOptionString("logictim.balls", "MIN_PAYMENT_TYPE", '1');
		$minBonusIndex = COption::GetOptionString("logictim.balls", "MIN_PAYMENT_BONUS", '0');
		$round = (int)COption::GetOptionString("logictim.balls", "BONUS_ROUND", 2);
		$minBonusSum = 0;
					if($minBonusType == 1) {$minBonusSum = 0;}
					if($minBonusType == 2) {$minBonusSum = $minBonusIndex;}
					if($minBonusType == 3) {$minBonusSum = $order_cart_sum * $minBonusIndex / 100;}
					if($minBonusType == 4) {$minBonusSum = $order_sum * $minBonusIndex / 100;}
		$minBonusSum = round($minBonusSum, $round);
		return $minBonusSum;
	}
	//Poluchaem maksimal'nuju summu oplaty bonusami (iz nastroek modulja)
	public static function MaxBonusSum($order_sum, $order_cart_sum, $order_delivery_sum, $arItems)
	{
		$maxBonusType = COption::GetOptionString("logictim.balls", "MAX_PAYMENT_TYPE", '1');
		$maxBonusIndex = COption::GetOptionString("logictim.balls", "MAX_PAYMENT_SUM", '0');
		$round = (int)COption::GetOptionString("logictim.balls", "BONUS_ROUND", 2);
		
		if($arItems && !empty($arItems))
		{
			//Zapret oplati tovara bonusami
			$bonusItemsInfo = cHelperCalc::CartBonus($arItems);
			
			foreach($arItems as $arItem)
			{
				if(
					COption::GetOptionString("logictim.balls", "MAX_PAYMENT_DISCOUNT", 'N') == 'Y' && $arItem["DISCOUNT_PRICE"] > 0
					||
					//Zapret oplati tovara bonusami iz svoystv
					$bonusItemsInfo["ITEMS"][$arItem["PRODUCT_ID"]]["PROPERTY_BONUS_NO_PAY"] == 'Y' || $bonusItemsInfo["ITEMS"][$arItem["PRODUCT_ID"]]["MAIN_PRODUCT"]["PROPERTY_BONUS_NO_PAY"] == 'Y' || $bonusItemsInfo["ITEMS"][$arItem["PRODUCT_ID"]]["MAIN_PRODUCT"]["IBLOCK_SECTION"]["PROPERTY_BONUS_NO_PAY"] == 1
				)
				{
					$order_cart_sum = $order_cart_sum - $arItem["PRICE"] * $arItem["QUANTITY"];
					$order_sum = $order_sum - $arItem["PRICE"] * $arItem["QUANTITY"];
				}
				
				
			}
			
			
		}
		
		$maxBonusSum = 0;
			if($maxBonusType == 1) {$maxBonusSum = $order_sum;}
			if($maxBonusType == 2) {$maxBonusSum = $maxBonusIndex;}
			if($maxBonusType == 3) {$maxBonusSum = $order_cart_sum * $maxBonusIndex / 100;}
			if($maxBonusType == 4) {$maxBonusSum = $order_sum * $maxBonusIndex / 100;}
		$maxBonusSum = round($maxBonusSum, $round);
		return $maxBonusSum;
	}
	
	
	//Poluchaem koefficient iz diapazonov summi korzini
	public static function CartSumRate($user_id = '', $order_id = 0, $arElements = array(), $params = array())
	{
		$cartSum = 0;
		
		if(!empty($arElements))
		{
			foreach($arElements as $element):
				if($element["PRICE"] > 0 && $element["QUANTITY"] > 0)
					$cartSum = $cartSum + $element["PRICE"]*$element["QUANTITY"];
			endforeach;
		}
		
		$arRanges = array();
		$arRanges[1] = COption::GetOptionString("logictim.balls", "CART_SUM_RANGE_1", '');
		$arRanges[2] = COption::GetOptionString("logictim.balls", "CART_SUM_RANGE_2", '');
		$arRanges[3] = COption::GetOptionString("logictim.balls", "CART_SUM_RANGE_3", '');
		$arRanges[4] = COption::GetOptionString("logictim.balls", "CART_SUM_RANGE_4", '');
		$arRanges[5] = COption::GetOptionString("logictim.balls", "CART_SUM_RANGE_5", '');
		
		foreach($arRanges as $key => $range):
			$from = $to = '';
			$arRange =explode('-', $range);

			if(count($arRange) == 2)
			{
				$from = $arRange[0];
				$to = $arRange[1];
				if($to == 0)
					$to = PHP_INT_MAX;
					
				if($cartSum >= $from && $cartSum <= $to)
					$rate = COption::GetOptionString("logictim.balls", "CART_SUM_RANGE_".$key."_RATE", '');
			}
			
		endforeach;

		return $rate;
			
	}
	
	//Poluchaem summu oplachennih zakazov pol'zovatelya
	public static function CalculateOrdersSum($user_id, $site_id)
	{
		CModule::IncludeModule("sale");
		global $USER;
		if(!$user_id || $user_id == '')
			$user_id = $USER->GetID();
				
		$calculator = new \Bitrix\Sale\Discount\CumulativeCalculator($user_id, $site_id);
		
		$period = (int)COption::GetOptionString("logictim.balls", "ORDERS_SUM_RANGE_PERIOD", '240');
		$periodType = COption::GetOptionString("logictim.balls", "ORDERS_SUM_RANGE_PERIOD_TYPE", 'M');
		
		if($period > 0)
		{
			$sumConfiguration = array(
						'type_sum_period'=> 'relative',
						'sum_period_data' => array('period_value' => $period, 'period_type' => $periodType)
						);
			$calculator->setSumConfiguration($sumConfiguration);
		}
		
		$sum = $calculator->calculate();
		
		return $sum;
	}
	
	//Poluchaem koefficient iz diapazonov summi oplachennih zakazov
	public static function OrdersSumRate($user_id = '', $order_id = 0, $params = array())
	{
		if($order_id > 0)
		{
			$order = Bitrix\Sale\Order::load($order_id);
			$user_id = $order->getUserId();
			$site_id = $order->getSiteId();
		}
		else
		{
			$site_id = SITE_ID;
		}
		
		
		global $USER;
		if(!$user_id || $user_id == '')
			$user_id = $USER->GetID();
		
		$ordersSum = cHelper::CalculateOrdersSum($user_id, $site_id);
		
		
		//minusuem iz summi oplachennih zakazov zakaz? za kotoriy nachislyaem bonus
		if($order_id > 0 && $params["EVENT_ORDER_PAID"] == 'Y')
		{
			$ordersSum = $ordersSum - $order->getPrice();
		}
		
		
		$arRanges = array();
		$arRanges[1] = COption::GetOptionString("logictim.balls", "ORDERS_SUM_RANGE_1", '');
		$arRanges[2] = COption::GetOptionString("logictim.balls", "ORDERS_SUM_RANGE_2", '');
		$arRanges[3] = COption::GetOptionString("logictim.balls", "ORDERS_SUM_RANGE_3", '');
		$arRanges[4] = COption::GetOptionString("logictim.balls", "ORDERS_SUM_RANGE_4", '');
		$arRanges[5] = COption::GetOptionString("logictim.balls", "ORDERS_SUM_RANGE_5", '');
		
		foreach($arRanges as $key => $range):
			$from = $to = '';
			$arRange =explode('-', $range);

			if(count($arRange) == 2)
			{
				$from = $arRange[0];
				$to = $arRange[1];
				if($to == 0)
					$to = PHP_INT_MAX;
					
				if($ordersSum >= $from && $ordersSum <= $to)
					$rate = COption::GetOptionString("logictim.balls", "ORDERS_SUM_RANGE_".$key."_RATE", '');
			}
			
			
		endforeach;
			return $rate;
	}
	
	//Raskidivaem oplatu bonusami kak skidku
	public static function PayBonusToDiscount($order_id)
	{
		$PayBonusToDiscount = COption::GetOptionString("logictim.balls", "DISCOUNT_TO_PRODUCTS", 'N');
		if($PayBonusToDiscount != 'Y')
			return;
			
		$order = Bitrix\Sale\Order::load($order_id);
			
		//Poluchaem summu oplachennuyu bonusami
		$bonusPaySystemId = cHelper::PaySystemBonusId();
		$paymentCollection = $order->getPaymentCollection();
		foreach($paymentCollection as $arPayment):
			$payFields = $arPayment->GetFields();
			$pay = $payFields->GetValues();
			if($pay["PAY_SYSTEM_ID"] == $bonusPaySystemId && $pay["PAID"] == 'Y')
				$bonusPay = $pay["SUM"];
		endforeach;
		
		if($bonusPay > 0):
		
			$basket = $order->getBasket();
			
			$order_sum = $order->getPrice(); //Stoimost zakaza
			$order_cart_sum = $basket->getPrice(); //Stoimost korzini
			$order_delivery_sum = $order->getDeliveryPrice(); //Stoimost dostavki
			
			//Esli zapret na oplatu bonusami tovarov so skidkoy, a tovar so skidkoy, to korrektiruem summu korzini dlya rasschetov
			if(COption::GetOptionString("logictim.balls", "MAX_PAYMENT_DISCOUNT", 'N') == 'Y')
			{
				foreach($basket as $key => $basketItem):
					$item = $basketItem->getFields();
					$arItem = $item->getValues();
					if($arItem["DISCOUNT_PRICE"] > 0)
						$order_cart_sum = $order_cart_sum - $arItem["PRICE"]*$arItem["QUANTITY"];
				endforeach;
			}
			
			//Esli bonusami oplacheno bol'she, chem stoimost korzini, to vichitaem ih iz dostavki
			if($bonusPay > $order_cart_sum)
			{
				$bonusPayDelivery = $bonusPay - $order_cart_sum;
				$bonusPayCart = $order_cart_sum;
				$newDeliveryPrice = $order_delivery_sum - $bonusPayDelivery;
				CModule::IncludeModule("sale");
				CSaleOrder::Update($order_id, array('PRICE_DELIVERY'=>$newDeliveryPrice));
			}
			else
				$bonusPayCart = $bonusPay;
				
			//Otmenyaem oplatu bonusami
			$paymentCollectionChange = $order->getPaymentCollection();
			foreach($paymentCollectionChange as $arPayment):
				$fields = $arPayment->GetFields();
				$values = $fields->GetValues();
				if($values["PAY_SYSTEM_ID"] == $bonusPaySystemId)
				{
					$arPayment->setField("PAID", "N");
					$arPayment->Delete();				
				}
			endforeach;
				
			//Raskidivaem skidku po tovaram v korzine
			foreach($basket as $basketItem):
			
				$item = $basketItem->getFields();
				$arItem = $item->getValues();
				
				//Esli zapret na oplatu bonusami tovarov so skidkoy, a tovar so skidkoy, to propuskaem ego
				if(COption::GetOptionString("logictim.balls", "MAX_PAYMENT_DISCOUNT", 'N') == 'Y' && $arItem["DISCOUNT_PRICE"] > 0)
					continue;
				
				$productPart = $arItem["PRICE"] * $arItem["QUANTITY"] * 100 / $order_cart_sum; //Procentnoe sootnoshenie pozicii tovara s obshhej summoj
				$discountPlus = $bonusPayCart * $productPart / 100; //Skol'ko rublej nado pripljusovat' k skidke tovara
				$newPrice = $arItem["PRICE"] - $discountPlus / $arItem["QUANTITY"]; //Cena s uchetom raskidanooj skidki
				$newDiscount = $arItem["DISCOUNT_PRICE"] + $discountPlus / $arItem["QUANTITY"]; //Skidka s uchetom dobavlennoj novoj skidki
				
				$basketItem->setField('BASE_PRICE', $arItem["BASE_PRICE"]);
				$basketItem->setField('PRICE', $newPrice);
				$basketItem->setField('DISCOUNT_PRICE', $newDiscount);
				$basketItem->setField('CUSTOM_PRICE', 'Y');
			
			endforeach;	
			
			$order->Save();
				
		endif;
	}
	
	public static function UserBonusSystemDostupNew()
	{
		require_once($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/main/classes/general/update_client_partner.php');
		$moduleID = "logictim.balls";
		$arRequestedModules = array($moduleID);
		$arUpdateList = CUpdateClientPartner::GetUpdatesList($errorMessage, LANG, 'Y', $arRequestedModules, array('fullmoduleinfo' => 'Y'));
		if($arUpdateList && isset($arUpdateList['MODULE'])):
			foreach($arUpdateList['MODULE'] as $arModule):
				if($arModule['@']['ID'] === $moduleID)
					$myModuleInfo = $arModule['@'];
			endforeach;
		endif;
		
		if(!empty($myModuleInfo)):
			$dateTo = strtotime($myModuleInfo["DATE_TO"]);
		endif;
		
		if(!$myModuleInfo)
		{
			//include_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/".$module_id."/install/index.php");
			//$obModule = new $module_id;
			//if($obModule->IsInstalled()) $obModule->DoUninstall();
			
			//CAdminMessage::ShowMessage(array("TYPE"=>"ERROR", "MESSAGE"=>'AGA'));
			
			$params = array(
					"LICENSE_KEY" => LICENSE_KEY,
					"LICENSE_NAME" => $arUpdateList["CLIENT"][0]["@"]["NAME"],
					"LICENSE_DATE_FROM" => strtotime($arUpdateList["CLIENT"][0]["@"]["DATE_FROM"]),
					"LICENSE_DATE_TO" => strtotime($arUpdateList["CLIENT"][0]["@"]["DATE_TO"]),
					"DOMEN" => $_SERVER['SERVER_NAME'],
					);
			$url = 'https://logictim.ru/marketplace/check_installs.php';
			if(fopen($url, "r")):
				$result = file_get_contents($url, false, stream_context_create(array(
					'http' => array(
						'method'  => 'POST',
						'header'  => 'Content-type: application/x-www-form-urlencoded',
						'content' => http_build_query($params)
					)
				)));
			endif;
		}
	}
	
	//Opredelyaem ID ibfobloka s operaciyami
	public static function IblokOperationsId()
	{
		CModule::IncludeModule("iblock");
				$dbiblokOpertion = CIBlock::GetList(
										Array(), 
										Array(
											'ACTIVE'=>'Y', 
											"CODE"=>'logictim_bonus_operations'
										), false
									);
				while($iblokOpertion = $dbiblokOpertion->Fetch())
				{
					$iblokOperationsId = $iblokOpertion["ID"];
				}
		return $iblokOperationsId;
	}
	//Opredelyaem ID ibfobloka s operaciyami ojidaniya
	public static function IblokWaitId()
	{
		CModule::IncludeModule("iblock");
				$dbiblokWait = CIBlock::GetList(
										Array(), 
										Array(
											'ACTIVE'=>'Y', 
											"CODE"=>'logictim_bonus_wait'
										), false
									);
				while($iblokWait = $dbiblokWait->Fetch())
				{
					$iblokWaitId = $iblokWait["ID"];
				}
		return $iblokWaitId;
	}
	//Opredelyaem ID ibfobloka s repostami
	public static function IblokRepostsId()
	{
		CModule::IncludeModule("iblock");
				$dbiblokReposts = CIBlock::GetList(
										Array(), 
										Array(
											'ACTIVE'=>'Y', 
											"CODE"=>'logictim_bonus_reposts'
										), false
									);
				while($iblokReposts = $dbiblokReposts->Fetch())
				{
					$iblokRepostsId = $iblokReposts["ID"];
				}
		return $iblokRepostsId;
	}
	//Opredelyaem ID infobloka s perehodami
	public static function IblokLinksId()
	{
		CModule::IncludeModule("iblock");
				$dbiblokLinks = CIBlock::GetList(
										Array(), 
										Array(
											'ACTIVE'=>'Y', 
											"CODE"=>'logictim_bonus_links'
										), false
									);
				while($iblokLinks = $dbiblokLinks->Fetch())
				{
					$iblokLinksId = $iblokLinks["ID"];
				}
		return $iblokLinksId;
	}
	//Opredelyaem ID infobloka s referalami
	public static function IblokLReferalsId()
	{
		CModule::IncludeModule("iblock");
				$dbiblokReferals = CIBlock::GetList(
										Array(), 
										Array(
											'ACTIVE'=>'Y', 
											"CODE"=>'logictim_bonus_referals'
										), false
									);
				while($iblokReferals = $dbiblokReferals->Fetch())
				{
					$iblokReferalsId = $iblokReferals["ID"];
				}
		return $iblokReferalsId;
	}
	//Poluchaem vozmojnie znacheniya svoystava "OPERATION_TYPE"
	public static function OperationsType()
	{
		CModule::IncludeModule("iblock");
			$operationsType = array();
			$iblokOperationsId = cHelper::IblokOperationsId();
			$property_enums = CIBlockPropertyEnum::GetList(Array("DEF"=>"DESC", "SORT"=>"ASC"), Array("IBLOCK_ID"=>$iblokOperationsId, "CODE"=>"OPERATION_TYPE"));
			while($enum_fields = $property_enums->GetNext())
			{
				$operationsType[$enum_fields["XML_ID"]] = $enum_fields["ID"];
			}
			return $operationsType;
	}
	//Poluchaem vozmojnie znacheniya svoystava "OPERATION_TYPE" ibfobloka s operaciyami ojidaniya
	public static function OperationsTypeWait()
	{
		CModule::IncludeModule("iblock");
			$operationsType = array();
			$iblokWaitId = cHelper::IblokWaitId();
			$property_enums = CIBlockPropertyEnum::GetList(Array("DEF"=>"DESC", "SORT"=>"ASC"), Array("IBLOCK_ID"=>$iblokWaitId, "CODE"=>"OPERATION_TYPE"));
			while($enum_fields = $property_enums->GetNext())
			{
				$operationsType[$enum_fields["XML_ID"]] = $enum_fields["ID"];
			}
			return $operationsType;
	}
	//Poluchaem nazvaniya operaciy iz infobloka
	public static function OperationsName()
	{
		$operationsName = array();
		$iblokOperationsId = cHelper::IblokOperationsId();
		$property_enums = CIBlockPropertyEnum::GetList(Array("DEF"=>"DESC", "SORT"=>"ASC"), Array("IBLOCK_ID"=>$iblokOperationsId, "CODE"=>"OPERATION_TYPE"));
		while($enum_fields = $property_enums->GetNext())
		{
			$operationsName[$enum_fields["XML_ID"]] = $enum_fields["VALUE"];
		}
		return $operationsName;
	}
	//Poluchaem vozmojnie znacheniya svoystava "LIVE_STATUS"
	public static function LiveStatus()
	{
		CModule::IncludeModule("iblock");
		$iblokOperationsId = cHelper::IblokOperationsId();
		$operationsStatus = array();
		$status_enums = CIBlockPropertyEnum::GetList(Array("DEF"=>"DESC", "SORT"=>"ASC"), Array("IBLOCK_ID"=>$iblokOperationsId, "CODE"=>"LIVE_STATUS"));
		while($status_fields = $status_enums->GetNext())
		{
			$operationsStatus[$status_fields["XML_ID"]] = $status_fields["ID"];
		}
		return $operationsStatus;
	}
	//Poluchaem vozmojnie znacheniya svoystava "SOCIAL_NETWORK"
	public static function SocalNetworks()
	{
		$iblokRepostsId = cHelper::IblokRepostsId();
		CModule::IncludeModule("iblock");
			$SocalNetworks = array();
			$property_enums = CIBlockPropertyEnum::GetList(Array("DEF"=>"DESC", "SORT"=>"ASC"), Array("IBLOCK_ID"=>$iblokRepostsId, "CODE"=>"SOCIAL_NETWORK"));
			while($enum_fields = $property_enums->GetNext())
			{
				$SocalNetworks[$enum_fields["XML_ID"]] = $enum_fields["ID"];
			}
			return $SocalNetworks;
	}
	//Poluchaem vozmojnie znacheniya svoystava "POST_STATUS"
	public static function PostStatuses()
	{
		$iblokRepostsId = cHelper::IblokRepostsId();
		CModule::IncludeModule("iblock");
			$PostStatuses = array();
			$property_enums = CIBlockPropertyEnum::GetList(Array("DEF"=>"DESC", "SORT"=>"ASC"), Array("IBLOCK_ID"=>$iblokRepostsId, "CODE"=>"POST_STATUS"));
			while($enum_fields = $property_enums->GetNext())
			{
				$PostStatuses[$enum_fields["XML_ID"]] = $enum_fields["ID"];
			}
			return $PostStatuses;
	}
	//Polushaem spisok id saitov
	public static function SitesId()
	{
		$sites = array();
		$rsSites = CSite::GetList($by="sort", $order="desc", array());
		while($arSite = $rsSites->Fetch())
		{
		  $sites[] = $arSite["ID"];
		}
		return $sites;
	}
	
	//Poluchaem partnerov po id referala
	public static function GetPartnerId($order_user_id)
	{
		CModule::IncludeModule("iblock");
		
		$partnerId = 0;
		
		$dbPartners = CIBlockElement::GetList(array("ID"=>"ASC"), array("IBLOCK_CODE" => 'logictim_bonus_referals', "PROPERTY_REFERAL" => $order_user_id), false, array("nPageSize"=>1), array("ID", "NAME", "PROPERTY_PARTNER", "PROPERTY_REFERAL"));
		
		while($obPartners = $dbPartners->GetNextElement())
		{
			$dbPartner = $obPartners->GetFields();
			if($dbPartner["PROPERTY_PARTNER_VALUE"] > 0)
				$partnerId = $dbPartner["PROPERTY_PARTNER_VALUE"];
		}
		
		/*if($order_user_id > 0 && (int)COption::GetOptionString("logictim.balls", "REFERAL_SYSTEM_TYPE", 0) > 0)
		{
			
			while($obPartners = $dbPartners->GetNextElement())
			{
				$dbPartner = $obPartners->GetFields();
				if($dbPartner["PROPERTY_PARTNER_VALUE"] > 0)
				{
					$arPartner["PARTNER_ID"] = $dbPartner["PROPERTY_PARTNER_VALUE"];
					$arPartner["ACCESS"] = cHelper::UserBonusSystemDostup($dbPartner["PROPERTY_PARTNER_VALUE"]);
					$arPartners[] = $arPartner;
				}
			}
		}*/
		return $partnerId;
	}
}
?>