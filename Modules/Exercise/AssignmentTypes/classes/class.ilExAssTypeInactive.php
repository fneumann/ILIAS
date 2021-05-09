<?php

/* Copyright (c) 1998-2018 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once("./Modules/Exercise/AssignmentTypes/classes/interface.ilExAssignmentTypeInterface.php");

/**
 * Dummy assignment type for inactive plugins.
 */
class ilExAssTypeInactive implements ilExAssignmentTypeInterface
{
    /**
     * @var ilLanguage
     */
    protected $lng;

    /**
     * Constructor
     *
     * @param ilLanguage|null $a_lng
     */
    public function __construct(ilLanguage $a_lng = null)
    {
        global $DIC;

        $this->lng = ($a_lng)
            ? $a_lng
            : $DIC->language();
    }

    /**
     * @inheritdoc
     */
    public function isActive()
    {
        return false;
    }

    /**
     * @inheritdoc
     */
    public function usesTeams()
    {
        return false;
    }

    /**
     * @inheritdoc
     */
    public function hasFiles()
    {
        return false;
    }

    /**
     * @inheritdoc
     */
    public function usesFileUpload()
    {
        return false;
    }

    /**
     * @inheritdoc
     */
    public function getTitle()
    {
        $lng = $this->lng;

        return $lng->txt("exc_type_inactive");
    }

    /**
     * @inheritdoc
     */
    public function getSubmissionType()
    {
        return ilExSubmission::TYPE_INACTIVE;
    }

    /**
     * @inheritdoc
     */
    public function isSubmissionAssignedToTeam()
    {
        return false;
    }

    /**
     * @inheritdoc
     */
    public function cloneSpecificProperties(ilExAssignment $source, ilExAssignment $target)
    {
    }

    /**
     * @inheritdoc
     */
    public function deleteSpecificProperties(ilExAssignment $assignment)
    {
    }

    /**
     * Returns if the submission has support to web access directory.
     *
     * @return bool
     */
    public function supportsWebDirAccess() : bool {
        return false;
    }

    /**
     * Returns the short string identifier (used for direct access tu submission in web directory)
     * @return string
     */
    public function getStringIdentifier() : string {

        return '';
    }

}
