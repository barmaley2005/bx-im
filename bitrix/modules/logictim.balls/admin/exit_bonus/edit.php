<? require($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/main/include/prolog_admin_after.php');

use \Bitrix\Main\Application,
	Bitrix\Main\Localization\Loc;
?>

<style type="text/css">
.lb_descript {
	font-size:14px;
	font-weight:bold;
	margin-bottom:10px;
	text-align:center;
}
.lb_description p {
	color:grey !important;
	font-style:italic !important;
}
.close_exit {
	height: 29px;
	padding: 0px 13px 0px;
    margin: 2px;
	-webkit-border-radius: 4px;
    border-radius: 4px;
    border: none;
    -webkit-box-shadow: 0 0 1px rgba(0,0,0,.11), 0 1px 1px rgba(0,0,0,.3), inset 0 1px #fff, inset 0 0 1px rgba(255,255,255,.5);
    box-shadow: 0 0 1px rgba(0,0,0,.3), 0 1px 1px rgba(0,0,0,.3), inset 0 1px 0 #fff, inset 0 0 1px rgba(255,255,255,.5);
    background-color: #e0e9ec;
    background-image: -webkit-linear-gradient(bottom, #d7e3e7, #fff)!important;
    background-image: -moz-linear-gradient(bottom, #d7e3e7, #fff)!important;
    background-image: -ms-linear-gradient(bottom, #d7e3e7, #fff)!important;
    background-image: -o-linear-gradient(bottom, #d7e3e7, #fff)!important;
    background-image: linear-gradient(bottom, #d7e3e7, #fff)!important;
    color: #3f4b54;
    cursor: pointer;
    display: inline-block;
    font-family: "Helvetica Neue",Helvetica,Arial,sans-serif;
    font-weight: bold;
    font-size: 13px;
    text-shadow: 0 1px rgba(255,255,255,0.7);
    text-decoration: none;
    position: relative;
    vertical-align: middle;
    -webkit-font-smoothing: antialiased;
	line-height: 29px !important;
		
	
}
.close_exit:hover {
	text-decoration: none;
    background: #f3f6f7!important;
}
</style>
	
<?	
$exitId = (int)$request['id'];
$res=$DB->Query('select * from '.$arTable["TABLE_NAME"].' where id='.$exitId.';');
$arExit = $res->Fetch();
//echo '<pre>'; print_r($arExit); echo '</pre>';
require_once($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/logictim.balls/admin/header.php');


$aTabs = array(
	array("DIV" => "logictim_balls_tab_1", "TAB" => GetMessage("logictim.balls_EXIT_TAB_1"), "TITLE" => GetMessage("logictim.balls_EXIT_TAB_1")),
);
$tabControl = new CAdminTabControl("tabControl".$profileId, $aTabs);

$arStatuses = array(
					"P" => GetMessage("logictim.balls_EXIT_STATUS_P"),
					"F" => GetMessage("logictim.balls_EXIT_STATUS_F"),
					"C" => GetMessage("logictim.balls_EXIT_STATUS_C")
					);
					
if(!empty($request['close']) && !empty($request['id']) && check_bitrix_sessid())
{
	$id = \Logictim\Balls\PayBonus\ExitBonus::QueryClose($request);
	LocalRedirect($APPLICATION->GetCurPage().'?id='.$id.'&'.$tabControl->ActiveTabParam().'&lang='.LANGUAGE_ID);
}
if(!empty($request['cancel']) && !empty($request['id']) && check_bitrix_sessid())
{
	$id = \Logictim\Balls\PayBonus\ExitBonus::QueryCancel($request);
	LocalRedirect($APPLICATION->GetCurPage().'?id='.$id.'&'.$tabControl->ActiveTabParam().'&lang='.LANGUAGE_ID);
}

					
					
CJSCore::Init(array('jquery2'));
?>
<section class="">
	
    <form class="logictim_profile" name="logictim_profile" method="post" action="<?echo $APPLICATION->GetCurPage()?>?lang=<?=LANGUAGE_ID?>">
    	<input type="hidden" name="id" value="<?=$exitId?>" />
        <? if($request['action']) {?>
        <input type="hidden" name="action" value="<?=$request['action']?>" />
        <? }?>
        <?$tabControl->Begin();?>
		<? $tabControl->BeginNextTab();?>
        
        
                <? if($profileId > 0) {?>
                <tr><td width="40%"><?=GetMessage("logictim.balls_EXIT_ID")?></td><td><?=$exitId?></td></tr>
                <? }?>
                <tr><td width="40%"><?=GetMessage("logictim.balls_EXIT_NAME")?></td><td><?=$arExit["name"]?></td></tr>
                <tr><td width="40%"><?=GetMessage("logictim.balls_EXIT_DATE_INSERT")?></td><td><?=$DB->FormatDate($arExit["date_insert"], "YYYY-MM-DD HH:MI:SS", \CSite::GetDateFormat())?></td></tr>
                <? if($arExit["date_insert"] != $arExit["date_update"]) {?>
                <tr><td width="40%"><?=GetMessage("logictim.balls_EXIT_DATE_UPDATE")?></td><td><?=$DB->FormatDate($arExit["date_update"], "YYYY-MM-DD HH:MI:SS", \CSite::GetDateFormat())?></td></tr>
                <? }?>
				<? if($arExit["date_close"] != '') {?>
                <tr><td width="40%"><?=GetMessage("logictim.balls_EXIT_DATE_CLOSE")?></td><td><?=$DB->FormatDate($arExit["date_close"], "YYYY-MM-DD HH:MI:SS", \CSite::GetDateFormat())?></td></tr>
                <? }?>
                <tr><td width="40%"><?=GetMessage("logictim.balls_EXIT_STATUS")?></td><td><b><?=$arStatuses[$arExit["status"]]?></b></td></tr>
				
				
            <tr class="heading"><td colspan="2"></td></tr>
            
            	<tr><td width="40%"><?=GetMessage("logictim.balls_EXIT_SUM")?></td><td><?=$arExit["sum"]?></td></tr>
                
                <?
                $rsUser = CUser::GetByID($arExit["user"]);
				$arUser = $rsUser->Fetch();
				?>
                <tr>
                    <td width="40%"><?=GetMessage("logictim.balls_EXIT_USER")?></td>
                    <td>
                    	[<a target="_blank" href="/bitrix/admin/user_edit.php?ID=<?=$arUser["ID"]?>&lang=<?=LANGUAGE_ID?>"><?=$arUser["ID"]?></a>]
                        (<?=$arUser["LOGIN"]?>)
                        <?=$arUser["NAME"].' '.$arUser["LAST_NAME"]?>
                    </td>
                </tr>
                
                <tr>
                    <td width="40%"><?=GetMessage("logictim.balls_EXIT_OPERATION")?></td>
                    <td>
                    	<a target="_blank" href="/bitrix/admin/iblock_element_edit.php?IBLOCK_ID=<?=\Logictim\Balls\Helpers::IblokOperationsId();?>&type=LOGICTIM_BONUS_STATISTIC&ID=<?=$arExit["operation_output"]?>&lang=<?=LANGUAGE_ID?>">
							<?=$arExit["operation_output"]?>
                        </a>
                    </td>
                </tr>
                
                
                <? if($arExit["status"] == 'P'):?>
                <tr>
                    <td width="40%"><?=GetMessage("logictim.balls_EXIT_ADMIN_COMMENT")?></td>
                    <td>
                    	<textarea name="comment_admin"></textarea>
                    </td>
                </tr>
                <? else:?>
                <? if($arExit["comment_admin"] != '') {?>
                <tr>
                    <td width="40%"><?=GetMessage("logictim.balls_EXIT_ADMIN_COMMENT")?></td>
                    <td><?=nl2br($arExit["comment_admin"])?></td>
                </tr>
                <? }?>
                <? endif;?>
                
                
                <? if($arExit["status"] == 'P'):?>
                <tr>
                	<td width="40%">
                		<input type="submit" name="close" value="<?=GetMessage("logictim.balls_EXIT_CLOSE")?>" title="<?=GetMessage("logictim.balls_EXIT_CLOSE")?>" class="adm-btn-save">
                    </td>
                    <td>
                    	<a href="#" onclick="return false;" class="close_exit"><?=GetMessage("logictim.balls_EXIT_CANCEL")?></a>
                    </td>
            	</tr>
                
                <tr id="cancel_block" style="display:none;">
                    <td width="40%"><?=GetMessage("logictim.balls_BONUS_LIVE_TIME")?></td>
                    <td>
                    	<div><?=GetMessage("logictim.balls_EXIT_RETURN")?></div>
                        <input type="text" size="5" name="deactive_after_period" value="365">
                        <select name="deactive_after_type" style="margin-left:5px;">
                            <option value="D" selected="selected"><?=GetMessage("logictim.balls_BONUS_DAYS")?></option>
                            <option value="M"><?=GetMessage("logictim.balls_BONUS_MONTHS")?></option>
                        </select>
                        
                        <input type="submit" name="cancel" value="<?=GetMessage("logictim.balls_EXIT_CANCEL")?>" title="<?=GetMessage("logictim.balls_EXIT_CANCEL")?>">
                    </td>
                </tr>
                
                <tr>
                	<td colspan="2">
                    	<div class="lb_description">
                        	<p><?=GetMessage("logictim.balls_EXIT_COMMENT")?></p>
                        </div>
                    </td>
                </tr>
                <? endif;?>
                
                
                <? if($arExit["status"] == 'C' && $arExit["operation_refund"] > 0):?>
                <tr>
                    <td width="40%"><?=GetMessage("logictim.balls_EXIT_OPERATION_REFUND")?></td>
                    <td>
                    	<a target="_blank" href="/bitrix/admin/iblock_element_edit.php?IBLOCK_ID=<?=\Logictim\Balls\Helpers::IblokOperationsId();?>&type=LOGICTIM_BONUS_STATISTIC&ID=<?=$arExit["operation_refund"]?>&lang=<?=LANGUAGE_ID?>">
							<?=$arExit["operation_refund"]?>
                        </a>
                    </td>
                </tr>
                <? endif;?>
            
            
        

        <?
		
	?>
       <?echo bitrix_sessid_post();?> 
       <?$tabControl->End();?>
    </form>
    
</section>


<script type="text/javascript">

BX.ready(function(){
	$(document).on('click', '.close_exit', function(){
		if($("#cancel_block").is(":hidden"))
			$("#cancel_block").show();
		else
			$("#cancel_block").hide();
		console.log('test');
	});
});

</script>