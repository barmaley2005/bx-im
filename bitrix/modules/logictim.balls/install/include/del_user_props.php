<?
		if (CModule::IncludeModule("main"))
			{
				$dbUserProp = CUserTypeEntity::GetList(array($by=>$order), array("FIELD_NAME" => "UF_LOGICTIM_BONUS"));
				while($userProp = $dbUserProp->Fetch())
				{
					$oUserTypeEntity    = new CUserTypeEntity();
					$oUserTypeEntity->Delete($userProp["ID"]); 
				}
				//DEL Categories props
				$dbUserProp = CUserTypeEntity::GetList(array($by=>$order), array("FIELD_NAME" => "UF_LOGICTIM_BONUS_NO"));
				while($userProp = $dbUserProp->Fetch())
				{
					$oUserTypeEntity    = new CUserTypeEntity();
					$oUserTypeEntity->Delete($userProp["ID"]); 
				}
				$dbUserProp = CUserTypeEntity::GetList(array($by=>$order), array("FIELD_NAME" => "UF_LOGICTIM_BONUS_NP"));
				while($userProp = $dbUserProp->Fetch())
				{
					$oUserTypeEntity    = new CUserTypeEntity();
					$oUserTypeEntity->Delete($userProp["ID"]); 
				}
				//DEL Prop of subscribe
				$dbUserProp = CUserTypeEntity::GetList(array($by=>$order), array("FIELD_NAME" => "UF_LGB_SUBSCRIBE"));
				while($userProp = $dbUserProp->Fetch())
				{
					$oUserTypeEntity    = new CUserTypeEntity();
					$oUserTypeEntity->Delete($userProp["ID"]); 
				}
			}
?>