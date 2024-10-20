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

use ILIAS\Exercise\PeerReview\Criteria\CriteriaFileManager;

/**
 * Class ilExcCriteriaFile
 *
 * @author Jörg Lützenkirchen <luetzenkirchen@leifos.com>
 * @author Alexander Killing <killing@leifos.de>
 */
class ilExcCriteriaFile extends ilExcCriteria
{
    protected \ILIAS\Exercise\PeerReview\DomainService $peer_review;
    protected string $requested_file_hash = "";

    public function __construct()
    {
        /** @var \ILIAS\DI\Container $DIC */
        global $DIC;

        parent::__construct();

        $request = $DIC->exercise()->internal()->gui()->request();
        $this->requested_file_hash = $request->getFileHash();
        $this->peer_review = $DIC->exercise()->internal()->domain()->peerReview();
    }

    public function getType(): string
    {
        return "file";
    }

    /**
     * @return \ILIAS\Exercise\PeerReview\Criteria\CriteriaFile[]
     */
    public function getFiles(): array
    {
        $file_manager = $this->peer_review->criteriaFile($this->ass->getId());
        $file = $file_manager->getFile($this->giver_id, $this->peer_id, $this->getId());
        $files = $file ? [$file]
            : [];
        return $files;
    }

    public function resetReview(): void
    {
        $this->peer_review->criteriaFile($this->ass->getId())
            ->delete($this->giver_id, $this->peer_id, $this->getId());
    }

    // PEER REVIEW

    public function addToPeerReviewForm($a_value = null): void
    {
        $existing = array();
        foreach ($this->getFiles() as $file) {
            $existing[] = $file->getTitle();
        }
        $files = new ilFileInputGUI($this->getTitle(), "prccc_file_" . $this->getId());
        $files->setInfo($this->getDescription());
        $files->setRequired($this->isRequired());
        $files->setValue(implode("<br />", $existing));
        $files->setAllowDeletion(true);
        $this->form->addItem($files);
    }

    /**
     * @throws ilException
     */
    public function importFromPeerReviewForm(): void
    {
        $file_manager = $this->peer_review->criteriaFile($this->ass->getId());
        if ($this->form->getItemByPostVar("prccc_file_" . $this->getId())->getDeletionFlag()) {
            $file_manager->delete($this->giver_id, $this->peer_id, $this->getId());
            $this->form->getItemByPostVar("prccc_file_" . $this->getId())->setValue("");
        }

        $incoming = $_FILES["prccc_file_" . $this->getId()];
        if ($incoming["tmp_name"]) {
            $org_name = basename($incoming["name"]);
            $file_manager->addFromLegacyUpload(
                $incoming,
                $this->giver_id,
                $this->peer_id,
                $this->getId()
            );
        }
    }

    public function hasValue($a_value): bool
    {
        return count($this->getFiles()) > 0;
    }

    public function validate($a_value): bool
    {
        $lng = $this->lng;

        // because of deletion flag we have to also check ourselves
        if ($this->isRequired()) {
            if (!$this->hasValue($a_value)) {
                if ($this->form) {
                    $this->form->getItemByPostVar("prccc_file_" . $this->getId())->setAlert($lng->txt("msg_input_is_required"));
                }
                return false;
            }
        }
        return true;
    }

    /**
     * @return false|mixed
     */
    public function getFileByHash()
    {
        $hash = trim($this->requested_file_hash);
        if ($hash != "") {
            foreach ($this->getFiles() as $file) {
                if (md5($file->getTitle()) == $hash) {
                    return $file;
                }
            }
        }
        return false;
    }

    public function getHTML($a_value): string
    {
        $ilCtrl = $this->ctrl;

        $crit_id = $this->getId()
            ?: "file";
        $ilCtrl->setParameterByClass(
            "ilExPeerReviewGUI",
            "fu",
            $this->giver_id . "__" . $this->peer_id . "__" . $crit_id
        );

        $files = array();
        foreach ($this->getFiles() as $file) {
            $ilCtrl->setParameterByClass("ilExPeerReviewGUI", "fuf", md5($file->getTitle()));
            $dl = $ilCtrl->getLinkTargetByClass("ilExPeerReviewGUI", "downloadPeerReview");
            $ilCtrl->setParameterByClass("ilExPeerReviewGUI", "fuf", "");

            $files[] = '<a href="' . $dl . '">' . basename($file) . '</a>';
        }

        $ilCtrl->setParameterByClass("ilExPeerReviewGUI", "fu", "");

        return implode("<br />", $files);
    }
}
