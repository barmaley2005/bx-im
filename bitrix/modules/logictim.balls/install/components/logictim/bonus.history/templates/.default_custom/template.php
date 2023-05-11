<? if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
 ?>

<div class="logictim_bonus_block">

<div class="logictim_user_bonus"><?=GetMessage("LOGICTIM_BONUS_HAVE")?> <span><?=$arResult["USER_BONUS"]?></span></div>

<? $liveBonus = COption::GetOptionString("logictim.balls", "LIVE_BONUS", 'N');
	if($liveBonus == 'Y') {
?>
	<div class="logictim_user_bonus">
		<?=GetMessage("LOGICTIM_BONUS_SUBSCRIBE_TEXT")?>
        <? if($arResult["UF_LGB_SUBSCRIBE"] == 1) {
			 $newUrl = $APPLICATION->GetCurPageParam("lgb_unsubscribe=Y", array('lgb_subscribe'));?>
        	<span><?=GetMessage("LOGICTIM_BONUS_YES")?></span> | <a href="<?=$newUrl?>"><?=GetMessage("LOGICTIM_BONUS_UNSUBSCRIBE")?></a>
        <? } else { $newUrl = $APPLICATION->GetCurPageParam("lgb_subscribe=Y", array('lgb_unsubscribe'));?>
        	<span><?=GetMessage("LOGICTIM_BONUS_NO")?></span> | <a href="<?=$newUrl?>"><?=GetMessage("LOGICTIM_BONUS_SUBSCRIBE")?></a>
        <? }?>
    </div>
<? }?>

<? if(COption::GetOptionString("logictim.balls", "BONUS_REFERAL", 0) > 0 || COption::GetOptionString("logictim.balls", "REFERAL_SYSTEM_TYPE", 0) > 0) {?>
	<div class="logictim_user_bonus"><?=GetMessage("LOGICTIM_BONUS_REF_LINK")?>: <span><?=$arResult["REF_LINK"]?></span></div>
<? }?>
<?
if(COption::GetOptionString("logictim.balls", "REFERAL_SYSTEM_TYPE", 0) > 0 && COption::GetOptionString("logictim.balls", "REFERAL_USE_COUPONS", 'N') == 'Y')
{
	if(!empty($arResult["COUPON"]))
		$coupon = $arResult["COUPON"];
	else
	{
		//$coupon = '<span class="generate_coupon" id="generate_coupon">'.GetMessage("LOGICTIM_REFERALS_REF_COUPON_GENERATE").'</span>';
		$coupon = '<input type="text" id="enter_coupon_code" value="" placeholder="??????? ??? ?????? ? ?????" /><a href="#" id="enter_coupon">?????????</a><div id="coupon_error"></div>';
	}
?>

    <div class="logictim_user_bonus coupon_block"><?=GetMessage("LOGICTIM_REFERALS_REF_COUPON")?>: <div id="partnet_coupon"><?=$coupon?></div></div>
<?
}
?>

