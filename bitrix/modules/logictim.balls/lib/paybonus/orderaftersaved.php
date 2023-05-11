<?
namespace Logictim\Balls\PayBonus;

IncludeModuleLangFile(__FILE__);

use Bitrix\Sale;

class OrderAfterSaved {
	public static function AddBonusPayment($order)
	{
		$is_new = $order->isNew();
		$fields = $order->GetFields();
		$values = $fields->GetValues();
		$user_id = $values["USER_ID"];
		$order_id = $values["ID"];
		$order_num = $values["ACCOUNT_NUMBER"];
		
		//--- ADD REFERAL FROM COUPON ---//
		$discountData = $order->getDiscount()->getApplyResult();
		if(!empty($discountData["COUPON_LIST"]))
		{
			foreach($discountData["COUPON_LIST"] as $coupon):
				$partnerId = \LBReferalsApi::GetPartnerFromCoupon($coupon["COUPON"]);
			endforeach;
			
			if($partnerId > 0)
				\LBReferalsApi::AddReferal($referalId = $user_id, $partnerId);
		}
		//--- ADD REFERAL FROM COUPON ---//
		if($user_id > 0):
			$UserBallance = \Logictim\Balls\Helpers::UserBallance($user_id);
			
			$props = $order->getPropertyCollection();
			foreach($props as $prop) 
			{
				$fields = $prop->GetFields();
				$values = $fields->GetValues();
				if($values["CODE"] == 'LOGICTIM_PAYMENT_BONUS')
					$pay_bonus = $values["VALUE"];
			}
			
			if($is_new && $UserBallance > 0 && $pay_bonus > 0):
				
				//Poluchaem ID platega bonusami po zakazu
				$paymentCollection = $order->getPaymentCollection();
				foreach($paymentCollection as $arPayment):
					$fields = $arPayment->GetFields();
					$values = $fields->GetValues();
					$paySystemId = \cHelper::PaySystemBonusId();
					if($values["PAY_SYSTEM_ID"] == $paySystemId)
						$paymentId = $values["ID"];
				endforeach;
				
				//Sozdaem operaciyu spisaniya
				$arFields = array(
				  "MINUS_BONUS" => $pay_bonus,
				  "USER_ID" => $user_id,
				  "OPERATION_TYPE" => 'MINUS_FROM_ORDER',
				  "OPERATION_NAME" => GetMessage("logictim.balls_BONUS_FROM_ORDER_NUM").$order_num,
				  "ORDER_ID" => $order_id,
				  "PAYMENT_ID" => $paymentId,
				  "DETAIL_TEXT" => GetMessage("logictim.balls_BONUS_FROM_ORDER"),
				);
	
				\logictimBonusApi::MinusBonus($arFields);
				
				
			endif;
		endif;
	}
}