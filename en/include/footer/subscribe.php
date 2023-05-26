<?php if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

$settings = \Local\Lib\Settings::getInstance();
?>
<div class="footer-right order-0 order-lg-2">
    <?$APPLICATION->IncludeComponent("bitrix:subscribe.form", "footer", Array(
        "AJAX_MODE" => "N",
        "AJAX_OPTION_ADDITIONAL" => "",
        "AJAX_OPTION_HISTORY" => "N",
        "AJAX_OPTION_JUMP" => "N",
        "AJAX_OPTION_STYLE" => "Y",
        "ALLOW_ANONYMOUS" => "Y",
        "CACHE_NOTES" => "",
        "CACHE_TIME" => "3600000",	// Время кеширования (сек.)
        "CACHE_TYPE" => "A",	// Тип кеширования
        "COMPONENT_TEMPLATE" => "main",
        "LK" => "Y",
        "PAGE" => SITE_DIR."personal/subscribe/",	// Страница редактирования подписки (доступен макрос #SITE_DIR#)
        "SET_TITLE" => "N",
        "SHOW_AUTH_LINKS" => "N",
        "SHOW_HIDDEN" => "N",	// Показать скрытые рубрики подписки
        "URL_SUBSCRIBE" => SITE_DIR."personal/subscribe/",
        "USE_PERSONALIZATION" => "Y",	// Определять подписку текущего пользователя
    ),
        false
    );?>

    <div class="footer-right__soc d-none d-lg-flex">
        <a href="<?=$settings->vk?>">
            <svg width="32" height="32" viewBox="0 0 32 32" fill="none" xmlns="http://www.w3.org/2000/svg">
                <circle cx="16" cy="16" r="14.5" stroke="#877569" />
                <path
                    d="M24.5631 11.7441C25.0005 10.9996 25.0005 10.9996 23.9343 10.9996H21.8558C21.3267 10.9996 21.0835 11.2718 20.9515 11.5728C20.9515 11.5728 19.8942 14.0843 18.3971 15.7161C17.9127 16.1884 17.6932 16.3384 17.4282 16.3384C17.2962 16.3384 17.1043 16.1875 17.1043 15.7587V11.7441C17.1043 11.2292 16.9513 10.9996 16.5106 10.9996H13.2447C12.9141 10.9996 12.7156 11.2385 12.7156 11.4654C12.7156 11.9534 13.4641 12.0664 13.542 13.4407V16.4245C13.542 17.0793 13.4205 17.1978 13.1564 17.1978C12.4525 17.1978 10.7378 14.6743 9.72142 11.7877C9.52193 11.2265 9.32245 10.9996 8.79144 10.9996H6.71299C5.50093 10.9996 6.00058 10.8585 6.00054 11.5728C6.00054 12.1099 6.70444 14.7715 9.28065 18.2925C10.9991 20.6966 13.4176 21.9996 15.6205 21.9996C16.9418 21.9996 17.1052 21.7097 17.1052 21.2115V19.3945C17.1052 18.8157 17.2306 18.6999 17.6486 18.6999C17.9573 18.6999 18.4855 18.85 19.7194 20.0094C21.1282 21.3828 21.3609 21.9996 22.1541 21.9996H24.2326C25.5005 21.9996 25.0005 21.9996 24.9526 21.1383C24.7655 20.5688 24.092 19.7436 23.199 18.7638C22.7146 18.2054 21.9888 17.6044 21.7675 17.3043C21.4597 16.9181 21.5481 16.7459 21.7675 16.4023C21.7675 16.4023 24.2991 12.9249 24.5631 11.7441Z"
                    fill="white" />
                <path
                    d="M24.6954 23H22.5071C21.697 23 21.291 22.5428 20.6159 21.7858C20.3499 21.488 20.0238 21.1217 19.5908 20.6836C18.3966 19.5219 17.8906 19.4048 17.7045 19.4048C17.6975 19.4543 17.6915 19.533 17.6915 19.6561V21.6364C17.6915 22.7679 16.8674 23 15.6283 23C13.032 23 10.3837 21.4083 8.54343 18.7427C5.89311 14.9931 5 11.945 5 11.1304C5 10.4128 5.45606 10.001 6.25015 10.001H8.43842C9.39854 10.001 9.69157 10.6197 9.8906 11.1991C10.8757 14.0968 12.3279 16.2557 12.932 16.6867C12.937 16.6211 12.941 16.5343 12.941 16.4172V13.1652C12.9 12.4294 12.6579 12.1266 12.4439 11.8602C12.2689 11.6422 12.0709 11.3949 12.0709 11.0134C12.0709 10.5269 12.4749 10.001 13.128 10.001H16.5664C17.0805 10.001 17.6915 10.2291 17.6915 11.3172V15.6925C17.6915 15.7127 17.6925 15.7309 17.6935 15.7481C17.8066 15.6693 17.9676 15.521 18.1986 15.2888C19.6778 13.6184 20.7679 10.9649 20.7789 10.9377C21.04 10.322 21.528 10 22.1941 10H24.3824C24.8554 10 25.2005 10.1433 25.4075 10.4239C25.5455 10.6106 25.6775 10.9427 25.5235 11.4615C25.2415 12.7726 22.7862 16.2891 22.5061 16.6857C22.4851 16.721 22.4001 16.8603 22.3891 16.9078C22.3921 16.9078 22.4101 16.9572 22.4931 17.0662C22.5941 17.2065 22.8432 17.4528 23.0842 17.692C23.3822 17.9867 23.7193 18.3208 23.9873 18.6408C25.1675 19.9801 25.7305 20.7825 25.9276 21.4002C26.0916 21.9715 25.9466 22.3308 25.7945 22.5337C25.6395 22.7477 25.3215 23 24.6954 23ZM17.7646 18.3945C18.2166 18.3945 18.8707 18.5812 20.2909 19.963C20.7469 20.4232 21.085 20.8017 21.36 21.1106C22.0141 21.8464 22.1731 21.9907 22.5061 21.9907H24.6944C24.9014 21.9907 24.9854 21.9432 24.9964 21.9301C24.9964 21.9301 25.0214 21.8686 24.9724 21.698C24.7884 21.1207 23.9463 20.1144 23.2332 19.3039C22.9762 18.9971 22.6632 18.6882 22.3851 18.4147C22.0761 18.1099 21.8331 17.8686 21.694 17.6728C21.196 17.0259 21.391 16.5908 21.676 16.1286C22.4171 15.0728 24.3454 12.1832 24.5564 11.2112C24.5854 11.1082 24.5924 11.0517 24.5924 11.0245C24.5744 11.0265 24.5074 11.0113 24.3824 11.0113H22.1941C21.8951 11.0113 21.787 11.1284 21.702 11.3293C21.658 11.4373 20.5399 14.1614 18.9257 15.9832C18.4116 16.503 18.0486 16.83 17.5325 16.83C17.1275 16.83 16.6914 16.4738 16.6914 15.6936V11.3182C16.6914 11.1244 16.6684 11.0426 16.6574 11.0144C16.6464 11.0184 16.6194 11.0123 16.5664 11.0113H13.128C13.079 11.0497 13.165 11.1567 13.222 11.2273C13.485 11.5554 13.8811 12.0509 13.9411 13.139L13.9421 16.4193C13.9421 16.822 13.9421 17.7667 13.036 17.7667C11.7528 17.7667 9.83659 14.1443 8.94648 11.5301C8.76846 11.0123 8.68545 11.0123 8.43942 11.0123H6.25115C6.05513 11.0123 5.99812 11.0507 5.99712 11.0507C6.00112 11.4857 6.6212 14.2845 9.36153 18.1623C11.0177 20.5615 13.359 21.9917 15.6293 21.9917C16.6924 21.9917 16.6924 21.8433 16.6924 21.6374V19.6571C16.6924 19.0324 16.8194 18.3945 17.7646 18.3945Z"
                    fill="#877569" />
            </svg>
        </a>
        <a href="<?=$settings->telegram?>">
            <svg width="32" height="32" viewBox="0 0 32 32" fill="none" xmlns="http://www.w3.org/2000/svg">
                <circle cx="16" cy="16" r="14.5" stroke="#877569" />
                <path d="M13.25 22.875C12.764 22.875 12.847 22.691 12.679 22.229L11.25 17.526L22.25 11L13.25 22.875Z"
                      fill="#E6E2D9" />
                <path
                    d="M13.25 22.8748C13.625 22.8748 13.791 22.7038 14 22.4998L16 20.5548L13.505 19.0508L13.25 22.8748Z"
                    fill="#E6E2D9" />
                <path
                    d="M13.5032 19.0507L19.5482 23.5167C20.2382 23.8967 20.7362 23.6997 20.9072 22.8757L23.3682 11.2807C23.6202 10.2707 22.9832 9.8127 22.3232 10.1117L7.87423 15.6847C6.88823 16.0807 6.89523 16.6307 7.69423 16.8757L11.4022 18.0327L19.9862 12.6167C20.3912 12.3707 20.7632 12.5027 20.4592 12.7737L13.5032 19.0507Z"
                    fill="white" />
                <path
                    d="M19.5522 24.0172C19.4412 24.0172 19.3282 23.9802 19.2362 23.9042L13.4732 19.1832C13.3602 19.0912 13.2932 18.9542 13.2892 18.8082C13.2862 18.6622 13.3462 18.5232 13.4542 18.4252L17.7312 14.5652C17.9362 14.3782 18.2522 14.3962 18.4372 14.6012C18.6232 14.8062 18.6062 15.1222 18.4012 15.3072L14.5552 18.7772L19.8682 23.1302C20.0822 23.3052 20.1132 23.6202 19.9382 23.8342C19.8402 23.9542 19.6962 24.0172 19.5522 24.0172Z"
                    fill="#877569" />
                <path
                    d="M20.157 24.2036C19.892 24.2036 19.607 24.1206 19.308 23.9556L16.022 21.3146L14.338 22.8676C14.104 23.0966 13.818 23.3746 13.249 23.3746C12.549 23.3746 12.386 23.0056 12.279 22.6266C12.26 22.5616 12.239 22.4856 12.208 22.3986L11.002 18.4296L7.54603 17.3516C6.73203 17.1016 6.62303 16.6026 6.61403 16.4006C6.60003 16.0646 6.77503 15.5856 7.68803 15.2196L22.536 9.49058C22.961 9.32458 23.46 9.45858 23.748 9.81358C23.951 10.0626 24.029 10.3866 23.963 10.7026L21.398 22.9776C21.168 24.0836 20.453 24.2036 20.157 24.2036ZM16 20.1546C16.111 20.1546 16.222 20.1916 16.313 20.2656L19.863 23.1276C20.322 23.3466 20.363 23.0426 20.42 22.7736L22.985 10.4996L22.922 10.4186L8.05603 16.1506C7.88103 16.2206 7.76603 16.2896 7.69603 16.3406C7.73303 16.3586 7.78203 16.3776 7.84303 16.3966L11.554 17.5546C11.711 17.6046 11.835 17.7286 11.883 17.8866L13.157 22.0826C13.19 22.1706 13.218 22.2706 13.242 22.3546C13.244 22.3606 13.246 22.3676 13.247 22.3736C13.38 22.3726 13.45 22.3366 13.635 22.1556L15.661 20.2856C15.757 20.1996 15.878 20.1546 16 20.1546Z"
                    fill="#877569" />
            </svg>
        </a>
        <a href="<?=$settings->whatsapp?>">
            <svg width="32" height="32" viewBox="0 0 32 32" fill="none" xmlns="http://www.w3.org/2000/svg">
                <path
                    d="M16.004 1.5H15.996C8.002 1.5 1.5 8.004 1.5 16C1.5 19.171 2.522 22.112 4.261 24.499L2.454 29.887L8.027 28.105C10.32 29.623 13.055 30.501 16.004 30.501C23.998 30.5 30.5 23.996 30.5 16C30.5 8.004 23.998 1.5 16.004 1.5Z" />
                <path
                    d="M16.004 31C13.136 31 10.358 30.189 7.954 28.653L2.606 30.362C2.427 30.419 2.23 30.371 2.097 30.237C1.965 30.103 1.919 29.905 1.98 29.727L3.705 24.581C1.935 22.061 1 19.1 1 16C1 11.924 2.612 8.109 5.539 5.258C6.30485 4.51203 7 4 7 4L7.98532 4.51133C7.98532 4.51133 6.80182 5.42248 6.236 5.974C3.505 8.636 2 12.196 2 16C2 18.974 2.922 21.811 4.665 24.204C4.76 24.335 4.786 24.504 4.734 24.658L3.242 29.11L7.875 27.629C8.019 27.582 8.177 27.605 8.304 27.688C10.589 29.2 13.252 30 16.004 30C23.722 30 30 23.72 30 16C30 8.281 23.722 2 16.004 2H16.001C13.834 2 11.763 2.481 9.837 3.429C9.25087 3.69761 7.98532 4.51133 7.98532 4.51133L7 4C7 4 7.5 3.5 9.396 2.532C11.4449 1.48593 13.682 1 15.996 1C24.272 1 31 7.729 31 16C31 24.271 24.272 31 16.004 31Z"
                    fill="#877569" />
                <path
                    d="M23.7161 19.3439C23.2901 19.1309 21.2191 18.1079 20.8271 17.9709C20.4431 17.8259 20.0771 17.8779 19.7881 18.2869C19.3781 18.8579 18.9781 19.4379 18.6541 19.7869C18.3981 20.0599 17.9801 20.0939 17.6321 19.9489C17.1631 19.7529 15.8511 19.2919 14.2321 17.8519C12.9791 16.7359 12.1271 15.3459 11.8791 14.9289C11.6321 14.5029 11.8541 14.2549 12.0501 14.0249C12.2631 13.7609 12.4671 13.5729 12.6801 13.3259C12.8941 13.0779 13.0131 12.9509 13.1501 12.6609C13.2951 12.3799 13.1921 12.0899 13.0891 11.8759C12.9871 11.6629 12.1351 9.58286 11.7851 8.73886C11.5041 8.06586 11.2911 8.03986 10.8651 8.02286C10.7191 8.01486 10.5581 8.00586 10.3791 8.00586C9.82506 8.00586 9.24606 8.16786 8.89706 8.52586C8.47006 8.96086 7.41406 9.97486 7.41406 12.0549C7.41406 14.1349 8.93106 16.1469 9.13606 16.4289C9.34906 16.7099 12.0931 21.0399 16.3541 22.8049C19.6861 24.1859 20.6751 24.0579 21.4331 23.8959C22.5411 23.6569 23.9301 22.8379 24.2801 21.8499C24.6301 20.8609 24.6301 20.0169 24.5271 19.8379C24.4231 19.6599 24.1421 19.5579 23.7161 19.3439Z"
                    fill="white" />
                <path
                    d="M20.6011 24.4928C19.5901 24.4928 18.1791 24.1028 16.1621 23.2668C13.5291 22.1768 10.9191 19.8928 8.81206 16.8378L8.73706 16.7308C8.04106 15.7798 6.91406 13.9578 6.91406 12.0558C6.91406 9.82684 8.02906 8.69584 8.50606 8.21284C8.95506 7.75184 9.62606 7.50684 10.3801 7.50684C10.5701 7.50684 10.7401 7.51584 10.8951 7.52484C11.5301 7.54984 11.8981 7.70984 12.2481 8.54684L12.6111 9.42684C12.9951 10.3578 13.4681 11.5068 13.5421 11.6618C13.6241 11.8308 13.8731 12.3498 13.5961 12.8898C13.4481 13.2058 13.3031 13.3728 13.1041 13.6028C12.9641 13.7638 12.8711 13.8638 12.7761 13.9638C12.6661 14.0818 12.5541 14.1978 12.4421 14.3388C12.2491 14.5648 12.2491 14.5648 12.3141 14.6778C12.6841 15.3028 13.4711 16.5028 14.5671 17.4778C15.9891 18.7428 17.1381 19.2078 17.6901 19.4318L17.8271 19.4878C17.9721 19.5478 18.1551 19.5908 18.2921 19.4458C18.5401 19.1788 18.8541 18.7398 19.1861 18.2748L19.3851 17.9958C19.7341 17.5028 20.1641 17.3988 20.4631 17.3988C20.6381 17.3988 20.8201 17.4338 21.0061 17.5038C21.4711 17.6658 23.9181 18.8848 23.9431 18.8968L24.1781 19.0118C24.5281 19.1798 24.8041 19.3128 24.9621 19.5908C25.1911 19.9888 25.1011 21.0328 24.7531 22.0178C24.3361 23.1968 22.7861 24.1178 21.5401 24.3858C21.2921 24.4378 20.9971 24.4928 20.6011 24.4928ZM10.3791 8.50684C9.90706 8.50684 9.47706 8.64884 9.25506 8.87584C8.79406 9.34384 7.91306 10.2368 7.91306 12.0558C7.91306 13.2368 8.49806 14.7138 9.51806 16.1068L9.63406 16.2718C11.6321 19.1698 14.0861 21.3268 16.5431 22.3438C18.4361 23.1288 19.7251 23.4938 20.6001 23.4938C20.9001 23.4938 21.1231 23.4518 21.3271 23.4088C22.3001 23.1988 23.5251 22.4798 23.8061 21.6848C24.1101 20.8278 24.1041 20.1688 24.0681 20.0378C24.0661 20.0688 23.8861 19.9838 23.7411 19.9138L23.4921 19.7918C22.7911 19.4408 20.9791 18.5558 20.6621 18.4448C20.5781 18.4128 20.5141 18.3998 20.4601 18.3998C20.4151 18.3998 20.3221 18.3998 20.1961 18.5778L19.9961 18.8578C19.6451 19.3498 19.3141 19.8138 19.0201 20.1298C18.6431 20.5328 17.9961 20.6448 17.4391 20.4128L17.3121 20.3608C16.7551 20.1358 15.4501 19.6078 13.8991 18.2278C12.7031 17.1618 11.8501 15.8648 11.4481 15.1858C11.0251 14.4568 11.4541 13.9548 11.6601 13.7138C11.7911 13.5508 11.9191 13.4158 12.0471 13.2798C12.1311 13.1908 12.2151 13.1018 12.3011 13.0018C12.5261 12.7428 12.6001 12.6568 12.6971 12.4498C12.7261 12.3928 12.7421 12.3118 12.6381 12.0958C12.5621 11.9358 12.0761 10.7618 11.6851 9.80984L11.3231 8.93284C11.1581 8.53684 11.1581 8.53684 10.8441 8.52484C10.7001 8.51484 10.5481 8.50684 10.3791 8.50684Z"
                    fill="#877569" />
            </svg>
        </a>
    </div>
</div>
