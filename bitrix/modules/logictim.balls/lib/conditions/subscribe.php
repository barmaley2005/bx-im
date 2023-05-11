<?
namespace Logictim\Balls\Conditions;

class Subscribe
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
											"SELECT_CONTROL" => GetMessage("logictim.balls_COND_ADD_SUBSCRIBE_SELECT"),
											"ADD_CONTROL" => GetMessage("logictim.balls_COND_ADD_SUBSCRIBE_SELECT"),
											"DELETE_CONTROL" => GetMessage("logictim.balls_COND_ADD_SUBSCRIBE_DEL"),
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
									)
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
			
		
		///Select subscribe from module Sender
		if(\CModule::IncludeModule("sender")):
			/*$listSender = \Bitrix\Sender\Subscription::getMailingList(array());
			$arSenders = array();
			foreach($listSender as $arSender):
				$arSenders[$arSender["ID"]] = $arSender["NAME"];
			endforeach;*/
			
			global $DB;
			$listSender = $DB->Query('select * from b_sender_mailing;');
			$arSenders = array();
			while($arSender = $listSender->GetNext()) {
				$arSenders[$arSender["ID"]] = $arSender["NAME"].' ('.$arSender["SITE_ID"].')';
			}
		
		
		$params[] = array(
							'controlId'=> 'sender',
							'group'=> false,
							'label'=> GetMessage("logictim.balls_COND_SUBSCRIBE_MODULE_SENDER"),
							'showIn'=> array('CondGroup'),
							'control'=> array(
											array('id'=>'prefix', 'type'=>'prefix', 'text'=>GetMessage("logictim.balls_COND_SUBSCRIBE_MODULE_SENDER")),
											array(
												'id' => 'logic',
												'name' => 'logic',
												'type' => 'select',
												'values' => array
																(
																	'Equal' => GetMessage("logictim.balls_COND_EQUAL"),
																),
												'defaultText' => GetMessage("logictim.balls_COND_EQUAL"),
												'defaultValue' => 'Equal'
											),
											array(
													'type'=> 'select',
													'multiple'=>'Y',
													'values'=> $arSenders,
													'id'=> 'value',
													'name'=> 'value',
													'show_value'=>'Y',
													'first_option'=> '...',
													'defaultText'=> '...',
													'defaultValue'=> ''
												)
										)
					);
		endif;
		
		//Select subscribe from module Subscribe
		if(\CModule::IncludeModule("subscribe")):
			$rsRubric = \CRubric::GetList(array("SORT"=>"ASC", "NAME"=>"ASC"), array("ACTIVE"=>"Y"));
			$arRubrics = array();
			while($arRubric = $rsRubric->GetNext()) {
				$arRubrics[$arRubric["ID"]] = $arRubric["NAME"].' ('.$arRubric["LID"].')';
			}
		
		$params[] = array(
							'controlId'=> 'subscribe',
							'group'=> false,
							'label'=> GetMessage("logictim.balls_COND_SUBSCRIBE_MODULE_SUBSCRIBE"),
							'showIn'=> array('CondGroup'),
							'control'=> array(
											array('id'=>'prefix', 'type'=>'prefix', 'text'=>GetMessage("logictim.balls_COND_SUBSCRIBE_MODULE_SUBSCRIBE")),
											array(
												'id' => 'logic',
												'name' => 'logic',
												'type' => 'select',
												'values' => array
																(
																	'Equal' => GetMessage("logictim.balls_COND_EQUAL"),
																),
												'defaultText' => GetMessage("logictim.balls_COND_EQUAL"),
												'defaultValue' => 'Equal'
											),
											array(
													'type'=> 'select',
													'multiple'=>'Y',
													'values'=> $arRubrics,
													'id'=> 'value',
													'name'=> 'value',
													'show_value'=>'Y',
													'first_option'=> '...',
													'defaultText'=> '...',
													'defaultValue'=> ''
												)
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