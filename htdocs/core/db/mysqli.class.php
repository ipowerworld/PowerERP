<?php
/* Copyright (C) 2001		Fabien Seisen			<seisen@linuxfr.org>
 * Copyright (C) 2002-2005	Rodolphe Quiedeville	<rodolphe@quiedeville.org>
 * Copyright (C) 2004-2011	Laurent Destailleur		<eldy@users.sourceforge.net>
 * Copyright (C) 2006		Andre Cianfarani		<acianfa@free.fr>
 * Copyright (C) 2005-2012	Regis Houssin			<regis.houssin@inodbox.com>
 * Copyright (C) 2015       Raphaël Doursenaud      <rdoursenaud@gpcsolutions.fr>
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see <https://www.gnu.org/licenses/>.
 */

/**
 *	\file       htdocs/core/db/mysqli.class.php
 *	\brief      Class file to manage PowerERP database access for a MySQL database
 */

require_once DOL_DOCUMENT_ROOT.'/core/db/DoliDB.class.php';

/**
 *	Class to manage PowerERP database access for a MySQL database using the MySQLi extension
 */
class DoliDBMysqli extends DoliDB
{
	/** @var mysqli Database object */
	public $db;
	//! Database type
	public $type = 'mysqli';
	//! Database label
	const LABEL = 'MySQL or MariaDB';
	//! Version min database
	const VERSIONMIN = '5.0.3';
	/** @var bool|mysqli_result Resultset of last query */
	private $_results;

	/**
	 *	Constructor.
	 *	This create an opened connexion to a database server and eventually to a database
	 *
	 *	@param      string	$type		Type of database (mysql, pgsql...)
	 *	@param	    string	$host		Address of database server
	 *	@param	    string	$user		Nom de l'utilisateur autorise
	 *	@param	    string	$pass		Mot de passe
	 *	@param	    string	$name		Nom de la database
	 *	@param	    int		$port		Port of database server
	 */
	public function __construct($type, $host, $user, $pass, $name = '', $port = 0)
	{
		global $conf, $langs;

		// Note that having "static" property for "$forcecharset" and "$forcecollate" will make error here in strict mode, so they are not static
		if (!empty($conf->db->character_set)) {
			$this->forcecharset = $conf->db->character_set;
		}
		if (!empty($conf->db->powererp_main_db_collation)) {
			$this->forcecollate = $conf->db->powererp_main_db_collation;
		}

		$this->database_user = $user;
		$this->database_host = $host;
		$this->database_port = $port;

		$this->transaction_opened = 0;

		//print "Name DB: $host,$user,$pass,$name<br>";

		if (!class_exists('mysqli')) {
			$this->connected = false;
			$this->ok = false;
			$this->error = "Mysqli PHP functions for using Mysqli driver are not available in this version of PHP. Try to use another driver.";
			dol_syslog(get_class($this)."::DoliDBMysqli : Mysqli PHP functions for using Mysqli driver are not available in this version of PHP. Try to use another driver.", LOG_ERR);
		}

		if (!$host) {
			$this->connected = false;
			$this->ok = false;
			$this->error = $langs->trans("ErrorWrongHostParameter");
			dol_syslog(get_class($this)."::DoliDBMysqli : Connect error, wrong host parameters", LOG_ERR);
		}

		// Try server connection
		// We do not try to connect to database, only to server. Connect to database is done later in constrcutor
		$this->db = $this->connect($host, $user, $pass, '', $port);

		if ($this->db && empty($this->db->connect_errno)) {
			$this->connected = true;
			$this->ok = true;
		} else {
			$this->connected = false;
			$this->ok = false;
			$this->error = empty($this->db) ? 'Failed to connect' : $this->db->connect_error;
			dol_syslog(get_class($this)."::DoliDBMysqli Connect error: ".$this->error, LOG_ERR);
		}

		// If server connection is ok, we try to connect to the database
		if ($this->connected && $name) {
			if ($this->select_db($name)) {
				$this->database_selected = true;
				$this->database_name = $name;
				$this->ok = true;

				// If client is old latin, we force utf8
				$clientmustbe = empty($conf->db->powererp_main_db_character_set) ? 'utf8' : $conf->db->powererp_main_db_character_set;
				if (preg_match('/latin1/', $clientmustbe)) {
					$clientmustbe = 'utf8';
				}

				if ($this->db->character_set_name() != $clientmustbe) {
					$this->db->set_charset($clientmustbe); // This set charset, but with a bad collation

					$collation = $conf->db->powererp_main_db_collation;
					if (preg_match('/latin1/', $collation)) {
						$collation = 'utf8_unicode_ci';
					}

					if (!preg_match('/general/', $collation)) {
						$this->db->query("SET collation_connection = ".$collation);
					}
				}
			} else {
				$this->database_selected = false;
				$this->database_name = '';
				$this->ok = false;
				$this->error = $this->error();
				dol_syslog(get_class($this)."::DoliDBMysqli : Select_db error ".$this->error, LOG_ERR);
			}
		} else {
			// Pas de selection de base demandee, ok ou ko
			$this->database_selected = false;

			if ($this->connected) {
				// If client is old latin, we force utf8
				$clientmustbe = empty($conf->db->powererp_main_db_character_set) ? 'utf8' : $conf->db->powererp_main_db_character_set;
				if (preg_match('/latin1/', $clientmustbe)) {
					$clientmustbe = 'utf8';
				}
				if (preg_match('/utf8mb4/', $clientmustbe)) {
					$clientmustbe = 'utf8';
				}

				if ($this->db->character_set_name() != $clientmustbe) {
					$this->db->set_charset($clientmustbe); // This set utf8_unicode_ci

					$collation = $conf->db->powererp_main_db_collation;
					if (preg_match('/latin1/', $collation)) {
						$collation = 'utf8_unicode_ci';
					}
					if (preg_match('/utf8mb4/', $collation)) {
						$collation = 'utf8_unicode_ci';
					}

					if (!preg_match('/general/', $collation)) {
						$this->db->query("SET collation_connection = ".$collation);
					}
				}
			}
		}
	}


