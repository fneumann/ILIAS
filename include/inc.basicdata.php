<?php
/**
* inc.basicdata.php
* basic system settings
*
* @author Stefan Meyer <smeyer@databay.de> 
* @author Sascha Hofmann <shofmann@databay.de> 
* @version $Id$
* 
* @package ilias-core
*/

// load message template
$tpl->addBlockFile("MSG","sys_message","tpl.message.html");

// load all settings. later we overwrite posted settings
$settings = $ilias->getAllSettings();

if (isset($_POST["save_settings"]))  // formular sent
{
	//init checking var
	$form_valid = true;
	
	// check required fields
	if (empty($_POST["admin_firstname"]) or empty($_POST["admin_lastname"])
		or empty($_POST["admin_street"]) or empty($_POST["admin_zipcode"])
		or empty($_POST["admin_country"]) or empty($_POST["admin_city"])
		or empty($_POST["admin_phone"]) or empty($_POST["admin_email"]))
	{
		$tpl->setVariable("MSG", $lng->txt("fill_out_all_required_fields"));
		$form_valid = false;
	}
	// check email adresses
	// feedback_recipient
	if (!TUtil::is_email($_POST["feedback_recipient"]) and !empty($_POST["feedback_recipient"]) and $form_valid)
	{
		$tpl->setVariable("MSG", $lng->txt("input_error").": '".$lng->txt("feedback_recipient")."'<br/>".$lng->txt("email_not_valid"));
		$form_valid = false;
	}
	
	// error_recipient
	if (!TUtil::is_email($_POST["error_recipient"]) and !empty($_POST["error_recipient"]) and $form_valid)
	{
		$tpl->setVariable("MSG", $lng->txt("input_error").": '".$lng->txt("error_recipient")."'<br/>".$lng->txt("email_not_valid"));
		$form_valid = false;
	}

	// admin email
	if (!TUtil::is_email($_POST["admin_email"]) and $form_valid)
	{
		$tpl->setVariable("MSG", $lng->txt("input_error").": '".$lng->txt("email")."'<br/>".$lng->txt("email_not_valid"));
		$form_valid = false;
	}
	
	if (!$form_valid)	//required fields not satisfied. Set formular to already fill in values
	{
		// values from formular
		$settings["inst_name"] = $_POST["inst_name"];
		$settings["inst_info"] = $_POST["inst_info"];
		$settings["feedback_recipient"] = $_POST["feedback_recipient"];
		$settings["error_recipient"] = $_POST["error_recipient"];

		$settings["tpl_path"] = $_POST["tpl_path"];
		$settings["lang_path"] = $_POST["lang_path"];
		$settings["convert_path"] = $_POST["convert_path"];
		$settings["zip_path"] = $_POST["zip_path"];
		$settings["unzip_path"] = $_POST["unzip_path"];
		$settings["java_path"] = $_POST["java_path"];
		$settings["babylon_path"] = $_POST["babylon_path"];

		$settings["pub_section"] = $_POST["pub_section"];
		$settings["news"] = $_POST["news"];
		$settings["payment_system"] = $_POST["payment_system"];
		$settings["group_file_sharing"] = $_POST["group_file_sharing"];
		$settings["crs_enable"] = $_POST["crs_enable"];

		$settings["ldap_enable"] = $_POST["ldap_enable"];
		$settings["ldap_server"] = $_POST["ldap_server"];
		$settings["ldap_port"] = $_POST["ldap_port"];
		$settings["ldap_basedn"] = $_POST["ldap_basedn"];

		$settings["mail_enable"] = $_POST["mail_enable"];
		$settings["mail_server"] = $_POST["mail_server"];
		$settings["mail_port"] = $_POST["mail_port"];

		$settings["admin_firstname"] = $_POST["admin_firstname"];
		$settings["admin_lastname"] = $_POST["admin_lastname"];
		$settings["admin_title"] = $_POST["admin_title"];
		$settings["admin_position"] = $_POST["admin_position"];
		$settings["admin_institution"] = $_POST["admin_institution"];
		$settings["admin_street"] = $_POST["admin_street"];
		$settings["admin_zipcode"] = $_POST["admin_zipcode"];
		$settings["admin_city"] = $_POST["admin_city"];
		$settings["admin_country"] = $_POST["admin_country"];
		$settings["admin_phone"] = $_POST["admin_phone"];
		$settings["admin_email"] = $_POST["admin_email"];
	}
	else // all required fields ok
	{
		$ilias->setSetting("inst_name",$_POST["inst_name"]);
		$ilias->setSetting("inst_info",$_POST["inst_info"]);
		$ilias->setSetting("feedback_recipient",$_POST["feedback_recipient"]);
		$ilias->setSetting("error_recipient",$_POST["error_recipient"]);

		$ilias->setSetting("convert_path",$_POST["convert_path"]);
		$ilias->setSetting("zip_path",$_POST["zip_path"]);
		$ilias->setSetting("unzip_path",$_POST["unzip_path"]);
		$ilias->setSetting("java_path",$_POST["java_path"]);
		$ilias->setSetting("babylon_path",$_POST["babylon_path"]);

		$ilias->setSetting("pub_section",$_POST["pub_section"]);
		$ilias->setSetting("news",$_POST["news"]);
		$ilias->setSetting("payment_system",$_POST["payment_system"]);
		$ilias->setSetting("group_file_sharing",$_POST["group_file_sharing"]);
		$ilias->setSetting("crs_enable",$_POST["crs_enable"]);

		$ilias->setSetting("ldap_enable",$_POST["ldap_enable"]);
		$ilias->setSetting("ldap_server",$_POST["ldap_server"]);
		$ilias->setSetting("ldap_port",$_POST["ldap_port"]);
		$ilias->setSetting("ldap_basedn",$_POST["ldap_basedn"]);

		$ilias->setSetting("mail_enable",$_POST["mail_enable"]);
		$ilias->setSetting("mail_server",$_POST["mail_server"]);
		$ilias->setSetting("mail_port",$_POST["mail_port"]);

		$ilias->setSetting("admin_firstname",$_POST["admin_firstname"]);
		$ilias->setSetting("admin_lastname",$_POST["admin_lastname"]);
		$ilias->setSetting("admin_title",$_POST["admin_title"]);
		$ilias->setSetting("admin_position",$_POST["admin_position"]);
		$ilias->setSetting("admin_institution",$_POST["admin_institution"]);
		$ilias->setSetting("admin_street",$_POST["admin_street"]);
		$ilias->setSetting("admin_zipcode",$_POST["admin_zipcode"]);
		$ilias->setSetting("admin_city",$_POST["admin_city"]);
		$ilias->setSetting("admin_country",$_POST["admin_country"]);
		$ilias->setSetting("admin_phone",$_POST["admin_phone"]);
		$ilias->setSetting("admin_email",$_POST["admin_email"]);

		$ilias->ini->setVariable("server","tpl_path",$_POST["tpl_path"]);
		$ilias->ini->setVariable("language","path",$_POST["lang_path"]);
		$ilias->ini->setVariable("language","default",$_POST["default_language"]);
		$ilias->ini->setVariable("layout","skin",$_POST["default_skin"]);
		$ilias->ini->setVariable("layout","style",$_POST["default_style"]);
		$ilias->ini->write();

		$tpl->setVariable("MSG", $lng->txt("saved_successfully"));
		
		$settings = $ilias->getAllSettings();
	}
}

