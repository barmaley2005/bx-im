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
</style>

<?

if($request['id'] == 'new')
{
	$jsonProductConditions = \Logictim\Balls\Conditions\Products::BaseConditions('json');
	$jsonProfileConditions = \Logictim\Balls\Conditions\ProfileOrder::BaseConditions('json');
	
	$activeFrom = '';
	$activeTo = '';
}
else
{
	$arProductConditions = unserialize($arProfile["conditions"]);
	$arProductConditions = \Logictim\Balls\Conditions::SetLabelsProduct($arProductConditions);
	$jsonProductConditions = \Bitrix\Main\Web\Json::encode($arProductConditions);
	
	$arProfileConditions = unserialize($arProfile["profile_conditions"]);
	$arProfileConditions = \Logictim\Balls\Conditions::SetLabelsProfile($arProfileConditions);
	$jsonProfileConditions = \Bitrix\Main\Web\Json::encode($arProfileConditions);
}

if($profileType == 'order')
	$APPLICATION->SetTitle(GetMessage("logictim.balls_BONUS_FROM_ORDER"));
elseif($profileType == 'order_referal')
	$APPLICATION->SetTitle(GetMessage("logictim.balls_BONUS_FROM_ORDER_REFERAL"));


$aTabs = array(
	array("DIV" => "logictim_balls_tab_1", "TAB" => GetMessage("logictim.balls_PROFILE_ORDER_TAB_1"), "TITLE" => GetMessage("logictim.balls_PROFILE_ORDER_TAB_1")),
	array("DIV" => "logictim_balls_tab_2", "TAB" => GetMessage("logictim.balls_PROFILE_ORDER_TAB_2"), "TITLE" => GetMessage("logictim.balls_PROFILE_ORDER_TAB_2")),
	array("DIV" => "logictim_balls_tab_3", "TAB" => GetMessage("logictim.balls_PROFILE_ORDER_TAB_3"), "TITLE" => GetMessage("logictim.balls_PROFILE_ORDER_TAB_3"))
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
		$arSaveFields['sort'] = $sort;
		$arSaveFields['type'] = '"'.$request['type'].'"';
		$arSaveFields['active_after_period'] = $activeAfter;
		$arSaveFields['active_after_type'] = '"'.$request['active_after_type'].'"';
		$arSaveFields['deactive_after_period'] = $deActiveAfter;
		$arSaveFields['deactive_after_type'] = '"'.$request['deactive_after_type'].'"';
		
		$arSaveFields['active_from'] = '"'.\ConvertDateTime($request['active_from'], "YYYY-MM-DD HH:MI:SS", LANG).'"';
		$arSaveFields['active_to'] = '"'.\ConvertDateTime($request['active_to'], "YYYY-MM-DD HH:MI:SS", LANG).'"';
		
		if(!empty($request["profileProductsCond"]))
		{
			$saveConditions = \Logictim\Balls\Conditions::SaveConditions($request["profileProductsCond"]);
			$arSaveFields['conditions'] = "'".serialize($saveConditions)."'";
		}
		if(!empty($request["profileCond"]))
		{
			$saveProfileConditions = \Logictim\Balls\Conditions::SaveConditions($request["profileCond"]);
			$arSaveFields['profile_conditions'] = "'".serialize($saveProfileConditions)."'";
		}
		
		if(!empty($request["always_view"]))
			$arOptions['always_view'] = $request['always_view'];
		if(!empty($request["referal_coupon_discount"]))
			$arOptions['referal_coupon_discount'] = $request['referal_coupon_discount'];
		$arSaveFields['other_conditions'] = "'".serialize($arOptions)."'";
		
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
	
    <form class="logictim_profile" id="logictim_profile_form" name="logictim_profile" method="post" action="<?echo $APPLICATION->GetCurPage()?>?lang=<?=LANGUAGE_ID?>">
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
					$profileName = GetMessage("logictim.balls_BONUS_FROM_ORDER");
					$sort = 100;
					$activeAfter = 0;
					$activeAfterType = 'D';
					$deActiveAfter = 365;
					$deActiveAfterType = 'D';
					$always_view = 'N';
					
					if($profileType == 'order')
						$profileName = $profileTypeName = GetMessage("logictim.balls_BONUS_FROM_ORDER");
					elseif($profileType == 'order_referal')
						$profileName = $profileTypeName = GetMessage("logictim.balls_BONUS_FROM_ORDER_REFERAL");
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
					
					
					$arOptions = unserialize($arProfile["other_conditions"]);
					$always_view = $arOptions["always_view"];
					$referalCouponDiscount = $arOptions["referal_coupon_discount"];
					
					if($profileType == 'order')
						$profileTypeName = GetMessage("logictim.balls_BONUS_FROM_ORDER");
					elseif($profileType == 'order_referal')
						$profileTypeName = GetMessage("logictim.balls_BONUS_FROM_ORDER_REFERAL");
				}
				?>
                
				<tr><td width="40%"><?=GetMessage("logictim.balls_PROFILE_TYPE")?></td><td><?=$profileTypeName?></td></tr>
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
            
            <?
			if($profileType == 'order_referal' && COption::GetOptionString("logictim.balls", "REFERAL_USE_COUPONS", 'N') == 'Y' && COption::GetOptionString("logictim.balls", "REFERAL_COUPON_DISCOUNT_IN_PROFILE", 'N') == 'Y'):
				//Select discounts
				$arDiscounts = array();
				$discountIterator = \Bitrix\Sale\Internals\DiscountTable::getList(array('filter' => array()));
				while($discount = $discountIterator->fetch())
				{
					$arDiscounts[$discount["ID"]] = array("ID" => $discount["ID"], "NAME" => $discount["NAME"]);
				}
				if($referalCouponDiscount > 0)
					$referalCouponDiscount;
				else
					$referalCouponDiscount = (int)COption::GetOptionString("logictim.balls", "REFERAL_COUPON_DISCOUNT", 0);
			?>
            	
            	<tr class="heading" id="tr_BT_SALE_DISCOUNT_SECT_APP"><td colspan="2"><?=GetMessage("logictim.balls_BONUS_COUPUN_DISCOUNT_HEADER")?></td></tr>
                <tr>
                    <td width="40%">
                    	<span data-hint="<?=GetMessage("logictim.balls_BONUS_COUPUN_DISCOUNT_HINT")?>"></span>
						<?=GetMessage("logictim.balls_BONUS_COUPUN_DISCOUNT")?>
                    </td>
                    <td>
                        <select name="referal_coupon_discount" style="margin-left:5px;">
                        	<option value="0" <? if($referalCouponDiscount == 0) echo 'selected="selected"'?>><?=GetMessage("logictim.balls_BONUS_COUPUN_DISCOUNT_DEFAULT")?></option>
                        	<? foreach($arDiscounts as $arDiscount):?>
                            	<option value="<?=$arDiscount["ID"]?>" <? if($arDiscount["ID"] == $referalCouponDiscount) echo 'selected="selected"'?>><?=$arDiscount["NAME"]?></option>
                            <? endforeach;?>
                        </select>
                    </td>
                </tr>
            <? endif;?>
            
            
        <? $tabControl->BeginNextTab();?>
        
        	<tr class="heading" id="tr_BT_SALE_DISCOUNT_SECT_APP"><td colspan="2"><?=GetMessage("logictim.balls_PROFILE_COND_SECT")?></td></tr>
            <tr><td width="100%" colspan="2">
            	<div id="ProfileConditions"></div>
                <script>
                    var JSSaleAct=new BX.TreeConditions(<?=\Logictim\Balls\Conditions\ProfileOrder::MainParams('json');?>,<?=$jsonProfileConditions?>,<?=\Logictim\Balls\Conditions\ProfileOrder::Controls('json', $profileType)?>);
                </script>
                
                <div class="lb_description">
                	<? 
					if($profileType == 'order')
                		echo GetMessage("logictim.balls_PROFILE_COND_COMMENT");
					elseif($profileType == 'order_referal')
						echo GetMessage("logictim.balls_PROFILE_COND_REFERAL_COMMENT");
                    ?>
                    <? if($profileType == 'order') {?>
                    <input type="checkbox" name="always_view" value="Y" <? if($always_view == "Y") echo " checked"?> /><?=GetMessage("logictim.balls_PROFILE_COND_ABSOLUTE")?>
                    <? }?>
                </div>
            </td></tr>
            
        
        <? $tabControl->BeginNextTab();?>
        
        	<tr class="heading" id="tr_BT_SALE_DISCOUNT_SECT_APP"><td colspan="2"><?=GetMessage("logictim.balls_PRODUCTS_COND_SECT")?></td></tr>
            <tr><td width="100%" colspan="2">
            	<div id="ProductsConditions"></div>
                <script>
                    var JSSaleAct=new BX.TreeConditions(<?=\Logictim\Balls\Conditions\Products::MainParams('json');?>,<?=$jsonProductConditions?>,<?=\Logictim\Balls\Conditions\Products::Controls('json')?>);
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

