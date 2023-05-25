<? if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

use Bitrix\Main\Localization\Loc;
?>

<div class="bx-soa-empty-cart-container container" style="min-height: 400px;">
	<div class="bx-soa-empty-cart-image">
		<img src="" alt="">
	</div>
	<div class="bx-soa-empty-cart-text"><h3><?=Loc::getMessage("EMPTY_BASKET_TITLE")?></h3></div>
	<?
	if (!empty($arParams['EMPTY_BASKET_HINT_PATH']))
	{
		?>
		<div class="bx-soa-empty-cart-desc">
			<?=Loc::getMessage(
				'EMPTY_BASKET_HINT',
				[
					'#A1#' => '<a style="color:#000;" href="'.$arParams['EMPTY_BASKET_HINT_PATH'].'">',
					'#A2#' => '</a>',
				]
			)?>
		</div>
		<?
	}
	?>
</div>