<?

$oSort = new CAdminSorting($arTable["TABLE_NAME"], "ID", "desc");
$lAdmin = new CAdminList($arTable["TABLE_NAME"], $oSort);


//update elements
if($lAdmin->EditAction() && $rights > "E")
{
	foreach($FIELDS as $ID => $arFields):
		
		if(!$lAdmin->IsUpdated($ID))
      		continue;
	
		$active=($arFields['active']=='Y')?'Y':'N';
		$DB->Update($arTable["TABLE_NAME"], array(
								'sort'=>(int) $arFields['sort'],
								'active'=>'"'.$active.'"',
								'name'=>"'".$DB->ForSql($arFields['name'])."'"
								), 
					"WHERE ID='".$ID."'", $err_mess.__LINE__);
	endforeach;
}

//action after elements
if($lAdmin->GroupAction() && $rights > 'E')
{
	$arID = is_array($_REQUEST['ID']) ? $_REQUEST['ID'] : array($_REQUEST['ID']);
	
	if($_REQUEST['action_target']=='selected')
	{
		$arID = array();
		$rsData = $DB->Query('SELECT * FROM '.$arTable["TABLE_NAME"].';', false, $err_mess.__LINE__);
		while($arRes = $rsData->Fetch())
		{
			$arID[] = $arRes['id'];
		}
	}
	
	foreach($arID as $ID):
		if(strlen($ID) <= 0)
			continue;
		$ID = IntVal($ID);
		
		switch($_REQUEST['action'])
		{
			case "delete":
				$DB->Query('DELETE FROM '.$arTable["TABLE_NAME"].' WHERE id='.$ID.';', false, 'DELETE_ERROR');
			break;
			case "activate":
			case "deactivate":
				$cData = $DB->Query('SELECT * FROM '.$arTable["TABLE_NAME"].' WHERE id='.$ID.';', false, $err_mess.__LINE__);
				if($arFields = $cData->Fetch())
				{
					$arFields["active"]=($_REQUEST['action']=="activate"?"Y":"N");	
					$DB->Query('UPDATE '.$arTable["TABLE_NAME"].' SET active="'.$arFields["active"].'" WHERE id='.$ID.';', false, $err_mess.__LINE__);
				}
			break;
		}
	endforeach;
	
}
//group actions
if($rights > "E")
{
	$lAdmin->AddGroupActionTable(Array(
	  "edit"=>GetMessage("MAIN_ADMIN_LIST_EDIT"),
	  "delete"=>GetMessage("MAIN_ADMIN_LIST_DELETE"),
	  "activate"=>GetMessage("MAIN_ADMIN_LIST_ACTIVATE"),
	  "deactivate"=>GetMessage("MAIN_ADMIN_LIST_DEACTIVATE"),
	  ));
}


// --- Vivod spiska ---//
function CheckFilter()
{
  global $FilterArr, $lAdmin;
  foreach($FilterArr as $f) global $$f;
  return count($lAdmin->arFilterErrors)==0;
}
$FilterArr = array(
				  "find_id",
				  "find_type",
				  "find_active"
				  );
$lAdmin->InitFilter($FilterArr);
if(CheckFilter())
{
  $arFilter = Array(
					"id" => ($find!="" && $find_type == "id" ? $find : $find_id),
					"type" => ($find!="" && $find_type == "type" ? $find : $find_type),
					"active" => ($find!="" && $find_active == "active" ? $find : $find_active),
				  );
}
$filterStr = '';
if(!empty($arFilter))
{
	foreach($arFilter as $keyFilter => $valFilter):
		if($keyFilter != '' && !empty($valFilter))
		{
			if($filterStr == '')
				$filterStr .= ' where ';
			else
				$filterStr .= ' AND ';
			
			$filterStr .= $keyFilter.'="'.$valFilter.'"';
		}
	endforeach;
}

