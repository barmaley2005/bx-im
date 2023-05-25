<?php

global $USER;

if (!$USER->IsAuthorized())
    return;

\Bitrix\Main\Loader::includeModule('currency');
\Bitrix\Main\Loader::includeModule('sale');
\Bitrix\Main\Loader::includeModule('logictim.balls');

use \Bitrix\Sale\Discount\CumulativeCalculator;

$balance = Logictim\Balls\Helpers::UserBallance($USER->GetID());

$calculator = new CumulativeCalculator($USER->GetID(), SITE_ID);
$sumConfiguration = array(
    'type_sum_period'=> CumulativeCalculator::TYPE_COUNT_PERIOD_RELATIVE,
    'sum_period_data' => array('period_value' => 1, 'period_type' => 'Y')
);
$calculator->setSumConfiguration($sumConfiguration);
$ordersSum = $calculator->calculate();

$arBonusInfo = array(
    'level' => 1,
);

if ($ordersSum>=50000 && $ordersSum<=99999)
{
    $arBonusInfo['level'] = 2;
} elseif ($ordersSum>=100000)
{
    $arBonusInfo['level'] = 3;
}

switch ($arBonusInfo['level'])
{
    case 1:
        $arBonusInfo['levelText'] = 'Первый';
        $arBonusInfo['writeOff'] = 'до 30%';
        $arBonusInfo['cashback'] = '5%';
        $arBonusInfo['birthdayBonus'] = '500';
        $arBonusInfo['accessSale'] = 'на 1 день раньше';
        $arBonusInfo['nextLevel'] = CCurrencyLang::CurrencyFormat(50000-$ordersSum, 'RUB');
        break;
    case 2:
        $arBonusInfo['levelText'] = 'Второй';
        $arBonusInfo['writeOff'] = 'до 35%';
        $arBonusInfo['cashback'] = '7%';
        $arBonusInfo['birthdayBonus'] = '1000';
        $arBonusInfo['accessSale'] = 'на 2 дня раньше';
        $arBonusInfo['nextLevel'] = CCurrencyLang::CurrencyFormat(100000-$ordersSum, 'RUB');
        break;
    case 3:
        $arBonusInfo['levelText'] = 'Третий';
        $arBonusInfo['writeOff'] = 'до 40%';
        $arBonusInfo['cashback'] = '10%';
        $arBonusInfo['birthdayBonus'] = '1500';
        $arBonusInfo['accessSale'] = 'на 3 дня раньше';
        $arBonusInfo['nextLevel'] = '&infin;';
        break;
}

