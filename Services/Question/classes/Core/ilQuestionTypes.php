<?php

/**
 * Service for handling the new question types
 */
class ilQuestionTypes
{
    protected static self $instance;

    protected \ILIAS\DI\Container $dic;
    protected ilComponentFactory $component_factory;


    /** @var ilQuestionTypeFactory[] indexed by type tag */
    protected array $factories = [];

    /**
     * @todo: add to DIC instead?
     */
    public static function instance()
    {
        global $DIC;
        if (!isset(self::$instance)) {
            self::$instance = new self($DIC);
        }
        return self::$instance;
    }

    /**
     * The constructor loads all factories of the new question types
     * @todo load the factories of migrated core question tyoes later
     */
    protected function __construct(\ILIAS\DI\Container $dic)
    {
        $this->dic = $dic;
        $this->component_factory = $dic['component.factory'];

        /** @var ilQuestionTypePlugin $pl */
        foreach ($this->component_factory->getActivePluginsInSlot("qtype") as $pl) {
            $factory = $pl->factory();
            $this->factories[$factory->getTypeTag()] = $pl->factory();
        }
    }

    /**
     * Check if it is a new question type by the existence of a factory
     */
    public function hasFactory(string $type_tag)
    {
        return $this->factories[$type_tag];
    }

    /**
     * Get the factory of a question type
     */
    public function getFactory($type_tag) : ?ilQuestionTypeFactory
    {
        return $this->factories[$type_tag] ?? null;
    }

    /**
     * Get a translated title of a question type that can be used in lists
     */
    public function getTypeTranslation(string $type_tag) :?string
    {
        return $this->getFactory($type_tag)?->getTypeTranslation();
    }

}