<?php
use Bitrix\Main;
use Bitrix\Sale;
Main\Loader::includeModule("sale");
IncludeModuleLangFile(__FILE__);

class cSaleStatusOrderChange
{
	public static function SaleStatusOrderChange($order)
	{
		$fields = $order->GetFields();
		$values = $fields->GetValues();
		
		$order_id = $values["ID"];
		$order_status = $values["STATUS_ID"];
		
		if($order_status == 'F' && COption::GetOptionString("logictim.balls", "EVENT_ORDER_END", 'Y') == 'Y') {
			BonusFromOrderAdd::BonusAdd($order);
			
		}
	}
}