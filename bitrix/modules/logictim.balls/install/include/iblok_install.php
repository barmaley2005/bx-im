<?
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
IncludeModuleLangFile(__FILE__);


if(CModule::IncludeModule("iblock")):
	//Install iblok type
	$arFields = Array(
		'ID'=>'LOGICTIM_BONUS_STATISTIC',
		'SECTIONS'=>'N',
		'IN_RSS'=>'N',
		'SORT'=>500,
		'LANG'=>Array(
			'en'=>Array(
				'NAME'=>'Bonus system',
				'SECTION_NAME'=>'Sections',
				'ELEMENT_NAME'=>'Operation'
				),
			'ru'=>Array(
				'NAME'=>GetMessage("logictim.balls_IBLOCK_TYPE"),
				'SECTION_NAME'=>GetMessage("logictim.balls_IBLOCK_RAZDEL"),
				'ELEMENT_NAME'=>GetMessage("logictim.balls_IBLOCK_ELEMENT")
				)
			)
		);
	
	$obBlocktype = new CIBlockType;
	$DB->StartTransaction();
	$res = $obBlocktype->Add($arFields);
	if(!$res)
		{
		   $DB->Rollback();
		   echo 'Error: '.$obBlocktype->LAST_ERROR.'<br>';
		}
		else
		   $DB->Commit();
	
		//Install iblok
		$sites = array();
		$rsSites = CSite::GetList($by="sort", $order="desc", array());
		while($arSite = $rsSites->Fetch())
		{
		  $sites[] = $arSite["ID"];
		}
	$ib = new CIBlock;
	
	//ADD iblock "logictim_bonus_operations"
	$arFields = Array(
	  "ACTIVE" => 'Y',
	  "NAME" => GetMessage("logictim.balls_IBLOCK_NAME"),
	  "CODE" => 'logictim_bonus_operations',
	  "IBLOCK_TYPE_ID" => 'LOGICTIM_BONUS_STATISTIC',
	  "SITE_ID" => $sites,
	  "SORT" => 500,
	  "DESCRIPTION_TYPE" => 'text',
	  "GROUP_ID" => Array("1"=>"R", "2"=>"R"),
	  "INDEX_ELEMENT" => 'N',
	  "INDEX_SECTION" => 'N'
	  );
	  $ID = $ib->Add($arFields);
	  
	 
	//Install iblok propertys
	$arProps = array(
					"OPERATION_TYPE" => array(
							  "NAME" => GetMessage("logictim.balls_OPERATION_TYPE"),
							  "ACTIVE" => "Y",
							  "SORT" => "100",
							  "CODE" => "OPERATION_TYPE",
							  "PROPERTY_TYPE" => "L",
							  "IBLOCK_ID" => $ID,
							  "FILTRABLE" => 'Y',
							  "VALUES" => array (
							  					"0" => array( "XML_ID" => "ADD_FROM_ORDER",
															  "VALUE" => GetMessage("logictim.balls_ADD_FROM_ORDER"),
															  "DEF" => "N",
															  "SORT" => "100"
															),
												"1" => array( "XML_ID" => "MINUS_FROM_ORDER",
															  "VALUE" => GetMessage("logictim.balls_MINUS_FROM_ORDER"),
															  "DEF" => "N",
															  "SORT" => "200"
															),
												"2" => array( "XML_ID" => "USER_BALLANCE_CHANGE",
															  "VALUE" => GetMessage("logictim.balls_USER_BALLANCE_CHANGE"),
															  "DEF" => "N",
															  "SORT" => "300"
															),
												"3" => array( "XML_ID" => "BACK_FROM_CANCEL",
															  "VALUE" => GetMessage("logictim.balls_BACK_FROM_CANCEL"),
															  "DEF" => "N",
															  "SORT" => "400"
															),
												"4" => array( "XML_ID" => "BACK_FROM_DELETTE",
															  "VALUE" => GetMessage("logictim.balls_BACK_FROM_DELETTE"),
															  "DEF" => "N",
															  "SORT" => "500"
															),
												"5" => array( "XML_ID" => "ADD_FROM_REGISTER",
															  "VALUE" => GetMessage("logictim.balls_ADD_FROM_REGISTER"),
															  "DEF" => "N",
															  "SORT" => "600"
															),
												"6" => array( "XML_ID" => "ADD_FROM_BIRTHDAY",
															  "VALUE" => GetMessage("logictim.balls_ADD_FROM_BIRTHDAY"),
															  "DEF" => "N",
															  "SORT" => "700"
															),
												"7" => array( "XML_ID" => "DEACIVATE_FROM_DATE",
															  "VALUE" => GetMessage("logictim.balls_BONUS_DEACTIVATE_FROM_DATE"),
															  "DEF" => "N",
															  "SORT" => "800"
															),
												"8" => array( "XML_ID" => "ADD_FROM_REPOST",
															  "VALUE" => GetMessage("logictim.balls_ADD_FROM_REPOST"),
															  "DEF" => "N",
															  "SORT" => "900"
															),
												"9" => array( "XML_ID" => "ADD_FROM_REVIEW",
															  "VALUE" => GetMessage("logictim.balls_ADD_FROM_REVIEW"),
															  "DEF" => "N",
															  "SORT" => "901"
															),
												"10" => array( "XML_ID" => "ADD_FROM_LINK",
															  "VALUE" => GetMessage("logictim.balls_ADD_FROM_LINK"),
															  "DEF" => "N",
															  "SORT" => "902"
															),
												"11" => array( "XML_ID" => "ADD_FROM_REFERAL",
															  "VALUE" => GetMessage("logictim.balls_ADD_FROM_REFERAL"),
															  "DEF" => "N",
															  "SORT" => "903"
															),
												"12" => array( "XML_ID" => "EXIT_BONUS",
															  "VALUE" => GetMessage("logictim.balls_EXIT_BONUS"),
															  "DEF" => "N",
															  "SORT" => "904"
															),
												"13" => array( "XML_ID" => "EXIT_REFUND_BONUS",
															  "VALUE" => GetMessage("logictim.balls_EXIT_REFUND_BONUS"),
															  "DEF" => "N",
															  "SORT" => "905"
															),
												"14" => array( "XML_ID" => "ADD_FROM_SUBSCRIBE",
															  "VALUE" => GetMessage("logictim.balls_ADD_FROM_SUBSCRIBE"),
															  "DEF" => "N",
															  "SORT" => "906"
															),
												"15" => array( "XML_ID" => "MINUS_FROM_ORDER_CANCEL",
															  "VALUE" => GetMessage("logictim.balls_BONUS_MINUS_FROM_CANCEL_ORDER"),
															  "DEF" => "N",
															  "SORT" => "907"
															),
															
							  					)
							  ),
					"OPERATION_SUM" => array(
							  "NAME" => GetMessage("logictim.balls_OPERATION_SUM"),
							  "ACTIVE" => "Y",
							  "SORT" => "200",
							  "CODE" => "OPERATION_SUM",
							  "PROPERTY_TYPE" => "N",
							  "IBLOCK_ID" => $ID,
							  "FILTRABLE" => 'Y'
							  ),
					"BALLANCE_BEFORE" => array(
							  "NAME" => GetMessage("logictim.balls_BALLANCE_BEFORE"),
							  "ACTIVE" => "Y",
							  "SORT" => "300",
							  "CODE" => "BALLANCE_BEFORE",
							  "PROPERTY_TYPE" => "N",
							  "IBLOCK_ID" => $ID,
							  "FILTRABLE" => 'Y'
							  ),
					"BALLANCE_AFTER" => array(
							  "NAME" => GetMessage("logictim.balls_BALLANCE_AFTER"),
							  "ACTIVE" => "Y",
							  "SORT" => "400",
							  "CODE" => "BALLANCE_AFTER",
							  "PROPERTY_TYPE" => "N",
							  "IBLOCK_ID" => $ID,
							  "FILTRABLE" => 'Y'
							  ),
					"USER" => array(
							  "NAME" => GetMessage("logictim.balls_USER"),
							  "ACTIVE" => "Y",
							  "SORT" => "500",
							  "CODE" => "USER",
							  "PROPERTY_TYPE" => "S",
							  "USER_TYPE" => "UserID",
							  "IBLOCK_ID" => $ID,
							  "FILTRABLE" => 'Y'
							  ),
					"ORDER_ID" => array(
							  "NAME" => GetMessage("logictim.balls_ORDER_ID"),
							  "ACTIVE" => "Y",
							  "SORT" => "600",
							  "CODE" => "ORDER_ID",
							  "PROPERTY_TYPE" => "N",
							  "IBLOCK_ID" => $ID,
							  ),
					"ADD_DETAIL" => array(
							  "NAME" => GetMessage("logictim.balls_ADD_DETAIL"),
							  "ACTIVE" => "Y",
							  "SORT" => "700",
							  "CODE" => "ADD_DETAIL",
							  "PROPERTY_TYPE" => "S",
							  "USER_TYPE" => "HTML",
							  "ROW_COUNT" => 5,
							  "IBLOCK_ID" => $ID,
							  ),
					"LIVE_DATE" => array(
							  "NAME" => GetMessage("logictim.balls_LIVE_DATE"),
							  "ACTIVE" => "Y",
							  "SORT" => "800",
							  "CODE" => "LIVE_DATE",
							  "PROPERTY_TYPE" => "S",
							  "USER_TYPE" => "DateTime",
							  "ROW_COUNT" => 1,
							  "IBLOCK_ID" => $ID,
							  ),
					"LIVE_STATUS" => array(
							  "NAME" => GetMessage("logictim.balls_LIVE_STATUS"),
							  "ACTIVE" => "Y",
							  "SORT" => "900",
							  "CODE" => "LIVE_STATUS",
							  "PROPERTY_TYPE" => "L",
							  "IBLOCK_ID" => $ID,
							  "FILTRABLE" => 'Y',
							  "VALUES" => array (
							  					"0" => array( "XML_ID" => "ACTIVE",
															  "VALUE" => GetMessage("logictim.balls_LIVE_STATUS_ACTIVE"),
															  "DEF" => "N",
															  "SORT" => "100"
															),
												"1" => array( "XML_ID" => "END",
															  "VALUE" => GetMessage("logictim.balls_LIVE_STATUS_END"),
															  "DEF" => "N",
															  "SORT" => "200"
															),
												"2" => array( "XML_ID" => "LIVE_END",
															  "VALUE" => GetMessage("logictim.balls_LIVE_STATUS_LIVE_END"),
															  "DEF" => "N",
															  "SORT" => "300"
															),
							  					)
							  ),
					"PAID" => array(
							  "NAME" => GetMessage("logictim.balls_PAID"),
							  "ACTIVE" => "Y",
							  "SORT" => "1000",
							  "CODE" => "PAID",
							  "PROPERTY_TYPE" => "N",
							  "IBLOCK_ID" => $ID,
							  "FILTRABLE" => 'Y'
							  ),
					"BALLANCE" => array(
							  "NAME" => GetMessage("logictim.balls_BALLANCE"),
							  "ACTIVE" => "Y",
							  "SORT" => "1000",
							  "CODE" => "BALLANCE",
							  "PROPERTY_TYPE" => "N",
							  "IBLOCK_ID" => $ID,
							  "FILTRABLE" => 'Y'
							  ),
					"PAYMENT_ID" => array(
							  "NAME" => GetMessage("logictim.balls_PAYMENT_ID"),
							  "ACTIVE" => "Y",
							  "SORT" => "1010",
							  "CODE" => "PAYMENT_ID",
							  "PROPERTY_TYPE" => "N",
							  "IBLOCK_ID" => $ID,
							  "FILTRABLE" => 'Y'
							  ),
					"REPOST_ID" => array(
							  "NAME" => GetMessage("logictim.balls_REPOST_ID"),
							  "ACTIVE" => "Y",
							  "SORT" => "500",
							  "CODE" => "REPOST_ID",
							  "PROPERTY_TYPE" => "E",
							  "IBLOCK_ID" => $ID,
							  ),
					"SERVICE_INFO" => array(
								  "NAME" => GetMessage("logictim.balls_PROP_SERVICE_INFO"),
								  "ACTIVE" => "Y",
								  "SORT" => "1012",
								  "CODE" => "SERVICE_INFO",
								  "PROPERTY_TYPE" => "S",
								  "IBLOCK_ID" => $ID,
								  ),
				  );
	$ibp = new CIBlockProperty;
	foreach($arProps as $prop) {
		$PropID = $ibp->Add($prop);
	}
	
	//ADD iblock "logictim_bonus_reposts"
	$arFields = Array(
	  "ACTIVE" => 'Y',
	  "NAME" => GetMessage("logictim.balls_IBLOCK_REPOST_NAME"),
	  "CODE" => 'logictim_bonus_reposts',
	  "IBLOCK_TYPE_ID" => 'LOGICTIM_BONUS_STATISTIC',
	  "SITE_ID" => $sites,
	  "SORT" => 500,
	  "DESCRIPTION_TYPE" => 'text',
	  "GROUP_ID" => Array("1"=>"R", "2"=>"R"),
	  "INDEX_ELEMENT" => 'N',
	  "INDEX_SECTION" => 'N'
	  );
	  $ID = $ib->Add($arFields);
	  
	//Install iblok propertys
	$arProps = array(
					"SOCIAL_NETWORK" => array(
							  "NAME" => GetMessage("logictim.balls_SOCIAL_NETWORK"),
							  "ACTIVE" => "Y",
							  "SORT" => "500",
							  "CODE" => "SOCIAL_NETWORK",
							  "PROPERTY_TYPE" => "L",
							  "IBLOCK_ID" => $ID,
							  "FILTRABLE" => 'Y',
							  "VALUES" => array (
							  					"0" => array( "XML_ID" => "VK",
															  "VALUE" => GetMessage("logictim.balls_SOCIAL_NETWORK_VK"),
															  "DEF" => "N",
															  "SORT" => "1"
															),
												"1" => array( "XML_ID" => "FB",
															  "VALUE" => GetMessage("logictim.balls_SOCIAL_NETWORK_FB"),
															  "DEF" => "N",
															  "SORT" => "2"
															),
												"2" => array( "XML_ID" => "TW",
															  "VALUE" => GetMessage("logictim.balls_SOCIAL_NETWORK_TW"),
															  "DEF" => "N",
															  "SORT" => "3"
															),
												"3" => array( "XML_ID" => "OK",
															  "VALUE" => GetMessage("logictim.balls_SOCIAL_NETWORK_OK"),
															  "DEF" => "N",
															  "SORT" => "4"
															),
							  					)
							  ),
					"POST_STATUS" => array(
							  "NAME" => GetMessage("logictim.balls_POST_STATUS"),
							  "ACTIVE" => "Y",
							  "SORT" => "500",
							  "CODE" => "POST_STATUS",
							  "PROPERTY_TYPE" => "L",
							  "IBLOCK_ID" => $ID,
							  "FILTRABLE" => 'Y',
							  "VALUES" => array (
							  					"0" => array( "XML_ID" => "WATE_CHECK",
															  "VALUE" => GetMessage("logictim.balls_POST_STATUS_WATE_CHECK"),
															  "DEF" => "N",
															  "SORT" => "1"
															),
												"1" => array( "XML_ID" => "BONUS_ADD",
															  "VALUE" => GetMessage("logictim.balls_POST_STATUS_BONUS_ADD"),
															  "DEF" => "N",
															  "SORT" => "2"
															),
												"2" => array( "XML_ID" => "ERROR",
															  "VALUE" => GetMessage("logictim.balls_POST_STATUS_ERROR"),
															  "DEF" => "N",
															  "SORT" => "3"
															),
												"3" => array( "XML_ID" => "LIMIT",
															  "VALUE" => GetMessage("logictim.balls_POST_STATUS_LIMIT"),
															  "DEF" => "N",
															  "SORT" => "4"
															),
												"4" => array( "XML_ID" => "REPOST",
															  "VALUE" => GetMessage("logictim.balls_POST_STATUS_REPOST"),
															  "DEF" => "N",
															  "SORT" => "5"
															),
							  					)
							  ),
					"SITE_USER" => array(
							  "NAME" => GetMessage("logictim.balls_SITE_USER"),
							  "ACTIVE" => "Y",
							  "SORT" => "500",
							  "CODE" => "SITE_USER",
							  "PROPERTY_TYPE" => "S",
							  "USER_TYPE" => "UserID",
							  "IBLOCK_ID" => $ID,
							  "FILTRABLE" => 'Y'
							  ),
					"SOCIAL_POST_DATE" => array(
							  "NAME" => GetMessage("logictim.balls_SOCIAL_POST_DATE"),
							  "ACTIVE" => "Y",
							  "SORT" => "500",
							  "CODE" => "SOCIAL_POST_DATE",
							  "PROPERTY_TYPE" => "S",
							  "USER_TYPE" => "DateTime",
							  "ROW_COUNT" => 1,
							  "IBLOCK_ID" => $ID,
							  ),
					"PAGE" => array(
							  "NAME" => GetMessage("logictim.balls_POST_PAGE"),
							  "ACTIVE" => "Y",
							  "SORT" => "500",
							  "CODE" => "PAGE",
							  "PROPERTY_TYPE" => "S",
							  "ROW_COUNT" => 1,
							  "IBLOCK_ID" => $ID,
							  ),
					"COMMENT" => array(
							  "NAME" => GetMessage("logictim.balls_POST_COMMENT"),
							  "ACTIVE" => "Y",
							  "SORT" => "500",
							  "CODE" => "COMMENT",
							  "PROPERTY_TYPE" => "S",
							  "ROW_COUNT" => 1,
							  "IBLOCK_ID" => $ID,
							  ),
					"OPERATION_ID" => array(
							  "NAME" => GetMessage("logictim.balls_OPERATION_ID"),
							  "ACTIVE" => "Y",
							  "SORT" => "500",
							  "CODE" => "OPERATION_ID",
							  "PROPERTY_TYPE" => "E",
							  "IBLOCK_ID" => $ID,
							  ),
					);
			$ibp = new CIBlockProperty;
			foreach($arProps as $prop) {
				$PropID = $ibp->Add($prop);
			}
			
			
	//ADD iblock "logictim_bonus_links"
	$arFields = Array(
	  "ACTIVE" => 'Y',
	  "NAME" => GetMessage("logictim.balls_IBLOCK_LINKS_NAME"),
	  "CODE" => 'logictim_bonus_links',
	  "IBLOCK_TYPE_ID" => 'LOGICTIM_BONUS_STATISTIC',
	  "SITE_ID" => $sites,
	  "SORT" => 700,
	  "DESCRIPTION_TYPE" => 'text',
	  "GROUP_ID" => Array("1"=>"R", "2"=>"R"),
	  "INDEX_ELEMENT" => 'N',
	  "INDEX_SECTION" => 'N'
	  );
	  $ID = $ib->Add($arFields);
	  
	  //Install iblok propertys
	$arProps = array(
					"IP" => array(
							  "NAME" => GetMessage("logictim.balls_USER_IP"),
							  "ACTIVE" => "Y",
							  "SORT" => "500",
							  "CODE" => "IP",
							  "PROPERTY_TYPE" => "S",
							  "ROW_COUNT" => 1,
							  "IBLOCK_ID" => $ID,
							  ),
					"URL" => array(
							  "NAME" => GetMessage("logictim.balls_LINK_PAGE"),
							  "ACTIVE" => "Y",
							  "SORT" => "500",
							  "CODE" => "URL",
							  "PROPERTY_TYPE" => "S",
							  "ROW_COUNT" => 1,
							  "IBLOCK_ID" => $ID,
							  ),
					"REF_LINK" => array(
							  "NAME" => GetMessage("logictim.balls_REF_LINK"),
							  "ACTIVE" => "Y",
							  "SORT" => "500",
							  "CODE" => "REF_LINK",
							  "PROPERTY_TYPE" => "S",
							  "ROW_COUNT" => 1,
							  "IBLOCK_ID" => $ID,
							  ),
					"REFERAL" => array(
							  "NAME" => GetMessage("logictim.balls_REFERAL"),
							  "ACTIVE" => "Y",
							  "SORT" => "500",
							  "CODE" => "REFERAL",
							  "PROPERTY_TYPE" => "S",
							  "USER_TYPE" => "UserID",
							  "IBLOCK_ID" => $ID,
							  "FILTRABLE" => 'Y'
							  ),
					"OPERATION_ID" => array(
							  "NAME" => GetMessage("logictim.balls_OPERATION_ID"),
							  "ACTIVE" => "Y",
							  "SORT" => "500",
							  "CODE" => "OPERATION_ID",
							  "PROPERTY_TYPE" => "E",
							  "IBLOCK_ID" => $ID,
							  ),
					);
			$ibp = new CIBlockProperty;
			foreach($arProps as $prop) {
				$PropID = $ibp->Add($prop);
			}
			
		//ADD iblock "logictim_bonus_wait"
		$arFields = Array(
			  "ACTIVE" => 'Y',
			  "NAME" => GetMessage("logictim.balls_IBLOCK_BONUS_WAIT_NAME"),
			  "CODE" => 'logictim_bonus_wait',
			  "IBLOCK_TYPE_ID" => 'LOGICTIM_BONUS_STATISTIC',
			  "SITE_ID" => $sites,
			  "SORT" => 500,
			  "DESCRIPTION_TYPE" => 'text',
			  "GROUP_ID" => Array("1"=>"R", "2"=>"R"),
			  "INDEX_ELEMENT" => 'N',
	  		  "INDEX_SECTION" => 'N'
			  );
		$ID = $ib->Add($arFields);
		
		$arProps = array(
				"OPERATION_TYPE" => array(
							  "NAME" => GetMessage("logictim.balls_OPERATION_TYPE"),
							  "ACTIVE" => "Y",
							  "SORT" => "100",
							  "CODE" => "OPERATION_TYPE",
							  "PROPERTY_TYPE" => "L",
							  "IBLOCK_ID" => $ID,
							  "FILTRABLE" => 'Y',
							  "VALUES" => array (
							  					"0" => array( "XML_ID" => "ADD_FROM_ORDER",
															  "VALUE" => GetMessage("logictim.balls_ADD_FROM_ORDER"),
															  "DEF" => "N",
															  "SORT" => "100"
															),
												"1" => array( "XML_ID" => "MINUS_FROM_ORDER",
															  "VALUE" => GetMessage("logictim.balls_MINUS_FROM_ORDER"),
															  "DEF" => "N",
															  "SORT" => "200"
															),
												"2" => array( "XML_ID" => "USER_BALLANCE_CHANGE",
															  "VALUE" => GetMessage("logictim.balls_USER_BALLANCE_CHANGE"),
															  "DEF" => "N",
															  "SORT" => "300"
															),
												"3" => array( "XML_ID" => "BACK_FROM_CANCEL",
															  "VALUE" => GetMessage("logictim.balls_BACK_FROM_CANCEL"),
															  "DEF" => "N",
															  "SORT" => "400"
															),
												"4" => array( "XML_ID" => "BACK_FROM_DELETTE",
															  "VALUE" => GetMessage("logictim.balls_BACK_FROM_DELETTE"),
															  "DEF" => "N",
															  "SORT" => "500"
															),
												"5" => array( "XML_ID" => "ADD_FROM_REGISTER",
															  "VALUE" => GetMessage("logictim.balls_ADD_FROM_REGISTER"),
															  "DEF" => "N",
															  "SORT" => "600"
															),
												"6" => array( "XML_ID" => "ADD_FROM_BIRTHDAY",
															  "VALUE" => GetMessage("logictim.balls_ADD_FROM_BIRTHDAY"),
															  "DEF" => "N",
															  "SORT" => "700"
															),
												"7" => array( "XML_ID" => "DEACIVATE_FROM_DATE",
															  "VALUE" => GetMessage("logictim.balls_BONUS_DEACTIVATE_FROM_DATE"),
															  "DEF" => "N",
															  "SORT" => "800"
															),
												"8" => array( "XML_ID" => "ADD_FROM_REPOST",
															  "VALUE" => GetMessage("logictim.balls_ADD_FROM_REPOST"),
															  "DEF" => "N",
															  "SORT" => "900"
															),
												"9" => array( "XML_ID" => "ADD_FROM_REVIEW",
															  "VALUE" => GetMessage("logictim.balls_ADD_FROM_REVIEW"),
															  "DEF" => "N",
															  "SORT" => "901"
															),
												"10" => array( "XML_ID" => "ADD_FROM_LINK",
															  "VALUE" => GetMessage("logictim.balls_ADD_FROM_LINK"),
															  "DEF" => "N",
															  "SORT" => "902"
															),
												"11" => array( "XML_ID" => "ADD_FROM_REFERAL",
															  "VALUE" => GetMessage("logictim.balls_ADD_FROM_REFERAL"),
															  "DEF" => "N",
															  "SORT" => "903"
															),
												"12" => array( "XML_ID" => "EXIT_BONUS",
															  "VALUE" => GetMessage("logictim.balls_EXIT_BONUS"),
															  "DEF" => "N",
															  "SORT" => "904"
															),
												"13" => array( "XML_ID" => "EXIT_REFUND_BONUS",
															  "VALUE" => GetMessage("logictim.balls_EXIT_REFUND_BONUS"),
															  "DEF" => "N",
															  "SORT" => "905"
															),
												"14" => array( "XML_ID" => "ADD_FROM_SUBSCRIBE",
															  "VALUE" => GetMessage("logictim.balls_ADD_FROM_SUBSCRIBE"),
															  "DEF" => "N",
															  "SORT" => "906"
															),
												"15" => array( "XML_ID" => "MINUS_FROM_ORDER_CANCEL",
															  "VALUE" => GetMessage("logictim.balls_BONUS_MINUS_FROM_CANCEL_ORDER"),
															  "DEF" => "N",
															  "SORT" => "907"
															),
															
							  					)
							  ),
				"OPERATION_SUM" => array(
							  "NAME" => GetMessage("logictim.balls_OPERATION_SUM"),
							  "ACTIVE" => "Y",
							  "SORT" => "200",
							  "CODE" => "OPERATION_SUM",
							  "PROPERTY_TYPE" => "N",
							  "IBLOCK_ID" => $ID,
							  "FILTRABLE" => 'Y'
							  ),
				"USER" => array(
							  "NAME" => GetMessage("logictim.balls_USER"),
							  "ACTIVE" => "Y",
							  "SORT" => "300",
							  "CODE" => "USER",
							  "PROPERTY_TYPE" => "S",
							  "USER_TYPE" => "UserID",
							  "IBLOCK_ID" => $ID,
							  "FILTRABLE" => 'Y'
							  ),
				"ORDER_ID" => array(
							  "NAME" => GetMessage("logictim.balls_ORDER_ID"),
							  "ACTIVE" => "Y",
							  "SORT" => "400",
							  "CODE" => "ORDER_ID",
							  "PROPERTY_TYPE" => "N",
							  "IBLOCK_ID" => $ID,
							  ),
				"ADD_DETAIL" => array(
							  "NAME" => GetMessage("logictim.balls_ADD_DETAIL"),
							  "ACTIVE" => "Y",
							  "SORT" => "500",
							  "CODE" => "ADD_DETAIL",
							  "PROPERTY_TYPE" => "S",
							  "USER_TYPE" => "HTML",
							  "ROW_COUNT" => 5,
							  "IBLOCK_ID" => $ID,
							  ),
				"ACTIVATE_DATE" => array(
							  "NAME" => GetMessage("logictim.balls_ACTIVATE_DATE"),
							  "ACTIVE" => "Y",
							  "SORT" => "600",
							  "CODE" => "ACTIVATE_DATE",
							  "PROPERTY_TYPE" => "S",
							  "USER_TYPE" => "DateTime",
							  "ROW_COUNT" => 1,
							  "IBLOCK_ID" => $ID,
							  ),
				"LIVE_DATE" => array(
							  "NAME" => GetMessage("logictim.balls_LIVE_DATE"),
							  "ACTIVE" => "Y",
							  "SORT" => "700",
							  "CODE" => "LIVE_DATE",
							  "PROPERTY_TYPE" => "S",
							  "USER_TYPE" => "DateTime",
							  "ROW_COUNT" => 1,
							  "IBLOCK_ID" => $ID,
							  ),
		);
		$ibp = new CIBlockProperty;
		foreach($arProps as $prop) {
			$PropID = $ibp->Add($prop);
		}
		
		
		//ADD iblock "logictim_referals"
		$arFields = Array(
			  "ACTIVE" => 'Y',
			  "NAME" => GetMessage("logictim.referals_IBLOCK_NAME"),
			  "CODE" => 'logictim_bonus_referals',
			  "IBLOCK_TYPE_ID" => 'LOGICTIM_BONUS_STATISTIC',
			  "SITE_ID" => $sites,
			  "SORT" => 800,
			  "DESCRIPTION_TYPE" => 'text',
			  "GROUP_ID" => Array("1"=>"R", "2"=>"R"),
			  "INDEX_ELEMENT" => 'N',
	  		  "INDEX_SECTION" => 'N'
			  );
		$ID = $ib->Add($arFields);
		$arProps = array(
					"REFERAL" => array(
							  "NAME" => GetMessage("logictim.referals_PROPERTY_NAME_REFERAL"),
							  "ACTIVE" => "Y",
							  "SORT" => "1",
							  "CODE" => "REFERAL",
							  "PROPERTY_TYPE" => "E",
							  "USER_TYPE" => "UserID",
							  "IBLOCK_ID" => $ID,
							  ),
					"PARTNER" => array(
							  "NAME" => GetMessage("logictim.referals_PROPERTY_NAME_PARTNER"),
							  "ACTIVE" => "Y",
							  "SORT" => "2",
							  "CODE" => "PARTNER",
							  "PROPERTY_TYPE" => "E",
							  "USER_TYPE" => "UserID",
							  "IBLOCK_ID" => $ID,
							  ),
				  );
		$ibp = new CIBlockProperty;
		foreach($arProps as $prop) {
			$PropID = $ibp->Add($prop);
		}

endif;
?>		