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

use ILIAS\Data\ObjectId;
use ILIAS\Filesystem\Util\Convert\ImageOutputOptions;
use ILIAS\Poll\Image\I\FactoryInterface as PollImageFactoryInterface;
use ILIAS\Poll\Image\Factory as PollImageFactory;
use ILIAS\Data\Factory as DataFactory;
use ILIAS\Notes\Service as NotesService;

/**
* Class ilObjPoll
*
* @author Jörg Lützenkirchen <luetzenkirchen@leifos.com>
*/
class ilObjPoll extends ilObject2
{
    protected PollImageFactory $poll_image_factory;
    protected NotesService $notes;
    protected DataFactory $data_factory;
    protected int $access_type = 0;
    protected int $access_begin = 0;
    protected int $access_end = 0;
    protected bool $access_visibility = false;
    protected string $question = "";
    protected string $image = "";
    protected int $view_results = 0;
    protected bool $period = false;
    protected int $period_begin = 0;
    protected int $period_end = 0;

    protected int $max_number_answers = 0;
    protected bool $result_sort_by_votes = false;
    protected bool $mode_non_anonymous = false;
    protected bool $show_comments = false;
    protected int $show_results_as = 1;

    public const VIEW_RESULTS_ALWAYS = 1;
    public const VIEW_RESULTS_NEVER = 2;
    public const VIEW_RESULTS_AFTER_VOTE = 3;
    public const VIEW_RESULTS_AFTER_PERIOD = 4;

    public const SHOW_RESULTS_AS_BARCHART = 1;
    public const SHOW_RESULTS_AS_STACKED_CHART = 2;

    public function __construct(int $a_id = 0, bool $a_reference = true)
    {
        global $DIC;

        $this->db = $DIC->database();
        // default
        $this->setViewResults(self::VIEW_RESULTS_AFTER_VOTE);
        $this->setAccessType(ilObjectActivation::TIMINGS_DEACTIVATED);
        $this->setVotingPeriod(false);
        $this->notes = $DIC->notes();
        $this->poll_image_factory = new PollImageFactory();
        $this->data_factory = new DataFactory();
        parent::__construct($a_id, $a_reference);
    }

    protected function initType(): void
    {
        $this->type = "poll";
    }

    public function setAccessType(int $a_value): void
    {
        $this->access_type = $a_value;
    }

    public function getAccessType(): int
    {
        return $this->access_type;
    }

    public function setAccessBegin(int $a_value): void
    {
        $this->access_begin = $a_value;
    }

    public function getAccessBegin(): int
    {
        return $this->access_begin;
    }

    public function setAccessEnd(int $a_value): void
    {
        $this->access_end = $a_value;
    }

    public function getAccessEnd(): int
    {
        return $this->access_end;
    }

    public function setAccessVisibility(bool $a_value): void
    {
        $this->access_visibility = $a_value;
    }

    public function getAccessVisibility(): bool
    {
        return $this->access_visibility;
    }

    public function setQuestion(string $a_value): void
    {
        $this->question = $a_value;
    }

    public function getQuestion(): string
    {
        return $this->question;
    }

    public function setViewResults(int $a_value): void
    {
        $this->view_results = $a_value;
    }

    public function getViewResults(): int
    {
        return $this->view_results;
    }

    public function setVotingPeriod(bool $a_value): void
    {
        $this->period = $a_value;
    }

    public function getVotingPeriod(): bool
    {
        return $this->period;
    }

    public function setVotingPeriodBegin(int $a_value): void
    {
        $this->period_begin = $a_value;
    }

    public function getVotingPeriodBegin(): int
    {
        return $this->period_begin;
    }

    public function setVotingPeriodEnd(int $a_value): void
    {
        $this->period_end = $a_value;
    }

    public function getVotingPeriodEnd(): int
    {
        return $this->period_end;
    }

    public function setMaxNumberOfAnswers(int $a_value): void
    {
        $this->max_number_answers = $a_value;
    }

    public function getMaxNumberOfAnswers(): int
    {
        return $this->max_number_answers;
    }

    public function setSortResultByVotes(bool $a_value): void
    {
        $this->result_sort_by_votes = $a_value;
    }

    public function getSortResultByVotes(): bool
    {
        return $this->result_sort_by_votes;
    }

    public function setNonAnonymous(bool $a_value): void
    {
        $this->mode_non_anonymous = $a_value;
    }

    public function getNonAnonymous(): bool
    {
        return $this->mode_non_anonymous;
    }

    public function setShowComments(bool $a_value): void
    {
        $this->show_comments = $a_value;
    }

    public function getShowComments(): bool
    {
        return $this->show_comments;
    }

    public function setShowResultsAs(int $a_value): void
    {
        $this->show_results_as = $a_value;
    }

