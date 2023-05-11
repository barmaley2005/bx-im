<?
namespace Logictim\Balls;

class Helpers {
	
	public static function GetSites()
	{
		$sites = array();
		$rsSites = \CSite::GetList($by="sort", $order="desc", array());
		while($arSite = $rsSites->Fetch())
		{
		  $sites[$arSite["ID"]] = $arSite["NAME"];
		}
		return $sites;
	}
	
	public static function GetUserGroups()
	{
		$userGrups = array();
		$rsGroups = \CGroup::GetList(($by="id"), ($order="asc"), array("ACTIVE"  => "Y"));
		while($arUserGroups = $rsGroups->Fetch()) {
			$userGrups[$arUserGroups["ID"]] = $arUserGroups["NAME"];
		}
		return $userGrups;
	}
	
	public static function GetOrderStatuses()
	{
		$arStatuses = array();
		$dbStatuses = \Bitrix\Sale\Internals\StatusLangTable::getList(array('order' => array('STATUS.SORT'=>'ASC'), 'filter' => array('STATUS.TYPE'=>'O', 'LID'=>LANGUAGE_ID)));
		while($arStatus = $dbStatuses->fetch())
		{
			$arStatuses[$arStatus["STATUS_ID"]] = $arStatus["NAME"];
		}
		
		return $arStatuses;
	}
	
	public static function getBasketRules(){
		$basketRules = array();
		$discountIterator = \Bitrix\Sale\Internals\DiscountTable::getList(array(
			'select' => array("ID", "NAME"),
			'filter' => array('ACTIVE' => 'Y'),
			'order' => array("NAME" => "ASC")
		));
		while ($discount = $discountIterator->fetch()){
			 $basketRules[$discount['ID']] = $discount['NAME'];
		}
		return $basketRules ;
	}
	
	public static function getPaySystems(){
		$paySystems = array();
		$res = \Bitrix\Sale\Internals\PaySystemActionTable::GetList(array('order' => array("NAME" => "ASC")));
		while($row=$res->fetch()){
			$paySystems[]=$row;
		}
		return $paySystems;
	}
	
	public static function getDelivery(){
		$delivery = array();
		$res = \Bitrix\Sale\Delivery\Services\Table::getList(array('order' => array("NAME" => "ASC")));
	   while($del = $res->Fetch()) {
		   $delivery[] = $del;
	   }
		return $delivery;
	}
	
	public static function getPersonTypes(){
		$personTypes = array();
		$res = \CSalePersonType::GetList(array('NAME'=>'ASC'),array(),false,false,array());
		while($type = $res->Fetch()){
			$personTypes[$type['ID']]=$type['NAME'];
		}
		return $personTypes;
	}
	
	public static function getCatalogs(){
		$arCatalogs = array();
		$dbCatalogs = \CIBlock::GetList(array(), array('ACTIVE'=>'Y', ), false);
		//take only torgoviy catalog
		while($arCatalog = $dbCatalogs->Fetch())
		{
			$catDb = \CCatalog::GetByID($arCatalog["ID"]);
			if($catDb)
				$arCatalogs[$catDb["ID"]] = $catDb["NAME"];
		}
		return $arCatalogs;
	}
	
	//Opredelyaem ID ibfobloka s operaciyami
	public static function IblokOperationsId()
	{
		\CModule::IncludeModule("iblock");
		$dbiblokOpertion = \CIBlock::GetList(array(), array('ACTIVE'=>'Y', "CODE"=>'logictim_bonus_operations'), false);
		while($iblokOpertion = $dbiblokOpertion->Fetch())
		{
			$iblokOperationsId = $iblokOpertion["ID"];
		}
		return $iblokOperationsId;
	}
	
	//Opredelyaem ID ibfobloka s operaciyami ojidaniya
	public static function IblokWaitId()
	{
		\CModule::IncludeModule("iblock");
		$dbiblokWait = \CIBlock::GetList(array(), array('ACTIVE'=>'Y', "CODE"=>'logictim_bonus_wait'), false);
		while($iblokWait = $dbiblokWait->Fetch())
		{
			$iblokWaitId = $iblokWait["ID"];
		}
		return $iblokWaitId;
	}
	
	//Poluchaem vozmojnie znacheniya svoystava "OPERATION_TYPE"
	public static function OperationsType()
	{
		\CModule::IncludeModule("iblock");
		$operationsType = array();
		$iblokOperationsId = \Logictim\Balls\Helpers::IblokOperationsId();
		$property_enums = \CIBlockPropertyEnum::GetList(Array("DEF"=>"DESC", "SORT"=>"ASC"), Array("IBLOCK_ID"=>$iblokOperationsId, "CODE"=>"OPERATION_TYPE"));
		while($enum_fields = $property_enums->GetNext())
		{
			$operationsType[$enum_fields["XML_ID"]] = $enum_fields["ID"];
		}
		return $operationsType;
	}
	
