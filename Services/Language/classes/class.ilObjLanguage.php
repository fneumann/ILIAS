<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once "./Services/Object/classes/class.ilObject.php";

/**
 * Class ilObjLanguage
 *
 * @author Sascha Hofmann <shofmann@databay.de>
 * @version $Id$
 *
 * @extends ilObject
 */
class ilObjLanguage extends ilObject
{
	/**
	 * @var int $delete_chunk_size	maximum deleted done execute in one statement by listing indentifiers in an IN condition
	 * @see self::replaceLangData()
	 */
	private static $delete_chunk_size = 1000;

	/**
	 * @var int $insert_chunk_size	maximum inserts done in one statement by repeating values
	 * @see self::replaceLangData()
	 */
	private static $insert_chunk_size = 1000;

	/**
	 * separator of module, comment separator, identifier & values
	 * in language files
	 *
	 * @var		string
	 * @access	private
	 */
	var $separator;
	var $comment_separator;
	var $lang_default;
	var $lang_user;
	var $lang_path;

	var $key;
	var $status;


	/**
	 * Constructor
	 * 
	 * @access	public
	 * @param	integer	reference_id or object_id
	 * @param	boolean	treat the id as reference_id (true) or object_id (false)
	 */
	function __construct($a_id = 0, $a_call_by_reference = false)
	{
		global $lng;

		$this->type = "lng";
		parent::__construct($a_id,$a_call_by_reference);

		$this->type = "lng";
		$this->key = $this->title;
		$this->status = $this->desc;
		$this->lang_default = $lng->lang_default;
		$this->lang_user = $lng->lang_user;
		$this->lang_path = $lng->lang_path;
		$this->cust_lang_path = $lng->cust_lang_path;
		$this->separator = $lng->separator;
		$this->comment_separator = $lng->comment_separator;
	}


	/**
	 * Get the language objects of the installed languages
	 * @return self[]
	 */
	public static function getInstalledLanguages()
	{
		$objects = array();
		$languages = ilObject::_getObjectsByType("lng");
		foreach ($languages as $lang)
		{
			$langObj = new ilObjLanguage($lang["obj_id"], false);
			if ($langObj->isInstalled())
			{
				$objects[] = $langObj;
			}
			else
			{
				unset($langObj);
			}
		}
		return $objects;
	}


	/**
	 * get language key
	 *
	 * @return	string		language key
	 */
	function getKey()
	{
		return $this->key;
	}

	/**
	 * get language status
	 *
	 * @return	string		language status
	 */
	function getStatus()
	{
		return $this->status;
	}

	/**
	 * check if language is system language
	 */
	function isSystemLanguage()
	{
		if ($this->key == $this->lang_default)
			return true;
		else
			return false;
	}

	/**
	 * check if language is system language
	 */
	function isUserLanguage()
	{
		if ($this->key == $this->lang_user)
		{
			return true;
		}
		else
		{
			return false;
		}
	}


	/**
	 * Check language object status, and return true if language is installed.
	 * 
	 * @return  boolean     true if installed
	 */
	function isInstalled()
	{
		if (substr($this->getStatus(), 0, 9) == "installed")
		{
			return true;
		}
		else
		{
			return false;
		}
	}

	/**
	 * Check language object status, and return true if a local language file is installed.
	 * @return  boolean     true if local language is installed
	 */
	function isLocal()
	{
		if (substr($this->getStatus(), 10) == "local")
		{
			return true;
		}
		else
		{
			return false;
		}
	}

	/**
	 * Install language
	 * @param   string  $scope  "global" or "local"
	 * @return	string	installed language key
	 */
	public function install($scope = 'global')
	{
		if (($this->isInstalled() == false) || 
			($this->isInstalled() == true && $this->isLocal() == false && $scope == 'local'))
		{
			if ($this->check($scope))
			{
				// lang-file is ok. Flush data in db and...
				if ($scope == 'global')
				{
					$this->flush('keep_local');
				}

				// ...re-insert data from lang-file
				$this->insert($scope);

				// update information in db-table about available/installed languages
				$this->setDescription($scope == 'local' ? 'installed_local' : 'installed');
				$this->update();
				return $this->getKey();
			}
		}
		return "";
	}