    public function getShowResultsAs(): int
    {
        return $this->show_results_as;
    }

    protected function doRead(): void
    {
        $ilDB = $this->db;

        $set = $ilDB->query("SELECT * FROM il_poll" .
                " WHERE id = " . $ilDB->quote($this->getId(), "integer"));
        $row = $ilDB->fetchAssoc($set);
        $this->setQuestion((string) ($row["question"] ?? ''));
        $this->setViewResults((int) ($row["view_results"] ?? self::VIEW_RESULTS_AFTER_VOTE));
        $this->setVotingPeriod((bool) ($row["period"] ?? 0));
        $this->setVotingPeriodBegin((int) ($row["period_begin"] ?? 0));
        $this->setVotingPeriodEnd((int) ($row["period_end"] ?? 0));
        $this->setMaxNumberOfAnswers((int) ($row["max_answers"] ?? 0));
        $this->setSortResultByVotes((bool) ($row["result_sort"] ?? 0));
        $this->setNonAnonymous((bool) ($row["non_anon"] ?? 0));
        $this->setShowResultsAs((int) ($row["show_results_as"] ?? self::SHOW_RESULTS_AS_BARCHART));

        // #14661
        $this->setShowComments($this->notes->domain()->commentsActive($this->getId()));

        if ($this->ref_id) {
            $activation = ilObjectActivation::getItem($this->ref_id);
            $this->setAccessType((int) ($activation["timing_type"] ?? ilObjectActivation::TIMINGS_DEACTIVATED));
            if ($this->getAccessType() === ilObjectActivation::TIMINGS_ACTIVATION) {
                // default entry values should not be loaded if not activated
                $this->setAccessBegin((int) ($activation["timing_start"] ?? time()));
                $this->setAccessEnd((int) ($activation["timing_end"] ?? time()));
                $this->setAccessVisibility((bool) ($activation["visible"] ?? false));
            }
        }
    }

    protected function propertiesToDB(): array
    {
        return array(
            "question" => array("text", $this->getQuestion()),
            "view_results" => array("integer", $this->getViewResults()),
            "period" => array("integer", $this->getVotingPeriod()),
            "period_begin" => array("integer", $this->getVotingPeriodBegin()),
            "period_end" => array("integer", $this->getVotingPeriodEnd()),
            "max_answers" => array("integer", $this->getMaxNumberOfAnswers()),
            "result_sort" => array("integer", $this->getSortResultByVotes()),
            "non_anon" => array("integer", $this->getNonAnonymous()),
            "show_results_as" => array("integer", $this->getShowResultsAs()),
        );
    }

    protected function doCreate(bool $clone_mode = false): void
    {
        $ilDB = $this->db;

        if ($this->getId()) {
            $fields = $this->propertiesToDB();
            $fields["id"] = array("integer", $this->getId());
            $fields["migrated"] = array("integer", "1");

            $ilDB->insert("il_poll", $fields);


            // object activation default entry will be created on demand


            // block handling
            $block = new ilPollBlock();
            $block->setType("poll");
            $block->setContextObjId($this->getId());
            $block->setContextObjType("poll");
            $block->create();
        }
    }

    protected function doUpdate(): void
    {
        $ilDB = $this->db;

        if ($this->getId()) {
            $fields = $this->propertiesToDB();

            $ilDB->update(
                "il_poll",
                $fields,
                array("id" => array("integer", $this->getId()))
            );

            // #14661
            $this->notes->domain()->activateComments($this->getId(), $this->getShowComments());

            if ($this->getRefId()) {
                $activation = new ilObjectActivation();
                $activation->setTimingType($this->getAccessType());
                $activation->setTimingStart($this->getAccessBegin());
                $activation->setTimingEnd($this->getAccessEnd());
                $activation->toggleVisible($this->getAccessVisibility());
                $activation->update($this->ref_id);
            }
        }
    }

    protected function doDelete(): void
    {
        $ilDB = $this->db;

        if ($this->getId()) {
            $this->poll_image_factory->handler()->deleteImage(
                $this->data_factory->objId($this->getId()),
                $this->user->getId()
            );
            $this->deleteAllAnswers();

            if ($this->ref_id) {
                ilObjectActivation::deleteAllEntries($this->ref_id);
            }

            $ilDB->manipulate("DELETE FROM il_poll" .
                " WHERE id = " . $ilDB->quote($this->id, "integer"));
        }
    }

