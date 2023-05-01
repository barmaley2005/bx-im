<?php

namespace DevBx\Forms\WebForm\Types;

use DevBx\Core\MSLang;
use DevBx\Forms\WebForm\MSLang\StackVariableFields;
use DevBx\Forms\WebForm\WOForm;

class ConditionType extends ObjectType {

    public function __construct(string $name, $parameters = array())
    {
        $parameters['fields'] = array(
            (new EnumType('VALUE'))->configureValues(array(
                array('value'=>'always'),
                array('value'=>'never'),
                array('value'=>'when'),
            )),
            (new StringType('CODE'))->configureDefaultValue(''),
        );

        parent::__construct($name, $parameters);
    }

    public function configureDefaultValue($value)
    {
        foreach ($value as $k=>$v)
        {
            $this->fields->sysGetRawValue($k)->configureDefaultValue($v);
        }

        return $this;
    }

    public function checkCondition(WOForm $obForm)
    {
        $value = $this->fields->value;
        if ($value == 'always')
            return true;

        if ($value == 'never')
            return false;

        $lexer = new MSLang\CodeLexer($this->fields->code);
        $parser = new MSLang\CodeParser($lexer);

        $nodeList = [];

        try {
            $parser->parseCode($nodeList, true, true, MSLang\LexerTypeArray::one(MSLang\LexerType::ltEof));

            $interpreter = new MSLang\Interpreter();
            $interpreter->registerHandlers();

            $context = new MSLang\ContextInterpreter($nodeList, $interpreter);
            $context->registerConst();
            $context->setVariable('DateTime', new MSLang\StackVariableDatetime(null));

            $context->setVariable('Fields', new StackVariableFields($obForm->getWebFormValues()));

            $returnVal = $context->exec(true);

            if (!$returnVal)
                return false;

            return $returnVal->value === true;
        } catch (\Exception $e)
        {
            return false;
        }
    }

}