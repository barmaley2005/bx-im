<?php

namespace DevBx\Core\UserType;

use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Loader;
use Bitrix\Main\Page\Asset;
use Bitrix\Sale\Location\LocationTable;
use Bitrix\Main\Type;
use google\protobuf\FieldOptions\JSType;

Loc::loadMessages(__FILE__);

class MapYandex extends BaseType
{
    const USER_TYPE_ID = "devbx_map_yandex";

    public static function GetUserTypeDescription()
    {
        return array(
            "USER_TYPE_ID" => self::USER_TYPE_ID,
            "CLASS_NAME" => __CLASS__,
            "DESCRIPTION" => Loc::getMessage('DEVBX_USER_TYPE_MAP_YANDEX_DESCRIPTION'),
            "BASE_TYPE" => 'string',
            "EDIT_CALLBACK" => array(__CLASS__, 'GetPublicEdit'),
            "VIEW_CALLBACK" => array(__CLASS__, 'GetPublicView'),
        );
    }

    public static function GetDBColumnType($arUserField)
    {
        return "varchar(255)";
    }

    public static function CheckFields($arUserField, $value)
    {
        $aMsg = array();
        return $aMsg;
    }

    public static function PrepareSettings($arUserField)
    {
        return array(
            "DEFAULT_VALUE" => $arUserField["SETTINGS"]["DEFAULT_VALUE"],
        );
    }

    public static function GetSettingsHTML($arUserField, $arHtmlControl, $bVarsFromForm)
    {
        if ($bVarsFromForm) {
            $arSettings = self::PrepareSettings(array("SETTINGS" => $GLOBALS[$arHtmlControl["NAME"]]));
        } elseif (is_array($arUserField)) {
            $arSettings = self::PrepareSettings($arUserField);
        } else {
            $arSettings = array();
        }

        return <<<JS
<script>
    var node = document.querySelector('input[name="MULTIPLE"]');
    if (node)
    {
        node = node.closest('tr');
        if (node)
            {
                node.style.display = 'none';
            }
    }
</script>
JS;
    }

    public static function GetPublicEdit($arUserField, $arAdditionalParameters = array())
    {
        $fieldName = static::getFieldName($arUserField, $arAdditionalParameters);
        $value = static::getFieldValue($arUserField, $arAdditionalParameters);

        if ($arUserField['MULTIPLE'] !== 'Y')
        {
            $arHtmlControl = [
                'NAME' => $fieldName,
                'VALUE' => is_array($value) ? reset($value) : $value,
            ];

            return static::__getEditFormHTML($arUserField, $arHtmlControl, false);
        } else
        {
            $html = '';

            foreach ($value as $singleValue)
            {
                $arHtmlControl = [
                    'NAME' => $fieldName,
                    'VALUE' => $singleValue,
                ];

                $html .= static::__getEditFormHTML($arUserField, $arHtmlControl, $singleValue);
            }

            return $html;
        }
    }

