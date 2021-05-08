<#1>
<?php
    /**
     * todo: these update steps should be moved at the right place when merged into trunk
     * todo: then remove the comment for dbupdate_custom in .gitignore
     */
    $ilCtrlStructureReader->getStructure();
?>
<#2>
<?php
    /**
     * Migrate integer assignment types to string identifiers
     */
    if (!$ilDB->tableColumnExists('exc_assignment', 'type_str')) {
        $ilDB->addTableColumn('exc_assignment', 'type_str', array(
            "type" => "text",
            "notnull" => false,
            "length" => 50,
            "default" => null
        ));
    }
    $ilDB->manipulate("UPDATE exc_assignment SET type_str = 'upld' WHERE type = 1");
    $ilDB->manipulate("UPDATE exc_assignment SET type_str = 'blog' WHERE type = 2");
    $ilDB->manipulate("UPDATE exc_assignment SET type_str = 'prtf' WHERE type = 3");
    $ilDB->manipulate("UPDATE exc_assignment SET type_str = 'uptm' WHERE type = 4");
    $ilDB->manipulate("UPDATE exc_assignment SET type_str = 'text' WHERE type = 5");
    $ilDB->manipulate("UPDATE exc_assignment SET type_str = 'wiki' WHERE type = 6");
?>