<?php

global $arCatalogAvailableSort;

$arCatalogAvailableSort = array(
    'new' => array(
        'NAME' => GetMessage('CATALOG_SORT_NEW'),
        'ELEMENT_SORT_FIELD' => 'ID',
        'ELEMENT_SORT_ORDER' => 'desc',
    ),
    'popular' => array(
        'NAME' => GetMessage('CATALOG_SORT_POPULAR'),
        'ELEMENT_SORT_FIELD' => 'shows',
        'ELEMENT_SORT_ORDER' => 'desc',
    ),
    'price_asc' => array(
        'NAME' => GetMessage('CATALOG_SORT_PRICE'),
        'ELEMENT_SORT_FIELD' => 'PROPERTY_MINIMUM_PRICE',
        'ELEMENT_SORT_ORDER' => 'asc',
        'ICON' => '<svg class="radio-icon" width="8" height="10" viewBox="0 0 8 10" fill="none"
                            xmlns="http://www.w3.org/2000/svg">
                            <path
                              d="M4.35355 0.646447C4.15829 0.451184 3.84171 0.451184 3.64645 0.646447L0.464466 3.82843C0.269204 4.02369 0.269204 4.34027 0.464466 4.53553C0.659728 4.7308 0.976311 4.7308 1.17157 4.53553L4 1.70711L6.82843 4.53553C7.02369 4.7308 7.34027 4.7308 7.53553 4.53553C7.7308 4.34027 7.7308 4.02369 7.53553 3.82843L4.35355 0.646447ZM4.5 10V1H3.5V10H4.5Z"
                              fill="#877569" />
                          </svg>'
    ),
    'price_desc' => array(
        'NAME' => GetMessage('CATALOG_SORT_PRICE'),
        'ELEMENT_SORT_FIELD' => 'PROPERTY_MINIMUM_PRICE',
        'ELEMENT_SORT_ORDER' => 'desc',
        'ICON' => '<svg class="radio-icon" width="8" height="10" viewBox="0 0 8 10" fill="none"
                            xmlns="http://www.w3.org/2000/svg">
                            <path
                              d="M3.64645 9.35355C3.84171 9.54882 4.15829 9.54882 4.35355 9.35355L7.53553 6.17157C7.7308 5.97631 7.7308 5.65973 7.53553 5.46447C7.34027 5.2692 7.02369 5.2692 6.82843 5.46447L4 8.29289L1.17157 5.46447C0.976311 5.2692 0.659728 5.2692 0.464466 5.46447C0.269204 5.65973 0.269204 5.97631 0.464466 6.17157L3.64645 9.35355ZM3.5 0L3.5 9H4.5L4.5 0H3.5Z"
                              fill="#877569" />
                          </svg>'
    ),
);

$sortMode = 'popular';

if ($request->offsetExists('sort'))
{
    if (array_key_exists($request->get('sort'), $arCatalogAvailableSort))
    {
        $sortMode = $request->get('sort');
        $_SESSION['catalog_sort_mode'] = $sortMode;
    }
} else {
    if (array_key_exists('catalog_sort_mode', $_SESSION) && array_key_exists($_SESSION['catalog_sort_mode'], $arCatalogAvailableSort))
    {
        $sortMode = $_SESSION['catalog_sort_mode'];
    }
}
