<?php

/**
 * TA Wrapper for a new question type
 */
class assWrappedQuestion extends assQuestion
{

	protected ilQuestionFactory $factory;


	/**
	 * Constructor
	 *
	 * The constructor takes possible arguments and creates an instance of the question object.
	 *
	 * @param string $title A title string to describe the question
	 * @param string $comment A comment string to describe the question
	 * @param string $author A string containing the name of the questions author
	 * @param integer $owner A numerical ID to identify the owner/creator
	 * @param string $question Question text
	 * @access public
	 *
	 * @see assQuestion
	 */
	public function __construct(
		string $title = "",
		string $comment = "",
		string $author = "",
		int $owner = -1,
		string $question = ""
	)
	{
		parent::__construct($title, $comment, $author, $owner, $question);
	}

	/**
	 * This function should be called directly after the constructor
	 */
	public function init(ilQuestionFactory $factory): self
	{
		$this->factory = $factory;
		return $this;
	}

	/**
	 * Make the questionfactory available for the feedback object (question is injected there)
	 */
	public function getFactory() : ilQuestionFactory
	{
		return $this->factory;
	}

	/**
	 * @return ilQuestionBaseSettings|null
	 */
	public function getStoredBasicSettings()
	{
		global $DIC;
		$repo = new ilQuestionBaseRepo($DIC->database());
		return $repo->getBaseSettingsForId($this->getId());
	}

	/**
	 * Returns the question type of the question
	 *
	 * @return string The question type of the question
	 */
	public function getQuestionType() : string
	{
		return $this->factory->getTypeTag();
	}


	/**
	 * Collects all texts in the question which could contain media objects
	 * which were created with the Rich Text Editor
	 */
	protected function getRTETextWithMediaObjects(): string
	{
		$text = parent::getRTETextWithMediaObjects();

		// eventually add the content of question type specific text fields
		// ..

		return (string) $text;
	}

	/**
	 * Returns true, if the question is complete
	 *
	 * @return boolean True, if the question is complete for use, otherwise false
	 */
	public function isComplete(): bool
	{
		// Please add here your own check for question completeness
		// The parent function will always return false
		if(!empty($this->title) && !empty($this->author) && !empty($this->question) && $this->getMaximumPoints() > 0)
		{
			return true;
		}
		else
		{
			return false;
		}
	}

	/**
	 * Saves a question object to a database
	 * 
	 * @param	string		$original_id
	 * @access 	public
	 * @see assQuestion::saveToDb()
	 */
	function saveToDb($original_id = ''): void
	{

		// save the basic data (implemented in parent)
		// a new question is created if the id is -1
		// afterwards the new id is set
		if ($original_id == '') {
			$this->saveQuestionDataToDb();
		} else {
			$this->saveQuestionDataToDb($original_id);
		}

		// Now you can save additional data
		// ...

		// save stuff like suggested solutions
		// update the question time stamp and completion status
		parent::saveToDb();
	}

	/**
	 * Loads a question object from a database
	 * This has to be done here (assQuestion does not load the basic data)!
	 *
	 * @param integer $question_id A unique key which defines the question in the database
	 * @see assQuestion::loadFromDb()
	 */
	public function loadFromDb(int $question_id): void
	{
		global $DIC;

		$repo = new ilQuestionBaseRepo($DIC->database());
		if (!empty($base_settings = $repo->getBaseSettingsForId($question_id))) {
			$this->setId($base_settings->getQuestionId());
			$this->setObjId($base_settings->getObjId());
			$this->setTitle($base_settings->getTitle());
			$this->setComment($base_settings->getComment());
			$this->setQuestion(ilRTE::_replaceMediaObjectImageSrc($base_settings->getQuestion(), 1));
			$this->setAuthor($base_settings->getAuthor());
			$this->setOwner($base_settings->getOwner());
			list($hours, $minutes, $seconds) = $base_settings->getEstimatedWorkingTimeParts();
			$this->setEstimatedWorkingTime($hours, $minutes, $seconds);
			$this->setPoints($base_settings->getMaxPoints());
			$this->setNrOfTries($base_settings->getNrOfTries());
			$this->setLastChange($base_settings->getModified());
			$this->setOriginalId($base_settings->getOriginalId());
			$this->setExternalId($base_settings->getExternalId());

			try {
				$this->setAdditionalContentEditingMode($base_settings->getAdditionalContentEditiongMode());
			}
			catch(ilTestQuestionPoolException $e) {
			}

			try {
				$this->setLifecycle(ilAssQuestionLifecycle::getInstance($base_settings->getLifecycle()));
			} catch (ilTestQuestionPoolInvalidArgumentException $e) {
				$this->setLifecycle(ilAssQuestionLifecycle::getDraftInstance());
			}
		}

		// loads additional stuff like suggested solutions
		parent::loadFromDb($question_id);
	}
	

