<?php if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

\Bitrix\Main\Loader::includeModule('local.lib');

$settings = \Local\Lib\Settings::getInstance();

?>
<?$APPLICATION->ShowViewContent('CONTENT_BOTTOM');?>
<?
\Local\Lib\Utils::includePageFile('footer.php');
?>
</div>
<?$APPLICATION->ShowViewContent('BEFORE_FOOTER');?>
<footer class="footer">
    <div class="container">
        <div class="footer-container">
            <?$APPLICATION->IncludeComponent(
                "bitrix:catalog.section.list",
                "footer_menu",
                Array(
                    "ADD_SECTIONS_CHAIN" => "N",
                    "CACHE_FILTER" => "N",
                    "CACHE_GROUPS" => "Y",
                    "CACHE_TIME" => "36000000",
                    "CACHE_TYPE" => "A",
                    "COUNT_ELEMENTS" => "N",
                    "COUNT_ELEMENTS_FILTER" => "CNT_ACTIVE",
                    "FILTER_NAME" => "sectionsFilter",
                    "HIDE_SECTIONS_WITH_ZERO_COUNT_ELEMENTS" => "N",
                    "IBLOCK_ID" => \Local\Lib\Utils::getIblockIdByCode('FOOTER_MENU'),
                    "IBLOCK_TYPE" => "content",
                    "SECTION_CODE" => "",
                    "SECTION_FIELDS" => array("", ""),
                    "SECTION_ID" => "",
                    "SECTION_URL" => "",
                    "SECTION_USER_FIELDS" => array("", ""),
                    "SHOW_PARENT_NAME" => "Y",
                    "TOP_DEPTH" => "2",
                    "VIEW_MODE" => "LINE"
                ),false,array('HIDE_ICONS'=>'Y')
            );?>
            <?
            include($_SERVER['DOCUMENT_ROOT'].SITE_DIR.'include/footer/subscribe.php');
            ?>
        </div>

        <div class="copyright">
            <div class="footer-right__soc d-flex d-lg-none">
                <?
                include($_SERVER['DOCUMENT_ROOT'].SITE_DIR.'include/footer/social.php');
                ?>
            </div>

            <p class="copyright-text">
                <?$APPLICATION->IncludeFile(
                                    	SITE_DIR."include/footer/copyright.php",
                                    	Array(),
                                    	Array("MODE"=>"html","NAME"=>"","TEMPLATE"=>"clean.php")
                                    );?>

            </p>
        </div>
    </div>
</footer>

