<?php if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();?>
<?$APPLICATION->IncludeComponent("bitrix:subscribe.form", "content_footer", Array(
	"AJAX_MODE" => "N",
	"AJAX_OPTION_ADDITIONAL" => "",
	"AJAX_OPTION_HISTORY" => "N",
	"AJAX_OPTION_JUMP" => "N",
	"AJAX_OPTION_STYLE" => "Y",
	"ALLOW_ANONYMOUS" => "Y",
	"CACHE_NOTES" => "",
	"CACHE_TIME" => "3600000",	// Время кеширования (сек.)
	"CACHE_TYPE" => "A",	// Тип кеширования
	"COMPONENT_TEMPLATE" => "main",
	"LK" => "Y",
	"PAGE" => SITE_DIR."personal/subscribe/",	// Страница редактирования подписки (доступен макрос #SITE_DIR#)
	"SET_TITLE" => "N",
	"SHOW_AUTH_LINKS" => "N",
	"SHOW_HIDDEN" => "N",	// Показать скрытые рубрики подписки
	"URL_SUBSCRIBE" => SITE_DIR."personal/subscribe/",
	"USE_PERSONALIZATION" => "Y",	// Определять подписку текущего пользователя
),
	false
);?>
