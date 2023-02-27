<?php

class ilQuestionBaseRepo
{
    private ilDBInterface $db;

    public function __Construct(ilDBInterface $db) {
        $this->db = $db;
    }


    public function getBaseSettingsForId(int $question_id): ?ilQuestionBaseSettings
    {
        $query = "SELECT qpl_questions.* FROM qpl_questions WHERE question_id = "
            . $this->db->quote($question_id, 'integer');

        $result = $this->db->query($query);

        if ($row = $this->db->fetchAssoc($result)) {

            return new ilQuestionBaseSettings(
                (int) $row['question_id'],
                (int) $row['question_type_fi'],
                (int) $row['obj_fi'],
                (string) $row['title'],
                (string) $row['description'],
                (string) $row['question_text'],
                (string) $row['author'],
                (int) $row['owner'],
                (int) substr($row['working_time'], 0, 2) * 3600
                + (int) substr($row['working_time'], 3, 2) * 60
                + (int) substr($row['working_time'], 6, 2),
                (int) $row['points'],
                (int) $row['nr_of_tries'],
                (bool) $row['complete'],
                (int) $row['created'],
                (int) $row['tstamp'],
                $row['original_id'] ? (int) $row['original_id'] : null,
                (string) $row['external_id'],
                (string) $row['add_cont_edit_mode'],
                (string) $row['lifecycle']
            );
        }
        return null;
    }

    public function saveBaseSettings(ilQuestionBaseSettings $settings) : void
    {
        // todo
    }

}