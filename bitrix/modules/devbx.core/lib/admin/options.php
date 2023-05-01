<?php

namespace DevBx\Core\Admin;

use Bitrix\Main\Application;
use Bitrix\Main\Config\Option;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\SystemException;
use Bitrix\Main\EventResult;
use Bitrix\Main\Web\Json;
use Bitrix\Main\Text\StringHelper;
use DevBx\Core\ValueType\BaseType;
use DevBx\Core\ValueType\Manager;

IncludeModuleLangFile(__FILE__);

class Options
{

    protected $module_id;
    protected $lang_prefix;

    /**
     * @var BaseType[]
     */
    protected $usedValueType;

    public function __construct($moduleId)
    {
        $this->module_id = $moduleId;
        $this->lang_prefix = ToUpper(str_replace('.','_',$moduleId)).'_';
        $this->usedValueType = array();
    }

    protected function getRegisteredValueType()
    {
        $event = new \Bitrix\Main\Event(
            "devbx.core",
            "OnRegisterValueType",
            array()
        );

        $event->send();

        foreach ($event->getResults() as $eventResult)
        {
            if ($eventResult->getType() == EventResult::SUCCESS)
            {
                $parameters = $eventResult->getParameters();


            }
        }
    }

    protected static function getFieldNameCamelCase($fieldName)
    {
        return lcfirst(StringHelper::snake2camel($fieldName));
    }

    protected function getSettingsGroups($arSettings)
    {
        if (isset($arSettings["GROUPS"]))
        {
            $arGroups = $arSettings["GROUPS"];
            if (!is_array($arGroups))
                $arGroups = array();

            $arOptions = $arSettings["OPTIONS"];
            if (is_array($arOptions))
            {
                if (!isset($arGroups["DEFAULT"]))
                {
                    $arGroups["DEFAULT"] = array(
                        "TITLE" => Loc::getMessage("DEVBX_ADMIN_OPTIONS_DEFAULT_GROUP_TITLE"),
                        "SORT" => 100,
                    );
                }

                foreach ($arOptions as $fieldName => &$arOption) {
                    if (isset($arOption["GROUP"])) {
                        if (array_key_exists($arOption["GROUP"], $arGroups)) {
                            $group = $arOption["GROUP"];
                            if (preg_replace("/[^a-zA-Z0-9_]/is", "", $group) != $group)
                            {
                                throw new SystemException('Invalid group id '.$group);
                            }
                        } else {
                            $group = "DEFAULT";
                        }
                    } else {
                        $group = "DEFAULT";
                    }

                    $arGroups[$group]["ITEMS"][$fieldName] = $arOption;
                }
            }
        } else
        {
            $arGroups = $arSettings;
        }

        $arIdMap = [];

        foreach ($arGroups as $groupId=>&$arGroup)
        {
            $arGroup['ID'] = $groupId;
            if ($groupId == 'DEFAULT' && !isset($arGroup['TITLE']))
            {
                $arGroup["TITLE"] = Loc::getMessage("DEVBX_ADMIN_OPTIONS_DEFAULT_GROUP_TITLE");
            }

            foreach ($arGroup['ITEMS'] as $optionId=>&$arOption)
            {
                if (empty($arOption['TITLE'])) {
                    $arOption['TITLE'] = Loc::getMessage($this->lang_prefix.$optionId);
                    if (empty($arOption['TITLE']))
                    {
                        $arOption['TITLE'] = Loc::getMessage("OPTION_" . $optionId); // deprecated
                        if (empty($arOption['TITLE']))
                        {
                            $arOption['TITLE'] = $optionId;
                        }
                    }
                }

                if ($arOption['TYPE'] == 'Y/N')
                {
                    $arOption['TYPE'] = 'CHECKBOX';
                }

                if (!isset($arOption['ID']))
                {
                    $arOption['ID'] = $optionId;
                }

                if (isset($arOption['PARENT_REQUIRED']))
                {
                    if (!isset($arOption['VISIBLE_CONDITION']))
                    {
                        $arOption['VISIBLE_CONDITION'] = static::getFieldNameCamelCase($arOption['PARENT_REQUIRED']).'.value == "Y"';
                    }
                }

                if (array_key_exists($arOption['ID'], $arIdMap))
                    throw new SystemException('option id "'.$arOption['ID'].'" already exists');

                $arIdMap[$arOption['ID']] = &$arOption;
            }
        }

        unset($arOption, $arGroup);

        \Bitrix\Main\Type\Collection::sortByColumn($arGroups, "SORT", function ($v) {
            if (!is_numeric($v))
                $v = 500;
            return $v;
        });

        return $arGroups;
    }