$tpl->setVariable("TXT_BASIC_DATA", $lng->txt("basic_data"));

//language things
$tpl->setVariable("TXT_ILIAS_VERSION", $lng->txt("ilias_version"));
$tpl->setVariable("TXT_DB_VERSION", $lng->txt("db_version"));
$tpl->setVariable("TXT_INST_ID", $lng->txt("inst_id"));
$tpl->setVariable("TXT_HOSTNAME", $lng->txt("host"));
$tpl->setVariable("TXT_IP_ADDRESS", $lng->txt("ip_address"));
$tpl->setVariable("TXT_SERVER_SOFTWARE", $lng->txt("server_software"));
$tpl->setVariable("TXT_HTTP_PATH", $lng->txt("http_path"));
$tpl->setVariable("TXT_ABSOLUTE_PATH", $lng->txt("absolute_path"));

$tpl->setVariable("TXT_INST_NAME", $lng->txt("inst_name"));
$tpl->setVariable("TXT_INST_INFO", $lng->txt("inst_info"));
$tpl->setVariable("TXT_DEFAULT_SKIN", $lng->txt("default_skin"));
$tpl->setVariable("TXT_DEFAULT_STYLE", $lng->txt("default_style"));
$tpl->setVariable("TXT_DEFAULT_LANGUAGE", $lng->txt("default_language"));
$tpl->setVariable("TXT_FEEDBACK_RECIPIENT", $lng->txt("feedback_recipient"));
$tpl->setVariable("TXT_ERROR_RECIPIENT", $lng->txt("error_recipient"));

