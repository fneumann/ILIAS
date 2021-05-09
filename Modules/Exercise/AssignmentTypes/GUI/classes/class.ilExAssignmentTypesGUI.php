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
     * Get type gui object by id
     *
     * Centralized ID management is still an issue to be tackled in the future and caused
     * by initial consts definition.
     *
     * @param int $a_id type id
     * @return ilExAssignmentTypeGUIInterface
     * @deprecated
     * @todo remove this, when refactoring is finished
     */
    private function getById($a_id)
    {
        // @todo: check id

        switch ($a_id) {
            case ilExAssignment::TYPE_UPLOAD:
                return new ilExAssTypeUploadGUI();
                break;

            case ilExAssignment::TYPE_BLOG:
                return new ilExAssTypeBlogGUI();
                break;

            case ilExAssignment::TYPE_PORTFOLIO:
                return new ilExAssTypePortfolioGUI();
                break;

            case ilExAssignment::TYPE_UPLOAD_TEAM:
                return new ilExAssTypeUploadTeamGUI();
                break;

            case ilExAssignment::TYPE_TEXT:
                return new ilExAssTypeTextGUI();
                break;

            case ilExAssignment::TYPE_WIKI_TEAM:
                return new ilExAssTypeWikiTeamGUI();
                break;
        }

        // we should throw some exception here
    }

    /**
     * Get the GUI for an assignment type
     * @param ilExAssignmentTypeInterface $a_type
     * @return ilExAssignmentTypeGUIInterface
     */
    public function getForType(ilExAssignmentTypeInterface $a_type)
    {
        return $this->getByStringIdentifier($a_type->getStringIdentifier());
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
                    return $plugin->getAssignmentTypeGUIByStringIdentifier($identifier);
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
            foreach ($plugin->getAssignmentTypeGUIClassNames() as $identifier => $cn) {
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
     * @param string
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
            foreach ($plugin->getAssignmentTypeGUIClassNames() as $identifier => $cn) {
                if (strtolower($cn) == strtolower($a_string)) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * Get type id for class name
     *
     * @param $a_string
     * @return null|int
     * @deprecated
     * @todo remove this, when refactoring is finished
     */
    private function getIdForClassName($a_string)
    {
        foreach ($this->class_names as $k => $cn) {
            if (strtolower($cn) == strtolower($a_string)) {
                return $k;
            }
        }
        return null;
    }
}