<style type="text/css">

</style>
<script type="text/javascript">

BX.ready(function(){
	
	setArrowsCond();
	
	$(document).on('click', '.lb_cond_up', function(){ 
        var pdiv = $(this).parent('div');
        pdiv.insertBefore(pdiv.prev(".condition-wrapper"));
		setArrowsCond();
        return false
    });
	
	$(document).on('click', '.lb_cond_down', function(){ 
        var pdiv = $(this).parent('div');
        pdiv.insertAfter(pdiv.next(".condition-wrapper"));
		setArrowsCond();
        return false
    });
	
	$('.condition-wrapper').on('change', function(){
		setArrowsCond();
	});
});

function setArrowsCond(mode)
{
	var up_block = '<div class="lb_cond_up lb_arrow"></div>';
	var down_block = '<div class="lb_cond_down lb_arrow"></div>';
	
	$(".lb_arrow").remove();
	$("#ProductsConditions .condition-container").before(up_block);
	$("#ProductsConditions .condition-container").before(down_block);
	
	$("#ProductsConditions .condition-wrapper .condition-wrapper .lb_arrow").show();
	$("#ProductsConditions .condition-wrapper .condition-wrapper .lb_cond_up:first").hide();
	$("#ProductsConditions .condition-wrapper .condition-wrapper .lb_cond_down:last").hide();
}

</script>

<? \Bitrix\Main\UI\Extension::load("ui.hint");?>
<script type="text/javascript">
	BX.ready(function() {
		BX.UI.Hint.init(BX('logictim_profile_form'));
	})
</script>
