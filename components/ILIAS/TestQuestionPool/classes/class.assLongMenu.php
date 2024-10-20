<?php

/**
 * This file is part of ILIAS, a powerful learning management system
 * published by ILIAS open source e-Learning e.V.
 *
 * ILIAS is licensed with the GPL-3.0,
 * see https://www.gnu.org/licenses/gpl-3.0.en.html
 * You should have received a copy of said license along with the
 * source code, too.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 * https://www.ilias.de
 * https://github.com/ILIAS-eLearning
 *
 *********************************************************************/

declare(strict_types=1);

use ILIAS\TestQuestionPool\Questions\QuestionLMExportable;
use ILIAS\TestQuestionPool\Questions\QuestionAutosaveable;

use ILIAS\Test\Logging\AdditionalInformationGenerator;

class assLongMenu extends assQuestion implements ilObjQuestionScoringAdjustable, QuestionLMExportable, QuestionAutosaveable
{
    public const ANSWER_TYPE_SELECT_VAL = 0;
    public const ANSWER_TYPE_TEXT_VAL = 1;
    public const GAP_PLACEHOLDER = 'Longmenu';
    public const MIN_LENGTH_AUTOCOMPLETE = 3;
    public const MAX_INPUT_FIELDS = 500;

    protected const HAS_SPECIFIC_FEEDBACK = false;

    private ?array $answerType = null;
    private string $long_menu_text = '';
    private string $json_structure = '';
    private int $specificFeedbackSetting = ilAssConfigurableMultiOptionQuestionFeedback::FEEDBACK_SETTING_ALL;
    private int $minAutoComplete = self::MIN_LENGTH_AUTOCOMPLETE;
    private bool $identical_scoring = true;

    private array $correct_answers = [];
    private array $answers = [];


    public function getAnswerType(): ?array
    {
        return $this->answerType;
    }

    public function setAnswerType(array $answerType): void
    {
        $this->answerType = $answerType;
    }

    /**
     * @return mixed
     */
    public function getCorrectAnswers()
    {
        return $this->correct_answers;
    }


    public function setCorrectAnswers($correct_answers): void
    {
        $this->correct_answers = $correct_answers;
    }

    private function buildFolderName(): string
    {
        return ilFileUtils::getDataDir() . '/assessment/longMenuQuestion/' . $this->getId() . '/' ;
    }

    public function getAnswerTableName(): string
    {
        return "qpl_a_lome";
    }

    private function buildFileName($gap_id): ?string
    {
        try {
            $this->assertDirExists();
            return $this->buildFolderName() . $gap_id . '.txt';
        } catch (ilException $e) {
        }
        return null;
    }

    public function setLongMenuTextValue(string $long_menu_text = ''): void
    {
        $this->long_menu_text = $this->getHtmlQuestionContentPurifier()->purify($long_menu_text);
    }

    public function getLongMenuTextValue(): string
    {
        return $this->long_menu_text;
    }

    public function setAnswers($answers): void
    {
        $this->answers = $answers;
    }

    public function getAnswers(): array
    {
        return $this->answers;
    }

    /**
     * @return mixed
     */
    public function getJsonStructure(): string
    {
        return $this->json_structure;
    }

    /**
     * @param mixed $json_structure
     */
    private function setJsonStructure(string $json_structure): void
    {
        $this->json_structure = $json_structure;
    }

    public function setSpecificFeedbackSetting($specificFeedbackSetting): void
    {
        $this->specificFeedbackSetting = $specificFeedbackSetting;
    }

    public function getSpecificFeedbackSetting(): int
    {
        return $this->specificFeedbackSetting;
    }

    public function setMinAutoComplete($minAutoComplete): void
    {
        $this->minAutoComplete = $minAutoComplete;
    }

    public function getMinAutoComplete(): int
    {
        return $this->minAutoComplete ? $this->minAutoComplete : self::MIN_LENGTH_AUTOCOMPLETE;
    }