	/**
	 * Duplicates a question
	 * This is used for copying a question to a test
	 *
	 * @param bool   		$for_test
	 * @param string 		$title
	 * @param string 		$author
	 * @param string 		$owner
	 * @param integer|null	$testObjId
	 *
	 * @return void|integer Id of the clone or nothing.
	 */
	public function duplicate(bool $for_test = true, string $title = "", string $author = "", string $owner = "", $testObjId = null): int
	{
		if ($this->getId() <= 0)
		{
			// The question has not been saved. It cannot be duplicated
			return 0;
		}

		// make a real clone to keep the actual object unchanged
		$clone = clone $this;
							
		$original_id = assQuestion::_getOriginalId($this->getId());
		$clone->setId(-1);

		if( (int) $testObjId > 0 )
		{
			$clone->setObjId($testObjId);
		}

		if (!empty($title))
		{
			$clone->setTitle($title);
		}
		if (!empty($author))
		{
			$clone->setAuthor($author);
		}
		if (!empty($owner))
		{
			$clone->setOwner($owner);
		}		
		
		if ($for_test)
		{
			$clone->saveToDb($original_id);
		}
		else
		{
			$clone->saveToDb();
		}		

		// copy question page content
		$clone->copyPageOfQuestion($this->getId());
		// copy XHTML media objects
		$clone->copyXHTMLMediaObjectsOfQuestion($this->getId());

		// call the event handler for duplication
		$clone->onDuplicate($this->getObjId(), $this->getId(), $clone->getObjId(), $clone->getId());

		return $clone->getId();
	}

	/**
	 * Copies a question
	 * This is used when a question is copied on a question pool
	 *
	 * @param integer	$target_questionpool_id
	 * @param string	$title
	 *
	 * @return void|integer Id of the clone or nothing.
	 */
	function copyObject($target_questionpool_id, $title = '')
	{
		if ($this->getId() <= 0)
		{
			// The question has not been saved. It cannot be duplicated
			return;
		}

		// make a real clone to keep the object unchanged
		$clone = clone $this;
				
		$original_id = assQuestion::_getOriginalId($this->getId());
		$source_questionpool_id = $this->getObjId();
		$clone->setId(-1);
		$clone->setObjId($target_questionpool_id);
		if (!empty($title))
		{
			$clone->setTitle($title);
		}
				
		// save the clone data
		$clone->saveToDb();

		// copy question page content
		$clone->copyPageOfQuestion($original_id);
		// copy XHTML media objects
		$clone->copyXHTMLMediaObjectsOfQuestion($original_id);

		// call the event handler for copy
		$clone->onCopy($source_questionpool_id, $original_id, $clone->getObjId(), $clone->getId());

		return $clone->getId();
	}

	/**
	 * Create a new original question in a question pool for a test question
	 * @param int $targetParentId			id of the target question pool
	 * @param string $targetQuestionTitle
	 * @return int|void
	 */
	public function createNewOriginalFromThisDuplicate($targetParentId, $targetQuestionTitle = '')
	{
		if ($this->id <= 0)
		{
			// The question has not been saved. It cannot be duplicated
			return;
		}

		$sourceQuestionId = $this->id;
		$sourceParentId = $this->getObjId();

		// make a real clone to keep the object unchanged
		$clone = clone $this;
		$clone->setId(-1);

		$clone->setObjId($targetParentId);

		if (!empty($targetQuestionTitle))
		{
			$clone->setTitle($targetQuestionTitle);
		}

		$clone->saveToDb();
		// copy question page content
		$clone->copyPageOfQuestion($sourceQuestionId);
		// copy XHTML media objects
		$clone->copyXHTMLMediaObjectsOfQuestion($sourceQuestionId);

		$clone->onCopy($sourceParentId, $sourceQuestionId, $clone->getObjId(), $clone->getId());

		return $clone->getId();
	}

