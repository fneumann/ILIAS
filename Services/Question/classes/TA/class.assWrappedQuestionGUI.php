<?php

/**
 * Example GUI class for question type plugins
 *
 * @author	Fred Neumann <fred.neumann@fau.de>
 * @version	$Id:  $
 * @ingroup ModulesTestQuestionPool
 *
 * @ilctrl_iscalledby assWrappedQuestionGUI: ilObjQuestionPoolGUI, ilObjTestGUI, ilQuestionEditGUI, ilTestExpressPageObjectGUI
 * @ilctrl_calls assWrappedQuestionGUI: ilFormPropertyDispatchGUI
 */
class assWrappedQuestionGUI extends assQuestionGUI
{
	protected ilQuestionFactory $factory;
	protected \ILIAS\DI\Container $dic;

	/**
	 * @var assWrappedQuestion	The question object
	 */
	public assQuestion $object;
	
	/**
	* Constructor
	*
	* @param integer $id The database id of a question object
	* @access public
	*/
	public function __construct($id = -1)
	{
		global $DIC;

		parent::__construct();

		$this->dic = $DIC;
		$this->object = new assWrappedQuestion();
		if ($id >= 0)
		{
			$this->object->loadFromDb($id);
		}
	}

	/**
	 * This function should be called directly after the constructor
	 */
	public function init(ilQuestionFactory $factory): self
	{
		$this->factory = $factory;
		$this->object->init($factory);
		return $this;
	}


	/**
	 * Creates an output of the edit form for the question
	 *
	 * @param bool $checkonly
	 * @return bool
	 */
	public function editQuestion($checkonly = false)
	{
		global $DIC;
		$lng = $DIC->language();

		$form = new ilPropertyFormGUI();
		$form->setFormAction($this->ctrl->getFormAction($this));
		$form->setTitle($this->outQuestionType());
		$form->setMultipart(TRUE);
		$form->setTableWidth("100%");
		$form->setId($this->factory->getTypeTag());

		// Title, author, description, question, working time
		$this->addBasicQuestionFormProperties($form);

		// Here you can add question type specific form properties
		// We only add an input field for the maximum points
		// NOTE: in complex question types the maximum points are summed up by partial points
		$points = new ilNumberInputGUI($lng->txt('maximum_points'),'points');
		$points->setSize(3);
		$points->setMinValue(1);
		$points->allowDecimals(0);
		$points->setRequired(true);
		$points->setValue($this->object->getPoints());
		$form->addItem($points);

		$this->populateTaxonomyFormSection($form);
		$this->addQuestionFormCommandButtons($form);

		$errors = false;
		if ($this->isSaveCommand())
		{
			$form->setValuesByPost();
			$errors = !$form->checkInput();
			$form->setValuesByPost(); // again, because checkInput now performs the whole stripSlashes handling and we need this if we don't want to have duplication of backslashes
			if ($errors)
			{
				$checkonly = false;
			}
		}

		if (!$checkonly)
		{
			$this->getQuestionTemplate();
			$this->tpl->setVariable("QUESTION_DATA", $form->getHTML());
		}

		return $errors;
	}

	/**
	 * Evaluates a posted edit form and writes the form data in the question object
	 *
	 * @param bool $always
	 * @return integer A positive value, if one of the required fields wasn't set, else 0
	 */
	protected function writePostData($always = false): int
	{
		$hasErrors = (!$always) ? $this->editQuestion(true) : false;
		if (!$hasErrors)
		{
			$this->writeQuestionGenericPostData();

			// Here you can write the question type specific values
			// Some question types define the maximum points directly,
			// other calculate them from other properties
			$this->object->setPoints((int) $_POST["points"]);

			$this->saveTaxonomyAssignments();
			return 0;
		}
		return 1;
	}


