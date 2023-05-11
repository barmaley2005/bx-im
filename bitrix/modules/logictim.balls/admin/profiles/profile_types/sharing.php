<?
$APPLICATION->SetTitle(GetMessage("logictim.balls_BONUS_FROM_REPOST"));

if($request['id'] == 'new')
{
	$jsonProfileConditions = \Logictim\Balls\Conditions\Profile::BaseConditions('json');
	
	$activeFrom = '';
	$activeTo = '';
}
else
{
	$arProfileConditions = unserialize($arProfile["profile_conditions"]);
	$jsonProfileConditions = \Bitrix\Main\Web\Json::encode($arProfileConditions);
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
		
		if(!empty($request["bonus_repost_vk"]))
			$arOptions['bonus_repost_vk'] = (float)str_replace(',', '.', $request['bonus_repost_vk']);
		if(!empty($request["bonus_repost_fb"]))
			$arOptions['bonus_repost_fb'] = (float)str_replace(',', '.', $request['bonus_repost_fb']);
		if(!empty($request["bonus_repost_ok"]))
			$arOptions['bonus_repost_ok'] = (float)str_replace(',', '.', $request['bonus_repost_ok']);
			
		$arOptions['repost_page_count'] = ((int)$request['repost_page_count'] ? $request['repost_page_count'] : 10);
		$arOptions['repost_page_time'] = ((int)$request['repost_page_time'] ? $request['repost_page_time'] : 1);
		$arOptions['repost_page_type'] = ($request['repost_page_type'] ? $request['repost_page_type'] : 'D');
		$arOptions['repost_all_count'] = ((int)$request['repost_all_count'] ? $request['repost_all_count'] : 10);
		$arOptions['repost_all_time'] = ((int)$request['repost_all_time'] ? $request['repost_all_time'] : 1);
		$arOptions['repost_all_type'] = ($request['repost_all_type'] ? $request['repost_all_type'] : 'D');
		$arOptions['repost_all_save'] = $request['repost_all_save'];
		$arOptions['vk_app_id'] = $request['vk_app_id'];
		$arOptions['fb_app_id'] = $request['fb_app_id'];
		
		$arSaveFields['conditions'] = "'".serialize($arOptions)."'";

		if(!empty($request["profileCond"]))
		{
			$saveProfileConditions = \Logictim\Balls\Conditions::SaveConditions($request["profileCond"]);
			$arSaveFields['profile_conditions'] = "'".serialize($saveProfileConditions)."'";
		}
		
		
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
					$profileName = GetMessage("logictim.balls_BONUS_FROM_REPOST");
					$sort = 100;
					$activeAfter = 0;
					$activeAfterType = 'D';
					$deActiveAfter = 365;
					$deActiveAfterType = 'D';
					
					$bonus_vk = 0;
					$bonus_fb = 0;
					$bonus_ok = 0;
					$repostPageCount = 10;
					$repostPageTime = 1;
					$repostPageType = 'D';
					$repostAllCount = 10;
					$repostAllTime = 1;
					$repostAllType = 'D';
					$allRepostSave = '';
					$vkAppId = '';
					$fbAppId = '';
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
					
					$arOptions = unserialize($arProfile["conditions"]);
					$bonus_vk = $arOptions["bonus_repost_vk"];
					$bonus_fb = $arOptions["bonus_repost_fb"];
					$bonus_ok = $arOptions["bonus_repost_ok"];
					$repostPageCount = (int)$arOptions["repost_page_count"];
					$repostPageTime = (int)$arOptions["repost_page_time"];
					$repostPageType = $arOptions["repost_page_type"];
					$repostAllCount = (int)$arOptions["repost_all_count"];
					$repostAllTime = (int)$arOptions["repost_all_time"];
					$repostAllType = $arOptions["repost_all_type"];
					$allRepostSave = $arOptions["repost_all_save"];
					$vkAppId = $arOptions["vk_app_id"];
					$fbAppId = $arOptions["fb_app_id"];
				}
				?>
        
				<tr class="heading"><td colspan="2" align="center">
					<a target="_blank" href="https://logictim.ru/marketplace/nakopitelnaya_referalnaya_sistema_versii_4_0_i_vyshe/instruktsii_po_nastroyke_4/nastroyka_pravil/bonusy_za_repost/" class="lgb_instruction" style="font-size:14px"><?=GetMessage("logictim.balls_BONUS_INSTRUCTION")?></a>
                </td></tr>
                <tr><td width="40%"><?=GetMessage("logictim.balls_PROFILE_TYPE")?></td><td><?=GetMessage("logictim.balls_BONUS_FROM_REPOST")?></td></tr>
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
            
            <tr class="heading"><td colspan="2"><?=GetMessage("logictim.balls_SHARING_BONUS")?></td></tr>
            <tr>
            	<td width="40%"><?=GetMessage("logictim.balls_SHARING_BONUS_VK")?></td>
                <td><input type="text" name="bonus_repost_vk" value="<?=$bonus_vk?>" size="5"></td>
            </tr>
            <tr>
            	<td width="40%"><?=GetMessage("logictim.balls_SHARING_BONUS_FB")?></td>
                <td><input type="text" name="bonus_repost_fb" value="<?=$bonus_fb?>" size="5"></td>
            </tr>
            <tr>
            	<td width="40%"><?=GetMessage("logictim.balls_SHARING_BONUS_OK")?></td>
                <td><input type="text" name="bonus_repost_ok" value="<?=$bonus_ok?>" size="5"></td>
            </tr>
            
            <tr class="heading"><td colspan="2"><?=GetMessage("logictim.balls_MAX_SHARING_BONUS")?></td></tr>
            <tr>
            	<td colspan="2" align="center"><?=GetMessage("logictim.balls_MAX_SHARING_BONUS_PAGE")?></td>
            </tr>    
            <tr>
            	<td width="40%"></td>
                <td>
                	<input type="text" name="repost_page_count" value="<?=$repostPageCount?>" size="5">
                    <?=GetMessage("logictim.balls_MAX_SHARING_REPOSTS")?>
                    <?=GetMessage("logictim.balls_MAX_SHARING_FROM")?>
                    <input type="text" name="repost_page_time" value="<?=$repostPageTime?>" size="5">
                    <select name="repost_page_type" style="margin-left:5px;">
                    	<option value="MIN" <? if($repostPageType == 'MIN') echo 'selected="selected"'?>><?=GetMessage("logictim.balls_BONUS_MINS")?></option>
                        <option value="H" <? if($repostPageType == 'H') echo 'selected="selected"'?>><?=GetMessage("logictim.balls_BONUS_HOURS")?></option>
                    	<option value="D" <? if($repostPageType == 'D') echo 'selected="selected"'?>><?=GetMessage("logictim.balls_BONUS_DAYS")?></option>
                        <option value="M" <? if($repostPageType == 'M') echo 'selected="selected"'?>><?=GetMessage("logictim.balls_BONUS_MONTHS")?></option>
                    </select>
                </td>
            </tr>
            <tr>
            	<td colspan="2" align="center"><br /><?=GetMessage("logictim.balls_MAX_SHARING_BONUS_ALL")?></td>
            </tr>    
            <tr>
            	<td width="40%"></td>
                <td>
                	<input type="text" name="repost_all_count" value="<?=$repostAllCount?>" size="5">
                    <?=GetMessage("logictim.balls_MAX_SHARING_REPOSTS")?>
                    <?=GetMessage("logictim.balls_MAX_SHARING_FROM")?>
                    <input type="text" name="repost_all_time" value="<?=$repostAllTime?>" size="5">
                    <select name="repost_all_type" style="margin-left:5px;">
                    	<option value="MIN" <? if($repostAllType == 'MIN') echo 'selected="selected"'?>><?=GetMessage("logictim.balls_BONUS_MINS")?></option>
                        <option value="H" <? if($repostAllType == 'H') echo 'selected="selected"'?>><?=GetMessage("logictim.balls_BONUS_HOURS")?></option>
                    	<option value="D" <? if($repostAllType == 'D') echo 'selected="selected"'?>><?=GetMessage("logictim.balls_BONUS_DAYS")?></option>
                        <option value="M" <? if($repostAllType == 'M') echo 'selected="selected"'?>><?=GetMessage("logictim.balls_BONUS_MONTHS")?></option>
                    </select>
                </td>
            </tr>
            <tr><td width="40%"></td><td><br /><input type="checkbox" name="repost_all_save" value="Y" <? if($allRepostSave == "Y") echo " checked"?> /><?=GetMessage("logictim.balls_MAX_SHARING_SAVE_ALL")?></td></tr>
            
            <tr class="heading"><td colspan="2"><?=GetMessage("logictim.balls_SHARING_TECH")?></td></tr>
            <tr>
            	<td width="40%"><?=GetMessage("logictim.balls_SHARING_VK_APP")?></td>
                <td><input type="text" name="vk_app_id" value="<?=$vkAppId?>" size="5"></td>
            </tr>
            <tr>
            	<td width="40%"><?=GetMessage("logictim.balls_SHARING_FB_APP")?></td>
                <td><input type="text" name="fb_app_id" value="<?=$fbAppId?>" size="5"></td>
            </tr>
            
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