	/**
	 * Synchronize a question with its original
	 * You need to extend this function if a question has additional data that needs to be synchronized
	 * 
	 * @access public
	 */
	function syncWithOriginal(): void
	{
		parent::syncWithOriginal();
	}


	/**
	 * Get a submitted solution array from $_POST
	 *
	 * In general this may return any type that can be stored in a php session
	 * The return value is used by:
	 * 		savePreviewData()
	 * 		saveWorkingData()
	 * 		calculateReachedPointsForSolution()
	 */
	protected function getSolutionSubmit() : ilQuestionSolution
	{
		// this has to be provided by the active question canvas
		$json = trim(ilUtil::stripSlashes($_POST['question'.$this->getId().'json']));
		return $this->factory->getSolutionHandler()->getSolutionFromJSON($json);
	}

	/**
	 * Get a stored solution for a user and test pass
	 *
	 * @param int 	$active_id		active_id of hte user
	 * @param int	$pass			number of the test pass
	 * @param bool	$authorized		get the authorized solution
	 */
	public function getSolutionStored($active_id, $pass, $authorized = null) : ilQuestionSolution
	{
		// This provides an array with records from tst_solution
		// The example question should only store one record per answer
		// Other question types may use multiple records with value1/value2 in a key/value style
		if (isset($authorized))
		{
			// this provides either the authorized or intermediate solution
			$solutions = $this->getSolutionValues($active_id, $pass, $authorized);
		}
		else
		{
			// this provides the solution preferring the intermediate
			// or the solution from the previous pass
			$solutions = $this->getTestOutputSolutions($active_id, $pass);
		}

		$pairs = [];
		foreach ($solutions as $row) {
			$pairs = new ilQuestionSolutionValuePair($row['value1'], $row['value2']);
		}
		return $this->factory->getSolutionHandler()->getSolutionFromValuePairs($pairs);
	}


	/**
	 * Calculate the reached points from a solution
	 * The json representation is coing from a preview session
	 *
	 * @param	ilQuestionSolution|string $solution object or json representation of the solution
	 * @return  float	reached points
	 */
	protected function calculateReachedPointsForSolution($solution)
	{
		if (is_string($solution)) {
			$solution = $this->factory->getSolutionHandler()->getSolutionFromJSON($solution);
		}

		$grader = $this->factory->getBackendGrader(
			$this->getStoredBasicSettings(),
			$this->factory->getTypeSettings($this->getId())
		);

		// return the raw points given to the answer
		// these points will afterwards be adjusted by the scoring options of a test
		return $grader->getReachedPoints($solution);
	}


	/**
	 * Returns the points, a learner has reached answering the question
	 * The points are calculated from the given answers.
	 *
	 * @param int $active_id
	 * @param integer $pass The Id of the test pass
	 * @param bool $authorizedSolution
	 * @param boolean $returndetails (deprecated !!)
	 * @return int
	 *
	 * @throws ilTestException
	 */
	public function calculateReachedPoints($active_id, $pass = NULL, $authorizedSolution = true, $returndetails = false)
	{
		if( $returndetails )
		{
			throw new ilTestException('return details not implemented for '.__METHOD__);
		}

		if(is_null($pass))
		{
			$pass = $this->getSolutionMaxPass($active_id);
		}

		// get the answers of the learner from the tst_solution table
		// the data is saved by saveWorkingData() in this class
		$solution = $this->getSolutionStored($active_id, $pass, $authorizedSolution);

		return $this->calculateReachedPointsForSolution($solution);
	}


