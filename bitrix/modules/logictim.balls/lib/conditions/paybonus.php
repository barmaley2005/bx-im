<?
namespace Logictim\Balls\Conditions;

class PayBonus
{
	public static function MainParams($mode='')
	{
			$arShowParams = array(
						"parentContainer" => 'ProductsConditions',
						"form" => '',
						"formName" => 'logictim_profile',
						"sepID" => '__',
						"prefix" => "profileProductsCond",
						"messTree" => array(
											"SELECT_CONTROL" => GetMessage("logictim.balls_SELECT_COND_GROUP"),
											"ADD_CONTROL" => GetMessage("logictim.balls_ADD_PAY_COND"),
											"DELETE_CONTROL" => GetMessage("logictim.balls_DEL_COND")
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
						'id' => '0',
						'controlId' => 'CondGroup',
						'children' => array(
										array(
											'id' => '0',
											'controlId' => 'conditionGroup',
											'values' => array(
																'bonus' => '100',
																'bonus_type' => 'percent',
																"All" => 'OR',
																"True" => 'True'
															),
											"children" => array()
										)
										
									)
						);
		
		if($mode=='json'){
			return \Bitrix\Main\Web\Json::encode($params);
		}
		return $params;
	}
	
	public static function Controls($mode='')
	{
		$params = array();
		
		$params[] = array(
							"controlId" => 'conditionGroup',
							'group'=> true,
							'label'=> GetMessage("logictim.balls_ADD_PAY_COND"),
							'showIn'=> array('CondGroup'),
							'visual'=> array(
												'controls' => array('All', 'True'),
												'values' => array(
																	array(
																			'All' => 'AND',
																			'True' => 'True',
																		),
																	/*array(
																			'All' => 'AND',
																			'True' => 'False',
																		),*/
																	array(
																			'All' => 'OR',
																			'True' => 'True',
																		),
																	/*array(
																			'All' => 'OR',
																			'True' => 'False',
																		)*/
																	),
												'logic' => array(
																	array(
																			'style' => 'condition-logic-and',
																			'message' => GetMessage("logictim.balls_COND_AND")
																		),
																	/*array(
																			'style' => 'condition-logic-and',
																			'message' => 'AND NOT'
																		),*/
																	array(
																			'style' => 'condition-logic-or',
																			'message' => GetMessage("logictim.balls_COND_OR")
																		),
																	/*array(
																			'style' => 'condition-logic-or',
																			'message' => 'OR NOT'
																		)*/
																)
											),
							'control'=> array(
												GetMessage("logictim.balls_CAN_PAY"),
												array
												(
													'id' => 'bonus',
													'name' => 'bonus',
													'type' => 'input',
													'show_value' => 'Y',
													'defaultValue' => '10'
												),
												array
												(
													'id' => 'bonus_type',
													'name' => 'bonus_type',
													'type' => 'select',
													'values' => array('percent'=>GetMessage("logictim.balls_SELECT_ADD_BONUS_PERCENT"), 'bonus'=>GetMessage("logictim.balls_SELECT_ADD_BONUS_BONUS")),
													'defaultText' => GetMessage("logictim.balls_SELECT_ADD_BONUS_PERCENT"),
													'defaultValue' => 'percent'
												),
												GetMessage("logictim.balls_ROUND_LABEL_NEW"),
												array
												(
													'id' => 'round_type',
													'name' => 'round_type',
													'type' => 'select',
													'values' => array('UNIT'=>GetMessage("logictim.balls_ROUND_FOR_UNIT"), 'POSITION'=>GetMessage("logictim.balls_ROUND_FOR_POSITION")),
													'defaultValue' => 'UNIT',
													'defaultText' => GetMessage("logictim.balls_ROUND_FOR_UNIT"),
												),
												GetMessage("logictim.balls_ROUND_LABEL_TO"),
												array
												(
													'id' => 'round',
													'name' => 'round',
													'type' => 'select',
													'values' => array('A'=>'0', 'B'=>'1', 'C'=>'2', 'D'=>'3', 'E'=>'4'),
													'defaultValue' => 'C',
													'defaultText' => '2',
												),
												GetMessage("logictim.balls_ROUND_SYMBOLS"),
												array
												(
													'id' => 'All',
													'name' => 'All',
													'type' => 'select',
													'values' => array('AND'=>GetMessage("logictim.balls_AND_CONDS"), 'OR'=>GetMessage("logictim.balls_OR_CONDS")),
													'defaultText' => GetMessage("logictim.balls_OR_CONDS"),
													'defaultValue' => 'OR'
												),
												array
												(
													'id' => 'True',
													'name' => 'True',
													'type' => 'select',
													'values' => array('True'=>GetMessage("logictim.balls_CONDS_TRUE")/*, 'False'=>GetMessage("logictim.balls_CONDS_FALSE")*/),
													'defaultText' => GetMessage("logictim.balls_CONDS_TRUE"),
													'defaultValue' => 'True'
												),
											),
							'mess' => array
										(
											'ADD_CONTROL' => GetMessage("logictim.balls_ADD_COND"),
											'SELECT_CONTROL' => GetMessage("logictim.balls_SELECT_COND")
										)
												
						);
		
		
		
		
		$arCatalogs = \Logictim\Balls\Helpers::getCatalogs();
		$arSites = \Logictim\Balls\Helpers::GetSites();
		$params[] = array(
							'controlgroup'=> '1',
							'group'=> false,
							'label'=> GetMessage("logictim.balls_COND_MAIN_PARAMS"),
							'showIn'=> array('conditionGroup', 'conditionGroup2'),
							'children'=> array(
												array(
														'controlId'=> 'iblock',
														'group'=> false,
														'label'=> GetMessage("logictim.balls_COND_IBLOCK"),
														'showIn'=> array('conditionGroup', 'conditionGroup2'),
														'control'=> array(
																		array('id'=>'prefix', 'type'=>'prefix', 'text'=>GetMessage("logictim.balls_COND_IBLOCK")),
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
																				'values'=> $arCatalogs,
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
														'controlId'=> 'product_categoty',
														'group'=> false,
														'label'=> GetMessage("logictim.balls_COND_SECTION"),
														'showIn'=> array('conditionGroup', 'conditionGroup2'),
														'control'=> array(
																		array('id'=>'prefix', 'type'=>'prefix', 'text'=>GetMessage("logictim.balls_COND_SECTION")),
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
																				'type'=> 'popup',
																				'popup_url'=> 'iblock_section_search.php',
																				'popup_params'=> array('lang'=>LANGUAGE_ID,'discount'=>'Y','simplename'=>'Y'),
																				'param_id'=> 'n',
																				'multiple'=> 'Y',
																				'show_value'=> 'Y',
																				'id'=> 'value',
																				'name'=> 'value'
																		)
																	)
												),
												array(
														'controlId'=> 'product',
														'description'=> '',
														'group'=> false,
														'label'=> GetMessage("logictim.balls_COND_PRODUCT"),
														'showIn'=> array('conditionGroup', 'conditionGroup2'),
														'control'=> array(
																		array('id'=>'prefix', 'type'=>'prefix', 'text'=>GetMessage("logictim.balls_COND_PRODUCT")),
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
																				'type'=> 'multiDialog',
																				'popup_url'=> 'cat_product_search_dialog.php',
																				'popup_params'=> array('lang'=>LANGUAGE_ID, 'caller'=>'discount_rules','allow_select_parent'=>'Y'),
																				'param_id'=> 'n',
																				'show_value'=> 'Y',
																				'id'=> 'value',
																				'name'=> 'value'
																		)
																	)
												),
												array(
														'controlId'=> 'price',
														'group'=> false,
														'label'=> GetMessage("logictim.balls_COND_PRICE"),
														'showIn'=> array('conditionGroup', 'conditionGroup2'),
														'control'=> array(
																		array('id'=>'prefix', 'type'=>'prefix', 'text'=>GetMessage("logictim.balls_COND_PRICE")),
																		array(
																			'id' => 'logic',
																			'name' => 'logic',
																			'type' => 'select',
																			'values' => array
																							(
																								'Equal' => GetMessage("logictim.balls_COND_EQUAL"),
																								'Not' => GetMessage("logictim.balls_COND_NOT"),
																								'Great' => GetMessage("logictim.balls_COND_GREAT"),
																								'Less' => GetMessage("logictim.balls_COND_LESS"),
																								'EqGr' => GetMessage("logictim.balls_COND_EQGR"),
																								'EqLs' => GetMessage("logictim.balls_COND_EQLS"),
																							),
																			'defaultText' => GetMessage("logictim.balls_COND_GREAT"),
																			'defaultValue' => 'Great'
																		),
																		array(
																				'type'=> 'input',
																				'id'=> 'value',
																				'name'=> 'value',
																				'show_value'=>'Y',
																				'defaultValue'=> '0',
																				'logictimType' => 'float'
																			)
																	)
												),
												array(
														'controlId'=> 'discount',
														'group'=> false,
														'label'=> GetMessage("logictim.balls_COND_DISCOUNT"),
														'showIn'=> array('conditionGroup', 'conditionGroup2'),
														'control'=> array(
																		/*array('id'=>'prefix', 'type'=>'prefix', 'text'=>GetMessage("logictim.balls_COND_DISCOUNT")),
																		array(
																			'id' => 'logic',
																			'name' => 'logic',
																			'type' => 'select',
																			'values' => array
																							(
																								'Equal' => GetMessage("logictim.balls_COND_EQUAL"),
																								'Not' => GetMessage("logictim.balls_COND_NOT"),
																							),
																			'defaultText' => GetMessage("logictim.balls_COND_EQUAL"),
																			'defaultValue' => 'Equal'
																		),*/
																		array(
																				'type'=> 'select',
																				'id'=> 'value',
																				'name'=> 'value',
																				'values' => array
																							(
																								'N' => GetMessage("logictim.balls_COND_WITHOUT_DISCOUNT"),
																								'Y' => GetMessage("logictim.balls_COND_WITH_DISCOUNT"),
																							),
																				'defaultValue'=> 'N',
																			)
																	)
												),
												array(
														'controlId'=> 'discount_size',
														'group'=> false,
														'label'=> GetMessage("logictim.balls_COND_DISCOUNT_SIZE"),
														'showIn'=> array('conditionGroup', 'conditionGroup2'),
														'control'=> array(
																		array('id'=>'prefix', 'type'=>'prefix', 'text'=>GetMessage("logictim.balls_COND_DISCOUNT_SIZE")),
																		array(
																			'id' => 'logic',
																			'name' => 'logic',
																			'type' => 'select',
																			'values' => array
																							(
																								'Equal' => GetMessage("logictim.balls_COND_EQUAL"),
																								'Not' => GetMessage("logictim.balls_COND_NOT"),
																								'Great' => GetMessage("logictim.balls_COND_GREAT"),
																								'Less' => GetMessage("logictim.balls_COND_LESS"),
																								'EqGr' => GetMessage("logictim.balls_COND_EQGR"),
																								'EqLs' => GetMessage("logictim.balls_COND_EQLS"),
																							),
																			'defaultText' => GetMessage("logictim.balls_COND_GREAT"),
																			'defaultValue' => 'Great'
																		),
																		array(
																				'type'=> 'input',
																				'id'=> 'value',
																				'name'=> 'value',
																				'show_value'=>'Y',
																				'defaultValue'=> '0',
																				'logictimType' => 'float'
																			),
																		array(
																				'type'=> 'select',
																				'id'=> 'type',
																				'name'=> 'type',
																				'values' => array
																							(
																								'P' => GetMessage("logictim.balls_COND_PERCENT"),
																								'C' => GetMessage("logictim.balls_COND_EDINIC"),
																							),
																				'defaultValue'=> 'P',
																			)
																	)
												),
												
											)
						);
						
		
		//CART PARAMS
		$params[] = array(
							'controlgroup'=> '1',
							'group'=> false,
							'label'=> GetMessage("logictim.balls_COND_CART_PARAMS"),
							'showIn'=> array('conditionGroup', 'conditionGroup2'),
							'children'=> array(
												array(
														'controlId'=> 'product_prop_in_cart',
														'group'=> false,
														'label'=> GetMessage("logictim.balls_COND_PRODUCT_PROP_IN_CART"),
														'showIn'=> array('conditionGroup', 'conditionGroup2'),
														'control'=> array(
																		array('id'=>'prefix', 'type'=>'prefix', 'text'=>GetMessage("logictim.balls_COND_PRODUCT_PROP_IN_CART")),
																		array(
																			'id' => 'logic-type',
																			'name' => 'logic-type',
																			'type' => 'select',
																			'values' => array
																							(
																								'xml_id' => GetMessage("logictim.balls_COND_PRODUCT_PROP_XML_ID"),
																								'name' => GetMessage("logictim.balls_COND_PRODUCT_PROP_NAME"),
																							),
																			'defaultText' => GetMessage("logictim.balls_COND_PRODUCT_PROP_XML_ID"),
																			'defaultValue' => 'xml_id'
																		),
																		array(
																				'type'=> 'input',
																				'id'=> 'logic-type_value',
																				'name'=> 'logic-type_value',
																				'show_value'=>'Y',
																				'defaultValue'=> '',
																			),
																		array(
																			'id' => 'logic',
																			'name' => 'logic',
																			'type' => 'select',
																			'values' => array
																							(
																								'Equal' => GetMessage("logictim.balls_COND_EQUAL"),
																								'Not' => GetMessage("logictim.balls_COND_NOT"),
																								'Contain' => GetMessage("logictim.balls_COND_CONT"),
																								'NotCont' => GetMessage("logictim.balls_COND_NOTCONT"),
																							),
																			'defaultText' => GetMessage("logictim.balls_COND_EQUAL"),
																			'defaultValue' => 'Equal'
																		),
																		array(
																				'type'=> 'input',
																				'id'=> 'value',
																				'name'=> 'value',
																				'show_value'=>'Y',
																				'defaultValue'=> '',
																			),
																	)
												),
												
											)
						);
						
		$condotionsProps = \CCatalogCondCtrlIBlockProps::GetControlShow(array('SHOW_IN_GROUPS'=>array('conditionGroup', 'conditionGroup2')));
		if(count($condotionsProps) > 0)
		{
			foreach($condotionsProps as $oneProp):
				$params[] = $oneProp;
			endforeach;
			
		}
						
		$params[]=array(
				'controlId'=> 'CondGroup',
				'group'=> true,
				'label'=> '',
				'defaultText'=> '',
				'showIn'=> array(),
				'control'=> array('CONDITION_PERFORM_OPERATIONS')
			);
		
		if($mode=='json'){
			return \Bitrix\Main\Web\Json::encode($params);
		}
		return $params;
	}
}



?>