?>
<div class="account-block__pages">
    <div id="account-bonus" class="account-block__page account-page _active">
        <a href="<?=SITE_DIR?>personal/" class="account-page__back">
            <svg class="account-block__back-icon width=" 8" height="14" viewBox="0 0 8 14" fill="none"
            xmlns="http://www.w3.org/2000/svg">
            <path d="M7 13L1 7L7 1" stroke="#877569" stroke-linecap="round" />
            </svg>
        </a>
        <h2 class="account-page__title">Бонусные баллы</h2>
        <div class="account-bonus">
            <div class="account-bonus__row">
                <div class="account-bonus__balance">
                    <svg class="account-bonus__icon" width="25" height="23" viewBox="0 0 25 23" fill="none"
                         xmlns="http://www.w3.org/2000/svg">
                        <path
                            d="M23.981 2.54452C23.5415 1.54843 22.7407 0.776943 21.7251 0.376552C20.7095 -0.0238383 19.5962 0.00545859 18.6001 0.444912C18.1313 0.64999 17.7114 0.942959 17.3501 1.30429C17.1646 1.48983 17.1646 1.80233 17.3501 1.98788C17.5356 2.17343 17.8481 2.17343 18.0337 1.98788C18.3071 1.71444 18.6294 1.48983 18.9907 1.33358C19.7524 1.00155 20.602 0.982021 21.3833 1.28476C22.1645 1.58749 22.77 2.17343 23.1118 2.93515C23.8052 4.51718 23.0825 6.36288 21.5103 7.05624C21.1489 7.21249 20.7681 7.30038 20.3774 7.31991C20.1138 7.32968 19.9087 7.55429 19.9185 7.81796C19.9282 8.08163 20.1431 8.28671 20.397 8.28671C20.4067 8.28671 20.4067 8.28671 20.4165 8.28671C20.9243 8.26718 21.4224 8.14999 21.9009 7.94491C23.9419 7.02694 24.8892 4.60507 23.981 2.54452Z"
                            fill="#877569" />
                        <path
                            d="M13.9498 10.1031C14.4283 10.2887 14.9361 10.3863 15.4342 10.3863C15.9908 10.3863 16.5475 10.2691 17.0748 10.0348C18.0709 9.5953 18.8424 8.79452 19.2428 7.7789C19.6432 6.76327 19.6139 5.64999 19.1744 4.6539C18.735 3.6578 17.9342 2.88632 16.9185 2.48593C15.9029 2.08554 14.7896 2.11483 13.7935 2.55429C12.7975 2.99374 12.026 3.79452 11.6256 4.81015C11.2252 5.82577 11.2545 6.93905 11.6939 7.93515C12.1334 8.94101 12.9342 9.70272 13.9498 10.1031ZM12.524 5.16171C12.8267 4.38046 13.4127 3.77499 14.1744 3.43319C14.5748 3.25741 15.0045 3.16952 15.4244 3.16952C15.8053 3.16952 16.1959 3.23788 16.5572 3.38436C17.3385 3.6871 17.9439 4.27304 18.2857 5.03476C18.6178 5.79647 18.6373 6.64608 18.3346 7.42733C18.0318 8.20858 17.4459 8.81405 16.6842 9.15585C15.9225 9.48788 15.0728 9.50741 14.2916 9.20468C13.5103 8.90194 12.9049 8.31601 12.5631 7.55429C12.2408 6.7828 12.2213 5.94296 12.524 5.16171Z"
                            fill="#877569" />
                        <path
                            d="M8.17435 7.34918C8.23294 7.34918 8.29154 7.33941 8.34037 7.31988C8.90677 7.10504 9.49271 6.94879 10.0982 6.86089C10.3619 6.82183 10.5376 6.57769 10.4986 6.31402C10.4595 6.05035 10.2154 5.87457 9.95169 5.91363C9.28763 6.01129 8.63334 6.18707 7.99857 6.42144C7.75443 6.5191 7.62748 6.79254 7.71537 7.03668C7.79349 7.22222 7.97904 7.34918 8.17435 7.34918Z"
                            fill="#877569" />
                        <path
                            d="M20.6777 11.9395C20.4824 11.1973 20.1504 10.4746 19.7012 9.80078C19.5547 9.57617 19.252 9.51758 19.0371 9.66406C18.8125 9.81055 18.7539 10.1133 18.9004 10.3281C19.291 10.9141 19.5742 11.5391 19.75 12.1836C19.7988 12.3594 19.8379 12.5352 19.8672 12.7109C20.2285 14.7227 19.584 16.832 18.1973 18.2285C18.0313 18.4043 17.9043 18.6191 17.8555 18.834L16.957 21.9395H14.9453L14.7109 21.0215C14.6523 20.8066 14.4668 20.6602 14.2422 20.6602L9.0957 20.6406C8.88086 20.6406 8.68555 20.7871 8.62695 21.002L8.38281 21.9395H6.38086L5.80469 19.9082C5.6875 19.5078 5.38477 19.1465 5.01367 18.9902C3.79297 18.4336 2.70898 17.7891 1.77148 17.0859C1.69336 17.0273 1.64453 16.9297 1.64453 16.8223L1.6543 13.0234L2.17188 13.0137C2.31836 13.0137 2.45508 12.9355 2.55273 12.8184L4.61328 10.084C4.62305 10.0645 4.64258 10.0449 4.65234 10.0254C4.7793 9.80078 4.69141 9.58594 4.59375 9.38086C4.57422 9.32227 4.54492 9.26367 4.52539 9.20508L4.48633 9.11719C4.4082 8.93164 4.32031 8.74609 4.23242 8.57031C4.07617 8.23828 3.90039 7.90625 3.72461 7.58398C3.70508 7.54492 3.68555 7.51563 3.66602 7.47656C3.69531 7.47656 3.72461 7.47656 3.76367 7.47656C3.77344 7.47656 3.7832 7.47656 3.80273 7.47656C4.29102 7.47656 4.74023 7.54492 5.15039 7.69141C5.26758 7.73047 5.51172 7.84766 5.57031 7.87695C5.57031 7.87695 5.58008 7.87695 5.58008 7.88672C5.66797 7.93555 6.18555 8.23828 6.30273 8.32617C6.82031 8.7168 7.25977 9.23438 7.55274 9.83008C7.66992 10.0645 7.96289 10.1621 8.19727 10.0449C8.43164 9.92773 8.5293 9.63477 8.41211 9.40039C8.05078 8.67773 7.52344 8.04297 6.87891 7.55469C6.71289 7.42773 6.20508 7.13477 6.06836 7.05664C5.94141 6.96875 5.75586 6.88086 5.46289 6.7832C4.96484 6.60742 4.42773 6.51953 3.8418 6.51953C3.72461 6.51953 3.60742 6.51953 3.50977 6.5293L3.32422 6.53906C3.29492 6.53906 3.29492 6.53906 3.26563 6.54883C3.24609 6.54883 3.22656 6.55859 3.20703 6.55859C2.95312 6.61719 2.73828 6.79297 2.63086 7.04688C2.5332 7.30078 2.64063 7.54492 2.67969 7.65234C2.70898 7.75 2.75781 7.83789 2.82617 7.95508C2.82617 7.96484 2.8457 7.98437 2.85547 7.99414L2.88477 8.04297C3.05078 8.35547 3.2168 8.66797 3.37305 8.99023C3.45117 9.16602 3.53906 9.33203 3.60742 9.49805L3.64648 9.5957C3.66602 9.63477 3.68555 9.68359 3.69531 9.72266L1.9375 12.0566L1.17578 12.0664C0.912109 12.0664 0.707031 12.2812 0.707031 12.5449L0.6875 16.832C0.6875 17.2422 0.873047 17.623 1.19531 17.8672C2.18164 18.6191 3.33398 19.293 4.62305 19.8887C4.74023 19.9375 4.84766 20.0645 4.87695 20.1914L5.56055 22.5645C5.61914 22.7695 5.80469 22.916 6.01953 22.916H8.76367C8.97852 22.916 9.17383 22.7695 9.23242 22.5547L9.47656 21.6172L13.8711 21.6367L14.1055 22.5547C14.1641 22.7695 14.3496 22.916 14.5742 22.916H17.3281C17.543 22.916 17.7285 22.7695 17.7871 22.5645L18.793 19.0879C18.8125 19.0293 18.8418 18.9609 18.8906 18.9121C20.4922 17.3105 21.2344 14.8691 20.8242 12.5547C20.7852 12.3496 20.7363 12.1445 20.6777 11.9395Z"
                            fill="#877569" />
                        <path
                            d="M6.26172 12.3301C6.26172 12.75 5.91016 13.1016 5.49023 13.1016C5.07031 13.1016 4.71875 12.75 4.71875 12.3301C4.71875 11.9102 5.07031 11.5586 5.49023 11.5586C5.91016 11.5586 6.26172 11.9199 6.26172 12.3301Z"
                            fill="#877569" />
                    </svg>
                    <span class="account-bonus__label-head">Ваш баланс:</span>
                    <span class="account-bonus__value-head"><?=$balance?> б</span>
                </div>
                <div class="account-bonus__logo">
                    <spap class="account-bonus__logo-text"> ORENSHAL CLUB</spap>
                    <svg class="account-bonus__logo-icon" width="18" height="15" viewBox="0 0 18 15" fill="none"
                         xmlns="http://www.w3.org/2000/svg">
                        <g clip-path="url(#clip0_507_22722)">
                            <path
                                d="M11.9948 13.5383C14.1267 13.1955 17.2736 9.11691 17.2736 6.98724C17.2736 5.34499 16.1231 4.17187 14.5666 4.17187C13.2469 4.17187 12.1639 5.23661 11.8595 6.06688C11.555 5.23661 10.472 4.17188 9.15239 4.17188C7.59582 4.17188 6.44531 5.34499 6.44531 6.98724C6.44531 9.11691 9.59239 13.1955 11.7241 13.5383C11.7688 13.548 11.814 13.554 11.8595 13.5564C11.9037 13.5444 11.9492 13.5383 11.9948 13.5383V13.5383Z"
                                stroke="#D2C7BC" stroke-miterlimit="10" stroke-linecap="round" stroke-linejoin="round" />
                        </g>
                        <g clip-path="url(#clip1_507_22722)">
                            <path
                                d="M8.61868 13.2135C11.2244 12.7945 15.0706 7.80955 15.0706 5.20663C15.0706 3.19943 13.6644 1.76562 11.7619 1.76562C10.149 1.76562 8.82536 3.06697 8.45325 4.08174C8.08113 3.06697 6.75745 1.76562 5.14459 1.76562C3.24212 1.76562 1.83594 3.19943 1.83594 5.20663C1.83594 7.80955 5.68236 12.7945 8.28782 13.2135C8.34236 13.2253 8.39766 13.2327 8.45325 13.2356C8.50731 13.2209 8.56289 13.2135 8.61868 13.2135V13.2135Z"
                                stroke="#877569" stroke-miterlimit="10" stroke-linecap="round" stroke-linejoin="round" />
                        </g>
                        <defs>
                            <clipPath id="clip0_507_22722">
                                <rect width="12.2721" height="12.2721" fill="white" transform="translate(5.72656 2.72656)" />
                            </clipPath>
                            <clipPath id="clip1_507_22722">
                                <rect width="14.9992" height="14.9992" fill="white" transform="translate(0.953125)" />
                            </clipPath>
                        </defs>
                    </svg>
                </div>
            </div>
            <div class="account-bonus__row">
                <div class="account-bonus__column account-bonus__column_position_left">
                    <span class="account-bonus__label">Ваш уровень:</span>
                    <span class="account-bonus__value"><?=$arBonusInfo['levelText']?></span>
                </div>
                <div class="account-bonus__column account-bonus__column_position_right">
                    <span class="account-bonus__label">Списание бонусов:</span>
                    <span class="account-bonus__value"><?=$arBonusInfo['writeOff']?></span>
                </div>
                <div class="account-bonus__column account-bonus__column_position_left">
                    <span class="account-bonus__label">Кэшбек:</span>
                    <span class="account-bonus__value"><?=$arBonusInfo['cashback']?></span>
                </div>
                <div class="account-bonus__column account-bonus__column_position_right">
                    <span class="account-bonus__label">Бонусы ко Дню Рождения:</span>
                    <span class="account-bonus__value"><?=$arBonusInfo['birthdayBonus']?> бонусов</span>
                </div>
                <div class="account-bonus__column account-bonus__column_position_left">
                    <span class="account-bonus__label">Сумма покупок в год:</span>
                    <span class="account-bonus__value"><?=CCurrencyLang::CurrencyFormat($ordersSum, 'RUB', true)?></span>
                </div>
                <div class="account-bonus__column account-bonus__column_position_right">
                    <span class="account-bonus__label">Доступ к распродаже:</span>
                    <span class="account-bonus__value"><?=$arBonusInfo['accessSale']?></span>
                </div>
            </div>
            <div class="account-bonus__row">
                <div class="account-bonus__column account-bonus__column_position_left">
                    <span class="account-bonus__label">До следующего уровня:</span>
                    <span class="account-bonus__value"><?=$arBonusInfo['nextLevel']?></span>
                </div>
                <a href="<?=SITE_DIR?>customers/bonus/" class="account-bonus__rulse">
                    <span>Правила бонусной программы</span>
                    <svg width="17" height="12" viewBox="0 0 17 12" stroke="currentColor" stroke
                         xmlns="http://www.w3.org/2000/svg">
                        <path d="M16 6L-2.98023e-07 6M16 6L9.70667 1M16 6L9.70667 11" stroke="inhiret"
                              stroke-width="0.75" />
                    </svg>
                </a>
            </div>
        </div>
        <?$APPLICATION->IncludeComponent(
            "logictim:bonus.history",
            "main",
            Array(
                "FIELDS" => array("ID", "DATE", "NAME", "OPERATION_SUM", "BALLANCE_BEFORE", "BALLANCE_AFTER"),
                "OPERATIONS_WAIT" => "Y",
                "ORDER_LINK" => "N",
                "ORDER_URL" => "/personal/bonus/",
                "PAGE_NAVIG_LIST" => "30",
                "PAGE_NAVIG_TEMP" => "arrows",
                "SORT" => "DESC"
            )
        );?>
    </div>
</div>