	/**
	 * Uninstall language
	 * @return	string	uninstalled language key
	 */
	public function uninstall()
	{
		if ($this->isInstalled() && ($this->key != $this->lang_default) && ($this->key != $this->lang_user))
		{
			$this->flush('all');
			$this->setDescription("not_installed");
			$this->update();
			$this->resetUserLanguage($this->getKey());
			return $this->getKey();
		}
		return "";
	}

	/**
	 * Uninstall local changes
	 * @return	bool
	 */
	public function uninstallChanges()
	{
		if ($this->isInstalled() && $this->check('global'))
		{
			$this->flush('all');
			$this->insert();
			$this->setDescription('installed');
			$this->update();
			return true;
		}
		return false;
	}


	/**
	 * Refresh language
	 * @return bool
	 */
	public function refresh()
	{
		if ($this->isInstalled() && $this->check('global'))
		{
			$this->flush('keep_local');
			$this->insert('global');

			if ($this->isLocal() && $this->check('local'))
			{
				$this->insert('local');
			}

			$this->update();
			return true;
		}
		return false;
	}


	/**
	 * Refresh languages of activated plugins
	 * @var array|null	keys of languages to be refreshed (not yet supported, all available will be refreshed)
	 * @todo: provide $a_lang_keys for ilPlugin::updateLanguages() when it is supported there
	 */
	public static function refreshPlugins($a_lang_keys = null)
	{
		global $ilPluginAdmin;

		// refresh languages of activated plugins
		include_once("./Services/Component/classes/class.ilPluginSlot.php");
		$slots = ilPluginSlot::getAllSlots();
		foreach ($slots as $slot)
		{
			$act_plugins = $ilPluginAdmin->getActivePluginsForSlot($slot["component_type"],
				$slot["component_name"], $slot["slot_id"]);
			foreach ($act_plugins as $plugin)
			{
				include_once("./Services/Component/classes/class.ilPlugin.php");
				$pl = ilPlugin::getPluginObject($slot["component_type"],
					$slot["component_name"], $slot["slot_id"], $plugin);
				if (is_object($pl))
				{
					$pl->updateLanguages($a_lang_keys);
				}
			}
		}
	}


	/**
	* Delete languge data
	*
	* @param	string		lang key
	*/
	static function _deleteLangData($a_lang_key, $a_keep_local_change = false)
	{
		global $ilDB;
		if (!$a_keep_local_change)
		{
			$ilDB->manipulate("DELETE FROM lng_data WHERE lang_key = ".
				$ilDB->quote($a_lang_key, "text"));
		}
		else
		{
			$ilDB->manipulate("DELETE FROM lng_data WHERE lang_key = ".
				$ilDB->quote($a_lang_key, "text").
				" AND local_change IS NULL");
		}
	}

	/**
	 * remove language data from database
	 * @param   string     "all" or "keep_local"
	 */
	function flush($a_mode = 'all')
	{
		global $ilDB;
		
		ilObjLanguage::_deleteLangData($this->key, ($a_mode == 'keep_local'));

		if ($a_mode == 'all')
		{
			$ilDB->manipulate("DELETE FROM lng_modules WHERE lang_key = ".
				$ilDB->quote($this->key, "text"));
		}
	}


