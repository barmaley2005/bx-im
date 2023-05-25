<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

use Bitrix\Main\Localization\Loc;


if ($arParams["MAIN_CHAIN_NAME"] <> '')
{
	$APPLICATION->AddChainItem(htmlspecialcharsbx($arParams["MAIN_CHAIN_NAME"]), $arResult['SEF_FOLDER']);
}

?>
<div class="account-block__pages">
	<div id="account-personal" class="account-block__page account-page">
		<h2 class="account-page__title">
			<span><?=GetMessage('SPS_TITLE_MAIN')?></span>
		</h2>
		<menu class="account-page__list">
			<li class="account-page__item">
				<a href="<?=SITE_DIR?>personal/orders/" class="account-page__link">
					<svg class="account-page__icon" width="57" height="60" viewBox="0 0 57 60" fill="inhiret"
						 xmlns="http://www.w3.org/2000/svg">
						<path
							d="M56.672 1.26227C56.672 1.24397 56.672 1.22568 56.6537 1.20739C56.6537 1.1891 56.6354 1.15251 56.6354 1.13422C56.6354 1.11592 56.6171 1.09763 56.6171 1.07934C56.6171 1.06105 56.5988 1.02446 56.5988 1.00617C56.5988 0.987876 56.5805 0.969583 56.5805 0.95129C56.5805 0.932997 56.5622 0.914706 56.5439 0.878121C56.5439 0.859828 56.5256 0.841535 56.5256 0.823242C56.5073 0.804949 56.5073 0.786656 56.489 0.768363C56.4707 0.750071 56.4708 0.731779 56.4525 0.713486C56.4342 0.695193 56.4342 0.6769 56.4159 0.658607C56.3976 0.640315 56.3793 0.622023 56.3793 0.60373C56.361 0.585438 56.361 0.567144 56.3427 0.548852C56.3244 0.530559 56.3061 0.512266 56.2878 0.493973C56.2695 0.47568 56.2512 0.457389 56.2329 0.439096C56.2147 0.420803 56.1964 0.40251 56.1781 0.40251C56.1598 0.384217 56.1415 0.365925 56.1232 0.347632C56.1049 0.347632 56.1049 0.32934 56.0866 0.32934C56.0866 0.32934 56.0683 0.329339 56.0683 0.311046C56.05 0.292754 56.0317 0.274461 55.9951 0.274461C55.9768 0.256169 55.9586 0.256169 55.9403 0.237877C55.922 0.219584 55.8854 0.219583 55.8671 0.20129C55.8488 0.20129 55.8305 0.182998 55.8122 0.182998C55.7939 0.164705 55.7756 0.164706 55.739 0.164706C55.7208 0.164706 55.7025 0.146413 55.6842 0.146413C55.6659 0.146413 55.6476 0.128119 55.6293 0.128119C55.611 0.128119 55.5744 0.109827 55.5561 0.109827C55.5378 0.109827 55.5195 0.109827 55.5012 0.0915346C55.4829 0.0915346 55.4464 0.0915349 55.4281 0.0732422C55.4098 0.0732422 55.3915 0.0732422 55.3732 0.0732422C55.3549 0.0732422 55.3183 0.0732422 55.3 0.0732422H55.2817H1.40977H1.39148C1.37319 0.0732422 1.3366 0.0732422 1.31831 0.0732422C1.30002 0.0732422 1.28173 0.0732422 1.26343 0.0732422C1.24514 0.0732422 1.20856 0.0732419 1.19026 0.0915346C1.17197 0.0915346 1.15368 0.0915343 1.13538 0.109827C1.11709 0.109827 1.0988 0.128119 1.06222 0.128119C1.04392 0.128119 1.02563 0.146413 1.00734 0.146413C0.989044 0.146413 0.970751 0.164706 0.952458 0.164706C0.934165 0.164706 0.897581 0.182998 0.879288 0.182998C0.860996 0.182998 0.842702 0.20129 0.82441 0.20129C0.806117 0.219583 0.769531 0.219584 0.751239 0.237877C0.732946 0.237877 0.714654 0.256169 0.696361 0.274461C0.678069 0.292754 0.641483 0.311046 0.62319 0.311046C0.62319 0.311046 0.604898 0.311047 0.604898 0.32934C0.586605 0.32934 0.586604 0.347632 0.568312 0.347632C0.550019 0.365925 0.531727 0.384217 0.513435 0.40251C0.495142 0.420802 0.476849 0.439096 0.458556 0.439096C0.440263 0.457389 0.42197 0.47568 0.403677 0.493973C0.385385 0.512266 0.367093 0.530559 0.3488 0.548852C0.330507 0.567144 0.330507 0.585438 0.312214 0.60373C0.293921 0.622023 0.275629 0.640315 0.275629 0.658607C0.257336 0.6769 0.257337 0.695193 0.239044 0.713486C0.220752 0.731779 0.220751 0.750071 0.202458 0.768363C0.184165 0.786656 0.184166 0.804949 0.165873 0.823242C0.147581 0.841535 0.147581 0.859828 0.147581 0.878121C0.129288 0.896413 0.129287 0.914705 0.110995 0.95129C0.110995 0.969583 0.0927023 0.987876 0.0927023 1.00617C0.0927023 1.02446 0.0744099 1.06105 0.0744099 1.07934C0.0744099 1.09763 0.0561175 1.11592 0.0561175 1.13422C0.0561175 1.15251 0.0378237 1.1891 0.0378237 1.20739C0.0378237 1.22568 0.0378239 1.24397 0.0195312 1.26227C0.0195312 1.28056 0.0195312 1.29885 0.0195312 1.33544C0.0195312 1.35373 0.0195312 1.39031 0.0195312 1.40861C0.0195312 1.4269 0.0195312 1.4269 0.0195312 1.44519V58.555C0.0195312 59.305 0.641483 59.9269 1.39148 59.9269H55.3C56.05 59.9269 56.672 59.305 56.672 58.555V1.44519C56.672 1.4269 56.672 1.4269 56.672 1.40861C56.672 1.39031 56.672 1.35373 56.672 1.33544C56.672 1.31714 56.672 1.28056 56.672 1.26227ZM26.0317 19.6098V17.2867H30.6598V19.6098H26.0317ZM32.05 14.5428H24.6781H12.6049L4.11709 2.81714H52.6476L44.1598 14.5428H32.05ZM2.78173 5.6708L10.7756 16.7196C11.0317 17.0671 11.4525 17.2867 11.8915 17.2867H23.3061V20.9818C23.3061 21.7318 23.9281 22.3537 24.6781 22.3537H27.0012V57.183H2.78173V5.6708ZM29.7268 57.183V22.3537H32.05C32.8 22.3537 33.422 21.7318 33.422 20.9818V17.2867H44.8183C45.2573 17.2867 45.6781 17.0671 45.9342 16.7196L53.9281 5.6708V57.183H29.7268Z"
							fill="inhiret" />
					</svg>
					<?=GetMessage('SPS_MY_ORDERS')?>
				</a>
			</li>
			<li class="account-page__item">
				<a href="<?=SITE_DIR?>personal/private/" class="account-page__link">
					<svg class="account-page__icon" width="71" height="60" viewBox="0 0 71 60" fill="inhiret"
						 xmlns="http://www.w3.org/2000/svg">
						<path
							d="M59.64 24.2857C59.64 24.6646 59.4904 25.0279 59.2241 25.2958C58.9578 25.5638 58.5966 25.7143 58.22 25.7143H44.02C43.6434 25.7143 43.2822 25.5638 43.0159 25.2959C42.7496 25.028 42.6 24.6646 42.6 24.2857C42.6 23.9068 42.7496 23.5435 43.0159 23.2756C43.2822 23.0077 43.6434 22.8571 44.02 22.8571H58.22C58.5966 22.8572 58.9578 23.0077 59.2241 23.2756C59.4904 23.5435 59.64 23.9068 59.64 24.2857ZM58.22 34.2857H44.02C43.6434 34.2857 43.2822 34.4362 43.0159 34.7041C42.7496 34.972 42.6 35.3354 42.6 35.7143C42.6 36.0932 42.7496 36.4565 43.0159 36.7244C43.2822 36.9923 43.6434 37.1429 44.02 37.1429H58.22C58.5966 37.1429 58.9578 36.9923 59.2241 36.7244C59.4904 36.4565 59.64 36.0932 59.64 35.7143C59.64 35.3354 59.4904 34.972 59.2241 34.7041C58.9578 34.4362 58.5966 34.2857 58.22 34.2857ZM35.1327 43.9286C35.2266 44.2955 35.1718 44.6849 34.9803 45.0111C34.7889 45.3374 34.4764 45.5738 34.1117 45.6682C33.747 45.7627 33.3599 45.7076 33.0357 45.515C32.7114 45.3224 32.4764 45.008 32.3825 44.6411C31.8305 42.4947 30.5855 40.5936 28.843 39.2364C27.1005 37.8792 24.9591 37.1428 22.7554 37.1429C20.5517 37.1429 18.4104 37.8794 16.668 39.2368C14.9255 40.5941 13.6806 42.4952 13.1288 44.6416C13.0823 44.8233 13.0007 44.994 12.8886 45.1439C12.7766 45.2939 12.6363 45.4202 12.4757 45.5156C12.1515 45.7082 11.7644 45.7634 11.3997 45.6689C11.035 45.5745 10.7225 45.3382 10.531 45.012C10.3395 44.6857 10.2847 44.2963 10.3785 43.9294C10.8471 42.098 11.712 40.3934 12.9116 38.9375C14.1112 37.4815 15.6158 36.3102 17.3177 35.5074C15.5254 34.3289 14.1593 32.5998 13.4226 30.5772C12.686 28.5547 12.6181 26.3469 13.2293 24.2825C13.8404 22.218 15.0978 20.4073 16.8143 19.1198C18.5308 17.8322 20.6147 17.1367 22.7558 17.1367C24.8969 17.1367 26.9808 17.8322 28.6973 19.1198C30.4138 20.4074 31.6712 22.2181 32.2823 24.2826C32.8934 26.347 32.8255 28.5548 32.0888 30.5773C31.3521 32.5998 29.986 34.329 28.1937 35.5074C29.8955 36.3102 31.3999 37.4814 32.5995 38.9372C33.799 40.3929 34.664 42.0974 35.1327 43.9286ZM22.7557 34.2857C24.16 34.2857 25.5327 33.8668 26.7003 33.0819C27.8678 32.2971 28.7779 31.1815 29.3153 29.8763C29.8526 28.5711 29.9932 27.1349 29.7193 25.7494C29.4453 24.3638 28.7691 23.091 27.7762 22.0921C26.7832 21.0931 25.5181 20.4129 24.1409 20.1372C22.7636 19.8616 21.336 20.0031 20.0387 20.5437C18.7413 21.0843 17.6324 21.9999 16.8523 23.1745C16.0721 24.3491 15.6557 25.7301 15.6557 27.1429C15.6579 29.0366 16.4066 30.8522 17.7376 32.1912C19.0687 33.5303 20.8733 34.2836 22.7557 34.2857ZM71 4.28571V55.7143C70.9987 56.8505 70.5495 57.9399 69.7508 58.7433C68.9522 59.5468 67.8694 59.9987 66.74 60H4.26C3.13057 59.9987 2.04778 59.5468 1.24915 58.7433C0.450527 57.9399 0.00129191 56.8505 0 55.7143V4.28571C0.00129191 3.14947 0.450527 2.06014 1.24915 1.25669C2.04778 0.453246 3.13057 0.00129971 4.26 0H66.74C67.8694 0.00129971 68.9522 0.453246 69.7508 1.25669C70.5495 2.06014 70.9987 3.14947 71 4.28571ZM68.16 4.28571C68.1596 3.90697 68.0098 3.54386 67.7436 3.27604C67.4774 3.00822 67.1165 2.85758 66.74 2.85714H4.26C3.88352 2.85758 3.52259 3.00822 3.25638 3.27604C2.99018 3.54386 2.84043 3.90697 2.84 4.28571V55.7143C2.84043 56.093 2.99018 56.4561 3.25638 56.724C3.52259 56.9918 3.88352 57.1424 4.26 57.1429H66.74C67.1165 57.1424 67.4774 56.9918 67.7436 56.724C68.0098 56.4561 68.1596 56.093 68.16 55.7143V4.28571Z"
							fill="inhiret" />
					</svg>
					<?=GetMessage('SPS_PERSONAL_PAGE_NAME')?>
				</a>
			</li>
			<li class="account-page__item">
				<a href="<?=SITE_DIR?>personal/bonus/" class="account-page__link">
					<svg class="account-page__icon" width="64" height="60" viewBox="0 0 64 60" fill="inhiret"
						 xmlns="http://www.w3.org/2000/svg">
						<path
							d="M59.5128 13.7818H42.4562C45.1082 11.8938 46.749 9.30721 46.9589 6.81502C47.1878 4.19067 45.6997 1.09432 40.9108 0.30135C40.3766 0.206949 39.8424 0.169189 39.3273 0.169189C37.2477 0.169189 35.5306 0.924398 34.2332 2.39705C33.0122 3.77531 32.3444 5.64445 31.9819 7.53247C31.6194 5.64445 30.9325 3.75643 29.7306 2.39705C28.4523 0.924398 26.7352 0.169189 24.6556 0.169189C24.1595 0.169189 23.6253 0.206949 23.072 0.30135C18.2832 1.09432 16.8141 4.19067 17.024 6.81502C17.2339 9.30721 18.8937 11.8938 21.5266 13.7818H4.47005C2.08518 13.7818 0.158203 15.6887 0.158203 18.0487V20.7486C0.158203 23.1086 2.08518 25.0155 4.47005 25.0155H5.72926V54.5442C5.72926 57.4706 8.1332 59.8495 11.0904 59.8495H52.8924C55.8497 59.8495 58.2536 57.4706 58.2536 54.5442V25.0155H59.5128C61.8977 25.0155 63.8246 23.1086 63.8246 20.7486V18.0487C63.8246 15.6887 61.8977 13.7818 59.5128 13.7818ZM36.4082 4.24731C37.1523 3.3977 38.1062 3.00122 39.3464 3.00122C39.6898 3.00122 40.0714 3.03898 40.4529 3.09562C41.9029 3.34106 44.326 4.11515 44.1161 6.58846C43.8871 9.32609 40.3766 13.2154 34.5385 13.7252C34.3477 11.0631 34.4812 6.4563 36.4082 4.24731ZM19.8858 6.58846C19.676 4.11515 22.099 3.34106 23.549 3.09562C23.9306 3.03898 24.3121 3.00122 24.6556 3.00122C25.8957 3.00122 26.8496 3.41658 27.5937 4.24731C29.5207 6.43742 29.6543 11.0442 29.4635 13.7252C23.6253 13.2154 20.1148 9.32609 19.8858 6.58846ZM55.3918 54.5253C55.3918 55.8847 54.2661 56.9986 52.8924 56.9986H11.0904C9.71675 56.9986 8.5911 55.8847 8.5911 54.5253V25.0155H55.3727V54.5253H55.3918ZM60.9628 20.7486C60.9628 21.5416 60.3141 22.1835 59.5128 22.1835H56.8227H7.17926H4.48913C3.68781 22.1835 3.03912 21.5416 3.03912 20.7486V18.0487C3.03912 17.2558 3.68781 16.6139 4.48913 16.6139H59.5128C60.3141 16.6139 60.9628 17.2558 60.9628 18.0487V20.7486Z"
							fill="inhiret" />
					</svg>
					<?=GetMessage('SPS_BONUS_PAGE_NAME')?>
				</a>
			</li>
			<li class="account-page__item">
				<a href="<?=SITE_DIR?>personal/favorite/" class="account-page__link">
					<svg class="account-page__icon" width="68" height="60" viewBox="0 0 68 60" fill="inhiret"
						 xmlns="http://www.w3.org/2000/svg">
						<path
							d="M33.915 60C33.6919 60.0001 33.4724 59.9428 33.2778 59.8337C27.3736 56.3751 21.8325 52.3312 16.7381 47.763C5.63269 37.7682 0.00195312 27.8423 0.00195312 18.2609C0.00288041 14.2691 1.31145 10.3876 3.72748 7.21003C6.14351 4.03248 9.53397 1.73389 13.3802 0.665889C17.2264 -0.402112 21.3166 -0.180713 25.0251 1.29622C28.7335 2.77315 31.8561 5.42428 33.915 8.84406C35.9739 5.42428 39.0965 2.77315 42.8049 1.29622C46.5134 -0.180713 50.6036 -0.402112 54.4498 0.665889C58.296 1.73389 61.6865 4.03248 64.1025 7.21003C66.5185 10.3876 67.8271 14.2691 67.828 18.2609C67.828 27.8423 62.1973 37.7683 51.0919 47.763C45.9975 52.3312 40.4564 56.3751 34.5522 59.8337C34.3576 59.9428 34.1381 60.0001 33.915 60ZM18.2628 2.6087C14.113 2.61336 10.1346 4.26392 7.20021 7.19826C4.26587 10.1326 2.61531 14.1111 2.61065 18.2609C2.61065 37.7286 29.4071 54.5027 33.915 57.1854C38.4229 54.5027 65.2193 37.7286 65.2193 18.2609C65.2187 14.6432 63.9651 11.1373 61.6717 8.33944C59.3783 5.54156 56.1866 3.62432 52.6394 2.91369C49.0922 2.20306 45.4083 2.74291 42.2141 4.44144C39.0199 6.13998 36.5126 8.89237 35.1184 12.2306C35.0192 12.468 34.8521 12.6708 34.6379 12.8134C34.4238 12.956 34.1723 13.0321 33.915 13.0321C33.6577 13.0321 33.4062 12.956 33.1921 12.8134C32.9779 12.6708 32.8108 12.468 32.7116 12.2306C31.525 9.37756 29.5188 6.94051 26.9469 5.22778C24.375 3.51505 21.3528 2.60359 18.2628 2.6087Z"
							fill="inhiret" />
					</svg>
					<?=GetMessage('SPS_FAVORITE_PAGE_NAME')?>
				</a>
			</li>
			<li class="account-page__item">
				<a href="" class="account-page__link account-page__link-logout" data-action="logout">
					<svg class="account-page__icon" width="60" height="60" viewBox="0 0 60 60" fill="inhiret"
						 xmlns="http://www.w3.org/2000/svg">
						<path
							d="M59.3698 30.9222L45.6741 44.6179C45.4295 44.8625 45.0978 44.9999 44.7519 44.9999C44.406 44.9999 44.0742 44.8625 43.8296 44.6179C43.585 44.3734 43.4476 44.0416 43.4476 43.6957C43.4475 43.3498 43.5849 43.018 43.8295 42.7734L55.2985 31.3043H21.9161C21.5702 31.3043 21.2384 31.1669 20.9938 30.9223C20.7492 30.6777 20.6118 30.3459 20.6118 30C20.6118 29.6541 20.7492 29.3223 20.9938 29.0777C21.2384 28.8331 21.5702 28.6957 21.9161 28.6957H55.2985L43.8295 17.2266C43.5849 16.982 43.4475 16.6502 43.4476 16.3043C43.4476 15.9584 43.585 15.6266 43.8296 15.3821C44.0742 15.1375 44.406 15.0001 44.7519 15.0001C45.0978 15.0001 45.4295 15.1375 45.6741 15.3821L59.3698 29.0778C59.4909 29.1989 59.587 29.3427 59.6525 29.5009C59.718 29.6591 59.7518 29.8287 59.7518 30C59.7518 30.1713 59.718 30.3409 59.6525 30.4991C59.587 30.6573 59.4909 30.8011 59.3698 30.9222ZM27.1335 57.3913H3.65523C3.30941 57.3909 2.97786 57.2534 2.73333 57.0089C2.48879 56.7643 2.35125 56.4328 2.35088 56.087V3.91304C2.35125 3.56722 2.48879 3.23567 2.73333 2.99114C2.97786 2.7466 3.30941 2.60906 3.65523 2.6087H27.1335C27.4794 2.6087 27.8112 2.47127 28.0558 2.22666C28.3004 1.98205 28.4378 1.65028 28.4378 1.30435C28.4378 0.958413 28.3004 0.626648 28.0558 0.382035C27.8112 0.137422 27.4794 0 27.1335 0H3.65523C2.61778 0.00115913 1.62316 0.413798 0.889572 1.14738C0.155985 1.88097 -0.256653 2.8756 -0.257812 3.91304V56.087C-0.256653 57.1244 0.155985 58.119 0.889572 58.8526C1.62316 59.5862 2.61778 59.9988 3.65523 60H27.1335C27.4794 60 27.8112 59.8626 28.0558 59.618C28.3004 59.3734 28.4378 59.0416 28.4378 58.6957C28.4378 58.3497 28.3004 58.018 28.0558 57.7733C27.8112 57.5287 27.4794 57.3913 27.1335 57.3913Z"
							fill="inhiret" />
					</svg>
					<?=GetMessage("SPS_LOGOUT")?>
				</a>
			</li>
		</menu>
	</div>
</div>
