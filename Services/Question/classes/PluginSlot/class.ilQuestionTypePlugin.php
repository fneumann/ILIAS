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

/**
 * Abstract parent class for all question type plugin classes.
 */
abstract class ilQuestionTypePlugin extends ilPlugin
{
    protected ilLanguage $lng;
    protected ilDBInterface $db;


    /**
     * Everything specific for a new question type is created through this factory
     */
    abstract public function factory(): ilQuestionFactory;


    public function install(): void
    {
        parent::install();
        $this->addQuestionType();
    }

    /**
     * @todo: migrate to a general question type repository
     */
    private function addQuestionType()
    {
        $query = "SELECT * FROM qpl_qst_type WHERE type_tag =" . $this->db->quote($this->factory()->getTypeTag(), 'text');

        if (empty($row = $this->db->fetchAssoc($this->db->query($query)))) {

            $query2 = "SELECT MAX(question_type_id) maxid FROM qpl_qst_type";
            if ($row = $this->db->fetchAssoc($this->db->query($query2))) {
                $max = (int) $row["maxid"] + 1;
            }
            else {
                $max = 1;
            }

            $this->db->insert('qpl_qst_type', [
                'question_type_id' => ['integer', $max],
                'type_tag' => ['string', $this->factory()->getTypeTag()],
                'plugin' => ['integer', 1]
            ]);
        }
    }

    /**
     * @todo: migrate to a general question type repository
     * @todo: should types be removedon uninstall - their id relation gets lost!
     */
    private function removeQuestionType()
    {
        $query = "DELETE FROM qpl_qst_type WHERE type_tag =" . $this->db->quote($this->factory()->getTypeTag(), 'text');
        $this->db->manipulate($query);
    }
}