	/**
	 * Return SQL string to force an index
	 *
	 * @param	string	$nameofindex	Name of index
	 * @return	string					SQL string
	 */
	public function hintindex($nameofindex)
	{
		return " FORCE INDEX(".preg_replace('/[^a-z0-9_]/', '', $nameofindex).")";
	}


	/**
	 *  Convert a SQL request in Mysql syntax to native syntax
	 *
	 *  @param     string	$line   SQL request line to convert
	 *  @param     string	$type	Type of SQL order ('ddl' for insert, update, select, delete or 'dml' for create, alter...)
	 *  @return    string   		SQL request line converted
	 */
	public static function convertSQLFromMysql($line, $type = 'ddl')
	{
		return $line;
	}


	// phpcs:disable PEAR.NamingConventions.ValidFunctionName.ScopeNotCamelCaps
	/**
	 *  Select a database
	 *
	 *  @param	    string	$database	Name of database
	 *  @return	    boolean  		    true if OK, false if KO
	 */
	public function select_db($database)
	{
		// phpcs:enable
		dol_syslog(get_class($this)."::select_db database=".$database, LOG_DEBUG);
		$result = false;
		try {
			$result = $this->db->select_db($database);
		} catch (Exception $e) {
			// Nothing done on error
		}
		return $result;
	}


	/**
	 * Connect to server
	 *
	 * @param   string  $host 	Database server host
	 * @param   string  $login 	Login
	 * @param   string  $passwd Password
	 * @param   string  $name 	Name of database (not used for mysql, used for pgsql)
	 * @param   integer $port 	Port of database server
	 * @return  mysqli|null		Database access object
	 * @see close()
	 */
	public function connect($host, $login, $passwd, $name, $port = 0)
	{
		dol_syslog(get_class($this)."::connect host=$host, port=$port, login=$login, passwd=--hidden--, name=$name", LOG_DEBUG);

		//mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

		// Can also be
		// mysqli::init(); mysql::options(MYSQLI_INIT_COMMAND, 'SET AUTOCOMMIT = 0'); mysqli::options(MYSQLI_OPT_CONNECT_TIMEOUT, 5);
		// return mysqli::real_connect($host, $user, $pass, $db, $port);
		$tmp = false;
		try {
			$tmp = new mysqli($host, $login, $passwd, $name, $port);
		} catch (Exception $e) {
			dol_syslog(get_class($this)."::connect failed", LOG_DEBUG);
		}
		return $tmp;
	}

	/**
	 *	Return version of database server
	 *
	 *	@return	        string      Version string
	 */
	public function getVersion()
	{
		return $this->db->server_info;
	}

	/**
	 *	Return version of database client driver
	 *
	 *	@return	        string      Version string
	 */
	public function getDriverInfo()
	{
		return $this->db->client_info;
	}


	/**
	 *  Close database connexion
	 *
	 *  @return     bool     True if disconnect successfull, false otherwise
	 *  @see        connect()
	 */
	public function close()
	{
		if ($this->db) {
			if ($this->transaction_opened > 0) {
				dol_syslog(get_class($this)."::close Closing a connection with an opened transaction depth=".$this->transaction_opened, LOG_ERR);
			}
			$this->connected = false;
			return $this->db->close();
		}
		return false;
	}



	/**
	 * 	Execute a SQL request and return the resultset
	 *
	 * 	@param	string	$query			SQL query string
	 * 	@param	int		$usesavepoint	0=Default mode, 1=Run a savepoint before and a rollback to savepoint if error (this allow to have some request with errors inside global transactions).
	 * 									Note that with Mysql, this parameter is not used as Myssql can already commit a transaction even if one request is in error, without using savepoints.
	 *  @param  string	$type           Type of SQL order ('ddl' for insert, update, select, delete or 'dml' for create, alter...)
	 * 	@param	int		$result_mode	Result mode (Using 1=MYSQLI_USE_RESULT instead of 0=MYSQLI_STORE_RESULT will not buffer the result and save memory)
	 *	@return	bool|mysqli_result		Resultset of answer
	 */
	public function query($query, $usesavepoint = 0, $type = 'auto', $result_mode = 0)
	{
		global $conf, $powererp_main_db_readonly;

		$query = trim($query);

		if (!in_array($query, array('BEGIN', 'COMMIT', 'ROLLBACK'))) {
			$SYSLOG_SQL_LIMIT = 10000; // limit log to 10kb per line to limit DOS attacks
			dol_syslog('sql='.substr($query, 0, $SYSLOG_SQL_LIMIT), LOG_DEBUG);
		}
		if (empty($query)) {
			return false; // Return false = error if empty request
		}

		if (!empty($powererp_main_db_readonly)) {
			if (preg_match('/^(INSERT|UPDATE|REPLACE|DELETE|CREATE|ALTER|TRUNCATE|DROP)/i', $query)) {
				$this->lasterror = 'Application in read-only mode';
				$this->lasterrno = 'APPREADONLY';
				$this->lastquery = $query;
				return false;
			}
		}

		try {
			if (!$this->database_name) {
				// Ordre SQL ne necessitant pas de connexion a une base (exemple: CREATE DATABASE)
				$ret = $this->db->query($query, $result_mode);
			} else {
				$ret = $this->db->query($query, $result_mode);
			}
		} catch (Exception $e) {
			dol_syslog(get_class($this)."::query Exception in query instead of returning an error: ".$e->getMessage(), LOG_ERR);
			$ret = false;
		}

		if (!preg_match("/^COMMIT/i", $query) && !preg_match("/^ROLLBACK/i", $query)) {
			// Si requete utilisateur, on la sauvegarde ainsi que son resultset
			if (!$ret) {
				$this->lastqueryerror = $query;
				$this->lasterror = $this->error();
				$this->lasterrno = $this->errno();

				if ($conf->global->SYSLOG_LEVEL < LOG_DEBUG) {
					dol_syslog(get_class($this)."::query SQL Error query: ".$query, LOG_ERR); // Log of request was not yet done previously
				}
				dol_syslog(get_class($this)."::query SQL Error message: ".$this->lasterrno." ".$this->lasterror, LOG_ERR);
				//var_dump(debug_print_backtrace());
			}
			$this->lastquery = $query;
			$this->_results = $ret;
		}

		return $ret;
	}

