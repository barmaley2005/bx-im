<? if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
use Bitrix\Main\Loader;
use Bitrix\Main\Application; 
use Bitrix\Main\Web\Uri; 
Loader::includeModule("logictim.balls");
$request = Application::getInstance()->getContext()->getRequest();
if(!empty($arParams["FIELDS"])):


CModule::IncludeModule('logictim.balls');

?>

<div class="social-buttons">
	<? if(in_array("VK", $arParams["FIELDS"]) && $arResult["VK_APP_ID"] != '') {?>
		<div class="button vk" id="vk"></div>
    <? }?>
    <? if(in_array("FB", $arParams["FIELDS"]) && $arResult["FB_APP_ID"] != '') {?>
    	<div class="button facebook" id="facebook"></div>
    <? }?>
    <? if(in_array("OK", $arParams["FIELDS"])) {?>
    	<div class="button odnoklassniki" id="odnoklassniki"></div>
    <? }?>
</div>


<script>
    var buttons = {
        facebook: document.getElementById('facebook'),
        odnoklassniki: document.getElementById('odnoklassniki'),
        vk: document.getElementById('vk')
    };

    var appId = {
        facebook: '<?=$arResult["FB_APP_ID"]?>',
        vk: '<?=$arResult["VK_APP_ID"]?>'
    };

	
	var fb = new socialShareCallback.FacebookShare(appId.facebook, function () {
        buttons.facebook.classList.add('active');
        (function recurse() {
            fb.share(buttons.facebook, location.href, function () {
                AddBonusFromRepost('FB');
                recurse();
            });
        })();
    });

    var vk = new socialShareCallback.VkShare(appId.vk, function () {
        buttons.vk.classList.add('active');
        (function recurse() {
            vk.share(buttons.vk, location.href, function () {
                AddBonusFromRepost('VK');
                recurse();
            });
        })();
    });

    var ok = new socialShareCallback.OdnoklassnikiShare(function () {
        buttons.odnoklassniki.classList.add('active');
        (function recurse() {
            ok.share(buttons.odnoklassniki, location.href + '#' + new Date().getTime(), function () {
                AddBonusFromRepost('OK');
                recurse();
            });
        })();
    });
	
	
	function AddBonusFromRepost(social_network)
	{
		BX.ajax({
			url: '<?=$componentPath?>/ajax.php',
			data: {
					'social_network': social_network,
					'page': '<?=$request->getRequestUri();?>'
				},
			method: 'POST',
			dataType: 'html',
			timeout: 30,
			async: true,
			processData: true,
			scriptsRunFirst: true,
			emulateOnload: true,
			start: true,
			cache: false,
			onsuccess: function(html){},
			onfailure: function(){}
		}); 
	}
</script>



<? endif;?>