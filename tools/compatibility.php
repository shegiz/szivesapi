<?php

/**
 * Wave Framework <http://www.waveframework.com>
 * System Compatibility Script
 *
 * This script checks if installation is ready for Wave Framework. It checks for PHP version, whether 
 * Apache mod_rewrite RewriteEngine or Nginx URL rewriting is turned on and whether filesystem can be 
 * written to.
 *
 * @package    Tools
 * @author     Kristo Vaher <kristo@waher.net>
 * @copyright  Copyright (c) 2012, Kristo Vaher
 * @license    GNU Lesser General Public License Version 3
 * @tutorial   /doc/pages/guide_tools.htm
 * @since      1.5.1
 * @version    3.6.0
 */

// This initializes tools and authentication
require('.'.DIRECTORY_SEPARATOR.'tools_autoload.php');

// Log is printed out in plain text format
header('Content-Type: text/html;charset=utf-8');

?>
<!DOCTYPE html>
<html lang="en">
	<head>
		<title>Compatibility</title>
		<meta charset="utf-8">
		<meta name="viewport" content="width=device-width"/> 
		<link type="text/css" href="style.css" rel="stylesheet" media="all"/>
		<link rel="icon" href="../favicon.ico" type="image/x-icon"/>
		<link rel="icon" href="../favicon.ico" type="image/vnd.microsoft.icon"/>
		<meta content="noindex,nocache,nofollow,noarchive,noimageindex,nosnippet" name="robots"/>
		<meta http-equiv="cache-control" content="no-cache"/>
		<meta http-equiv="pragma" content="no-cache"/>
		<meta http-equiv="expires" content="0"/>
	</head>
	<body>
		<?php
		
		// Pops up an alert about default password
		passwordNotification($config['http-authentication-password']);
		
		// Header
		echo '<h1>System compatibility</h1>';
		echo '<h4 class="highlight">';
		foreach($softwareVersions as $software=>$version){
			// Adding version numbers
			echo '<b>'.$software.'</b> ('.$version.') ';
		}
		echo '</h4>';
		
		echo '<h2>Reference</h2>';
		echo '<div class="border box">';
			echo '<span class="bold">SUCCESS</span> Everything is OK<br/>';
			echo '<span class="bold orange">WARNING</span> Some functionality might not work or is unavailable<br/>';
			echo '<span class="bold red">FAILURE</span> Wave Framework is not meant to work on this system';
		echo '</div>';
		
		echo '<h2>Details</h2>';

		// Messages are stored in this array
		$log=array();

		// PHP VERSION
			$phpVersion=phpversion();
			if($phpVersion){
				if(version_compare($phpVersion,'5.3.0')>=0){
					$log[]='<span class="bold">SUCCESS</span>: PHP is version 5.3.0 or above (running '.$phpVersion.')';
				} else {
					$log[]='<span class="bold red"><span class="bold red">FAILURE</span></span>: PHP is running older version than 5.3, Wave Framework has not been tested on older versions of PHP';
				}
			} else {
				$log[]='<span class="bold orange">WARNING</span>: Unable to detect PHP version number, Wave Framework requires PHP version 5.3 or above';
			}
			
		// SHORT OPEN TAG
			if(function_exists('ini_get') && ini_get('short_open_tag')==1){
				$log[]='<span class="bold">SUCCESS</span>: PHP setting short_open_tag is enabled';
			} else {
				$log[]='<span class="bold orange">WARNING</span>: PHP setting short_open_tag is turned off, default View controller requires this to work properly, if this is not possible then edit /controllers/controller.view.php and example view files in /views/ folder';
			}

		// PDO

			// PDO
			if(extension_loaded('PDO')){
				$log[]='<span class="bold">SUCCESS</span>: PDO is supported';
			} else {
				$log[]='<span class="bold orange">WARNING</span>: PDO PHP extension is not supported, this extension is used by default database class, if database connections are not used then this warning can be ignored';
			}
			
			// PDO MYSQL
			if(extension_loaded('pdo_mysql')){
				$log[]='<span class="bold">SUCCESS</span>: PDO MySQL is supported';
			} else {
				$log[]='<span class="bold orange">WARNING</span>: PDO MySQL PHP extension is not supported, MySQL connections could not be used, if MySQL is not used then this warning can be ignored';
			}
			
			// PDO SQLITE
			if(extension_loaded('pdo_sqlite')){
				$log[]='<span class="bold">SUCCESS</span>: PDO SQLite is supported';
			} else {
				$log[]='<span class="bold orange">WARNING</span>: PDO SQLite PHP extension is not supported, SQLite connections could not be used, if SQLite is not used then this warning can be ignored';
			}
			
			//PDO POSTGRESQL
			if(extension_loaded('pdo_pgsql')){
				$log[]='<span class="bold">SUCCESS</span>: PDO PostgreSQL is supported';
			} else {
				$log[]='<span class="bold orange">WARNING</span>: PDO PostgreSQL PHP extension is not supported, PostgreSQL connections could not be used, if PostgreSQL is not used then this warning can be ignored';
			}
			
			//PDO ORACLE
			if(extension_loaded('pdo_oci')){
				$log[]='<span class="bold">SUCCESS</span>: PDO Oracle is supported';
			} else {
				$log[]='<span class="bold orange">WARNING</span>: PDO Oracle PHP extension is not supported, Oracle connections could not be used, if Oracle is not used then this warning can be ignored';
			}
			
			//PDO MSSQL
			if(extension_loaded('pdo_mssql')){
				$log[]='<span class="bold">SUCCESS</span>: PDO MSSQL is supported';
			} else {
				$log[]='<span class="bold orange">WARNING</span>: PDO MSSQL PHP extension is not supported, MSSQL connections could not be used, if MSSQL is not used then this warning can be ignored';
			}
			
		// ZLIB
			if(extension_loaded('Zlib')){
				$log[]='<span class="bold">SUCCESS</span>: Zlib is supported';
			} else {
				$log[]='<span class="bold orange">WARNING</span>: Zlib PHP extension is not supported, this is needed if output compression is used, but system turns it off automatically if extension is not present';
			}
			
		// APC
			if(extension_loaded('apc')){
				$log[]='<span class="bold">SUCCESS</span>: APC is supported';
			} else {
				$log[]='<span class="bold orange">WARNING</span>: APC PHP extension is not supported, this is not required by Wave Framework, but can improve performance, if supported';
			}
			
		// XML
			if(extension_loaded('SimpleXML')){
				$log[]='<span class="bold">SUCCESS</span>: SimpleXML is supported';
			} else {
				$log[]='<span class="bold orange">WARNING</span>: SimpleXML PHP extension is not supported, this is not required by Wave Framework, but it is needed if you wish to send direct XML procedure to API';
			}
			
		// CURL AND URL OPEN
			if(extension_loaded('curl')){
				$log[]='<span class="bold">SUCCESS</span>: cURL is supported';
			} else {
				if(function_exists('ini_get') && ini_get('allow_url_fopen')==1){
					$log[]='<span class="bold orange">WARNING</span>: cURL PHP extension is not supported, this is not required by Wave Framework, but is useful when making API requests to other systems that include POST data';
				} else {
					$log[]='<span class="bold orange">WARNING</span>: cURL PHP extension is not supported and allow_url_fopen setting is also off, these are not required by Wave Framework, but without them you cannot make API requests to other networks';
				}
			}
			
		// FILEINFO
			if(extension_loaded('fileinfo')){
				$log[]='<span class="bold">SUCCESS</span>: Fileinfo is supported';
			} else {
				$log[]='<span class="bold orange">WARNING</span>: Fileinfo PHP extension is not supported, this is used by File Handler, if this is not available then system detects all downloadable files as application/octet-stream';
			}
			
		// MCRYPT
			if(extension_loaded('mcrypt')){
				$log[]='<span class="bold">SUCCESS</span>: Mcrypt is supported';
			} else {
				$log[]='<span class="bold orange">WARNING</span>: Mcrypt PHP extension is not supported, this is optional and used only when API requests are made with www-crypt-input and www-crypt-output requests';
			}
			
		// ZIP
			if(extension_loaded('Zip')){
				$log[]='<span class="bold">SUCCESS</span>: Zip is supported';
			} else {
				$log[]='<span class="bold orange">WARNING</span>: Zip PHP extension is not supported, this is required by automatic update script, this warning can be ignored if update script is not used';
			}
			
		// FTP
			if(extension_loaded('ftp')){
				$log[]='<span class="bold">SUCCESS</span>: FTP is supported';
			} else {
				$log[]='<span class="bold orange">WARNING</span>: FTP PHP extension is not supported, this is required by automatic update script, this warning can be ignored if update script is not used';
			}
			
		// FTP
			if(extension_loaded('memcache')){
				$log[]='<span class="bold">SUCCESS</span>: Memcache is supported';
			} else {
				$log[]='<span class="bold orange">WARNING</span>: Memcache is not supported, this can be ignored if you do not intend to support Memcache as a caching layer';
			}
			
		// GD LIBRARY
			if(extension_loaded('gd')){
				$log[]='<span class="bold">SUCCESS</span>: GD Graphics Library is supported';
			} else {
				$log[]='<span class="bold orange">WARNING</span>: GD Graphics Library extension is not supported, this is required for dynamically loaded images, this warning can be ignored if dynamic loading is not used';
			}
			
		// APACHE AND NGINX
			if(strpos($_SERVER['SERVER_SOFTWARE'],'Apache')!==false){
				$log[]='<span class="bold">SUCCESS</span>: Apache server is used';
				
				// APACHE URL REWRITES
					if(file_exists('.htaccess')){
						// .htaccess in this directory attempts to rewrite compatibility.php into compatibility.php?mod_rewrite_enabled and if this is <span class="bold">SUCCESS</span>ful then mod_rewrite must work
						if(isset($_GET['rewrite_enabled'])){
							$log[]='<span class="bold">SUCCESS</span>: Apache mod_rewrite extension is supported';
						} else {
							$log[]='<span class="bold orange">WARNING</span>: Apache mod_rewrite extension is not supported, Index Gateway and mod_rewrite functionality will not work, this warning can be ignored if Index Gateway is not used';
						}
					} else {
						$log[]='<span class="bold orange">WARNING</span>: Cannot test if mod_rewrite and RewriteEngine are enabled, .htaccess file is missing from /tools/ folder, this warning can be ignored if Index Gateway is not used';
					}
					
				// HTACCESS
					if(file_exists('..'.DIRECTORY_SEPARATOR.'.htaccess')){
						$log[]='<span class="bold">SUCCESS</span>: .htaccess file is present';
					} else {
						$log[]='<span class="bold orange">WARNING</span>: .htaccess file is missing from root folder, Index Gateway and rewrite functionality will not work, this warning can be ignored if Index Gateway is not used';
					}
					
			} else if(strpos($_SERVER['SERVER_SOFTWARE'],'nginx')!==false){
				$log[]='<span class="bold">SUCCESS</span>: Nginx server is used';

				// NGINX URL REWRITES
				// This only works if the /nginx.conf location setting for compatibility script is used in Nginx server configuration
				if(isset($_GET['rewrite_enabled'])){
					$log[]='<span class="bold">SUCCESS</span>: Nginx HttpRewriteModule is supported';
				} else {
					$log[]='<span class="bold orange">WARNING</span>: Nginx HttpRewriteModule is not supported, Index Gateway and rewrite functionality will not work, this warning can be ignored if Index Gateway is not used';
				}
				
			} else {
				$log[]='<span class="bold orange">WARNING</span>: Your server is not Apache or Nginx, Index Gateway will not work, this warning can be ignored if Index Gateway is not used';
			}
			
			
		// FILESYSTEM

			// FILESYSTEM ROOT
			// No files should really be saved in this folder, but it might be necessary
			if(file_put_contents('..'.DIRECTORY_SEPARATOR.'filesystem'.DIRECTORY_SEPARATOR.'test.tmp','1')){
				$log[]='<span class="bold">SUCCESS</span>: /filesystem/ is writable';
				unlink('../filesystem/test.tmp');
			} else {
				$log[]='<span class="bold red"><span class="bold red">FAILURE</span></span>: /filesystem/ is not writable';
			}
			
			// FILESYSTEM CACHE ROOT
			// No files should really be saved in this folder, but it might be necessary
			if(file_put_contents('..'.DIRECTORY_SEPARATOR.'filesystem'.DIRECTORY_SEPARATOR.'cache'.DIRECTORY_SEPARATOR.'test.tmp','1')){
				$log[]='<span class="bold">SUCCESS</span>: /filesystem/cache/ is writable';
				unlink('..'.DIRECTORY_SEPARATOR.'filesystem'.DIRECTORY_SEPARATOR.'cache'.DIRECTORY_SEPARATOR.'test.tmp');
			} else {
				$log[]='<span class="bold red"><span class="bold red">FAILURE</span></span>: /filesystem/cache/ is not writable';
			}
			
			// FILESYSTEM IMAGE CACHE
			// All dynamically loaded image cache is stored here
			if(file_put_contents('..'.DIRECTORY_SEPARATOR.'filesystem'.DIRECTORY_SEPARATOR.'cache'.DIRECTORY_SEPARATOR.'images'.DIRECTORY_SEPARATOR.'test.tmp','1')){
				$log[]='<span class="bold">SUCCESS</span>: /filesystem/cache/images/ is writable';
				unlink('..'.DIRECTORY_SEPARATOR.'filesystem'.DIRECTORY_SEPARATOR.'cache'.DIRECTORY_SEPARATOR.'images'.DIRECTORY_SEPARATOR.'test.tmp');
			} else {
				$log[]='<span class="bold red"><span class="bold red">FAILURE</span></span>: /filesystem/cache/images/ is not writable';
			}
			
			// FILESYSTEM OUTPUT CACHE
			// All API response cache is stored here
			if(file_put_contents('..'.DIRECTORY_SEPARATOR.'filesystem'.DIRECTORY_SEPARATOR.'cache'.DIRECTORY_SEPARATOR.'output'.DIRECTORY_SEPARATOR.'test.tmp','1')){
				$log[]='<span class="bold">SUCCESS</span>: /filesystem/cache/output/ is writable';
				unlink('..'.DIRECTORY_SEPARATOR.'filesystem'.DIRECTORY_SEPARATOR.'cache'.DIRECTORY_SEPARATOR.'output'.DIRECTORY_SEPARATOR.'test.tmp');
			} else {
				$log[]='<span class="bold red"><span class="bold red">FAILURE</span></span>: /filesystem/cache/output/ is not writable';
			}
			
			// FILESYSTEM RESOURCE CACHE
			// All loaded resources (JavaScript, CSS and so on) in their compressed and/or minified format are cached here
			if(file_put_contents('..'.DIRECTORY_SEPARATOR.'filesystem'.DIRECTORY_SEPARATOR.'cache'.DIRECTORY_SEPARATOR.'resources'.DIRECTORY_SEPARATOR.'test.tmp','1')){
				$log[]='<span class="bold">SUCCESS</span>: /filesystem/cache/resources/ is writable';
				unlink('..'.DIRECTORY_SEPARATOR.'filesystem'.DIRECTORY_SEPARATOR.'cache'.DIRECTORY_SEPARATOR.'resources'.DIRECTORY_SEPARATOR.'test.tmp');
			} else {
				$log[]='<span class="bold red"><span class="bold red">FAILURE</span></span>: /filesystem/cache/resources/ is not writable';
			}
			
			// FILESYSTEM CUSTOM CACHE
			// Cache created through Factory cache methods
			if(file_put_contents('..'.DIRECTORY_SEPARATOR.'filesystem'.DIRECTORY_SEPARATOR.'cache'.DIRECTORY_SEPARATOR.'custom'.DIRECTORY_SEPARATOR.'test.tmp','1')){
				$log[]='<span class="bold">SUCCESS</span>: /filesystem/cache/custom/ is writable';
				unlink('..'.DIRECTORY_SEPARATOR.'filesystem'.DIRECTORY_SEPARATOR.'cache'.DIRECTORY_SEPARATOR.'custom'.DIRECTORY_SEPARATOR.'test.tmp');
			} else {
				$log[]='<span class="bold red"><span class="bold red">FAILURE</span></span>: /filesystem/cache/custom/ is not writable';
			}
			
			// FILESYSTEM CACHE TAGS
			// Cache created through Factory cache methods
			if(file_put_contents('..'.DIRECTORY_SEPARATOR.'filesystem'.DIRECTORY_SEPARATOR.'cache'.DIRECTORY_SEPARATOR.'tags'.DIRECTORY_SEPARATOR.'test.tmp','1')){
				$log[]='<span class="bold">SUCCESS</span>: /filesystem/cache/tags/ is writable';
				unlink('..'.DIRECTORY_SEPARATOR.'filesystem'.DIRECTORY_SEPARATOR.'cache'.DIRECTORY_SEPARATOR.'tags'.DIRECTORY_SEPARATOR.'test.tmp');
			} else {
				$log[]='<span class="bold red"><span class="bold red">FAILURE</span></span>: /filesystem/cache/tags/ is not writable';
			}
			
			// FILESYSTEM MESSENGER
			// All the certificates and encryption keys should be stored here
			// Wave Framework itself does not use this folder and this should be used by developer, if necessary
			if(file_put_contents('..'.DIRECTORY_SEPARATOR.'filesystem'.DIRECTORY_SEPARATOR.'messenger'.DIRECTORY_SEPARATOR.'test.tmp','1')){
				$log[]='<span class="bold">SUCCESS</span>: /filesystem/messenger/ is writable';
				unlink('..'.DIRECTORY_SEPARATOR.'filesystem'.DIRECTORY_SEPARATOR.'messenger'.DIRECTORY_SEPARATOR.'test.tmp');
			} else {
				$log[]='<span class="bold orange">WARNING</span>: /filesystem/messenger/ is not writable, this warning can be ignored if state messenger is not used';
			}
			
			// FILESYSTEM SESSIONS
			// User session cookies
			if(file_put_contents('..'.DIRECTORY_SEPARATOR.'filesystem'.DIRECTORY_SEPARATOR.'sessions'.DIRECTORY_SEPARATOR.'test.tmp','1')){
				$log[]='<span class="bold">SUCCESS</span>: /filesystem/sessions/ is writable';
				unlink('..'.DIRECTORY_SEPARATOR.'filesystem'.DIRECTORY_SEPARATOR.'sessions'.DIRECTORY_SEPARATOR.'test.tmp');
			} else {
				$log[]='<span class="bold orange">WARNING</span>: /filesystem/sessions/ is not writable, user session storage is unavailable and session storage will not work, if used';
			}
			
			// FILESYSTEM KEYS
			// All the certificates and encryption keys should be stored here
			// Wave Framework itself does not use this folder and this should be used by developer, if necessary
			if(file_put_contents('..'.DIRECTORY_SEPARATOR.'filesystem'.DIRECTORY_SEPARATOR.'keys'.DIRECTORY_SEPARATOR.'test.tmp','1')){
				$log[]='<span class="bold">SUCCESS</span>: /filesystem/keys/ is writable';
				unlink('..'.DIRECTORY_SEPARATOR.'filesystem'.DIRECTORY_SEPARATOR.'keys'.DIRECTORY_SEPARATOR.'test.tmp');
			} else {
				$log[]='<span class="bold orange">WARNING</span>: /filesystem/keys/ is not writable, this warning can be ignored if API keys and security features are not used';
			}
			
			// FILESYSTEM LIMITER
			// All the limiter data (requests per IP and so on) is stored here and is used by Limiter to check for possible denial of service attacks from IP's
			// Automatically blocked IP's are also stored here
			if(file_put_contents('..'.DIRECTORY_SEPARATOR.'filesystem'.DIRECTORY_SEPARATOR.'limiter'.DIRECTORY_SEPARATOR.'test.tmp','1')){
				$log[]='<span class="bold">SUCCESS</span>: /filesystem/limiter/ is writable';
				unlink('..'.DIRECTORY_SEPARATOR.'filesystem'.DIRECTORY_SEPARATOR.'limiter'.DIRECTORY_SEPARATOR.'test.tmp');
			} else {
				$log[]='<span class="bold orange">WARNING</span>: /filesystem/limiter/ is not writable, this warning can be ignored if Limiter is not used by Index Gateway';
			}
			
			// FILESYSTEM LOG
			// This stores all Logger generated log files
			if(file_put_contents('..'.DIRECTORY_SEPARATOR.'filesystem'.DIRECTORY_SEPARATOR.'logs'.DIRECTORY_SEPARATOR.'test.tmp','1')){
				$log[]='<span class="bold">SUCCESS</span>: /filesystem/logs/ is writable';
				unlink('..'.DIRECTORY_SEPARATOR.'filesystem'.DIRECTORY_SEPARATOR.'logs'.DIRECTORY_SEPARATOR.'test.tmp');
			} else {
				$log[]='<span class="bold orange">WARNING</span>: /filesystem/logs/ is not writable, this warning can be ignored if performance logging is not used by Index Gateway';
			}
			
			// FILESYSTEM ERRORS
			// This stores all Logger generated log files
			if(file_put_contents('..'.DIRECTORY_SEPARATOR.'filesystem'.DIRECTORY_SEPARATOR.'errors'.DIRECTORY_SEPARATOR.'test.tmp','1')){
				$log[]='<span class="bold">SUCCESS</span>: /filesystem/errors/ is writable';
				unlink('..'.DIRECTORY_SEPARATOR.'filesystem'.DIRECTORY_SEPARATOR.'errors'.DIRECTORY_SEPARATOR.'test.tmp');
			} else {
				$log[]='<span class="bold orange">WARNING</span>: /filesystem/errors/ is not writable, it is recommended to keep this folder writable, since it is useful for debugging purposes';
			}
			
			// FILESYSTEM SESSION TOKENS
			// This stores all API sessions and tokens per API profile
			if(file_put_contents('..'.DIRECTORY_SEPARATOR.'filesystem'.DIRECTORY_SEPARATOR.'tokens'.DIRECTORY_SEPARATOR.'test.tmp','1')){
				$log[]='<span class="bold">SUCCESS</span>: /filesystem/tokens/ is writable';
				unlink('..'.DIRECTORY_SEPARATOR.'filesystem'.DIRECTORY_SEPARATOR.'tokens'.DIRECTORY_SEPARATOR.'test.tmp');
			} else {
				$log[]='<span class="bold orange">WARNING</span>: /filesystem/tokens/ is not writable, this warning can be ignored if API keys and security features are not used';
			}
			
			// FILESYSTEM TEMPORARY FILES
			// Various temporary files should be stored here
			// Wave Framework itself does not use this folder and this should be used by developer, if necessary
			if(file_put_contents('..'.DIRECTORY_SEPARATOR.'filesystem'.DIRECTORY_SEPARATOR.'tmp'.DIRECTORY_SEPARATOR.'test.tmp','1')){
				$log[]='<span class="bold">SUCCESS</span>: /filesystem/tmp/ is writable';
				unlink('..'.DIRECTORY_SEPARATOR.'filesystem'.DIRECTORY_SEPARATOR.'tmp'.DIRECTORY_SEPARATOR.'test.tmp');
			} else {
				$log[]='<span class="bold orange">WARNING</span>: /filesystem/tmp/ is not writable, this warning can be ignored if your system does not write anything to that folder';
			}
			
			// FILESYSTEM USERDATA
			// Various user uploaded files should be stored here
			// Wave Framework itself does not use this folder and this should be used by developer, if necessary
			if(file_put_contents('..'.DIRECTORY_SEPARATOR.'filesystem'.DIRECTORY_SEPARATOR.'userdata'.DIRECTORY_SEPARATOR.'test.tmp','1')){
				$log[]='<span class="bold">SUCCESS</span>: /filesystem/userdata/ is writable';
				unlink('..'.DIRECTORY_SEPARATOR.'filesystem'.DIRECTORY_SEPARATOR.'userdata'.DIRECTORY_SEPARATOR.'test.tmp');
			} else {
				$log[]='<span class="bold orange">WARNING</span>: /filesystem/userdata/ is not writable, this warning can be ignored if your system does not write anything to that folder';
			}
			
			// BACKUPS
			// System backups are stored here
			// Wave Framework itself does not use this folder and this should be used by developer, if necessary
			if(file_put_contents('..'.DIRECTORY_SEPARATOR.'filesystem'.DIRECTORY_SEPARATOR.'backups'.DIRECTORY_SEPARATOR.'test.tmp','1')){
				$log[]='<span class="bold">SUCCESS</span>: /filesystem/backups/ is writable';
				unlink('..'.DIRECTORY_SEPARATOR.'filesystem'.DIRECTORY_SEPARATOR.'backups'.DIRECTORY_SEPARATOR.'test.tmp');
			} else {
				$log[]='<span class="bold orange">WARNING</span>: /filesystem/backups/ is not writable, this means that default update script cannot store backup archives when used';
			}
			
			// STATIC
			// Static uploads are stored here
			// Wave Framework itself does not use this folder and this should be used by developer, if necessary
			if(file_put_contents('..'.DIRECTORY_SEPARATOR.'filesystem'.DIRECTORY_SEPARATOR.'static'.DIRECTORY_SEPARATOR.'test.tmp','1')){
				$log[]='<span class="bold">SUCCESS</span>: /filesystem/static/ is writable';
				unlink('..'.DIRECTORY_SEPARATOR.'filesystem'.DIRECTORY_SEPARATOR.'static'.DIRECTORY_SEPARATOR.'test.tmp');
			} else {
				$log[]='<span class="bold orange">WARNING</span>: /filesystem/static/ is not writable, this warning can be ignored if you do not intend to write anything in static folder';
			}
			
			// UPDATES
			// Update archives are stored here
			// Wave Framework itself does not use this folder and this should be used by developer, if necessary
			if(file_put_contents('..'.DIRECTORY_SEPARATOR.'filesystem'.DIRECTORY_SEPARATOR.'updates'.DIRECTORY_SEPARATOR.'test.tmp','1')){
				$log[]='<span class="bold">SUCCESS</span>: /filesystem/updates/ is writable';
				unlink('..'.DIRECTORY_SEPARATOR.'filesystem'.DIRECTORY_SEPARATOR.'updates'.DIRECTORY_SEPARATOR.'test.tmp');
			} else {
				$log[]='<span class="bold orange">WARNING</span>: /filesystem/updates/ is not writable, this means that default update script cannot store update archives when used';
			}
			
			// FILESYSTEM DATA
			// Various databases (like SQLite) should be stored here
			// Wave Framework itself does not use this folder and this should be used by developer, if necessary
			if(file_put_contents('..'.DIRECTORY_SEPARATOR.'filesystem'.DIRECTORY_SEPARATOR.'data'.DIRECTORY_SEPARATOR.'test.tmp','1')){
				$log[]='<span class="bold">SUCCESS</span>: /filesystem/data/ is writable';
				unlink('..'.DIRECTORY_SEPARATOR.'filesystem'.DIRECTORY_SEPARATOR.'data'.DIRECTORY_SEPARATOR.'test.tmp');
			} else {
				$log[]='<span class="bold orange">WARNING</span>: /filesystem/data/ is not writable, this warning can be ignored if your system does not write anything to that folder';
			}

		// Printing out the log
		echo '<p>';
		echo implode('</p><p>',$log);
		echo '</p>';

		// Footer
		echo '<p class="footer small bold">Generated at '.date('d.m.Y h:i').' GMT '.date('P').' for '.$_SERVER['HTTP_HOST'].'</p>';
	
		?>
	</body>
</html>