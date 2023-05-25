<?php if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();
/** @var array $arParams */
/** @var array $arResult */
/** @global \CMain $APPLICATION */
/** @global \CUser $USER */
/** @global \CDatabase $DB */
/** @var \CBitrixComponentTemplate $this */
/** @var string $templateName */
/** @var string $templateFile */
/** @var string $templateFolder */
/** @var string $componentPath */
/** @var array $templateData */
/** @var \CBitrixComponent $component */
$this->setFrameMode(true);

//echo '<pre>';print_r($arResult);echo '</pre>';

$containerId = $this->GetEditAreaId('quiz');

$arJSParams = $arResult;

?>
<section class="section quiz" id="<?=$containerId?>">
</section>

<script id="vue-quiz-tpl" type="text/html">
    <div class="container" v-if="loaded">
        <div class="quiz-container">
            <div class="quiz-main" v-if="!showResult">
                <div class="quiz-progress">
                    <div class="quiz-progress__head">
                        <p class="quiz-count">{{step}}</p>
                        <svg width="14" height="33" viewBox="0 0 14 33" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M0.15 32.4L11.37 0.371998H13.58L2.36 32.4H0.15Z" fill="#C5A994" />
                        </svg>
                        <p class="quiz-all">5</p>
                    </div>
                    <p class="quiz-progress__text">Шаг</p>
                </div>
                <component :is="'quiz-step-'+step" :config="config"></component>
            </div>
            <quiz-show-result v-else></quiz-show-result>
        </div>
    </div>
</script>

<script id="vue-quiz-step-1-tpl" type="text/html">
    <div class="quiz-question _active">
        <div class="quiz-content">
            <h3 class="quiz-title">Идеальный платок</h3>
            <div class="quiz-description">
                <p>В нашем ассортименте более 200 видов платков, иногда так сложно определиться!</p>
                <p>Ответьте на 5 вопросов и мы поможем с выбором.</p>
            </div>
        </div>

        <div class="quiz-choice">
            <p>1. Выберите платок, который Вам нужен:</p>
        </div>

        <div class="quiz-check">
            <div class="quiz-check__item" v-for="(item, key) in config.WRAP_TYPE" :key="key">
                <label class="quiz-radio">
                    <input class="quiz-radio__input" type="radio" :value="item.XML_ID" v-model="$root.form.wrapType"/>
                    <div class="quiz-radio__content">
                        <div class="quiz-radio__head">
                            <span class="quiz-radio__box"></span>
                            <span class="quiz-radio__text">{{item.NAME}}</span>
                        </div>

                        <div class="quiz-radio__container">
                            <div class="quiz-radio__img">
                                <img :src="item.FILE.SRC" alt="" loading="lazy" />
                            </div>
                        </div>
                    </div>
                </label>
            </div>
        </div>

        <div class="quiz-button">
            <button class="quiz-button__further" :class="{'_disable': !$root.form.wrapType}" @click.stop.prevent="$root.step++">Далее</button>
        </div>
    </div>
</script>

<script id="vue-quiz-step-2-tpl" type="text/html">
    <div class="quiz-question _active">
        <div class="quiz-content">
            <h3 class="quiz-title">Идеальный платок</h3>
            <div class="quiz-description">
                <p>В нашем ассортименте более 200 видов платков, иногда так сложно определиться!</p>
                <p>Ответьте на 5 вопросов и мы поможем с выбором.</p>
            </div>
        </div>

        <div class="quiz-choice">
            <p>2. Выберите форму платка:</p>
        </div>

        <div class="quiz-check">
            <div class="quiz-check__item" v-for="(item, key) in config.WRAP_FORM" :key="key">
                <label class="quiz-radio">
                    <input class="quiz-radio__input" type="radio" :value="item.XML_ID" v-model="$root.form.wrapForm"/>
                    <div class="quiz-radio__content">
                        <div class="quiz-radio__head">
                            <span class="quiz-radio__box"></span>
                            <span class="quiz-radio__text">{{item.NAME}}</span>
                        </div>

                        <div class="quiz-radio__container">
                            <div class="quiz-radio__img">
                                <img :src="item.FILE.SRC" alt="" loading="lazy" />
                            </div>
                        </div>
                    </div>
                </label>
            </div>
        </div>

        <div class="quiz-button">
            <button class="quiz-button__back" @click.stop.prevent="$root.step--">
                <svg width="18" height="12" viewBox="0 0 18 12" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path d="M1.5 6H17.5M1.5 6L7.79333 11M1.5 6L7.79333 1" stroke-width="0.75" />
                </svg>
                Назад
            </button>

            <button class="quiz-button__further" :class="{'_disable': !$root.form.wrapForm}" @click.stop.prevent="$root.step++">Далее</button>
        </div>
    </div>
