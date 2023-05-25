<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
/** @var array $arParams */
/** @var array $arResult */
/** @global CMain $APPLICATION */
/** @global CUser $USER */
/** @global CDatabase $DB */
/** @var CBitrixComponentTemplate $this */
/** @var string $templateName */
/** @var string $templateFile */
/** @var string $templateFolder */
/** @var string $componentPath */
/** @var CBitrixComponent $component */
$this->setFrameMode(true);?>
<?
$INPUT_ID = trim($arParams["~INPUT_ID"]);
if($INPUT_ID == '')
	$INPUT_ID = "title-search-input";
$INPUT_ID = CUtil::JSEscape($INPUT_ID);

$CONTAINER_ID = trim($arParams["~CONTAINER_ID"]);
if($CONTAINER_ID == '')
	$CONTAINER_ID = "title-search";
$CONTAINER_ID = CUtil::JSEscape($CONTAINER_ID);

if($arParams["SHOW_INPUT"] !== "N"):?>
	<div class="header-desctop__search" id="<?echo $CONTAINER_ID?>">
		<input id="<?echo $INPUT_ID?>" class="header-desctop__input" type="text" placeholder="Поиск" name="q" value="">
		<div class="header-desctop__btn">
			<svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
				<path d="M11.7656 12.9412L16.4715 17.647" stroke-miterlimit="10" stroke-linecap="round"
					  stroke-linejoin="round" />
				<path
					d="M8.23392 14.1172C11.4826 14.1172 14.1163 11.4836 14.1163 8.23489C14.1163 4.98616 11.4826 2.35254 8.23392 2.35254C4.98518 2.35254 2.35156 4.98616 2.35156 8.23489C2.35156 11.4836 4.98518 14.1172 8.23392 14.1172Z"
					stroke-miterlimit="10" stroke-linecap="round" stroke-linejoin="round" />
			</svg>
		</div>
	</div>

<?endif?>
<script>
	BX.ready(function(){
		new DevBxTitleSearch({
			'AJAX_PAGE' : '<?echo CUtil::JSEscape(POST_FORM_ACTION_URI)?>',
			'CONTAINER_ID': '<?echo $CONTAINER_ID?>',
			'INPUT_ID': '<?echo $INPUT_ID?>',
			'MIN_QUERY_LEN': 2
		});
	});
</script>
