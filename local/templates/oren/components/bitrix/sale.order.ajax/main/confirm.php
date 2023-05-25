<? if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

use Bitrix\Main\Localization\Loc;

/**
 * @var array $arParams
 * @var array $arResult
 * @var $APPLICATION CMain
 */

if ($arParams["SET_TITLE"] == "Y")
{
	$APPLICATION->SetTitle(Loc::getMessage("SOA_ORDER_COMPLETE"));
}
?>
	<section class="section elected">
		<div class="container">
			<h1 class="title text-left"><?=GetMessage('ORDER_SUCCESS_TITLE')?></h1>

			<div class="elected-container _empty">
				<div class="elected-content">
					<div class="elected-content__icon">
						<svg width="40" height="40" viewBox="0 0 40 40" fill="none" xmlns="http://www.w3.org/2000/svg">
							<path
								d="M20.432 35.2353C27.3808 34.1178 37.6379 20.824 37.6379 13.8825C37.6379 8.52974 33.8879 4.70605 28.8143 4.70605C24.5132 4.70605 20.9832 8.17649 19.9908 10.8827C18.9985 8.17649 15.4685 4.70605 11.1673 4.70605C6.09375 4.70605 2.34375 8.52974 2.34375 13.8825C2.34375 20.824 12.6014 34.1178 19.5496 35.2353C19.6951 35.2667 19.8426 35.2864 19.9908 35.2943C20.135 35.255 20.2832 35.2352 20.432 35.2353V35.2353Z"
								stroke="#877569" stroke-miterlimit="10" stroke-linecap="round" stroke-linejoin="round" />
						</svg>
					</div>

					<p class="elected-content__text"><?=GetMessage('ORDER_SUCCESS_TEXT')?></p>

					<?
					if ($arResult["ORDER"]["IS_ALLOW_PAY"] === 'Y')
					{
						if (!empty($arResult["PAYMENT"]))
						{
							foreach ($arResult["PAYMENT"] as $payment)
							{
								if ($payment["PAID"] != 'Y')
								{
									if (!empty($arResult['PAY_SYSTEM_LIST'])
										&& array_key_exists($payment["PAY_SYSTEM_ID"], $arResult['PAY_SYSTEM_LIST'])
									)
									{
										$arPaySystem = $arResult['PAY_SYSTEM_LIST_BY_PAYMENT_ID'][$payment["ID"]];

										if (empty($arPaySystem["ERROR"]))
										{
											?>
											<table class="sale_order_full_table">
												<tr>
													<td class="ps_logo">
														<div class="pay_name"><?=Loc::getMessage("SOA_PAY") ?></div>
														<?=CFile::ShowImage($arPaySystem["LOGOTIP"], 100, 100, "border=0\" style=\"width:100px\"", "", false) ?>
														<div class="paysystem_name"><?=$arPaySystem["NAME"] ?></div>
														<br/>
													</td>
												</tr>
												<tr>
													<td>
														<? if ($arPaySystem["ACTION_FILE"] <> '' && $arPaySystem["NEW_WINDOW"] == "Y" && $arPaySystem["IS_CASH"] != "Y"): ?>
															<?
															$orderAccountNumber = urlencode(urlencode($arResult["ORDER"]["ACCOUNT_NUMBER"]));
															$paymentAccountNumber = $payment["ACCOUNT_NUMBER"];
															?>
															<script>
																window.open('<?=$arParams["PATH_TO_PAYMENT"]?>?ORDER_ID=<?=$orderAccountNumber?>&PAYMENT_ID=<?=$paymentAccountNumber?>');
															</script>
														<?=Loc::getMessage("SOA_PAY_LINK", array("#LINK#" => $arParams["PATH_TO_PAYMENT"]."?ORDER_ID=".$orderAccountNumber."&PAYMENT_ID=".$paymentAccountNumber))?>
														<? if (CSalePdf::isPdfAvailable() && $arPaySystem['IS_AFFORD_PDF']): ?>
														<br/>
															<?=Loc::getMessage("SOA_PAY_PDF", array("#LINK#" => $arParams["PATH_TO_PAYMENT"]."?ORDER_ID=".$orderAccountNumber."&pdf=1&DOWNLOAD=Y"))?>
														<? endif ?>
														<? else: ?>
															<?=$arPaySystem["BUFFERED_OUTPUT"]?>
														<? endif ?>
													</td>
												</tr>
											</table>

											<?
										}
										else
										{
											?>
											<span style="color:red;"><?=Loc::getMessage("SOA_ORDER_PS_ERROR")?></span>
											<?
										}
									}
									else
									{
										?>
										<span style="color:red;"><?=Loc::getMessage("SOA_ORDER_PS_ERROR")?></span>
										<?
									}
								}
							}
						}
					}
					else
					{
						?>
						<br /><strong><?=$arParams['MESS_PAY_SYSTEM_PAYABLE_ERROR']?></strong>
						<?
					}
					?>


					<div class="elected-content__button">
						<div class="button-box">
							<a href="<?=SITE_DIR?>personal/" class="button"><?=GetMessage('ORDER_PERSONAL_SECTION_TITLE')?></a>
							<svg class="button-bg" width="238" height="68" viewBox="0 0 238 68" fill="none"
								 xmlns="http://www.w3.org/2000/svg">
								<path
									d="M63.8598 11.0954C63.8598 11.0954 87.0187 6.81025 136.7 6.81025C166.972 6.81025 237 14.3733 237 37.169C237 61.644 171.494 67 117.487 67C65.1837 67 0.999788 62.4177 1 37.169C1.00032 -0.818731 136.7 1.0142 136.7 1.0142"
									stroke-linecap="round" class="button-bg__elem" />
							</svg>
						</div>
					</div>
				</div>
			</div>

		</div>
	</section>


