<?

namespace Local\Lib\DB;

use Bitrix\Iblock\ElementTable;
use Bitrix\Main\Entity;
use Bitrix\Main\Loader;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Type;

Loc::loadMessages(__FILE__);

Loader::includeModule('iblock');

class FavoriteTable extends Entity\DataManager
{
    public static function getTableName()
    {
        return 'b_devbx_favorite';
    }

    public static function getMap()
    {
        return array(
            new Entity\IntegerField('ID', array("primary" => true, "autocomplete" => true, "title" => "ID")),
            new Entity\DatetimeField('DATE_INSERT', array("title" => Loc::getMessage("DEVBX_OBJ_DB_FAVORITE_DATE_INSERT"), "default_value" => function() {
                return new Type\DateTime();
            })),
            new Entity\IntegerField('USER_ID', array("title" => Loc::getMessage("DEVBX_OBJ_DB_FAVORITE_USER_ID"), "required" => true)),
            new Entity\IntegerField('PRODUCT_ID', array("title" => Loc::getMessage("DEVBX_OBJ_DB_FAVORITE_PRODUCT_ID"), "required" => true)),
            (new Entity\ReferenceField('PRODUCT', '\Bitrix\Iblock\ElementTable', Entity\Query\Join::on('this.PRODUCT_ID','ref.ID')))->configureJoinType(Entity\Query\Join::TYPE_INNER),

        );
    }

    protected static function saveAnonymousList()
    {
        global $USER;

        if (!$USER->IsAuthorized())
            return;

        if (!isset($_SESSION['DEVBX_PRODUCT_FAVORITE']) || empty($_SESSION['DEVBX_PRODUCT_FAVORITE']) || !is_array($_SESSION['DEVBX_PRODUCT_FAVORITE']))
            return;

        $existList = [];

        $dbRes = self::query()
            ->addSelect('PRODUCT_ID')
            ->where('USER_ID',$USER->GetID());
        while ($arRes = $dbRes->fetch())
        {
            $existList[$arRes['PRODUCT_ID']] = true;
        }

        foreach ($_SESSION['DEVBX_PRODUCT_FAVORITE'] as $productId)
        {
            if (array_key_exists($productId, $existList))
                continue;

            self::add([
                'USER_ID' => $USER->GetID(),
                'PRODUCT_ID' => $productId,
            ]);
        }

        unset($_SESSION['DEVBX_PRODUCT_FAVORITE']);
    }

    protected static function getSessionKey()
    {
        global $USER;

        if (!$USER->IsAuthorized())
            return 'DEVBX_PRODUCT_FAVORITE';

        return 'DEVBX_PRODUCT_FAVORITE_'.$USER->GetID();
    }

    public static function saveProductId($productId)
    {
        global $USER;

        $productId = intval($productId);
        if ($productId<=0)
            return;

        $ar = self::getUserFavoriteArray();

        if (in_array($productId, $ar))
            return;

        if (!ElementTable::query()
            ->addSelect('ID')
            ->where('ID', $productId)
            ->fetch())
            return;


        $k = self::getSessionKey();

        $_SESSION[$k][] = $productId;

        if ($USER->IsAuthorized())
        {
            if (!self::query()
                ->addSelect('ID')
                ->where('PRODUCT_ID',$productId)
                ->where('USER_ID',$USER->GetID())
                ->fetch())
            {
                self::add([
                    'USER_ID' => $USER->GetID(),
                    'PRODUCT_ID' => $productId,
                ]);
            }
        }
    }

    public static function removeProductId($productId)
    {
        global $USER;

        $productId = intval($productId);
        if ($productId<=0)
            return;

        $ar = self::getUserFavoriteArray();

        if (!in_array($productId, $ar))
            return;

        $k = self::getSessionKey();

        $arKey = array_search($productId, $_SESSION[$k]);

        if ($arKey === false)
            return;

        unset($_SESSION[$k][$arKey]);

        if ($USER->IsAuthorized())
        {
            if ($arRes = self::query()
                ->addSelect('ID')
                ->where('PRODUCT_ID',$productId)
                ->where('USER_ID',$USER->GetID())
                ->fetch())
            {
                self::delete($arRes['ID']);
            }
        }
    }

    public static function getUserFavoriteArray()
    {
        global $USER;

        self::saveAnonymousList();

        $k = self::getSessionKey();

        if (!isset($_SESSION[$k]) || !is_array($_SESSION[$k]))
        {
            $result = [];

            if ($USER->IsAuthorized())
            {
                $dbRes = self::query()
                    ->addSelect('PRODUCT.ID', 'DB_PRODUCT_ID') //выбираем через Join inner
                    ->where('USER_ID', $USER->GetID())->exec();

                while ($arRes = $dbRes->fetch())
                {
                    $result[] = $arRes['DB_PRODUCT_ID'];
                }
            }

            $_SESSION[$k] = $result;
        }

        return $_SESSION[$k];
    }

    public static function deleteByProductId($id)
    {
        $dbConnection = static::getEntity()->getConnection();

        $dbConnection->query('DELETE FROM ' . static::getTableName() . ' WHERE PRODUCT_ID = ' . intval($id));
    }

    public static function deleteByUser($id)
    {
        $dbConnection = static::getEntity()->getConnection();

        $dbConnection->query('DELETE FROM ' . static::getTableName() . ' WHERE USER_ID = ' . intval($id));
    }
}

