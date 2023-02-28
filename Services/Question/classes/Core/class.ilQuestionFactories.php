<?php

/**
 * Service to get the factories of the new question types
 */
class ilQuestionFactories
{
    protected ilComponentFactory $component_factory;


    /** @var ilQuestionFactory[] indexed by type tag */
    protected ?array $factories = null;


    /**
     * The constructor loads all factories of the new question types
     */
    public function __construct(ilComponentFactory $component_factory)
    {
        $this->component_factory = $component_factory;
        $this->load();
    }

    /**
     * Load the instances of question factories
     * Must be calle before the internal factories array is accesed
     * @todo load the factories of core question types when they are migrated
     */
    protected function load()
    {
        if (!isset($this->factories)) {
            $this->factories = [];
            /** @var ilQuestionTypePlugin $pl */
            foreach ($this->component_factory->getActivePluginsInSlot("qtype") as $pl) {
                $factory = $pl->factory();
                $this->factories[$factory->getTypeTag()] = $pl->factory();
            }
        }
    }

    /**
     * Check if a factory for the question types exists
     */
    public function has(string $type) : bool
    {
        return isset($this->factories[$type]);
    }

    /**
     * Get the factory of a question type
     */
    public function get($type) : ?ilQuestionFactory
    {
        return $this->factories[$type] ?? null;
    }
}