    public function isComplete(): bool
    {
        if (strlen($this->title)
            && $this->author
            && $this->long_menu_text
            && sizeof($this->answers) > 0
            && sizeof($this->correct_answers) > 0
            && $this->getPoints() > 0
        ) {
            return true;
        }
        return false;
    }

    public function saveToDb(?int $original_id = null): void
    {
        $this->saveQuestionDataToDb($original_id);
        $this->saveAdditionalQuestionDataToDb();
        $this->saveAnswerSpecificDataToDb();
        parent::saveToDb();
    }

    /**
     * @param ilPropertyFormGUI|null $form
     * @return bool
     */
    public function checkQuestionCustomPart($form = null): bool
    {
        $hidden_text_files = $this->getAnswers();
        $correct_answers = $this->getCorrectAnswers();
        $points = [];
        if ($correct_answers === null
            || $correct_answers === []
            || $hidden_text_files === null
            || $hidden_text_files === []) {
            return false;
        }
        if (sizeof($correct_answers) != sizeof($hidden_text_files)) {
            return false;
        }
        foreach ($correct_answers as $key => $correct_answers_row) {
            if ($this->correctAnswerDoesNotExistInAnswerOptions($correct_answers_row, $hidden_text_files[$key])) {
                return false;
            }
            if (!is_array($correct_answers_row[0]) || $correct_answers_row[0] === []) {
                return false;
            }
            if ($correct_answers_row[1] > 0) {
                array_push($points, $correct_answers_row[1]);
            }
        }
        if (sizeof($correct_answers) != sizeof($points)) {
            return false;
        }

        foreach ($points as $row) {
            if ($row <= 0) {
                return false;
            }
        }
        return true;
    }

    /**
     * @param $answers
     * @param $answer_options
     * @return bool
     */
    private function correctAnswerDoesNotExistInAnswerOptions($answers, $answer_options): bool
    {
        foreach ($answers[0] as $key => $answer) {
            if (!in_array($answer, $answer_options)) {
                return true;
            }
        }
        return false;
    }


    /**
     * Returns the maximum points, a learner can reach answering the question
     *
     * @access public
     * @see $points
     */
    public function getMaximumPoints(): float
    {
        $sum = 0;
        $points = $this->getCorrectAnswers();
        if ($points) {
            foreach ($points as $add) {
                $sum += (float) $add[1];
            }
        }
        return $sum;
    }

    public function saveAdditionalQuestionDataToDb()
    {
        // save additional data
        $this->db->manipulateF(
            "DELETE FROM " . $this->getAdditionalTableName() . " WHERE question_fi = %s",
            [ "integer" ],
            [ $this->getId() ]
        );
        $this->db->manipulateF(
            "INSERT INTO " . $this->getAdditionalTableName(
            ) . " (question_fi, long_menu_text, feedback_setting, min_auto_complete, identical_scoring) VALUES (%s, %s, %s, %s, %s)",
            [ "integer", "text", "integer", "integer", "integer"],
            [
                $this->getId(),
                $this->getLongMenuTextValue(),
                $this->getSpecificFeedbackSetting(),
                $this->getMinAutoComplete(),
                $this->getIdenticalScoring()
            ]
        );

        $this->createFileFromArray();
    }

    public function saveAnswerSpecificDataToDb(): void
    {
        $this->clearAnswerSpecificDataFromDb($this->getId());
        $type_array = $this->getAnswerType();
        $points = 0;
        foreach ($this->getCorrectAnswers() as $gap_number => $gap) {
            foreach ($gap[0] as $position => $answer) {
                if ($type_array == null) {
                    $type = $gap[2];
                } else {
                    $type = $type_array[$gap_number];
                }
                $this->db->replace(
                    $this->getAnswerTableName(),
                    [
                        'question_fi' => ['integer', $this->getId()],
                        'gap_number' => ['integer', (int) $gap_number],
                        'position' => ['integer', (int) $position]
                        ],
                    [
                        'answer_text' => ['text', $answer],
                        'points' => ['float', $gap[1]],
                        'type' => ['integer', (int) $type]
                        ]
                );
            }
            $points += $gap[1];
        }
        $this->setPoints($points);
    }