    protected function doCloneObject(ilObject2 $new_obj, int $a_target_id, ?int $a_copy_id = 0): void
    {
        assert($new_obj instanceof ilObjPoll);

        // question/image
        $new_obj->setQuestion($this->getQuestion());

        $this->poll_image_factory->handler()->cloneImage(
            $this->data_factory->objId($this->id),
            $this->data_factory->objId($new_obj->getId()),
            $this->user->getId()
        );

        //copy online status if object is not the root copy object
        $cp_options = ilCopyWizardOptions::_getInstance($a_copy_id);

        if ($cp_options->isRootNode($this->getRefId())) {
            $new_obj->setOfflineStatus(true);
        }

        $view_results = $this->getViewResults();
        if ($view_results === ilObjPoll::VIEW_RESULTS_AFTER_PERIOD) {
            // default view results setting to always, since
            // voting period is not copied.
            $view_results = ilObjPoll::VIEW_RESULTS_ALWAYS;
        }
        $new_obj->setViewResults($view_results);
        $new_obj->setShowComments($this->getShowComments());
        $new_obj->setShowResultsAs($this->getShowResultsAs());
        $new_obj->setMaxNumberOfAnswers($this->getMaxNumberOfAnswers());
        $new_obj->setSortResultByVotes($this->getSortResultByVotes());
        $new_obj->setNonAnonymous($this->getNonAnonymous());
        $new_obj->update();

        // answers
        $answers = $this->getAnswers();
        if ($answers) {
            foreach ($answers as $item) {
                $new_obj->saveAnswer($item["answer"]);
            }
        }
    }

    public function uploadImage(string $file_path, string $file_name): void
    {
        if (!$this->getId() or $file_path === "") {
            return;
        }
        $this->poll_image_factory->handler()->uploadImage(
            $this->data_factory->objId($this->getId()),
            $file_path,
            $file_name,
            $this->user->getId()
        );
    }

    public static function getImageSize(): string
    {
        // :TODO:
        return "600x600";
    }


    //
    // Answer
    //

    public function getAnswers(): array
    {
        $ilDB = $this->db;

        $res = [];

        $sql = "SELECT * FROM il_poll_answer" .
            " WHERE poll_id = " . $ilDB->quote($this->getId(), "integer") .
            " ORDER BY pos ASC";
        $set = $ilDB->query($sql);
        while ($row = $ilDB->fetchAssoc($set)) {
            $res[] = $row;
        }
        return $res;
    }

    public function getAnswer(int $a_id): array
    {
        $ilDB = $this->db;

        $sql = "SELECT * FROM il_poll_answer" .
            " WHERE id = " . $ilDB->quote($a_id, "integer");
        $set = $ilDB->query($sql);
        return (array) $ilDB->fetchAssoc($set);
    }

    public function saveAnswer(string $a_text, ?int $a_pos = null): ?int
    {
        $ilDB = $this->db;

        if (!trim($a_text)) {
            return null;
        }

        $id = $ilDB->nextId("il_poll_answer");

        if (!$a_pos) {
            // append
            $sql = "SELECT max(pos) pos" .
                " FROM il_poll_answer" .
                " WHERE poll_id = " . $ilDB->quote($this->getId(), "integer");
            $set = $ilDB->query($sql);
            $a_pos = $ilDB->fetchAssoc($set);
            $a_pos = (int) ($a_pos["pos"] ?? 0) + 10;
        }

        $fields = array(
            "id" => array("integer", $id),
            "poll_id" => array("integer", $this->getId()),
            "answer" => array("text", trim($a_text)),
            "pos" => array("integer", $a_pos)
        );
        $ilDB->insert("il_poll_answer", $fields);

        return $id;
    }

    public function updateAnswer(int $a_id, string $a_text): void
    {
        $ilDB = $this->db;

        $ilDB->update(
            "il_poll_answer",
            array("answer" => array("text", $a_text)),
            array("id" => array("integer", $a_id))
        );
    }

    public function rebuildAnswerPositions(): void
    {
        $answers = $this->getAnswers();

        $pos = [];
        foreach ($answers as $item) {
            $id = (int) ($item['id'] ?? 0);
            $pos[$id] = (int) ($item["pos"] ?? 10);
        }

        $this->updateAnswerPositions($pos);
    }

    public function updateAnswerPositions(array $a_pos): void
    {
        $ilDB = $this->db;

        asort($a_pos);

        $pos = 0;
        foreach (array_keys($a_pos) as $id) {
            $pos += 10;

            $ilDB->update(
                "il_poll_answer",
                array("pos" => array("integer", $pos)),
                array("id" => array("integer", $id))
            );
        }
    }

    public function deleteAnswer(int $a_id): void
    {
        $ilDB = $this->db;

        if ($a_id) {
            $ilDB->manipulate("DELETE FROM il_poll_vote" .
                " WHERE answer_id = " . $ilDB->quote($this->getId(), "integer"));

            $ilDB->manipulate("DELETE FROM il_poll_answer" .
                " WHERE id = " . $ilDB->quote($a_id, "integer"));
        }
    }

