<?
namespace Logictim\Balls\Events;


\Bitrix\Main\Loader::includeModule("logictim.balls");

class ChangeOrderStatus {
	
	public static function ChangeOrderStatus($order)
	{
		if(\COption::GetOptionString('logictim.balls', 'MODULE_VERSION', '4') < 4)
			\cSaleStatusOrderChange::SaleStatusOrderChange($order);
		else
		{
			$fields = $order->GetFields();
			$values = $fields->GetValues();
			$order_id = $values["ID"];
			$order_status = $values["STATUS_ID"];
			
			if($order_status ==  \COption::GetOptionString("logictim.balls", "ORDER_STATUS", '') )
				\Logictim\Balls\AddBonus\FromOrder::BonusFromOrder($order);
		}
		
	}
	
}