<div class="soc-box _other">
    <div class="container">
        <div class="soc _animat">
            <div class="soc-container">
                <a href="tel:+7<?=$settings->phone?>" class="soc-link">
                    <svg width="32" height="32" viewBox="0 0 32 32" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path fill-rule="evenodd" clip-rule="evenodd"
                              d="M16.0012 1.5769C8.04107 1.5769 1.57812 8.03985 1.57812 16C1.57812 23.9602 8.04107 30.4231 16.0012 30.4231C23.9614 30.4231 30.4244 23.9602 30.4244 16C30.4244 8.03985 23.9614 1.5769 16.0012 1.5769ZM16.0012 2.73075C23.325 2.73075 29.2705 8.67631 29.2705 16C29.2705 23.3237 23.325 29.2693 16.0012 29.2693C8.67753 29.2693 2.73197 23.3237 2.73197 16C2.73197 8.67631 8.67753 2.73075 16.0012 2.73075Z" />
                        <path fill-rule="evenodd" clip-rule="evenodd"
                              d="M20.4603 17.0395C20.2594 16.953 20.0257 16.9982 19.8708 17.1531L17.9768 19.0472C17.8487 19.1752 17.6312 19.163 17.3663 19.1065C16.7098 18.9659 15.8604 18.446 14.9462 17.5319C14.0321 16.6178 13.5123 15.7684 13.3717 15.1118C13.3152 14.847 13.3029 14.6294 13.431 14.5014L15.325 12.6074C15.48 12.4524 15.5251 12.2188 15.4386 12.0179C15.4386 12.0179 14.9417 10.8581 14.4125 9.6235C14.0622 8.80651 13.3297 8.21646 12.4572 8.04849C11.5843 7.88018 10.6849 8.15589 10.0565 8.78434C9.5269 9.31391 9.11248 9.72833 9.11248 9.72833C8.20182 10.639 7.85232 11.9972 8.05636 13.5664C8.33799 15.7353 9.68695 18.3335 11.9158 20.5624C14.1446 22.7912 16.7429 24.1402 18.9117 24.4218C20.481 24.6258 21.8392 24.2763 22.7498 23.3657C22.7498 23.3657 23.1642 22.9513 23.6938 22.4217C24.3223 21.7932 24.598 20.8939 24.4297 20.021C24.2617 19.1484 23.6716 18.416 22.8547 18.0657C21.6201 17.5364 20.4603 17.0395 20.4603 17.0395ZM20.3719 18.1671L22.4325 19.0504C22.9228 19.2604 23.2769 19.7001 23.3777 20.2236C23.4785 20.7471 23.3129 21.2869 22.936 21.6638L21.992 22.6078C21.2972 23.3026 20.2468 23.5151 19.0495 23.3595C17.0681 23.1025 14.7097 21.8411 12.6734 19.8048C10.6371 17.7684 9.37569 15.41 9.1187 13.4287C8.96305 12.2313 9.1756 11.1809 9.87034 10.4862L10.8143 9.54219C11.1912 9.16528 11.731 8.9997 12.2545 9.10048C12.7781 9.20126 13.2178 9.55535 13.4277 10.0456L14.311 12.1063L12.6735 13.7439C12.3325 14.0848 12.1733 14.6324 12.3238 15.336C12.494 16.1305 13.0827 17.1836 14.1886 18.2895C15.2946 19.3955 16.3477 19.9841 17.1421 20.1543C17.8457 20.3048 18.3933 20.1456 18.7343 19.8047L20.3719 18.1671Z" />
                    </svg>
                    <div class="soc-link__info">
                        <span><?=GetMessage('FOOTER_CALL')?></span>
                    </div>
                </a>
                <a href="mailto:<?=$settings->email?>" class="soc-link">
                    <svg width="32" height="32" viewBox="0 0 32 32" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path fill-rule="evenodd" clip-rule="evenodd"
                              d="M31.0001 16.0004C31.0001 7.72148 24.2789 1.00031 16 1.00031C7.72117 1.00031 1 7.72148 1 16.0004C1 19.6002 2.29404 23.0761 4.64155 25.7971C4.64097 25.7971 3.89212 30.3295 3.89212 30.3295C3.85809 30.5354 3.93828 30.7438 4.10097 30.8741C4.26424 31.0044 4.48463 31.0367 4.67847 30.9581L8.93618 29.2328C11.1083 30.3927 13.5343 31.0004 16 31.0004C24.2789 31.0004 31.0001 24.2792 31.0001 16.0004ZM29.8462 16.0004C29.8462 23.6421 23.642 29.8466 16 29.8466C13.6381 29.8466 11.316 29.2423 9.25349 28.0919C9.1006 28.0064 8.91772 27.9954 8.7556 28.0611L5.19828 29.5024L5.82367 25.7156C5.85252 25.543 5.80117 25.3669 5.68463 25.2366C3.41097 22.6972 2.15385 19.4087 2.15385 16.0004C2.15385 8.35859 8.3581 2.15416 16 2.15416C23.642 2.15416 29.8462 8.35859 29.8462 16.0004Z" />
                        <path d="M8 11.4286H24" stroke-linecap="round" />
                        <path d="M8 16H24" stroke-linecap="round" />
                        <path d="M8 20.5714H18.2857" stroke-linecap="round" />
                    </svg>
                    <div class="soc-link__info">
                        <span><?=GetMessage('FOOTER_WRITE')?></span>
                    </div>
                </a>
                <a href="<?=$settings->whatsapp?>" class="soc-link">
                    <svg width="32" height="32" viewBox="0 0 32 32" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path fill-rule="evenodd" clip-rule="evenodd"
                              d="M4.09478 23.2316L2.09763 29.2236C2.03388 29.416 2.08371 29.6281 2.22728 29.7717C2.37085 29.9152 2.58299 29.9651 2.77531 29.9013L8.76729 27.9036C10.8753 29.1883 13.3514 29.9286 15.9989 29.9286C23.6864 29.9286 29.9275 23.6875 29.9275 16C29.9275 8.3125 23.6864 2.07141 15.9989 2.07141C8.3114 2.07141 2.07031 8.3125 2.07031 16C2.07031 18.6475 2.81067 21.1236 4.09478 23.2316ZM5.19086 23.3318C5.24175 23.1791 5.22139 23.0115 5.13514 22.8754C3.873 20.8868 3.14174 18.5281 3.14174 16C3.14174 8.90393 8.90283 3.14284 15.9989 3.14284C23.095 3.14284 28.8561 8.90393 28.8561 16C28.8561 23.0961 23.095 28.8572 15.9989 28.8572C13.4709 28.8572 11.1121 28.1259 9.12355 26.8638C8.98747 26.7776 8.81979 26.7572 8.66712 26.8081L3.453 28.546L5.19086 23.3318Z" />
                        <path fill-rule="evenodd" clip-rule="evenodd"
                              d="M19.4173 17.6055C19.2164 17.519 18.9828 17.5642 18.8278 17.7191L16.9338 19.6132C16.8058 19.7412 16.5882 19.7289 16.3234 19.6725C15.6668 19.5319 14.8174 19.012 13.9033 18.0979C12.9892 17.1838 12.4693 16.3343 12.3287 15.6778C12.2722 15.413 12.26 15.1954 12.388 15.0674L14.2821 13.1733C14.437 13.0184 14.4822 12.7848 14.3957 12.5839C14.3957 12.5839 13.8988 11.4241 13.3695 10.1895C13.0192 9.37248 12.2867 8.78244 11.4142 8.61447C10.5413 8.44616 9.64195 8.72187 9.0135 9.35032C8.48393 9.87989 8.06951 10.2943 8.06951 10.2943C7.15886 11.205 6.80935 12.5631 7.01339 14.1324C7.29502 16.3013 8.64398 18.8995 10.8728 21.1284C13.1016 23.3572 15.6999 24.7061 17.8688 24.9878C19.438 25.1918 20.7962 24.8423 21.7069 23.9316C21.7069 23.9316 22.1213 23.5172 22.6508 22.9877C23.2793 22.3592 23.555 21.4599 23.3867 20.587C23.2187 19.7144 22.6287 18.9819 21.8117 18.6316C20.5771 18.1024 19.4173 17.6055 19.4173 17.6055ZM19.3289 18.7331L21.3896 19.6164C21.8798 19.8264 22.2339 20.2661 22.3347 20.7896C22.4355 21.3131 22.2699 21.8529 21.893 22.2298L20.949 23.1738C20.2543 23.8685 19.2039 24.0811 18.0065 23.9254C16.0252 23.6684 13.6667 22.407 11.6304 20.3707C9.59411 18.3344 8.33272 15.976 8.07573 13.9947C7.92008 12.7973 8.13263 11.7469 8.82737 11.0522L9.77136 10.1082C10.1483 9.73126 10.688 9.56568 11.2116 9.66646C11.7351 9.76724 12.1748 10.1213 12.3848 10.6116L13.2681 12.6723L11.6305 14.3099C11.2896 14.6508 11.1303 15.1984 11.2809 15.902C11.451 16.6965 12.0397 17.7496 13.1457 18.8555C14.2516 19.9614 15.3047 20.5501 16.0992 20.7203C16.8027 20.8708 17.3504 20.7116 17.6913 20.3707L19.3289 18.7331Z" />
                    </svg>
                    <div class="soc-link__info">
                        <span>WhatsApp</span>
                    </div>
                </a>
                <a href="<?=$settings->telegram?>" class="soc-link">
                    <svg width="32" height="32" viewBox="0 0 32 32" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path fill-rule="evenodd" clip-rule="evenodd"
                              d="M16.0012 1.5769C8.04107 1.5769 1.57812 8.03985 1.57812 16C1.57812 23.9602 8.04107 30.4231 16.0012 30.4231C23.9614 30.4231 30.4244 23.9602 30.4244 16C30.4244 8.03985 23.9614 1.5769 16.0012 1.5769ZM16.0012 2.73075C23.325 2.73075 29.2705 8.67631 29.2705 16C29.2705 23.3237 23.325 29.2693 16.0012 29.2693C8.67753 29.2693 2.73197 23.3237 2.73197 16C2.73197 8.67631 8.67753 2.73075 16.0012 2.73075Z" />
                        <path fill-rule="evenodd" clip-rule="evenodd"
                              d="M17.4644 13.9326C15.3576 16.0999 13.2553 18.2626 12.4456 19.0955C12.0618 19.4904 12.0613 20.1308 12.4456 20.5261L16.5015 24.6985C16.8678 25.0753 17.3998 25.227 17.9024 25.0974C18.405 24.9685 18.8049 24.5774 18.9558 24.0674L22.7918 11.1021C22.952 10.5587 22.8045 9.96923 22.4098 9.57324C22.0144 9.17716 21.4371 9.04115 20.9139 9.22107C17.714 10.3182 11.0826 12.592 7.86417 13.6959C7.33795 13.8763 6.95426 14.3449 6.87179 14.9096C6.78966 15.4738 7.02171 16.038 7.47361 16.3698L10.4723 18.5734C10.8248 18.8323 11.3011 18.8238 11.6443 18.5509L17.4644 13.9326ZM11.0439 17.7502C10.4591 17.321 9.12235 16.3385 8.04452 15.5466C7.89452 15.4359 7.81694 15.2481 7.84441 15.0596C7.87195 14.872 7.99965 14.7151 8.17509 14.6552L21.2249 10.1804C21.3995 10.1204 21.5917 10.1657 21.7235 10.2978C21.8553 10.4298 21.9043 10.6268 21.8507 10.8078L18.0151 23.7727C17.9646 23.9426 17.8312 24.0728 17.6641 24.1162C17.4965 24.1591 17.3188 24.1087 17.1969 23.9832L13.1409 19.8108C13.9506 18.9778 16.0529 16.8152 18.1597 14.6479C18.5265 14.2706 18.545 13.6652 18.2028 13.2645C17.8606 12.8638 17.2744 12.8058 16.8636 13.1315L11.0439 17.7502Z" />
                    </svg>
                    <div class="soc-link__info">
                        <span>Telegram</span>
                    </div>
                </a>
            </div>
            <div class="soc-button">
                <div class="soc-button__icon">
                    <svg width="32" height="27" viewBox="0 0 32 27" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path
                            d="M21 1.23077H11C8.34784 1.23077 5.8043 2.28434 3.92893 4.15971C2.05357 6.03507 1 8.57861 1 11.2308C1 13.8829 2.05357 16.4265 3.92893 18.3018C5.8043 20.1772 8.34784 21.2308 11 21.2308H12V26.2308L18 21.2308H21C23.6522 21.2308 26.1957 20.1772 28.0711 18.3018C29.9464 16.4265 31 13.8829 31 11.2308C31 8.57861 29.9464 6.03507 28.0711 4.15971C26.1957 2.28434 23.6522 1.23077 21 1.23077V1.23077Z"
                            stroke="white" stroke-miterlimit="10" stroke-linecap="round" stroke-linejoin="round" />
                        <path
                            d="M9.60182 12.1641C10.1909 12.1641 10.6685 11.6865 10.6685 11.0974C10.6685 10.5083 10.1909 10.0308 9.60182 10.0308C9.01272 10.0308 8.53516 10.5083 8.53516 11.0974C8.53516 11.6865 9.01272 12.1641 9.60182 12.1641Z"
                            stroke="white" stroke-miterlimit="10" stroke-linecap="round" stroke-linejoin="round" />
                        <path
                            d="M16.0003 12.1642C16.5894 12.1642 17.0669 11.6867 17.0669 11.0976C17.0669 10.5084 16.5894 10.0309 16.0003 10.0309C15.4112 10.0309 14.9336 10.5084 14.9336 11.0976C14.9336 11.6867 15.4112 12.1642 16.0003 12.1642Z"
                            stroke="white" stroke-miterlimit="10" stroke-linecap="round" stroke-linejoin="round" />
                        <path
                            d="M22.3987 12.1642C22.9878 12.1642 23.4654 11.6867 23.4654 11.0976C23.4654 10.5084 22.9878 10.0309 22.3987 10.0309C21.8096 10.0309 21.332 10.5084 21.332 11.0976C21.332 11.6867 21.8096 12.1642 22.3987 12.1642Z"
                            stroke="white" stroke-miterlimit="10" stroke-linecap="round" stroke-linejoin="round" />
                    </svg>
                </div>
                <div class="soc-button__close">
                    <svg width="30" height="30" viewBox="0 0 30 30" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path
                            d="M8.54138 21.9999C8.47054 22.0003 8.40032 21.9868 8.33474 21.96C8.26916 21.9332 8.20951 21.8937 8.15922 21.8438C8.10877 21.7938 8.06872 21.7342 8.0414 21.6686C8.01407 21.603 8 21.5326 8 21.4616C8 21.3905 8.01407 21.3201 8.0414 21.2545C8.06872 21.1889 8.10877 21.1294 8.15922 21.0793L21.0774 8.15827C21.1787 8.05689 21.3162 7.99994 21.4595 7.99994C21.6029 7.99994 21.7403 8.05689 21.8417 8.15827C21.9431 8.25965 22 8.39715 22 8.54052C22 8.68389 21.9431 8.82139 21.8417 8.92277L8.92354 21.8438C8.87325 21.8937 8.8136 21.9332 8.74802 21.96C8.68244 21.9868 8.61222 22.0003 8.54138 21.9999Z"
                            fill="#D9D9D9" />
                        <path
                            d="M21.4586 21.9999C21.3878 22.0003 21.3176 21.9868 21.252 21.96C21.1864 21.9332 21.1268 21.8937 21.0765 21.8438L8.1583 8.92277C8.05694 8.82139 8 8.68389 8 8.54052C8 8.39715 8.05694 8.25965 8.1583 8.15827C8.25965 8.05689 8.39712 7.99994 8.54046 7.99994C8.6838 7.99994 8.82127 8.05689 8.92262 8.15827L21.8408 21.0793C21.8912 21.1294 21.9313 21.1889 21.9586 21.2545C21.9859 21.3201 22 21.3905 22 21.4616C22 21.5326 21.9859 21.603 21.9586 21.6686C21.9313 21.7342 21.8912 21.7938 21.8408 21.8438C21.7905 21.8937 21.7308 21.9332 21.6653 21.96C21.5997 21.9868 21.5295 22.0003 21.4586 21.9999Z"
                            fill="#D9D9D9" />
                        <path fill-rule="evenodd" clip-rule="evenodd"
                              d="M15.0012 0.591736C7.04107 0.591736 0.578125 7.05468 0.578125 15.0149C0.578125 22.975 7.04107 29.438 15.0012 29.438C22.9614 29.438 29.4244 22.975 29.4244 15.0149C29.4244 7.05468 22.9614 0.591736 15.0012 0.591736ZM15.0012 1.74559C22.325 1.74559 28.2705 7.69114 28.2705 15.0149C28.2705 22.3386 22.325 28.2841 15.0012 28.2841C7.67753 28.2841 1.73197 22.3386 1.73197 15.0149C1.73197 7.69114 7.67753 1.74559 15.0012 1.74559Z"
                              fill="#D9D9D9" />
                    </svg>
                </div>
            </div>
        </div>
    </div>