    protected function getGroupValues(&$arGroup, $siteId = false)
    {
        foreach ($arGroup["ITEMS"] as $name => &$option) {
            $option["VALUE"] = Option::get($this->module_id, $name, $option["DEFAULT"], $siteId);

            $valueType = Manager::getInstance()->getValueType($option['TYPE']);
            if ($valueType)
            {
                $option["VALUE"] = $valueType::convertFromDB($option["VALUE"], $option);
            }
        }
    }

    protected function setGroupValues($arGroup, $siteId = false)
    {
        foreach ($arGroup["ITEMS"] as $name => $option) {

            $value = $option['VALUE'];

            $valueType = Manager::getInstance()->getValueType($option['TYPE']);
            if ($valueType)
            {
                $value = $valueType::convertToDB($value, $option);
            }

            if (is_array($value))
                $value = serialize($value);

            Option::set($this->module_id, $name, $value, $siteId);
        }
    }

    public function showSettings($arSettings, $siteId = false)
    {
        \CJSCore::Init(['devbx_core_admin','devbx_core_mslang']);

        $arGroups = $this->getSettingsGroups($arSettings);

        $arJSOption = array();

        foreach ($arGroups as $groupId=>$arGroup) {
            if (empty($arGroup["ITEMS"]))
                continue;

            $this->getGroupValues($arGroup, $siteId);

            ?>
            <tr class="heading">
                <td colspan="2"><?= htmlspecialcharsbx($arGroup["TITLE"]) ?></td>
            </tr>
            <?

            foreach ($arGroup["ITEMS"] as $name => $option) {
                if ($siteId !== false)
                {
                    $htmlId = 'option_'.$siteId.'_'.$groupId.'_'.($option['ID'] ?? $name);
                    $htmlName = 'SETTINGS['.$siteId.']['.$groupId.']['.$name.']';
                } else {
                    $htmlId = 'option_'.$groupId.'_'.($option['ID'] ?? $name);
                    $htmlName = 'SETTINGS['.$groupId.']['.$name.']';
                }

                $option['VARIABLE_NAME'] = $htmlName;
                $option['VARIABLE_ID'] = $htmlId;

                if (isset($option['RELATION_ID']))
                {
                    throw new SystemException('Options "RELATION_ID" deprecated');
                }

                $this->showOption($arGroup, $option);

                unset($option['VALUE']);
                $arJSOption[] = $option;
            }
        }

        $arJSParams = array(
            'VALUE_TYPE' => array(),
            'OPTIONS' => $arJSOption,
        );

        foreach ($this->usedValueType as $type=>$objClass)
        {
            $arJSParams['VALUE_TYPE'][$type] = $objClass::getJSClass();
        }

        $containerId = 'options';
        if ($siteId)
        {
            $containerId.='_'.$siteId;
        }

        $arJSParams['CONTAINER_ID'] = $containerId;

        ?>
        <tr style="display:none;" id="<?=htmlspecialcharsbx($containerId)?>">
            <td>
                <script>
                    new DevBX.Admin.Options(<?=Json::encode($arJSParams)?>);
                </script>
            </td>
        </tr>
        <?
    }

    public function showMultiSiteSettings($arSettings)
    {
        static $tabCounter = array();

        \CJSCore::Init(['devbx_core_admin']);

        echo '<tr><td colspan="2">';

        $arSites = array();
        $dbSites = \CSite::GetList($b = "sort", $o = "asc", array("ACTIVE" => "Y"));
        while ($arSite = $dbSites->Fetch()) {
            $arSites[] = $arSite;
        }

        $aSiteTabs = array();

        $tabId = "TabControl_" . preg_replace('#[^a-z]#i', '_', $this->module_id) . "_options";

        $tabCounter[$this->module_id]++;
        if ($tabCounter[$this->module_id] > 1) {
            $tabId .= '_' . $tabCounter[$this->module_id];
        }

        foreach ($arSites as $arSite) {
            $aSiteTabs[] = array(
                "DIV" => $tabId . '_' . $arSite["ID"],
                "TAB" => '[' . $arSite["ID"] . '] ' . htmlspecialcharsbx($arSite["NAME"]),
                'TITLE' => Loc::getMessage("DEVBX_ADMIN_OPTIONS_SITE_NAME", array("#ID#" => $arSite["ID"], "#NAME#" => htmlspecialcharsbx($arSite["NAME"])))
            );
        }

        $siteTabControl = new \CAdminViewTabControl($tabId, $aSiteTabs);

        $siteTabControl->Begin();

        foreach ($arSites as $arSite)
        {
            $siteTabControl->BeginNextTab();

            ?>
            <table cellpadding="0" cellspacing="0" border="0" class="edit-table" width="100%">
                <?
                $this->showSettings($arSettings, $arSite['ID']);
                ?>
            </table>
            <?

            $siteTabControl->EndTab();
        }

        $siteTabControl->End();

        echo '</td></tr>';
    }

