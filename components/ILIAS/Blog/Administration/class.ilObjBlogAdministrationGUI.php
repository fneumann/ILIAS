<?php

declare(strict_types=1);

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
 * Blog Administration Settings.
 *
 * @author Jörg Lützenkirchen <luetzenkirchen@leifos.com>
 * @ilCtrl_Calls ilObjBlogAdministrationGUI: ilPermissionGUI
 * @ilCtrl_IsCalledBy ilObjBlogAdministrationGUI: ilAdministrationGUI
 */
class ilObjBlogAdministrationGUI extends ilObjectGUI
{
    public function __construct(
        $a_data,
        int $a_id,
        bool $a_call_by_reference = true,
        bool $a_prepare_output = true
    ) {
        global $DIC;

        $this->lng = $DIC->language();
        $this->settings = $DIC->settings();
        $this->ctrl = $DIC->ctrl();
        $this->type = "blga";
        parent::__construct($a_data, $a_id, $a_call_by_reference, $a_prepare_output);

        $this->lng->loadLanguageModule("blog");
    }

    public function executeCommand(): void
    {
        $next_class = $this->ctrl->getNextClass($this);
        $cmd = $this->ctrl->getCmd();

        $this->prepareOutput();

        switch ($next_class) {
            case 'ilpermissiongui':
                $this->tabs_gui->setTabActive('perm_settings');
                $perm_gui = new ilPermissionGUI($this);
                $this->ctrl->forwardCommand($perm_gui);
                break;

            default:
                if (!$cmd || $cmd === 'view') {
                    $cmd = "editSettings";
                }

                $this->$cmd();
                break;
        }
    }

    public function getAdminTabs(): void
    {
        if ($this->checkPermissionBool("visible,read")) {
            $this->tabs_gui->addTarget(
                "settings",
                $this->ctrl->getLinkTarget($this, "editSettings"),
                array("editSettings", "view")
            );
        }

        if ($this->checkPermissionBool('edit_permission')) {
            $this->tabs_gui->addTarget(
                "perm_settings",
                $this->ctrl->getLinkTargetByClass('ilpermissiongui', "perm"),
                array(),
                'ilpermissiongui'
            );
        }
    }

    public function editSettings(?ilPropertyFormGUI $a_form = null): void
    {
        $lng = $this->lng;
        $ilSetting = $this->settings;

        $this->tabs_gui->setTabActive('settings');

        if (!$ilSetting->get("disable_wsp_blogs")) {
            $this->tpl->setOnScreenMessage('info', $lng->txt("blog_admin_toggle_info"));
        } else {
            $this->tpl->setOnScreenMessage('info', $lng->txt("blog_admin_inactive_info"));
        }

        if (!$a_form) {
            $a_form = $this->initFormSettings();
        }
        $this->tpl->setContent($a_form->getHTML());
    }

    public function saveSettings(): void
    {
        $ilCtrl = $this->ctrl;

        $this->checkPermission("write");

        $form = $this->initFormSettings();
        if ($form->checkInput()) {

            $blga_set = new ilSetting("blga");
            $blga_set->set("mask", (string) (bool) $form->getInput("mask"));
            $blga_set->set("est_reading_time", (string) (bool) $form->getInput("est_reading_time"));

            $this->tpl->setOnScreenMessage('success', $this->lng->txt("settings_saved"), true);
            $ilCtrl->redirect($this, "editSettings");
        }

        $form->setValuesByPost();
        $this->editSettings($form);
    }

    public function cancel(): void
    {
        $ilCtrl = $this->ctrl;

        $ilCtrl->redirect($this, "view");
    }

    protected function initFormSettings(): ilPropertyFormGUI
    {
        $lng = $this->lng;

        $form = new ilPropertyFormGUI();
        $form->setFormAction($this->ctrl->getFormAction($this));
        $form->setTitle($this->lng->txt('blog_settings'));

        if ($this->checkPermissionBool("write")) {
            $form->addCommandButton('saveSettings', $this->lng->txt('save'));
            $form->addCommandButton('cancel', $this->lng->txt('cancel'));
        }

        $blga_set = new ilSetting("blga");

        $cb_prop = new ilCheckboxInputGUI(
            $lng->txt("blog_est_reading_time"),
            "est_reading_time"
        );
        $cb_prop->setInfo($lng->txt("blog_est_reading_time_info"));
        $cb_prop->setChecked((bool) $blga_set->get("est_reading_time"));
        $form->addItem($cb_prop);

        return $form;
    }
}