	/**
	* get locally changed language entries
	* @param    string  	minimum change date "yyyy-mm-dd hh:mm:ss"
	* @param    string  	maximum change date "yyyy-mm-dd hh:mm:ss"
	* @return   array       [module][identifier] => value
	*/
	function getLocalChanges($a_min_date = "", $a_max_date = "")
	{
		global $ilDB;
		
		if ($a_min_date == "")
		{
			$a_min_date = "1980-01-01 00:00:00";
		}
		if ($a_max_date == "")
		{
			$a_max_date = "2200-01-01 00:00:00";
		}
		
		$q = sprintf("SELECT * FROM lng_data WHERE lang_key = %s ".
			"AND local_change >= %s AND local_change <= %s",
			$ilDB->quote($this->key, "text"), $ilDB->quote($a_min_date, "timestamp"),
			$ilDB->quote($a_max_date, "timestamp"));
		$result = $ilDB->query($q);
		
		$changes = array();
		while ($row = $result->fetchRow(ilDBConstants::FETCHMODE_ASSOC))
		{
			$changes[$row["module"]][$row["identifier"]] = $row["value"];
		}
		return $changes;
	}


	/**
	* get the date of the last local change
	* @param    string  	language key
	* @return   array       change_date "yyyy-mm-dd hh:mm:ss"
	*/
	static function _getLastLocalChange($a_key)
	{
		global $ilDB;

		$q = sprintf("SELECT MAX(local_change) last_change FROM lng_data ".
					"WHERE lang_key = %s AND local_change IS NOT NULL",
			$ilDB->quote($a_key, "text"));
		$result = $ilDB->query($q);

		if ($row = $result->fetchRow(ilDBConstants::FETCHMODE_ASSOC))
		{
			return $row['last_change'];
		}
		else
		{
			return "";
		}
	}


	/**
	 * Get the local changes of a language module
	 * @param string	$a_key		Language key
	 * @param string	$a_module 	Module key
	 * @return array	identifier => value
	 */
	static function _getLocalChangesByModule($a_key, $a_module)
	{
		/** @var ilDB $ilDB */
		global $ilDB;

		$changes = array();
		$result = $ilDB->queryF("SELECT * FROM lng_data WHERE lang_key = %s AND module = %s AND local_change IS NOT NULL",

			array('text', 'text'),
			array($a_key, $a_module));

		while ($row = $ilDB->fetchAssoc($result))
		{
			$changes[$row['identifier']] = $row['value'];
		}
		return $changes;
	}


	/**
	 * Insert language data from file into database
	 * This also inserts the language data of plugins
	 * 
	 * @param   string  $scope  'global' or "local"
	 */
	function insert($scope = 'global')
	{
		switch ($scope)
		{
			case 'local':
				$extension = '.lang.local';
				$path =  $this->cust_lang_path;
				break;

			case 'global':
			default:
				$scope = 'global';
				$extension = '.lang';
				$path = $this->lang_path;
				break;
		}

		$lang_file = $path . '/ilias_' . $this->key . $extension;
		if (is_file($lang_file))
		{
			// initialize the data for self::replaceLangData
			$data = array($this->key => array('common' => array()));

			// remove header first
			if ($content = $this->cut_header(file($lang_file)))
			{
				switch($scope)
				{
					case 'global':
						// reset change date for entries written from a global file
						// get all local changes for a global file
						$change_date = null;
						$local_changes = $this->getLocalChanges();
						break;

					case 'local':
						// set the change date to import time for entries written from a local file
						// get the modification date of the local file
						// get the newer local changes for a local file
						$change_date = date("Y-m-d H:i:s",time());
						$min_date = date("Y-m-d H:i:s", filemtime($lang_file));
						$local_changes = $this->getLocalChanges($min_date);
						break;
				}

				foreach ($content as $key => $val)
				{
					$separated = explode($this->separator,trim($val));
					$module = $separated[0];
					$identifier = $separated[1];
					$value = $separated[2];
					$remarks = null;

					$pos = strpos($value, $this->comment_separator);
					if ($pos !== false)
					{
						$remarks = substr($value, $pos + strlen($this->comment_separator));
						$value = substr($value , 0 , $pos);
					}

					// check if the value has a local change
					$changed_value = (string) $local_changes[$module][$identifier];

					// insert unchanged values in any case
					// overwrite equal changed values for global file to reset the change date
					if ( empty($changed_value) || ($scope == 'global' && $changed_value == $value))
					{
						$data[$this->key][$module][$identifier] = array (
							'value' =>  $value,
							'local_change' => $change_date,
							'remarks' => $remarks
						);
					}
				}
			}

			// write the updated data
			self::replaceLangData($data);

			// update plugins if global data is written
			if ($scope == 'global')
			{
				self::refreshPlugins(array($this->key));
			}
		}

	}

