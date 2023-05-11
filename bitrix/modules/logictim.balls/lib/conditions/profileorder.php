<?
namespace Logictim\Balls\Conditions;

class ProfileOrder
{
	public static function MainParams($mode='')
	{
			$arShowParams = array(
						"parentContainer" => 'ProfileConditions',
						"form" => '',
						"formName" => 'logictim_profile',
						"sepID" => '__',
						"prefix" => "profileCond",
						"messTree" => array(
											"SELECT_CONTROL" => GetMessage("logictim.balls_SELECT_COND"),
											"ADD_CONTROL" => GetMessage("logictim.balls_ADD_PROFILE_COND"),
											"DELETE_CONTROL" => GetMessage("logictim.balls_DEL_COND"),
											)
						);
						
			if($mode=='json'){
				return \Bitrix\Main\Web\Json::encode($arShowParams);
			}
			
			return $arShowParams;
	}
	
	public static function BaseConditions($mode='')
	{
		$params = array(
						"id" => '0',
						"controlId" => 'CondGroup',
						"children" => array(
										),
					);
					
		if($mode=='json'){
			return \Bitrix\Main\Web\Json::encode($params);
		}
		return $params;
	}
	
	public static function Controls($mode='', $type = 'order')
	{
		$arSites = \Logictim\Balls\Helpers::GetSites();
		$arUserGroups = \Logictim\Balls\Helpers::GetUserGroups();
		$basketRules = \Logictim\Balls\Helpers::getBasketRules();
		$arPaySystems = array();
		foreach(\Logictim\Balls\Helpers::getPaySystems() as $arPaySystem){
			$arPaySystems[$arPaySystem['ID']] = $arPaySystem['NAME'];
		}
		$arDelivery = array();
		foreach(\Logictim\Balls\Helpers::getDelivery() as $delivery){
			$arDelivery[$delivery['ID']] = $delivery['NAME'];
		}
		$arPersonTypes = \Logictim\Balls\Helpers::getPersonTypes();
		$arOrderStatuses = \Logictim\Balls\Helpers::GetOrderStatuses();
		$arOrderStatuses = array_merge(array('All'=>GetMessage("logictim.balls_COND_COUNT_ORDERS_ALL")), $arOrderStatuses);
		
		$params = array();
		
		$params[]=array(
				'controlId'=> 'CondGroup',
				'group'=> true,
				'label'=> '',
				'defaultText'=> '',
				'showIn'=> array(),
				'control'=> array('CONDITION_PERFORM_OPERATIONS')
			);
		
		if($type == 'order_referal'):
			$levels = array();
			while($x++ < (int)\COption::GetOptionString("logictim.balls", "REFERAL_LEVELS", 1))
			{
				$levels[$x] = $x;
			}
			$params[] = array(
							'controlgroup'=> '1',
							'group'=> true,
							'label'=> GetMessage("logictim.balls_COND_REFERAL_PARAMS"),
							'showIn'=> array('CondGroup'),
							'children'=> array(
			
												array(
														'controlId'=> 'PartnerGroups',
														'group'=> false,
														'label'=> GetMessage("logictim.balls_PARTNER_GROUP"),
														'showIn'=> array('CondGroup'),
														'control'=> array(
																			array('id'=>'prefix', 'type'=>'prefix', 'text'=>GetMessage("logictim.balls_PARTNER_GROUP")),
																			array(
																				'id' => 'logic',
																				'name' => 'logic',
																				'type' => 'select',
																				'values' => array
																								(
																									'Equal' => GetMessage("logictim.balls_COND_EQUAL"),
																									'Not' => GetMessage("logictim.balls_COND_NOT")
																								),
																				'defaultText' => GetMessage("logictim.balls_COND_EQUAL"),
																				'defaultValue' => 'Equal'
																			),
																			array(
																				'type'=> 'select',
																				'multiple'=>'Y',
																				'values'=> $arUserGroups,
																				'id'=> 'value',
																				'name'=> 'value',
																				'show_value'=>'Y',
																				'first_option'=> '...',
																				'defaultText'=> '...',
																				'defaultValue'=> ''
																			)
																		)
													),
												array(
														'controlId'=> 'PartnerLevel',
														'group'=> false,
														'label'=> GetMessage("logictim.balls_PARTNER_LEVEL"),
														'showIn'=> array('CondGroup'),
														'control'=> array(
																			array('id'=>'prefix', 'type'=>'prefix', 'text'=>GetMessage("logictim.balls_PARTNER_LEVEL")),
																			array(
																				'id' => 'logic',
																				'name' => 'logic',
																				'type' => 'select',
																				'values' => array
																								(
																									'Equal' => GetMessage("logictim.balls_COND_EQUAL"),
																									'Not' => GetMessage("logictim.balls_COND_NOT")
																								),
																				'defaultText' => GetMessage("logictim.balls_COND_EQUAL"),
																				'defaultValue' => 'Equal'
																			),
																			array(
																				'type'=> 'select',
																				'multiple'=>'Y',
																				'values'=> $levels,
																				'id'=> 'value',
																				'name'=> 'value',
																				'show_value'=>'N',
																				'first_option'=> '...',
																				'defaultText'=> '...',
																				'defaultValue'=> ''
																			)
																		)
													),
												array(
														'controlId'=> 'userGroups',
														'group'=> false,
														'label'=> GetMessage("logictim.balls_ORDER_USER_GROUP"),
														'showIn'=> array('CondGroup'),
														'control'=> array(
																			array('id'=>'prefix', 'type'=>'prefix', 'text'=>GetMessage("logictim.balls_ORDER_USER_GROUP")),
																			array(
																				'id' => 'logic',
																				'name' => 'logic',
																				'type' => 'select',
																				'values' => array
																								(
																									'Equal' => GetMessage("logictim.balls_COND_EQUAL"),
																									'Not' => GetMessage("logictim.balls_COND_NOT")
																								),
																				'defaultText' => GetMessage("logictim.balls_COND_EQUAL"),
																				'defaultValue' => 'Equal'
																			),
																			array(
																				'type'=> 'select',
																				'multiple'=>'Y',
																				'values'=> $arUserGroups,
																				'id'=> 'value',
																				'name'=> 'value',
																				'show_value'=>'Y',
																				'first_option'=> '...',
																				'defaultText'=> '...',
																				'defaultValue'=> ''
																			)
																		)
													),
													array(
														'controlId'=> 'sites',
														'group'=> false,
														'label'=> GetMessage("logictim.balls_ORDER_SITE"),
														'showIn'=> array('CondGroup'),
														'control'=> array(
																			array('id'=>'prefix', 'type'=>'prefix', 'text'=>GetMessage("logictim.balls_ORDER_SITE")),
																			array(
																				'id' => 'logic',
																				'name' => 'logic',
																				'type' => 'select',
																				'values' => array
																								(
																									'Equal' => GetMessage("logictim.balls_COND_EQUAL"),
																									'Not' => GetMessage("logictim.balls_COND_NOT")
																								),
																				'defaultText' => GetMessage("logictim.balls_COND_EQUAL"),
																				'defaultValue' => 'Equal'
																			),
																			array(
																				'type'=> 'select',
																				'multiple'=>'Y',
																				'values'=> $arSites,
																				'id'=> 'value',
																				'name'=> 'value',
																				'show_value'=>'Y',
																				'first_option'=> '...',
																				'defaultText'=> '...',
																				'defaultValue'=> ''
																			)
																		)
													),
													array(
														'controlId'=> 'pay_bonus',
														'group'=> false,
														'label'=> GetMessage("logictim.balls_COND_PAY_BONUS"),
														'showIn'=> array('CondGroup'),
														'control'=> array(
																			array('id'=>'prefix', 'type'=>'prefix', 'text'=>GetMessage("logictim.balls_COND_PAY_BONUS")),
																			array(
																				'id' => 'logic',
																				'name' => 'logic',
																				'type' => 'select',
																				'values' => array
																								(
																									'Equal' => GetMessage("logictim.balls_COND_HAVE"),
																									'Not' => GetMessage("logictim.balls_COND_HAVE_NO")
																								),
																				'defaultText' => GetMessage("logictim.balls_COND_HAVE_NO"),
																				'defaultValue' => 'Not'
																			),
																		)
												),
													
											),
											
											
							);
							
			$params[] = array(
							'controlgroup'=> '1',
							'group'=> true,
							'label'=> GetMessage("logictim.balls_COND_OTHER_PARAMS"),
							'showIn'=> array('CondGroup'),
							'children'=> array(
												array(
														'controlId'=> 'PartnerUserId',
														'group'=> false,
														'label'=> GetMessage("logictim.balls_COND_PARTNERS_ID"),
														'showIn'=> array('CondGroup'),
														'control'=> array(
																			array('id'=>'prefix', 'type'=>'prefix', 'text'=>GetMessage("logictim.balls_COND_PARTNERS_ID")),
																			array(
																				'id' => 'logic',
																				'name' => 'logic',
																				'type' => 'select',
																				'values' => array
																								(
																									'Equal' => GetMessage("logictim.balls_COND_EQUAL"),
																									'Not' => GetMessage("logictim.balls_COND_NOT")
																								),
																				'defaultText' => GetMessage("logictim.balls_COND_EQUAL"),
																				'defaultValue' => 'Equal'
																			),
																			array(
																				'type' => 'userPopup',
																				'popup_url' => '/bitrix/admin/user_search.php',
																				'popup_params' => array('FN'=>'logictim_profile'),
																				'param_id' => 'n',
																				'show_value'=>'Y',
																				'user_load_url' => '/bitrix/admin/sale_discount_edit.php',
																				'id'=> 'value',
																				'name'=> 'value',
																			),
																		)
													),
												array(
														'controlId'=> 'MainUserId',
														'group'=> false,
														'label'=> GetMessage("logictim.balls_COND_REFERALS_ID"),
														'showIn'=> array('CondGroup'),
														'control'=> array(
																			array('id'=>'prefix', 'type'=>'prefix', 'text'=>GetMessage("logictim.balls_COND_REFERALS_ID")),
																			array(
																				'id' => 'logic',
																				'name' => 'logic',
																				'type' => 'select',
																				'values' => array
																								(
																									'Equal' => GetMessage("logictim.balls_COND_EQUAL"),
																									'Not' => GetMessage("logictim.balls_COND_NOT")
																								),
																				'defaultText' => GetMessage("logictim.balls_COND_EQUAL"),
																				'defaultValue' => 'Equal'
																			),
																			array(
																				'type' => 'userPopup',
																				'popup_url' => '/bitrix/admin/user_search.php',
																				'popup_params' => array('FN'=>'logictim_profile'),
																				'param_id' => 'n',
																				'show_value'=>'Y',
																				'user_load_url' => '/bitrix/admin/sale_discount_edit.php',
																				'id'=> 'value',
																				'name'=> 'value',
																			),
																		)
													),
												array(
														'controlId'=> 'orderRowNum',
														'group'=> false,
														'label'=> GetMessage("logictim.balls_COND_USER_ORDERS_COUNT_ROW").($type == 'order_referal' ? ' '.GetMessage("logictim.balls_COND_USER_REFERALA_POSTFIX") : ''),
														'showIn'=> array('CondGroup'),
														'control'=> array(
																			array('id'=>'prefix', 'type'=>'prefix', 'text'=>GetMessage("logictim.balls_COND_USER_ORDERS_COUNT_ROW_USE")),
																			array(
																				'id' => 'logic',
																				'name' => 'logic',
																				'type' => 'select',
																				'values' => array
																								(
																									'Evry' => GetMessage("logictim.balls_COND_USER_ORDERS_COUNT_ROW_EVRY"),
																									'Only' => GetMessage("logictim.balls_COND_USER_ORDERS_COUNT_ROW_ONLY"),
																								),
																				'defaultText' => GetMessage("logictim.balls_COND_USER_ORDERS_COUNT_ROW_EVRY"),
																				'defaultValue' => 'Evry'
																			),
																			array(
																				'type'=> 'input',
																				'id'=> 'ordersCount',
																				'name'=> 'ordersCount',
																				'show_value'=>'Y',
																				'defaultValue' => '2'
																			),
																			GetMessage("logictim.balls_COND_USER_POSTFIX_IY"),
																			array('id'=>'prefix', 'type'=>'prefix', 'text'=>GetMessage("logictim.balls_COND_USER_ORDERS_COUNT_ROW_ORDER")),
																			'( '.GetMessage("logictim.balls_COND_COUNT_ORDERS_TEXT_1"),
																			array(
																				'id' => 'type_count',
																				'name' => 'type_count',
																				'type' => 'select',
																				'values' => array
																								(
																									'Include' => GetMessage("logictim.balls_COND_COUNT_TYPE_ORDERS_INCLUDE"),
																									'Exclude' => GetMessage("logictim.balls_COND_COUNT_TYPE_ORDERS_NOT_INCLUDE"),
																								),
																				'defaultText' => GetMessage("logictim.balls_COND_COUNT_TYPE_ORDERS_INCLUDE"),
																				'defaultValue' => 'Include'
																			),
																			array(
																				'id' => 'cancell',
																				'name' => 'cancell',
																				'type' => 'select',
																				'values' => array
																								(
																									'All' => GetMessage("logictim.balls_COND_COUNT_ORDERS_ALL"),
																									'Cancell' => GetMessage("logictim.balls_COND_COUNT_ORDERS_CANCELL"),
																									'NotCancell' => GetMessage("logictim.balls_COND_COUNT_ORDERS_NOT_CANCELL"),
																								),
																				'defaultText' => GetMessage("logictim.balls_COND_COUNT_ORDERS_ALL"),
																				'defaultValue' => 'All'
																			),
																			array(
																				'id' => 'paid',
																				'name' => 'paid',
																				'type' => 'select',
																				'values' => array
																								(
																									'All' => GetMessage("logictim.balls_COND_COUNT_ORDERS_ALL"),
																									'Paid' => GetMessage("logictim.balls_COND_COUNT_ORDERS_PAID"),
																									'NotPaid' => GetMessage("logictim.balls_COND_COUNT_ORDERS_NOT_PAID"),
																								),
																				'defaultText' => GetMessage("logictim.balls_COND_COUNT_ORDERS_ALL"),
																				'defaultValue' => 'All'
																			),
																			GetMessage("logictim.balls_COND_COUNT_ORDERS_STATUS"),
																			array(
																				'id' => 'order_status',
																				'name' => 'order_status',
																				'type' => 'select',
																				'values' => $arOrderStatuses,
																				'defaultText' => GetMessage("logictim.balls_COND_COUNT_ORDERS_ALL"),
																				'defaultValue' => 'All'
																			),
																			')'
																		)
													),
												array(
														'controlId'=> 'ordersSum',
														'group'=> false,
														'label'=> GetMessage("logictim.balls_COND_USER_ORDERS_SUM").($type == 'order_referal' ? ' '.GetMessage("logictim.balls_COND_USER_REFERALA_POSTFIX") : ''),
														'showIn'=> array('CondGroup'),
														'control'=> array(
																			array('id'=>'prefix', 'type'=>'prefix', 'text'=>GetMessage("logictim.balls_COND_USER_ORDERS_SUM")),
																			array(
																				'id' => 'logic',
																				'name' => 'logic',
																				'type' => 'select',
																				'values' => array
																								(
																									'EqGr' => GetMessage("logictim.balls_COND_EQGR"),
																									'Less' => GetMessage("logictim.balls_COND_LESS")
																								),
																				'defaultText' => GetMessage("logictim.balls_COND_EQGR"),
																				'defaultValue' => 'EqGr'
																			),
																			array(
																				'type'=> 'input',
																				'id'=> 'ordersSum',
																				'name'=> 'ordersSum',
																				'show_value'=>'Y',
																				'defaultValue' => '0'
																			),
																			array('id'=>'prefix', 'type'=>'prefix', 'text'=>GetMessage("logictim.balls_COND_FOR_PERIOD")),
																			array(
																				'type'=> 'input',
																				'id'=> 'period',
																				'name'=> 'period',
																				'defaultValue' => '1'
																			),
																			array(
																				'type'=> 'select',
																				'id'=> 'period_type',
																				'name'=> 'period_type',
																				'values' => array
																								(
																									'D' => GetMessage("logictim.balls_COND_DAY"),
																									'M' => GetMessage("logictim.balls_COND_MONTH"),
																									'Y' => GetMessage("logictim.balls_COND_YEAR")
																								),
																				'defaultValue' => 'Y',
																				'defaultText' => GetMessage("logictim.balls_COND_YEAR")
																			),
																		)
													),
												array(
														'controlId'=> 'firstOrderDate',
														'group'=> false,
														'label'=> GetMessage("logictim.balls_COND_USER_FIRST_ORDER_DATE").($type == 'order_referal' ? ' '.GetMessage("logictim.balls_COND_USER_REFERALA_POSTFIX") : ''),
														'showIn'=> array('CondGroup'),
														'control'=> array(
																			array('id'=>'prefix', 'type'=>'prefix', 'text'=>GetMessage("logictim.balls_COND_USER_FIRST_ORDER_DATE_USE")),
																			array(
																				'type'=> 'input',
																				'id'=> 'order_num',
																				'name'=> 'order_num',
																				'defaultValue' => '1'
																			),
																			GetMessage("logictim.balls_COND_USER_POSTFIX_GO"),
																			array('id'=>'prefix', 'type'=>'prefix', 'text'=>($type == 'order_referal' ? GetMessage("logictim.balls_COND_USER_FIRST_ORDER_DATE_USE_1_REFERAL") : GetMessage("logictim.balls_COND_USER_FIRST_ORDER_DATE_USE_1"))),
																			array(
																				'type'=> 'input',
																				'id'=> 'period',
																				'name'=> 'period',
																				'defaultValue' => '1'
																			),
																			array(
																				'type'=> 'select',
																				'id'=> 'period_type',
																				'name'=> 'period_type',
																				'values' => array
																								(
																									'D' => GetMessage("logictim.balls_COND_DAY"),
																									'M' => GetMessage("logictim.balls_COND_MONTH"),
																									'Y' => GetMessage("logictim.balls_COND_YEAR")
																								),
																				'defaultValue' => 'Y',
																				'defaultText' => GetMessage("logictim.balls_COND_YEAR")
																			),
																		)
													),
												array(
														'controlId'=> 'registrationDate',
														'group'=> false,
														'label'=> GetMessage("logictim.balls_COND_USER_REGISTRATION_DATE").($type == 'order_referal' ? ' '.GetMessage("logictim.balls_COND_USER_REFERALA_POSTFIX") : ''),
														'showIn'=> array('CondGroup'),
														'control'=> array(
																			array('id'=>'prefix', 'type'=>'prefix', 'text'=>($type == 'order_referal' ? GetMessage("logictim.balls_COND_USER_REGISTRATION_DATE_USE_REFERAL") : GetMessage("logictim.balls_COND_USER_REGISTRATION_DATE_USE"))),
																			array(
																				'type'=> 'input',
																				'id'=> 'period',
																				'name'=> 'period',
																				'defaultValue' => '1'
																			),
																			array(
																				'type'=> 'select',
																				'id'=> 'period_type',
																				'name'=> 'period_type',
																				'values' => array
																								(
																									'D' => GetMessage("logictim.balls_COND_DAY"),
																									'M' => GetMessage("logictim.balls_COND_MONTH"),
																									'Y' => GetMessage("logictim.balls_COND_YEAR")
																								),
																				'defaultValue' => 'Y',
																				'defaultText' => GetMessage("logictim.balls_COND_YEAR")
																			),
																		)
													),
												array(
														'controlId'=> 'cartSum',
														'group'=> false,
														'label'=> GetMessage("logictim.balls_COND_CART_SUM"),
														'showIn'=> array('CondGroup'),
														'control'=> array(
																			array('id'=>'prefix', 'type'=>'prefix', 'text'=>GetMessage("logictim.balls_COND_CART_SUM")),
																			array(
																				'id' => 'logic',
																				'name' => 'logic',
																				'type' => 'select',
																				'values' => array
																								(
																									'EqGr' => GetMessage("logictim.balls_COND_EQGR"),
																									'Less' => GetMessage("logictim.balls_COND_LESS")
																								),
																				'defaultText' => GetMessage("logictim.balls_COND_EQGR"),
																				'defaultValue' => 'EqGr'
																			),
																			array(
																				'type'=> 'input',
																				'id'=> 'value',
																				'name'=> 'value',
																				'show_value'=>'Y',
																				'defaultValue' => '0'
																			)
																		)
													),
												array(
														'controlId'=> 'orderSum',
														'group'=> false,
														'label'=> GetMessage("logictim.balls_COND_ORDER_SUM"),
														'showIn'=> array('CondGroup'),
														'control'=> array(
																			array('id'=>'prefix', 'type'=>'prefix', 'text'=>GetMessage("logictim.balls_COND_ORDER_SUM")),
																			array(
																				'id' => 'logic',
																				'name' => 'logic',
																				'type' => 'select',
																				'values' => array
																								(
																									'EqGr' => GetMessage("logictim.balls_COND_EQGR"),
																									'Less' => GetMessage("logictim.balls_COND_LESS")
																								),
																				'defaultText' => GetMessage("logictim.balls_COND_EQGR"),
																				'defaultValue' => 'EqGr'
																			),
																			array(
																				'type'=> 'input',
																				'id'=> 'value',
																				'name'=> 'value',
																				'show_value'=>'Y',
																				'defaultValue' => '0'
																			)
																		)
													),
												array(
														'controlId'=> 'basketRules',
														'group'=> false,
														'label'=> GetMessage("logictim.balls_COND_BASKET_RULES"),
														'showIn'=> array('CondGroup'),
														'control'=> array(
																		array('id'=>'prefix', 'type'=>'prefix', 'text'=>GetMessage("logictim.balls_COND_BASKET_RULES")),
																		array(
																				'id' => 'logic',
																				'name' => 'logic',
																				'type' => 'select',
																				'values' => array
																								(
																									'Equal' => GetMessage("logictim.balls_COND_EQUAL_USE"),
																									'Not' => GetMessage("logictim.balls_COND_NOT_USE")
																								),
																				'defaultText' => GetMessage("logictim.balls_COND_EQUAL"),
																				'defaultValue' => 'Equal'
																			),
																		array(
																				'type'=> 'select',
																				'multiple'=>'Y',
																				'size'=> 7,
																				'values'=> $basketRules,
																				'show_value'=>'Y',
																				'id'=> 'value',
																				'name'=> 'value',
																			)
																		)
														),
												array(
														'controlId'=> 'paySystems',
														'group'=> false,
														'label'=> GetMessage("logictim.balls_COND_PAY_SYSTEM"),
														'showIn'=> array('CondGroup'),
														'control'=> array(
																		array('id'=>'prefix', 'type'=>'prefix', 'text'=>GetMessage("logictim.balls_COND_PAY_SYSTEM")),
																		array(
																				'id' => 'logic',
																				'name' => 'logic',
																				'type' => 'select',
																				'values' => array
																								(
																									'Equal' => GetMessage("logictim.balls_COND_EQUAL"),
																									'Not' => GetMessage("logictim.balls_COND_NOT")
																								),
																				'defaultText' => GetMessage("logictim.balls_COND_EQUAL"),
																				'defaultValue' => 'Equal'
																			),
																		array(
																				'type'=> 'select',
																				'multiple'=>'Y',
																				'values'=> $arPaySystems,
																				'show_value'=>'Y',
																				'id'=> 'value',
																				'name'=> 'value',
																			)
																		)
														),
												array(
														'controlId'=> 'delivery',
														'group'=> false,
														'label'=> GetMessage("logictim.balls_COND_DELIVERY_SYSTEM"),
														'showIn'=> array('CondGroup'),
														'control'=> array(
																		array('id'=>'prefix', 'type'=>'prefix', 'text'=>GetMessage("logictim.balls_COND_DELIVERY_SYSTEM")),
																		array(
																				'id' => 'logic',
																				'name' => 'logic',
																				'type' => 'select',
																				'values' => array
																								(
																									'Equal' => GetMessage("logictim.balls_COND_EQUAL"),
																									'Not' => GetMessage("logictim.balls_COND_NOT")
																								),
																				'defaultText' => GetMessage("logictim.balls_COND_EQUAL"),
																				'defaultValue' => 'Equal'
																			),
																		array(
																				'type'=> 'select',
																				'multiple'=>'Y',
																				'values'=> $arDelivery,
																				'show_value'=>'Y',
																				'id'=> 'value',
																				'name'=> 'value',
																			)
																		)
														),
												array(
														'controlId'=> 'personTypes',
														'group'=> false,
														'label'=> GetMessage("logictim.balls_COND_PERSON_TYPE"),
														'showIn'=> array('CondGroup'),
														'control'=> array(
																		array('id'=>'prefix', 'type'=>'prefix', 'text'=>GetMessage("logictim.balls_COND_PERSON_TYPE")),
																		array(
																				'id' => 'logic',
																				'name' => 'logic',
																				'type' => 'select',
																				'values' => array
																								(
																									'Equal' => GetMessage("logictim.balls_COND_EQUAL"),
																									'Not' => GetMessage("logictim.balls_COND_NOT")
																								),
																				'defaultText' => GetMessage("logictim.balls_COND_EQUAL"),
																				'defaultValue' => 'Equal'
																			),
																		array(
																				'type'=> 'select',
																				'multiple'=>'Y',
																				'values'=> $arPersonTypes,
																				'show_value'=>'Y',
																				'id'=> 'value',
																				'name'=> 'value',
																			)
																		)
														),
											),
						);	
		
		else:
		
		$params[] = array(
							'controlgroup'=> '1',
							'group'=> true,
							'label'=> GetMessage("logictim.balls_COND_MAIN_PARAMS"),
							'showIn'=> array('CondGroup'),
							'children'=> array(
												array(
														'controlId'=> 'sites',
														'group'=> false,
														'label'=> GetMessage("logictim.balls_COND_SITE"),
														'showIn'=> array('CondGroup'),
														'control'=> array(
																			array('id'=>'prefix', 'type'=>'prefix', 'text'=>GetMessage("logictim.balls_COND_SITE")),
																			array(
																				'id' => 'logic',
																				'name' => 'logic',
																				'type' => 'select',
																				'values' => array
																								(
																									'Equal' => GetMessage("logictim.balls_COND_EQUAL"),
																									'Not' => GetMessage("logictim.balls_COND_NOT")
																								),
																				'defaultText' => GetMessage("logictim.balls_COND_EQUAL"),
																				'defaultValue' => 'Equal'
																			),
																			array(
																				'type'=> 'select',
																				'multiple'=>'Y',
																				'values'=> $arSites,
																				'id'=> 'value',
																				'name'=> 'value',
																				'show_value'=>'Y',
																				'first_option'=> '...',
																				'defaultText'=> '...',
																				'defaultValue'=> ''
																			)
																		)
													),
													array(
														'controlId'=> 'userGroups',
														'group'=> false,
														'label'=> GetMessage("logictim.balls_COND_USER_GROUP"),
														'showIn'=> array('CondGroup'),
														'control'=> array(
																			array('id'=>'prefix', 'type'=>'prefix', 'text'=>GetMessage("logictim.balls_COND_USER_GROUP")),
																			array(
																				'id' => 'logic',
																				'name' => 'logic',
																				'type' => 'select',
																				'values' => array
																								(
																									'Equal' => GetMessage("logictim.balls_COND_EQUAL"),
																									'Not' => GetMessage("logictim.balls_COND_NOT")
																								),
																				'defaultText' => GetMessage("logictim.balls_COND_EQUAL"),
																				'defaultValue' => 'Equal'
																			),
																			array(
																				'type'=> 'select',
																				'multiple'=>'Y',
																				'values'=> $arUserGroups,
																				'id'=> 'value',
																				'name'=> 'value',
																				'show_value'=>'Y',
																				'first_option'=> '...',
																				'defaultText'=> '...',
																				'defaultValue'=> ''
																			)
																		)
													),
													array(
														'controlId'=> 'pay_bonus',
														'group'=> false,
														'label'=> GetMessage("logictim.balls_COND_PAY_BONUS"),
														'showIn'=> array('CondGroup'),
														'control'=> array(
																			array('id'=>'prefix', 'type'=>'prefix', 'text'=>GetMessage("logictim.balls_COND_PAY_BONUS")),
																			array(
																				'id' => 'logic',
																				'name' => 'logic',
																				'type' => 'select',
																				'values' => array
																								(
																									'Equal' => GetMessage("logictim.balls_COND_HAVE"),
																									'Not' => GetMessage("logictim.balls_COND_HAVE_NO")
																								),
																				'defaultText' => GetMessage("logictim.balls_COND_HAVE_NO"),
																				'defaultValue' => 'Not'
																			),
																		)
												),
												
											)
						);
						
			$params[] = array(
							'controlgroup'=> '1',
							'group'=> true,
							'label'=> GetMessage("logictim.balls_COND_OTHER_PARAMS"),
							'showIn'=> array('CondGroup'),
							'children'=> array(
												array(
														'controlId'=> 'MainUserId',
														'group'=> false,
														'label'=> GetMessage("logictim.balls_COND_USERS_ID"),
														'showIn'=> array('CondGroup'),
														'control'=> array(
																			array('id'=>'prefix', 'type'=>'prefix', 'text'=>GetMessage("logictim.balls_COND_USERS_ID")),
																			array(
																				'id' => 'logic',
																				'name' => 'logic',
																				'type' => 'select',
																				'values' => array
																								(
																									'Equal' => GetMessage("logictim.balls_COND_EQUAL"),
																									'Not' => GetMessage("logictim.balls_COND_NOT")
																								),
																				'defaultText' => GetMessage("logictim.balls_COND_EQUAL"),
																				'defaultValue' => 'Equal'
																			),
																			array(
																				'type' => 'userPopup',
																				'popup_url' => '/bitrix/admin/user_search.php',
																				'popup_params' => array('FN'=>'logictim_profile'),
																				'param_id' => 'n',
																				'show_value'=>'Y',
																				'user_load_url' => '/bitrix/admin/sale_discount_edit.php',
																				'id'=> 'value',
																				'name'=> 'value',
																			),
																		)
													),
												array(
														'controlId'=> 'orderRowNum',
														'group'=> false,
														'label'=> GetMessage("logictim.balls_COND_USER_ORDERS_COUNT_ROW").($type == 'order_referal' ? ' '.GetMessage("logictim.balls_COND_USER_REFERALA_POSTFIX") : ''),
														'showIn'=> array('CondGroup'),
														'control'=> array(
																			array('id'=>'prefix', 'type'=>'prefix', 'text'=>GetMessage("logictim.balls_COND_USER_ORDERS_COUNT_ROW_USE")),
																			array(
																				'id' => 'logic',
																				'name' => 'logic',
																				'type' => 'select',
																				'values' => array
																								(
																									'Evry' => GetMessage("logictim.balls_COND_USER_ORDERS_COUNT_ROW_EVRY"),
																									'Only' => GetMessage("logictim.balls_COND_USER_ORDERS_COUNT_ROW_ONLY"),
																								),
																				'defaultText' => GetMessage("logictim.balls_COND_USER_ORDERS_COUNT_ROW_EVRY"),
																				'defaultValue' => 'Evry'
																			),
																			array(
																				'type'=> 'input',
																				'id'=> 'ordersCount',
																				'name'=> 'ordersCount',
																				'show_value'=>'Y',
																				'defaultValue' => '2'
																			),
																			GetMessage("logictim.balls_COND_USER_POSTFIX_IY"),
																			array('id'=>'prefix', 'type'=>'prefix', 'text'=>GetMessage("logictim.balls_COND_USER_ORDERS_COUNT_ROW_ORDER")),
																			'( '.GetMessage("logictim.balls_COND_COUNT_ORDERS_TEXT_1"),
																			array(
																				'id' => 'type_count',
																				'name' => 'type_count',
																				'type' => 'select',
																				'values' => array
																								(
																									'Include' => GetMessage("logictim.balls_COND_COUNT_TYPE_ORDERS_INCLUDE"),
																									'Exclude' => GetMessage("logictim.balls_COND_COUNT_TYPE_ORDERS_NOT_INCLUDE"),
																								),
																				'defaultText' => GetMessage("logictim.balls_COND_COUNT_TYPE_ORDERS_INCLUDE"),
																				'defaultValue' => 'Include'
																			),
																			array(
																				'id' => 'cancell',
																				'name' => 'cancell',
																				'type' => 'select',
																				'values' => array
																								(
																									'All' => GetMessage("logictim.balls_COND_COUNT_ORDERS_ALL"),
																									'Cancell' => GetMessage("logictim.balls_COND_COUNT_ORDERS_CANCELL"),
																									'NotCancell' => GetMessage("logictim.balls_COND_COUNT_ORDERS_NOT_CANCELL"),
																								),
																				'defaultText' => GetMessage("logictim.balls_COND_COUNT_ORDERS_ALL"),
																				'defaultValue' => 'All'
																			),
																			array(
																				'id' => 'paid',
																				'name' => 'paid',
																				'type' => 'select',
																				'values' => array
																								(
																									'All' => GetMessage("logictim.balls_COND_COUNT_ORDERS_ALL"),
																									'Paid' => GetMessage("logictim.balls_COND_COUNT_ORDERS_PAID"),
																									'NotPaid' => GetMessage("logictim.balls_COND_COUNT_ORDERS_NOT_PAID"),
																								),
																				'defaultText' => GetMessage("logictim.balls_COND_COUNT_ORDERS_ALL"),
																				'defaultValue' => 'All'
																			),
																			GetMessage("logictim.balls_COND_COUNT_ORDERS_STATUS"),
																			array(
																				'id' => 'order_status',
																				'name' => 'order_status',
																				'type' => 'select',
																				'values' => $arOrderStatuses,
																				'defaultText' => GetMessage("logictim.balls_COND_COUNT_ORDERS_ALL"),
																				'defaultValue' => 'All'
																			),
																			')'
																		)
													),
												array(
														'controlId'=> 'ordersSum',
														'group'=> false,
														'label'=> GetMessage("logictim.balls_COND_USER_ORDERS_SUM").($type == 'order_referal' ? ' '.GetMessage("logictim.balls_COND_USER_REFERALA_POSTFIX") : ''),
														'showIn'=> array('CondGroup'),
														'control'=> array(
																			array('id'=>'prefix', 'type'=>'prefix', 'text'=>GetMessage("logictim.balls_COND_USER_ORDERS_SUM")),
																			array(
																				'id' => 'logic',
																				'name' => 'logic',
																				'type' => 'select',
																				'values' => array
																								(
																									'EqGr' => GetMessage("logictim.balls_COND_EQGR"),
																									'Less' => GetMessage("logictim.balls_COND_LESS")
																								),
																				'defaultText' => GetMessage("logictim.balls_COND_EQGR"),
																				'defaultValue' => 'EqGr'
																			),
																			array(
																				'type'=> 'input',
																				'id'=> 'ordersSum',
																				'name'=> 'ordersSum',
																				'show_value'=>'Y',
																				'defaultValue' => '0'
																			),
																			array('id'=>'prefix', 'type'=>'prefix', 'text'=>GetMessage("logictim.balls_COND_FOR_PERIOD")),
																			array(
																				'type'=> 'input',
																				'id'=> 'period',
																				'name'=> 'period',
																				'defaultValue' => '1'
																			),
																			array(
																				'type'=> 'select',
																				'id'=> 'period_type',
																				'name'=> 'period_type',
																				'values' => array
																								(
																									'D' => GetMessage("logictim.balls_COND_DAY"),
																									'M' => GetMessage("logictim.balls_COND_MONTH"),
																									'Y' => GetMessage("logictim.balls_COND_YEAR")
																								),
																				'defaultValue' => 'Y',
																				'defaultText' => GetMessage("logictim.balls_COND_YEAR")
																			),
																		)
													),
												array(
														'controlId'=> 'firstOrderDate',
														'group'=> false,
														'label'=> GetMessage("logictim.balls_COND_USER_FIRST_ORDER_DATE").($type == 'order_referal' ? ' '.GetMessage("logictim.balls_COND_USER_REFERALA_POSTFIX") : ''),
														'showIn'=> array('CondGroup'),
														'control'=> array(
																			array('id'=>'prefix', 'type'=>'prefix', 'text'=>GetMessage("logictim.balls_COND_USER_FIRST_ORDER_DATE_USE")),
																			array(
																				'type'=> 'input',
																				'id'=> 'order_num',
																				'name'=> 'order_num',
																				'defaultValue' => '1'
																			),
																			GetMessage("logictim.balls_COND_USER_POSTFIX_GO"),
																			array('id'=>'prefix', 'type'=>'prefix', 'text'=>($type == 'order_referal' ? GetMessage("logictim.balls_COND_USER_FIRST_ORDER_DATE_USE_1_REFERAL") : GetMessage("logictim.balls_COND_USER_FIRST_ORDER_DATE_USE_1"))),
																			array(
																				'type'=> 'input',
																				'id'=> 'period',
																				'name'=> 'period',
																				'defaultValue' => '1'
																			),
																			array(
																				'type'=> 'select',
																				'id'=> 'period_type',
																				'name'=> 'period_type',
																				'values' => array
																								(
																									'D' => GetMessage("logictim.balls_COND_DAY"),
																									'M' => GetMessage("logictim.balls_COND_MONTH"),
																									'Y' => GetMessage("logictim.balls_COND_YEAR")
																								),
																				'defaultValue' => 'Y',
																				'defaultText' => GetMessage("logictim.balls_COND_YEAR")
																			),
																		)
													),
												array(
														'controlId'=> 'registrationDate',
														'group'=> false,
														'label'=> GetMessage("logictim.balls_COND_USER_REGISTRATION_DATE").($type == 'order_referal' ? ' '.GetMessage("logictim.balls_COND_USER_REFERALA_POSTFIX") : ''),
														'showIn'=> array('CondGroup'),
														'control'=> array(
																			array('id'=>'prefix', 'type'=>'prefix', 'text'=>($type == 'order_referal' ? GetMessage("logictim.balls_COND_USER_REGISTRATION_DATE_USE_REFERAL") : GetMessage("logictim.balls_COND_USER_REGISTRATION_DATE_USE"))),
																			array(
																				'type'=> 'input',
																				'id'=> 'period',
																				'name'=> 'period',
																				'defaultValue' => '1'
																			),
																			array(
																				'type'=> 'select',
																				'id'=> 'period_type',
																				'name'=> 'period_type',
																				'values' => array
																								(
																									'D' => GetMessage("logictim.balls_COND_DAY"),
																									'M' => GetMessage("logictim.balls_COND_MONTH"),
																									'Y' => GetMessage("logictim.balls_COND_YEAR")
																								),
																				'defaultValue' => 'Y',
																				'defaultText' => GetMessage("logictim.balls_COND_YEAR")
																			),
																		)
													),
												array(
														'controlId'=> 'cartSum',
														'group'=> false,
														'label'=> GetMessage("logictim.balls_COND_CART_SUM"),
														'showIn'=> array('CondGroup'),
														'control'=> array(
																			array('id'=>'prefix', 'type'=>'prefix', 'text'=>GetMessage("logictim.balls_COND_CART_SUM")),
																			array(
																				'id' => 'logic',
																				'name' => 'logic',
																				'type' => 'select',
																				'values' => array
																								(
																									'EqGr' => GetMessage("logictim.balls_COND_EQGR"),
																									'Less' => GetMessage("logictim.balls_COND_LESS")
																								),
																				'defaultText' => GetMessage("logictim.balls_COND_EQGR"),
																				'defaultValue' => 'EqGr'
																			),
																			array(
																				'type'=> 'input',
																				'id'=> 'value',
																				'name'=> 'value',
																				'show_value'=>'Y',
																				'defaultValue' => '0'
																			)
																		)
													),
												array(
														'controlId'=> 'orderSum',
														'group'=> false,
														'label'=> GetMessage("logictim.balls_COND_ORDER_SUM"),
														'showIn'=> array('CondGroup'),
														'control'=> array(
																			array('id'=>'prefix', 'type'=>'prefix', 'text'=>GetMessage("logictim.balls_COND_ORDER_SUM")),
																			array(
																				'id' => 'logic',
																				'name' => 'logic',
																				'type' => 'select',
																				'values' => array
																								(
																									'EqGr' => GetMessage("logictim.balls_COND_EQGR"),
																									'Less' => GetMessage("logictim.balls_COND_LESS")
																								),
																				'defaultText' => GetMessage("logictim.balls_COND_EQGR"),
																				'defaultValue' => 'EqGr'
																			),
																			array(
																				'type'=> 'input',
																				'id'=> 'value',
																				'name'=> 'value',
																				'show_value'=>'Y',
																				'defaultValue' => '0'
																			)
																		)
													),
												array(
														'controlId'=> 'basketRules',
														'group'=> false,
														'label'=> GetMessage("logictim.balls_COND_BASKET_RULES"),
														'showIn'=> array('CondGroup'),
														'control'=> array(
																		array('id'=>'prefix', 'type'=>'prefix', 'text'=>GetMessage("logictim.balls_COND_BASKET_RULES")),
																		array(
																				'id' => 'logic',
																				'name' => 'logic',
																				'type' => 'select',
																				'values' => array
																								(
																									'Equal' => GetMessage("logictim.balls_COND_EQUAL_USE"),
																									'Not' => GetMessage("logictim.balls_COND_NOT_USE")
																								),
																				'defaultText' => GetMessage("logictim.balls_COND_EQUAL"),
																				'defaultValue' => 'Equal'
																			),
																		array(
																				'type'=> 'select',
																				'multiple'=>'Y',
																				'size'=> 7,
																				'values'=> $basketRules,
																				'show_value'=>'Y',
																				'id'=> 'value',
																				'name'=> 'value',
																			)
																		)
														),
												array(
														'controlId'=> 'paySystems',
														'group'=> false,
														'label'=> GetMessage("logictim.balls_COND_PAY_SYSTEM"),
														'showIn'=> array('CondGroup'),
														'control'=> array(
																		array('id'=>'prefix', 'type'=>'prefix', 'text'=>GetMessage("logictim.balls_COND_PAY_SYSTEM")),
																		array(
																				'id' => 'logic',
																				'name' => 'logic',
																				'type' => 'select',
																				'values' => array
																								(
																									'Equal' => GetMessage("logictim.balls_COND_EQUAL"),
																									'Not' => GetMessage("logictim.balls_COND_NOT")
																								),
																				'defaultText' => GetMessage("logictim.balls_COND_EQUAL"),
																				'defaultValue' => 'Equal'
																			),
																		array(
																				'type'=> 'select',
																				'multiple'=>'Y',
																				'values'=> $arPaySystems,
																				'show_value'=>'Y',
																				'id'=> 'value',
																				'name'=> 'value',
																			)
																		)
														),
												array(
														'controlId'=> 'delivery',
														'group'=> false,
														'label'=> GetMessage("logictim.balls_COND_DELIVERY_SYSTEM"),
														'showIn'=> array('CondGroup'),
														'control'=> array(
																		array('id'=>'prefix', 'type'=>'prefix', 'text'=>GetMessage("logictim.balls_COND_DELIVERY_SYSTEM")),
																		array(
																				'id' => 'logic',
																				'name' => 'logic',
																				'type' => 'select',
																				'values' => array
																								(
																									'Equal' => GetMessage("logictim.balls_COND_EQUAL"),
																									'Not' => GetMessage("logictim.balls_COND_NOT")
																								),
																				'defaultText' => GetMessage("logictim.balls_COND_EQUAL"),
																				'defaultValue' => 'Equal'
																			),
																		array(
																				'type'=> 'select',
																				'multiple'=>'Y',
																				'values'=> $arDelivery,
																				'show_value'=>'Y',
																				'id'=> 'value',
																				'name'=> 'value',
																			)
																		)
														),
												array(
														'controlId'=> 'personTypes',
														'group'=> false,
														'label'=> GetMessage("logictim.balls_COND_PERSON_TYPE"),
														'showIn'=> array('CondGroup'),
														'control'=> array(
																		array('id'=>'prefix', 'type'=>'prefix', 'text'=>GetMessage("logictim.balls_COND_PERSON_TYPE")),
																		array(
																				'id' => 'logic',
																				'name' => 'logic',
																				'type' => 'select',
																				'values' => array
																								(
																									'Equal' => GetMessage("logictim.balls_COND_EQUAL"),
																									'Not' => GetMessage("logictim.balls_COND_NOT")
																								),
																				'defaultText' => GetMessage("logictim.balls_COND_EQUAL"),
																				'defaultValue' => 'Equal'
																			),
																		array(
																				'type'=> 'select',
																				'multiple'=>'Y',
																				'values'=> $arPersonTypes,
																				'show_value'=>'Y',
																				'id'=> 'value',
																				'name'=> 'value',
																			)
																		)
														),
											),
						);	
		
		endif;
						
		
					
						
		
		
		if($mode=='json'){
			return \Bitrix\Main\Web\Json::encode($params);
		}
		return $params;
	}
}



?>