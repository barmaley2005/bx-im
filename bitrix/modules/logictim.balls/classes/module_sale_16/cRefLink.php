<?
use Bitrix\Main;
use Bitrix\Sale;
use Bitrix\Main\Application;
Main\Loader::includeModule("sale");
Main\Loader::includeModule("iblock");
Main\Loader::includeModule("main");
IncludeModuleLangFile(__FILE__);

class cRefLink
{
	public static function RefLink()
	{
		GLOBAL $APPLICATION;
		$request = Application::getInstance()->getContext()->getRequest();
		$referal = htmlspecialchars($request->getQuery("ref"));
		
		if($referal > 0)
			$referal;
		else
			return;
		
		//DLYA referalnoy sistemi
		if($referal > 0)
		{
			session_start();
				$_SESSION['LT_BONUS_REFERAL'] = $referal;
			session_write_close();
				setcookie("LT_BONUS_REFERAL", $referal, time() + 86400);
		}
		
		//Dlya bonusov za perehod
		if(COption::GetOptionString('logictim.balls', 'MODULE_VERSION', '4') < 4)
		{
			$bonusAdd = COption::GetOptionString("logictim.balls", "BONUS_REFERAL", 0);	
			$UserBonusSystemDostup = cHelper::UserBonusSystemDostup($referal);
		}
		else
		{
			$rsUser = CUser::GetByID($referal);
			$arUser = $rsUser->Fetch();
			$arUser["USER_GROUPS"] = \CUser::GetUserGroup($referal);
			$arParams = array(
									"PROFILE_TYPE" => 'reflink',
									"SITE_ID" => $arUser["LID"],
									"USER_GROUPS" => $arUser["USER_GROUPS"],
									"LIMIT" => 1,
									"SORT_FIELD_1" => 'sort',
									"SORT_ORDER_1" => 'DESC',
								);
			$arProfiles = \Logictim\Balls\Profiles::getProfiles($arParams);
			$arProfile = end($arProfiles);
			$bonusAdd = (float)$arProfile["add_bonus"];
			$UserBonusSystemDostup = 'Y';
		}
		
		
		if($referal > 0 && $bonusAdd > 0):
		
			//Opredelyaem botov
			$bots = array(
					'rambler','googlebot','aport','yahoo','msnbot','turtle','mail.ru','omsktele',
					'yetibot','picsearch','sape.bot','sape_context','gigabot','snapbot','alexa.com',
					'megadownload.net','askpeter.info','igde.ru','ask.com','qwartabot','yanga.co.uk',
					'scoutjet','similarpages','oozbot','shrinktheweb.com','aboutusbot','followsite.com',
					'dataparksearch','google-sitemaps','appEngine-google','feedfetcher-google',
					'liveinternet.ru','xml-sitemaps.com','agama','metadatalabs.com','h1.hrn.ru',
					'googlealert.com','seo-rus.com','yaDirectBot','yandeG','yandex',
					'yandexSomething','Copyscape.com','AdsBot-Google','domaintools.com',
					'Nigma.ru','bing.com','dotnetdotcom'
			);
			foreach($bots as $bot):
				if(stripos($_SERVER['HTTP_USER_AGENT'], $bot) !== false)
					return true;
			endforeach;
		
			$CanAddBonus = 'Y'; //Peremennaya, dlya proverki razresheniya nachisleniya
			
			//Proveryaem v kukah i sessii nalichie fakta nachisleniya za perehod etogo usera
			session_start();
			if($_SESSION['LT_BONUS_SESSION_ID'] > 0 || $_COOKIE["LT_BONUS_COOKE"] > 0)
				$CanAddBonus = 'N';
				
			//Proveryaem, est' li u referala dostup k bonusnoy sisteme
			if($UserBonusSystemDostup != 'Y')
				$CanAddBonus = 'N';
				
			//Esli kukov i sessii net, to dopolnitelno proveryaem po IP
			if($CanAddBonus == 'Y')
			{
				$iblokLinksId = cHelper::IblokLinksId(); //id infobloka perehodov
				$arSelectLink = Array("ID", "IBLOCK_ID", "NAME", "DATE_CREATE", "PROPERTY_IP");
				$arFilterLink = Array("IBLOCK_ID" => $iblokLinksId, "ACTIVE" => "Y", "PROPERTY_IP" => $_SERVER['REMOTE_ADDR'], ">=DATE_CREATE" => ConvertTimeStamp(time()-86400, "FULL"));
				$resLink = CIBlockElement::GetList(array("ID" => "DESC"), $arFilterLink, false, Array("nPageSize"=>1), $arSelectLink);
				while($obLink = $resLink->GetNextElement())
				{
					$arFieldsLink = $obLink->GetFields();
				}
				if($arFieldsLink["ID"] > 0)
					$CanAddBonus = 'N';
			}
			
			
			if($CanAddBonus == 'Y'):
				//Add Link
				$newLink = new CIBlockElement;
				$PROP = array();
				$PROP["REFERAL"] = $referal;
				$PROP["REF_LINK"] = $_SERVER["HTTP_REFERER"];
				$PROP["URL"] = $APPLICATION->GetCurPage();
				$PROP["IP"] = $_SERVER['REMOTE_ADDR'];
				$newLinkArray = Array(
								"MODIFIED_BY"    =>  $referal, 
								"IBLOCK_SECTION" => false,          
								"IBLOCK_ID"      => $iblokLinksId,
								"IBLOCK_CODE "   => 'logictim_bonus_links',
								"PROPERTY_VALUES"=> $PROP,
								"NAME"           => GetMessage("logictim.balls_BONUS_REF_LINK_NAME"),
								"ACTIVE"         => "Y"
								);
				if($LinkId = $newLink->Add($newLinkArray));
				
				//Add bonus
				$arFields = array(
							"ADD_BONUS" => $bonusAdd,
							"USER_ID" => $referal,
							"OPERATION_TYPE" => 'ADD_FROM_LINK',
							"OPERATION_NAME" => GetMessage("logictim.balls_BONUS_FROM_REF_LINK"),
							"ACTIVE_AFTER" => $arProfile["active_after_period"],
						    "ACTIVE_AFTER_TYPE" => $arProfile["active_after_type"],
						    "DEACTIVE_AFTER" => $arProfile["deactive_after_period"],
						    "DEACTIVE_AFTER_TYPE" => $arProfile["deactive_after_type"],
							"SERVICE_INFO" => 'IBLOCK_LINKS_ID: '.$iblokLinksId.'; LINK_ID: '.$LinkId,
							"MAIL_EVENT" => array(
													"EVENT_NAME" => "LOGICTIM_BONUS_FROM_LINK",
													"CUSTOM_FIELDS" => array(
																			"URL" => $APPLICATION->GetCurPage(),
																			"REF_LINK" => $_SERVER["HTTP_REFERER"],
																			),
												),
							"SMS_EVENT" => array(
                                      "EVENT_NAME" => "LOGICTIM_BONUS_FROM_LINK_SMS",
									  "CUSTOM_FIELDS" => array(
																			"URL" => $APPLICATION->GetCurPage(),
																			"REF_LINK" => $_SERVER["HTTP_REFERER"],
																			),
                                        )
						);
				$operationId = logictimBonusApi::AddBonus($arFields);
				if($operationId > 0)
					CIBlockElement::SetPropertyValuesEx($LinkId, false, array("OPERATION_ID" => $operationId));
				
				//Esli bonusi nachisleni, to ustanavlivaem kuki i sessiyu
				if($operationId > 0)
				{
					setcookie("LT_BONUS_COOKE", $referal, time() + 86400);
					
					$_SESSION['LT_BONUS_SESSION_ID'] = $referal;
				}
			endif;
			
			
		endif;
		
		
		
		//echo '<pre>'; print_r($_SERVER); echo '</pre>';
		
		
	}
}
?>