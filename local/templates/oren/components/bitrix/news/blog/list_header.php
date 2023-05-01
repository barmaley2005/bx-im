<?php if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();?>
<?
/**
 * @var array $arResult
 * @var array $arParams
 */

\Bitrix\Main\Loader::includeModule('iblock');
\Bitrix\Main\Loader::includeModule('local.lib');

$iblockId = \Local\Lib\Utils::getIblockIdByCode($arParams['IBLOCK_ID']);

$arFilter = array(
    'IBLOCK_ID' => $iblockId,
    "ACTIVE" => "Y",
    "GLOBAL_ACTIVE" => "Y",
);

$arNav = array();

$arNav[] = array(
    'PAGE' => $arResult['FOLDER'],
    'TEXT' => GetMessage('BLOG_ALL_SECTIONS'),
    'SELECTED' => false,
);

$selectedIndex = 0;
$index = 0;

$iterator = \CIBlockSection::GetList(array('LEFT_MARGIN'=>'ASC'),$arFilter);
$iterator->SetUrlTemplates("", $arResult['FOLDER'].$arResult['URL_TEMPLATES']['section']);

while ($ar = $iterator->GetNext())
{
    $index++;

    if ($ar['ID'] == $arResult['VARIABLES']['SECTION_ID'] || $ar['CODE'] == $arResult['VARIABLES']['SECTION_CODE'])
    {
        $selectedIndex = $index;
    }

    $arNav[$index] = array(
        'PAGE' => $ar['SECTION_PAGE_URL'],
        'TEXT' => $ar['NAME'],
        'SELECTED' => false,
    );
}

$arNav[$selectedIndex]['SELECTED'] = true;

?>
<section class="section weblog">
    <div class="container">
        <div class="weblog-title">
            <h1 class="title text-left"><?=GetMessage('BLOG_TITLE')?></h1>

            <div class="select">
                <div class="select-head">
                    <input type="text" class="select-head__input" value="<?=$arNav[$selectedIndex]['TEXT']?>">
                    <div class="select-arrow">
                        <svg width="10" height="7" viewBox="0 0 10 7" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M1 2L4.29289 5.29289C4.68342 5.68342 5.31658 5.68342 5.70711 5.29289L9 2" stroke="#877569"
                                  stroke-linecap="round" stroke-linejoin="round" />
                        </svg>
                    </div>
                </div>
                <div class="select-container">
                    <ul class="select-list">
                        <?
                        foreach ($arNav as $item)
                        {
                            ?>
                            <li<?if ($item['SELECTED']):?> class="_active"<?endif?> data-url="<?=$item['PAGE']?>">
                                <?=$item['TEXT']?>
                            </li>
                            <?
                        }
                        ?>
                    </ul>
                </div>
            </div>

        </div>

