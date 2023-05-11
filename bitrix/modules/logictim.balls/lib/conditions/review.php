<?
namespace Logictim\Balls\Conditions;

class Review
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
											"SELECT_CONTROL" => GetMessage("logictim.balls_COND_ADD_REVIEW_PLACE"),
											"ADD_CONTROL" => GetMessage("logictim.balls_COND_ADD_REVIEW_PLACE"),
											"DELETE_CONTROL" => GetMessage("logictim.balls_COND_DEL_REVIEW_PLACE"),
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
		
		//Select all iblocks
		if(\CModule::IncludeModule("iblock"))
		{
			$arIblocks = array();
			$dbIblocks = \CIBlock::GetList(array("SORT"=>"NAME"), array("ACTIVE" => "Y"), false);
			while($arIblock = $dbIblocks->Fetch())
			{
				$arIblocks[$arIblock["ID"]] = $arIblock["NAME"];
			}
		}
		
		$params[] = array(
							'controlId'=> 'iblock',
							'group'=> false,
							'label'=> GetMessage("logictim.balls_COND_IBLOCK"),
							'showIn'=> array('CondGroup'),
							'control'=> array(
											array('id'=>'prefix', 'type'=>'prefix', 'text'=>GetMessage("logictim.balls_COND_IBLOCK")),
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
													'values'=> $arIblocks,
													'id'=> 'value',
													'name'=> 'value',
													'show_value'=>'Y',
													'first_option'=> '...',
													'defaultText'=> '...',
													'defaultValue'=> ''
												)
										)
					);
		
		
		//Select all blogs
		if(\CModule::IncludeModule("blog"))
		{
			$arBlogs = array();
			$dbBlogs = \CBlog::GetList(array("NAME" => "ASC"), array("ACTIVE" => "Y"), false, false, array("ID", "NAME"));
			while ($arBlog = $dbBlogs->Fetch())
			{
				$arBlogs[$arBlog["ID"]] = $arBlog["NAME"];
			}
		}
		
		$params[] = array(
							'controlId'=> 'blog',
							'group'=> false,
							'label'=> GetMessage("logictim.balls_COND_BLOG"),
							'showIn'=> array('CondGroup'),
							'control'=> array(
											array('id'=>'prefix', 'type'=>'prefix', 'text'=>GetMessage("logictim.balls_COND_BLOG")),
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
													'values'=> $arBlogs,
													'id'=> 'value',
													'name'=> 'value',
													'show_value'=>'Y',
													'first_option'=> '...',
													'defaultText'=> '...',
													'defaultValue'=> ''
												)
										)
					);
		
		
		//Select all forums
		if(\CModule::IncludeModule("forum"))
		{
			$arForums = array();
			$arOrder = array("SORT"=>"ASC", "NAME"=>"ASC");
			$dbForums = \CForumNew::GetList($arOrder, array("ACTIVE" => "Y"));
			while ($arForum = $dbForums->Fetch())
			{
				$arForums[$arForum["ID"]] = $arForum["NAME"];
			}
		}
		$params[] = array(
							'controlId'=> 'forum',
							'group'=> false,
							'label'=> GetMessage("logictim.balls_COND_FORUM"),
							'showIn'=> array('CondGroup'),
							'control'=> array(
											array('id'=>'prefix', 'type'=>'prefix', 'text'=>GetMessage("logictim.balls_COND_FORUM")),
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
													'values'=> $arForums,
													'id'=> 'value',
													'name'=> 'value',
													'show_value'=>'Y',
													'first_option'=> '...',
													'defaultText'=> '...',
													'defaultValue'=> ''
												)
										)
					);
		
				
						
		
		
		if($mode=='json'){
			return \Bitrix\Main\Web\Json::encode($params);
		}
		return $params;
	}
}



?>