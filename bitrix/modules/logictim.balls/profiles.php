<?
namespace Logictim\Balls;

class Profiles {

	public function getProfiles($arParams = array(
													"LIMIT" => 0, 
													"SORT_FIELD_1" => 'sort',
													"SORT_ORDER_1" => 'ASC',
													"SORT_FIELD_1" => 'id',
													"SORT_ORDER_2" => 'ASC',
													"PROFILE_TYPE" => '', 
													"SITE_ID" => '', 
													"USER_GROUPS" => array(), 
													"ORDER" => array()
												)
								)
	{
		/* check license */
		$resultCheck = \Logictim\Balls\Profiles::CheckLicense();
		if($resultCheck['BLOCK_MODULE'] == 'Y')
			return;
		/* check license */
		
		global $DB;
		
		$nowTime = time();
		
		$arProfiles = array();
		
		$where = '';
		if($arParams["PROFILE_TYPE"] != '')
			$where .= ' AND type="'.$arParams["PROFILE_TYPE"].'"';
		
		$limit = '';
		if($arParams["LIMIT"] > 0)
			$limit = ' limit '.$arParams["LIMIT"];
			
		
		$arParams["SORT_FIELD_1"] = $arParams["SORT_FIELD_1"] != '' ? $arParams["SORT_FIELD_1"] : 'sort';
		$arParams["SORT_ORDER_1"] = $arParams["SORT_ORDER_1"] != '' ? $arParams["SORT_ORDER_1"] : 'ASC';
		$arParams["SORT_FIELD_2"] = $arParams["SORT_FIELD_2"] != '' ? $arParams["SORT_FIELD_1"] : 'id';
		$arParams["SORT_ORDER_2"] = $arParams["SORT_ORDER_2"] != '' ? $arParams["SORT_ORDER_1"] : 'ASC';
		$sort = 'ORDER BY '.$arParams["SORT_FIELD_1"].' '.$arParams["SORT_ORDER_1"].', '.$arParams["SORT_FIELD_2"].' '.$arParams["SORT_ORDER_2"];
		
		$rsData = $DB->Query('SELECT * FROM logictim_balls_profiles 
								WHERE 
								active="Y" 
								'.$where.$sort.$limit.';', false, $err_mess.__LINE__);
		
		while($arProfile = $rsData->Fetch())
		{
			//check active_from
			if($arProfile["active_from"] > 0)
			{
				if($nowTime < strtotime($arProfile["active_from"]))
					continue;
			}
			//check active_to
			if($arProfile["active_to"] > 0)
			{
				if($nowTime > strtotime($arProfile["active_to"]))
					continue;
			}
			
			$arOptions = unserialize($arProfile["other_conditions"]);
			
			//check profile_conditions
			$strProfileConditions = json_encode($arProfile["profile_conditions"]);
			$arProfileConditions = unserialize(json_decode($strProfileConditions, true));
			$arProfile["PROFILE_CONDITIONS"] = $arProfileConditions['children'];
			$profileWork = 'Y';
			$ViewInCatalog = 'Y';
			if(!empty($arProfile["PROFILE_CONDITIONS"]))
			{
				foreach($arProfile["PROFILE_CONDITIONS"] as $arCondition):
					$condType = $arCondition["controlId"];
					$condValue = $arCondition["values"]["value"];
					$logic = $arCondition["values"]["logic"];
					
					//echo '<pre>'; print_r($arParams); echo '</pre>';
					//echo '<pre>'; print_r($arCondition); echo '</pre>';
					switch($condType)
					{
						case 'sites':
							if(empty($condValue) && $arParams["SITE_ID"] != '' || !isset($arParams["SITE_ID"]))
								continue;
							if(!in_array($arParams["SITE_ID"], $condValue) && $logic == 'Equal')
								$profileWork = 'N';
							if(in_array($arParams["SITE_ID"], $condValue) && $logic == 'Not')
								$profileWork = 'N';
						break;
						
						case 'userGroups':
							if(empty($condValue) && empty($arParams["USER_GROUPS"]) || !isset($arParams["USER_GROUPS"]))
								continue;
							$userGroupHave = 'N';
							foreach($condValue as $val):
								if(in_array($val, $arParams["USER_GROUPS"]))
									$userGroupHave = 'Y';
							endforeach;
							if($logic == 'Equal' && $userGroupHave == 'N')
								$profileWork = 'N';
							if($logic == 'Not' && $userGroupHave == 'Y')
								$profileWork = 'N';
						break;
						
						case 'PartnerGroups':
							if(empty($condValue) && empty($arParams["PARTNER"]["PARTNER_ID"]) || !isset($arParams["PARTNER"]["PARTNER_ID"]))
								continue;
							$partnerGroups = \CUser::GetUserGroup($arParams["PARTNER"]["PARTNER_ID"]);
							$userGroupHave = 'N';
							foreach($condValue as $val):
								if(in_array($val, $partnerGroups))
									$userGroupHave = 'Y';
							endforeach;
							if($logic == 'Equal' && $userGroupHave == 'N')
								$profileWork = 'N';
							if($logic == 'Not' && $userGroupHave == 'Y')
								$profileWork = 'N';
						break;
						
						case 'MainUserId':
							
							if(empty($condValue) || !is_int((int)($arParams["USER_ID"])))
								continue;
								
							if($arOptions["always_view"] != 'Y')
								$ViewInCatalog = 'N';
							/*if(!empty($arParams["IGNORE_COND_TYPES"]))
							{
								if(in_array('ALL', $arParams["IGNORE_COND_TYPES"]) || in_array('MainUserId', $arParams["IGNORE_COND_TYPES"]))
									continue;
							}*/
							if($arParams["TYPE"] == 'catalog' && $arOptions["always_view"] != 'Y')
								$profileWork = 'N';
							
							if($logic == 'Equal' && !in_array($arParams["USER_ID"], $condValue))
								$profileWork = 'N';
							if($logic == 'Not' && in_array($arParams["USER_ID"], $condValue))
								$profileWork = 'N';
							
						break;
						
						case 'PartnerUserId':
							
							if(empty($condValue) || !is_int((int)($arParams["PARTNER"]["PARTNER_ID"])))
								continue;
								
							if($arOptions["always_view"] != 'Y')
								$ViewInCatalog = 'N';
							/*if(!empty($arParams["IGNORE_COND_TYPES"]))
							{
								if(in_array('ALL', $arParams["IGNORE_COND_TYPES"]) || in_array('PartnerUserId', $arParams["IGNORE_COND_TYPES"]))
									continue;
							}*/
							if($arParams["TYPE"] == 'catalog' && $arOptions["always_view"] != 'Y')
								$profileWork = 'N';
							
							if($logic == 'Equal' && !in_array($arParams["PARTNER"]["PARTNER_ID"], $condValue))
								$profileWork = 'N';
							if($logic == 'Not' && in_array($arParams["PARTNER"]["PARTNER_ID"], $condValue))
								$profileWork = 'N';
							
						break;
						
						case 'PartnerLevel':
							if(empty($condValue) || !isset($arParams["PARTNER"]["PARTNER_LEVEL"]))
								continue;
								
							if(!empty($arParams["IGNORE_COND_TYPES"]))
							{
								if(in_array('ALL', $arParams["IGNORE_COND_TYPES"]) || in_array('PartnerLevel', $arParams["IGNORE_COND_TYPES"]))
									continue;
							}
								
							if($logic == 'Equal' && !in_array($arParams["PARTNER"]["PARTNER_LEVEL"], $condValue))
								$profileWork = 'N';
							if($logic == 'Not' && in_array($arParams["PARTNER"]["PARTNER_LEVEL"], $condValue))
								$profileWork = 'N';
						break;
						
						case 'HaveReferals':
							if(empty($logic))
								continue;
							if(!empty($arParams["IGNORE_COND_TYPES"]))
							{
								if(in_array('ALL', $arParams["IGNORE_COND_TYPES"]) || in_array('HaveReferals', $arParams["IGNORE_COND_TYPES"]))
									continue;
							}
								
							$arReferals = \LBReferalsApi::GetReferalsList($arParams["PARTNER"]["PARTNER_ID"], 1);
							if($logic == 'Equal' && empty($arReferals))
								$profileWork = 'N';
							if($logic == 'Not' && !empty($arReferals))
								$profileWork = 'N';
						break;
						
						case 'personTypes':
							if($arOptions["always_view"] == 'Y')
								$ViewInCatalog = 'Y';
							else
								$ViewInCatalog = 'N';
							
							if(empty($condValue))
								continue;
							if(!empty($arParams["IGNORE_COND_TYPES"]))
							{
								if(in_array('ALL', $arParams["IGNORE_COND_TYPES"]) || in_array('personTypes', $arParams["IGNORE_COND_TYPES"]))
									continue;
							}
							
							if(!empty($arParams["ORDER"]["PERSON_TYPE_ID"]))
							{
								if($logic == 'Equal' && !in_array($arParams["ORDER"]["PERSON_TYPE_ID"], $condValue))
									$profileWork = 'N';
								if($logic == 'Not' && in_array($arParams["ORDER"]["PERSON_TYPE_ID"], $condValue))
									$profileWork = 'N';
							}
							else
							{
								if($arOptions["always_view"] != 'Y')
									$profileWork = 'N';
							}
								
							
						break;
						
						case 'basketRules':
							$ViewInCatalog = 'N';
							if(empty($condValue))
								continue;
							if(!empty($arParams["IGNORE_COND_TYPES"]))
							{
								if(in_array('ALL', $arParams["IGNORE_COND_TYPES"]) || in_array('basketRules', $arParams["IGNORE_COND_TYPES"]))
									continue;
							}
							
							if($arOptions["always_view"] == 'Y' && $arParams["TYPE"] == 'catalog' && empty($arParams["ORDER_DICOUNTS"]["DISCOUNTS"]))
								$ViewInCatalog = 'Y';

							if($arParams["TYPE"] == 'cart')
							{
								$discountUse = 'N';
								if(!empty($arParams["ORDER_DICOUNTS"]["DISCOUNTS"]))
								{
									foreach($arParams["ORDER_DICOUNTS"]["DISCOUNTS"] as $arDiscount):
										if(in_array($arDiscount["ID"], $condValue))
											$discountUse = 'Y';
									endforeach;
								}
								if($logic == 'Equal' && $discountUse == 'N')
									$profileWork = 'N';
								if($logic == 'Not' && $discountUse == 'Y')
									$profileWork = 'N';
							}
							
						break;
						
						case 'paySystems':
							if($arOptions["always_view"] == 'Y')
								$ViewInCatalog = 'Y';
							else
								$ViewInCatalog = 'N';
							
							if(empty($condValue))
								continue;
							if(!empty($arParams["IGNORE_COND_TYPES"]))
							{
								if(in_array('ALL', $arParams["IGNORE_COND_TYPES"]) || in_array('paySystems', $arParams["IGNORE_COND_TYPES"]))
									continue;
							}
							
							
							if(!empty($arParams["ORDER"]["PAYMENTS"]))
							{
								$paySystemHave = 'N';
								foreach($arParams["ORDER"]["PAYMENTS"] as $payment):
									if(in_array($payment["ID"], $condValue))
										$paySystemHave = 'Y';
								endforeach;
								if($logic == 'Equal' && $paySystemHave == 'N')
									$profileWork = 'N';
								if($logic == 'Not' && $paySystemHave == 'Y')
									$profileWork = 'N';
							}
							else
							{
								if($arOptions["always_view"] != 'Y')
									$profileWork = 'N';
							}
								
						break;
						
						case 'delivery':
							if($arOptions["always_view"] == 'Y')
								$ViewInCatalog = 'Y';
							else
								$ViewInCatalog = 'N';
							if(empty($condValue))
								continue;
							if(!empty($arParams["IGNORE_COND_TYPES"]))
							{
								if(in_array('ALL', $arParams["IGNORE_COND_TYPES"]) || in_array('delivery', $arParams["IGNORE_COND_TYPES"]))
									continue;
							}
							
							if(!empty($arParams["ORDER"]["DELIVERY"]))
							{
								$deliveryHave = 'N';
								foreach($arParams["ORDER"]["DELIVERY"] as $delivery):
									if(in_array($delivery["ID"], $condValue))
										$deliveryHave = 'Y';
								endforeach;
								if($logic == 'Equal' && $deliveryHave == 'N')
									$profileWork = 'N';
								if($logic == 'Not' && $deliveryHave == 'Y')
									$profileWork = 'N';
							}
							else
							{
								if($arOptions["always_view"] != 'Y')
									$profileWork = 'N';
							}
								
							
						break;
						
						case 'cartSum':
							$ViewInCatalog = 'N';
							if(empty($condValue))
								continue;
							if(!empty($arParams["IGNORE_COND_TYPES"]))
							{
								if(in_array('ALL', $arParams["IGNORE_COND_TYPES"]) || in_array('cartSum', $arParams["IGNORE_COND_TYPES"]))
									continue;
							}
							
							if($arOptions["always_view"] == 'Y' && $arParams["TYPE"] == 'catalog' && empty($arParams["ORDER"]["CART_SUM"]))
							{
								$ViewInCatalog = 'Y';
								$basket = \Bitrix\Sale\Basket::loadItemsForFUser(\Bitrix\Sale\Fuser::getId(), \Bitrix\Main\Context::getCurrent()->getSite());
								$cartSum = $basket->getPrice();
								$arParams["ORDER"]["CART_SUM"] = $cartSum;
							}
							
							if(!isset($arParams["ORDER"]["CART_SUM"]))
								$profileWork = 'N';
								
							if($logic == 'EqGr' && $arParams["ORDER"]["CART_SUM"] < $condValue)
								$profileWork = 'N';
							if($logic == 'Less' && $arParams["ORDER"]["CART_SUM"] >= $condValue)
								$profileWork = 'N';
							
								
						break;
						
						case 'orderSum':
							$ViewInCatalog = 'N';
							if(empty($condValue))
								continue;
							if(!empty($arParams["IGNORE_COND_TYPES"]))
							{
								if(in_array('ALL', $arParams["IGNORE_COND_TYPES"]) || in_array('orderSum', $arParams["IGNORE_COND_TYPES"]))
									continue;
							}
							
							if($arOptions["always_view"] == 'Y' && $arParams["TYPE"] == 'catalog' && empty($arParams["ORDER"]["CART_SUM"]))
							{
								$ViewInCatalog = 'Y';
								$basket = \Bitrix\Sale\Basket::loadItemsForFUser(\Bitrix\Sale\Fuser::getId(), \Bitrix\Main\Context::getCurrent()->getSite());
								$cartSum = $basket->getPrice();
								$arParams["ORDER"]["CART_SUM"] = $cartSum;
							}
							
							if(empty($arParams["ORDER"]["ORDER_SUM"]) && $arParams["ORDER"]["CART_SUM"] > 0)
								$arParams["ORDER"]["ORDER_SUM"] = $arParams["ORDER"]["CART_SUM"];
							if(empty($arParams["ORDER"]["ORDER_SUM"]))
								$profileWork = 'N';
								
							if($logic == 'EqGr' && $arParams["ORDER"]["ORDER_SUM"] < $condValue)
								$profileWork = 'N';
							if($logic == 'Less' && $arParams["ORDER"]["ORDER_SUM"] >= $condValue)
								$profileWork = 'N';
						break;
						
						case 'ordersSum':
							if($arOptions["always_view"] != 'Y')
								$ViewInCatalog = 'N';
							
							if(!empty($arParams["IGNORE_COND_TYPES"]))
							{
								if(in_array('ALL', $arParams["IGNORE_COND_TYPES"]) || in_array('ordersSum', $arParams["IGNORE_COND_TYPES"]))
									continue;
							}
							if($arParams["TYPE"] == 'catalog' && $arOptions["always_view"] != 'Y')
								$profileWork = 'N';
							
							if(empty($arParams["USER_ID"]))
								$ordersSum = 0;
							else
							{
								$ordersSum = \Logictim\Balls\Helpers::UserOrdersSum($arParams, $arCondition);
							}
							
							if($logic == 'EqGr' && $ordersSum < $arCondition["values"]["ordersSum"])
								$profileWork = 'N';
							if($logic == 'Less' && $ordersSum >= $arCondition["values"]["ordersSum"])
								$profileWork = 'N';
								
						break;
						
						case 'firstOrderDate':
							if($arOptions["always_view"] != 'Y')
								$ViewInCatalog = 'N';
							
							if(!empty($arParams["IGNORE_COND_TYPES"]))
							{
								if(in_array('ALL', $arParams["IGNORE_COND_TYPES"]) || in_array('firstOrderDate', $arParams["IGNORE_COND_TYPES"]))
									continue;
							}
							if($arParams["TYPE"] == 'catalog' && $arOptions["always_view"] != 'Y')
								$profileWork = 'N';
							
							if(empty($arParams["USER_ID"]))
								$objDateInsert = false;
							else
							{
								$objDateInsert = \Logictim\Balls\Helpers::UserFirstOrderDate($arParams, $arCondition);
							}
							if($objDateInsert != false)
							{
								if($arCondition["values"]["period_type"] == 'Y')
									$objDateInsertCheck = $objDateInsert->add($arCondition["values"]["period"].' years');
								if($arCondition["values"]["period_type"] == 'M')
									$objDateInsertCheck = $objDateInsert->add($arCondition["values"]["period"].' months');
								if($arCondition["values"]["period_type"] == 'D')
									$objDateInsertCheck = $objDateInsert->add($arCondition["values"]["period"].' days');
								
								$checkTimeStamp = $objDateInsertCheck->getTimestamp();
								
								if($nowTime > $checkTimeStamp)
									$profileWork = 'N';
							}
						break;
						
						case 'registrationDate':
							if($arOptions["always_view"] != 'Y')
								$ViewInCatalog = 'N';
							
							if(!empty($arParams["IGNORE_COND_TYPES"]))
							{
								if(in_array('ALL', $arParams["IGNORE_COND_TYPES"]) || in_array('firstOrderDate', $arParams["IGNORE_COND_TYPES"]))
									continue;
							}
							if($arParams["TYPE"] == 'catalog' && $arOptions["always_view"] != 'Y')
								$profileWork = 'N';
							
							if(empty($arParams["USER_ID"]))
								$objDateRegister = false;
							else
							{
								$objDateRegister = \Logictim\Balls\Helpers::UserRegistrationDate($arParams, $arCondition);
							}
							if($objDateRegister != false)
							{
								if($arCondition["values"]["period_type"] == 'Y')
									$objDateRegisterCheck = $objDateRegister->add($arCondition["values"]["period"].' years');
								if($arCondition["values"]["period_type"] == 'M')
									$objDateRegisterCheck = $objDateRegister->add($arCondition["values"]["period"].' months');
								if($arCondition["values"]["period_type"] == 'D')
									$objDateRegisterCheck = $objDateRegister->add($arCondition["values"]["period"].' days');
								
								$checkRegisterTimeStamp = $objDateRegisterCheck->getTimestamp();
								
								if($nowTime > $checkRegisterTimeStamp)
									$profileWork = 'N';
							}
							
						break;
						
						case 'orderRowNum':
							if($arOptions["always_view"] != 'Y')
								$ViewInCatalog = 'N';
								
							if(!empty($arParams["IGNORE_COND_TYPES"]))
							{
								if(in_array('ALL', $arParams["IGNORE_COND_TYPES"]) || in_array('orderRowNum', $arParams["IGNORE_COND_TYPES"]))
									continue;
							}
							
							if($arParams["TYPE"] == 'catalog' && $arOptions["always_view"] != 'Y')
								$profileWork = 'N';
								
							if(empty($arParams["USER_ID"]))
								$ordersCount = 0;
							else
							{
								$ordersCount = \Logictim\Balls\Helpers::UserOrdersCount($arParams, $arCondition);
							}
							if($arParams["ORDER"]["ORDER_ID"] > 0)
								$ordersCount;
							else
								$ordersCount = $ordersCount + 1;
								
							if($arCondition["values"]["logic"] == 'Evry')
							{
								if($ordersCount % (int)$arCondition["values"]["ordersCount"] == 0)
									$profileWork = 'Y';
								else
									$profileWork = 'N';
									
							}
							if($arCondition["values"]["logic"] == 'Only')
							{
								
								if($ordersCount != (int)$arCondition["values"]["ordersCount"])
									$profileWork = 'N';
							}
														
						break;
						
						case 'pay_bonus':
							if($arParams["TYPE"] == 'catalog')
								continue;
								
							$payBonus = 0;
							if($arParams["ORDER"]["ORDER_ID"] > 0)
							{
								$iblokOperationsId = \Logictim\Balls\Helpers::IblokOperationsId();
								$operationsType = \Logictim\Balls\Helpers::OperationsType();
								$dbOperations = \CIBlockElement::GetList(array("ID" => "DESC"), array("IBLOCK_ID"=>$iblokOperationsId, "PROPERTY_ORDER_ID" => $arParams["ORDER"]["ORDER_ID"],  "PROPERTY_OPERATION_TYPE" => array($operationsType['MINUS_FROM_ORDER'], $operationsType['BACK_FROM_CANCEL'], $operationsType['BACK_FROM_DELETTE'], $operationsType['DEACIVATE_FROM_DATE'])), false, array("nPageSize"=>1));
								while($Op = $dbOperations->GetNextElement())
								{
									 $OperationFields = $Op->GetFields();
									 $operationProps = $Op->GetProperties();
									 $lastOperationType = $operationProps["OPERATION_TYPE"]["VALUE_XML_ID"];
									 $operationSum = $operationProps["OPERATION_SUM"]["VALUE"];
									 
									 if($lastOperationType == 'MINUS_FROM_ORDER')
									 	$payBonus = $operationSum;
								}
								
							}
							//dlya sale order ajax
							elseif($arParams["ORDER"]["PAY_BONUS"] > 0)
							{
								$payBonus = $arParams["ORDER"]["PAY_BONUS"];
							}
							
							
							if($logic == 'Equal' && $payBonus <= 0)
								$profileWork = 'N';
							if($logic == 'Not' && $payBonus > 0)
								$profileWork = 'N';
								
						break;
					}
				endforeach;
			}
			
			if($profileWork != "N")
			{
				$strProductConditions = json_encode($arProfile["conditions"]);
				$arProductConditions = unserialize(json_decode($strProductConditions, true));
				$arProfile["VIEW_IN_CATALOG"] = $ViewInCatalog;
				$arProfile["PRODUCT_CONDITIONS"] = $arProductConditions['children'];
				$arProfiles[$arProfile['id']] = $arProfile;
			}
		}
		//echo '<pre>'; print_r($arProfiles); echo '</pre>';
		return $arProfiles;
	}
	
	function CheckLicense()
	{
		$needRequest = 'N';
		$lastCheck = \COption::GetOptionString("logictim.balls", "LAST_LICENSE_CHECK", 0);
		$checkTimeHour = (time() - $lastCheck)/3600;
		if($checkTimeHour > 24 || $checkTimeHour <= 0)
			$needRequest = 'Y';
		
		if($needRequest == 'Y'):
			$params = array(
						"MODULE_ID" => 'logictim.balls',
						"LICENSE_KEY_HASH" => md5(LICENSE_KEY),
						"DOMEN" => $_SERVER['SERVER_NAME'],
						"IP" => $_SERVER['SERVER_ADDR'],
						"SHIFR" => 'logictimCheckInfo'
						);
			$url = 'https://logictim.ru/marketplace/protection/request.php';
			if(fopen($url, "r")):
				$result = file_get_contents($url, false, stream_context_create(array(
					'http' => array(
						'method'  => 'POST',
						'header'  => 'Content-type: application/x-www-form-urlencoded',
						'content' => http_build_query($params),
						'timeout' => (float)1
					)
				)));
			endif;
			
			\COption::SetOptionString("logictim.balls", "LAST_LICENSE_CHECK", time());
			
			$arResult = json_decode($result, true);
			
			if(!empty($arResult))
			{
				if(isset($arResult['WARNING']) && $arResult['WARNING'] != '')
					\COption::SetOptionString("logictim.balls", "LICENSE_CHECK_WARNING", $arResult['WARNING']);
				if(\COption::GetOptionString("logictim.balls", "LICENSE_CHECK_WARNING", '') != '' && $arResult['WARNING'] == '')
					\COption::RemoveOption("logictim.balls", "LICENSE_CHECK_WARNING");
				if(isset($arResult['BLOCK_MODULE']) && $arResult['BLOCK_MODULE'] == 'Y')
					\COption::SetOptionString("main", "LTB_BLOCK_MODULE", 'Y');
				if(\COption::GetOptionString("main", "LTB_BLOCK_MODULE", '') == 'Y' && $arResult['BLOCK_MODULE'] != 'Y')
					\COption::RemoveOption("main", "LTB_BLOCK_MODULE");
			}
		endif;
		
		$result = array();
		if(\COption::GetOptionString("main", "LTB_BLOCK_MODULE", '') == 'Y')
			$result['BLOCK_MODULE'] = 'Y';
		
		return $result;
	}
	
}

?>