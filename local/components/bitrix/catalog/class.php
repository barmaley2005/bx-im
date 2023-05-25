<?php

class CDevBxCatalog extends CBitrixComponent
{
    public static function resolveComponentEngine(CComponentEngine $engine, $pageCandidates, &$arVariables)
    {
        static $arPreset = array('saleleader','new','specialoffer');

        if (isset($pageCandidates['section']) && in_array($pageCandidates['section']['SECTION_CODE'], $arPreset))
        {
            $arVariables = array(
                'PRESET' => $pageCandidates['section']['SECTION_CODE']
            );

            return 'sections';
        }

        return CIBlockFindTools::resolveComponentEngine($engine, $pageCandidates, $arVariables);
    }

}