	// phpcs:disable PEAR.NamingConventions.ValidFunctionName.ScopeNotCamelCaps
	/**
	 * 	Returns the current line (as an object) for the resultset cursor
	 *
	 *	@param	mysqli_result	$resultset	Curseur de la requete voulue
	 *	@return	object|null					Object result line or null if KO or end of cursor
	 */
	public function fetch_object($resultset)
	{
		// phpcs:enable
		// Si le resultset n'est pas fourni, on prend le dernier utilise sur cette connexion
		if (!is_object($resultset)) {
			$resultset = $this->_results;
		}
		return $resultset->fetch_object();
	}


	// phpcs:disable PEAR.NamingConventions.ValidFunctionName.ScopeNotCamelCaps
	/**
	 *	Return datas as an array
	 *
	 *	@param	mysqli_result	$resultset	Resultset of request
	 *	@return	array|null					Array or null if KO or end of cursor
	 */
	public function fetch_array($resultset)
	{
		// phpcs:enable
		// If resultset not provided, we take the last used by connexion
		if (!is_object($resultset)) {
			$resultset = $this->_results;
		}
		return $resultset->fetch_array();
	}

	// phpcs:disable PEAR.NamingConventions.ValidFunctionName.ScopeNotCamelCaps
	/**
	 *	Return datas as an array
	 *
	 *	@param	mysqli_result	$resultset	Resultset of request
	 *	@return	array|null|int				Array or null if KO or end of cursor or 0 if resultset is bool
	 */
	public function fetch_row($resultset)
	{
		// phpcs:enable
		// If resultset not provided, we take the last used by connexion
		if (!is_bool($resultset)) {
			if (!is_object($resultset)) {
				$resultset = $this->_results;
			}
			return $resultset->fetch_row();
		} else {
			// si le curseur est un booleen on retourne la valeur 0
			return 0;
		}
	}

	// phpcs:disable PEAR.NamingConventions.ValidFunctionName.ScopeNotCamelCaps
	/**
	 *	Return number of lines for result of a SELECT
	 *
	 *	@param	mysqli_result	$resultset  Resulset of requests
	 *	@return	int				Nb of lines
	 *	@see    affected_rows()
	 */
	public function num_rows($resultset)
	{
		// phpcs:enable
		// If resultset not provided, we take the last used by connexion
		if (!is_object($resultset)) {
			$resultset = $this->_results;
		}
		return isset($resultset->num_rows) ? $resultset->num_rows : 0;
	}

	// phpcs:disable PEAR.NamingConventions.ValidFunctionName.ScopeNotCamelCaps
	/**
	 *	Return the number of lines in the result of a request INSERT, DELETE or UPDATE
	 *
	 *	@param	mysqli_result	$resultset	Curseur de la requete voulue
	 *	@return int							Number of lines
	 *	@see    num_rows()
	 */
	public function affected_rows($resultset)
	{
		// phpcs:enable
		// If resultset not provided, we take the last used by connexion
		if (!is_object($resultset)) {
			$resultset = $this->_results;
		}
		// mysql necessite un link de base pour cette fonction contrairement
		// a pqsql qui prend un resultset
		return $this->db->affected_rows;
	}


	/**
	 *	Libere le dernier resultset utilise sur cette connexion
	 *
	 *	@param  mysqli_result	$resultset	Curseur de la requete voulue
	 *	@return	void
	 */
	public function free($resultset = null)
	{
		// If resultset not provided, we take the last used by connexion
		if (!is_object($resultset)) {
			$resultset = $this->_results;
		}
		// Si resultset en est un, on libere la memoire
		if (is_object($resultset)) {
			$resultset->free_result();
		}
	}

	/**
	 *	Escape a string to insert data
	 *
	 *	@param	string	$stringtoencode		String to escape
	 *	@return	string						String escaped
	 */
	public function escape($stringtoencode)
	{
		return $this->db->real_escape_string((string) $stringtoencode);
	}

	/**
	 *	Escape a string to insert data
	 *
	 *	@param	string	$stringtoencode		String to escape
	 *	@return	string						String escaped
	 *  @deprecated
	 */
	public function escapeunderscore($stringtoencode)
	{
		return str_replace('_', '\_', (string) $stringtoencode);
	}