    private function createFileFromArray(): void
    {
        $array = $this->getAnswers();
        $this->clearFolder();
        foreach ($array as $gap => $values) {
            $file_content = '';
            if (is_array($values)) {
                foreach ($values as $key => $value) {
                    $file_content .= $value . "\n";
                }
                $file_content = rtrim($file_content, "\n");
                $file = fopen($this->buildFileName($gap), "w");
                fwrite($file, $file_content);
                fclose($file);
            }
        }
    }

    private function createArrayFromFile(): array
    {
        $files = glob($this->buildFolderName() . '*.txt');

        if ($files === false) {
            $files = [];
        }

        $answers = [];

        foreach ($files as $file) {
            $gap = str_replace('.txt', '', basename($file));
            $answers[(int) $gap] = explode("\n", file_get_contents($file));
        }
        // Sort by gap keys, to ensure the numbers are in ascending order.
        // Glob will report the keys in files order like 0, 1, 10, 11, 2,...
        // json_encoding the array with keys in order 0,1,10,11,2,.. will create
        // a json_object instead of a list when keys are numeric, sorted and start with 0
        ksort($answers);
        $this->setAnswers($answers);
        return $answers;
    }

    private function clearFolder($let_folder_exists = true): void
    {
        ilFileUtils::delDir($this->buildFolderName(), $let_folder_exists);
    }

    private function assertDirExists(): void
    {
        $folder_name = $this->buildFolderName();
        if (!ilFileUtils::makeDirParents($folder_name)) {
            throw new ilException('Cannot create export directory');
        }

        if (
            !is_dir($folder_name) ||
            !is_readable($folder_name) ||
            !is_writable($folder_name)
        ) {
            throw new ilException('Cannot create export directory');
        }
    }

    public function loadFromDb($question_id): void
    {
        $result = $this->db->queryF(
            "SELECT qpl_questions.*, " . $this->getAdditionalTableName() . ".* FROM qpl_questions LEFT JOIN " . $this->getAdditionalTableName() . " ON " . $this->getAdditionalTableName() . ".question_fi = qpl_questions.question_id WHERE qpl_questions.question_id = %s",
            ["integer"],
            [$question_id]
        );
        if ($result->numRows() == 1) {
            $data = $this->db->fetchAssoc($result);
            $this->setId($question_id);
            $this->setObjId($data["obj_fi"]);
            $this->setNrOfTries($data['nr_of_tries']);
            $this->setTitle((string) $data["title"]);
            $this->setComment((string) $data["description"]);
            $this->setOriginalId($data["original_id"]);
            $this->setAuthor($data["author"]);
            $this->setPoints($data["points"]);
            $this->setIdenticalScoring((bool) $data["identical_scoring"]);
            $this->setOwner($data["owner"]);
            $this->setQuestion(ilRTE::_replaceMediaObjectImageSrc((string) $data['question_text'], 1));
            $this->setLongMenuTextValue(ilRTE::_replaceMediaObjectImageSrc((string) $data['long_menu_text'], 1));
            $this->loadCorrectAnswerData($question_id);
            $this->setMinAutoComplete($data["min_auto_complete"]);
            if (isset($data['feedback_setting'])) {
                $this->setSpecificFeedbackSetting((int) $data['feedback_setting']);
            }

            try {
                $this->setLifecycle(ilAssQuestionLifecycle::getInstance($data['lifecycle']));
            } catch (ilTestQuestionPoolInvalidArgumentException $e) {
                $this->setLifecycle(ilAssQuestionLifecycle::getDraftInstance());
            }

            try {
                $this->setAdditionalContentEditingMode($data['add_cont_edit_mode']);
            } catch (ilTestQuestionPoolException $e) {
            }
        }

        $this->loadCorrectAnswerData($question_id);
        $this->createArrayFromFile();
        parent::loadFromDb($question_id);
    }

