<?php
/**
 * Base class for exercise assignment hook plugins
 *
 * @ingroup ModulesExercise
 */
abstract class ilAssignmentHookPlugin extends ilPlugin
{
    /**
     * @inheritDoc
     */
    final public function getComponentType() {
        return IL_COMP_MODULE;
    }

    /**
     * @inheritDoc
     */
    final public function getComponentName() {
        return "Exercise";
    }

    /**
     * @inheritDoc
     */
    final public function getSlot() {
        return "AssignmentHook";
    }

    /**
     * @inheritDoc
     */
    final public function getSlotId() {
        return "exashk";
    }

    /**
     * @inheritDoc
     */
    final protected function slotInit() {
        // nothing to do here.
    }

    /**
     * Get the string identifiers of the available assignment types
     * plugin authors have to take care of unique string identifiers
     * @return string[]
     */
    abstract function getAssignmentTypeStringIdentifiers();

    /**
     * Get an assignment type by its string identifier
     * @param string $a_identifier
     * @return ilExAssignmentTypeInterface
     */
    abstract function getAssignmentTypeByStringIdentifier(string $a_identifier);

    /**
     * Get an assignment type GUI by its string identifier
     * @param string $a_identifier
     * @return ilExAssignmentTypeGUIInterface
     */
    abstract function getAssignmentTypeGUIByStringIdentifier(string $a_identifier);

    /**
     * Get the class names of the assignment type GUIs
     * @return string[] (indexed by string identifier)
     */
    abstract function getAssignmentTypeGUIClassNames();
}