	/**
	 *	Escape a string to insert data into a like
	 *
	 *	@param	string	$stringtoencode		String to escape
	 *	@return	string						String escaped
	 */
	public function escapeforlike($stringtoencode)
	{
		return str_replace(array('\\', '_', '%'), array('\\\\', '\_', '\%'), (string) $stringtoencode);
	}

	/**
	 *	Return generic error code of last operation.
	 *
	 *	@return	string		Error code (Exemples: DB_ERROR_TABLE_ALREADY_EXISTS, DB_ERROR_RECORD_ALREADY_EXISTS...)
	 */
	public function errno()
	{
		if (!$this->connected) {
			// Si il y a eu echec de connexion, $this->db n'est pas valide.
			return 'DB_ERROR_FAILED_TO_CONNECT';
		} else {
			// Constants to convert a MySql error code to a generic PowerERP error code
			$errorcode_map = array(
			1004 => 'DB_ERROR_CANNOT_CREATE',
			1005 => 'DB_ERROR_CANNOT_CREATE',
			1006 => 'DB_ERROR_CANNOT_CREATE',
			1007 => 'DB_ERROR_ALREADY_EXISTS',
			1008 => 'DB_ERROR_CANNOT_DROP',
			1022 => 'DB_ERROR_KEY_NAME_ALREADY_EXISTS',
			1025 => 'DB_ERROR_NO_FOREIGN_KEY_TO_DROP',
			1044 => 'DB_ERROR_ACCESSDENIED',
			1046 => 'DB_ERROR_NODBSELECTED',
			1048 => 'DB_ERROR_CONSTRAINT',
			1050 => 'DB_ERROR_TABLE_ALREADY_EXISTS',
			1051 => 'DB_ERROR_NOSUCHTABLE',
			1054 => 'DB_ERROR_NOSUCHFIELD',
			1060 => 'DB_ERROR_COLUMN_ALREADY_EXISTS',
			1061 => 'DB_ERROR_KEY_NAME_ALREADY_EXISTS',
			1062 => 'DB_ERROR_RECORD_ALREADY_EXISTS',
			1064 => 'DB_ERROR_SYNTAX',
			1068 => 'DB_ERROR_PRIMARY_KEY_ALREADY_EXISTS',
			1075 => 'DB_ERROR_CANT_DROP_PRIMARY_KEY',
			1091 => 'DB_ERROR_NOSUCHFIELD',
			1100 => 'DB_ERROR_NOT_LOCKED',
			1136 => 'DB_ERROR_VALUE_COUNT_ON_ROW',
			1146 => 'DB_ERROR_NOSUCHTABLE',
			1215 => 'DB_ERROR_CANNOT_ADD_FOREIGN_KEY_CONSTRAINT',
			1216 => 'DB_ERROR_NO_PARENT',
			1217 => 'DB_ERROR_CHILD_EXISTS',
			1396 => 'DB_ERROR_USER_ALREADY_EXISTS', // When creating a user that already existing
			1451 => 'DB_ERROR_CHILD_EXISTS',
			1826 => 'DB_ERROR_KEY_NAME_ALREADY_EXISTS'
			);

			if (isset($errorcode_map[$this->db->errno])) {
				return $errorcode_map[$this->db->errno];
			}
			$errno = $this->db->errno;
			return ($errno ? 'DB_ERROR_'.$errno : '0');
		}
	}

	/**
	 *	Return description of last error
	 *
	 *	@return	string		Error text
	 */
	public function error()
	{
		if (!$this->connected) {
			// Si il y a eu echec de connexion, $this->db n'est pas valide pour mysqli_error.
			return 'Not connected. Check setup parameters in conf/conf.php file and your mysql client and server versions';
		} else {
			return $this->db->error;
		}
	}

	// phpcs:disable PEAR.NamingConventions.ValidFunctionName.ScopeNotCamelCaps
	/**
	 * Get last ID after an insert INSERT
	 *
	 * @param   string	$tab    	Table name concerned by insert. Ne sert pas sous MySql mais requis pour compatibilite avec Postgresql
	 * @param	string	$fieldid	Field name
	 * @return  int|string			Id of row
	 */
	public function last_insert_id($tab, $fieldid = 'rowid')
	{
		// phpcs:enable
		return $this->db->insert_id;
	}

	/**
	 * Encrypt sensitive data in database
	 * Warning: This function includes the escape and add the SQL simple quotes on strings.
	 *
	 * @param	string	$fieldorvalue	Field name or value to encrypt
	 * @param	int		$withQuotes		Return string including the SQL simple quotes. This param must always be 1 (Value 0 is bugged and deprecated).
	 * @return	string					XXX(field) or XXX('value') or field or 'value'
	 */
	public function encrypt($fieldorvalue, $withQuotes = 1)
	{
		global $conf;

		// Type of encryption (2: AES (recommended), 1: DES , 0: no encryption)
		$cryptType = (!empty($conf->db->powererp_main_db_encryption) ? $conf->db->powererp_main_db_encryption : 0);

		//Encryption key
		$cryptKey = (!empty($conf->db->powererp_main_db_cryptkey) ? $conf->db->powererp_main_db_cryptkey : '');

		$escapedstringwithquotes = ($withQuotes ? "'" : "").$this->escape($fieldorvalue).($withQuotes ? "'" : "");

		if ($cryptType && !empty($cryptKey)) {
			if ($cryptType == 2) {
				$escapedstringwithquotes = "AES_ENCRYPT(".$escapedstringwithquotes.", '".$this->escape($cryptKey)."')";
			} elseif ($cryptType == 1) {
				$escapedstringwithquotes = "DES_ENCRYPT(".$escapedstringwithquotes.", '".$this->escape($cryptKey)."')";
			}
		}

		return $escapedstringwithquotes;
	}

