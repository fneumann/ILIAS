<?php
/******************************************************************************
 *
 * This file is part of ILIAS, a powerful learning management system.
 *
 * ILIAS is licensed with the GPL-3.0, you should have received a copy
 * of said license along with the source code.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 *      https://www.ilias.de
 *      https://github.com/ILIAS-eLearning
 *
 *****************************************************************************/
/**
 *
 * @author  Stefan Meyer <meyer@leifos.com>
 * @author  Fabian Schmid <fabian.schmid@ilub.unibe.ch>
 * @version $Id$
 *
 *
 * @ingroup ServicesAuthShibboleth
 */
class ilShibbolethRoleAssignmentTableGUI extends ilTable2GUI
{
    /**
     * @throws ilCtrlException
     */
    public function __construct(ilAuthShibbolethSettingsGUI $a_parent_obj, string $a_parent_cmd = '')
    {
        global $DIC;
        $this->ctrl = $DIC->ctrl();
        $this->lng = $DIC->language();
        parent::__construct($a_parent_obj, $a_parent_cmd);

        $this->addColumn('', 'f', 1);
        $this->addColumn($this->lng->txt('shib_rule_type'), 'type', "20%");
        $this->addColumn($this->lng->txt('shib_ilias_role'), 'role', "30%");
        $this->addColumn($this->lng->txt('shib_rule_condition'), 'condition', "20%");
        $this->addColumn($this->lng->txt('shib_add_remove'), 'add_remove', "30%");
        $this->setFormAction($this->ctrl->getFormAction($a_parent_obj));
        $this->setRowTemplate("tpl.show_role_assignment_row.html", "components/ILIAS/AuthShibboleth");
        $this->setDefaultOrderField('type');
        $this->setDefaultOrderDirection("desc");
    }

    /**
     * @throws ilCtrlException
     */
    protected function fillRow(array $a_set): void
    {
        $this->tpl->setVariable('VAL_ID', $a_set['id']);
        $this->tpl->setVariable('VAL_TYPE', $a_set['type']);
        $this->tpl->setVariable('VAL_CONDITION', $a_set['condition']);
        $this->tpl->setVariable('VAL_ROLE', $a_set['role']);
        $this->tpl->setVariable('TXT_EDIT', $this->lng->txt('edit'));
        if ($a_set['add']) {
            $this->tpl->setVariable('STATA_SRC', ilUtil::getImagePath('standard/icon_ok.svg'));
            $this->tpl->setVariable('STATA_ALT', $this->lng->txt('yes'));
        } else {
            $this->tpl->setVariable('STATA_SRC', ilUtil::getImagePath('standard/icon_not_ok.svg'));
            $this->tpl->setVariable('STATA_ALT', $this->lng->txt('no'));
        }
        if ($a_set['remove']) {
            $this->tpl->setVariable('STATB_SRC', ilUtil::getImagePath('standard/icon_ok.svg'));
            $this->tpl->setVariable('STATB_ALT', $this->lng->txt('yes'));
        } else {
            $this->tpl->setVariable('STATB_SRC', ilUtil::getImagePath('standard/icon_not_ok.svg'));
            $this->tpl->setVariable('STATB_ALT', $this->lng->txt('no'));
        }
        $this->ctrl->setParameter($this->getParentObject(), 'rule_id', $a_set['id']);
        $this->tpl->setVariable('EDIT_LINK', $this->ctrl->getLinkTarget($this->getParentObject(), 'editRoleAssignment'));
    }


    public function parse(array $rule_objs): void
    {
        $records_arr = [];
        foreach ($rule_objs as $rule) {
            $tmp_arr['id'] = $rule->getRuleId();
            $tmp_arr['type'] = $rule->isPluginActive() ? $this->lng->txt('shib_role_by_plugin') : $this->lng->txt('shib_role_by_attribute');
            $tmp_arr['add'] = $rule->isAddOnUpdateEnabled();
            $tmp_arr['remove'] = $rule->isRemoveOnUpdateEnabled();
            $tmp_arr['condition'] = $rule->conditionToString();
            $tmp_arr['role'] = ilObject::_lookupTitle($rule->getRoleId());
            $records_arr[] = $tmp_arr;
        }
        $this->setData($records_arr);
    }
}
