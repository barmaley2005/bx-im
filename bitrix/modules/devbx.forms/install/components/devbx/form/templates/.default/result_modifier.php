<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();
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

foreach ($arResult['FIELDS'] as &$arField)
{
    $arField['HTML'] = preg_replace_callback('#<(input.*?)>#misu'.BX_UTF_PCRE_MODIFIER, function($matches) {

        $html = $matches[1];

        $type = 'text';
        if (preg_match('#type="(.*?)"#misu'.BX_UTF_PCRE_MODIFIER, $html, $matches))
        {
            $type = strtolower(trim($matches[1]));
        }

        switch ($type)
        {
            case 'checkbox':
            case 'radio':
                $class = 'form-check-input';
                break;
            default:
                $class = 'form-control';
        }

        $count = 0;

        $html = preg_replace_callback('#class="(.*?)"#misu'.BX_UTF_PCRE_MODIFIER, function($matches) use ($class) {
            return 'class="'.$matches[1].' '.$class.'"';
        }, $html, -1, $count);

        if ($count == 0)
        {
            $html.=' class="'.$class.'"';
        }

        return '<'.$html.'>';

    }, $arField['HTML']);

    $arField['HTML'] = preg_replace_callback('#<(textarea.*?)>#misu'.BX_UTF_PCRE_MODIFIER, function($matches) {

        $html = $matches[1];

        $count = 0;

        $html = preg_replace_callback('#class="(.*?)"#misu'.BX_UTF_PCRE_MODIFIER, function($matches) {
            return 'class="'.$matches[1].' form-control"';
        }, $html, -1, $count);

        if ($count == 0)
        {
            $html.=' class="form-control"';
        }

        return '<'.$html.'>';

    }, $arField['HTML']);

}

unset($arField);