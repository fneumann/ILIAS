<?php

/**
 * Service to get the activ classic question pool plugins
 */
class ilQuestionPoolPlugins
{
    protected ilComponentFactory $component_factory;


    /** @var ilQuestionsPlugin[] indexed by type tag */
    protected ?array $plugins = null;


    /**
     * The constructor loads all factories of the new question types
     */
    public function __construct(ilComponentFactory $component_factory)
    {
        $this->component_factory = $component_factory;
        $this->load();
    }

    /**
     * Load the instances of classic question pool plugins
     */
    protected function load()
    {
        if (!isset($this->plugins)) {
            $this->plugins = [];
            /** @var ilQuestionsPlugin $plugin */
            foreach ($this->component_factory->getActivePluginsInSlot("qst") as $plugin) {
                $this->plugins[$plugin->getQuestionType()] = $plugin;
            }
        }
    }

    /**
     * Check if an active plugin for the question type exists
     */
    public function has(string $type) : bool
    {
        return isset($this->plugins[$type]);
    }

    /**
     * Get the plugin for a question type
     */
    public function get($type) : ?ilQuestionsPlugin
    {
        return $this->plugins[$type] ?? null;
    }
}