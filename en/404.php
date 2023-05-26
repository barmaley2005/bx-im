<?
include_once($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/main/include/urlrewrite.php');

CHTTP::SetStatus("404 Not Found");
@define("ERROR_404","Y");
define("HIDE_SIDEBAR", true);

require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");

$APPLICATION->SetPageProperty('NOT_SHOW_NAV_CHAIN', 'Y');

$APPLICATION->SetTitle("Страница не найдена");?>
    <section class="oops">
        <div class="container">
            <div class="oops-container">
                <h2 class="oops-title">Упс! Страница не найдена</h2>

                <div class="oops-row">
                    <div class="oops-col order-1 order-md-0">
                        <a href="<?=SITE_DIR?>" class="oops-link">
                            <svg width="22" height="14" viewBox="0 0 22 14" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <path d="M1 7L21 7M1 7L8.86667 1M1 7L8.86667 13" stroke-linecap="round" />
                            </svg>
                            На главную
                        </a>
                    </div>
                    <div class="oops-col order-0 order-md-1">
                        <div class="oops-img">
                            <img src="<?=SITE_TEMPLATE_PATH?>/img/oops/img-1.png" alt="">
                        </div>
                    </div>
                    <div class="oops-col order-2">
                        <a href="<?=SITE_DIR?>catalog/" class="oops-link">
                            В каталог
                            <svg width="22" height="14" viewBox="0 0 22 14" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <path d="M21 7H1M21 7L13.1333 13M21 7L13.1333 1" stroke-linecap="round" />
                            </svg>
                        </a>
                    </div>
                </div>

            </div>
        </div>
    </section>
<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>