	/**
	 * Performance improved replacement of language data
	 * The cached data in lng_modules is updated, too
	 *
	 * @param array $a_data	lang_key => [module => [identifier => ['value' => string, 'local_change' => string, 'remarks' => string]]]
	 */
	static final function replaceLangData($a_data)
	{
		global $DIC;
		/** @var ilDBInterface $db */
		$db = $DIC->database();

		static $delete_count;
		static $insert_count;

		foreach ($a_data as $key => $modules)
		{
			foreach ($modules as $module => $entries)
			{
				// get the existing data of the module
				$old_entries = array();
				$result = $db->queryF("SELECT * FROM lng_data WHERE lang_key = %s AND module = %s",
					array('text', 'text'), array($key, $module));
				while ($row = $db->fetchAssoc($result))
				{
					// fault tolerance: mysql would throw duplicyte violation for same keys with different case
					$old_entries[strtolower($row['identifier'])] = $row;
				}

				// collect the data to be inserted or deleted
				$delete_ids = array();
				$insert_data = array();
				$module_data = array();
				foreach ($entries as $identifier => $data)
				{
					// fault tolerance: mysql would throw duplicyte violation for same keys with different case
					$identifier = strtolower($identifier);

					// check whether data has to be replaced, ignored or inserted
					if (isset($old_entries[$identifier]))
					{
						if ((string) $old_entries[$identifier]['value'] != (string) $data['value'] ||
							(string) $old_entries[$identifier]['local_change'] != (string) $data['local_change'] ||
							(string) $old_entries[$identifier]['remarks'] != (string) $data['remarks'])
						{
							// replace old data
							$delete_ids[] = $identifier;
							$delete_count++;
							$to_insert = true;
						}
						else
						{
							// keep old data
							$to_insert = false;
						}
					}
					else
					{
						// insert new data
						$to_insert = true;
					}

					// create query part for multiple insert
					if ($to_insert)
					{
						// index by identifier to prevent integrity violation
						$insert_data[$identifier] = '('
							. $db->quote($key, 'text'). ','
							. $db->quote($module, 'text'). ','
							. $db->quote($identifier, 'text'). ','
							. $db->quote(empty($data['value']) ? null : substr($data['value'],0, 4000), 'text'). ','
							. $db->quote(empty($data['remarks']) ? null : substr($data['remarks'],0, 250), 'text'). ','
							. $db->quote(empty($data['local_change']) ? null : $data['local_change'], 'timestamp'). ')';

						$insert_count++;
					}


					// add the value for lng_modules in any case
					$module_data[$identifier] = $data['value'];
				}

				// delete old data that will be replaced
				foreach (array_chunk($delete_ids, self::$delete_chunk_size) as $delete_chunk)
				{
					$db->manipulate("DELETE FROM lng_data WHERE lang_key = ".$db->quote($key, 'text')
						. " AND module = ".$db->quote($module, 'text')
						. " AND " . $db->in('identifier', $delete_chunk, false, 'text'));
				}

				// insert the data entries
				foreach (array_chunk($insert_data, self::$insert_chunk_size) as $insert_chunk)
				{
					$db->manipulate("INSERT INTO lng_data(lang_key, module, identifier, value, remarks, local_change) VALUES "
						. implode(',', $insert_chunk));
				}

				// replace the data in lng_modules
				self::replaceLangModule($key, $module, $module_data);
			}
		}

		// debug counts
		ilUtil::sendFailure('deleted: '. $delete_count. ' inserted:'. $insert_count, true);
	}


