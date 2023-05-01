<?
\Bitrix\Main\Loader::includeModule('devbx.forms');

$form = \DevBx\Forms\FormManager::getInstance()->getFormInstance(2);

$arFilter = [
    '=ACTIVE' => 'Y',
    '=UF_PRODUCT_ID' => $arResult['ID'],
];

$row = $form->getDataClass()::getList([
    'filter' => $arFilter,
    'runtime' => [
        new \Bitrix\Main\ORM\Fields\ExpressionField('AVG_RATING', 'avg(%s)', ['UF_RATING'])
    ],
    'select' => [
        'AVG_RATING'
    ],
])->fetch();

$query = $form->getDataClass()::query();
$query->setFilter($arFilter);
$reviewCnt = $query->queryCountTotal();

$query = $form->getDataClass()::query();
$query->setFilter($arFilter+array('UF_RECOMMEND'=>1));
$recommendCnt = $query->queryCountTotal();



$avgRating = $row ? ceil($row['AVG_RATING']) : 0;

?>
<div class="assessment">
    <h3 class="assessment-count">4</h3>
    <div class="assessment-icon">
        <svg width="21" height="20" viewBox="0 0 21 20" fill="none" xmlns="http://www.w3.org/2000/svg">
            <path fill-rule="evenodd" clip-rule="evenodd"
                  d="M16.1864 18.8376C15.878 18.9938 15.5215 18.9982 15.1784 18.7548L10.9863 15.7549C10.8039 15.6421 10.6602 15.5937 10.5119 15.5937C10.3414 15.5937 10.1836 15.6274 9.98716 15.708L5.84695 18.6991C5.51861 18.9696 5.15691 18.9652 4.83302 18.8171C4.80538 18.8046 4.77819 18.7911 4.75149 18.7768C4.68933 18.7465 4.63292 18.7058 4.58472 18.6565C4.61956 18.6917 4.53581 18.6169 4.48244 18.5539C4.37386 18.4319 4.30106 18.2829 4.27193 18.1229C4.2428 17.9629 4.25844 17.7981 4.31716 17.6463L5.94108 12.7229C5.9904 12.579 5.99136 12.4233 5.9438 12.2789C5.89625 12.1344 5.80273 12.0091 5.67722 11.9216L1.47474 8.88219C1.30323 8.771 1.17326 8.60729 1.10473 8.41611C1.0362 8.22494 1.03287 8.01682 1.09526 7.8236C1.17753 7.44679 1.55479 7.12862 2.03063 7.12862H7.27223C7.57834 7.12862 7.86517 6.92042 7.96968 6.62645L9.58322 1.73744C9.72182 1.2536 10.1302 1.05273 10.5127 1.05273C10.6898 1.05273 10.8403 1.07913 11.0196 1.18689C11.2027 1.29832 11.3428 1.4706 11.4354 1.71472L13.0512 6.61326C13.1601 6.92042 13.447 7.12862 13.7531 7.12862H18.9621C19.2978 7.12862 19.5661 7.27671 19.7381 7.51276C19.7826 7.57434 19.8189 7.63886 19.8389 7.68138C20.0568 8.03326 19.956 8.57575 19.5402 8.86752L15.3177 11.9216C15.1905 12.0109 15.0962 12.1388 15.0492 12.286C15.0023 12.4331 15.0052 12.5914 15.0576 12.7368L16.6585 17.6287C16.8734 18.1265 16.6244 18.6184 16.2509 18.8024C16.2299 18.8151 16.2084 18.8269 16.1864 18.8376Z"
                  fill="#F4AF48" />
        </svg>
        <svg width="21" height="20" viewBox="0 0 21 20" fill="none" xmlns="http://www.w3.org/2000/svg">
            <path fill-rule="evenodd" clip-rule="evenodd"
                  d="M16.1864 18.8376C15.878 18.9938 15.5215 18.9982 15.1784 18.7548L10.9863 15.7549C10.8039 15.6421 10.6602 15.5937 10.5119 15.5937C10.3414 15.5937 10.1836 15.6274 9.98716 15.708L5.84695 18.6991C5.51861 18.9696 5.15691 18.9652 4.83302 18.8171C4.80538 18.8046 4.77819 18.7911 4.75149 18.7768C4.68933 18.7465 4.63292 18.7058 4.58472 18.6565C4.61956 18.6917 4.53581 18.6169 4.48244 18.5539C4.37386 18.4319 4.30106 18.2829 4.27193 18.1229C4.2428 17.9629 4.25844 17.7981 4.31716 17.6463L5.94108 12.7229C5.9904 12.579 5.99136 12.4233 5.9438 12.2789C5.89625 12.1344 5.80273 12.0091 5.67722 11.9216L1.47474 8.88219C1.30323 8.771 1.17326 8.60729 1.10473 8.41611C1.0362 8.22494 1.03287 8.01682 1.09526 7.8236C1.17753 7.44679 1.55479 7.12862 2.03063 7.12862H7.27223C7.57834 7.12862 7.86517 6.92042 7.96968 6.62645L9.58322 1.73744C9.72182 1.2536 10.1302 1.05273 10.5127 1.05273C10.6898 1.05273 10.8403 1.07913 11.0196 1.18689C11.2027 1.29832 11.3428 1.4706 11.4354 1.71472L13.0512 6.61326C13.1601 6.92042 13.447 7.12862 13.7531 7.12862H18.9621C19.2978 7.12862 19.5661 7.27671 19.7381 7.51276C19.7826 7.57434 19.8189 7.63886 19.8389 7.68138C20.0568 8.03326 19.956 8.57575 19.5402 8.86752L15.3177 11.9216C15.1905 12.0109 15.0962 12.1388 15.0492 12.286C15.0023 12.4331 15.0052 12.5914 15.0576 12.7368L16.6585 17.6287C16.8734 18.1265 16.6244 18.6184 16.2509 18.8024C16.2299 18.8151 16.2084 18.8269 16.1864 18.8376Z"
                  fill="#F4AF48" />
        </svg>
        <svg width="21" height="20" viewBox="0 0 21 20" fill="none" xmlns="http://www.w3.org/2000/svg">
            <path fill-rule="evenodd" clip-rule="evenodd"
                  d="M16.1864 18.8376C15.878 18.9938 15.5215 18.9982 15.1784 18.7548L10.9863 15.7549C10.8039 15.6421 10.6602 15.5937 10.5119 15.5937C10.3414 15.5937 10.1836 15.6274 9.98716 15.708L5.84695 18.6991C5.51861 18.9696 5.15691 18.9652 4.83302 18.8171C4.80538 18.8046 4.77819 18.7911 4.75149 18.7768C4.68933 18.7465 4.63292 18.7058 4.58472 18.6565C4.61956 18.6917 4.53581 18.6169 4.48244 18.5539C4.37386 18.4319 4.30106 18.2829 4.27193 18.1229C4.2428 17.9629 4.25844 17.7981 4.31716 17.6463L5.94108 12.7229C5.9904 12.579 5.99136 12.4233 5.9438 12.2789C5.89625 12.1344 5.80273 12.0091 5.67722 11.9216L1.47474 8.88219C1.30323 8.771 1.17326 8.60729 1.10473 8.41611C1.0362 8.22494 1.03287 8.01682 1.09526 7.8236C1.17753 7.44679 1.55479 7.12862 2.03063 7.12862H7.27223C7.57834 7.12862 7.86517 6.92042 7.96968 6.62645L9.58322 1.73744C9.72182 1.2536 10.1302 1.05273 10.5127 1.05273C10.6898 1.05273 10.8403 1.07913 11.0196 1.18689C11.2027 1.29832 11.3428 1.4706 11.4354 1.71472L13.0512 6.61326C13.1601 6.92042 13.447 7.12862 13.7531 7.12862H18.9621C19.2978 7.12862 19.5661 7.27671 19.7381 7.51276C19.7826 7.57434 19.8189 7.63886 19.8389 7.68138C20.0568 8.03326 19.956 8.57575 19.5402 8.86752L15.3177 11.9216C15.1905 12.0109 15.0962 12.1388 15.0492 12.286C15.0023 12.4331 15.0052 12.5914 15.0576 12.7368L16.6585 17.6287C16.8734 18.1265 16.6244 18.6184 16.2509 18.8024C16.2299 18.8151 16.2084 18.8269 16.1864 18.8376Z"
                  fill="#F4AF48" />
        </svg>
        <svg width="21" height="20" viewBox="0 0 21 20" fill="none" xmlns="http://www.w3.org/2000/svg">
            <path fill-rule="evenodd" clip-rule="evenodd"
                  d="M16.1864 18.8376C15.878 18.9938 15.5215 18.9982 15.1784 18.7548L10.9863 15.7549C10.8039 15.6421 10.6602 15.5937 10.5119 15.5937C10.3414 15.5937 10.1836 15.6274 9.98716 15.708L5.84695 18.6991C5.51861 18.9696 5.15691 18.9652 4.83302 18.8171C4.80538 18.8046 4.77819 18.7911 4.75149 18.7768C4.68933 18.7465 4.63292 18.7058 4.58472 18.6565C4.61956 18.6917 4.53581 18.6169 4.48244 18.5539C4.37386 18.4319 4.30106 18.2829 4.27193 18.1229C4.2428 17.9629 4.25844 17.7981 4.31716 17.6463L5.94108 12.7229C5.9904 12.579 5.99136 12.4233 5.9438 12.2789C5.89625 12.1344 5.80273 12.0091 5.67722 11.9216L1.47474 8.88219C1.30323 8.771 1.17326 8.60729 1.10473 8.41611C1.0362 8.22494 1.03287 8.01682 1.09526 7.8236C1.17753 7.44679 1.55479 7.12862 2.03063 7.12862H7.27223C7.57834 7.12862 7.86517 6.92042 7.96968 6.62645L9.58322 1.73744C9.72182 1.2536 10.1302 1.05273 10.5127 1.05273C10.6898 1.05273 10.8403 1.07913 11.0196 1.18689C11.2027 1.29832 11.3428 1.4706 11.4354 1.71472L13.0512 6.61326C13.1601 6.92042 13.447 7.12862 13.7531 7.12862H18.9621C19.2978 7.12862 19.5661 7.27671 19.7381 7.51276C19.7826 7.57434 19.8189 7.63886 19.8389 7.68138C20.0568 8.03326 19.956 8.57575 19.5402 8.86752L15.3177 11.9216C15.1905 12.0109 15.0962 12.1388 15.0492 12.286C15.0023 12.4331 15.0052 12.5914 15.0576 12.7368L16.6585 17.6287C16.8734 18.1265 16.6244 18.6184 16.2509 18.8024C16.2299 18.8151 16.2084 18.8269 16.1864 18.8376Z"
                  fill="#F4AF48" />
        </svg>
        <svg width="21" height="20" viewBox="0 0 21 20" fill="none" xmlns="http://www.w3.org/2000/svg">
            <path fill-rule="evenodd" clip-rule="evenodd"
                  d="M16.1864 18.8376C15.878 18.9938 15.5215 18.9982 15.1784 18.7548L10.9863 15.7549C10.8039 15.6421 10.6602 15.5937 10.5119 15.5937C10.3414 15.5937 10.1836 15.6274 9.98716 15.708L5.84695 18.6991C5.51861 18.9696 5.15691 18.9652 4.83302 18.8171C4.80538 18.8046 4.77819 18.7911 4.75149 18.7768C4.68933 18.7465 4.63292 18.7058 4.58472 18.6565C4.61956 18.6917 4.53581 18.6169 4.48244 18.5539C4.37386 18.4319 4.30106 18.2829 4.27193 18.1229C4.2428 17.9629 4.25844 17.7981 4.31716 17.6463L5.94108 12.7229C5.9904 12.579 5.99136 12.4233 5.9438 12.2789C5.89625 12.1344 5.80273 12.0091 5.67722 11.9216L1.47474 8.88219C1.30323 8.771 1.17326 8.60729 1.10473 8.41611C1.0362 8.22494 1.03287 8.01682 1.09526 7.8236C1.17753 7.44679 1.55479 7.12862 2.03063 7.12862H7.27223C7.57834 7.12862 7.86517 6.92042 7.96968 6.62645L9.58322 1.73744C9.72182 1.2536 10.1302 1.05273 10.5127 1.05273C10.6898 1.05273 10.8403 1.07913 11.0196 1.18689C11.2027 1.29832 11.3428 1.4706 11.4354 1.71472L13.0512 6.61326C13.1601 6.92042 13.447 7.12862 13.7531 7.12862H18.9621C19.2978 7.12862 19.5661 7.27671 19.7381 7.51276C19.7826 7.57434 19.8189 7.63886 19.8389 7.68138C20.0568 8.03326 19.956 8.57575 19.5402 8.86752L15.3177 11.9216C15.1905 12.0109 15.0962 12.1388 15.0492 12.286C15.0023 12.4331 15.0052 12.5914 15.0576 12.7368L16.6585 17.6287C16.8734 18.1265 16.6244 18.6184 16.2509 18.8024C16.2299 18.8151 16.2084 18.8269 16.1864 18.8376Z"
                  fill="#E6E2D9" />
        </svg>
    </div>
    <div class="assessment-content">
        <p>средняя оценка покупателей</p>
        <p>
            <svg width="15" height="17" viewBox="0 0 15 17" fill="none" xmlns="http://www.w3.org/2000/svg">
                <g clip-path="url(#clip0_507_23588)">
                    <path
                        d="M10.412 1.18207C10.6189 1.27766 10.7222 1.50744 10.6543 1.72097L9.27211 6.07112L12.2935 6.23379L12.3578 6.24172C13.6677 6.49618 14.2476 7.40326 14.0107 8.77364C13.8174 9.89182 13.6504 11.7476 13.5071 14.3762C13.3103 15.6401 12.4244 16.2386 11.0372 16.0803C9.90773 15.9513 7.68542 15.5995 4.36169 15.0237C4.13949 15.3179 3.78211 15.5087 3.37913 15.5087H2.15831C1.48408 15.5087 0.9375 14.9746 0.9375 14.3158V6.56138C0.9375 5.90249 1.48408 5.3684 2.15831 5.3684H3.37913C3.75898 5.3684 4.09832 5.53792 4.3222 5.80378C5.71661 4.22501 6.69546 2.9941 7.25551 2.1178C8.02108 0.919971 9.12891 0.589103 10.412 1.18207ZM8.03476 2.59335C7.39017 3.60188 6.24956 5.01814 4.6083 6.85091V14.1547C7.86641 14.7185 10.0477 15.0635 11.1439 15.1886C12.0572 15.2929 12.4796 15.0076 12.5942 14.2852C12.7346 11.6687 12.9042 9.78458 13.1048 8.6241C13.2598 7.72746 12.9892 7.28957 12.2092 7.1284L8.63351 6.93585C8.33489 6.91981 8.13138 6.63361 8.22008 6.35457L9.64995 1.8545C8.9818 1.66576 8.47761 1.9004 8.03476 2.59335ZM3.37913 6.26313H2.15831C1.98976 6.26313 1.85311 6.39669 1.85311 6.56138V14.3158C1.85311 14.4805 1.98976 14.614 2.15831 14.614H3.37913C3.54769 14.614 3.68433 14.4805 3.68433 14.3158V6.56138C3.68433 6.39669 3.54769 6.26313 3.37913 6.26313Z"
                        fill="#04857F" />
                </g>
                <defs>
                    <clipPath id="clip0_507_23588">
                        <rect width="15" height="17" fill="white" />
                    </clipPath>
                </defs>
            </svg>
            90% клиентов рекомендуют этот товар
        </p>
    </div>
</div>