<? if(!empty($arResult["ITEMS"])):?>
<div class="logictim_user_bonus"><?=GetMessage("LOGICTIM_BONUS_HISTORY")?></div>
<div class="table_block">
<table id="logictim_table" cellpadding="0" cellspacing="0">
	<tr class="logictim_table_header">
    	<? foreach($arParams["FIELDS"] as $field):
        		if($field == 'ID') echo '<td>'.GetMessage("LOGICTIM_BONUS_FIELD_ID").'</td>';
				if($field == 'DATE') echo '<td>'.GetMessage("LOGICTIM_BONUS_FIELD_DATE").'</td>';
                if($field == 'NAME') echo '<td>'.GetMessage("LOGICTIM_BONUS_FIELD_TYPE").'</td>';
				if($field == 'OPERATION_SUM') echo '<td>'.GetMessage("LOGICTIM_BONUS_FIELD_SUM").'</td>';
				if($field == 'BALLANCE_BEFORE') echo '<td>'.GetMessage("LOGICTIM_BONUS_FIELD_BEFORE").'</td>';
				if($field == 'BALLANCE_AFTER') echo '<td>'.GetMessage("LOGICTIM_BONUS_FIELD_AFTER").'</td>';
				if($field == 'ADD_DETAIL') echo '<td>'.GetMessage("LOGICTIM_BONUS_FIELD_DETAIL").'</td>';
         endforeach;?>
    </tr>
    
    <? foreach($arResult["ITEMS"] as $item):?>
    	<tr>
			<? foreach($arParams["FIELDS"] as $field):
        		if($field == 'ID') echo '<td align="center">'.$item["ID"].'</td>';
				if($field == 'DATE') {
					$date = $item["DATE_CREATE"];
					$arDate = ParseDateTime($date, FORMAT_DATETIME);
					$date = $arDate["DD"]." ".ToLower(GetMessage("MONTH_".intval($arDate["MM"])."_S")).", ".$arDate["YYYY"].'<br />'.$arDate["HH"].':'.$arDate["MI"];
					echo '<td align="center">'.$date.'</td>';
					//echo '<pre>'; print_r($arDate); echo '</pre>';

				}
                if($field == 'NAME') { ?>
                	<td>
                    	<? if($item["PROPS"]["OPERATION_TYPE"]["VALUE_XML_ID"] == 'USER_BALLANCE_CHANGE') {
								echo GetMessage("LOGICTIM_BONUS_BALLANCE_CHANGE");
							}
							elseif($item["PROPS"]["OPERATION_TYPE"]["VALUE_XML_ID"] == 'ADD_FROM_REGISTER') {
								echo GetMessage("LOGICTIM_BONUS_ADD_FROM_REGISTER");
							}
							elseif($item["PROPS"]["OPERATION_TYPE"]["VALUE_XML_ID"] == 'ADD_FROM_BIRTHDAY') {
								echo GetMessage("LOGICTIM_BONUS_ADD_FROM_BIRTHDAY");
							}
							else {
								if($arParams["ORDER_LINK"] == 'Y' && $item["PROPS"]["ORDER_ID"]["VALUE"] != '') { ?>
									<a href="<?=$arParams["ORDER_URL"].'detail/'.$item["PROPS"]["ORDER_ID"]["VALUE"]?>/"><?=$item["NAME"]?></a>
								<? }
								else {
									echo $item["NAME"];
								}
								
							}
							if($item["PROPS"]["LIVE_DATE"]["VALUE"] != '' && $item["PROPS"]["LIVE_STATUS"]["VALUE_XML_ID"] != 'END')
								echo '<br/>'.GetMessage("LOGICTIM_BONUS_ACTIVE_TO").$item["PROPS"]["LIVE_DATE"]["VALUE"];
							if($item["PROPS"]["BALLANCE"]["VALUE"] != '' && $item["PROPS"]["LIVE_STATUS"]["VALUE_XML_ID"] != 'END'  && $item["PROPS"]["LIVE_STATUS"]["VALUE_XML_ID"] != 'LIVE_END')
								echo '<br/><span class="logictim_plus">'.GetMessage("LOGICTIM_BONUS_HAVE_H").$item["PROPS"]["BALLANCE"]["VALUE"].'</span>';
							if($item["PROPS"]["PAID"]["VALUE"] != '' && $item["PROPS"]["LIVE_STATUS"]["VALUE_XML_ID"] != 'END')
								echo '<br/><span class="logictim_plus">'.GetMessage("LOGICTIM_BONUS_PAID").$item["PROPS"]["PAID"]["VALUE"].'</span>';
							if($item["PROPS"]["LIVE_STATUS"]["VALUE_XML_ID"] == 'LIVE_END')
								echo '<br/><span class="logictim_minus">'.GetMessage("LOGICTIM_BONUS_LIVE_END").'</span>';
							if($item["PROPS"]["LIVE_STATUS"]["VALUE_XML_ID"] == 'END')
								echo '<br/><span class="logictim_minus">'.GetMessage("LOGICTIM_BONUS_END").'</span>';
							?>
                     </td>
					<? }
				
				if($field == 'OPERATION_SUM') { ?>
					<td align="right">
                    	<? if($item["PROPS"]["BALLANCE_BEFORE"]["VALUE"] > $item["PROPS"]["BALLANCE_AFTER"]["VALUE"])
					 			echo '<span class="logictim_minus">-'.$item["PROPS"]["OPERATION_SUM"]["VALUE"].'</span>';
							elseif($item["PROPS"]["BALLANCE_BEFORE"]["VALUE"] < $item["PROPS"]["BALLANCE_AFTER"]["VALUE"])
								echo '<span class="logictim_plus">+'.$item["PROPS"]["OPERATION_SUM"]["VALUE"].'</span>';
							else
								echo '<span>'.$item["PROPS"]["OPERATION_SUM"]["VALUE"].'</span>';?>
					</td>
				<? }
				
				if($field == 'BALLANCE_BEFORE') {?>
                	<td align="right">
						<? if($item["PROPS"]["BALLANCE_BEFORE"]["VALUE"] != '') { 
								echo $item["PROPS"]["BALLANCE_BEFORE"]["VALUE"];
							}
							else { echo '0';}
						?>
					</td>
                <? } 
				if($field == 'BALLANCE_AFTER') echo '<td align="right">'.$item["PROPS"]["BALLANCE_AFTER"]["VALUE"].'</td>';
				if($field == 'ADD_DETAIL') {
					if($item["PROPS"]["OPERATION_TYPE"]["VALUE_XML_ID"] == 'ADD_FROM_ORDER')
						echo '<td>'.$item["PROPS"]["ADD_DETAIL"]["VALUE"]["TEXT"].'</td>';
					else
						echo '<td></td>';
				}
        	 endforeach;?>
        </tr>
	 <? endforeach;?>

</table>
</div>
<? endif;?>

<? echo $arResult["NAV_STRING"];?>

<? //echo '<pre>'; print_r($arResult); echo '</pre>';?>
</div>
