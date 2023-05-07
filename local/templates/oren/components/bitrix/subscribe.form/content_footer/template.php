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
?>
<section class="section sale" id="subscribe-form">
	<?
	$frame = $this->createFrame("subscribe-form", false)->begin();
	?>
	<div class="container">
		<div class="sale-container">
			<div class="sale-form">
				<form action="<?=$arResult["FORM_ACTION"]?>">
					<div class="sale-form__content">
						<div class="sale-form__icon">
							<svg width="35" height="30" viewBox="0 0 35 30" fill="none" xmlns="http://www.w3.org/2000/svg">
								<g clip-path="url(#clip0_507_18833)">
									<path
										d="M22.5684 27.0781C26.8324 26.3924 33.1265 18.2348 33.1265 13.9753C33.1265 10.6906 30.8254 8.34424 27.7121 8.34424C25.0727 8.34424 22.9066 10.4738 22.2976 12.1344C21.6887 10.4738 19.5225 8.34424 16.8832 8.34424C13.7699 8.34424 11.4688 10.6906 11.4688 13.9753C11.4688 18.2348 17.7632 26.3924 22.0269 27.0781C22.1162 27.0974 22.2067 27.1095 22.2976 27.1143C22.3861 27.0902 22.477 27.078 22.5684 27.0781V27.0781Z"
										stroke="#D2C7BC" stroke-miterlimit="10" stroke-linecap="round" stroke-linejoin="round" />
								</g>
								<path
									d="M15.8084 26.4282C21.02 25.5901 28.7128 15.6197 28.7128 10.4136C28.7128 6.39902 25.9003 3.53125 22.0951 3.53125C18.8692 3.53125 16.2217 6.13407 15.4775 8.16372C14.7332 6.13407 12.0857 3.53125 8.85983 3.53125C5.05469 3.53125 2.24219 6.39902 2.24219 10.4136C2.24219 15.6197 9.93542 25.5901 15.1466 26.4282C15.2557 26.4517 15.3663 26.4665 15.4775 26.4724C15.5856 26.4429 15.6968 26.4281 15.8084 26.4282V26.4282Z"
									stroke="#877569" stroke-miterlimit="10" stroke-linecap="round" stroke-linejoin="round" />
								<defs>
									<clipPath id="clip0_507_18833">
										<rect width="24.5455" height="24.5455" fill="white" transform="translate(10.0234 5.45361)" />
									</clipPath>
								</defs>
							</svg>
						</div>
						<h2 class="title sale-form__title">Узнайте первым о наших новинках, акциях и распродажах</h2>
						<h4 class="sale-form__subtitle">Рассылка новинок, скидок и советов стилиста</h4>
					</div>

					<div class="sale-form__footer">
						<div class="placement-inputs__col">
							<label class="placement-inputs__label" for=""><?=GetMessage('subscr_form_email_title')?></label>
							<input type="email" class="input" placeholder="<?=GetMessage('subscr_form_email_title')?>" name="sf_EMAIL" value="<?=$arResult["EMAIL"]?>">
							<span class="placement-inputs__info"><?=GetMessage('subscr_invalid_email')?></span>
						</div>
						<input type="hidden" name="OK" value="Y">
						<button class="sale-form__button submit" type="submit"><?=GetMessage('subscr_form_button')?></button>
					</div>
				</form>
			</div>
			<div class="sale-img">
				<img src="<?=SITE_TEMPLATE_PATH?>/img/sale/img-1.jpg" alt="" loading="lazy">
			</div>
		</div>
	</div>
	<?
	$frame->end();
	?>
</section>
