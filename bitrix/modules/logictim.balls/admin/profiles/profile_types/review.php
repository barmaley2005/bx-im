<?
$APPLICATION->SetTitle(GetMessage("logictim.balls_BONUS_FROM_REVIEW"));

if($request['id'] == 'new')
{
	$jsonProfileConditions = \Logictim\Balls\Conditions\Profile::BaseConditions('json');
	$jsonProductConditions = \Logictim\Balls\Conditions\Review::BaseConditions('json');
	
	$activeFrom = '';
	$activeTo = '';
}
else
{
	$arProfileConditions = unserialize($arProfile["profile_conditions"]);
	$jsonProfileConditions = \Bitrix\Main\Web\Json::encode($arProfileConditions);
	
	$arProductConditions = unserialize($arProfile["conditions"]);
	$jsonProductConditions = \Bitrix\Main\Web\Json::encode($arProductConditions);
	
	$otherConditions = unserialize($arProfile["other_conditions"]);
}

$aTabs = array(
	array("DIV" => "logictim_balls_tab_1", "TAB" => GetMessage("logictim.balls_PROFILE_ORDER_TAB_1"), "TITLE" => GetMessage("logictim.balls_PROFILE_ORDER_TAB_1")),
	array("DIV" => "logictim_balls_tab_2", "TAB" => GetMessage("logictim.balls_PROFILE_ORDER_TAB_2"), "TITLE" => GetMessage("logictim.balls_PROFILE_ORDER_TAB_2")),
);
$tabControl = new CAdminTabControl("tabControl".$profileId, $aTabs);

if((!empty($request['apply']) || !empty($request['save'])) && check_bitrix_sessid())
{
	if(!empty($request['id']))
	{
		//check numbers
		$activeAfter = ((int)$request['active_after_period'] ? $request['active_after_period'] : 0);
		$deActiveAfter = ((int)$request['deactive_after_period'] ? $request['deactive_after_period'] : 365);
		$sort = ((int)$request['sort'] ? $request['sort'] : 100);
		
		$arSaveFields = array();
		$arSaveFields['name'] = '"'.$DB->ForSql($request['name_profile']).'"';
		$arSaveFields['active'] = '"'.$request['active'].'"';
		$arSaveFields['sort'] = $request['sort'];
		$arSaveFields['type'] = '"'.$request['type'].'"';
		$arSaveFields['active_after_period'] = $activeAfter;
		$arSaveFields['active_after_type'] = '"'.$request['active_after_type'].'"';
		$arSaveFields['deactive_after_period'] = $deActiveAfter;
		$arSaveFields['deactive_after_type'] = '"'.$request['deactive_after_type'].'"';
		
		
		$arSaveFields['active_from'] = '"'.\ConvertDateTime($request['active_from'], "YYYY-MM-DD HH:MI:SS", LANG).'"';
		$arSaveFields['active_to'] = '"'.\ConvertDateTime($request['active_to'], "YYYY-MM-DD HH:MI:SS", LANG).'"';
		
		if(!empty($request["add_bonus"]))
			$arSaveFields['add_bonus'] = (float)str_replace(',', '.', $request['add_bonus']);
		
		if(!empty($request["profileCond"]))
		{
			$saveProfileConditions = \Logictim\Balls\Conditions::SaveConditions($request["profileCond"]);
			$arSaveFields['profile_conditions'] = "'".serialize($saveProfileConditions)."'";
		}
		if(!empty($request["profileProductsCond"]))
		{
			$saveConditions = \Logictim\Balls\Conditions::SaveConditions($request["profileProductsCond"]);
			$arSaveFields['conditions'] = "'".serialize($saveConditions)."'";
		}
		
		$otherConditions = array(
									"MAX_RIVIEW_COUNT" => (int)$request['max_review_count'],
									"MAX_RIVIEW_TIME" => (int)$request['max_review_time'],
									"MAX_RIVIEW_TYPE" => $request['max_review_type'],
								);
		$arSaveFields['other_conditions'] = "'".serialize($otherConditions)."'";
		
		
		if($request['id']=='new')
			$id = $DB->Insert($arTable["TABLE_NAME"], $arSaveFields, $err_mess.__LINE__);
		elseif($request['action'] == 'copy')
			$id = $DB->Insert($arTable["TABLE_NAME"], $arSaveFields, $err_mess.__LINE__);
		else
		{
			$DB->Update($arTable["TABLE_NAME"], $arSaveFields, "where id='".$request['id']."'");
			$id = $request['id'];
		}
		
		if(!empty($request['apply']))
			LocalRedirect($APPLICATION->GetCurPage().'?id='.$id.'&'.$tabControl->ActiveTabParam().'&lang='.LANGUAGE_ID);
		if(!empty($request['save']))
			LocalRedirect($APPLICATION->GetCurPage().'?save=Y&lang='.LANGUAGE_ID);
	}
}