</script>

<script id="vue-quiz-step-3-tpl" type="text/html">
    <div class="quiz-question _active">
        <div class="quiz-content">
            <h3 class="quiz-title">Идеальный платок</h3>
            <div class="quiz-description">
                <p>В нашем ассортименте более 200 видов платков, иногда так сложно определиться!</p>
                <p>Ответьте на 5 вопросов и мы поможем с выбором.</p>
            </div>
        </div>

        <div class="quiz-choice">
            <p>3. Выберите размер платка:</p>
        </div>

        <div class="quiz-check">
            <div class="quiz-check__item" v-for="(item, key) in config.WRAP_SIZE" :key="key">
                <label class="quiz-radio">
                    <input class="quiz-radio__input" type="radio" :value="item.XML_ID" v-model="$root.form.wrapSize"/>
                    <div class="quiz-radio__content">
                        <div class="quiz-radio__head">
                            <span class="quiz-radio__box"></span>
                            <span class="quiz-radio__text">{{item.NAME}}</span>
                        </div>

                        <div class="quiz-radio__container">
                            <div class="quiz-radio__img">
                                <img :src="item.FILE.SRC" alt="" loading="lazy" />
                            </div>
                        </div>
                    </div>
                </label>
            </div>
        </div>

        <div class="quiz-button">
            <button class="quiz-button__back" @click.stop.prevent="$root.step--">
                <svg width="18" height="12" viewBox="0 0 18 12" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path d="M1.5 6H17.5M1.5 6L7.79333 11M1.5 6L7.79333 1" stroke-width="0.75" />
                </svg>
                Назад
            </button>

            <button class="quiz-button__further" :class="{'_disable': !$root.form.wrapSize}" @click.stop.prevent="$root.step++">Далее</button>
        </div>
    </div>
</script>

<script id="vue-quiz-step-4-tpl" type="text/html">
    <div class="quiz-question _active">
        <div class="quiz-content">
            <h3 class="quiz-title">Идеальный платок</h3>
            <div class="quiz-description">
                <p>В нашем ассортименте более 200 видов платков, иногда так сложно определиться!</p>
                <p>Ответьте на 5 вопросов и мы поможем с выбором.</p>
            </div>
        </div>

        <div class="quiz-choice">
            <p>4. Выберите цвет платка:</p>
        </div>

        <div class="quiz-check">
            <div class="quiz-check__item" v-for="(item, key) in config.WRAP_COLOR" :key="key">
                <label class="quiz-radio">
                    <input class="quiz-radio__input" type="radio" :value="item.XML_ID" v-model="$root.form.wrapColor"/>
                    <div class="quiz-radio__content">
                        <div class="quiz-radio__head">
                            <span class="quiz-radio__box"></span>
                            <span class="quiz-radio__text">{{item.NAME}}</span>
                        </div>

                        <div class="quiz-radio__container">
                            <div class="quiz-radio__img">
                                <img :src="item.FILE.SRC" alt="" loading="lazy" />
                            </div>
                        </div>
                    </div>
                </label>
            </div>
        </div>

        <div class="quiz-button">
            <button class="quiz-button__back" @click.stop.prevent="$root.step--">
                <svg width="18" height="12" viewBox="0 0 18 12" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path d="M1.5 6H17.5M1.5 6L7.79333 11M1.5 6L7.79333 1" stroke-width="0.75" />
                </svg>
                Назад
            </button>

            <button class="quiz-button__further" :class="{'_disable': !$root.form.wrapColor}" @click.stop.prevent="$root.step++">Далее</button>
        </div>
    </div>
