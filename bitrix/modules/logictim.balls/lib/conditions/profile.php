<?
namespace Logictim\Balls\Conditions;

class Profile
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
	
	public static function Controls($mode='', $type = '')
	{
		$arSites = \Logictim\Balls\Helpers::GetSites();
		$arUserGroups = \Logictim\Balls\Helpers::GetUserGroups();
		
		$params = array();
		
		$params[]=array(
				'controlId'=> 'CondGroup',
				'group'=> true,
				'label'=> '',
				'defaultText'=> '',
				'showIn'=> array(),
				'control'=> array('CONDITION_PERFORM_OPERATIONS')
			);
		
		
		if($type == 'referal'):
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
													
												
											)
						);
		
		endif;
		
		if($type == 'exit_bonus'):
				$levels = array();
				while($x++ < (int)\COption::GetOptionString("logictim.balls", "REFERAL_LEVELS", 1))
				{
					$levels[$x] = $x;
				}
				$params[] = array(
							'controlgroup'=> '2',
							'group'=> true,
							'label'=> GetMessage("logictim.balls_COND_TYPE_USERS"),
							'showIn'=> array('CondGroup'),
							'children'=> array(
												array(
														'controlId'=> 'HaveReferals',
														'group'=> false,
														'label'=> GetMessage("logictim.balls_COND_HAVE_REFERALS_LABEL"),
														'showIn'=> array('CondGroup'),
														'control'=> array(
																			array('id'=>'prefix', 'type'=>'prefix', 'text'=>GetMessage("logictim.balls_COND_HAVE_REFERALS")),
																			array(
																				'id' => 'logic',
																				'name' => 'logic',
																				'type' => 'select',
																				'values' => array
																								(
																									'Equal' => GetMessage("logictim.balls_COND_HAVE_REFERALS_Y"),
																									'Not' => GetMessage("logictim.balls_COND_HAVE_REFERALS_N")
																								),
																				'defaultText' => GetMessage("logictim.balls_COND_HAVE_REFERALS_Y"),
																				'defaultValue' => 'Equal'
																			),
																		)
													),
											)
						);
		endif;
		
		
		if($mode=='json'){
			return \Bitrix\Main\Web\Json::encode($params);
		}
		return $params;
	}
}



?>