	/**
	* Replace language module array
	*/
	static final function replaceLangModule($a_key, $a_module, $a_array)
	{
		global $ilDB;

		ilGlobalCache::flushAll();

		$ilDB->manipulate(sprintf("DELETE FROM lng_modules WHERE lang_key = %s AND module = %s",
			$ilDB->quote($a_key, "text"), $ilDB->quote($a_module, "text")));

		$ilDB->insert("lng_modules", array(
			"lang_key" => array("text", $a_key),
			"module" => array("text", $a_module),
			"lang_array" => array("clob", serialize($a_array))
			));
	}

	/**
	* Replace lang entry
	*/
	static final function replaceLangEntry($a_module, $a_identifier,
		$a_lang_key, $a_value, $a_local_change = null, $a_remarks = null)
	{
		global $ilDB;

		if (isset($a_remarks))
		{
	        $a_remarks = substr($a_remarks, 0, 250);
		}
		if ($a_remarks == '')
		{
	        unset($a_remarks);
		}

		if (isset($a_value))
		{
	        $a_value = substr($a_value, 0, 4000);
	    }
		if ($a_value == '')
		{
	        unset($a_value);
		}

		$ilDB->replace(
			'lng_data',
			array(
				'module'		=> array('text',$a_module),
				'identifier'	=> array('text',$a_identifier),
				'lang_key'		=> array('text',$a_lang_key)
				),
			array(
				'value'			=> array('text',$a_value),
				'local_change'	=> array('timestamp',$a_local_change),
				'remarks'       => array('text', $a_remarks)
			)
		);
		return true;
	}
	
	/**
	* Replace lang entry
	*/
	static final function updateLangEntry($a_module, $a_identifier,
		$a_lang_key, $a_value, $a_local_change = null, $a_remarks = null)
	{
		global $ilDB;

		if (isset($a_remarks))
		{
	        $a_remarks = substr($a_remarks, 0, 250);
		}
		if ($a_remarks == '')
		{
	        unset($a_remarks);
		}

		if (isset($a_value))
		{
	        $a_value = substr($a_value, 0, 4000);
	    }
		if ($a_value == '')
		{
	        unset($a_value);
		}

		$ilDB->manipulate(sprintf("UPDATE lng_data " .
			"SET value = %s, local_change = %s, remarks = %s ".
			"WHERE module = %s AND identifier = %s AND lang_key = %s ",
			$ilDB->quote($a_value, "text"), $ilDB->quote($a_local_change, "timestamp"),
			$ilDB->quote($a_remarks, "text"),
			$ilDB->quote($a_module, "text"), $ilDB->quote($a_identifier, "text"),
			$ilDB->quote($a_lang_key, "text")));
	}


	/**
	* Delete lang entry
	*/
	static final function deleteLangEntry($a_module, $a_identifier, $a_lang_key)
	{
		global $ilDB;

		$ilDB->manipulate(sprintf("DELETE FROM lng_data " .
			"WHERE module = %s AND identifier = %s AND lang_key = %s ",
			$ilDB->quote($a_module, "text"),
			$ilDB->quote($a_identifier, "text"),
			$ilDB->quote($a_lang_key, "text")));

		return true;
	}

	
	/**
	 * search ILIAS for users which have selected '$lang_key' as their prefered language and
	 * reset them to default language (english). A message is sent to all affected users
	 *
	 * @param	string		$lang_key	international language key (2 digits)
	 */
	function resetUserLanguage($lang_key)
	{
		global $ilDB;
		
		$query = "UPDATE usr_pref SET " .
				"value = ".$ilDB->quote($this->lang_default, "text")." " .
				"WHERE keyword = ".$ilDB->quote('language', "text")." ".
				"AND value = ".$ilDB->quote($lang_key, "text");
		$ilDB->manipulate($query);
	}

