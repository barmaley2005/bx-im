<?
namespace Logictim\Balls\Events;

\Bitrix\Main\Loader::includeModule("logictim.balls");

class OrderAfterSaved {
	public static function OrderAfterSaved($order)
	{
		
		if(\COption::GetOptionString('logictim.balls', 'MODULE_VERSION', '4') < 4)
			\cSaleOrderSaved::SaleOrderSaved($order);
		else
			\Logictim\Balls\PayBonus\OrderAfterSaved::AddBonusPayment($order);
	}
}