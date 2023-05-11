<?
namespace Logictim\Balls\Events;

\Bitrix\Main\Loader::includeModule("logictim.balls");

class OrderBeforeSaved {
	public static function OrderBeforeSaved($order)
	{
		
		if(\COption::GetOptionString('logictim.balls', 'MODULE_VERSION', '4') < 4)
			\cSaleOrderBeforeSaved::SaleOrderBeforeSaved($order);
		else
			\Logictim\Balls\PayBonus\OrderBeforeSaved::AddBonusPayment($order);
	}
}