	//Poluchaem vozmojnie znacheniya svoystava "OPERATION_TYPE" ibfobloka s operaciyami ojidaniya
	public static function OperationsTypeWait()
	{
		\CModule::IncludeModule("iblock");
		$operationsType = array();
		$iblokWaitId = \Logictim\Balls\Helpers::IblokWaitId();
		$property_enums = \CIBlockPropertyEnum::GetList(Array("DEF"=>"DESC", "SORT"=>"ASC"), Array("IBLOCK_ID"=>$iblokWaitId, "CODE"=>"OPERATION_TYPE"));
		while($enum_fields = $property_enums->GetNext())
		{
			$operationsType[$enum_fields["XML_ID"]] = $enum_fields["ID"];
		}
		return $operationsType;
	}
	
	public static function UserBallance($user_id)
	{
		global $USER;
		if(!$user_id || $user_id == '')
			$user_id = $USER->GetID();
			
		//Esli ispol'zuetsya bonusniy schet modulya
		if(\COption::GetOptionString("logictim.balls", "BONUS_BILL", '1') == 1)
		{
			$arParams["SELECT"] = array("UF_LOGICTIM_BONUS");
			$DBUserBonus = \CUser::GetList(($by="ID"),($order="desc"),array("ID" => $user_id),$arParams);
			if($arUserBonus = $DBUserBonus->Fetch()) 
			{
				$userBonus = $arUserBonus["UF_LOGICTIM_BONUS"];
			}
		}
		//Esli ispol'zuetsya vnutrenniy schet bitrix
		else
		{
			\CModule::IncludeModule("sale");
			//Opredelyaem valyutu
			$currency = \COption::GetOptionString("logictim.balls", "BONUS_CURRENCY", 'RUB');
			
			$dbAccountCurrency = \CSaleUserAccount::GetList(array(), array("USER_ID" => $user_id, "CURRENCY" => $currency), false, false, array());
			while($arAccountCurrency = $dbAccountCurrency->Fetch())
			{
				$userBonus = (float)$arAccountCurrency["CURRENT_BUDGET"];
			}
		}
		
		return $userBonus;
	}
	
	public static function UserOrdersSum($arParams, $arCondition)
	{	
		//SOBITIE DO rascheta summi zakazkov
		$arFields = $arParams;
		$arFields['CUSTOM_ORDERS_SUM'] = 'N';
		$event = new \Bitrix\Main\Event("logictim.balls", "BeforeUserOrdersSum", $arFields);
		$event->send();
		if($event->getResults())
		{
			foreach ($event->getResults() as $eventResult):
				$arFields = $eventResult->getParameters();
			endforeach;
		}
		if($arFields["CUSTOM_ORDERS_SUM"] !== 'N' && $arFields["CUSTOM_ORDERS_SUM"] >= 0)
			return $arFields["CUSTOM_ORDERS_SUM"];
		//SOBITIE DO rascheta summi zakazkov
		
		\CModule::IncludeModule("sale");
		$calculator = new \Bitrix\Sale\Discount\CumulativeCalculator($arParams["USER_ID"], $arParams["SITE_ID"]);
		$sumConfiguration = array(
								'type_sum_period'=> 'relative',
								'sum_period_data' => array('period_value' => $arCondition["values"]["period"], 'period_type' => $arCondition["values"]["period_type"])
								);
		$calculator->setSumConfiguration($sumConfiguration);
		$ordersSum = $calculator->calculate();
		if($arParams["ORDER"]["ORDER_ID"] > 0 && $arParams["EVENT_ORDER_PAID"] == 'Y')
			$ordersSum = $ordersSum - $arParams["ORDER"]["ORDER_SUM"];
		
		return $ordersSum;
	}
	
