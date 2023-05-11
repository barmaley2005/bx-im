<?

$oSort = new CAdminSorting($arTable["TABLE_NAME"], "ID", "desc");
$lAdmin = new CAdminList($arTable["TABLE_NAME"], $oSort);



//action after elements
if($lAdmin->GroupAction() && $rights > 'E')
{
	$arID = is_array($_REQUEST['ID']) ? $_REQUEST['ID'] : array($_REQUEST['ID']);
	
	if($_REQUEST['action_target']=='selected')
	{
		
	}
	
	foreach($arID as $ID):
		if(strlen($ID) <= 0)
			continue;
		$ID = IntVal($ID);
		
		switch($_REQUEST['action'])
		{
			case "close":
				\Logictim\Balls\PayBonus\ExitBonus::QueryClose(array("id"=>$ID));
			break;
			case "cancel":
				\Logictim\Balls\PayBonus\ExitBonus::QueryCancel(array("id"=>$ID));
			break;
		}
	endforeach;
	
}
//group actions
if($rights > "E")
{
	$lAdmin->AddGroupActionTable(Array(
	  "close"=>GetMessage("MAIN_ADMIN_LIST_CLOSE"),
	  "cancel"=>GetMessage("MAIN_ADMIN_LIST_CANCEL"),
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
				  "find_status",
				  "find_user"
				  );
$lAdmin->InitFilter($FilterArr);
if(CheckFilter())
{
  $arFilter = Array(
					"id" => ($find!="" && $find_type == "id" ? $find : $find_id),
					"status" => ($find!="" && $find_status == "status" ? $find : $find_status),
					"user" => ($find!="" && $find_user == "user" ? $find : $find_user),
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

$rsData = $DB->Query("select * from ".$arTable["TABLE_NAME"].$filterStr." order by ".$by." ".$order);
			
$rsData = new CAdminResult($rsData, $arTable["TABLE_NAME"]);
$rsData->NavStart();
$lAdmin->NavText($rsData->GetNavPrint(GetMessage("logictim.balls_BONUS_PROFILE_NAV")));

$lAdmin->AddHeaders(array(
						array(  "id"    =>"id",
							"content"  =>"ID",
							"sort"     =>"id",
							"default"  =>true,
						  ),
						 array(  "id"    =>"name",
							"content"  => GetMessage("logictim.balls_BONUS_OPERATION_NAME"),
							"sort"     =>"name",
							"default"  =>true,
						  ),
						   array(  "id"    =>"sum",
							"content"  => GetMessage("logictim.balls_BONUS_OPERATION_SUM"),
							"sort"     =>"sum",
							"default"  =>true,
						  ),
						  array(  "id"    =>"date_insert",
							"content"  => GetMessage("logictim.balls_BONUS_OPERATION_DATE_INSERT"),
							"sort"     =>"date_insert",
							"align"    =>"right",
							"default"  =>true,
						  ),
						   array(  "id"    =>"status",
							"content"  => GetMessage("logictim.balls_BONUS_OPERATION_STATUS"),
							"sort"     =>"status",
							"default"  =>true,
						  ),
						  array(  "id"    =>"user",
							"content"  => GetMessage("logictim.balls_BONUS_OPERATION_USER"),
							"sort"     =>"user",
							"default"  =>true,
						  ),
						  
						));
$arStatuses = array(
					"P" => GetMessage("logictim.balls_EXIT_STATUS_P"),
					"F" => GetMessage("logictim.balls_EXIT_STATUS_F"),
					"C" => GetMessage("logictim.balls_EXIT_STATUS_C")
					);
					

while($arRes = $rsData->Fetch())
{
	$row = & $lAdmin->AddRow($arRes['id'], $arRes);
	
	$row->AddViewField("id", '<a href="./'.$pageLink.'?lang='.SITE_ID.'&id='.$arRes['id'].'">'.$arRes['id'].'</a>');
	
	$status =  $arStatuses[$arRes['status']];
	$row->AddViewField("status", $status);
	//$row->AddSelectField("status", $arStatuses);
	
	$rsUser = \CUser::GetByID($arRes["user"]);
	$arUser = $rsUser->Fetch();
	$row->AddViewField("user", '[<a target="_blank" href="/bitrix/admin/user_edit.php?ID='.$arUser["ID"].'&lang='.LANGUAGE_ID.'">'.$arUser["ID"].'</a>] ('.$arUser["LOGIN"].') '.$arUser["NAME"].' '.$arUser["LAST_NAME"]);
	
	$arActions = Array();
	$arActions[] = array(
		"ICON"=>"edit",
		"DEFAULT"=>true,
		"TEXT"=>GetMessage("logictim.balls_BONUS_PROFILE_EDIT"),
		"ACTION"=>$lAdmin->ActionRedirect("./".$pageLink."?id=".$arRes['id'])
	);
	
	
	$row->AddActions($arActions);
}

$lAdmin->AddFooter(
  array(
    array("title"=>GetMessage("MAIN_ADMIN_LIST_SELECTED"), "value"=>$rsData->SelectedRowsCount()),
    array("counter"=>true, "title"=>GetMessage("MAIN_ADMIN_LIST_CHECKED"), "value"=>"0"),
  )
);




$aContext = array(
);
$lAdmin->AddAdminContextMenu($aContext);

$lAdmin->CheckListMode();

$APPLICATION->SetTitle(GetMessage("logictim.balls_BONUS_EXIT_LIST"));

require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_after.php");
// --- Vivod spiska ---//


$oFilter = new CAdminFilter(
  $arTable["TABLE_NAME"]."_filter",
  array(
    "ID",
	GetMessage("logictim.balls_BONUS_OPERATION_STATUS"),
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
<td><?=GetMessage("logictim.balls_BONUS_OPERATION_STATUS")?>:</td>
  <td>
		<select name="find_status">
        	<option value=""><?=GetMessage("logictim.balls_BONUS_EXIT_STATUS_ALL")?></option>
            <? foreach($arStatuses as $arStatusKey => $arStatus):?>
            	
                <option value="<?=$arStatusKey?>" <? if($find_status == $arStatusKey) echo 'selected="selected"';?>><?=$arStatus?></option>
            <? endforeach;?>
        </select>
  </td>
</tr>
<tr>
  <td><?=GetMessage("logictim.balls_BONUS_USER_ID")?>:</td>
  <td>
    <input type="text" name="find_user" size="47" value="<?echo htmlspecialchars($find_user)?>">
  </td>
</tr>
<?
$oFilter->Buttons(array("table_id"=>$arTable["TABLE_NAME"],"url"=>$APPLICATION->GetCurPage(),"form"=>"filter_form"));
$oFilter->End();
?>
</form>
<?
$lAdmin->DisplayList();
?>