    private function loadCorrectAnswerData($question_id): void
    {
        $res = $this->db->queryF(
            "SELECT * FROM {$this->getAnswerTableName()} WHERE question_fi = %s ORDER BY gap_number, position ASC",
            ['integer'],
            [$question_id]
        );

        $correct_answers = [];
        while ($data = $this->db->fetchAssoc($res)) {
            $correct_answers[$data['gap_number']][0][$data['position']] = rtrim($data['answer_text']);
            $correct_answers[$data['gap_number']][1] = $data['points'];
            $correct_answers[$data['gap_number']][2] = $data['type'];
        }
        $this->setJsonStructure(json_encode($correct_answers));
        $this->setCorrectAnswers($correct_answers);
    }

    public function getCorrectAnswersForQuestionSolution($question_id): array
    {
        $correct_answers = [];
        $res = $this->db->queryF(
            'SELECT gap_number, answer_text FROM  ' . $this->getAnswerTableName() . ' WHERE question_fi = %s',
            ['integer'],
            [$question_id]
        );
        while ($data = $this->db->fetchAssoc($res)) {
            if (array_key_exists($data['gap_number'], $correct_answers)) {
                $correct_answers[$data['gap_number']] .= ' ' . $this->lng->txt("or") . ' ';
                $correct_answers[$data['gap_number']] .= rtrim($data['answer_text']);
            } else {
                $correct_answers[$data['gap_number']] = rtrim($data['answer_text']);
            }
        }
        return $correct_answers;
    }

    private function getCorrectAnswersForGap($question_id, $gap_id): array
    {
        $correct_answers = [];
        $res = $this->db->queryF(
            'SELECT answer_text FROM  ' . $this->getAnswerTableName() . ' WHERE question_fi = %s AND gap_number = %s',
            ['integer', 'integer'],
            [$question_id, $gap_id]
        );
        while ($data = $this->db->fetchAssoc($res)) {
            $correct_answers[] = rtrim($data['answer_text']);
        }
        return $correct_answers;
    }

    private function getPointsForGap($question_id, $gap_id): float
    {
        $points = 0.0;
        $res = $this->db->queryF(
            'SELECT points FROM  ' . $this->getAnswerTableName() . ' WHERE question_fi = %s AND gap_number = %s GROUP BY gap_number, points',
            ['integer', 'integer'],
            [$question_id, $gap_id]
        );
        while ($data = $this->db->fetchAssoc($res)) {
            $points = (float) $data['points'];
        }
        return $points;
    }


    public function getAnswersObject()
    {
        return json_encode($this->createArrayFromFile());
    }

    public function getCorrectAnswersAsJson()
    {
        $this->loadCorrectAnswerData($this->getId());
        return $this->getJsonStructure();
    }

    public function calculateReachedPoints(
        int $active_id,
        ?int $pass = null,
        bool $authorized_solution = true
    ): float {
        $found_values = [];
        if (is_null($pass)) {
            $pass = $this->getSolutionMaxPass($active_id);
        }
        $result = $this->getCurrentSolutionResultSet($active_id, $pass, $authorized_solution);
        while ($data = $this->db->fetchAssoc($result)) {
            $found_values[(int) $data['value1']] = $data['value2'];
        }

        return $this->calculateReachedPointsForSolution($found_values, $active_id);
    }

