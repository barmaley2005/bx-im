<?
if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true)
	die();

use Bitrix\Main\Localization\Loc;

?>
<div class="account-block__pages">
	<div id="account-data" class="account-block__page account-page">
		<a href="<?=SITE_DIR?>personal/" class="account-page__back">
			<svg class="account-block__back-icon width=" 8" height="14" viewBox="0 0 8 14" fill="none"
			xmlns="http://www.w3.org/2000/svg">
			<path d="M7 13L1 7L7 1" stroke="#877569" stroke-linecap="round" />
			</svg>
		</a>
		<h2 class="account-page__title">Личные данные</h2>
		<form method="post" name="form1" action="<?=POST_FORM_ACTION_URI?>" enctype="multipart/form-data"
			  role="form" class="account-data" style="grid-auto-rows: max-content; ">
			<?=$arResult["BX_SESSION_CHECK"]?>
			<input type="hidden" name="lang" value="<?=LANG?>" />
			<input type="hidden" name="ID" value="<?=$arResult["ID"]?>" />
			<input type="hidden" name="LOGIN" value="<?=$arResult["arUser"]["LOGIN"]?>" />
			<input type="hidden" name="save" value="y">

			<div class="placement-inputs__col" style="grid-column: 1/-1;">
			<?
			ShowError($arResult["strProfileError"]);

			if ($arResult['DATA_SAVED'] == 'Y')
			{
				ShowNote(Loc::getMessage('PROFILE_DATA_SAVED'));
			}
			?>
			</div>

			<div class="placement-inputs__col">
				<label class="placement-inputs__label" for=""><?=GetMessage('NAME')?></label>
				<input name="NAME" type="text" class="input" placeholder="<?=GetMessage('NAME')?>" nonce="NAME" value="<?=$arResult["arUser"]["NAME"]?>">
			</div>
			<div class="placement-inputs__col">
				<label class="placement-inputs__label" for=""><?=GetMessage('SECOND_NAME')?></label>
				<input name="SECOND_NAME" type="text" class="input" placeholder="<?=GetMessage('SECOND_NAME')?>" value="<?=$arResult["arUser"]["SECOND_NAME"]?>">
			</div>
			<div class="placement-inputs__col">
				<label class="placement-inputs__label" for=""><?=GetMessage('LAST_NAME')?></label>
				<input name="LAST_NAME" type="text" class="input" placeholder="<?=GetMessage('LAST_NAME')?>" value="<?=$arResult["arUser"]["LAST_NAME"]?>">
			</div>
			<div class="placement-inputs__col">
				<label class="placement-inputs__label" for=""><?=GetMessage('BIRTHDAY')?></label>
				<input name="PERSONAL_BIRTHDAY" type="text" class="input" placeholder="<?=GetMessage('BIRTHDAY')?>" value="<?=$arResult["arUser"]["PERSONAL_BIRTHDAY"]?>">
			</div>
			<label class="account-data__label"><?=GetMessage('SEX')?></label>
			<div class="account-data__group">
				<label class="account-data__radio">
					<input id="man" name="PERSONAL_GENDER" type="radio" class="account-data__input-hidden" value="M"
						<?if ($arResult['arUser']['PERSONAL_GENDER'] == 'M'):?> checked<?endif?>>
					<span><?=GetMessage('SEX_M')?></span>
				</label>
				<label class="account-data__radio">
					<input id="woman" name="PERSONAL_GENDER" type="radio" class="account-data__input-hidden" value="F"
						<?if ($arResult['arUser']['PERSONAL_GENDER'] == 'F'):?> checked<?endif?>>
					<span><?=GetMessage('SEX_F')?></span>
				</label>
			</div>
			<div class="placement-inputs__col">
				<label class="placement-inputs__label" for="">E-mail</label>
				<input name="EMAIL" type="email" class="input" placeholder="E-mail" value="<?=$arResult["arUser"]["EMAIL"]?>">
			</div>
			<div class="placement-inputs__col">
				<label class="placement-inputs__label" for=""><?=GetMessage('PHONE')?></label>
				<input name="PERSONAL_PHONE" type="tel" class="input phone" placeholder="<?=GetMessage('PHONE')?>" value="<?=$arResult["arUser"]["PERSONAL_PHONE"]?>">
			</div>
			<button type="submit" class="account-data__submit submit"><?=GetMessage('SAVE')?></button>
		</form>
	</div>
</div>