	/**
	 *	Decrypt sensitive data in database
	 *
	 *	@param	string	$value			Value to decrypt
	 * 	@return	string					Decrypted value if used
	 */
	public function decrypt($value)
	{
		global $conf;

		// Type of encryption (2: AES (recommended), 1: DES , 0: no encryption)
		$cryptType = (!empty($conf->db->powererp_main_db_encryption) ? $conf->db->powererp_main_db_encryption : 0);

		//Encryption key
		$cryptKey = (!empty($conf->db->powererp_main_db_cryptkey) ? $conf->db->powererp_main_db_cryptkey : '');

		$return = $value;

		if ($cryptType && !empty($cryptKey)) {
			if ($cryptType == 2) {
				$return = 'AES_DECRYPT('.$value.',\''.$cryptKey.'\')';
			} elseif ($cryptType == 1) {
				$return = 'DES_DECRYPT('.$value.',\''.$cryptKey.'\')';
			}
		}

		return $return;
	}


	// phpcs:disable PEAR.NamingConventions.ValidFunctionName.ScopeNotCamelCaps
	/**
	 * Return connexion ID
	 *
	 * @return	        string      Id connexion
	 */
	public function DDLGetConnectId()
	{
		// phpcs:enable
		$resql = $this->query('SELECT CONNECTION_ID()');
		if ($resql) {
			$row = $this->fetch_row($resql);
			return $row[0];
		} else {
			return '?';
		}
	}

	// phpcs:disable PEAR.NamingConventions.ValidFunctionName.ScopeNotCamelCaps
	/**
	 *  Create a new database
	 *	Do not use function xxx_create_db (xxx=mysql, ...) as they are deprecated
	 *	We force to create database with charset this->forcecharset and collate this->forcecollate
	 *
	 *	@param	string	$database		Database name to create
	 * 	@param	string	$charset		Charset used to store data
	 * 	@param	string	$collation		Charset used to sort data
	 * 	@param	string	$owner			Username of database owner
	 * 	@return	bool|mysqli_result		resource defined if OK, null if KO
	 */
	public function DDLCreateDb($database, $charset = '', $collation = '', $owner = '')
	{
		// phpcs:enable
		if (empty($charset)) {
			$charset = $this->forcecharset;
		}
		if (empty($collation)) {
			$collation = $this->forcecollate;
		}

		// ALTER DATABASE powererp_db DEFAULT CHARACTER SET latin DEFAULT COLLATE latin1_swedish_ci
		$sql = "CREATE DATABASE `".$this->escape($database)."`";
		$sql .= " DEFAULT CHARACTER SET `".$this->escape($charset)."` DEFAULT COLLATE `".$this->escape($collation)."`";

		dol_syslog($sql, LOG_DEBUG);
		$ret = $this->query($sql);
		if (!$ret) {
			// We try again for compatibility with Mysql < 4.1.1
			$sql = "CREATE DATABASE `".$this->escape($database)."`";
			dol_syslog($sql, LOG_DEBUG);
			$ret = $this->query($sql);
		}
		return $ret;
	}

	// phpcs:disable PEAR.NamingConventions.ValidFunctionName.ScopeNotCamelCaps
	/**
	 *  List tables into a database
	 *
	 *  @param	string		$database	Name of database
	 *  @param	string		$table		Nmae of table filter ('xxx%')
	 *  @return	array					List of tables in an array
	 */
	public function DDLListTables($database, $table = '')
	{
		// phpcs:enable
		$listtables = array();

		$like = '';
		if ($table) {
			$tmptable = preg_replace('/[^a-z0-9\.\-\_%]/i', '', $table);

			$like = "LIKE '".$this->escape($tmptable)."'";
		}
		$tmpdatabase = preg_replace('/[^a-z0-9\.\-\_]/i', '', $database);

		$sql = "SHOW TABLES FROM `".$tmpdatabase."` ".$like.";";
		//print $sql;
		$result = $this->query($sql);
		if ($result) {
			while ($row = $this->fetch_row($result)) {
				$listtables[] = $row[0];
			}
		}
		return $listtables;
	}

	// phpcs:disable PEAR.NamingConventions.ValidFunctionName.ScopeNotCamelCaps
	/**
	 *	List information of columns into a table.
	 *
	 *	@param	string	$table		Name of table
	 *	@return	array				Tableau des informations des champs de la table
	 */
	public function DDLInfoTable($table)
	{
		// phpcs:enable
		$infotables = array();

		$tmptable = preg_replace('/[^a-z0-9\.\-\_]/i', '', $table);

		$sql = "SHOW FULL COLUMNS FROM ".$tmptable.";";

		dol_syslog($sql, LOG_DEBUG);
		$result = $this->query($sql);
		if ($result) {
			while ($row = $this->fetch_row($result)) {
				$infotables[] = $row;
			}
		}
		return $infotables;
	}

