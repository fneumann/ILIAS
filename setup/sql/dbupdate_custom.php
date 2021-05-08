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
            "type" => "string",
            "notnull" => false,
            "length" => 50,
            "default" => null
        ));
    }
    $ilDB->manipulate("UPDATE exc_assignment SET type_str = 'ilExAssTypeUpload' WHERE type = 1");
    $ilDB->manipulate("UPDATE exc_assignment SET type_str = 'ilExAssTypeBlog' WHERE type = 2");
    $ilDB->manipulate("UPDATE exc_assignment SET type_str = 'ilExAssTypePortfolio' WHERE type = 3");
    $ilDB->manipulate("UPDATE exc_assignment SET type_str = 'ilExAssTypeUploadTeam' WHERE type = 4");
    $ilDB->manipulate("UPDATE exc_assignment SET type_str = 'ilExAssTypeText' WHERE type = 5");
    $ilDB->manipulate("UPDATE exc_assignment SET type_str = 'ilExAssTypeWikiTeam' WHERE type = 6");
?>