<?php
/* Copyright (C) 2004       Rodolphe Quiedeville    <rodolphe@quiedeville.org>
 * Copyright (C) 2004-2010  Laurent Destailleur     <eldy@users.sourceforge.net>
 * Copyright (C) 2015       Cedric GROSS            <c.gross@kreiz-it.fr>
 * Copyright (C) 2015-2016  Raphaël Doursenaud      <rdoursenaud@gpcsolutions.fr>
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
 *      \file       htdocs/install/step2.php
 *      \ingroup    install
 *      \brief      Create tables, primary keys, foreign keys, indexes and functions into database and then load reference data
 */

include 'inc.php';
require_once $powererp_main_document_root.'/core/class/conf.class.php';
require_once $powererp_main_document_root.'/core/lib/admin.lib.php';

global $langs;

$step = 2;
$ok = 0;


// This page can be long. We increase the time allowed. / Cette page peut etre longue. On augmente le delai autorise.
// Only works if you are not in safe_mode. / Ne fonctionne que si on est pas en safe_mode.

$err = error_reporting();
error_reporting(0);      // Disable all errors
//error_reporting(E_ALL);
@set_time_limit(1800);   // Need 1800 on some very slow OS like Windows 7/64
error_reporting($err);

$action = GETPOST('action', 'aZ09') ?GETPOST('action', 'aZ09') : (empty($argv[1]) ? '' : $argv[1]);
$setuplang = GETPOST('selectlang', 'aZ09', 3) ?GETPOST('selectlang', 'aZ09', 3) : (empty($argv[2]) ? 'auto' : $argv[2]);
$langs->setDefaultLang($setuplang);

$langs->loadLangs(array("admin", "install"));


// Choice of DBMS

$choix = 0;
if ($powererp_main_db_type == "mysqli") {
	$choix = 1;
}
if ($powererp_main_db_type == "pgsql") {
	$choix = 2;
}
if ($powererp_main_db_type == "mssql") {
	$choix = 3;
}
if ($powererp_main_db_type == "sqlite") {
	$choix = 4;
}
if ($powererp_main_db_type == "sqlite3") {
	$choix = 5;
}
//if (empty($choix)) dol_print_error('','Database type '.$powererp_main_db_type.' not supported into step2.php page');


// Now we load forced values from install.forced.php file.

$useforcedwizard = false;
$forcedfile = "./install.forced.php";
if ($conffile == "/etc/powererp/conf.php") {
	$forcedfile = "/etc/powererp/install.forced.php";
}
if (@file_exists($forcedfile)) {
	$useforcedwizard = true;
	include_once $forcedfile;
	// test for travis
	if (!empty($argv[1]) && $argv[1] == "set") {
		$action = "set";
	}
}

powererp_install_syslog("- step2: entering step2.php page");


/*
 *	View
 */

pHeader($langs->trans("CreateDatabaseObjects"), "step4");

// Test if we can run a first install process
if (!is_writable($conffile)) {
	print $langs->trans("ConfFileIsNotWritable", $conffiletoshow);
	pFooter(1, $setuplang, 'jscheckparam');
	exit;
}

