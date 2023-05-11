<style type="text/css">
div.descrioption {
	width:500px;
	margin:auto;
	background-color: #FEFDEA;
    font-size: 12px;
    color: #333333;
	border:1px solid #D7D6BA;
	padding:10px;
	line-height:16px;
	margin-bottom: 15px;
}
.hidden {
	display:none;
}
.comment {
	font-size:12px;
	color:#ccc;
	font-style:italic;
}

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
</style>

<?
require_once($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/'.$module_id.'/classes/module-options/options_version_4.php');
IncludeModuleLangFile(__FILE__);

$tabControl = new CAdminTabControl("tabControl", $arTabs);

//echo '<pre>'; print_r($_REQUEST); echo '</pre>';
if($_REQUEST['Update'] == 'Y' && check_bitrix_sessid())
{
	foreach($arOptions as $opt => $arOptParams):
		$val = $_REQUEST[$opt];
		
		if($arOptParams['TYPE'] == 'CHECKBOX' && $val != 'Y')
			$val = 'N';
		elseif(is_array($val))
			$val = serialize($val);
			
		COption::SetOptionString($module_id, $opt, $val);
	endforeach;
	
}

require_once($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/logictim.balls/admin/header.php');
?>

<form name="logictim.balls" id="logictim.balls_form" method="POST" action="<?=$APPLICATION->GetCurPage().'?mid=logictim.balls&mid_menu=1&'.$tabControl->ActiveTabParam().'&lang='.LANGUAGE_ID?>" enctype="multipart/form-data">
	
    <? $tabControl->Begin();?>
    	
    <? $tabControl->BeginNextTab();?>
        <tr class="heading" id="tr_BT_SALE_DISCOUNT_SECT_APP"><td colspan="2"><?=$arGroups["MAIN"]["TITLE"]?></td></tr>
        
        <? 	$option = "MODULE_VERSION";
			$arOption = $arOptions[$option];
			$val = COption::GetOptionString($module_id, $option, $arOption['DEFAULT']);
			$path = $_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/logictim.balls/classes/module-options/version_access.txt';
			if(file_exists($path))
				$access_v_3 = file_get_contents($path);
		?>
        <tr <? if($access_v_3 != 'Y') echo 'class="hidden"';?>>
            <td width="40%"><?=$arOption["TITLE"]?></td>
            <td>
                <? echo SelectBoxFromArray($option, $arOption['VALUES'], $val, '', 'class="'.$arOption['CLASS'].'"', false, $module_id);?>
            </td>
        </tr>
        <tr <? if($access_v_3 != 'Y') echo 'class="hidden"';?>>
        	<td colspan="2">
				<div class="descrioption" style="margin-bottom:40px;"><?=$arOption["NOTES"]?></div>
            </td>
        </tr>
        
        
        <? 	$option = "BONUS_BILL";
			$arOption = $arOptions[$option];
			$val = COption::GetOptionString($module_id, $option, $arOption['DEFAULT']);
		?>
        <tr>
        	<td colspan="2">
				<div class="descrioption"><?=$arOptions["BONUS_BILL_DESCRIPTION"]["NOTES"]?></div>
            </td>
        </tr>
        <tr>
            <td width="40%"><?=$arOption["TITLE"]?></td>
            <td>
                <? echo SelectBoxFromArray($option, $arOption['VALUES'], $val, '', 'class="'.$arOption['CLASS'].'"', false, $module_id);?>
            </td>
        </tr>
        
        <? 	$option = "BONUS_CURRENCY";
			$arOption = $arOptions[$option];
			$val = COption::GetOptionString($module_id, $option, $arOption['DEFAULT']);
		?>
        <tr class="<?=$arOption['CLASS']?>">
            <td width="40%"><?=$arOption["TITLE"]?></td>
            <td>
                <? echo SelectBoxFromArray($option, $arOption['VALUES'], $val, '', $classSel, false, $module_id);?>
            </td>
        </tr>
        
        <? 	$option = "DISCOUNT_TO_PRODUCTS";
			$arOption = $arOptions[$option];
			$val = COption::GetOptionString($module_id, $option, $arOption['DEFAULT']);
		?>
        <tr>
            <td width="40%"><?=$arOption["TITLE"]?></td>
            <td>
                <? echo SelectBoxFromArray($option, $arOption['VALUES'], $val, '', $classSel, false, $module_id);?>
            </td>
        </tr>
        
        
        
        <tr class="heading" id="tr_BT_SALE_DISCOUNT_SECT_APP"><td colspan="2"><?=$arGroups["EVENTS_ORDER_TO_BONUS"]["TITLE"]?></td></tr>
    	
        <? 	$option = "ORDER_STATUS";
			$arOption = $arOptions[$option];
			$val = COption::GetOptionString($module_id, $option, $arOption['DEFAULT']);
		?>
        <tr>
            <td width="40%"><?=$arOption["TITLE"]?></td>
            <td>
                <? echo SelectBoxFromArray($option, $arOption['VALUES'], $val, '', $classSel, false, $module_id);?>
            </td>
        </tr>
        
        <? 	$option = "EVENT_ORDER_PAYED";
			$arOption = $arOptions[$option];
			$val = COption::GetOptionString($module_id, $option, $arOption['DEFAULT']);
		?>
         <tr>
            <td width="40%"><?=$arOption["TITLE"]?></td>
            <td>
                <input type="checkbox" name="<?=$option?>" id="<?=$option?>" value="Y" <? if($val == 'Y') echo ' checked';?> />
            </td>
        </tr>
        
        <tr class="heading" id="tr_BT_SALE_DISCOUNT_SECT_APP"><td colspan="2"><?=GetMessage("logictim.balls_EVENT_USER_REGISTER")?></td></tr>
        <? 	$option = "USER_REGISTER";
			$arOption = $arOptions[$option];
			$val = COption::GetOptionString($module_id, $option, $arOption['DEFAULT']);
		?>
        <tr>
            <td width="40%"><?=$arOption["TITLE"]?></td>
            <td>
                <? echo SelectBoxFromArray($option, $arOption['VALUES'], $val, '', $classSel, false, $module_id);?>
            </td>
        </tr>
        
    <? $tabControl->BeginNextTab();?>
    	
        <tr class="heading" id="tr_BT_SALE_DISCOUNT_SECT_APP"><td colspan="2"><?=$arGroups["REFERAL_SYSTEM"]["TITLE"]?></td></tr>
    	
        <? 	$option = "REFERAL_LEVELS";
			$arOption = $arOptions[$option];
			$val = COption::GetOptionString($module_id, $option, $arOption['DEFAULT']);
		?>
        <tr>
            <td width="40%"><?=$arOption["TITLE"]?></td>
            <td>
                <input type="number" name="<?=$option?>" id="<?=$option?>" size="5" value="<?=$val?>" min="0">
                <input type="submit" name="refresh" value="OK">
            </td>
        </tr>
        
        <? 	$option = "REFERAL_USE_COUPONS";
			$arOption = $arOptions[$option];
			$val = COption::GetOptionString($module_id, $option, $arOption['DEFAULT']);
		?>
        <tr>
            <td width="40%"><?=$arOption["TITLE"]?></td>
            <td>
                <? echo SelectBoxFromArray($option, $arOption['VALUES'], $val, '', 'class="'.$arOption['CLASS'].'"', false, $module_id);?>
            </td>
        </tr>
        
        <? 	$option = "REFERAL_COUPON_DISCOUNT";
			$arOption = $arOptions[$option];
			$val = COption::GetOptionString($module_id, $option, $arOption['DEFAULT']);
		?>
        <tr class="<?=$arOption['CLASS']?>">
            <td width="40%"><?=$arOption["TITLE"]?></td>
            <td>
                <? echo SelectBoxFromArray($option, $arOption['VALUES'], $val, '', $classSel, false, $module_id);?>
            </td>
        </tr>
        
        <? 	$option = "REFERAL_COUPON_DISCOUNT_IN_PROFILE";
			$arOption = $arOptions[$option];
			$val = COption::GetOptionString($module_id, $option, $arOption['DEFAULT']);
		?>
        
        <tr class="<?=$arOption['CLASS']?>">
        
            <td width="40%">
            	<span data-hint="<?=$arOption["NOTES"]?>"></span>
				<?=$arOption["TITLE"]?>
            </td>
            <td>
                 <input type="checkbox" name="<?=$option?>" id="<?=$option?>" value="Y" <? if($val == 'Y') echo ' checked';?> />
            </td>
        </tr>
        
        <? 	$option = "REFERAL_COUPON_PREFIX";
			$arOption = $arOptions[$option];
			$val = COption::GetOptionString($module_id, $option, $arOption['DEFAULT']);
		?>
        <tr class="<?=$arOption['CLASS']?>">
            <td width="40%"><?=$arOption["TITLE"]?></td>
            <td>
                <input type="text" id="<?=$option?>" name="<?=$option?>" size="25" maxlength="255" value="<?=$val?>">
            </td>
        </tr>
        <tr class="<?=$arOption['CLASS']?>">
        	<td colspan="2"><div class="descrioption"><?=$arOption["NOTES"]?></div></td>
        </tr>
        
        <? 	$option = "REFERAL_COUPON_CAN_USER";
			$arOption = $arOptions[$option];
			$val = COption::GetOptionString($module_id, $option, $arOption['DEFAULT']);
		?>
        <tr class="<?=$arOption['CLASS']?>">
            <td width="40%"><?=$arOption["TITLE"]?></td>
            <td>
                <input type="checkbox" name="<?=$option?>" id="<?=$option?>" value="Y" <? if($val == 'Y') echo ' checked';?> />
            </td>
        </tr>
        
        <? 	$option = "PARTNER_GROUPS";
			$arOption = $arOptions[$option];
			$val = COption::GetOptionString($module_id, $option, $arOption['DEFAULT']);
			$val = unserialize($val);
		?>
        <tr class="<?=$arOption['CLASS']?>">
            <td width="40%"><?=$arOption["TITLE"]?></td>
            <td>
            	<? echo SelectBoxMFromArray($option.'[]', $arOption['VALUES'], $val);?>
            </td>
        </tr>
        
    
    
    <? $tabControl->BeginNextTab();?>
    	
        <? 	$option = "COUNT_DAY_WARNING";
			$arOption = $arOptions[$option];
			$val = COption::GetOptionString($module_id, $option, $arOption['DEFAULT']);
		?>
        <tr>
            <td width="40%"><?=$arOption["TITLE"]?></td>
            <td>
                <input type="text" id="<?=$option?>" name="<?=$option?>" size="25" maxlength="255" value="<?=$val?>">
                <input type="submit" name="refresh" value="OK">
            </td>
        </tr>
        
        <? 	$option = "AGENTS_WORK_TIME_FROM";
			$arOption = $arOptions[$option];
			$val = COption::GetOptionString($module_id, $option, $arOption['DEFAULT']);
		?>
        <tr>
            <td width="40%"><?=$arOption["TITLE"]?></td>
            <td>
            	<?=GetMessage("logictim.balls_AGENTS_WORK_TIME_FROM")?>
            	<?
				CJSCore::Init(array('masked_input'));
				$APPLICATION->IncludeComponent("bitrix:main.clock","",Array(
						"INPUT_ID" => "AGENTS_WORK_TIME_FROM", 
						"INPUT_NAME" => "AGENTS_WORK_TIME_FROM", 
						"INPUT_TITLE" => "", 
						"INIT_TIME" => $val, 
						"STEP" => "0" 
					)
				);?>
                <?=GetMessage("logictim.balls_AGENTS_WORK_TIME_TO")?>
                <?
				$option = "AGENTS_WORK_TIME_TO";
				$arOption = $arOptions[$option];
				$val = COption::GetOptionString($module_id, $option, $arOption['DEFAULT']);
				$APPLICATION->IncludeComponent("bitrix:main.clock","",Array(
						"INPUT_ID" => "AGENTS_WORK_TIME_TO", 
						"INPUT_NAME" => "AGENTS_WORK_TIME_TO", 
						"INPUT_TITLE" => "", 
						"INIT_TIME" => $val, 
						"STEP" => "0" 
					)
				);?>
                <input type="submit" name="refresh" value="OK">
                <script>
					BX.ready(function() {
						var result = new BX.MaskedInput({
							mask: '99:99',
							input: BX('AGENTS_WORK_TIME_FROM'),
							onChange: function(e) {
								console.log('test');
							}
						});
						var result = new BX.MaskedInput({
							mask: '99:99',
							input: BX('AGENTS_WORK_TIME_TO'),
							onChange: function(e) {
								console.log('test');
							}
						});
					});
				</script>
            </td>
        </tr>
        <tr>
        	<td colspan="2">
            	<div class="descrioption"><?=GetMessage("logictim.balls_AGENTS_WORK_DESCRIPTION")?></div>
            </td>
        </tr>
        
        <tr class="heading">
        	<td colspan="2"><?=$arGroups["EVENTS_MAIL"]["TITLE"]?></td>
        </tr>
        <tr>
        	<td width="40%"></td>
        	<td colspan="2" class="descrioption"><?=$arOptions["EVENTS_MAIL_DESCRIPTION"]["VALUE"]?></td>
        </tr>
        
        <tr class="heading">
        	<td colspan="2"><?=GetMessage("logictim.balls_EVENTS_SMS_TITLE")?></td>
        </tr>
        
        
        <tr>
        	<td width="40%"></td>
        	<td colspan="2" class="descrioption">
            <?
			if($info = CModule::CreateModuleObject('main'))
			{
				 $testVersion = '17.0.18';
				 if(CheckVersion($testVersion, $info->MODULE_VERSION))
				 {
					  echo GetMessage("logictim.balls_EVENTS_SMS_OLD_BITRIX");
				 }
				 else
				 {
					 if(CModule::IncludeModule("messageservice"))
					 {
						if($_REQUEST['install_sms'] == 'Y')
						{
							$rsET = CEventType::GetList(array("EVENT_TYPE" => "sms"));
							$arSmsEvents = array();
							while ($arET = $rsET->Fetch())
							{
								if(strpos($arET['EVENT_NAME'], 'LOGICTIM_BONUS') !== false)
									$arSmsEvents = $arET;
							}
							if(empty($arSmsEvents))
							{
								require_once($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/'.$module_id.'/install/include/sms_events_install.php');
							}
								
						}
						 
						$rsET = CEventType::GetList(array("EVENT_TYPE" => "sms"));
						$arSmsEvents = array();
						while($arET = $rsET->Fetch())
						{
							if(strpos($arET['EVENT_NAME'], 'LOGICTIM_BONUS') !== false)
								$arSmsEvents = $arET;
						}
						
						if(!empty($arSmsEvents))
							echo GetMessage("logictim.balls_EVENTS_SMS_DESCRIPTION");
						else
							echo GetMessage("logictim.balls_EVENTS_SMS_INSTALL_TEMPLATES");
						
					 }
					 else
					 {
						 echo GetMessage("logictim.balls_EVENTS_SMS_INSTALL_MODULE");
					 }
				 }
			}
			?>
            
            </td>
        </tr>
        
        
    	
    <? $tabControl->BeginNextTab();?>
    
    	<tr class="heading">
        	<td colspan="2"><?=$arGroups["ORDER_FORM"]["TITLE"]?></td>
        </tr>
        
        <? 	$option = "INTEGRATE_IN_SALE_ORDER_AJAX";
			$arOption = $arOptions[$option];
			$val = COption::GetOptionString($module_id, $option, $arOption['DEFAULT']);
		?>
         <tr>
            <td width="40%"><?=$arOption["TITLE"]?></td>
            <td>
                <input type="checkbox" name="<?=$option?>" id="<?=$option?>" value="Y" <? if($val == 'Y') echo ' checked';?> />
            </td>
        </tr>
        <tr>
        	<td colspan="2"><div class="descrioption"><?=$arOption["NOTES"]?></div></td>
        </tr>
        
        <? 	$option = "ORDER_TOTAL_BONUS";
			$arOption = $arOptions[$option];
			$val = COption::GetOptionString($module_id, $option, $arOption['DEFAULT']);
		?>
         <tr>
            <td width="40%"><?=$arOption["TITLE"]?></td>
            <td>
                <input type="checkbox" name="<?=$option?>" id="<?=$option?>" value="Y" <? if($val == 'Y') echo ' checked';?> />
            </td>
        </tr>
        
        <? 	$option = "ORDER_PAY_BONUS_AUTO";
			$arOption = $arOptions[$option];
			$val = COption::GetOptionString($module_id, $option, $arOption['DEFAULT']);
		?>
         <tr>
            <td width="40%"><?=$arOption["TITLE"]?></td>
            <td>
                <input type="checkbox" name="<?=$option?>" id="<?=$option?>" value="Y" <? if($val == 'Y') echo ' checked';?> />
            </td>
        </tr>
        
        
        <tr class="heading">
        	<td colspan="2"><?=$arGroups["BASKET_INTAGRATE"]["TITLE"]?></td>
        </tr>
        
        <? 	$option = "INTEGRATE_IN_SALE_BASKET";
			$arOption = $arOptions[$option];
			$val = COption::GetOptionString($module_id, $option, $arOption['DEFAULT']);
		?>
         <tr>
            <td width="40%"><?=$arOption["TITLE"]?></td>
            <td>
                <input type="checkbox" name="<?=$option?>" id="<?=$option?>" value="Y" <? if($val == 'Y') echo ' checked';?> />
            </td>
        </tr>
        <tr>
        	<td colspan="2"><div class="descrioption"><?=$arOption["NOTES"]?></div></td>
        </tr>
        
        <tr class="heading">
        	<td colspan="2"><?=$arGroups["CATALOG_INTAGRATE"]["TITLE"]?></td>
        </tr>
        <? 	$option = "AJAX_IN_CATALOG";
			$arOption = $arOptions[$option];
			$val = COption::GetOptionString($module_id, $option, $arOption['DEFAULT']);
		?>
        <tr>
            <td width="40%"><?=$arOption["TITLE"]?></td>
            <td>
                <input type="checkbox" name="<?=$option?>" id="<?=$option?>" value="Y" <? if($val == 'Y') echo ' checked';?> />
            </td>
        </tr>
        
        
        
        <tr class="heading">
        	<td colspan="2"><?=$arGroups["TEXT"]["TITLE"]?></td>
        </tr>
        
        <? 	$option = "TEXT_BONUS_BALLS";
			$arOption = $arOptions[$option];
			$val = COption::GetOptionString($module_id, $option, $arOption['DEFAULT']);
		?>
         <tr>
            <td width="40%"><?=$arOption["TITLE"]?></td>
            <td>
                <input type="text" id="<?=$option?>" name="<?=$option?>"  size="50" maxlength="255" value="<?=$val?>">
            </td>
        </tr>
        
        <? 	$option = "HAVE_BONUS_TEXT";
			$arOption = $arOptions[$option];
			$val = COption::GetOptionString($module_id, $option, $arOption['DEFAULT']);
		?>
         <tr>
            <td width="40%"><?=$arOption["TITLE"]?></td>
            <td>
                <input type="text" id="<?=$option?>" name="<?=$option?>"  size="50" maxlength="255" value="<?=$val?>">
            </td>
        </tr>
        
        <? 	$option = "CAN_BONUS_TEXT";
			$arOption = $arOptions[$option];
			$val = COption::GetOptionString($module_id, $option, $arOption['DEFAULT']);
		?>
         <tr>
            <td width="40%"><?=$arOption["TITLE"]?></td>
            <td>
                <input type="text" id="<?=$option?>" name="<?=$option?>"  size="50" maxlength="255" value="<?=$val?>">
            </td>
        </tr>
        
        <? 	$option = "MIN_BONUS_TEXT";
			$arOption = $arOptions[$option];
			$val = COption::GetOptionString($module_id, $option, $arOption['DEFAULT']);
		?>
         <tr>
            <td width="40%"><?=$arOption["TITLE"]?></td>
            <td>
                <input type="text" id="<?=$option?>" name="<?=$option?>"  size="50" maxlength="255" value="<?=$val?>">
            </td>
        </tr>
        
        <? 	$option = "MAX_BONUS_TEXT";
			$arOption = $arOptions[$option];
			$val = COption::GetOptionString($module_id, $option, $arOption['DEFAULT']);
		?>
         <tr>
            <td width="40%"><?=$arOption["TITLE"]?></td>
            <td>
                <input type="text" id="<?=$option?>" name="<?=$option?>"  size="50" maxlength="255" value="<?=$val?>">
            </td>
        </tr>
        
        <? 	$option = "PAY_BONUS_TEXT";
			$arOption = $arOptions[$option];
			$val = COption::GetOptionString($module_id, $option, $arOption['DEFAULT']);
		?>
         <tr>
            <td width="40%"><?=$arOption["TITLE"]?></td>
            <td>
                <input type="text" id="<?=$option?>" name="<?=$option?>"  size="50" maxlength="255" value="<?=$val?>">
            </td>
        </tr>
        
        <? 	$option = "TEXT_BONUS_PAY";
			$arOption = $arOptions[$option];
			$val = COption::GetOptionString($module_id, $option, $arOption['DEFAULT']);
		?>
         <tr>
            <td width="40%"><?=$arOption["TITLE"]?></td>
            <td>
                <input type="text" id="<?=$option?>" name="<?=$option?>"  size="50" maxlength="255" value="<?=$val?>">
            </td>
        </tr>
        
        <? 	$option = "ERROR_1_TEXT";
			$arOption = $arOptions[$option];
			$val = COption::GetOptionString($module_id, $option, $arOption['DEFAULT']);
		?>
         <tr>
            <td width="40%"><?=$arOption["TITLE"]?></td>
            <td>
                <input type="text" id="<?=$option?>" name="<?=$option?>"  size="50" maxlength="255" value="<?=$val?>">
            </td>
        </tr>
        
        <? 	$option = "TEXT_BONUS_FOR_ITEM";
			$arOption = $arOptions[$option];
			$val = COption::GetOptionString($module_id, $option, $arOption['DEFAULT']);
		?>
         <tr>
            <td width="40%"><?=$arOption["TITLE"]?></td>
            <td>
                <input type="text" id="<?=$option?>" name="<?=$option?>"  size="50" maxlength="255" value="<?=$val?>">
            </td>
        </tr>
        
        <? 	$option = "TEXT_BONUS_FOR_PAYMENT";
			$arOption = $arOptions[$option];
			$val = COption::GetOptionString($module_id, $option, $arOption['DEFAULT']);
		?>
         <tr>
            <td width="40%"><?=$arOption["TITLE"]?></td>
            <td>
                <input type="text" id="<?=$option?>" name="<?=$option?>"  size="50" maxlength="255" value="<?=$val?>">
            </td>
        </tr>
        <? 	$option = "TEXT_BONUS_USE_BONUS_BUTTON";
			$arOption = $arOptions[$option];
			$val = COption::GetOptionString($module_id, $option, $arOption['DEFAULT']);
		?>
         <tr>
            <td width="40%"><?=$arOption["TITLE"]?></td>
            <td>
                <input type="text" id="<?=$option?>" name="<?=$option?>"  size="50" maxlength="255" value="<?=$val?>">
            </td>
        </tr>
        <? 	$option = "TEXT_BONUS_ERROR_MIN_BONUS";
			$arOption = $arOptions[$option];
			$val = COption::GetOptionString($module_id, $option, $arOption['DEFAULT']);
		?>
         <tr>
            <td width="40%"><?=$arOption["TITLE"]?></td>
            <td>
                <input type="text" id="<?=$option?>" name="<?=$option?>"  size="50" maxlength="255" value="<?=$val?>">
            </td>
        </tr>
        
        <tr class="heading">
        	<td colspan="2"><?=GetMessage("logictim.balls_TEMPLATE_VIEW_BONUS");?></td>
        </tr>
        <tr>
            <td colspan="2" align="center"><div class="descrioption"><?=GetMessage("logictim.balls_TEMPLATE_VIEW_BONUS_COMMENT");?></div></td>
        </tr>
        
        <? $option = "TEMPLATE_BONUS_FOR_CART_ITEM";
			$arOption = $arOptions[$option];
			$val = COption::GetOptionString($module_id, $option, $arOption['DEFAULT']);
		?>
         <tr>
            <td width="40%"><?=$arOption["TITLE"]?></td>
            <td>
                <input type="text" id="<?=$option?>" name="<?=$option?>"  size="50" maxlength="255" value="<?=$val?>">
            </td>
        </tr>
        <? $option = "TEMPLATE_BONUS_FOR_CART";
			$arOption = $arOptions[$option];
			$val = COption::GetOptionString($module_id, $option, $arOption['DEFAULT']);
		?>
         <tr>
            <td width="40%"><?=$arOption["TITLE"]?></td>
            <td>
                <input type="text" id="<?=$option?>" name="<?=$option?>"  size="50" maxlength="255" value="<?=$val?>">
            </td>
        </tr>
        <? $option = "TEMPLATE_BONUS_FOR_ORDER";
			$arOption = $arOptions[$option];
			$val = COption::GetOptionString($module_id, $option, $arOption['DEFAULT']);
		?>
         <tr>
            <td width="40%"><?=$arOption["TITLE"]?></td>
            <td>
                <input type="text" id="<?=$option?>" name="<?=$option?>"  size="50" maxlength="255" value="<?=$val?>">
            </td>
        </tr>
       <?php /*?> <? $option = "TEMPLATE_BONUS_FOR_CATALOG_SECTION";
			$arOption = $arOptions[$option];
			$val = COption::GetOptionString($module_id, $option, $arOption['DEFAULT']);
		?>
         <tr>
            <td width="40%"><?=$arOption["TITLE"]?></td>
            <td>
                <input type="text" id="<?=$option?>" name="<?=$option?>"  size="50" maxlength="255" value="<?=$val?>">
            </td>
        </tr>
        <? $option = "TEMPLATE_BONUS_FOR_CATALOG_ELEMENT";
			$arOption = $arOptions[$option];
			$val = COption::GetOptionString($module_id, $option, $arOption['DEFAULT']);
		?>
         <tr>
            <td width="40%"><?=$arOption["TITLE"]?></td>
            <td>
                <input type="text" id="<?=$option?>" name="<?=$option?>"  size="50" maxlength="255" value="<?=$val?>">
            </td>
        </tr><?php */?>
        
        
    	
    
    
	
    <? $tabControl->BeginNextTab();?>
    <? require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/admin/group_rights.php");?>
    
    <? $tabControl->Buttons();
			
			echo 	'<input type="hidden" name="Update" value="Y" />';
			$tabControl->Buttons(array(
							"back_url" => $APPLICATION->GetCurPage().'?lang='.LANGUAGE_ID
						));
		$tabControl->End();
	?>
    
    <?echo bitrix_sessid_post();?> 
</form>

<? CJSCore::Init(array('jquery2','core_condtree'));?>
<script type="text/javascript">
	BX.ready(function(){
		$("select.LGB_PARENT_SELECT").change(function() {
			var select_name = $(this).attr('name');
			var select_val = $(this).val();
			$("tr."+select_name).each(function(i,elem){
				if($(this).hasClass(select_name+'_'+select_val))
					$(this).show();
				else
					$(this).hide();
			});
			
		});
		
		$("select.LGB_PARENT_SELECT").each(function(i,elem) {
			var select_name = $(this).attr('name');
			var select_val = $(this).val();
			$("tr."+select_name).each(function(i,elem){
				if($(this).hasClass(select_name+'_'+select_val))
					$(this).show();
				else
					$(this).hide();
			});
			
			
		});
	});
</script>

<? \Bitrix\Main\UI\Extension::load("ui.hint");?>
<script type="text/javascript">
	BX.ready(function() {
		BX.UI.Hint.init(BX('logictim.balls_form'));
	})
</script>