</div>

<?
\Bitrix\Main\Loader::includeModule('iblock');

$iblockId = \Local\Lib\Utils::getIblockIdByCode('ADV_BLOCK');
if ($iblockId)
{
    $obElement = \CIBlockElement::GetList(array('RAND'=>'ASC'),array('IBLOCK_ID'=>$iblockId,'=ACTIVE'=>'Y'), false,array('nTopCount'=>1))->GetNextElement();
    if ($obElement)
    {
        $arElement = $obElement->GetFields();
        $arProperties = $obElement->GetProperties();
        $arElement['PREVIEW_PICTURE'] = \CFile::GetFileArray($arElement['PREVIEW_PICTURE']);

        ?>
        <div class="banner">
            <div class="conatiner">
                <div class="banner-container">
                    <div class="banner-close">
                        <svg width="16" height="16" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M3.5 3.5L12.5 12.5" stroke="#877569" stroke-linecap="round" stroke-linejoin="round" />
                            <path d="M3.5 12.5L12.5 3.5" stroke="#877569" stroke-linecap="round" stroke-linejoin="round" />
                        </svg>
                    </div>
                    <div class="banner-img">
                        <img src="<?=$arElement['PREVIEW_PICTURE']['SRC']?>" alt="">
                    </div>
                    <div class="banner-content">
                        <p class="banner-title"><?=$arElement['NAME']?></p>
                        <p class="banner-description"><?=$arElement['PREVIEW_TEXT']?></p>

                        <div class="banner-button">
                            <div class="button-box">
                                <a class="button" href="<?=$arProperties['URL']['VALUE']?>"><?=GetMessage('FOOTER_MORE')?></a>
                                <svg class="button-bg" width="238" height="68" viewBox="0 0 238 68" fill="none"
                                     xmlns="http://www.w3.org/2000/svg">
                                    <path
                                        d="M63.8598 11.0954C63.8598 11.0954 87.0187 6.81025 136.7 6.81025C166.972 6.81025 237 14.3733 237 37.169C237 61.644 171.494 67 117.487 67C65.1837 67 0.999788 62.4177 1 37.169C1.00032 -0.818731 136.7 1.0142 136.7 1.0142"
                                        stroke-linecap="round" class="button-bg__elem" />
                                </svg>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <?
    }
}
?>

