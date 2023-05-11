<?php
IncludeModuleLangFile(__FILE__);
class cLTBBeforeBuferContent {
	public static function BeforeEndBufferContent()
	{
		$UserBonusSystemDostup = cHelper::UserBonusSystemDostup('');
		if($UserBonusSystemDostup == 'Y' && COption::GetOptionString("logictim.balls", "INTEGRATE_IN_SALE_BASKET", 'N') == 'Y')
		{
			global $APPLICATION;
			$APPLICATION->AddHeadScript('/bitrix/js/logictim.balls/basket.js');
			
			$warning = \COption::GetOptionString("logictim.balls", "LICENSE_CHECK_WARNING", '');
			if($warning != '')
			{
				$warning = str_replace(array("\r\n", "\n"), " ", $warning); 
				$APPLICATION->AddHeadString('<script type="text/javascript">var lt_bonus_warning = \''.$warning.'\';</script>', true);
				$APPLICATION->AddHeadScript('/bitrix/js/logictim.balls/warning.js');
				$APPLICATION->SetAdditionalCSS('/bitrix/js/logictim.balls/warning.css');
			}	
		}
    }
	
}
?>