    public static function _DrawKeyInputControl($MAP_ID, $strDomain)
    {
        echo BeginNote();
        ?>
        <div id="key_input_control_<?echo $MAP_ID?>">
            <?echo str_replace('#DOMAIN#', $strDomain, Loc::getMessage('DEVBX_USER_TYPE_MAP_YANDEX_NO_KEY_MESSAGE'))?><br /><br />
            <?echo Loc::getMessage('DEVBX_USER_TYPE_MAP_YANDEX_NO_KEY')?><input type="text" name="map_yandex_key_<?echo $MAP_ID?>" id="map_yandex_key_<?echo $MAP_ID?>" /> <input type="button" value="<?echo htmlspecialcharsbx(Loc::getMessage('DEVBX_USER_TYPE_MAP_YANDEX_NO_KEY_BUTTON'))?>" onclick="setYandexKey('<?echo $strDomain?>', 'map_yandex_key_<?echo $MAP_ID?>')" /> <input type="button" value="<?echo htmlspecialcharsbx(Loc::getMessage('DEVBX_USER_TYPE_MAP_YANDEX_SAVE_KEY_BUTTON'))?>" onclick="saveYandexKey('<?echo $strDomain?>', 'map_yandex_key_<?echo $MAP_ID?>')" />
        </div>
        <div id="key_input_message_<?echo $MAP_ID?>" style="display: none;"><?echo Loc::getMessage('DEVBX_USER_TYPE_MAP_YANDEX_NO_KEY_OKMESSAGE')?></div>
        <?
        echo EndNote();
        ?>
        <script type="text/javascript">
            function setYandexKey(domain, input)
            {
                LoadMap_<?echo $MAP_ID?>(document.getElementById(input).value);
            }

            function saveYandexKey(domain, input)
            {
                var value = document.getElementById(input).value;

                CHttpRequest.Action = function(result)
                {
                    CloseWaitWindow();
                    if (result == 'OK')
                    {
                        document.getElementById('key_input_control_<?echo $MAP_ID?>').style.display = 'none';
                        document.getElementById('key_input_message_<?echo $MAP_ID?>').style.display = 'block';
                        if (!window.GLOBAL_arMapObjects['<?echo $MAP_ID?>'])
                            setYandexKey(domain, input);
                    }
                    else
                        alert('<?echo \CUtil::JSEscape(Loc::getMessage('DEVBX_USER_TYPE_MAP_YANDEX_NO_KEY_ERRORMESSAGE'))?>');
                }

                var data = 'key_type=yandex&domain=' + domain + '&key=' + value + '&<?echo bitrix_sessid_get()?>';
                ShowWaitWindow();
                CHttpRequest.Post('/bitrix/admin/settings.php?lang=<?echo LANGUAGE_ID?>&mid=fileman&save_map_key=Y', data);
            }
        </script>
        <?
    } // _DrawKeyInputControl()