    public function showOption($arGroup, $option)
    {
        $title = $option['TITLE'];

        if ($option['HINT'])
        {
            $hintId = "hint_".$option["VARIABLE_ID"];
            $jsHintId = \CUtil::JSEscape($hintId);
            $jsHint = \CUtil::JSEscape($option['HINT']);

            $title.= '<span id="'.$hintId.'"></span><script>DevBX.Admin.popupHint(BX(\''.$hintId.'\'), \''.$jsHint.'\')</script>';
            //$title.= '<span id="'.$hintId.'"></span><script>BX.hint_replace(BX(\''.$hintId.'\'), \'\', \''.$jsHint.'\')</script>';
        }
        ?>
        <tr data-entity="option" data-id="<?=$option['ID']?>"<?if ($option['HIDDEN'] == 'Y'):?> style="display:none;" <?endif?>>
            <td width="40%"><?= $title ?></td>
            <td>
                <?
                $this->showInput($arGroup, $option) ?>
            </td>
        </tr>
        <?
    }

    public function showInput($arGroup, $option)
    {
        $valueType = Manager::getInstance()->getValueType($option["TYPE"]);
        if ($valueType)
        {
            $this->usedValueType[$option["TYPE"]] = $valueType;

            $valueType::showValue($option);
        }
    }

    public function saveSettings($arSettings)
    {
        $request = Application::getInstance()->getContext()->getRequest();

        $arGroups = $this->getSettingsGroups($arSettings);

        $values = $request['SETTINGS'];
        if (!is_array($values))
            $values = array();

        foreach ($arGroups as $groupId=>$arGroup)
        {
            if (empty($arGroup['ITEMS']))
                continue;

            $groupValues = isset($values[$groupId]) && is_array($values[$groupId]) ? $values[$groupId] : [];

            foreach ($arGroup['ITEMS'] as $itemId=>&$arItem)
            {
                $arItem['VALUE'] = $groupValues[$itemId];
            }
            unset($arItem);

            $this->setGroupValues($arGroup, false);
        }
    }

    public function saveMultiSiteSettings($arSettings)
    {
        $request = Application::getInstance()->getContext()->getRequest();

        $arSites = array();
        $dbSites = \CSite::GetList($b = "sort", $o = "asc", array("ACTIVE" => "Y"));
        while ($arSite = $dbSites->Fetch()) {
            $arSites[] = $arSite;
        }

        $arGroups = $this->getSettingsGroups($arSettings);

        $values = $request['SETTINGS'];
        if (!is_array($values))
            $values = array();

        foreach ($arSites as $arSite)
        {
            $siteValues = isset($values[$arSite['ID']]) && is_array($values[$arSite['ID']]) ? $values[$arSite['ID']] : [];

            foreach ($arGroups as $groupId=>$arGroup)
            {
                if (empty($arGroup['ITEMS']))
                    continue;

                $groupValues = isset($siteValues[$groupId]) && is_array($siteValues[$groupId]) ? $siteValues[$groupId] : [];

                foreach ($arGroup['ITEMS'] as $itemId=>&$arItem)
                {
                    $arItem['VALUE'] = $groupValues[$itemId];
                }
                unset($arItem);

                $this->setGroupValues($arGroup, $arSite['ID']);
            }
        }
    }

    public function setDefaultValues($multiSite = false)
    {
        if ($multiSite)
        {
            $dbResult = \Bitrix\Main\SiteTable::getList();
            while ($arSite = $dbResult->fetch()) {
                Option::delete($this->module_id, ['site_id'=>$arSite['LID']]);
            }
        } else {
            Option::delete($this->module_id);
        }
    }

}