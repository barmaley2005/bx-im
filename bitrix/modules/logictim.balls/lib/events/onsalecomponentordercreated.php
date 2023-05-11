<?
namespace Logictim\Balls\Events;


\Bitrix\Main\Loader::includeModule("logictim.balls");

class OnSaleComponentOrderCreated {
	
	
	public static function OnSaleComponentOrderCreated($order, &$arUserResult, $request, &$arParams, &$arResult)
	{
		
		if(\COption::GetOptionString('logictim.balls', 'MODULE_VERSION', '4') < 4)
			\cLTBOnSaleComponentOrderCreated::OnSaleComponentOrderCreated($order, $arUserResult, $request, $arParams, $arResult);
		else
			\Logictim\Balls\Ajax\SaleOrderAjax::OnSaleComponentOrderCreated($order, $arUserResult, $request, $arParams, $arResult);
	}
}

?>