	// phpcs:disable PEAR.NamingConventions.ValidFunctionName.ScopeNotCamelCaps
	/**
	 *	Create a table into database
	 *
	 *	@param	    string	$table 			Name of table
	 *	@param	    array	$fields 		Tableau associatif [nom champ][tableau des descriptions]
	 *	@param	    string	$primary_key 	Nom du champ qui sera la clef primaire
	 *	@param	    string	$type 			Type de la table
	 *	@param	    array	$unique_keys 	Tableau associatifs Nom de champs qui seront clef unique => valeur
	 *	@param	    array	$fulltext_keys	Tableau des Nom de champs qui seront indexes en fulltext
	 *	@param	    array	$keys 			Tableau des champs cles noms => valeur
	 *	@return	    int						<0 if KO, >=0 if OK
	 */
	public function DDLCreateTable($table, $fields, $primary_key, $type, $unique_keys = null, $fulltext_keys = null, $keys = null)
	{
		// phpcs:enable
		// FIXME: $fulltext_keys parameter is unused

		$pk = '';
		$sqluq = $sqlk = array();

		// cles recherchees dans le tableau des descriptions (fields) : type,value,attribute,null,default,extra
		// ex. : $fields['rowid'] = array('type'=>'int','value'=>'11','null'=>'not null','extra'=> 'auto_increment');
		$sql = "CREATE TABLE ".$table."(";
		$i = 0;
		$sqlfields = array();
		foreach ($fields as $field_name => $field_desc) {
			$sqlfields[$i] = $field_name." ";
			$sqlfields[$i] .= $field_desc['type'];
			if (preg_match("/^[^\s]/i", $field_desc['value'])) {
				$sqlfields[$i]  .= "(".$field_desc['value'].")";
			}
			if (preg_match("/^[^\s]/i", $field_desc['attribute'])) {
				$sqlfields[$i]  .= " ".$field_desc['attribute'];
			}
			if (preg_match("/^[^\s]/i", $field_desc['default'])) {
				if ((preg_match("/null/i", $field_desc['default'])) || (preg_match("/CURRENT_TIMESTAMP/i", $field_desc['default']))) {
					$sqlfields[$i]  .= " default ".$field_desc['default'];
				} else {
					$sqlfields[$i]  .= " default '".$this->escape($field_desc['default'])."'";
				}
			}
			if (preg_match("/^[^\s]/i", $field_desc['null'])) {
				$sqlfields[$i]  .= " ".$field_desc['null'];
			}
			if (preg_match("/^[^\s]/i", $field_desc['extra'])) {
				$sqlfields[$i]  .= " ".$field_desc['extra'];
			}
			$i++;
		}
		if ($primary_key != "") {
			$pk = "primary key(".$primary_key.")";
		}

		if (is_array($unique_keys)) {
			$i = 0;
			foreach ($unique_keys as $key => $value) {
				$sqluq[$i] = "UNIQUE KEY '".$key."' ('".$this->escape($value)."')";
				$i++;
			}
		}
		if (is_array($keys)) {
			$i = 0;
			foreach ($keys as $key => $value) {
				$sqlk[$i] = "KEY ".$key." (".$value.")";
				$i++;
			}
		}
		$sql .= implode(',', $sqlfields);
		if ($primary_key != "") {
			$sql .= ",".$pk;
		}
		if ($unique_keys != "") {
			$sql .= ",".implode(',', $sqluq);
		}
		if (is_array($keys)) {
			$sql .= ",".implode(',', $sqlk);
		}
		$sql .= ") engine=".$type;

		if (!$this->query($sql)) {
			return -1;
		} else {
			return 1;
		}
	}

	// phpcs:disable PEAR.NamingConventions.ValidFunctionName.ScopeNotCamelCaps
	/**
	 *  Drop a table into database
	 *
	 *	@param	    string	$table 			Name of table
	 *	@return	    int						<0 if KO, >=0 if OK
	 */
	public function DDLDropTable($table)
	{
		// phpcs:enable
		$tmptable = preg_replace('/[^a-z0-9\.\-\_]/i', '', $table);

		$sql = "DROP TABLE ".$tmptable;

		if (!$this->query($sql)) {
			return -1;
		} else {
			return 1;
		}
	}

	// phpcs:disable PEAR.NamingConventions.ValidFunctionName.ScopeNotCamelCaps
	/**
	 *	Return a pointer of line with description of a table or field
	 *
	 *	@param	string		$table	Name of table
	 *	@param	string		$field	Optionnel : Name of field if we want description of field
	 *	@return	bool|mysqli_result	Resultset x (x->Field, x->Type, ...)
	 */
	public function DDLDescTable($table, $field = "")
	{
		// phpcs:enable
		$sql = "DESC ".$table." ".$field;

		dol_syslog(get_class($this)."::DDLDescTable ".$sql, LOG_DEBUG);
		$this->_results = $this->query($sql);
		return $this->_results;
	}

	// phpcs:disable PEAR.NamingConventions.ValidFunctionName.ScopeNotCamelCaps
	/**
	 *	Create a new field into table
	 *
	 *	@param	string	$table 				Name of table
	 *	@param	string	$field_name 		Name of field to add
	 *	@param	string	$field_desc 		Tableau associatif de description du champ a inserer[nom du parametre][valeur du parametre]
	 *	@param	string	$field_position 	Optionnel ex.: "after champtruc"
	 *	@return	int							<0 if KO, >0 if OK
	 */
	public function DDLAddField($table, $field_name, $field_desc, $field_position = "")
	{
		// phpcs:enable
		// cles recherchees dans le tableau des descriptions (field_desc) : type,value,attribute,null,default,extra
		// ex. : $field_desc = array('type'=>'int','value'=>'11','null'=>'not null','extra'=> 'auto_increment');
		$sql = "ALTER TABLE ".$table." ADD ".$field_name." ";
		$sql .= $field_desc['type'];
		if (preg_match("/^[^\s]/i", $field_desc['value'])) {
			if (!in_array($field_desc['type'], array('date', 'datetime')) && $field_desc['value']) {
				$sql .= "(".$field_desc['value'].")";
			}
		}
		if (isset($field_desc['attribute']) && preg_match("/^[^\s]/i", $field_desc['attribute'])) {
			$sql .= " ".$field_desc['attribute'];
		}
		if (isset($field_desc['null']) && preg_match("/^[^\s]/i", $field_desc['null'])) {
			$sql .= " ".$field_desc['null'];
		}
		if (isset($field_desc['default']) && preg_match("/^[^\s]/i", $field_desc['default'])) {
			if (preg_match("/null/i", $field_desc['default'])) {
				$sql .= " default ".$field_desc['default'];
			} else {
				$sql .= " default '".$this->escape($field_desc['default'])."'";
			}
		}
		if (isset($field_desc['extra']) && preg_match("/^[^\s]/i", $field_desc['extra'])) {
			$sql .= " ".$field_desc['extra'];
		}
		$sql .= " ".$field_position;

		dol_syslog(get_class($this)."::DDLAddField ".$sql, LOG_DEBUG);
		if ($this->query($sql)) {
			return 1;
		}
		return -1;
	}