if ($action == "set") {
	print '<h3><img class="valignmiddle inline-block paddingright" src="../theme/common/octicons/build/svg/database.svg" width="20" alt="Database"> '.$langs->trans("Database").'</h3>';

	print '<table cellspacing="0" style="padding: 4px 4px 4px 0" border="0" width="100%">';
	$error = 0;

	$db = getDoliDBInstance($conf->db->type, $conf->db->host, $conf->db->user, $conf->db->pass, $conf->db->name, $conf->db->port);

	if ($db->connected) {
		print "<tr><td>";
		print $langs->trans("ServerConnection")." : ".$conf->db->host.'</td><td><img src="../theme/eldy/img/tick.png" alt="Ok"></td></tr>';
		$ok = 1;
	} else {
		print "<tr><td>Failed to connect to server : ".$conf->db->host.'</td><td><img src="../theme/eldy/img/error.png" alt="Error"></td></tr>';
	}

	if ($ok) {
		if ($db->database_selected) {
			powererp_install_syslog("step2: successful connection to database: ".$conf->db->name);
		} else {
			powererp_install_syslog("step2: failed connection to database :".$conf->db->name, LOG_ERR);
			print "<tr><td>Failed to select database ".$conf->db->name.'</td><td><img src="../theme/eldy/img/error.png" alt="Error"></td></tr>';
			$ok = 0;
		}
	}


	// Display version / Affiche version
	if ($ok) {
		$version = $db->getVersion();
		$versionarray = $db->getVersionArray();
		print '<tr><td>'.$langs->trans("DatabaseVersion").'</td>';
		print '<td>'.$version.'</td></tr>';
		//print '<td class="right">'.join('.',$versionarray).'</td></tr>';

		print '<tr><td>'.$langs->trans("DatabaseName").'</td>';
		print '<td>'.$db->database_name.'</td></tr>';
		//print '<td class="right">'.join('.',$versionarray).'</td></tr>';
	}

	$requestnb = 0;

	// To disable some code, so you can call step2 with url like
	// http://localhost/powererpnew/install/step2.php?action=set&token='.newToken().'&createtables=0&createkeys=0&createfunctions=0&createdata=llx_20_c_departements
	$createtables = GETPOSTISSET('createtables') ? GETPOST('createtables') : 1;
	$createkeys = GETPOSTISSET('createkeys') ? GETPOST('createkeys') : 1;
	$createfunctions = GETPOSTISSET('createfunctions') ? GETPOST('createfunction') : 1;
	$createdata = GETPOSTISSET('createdata') ? GETPOST('createdata') : 1;


	// To say sql requests are escaped for mysql so we need to unescape them
	$db->unescapeslashquot = true;


	/**************************************************************************************
	 *
	 * Load files tables/*.sql (not the *.key.sql). Files with '-xxx' in name are excluded (they will be loaded during activation of module 'xxx').
	 * To do before the files *.key.sql
	 *
	 ***************************************************************************************/
	if ($ok && $createtables) {
		// We always choose in mysql directory (Conversion is done by driver to translate SQL syntax)
		$dir = "mysql/tables/";

		$ok = 0;
		$handle = opendir($dir);
		powererp_install_syslog("step2: open tables directory ".$dir." handle=".$handle);
		$tablefound = 0;
		$tabledata = array();
		if (is_resource($handle)) {
			while (($file = readdir($handle)) !== false) {
				if (preg_match('/\.sql$/i', $file) && preg_match('/^llx_/i', $file) && !preg_match('/\.key\.sql$/i', $file) && !preg_match('/\-/', $file)) {
					$tablefound++;
					$tabledata[] = $file;
				}
			}
			closedir($handle);
		}

		// Sort list of sql files on alphabetical order (load order is important)
		sort($tabledata);
		foreach ($tabledata as $file) {
			$name = substr($file, 0, dol_strlen($file) - 4);
			$buffer = '';
			$fp = fopen($dir.$file, "r");
			if ($fp) {
				while (!feof($fp)) {
					$buf = fgets($fp, 4096);
					if (substr($buf, 0, 2) <> '--') {
						$buf = preg_replace('/--(.+)*/', '', $buf);
						$buffer .= $buf;
					}
				}
				fclose($fp);

				$buffer = trim($buffer);
				if ($conf->db->type == 'mysql' || $conf->db->type == 'mysqli') {	// For Mysql 5.5+, we must replace type=innodb with ENGINE=innodb
					$buffer = preg_replace('/type=innodb/i', 'ENGINE=innodb', $buffer);
				} else {
					// Keyword ENGINE is MySQL-specific, so scrub it for
					// other database types (mssql, pgsql)
					$buffer = preg_replace('/type=innodb/i', '', $buffer);
					$buffer = preg_replace('/ENGINE=innodb/i', '', $buffer);
				}

				// Replace the prefix tables
				if ($powererp_main_db_prefix != 'llx_') {
					$buffer = preg_replace('/llx_/i', $powererp_main_db_prefix, $buffer);
				}

				//print "<tr><td>Creation of table $name/td>";
				$requestnb++;

				powererp_install_syslog("step2: request: ".$buffer);
				$resql = $db->query($buffer, 0, 'dml');
				if ($resql) {
					// print "<td>OK request ==== $buffer</td></tr>";
					$db->free($resql);
				} else {
					if ($db->errno() == 'DB_ERROR_TABLE_ALREADY_EXISTS' ||
						$db->errno() == 'DB_ERROR_TABLE_OR_KEY_ALREADY_EXISTS') {
						//print "<td>already existing</td></tr>";
					} else {
						print "<tr><td>".$langs->trans("CreateTableAndPrimaryKey", $name);
						print "<br>\n".$langs->trans("Request").' '.$requestnb.' : '.$buffer.' <br>Executed query : '.$db->lastquery;
						print "\n</td>";
						print '<td><span class="error">'.$langs->trans("ErrorSQL")." ".$db->errno()." ".$db->error().'</span></td></tr>';
						$error++;
					}
				}
			} else {
				print "<tr><td>".$langs->trans("CreateTableAndPrimaryKey", $name);
				print "</td>";
				print '<td><span class="error">'.$langs->trans("Error").' Failed to open file '.$dir.$file.'</span></td></tr>';
				$error++;
				powererp_install_syslog("step2: failed to open file ".$dir.$file, LOG_ERR);
			}
		}

		if ($tablefound) {
			if ($error == 0) {
				print '<tr><td>';
				print $langs->trans("TablesAndPrimaryKeysCreation").'</td><td><img src="../theme/eldy/img/tick.png" alt="Ok"></td></tr>';
				$ok = 1;
			}
		} else {
			print '<tr><td>'.$langs->trans("ErrorFailedToFindSomeFiles", $dir).'</td><td><img src="../theme/eldy/img/error.png" alt="Error"></td></tr>';
			powererp_install_syslog("step2: failed to find files to create database in directory ".$dir, LOG_ERR);
		}
	}


	/***************************************************************************************
	 *
	 * Load files tables/*.key.sql. Files with '-xxx' in name are excluded (they will be loaded during activation of module 'xxx').
	 * To do after the files *.sql
	 *
	 ***************************************************************************************/
	if ($ok && $createkeys) {
		// We always choose in mysql directory (Conversion is done by driver to translate SQL syntax)
		$dir = "mysql/tables/";

		$okkeys = 0;
		$handle = opendir($dir);
		powererp_install_syslog("step2: open keys directory ".$dir." handle=".$handle);
		$tablefound = 0;
		$tabledata = array();
		if (is_resource($handle)) {
			while (($file = readdir($handle)) !== false) {
				if (preg_match('/\.sql$/i', $file) && preg_match('/^llx_/i', $file) && preg_match('/\.key\.sql$/i', $file) && !preg_match('/\-/', $file)) {
					$tablefound++;
					$tabledata[] = $file;
				}
			}
			closedir($handle);
		}

		// Sort list of sql files on alphabetical order (load order is important)
		sort($tabledata);
		foreach ($tabledata as $file) {
			$name = substr($file, 0, dol_strlen($file) - 4);
			//print "<tr><td>Creation of table $name</td>";
			$buffer = '';
			$fp = fopen($dir.$file, "r");
			if ($fp) {
				while (!feof($fp)) {
					$buf = fgets($fp, 4096);

					// Special case of lines allowed for some version only
					 // MySQL
					if ($choix == 1 && preg_match('/^--\sV([0-9\.]+)/i', $buf, $reg)) {
						$versioncommande = explode('.', $reg[1]);
						//var_dump($versioncommande);
						//var_dump($versionarray);
						if (count($versioncommande) && count($versionarray)
						&& versioncompare($versioncommande, $versionarray) <= 0) {
							// Version qualified, delete SQL comments
							$buf = preg_replace('/^--\sV([0-9\.]+)/i', '', $buf);
							//print "Ligne $i qualifiee par version: ".$buf.'<br>';
						}
					}
					 // PGSQL
					if ($choix == 2 && preg_match('/^--\sPOSTGRESQL\sV([0-9\.]+)/i', $buf, $reg)) {
						$versioncommande = explode('.', $reg[1]);
						//var_dump($versioncommande);
						//var_dump($versionarray);
						if (count($versioncommande) && count($versionarray)
						&& versioncompare($versioncommande, $versionarray) <= 0) {
							// Version qualified, delete SQL comments
							$buf = preg_replace('/^--\sPOSTGRESQL\sV([0-9\.]+)/i', '', $buf);
							//print "Ligne $i qualifiee par version: ".$buf.'<br>';
						}
					}

					// Add line if no comment
					if (!preg_match('/^--/i', $buf)) {
						$buffer .= $buf;
					}
				}
				fclose($fp);

				// If several requests, we loop on each
				$listesql = explode(';', $buffer);
				foreach ($listesql as $req) {
					$buffer = trim($req);
					if ($buffer) {
						// Replace the prefix tables
						if ($powererp_main_db_prefix != 'llx_') {
							$buffer = preg_replace('/llx_/i', $powererp_main_db_prefix, $buffer);
						}

						//print "<tr><td>Creation of keys and table index $name: '$buffer'</td>";
						$requestnb++;

						powererp_install_syslog("step2: request: ".$buffer);
						$resql = $db->query($buffer, 0, 'dml');
						if ($resql) {
							//print "<td>OK request ==== $buffer</td></tr>";
							$db->free($resql);
						} else {
							if ($db->errno() == 'DB_ERROR_KEY_NAME_ALREADY_EXISTS' ||
							$db->errno() == 'DB_ERROR_CANNOT_CREATE' ||
							$db->errno() == 'DB_ERROR_PRIMARY_KEY_ALREADY_EXISTS' ||
							$db->errno() == 'DB_ERROR_TABLE_OR_KEY_ALREADY_EXISTS' ||
							preg_match('/duplicate key name/i', $db->error())) {
								//print "<td>Deja existante</td></tr>";
								$key_exists = 1;
							} else {
								print "<tr><td>".$langs->trans("CreateOtherKeysForTable", $name);
								print "<br>\n".$langs->trans("Request").' '.$requestnb.' : '.$db->lastqueryerror();
								print "\n</td>";
								print '<td><span class="error">'.$langs->trans("ErrorSQL")." ".$db->errno()." ".$db->error().'</span></td></tr>';
								$error++;
							}
						}
					}
				}
			} else {
				print "<tr><td>".$langs->trans("CreateOtherKeysForTable", $name);
				print "</td>";
				print '<td><span class="error">'.$langs->trans("Error")." Failed to open file ".$dir.$file."</span></td></tr>";
				$error++;
				powererp_install_syslog("step2: failed to open file ".$dir.$file, LOG_ERR);
			}
		}

		if ($tablefound && $error == 0) {
			print '<tr><td>';
			print $langs->trans("OtherKeysCreation").'</td><td><img src="../theme/eldy/img/tick.png" alt="Ok"></td></tr>';
			$okkeys = 1;
		}
	}


	/***************************************************************************************
	 *
	 * Load the file 'functions.sql'
	 *
	 ***************************************************************************************/
	if ($ok && $createfunctions) {
		// For this file, we use a directory according to database type
		if ($choix == 1) {
			$dir = "mysql/functions/";
		} elseif ($choix == 2) {
			$dir = "pgsql/functions/";
		} elseif ($choix == 3) {
			$dir = "mssql/functions/";
		} elseif ($choix == 4) {
			$dir = "sqlite3/functions/";
		}

		// Creation of data
		$file = "functions.sql";
		if (file_exists($dir.$file)) {
			$fp = fopen($dir.$file, "r");
			powererp_install_syslog("step2: open function file ".$dir.$file." handle=".$fp);
			if ($fp) {
				$buffer = '';
				while (!feof($fp)) {
					$buf = fgets($fp, 4096);
					if (substr($buf, 0, 2) <> '--') {
						$buffer .= $buf."§";
					}
				}
				fclose($fp);
			}
			//$buffer=preg_replace('/;\';/',";'§",$buffer);

			// If several requests, we loop on each of them
			$listesql = explode('§', $buffer);
			foreach ($listesql as $buffer) {
				$buffer = trim($buffer);
				if ($buffer) {
					// Replace the prefix in table names
					if ($powererp_main_db_prefix != 'llx_') {
						$buffer = preg_replace('/llx_/i', $powererp_main_db_prefix, $buffer);
					}
					powererp_install_syslog("step2: request: ".$buffer);
					print "<!-- Insert line : ".$buffer."<br>-->\n";
					$resql = $db->query($buffer, 0, 'dml');
					if ($resql) {
						$ok = 1;
						$db->free($resql);
					} else {
						if ($db->errno() == 'DB_ERROR_RECORD_ALREADY_EXISTS'
						|| $db->errno() == 'DB_ERROR_KEY_NAME_ALREADY_EXISTS') {
							//print "Insert line : ".$buffer."<br>\n";
						} else {
							$ok = 0;

							print "<tr><td>".$langs->trans("FunctionsCreation");
							print "<br>\n".$langs->trans("Request").' '.$requestnb.' : '.$buffer;
							print "\n</td>";
							print '<td><span class="error">'.$langs->trans("ErrorSQL")." ".$db->errno()." ".$db->error().'</span></td></tr>';
							$error++;
						}
					}
				}
			}

			print "<tr><td>".$langs->trans("FunctionsCreation")."</td>";
			if ($ok) {
				print '<td><img src="../theme/eldy/img/tick.png" alt="Ok"></td></tr>';
			} else {
				print '<td><img src="../theme/eldy/img/error.png" alt="Error"></td></tr>';
				$ok = 1;
			}
		}
	}


	/***************************************************************************************
	 *
	 * Load files data/*.sql. Files with '-xxx' in name are excluded (they will be loaded during activation of module 'xxx').
	 *
	 ***************************************************************************************/
	if ($ok && $createdata) {
		// We always choose in mysql directory (Conversion is done by driver to translate SQL syntax)
		$dir = "mysql/data/";

		// Insert data
		$handle = opendir($dir);
		powererp_install_syslog("step2: open directory data ".$dir." handle=".$handle);
		$tablefound = 0;
		$tabledata = array();
		if (is_resource($handle)) {
			while (($file = readdir($handle)) !== false) {
				if (preg_match('/\.sql$/i', $file) && preg_match('/^llx_/i', $file) && !preg_match('/\-/', $file)) {
					if (preg_match('/^llx_accounting_account_/', $file)) {
						continue; // We discard data file of chart of account. This will be loaded when a chart is selected.
					}

					//print 'x'.$file.'-'.$createdata.'<br>';
					if (is_numeric($createdata) || preg_match('/'.preg_quote($createdata).'/i', $file)) {
						$tablefound++;
						$tabledata[] = $file;
					}
				}
			}
			closedir($handle);
		}

		// Sort list of data files on alphabetical order (load order is important)
		sort($tabledata);
		foreach ($tabledata as $file) {
			$name = substr($file, 0, dol_strlen($file) - 4);
			$fp = fopen($dir.$file, "r");
			powererp_install_syslog("step2: open data file ".$dir.$file." handle=".$fp);
			if ($fp) {
				$arrayofrequests = array();
				$linefound = 0;
				$linegroup = 0;
				$sizeofgroup = 1; // Grouping request to have 1 query for several requests does not works with mysql, so we use 1.

				// Load all requests
				while (!feof($fp)) {
					$buffer = fgets($fp, 4096);
					$buffer = trim($buffer);
					if ($buffer) {
						if (substr($buffer, 0, 2) == '--') {
							continue;
						}

						if ($linefound && ($linefound % $sizeofgroup) == 0) {
							$linegroup++;
						}
						if (empty($arrayofrequests[$linegroup])) {
							$arrayofrequests[$linegroup] = $buffer;
						} else {
							$arrayofrequests[$linegroup] .= " ".$buffer;
						}

						$linefound++;
					}
				}
				fclose($fp);

				powererp_install_syslog("step2: found ".$linefound." records, defined ".count($arrayofrequests)." group(s).");

				$okallfile = 1;
				$db->begin();

				// We loop on each requests of file
				foreach ($arrayofrequests as $buffer) {
					// Replace the tables prefixes
					if ($powererp_main_db_prefix != 'llx_') {
						$buffer = preg_replace('/llx_/i', $powererp_main_db_prefix, $buffer);
					}

					//powererp_install_syslog("step2: request: " . $buffer);
					$resql = $db->query($buffer, 1);
					if ($resql) {
						//$db->free($resql);     // Not required as request we launch here does not return memory needs.
					} else {
						if ($db->lasterrno() == 'DB_ERROR_RECORD_ALREADY_EXISTS') {
							//print "<tr><td>Insertion ligne : $buffer</td><td>";
						} else {
							$ok = 0;
							$okallfile = 0;
							print '<span class="error">'.$langs->trans("ErrorSQL")." : ".$db->lasterrno()." - ".$db->lastqueryerror()." - ".$db->lasterror()."</span><br>";
						}
					}
				}

				if ($okallfile) {
					$db->commit();
				} else {
					$db->rollback();
				}
			}
		}

		print "<tr><td>".$langs->trans("ReferenceDataLoading")."</td>";
		if ($ok) {
			print '<td><img src="../theme/eldy/img/tick.png" alt="Ok"></td></tr>';
		} else {
			print '<td><img src="../theme/eldy/img/error.png" alt="Error"></td></tr>';
			$ok = 1; // Data loading are not blocking errors
		}
	}
	print '</table>';
} else {
	print 'Parameter action=set not defined';
}


