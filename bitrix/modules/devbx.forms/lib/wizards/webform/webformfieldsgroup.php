<?php

namespace DevBx\Forms\Wizards\WebForm;

use DevBx\Forms\WebForm\Fields;

class WebFormFieldsGroup {

    protected $groupId;
    protected $name;
    protected $sort;
    protected $manager;

    public function __construct(FieldManager $manager, $groupId, $name, $sort)
    {
        $this->manager = $manager;
        $this->groupId = $groupId;
        $this->name = $name;
        $this->sort = $sort;
    }

    public function getName()
    {
        return $this->name;
    }

    public function getSort()
    {
        return $this->sort;
    }

    public function getManager()
    {
        return $this->manager;
    }

    /* @return Fields\Base[] */
    public function getFields()
    {
        $data = [];

        foreach ($this->manager->getWebFormField(false) as $ar)
        {
            /* @var Fields\Base $className */
            $className = $ar['CLASS_NAME'];

            if ($className::getGroupId() == $this->groupId)
            {
                $data[] = $ar;
            }
        }

        uasort($data, function($a, $b) {
            if ($a['SORT'] == $b['SORT'])
                return 0;

            return $a['SORT']>$b['SORT'] ? 1 : -1;
        });

        $classList = [];

        foreach ($data as $ar)
        {
            $classList[] = $ar['CLASS_NAME'];
        }

        return $classList;
    }

}