<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

if (empty($arResult['ITEMS']))
	return;
?>
<div class="search-box">
	<?
	foreach ($arResult['ITEMS'] as $arItem)
	{
		?>
		<div class="search-container__row">
			<div class="search-container__col">
				<a href="<?=$arItem['URL']?>" class="search-container__img">
					<img src="<?=$arItem['PICTURE']['SRC']?>" alt="">
				</a>
			</div>
			<div class="search-container__col">
				<a href="<?=$arItem['URL']?>" class="search-container__name"><?=$arItem['NAME']?></a>
				<div class="search-container__price">
					<span><?=$arItem['PRICE']?></span>
				</div>
			</div>
		</div>
		<?
	}
	?>
</div>
