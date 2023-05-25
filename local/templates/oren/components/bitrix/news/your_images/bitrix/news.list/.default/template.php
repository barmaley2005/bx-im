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
$this->setFrameMode(true);
?>
<div class="yourImages-container" data-ajax-items>
<?foreach($arResult["ITEMS"] as $arItem):?>
	<?
	$this->AddEditAction($arItem['ID'], $arItem['EDIT_LINK'], CIBlock::GetArrayByID($arItem["IBLOCK_ID"], "ELEMENT_EDIT"));
	$this->AddDeleteAction($arItem['ID'], $arItem['DELETE_LINK'], CIBlock::GetArrayByID($arItem["IBLOCK_ID"], "ELEMENT_DELETE"), array("CONFIRM" => GetMessage('CT_BNL_ELEMENT_DELETE_CONFIRM')));
	?>
	<div class="collections-item" id="<?=$this->GetEditAreaId($arItem['ID']);?>">
		<div class="collections-head">
			<a href="<?=$arItem['DETAIL_PAGE_URL']?>" class="collections-head__img">
				<img src="<?=$arItem['PREVIEW_PICTURE']['SRC']?>" alt="" loading="lazy">
			</a>

			<?
			foreach ($arItem['PRODUCTS'] as $arProduct)
			{
				?>
				<a href="<?=$arItem['DETAIL_PAGE_URL']?>" class="collections-head__label" style="left: <?=$arProduct['X']?>%; top: <?=$arProduct['Y']?>%;">
					<div class="collections-head__box">
						<p class="collections-head__text">
							<?=$arProduct['NAME']?> <span class="collections-head__price"><?=$arProduct['DISPLAY_PRICE']['PRINT_DISCOUNT_VALUE']?></span>
						</p>
					</div>
				</a>
				<?
			}
			?>

			<a href="<?=$arItem['DETAIL_PAGE_URL']?>" class="collections-head__more">
				<svg width="12" height="12" viewBox="0 0 12 12" fill="none" xmlns="http://www.w3.org/2000/svg">
					<path d="M9.97243 10.9632H2.03125L2.36213 3.68384H9.64154L9.97243 10.9632Z" stroke="white"
						  stroke-miterlimit="10" stroke-linecap="round" stroke-linejoin="round" />
					<path
						d="M4.34375 5.0077V2.69152C4.34375 2.25274 4.51805 1.83194 4.82832 1.52168C5.13858 1.21141 5.55938 1.03711 5.99816 1.03711C6.21542 1.03711 6.43056 1.0799 6.63128 1.16304C6.832 1.24619 7.01438 1.36805 7.16801 1.52168C7.32163 1.6753 7.4435 1.85768 7.52664 2.05841C7.60978 2.25913 7.65257 2.47426 7.65257 2.69152V5.0077"
						stroke="white" stroke-miterlimit="10" stroke-linecap="round" stroke-linejoin="round" />
				</svg>
				<p class="collections-head__look">
					<?=GetMessage('YOUR_IMAGES_VIEW_PRODUCTS')?>
				</p>
			</a>
		</div>
		<div class="collections-content">
			<a href="<?=$arItem['DETAIL_PAGE_URL']?>" class="collections-name"><?=$arItem['NAME']?></a>
			<div class="collections-description">
				<p><?=$arItem['PREVIEW_TEXT']?></p>
			</div>
			<a href="<?=$arItem['DETAIL_PAGE_URL']?>" class="collections-more"><?=GetMessage('YOUR_IMAGES_READ_MORE')?></a>
		</div>
	</div>
<?endforeach;?>
</div>