    protected function calculateReachedPointsForSolution(?array $found_values, int $active_id = 0): float
    {
        if ($found_values == null) {
            $found_values = [];
        }
        $points = 0.0;
        $solution_values_text = [];
        foreach ($found_values as $key => $answer) {
            if ($answer === '') {
                continue;
            }

            $correct_answers = $this->getCorrectAnswersForGap($this->id, $key);
            if (!in_array($answer, $correct_answers)) {
                continue;
            }

            $points_gap = $this->getPointsForGap($this->id, $key);
            if (!$this->getIdenticalScoring()
                && in_array($answer, $solution_values_text)
                && ($points > 0)) {
                $points_gap = 0;
            }

            $points += $points_gap;
            array_push($solution_values_text, $answer);
        }

        return $points;
    }

    public function saveWorkingData(
        int $active_id,
        ?int $pass = null,
        bool $authorized = true
    ): bool {
        if (is_null($pass)) {
            $pass = ilObjTest::_getPass($active_id);
        }

        $answer = $this->getSolutionSubmit();
        $this->getProcessLocker()->executeUserSolutionUpdateLockOperation(
            function () use ($answer, $active_id, $pass, $authorized) {
                $this->removeCurrentSolution($active_id, $pass, $authorized);

                foreach ($answer as $key => $value) {
                    if ($value === '') {
                        continue;
                    }
                    $this->saveCurrentSolution($active_id, $pass, $key, $value, $authorized);
                }
            }
        );

        return true;
    }

    // fau: testNav - overridden function lookupForExistingSolutions (specific for long menu question: ignore unselected values)
    /**
     * Lookup if an authorized or intermediate solution exists
     * @return 	array		['authorized' => bool, 'intermediate' => bool]
     */
    public function lookupForExistingSolutions(int $activeId, int $pass): array
    {
        $return = [
            'authorized' => false,
            'intermediate' => false
        ];

        $query = "
			SELECT authorized, COUNT(*) cnt
			FROM tst_solutions
			WHERE active_fi = " . $this->db->quote($activeId, 'integer') . "
			AND question_fi = " . $this->db->quote($this->getId(), 'integer') . "
			AND pass = " . $this->db->quote($pass, 'integer') . "
			AND value2 <> '-1'
		";

        if ($this->getStep() !== null) {
            $query .= " AND step = " . $this->db->quote((int) $this->getStep(), 'integer') . " ";
        }

        $query .= "
			GROUP BY authorized
		";

        $result = $this->db->query($query);

        while ($row = $this->db->fetchAssoc($result)) {
            if ($row['authorized']) {
                $return['authorized'] = $row['cnt'] > 0;
            } else {
                $return['intermediate'] = $row['cnt'] > 0;
            }
        }
        return $return;
    }
    // fau.


    protected function getSolutionSubmit(): array
    {
        $answer = $this->questionpool_request->retrieveArrayOfStringsFromPost('answer');

        if ($answer === null) {
            return [];
        }

        foreach ($answer as $key => $value) {
            $solutionSubmit[$key] = $value;
        }

        return $solutionSubmit;
    }

    protected function savePreviewData(ilAssQuestionPreviewSession $previewSession): void
    {
        $answer = $_POST['answer'] ?? null;
        if (is_array($answer)) {
            $answer = array_map(function ($value) {
                return trim($value);
            }, $answer);
        }
        $previewSession->setParticipantsSolution($answer);
    }

    /**
     * Returns the question type of the question
     *
     * @return integer The question type of the question
     */
    public function getQuestionType(): string
    {
        return "assLongMenu";
    }

    public function getAdditionalTableName(): string
    {
        return 'qpl_qst_lome';
    }

    /**
     * Collects all text in the question which could contain media objects
     * which were created with the Rich Text Editor
     */
    public function getRTETextWithMediaObjects(): string
    {
        return parent::getRTETextWithMediaObjects() . $this->getLongMenuTextValue();
    }