	/**
	 * Saves the learners input of the question to the database.
	 *
	 * @param integer $active_id 	Active id of the user
	 * @param integer $pass 		Test pass
	 * @param boolean $authorized	The solution is authorized
	 *
	 * @return boolean $status
	 */
	function saveWorkingData($active_id, $pass = NULL, $authorized = true): bool
	{
		if (is_null($pass))
		{
			$pass = ilObjTest::_getPass($active_id);
		}

		// get the submitted solution
		$solution = $this->getSolutionSubmit();

		$entered_values = 0;

		// save the submitted values avoiding race conditions
		$this->getProcessLocker()->executeUserSolutionUpdateLockOperation(function() use (&$entered_values, $solution, $active_id, $pass, $authorized) {

			$entered_values = !$solution->isEmpty();

			if ($authorized)
			{
				// a new authorized solution will delete the old one and the intermediate
				$this->removeExistingSolutions($active_id, $pass);
			}
			elseif ($entered_values)
			{
				// an new intermediate solution will only delete a previous one
				$this->removeIntermediateSolution($active_id, $pass);
			}

			if ($entered_values)
			{
				$this->saveCurrentSolution($active_id, $pass, $solution['value1'],  $solution['value2'], $authorized);
			}
		});


		// Log whether the user entered values
		if (ilObjAssessmentFolder::_enabledAssessmentLogging())
		{
			assQuestion::logAction($this->lng->txtlng(
				'assessment',
				$entered_values ? 'log_user_entered_values' : 'log_user_not_entered_values',
				ilObjAssessmentFolder::_getLogLanguage()
			),
				$active_id,
				$this->getId()
			);
		}

		// submitted solution is valid
		return true;
	}

	/**
	 * Save a posted solution in the preview session
	 * This must be JSON because objects can't be stred directly
	 */
	protected function savePreviewData(ilAssQuestionPreviewSession $previewSession): void
	{
		$previewSession->setParticipantsSolution($this->getSolutionSubmit()->toJSON());
	}


	/**
	 * Creates an Excel worksheet for the detailed cumulated results of this question
	 *
	 * @param object $worksheet    Reference to the parent excel worksheet
	 * @param int $startrow     Startrow of the output in the excel worksheet
	 * @param int $active_id    Active id of the participant
	 * @param int $pass         Test pass
	 *
	 * @return int
	 */
	public function setExportDetailsXLS(ilAssExcelFormatHelper $worksheet, int $startrow, int $active_id, int $pass): int
	{
		$worksheet->setFormattedExcelTitle($worksheet->getColumnCoord(0) . $startrow, $this->factory->getTypeTranslation());
		$worksheet->setFormattedExcelTitle($worksheet->getColumnCoord(1) . $startrow, $this->getTitle());

		$solution = $this->getSolutionStored($active_id, $pass, true);

		$row = $startrow + 1;
		foreach ($solution->toValuePairs() as $pair)
		{
			$worksheet->setCell($row, 0, 'label_value1');
			$worksheet->setBold($worksheet->getColumnCoord(0) . $row);
			$worksheet->setCell($row, 1, $pair->getValue1());
			$row++;

			$worksheet->setCell($row, 0, 'label_value2');
			$worksheet->setBold($worksheet->getColumnCoord(0) . $row);
			$worksheet->setCell($row, 1, $pair->getValue2());
			$row++;
		}
		return $row + 1;
	}

	/**
	 * Creates a question from a QTI file
	 *
	 * Receives parameters from a QTI parser and creates a valid ILIAS question object
	 *
	 * @param object $item The QTI item object
	 * @param integer $questionpool_id The id of the parent questionpool
	 * @param integer $tst_id The id of the parent test if the question is part of a test
	 * @param object $tst_object A reference to the parent test object
	 * @param integer $question_counter A reference to a question counter to count the questions of an imported question pool
	 * @param array $import_mapping An array containing references to included ILIAS objects
	 * @access public
	 */
	function fromXML($item, int $questionpool_id, ?int $tst_id, &$tst_object, int &$question_counter,  array $import_mapping, array &$solutionhints = []): array
	{
		// todo
		return $import_mapping;
	}

	/**
	 * Returns a QTI xml representation of the question and sets the internal
	 * domxml variable with the DOM XML representation of the QTI xml representation
	 *
	 * @return string The QTI xml representation of the question
	 * @access public
	 */
	function toXML(
		bool $a_include_header = true,
		bool $a_include_binary = true,
		bool $a_shuffle = false,
		bool $test_output = false,
		bool $force_image_references = false
	): string
	{
		// todo
		return '';
	}
}

?>
