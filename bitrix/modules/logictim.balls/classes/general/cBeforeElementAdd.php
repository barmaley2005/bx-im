<?php
IncludeModuleLangFile(__FILE__);
class cLBBeforeElementAdd {
    static $MODULE_ID="logictim.balls";
    
	//dobavlenie elementa istorii operaciy
	static function OnBeforeIBlockElementAdd(&$arFields)
	{
		CModule::IncludeModule("iblock");
		
		$iblokOperationsId = cHelper::IblokOperationsId(); //Opredelyaem ID ibfobloka s operaciyami
		$iblokWaitId = cHelper::IblokWaitId(); //Opredelyaem ID ibfobloka s operaciyami ojidaniya
			
		//Zapret dobavleniya istorii operaciy && Zapret dobavleniya operaciy ojidaniya
		if($arFields["IBLOCK_ID"] == $iblokOperationsId || $arFields["IBLOCK_ID"] == $iblokWaitId)
		{
			
			if($arFields["CODE"] != 'API_OPERATIONS')
			{
				global $APPLICATION;
				$APPLICATION->throwException(GetMessage("logictim.balls_ADD_ELEMENT"));
				
					return false;
			}
			$arFields["CODE"] = '';
		}		
		
		
    }
	
}
?>