	/**
	 * Get the HTML output of the question for a test
	 * (this function could be private)
	 * 
	 * @param integer $active_id						The active user id
	 * @param integer $pass								The test pass
	 * @param boolean $is_postponed						Question is postponed
	 * @param boolean $use_post_solutions				Use post solutions
	 * @param boolean $show_specific_inline_feedback	Show a specific inline feedback
	 * @return string
	 */
	public function getTestOutput($active_id, $pass = NULL, $is_postponed = FALSE, $use_post_solutions = FALSE, $show_specific_inline_feedback = FALSE): string
	{
		if (is_null($pass))
		{
			$pass = ilObjTest::_getPass($active_id);
		}

		$solution = $this->object->getSolutionStored($active_id, $pass, null);
		$value1 = isset($solution["value1"]) ? $solution["value1"] : "";
		$value2 = isset($solution["value2"]) ? $solution["value2"] : "";

		// fill the question output template
		// in out example we have 1:1 relation for the database field
		$template = new ilTemplate("tpl.qtype_ta_output.html", true, true, "Services/Question");

		$template->setVariable("QUESTION_ID", $this->object->getId());
		$questiontext = $this->object->getQuestion();
		$template->setVariable("QUESTIONTEXT", $this->object->prepareTextareaOutput($questiontext, TRUE));
		$template->setVariable("LABEL_VALUE1", $this->lng->txt('label_value1'));
		$template->setVariable("LABEL_VALUE2", $this->lng->txt('label_value2'));

		$template->setVariable("VALUE1", ilLegacyFormElementsUtil::prepareFormOutput($value1));
		$template->setVariable("VALUE2", ilLegacyFormElementsUtil::prepareFormOutput($value2));

		$questionoutput = $template->get();
		$pageoutput = $this->outQuestionPage("", $is_postponed, $active_id, $questionoutput);
		return $pageoutput;
	}

	
	/**
	 * Get the output for question preview
	 * (called from ilObjQuestionPoolGUI)
	 * 
	 * @param boolean	$show_question_only 	show only the question instead of embedding page (true/false)
	 * @param boolean	$show_question_only
	 * @return string
	 */
	public function getPreview($show_question_only = FALSE, $showInlineFeedback = FALSE)
	{
		if( is_object($this->getPreviewSession()) )
		{
			$solution = $this->getPreviewSession()->getParticipantsSolution();
		}
		else
		{
			$solution = array('value1' => null, 'value2' => null);
		}

		// Fill the template with a preview version of the question
		$template = new ilTemplate("tpl.qtype_ta_output.html", true, true, "Services/Question");
		$questiontext = $this->object->getQuestion();
		$template->setVariable("QUESTIONTEXT", $this->object->prepareTextareaOutput($questiontext, TRUE));
		$template->setVariable("QUESTION_ID", $this->object->getId());
		$template->setVariable("LABEL_VALUE1", $this->lng->txt('label_value1'));
		$template->setVariable("LABEL_VALUE2", $this->lng->txt('label_value2'));

		$template->setVariable("VALUE1", ilLegacyFormElementsUtil::prepareFormOutput($solution['value1'] ?? ''));
		$template->setVariable("VALUE2", ilLegacyFormElementsUtil::prepareFormOutput($solution['value2'] ?? ''));

		$questionoutput = $template->get();
		if(!$show_question_only)
		{
			// get page object output
			$questionoutput = $this->getILIASPage($questionoutput);
		}
		return $questionoutput;
	}

	/**
	 * Get the question solution output
	 * @param integer $active_id             The active user id
	 * @param integer $pass                  The test pass
	 * @param boolean $graphicalOutput       Show visual feedback for right/wrong answers
	 * @param boolean $result_output         Show the reached points for parts of the question
	 * @param boolean $show_question_only    Show the question without the ILIAS content around
	 * @param boolean $show_feedback         Show the question feedback
	 * @param boolean $show_correct_solution Show the correct solution instead of the user solution
	 * @param boolean $show_manual_scoring   Show specific information for the manual scoring output
	 * @param bool    $show_question_text

	 * @return string solution output of the question as HTML code
	 */
	function getSolutionOutput(
		$active_id,
		$pass = NULL,
		$graphicalOutput = FALSE,
		$result_output = FALSE,
		$show_question_only = TRUE,
		$show_feedback = FALSE,
		$show_correct_solution = FALSE,
		$show_manual_scoring = FALSE,
		$show_question_text = TRUE
	): string
	{

		$base_settings = $this->object->getStoredBasicSettings();
		$type_settings = $this->factory->getTypeSettings($this->object->getId());
		$grader = $this->factory->getBackendGrader($base_settings, $type_settings);

		if ($show_correct_solution) {
			$solution = $grader->getCorrectSolution();
			$feedback = null;
		}
		else {
			$solution = $this->object->getSolutionStored($active_id, $pass, true);
			$feedback = $graphicalOutput ? $grader->getTypeFeedback($solution) : null;
		}

		if (!$show_question_text) {
			$base_settings = $base_settings->withQuestion('');
		}

		$canvas = $this->dic->ui()->factory()->question()->canvas()->inactive()
			->withPresentation($this->factory->getInactivePresentation(
				$base_settings,
				$type_settings,
				$solution,
				$feedback
			))->withPresentationRenderer($this->factory->getRenderer());

		$questionoutput = $this->dic->ui()->renderer()->render($canvas);

		$solutiontemplate = new ilTemplate("tpl.il_as_tst_solution_output.html", TRUE, TRUE, "Modules/TestQuestionPool");

		$feedback = ($show_feedback && !$this->isTestPresentationContext()) ? $this->getGenericFeedbackOutput($active_id, $pass) : "";
		if (strlen($feedback))
		{
			$cssClass = ( $this->hasCorrectSolution($active_id, $pass) ?
				ilAssQuestionFeedback::CSS_CLASS_FEEDBACK_CORRECT : ilAssQuestionFeedback::CSS_CLASS_FEEDBACK_WRONG
			);

			$solutiontemplate->setVariable("ILC_FB_CSS_CLASS", $cssClass);
			$solutiontemplate->setVariable("FEEDBACK", $this->object->prepareTextareaOutput( $feedback, true ));

		}
		$solutiontemplate->setVariable("SOLUTION_OUTPUT", $questionoutput);

		$solutionoutput = $solutiontemplate->get();
		if(!$show_question_only)
		{
			// get page object output
			$solutionoutput = $this->getILIASPage($solutionoutput);
		}
		return $solutionoutput;
	}

	/**
	 * Returns the answer specific feedback for the question
	 * 
	 * @param array $userSolution Array with the user solutions
	 * @return string HTML Code with the answer specific feedback
	 * @access public
	 */
	public function getSpecificFeedbackOutput($userSolution): string
	{
		// By default no answer specific feedback is defined
		$output = '';
		return $this->object->prepareTextareaOutput($output, TRUE);
	}
	
	
	/**
	* Sets the ILIAS tabs for this question type
	* called from ilObjTestGUI and ilObjQuestionPoolGUI
	*/
	public function setQuestionTabs(): void
	{
		parent::setQuestionTabs();
	}
}
?>