$tpl->setVariable("TXT_PATHES", $lng->txt("pathes"));
$tpl->setVariable("TXT_TPL_PATH", $lng->txt("tpl_path"));
$tpl->setVariable("TXT_LANG_PATH", $lng->txt("lang_path"));
$tpl->setVariable("TXT_CONVERT_PATH", $lng->txt("path_to_convert"));
$tpl->setVariable("TXT_ZIP_PATH", $lng->txt("path_to_zip"));
$tpl->setVariable("TXT_UNZIP_PATH", $lng->txt("path_to_unzip"));
$tpl->setVariable("TXT_JAVA_PATH", $lng->txt("path_to_java"));
$tpl->setVariable("TXT_BABYLON_PATH", $lng->txt("path_to_babylon"));

$tpl->setVariable("TXT_MODULES", $lng->txt("modules"));
$tpl->setVariable("TXT_PUB_SECTION", $lng->txt("pub_section"));
$tpl->setVariable("TXT_NEWS", $lng->txt("news"));
$tpl->setVariable("TXT_PAYMENT_SYSTEM", $lng->txt("payment_system"));
$tpl->setVariable("TXT_GROUP_FILE_SHARING", $lng->txt("group_filesharing"));
$tpl->setVariable("TXT_CRS_MANAGEMENT_SYSTEM", $lng->txt("crs_management_system"));

$tpl->setVariable("TXT_LDAP", $lng->txt("ldap"));
$tpl->setVariable("TXT_LDAP_ENABLE", $lng->txt("enable"));
$tpl->setVariable("TXT_LDAP_SERVER", $lng->txt("server"));
$tpl->setVariable("TXT_LDAP_PORT", $lng->txt("port"));
$tpl->setVariable("TXT_LDAP_BASEDN", $lng->txt("basedn"));

$tpl->setVariable("TXT_MAIL", $lng->txt("mail"));
$tpl->setVariable("TXT_MAIL_ENABLE", $lng->txt("enable"));
$tpl->setVariable("TXT_MAIL_SERVER", $lng->txt("server"));
$tpl->setVariable("TXT_MAIL_PORT", $lng->txt("port"));

$tpl->setVariable("TXT_CONTACT_INFORMATION", $lng->txt("contact_information"));
$tpl->setVariable("TXT_MUST_FILL_IN", $lng->txt("must_fill_in"));
$tpl->setVariable("TXT_ADMIN", $lng->txt("administrator"));
$tpl->setVariable("TXT_FIRSTNAME", $lng->txt("firstname"));
$tpl->setVariable("TXT_LASTNAME", $lng->txt("lastname"));
$tpl->setVariable("TXT_TITLE", $lng->txt("title"));
$tpl->setVariable("TXT_POSITION", $lng->txt("position"));
$tpl->setVariable("TXT_INSTITUTION", $lng->txt("institution"));
$tpl->setVariable("TXT_STREET", $lng->txt("street"));
$tpl->setVariable("TXT_ZIPCODE", $lng->txt("zipcode"));
$tpl->setVariable("TXT_CITY", $lng->txt("city"));
$tpl->setVariable("TXT_COUNTRY", $lng->txt("country"));
$tpl->setVariable("TXT_PHONE", $lng->txt("phone"));
$tpl->setVariable("TXT_EMAIL", $lng->txt("email"));
$tpl->setVariable("TXT_SAVE", $lng->txt("save"));

//values
$loc = "adm_object.php?ref_id=".$_GET["ref_id"];
$tpl->setVariable("FORMACTION_BASICDATA", $loc);
$tpl->setVariable("HTTP_PATH", "http://".$_SERVER["SERVER_NAME"].dirname($_SERVER["REQUEST_URI"]));
$tpl->setVariable("ABSOLUTE_PATH", dirname($_SERVER["SCRIPT_FILENAME"]));
$tpl->setVariable("HOSTNAME", $_SERVER["SERVER_NAME"]);
$tpl->setVariable("SERVER_PORT", $_SERVER["SERVER_PORT"]);
$tpl->setVariable("SERVER_ADMIN", $_SERVER["SERVER_ADMIN"]);
$tpl->setVariable("SERVER_SOFTWARE", $_SERVER["SERVER_SOFTWARE"]);
$tpl->setVariable("IP_ADDRESS", $_SERVER["SERVER_ADDR"]);

//Daten aus INI holen
$tpl->setVariable("TPL_PATH",$ilias->ini->readVariable("server","tpl_path"));
$tpl->setVariable("LANG_PATH",$ilias->ini->readVariable("language","path"));

