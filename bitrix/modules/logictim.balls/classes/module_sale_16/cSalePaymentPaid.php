<?
use Bitrix\Main;
use Bitrix\Sale;
Main\Loader::includeModule("sale");
IncludeModuleLangFile(__FILE__);

class cSalePaymentPaid
{
	public static function SalePaymentPaid($order)
	{
			//Sozdaem operaciyu vozvrata
			//		$newOperation = new CIBlockElement;
//					$newOperationArray = Array(
//											//"MODIFIED_BY"    =>  $GLOBALS['USER']->GetID(), 
//											"IBLOCK_SECTION" => false,          
//											"IBLOCK_ID"      => 40,
//											"IBLOCK_CODE "   => 'logictim_bonus_operations',
//											"NAME"           => 'cccccccc',
//											"ACTIVE"         => "Y"
//											);
//					if($newOperation->Add($newOperationArray));
		}
		
		
}
?>