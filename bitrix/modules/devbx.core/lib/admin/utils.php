<?php

namespace DevBx\Core\Admin;

use Bitrix\Main;

class Utils {

    protected static function includeFile($fileName)
    {
        include_once $fileName;
    }
    public static function getClassesByPath($moduleId, $path, $filter = array())
    {
        static $arIgnoreContent = array(
            '/bitrix/modules/main/include/prolog_before.php',
            '/bitrix/modules/main/include/epilog_after.php',
            '/bitrix/modules/main/include/prolog_admin_before.php',
            '/bitrix/modules/main/include/epilog_admin.php',
            '/bitrix/header.php',
            '/bitrix/footer.php',
        );

        $result = [];

        $localPath = Main\Loader::getLocal('modules/'.$moduleId.'/lib/'.$path);

        if (empty($localPath))
            return $result;

        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($localPath, \RecursiveDirectoryIterator::SKIP_DOTS | \RecursiveDirectoryIterator::FOLLOW_SYMLINKS),
            \RecursiveIteratorIterator::SELF_FIRST);

        foreach (
            $iterator as $item
        ) {
            if ($item->isFile() && $item->isReadable() && mb_substr($item->getFilename(), -4) == '.php') {
                if ($item->getPathname() == __FILE__)
                    continue;

                $content = file_get_contents($item->getPathname());

                foreach ($arIgnoreContent as $find)
                {
                    if (strpos($content, $find) !== false)
                        continue 2;
                }

                try {
                    static::includeFile($item->getPathName());
                } catch (\Exception $e)
                {
                }
            }
        }

        if (strpos($moduleId, '.') === false)
        {
            $classPath = 'Bitrix\\'.$moduleId.'\\';
        } else {
            $classPath = str_replace('.','\\', $moduleId).'\\'.str_replace('/','\\', $path);
        }

        $classPath = strtolower($classPath);

        foreach (get_declared_classes() as $className) {

            $className = ltrim($className, '\\');

            if (strtolower(substr($className, 0, strlen($classPath))) == $classPath)
            {
                if (isset($filter['abstract']))
                {
                    if ((new \ReflectionClass($className))->isAbstract() != $filter['abstract'])
                        continue;
                }

                if (isset($filter['subclass']))
                {
                    if (!is_subclass_of($className, $filter['subclass']))
                        continue;
                }

                $result[] = $className;
            }
        }