//Daten aus Settings holen
$tpl->setVariable("DB_VERSION",$settings["db_version"]);
$tpl->setVariable("ILIAS_VERSION",$settings["ilias_version"]);
$tpl->setVariable("INST_ID",$settings["inst_id"]);
$tpl->setVariable("INST_NAME",$settings["inst_name"]);
$tpl->setVariable("INST_INFO",$settings["inst_info"]);
$tpl->setVariable("CONVERT_PATH",$settings["convert_path"]);
$tpl->setVariable("ZIP_PATH",$settings["zip_path"]);
$tpl->setVariable("UNZIP_PATH",$settings["unzip_path"]);
$tpl->setVariable("JAVA_PATH",$settings["java_path"]);
$tpl->setVariable("BABYLON_PATH",$settings["babylon_path"]);
$tpl->setVariable("FEEDBACK_RECIPIENT",$settings["feedback_recipient"]);
$tpl->setVariable("ERROR_RECIPIENT",$settings["error_recipient"]);

if ($settings["pub_section"]=="y")
{
	$tpl->setVariable("PUB_SECTION","checked=\"checked\"");
}

if ($settings["news"]=="y")
{
	$tpl->setVariable("NEWS","checked=\"checked\"");
}

if ($settings["payment_system"]=="y")
{
	$tpl->setVariable("PAYMENT_SYSTEM","checked=\"checked\"");
}

if ($settings["group_file_sharing"]=="y")
{
	$tpl->setVariable("GROUP_FILE_SHARING","checked=\"checked\"");
}

if ($settings["crs_enable"]=="y")
{
	$tpl->setVariable("CRS_MANAGEMENT_SYSTEM","checked=\"checked\"");
}

// skin selection
$ilias->getSkins();
$tpl->setCurrentBlock("selectskin");

foreach ($ilias->skins as $row)
{
	if ($ilias->ini->readVariable("layout","skin") == $row["name"])
	{
		$tpl->setVariable("SKINSELECTED", " selected=\"selected\"");
	}

	$tpl->setVariable("SKINVALUE", $row["name"]);
	$tpl->setVariable("SKINOPTION", $row["name"]);
	$tpl->parseCurrentBlock();
}

// style selection
$ilias->getStyles($ilias->ini->readVariable("layout","skin"));
$tpl->setCurrentBlock("selectstyle");

foreach ($ilias->styles as $row)
{
	if ($ilias->ini->readVariable("layout","style") == $row["name"])
	{
		$tpl->setVariable("STYLESELECTED", " selected=\"selected\"");
	}

	$tpl->setVariable("STYLEVALUE", $row["name"]);
	$tpl->setVariable("STYLEOPTION", $row["name"]);
	$tpl->parseCurrentBlock();
}

// language selection
$languages = $lng->getInstalledLanguages();
$tpl->setCurrentBlock("selectlanguage");

foreach ($languages as $lang_key)
{
	if ($ilias->ini->readVariable("language","default") == $lang_key)
	{
		$tpl->setVariable("LANGSELECTED", " selected=\"selected\"");
	}

	$tpl->setVariable("LANGVALUE", $lang_key);
	$tpl->setVariable("LANGOPTION", $lng->txt("lang_".$lang_key));	
	$tpl->parseCurrentBlock();
}

if ($settings["ldap_enable"] == "y")
{
	$tpl->setVariable("LDAP_ENABLE","checked=\"checked\"");
}

$tpl->setVariable("LDAP_SERVER",$settings["ldap_server"]);
$tpl->setVariable("LDAP_PORT",$settings["ldap_port"]);
$tpl->setVariable("LDAP_BASEDN",$settings["ldap_basedn"]);

if ($settings["mail_enable"] == "y")
{
	$tpl->setVariable("MAIL_ENABLE","checked=\"checked\"");
}

$tpl->setVariable("MAIL_SERVER",$settings["mail_server"]);
$tpl->setVariable("MAIL_PORT",$settings["mail_port"]);

$tpl->setVariable("ADMIN_FIRSTNAME",$settings["admin_firstname"]);
$tpl->setVariable("ADMIN_LASTNAME",$settings["admin_lastname"]);
$tpl->setVariable("ADMIN_TITLE",$settings["admin_title"]);
$tpl->setVariable("ADMIN_POSITION",$settings["admin_position"]);
$tpl->setVariable("ADMIN_INSTITUTION",$settings["admin_institution"]);
$tpl->setVariable("ADMIN_STREET",$settings["admin_street"]);
$tpl->setVariable("ADMIN_ZIPCODE",$settings["admin_zipcode"]);
$tpl->setVariable("ADMIN_CITY",$settings["admin_city"]);
$tpl->setVariable("ADMIN_COUNTRY",$settings["admin_country"]);
$tpl->setVariable("ADMIN_PHONE",$settings["admin_phone"]);
$tpl->setVariable("ADMIN_EMAIL",$settings["admin_email"]);

$tpl->setCurrentBlock("sys_message");
$tpl->parseCurrentBlock();
?>