	/**
	 * remove lang-file haeder information from '$content'
	 * This function seeks for a special keyword where the language information starts.
	 * if found it returns the plain language information, otherwise returns false
	 *
	 * @param	array	$content	expecting an ILIAS lang-file
	 * @return	array	$content	content without header info OR false if no valid header was found
	 */
	static function cut_header($content)
	{
		foreach ($content as $key => $val)
		{
			if (trim($val) == "<!-- language file start -->")
			{
				
				return array_slice($content,$key +1);
			}
	 	}

	 	return false;
	}


	/**
	 * Validate the logical structure of a lang file.
	 * This function checks if a lang file exists, the file has a 
	 * header, and each lang-entry consists of exactly three elements
	 * (module, identifier, value).
	 *
	 * @return	string	system message
	 * @param   string  $scope  "global" or "local"
	 */
	function check($scope = 'global')
	{
		include_once("./Services/Utilities/classes/class.ilStr.php");

		switch ($scope)
		{
			case 'local':
				$extension = '.lang.local';
				$path =  $this->cust_lang_path;
				break;

			case 'global':
			default:
				$scope = 'global';
				$extension = '.lang';
				$path = $this->lang_path;
				break;
		}

		$filename = "ilias_" . $this->key . $extension;
		$lang_file = $path . '/' . $filename;

		// dir check
		if (!is_dir($path))
		{
			$this->ilias->raiseError("Directory not found: $path", $this->ilias->error_obj->MESSAGE);
		}

		// file check
		if (!is_file($lang_file))
		{
			$this->ilias->raiseError("File not found: $filename", $this->ilias->error_obj->MESSAGE);
		}

		// header check
		$content = $this->cut_header(file($lang_file));
		if ($content === false)
		{
			$this->ilias->raiseError("Wrong Header in $filename" ,$this->ilias->error_obj->MESSAGE);
		}
		
		// check (counting) elements of each lang-entry
		$line = 0;
		$double_checker = array();
		foreach ($content as $key => $val)
		{
			$separated = explode($this->separator, trim($val));
			$module = $separated[0];
			$identifier = $separated[1];
			$value = $separated[2];
			$num = count($separated);
			++$n;
			if ($num != 3)
			{
				$line = $n + 36;
				$this->ilias->raiseError("Wrong parameter count in $filename in line $line (Value: $val)! Please check your language file!",$this->ilias->error_obj->MESSAGE);
			}
			if (!ilStr::isUtf8($value))
			{
				$this->ilias->raiseError("Non UTF8 character found in $filename in line $line (Value: $val)! Please check your language file!",$this->ilias->error_obj->MESSAGE);
			}

			if ($scope == 'global')
			{
				// check for double entries in global file, be tolerant for local files
				if ($double_checker[$module][$identifier])
				{
					$this->ilias->raiseError("Duplicate Language Entry in $filename in line $line (Value: $val)! Please check your language file!",$this->ilias->error_obj->MESSAGE);
				}
				$double_checker[$module][$identifier] = true;
			}

		}

		// no error occured
		return true;
	}
	
	/**
	* Count number of users that use a language
	*/
	static function countUsers($a_lang)
	{
		global $ilDB, $lng;
		
		$set = $ilDB->query("SELECT COUNT(*) cnt FROM usr_data ud JOIN usr_pref up".
			" ON ud.usr_id = up.usr_id ".
			" WHERE up.value = ".$ilDB->quote($a_lang, "text").
			" AND up.keyword = ".$ilDB->quote("language", "text"));
		$rec = $ilDB->fetchAssoc($set);
		
		// add users with no usr_pref set to default language
		if ($a_lang == $lng->lang_default)
		{
			$set2 = $ilDB->query("SELECT COUNT(*) cnt FROM usr_data ud LEFT JOIN usr_pref up".
				" ON (ud.usr_id = up.usr_id AND up.keyword = ".$ilDB->quote("language", "text").")".
				" WHERE up.value IS NULL ");
			$rec2 = $ilDB->fetchAssoc($set2);
		}
		
		return (int) $rec["cnt"] + (int) $rec2["cnt"];
	}
	
	
} // END class.LanguageObject
?>
