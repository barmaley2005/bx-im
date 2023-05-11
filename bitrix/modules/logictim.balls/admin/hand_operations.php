<? require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");

global $APPLICATION, $DB;
$APPLICATION->SetTitle(GetMessage("logictim.balls_BONUS_OPERATION_TITLE"));

use Bitrix\Main,
	Bitrix\Main\Localization\Loc,
	Bitrix\Main\Application;
	
Loc::loadMessages(__FILE__);
	
$module_id='logictim.balls';
\Bitrix\Main\Loader::includeModule($module_id);

$context = Application::getInstance()->getContext();
$request = $context->getRequest();

$arGroupsUsers = array();

if((!empty($request['apply']) || !empty($request['save'])) && check_bitrix_sessid())
{
	$operationType = $request['type_operation'];
	$operationSum = $request['bonus'];
	$deactivePeriod = $request['deactive_after_period'];
	$deactiveType = $request['deactive_after_type'];
	$typeUsers = $request['type_users'];
	$arGroupsUsers = $request['group_users'];
	$operationName = $request['operation_name'];
	
	
	$arErrors = array();
	if($operationSum == '')
		$arErrors['NO_BONUS'] = GetMessage("logictim.balls_ERROR_NO_BONUS");
	if($operationType == 'plus' && $deactivePeriod == '')
		$arErrors['NO_DEACTIVE_PERIOD'] = GetMessage("logictim.balls_ERROR_NO_LIVE_PERIOD");
	if($operationName == '')
		$arErrors['NO_OPERATION_NAME'] = GetMessage("logictim.balls_ERROR_NO_OPERATION_NAME");
	
	$arUsers = array();
	if($typeUsers == 'group' && !empty($arGroupsUsers)):
		$rsUsers = CUser::GetList(($by="id"), ($order="desc"), array("GROUPS_ID"=> $arGroupsUsers));
		while($arItem = $rsUsers->GetNext()) 
		{
			$arUsers[] = $arItem['ID'];
		}
	endif;
	if($typeUsers == 'user'):
		foreach($request as $keyReq => $req):
			if(strpos($keyReq, 'USER_ID_') !== false && $req > 0)
				$arUsers[] = $req;
		endforeach;
	endif;
	
	if(empty($arUsers))
		$arErrors['NO_USERS'] = GetMessage("logictim.balls_ERROR_NO_USERS");
	
	if(!empty($arUsers) && empty($arErrors)):
	endif;
	
	//echo '<pre>'; print_r($request); echo '</pre>';
}





$rights = $APPLICATION->GetGroupRight($module_id);

if($rights == "D")
    $APPLICATION->AuthForm(GetMessage("ACCESS_DENIED"));
	