$rsData = $DB->Query("select
			id,
			sort,
			active,
			name,
			type 
			from ".$arTable["TABLE_NAME"].$filterStr." order by ".$by." ".$order);
			
$rsData = new CAdminResult($rsData, $arTable["TABLE_NAME"]);
$rsData->NavStart();
$lAdmin->NavText($rsData->GetNavPrint(GetMessage("logictim.balls_BONUS_PROFILE_NAV")));

$lAdmin->AddHeaders(array(
						 array(  "id"    =>"name",
							"content"  => GetMessage("logictim.balls_BONUS_PROFILE_NAME"),
							"sort"     =>"name",
							"default"  =>true,
						  ),
						   array(  "id"    =>"type",
							"content"  => GetMessage("logictim.balls_BONUS_PROFILE_TYPE"),
							"sort"     =>"type",
							"default"  =>true,
						  ),
						  array(  "id"    =>"sort",
							"content"  => GetMessage("logictim.balls_BONUS_PROFILE_SORT"),
							"sort"     =>"sort",
							"align"    =>"right",
							"default"  =>true,
						  ),
						   array(  "id"    =>"active",
							"content"  => GetMessage("logictim.balls_BONUS_PROFILE_ACTIVE"),
							"sort"     =>"active",
							"default"  =>true,
						  ),
						  array(  "id"    =>"id",
							"content"  =>"ID",
							"sort"     =>"id",
							"default"  =>true,
						  ),
						));
while($arRes = $rsData->Fetch())
{
	$row = & $lAdmin->AddRow($arRes['id'], $arRes);
	
	$row->AddViewField("id", '<a href="./'.$pageLink.'?lang='.SITE_ID.'&id='.$arRes['id'].'">'.$arRes['id'].'</a>');
	$row->AddCheckField("active");
	$row->AddInputField("sort", array("size"=>20));
	$row->AddInputField("name", array("size"=>20));
	
	$profileTypeName =  GetMessage("logictim.balls_BONUS_PROFILE_TYPE_".$arRes['type']);
	$row->AddViewField("type", $profileTypeName);
	
	$arActions = Array();
	$arActions[] = array(
		"ICON"=>"edit",
		"DEFAULT"=>true,
		"TEXT"=>GetMessage("logictim.balls_BONUS_PROFILE_EDIT"),
		"ACTION"=>$lAdmin->ActionRedirect("./".$pageLink."?id=".$arRes['id'])
	);
	$arActions[] = array(
		"ICON"=>"copy",
		"TEXT"=>GetMessage("logictim.balls_BONUS_PROFILE_COPY"),
		"ACTION"=>$lAdmin->ActionRedirect($pageLink."?action=copy&id=".$arRes['id']),
		//"ACTION"=>"if(confirm('".GetMessage("logictim.balls_BONUS_PROFILE_COPY")."')) ".$lAdmin->ActionRedirect($pageLink."?action=copy&id=".$arRes['id']),
	);
	$arActions[] = array(
		"ICON"=>"delete",
		"TEXT"=>GetMessage("logictim.balls_BONUS_PROFILE_DEL"),
		"ACTION"=>"if(confirm('".GetMessage("logictim.balls_BONUS_PROFILE_DEL")."')) ".$lAdmin->ActionDoGroup($arRes['id'], "delete")
	);
	
	$row->AddActions($arActions);
}

$lAdmin->AddFooter(
  array(
    array("title"=>GetMessage("MAIN_ADMIN_LIST_SELECTED"), "value"=>$rsData->SelectedRowsCount()), // eie-ai yeaiaioia
    array("counter"=>true, "title"=>GetMessage("MAIN_ADMIN_LIST_CHECKED"), "value"=>"0"), // n?ao?ee aua?aiiuo yeaiaioia
  )
);



$conditionTypes = array(
						"order" => GetMessage("logictim.balls_BONUS_PROFILE_ORDER"), 
						"registration" => GetMessage("logictim.balls_BONUS_PROFILE_REGISTER"), 
						"birthday"=> GetMessage("logictim.balls_BONUS_PROFILE_BIRTHDAY"), 
						"review"=> GetMessage("logictim.balls_BONUS_PROFILE_REVIEW"), 
						"subscribe"=> GetMessage("logictim.balls_BONUS_PROFILE_SUBSCRIBE"),
						"reflink"=> GetMessage("logictim.balls_BONUS_PROFILE_REFLINK"),
						"sharing"=> GetMessage("logictim.balls_BONUS_PROFILE_SHARING"), 
						);
foreach($conditionTypes as $keyConditionType => $nameConditionType):
	$ConditionTypesMenu[] = array(
				"TEXT" => $nameConditionType,
				"ACTION" => $lAdmin->ActionRedirect("./".$pageLink."?id=new&type=".$keyConditionType)
			);
endforeach;
$conditionReferalTypes = array(
								"order_referal" => GetMessage("logictim.balls_BONUS_PROFILE_ORDER_REFERAL"),
								/*"registration_referal" => GetMessage("logictim.balls_BONUS_PROFILE_REGISTER_REFERAL")*/
								);
foreach($conditionReferalTypes as $keyConditionType => $nameConditionType):
	$ConditionReferalMenu[] = array(
				"TEXT" => $nameConditionType,
				"ACTION" => $lAdmin->ActionRedirect("./".$pageLink."?id=new&type=".$keyConditionType)
			);
endforeach;
$conditionPayBonus = array("pay_bonus" => GetMessage("logictim.balls_BONUS_PROFILE_PAY"), "exit_bonus" => GetMessage("logictim.balls_BONUS_PROFILE_EEXIT_BONUS"));
foreach($conditionPayBonus as $keyConditionType => $nameConditionType):
	$ConditionPayBonusMenu[] = array(
				"TEXT" => $nameConditionType,
				"ACTION" => $lAdmin->ActionRedirect("./".$pageLink."?id=new&type=".$keyConditionType)
			);
endforeach;
$aContext = array(
  array(
    "TEXT" => GetMessage("logictim.balls_BONUS_PROFILES_ADD_ACTION"),
    "LINK" => $pageLink."?lang=".LANG."&id=new",
    "TITLE" => GetMessage("logictim.balls_BONUS_PROFILES_ADD_ACTION"),
    "ICON" => "btn_new",
	"MENU" => $ConditionTypesMenu
  ),
   array(
    "TEXT" => GetMessage("logictim.balls_BONUS_PROFILES_ADD_REFERAL"),
    "LINK" => $pageLink."?lang=".LANG."&id=new",
    "TITLE" => GetMessage("logictim.balls_BONUS_PROFILES_ADD_REFERAL"),
    "ICON" => "btn_new",
	"MENU" => $ConditionReferalMenu
  ),
  array(
    "TEXT" => GetMessage("logictim.balls_BONUS_PROFILES_ADD_PAYMENT"),
    "LINK" => $pageLink."?lang=".LANG."&id=new",
    "TITLE" => GetMessage("logictim.balls_BONUS_PROFILES_ADD_PAYMENT"),
    "ICON" => "btn_new",
	"MENU" => $ConditionPayBonusMenu
  ),
);
$lAdmin->AddAdminContextMenu($aContext);

$lAdmin->CheckListMode();

$APPLICATION->SetTitle(GetMessage("logictim.balls_BONUS_PROFILES_LIST"));

require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_after.php");
// --- Vivod spiska ---//


$oFilter = new CAdminFilter(
  $arTable["TABLE_NAME"]."_filter",
  array(
    "ID",
	GetMessage("logictim.balls_BONUS_PROFILE_TYPE"),
	GetMessage("logictim.balls_BONUS_PROFILE_ACTIVE")
  )
);

require_once($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/logictim.balls/admin/header.php');
?>
<form name="filter_form" method="get" action="<?echo $APPLICATION->GetCurPage();?>">
<?$oFilter->Begin();?>
<tr>
  <td><?="ID"?>:</td>
  <td>
    <input type="text" name="find_id" size="47" value="<?echo htmlspecialchars($find_id)?>">
  </td>
</tr>
<tr>
<td><?=GetMessage("logictim.balls_BONUS_PROFILE_TYPE")?>:</td>
  <td>
		<select name="find_type">
        	<option value=""><?=GetMessage("logictim.balls_BONUS_PROFILE_TYPE_ALL")?></option>
        	<option value="order" <? if($find_type == 'order') echo 'selected="selected"';?>><?=GetMessage("logictim.balls_BONUS_PROFILE_TYPE_order")?></option>
            <option value="registration" <? if($find_type == 'registration') echo 'selected="selected"';?>><?=GetMessage("logictim.balls_BONUS_PROFILE_TYPE_registration")?></option>
            <option value="birthday" <? if($find_type == 'birthday') echo 'selected="selected"';?>><?=GetMessage("logictim.balls_BONUS_PROFILE_TYPE_birthday")?></option>
            <option value="review" <? if($find_type == 'review') echo 'selected="selected"';?>><?=GetMessage("logictim.balls_BONUS_PROFILE_TYPE_review")?></option>
            <option value="subscribe" <? if($find_type == 'subscribe') echo 'selected="selected"';?>><?=GetMessage("logictim.balls_BONUS_PROFILE_TYPE_subscribe")?></option>
            <option value="reflink" <? if($find_type == 'reflink') echo 'selected="selected"';?>><?=GetMessage("logictim.balls_BONUS_PROFILE_TYPE_reflink")?></option>
            <option value="order_referal" <? if($find_type == 'order_referal') echo 'selected="selected"';?>><?=GetMessage("logictim.balls_BONUS_PROFILE_TYPE_order_referal")?></option>
            <option value="pay_bonus" <? if($find_type == 'pay_bonus') echo 'selected="selected"';?>><?=GetMessage("logictim.balls_BONUS_PROFILE_TYPE_pay_bonus")?></option>
        </select>
  </td>
</tr>
<td><?=GetMessage("logictim.balls_BONUS_PROFILE_ACTIVE")?>:</td>
  <td>
        <select name="find_active">
        	<option value=""><?=GetMessage("logictim.balls_BONUS_PROFILE_TYPE_ALL")?></option>
            <option value="Y" <? if($find_aactive == 'Y') echo 'selected="selected"';?>><?=GetMessage("logictim.balls_BONUS_PROFILE_ACTIVE_Y")?></option>
            <option value="N" <? if($find_aactive == 'N') echo 'selected="selected"';?>><?=GetMessage("logictim.balls_BONUS_PROFILE_ACTIVE_N")?></option>
        </select>
  </td>
</tr>
<?
$oFilter->Buttons(array("table_id"=>$arTable["TABLE_NAME"],"url"=>$APPLICATION->GetCurPage(),"form"=>"filter_form"));
$oFilter->End();
?>
</form>
<?php /*?><a href="<?=$APPLICATION->GetCurPage().'?lang='.LANGUAGE_ID.'&upgrade=Y'?>" hidefocus="true"  class="adm-btn adm-btn-save adm-btn-add adm-btn-menu" title="Aiaaaeou ia?eneaiea aiionia">Aiaaaeou ia?eneaiea aiionia</a>
<?
if(!empty($request['upgrade']) && $request['upgrade'] == 'Y'):
	LocalRedirect($APPLICATION->GetCurPage().'?&lang='.LANGUAGE_ID);
endif;
?><?php */?>
<?
$lAdmin->DisplayList();
?>