	// phpcs:disable PEAR.NamingConventions.ValidFunctionName.ScopeNotCamelCaps
	/**
	 *	Update format of a field into a table
	 *
	 *	@param	string	$table 				Name of table
	 *	@param	string	$field_name 		Name of field to modify
	 *	@param	string	$field_desc 		Array with description of field format
	 *	@return	int							<0 if KO, >0 if OK
	 */
	public function DDLUpdateField($table, $field_name, $field_desc)
	{
		// phpcs:enable
		$sql = "ALTER TABLE ".$table;
		$sql .= " MODIFY COLUMN ".$field_name." ".$field_desc['type'];
		if (in_array($field_desc['type'], array('double', 'tinyint', 'int', 'varchar')) && $field_desc['value']) {
			$sql .= "(".$field_desc['value'].")";
		}
		if ($field_desc['null'] == 'not null' || $field_desc['null'] == 'NOT NULL') {
			// We will try to change format of column to NOT NULL. To be sure the ALTER works, we try to update fields that are NULL
			if ($field_desc['type'] == 'varchar' || $field_desc['type'] == 'text') {
				$sqlbis = "UPDATE ".$table." SET ".$field_name." = '".$this->escape($field_desc['default'] ? $field_desc['default'] : '')."' WHERE ".$field_name." IS NULL";
				$this->query($sqlbis);
			} elseif ($field_desc['type'] == 'tinyint' || $field_desc['type'] == 'int') {
				$sqlbis = "UPDATE ".$table." SET ".$field_name." = ".((int) $this->escape($field_desc['default'] ? $field_desc['default'] : 0))." WHERE ".$field_name." IS NULL";
				$this->query($sqlbis);
			}

			$sql .= " NOT NULL";
		}

		if ($field_desc['default'] != '') {
			if ($field_desc['type'] == 'double' || $field_desc['type'] == 'tinyint' || $field_desc['type'] == 'int') {
				$sql .= " DEFAULT ".$this->escape($field_desc['default']);
			} elseif ($field_desc['type'] != 'text') {
				$sql .= " DEFAULT '".$this->escape($field_desc['default'])."'"; // Default not supported on text fields
			}
		}

		dol_syslog(get_class($this)."::DDLUpdateField ".$sql, LOG_DEBUG);
		if (!$this->query($sql)) {
			return -1;
		} else {
			return 1;
		}
	}

	// phpcs:disable PEAR.NamingConventions.ValidFunctionName.ScopeNotCamelCaps
	/**
	 *	Drop a field from table
	 *
	 *	@param	string	$table 			Name of table
	 *	@param	string	$field_name 	Name of field to drop
	 *	@return	int						<0 if KO, >0 if OK
	 */
	public function DDLDropField($table, $field_name)
	{
		// phpcs:enable
		$tmp_field_name = preg_replace('/[^a-z0-9\.\-\_]/i', '', $field_name);

		$sql = "ALTER TABLE ".$table." DROP COLUMN `".$tmp_field_name."`";
		if ($this->query($sql)) {
			return 1;
		}
		$this->error = $this->lasterror();
		return -1;
	}


	// phpcs:disable PEAR.NamingConventions.ValidFunctionName.ScopeNotCamelCaps
	/**
	 * 	Create a user and privileges to connect to database (even if database does not exists yet)
	 *
	 *	@param	string	$powererp_main_db_host 		Ip server or '%'
	 *	@param	string	$powererp_main_db_user 		Nom user a creer
	 *	@param	string	$powererp_main_db_pass 		Mot de passe user a creer
	 *	@param	string	$powererp_main_db_name		Database name where user must be granted
	 *	@return	int									<0 if KO, >=0 if OK
	 */
	public function DDLCreateUser($powererp_main_db_host, $powererp_main_db_user, $powererp_main_db_pass, $powererp_main_db_name)
	{
		// phpcs:enable
		$sql = "CREATE USER '".$this->escape($powererp_main_db_user)."' IDENTIFIED BY '".$this->escape($powererp_main_db_pass)."'";
		dol_syslog(get_class($this)."::DDLCreateUser", LOG_DEBUG); // No sql to avoid password in log
		$resql = $this->query($sql);
		if (!$resql) {
			if ($this->lasterrno != 'DB_ERROR_USER_ALREADY_EXISTS') {
				return -1;
			} else {
				// If user already exists, we continue to set permissions
				dol_syslog(get_class($this)."::DDLCreateUser sql=".$sql, LOG_WARNING);
			}
		}

		// Redo with localhost forced (sometimes user is created on %)
		$sql = "CREATE USER '".$this->escape($powererp_main_db_user)."'@'localhost' IDENTIFIED BY '".$this->escape($powererp_main_db_pass)."'";
		$resql = $this->query($sql);

		$sql = "GRANT ALL PRIVILEGES ON ".$this->escape($powererp_main_db_name).".* TO '".$this->escape($powererp_main_db_user)."'@'".$this->escape($powererp_main_db_host)."'";
		dol_syslog(get_class($this)."::DDLCreateUser", LOG_DEBUG); // No sql to avoid password in log
		$resql = $this->query($sql);
		if (!$resql) {
			$this->error = "Connected user not allowed to GRANT ALL PRIVILEGES ON ".$this->escape($powererp_main_db_name).".* TO '".$this->escape($powererp_main_db_user)."'@'".$this->escape($powererp_main_db_host)."'";
			return -1;
		}

		$sql = "FLUSH Privileges";

		dol_syslog(get_class($this)."::DDLCreateUser", LOG_DEBUG);
		$resql = $this->query($sql);
		if (!$resql) {
			return -1;
		}

		return 1;
	}