    protected function deleteAllAnswers(): void
    {
        $ilDB = $this->db;

        if ($this->getId()) {
            $this->deleteAllVotes();

            $ilDB->manipulate("DELETE FROM il_poll_answer" .
                " WHERE poll_id = " . $ilDB->quote($this->getId(), "integer"));
        }
    }

    public function deleteAllVotes(): void
    {
        $ilDB = $this->db;

        if ($this->getId()) {
            $ilDB->manipulate("DELETE FROM il_poll_vote" .
                " WHERE poll_id = " . $ilDB->quote($this->getId(), "integer"));
        }
    }

    public function saveAnswers(array $a_answers): int
    {
        $existing = $this->getAnswers();

        $ids = [];
        $pos = 0;
        $id = null;
        foreach ($a_answers as $answer) {
            if (trim($answer)) {
                // existing answer?
                $found = false;
                foreach ($existing as $idx => $item) {
                    if (trim($answer) === (string) ($item["answer"] ?? '')) {
                        $found = true;
                        unset($existing[$idx]);

                        $id = (int) ($item["id"] ?? 0);
                    }
                }

                // create new answer
                if (!$found) {
                    $id = $this->saveAnswer($answer);
                }

                // add existing answer id to order
                if (isset($id) && is_int($id)) {
                    $ids[$id] = ++$pos;
                }
            }
        }

        // remove obsolete answers
        if (count($existing)) {
            foreach ($existing as $item) {
                if (isset($item["id"])) {
                    $this->deleteAnswer((int) $item["id"]);
                }
            }
        }

        // save current order
        if (count($ids)) {
            $this->updateAnswerPositions($ids);
        }

        return count($ids);
    }


    //
    // votes
    //

    public function saveVote(int $a_user_id, array $a_answers): void
    {
        if ($this->hasUserVoted($a_user_id)) {
            return;
        }

        foreach ($a_answers as $answer_id) {
            $fields = array("user_id" => array("integer", $a_user_id),
                "poll_id" => array("integer", $this->getId()),
                "answer_id" => array("integer", $answer_id));
            $this->db->insert("il_poll_vote", $fields);
        }
    }

    public function hasUserVoted(int $a_user_id): bool
    {
        $sql = "SELECT user_id" .
            " FROM il_poll_vote" .
            " WHERE poll_id = " . $this->db->quote($this->getId(), "integer") .
            " AND user_id = " . $this->db->quote($a_user_id, "integer");
        $this->db->setLimit(1, 0);
        $set = $this->db->query($sql);
        return (bool) $this->db->numRows($set);
    }

    public function countVotes(): int
    {
        $sql = "SELECT COUNT(DISTINCT(user_id)) cnt" .
            " FROM il_poll_vote" .
            " WHERE poll_id = " . $this->db->quote($this->getId(), "integer");
        $set = $this->db->query($sql);
        $row = $this->db->fetchAssoc($set);
        return (int) $row["cnt"];
    }

    public function getVotePercentages(): array
    {
        $res = [];
        $cnt = 0;

        $sql = "SELECT answer_id, count(*) cnt" .
            " FROM il_poll_vote" .
            " WHERE poll_id = " . $this->db->quote($this->getId(), "integer") .
            " GROUP BY answer_id";
        $set = $this->db->query($sql);
        while ($row = $this->db->fetchAssoc($set)) {
            $cnt += (int) $row["cnt"];
            $res[(int) $row["answer_id"]] = array("abs" => (int) $row["cnt"], "perc" => 0);
        }

        foreach ($res as $id => $item) {
            $abs = (int) ($item['abs'] ?? 0);
            $id = (int) ($id ?? 0);
            if ($cnt === 0) {
                $res[$id]["perc"] = 0;
            } else {
                $res[$id]["perc"] = $abs / $cnt * 100;
            }
        }

        return array("perc" => $res, "total" => $this->countVotes());
    }

    public function getVotesByUsers(): array
    {
        $ilDB = $this->db;

        $res = [];

        $sql = "SELECT answer_id, user_id, firstname, lastname, login" .
            " FROM il_poll_vote" .
            " JOIN usr_data ON (usr_data.usr_id = il_poll_vote.user_id)" .
            " WHERE poll_id = " . $ilDB->quote($this->getId(), "integer");
        $set = $ilDB->query($sql);
        while ($row = $ilDB->fetchAssoc($set)) {
            $user_id = (int) ($row["user_id"] ?? 0);
            if (!isset($res[$user_id])) {
                $res[$user_id] = $row;
            }
            $res[$user_id]["answers"][] = (int) ($row["answer_id"] ?? 0);
        }

        return $res;
    }
}