require($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/main/include/prolog_admin_after.php');

if((!empty($request['apply']) || !empty($request['save'])) && check_bitrix_sessid() && empty($arErrors))
	CAdminMessage::ShowMessage(array("TYPE" => "OK", "MESSAGE" => GetMessage("logictim.balls_OK_MESSAGE")));
if((!empty($request['apply']) || !empty($request['save'])) && check_bitrix_sessid() && !empty($arErrors))
{
	foreach($arErrors as $error):
		CAdminMessage::ShowMessage(array("TYPE"=>"ERROR", "MESSAGE"=>$error));
	endforeach;
	
}

require_once($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/logictim.balls/admin/header.php');

$aTabs = array(
	array("DIV" => "logictim_balls_tab_1", "TAB" => GetMessage("logictim.balls_BONUS_TITLE"), "TITLE" => GetMessage("logictim.balls_BONUS_TITLE")),
);
$tabControl = new CAdminTabControl("tabControl".$profileId, $aTabs);

$tabControl->Begin();?>
<? CJSCore::Init(array('jquery2'));?>
<form class="logictim_hand_operations" name="logictim_hand_operations" method="post" action="<?echo $APPLICATION->GetCurPage()?>?lang=<?=LANGUAGE_ID?>">
        <?echo bitrix_sessid_post();?>
        
		<? $tabControl->BeginNextTab();?>
        
        	<? if(!empty($arUsers) && empty($arErrors)):
			
				session_start();
				$_SESSION['lb_hand_operation'] = array(
														'elements' => $arUsers,
														'count' => count($arUsers),
														'progress_counter' => 0,
														'PROGRESS_BAR' => 0,
														'operation_params' => array(
																					'operationType' => $operationType,
																					'operationSum' => $operationSum,
																					'deactivePeriod' => $deactivePeriod,
																					'deactiveType' => $deactiveType,
																					'typeUsers' => $typeUsers,
																					'arGroupsUsers' => $arGroupsUsers,
																					'operationName' => $operationName
																					)
														);
			?>
            
            	<tr>
                	<td colspan="2" width="100%">
                        <div id="progress_block">
                           <div class="lb_demo-wrapper lb_html5-progress-bar">
                                <div class="progress-bar-wrapper">
                                    <progress id="progressbar" value="0" max="100"></progress>
                                    <span class="progress-value" id="progress-value">0%</span>
                                </div>
                            </div>
                            
                            <div class="adm-info-message-wrap adm-info-message-red" id="operation_process" style="text-align:center;">
                                <div class="adm-info-message">
                                    <div class="adm-info-message-title"><?=GetMessage("logictim.balls_HAND_OPERATION_WAIT")?></div>
                                    <div class="adm-info-message-icon"></div>
                                </div>
                            </div>
                            
                        </div>
                        <div id="operation_result" style="width:100%; text-align:center; display:none;">
                        	<div class="adm-info-message-wrap adm-info-message-green">
                                <div class="adm-info-message">
                                    <div class="adm-info-message-title"><?=GetMessage("logictim.balls_OK_MESSAGE")?></div>
                                    <div class="adm-info-message-icon"></div>
                                </div>
                            </div>
                        	<span><?=GetMessage("logictim.balls_HAND_OPERATION_COMPLITE")?></span> <span id="operation_result_count"></span>
                            <br /><br />
                            <a href="<?=$APPLICATION->GetCurPage().'?lang='.LANGUAGE_ID?>" class="lb_submit"><?=GetMessage("logictim.balls_HAND_OPERATION_ELSE")?></a>
                        </div>
                    </td>
                </tr>
            	<script type="text/javascript">
					BX.ready(function(){
						LBHandOperation();
					});
					
					function LBHandOperation()
					{
						var data = new Object();
						data["TYPE"] = 'HandOperation';
						$("#progress_block").show();
						BX.ajax({
							url: '/bitrix/components/logictim/bonus.ajax/bonus_api_ajax.php',
							method: 'POST',
							data: data,
							dataType: 'json',
							onsuccess: function(result) {
								if(typeof(bxSession) != 'undefined')
								{
									bxSession.Expand(bxSession.timeout, bxSession.sessid, false, bxSession.key);
								}
								if(result.PROGRESS_BAR >= 0)
								{
									$("#progressbar").val(result.PROGRESS_BAR);
									$("#progress-value").text(result.PROGRESS_BAR+'%');
									
									if(result.PROGRESS_BAR_END != 'Y')
										LBHandOperation();
									else
									{
										$("#operation_result_count").text(result.ITERATION);
										$("#operation_process").hide();
										$("#operation_result").show();
									}
								}
							}
						});
					}
				</script>
                
        	
            <? else:?>
        		<tr>
                	<td width="40%">
                    	<?=GetMessage("logictim.balls_BONUS_OPERATION_TYPE")?>
                    </td>
                    <td>
                        <select name="type_operation" id="type_operation">
                            <option value="plus" <? if($operationType == 'plus') echo 'selected="selected"';?>><?=GetMessage("logictim.balls_BONUS_OPERATION_PLUS")?></option>
                            <option value="minus" <? if($operationType == 'minus') echo 'selected="selected"';?>><?=GetMessage("logictim.balls_BONUS_OPERATION_MINUS")?></option>
                        </select>
                	</td>
                </tr>
                
                <tr>
                	<td width="40%">
                    	<?=GetMessage("logictim.balls_BONUS_OPERATION_NAME")?>
                    </td>
                    <td>
                        <input type="text" name="operation_name" placeholder="" size="70" value="<?=$operationName?>" />
                	</td>
                </tr>
                
                <tr>
                	<td width="40%">
                    	<?=GetMessage("logictim.balls_BONUS_OPERATION_BONUS")?>
                    </td>
                    <td>
                        <input type="number" name="bonus" />
                	</td>
                </tr>
                <tr id="row_period">
            		<td width="40%"><?=GetMessage("logictim.balls_BONUS_LIVE_TIME")?></td>
                    <td>
                        <input type="text" size="5" name="deactive_after_period" value="<? echo $deactivePeriod ? $deactivePeriod : 365?>">
                        <select name="deactive_after_type" style="margin-left:5px;">
                            <option value="D" <? if($deactiveType == 'D') echo 'selected="selected"';?>><?=GetMessage("logictim.balls_BONUS_PERIOD_DAY")?></option>
                            <option value="M" <? if($deactiveType == 'M') echo 'selected="selected"';?>><?=GetMessage("logictim.balls_BONUS_PERIOD_MONTH")?></option>
                        </select>
                    </td>
            	</tr>
                
                <tr>
                	<td width="40%">
                    	<?=GetMessage("logictim.balls_BONUS_USER_TYPE")?>
                    </td>
                    <td>
                        <select name="type_users" id="type_users">
                            <option value="group" <? if($typeUsers == 'group') echo 'selected="selected"';?>><?=GetMessage("logictim.balls_BONUS_USER_TYPE_GROUPS")?></option>
                            <option value="user" <? if($typeUsers == 'user') echo 'selected="selected"';?>><?=GetMessage("logictim.balls_BONUS_USER_TYPE_USERS")?></option>
                        </select>
                	</td>
                </tr>
                
                <?
				$arUserGroups = array();
				$rsGroups = \CGroup::GetList(($by="id"), ($order="asc"), array("ACTIVE"  => "Y"));
				while($arUserGroup = $rsGroups->Fetch()) {
					$arUserGroups[$arUserGroup["ID"]] = $arUserGroup["NAME"];
				}
				?>
                <tr id="row_groups">
                	<td width="40%">
                    	<?=GetMessage("logictim.balls_BONUS_GROUPS")?>
                    </td>
                    <td>
                        <select name="group_users[]" multiple="multiple" size="7">
                        	<? foreach($arUserGroups as $userGroupId => $userGroupName):?>
                            	<option value="<?=$userGroupId?>" <? if(in_array($userGroupId, $arGroupsUsers)) echo 'selected="selected"';?>><?=$userGroupName?></option>
                            <? endforeach;?>
                        </select>
                	</td>
                </tr>
                
                <tr id="row_users">
                	<td width="40%">
                    	<?=GetMessage("logictim.balls_BONUS_USERS")?>
                    </td>
                    <td>
                        <? $name = "<a href=\"/bitrix/admin/user_edit.php?lang=".LANGUAGE_ID."&id=".$USER_ID."\">".$USER_ID."</a> ".$LOGIN." ".$NAME;?>
						
                        <div class="add_user" id="add_user_1">
							<? echo FindUserID("USER_ID_1", $USER_ID, $name, "logictim_hand_operations", "3", "", "...", "inputtext", "inputbodybutton");?>
                        </div>
                        <a href="#" onclick="addUser(); return false;"><?=GetMessage("logictim.balls_ADD_USER")?></a>
                	</td>
                </tr>
                
                <? $tabControl->Buttons(array("back_url" => $APPLICATION->GetCurPage().'?lang='.LANGUAGE_ID));?>
                
                
				<script type="text/javascript">
                    
                    $("body").on('DOMSubtreeModified', ".adm-filter-text-search", function() {
                        var id = $(this).children('a.tablebodylink').html();
                        var blockId = $(this).attr('id');
                        
                        if(id > 0 && $(this).children('.user_bonus').length == 0)
                        {
                            var data = new Object();
                            data["TYPE"] = 'UserBallance';
                            data["USER_ID"] = id;
                            BX.ajax({
                                url: '/bitrix/components/logictim/bonus.ajax/bonus_api_ajax.php',
                                method: 'POST',
                                data: data,
                                dataType: 'json',
                                onsuccess: function(result) {
                                    if(result.USER_BONUS > 0)
                                        BX(blockId).appendChild(BX.create('text', {attrs: {className: 'user_bonus'}, html: ' (<?=GetMessage("logictim.balls_USER_BALLANCE")?>: '+result.USER_BONUS+')'}));
                                }
                            });
                        }
                    });
                    
                    function addUser(event) {
                        var last_id = $(".add_user:last").attr('id');
                        var expId = last_id.split('add_user_');
                        var id = expId[1]*1;
                        var newId = id+1;
                        var last_div = $(".add_user:last").html();
                        
                        var regex = new RegExp('USER_ID_'+id,'g');
                        var new_div = last_div.replace(regex, "USER_ID_"+newId);
                        
                        var regex = new RegExp('USERxIDx'+id,'g');
                        var new_div = new_div.replace(regex, "USERxIDx"+newId);
                        
                        $('<div class="add_user" id="add_user_'+newId+'">'+new_div+'</div>').insertAfter($(".add_user:last"));
                        
                        //console.log(new_div);
                    }
                    BX.ready(function(){
                        ViewFields();
                        $("#type_operation").change(function(){ViewFields();});
                        $("#type_users").change(function(){ViewFields();});
                    });
                    function ViewFields()
                    {
                        var operationType = BX('type_operation').value;
                        if(operationType == 'plus')
                            BX.show(BX('row_period'));
                        else
                            BX.hide(BX('row_period'));
                            
                        var typeUsers = BX('type_users').value;
                        if(typeUsers == 'group')
                        {
                            BX.show(BX('row_groups'));
                            BX.hide(BX('row_users'));
                        }
                        if(typeUsers == 'user')
                        {
                            BX.show(BX('row_users'));
                            BX.hide(BX('row_groups'));
                        }
                        
                    }
                </script>
                
            <? endif;?>
                
                
        
       <?echo bitrix_sessid_post();?> 
    </form>
    <?$tabControl->End();?>
    




<? require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin.php");?>