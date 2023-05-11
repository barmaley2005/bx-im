<?
namespace Logictim\Balls;

use Bitrix\Main\Localization\Loc;
Loc::loadMessages(__FILE__);

\CModule::IncludeModule("sale");


class OrderViewTab {
	public static function OrderTab()
	{
		
		return array(
            "TABSET" => "MyTabBalls",
            "GetTabs" => array("\Logictim\Balls\OrderViewTab", "mygetTabs"),
            "ShowTab" => array("\Logictim\Balls\OrderViewTab", "myshowTab"),
            "Action" => array("\Logictim\Balls\OrderViewTab", "myaction"),
            "Check" => array("\Logictim\Balls\OrderViewTab", "mycheck"),
        ); 

	}
	
	public static function myaction($arArgs)
    {
        // Event after save order ( true / false)
        // Add message in tesult $GLOBALS["APPLICATION"]->ThrowException("ERROR TEXT!!!", "ERROR");
        return true;
    }
    public static function mycheck($arArgs)
    {
        // Check before save order ( true / false)
        return true;
    }

    public static function mygetTabs($arArgs)
    {
            return array(array("DIV" => "balls_order", "TAB" => GetMessage("logictim.balls.orderviewtab_title"),
            "ICON" => "sale", "TITLE" => GetMessage("logictim.balls.orderviewtab_title"),
 	   		 "SORT" => 1000));
    }