    /**
     * {@inheritdoc}
     */
    public function setExportDetailsXLSX(ilAssExcelFormatHelper $worksheet, int $startrow, int $col, int $active_id, int $pass): int
    {
        parent::setExportDetailsXLSX($worksheet, $startrow, $col, $active_id, $pass);

        $solution = $this->getSolutionValues($active_id, $pass);

        $i = 1;
        foreach ($this->getCorrectAnswers() as $gap_index => $gap) {
            $worksheet->setCell($startrow + $i, $col, $this->lng->txt('assLongMenu') . " $i");
            $worksheet->setBold($worksheet->getColumnCoord($col) . ($startrow + $i));
            foreach ($solution as $solutionvalue) {
                if ($gap_index == $solutionvalue["value1"]) {
                    switch ($gap[2]) {
                        case self::ANSWER_TYPE_SELECT_VAL:
                            $value = $solutionvalue["value2"];
                            if ($value == -1) {
                                $value = '';
                            }
                            $worksheet->setCell($startrow + $i, $col + 2, $value);
                            break;
                        case self::ANSWER_TYPE_TEXT_VAL:
                            $worksheet->setCell($startrow + $i, $col + 2, $solutionvalue["value2"]);
                            break;
                    }
                }
            }
            $i++;
        }

        return $startrow + $i + 1;
    }

    /**
     * Get the user solution for a question by active_id and the test pass
     *
     * @param int $active_id
     * @param int $pass
     *
     * @return ilUserQuestionResult
     */
    public function getUserQuestionResult($active_id, $pass): ilUserQuestionResult
    {
        $result = new ilUserQuestionResult($this, $active_id, $pass);

        $points = $this->calculateReachedPoints($active_id, $pass);
        $max_points = $this->getMaximumPoints();

        $result->setReachedPercentage(($points / $max_points) * 100);

        return $result;
    }

    /**
     * If index is null, the function returns an array with all anwser options
     * Else it returns the specific answer option
     *
     * @param null|int $index
     *
     * @return array|ASS_AnswerSimple
     */
    public function getAvailableAnswerOptions($index = null)
    {
        return $this->createArrayFromFile();
    }

    public function isShuffleAnswersEnabled(): bool
    {
        return false;
    }

    public function clearAnswerSpecificDataFromDb(int $question_id): void
    {
        $this->db->manipulateF(
            'DELETE FROM ' . $this->getAnswerTableName() . ' WHERE question_fi = %s',
            [ 'integer' ],
            [ $question_id ]
        );
    }

    public function delete(int $question_id): void
    {
        parent::delete($question_id);
        $this->clearFolder(false);
    }

    /**
     * @param ilAssSelfAssessmentMigrator $migrator
     */
    protected function lmMigrateQuestionTypeSpecificContent(ilAssSelfAssessmentMigrator $migrator): void
    {
        $this->setLongMenuTextValue($migrator->migrateToLmContent($this->getLongMenuTextValue()));
    }

    /**
     * Returns a JSON representation of the question
     */
    public function toJSON(): string
    {
        $result = [];
        $result['id'] = $this->getId();
        $result['type'] = (string) $this->getQuestionType();
        $result['title'] = $this->getTitle();
        $result['question'] = $this->formatSAQuestion($this->getQuestion());
        $replaced_quesiton_text = $this->getLongMenuTextValue();
        $result['lmtext'] = $this->formatSAQuestion($replaced_quesiton_text);
        $result['nr_of_tries'] = $this->getNrOfTries();
        $result['shuffle'] = $this->getShuffle();
        $result['feedback'] = [
            'onenotcorrect' => $this->formatSAQuestion($this->feedbackOBJ->getGenericFeedbackTestPresentation($this->getId(), false)),
            'allcorrect' => $this->formatSAQuestion($this->feedbackOBJ->getGenericFeedbackTestPresentation($this->getId(), true))
        ];

        $mobs = ilObjMediaObject::_getMobsOfObject("qpl:html", $this->getId());
        $result['answers'] = $this->getAnswers();
        $result['correct_answers'] = $this->getCorrectAnswers();
        $result['mobs'] = $mobs;
        return json_encode($result);
    }

