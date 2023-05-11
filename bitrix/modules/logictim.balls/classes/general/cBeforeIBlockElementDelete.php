<?php
IncludeModuleLangFile(__FILE__);

class cBeforeIBlockElementDelete {
    static $MODULE_ID="logictim.balls";
    
	static function BeforeIBlockElementDelete($ID)
	{
		if(CModule::IncludeModule("iblock")):
		
			$iblokOperationsId = cHelper::IblokOperationsId(); //Opredelyaem ID ibfobloka s operaciyami
		
			//Take ID of infoblock from elevrnt delete
			$dbElement = CIBlockElement::GetByID($ID);
			if($arElement = $dbElement->GetNext()) {$elIblokId = $arElement["IBLOCK_ID"];}
			  
				
			if($elIblokId == $iblokOperationsId)
			{
				global $APPLICATION;
				$APPLICATION->throwException(GetMessage("logictim.balls_DEL_ELEMENT"));
				return false;
			}
		endif;
    }
	
}
?>