    public static function myshowTab($divName, $arArgs, $bVarsFromForm)
    {
        if ($divName == "balls_order")
        {
			$orderId = $arArgs["ID"];
			$order = \Bitrix\Sale\Order::load($orderId);
			$user_id = $order->getUserId();
			
			$UserBallance = \Logictim\Balls\Helpers::UserBallance($user_id);
			$UserBallance = ($UserBallance == '' ? 0 : $UserBallance);
			
			$arPayBonus = \Logictim\Balls\Api\OrderPayBonus::CalculateOrderPayBonus($orderId);
			
            ?>
            <tr>
            	<div class="lt_pay_block">
                	<div class="lt_pay_block_title"><?=GetMessage("logictim.balls.orderviewtab_PAY_TITLE");?></div>
                    <div>
                    	<?=GetMessage("logictim.balls.orderviewtab_user_balls")?>: <?=$UserBallance?>
                    </div>
                    <div class="lt_can_use_bonus">
                    	<b><?=GetMessage("logictim.balls.orderviewtab_can_use")?></b>
                    </div>
                    <div>
                    	<span><?=GetMessage("logictim.balls.orderviewtab_can_use_min")?>: <?=$arPayBonus['MIN_ORDER_PAY']?></span><br />
                        <span><?=GetMessage("logictim.balls.orderviewtab_can_use_max")?>: <?=$arPayBonus['MAX_ORDER_PAY']?></span>
                    </div>
                    <div>
                    	<input type="text" name="lt_pay_bonus" id="lt_pay_bonus" value="<?=$arPayBonus['MAX_ORDER_PAY']?>" onchange="CheckBonusSum();"> 
            			<button name="lt_pay_bonus_button" id="lt_pay_bonus_button" onclick="LtPayBonus(); return false;" class="adm-btn"><?=GetMessage("logictim.balls.orderviewtab_BUTTON_PAY")?></button>
                    </div>
                    <div class="lt_pay_coomment"><?=GetMessage("logictim.balls.orderviewtab_PAY_COMMENT")?></div>
                </div>
                
                
                <?
				$iblokOperationsId = \cHelper::IblokOperationsId();
				$operationsType = \cHelper::OperationsType();
				$dbOperations = \CIBlockElement::GetList(
								array("ID" => "DESC"), 
								array
								(
									"IBLOCK_ID"=>$iblokOperationsId, 
									"ACTIVE"=>"Y", 
									"PROPERTY_ORDER_ID" => $orderId, 
								), 
								false, 
								false
							);
				$arOperations = array();
				while($Op = $dbOperations->GetNextElement())
				{
					 $OperationFields = $Op->GetFields();
					 $OperationFields['PROPS'] = $Op->GetProperties();
					 $arOperations[] = $OperationFields;
				}
				//echo '<pre>'; print_r($arOperations); echo '</pre>';
				?>
                <div class="lt_bonus_table">
                	<div class="lt_pay_block_title"><?=GetMessage("logictim.balls.orderviewtab_TABLE_NAME")?></div>
                	<?php /*?><table class="adm-list-table"><?php */?>
                    	<thead>
                        	<tr class="adm-list-table-header">
                            	<td class="adm-list-table-cell adm-list-table-cell-sort">
                                	<div class="adm-list-table-cell-inner"><?=GetMessage("logictim.balls.orderviewtab_TABLE_DATE")?></div>
                                </td>
                                <td class="adm-list-table-cell adm-list-table-cell-sort">
                                	<div class="adm-list-table-cell-inner"><?=GetMessage("logictim.balls.orderviewtab_TABLE_CREATE")?></div>
                                </td>
                                <td class="adm-list-table-cell adm-list-table-cell-sort">
                                	<div class="adm-list-table-cell-inner"><?=GetMessage("logictim.balls.orderviewtab_TABLE_OPERATION")?></div>
                                </td>
                                <td class="adm-list-table-cell adm-list-table-cell-sort">
                                	<div class="adm-list-table-cell-inner"><?=GetMessage("logictim.balls.orderviewtab_TABLE_OPERATION_SUM")?></div>
                                </td>
                                <td class="adm-list-table-cell adm-list-table-cell-sort">
                                	<div class="adm-list-table-cell-inner"><?=GetMessage("logictim.balls.orderviewtab_TABLE_BONUS_BEFORE")?></div>
                                </td>
                                <td class="adm-list-table-cell adm-list-table-cell-sort">
                                	<div class="adm-list-table-cell-inner"><?=GetMessage("logictim.balls.orderviewtab_TABLE_BONUS_AFTER")?></div>
                                </td>
                            </tr>
                        </thead>
                        
                        <tbody>
                        	<? if(!empty($arOperations)):?>
                            <? foreach($arOperations as $arOperation):
								$DBUser = \CUser::GetList(($by="ID"),($order="desc"),array("ID" => $arOperation['CREATED_BY']), array());
								while($arUser = $DBUser->Fetch())
								{
									$userName = trim(($arUser["LAST_NAME"] != '' ? $arUser["LAST_NAME"] : '').($arUser["NAME"] != '' ? ' '.$arUser["NAME"] : ''));
									$userLogin = $arUser["LOGIN"];
									$arUserLabel = ($userName == '' ? $userLogin : $userName);
								}
							?>
                        	<tr class="adm-list-table-row">
                            	<td class="adm-list-table-cell lt_bonus_cell"><?=$arOperation['DATE_CREATE'];?></td>
                                <td class="adm-list-table-cell lt_bonus_cell"><a href="/bitrix/admin/user_edit.php?ID=<?=$arOperation['CREATED_BY']?>"><?=$arUserLabel;?></a></td>
                                <td class="adm-list-table-cell lt_bonus_cell"><?=$arOperation['PROPS']['OPERATION_TYPE']['VALUE'];?></td>
                                <td class="adm-list-table-cell lt_bonus_cell"><?=$arOperation['PROPS']['OPERATION_SUM']['VALUE'];?></td>
                                <td class="adm-list-table-cell lt_bonus_cell"><?=$arOperation['PROPS']['BALLANCE_BEFORE']['VALUE'];?></td>
                                <td class="adm-list-table-cell lt_bonus_cell"><?=$arOperation['PROPS']['BALLANCE_AFTER']['VALUE'];?></td>
                            </tr>
                            <? endforeach;?>
                            <? endif;?>
                        </tbody>
                    <?php /*?></table><?php */?>
                </div>
                
            </tr>
            <style>
				.lt_pay_block {
					width:500px;
					margin:0 auto;
					padding: 20px;
					background-color:#cdeb8e;
				}
				.lt_pay_block_title {
					font-weight:bold;
					font-size:16px;
					text-align: center;
					margin-bottom: 15px;
				}
				.lt_can_use_bonus {
					text-align: center;
					margin-top: 10px;
					margin-bottom:5px;
				}
				.lt_pay_coomment {
					margin-top: 15px;
					font-style: italic;
					font-size: 12px;
				}
				.lt_bonus_table {
					margin-top:20px;
					margin-bottom:20px;
				}
				td.adm-list-table-cell.lt_bonus_cell.adm-detail-content-cell-l, td.adm-list-table-cell.lt_bonus_cell.adm-detail-content-cell-r {
					padding: 11px 0 10px 16px;
				}
			</style>
            
            
            
            
            
            <script type="text/javascript">
				var arPayBonus = <?=\CUtil::PhpToJSObject($arPayBonus, false, true)?>;
				function CheckBonusSum()
				{
					var bonus = parseFloat(BX('lt_pay_bonus').value);
					if(bonus < parseFloat(arPayBonus['MIN_ORDER_PAY']))
						bonus = arPayBonus['MIN_ORDER_PAY'];
					if(bonus > parseFloat(arPayBonus['MAX_ORDER_PAY']))
						bonus = arPayBonus['MAX_ORDER_PAY'];
					BX('lt_pay_bonus').value = bonus;
				}
				function LtPayBonus()
				{
					var bonus = BX('lt_pay_bonus').value;
					var data = new Object();
					data["TYPE"] = 'PAY_ORDER_BONUS';
					data["BONUS"] = bonus;
					data["ORDER_ID"] = <?=$orderId?>;
					BX.ajax({
						url: '/bitrix/components/logictim/bonus.ajax/bonus_api_ajax.php',
						method: 'POST',
						data: data,
						dataType: 'json',
						onsuccess: function(result) {
							//console.log(result);
							window.location.reload();
						}
					});
					
				}
			</script>	
            
            
		<?
        }
    }
}


?>

