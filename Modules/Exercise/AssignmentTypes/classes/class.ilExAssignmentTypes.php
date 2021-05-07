<?php

/* Copyright (c) 1998-2018 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Assignment types. Gives information on available types and acts as factory
 * to get assignment type objects.
 *
 * @author killing@leifos.de
 * @ingroup ModulesExercise
 */
class ilExAssignmentTypes
{
    const STR_IDENTIFIER_UPLOAD = 'upld';
    const STR_IDENTIFIER_UPLOAD_TEAM = 'uptm';
    const STR_IDENTIFIER_BLOG = 'blog';
    const STR_IDENTIFIER_PORTFOLIO = "prtf";
    const STR_IDENTIFIER_TEXT = 'text';
    const STR_IDENTIFIER_WIKI_TEAM = 'wiki';

    /**
     * Relationship between string identifier and class name
     * Used for instantiation
     * @var string[]
     */
    protected $class_names = array(
        self::STR_IDENTIFIER_UPLOAD => "ilExAssTypeUpload",
        self::STR_IDENTIFIER_UPLOAD_TEAM => "ilExAssTypeUploadTeam",
        self::STR_IDENTIFIER_BLOG => "ilExAssTypeBlog",
        self::STR_IDENTIFIER_PORTFOLIO => "ilExAssTypePortfolio",
        self::STR_IDENTIFIER_TEXT => "ilExAssTypeText",
        self::STR_IDENTIFIER_WIKI_TEAM => "ilExAssTypeWikiTeam"
    );

    /**
     * Relationship between old integer ids and class names
     * Used in ilExerciseDataSet for import from former versions
     * @var string[]
     */
    protected $legacy_id_types = array(
        1 => "ilExAssTypeUpload",
        2 => "ilExAssTypeBlog",
        3 => "ilExAssTypePortfolio",
        4 => "ilExAssTypeUploadTeam",
        5 => "ilExAssTypeText",
        6 => "ilExAssTypeWikiTeam"
    );

    /**
     * @var ilExerciseInternalService
     */
    protected $service;

    /** @var ilAssignmentHookPlugin[] */
    protected $plugins;

    /**
     * Constructor
     */
    protected function __construct(ilExerciseInternalService $service = null)
    {
        global $DIC;

        $this->service = ($service == null)
            ? $DIC->exercise()->internal()->service()
            : $service;
    }

    /**
     * Get instance
     *
     * @return ilExAssignmentTypes
     */
    public static function getInstance()
    {
        return new self();
    }

    /**
     * Get the active plugins
     * @return ilAssignmentHookPlugin[]
     */
    protected function getActivePlugins()
    {
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
     * Get all assignment types
     * @return ilExAssignmentTypeInterface[] (indexed by identifier)
     */
    public function getAll()
    {
        $types = [];
        foreach ($this->class_names as $identifier => $name) {
            $types[$identifier] = $this->getByStringIdentifier($identifier);
        }

        foreach ($this->getActivePlugins() as $plugin) {
            foreach ($plugin->getAssignmentTypeStringIdentifiers() as $identifier) {
                $types[$identifier] = $plugin->getAssignmentTypeGuiByStringIdentifier($identifier);
            }
        }
        return $types;
    }
    
    /**
     * Get all activated assignment types
     * @return ilExAssignmentTypeInterface[]
     */
    public function getAllActivated()
    {
        return array_filter($this->getAll(), function (ilExAssignmentTypeInterface $at) {
            return $at->isActive();
        });
    }

    /**
     * Get all allowed types for an exercise for an exercise
     *
     * @param ilObjExercise $exc
     * @return ilExAssignmentTypeInterface[]
     */
    public function getAllAllowed(ilObjExercise $exc)
    {
        $random_manager = $this->service->getRandomAssignmentManager($exc);
        $active = $this->getAllActivated();

        // no team assignments, if random mandatory assignments is activated
        if ($random_manager->isActivated()) {
            $active = array_filter($active, function (ilExAssignmentTypeInterface $at) {
                return !$at->usesTeams();
            });
        }
        return $active;
    }


    /**
     * Get type object by string identifier
     *
     * @param string $a_identifier
     * @return ilExAssignmentTypeInterface
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
                    return $plugin->getAssignmentTypeByStringIdentifier($identifier);
                }
            }
        }

        return new ilExAssTypeInactive();
    }

    /**
     * Get the type string identifier for a given submission type
     *
     * @param int $a_submission_type
     * @return string[]
     */
    public function getStringIdentifiersForSubmissionType($a_submission_type)
    {
        $string_ids = [];
        foreach ($this->getAll() as $identifier => $type) {
            if ($type->getSubmissionType() == $a_submission_type) {
                $string_ids[] = $identifier;
            }
        }
        return $string_ids;
    }


    /**
     * Get type object by string identifier
     *
     * @param int $a_id
     * @return ilExAssignmentTypeInterface
     */
    public function getByLegacyId(int $a_id)
    {
        if (isset($this->legacy_id_types[$a_id])) {
            $class = $this->legacy_id_types[$a_id];
            return new $class();
        }

        return new ilExAssTypeInactive();
    }
}
