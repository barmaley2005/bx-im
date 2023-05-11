<?php
class cAfterUserRegister {
    static $MODULE_ID="logictim.balls";
    
	static function AfterUserRegister($arFields)
	{
		//echo '<pre>'; print_r($arFields); echo '</pre>'; die();
		if(COption::GetOptionString("logictim.balls", "USER_REGISTER", 'ADD') == 'REGISTER')
			cBonusFromRegister::BonusFromRegister($arFields);			
    }
	
}
?>