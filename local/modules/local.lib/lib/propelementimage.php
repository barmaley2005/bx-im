<?php

namespace Local\Lib;

use Bitrix\Main\Loader;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Web\Json;

class PropElementImage {
    public static function GetUserTypeDescription() {
        return array(
            'PROPERTY_TYPE' => 'S',
            'USER_TYPE' => 'DEVBX_PROPERTY_ELEMENT_IMAGE',
            'DESCRIPTION' => Loc::getMessage('LOCAL_LIB_PROP_ELEMENT_IMAGE_DESCRIPTION'),
            "GetPublicViewHTML" => array(__CLASS__, "GetPublicViewHTML"),
            "GetPublicEditHTML" => array(__CLASS__, "GetPublicEditHTML"),
            "GetAdminListViewHTML" => array(__CLASS__, "GetAdminListViewHTML"),
            "GetPropertyFieldHtml" => array(__CLASS__, "GetPropertyFieldHtml"),
            "GetPropertyFieldHtmlMulty" => array(__CLASS__,'GetPropertyFieldHtmlMulty'),
            "ConvertToDB" => array(__CLASS__, "ConvertToDB"),
            "ConvertFromDB" => array(__CLASS__, "ConvertFromDB"),
        );
    }

    public static function GetPublicViewHTML($arProperty, $value, $strHTMLControlName)
    {
        Loader::includeModule('iblock');

        $arElement = \CIBlockElement::GetByID($value["VALUE"])->GetNext();
        if (!$arElement)
            return '';

        return $arElement['NAME'];
    }

    public static function GetPublicEditHTML($arProperty, $value, $strHTMLControlName)
    {
        return '';
    }

    public static function GetAdminListViewHTML($arProperty, $value, $strHTMLControlName)
    {
        return self::GetPublicViewHTML($arProperty, $value, $strHTMLControlName);
    }

    public static function GetPropertyFieldHtmlMulty($arProperty, $value, $strHTMLControlName)
    {
        Loader::includeModule('iblock');
        \Bitrix\Main\UI\Extension::load("ui.vue3");

        ob_start();

        $containerId = 'prop_el_image_'.md5(serialize($arProperty).serialize($strHTMLControlName));

        $jsValue = [];

        foreach ($value as $id=>$v)
        {
            $ar = \CIBlockElement::GetByID($v['VALUE']['ID'])->GetNext();

            $jsValue[] = array(
                'ID' => $id,
                'VALUE' => $v['VALUE'],
                'DESCRIPTION' => $v['DESCRIPTION'],
                'LABEL' => $ar['NAME'],
            );
        }

        $arJSParams = array(
            'containerId' => $containerId,
            'property' => $arProperty,
            'value' => $jsValue,
            'htmlControlName' => $strHTMLControlName,
            'md5' => md5($strHTMLControlName['VALUE']),
        );

        ?>
        <div id="<?=$containerId?>"></div>

        <script>
            (function() {

                let params = <?=Json::encode($arJSParams)?>;

                let app = BX.Vue3.BitrixVue.createApp({
                    data() {
                        return Object.assign({}, params, {
                            newValueIndex: 0
                        });
                    },
                    mounted() {
                        for (let i=0;i<5;i++)
                        {
                            this.addValue();
                        }
                    },
                    computed: {
                        inputs() {
                            let result = [];

                            this.value.forEach(value => {
                                result.push({
                                    name: this.htmlControlName.VALUE+'['+value.ID+']',
                                    labelId: 'sp_'+this.md5+'_'+value.ID,
                                    value: value,
                                });
                            });

                            return result;
                        },
                    },
                    methods: {
                        addValue()
                        {
                            this.value.push({
                                ID: 'n'+this.newValueIndex,
                                LABEL: '',
                                VALUE: {
                                    ID: '',
                                    X: '',
                                    Y: '',
                                },
                                DESCRIPTION: '',
                            })
                            this.newValueIndex++;
                        },

                        searchElement(item)
                        {
                            jsUtils.OpenWindow('/bitrix/admin/iblock_element_search.php?lang=ru&IBLOCK_ID='+this.property.IBLOCK_ID+'&n='+
                                this.htmlControlName.VALUE+'&k='+item.value.ID+'&tableId=iblockprop-E-'+this.property.ID+'-0', 900, 700);
                        },

                        inputChange(item, value)
                        {
                            item.value.VALUE.ID = value;

                            setTimeout(function() {

                                item.VALUE.LABEL = document.getElementById(item.labelId).innerHTML;

                            }, 0);
                        },
                    },
                    template: `
                    <table>
                        <tr v-for="input in inputs">
                            <td>
                                <div style="display:flex;flex-direction:column;">
                                    <div>
                                        <input type="text" :name="input.name+'[ID]'" :id="input.name" :value="input.value.VALUE.ID" size="5" @change="inputChange(input, $event.target.value)">
                                        <input type="button" value="..." @click.stop.prevent="searchElement(input)">
                                        <span :id="input.labelId" v-html="input.value.LABEL"></span>
                                        </div>
                                    <div>
                                        <input type="text" :name="input.name+'[X]'" v-model="input.value.VALUE.X" placeholder="X%">
                                        <input type="text" :name="input.name+'[Y]'" v-model="input.value.VALUE.Y" placeholder="Y%">
                                    </div>
                                </div>
                            </td>
                        </tr>
                        <tr>
                            <td>
                                <input type="button" value="<?=Loc::getMessage('LOCAL_LIB_PROP_ADD_BTN')?>" @click.stop.prevent="addValue">
                            </td>
                        </tr>
                    </table>
                    `
                });

                window.propElImage = app.mount('#'+params.containerId);
            })();
        </script>

        <?

        return ob_get_clean();
    }

    public static function ConvertToDB($arProperty, $value)
    {
        if (!is_array($value['VALUE']) || intval($value['VALUE']['ID'])<=0)
            return false;

        return Json::encode($value['VALUE']);
    }

    public static function ConvertFromDB($arProperty, $value)
    {
        if (empty($value['VALUE']))
            return array();

        try {
            $value['VALUE'] = Json::decode($value['VALUE']);
            return $value;
        } catch (\Exception $e) {
            return array();
        }
    }
}