</script>

<script id="vue-quiz-step-5-tpl" type="text/html">
    <div class="quiz-question _active" v-if="loaded">
        <div class="quiz-content">
            <h3 class="quiz-title">Идеальный платок</h3>
            <div class="quiz-description">
                <p>В нашем ассортименте более 200 видов платков, иногда так сложно определиться!</p>
                <p>Ответьте на 5 вопросов и мы поможем с выбором.</p>
            </div>
        </div>

        <div class="quiz-choice">
            <p>5. Выберите ценовой диапазон:</p>
        </div>

        <div class="quiz-toddler">
            <h4 class="quiz-toddler__title">
                Потяните за ползунки влево или вправо, в зависимости от нужного диапазона цен или укажите вручную.
            </h4>

            <div class="filters-price">
                <div class="filters-price__slider" ref="rangeSlider"></div>

                <div class="filters-price__inputs">
                    <label class="filters-price__label">
                        <input type="number" :min="minimumPrice" :max="maximumPrice" class="filters-price__input"
                               :placeholder="minimumPrice" ref="inputMin" :value="$root.form.minimumPrice" @change="setMinimumPrice($event.target.value)"/>
                    </label>

                    <label class="filters-price__label">
                        <input type="number" :min="minimumPrice" :max="maximumPrice" class="filters-price__input"
                               :placeholder="maximumPrice" ref="inputMax" :value="$root.form.maximumPrice" @change="setMaximumPrice($event.target.value)"/>
                    </label>
                </div>
            </div>
        </div>

        <div class="quiz-button">
            <button class="quiz-button__back" @click.stop.prevent="$root.step--">
                <svg width="18" height="12" viewBox="0 0 18 12" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path d="M1.5 6H17.5M1.5 6L7.79333 11M1.5 6L7.79333 1" stroke-width="0.75" />
                </svg>
                Назад
            </button>

            <button class="quiz-button__further" @click.stop.prevent="$root.showResult = true">Далее</button>
        </div>
    </div>
</script>

<script id="vue-quiz-show-result-tpl" type="text/html">
    <div class="quiz-final">
        <div class="quiz-progress">
            <button class="quiz-final__button" @click.stop.prevent="resetTest()">Пройти тест заново</button>
        </div>
        <div class="quiz-question _active">
            <div class="quiz-content">
                <h3 class="quiz-title">Идеальный платок</h3>
                <div class="quiz-description">
                    <p>В нашем ассортименте более 200 видов платков, иногда так сложно определиться!</p>
                    <p>Ответьте на 5 вопросов и мы поможем с выбором.</p>
                </div>
            </div>

            <div class="quiz-final__container">
                <div class="swiper quiz-final__swiper" ref="swiper">
                </div>

            </div>

        </div>
    </div>
</script>

<?
\Bitrix\Main\UI\Extension::load("ui.vue3");

$arJSParams = array(
    'container' => '#'.$containerId,
    'template' => '#vue-quiz-tpl',
    'ajaxPath' => $component->getPath().'/ajax.php',
    'signedParams' => $arResult['SIGNED_PARAMS'],
    'signedTemplate' => $arResult['SIGNED_TEMPLATE'],
    'actionVariable' => $arParams['ACTION_VARIABLE'],
);
?>

<script>
    (function() {

        window.vueQuiz = createVueQuiz(<?=\Bitrix\Main\Web\Json::encode($arJSParams)?>);

    })();
</script>