<?php

/* Copyright (c) 1998-2018 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Assignment types gui.
 *
 * @author killing@leifos.de
 * @ingroup ModulesExercise
 */
class ilExAssignmentTypesGUI
{
    protected $class_names = array(
        ilExAssignmentTypes::STR_IDENTIFIER_UPLOAD => "ilExAssTypeUploadGUI",
        ilExAssignmentTypes::STR_IDENTIFIER_BLOG => "ilExAssTypeBlogGUI",
        ilExAssignmentTypes::STR_IDENTIFIER_PORTFOLIO => "ilExAssTypePortfolioGUI",
        ilExAssignmentTypes::STR_IDENTIFIER_UPLOAD_TEAM => "ilExAssTypeUploadTeamGUI",
        ilExAssignmentTypes::STR_IDENTIFIER_TEXT => "ilExAssTypeTextGUI",
        ilExAssignmentTypes::STR_IDENTIFIER_WIKI_TEAM => "ilExAssTypeWikiTeamGUI"
    );

    /** @var ilAssignmentHookPlugin[] */
    protected $plugins;


    /**
     * Constructor
     */
    protected function __construct()
    {
    }

    /**
     * Get instance
     *
     * @return ilExAssignmentTypesGUI
     */
    public static function getInstance()
    {
        return new self();
    }

    /**
     * Get the active plugins
     */
    protected function getActivePlugins() {
        if (!isset($this->plugins)) {
            $this->plugins = [];
            $names = ilPluginAdmin::getActivePluginsForSlot(IL_COMP_MODULE, 'Exercise', 'exashk');
            foreach ($names as $name) {
                $this->plugins[] = ilPlugin::getPluginObject(IL_COMP_MODULE, 'Exercise','exashk', $name);
            }
        }

        return $this->plugins;
    }

    /**
     * Get type object by string identifier
     *
     * @param string $a_identifier
     * @return ilExAssignmentTypeGUIInterface
     */
    public function getByStringIdentifier(string $a_identifier)
    {
        if (isset($this->class_names[$a_identifier])) {
            $class = $this->class_names[$a_identifier];
            return new $class();
        }

        foreach ($this->getActivePlugins() as $plugin) {
            foreach ($plugin->getAssignmentTypeStringIdentifiers() as $identifier) {
                if ($identifier == $a_identifier) {
                    return $plugin->getAssignmentTypeGuiByStringIdentifier($identifier);
                }
            }
        }

        return new ilExAssTypeInactiveGUI();
    }

    /**
     * Get type gui object by classname
     *
     * @param string $a_class_name
     * @return ilExAssignmentTypeGUIInterface
     */
    public function getByClassName($a_class_name)
    {
        foreach ($this->class_names as $identifier => $cn) {
            if (strtolower($cn) == strtolower($a_class_name)) {
                return $this->getByStringIdentifier($identifier);
            }
        }

        foreach ($this->getActivePlugins() as $plugin) {
            foreach ($plugin->getAssignmentTypeGuiClassNames() as $identifier => $cn) {
                if (strtolower($cn) == strtolower($a_class_name)) {
                    return $this->getByStringIdentifier($identifier);
                }
            }
        }

        return new ilExAssTypeInactiveGUI();
    }


    /**
     * Checks if a class name is a valid exercise assignment type GUI class
     * (case insensitive, since ilCtrl uses lower keys due to historic reasons)
     *
     * @param string $a_string
     * @return bool
     */
    public function isExAssTypeGUIClass($a_string)
    {
        foreach ($this->class_names as $cn) {
            if (strtolower($cn) == strtolower($a_string)) {
                return true;
            }
        }

        foreach ($this->getActivePlugins() as $plugin) {
            foreach ($plugin->getAssignmentTypeGuiClassNames() as $identifier => $cn) {
                if (strtolower($cn) == strtolower($a_string)) {
                    return true;
                }
            }
        }

        return false;
    }
}