	public static function UserOrdersCount($arParams, $arCondition)
	{			
		$arFilter = array('USER_ID' => $arParams["USER_ID"], "LID" => $arParams["SITE_ID"]);
		if(isset($arCondition['values']['type_count']) && $arCondition['values']['type_count'] == 'Include')
		{
			if($arCondition['values']['cancell'] == 'Cancell')
				$arFilter['CANCELED'] = 'Y';
			if($arCondition['values']['cancell'] == 'NotCancell')
				$arFilter['CANCELED'] = 'N';
			if($arCondition['values']['paid'] == 'Paid')
				$arFilter['PAYED'] = 'Y';
			if($arCondition['values']['paid'] == 'NotPaid')
				$arFilter['PAYED'] = 'N';
			if($arCondition['values']['order_status'] != '' && $arCondition['values']['order_status'] != 'All')
				$arFilter['STATUS_ID'] = $arCondition['values']['order_status'];
		}
		if(isset($arCondition['values']['type_count']) && $arCondition['values']['type_count'] == 'Exclude')
		{
			if($arCondition['values']['cancell'] == 'Cancell')
				$arFilter['!CANCELED'] = 'Y';
			if($arCondition['values']['cancell'] == 'NotCancell')
				$arFilter['!CANCELED'] = 'N';
			if($arCondition['values']['paid'] == 'Paid')
				$arFilter['!PAYED'] = 'Y';
			if($arCondition['values']['paid'] == 'NotPaid')
				$arFilter['!PAYED'] = 'N';
			if($arCondition['values']['order_status'] != '' && $arCondition['values']['order_status'] != 'All')
				$arFilter['!STATUS_ID'] = $arCondition['values']['order_status'];
		}
		
		\Bitrix\Main\Loader::includeModule('sale');
		$orders = \Bitrix\Sale\Order::getList(array(
                                    'select' => array('CNT'),
                                    'filter' => $arFilter,
                                    'order' => array(),
                                    'runtime' => array(new \Bitrix\Main\Entity\ExpressionField('CNT', 'COUNT(*)'))
			));
			$rows = array();
			if($row = $orders->fetch())
				$ordersCount = $row["CNT"];
			else
				$ordersCount = 0;
			
			return $ordersCount;
	}
	
	public static function UserFirstOrderDate($arParams, $arCondition)
	{
		\Bitrix\Main\Loader::includeModule('sale');
		$order = \Bitrix\Sale\Order::getList(array(
                                    'select' => array('DATE_INSERT'),
                                    'filter' => array('USER_ID' => $arParams["USER_ID"], "LID" => $arParams["SITE_ID"]),
                                    'order' => array('ID'=>'ASC'),
                                    'runtime' => array(),
									'limit' => 1,
									'offset' => $arCondition["values"]["order_num"]-1
								));
		if($row = $order->fetch())
		{
			$objDateInsert = $row["DATE_INSERT"];
		}
		else
			$objDateInsert = false;
					
		return $objDateInsert;
	}
	
	public static function UserRegistrationDate($arParams, $arCondition)
	{
		$DBUser = \CUser::GetList(($by="ID"),($order="desc"),array("ID" => $arParams["USER_ID"]),array());
		if($arUser = $DBUser->Fetch()) 
		{
			$dateRegister = $arUser["DATE_REGISTER"];
			$objDateInsert = new \Bitrix\Main\Type\DateTime($dateRegister);
		}
		else
			$objDateInsert = false;
		
		return $objDateInsert;
	}
	
	public static function CheckVersionModuleSale($testVersion)
	{
		if($infoModulwSale = \CModule::CreateModuleObject('sale'))
		{
			if(\CheckVersion($infoModulwSale->MODULE_VERSION, $testVersion))
				$versionSaleNew = 'Y';
			else
				$versionSaleNew = 'N';
		}
		else
			$versionSaleNew = 'N';
		
		return $versionSaleNew;
	}
	
	public static function FormatBonusString($string, $search, $var)
	{
		if(strpos($string, $search) !== false) {
			$newString = str_replace($search, (string)$var, $string);
		}
		else
			$newString = $string. ' '.$var;
		
		return $newString;
	}
	
	public static function Round($number = 0, $round = 2, $round_method = 'MATH')
	{
		if($round_method == 'MATH')
		{
			$result = round($number, $round);
		}
		if($round_method == 'UP')
		{
			if($round == 0)
				$result = ceil($number);
			if($round == 1)
				$result = ceil($number*10)/10;
			if($round == 2)
				$result = ceil($number*100)/100;
			if($round == 3)
				$result = ceil($number*1000)/1000;
			if($round == 4)
				$result = ceil($number*10000)/10000;
		}
		if($round_method == 'DOWN')
		{
			if($round == 0)
				$result = floor($number);
			if($round == 1)
				$result = floor($number*10)/10;
			if($round == 2)
				$result = floor($number*100)/100;
			if($round == 3)
				$result = floor($number*1000)/1000;
			if($round == 4)
				$result = floor($number*10000)/10000;
		}
		return $result;
	}
	
}



?>