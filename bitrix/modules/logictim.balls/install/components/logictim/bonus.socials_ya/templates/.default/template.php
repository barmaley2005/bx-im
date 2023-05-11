<? if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

use Bitrix\Main\Loader;
Loader::includeModule("logictim.balls");

//$frame = $this->createFrame()->begin('');
	
if(!empty($arResult["SOCIALS"])):
	$strSocials = implode(',', $arResult["SOCIALS"]);
?>
	<script type="text/javascript" src="//yastatic.net/es5-shims/0.0.2/es5-shims.min.js" charset="utf-8"></script>
	<script type="text/javascript" src="//yastatic.net/share2/share.js" charset="utf-8"></script>
	<div class="ya-share2" id="my-share" data-services="<?=$strSocials?>"></div>
<?
	
	if($USER->IsAuthorized())
	{
		global $APPLICATION, $USER;
		$page = $APPLICATION->GetCurPageParam("ref=".$USER->GetID());
	
		if(isset($_SERVER['HTTPS']))
			$protocol = 'https://';
		else
			$protocol = 'http://';
			
		?>
			<script>
			var share = Ya.share2('my-share');
			share.updateContent({
				url: '<?=$protocol.$_SERVER["SERVER_NAME"].$page?>'
			});
			</script>
        <?
	
	}

 endif;
//$frame->end();
?>






