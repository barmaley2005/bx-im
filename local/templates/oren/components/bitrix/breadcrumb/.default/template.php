<?php if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

/**
 * @global CMain $APPLICATION
 */

global $APPLICATION;

//delayed function must return a string
if(empty($arResult))
    return "";

$strReturn = '<section class="'.$APPLICATION->GetProperty('BREADCRUMB_CLASS','breadcrumbs-section').'">
      <div class="container">
        <div class="breadcrumbs">
          <div class="breadcrumbs-container">';

$itemSize = count($arResult);
for($index = 0; $index < $itemSize; $index++)
{
    $title = htmlspecialcharsex($arResult[$index]["TITLE"]);

    if ($index>0)
        $strReturn.="\n";

    if($arResult[$index]["LINK"] <> "" && $index != $itemSize-1)
    {
        $strReturn .= '<span><a href="'.$arResult[$index]["LINK"].'" title="'.$title.'" itemprop="item">'.$title.'</a></span>';
    }
    else
    {
        $strReturn .= '<span>'.$title.'</span>';
    }
}


$strReturn .= '</div>
        </div>
      </div>
    </section>';

return $strReturn;