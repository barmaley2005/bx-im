<? if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
 ?>


<div class="lb_tabs">
    <input id="tab1" type="radio" name="tabs" checked>
    <label for="tab1" title="<?=GetMessage("LOGICTIM_BONUS_OPERATIONS_LIST")?>"><?=GetMessage("LOGICTIM_BONUS_OPERATIONS_LIST")?></label>
 
    <? if($arResult["EXIT_BONUS"]["CAN_EXIT"] == 'Y') {?>
    <input id="tab2" type="radio" name="tabs">
    <label for="tab2" title="<?=GetMessage("LOGICTIM_BONUS_OPERATIONS_EXIT")?>"><?=GetMessage("LOGICTIM_BONUS_OPERATIONS_EXIT")?></label>
    <? }?>
 
 
    <section id="content-tab1">
        <div class="logictim_user_bonus"><?=GetMessage("LOGICTIM_BONUS_HAVE")?> <span><?=$arResult["USER_BONUS"]?></span></div>

		<? if($arResult["VIEW_SUBSCRIBE"] == 'Y') {?>
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
        
        <? if($arResult["VIEW_REF_LINK"] == 'Y') {?>
            <div class="logictim_user_bonus"><?=GetMessage("LOGICTIM_BONUS_REF_LINK")?>: <span><?=$arResult["REF_LINK"]?></span></div>
        <? }?>
        <?
        if($arResult["VIEW_REF_COUPON"] == 'Y')
        {
            if(!empty($arResult["COUPON"]))
                $coupon = $arResult["COUPON"];
            else
			{
				if(COption::GetOptionString("logictim.balls", "REFERAL_COUPON_CAN_USER", 'N') == 'Y')
					$coupon = '<input type="text" class="inputtext" id="enter_coupon_code" value="" placeholder="'.GetMessage("LOGICTIM_BONUS_ENTER_COUPONE_CODE").'" /><a href="#" class="btn btn-default" id="enter_coupon">'.GetMessage("LOGICTIM_BONUS_ADD_COUPONE_CODE").'</a><div id="coupon_error"></div>';
				else
					$coupon = '<span class="generate_coupon" id="generate_coupon">'.GetMessage("LOGICTIM_REFERALS_REF_COUPON_GENERATE").'</span><div id="coupon_error"></div>';
			}
        ?>
            <div class="logictim_user_bonus"><?=GetMessage("LOGICTIM_REFERALS_REF_COUPON")?>: <span id="partnet_coupon"><?=$coupon?></span></div>
        <?
        }
        ?>
        
        <? if(!empty($arResult["ITEMS"])):?>
        <div class="logictim_bonus_exit_list"><?=GetMessage("LOGICTIM_BONUS_HISTORY")?></div>
        <div class="lb_table-wrap">
        <table class="lb_history">
        	<thead>
                <tr class="logictim_table_header">
                    <? foreach($arParams["FIELDS"] as $field):
                            if($field == 'ID') echo '<th>'.GetMessage("LOGICTIM_BONUS_FIELD_ID").'</th>';
                            if($field == 'DATE') echo '<th>'.GetMessage("LOGICTIM_BONUS_FIELD_DATE").'</th>';
                            if($field == 'NAME') echo '<th>'.GetMessage("LOGICTIM_BONUS_FIELD_TYPE").'</th>';
                            if($field == 'OPERATION_SUM') echo '<th>'.GetMessage("LOGICTIM_BONUS_FIELD_SUM").'</th>';
                            if($field == 'BALLANCE_BEFORE') echo '<th>'.GetMessage("LOGICTIM_BONUS_FIELD_BEFORE").'</th>';
                            if($field == 'BALLANCE_AFTER') echo '<th>'.GetMessage("LOGICTIM_BONUS_FIELD_AFTER").'</th>';
                            if($field == 'ADD_DETAIL') echo '<th>'.GetMessage("LOGICTIM_BONUS_FIELD_DETAIL").'</th>';
                     endforeach;?>
                </tr>
            </thead>
            
            <tbody>
            <? foreach($arResult["ITEMS"] as $item):?>
                <tr <? if($item["IBLOCK_CODE"] == 'logictim_bonus_wait') { echo 'class="lb_wait"';}?>>
                    <? foreach($arParams["FIELDS"] as $field):
                        if($field == 'ID') echo '<td data-label="'.GetMessage("LOGICTIM_BONUS_FIELD_ID").'" align="center">'.$item["ID"].'</td>';
                        if($field == 'DATE') {
                            $date = $item["DATE_CREATE"];
                            $arDate = ParseDateTime($date, FORMAT_DATETIME);
                            $date = $arDate["DD"]." ".ToLower(GetMessage("MONTH_".intval($arDate["MM"])."_S")).", ".$arDate["YYYY"].'<br />'.$arDate["HH"].':'.$arDate["MI"];
                            echo '<td align="center" data-label="'.GetMessage("LOGICTIM_BONUS_FIELD_DATE").'">'.$date.'</td>';
        
                        }
                        if($field == 'NAME') { ?>
                            <td data-label="<?=GetMessage("LOGICTIM_BONUS_FIELD_TYPE")?>">
                                <? if($arParams["ORDER_LINK"] == 'Y' && $item["PROPS"]["ORDER_ID"]["VALUE"] != '') { ?>
                                    <a href="<?=$arParams["ORDER_URL"].'detail/'.$item["PROPS"]["ORDER_ID"]["VALUE"]?>/"><?=$item["NAME"]?></a>
                                <? }
								else {
									echo $item["NAME"];
								}
                                        
								if($item["PROPS"]["LIVE_DATE"]["VALUE"] != '' && $item["PROPS"]["LIVE_STATUS"]["VALUE_XML_ID"] != 'END' && $item["IBLOCK_CODE"] != 'logictim_bonus_wait')
									echo '<br/>'.GetMessage("LOGICTIM_BONUS_ACTIVE_TO").$item["PROPS"]["LIVE_DATE"]["VALUE"];
								if($item["PROPS"]["BALLANCE"]["VALUE"] != '' && $item["PROPS"]["LIVE_STATUS"]["VALUE_XML_ID"] != 'END'  && $item["PROPS"]["LIVE_STATUS"]["VALUE_XML_ID"] != 'LIVE_END')
									echo '<br/><span class="logictim_plus">'.GetMessage("LOGICTIM_BONUS_HAVE_H").$item["PROPS"]["BALLANCE"]["VALUE"].'</span>';
								if($item["PROPS"]["PAID"]["VALUE"] != '' && $item["PROPS"]["LIVE_STATUS"]["VALUE_XML_ID"] != 'END')
									echo '<br/><span class="logictim_plus">'.GetMessage("LOGICTIM_BONUS_PAID").$item["PROPS"]["PAID"]["VALUE"].'</span>';
								if($item["PROPS"]["LIVE_STATUS"]["VALUE_XML_ID"] == 'LIVE_END')
									echo '<br/><span class="logictim_minus">'.GetMessage("LOGICTIM_BONUS_LIVE_END").'</span>';
								if($item["PROPS"]["LIVE_STATUS"]["VALUE_XML_ID"] == 'END')
									echo '<br/><span class="logictim_minus">'.GetMessage("LOGICTIM_BONUS_END").'</span>';
									
								if($item["IBLOCK_CODE"] == 'logictim_bonus_wait')
								{
									echo '<br/><span class="logictim_wait">'.GetMessage("LOGICTIM_BONUS_ACTIVE_WAIT").'</span>';
									echo '<br/><span class="logictim_wait">'.GetMessage("LOGICTIM_BONUS_ACTIVE_FROM").$item["PROPS"]["ACTIVATE_DATE"]["VALUE"].'</span>';
								}
								?>
                             </td>
                            <? }
                        
                        if($field == 'OPERATION_SUM') { ?>
                            <td align="right" data-label="<?=GetMessage("LOGICTIM_BONUS_FIELD_SUM")?>">
                                <? if($item["PROPS"]["BALLANCE_BEFORE"]["VALUE"] > $item["PROPS"]["BALLANCE_AFTER"]["VALUE"])
                                        echo '<span class="logictim_minus">-'.$item["PROPS"]["OPERATION_SUM"]["VALUE"].'</span>';
                                    elseif($item["PROPS"]["BALLANCE_BEFORE"]["VALUE"] < $item["PROPS"]["BALLANCE_AFTER"]["VALUE"])
                                        echo '<span class="logictim_plus">+'.$item["PROPS"]["OPERATION_SUM"]["VALUE"].'</span>';
                                    else
                                        echo '<span>'.$item["PROPS"]["OPERATION_SUM"]["VALUE"].'</span>';?>
                            </td>
                        <? }
                        
                        if($field == 'BALLANCE_BEFORE') {?>
                            <td align="right" data-label="<?=GetMessage("LOGICTIM_BONUS_FIELD_BEFORE")?>">
                                <? if($item["PROPS"]["BALLANCE_BEFORE"]["VALUE"] != '') { 
                                        echo $item["PROPS"]["BALLANCE_BEFORE"]["VALUE"];
                                    }
                                    else 
									{
										if($item["IBLOCK_CODE"] != 'logictim_bonus_wait')
											echo '0';
									}
                                ?>
                            </td>
                        <? } 
                        if($field == 'BALLANCE_AFTER') echo '<td align="right" data-label="'.GetMessage("LOGICTIM_BONUS_FIELD_AFTER").'">'.$item["PROPS"]["BALLANCE_AFTER"]["VALUE"].'</td>';
                        if($field == 'ADD_DETAIL') {
                            if($item["PROPS"]["OPERATION_TYPE"]["VALUE_XML_ID"] == 'ADD_FROM_ORDER')
                                echo '<td data-label="'.GetMessage("LOGICTIM_BONUS_FIELD_DETAIL").'">'.$item["PROPS"]["ADD_DETAIL"]["VALUE"]["TEXT"].'</td>';
                            else
                                echo '<td data-label="'.GetMessage("LOGICTIM_BONUS_FIELD_DETAIL").'"></td>';
                        }
                     endforeach;?>
                </tr>
             <? endforeach;?>
             </tbody>
        
        </table>
        </div>
        <? endif;?>
        
        <? echo $arResult["NAV_STRING"];?>
    </section>  
    
    <? if($arResult["EXIT_BONUS"]["CAN_EXIT"] == 'Y') {?>
    <section id="content-tab2">
          <div class="logictim_user_bonus"><?=GetMessage("LOGICTIM_BONUS_HAVE")?> <span><?=$arResult["USER_BONUS"]?></span></div>
          <? if($arResult["EXIT_BONUS"]["CONDITIONS"]["MIN_EXIT_BONUS"] > 0) {?>
          <div class="logictim_user_bonus"><?=GetMessage("LOGICTIM_BONUS_MIN_EXIT")?> <span><?=$arResult["EXIT_BONUS"]["CONDITIONS"]["MIN_EXIT_BONUS"]?></span></div>
          <? }?>
          <? if($arResult["EXIT_BONUS"]["CONDITIONS"]["MAX_EXIT_BONUS"] > 0) {?>
          <div class="logictim_user_bonus"><?=GetMessage("LOGICTIM_BONUS_MAX_EXIT")?> <span><?=$arResult["EXIT_BONUS"]["CONDITIONS"]["MAX_EXIT_BONUS"]?></span></div>
          <? }?>
          
          <?=GetMessage("LOGICTIM_BONUS_EXIT_LABEL")?>
          <input type="number" id="exit_bonus_input" class="exit_bonus_input" min="" max="" value="" />
          <a href="#" onclick="return false;" id="exit_bonus_link" class="exit_link"><?=GetMessage("LOGICTIM_BONUS_EXIT_LINK")?></a>
          <div id="exit_bonus_result"></div>
          
        <div class="logictim_bonus_exit_list"><?=GetMessage("LOGICTIM_BONUS_OPERATIONS_EXIT_HISTORY")?></div>
		<div class="table-wrap">
			<table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th><?=GetMessage("LOGICTIM_BONUS_DATE_INSERT")?></th>
                        <th><?=GetMessage("LOGICTIM_BONUS_EXIT_SUM")?></th>
                        <th><?=GetMessage("LOGICTIM_BONUS_EXIT_NAME")?></th>
                        <th><?=GetMessage("LOGICTIM_BONUS_EXIT_STATUS")?></th>
                        <th><?=GetMessage("LOGICTIM_BONUS_DATE_CLOSE")?></th>
                        <th><?=GetMessage("LOGICTIM_BONUS_EXIT_COMMENT_ADMIN")?></th>
                    </tr>
                </thead>
                <tbody>
                	<? foreach($arResult["EXIT_BONUS"]["ITEMS"] as $exitQuery):?>
                    <tr>
                        <td data-label="ID"><?=$exitQuery["id"]?></td>
                        <td data-label="<?=GetMessage("LOGICTIM_BONUS_DATE_INSERT")?>"><?=$exitQuery["DATE_INSERT_FORMAT"]?></td>
                        <td data-label="<?=GetMessage("LOGICTIM_BONUS_EXIT_SUM")?>"><?=$exitQuery["sum"]?></td>
                        <td data-label="<?=GetMessage("LOGICTIM_BONUS_EXIT_NAME")?>"><?=$exitQuery["name"]?></td>
                        <td data-label="<?=GetMessage("LOGICTIM_BONUS_EXIT_STATUS")?>"><?=$arResult["EXIT_BONUS"]["STATUS"][$exitQuery["status"]]?></td>
                        <td data-label="<?=GetMessage("LOGICTIM_BONUS_DATE_CLOSE")?>"><?=$exitQuery["DATE_CLOSE_FORMAT"]?></td>
                        <td data-label="<?=GetMessage("LOGICTIM_BONUS_EXIT_COMMENT_ADMIN")?>"><?=nl2br($exitQuery["comment_admin"])?></td>
                    </tr>
                    <? endforeach;?>
                </tbody>
            </table>
        </div>
        <? echo $arResult["EXIT_BONUS"]["NAV_STRING"];?>
          
    </section> 
    <? }?>
</div>
