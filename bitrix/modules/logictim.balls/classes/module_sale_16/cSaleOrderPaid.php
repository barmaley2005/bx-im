<?php
use Bitrix\Main;
use Bitrix\Sale;
Main\Loader::includeModule("sale");
IncludeModuleLangFile(__FILE__);

class cSaleOrderPaid
{
	public static function SaleOrderPaid($order)
	{
		$fields = $order->GetFields();
		$values = $fields->GetValues();
		
		if($values["PAYED"] == 'Y' && COption::GetOptionString("logictim.balls", "EVENT_ORDER_PAYED", 'Y') == 'Y') 
		{
			if(COption::GetOptionString('logictim.balls', 'MODULE_VERSION', '4') < 4)
				BonusFromOrderAdd::BonusAdd($order, array("EVENT_ORDER_PAID" => 'Y'));
			else
				\Logictim\Balls\AddBonus\FromOrder::BonusFromOrder($order, array("EVENT_ORDER_PAID" => 'Y'));
		}
	}
}