	/**
	 *	Return charset used to store data in current database
	 *  Note: if we are connected to databasename, it is same result than using SELECT default_character_set_name FROM information_schema.SCHEMATA WHERE schema_name = "databasename";)
	 *
	 *	@return		string		Charset
	 *  @see getDefaultCollationDatabase()
	 */
	public function getDefaultCharacterSetDatabase()
	{
		$resql = $this->query('SHOW VARIABLES LIKE \'character_set_database\'');
		if (!$resql) {
			// version Mysql < 4.1.1
			return $this->forcecharset;
		}
		$liste = $this->fetch_array($resql);
		$tmpval = $liste['Value'];

		return $tmpval;
	}

	/**
	 *	Return list of available charset that can be used to store data in database
	 *
	 *	@return		array|null		List of Charset
	 */
	public function getListOfCharacterSet()
	{
		$resql = $this->query('SHOW CHARSET');
		$liste = array();
		if ($resql) {
			$i = 0;
			while ($obj = $this->fetch_object($resql)) {
				$liste[$i]['charset'] = $obj->Charset;
				$liste[$i]['description'] = $obj->Description;
				$i++;
			}
			$this->free($resql);
		} else {
			// version Mysql < 4.1.1
			return null;
		}
		return $liste;
	}

	/**
	 *	Return collation used in current database
	 *
	 *	@return		string		Collation value
	 *  @see getDefaultCharacterSetDatabase()
	 */
	public function getDefaultCollationDatabase()
	{
		$resql = $this->query('SHOW VARIABLES LIKE \'collation_database\'');
		if (!$resql) {
			// version Mysql < 4.1.1
			return $this->forcecollate;
		}
		$liste = $this->fetch_array($resql);
		$tmpval = $liste['Value'];

		return $tmpval;
	}

	/**
	 *	Return list of available collation that can be used for database
	 *
	 *	@return		array|null		Liste of Collation
	 */
	public function getListOfCollation()
	{
		$resql = $this->query('SHOW COLLATION');
		$liste = array();
		if ($resql) {
			$i = 0;
			while ($obj = $this->fetch_object($resql)) {
				$liste[$i]['collation'] = $obj->Collation;
				$i++;
			}
			$this->free($resql);
		} else {
			// version Mysql < 4.1.1
			return null;
		}
		return $liste;
	}

	/**
	 *	Return full path of dump program
	 *
	 *	@return		string		Full path of dump program
	 */
	public function getPathOfDump()
	{
		$fullpathofdump = '/pathtomysqldump/mysqldump';

		$resql = $this->query('SHOW VARIABLES LIKE \'basedir\'');
		if ($resql) {
			$liste = $this->fetch_array($resql);
			$basedir = $liste['Value'];
			$fullpathofdump = $basedir.(preg_match('/\/$/', $basedir) ? '' : '/').'bin/mysqldump';
		}
		return $fullpathofdump;
	}

	/**
	 *	Return full path of restore program
	 *
	 *	@return		string		Full path of restore program
	 */
	public function getPathOfRestore()
	{
		$fullpathofimport = '/pathtomysql/mysql';

		$resql = $this->query('SHOW VARIABLES LIKE \'basedir\'');
		if ($resql) {
			$liste = $this->fetch_array($resql);
			$basedir = $liste['Value'];
			$fullpathofimport = $basedir.(preg_match('/\/$/', $basedir) ? '' : '/').'bin/mysql';
		}
		return $fullpathofimport;
	}

	/**
	 * Return value of server parameters
	 *
	 * @param	string	$filter		Filter list on a particular value
	 * @return	array				Array of key-values (key=>value)
	 */
	public function getServerParametersValues($filter = '')
	{
		$result = array();

		$sql = 'SHOW VARIABLES';
		if ($filter) {
			$sql .= " LIKE '".$this->escape($filter)."'";
		}
		$resql = $this->query($sql);
		if ($resql) {
			while ($obj = $this->fetch_object($resql)) {
				$result[$obj->Variable_name] = $obj->Value;
			}
		}

		return $result;
	}

	/**
	 * Return value of server status (current indicators on memory, cache...)
	 *
	 * @param	string	$filter		Filter list on a particular value
	 * @return  array				Array of key-values (key=>value)
	 */
	public function getServerStatusValues($filter = '')
	{
		$result = array();

		$sql = 'SHOW STATUS';
		if ($filter) {
			$sql .= " LIKE '".$this->escape($filter)."'";
		}
		$resql = $this->query($sql);
		if ($resql) {
			while ($obj = $this->fetch_object($resql)) {
				$result[$obj->Variable_name] = $obj->Value;
			}
		}

		return $result;
	}
}
