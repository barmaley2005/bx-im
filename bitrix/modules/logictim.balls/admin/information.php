<? require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");

global $APPLICATION;
$APPLICATION->SetTitle(GetMessage("logictim.balls_INFO"));

use Bitrix\Main,
	Bitrix\Main\Localization\Loc,
	Bitrix\Main\Application;
	
Loc::loadMessages(__FILE__);
	
$module_id='logictim.balls';
\Bitrix\Main\Loader::includeModule($module_id);

$context = Application::getInstance()->getContext();
$request = $context->getRequest();







$rights = $APPLICATION->GetGroupRight($module_id);

if($rights == "D")
    $APPLICATION->AuthForm(GetMessage("ACCESS_DENIED"));
	
require($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/main/include/prolog_admin_after.php');

require_once($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/logictim.balls/admin/header.php');

$aTabs = array(
	array("DIV" => "logictim_balls_tab_1", "TAB" => GetMessage("logictim.balls_INFO_OPTIONS_TAB"), "TITLE" => GetMessage("logictim.balls_INFO_OPTIONS_TAB")),
	//array("DIV" => "logictim_balls_tab_1", "TAB" => GetMessage("logictim.balls_BONUS_TITLE"), "TITLE" => GetMessage("logictim.balls_BONUS_TITLE")),
);
$tabControl = new CAdminTabControl("tabControl".$profileId, $aTabs);

$tabControl->Begin();?>

		<? $tabControl->BeginNextTab();?>
        	
            
            <div>
            	<h3><?=GetMessage("logictim.balls_INFO_RULES")?></h3>
                <div class="info_list">
                	<ul>
                    	<li><?=GetMessage("logictim.balls_INFO_RULES_MAIN")?> <a target="_blank" href="https://logictim.ru/marketplace/nakopitelnaya_referalnaya_sistema_versii_4_0_i_vyshe/instruktsii_po_nastroyke_4/nastroyka_pravil/nastroyka_pravil/"><?=GetMessage("logictim.balls_INFO_OPTIONS_SEE")?></a></li>
                        <li><?=GetMessage("logictim.balls_INFO_RULES_CONDITIONS")?> <a target="_blank" href="https://logictim.ru/marketplace/nakopitelnaya_referalnaya_sistema_versii_4_0_i_vyshe/instruktsii_po_nastroyke_4/nastroyka_pravil/usloviya_primeneniya_pravila/"><?=GetMessage("logictim.balls_INFO_OPTIONS_SEE")?></a></li>
                        <li><?=GetMessage("logictim.balls_INFO_RULES_CONDITIONS_PRODUCT")?> <a target="_blank" href="https://logictim.ru/marketplace/nakopitelnaya_referalnaya_sistema_versii_4_0_i_vyshe/instruktsii_po_nastroyke_4/nastroyka_pravil/usloviya_otbora_tovarov/"><?=GetMessage("logictim.balls_INFO_OPTIONS_SEE")?></a></li>
                    </ul>
                    <ul>
                        <li><?=GetMessage("logictim.balls_INFO_RULES_FROM_ORDER")?> <a target="_blank" href="https://logictim.ru/marketplace/nakopitelnaya_referalnaya_sistema_versii_4_0_i_vyshe/instruktsii_po_nastroyke_4/nastroyka_pravil/bonusy_za_zakaz_4/"><?=GetMessage("logictim.balls_INFO_OPTIONS_SEE")?></a></li>
                        <li><?=GetMessage("logictim.balls_INFO_RULES_FROM_REGISTER")?> <a target="_blank" href="https://logictim.ru/marketplace/nakopitelnaya_referalnaya_sistema_versii_4_0_i_vyshe/instruktsii_po_nastroyke_4/nastroyka_pravil/bonusy_za_registratsiyu/"><?=GetMessage("logictim.balls_INFO_OPTIONS_SEE")?></a></li>
                        <li><?=GetMessage("logictim.balls_INFO_RULES_FROM_BIRTHDAY")?> <a target="_blank" href="https://logictim.ru/marketplace/nakopitelnaya_referalnaya_sistema_versii_4_0_i_vyshe/instruktsii_po_nastroyke_4/nastroyka_pravil/bonusy_na_den_rozhdeniya/"><?=GetMessage("logictim.balls_INFO_OPTIONS_SEE")?></a></li>
                        <li><?=GetMessage("logictim.balls_INFO_RULES_FROM_REVIEW")?> <a target="_blank" href="https://logictim.ru/marketplace/nakopitelnaya_referalnaya_sistema_versii_4_0_i_vyshe/instruktsii_po_nastroyke_4/nastroyka_pravil/bonusy_za_otzyv_na_sayte/"><?=GetMessage("logictim.balls_INFO_OPTIONS_SEE")?></a></li>
                        <li><?=GetMessage("logictim.balls_INFO_RULES_FROM_SUBSCRIBE")?> <a target="_blank" href="https://logictim.ru/marketplace/nakopitelnaya_referalnaya_sistema_versii_4_0_i_vyshe/instruktsii_po_nastroyke_4/nastroyka_pravil/bonusy_za_podpisku/"><?=GetMessage("logictim.balls_INFO_OPTIONS_SEE")?></a></li>
                        <li><?=GetMessage("logictim.balls_INFO_RULES_FROM_REF_LINK")?> <a target="_blank" href="https://logictim.ru/marketplace/nakopitelnaya_referalnaya_sistema_versii_4_0_i_vyshe/instruktsii_po_nastroyke_4/nastroyka_pravil/bonusy_za_perekhod_po_repostu_ref_ssylke/"><?=GetMessage("logictim.balls_INFO_OPTIONS_SEE")?></a></li>
                        <li><?=GetMessage("logictim.balls_INFO_RULES_FROM_REFERAL_ORDER")?> <a target="_blank" href="https://logictim.ru/marketplace/nakopitelnaya_referalnaya_sistema_versii_4_0_i_vyshe/instruktsii_po_nastroyke_4/nastroyka_pravil/bonusy_za_zakaz_referala/"><?=GetMessage("logictim.balls_INFO_OPTIONS_SEE")?></a></li>
                        <li><?=GetMessage("logictim.balls_INFO_RULES_PAY_BONUS")?> <a target="_blank" href="https://logictim.ru/marketplace/nakopitelnaya_referalnaya_sistema_versii_4_0_i_vyshe/instruktsii_po_nastroyke_4/nastroyka_pravil/oplata_zakaza_bonusami/"><?=GetMessage("logictim.balls_INFO_OPTIONS_SEE")?></a></li>
                        <li><?=GetMessage("logictim.balls_INFO_RULES_EXIT_BONUS")?> <a target="_blank" href="https://logictim.ru/marketplace/nakopitelnaya_referalnaya_sistema_versii_4_0_i_vyshe/instruktsii_po_nastroyke_4/nastroyka_pravil/zapros_na_vyvod_bonusov/"><?=GetMessage("logictim.balls_INFO_OPTIONS_SEE")?></a></li>
                    </ul>
                </div>
            </div>
            
            <div>
            	<h3><?=GetMessage("logictim.balls_INFO_OPTIONS_MODULE")?></h3>
                <div class="info_list">
                	<ul>
                    	<li><?=GetMessage("logictim.balls_BONUS_INFO_MAIN_OPTIONS")?> <a target="_blank" href="https://logictim.ru/marketplace/nakopitelnaya_referalnaya_sistema_versii_4_0_i_vyshe/instruktsii_po_nastroyke_4/nastroyki_modulya/nastroyki_ispolzuemyy_schet_i_sposob_oplaty_bonusami/"><?=GetMessage("logictim.balls_INFO_OPTIONS_SEE")?></a></li>
                        <li><?=GetMessage("logictim.balls_BONUS_INFO_WHEN_ORDER")?> <a target="_blank" href="https://logictim.ru/marketplace/nakopitelnaya_referalnaya_sistema_versii_4_0_i_vyshe/instruktsii_po_nastroyke_4/nastroyki_modulya/kogda_nachislyat_bonusy_za_zakaz/"><?=GetMessage("logictim.balls_INFO_OPTIONS_SEE")?></a></li>
                        <li><?=GetMessage("logictim.balls_BONUS_INFO_REFERAL_OPTIONS")?> <a target="_blank" href="https://logictim.ru/marketplace/nakopitelnaya_referalnaya_sistema_versii_4_0_i_vyshe/instruktsii_po_nastroyke_4/nastroyki_modulya/nastroyki_referalnoy_sistemy/"><?=GetMessage("logictim.balls_INFO_OPTIONS_SEE")?></a></li>
                        <li><?=GetMessage("logictim.balls_BONUS_INFO_INEGRATE_OPTIONS")?> <a target="_blank" href="https://logictim.ru/marketplace/nakopitelnaya_sistema_bonusov_ballov/instruktsii_po_nastroyke/avtomaticheskaya_integratsiya_v_koriznu_i_oformlenie_zakaza/"><?=GetMessage("logictim.balls_INFO_OPTIONS_SEE")?></a></li>
                    </ul>
                </div>
            </div>
            
            
            <div>
            	<h3><?=GetMessage("logictim.balls_INFO_INSERT_TEMPLATE")?></h3>
                <div class="info_list">
                	<ul>
                    	<li><?=GetMessage("logictim.balls_INFO_BONUS_PERSONAL")?> <a target="_blank" href="https://logictim.ru/marketplace/nakopitelnaya_referalnaya_sistema_versii_4_0_i_vyshe/vnedrenie_v_shablony_4/lichnyy_kabinet/"><?=GetMessage("logictim.balls_INFO_OPTIONS_SEE")?></a></li>
                    	<li><?=GetMessage("logictim.balls_INFO_AUTO_INSERT")?> <a target="_blank" href="https://logictim.ru/marketplace/nakopitelnaya_sistema_bonusov_ballov/instruktsii_po_nastroyke/avtomaticheskaya_integratsiya_v_koriznu_i_oformlenie_zakaza/"><?=GetMessage("logictim.balls_INFO_OPTIONS_SEE")?></a></li>
                        <li><?=GetMessage("logictim.balls_INFO_INSERT_ASPRO_NEXT")?> <a target="_blank" href="https://logictim.ru/marketplace/nakopitelnaya_sistema_bonusov_ballov/vnedrenie_v_shablony/integratsiya_v_shablon_aspro_next/"><?=GetMessage("logictim.balls_INFO_OPTIONS_SEE")?></a></li>
                        <li><?=GetMessage("logictim.balls_INFO_INSERT_CATALOG")?> <a target="_blank" href="https://logictim.ru/marketplace/nakopitelnaya_sistema_bonusov_ballov/vnedrenie_v_shablony/vyvod_bonusov_v_shablone_kataloga_i_korzine/"><?=GetMessage("logictim.balls_INFO_OPTIONS_SEE")?></a></li>
                        <li><?=GetMessage("logictim.balls_INFO_INSERT_SALE_ORDER_AJAX")?> <a target="_blank" href="https://logictim.ru/marketplace/nakopitelnaya_sistema_bonusov_ballov/vnedrenie_v_shablony/integratsiya_v_kastomnyy_shablon_oformleniya_zakaza_bitriks/"><?=GetMessage("logictim.balls_INFO_OPTIONS_SEE")?></a></li>
                        <li><?=GetMessage("logictim.balls_INFO_INSERT_SALE_ORDER_AJAX_OLD")?> <a target="_blank" href="https://logictim.ru/marketplace/nakopitelnaya_sistema_bonusov_ballov/vnedrenie_v_shablony/integratsiya_v_staryy_shablon_oformleniya_zakaza/"><?=GetMessage("logictim.balls_INFO_OPTIONS_SEE")?></a></li>
                    </ul>
                </div>
            </div>
        		
                
                
        <?
		$tabControl->Buttons(array(
							"back_url" => $APPLICATION->GetCurPage().'?lang='.LANGUAGE_ID
						));
	?>
    <?$tabControl->End();?>
    
<? CJSCore::Init(array('jquery2'));?>
<script type="text/javascript">
	
</script>



<? require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin.php");?>