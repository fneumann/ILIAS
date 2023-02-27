<?php

/**
 * Base Settings that are common to all ILIAS questions
 */
class ilQuestionBaseSettings
{
    private int $question_id;
    private int $type_id;
    private int $obj_id;
    private string $title;
    private string $comment;
    private string $question;
    private string $author;
    private int $owner;
    private int $working_time;
    private int $max_points;
    private int $nr_of_tries;
    private bool $complete;
    private int $created;
    private int $modified;
    private ?int $original_id;
    private string $external_id;
    private string $additional_content_editiong_mode;
    private string $lifecycle;

    public function __construct(
        int $question_id = -1,
        int $type_id = 0,
        int $obj_id = 0,
        string $title = '',
        string $comment = '',
        string $question = '',
        string $author = '',
        int $owner = 0,
        int $working_time = 0,
        int $max_points = 0,
        int $nr_of_tries = 0,
        bool $complete = false,
        int $created = 0,
        int $modified = 0,
        ?int $original_id = null,
        string $external_id = '',
        string $additional_content_editiong_mode = '',
        string $lifecycle = 'draft',
    ) {
        $this->question_id = $question_id;
        $this->type_id = $type_id;
        $this->obj_id = $obj_id;
        $this->title = $title;
        $this->comment = $comment;
        $this->question = $question;
        $this->author = $author;
        $this->owner = $owner;
        $this->working_time = $working_time;
        $this->max_points = $max_points;
        $this->nr_of_tries = $nr_of_tries;
        $this->complete = $complete;
        $this->created = $created;
        $this->modified = $modified;
        $this->original_id = $original_id;
        $this->external_id = $external_id;
        $this->additional_content_editiong_mode = $additional_content_editiong_mode;
        $this->lifecycle = $lifecycle;
    }

    /**
     * Get the JSON encoded version of the settings
     */
    public function toJSON(): string
    {
        return json_encode($this);
    }

    /**
     * @return int
     */
    public function getQuestionId() : int
    {
        return $this->question_id;
    }

    /**
     * @param int $id
     * @return ilQuestionBaseSettings
     */
    public function withQuestionId(int $id) : ilQuestionBaseSettings
    {
        $clone = clone $this;
        $clone->question_id = $id;
        return $clone;
    }

    /**
     * @return int
     */
    public function getTypeId() : int
    {
        return $this->type_id;
    }

    /**
     * @return int
     */
    public function getObjId() : int
    {
        return $this->obj_id;
    }

    /**
     * @return string
     */
    public function getTitle() : string
    {
        return $this->title;
    }

    /**
     * @return string
     */
    public function getComment() : string
    {
        return $this->comment;
    }

    /**
     * @return string
     */
    public function getQuestion() : string
    {
        return $this->question;
    }

    /**
     * @param string $question
     * @return ilQuestionBaseSettings
     */
    public function withQuestion(string $question) : ilQuestionBaseSettings
    {
        $clone = clone $this;
        $clone->question = $question;
        return $clone;
    }

    /**
     * @return string
     */
    public function getAuthor() : string
    {
        return $this->author;
    }

    /**
     * @return int
     */
    public function getOwner() : int
    {
        return $this->owner;
    }

    /**
     * @return int
     */
    public function getEstimatedWorkingTime() : int
    {
        return $this->working_time;
    }

    /**
     * Get a three-element array with estimated working time in hours, minutes and seconds
     * @return int[]
     */
    public function getEstimatedWorkingTimeParts() : array
    {
        $hours = floor($this->working_time / 3600);
        $minutes = floor(($this->working_time % 3600) / 60);
        $seconds = $this->working_time % 60;

        return [$hours, $minutes, $seconds];
    }

    /**
     * @return int
     */
    public function getMaxPoints() : int
    {
        return $this->max_points;
    }

    /**
     * @return int
     */
    public function getNrOfTries() : int
    {
        return $this->nr_of_tries;
    }

    /**
     * @return bool
     */
    public function isComplete() : bool
    {
        return $this->complete;
    }

    /**
     * @return int
     */
    public function getCreated() : int
    {
        return $this->created;
    }

    /**
     * @return int
     */
    public function getModified() : int
    {
        return $this->modified;
    }

    /**
     * @return int|null
     */
    public function getOriginalId() : ?int
    {
        return $this->original_id;
    }

    /**
     * @return string
     */
    public function getExternalId() : string
    {
        return $this->external_id;
    }

    /**
     * @return string
     */
    public function getAdditionalContentEditiongMode() : string
    {
        return $this->additional_content_editiong_mode;
    }

    /**
     * @return string
     */
    public function getLifecycle() : string
    {
        return $this->lifecycle;
    }

}