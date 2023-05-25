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

$photoSrc = '/local/templates/oren/img/account-block/user-avatar-icon.svg';
$userName = '';

if ($USER->IsAuthorized())
{
    $arUser = \CUser::GetByID($USER->GetID())->Fetch();

    $arFile = \CFile::GetFileArray($arUser['PERSONAL_PHOTO']);
    if (is_array($arFile))
    {
        $photoSrc = $arFile['SRC'];
    }

    $userName = $USER->GetFullName();
}

?>
<div class="account-block__profile profile">
    <label for="user-photo-input" class="profile__photo">
        <img id="user-photo" src="<?=$photoSrc?>" alt="Фотография пользователя"
             class="profile__img" />
        <input class="profile__input" type="file" id="user-photo-input" name="user-photo" accept="image/png,
              image/jpeg" value="null" />
    </label>
    <a href="<?=SITE_DIR?>personal/private/" class="profile__link"><?=$userName ?: 'Заполните профиль'?></a>
</div>

<script>

    (function() {
        let el = document.getElementById('user-photo-input');

        el.addEventListener('change', function(e) {
            let fd = new FormData();

            if (el.files.length)
            {
                fd.append('file', el.files[0]);
            }

            BX.ajax.runAction('local:lib.api.user.uploadPersonalPhoto', {
                data: fd,
            }).then(
                function(response) {
                    document.getElementById('user-photo').src = response.data.src;
                },
            );
        });
    })();
</script>