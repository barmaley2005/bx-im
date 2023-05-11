<? if(!defined("B_PROLOG_INCLUDED")||B_PROLOG_INCLUDED!==true)die();


$arSelReferalSocials = array(
					'REFERENCE_ID' => array('vkontakte','facebook','odnoklassniki','moimir','gplus','twitter','lj','viber','whatsapp','skype','telegram'), 
					'REFERENCE' => array(
										
										
										
										
										
										
										
										
										
										
										)
					);

$arComponentParameters = array(
"GROUPS" => array(
	"DATA" => array(
         "NAME" => GetMessage("LOGICTIM_BONUS_DATA"),
      ),
   ),
	"PARAMETERS" => array(
		"SOCIALS" => array(
							"PARENT" => "DATA",
							"NAME" =>  GetMessage("logictim.balls_BONUS_SOCIALS_NETWORK"),
							"TYPE" => "LIST",
							"MULTIPLE" => "Y",
							"ADDITIONAL_VALUES" => "N",
							"SIZE" => 11,
							"VALUES" => array(
												'vkontakte' => GetMessage("logictim.balls_BONUS_REFERAL_NAME_VK"),
												'facebook' => GetMessage("logictim.balls_BONUS_REFERAL_NAME_FB"),
												'odnoklassniki' => GetMessage("logictim.balls_BONUS_REFERAL_NAME_OK"),
												'moimir' => GetMessage("logictim.balls_BONUS_REFERAL_NAME_MM"),
												'gplus' => GetMessage("logictim.balls_BONUS_REFERAL_NAME_GP"),
												'twitter' => GetMessage("logictim.balls_BONUS_REFERAL_NAME_TW"),
												'lj' => GetMessage("logictim.balls_BONUS_REFERAL_NAME_LJ"),
												'viber' => GetMessage("logictim.balls_BONUS_REFERAL_NAME_VB"),
												'whatsapp' => GetMessage("logictim.balls_BONUS_REFERAL_NAME_WA"),
												'skype' => GetMessage("logictim.balls_BONUS_REFERAL_NAME_SK"),
												'telegram' => GetMessage("logictim.balls_BONUS_REFERAL_NAME_TE"),
											),
							'DEFAULT' => array('vkontakte','facebook','odnoklassniki','moimir','gplus','twitter','lj','viber','whatsapp','skype','telegram'), 
							)
	),
);
?>