<div class="menu-footer">
    <div class="container">
        <div class="menu-footer-container">
            <a href="<?=SITE_DIR?>catalog/?q=" class="menu-footer-item">
                <div class="menu-footer-item__img">
                    <svg width="30" height="30" viewBox="0 0 30 30" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M17.6484 19.4116L24.7073 26.4704" stroke="#C5A994" stroke-miterlimit="10" stroke-linecap="round"
                              stroke-linejoin="round" />
                        <path
                            d="M12.3509 21.1744C17.224 21.1744 21.1744 17.224 21.1744 12.3509C21.1744 7.47777 17.224 3.52734 12.3509 3.52734C7.47777 3.52734 3.52734 7.47777 3.52734 12.3509C3.52734 17.224 7.47777 21.1744 12.3509 21.1744Z"
                            stroke="#C5A994" stroke-miterlimit="10" stroke-linecap="round" stroke-linejoin="round" />
                    </svg>
                </div>
                <div class="menu-footer-item__text">
                    <span><?=GetMessage('FOOTER_SEARCH')?></span>
                </div>
            </a>
            <a href="<?=SITE_DIR?>personal/favorite/" class="menu-footer-item">
                <div class="menu-footer-item__img">
                    <svg width="30" height="30" viewBox="0 0 30 30" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path
                            d="M15.3318 26.4243C20.5434 25.5862 28.2362 15.6158 28.2362 10.4097C28.2362 6.39511 25.4237 3.52734 21.6186 3.52734C18.3927 3.52734 15.7452 6.13017 15.0009 8.15981C14.2567 6.13017 11.6092 3.52734 8.38327 3.52734C4.57813 3.52734 1.76562 6.39511 1.76562 10.4097C1.76562 15.6158 9.45886 25.5862 14.67 26.4243C14.7791 26.4478 14.8897 26.4626 15.0009 26.4685C15.109 26.439 15.2202 26.4242 15.3318 26.4243V26.4243Z"
                            stroke="#C5A994" stroke-miterlimit="10" stroke-linecap="round" stroke-linejoin="round" />
                    </svg>
                </div>
                <div class="menu-footer-item__text">
                    <span><?=GetMessage('FOOTER_FAVORITE')?></span>
                </div>
            </a>
            <a href="<?=SITE_DIR?>catalog/" class="menu-footer-item">
                <div class="menu-footer-item__img">
                    <svg width="30" height="30" viewBox="0 0 30 30" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path
                            d="M5.49439 3L5.32388 3.27183L1.58976 9.27189L1.5 9.42193V11.1C1.5 12.4941 2.33652 13.6928 3.5106 14.1749V26.4C3.5106 26.7315 3.76786 27 4.08502 27H25.915C26.2321 27 26.4894 26.7315 26.4894 26.4V14.1749C27.6635 13.6928 28.5 12.4939 28.5 11.1V9.42193L28.4102 9.27189L24.6761 3.27183L24.5056 3H5.49416H5.49439ZM6.11369 4.1999H23.8864L27.3513 9.77781V11.0998C27.3513 12.2735 26.4645 13.1997 25.3407 13.1997C24.2169 13.1997 23.3301 12.2735 23.3301 11.0998H22.1812C22.1812 12.2735 21.2944 13.1997 20.1706 13.1997C19.0468 13.1997 18.16 12.2735 18.16 11.0998H17.0112C17.0112 12.2735 16.1244 13.1997 15.0006 13.1997C13.8768 13.1997 12.99 12.2735 12.99 11.0998H11.8411C11.8411 12.2735 10.9543 13.1997 9.83053 13.1997C8.70672 13.1997 7.81992 12.2735 7.81992 11.0998H6.67108C6.67108 12.2735 5.78429 13.1997 4.66048 13.1997C3.53667 13.1997 2.64988 12.2735 2.64988 11.0998V9.77781L6.11369 4.1999ZM7.24473 12.8812C7.81177 13.7702 8.734 14.4001 9.82976 14.4001C10.9255 14.4001 11.8478 13.7704 12.4148 12.8812C12.9818 13.7702 13.904 14.4001 14.9998 14.4001C16.0956 14.4001 17.0178 13.7704 17.5848 12.8812C18.1519 13.7702 19.0741 14.4001 20.1699 14.4001C21.2656 14.4001 22.1879 13.7704 22.7549 12.8812C23.3219 13.7702 24.2441 14.4001 25.3399 14.4001V25.8002H13.8504V16.8001H13.8511C13.8509 16.4686 13.5938 16.2002 13.2764 16.2H7.5317C7.21453 16.2002 6.95728 16.4687 6.95728 16.8001V25.8002H4.65934V14.4001C5.75509 14.4001 6.67738 13.7704 7.24436 12.8812H7.24473ZM16.1491 16.2H16.1489C15.8317 16.2002 15.5744 16.4687 15.5744 16.8001V21.0001C15.5744 21.3313 15.8317 21.6 16.1489 21.6H22.4681C22.7852 21.6 23.0425 21.3313 23.0425 21.0001V16.8001C23.0425 16.4686 22.7852 16.2002 22.4681 16.2H16.1491ZM8.10644 17.3999H12.7023V25.7999H8.10644V17.3999ZM16.7235 17.3999H21.8938V20.3999H16.7235V17.3999Z"
                            fill="#C5A994" />
                    </svg>
                </div>
                <div class="menu-footer-item__text">
                    <span><?=GetMessage('FOOTER_CATALOG')?></span>
                </div>
            </a>
            <button class="menu-footer-item" data-action="showBasket">
                <div class="menu-footer-item__img">
                    <svg width="30" height="30" viewBox="0 0 30 30" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M24.9272 27.4095H5.07422L5.90142 9.21094H24.1L24.9272 27.4095Z" stroke="#C5A994"
                              stroke-miterlimit="10" stroke-linecap="round" stroke-linejoin="round" />
                        <path
                            d="M10.8633 12.5163V6.72587C10.8633 5.62893 11.299 4.57691 12.0747 3.80126C12.8504 3.0256 13.9024 2.58984 14.9993 2.58984C15.5425 2.58984 16.0803 2.69683 16.5821 2.90468C17.0839 3.11254 17.5399 3.41719 17.9239 3.80126C18.308 4.18532 18.6126 4.64128 18.8205 5.14308C19.0284 5.64489 19.1353 6.18272 19.1353 6.72587V12.5163"
                            stroke="#C5A994" stroke-miterlimit="10" stroke-linecap="round" stroke-linejoin="round" />
                    </svg>
                </div>
                <div class="menu-footer-item__text">
                    <span><?=GetMessage('FOOTER_BASKET')?></span>
                </div>
            </button>
            <a href="<?=SITE_DIR?>personal/" class="menu-footer-item">
                <div class="menu-footer-item__img">
                    <svg width="30" height="30" viewBox="0 0 30 30" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path
                            d="M22.6082 22.8581C18.5136 22.0723 18.3068 20.7901 18.3068 19.9629V19.4666C19.4201 18.4999 20.2374 17.2381 20.6643 15.8269H20.7057C21.9465 15.8269 22.2773 13.2212 22.2773 12.8076C22.2773 12.3939 22.3187 10.8636 21.0365 10.8636C23.6836 3.41876 16.4455 0.440821 10.986 4.24597C8.75253 4.24597 8.54573 7.55479 9.37293 10.8636C8.09076 10.8636 8.13212 12.4353 8.13212 12.8076C8.13212 13.1798 8.42165 15.8269 9.70381 15.8269C10.1174 17.3158 10.6551 18.6807 11.6477 19.5493V20.0456C11.6477 20.8728 11.8545 22.1136 7.71852 22.8581C3.58249 23.6026 2.58984 27.4077 2.58984 27.4077H27.406C27.1353 26.265 26.5373 25.226 25.6851 24.4179C24.833 23.6098 23.7637 23.0678 22.6082 22.8581V22.8581Z"
                            stroke="#C5A994" stroke-miterlimit="10" stroke-linecap="round" stroke-linejoin="round" />
                    </svg>
                </div>
                <div class="menu-footer-item__text">
                    <span><?=GetMessage('FOOTER_PROFILE')?></span>
                </div>
            </a>
        </div>
    </div>
</div>

</body>

</html>