CJSCore::Init(array('jquery2','core_condtree'));

?>


<section class="">
	
    <form class="logictim_profile" name="logictim_profile" method="post" action="<?echo $APPLICATION->GetCurPage()?>?lang=<?=LANGUAGE_ID?>">
    	<input type="hidden" name="id" value="<?=$profileId?>" />
		<input type="hidden" name="type" value="<?=$profileType?>" />
        <? if($request['action']) {?>
        <input type="hidden" name="action" value="<?=$request['action']?>" />
        <? }?>
        <?$tabControl->Begin();?>
        
		<? $tabControl->BeginNextTab();?>
        
        		<?
				if($profileId == 'new')
				{
					$active = 'Y';
					$profileName = GetMessage("logictim.balls_BONUS_FROM_REVIEW");
					$sort = 100;
					$activeAfter = 0;
					$activeAfterType = 'D';
					$deActiveAfter = 365;
					$deActiveAfterType = 'D';
					$add_bonus = 10;
					$max_review_count = 10;
					$max_review_time = 24;
					$max_review_type = 'H';
				}
				else
				{
					$active = $arProfile["active"];
					$profileName = $arProfile["name"];
					$sort = $arProfile["sort"];
					$activeAfter = (int)$arProfile["active_after_period"];
					$activeAfterType = $arProfile["active_after_type"];
					$deActiveAfter = $arProfile["deactive_after_period"];
					$deActiveAfterType = $arProfile["deactive_after_type"];
					$add_bonus = $arProfile["add_bonus"];
					$max_review_count = ($otherConditions["MAX_RIVIEW_COUNT"] ? $otherConditions["MAX_RIVIEW_COUNT"] : 10);
					$max_review_time = ($otherConditions["MAX_RIVIEW_TIME"] ? $otherConditions["MAX_RIVIEW_TIME"] : 24);
					$max_review_type = ($otherConditions["MAX_RIVIEW_TYPE"] ? $otherConditions["MAX_RIVIEW_TYPE"] : 'H');
				}
				?>
        
				<tr><td width="40%"><?=GetMessage("logictim.balls_PROFILE_TYPE")?></td><td><?=GetMessage("logictim.balls_BONUS_FROM_REVIEW")?></td></tr>
                <? if($profileId > 0) {?>
                <tr><td width="40%"><?=GetMessage("logictim.balls_PROFILE_ID")?></td><td><?=$profileId?></td></tr>
                <? }?>
                <tr><td width="40%"><?=GetMessage("logictim.balls_PROFILE_ACTIVE")?></td><td><input type="checkbox" name="active" value="Y" <? if($active == "Y") echo " checked"?> /></td></tr>
                <tr><td width="40%"><?=GetMessage("logictim.balls_PROFILE_NAME")?></td><td><input type="text" name="name_profile" size="70" value="<?=$profileName?>" /></td></tr>
                <tr><td width="40%"><?=GetMessage("logictim.balls_PROFILE_SORT")?></td><td><input type="text" name="sort" value="<?=$sort?>"></td></tr>
				
				<tr>
					<td width="40%"><?=GetMessage("logictim.balls_PROFILE_PERIOD")?></td>
					<td>
						<? $APPLICATION->IncludeComponent('bitrix:main.calendar', '', array(
								  'SHOW_INPUT' => 'Y',
								  'FORM_NAME' => '',
								  'INPUT_NAME' => 'active_from',
								  'INPUT_NAME_FINISH' => 'active_to',
								  'INPUT_VALUE' => ($arProfile["active_from"] > 0 && $arProfile['active_from'] != '0000-00-00 00:00:00') ? $DB->FormatDate($arProfile["active_from"], "YYYY-MM-DD HH:MI:SS", \CSite::GetDateFormat()) : '',
								  'INPUT_VALUE_FINISH' => ($arProfile["active_to"] > 0 && $arProfile['active_to'] != '0000-00-00 00:00:00') ? $DB->FormatDate($arProfile["active_to"], "YYYY-MM-DD HH:MI:SS", \CSite::GetDateFormat()) : '', 
								  'SHOW_TIME' => 'Y', 
								  'HIDE_TIMEBAR' => 'N', 
								  'INPUT_ADDITIONAL_ATTR' => 'placeholder="'.GetMessage("logictim.balls_PROFILE_PERIOD_PLACEHOLDER").'"'
							   )
							);
						?>
					</td>
				</tr>
                
            
				
            <tr class="heading" id="tr_BT_SALE_DISCOUNT_SECT_APP"><td colspan="2"><?=GetMessage("logictim.balls_BONUS_PERIOD")?></td></tr>
            <tr><td width="40%"><?=GetMessage("logictim.balls_BONUS_ADD")?></td><td><input type="text" name="add_bonus" value="<?=$add_bonus?>"></td></tr>
            <tr>
            	<td width="40%"><?=GetMessage("logictim.balls_BONUS_ACTIVE_AFTER")?></td>
                <td>
                	<input type="text" size="5" name="active_after_period" value="<?=$activeAfter?>">
                    
                    <select name="active_after_type" style="margin-left:5px;">
                    	<option value="D" <? if($activeAfterType == 'D') echo 'selected="selected"'?>><?=GetMessage("logictim.balls_BONUS_DAYS")?></option>
                        <option value="M" <? if($activeAfterType == 'M') echo 'selected="selected"'?>><?=GetMessage("logictim.balls_BONUS_MONTHS")?></option>
                    </select>
                </td>
            </tr>
             <tr>
            	<td width="40%"><?=GetMessage("logictim.balls_BONUS_LIVE_TIME")?></td>
                <td>
                	<input type="text" size="5" name="deactive_after_period" value="<?=$deActiveAfter?>">
                    <select name="deactive_after_type" style="margin-left:5px;">
                    	<option value="D" <? if($deActiveAfterType == 'D') echo 'selected="selected"'?>><?=GetMessage("logictim.balls_BONUS_DAYS")?></option>
                        <option value="M" <? if($deActiveAfterType == 'M') echo 'selected="selected"'?>><?=GetMessage("logictim.balls_BONUS_MONTHS")?></option>
                    </select>
                </td>
            </tr>
            
            <tr>
            	<td width="40%">
                	<?=GetMessage("logictim.balls_BONUS_REVIEW_MAX")?>
                    <? if($profileId != 'new' && !$otherConditions["MAX_RIVIEW_COUNT"]):?>
                    	<br />
                        <span class="demo_info"><?=GetMessage("logictim.balls_BONUS_MUST_SAVE_RULE")?></span>
                    <? endif;?>
                </td>
                <td>
                	<input type="text" size="5" name="max_review_count" value="<?=$max_review_count?>">
                    <?=GetMessage("logictim.balls_BONUS_REVIEW_MAX_FROM")?>
                    <input type="text" size="5" name="max_review_time" value="<?=$max_review_time?>">
                    <select name="max_review_type" style="margin-left:5px;">
                    	<option value="MIN" <? if($max_review_type == 'MIN') echo 'selected="selected"'?>><?=GetMessage("logictim.balls_BONUS_MINS")?></option>
                    	<option value="H" <? if($max_review_type == 'H') echo 'selected="selected"'?>><?=GetMessage("logictim.balls_BONUS_HOURS")?></option>
                    	<option value="D" <? if($max_review_type == 'D') echo 'selected="selected"'?>><?=GetMessage("logictim.balls_BONUS_DAYS")?></option>
                        <option value="M" <? if($max_review_type == 'M') echo 'selected="selected"'?>><?=GetMessage("logictim.balls_BONUS_MONTHS")?></option>
                    </select>
                </td>
            </tr>
            
            <tr class="heading" id="tr_BT_SALE_DISCOUNT_SECT_APP"><td colspan="2"><?=GetMessage("logictim.balls_BONUS_REVIEW_COND")?></td></tr>
            <tr><td width="100%" colspan="2">
            	<div id="ProductsConditions"></div>
                <script>
                    var JSSaleAct=new BX.TreeConditions(<?=\Logictim\Balls\Conditions\Review::MainParams('json');?>,<?=$jsonProductConditions?>,<?=\Logictim\Balls\Conditions\Review::Controls('json')?>);
                </script>
            </td></tr>
            
            <? $tabControl->BeginNextTab();?>
            
            <tr class="heading" id="tr_BT_SALE_DISCOUNT_SECT_APP"><td colspan="2"><?=GetMessage("logictim.balls_PROFILE_COND_SECT")?></td></tr>
            <tr><td width="100%" colspan="2">
            	<div id="ProfileConditions"></div>
                <script>
                    var JSSaleAct=new BX.TreeConditions(<?=\Logictim\Balls\Conditions\Profile::MainParams('json');?>,<?=$jsonProfileConditions?>,<?=\Logictim\Balls\Conditions\Profile::Controls('json', $profileType)?>);
                </script>
            </td></tr>
            
            
        <?
		$tabControl->Buttons(array(
							"back_url" => $APPLICATION->GetCurPage().'?lang='.LANGUAGE_ID
						));
	?>
       <?echo bitrix_sessid_post();?> 
       <?$tabControl->End();?>
    </form>
    
</section>

