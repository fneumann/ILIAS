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
* Class for text subset question imports
*
* assTextSubsetImport is a class for text subset question imports
*
* @author		Helmut Schottmüller <helmut.schottmueller@mac.com>
* @version	$Id$
* @ingroup components\ILIASTestQuestionPool
*/
class assTextSubsetImport extends assQuestionImport
{
    public function fromXML(
        string $importdirectory,
        int $user_id,
        ilQTIItem $item,
        int $questionpool_id,
        ?int $tst_id,
        ?ilObject &$tst_object,
        int &$question_counter,
        array $import_mapping
    ): array {
        // empty session variable for imported xhtml mobs
        ilSession::clear('import_mob_xhtml');
        ;
        $presentation = $item->getPresentation();
        $shuffle = 0;
        $idents = [];
        $now = getdate();
        $created = sprintf("%04d%02d%02d%02d%02d%02d", $now['year'], $now['mon'], $now['mday'], $now['hours'], $now['minutes'], $now['seconds']);
        $gaps = [];
        foreach ($presentation->order as $entry) {
            switch ($entry["type"]) {
                case "response":
                    $response = $presentation->response[$entry["index"]];
                    if ($response->getResponseType() == ilQTIResponse::RT_RESPONSE_STR) {
                        array_push($idents, $response->getIdent());
                    }
                    break;
            }
        }
        $responses = [];
        $feedbacksgeneric = [];
        foreach ($item->resprocessing as $resprocessing) {
            foreach ($resprocessing->respcondition as $respcondition) {
                $ident = "";
                $correctness = 1;
                $conditionvar = $respcondition->getConditionvar();
                foreach ($conditionvar->order as $order) {
                    switch ($order["field"]) {
                        case "varsubset":
                            $respident = $conditionvar->varsubset[$order["index"]]->getRespident();
                            $content = $conditionvar->varsubset[$order["index"]]->getContent();
                            if (!isset($responses[$respident]) || !is_array($responses[$respident])) {
                                $responses[$respident] = [];
                            }
                            $vars = explode(",", $content);
                            foreach ($vars as $var) {
                                array_push($responses[$respident], ["solution" => $var, "points" => ""]);
                            }
                            break;
                    }
                }
                foreach ($respcondition->setvar as $setvar) {
                    if ((strcmp($setvar->getVarname(), "matches") == 0) && ($setvar->getAction() == ilQTISetvar::ACTION_ADD)) {
                        foreach ($responses[$respident] as $idx => $solutionarray) {
                            if (strlen($solutionarray["points"]) == 0) {
                                $responses[$respident][$idx]["points"] = $setvar->getContent();
                            }
                        }
                    }
                }
                foreach ($resprocessing->respcondition as $e_respcondition) {
                    foreach ($e_respcondition->displayfeedback as $feedbackpointer) {
                        if (strlen($feedbackpointer->getLinkrefid())) {
                            foreach ($item->itemfeedback as $ifb) {
                                if (strcmp($ifb->getIdent(), "response_allcorrect") == 0) {
                                    // found a feedback for the identifier
                                    if (count($ifb->material)) {
                                        foreach ($ifb->material as $material) {
                                            $feedbacksgeneric[1] = $material;
                                        }
                                    }
                                    if ((count($ifb->flow_mat) > 0)) {
                                        foreach ($ifb->flow_mat as $fmat) {
                                            if (count($fmat->material)) {
                                                foreach ($fmat->material as $material) {
                                                    $feedbacksgeneric[1] = $material;
                                                }
                                            }
                                        }
                                    }
                                } elseif (strcmp($ifb->getIdent(), "response_onenotcorrect") == 0) {
                                    // found a feedback for the identifier
                                    if (count($ifb->material)) {
                                        foreach ($ifb->material as $material) {
                                            $feedbacksgeneric[0] = $material;
                                        }
                                    }
                                    if ((count($ifb->flow_mat) > 0)) {
                                        foreach ($ifb->flow_mat as $fmat) {
                                            if (count($fmat->material)) {
                                                foreach ($fmat->material as $material) {
                                                    $feedbacksgeneric[0] = $material;
                                                }
                                            }
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
            }
        }
        $this->addGeneralMetadata($item);
        $this->object->setTitle($item->getTitle());
        $this->object->setNrOfTries((int) $item->getMaxattempts());
        $this->object->setComment($item->getComment());
        $this->object->setAuthor($item->getAuthor());
        $this->object->setOwner($user_id);
        $this->object->setQuestion($this->QTIMaterialToString($item->getQuestiontext()));
        $this->object->setObjId($questionpool_id);
        $textrating = $item->getMetadataEntry("textrating");
        if (strlen($textrating) == 0) {
            $textrating = "ci";
        }
        $this->object->setTextRating($textrating);
        $this->object->setCorrectAnswers($item->getMetadataEntry("correctanswers"));
        $response = current($responses);
        $counter = 0;
        if (is_array($response)) {
            foreach ($response as $answer) {
                $this->object->addAnswer($answer["solution"], $answer["points"], $counter);
                $counter++;
            }
        }
        // additional content editing mode information
        $this->object->setAdditionalContentEditingMode(
            $this->fetchAdditionalContentEditingModeInformation($item)
        );
        $this->object->saveToDb();
        if (count($item->suggested_solutions)) {
            foreach ($item->suggested_solutions as $suggested_solution) {
                $this->importSuggestedSolution(
                    $this->object->getId(),
                    $suggested_solution["solution"]->getContent(),
                    $suggested_solution["gap_index"]
                );
            }
        }
        foreach ($feedbacksgeneric as $correctness => $material) {
            $m = $this->QTIMaterialToString($material);
            $feedbacksgeneric[$correctness] = $m;
        }
        // handle the import of media objects in XHTML code
        $questiontext = $this->object->getQuestion();
        if (is_array(ilSession::get("import_mob_xhtml"))) {
            foreach (ilSession::get("import_mob_xhtml") as $mob) {
                $importfile = $importdirectory . DIRECTORY_SEPARATOR . $mob["uri"];

                global $DIC; /* @var ILIAS\DI\Container $DIC */
                $DIC['ilLog']->write(__METHOD__ . ': import mob from dir: ' . $importfile);

                $media_object = ilObjMediaObject::_saveTempFileAsMediaObject(basename($importfile), $importfile, false);
                ilObjMediaObject::_saveUsage($media_object->getId(), "qpl:html", $this->object->getId());
                $questiontext = str_replace("src=\"" . $mob["mob"] . "\"", "src=\"" . "il_" . IL_INST_ID . "_mob_" . $media_object->getId() . "\"", $questiontext);
                foreach ($feedbacksgeneric as $correctness => $material) {
                    $feedbacksgeneric[$correctness] = str_replace("src=\"" . $mob["mob"] . "\"", "src=\"" . "il_" . IL_INST_ID . "_mob_" . $media_object->getId() . "\"", $material);
                }
            }
        }
        $this->object->setQuestion(ilRTE::_replaceMediaObjectImageSrc($questiontext, 1));
        foreach ($feedbacksgeneric as $correctness => $material) {
            $this->object->feedbackOBJ->importGenericFeedback(
                $this->object->getId(),
                $correctness,
                ilRTE::_replaceMediaObjectImageSrc($material, 1)
            );
        }
        $this->object->saveToDb();
        if ($tst_id > 0) {
            $q_1_id = $this->object->getId();
            $question_id = $this->object->duplicate(true, "", "", -1, $tst_id);
            $tst_object->questions[$question_counter++] = $question_id;
            $import_mapping[$item->getIdent()] = ["pool" => $q_1_id, "test" => $question_id];
        } else {
            $import_mapping[$item->getIdent()] = ["pool" => $this->object->getId(), "test" => 0];
        }
        return $import_mapping;
    }
}
