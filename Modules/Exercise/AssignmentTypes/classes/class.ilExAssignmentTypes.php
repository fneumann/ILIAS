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
    private $class_names = array(
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
     * Get all ids
     *
     * @param
     * @return
     * @deprecated
     * @todo remove this, when refactoring is finished
     */
    private function getAllIds()
    {
        return [
            ilExAssignment::TYPE_UPLOAD,
            ilExAssignment::TYPE_UPLOAD_TEAM,
            ilExAssignment::TYPE_TEXT,
            ilExAssignment::TYPE_BLOG,
            ilExAssignment::TYPE_PORTFOLIO,
            ilExAssignment::TYPE_WIKI_TEAM
        ];
    }

    /**
     * Is valid id
     *
     * @param int $a_id
     * @return bool
     * @deprecated
     * @todo remove this, when refactoring is finished
     */
    private function isValidId($a_id)
    {
        return in_array($a_id, $this->getAllIds());
    }

    /**
     * Get all assignment types
     * @return ilExAssignmentTypeInterface[] (indexed by string identifier)
     */
    public function getAll()
    {
        $types = [];
        foreach ($this->class_names as $identifier => $name) {
            $types[$identifier] = $this->getByStringIdentifier($identifier);
        }

        foreach ($this->getActivePlugins() as $plugin) {
            foreach ($plugin->getAssignmentTypeStringIdentifiers() as $identifier) {
                $types[$identifier] = $plugin->getAssignmentTypeByStringIdentifier($identifier);
            }
        }
        return $types;
    }
    
    /**
     * Get all activated assignment types
     * @return ilExAssignmentTypeInterface[] (indexed by string identifier)
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
     * @return ilExAssignmentTypeInterface[] (indexed by string identifier)
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
     * Get type object by id
     *
     * Centralized ID management is still an issue to be tackled in the future and caused
     * by initial consts definition.
     *
     * @param int $a_id type id
     * @return ilExAssignmentTypeInterface
     * @deprecated
     * @todo remove this, when refactoring is finished
     */
    private function getById($a_id)
    {
        switch ($a_id) {
            case ilExAssignment::TYPE_UPLOAD:
                return new ilExAssTypeUpload();
                break;

            case ilExAssignment::TYPE_BLOG:
                return new ilExAssTypeBlog();
                break;

            case ilExAssignment::TYPE_PORTFOLIO:
                return new ilExAssTypePortfolio();
                break;

            case ilExAssignment::TYPE_UPLOAD_TEAM:
                return new ilExAssTypeUploadTeam();
                break;

            case ilExAssignment::TYPE_TEXT:
                return new ilExAssTypeText();
                break;

            case ilExAssignment::TYPE_WIKI_TEAM:
                return new ilExAssTypeWikiTeam();
                break;
        }

        // we should throw some exception here
    }

    /**
     * Get assignment type IDs for given submission type
     *
     * @param int $a_submission_type
     * @return array
     * @deprecated
     * @todo remove this, when refactoring is finished
     */
    private function getIdsForSubmissionType($a_submission_type)
    {
        $ids = [];
        foreach ($this->getAllIds() as $id) {
            if ($this->getById($id)->getSubmissionType() == $a_submission_type) {
                $ids[] = $id;
            }
        }
        return $ids;
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
