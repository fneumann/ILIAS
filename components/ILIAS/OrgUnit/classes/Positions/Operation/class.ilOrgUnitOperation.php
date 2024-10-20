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
 ********************************************************************
 */

/**
 * Class ilOrgUnitOperation
 * @author            Fabian Schmid <fs@studer-raimann.ch>
 */
class ilOrgUnitOperation
{
    public const OP_READ_LEARNING_PROGRESS = 'read_learning_progress';
    public const OP_WRITE_LEARNING_PROGRESS = 'write_learning_progress';
    public const OP_EDIT_SUBMISSION_GRADES = 'edit_submissions_grades';
    public const OP_ACCESS_RESULTS = 'access_results';
    public const OP_MANAGE_MEMBERS = 'manage_members';
    public const OP_ACCESS_ENROLMENTS = 'access_enrolments';
    public const OP_MANAGE_PARTICIPANTS = 'manage_participants';
    public const OP_SCORE_PARTICIPANTS = 'score_participants';
    public const OP_VIEW_CERTIFICATES = 'view_certificates';
    public const OP_VIEW_COMPETENCES = 'view_competences';
    public const OP_EDIT_USER_ACCOUNTS = 'edit_user_accounts';
    public const OP_VIEW_MEMBERS = 'view_members';
    public const OP_VIEW_INDIVIDUAL_PLAN = 'view_individual_plan';
    public const OP_EDIT_INDIVIDUAL_PLAN = 'edit_individual_plan';
    public const OP_READ_EMPLOYEE_TALK = 'read_employee_talk';
    public const OP_CREATE_EMPLOYEE_TALK = 'create_employee_talk';
    public const OP_EDIT_EMPLOYEE_TALK = 'edit_employee_talk';

    protected int $operation_id = 0;
    protected string $operation_string = '';
    protected string $description = '';
    protected int $list_order = 0;
    protected int $context_id = 0;

    public function __construct($operation_id = 0)
    {
        $this->operation_id = $operation_id;
    }

    public function getOperationId(): ?int
    {
        return $this->operation_id;
    }

    public function getOperationString(): string
    {
        return $this->operation_string;
    }

    public function withOperationString(string $operation_string): self
    {
        $clone = clone $this;
        $clone->operation_string = $operation_string;
        return $clone;
    }

    public function getDescription(): string
    {
        return $this->description;
    }

    public function withDescription(string $description): self
    {
        $clone = clone $this;
        $clone->description = $description;
        return $clone;
    }

    public function getListOrder(): int
    {
        return $this->list_order;
    }

    public function withListOrder(int $list_order): self
    {
        $clone = clone $this;
        $clone->list_order = $list_order;
        return $clone;
    }

    public function getContextId(): int
    {
        return $this->context_id;
    }

    public function withContextId(int $context_id): self
    {
        $clone = clone $this;
        $clone->context_id = $context_id;
        return $clone;
    }
}
