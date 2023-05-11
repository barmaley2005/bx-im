<?
global $DB;

//ADD PROFILE BONUS FROM ORDER
$arSaveFields = array();
$arSaveFields['name'] = '"'.GetMessage("logictim.balls_PROFILE_BONUS_FROM_ORDER").'"';
$arSaveFields['active'] = '"N"';
$arSaveFields['sort'] = 100;
$arSaveFields['type'] = '"order"';
$arSaveFields['active_after_period'] = 0;
$arSaveFields['active_after_type'] = '"D"';
$arSaveFields['deactive_after_period'] = 365;
$arSaveFields['deactive_after_type'] = '"D"';
$saveProductConditions = array(
						'id' => '0',
						'controlId' => 'CondGroup',
						'children' => array(
										array(
											'id' => '0',
											'controlId' => 'conditionGroup',
											'values' => array(
																'bonus' => '10',
																'bonus_type' => 'percent',
																'round' => 'C',
																"All" => 'OR',
																"True" => 'True'
															),
											"children" => array()
										)
										
									)
						);
$saveProfileConditions = array(
						"id" => '0',
						"controlId" => 'CondGroup',
						"children" => array(
										),
					);
$arSaveFields['conditions'] = "'".serialize($saveProductConditions)."'";
$arSaveFields['profile_conditions'] = "'".serialize($saveProfileConditions)."'";
$id = $DB->Insert('logictim_balls_profiles', $arSaveFields, '');
		

//ADD PROFILE PAY BONUS
$arSaveFields = array();
$arSaveFields['name'] = '"'.GetMessage("logictim.balls_PROFILE_PAY_BONUS").'"';
$arSaveFields['active'] = '"Y"';
$arSaveFields['sort'] = 200;
$arSaveFields['type'] = '"pay_bonus"';
$saveProductConditions = array(
						'id' => '0',
						'controlId' => 'CondGroup',
						'children' => array(
										array(
											'id' => '0',
											'controlId' => 'conditionGroup',
											'values' => array(
																'bonus' => '100',
																'bonus_type' => 'percent',
																'round' => 'C',
																"All" => 'OR',
																"True" => 'True'
															),
											"children" => array()
										)
										
									)
						);	
$saveProfileConditions = array(
						"id" => '0',
						"controlId" => 'CondGroup',
						"children" => array(
										),
					);
$otherConditions = array(
									"MIN_PAYMENT_BONUS" => 0,
									"MIN_PAYMENT_TYPE" => 'bonus',
									"MIN_PAYMENT_INCLUDE_SHIPPING" => 'N',
									"MAX_PAYMENT_BONUS" => 100,
									"MAX_PAYMENT_TYPE" => 'percent',
									"MAX_PAYMENT_INCLUDE_SHIPPING" => 'N'
								);
$arSaveFields['conditions'] = "'".serialize($saveProductConditions)."'";
$arSaveFields['profile_conditions'] = "'".serialize($saveProfileConditions)."'";
$arSaveFields['other_conditions'] = "'".serialize($otherConditions)."'";
$id = $DB->Insert('logictim_balls_profiles', $arSaveFields, '');
		
?>