$ret = 0;
if (!$ok && isset($argv[1])) {
	$ret = 1;
}
powererp_install_syslog("Exit ".$ret);

powererp_install_syslog("- step2: end");


// Force here a value we need after because master.inc.php is not loaded into step2.
// This code must be similar with the one into main.inc.php

$conf->file->instance_unique_id = (empty($powererp_main_instance_unique_id) ? (empty($powererp_main_cookie_cryptkey) ? '' : $powererp_main_cookie_cryptkey) : $powererp_main_instance_unique_id); // Unique id of instance

$hash_unique_id = md5('powererp'.$conf->file->instance_unique_id);

$out  = '<input type="checkbox" name="powererppingno" id="powererppingno"'.((!empty($conf->global->MAIN_FIRST_PING_OK_ID) && $conf->global->MAIN_FIRST_PING_OK_ID == 'disabled') ? '' : ' value="checked" checked="true"').'> ';
$out .= '<label for="powererppingno">'.$langs->trans("MakeAnonymousPing").'</label>';

$out .= '<!-- Add js script to manage the uncheck of option to not send the ping -->';
$out .= '<script type="text/javascript">';
$out .= 'jQuery(document).ready(function(){';
$out .= '  document.cookie = "DOLINSTALLNOPING_'.$hash_unique_id.'=0; path=/"'."\n";
$out .= '  jQuery("#powererppingno").click(function() {';
$out .= '    if (! $(this).is(\':checked\')) {';
$out .= '      console.log("We uncheck anonymous ping");';
$out .= '      document.cookie = "DOLINSTALLNOPING_'.$hash_unique_id.'=1; path=/"'."\n";
$out .= '    } else {'."\n";
$out .= '      console.log("We check anonymous ping");';
$out .= '      document.cookie = "DOLINSTALLNOPING_'.$hash_unique_id.'=0; path=/"'."\n";
$out .= '    }'."\n";
$out .= '  });';
$out .= '});';
$out .= '</script>';

print $out;

pFooter($ok ? 0 : 1, $setuplang);

if (isset($db) && is_object($db)) {
	$db->close();
}

// Return code if ran from command line
if ($ret) {
	exit($ret);
}
