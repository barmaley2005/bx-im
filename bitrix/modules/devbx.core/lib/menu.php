<?php

namespace DevBx\Core;

class Menu {

    protected static function getTreeLevel($arMenuLinks, &$position, $level)
    {
        $arResult = array();

        $index=0;

        for (;$position<count($arMenuLinks);$position++)
        {
            $arItem = $arMenuLinks[$position];
            if ($arItem['DEPTH_LEVEL']<$level)
            {
                $position--;
                return $arResult;
            }

            if ($arItem['DEPTH_LEVEL']>$level)
            {
                $arResult[$index-1]['ITEMS'] = static::getTreeLevel($arMenuLinks, $position, $arItem['DEPTH_LEVEL']);
            } else {
                $arResult[$index] = $arItem;
                $index++;
            }
        }

        return $arResult;
    }

    public static function getTreeMenu($arMenuLinks)
    {
        $arResult = array();

        $arMenuLinks = array_values($arMenuLinks);

        $prevDepthLevel = false;

        $index = 0;

        for ($i=0;$i<count($arMenuLinks);$i++)
        {
            $arItem = $arMenuLinks[$i];
            $arItem['ITEMS'] = array();

            if ($prevDepthLevel && $arItem['DEPTH_LEVEL']>$prevDepthLevel)
            {
                $arResult[$index-1]['ITEMS'] = static::getTreeLevel($arMenuLinks, $i, $arItem['DEPTH_LEVEL']);
            } else
            {
                $arResult[$index] = $arItem;
                $index++;
            }

            if (!$prevDepthLevel)
                $prevDepthLevel = $arItem['DEPTH_LEVEL'];
        }

        return $arResult;
    }

    public static function getActivePage($arPages, $keyUrl = 'URL', $curPageParam = false)
    {
        global $APPLICATION;

        if  ($curPageParam === false)
            $curPageParam = $APPLICATION->GetCurPageParam("");

        $arCurPage = parse_url($curPageParam);
        parse_str($arCurPage['query'], $arCurQuery);

        $arPageCand = array();

        foreach ($arPages as $key=>$page)
        {
            if ($keyUrl !== false)
            {
                $arUrl = parse_url($page[$keyUrl]);
            } else
            {
                $arUrl = parse_url($page);
            }

            if ($arCurPage['path'] == $arUrl['path'])
            {
                parse_str($arUrl['query'], $arQuery);
                $arPageCand[$key] = $arQuery;
            }
        }

        uasort($arPageCand, function($a,$b) {

            if (count($a) == count($b))
                return 0;

            return count($a) > count($b) ? -1 : 1;

        });

        foreach ($arPageCand as $pageKey=>$arQuery)
        {

            $cnt = 0;

            foreach ($arQuery as $key=>$val)
            {
                if (!isset($arCurQuery[$key]))
                    continue;

                if (is_array($val))
                {
                    if (is_array($arCurQuery[$key]) && count(array_diff_assoc($val, $arCurQuery[$key])) == 0)
                        $cnt++;
                } else
                {
                    if ($val == $arCurQuery[$key])
                        $cnt++;
                }
            }

            if ($cnt == count($arQuery)) {
                return $pageKey;
            }
        }

        return false;
    }
}