<?/*
        <section class="section placement">
            <div class="container">

<? if (!empty($arResult["ORDER"])): ?>

	<table class="sale_order_full_table">
		<tr>
			<td>
				<?=Loc::getMessage("SOA_ORDER_SUC", array(
					"#ORDER_DATE#" => $arResult["ORDER"]["DATE_INSERT"]->toUserTime()->format('d.m.Y H:i'),
					"#ORDER_ID#" => $arResult["ORDER"]["ACCOUNT_NUMBER"]
				))?>
				<? if (!empty($arResult['ORDER']["PAYMENT_ID"])): ?>
					<?=Loc::getMessage("SOA_PAYMENT_SUC", array(
						"#PAYMENT_ID#" => $arResult['PAYMENT'][$arResult['ORDER']["PAYMENT_ID"]]['ACCOUNT_NUMBER']
					))?>
				<? endif ?>
				<? if ($arParams['NO_PERSONAL'] !== 'Y'): ?>
					<br /><br />
					<?=Loc::getMessage('SOA_ORDER_SUC1', ['#LINK#' => $arParams['PATH_TO_PERSONAL']])?>
				<? endif; ?>
			</td>
		</tr>
	</table>

	<?
	if ($arResult["ORDER"]["IS_ALLOW_PAY"] === 'Y')
	{
		if (!empty($arResult["PAYMENT"]))
		{
			foreach ($arResult["PAYMENT"] as $payment)
			{
				if ($payment["PAID"] != 'Y')
				{
					if (!empty($arResult['PAY_SYSTEM_LIST'])
						&& array_key_exists($payment["PAY_SYSTEM_ID"], $arResult['PAY_SYSTEM_LIST'])
					)
					{
						$arPaySystem = $arResult['PAY_SYSTEM_LIST_BY_PAYMENT_ID'][$payment["ID"]];

						if (empty($arPaySystem["ERROR"]))
						{
							?>
							<br /><br />

							<table class="sale_order_full_table">
								<tr>
									<td class="ps_logo">
										<div class="pay_name"><?=Loc::getMessage("SOA_PAY") ?></div>
										<?=CFile::ShowImage($arPaySystem["LOGOTIP"], 100, 100, "border=0\" style=\"width:100px\"", "", false) ?>
										<div class="paysystem_name"><?=$arPaySystem["NAME"] ?></div>
										<br/>
									</td>
								</tr>
								<tr>
									<td>
										<? if ($arPaySystem["ACTION_FILE"] <> '' && $arPaySystem["NEW_WINDOW"] == "Y" && $arPaySystem["IS_CASH"] != "Y"): ?>
											<?
											$orderAccountNumber = urlencode(urlencode($arResult["ORDER"]["ACCOUNT_NUMBER"]));
											$paymentAccountNumber = $payment["ACCOUNT_NUMBER"];
											?>
											<script>
												window.open('<?=$arParams["PATH_TO_PAYMENT"]?>?ORDER_ID=<?=$orderAccountNumber?>&PAYMENT_ID=<?=$paymentAccountNumber?>');
											</script>
										<?=Loc::getMessage("SOA_PAY_LINK", array("#LINK#" => $arParams["PATH_TO_PAYMENT"]."?ORDER_ID=".$orderAccountNumber."&PAYMENT_ID=".$paymentAccountNumber))?>
										<? if (CSalePdf::isPdfAvailable() && $arPaySystem['IS_AFFORD_PDF']): ?>
										<br/>
											<?=Loc::getMessage("SOA_PAY_PDF", array("#LINK#" => $arParams["PATH_TO_PAYMENT"]."?ORDER_ID=".$orderAccountNumber."&pdf=1&DOWNLOAD=Y"))?>
										<? endif ?>
										<? else: ?>
											<?=$arPaySystem["BUFFERED_OUTPUT"]?>
										<? endif ?>
									</td>
								</tr>
							</table>

							<?
						}
						else
						{
							?>
							<span style="color:red;"><?=Loc::getMessage("SOA_ORDER_PS_ERROR")?></span>
							<?
						}
					}
					else
					{
						?>
						<span style="color:red;"><?=Loc::getMessage("SOA_ORDER_PS_ERROR")?></span>
						<?
					}
				}
			}
		}
	}
	else
	{
		?>
		<br /><strong><?=$arParams['MESS_PAY_SYSTEM_PAYABLE_ERROR']?></strong>
		<?
	}
	?>

<? else: ?>

	<b><?=Loc::getMessage("SOA_ERROR_ORDER")?></b>
	<br /><br />

	<table class="sale_order_full_table">
		<tr>
			<td>
				<?=Loc::getMessage("SOA_ERROR_ORDER_LOST", ["#ORDER_ID#" => htmlspecialcharsbx($arResult["ACCOUNT_NUMBER"])])?>
				<?=Loc::getMessage("SOA_ERROR_ORDER_LOST1")?>
			</td>
		</tr>
	</table>

<? endif ?>
			</div>
		</section>
*/?>