    protected static function __getEditFormHTML($arUserField, $arHtmlControl, $multiple = false)
    {
        global $APPLICATION;

        static $yandexMapLastNumber = 0;
        static $yandexMapID = '';

        if (($multiple && $yandexMapID !== $arUserField["ENTITY_VALUE_ID"]) || !$multiple)
            $yandexMapLastNumber = 0;

        if ($multiple)
            $yandexMapID = $arUserField["ENTITY_VALUE_ID"];

        if (strlen($arHtmlControl['VALUE']) > 0)
        {
            list($POINT_LAT, $POINT_LON) = explode(',', $arHtmlControl['VALUE'], 2);
            $bHasValue = true;
        }
        else
        {
            $POINT_LAT = doubleval(Loc::getMessage('DEVBX_USER_TYPE_MAP_YANDEX_INIT_LAT'));
            $POINT_LON = doubleval(Loc::getMessage('DEVBX_USER_TYPE_MAP_YANDEX_INIT_LON'));
            $bHasValue = false;
        }
        ob_start();

        if ($multiple && isset($GLOBALS['YANDEX_MAP_PROPERTY'][$arUserField["ENTITY_VALUE_ID"]]))
        {
            // property is multimple and map is already showed

            $MAP_ID = $GLOBALS['YANDEX_MAP_PROPERTY'][$arUserField["ENTITY_VALUE_ID"]];
        }
        else
        {
            $MAP_ID = 'map_yandex_'.$arUserField["ENTITY_VALUE_ID"].'_'.mt_rand(0,99999);
            $GLOBALS['YANDEX_MAP_PROPERTY'][$arUserField["ENTITY_VALUE_ID"]] = $MAP_ID;


            ?>
            <div>
            <div id="bx_map_hint_<?echo $MAP_ID?>" style="display: none;">
                <div id="bx_map_hint_value_<?echo $MAP_ID?>" style="display: <?echo $bHasValue ? 'block' : 'none'?>;">
                    <?
                    echo Loc::getMessage('DEVBX_USER_TYPE_MAP_YANDEX_INSTR_VALUE').'<br /><br />';
                    ?>
                </div>
                <div id="bx_map_hint_novalue_<?echo $MAP_ID?>" style="display: <?echo $bHasValue ? 'none' : 'block'?>;">
                    <?
                    echo Loc::getMessage('DEVBX_USER_TYPE_MAP_YANDEX_INSTR').'<br /><br />';
                    ?>
                </div>
            </div>
        <?
        $APPLICATION->IncludeComponent(
            'bitrix:map.yandex.system',
            '',
            array(
                'INIT_MAP_TYPE' => 'MAP',
                'INIT_MAP_LON' => $POINT_LON ? $POINT_LON : 37.64,
                'INIT_MAP_LAT' => $POINT_LAT ? $POINT_LAT : 55.76,
                'INIT_MAP_SCALE' => 10,
                'OPTIONS' => array('ENABLE_SCROLL_ZOOM', 'ENABLE_DRAGGING'),
                'CONTROLS' => array('ZOOM', 'MINIMAP', 'TYPECONTROL', 'SCALELINE'),
                'MAP_WIDTH' => '95%',
                'MAP_HEIGHT' => 400,
                'MAP_ID' => $MAP_ID,
                'DEV_MODE' => 'Y',
                //'ONMAPREADY' => 'BXWaitForMap_'.$MAP_ID
            ),
            false, array('HIDE_ICONS' => 'Y')
        );

        //http://jabber.bx/view.php?id=17908
        ?>
            <script type="text/javascript">
                BX.ready(function(){
                    var tabArea = BX.findParent(BX("BX_YMAP_<?=$MAP_ID?>"), {className: "adm-detail-content"});
                    if (tabArea && tabArea.id)
                    {
                        var tabButton = BX("tab_cont_" + tabArea.id);
                        BX.bind(tabButton, "click", function() { BXMapYandexAfterShow("<?=$MAP_ID?>"); });
                    }
                });

                <?if(!$multiple):?>
                function setPointValue_<?echo $MAP_ID?>(obEvent)
                {
                    var obPoint = BX.type.isArray(obEvent) ? obEvent : obEvent.get("coordPosition");

                    if (null == window.obPoint_<?echo $MAP_ID?>__n0_)
                    {
                        window.obPoint_<?echo $MAP_ID?>__n0_ = new ymaps.Placemark(obPoint, {}, {draggable:true});
                        window.GLOBAL_arMapObjects['<?echo $MAP_ID?>'].geoObjects.add(window.obPoint_<?echo $MAP_ID?>__n0_);
                        window.obPoint_<?echo $MAP_ID?>__n0_.events.add('dragend', updatePointPosition_<?echo $MAP_ID?>__n0_);
                    }
                    else
                    {
                        window.obPoint_<?echo $MAP_ID?>__n0_.geometry.setCoordinates(obPoint);
                    }

                    BX('bx_map_hint_novalue_<?echo $MAP_ID?>').style.display = 'none';
                    BX('bx_map_hint_value_<?echo $MAP_ID?>').style.display = 'block';
                    BX('point_control_<?echo $MAP_ID?>__n0_').style.display = 'inline-block';

                    updatePointPosition_<?echo $MAP_ID?>__n0_(obPoint);
                    window.GLOBAL_arMapObjects['<?echo $MAP_ID?>'].panTo(obPoint_<?echo $MAP_ID?>__n0_.geometry.getCoordinates(), {delay:0});
                }
                <?else:?>
                function setPointValue_<?echo $MAP_ID?>(obEvent)
                {
                    var obPoint = BX.type.isArray(obEvent) ? obEvent : obEvent.get("coordPosition");
                    var i = 0, point = [], k = [];
                    while (BX('point_<?echo $MAP_ID?>__n' + i + '_lat'))
                    {
                        if(BX('point_<?echo $MAP_ID?>__n' + i + '_lat').value == ''
                            && BX('point_<?echo $MAP_ID?>__n' + i + '_lon')
                            && BX('point_<?echo $MAP_ID?>__n' + i + '_lon').value == '')
                        {
                            k.push(i);
                        }
                        i++;
                    }
                    if (k.length <= 1)
                    {
                        window.addNewRow(BX('point_<?echo $MAP_ID?>__n0_lat').parentNode.parentNode.parentNode.parentNode.id);
                    }
                    k = (k.length) ? Math.min.apply(null, k) : i;
                    var obPnt = 'obPoint_<?echo $MAP_ID?>__n'+k+'_',
                        updPP = 'updatePointPosition_<?echo $MAP_ID?>__n'+k+'_';
                    if(window[updPP])
                    {
                        window[obPnt] = null;
                        window[obPnt] = new ymaps.Placemark(obPoint, {}, {draggable:true});
                        window.GLOBAL_arMapObjects["<?echo $MAP_ID?>"].geoObjects.add(window[obPnt]);
                        window[obPnt].events.add("dragend", window[updPP]);
                        window[updPP](obPoint);
                    }

                    BX('point_control_<?echo $MAP_ID?>__n'+k+'_').style.display = 'inline-block';

                    updateMapHint_<?echo $MAP_ID?>();
                }
                <?endif;?>

                function setDefaultPreset_<?echo $MAP_ID?>()
                {
                    if(window.GLOBAL_arMapObjects['<?echo $MAP_ID?>'].geoObjects)
                    {
                        window.GLOBAL_arMapObjects['<?echo $MAP_ID?>'].geoObjects.each(function (geoObject) {
                            geoObject.options.set({preset: 'twirl#blueIcon'});
                        });
                    }
                }

                function updateMapHint_<?echo $MAP_ID?>()
                {
                    var noValue = true,
                        i = 0;
                    while (BX('point_<?echo $MAP_ID?>__n' + i + '_lat'))
                    {
                        if (BX('point_<?echo $MAP_ID?>__n' + i + '_lat').value !== '' || !BX('point_<?echo $MAP_ID?>__n' + i + '_lon') || BX('point_<?echo $MAP_ID?>__n' + i + '_lon').value !=='')
                            noValue = false;
                        i++;
                    }
                    if (noValue)
                    {
                        BX('bx_map_hint_novalue_<?echo $MAP_ID?>').style.display = 'block';
                        BX('bx_map_hint_value_<?echo $MAP_ID?>').style.display = 'none';
                    }
                    else
                    {
                        BX('bx_map_hint_novalue_<?echo $MAP_ID?>').style.display = 'none';
                        BX('bx_map_hint_value_<?echo $MAP_ID?>').style.display = 'block';
                    }
                }
            </script>

            <div id="bx_address_search_control_<?echo $MAP_ID?>" style="display: none;margin-top:15px;"><?echo Loc::getMessage('DEVBX_USER_TYPE_MAP_YANDEX_SEARCH')?><input type="text" name="bx_address_<?echo $MAP_ID?>" id="bx_address_<?echo $MAP_ID?>" value="" style="width: 300px;" autocomplete="off" /></div>
            <br />
            <?
        }
        ?>
        <input type="text" style="width:125px;margin:0 0 4px" name="point_<?echo $MAP_ID?>__n<?=$yandexMapLastNumber?>_lat" id="point_<?echo $MAP_ID?>__n<?=$yandexMapLastNumber?>_lat" onchange="setInputPointValue_<?echo $MAP_ID?>__n<?=$yandexMapLastNumber?>_()" />, <input type="text" style="width:125px;margin:0 15px 4px 0;" name="point_<?echo $MAP_ID?>__n<?=$yandexMapLastNumber?>_lon" id="point_<?echo $MAP_ID?>__n<?=$yandexMapLastNumber?>_lon" onchange="setInputPointValue_<?echo $MAP_ID?>__n<?=$yandexMapLastNumber?>_()" />
        <div id="point_control_<?echo $MAP_ID?>__n<?=$yandexMapLastNumber?>_" style="display:none;margin:0 0 4px"><a href="javascript:void(0);" onclick="findPoint_<?echo $MAP_ID?>__n<?=$yandexMapLastNumber?>_()"><?echo Loc::getMessage('DEVBX_USER_TYPE_MAP_YANDEX_GOTO_POINT')?></a> | <a href="javascript:void(0);" onclick="if (confirm('<?echo \CUtil::JSEscape(Loc::getMessage('DEVBX_USER_TYPE_MAP_YANDEX_REMOVE_POINT_CONFIRM'))?>')) removePoint_<?echo $MAP_ID?>__n<?=$yandexMapLastNumber?>_()"><?echo Loc::getMessage('DEVBX_USER_TYPE_MAP_YANDEX_REMOVE_POINT')?></a></div><br />
        <input type="text" style="display:none;" id="value_<?echo $MAP_ID;?>__n<?=$yandexMapLastNumber?>_" name="<?=htmlspecialcharsbx($arHtmlControl["NAME"])?>" value="<?=htmlspecialcharsEx($arHtmlControl["VALUE"])?>" />
        <script>
            window.jsAdminYandexMess = {
                nothing_found: '<?echo \CUtil::JSEscape(Loc::getMessage('DEVBX_USER_TYPE_MAP_YANDEX_NOTHING_FOUND'))?>'
            }
            jsUtils.loadCSSFile('/bitrix/components/bitrix/map.yandex.view/settings/settings.css');

            function BXWaitForMap_<?echo $MAP_ID?>__n<?=$yandexMapLastNumber?>_()
            {
                if (!window.GLOBAL_arMapObjects['<?echo $MAP_ID?>'])
                    setTimeout(BXWaitForMap_<?echo $MAP_ID?>__n<?=$yandexMapLastNumber?>_, 300);
                else
                {
                    window.obPoint_<?echo $MAP_ID?>__n<?=$yandexMapLastNumber?>_ = null;

                    window.GLOBAL_arMapObjects['<?echo $MAP_ID?>'].events.remove('dblclick', window.setPointValue_<?echo $MAP_ID?>);
                    window.GLOBAL_arMapObjects['<?echo $MAP_ID?>'].events.add('dblclick', window.setPointValue_<?echo $MAP_ID?>);
                    window.GLOBAL_arMapObjects['<?echo $MAP_ID?>'].events.add('click', window.setDefaultPreset_<?echo $MAP_ID?>);
                    var searchInput = BX('bx_address_<?echo $MAP_ID?>');
                    BX.bind(searchInput, "keydown", jsYandexCESearch_<?echo $MAP_ID;?>.setTypingStarted);
                    BX.bind(searchInput, "contextmenu", jsYandexCESearch_<?echo $MAP_ID;?>.setTypingStarted);
                    BX('point_control_<?echo $MAP_ID?>__n<?=$yandexMapLastNumber?>_').style.display = 'none';

                    <?if ($bHasValue):?>
                    setPointValue_<?echo $MAP_ID?>__n<?=$yandexMapLastNumber?>_([<?echo $POINT_LAT?>, <?echo $POINT_LON?>]);
                    if (<?=$yandexMapLastNumber?> > 0)
                        window.GLOBAL_arMapObjects['<?echo $MAP_ID?>'].setBounds(window.GLOBAL_arMapObjects['<?echo $MAP_ID?>'].geoObjects.getBounds(), {checkZoomRange: true});
                    <?endif;?>

                    BX('bx_address_search_control_<?echo $MAP_ID?>').style.display = 'block';
                    BX('bx_map_hint_<?echo $MAP_ID?>').style.display = 'block';

                }
            }

            function findPoint_<?echo $MAP_ID?>__n<?=$yandexMapLastNumber?>_()
            {
                if (null != window.obPoint_<?echo $MAP_ID?>__n<?=$yandexMapLastNumber?>_)
                {
                    window.setDefaultPreset_<?echo $MAP_ID?>();
                    window.GLOBAL_arMapObjects['<?echo $MAP_ID?>'].panTo(window.obPoint_<?echo $MAP_ID?>__n<?=$yandexMapLastNumber?>_.geometry.getCoordinates(),{delay:0});
                    window.obPoint_<?echo $MAP_ID?>__n<?=$yandexMapLastNumber?>_.options.set({preset: 'twirl#redIcon'});

                }
            }

            function removePoint_<?echo $MAP_ID?>__n<?=$yandexMapLastNumber?>_()
            {
                window.GLOBAL_arMapObjects['<?echo $MAP_ID?>'].geoObjects.remove(window.obPoint_<?echo $MAP_ID?>__n<?=$yandexMapLastNumber?>_);
                window.obPoint_<?echo $MAP_ID?>__n<?=$yandexMapLastNumber?>_ = null;

                BX('point_control_<?echo $MAP_ID?>__n<?=$yandexMapLastNumber?>_').style.display = 'none';

                updatePointPosition_<?echo $MAP_ID?>__n<?=$yandexMapLastNumber?>_();

                updateMapHint_<?echo $MAP_ID?>();
            }

            function setPointValue_<?echo $MAP_ID?>__n<?=$yandexMapLastNumber?>_(obEvent)
            {
                var obPoint = BX.type.isArray(obEvent) ? obEvent : obEvent.get("coordPosition");

                if (null == window.obPoint_<?echo $MAP_ID?>__n<?=$yandexMapLastNumber?>_)
                {
                    window.obPoint_<?echo $MAP_ID?>__n<?=$yandexMapLastNumber?>_ = new ymaps.Placemark(obPoint, {}, {draggable:true});
                    window.GLOBAL_arMapObjects['<?echo $MAP_ID?>'].geoObjects.add(window.obPoint_<?echo $MAP_ID?>__n<?=$yandexMapLastNumber?>_);
                    window.obPoint_<?echo $MAP_ID?>__n<?=$yandexMapLastNumber?>_.events.add('dragend', updatePointPosition_<?echo $MAP_ID?>__n<?=$yandexMapLastNumber?>_);
                }
                else
                {
                    window.obPoint_<?echo $MAP_ID?>__n<?=$yandexMapLastNumber?>_.geometry.setCoordinates(obPoint);
                }

                BX('point_control_<?echo $MAP_ID?>__n<?=$yandexMapLastNumber?>_').style.display = 'inline-block';
                BX('bx_map_hint_novalue_<?echo $MAP_ID?>').style.display = 'none';
                BX('bx_map_hint_value_<?echo $MAP_ID?>').style.display = 'block';

                updatePointPosition_<?echo $MAP_ID?>__n<?=$yandexMapLastNumber?>_(obPoint);
                window.GLOBAL_arMapObjects['<?echo $MAP_ID?>'].panTo(obPoint_<?echo $MAP_ID?>__n<?=$yandexMapLastNumber?>_.geometry.getCoordinates(),{delay:0});
            }

            function setInputPointValue_<?echo $MAP_ID?>__n<?=$yandexMapLastNumber?>_()
            {
                var vv = [BX('point_<?echo $MAP_ID?>__n<?=$yandexMapLastNumber?>_lat').value, BX('point_<?echo $MAP_ID?>__n<?=$yandexMapLastNumber?>_lon').value];
                if (vv[0] == '' && vv[1] == '')
                {
                    removePoint_<?echo $MAP_ID?>__n<?=$yandexMapLastNumber?>_();
                }
                var v = [parseFloat(BX('point_<?echo $MAP_ID?>__n<?=$yandexMapLastNumber?>_lat').value), parseFloat(BX('point_<?echo $MAP_ID?>__n<?=$yandexMapLastNumber?>_lon').value)];
                if (!isNaN(v[0]) && !isNaN(v[1]))
                {
                    setPointValue_<?echo $MAP_ID?>__n<?=$yandexMapLastNumber?>_(v);
                }
            }

            function updatePointPosition_<?echo $MAP_ID?>__n<?=$yandexMapLastNumber?>_(obPoint)
            {
                //var obPosition = obPoint.getGeoPoint();
                if (!!obPoint && !!obPoint.geometry)
                    obPoint = obPoint.geometry.getCoordinates();
                else if (!!window.obPoint_<?echo $MAP_ID?>__n<?=$yandexMapLastNumber?>_)
                    obPoint = window.obPoint_<?echo $MAP_ID?>__n<?=$yandexMapLastNumber?>_.geometry.getCoordinates();
                else
                    obPoint = null;

                var obInput = BX('value_<?echo $MAP_ID?>__n<?=$yandexMapLastNumber?>_');
                obInput.value = null == obPoint ? '' : obPoint[0] + ',' + obPoint[1];

                BX('point_<?echo $MAP_ID?>__n<?=$yandexMapLastNumber?>_lat').value = obPoint ? obPoint[0] : '';
                BX('point_<?echo $MAP_ID?>__n<?=$yandexMapLastNumber?>_lon').value = obPoint ? obPoint[1] : '';
            }

            BX.ready(function(){
                setTimeout(BXWaitForMap_<?echo $MAP_ID?>__n<?=$yandexMapLastNumber?>_, 100);
            });

            var jsYandexCESearch_<?echo $MAP_ID;?> = {

                bInited: false,

                map: null,
                geocoder: null,
                obInput: null,
                timerID: null,
                timerDelay: 1000,

                arSearchResults: [],
                strLastSearch: null,

                obOut: null,

                __init: function(input)
                {
                    if (jsYandexCESearch_<?echo $MAP_ID;?>.bInited) return;

                    jsYandexCESearch_<?echo $MAP_ID;?>.map = window.GLOBAL_arMapObjects['<?echo $MAP_ID?>'];
                    jsYandexCESearch_<?echo $MAP_ID;?>.obInput = input;

                    input.onfocus = jsYandexCESearch_<?echo $MAP_ID;?>.showResults;
                    input.onblur = jsYandexCESearch_<?echo $MAP_ID;?>.hideResults;

                    jsYandexCESearch_<?echo $MAP_ID;?>.bInited = true;
                },

                setTypingStarted: function(e)
                {
                    if (null == e)
                        e = window.event;

                    jsYandexCESearch_<?echo $MAP_ID;?>.hideResults();

                    if (e.keyCode == 13 )
                    {
                        jsYandexCESearch_<?echo $MAP_ID;?>.doSearch();
                        return false;
                    }
                    else
                    {
                        if (!jsYandexCESearch_<?echo $MAP_ID;?>.bInited)
                            jsYandexCESearch_<?echo $MAP_ID;?>.__init(this);

                        if (e.type=="contextmenu")
                            jsYandexCESearch_<?echo $MAP_ID;?>.timerDelay=3000;
                        else
                            jsYandexCESearch_<?echo $MAP_ID;?>.timerDelay=1000;

                        if (null != jsYandexCESearch_<?echo $MAP_ID;?>.timerID)
                            clearTimeout(jsYandexCESearch_<?echo $MAP_ID;?>.timerID);

                        jsYandexCESearch_<?echo $MAP_ID;?>.timerID = setTimeout(jsYandexCESearch_<?echo $MAP_ID;?>.doSearch, jsYandexCESearch_<?echo $MAP_ID;?>.timerDelay);
                    }
                },

                doSearch: function()
                {
                    this.strLastSearch = jsUtils.trim(jsYandexCESearch_<?echo $MAP_ID;?>.obInput.value);

                    if (this.strLastSearch.length > 1)
                    {
                        ymaps.geocode(this.strLastSearch).then(
                            jsYandexCESearch_<?echo $MAP_ID;?>.__searchResultsLoad,
                            jsYandexCESearch_<?echo $MAP_ID;?>.handleError
                        );
                    }
                },

                handleError: function(error)
                {
                    alert(this.jsMess.mess_error + ': ' + error.message);
                },

                setResultsCoordinates: function()
                {
                    var obPos = jsUtils.GetRealPos(jsYandexCESearch_<?echo $MAP_ID;?>.obInput);
                    jsYandexCESearch_<?echo $MAP_ID;?>.obOut.style.top = (obPos.bottom + 2) + 'px';
                    jsYandexCESearch_<?echo $MAP_ID;?>.obOut.style.left = obPos.left + 'px';
                },

                __generateOutput: function()
                {
                    jsYandexCESearch_<?echo $MAP_ID;?>.obOut = document.body.appendChild(document.createElement('UL'));
                    jsYandexCESearch_<?echo $MAP_ID;?>.obOut.className = 'bx-yandex-address-search-results';
                },

                __searchResultsLoad: function(res)
                {
                    var _this = jsYandexCESearch_<?echo $MAP_ID;?>;

                    if (null == _this.obOut)
                        _this.__generateOutput();

                    _this.obOut.innerHTML = '';
                    _this.clearSearchResults();

                    var len = res.geoObjects.getLength();
                    if (len > 0)
                    {
                        for (var i = 0; i < len; i++)
                        {
                            _this.arSearchResults[i] = res.geoObjects.get(i);

                            var obListElement = document.createElement('LI');

                            if (i == 0)
                                obListElement.className = 'bx-yandex-first';

                            var obLink = document.createElement('A');
                            obLink.href = "javascript:void(0)";
                            var obText = obLink.appendChild(document.createElement('SPAN'));
                            obText.appendChild(document.createTextNode(
                                jsYandexCESearch_<?echo $MAP_ID;?>.arSearchResults[i].properties.get('metaDataProperty').GeocoderMetaData.text
                            ));

                            obLink.BXSearchIndex = i;
                            obLink.onclick = _this.__showSearchResult;

                            obListElement.appendChild(obLink);
                            _this.obOut.appendChild(obListElement);
                        }
                    }
                    else
                    {
                        //var str = _this.jsMess.mess_search_empty;
                        _this.obOut.innerHTML = '<li class="bx-yandex-notfound">' + window.jsAdminYandexMess.nothing_found + '</li>';
                    }

                    _this.showResults();
                },

                __showSearchResult: function()
                {
                    if (null !== this.BXSearchIndex)
                    {
                        var bounds =  jsYandexCESearch_<?echo $MAP_ID;?>.arSearchResults[this.BXSearchIndex].properties.get('boundedBy');
                        jsYandexCESearch_<?echo $MAP_ID;?>.map.setBounds(bounds, { checkZoomRange: true });
                    }
                },

                showResults: function()
                {
                    if(this.strLastSearch!=jsUtils.trim(jsYandexCESearch_<?echo $MAP_ID;?>.obInput.value))
                        jsYandexCESearch_<?echo $MAP_ID;?>.doSearch();

                    if (null != jsYandexCESearch_<?echo $MAP_ID;?>.obOut)
                    {
                        jsYandexCESearch_<?echo $MAP_ID;?>.setResultsCoordinates();
                        jsYandexCESearch_<?echo $MAP_ID;?>.obOut.style.display = 'block';
                    }
                },

                hideResults: function()
                {
                    if (null != jsYandexCESearch_<?echo $MAP_ID;?>.obOut)
                    {
                        setTimeout("jsYandexCESearch_<?echo $MAP_ID;?>.obOut.style.display = 'none'", 300);
                    }
                },

                clearSearchResults: function()
                {
                    for (var i = 0; i < jsYandexCESearch_<?echo $MAP_ID;?>.arSearchResults.length; i++)
                    {
                        delete jsYandexCESearch_<?echo $MAP_ID;?>.arSearchResults[i];
                    }

                    jsYandexCESearch_<?echo $MAP_ID;?>.arSearchResults = [];
                },

                clear: function()
                {
                    if (!jsYandexCESearch_<?echo $MAP_ID;?>.bInited)
                        return;

                    jsYandexCESearch_<?echo $MAP_ID;?>.bInited = false;
                    if (null != jsYandexCESearch_<?echo $MAP_ID;?>.obOut)
                    {
                        jsYandexCESearch_<?echo $MAP_ID;?>.obOut.parentNode.removeChild(jsYandexCESearch_<?echo $MAP_ID;?>.obOut);
                        jsYandexCESearch_<?echo $MAP_ID;?>.obOut = null;
                    }

                    jsYandexCESearch_<?echo $MAP_ID;?>.arSearchResults = [];
                    jsYandexCESearch_<?echo $MAP_ID;?>.map = null;
                    jsYandexCESearch_<?echo $MAP_ID;?>.geocoder = null;
                    jsYandexCESearch_<?echo $MAP_ID;?>.obInput = null;
                    jsYandexCESearch_<?echo $MAP_ID;?>.timerID = null;
                }
            }

        </script>
        </div>
        <?
        $out = ob_get_clean();

        if ($multiple)
            $yandexMapLastNumber++;

        return $out;
    }

    public static function GetEditFormHTML($arUserField, $arHtmlControl)
    {
        return self::__getEditFormHTML($arUserField, $arHtmlControl, false);
    }

    public static function GetAdminListViewHTML($arUserField, $arHtmlControl)
    {
        return $arHtmlControl['VALUE'];
    }

    public static function GetAdminListEditHTML($arUserField, $arHtmlControl)
    {
        return '<input type="text" name="'.$arHtmlControl['NAME'].'" value="'.$arHtmlControl['VALUE'].'">';
    }

    public static function GetPublicView($arUserField, $arAdditionalParameters = array())
    {
        //$arUserField["VALUE"]
    }
}