    public function getIdenticalScoring(): bool
    {
        return $this->identical_scoring;
    }

    public function setIdenticalScoring(bool $identical_scoring): void
    {
        $this->identical_scoring = $identical_scoring;
    }

    public function toLog(AdditionalInformationGenerator $additional_info): array
    {
        return [
            AdditionalInformationGenerator::KEY_QUESTION_TYPE => (string) $this->getQuestionType(),
            AdditionalInformationGenerator::KEY_QUESTION_TITLE => $this->getTitle(),
            AdditionalInformationGenerator::KEY_QUESTION_TEXT => $this->formatSAQuestion($this->getQuestion()),
            AdditionalInformationGenerator::KEY_QUESTION_LONGMENU_TEXT => $this->formatSAQuestion($this->getLongMenuTextValue()),
            AdditionalInformationGenerator::KEY_QUESTION_SHUFFLE_ANSWER_OPTIONS => $additional_info
                ->getTrueFalseTagForBool($this->getShuffle()),
            AdditionalInformationGenerator::KEY_FEEDBACK => [
                AdditionalInformationGenerator::KEY_QUESTION_FEEDBACK_ON_INCOMPLETE => $this->formatSAQuestion($this->feedbackOBJ->getGenericFeedbackTestPresentation($this->getId(), false)),
                AdditionalInformationGenerator::KEY_QUESTION_FEEDBACK_ON_COMPLETE => $this->formatSAQuestion($this->feedbackOBJ->getGenericFeedbackTestPresentation($this->getId(), true))
            ],
            AdditionalInformationGenerator::KEY_QUESTION_ANSWER_OPTIONS => $this->getAnswersForLog($additional_info),
            AdditionalInformationGenerator::KEY_QUESTION_CORRECT_ANSWER_OPTIONS => $this->getCorrectAnswersForLog($additional_info)
        ];
    }

    private function getAnswersForLog(AdditionalInformationGenerator $additional_info): string
    {
        $i = 1;
        return array_reduce(
            $this->getAnswers(),
            static function (string $c, array $v) use ($additional_info, $i): string {
                return $c . $additional_info->getTagForLangVar('gap')
                    . ' ' . $i++ . ': ' . implode(',', $v) . '; ';
            },
            ''
        );
    }

    private function getCorrectAnswersForLog(AdditionalInformationGenerator $additional_info): string
    {
        $answer_types = [
            self::ANSWER_TYPE_SELECT_VAL => $additional_info->getTagForLangVar('answers_select'),
            self::ANSWER_TYPE_TEXT_VAL => $additional_info->getTagForLangVar('answers_text_box')
        ];

        $i = 1;
        return array_reduce(
            $this->getCorrectAnswers(),
            static function (string $c, array $v) use ($additional_info, $answer_types, $i): string {
                return $c . $additional_info->getTagForLangVar('gap')
                    . ' ' . $i++ . ': ' . implode(',', $v[0]) . ', '
                    . $additional_info->getTagForLangVar('points') . ': ' . $v[1] . ', '
                    . $additional_info->getTagForLangVar('type') . ': ' . $answer_types[$v[2]] . '; ';
            },
            ''
        );
    }

    public function solutionValuesToLog(
        AdditionalInformationGenerator $additional_info,
        array $solution_values
    ): array {
        $parsed_solution = [];
        foreach ($this->getCorrectAnswers() as $gap_index => $gap) {
            foreach ($solution_values as $solution) {
                if ($gap_index != $solution['value1']) {
                    continue;
                }
                $value = $solution['value2'];
                if ($gap[2] === self::ANSWER_TYPE_SELECT_VAL
                    && $value === '-1') {
                    $value = '';
                }
                $parsed_solution[$gap_index + 1] = $value;
                break;
            }
        }
        return $parsed_solution;
    }
}
