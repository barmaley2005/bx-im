<?php

namespace DevBx\Forms\WebForm;

class WOFormPageCollection extends WOCollection
{

    public function __construct()
    {
        parent::__construct(WOFormPage::class);
    }
}