        return $result;
    }

    public static function getColumnTypeByField(Main\DB\Connection $conn, Main\ORM\Fields\ScalarField $field)
    {
        $dbType = $field->getParameter('db_type');

        if (!is_string($dbType) && is_callable($dbType))
            $dbType = call_user_func($dbType);

        if (!empty($dbType))
            return $dbType;

        if (class_exists('\Bitrix\Main\ORM\Fields\ArrayField'))
        {
            if ($field instanceof Main\ORM\Fields\ArrayField)
                return 'text';
        }

        if ($field instanceof Main\ORM\Fields\StringField)
        {
            if ($field->getSize()>0)
            {
                return 'varchar('.$field->getSize().')';
            }
        }

        return $conn->getSqlHelper()->getColumnTypeByField($field);
    }

    public static function getTableFields(Main\DB\Connection $conn, $tableName)
    {
        $fields = $conn->getTableFields($tableName);

        if ($conn->getType() == 'mysql')
        {
            $iterator = $conn->query('DESCRIBE '.$conn->getSqlHelper()->quote($tableName));
            while ($ar = $iterator->fetch())
            {
                $fieldSize = false;
                if (preg_match('#(.+)\((.+)\)#', $ar['Type'], $matches))
                {
                    $fieldType = $matches[1];
                    $fieldSize = explode(',', $matches[2]);
                    $fieldSize = array_map(function($v) {
                        return intval($v);
                    }, $fieldSize);
                } else {
                    $fieldType = $ar['Type'];
                }

                $field = false;

                switch ($fieldType)
                {
                    case 'int':
                        $field = new Main\ORM\Fields\IntegerField($ar['Field']);
                        break;
                    case 'varchar':
                        $field = (new Main\ORM\Fields\StringField($ar['Field']))
                            ->configureSize($fieldSize[0]);
                        break;
                    case 'double':
                        $field = new Main\ORM\Fields\FloatField($ar['Field']);
                        break;
                    case 'text':
                        $field = new Main\ORM\Fields\TextField($ar['Field']);
                        break;
                    case 'date':
                        $field = new Main\ORM\Fields\DateField($ar['Field']);
                        break;
                    case 'datetime':
                        $field = new Main\ORM\Fields\DatetimeField($ar['Field']);
                        break;
                }

                if ($field)
                {
                    /* @var Main\Entity\ScalarField $field */

                    $field->configureNullable($ar['Null'] == 'YES');
                    $fields[$field->getName()] = $field;
                }
            }
        }

        return $fields;
    }

    public static function correctTable(\Bitrix\Main\ORM\Entity $entity)
    {
        $conn = $entity->getConnection();

        $tableName = $entity->getDBTableName();

        if (!$conn->isTableExists($tableName))
            return;

        $conn->clearCaches();
        $dbFields = static::getTableFields($conn, $tableName);

        $entityFields = $entity->getScalarFields();
        $entityColumns = array();

        foreach ($dbFields as $field)
        {
            $columnName = $field->getColumnName();
            if (!isset($entityColumns[$columnName]))
            {
                $entityColumns[$columnName] = $field;
            }
        }

        foreach ($dbFields as $fieldName=>$field)
        {
            if (!array_key_exists($fieldName, $entityColumns))
            {
                $conn->dropColumn($tableName, $fieldName);
            }
        }

        $prevColumn = false;

        foreach ($entityFields as $id=>$field)
        {
            $realColumnName = $field->getColumnName();

            if (!array_key_exists($realColumnName, $dbFields)
                || $dbFields[$realColumnName]->isNullable() != $field->isNullable()
                || static::getColumnTypeByField($conn, $field) != static::getColumnTypeByField($conn, $dbFields[$realColumnName])
            ) {
                $sql = 'ALTER TABLE ' . $conn->getSqlHelper()->quote($tableName);

                if (!array_key_exists($realColumnName, $dbFields))
                {
                    $sql .= ' ADD COLUMN';
                } else {
                    $sql .= ' MODIFY COLUMN';
                }

                $sql .= ' ' . $conn->getSqlHelper()->quote($realColumnName)
                    . ' ' . static::getColumnTypeByField($conn, $field)
                    . ($field->isNullable() ? '' : ' NOT NULL');

                if ($prevColumn)
                {
                    $sql .= ' AFTER '.$conn->getSqlHelper()->quote($prevColumn);
                } else
                {
                    $sql .= ' FIRST';
                }

                $conn->query($sql);

                $conn->clearCaches();
                $dbFields = static::getTableFields($conn, $tableName);
            }

            $prevColumn = $realColumnName;
        }
    }

    public static function correctModuleTables($moduleId)
    {
        static::installModuleDB($moduleId, true);
    }

    public static function installModuleDB($moduleId, $install = true)
    {
        $arClass = static::getClassesByPath($moduleId, '', ['abstract'=>false,'subclass'=>\Bitrix\Main\Entity\DataManager::class]);

        foreach ($arClass as $className)
        {
            /* @var \Bitrix\Main\Entity\DataManager $className */

            $conn = $className::getEntity()->getConnection();

            if ($conn->isTableExists($className::getEntity()->getDBTableName()))
            {
                if ($install)
                {
                    static::correctTable($className::getEntity());
                } else {
                    $conn->dropTable($className::getEntity()->getDBTableName());
                }
            } else {
                if ($install)
                {
                    $entity = $className::getEntity();

                    if (count($entity->getScalarFields()))
                    {

                        $entity->createDbTable();
                        static::correctTable($entity);
                    }
                }
            }
        }
    }

    public static function registerEvents($moduleId, $install = true)
    {
        $eventManager = Main\EventManager::getInstance();

        $arClasses = static::getClassesByPath($moduleId, '', array('abstract'=>false));

        foreach ($arClasses as $className)
        {
            if (is_callable(array($className, 'getBitrixEvents')))
            {
                $arEvents = $className::getBitrixEvents();
                if (!is_array($arEvents))
                    continue;

                foreach ($arEvents as $arEvent)
                {
                    if ($install)
                    {
                        $eventManager->registerEventHandler($arEvent[0],$arEvent[1], $moduleId, $className, $arEvent[2]);
                    } else
                    {
                        $eventManager->unRegisterEventHandler($arEvent[0],$arEvent[1], $moduleId, $className, $arEvent[2]);
                    }
                }
            }
        }
    }

}