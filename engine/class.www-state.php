<?php

/**
 * Wave Framework <http://www.waveframework.com>
 * State Class
 *
 * State is always required by Wave Framework. It is used by API and some handlers. State is used 
 * to keep track of system state, configuration and its changes, such as relevant PHP settings. 
 * It allows changing these settings, and thus affecting API or PHP configuration. State also 
 * includes functionality for State Messenger, sessions, cookies, translations and sitemap data. 
 * State is assigned in API and is accessible in MVC objects through Factory wrapper methods. 
 * Multiple different states can be used by the same request, but usually just one is used per 
 * request. State is only kept for the duration of the request processing and is not stored 
 * beyond its use in the request.
 *
 * @package    State
 * @author     Kristo Vaher <kristo@waher.net>
 * @copyright  Copyright (c) 2012, Kristo Vaher
 * @license    GNU Lesser General Public License Version 3
 * @tutorial   /doc/pages/state.htm
 * @since      1.0.0
 * @version    3.6.9
 */

class WWW_State	{

	/**
	 * This is the primary variable of State class and it carries representation of the system 
	 * configuration, both loaded from /config.ini file as well as initialized from environmental 
	 * server variables.
	 */
	public $data=array();
	
	/**
	 * This should hold Database class and connection data, if used.
	 */
	public $databaseConnection=false;
	
	/**
	 * This holds the session handler for session management. This is a
	 * WWW_Sessions class from /resources/sessions.php file.
     */	 
	public $sessionHandler=false;
	
	/**
	 * This is an internal variable that State uses to check if it has already validated sessions 
	 * or not.
	 */
	private $sessionStarted=false;
	
	/**
	 * This holds the 'keyword' or 'passkey' of currently used State messenger.
	 */
	private $messenger=false;
	
	/**
	 * This holds state messenger data as an array.
	 */
	private $messengerData=array();
	
	/**
	 * This holds system Tool Class object, if it exists.
	 */
	private $tools=false;
	
	/**
	 * Construction of State object initializes the defaults for $data variable. A lot of the 
	 * data is either loaded from /config.ini file or initialized based on server environment 
	 * variables. Fingerprint string is also created during construction as well as input data 
	 * loaded from XML or JSON strings, if sent with POST directly.
	 *
	 * @param array $config configuration array
	 * @return WWW_State
	 */
	final public function __construct($config=array()){
	
		// PRE-DEFINED STATE VALUES
		
			// Required constants
			if(!defined('__IP__')){
				define('__IP__',$_SERVER['REMOTE_ADDR']);
			}
			if(!defined('__ROOT__')){
				define('__ROOT__',__DIR__.DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR);
			}
	
			// A lot of default State variables are loaded from PHP settings, others are simply pre-defined
			// Every setting from core configuration file is also listed here
			$this->data=array(
				'404-image-placeholder'=>true,
				'404-view'=>'404',
				'access-control'=>false,
				'apc'=>0,
				'api-logging'=>array('*','!public'),
				'api-permissions'=>array('*'),
				'api-profile'=>'public',
				'api-public-profile'=>'public',
				'api-public-token'=>false,
				'api-versions'=>array('v1'),
				'cache-database'=>false,
				'cache-database-address-column'=>'address',
				'cache-database-data-column'=>'data',
				'cache-database-errors'=>true,
				'cache-database-host'=>'localhost',
				'cache-database-name'=>'',
				'cache-database-password'=>'',
				'cache-database-persistent'=>false,
				'cache-database-table-name'=>'cache',
				'cache-database-timestamp-column'=>'timestamp',
				'cache-database-type'=>false,
				'cache-database-username'=>'',
				'client-ip'=>__IP__,
				'client-user-agent'=>((isset($_SERVER['HTTP_USER_AGENT']))?$_SERVER['HTTP_USER_AGENT']:''),
				'content-security-policy'=>false,
				'database-errors'=>true,
				'database-host'=>'localhost',
				'database-name'=>'',
				'database-password'=>'',
				'database-persistent'=>false,
				'database-type'=>'mysql',
				'database-username'=>'',
				'developer'=>false,
				'developer-ip'=>'',
				'developer-user-agent'=>'',
				'directory-data'=>false,
				'directory-filesystem'=>false,
				'directory-keys'=>false,
				'directory-static'=>false,
				'directory-system'=>str_replace('engine'.DIRECTORY_SEPARATOR.'class.www-state.php','',__FILE__),
				'directory-tmp'=>false,
				'directory-user'=>false,
				'dynamic-color-whitelist'=>'',
				'dynamic-filter-whitelist'=>'',
				'dynamic-image-filters'=>true,
				'dynamic-image-loading'=>true,
				'dynamic-max-size'=>1000,
				'dynamic-position-whitelist'=>'',
				'dynamic-quality-whitelist'=>'',
				'dynamic-resource-loading'=>true,
				'dynamic-size-whitelist'=>'',
				'enforce-first-language-url'=>true,
				'enforce-url-end-slash'=>true,
				'errors-reporting'=>'full',
				'errors-trace'=>false,
				'errors-verbose'=>false,
				'file-robots'=>'noindex,nocache,nofollow,noarchive,noimageindex,nosnippet',
				'fingerprint'=>false,
				'forbidden-extensions'=>array('tmp','log','ht','htaccess','pem','crt','db','sql','version','conf','ini','empty'),
				'frame-permissions'=>'',
				'headers-set'=>array(),
				'headers-unset'=>array(),
				'home-view'=>'home',
				'http-accept'=>((isset($_SERVER['HTTP_ACCEPT']))?explode(',',$_SERVER['HTTP_ACCEPT']):''),
				'http-accept-charset'=>((isset($_SERVER['HTTP_ACCEPT_CHARSET']))?explode(',',$_SERVER['HTTP_ACCEPT_CHARSET']):array()),
				'http-accept-encoding'=>((isset($_SERVER['HTTP_ACCEPT_ENCODING']))?explode(',',$_SERVER['HTTP_ACCEPT_ENCODING']):array()),
				'http-accept-language'=>((isset($_SERVER['HTTP_ACCEPT_LANGUAGE']))?explode(',',$_SERVER['HTTP_ACCEPT_LANGUAGE']):array()),
				'http-authentication-ip'=>'*',
				'http-authentication-password'=>'',
				'http-authentication-username'=>'',
				'http-do-not-track'=>((isset($_SERVER['HTTP_DNT']) && $_SERVER['HTTP_DNT']==1)?true:false),
				'http-content-length'=>((isset($_SERVER['CONTENT_LENGTH']))?$_SERVER['CONTENT_LENGTH']:false),
				'http-content-type'=>((isset($_SERVER['CONTENT_TYPE']))?$_SERVER['CONTENT_TYPE']:false),
				'http-host'=>$_SERVER['HTTP_HOST'],
				'http-if-modified-since'=>false,
				'http-input'=>false,
				'http-referrer'=>((isset($_SERVER['HTTP_REFERER']))?$_SERVER['HTTP_REFERER']:false),
				'http-request-method'=>$_SERVER['REQUEST_METHOD'],
				'https-mode'=>(((isset($_SERVER['HTTPS']) && ($_SERVER['HTTPS']==1 || $_SERVER['HTTPS']=='on')) || (isset($_SERVER['SCRIPT_URI']) && strpos($_SERVER['SCRIPT_URI'],'https://')!==false))?true:false),
				'image-extensions'=>array('jpeg','jpg','png'),
				'image-robots'=>'noindex,nocache,nofollow,noarchive,noimageindex,nosnippet',
				'index-url-cache-timeout'=>0,
				'index-view-cache-timeout'=>0,
				'internal-logging'=>array('*','!input-data','!output-data'),
				'language'=>false,
				'languages'=>array('en'),
				'limiter'=>false,
				'limiter-authentication'=>false,
				'limiter-blacklist'=>false,
				'limiter-https'=>false,
				'limiter-load'=>false,
				'limiter-referrer'=>'*',
				'limiter-request'=>false,
				'limiter-whitelist'=>false,
				'locale'=>setlocale(LC_ALL,0),
				'memcache'=>false,
				'memcache-host'=>'localhost',
				'memcache-port'=>11211,
				'memory-limit'=>false,
				'output-compression'=>'deflate',
				'project-author'=>false,
				'project-copyright'=>false,
				'project-title'=>false,
				'request-id'=>((isset($_SERVER['UNIQUE_ID']))?$_SERVER['UNIQUE_ID']:false),
				'request-time'=>$_SERVER['REQUEST_TIME'],
				'request-true'=>false,
				'request-uri'=>$_SERVER['REQUEST_URI'],
				'resource-cache-timeout'=>31536000,
				'resource-extensions'=>array('css','js','txt','csv','xml','html','htm','rss','vcard'),
				'resource-robots'=>'noindex,nocache,nofollow,noarchive,noimageindex,nosnippet',
				'robots'=>'noindex,nocache,nofollow,noarchive,noimageindex,nosnippet',
				'robots-cache-timeout'=>14400,
				'server-ip'=>((isset($_SERVER['SERVER_ADDR']))?$_SERVER['SERVER_ADDR']:false),
				'server-name'=>((isset($_SERVER['SERVER_NAME']))?$_SERVER['SERVER_NAME']:false),
				'server-port'=>((isset($_SERVER['SERVER_PORT']))?$_SERVER['SERVER_PORT']:false),
				'session-data'=>array(),
				'session-domain'=>false,
				'session-fingerprint'=>array(),
				'session-fingerprint-key'=>'www-fingerprint',
				'session-http-only'=>true,
				'session-id'=>false,
				'session-lifetime'=>0,
				'session-name'=>'WWW'.crc32(__ROOT__),
				'session-original-data'=>array(),
				'session-path'=>false,
				'session-permissions-key'=>'www-permissions',
				'session-regenerate'=>0,
				'session-secure'=>false,
				'session-timestamp-key'=>'www-timestamp',
				'session-token-key'=>'www-public-token',
				'session-user-key'=>'www-user',
				'sitemap'=>array(),
				'sitemap-cache-timeout'=>14400,
				'sitemap-raw'=>array(),
				'storage'=>array(),
				'test-database-host'=>false,
				'test-database-name'=>false,
				'test-database-password'=>false,
				'test-database-type'=>false,
				'test-database-username'=>false,
				'testing'=>false,
				'time-limit'=>false,
				'timezone'=>false,
				'translations'=>array(),
				'trusted-proxies'=>array('*'),
				'url-base'=>false,
				'url-web'=>str_replace('index.php','',$_SERVER['SCRIPT_NAME']),
				'user-data'=>false,
				'user-permissions'=>false,
				'version'=>'1.0.0',
				'view'=>array(),
				'view-headers'=>array()
			);			
			
		// ASSIGNING STATE FROM CONFIGURATION FILE
					
			// If array of configuration data is set during object creation, it is used
			// This loops over all the configuration options from /config.ini file through setState() function
			// That function has key-specific functionality that can be tied to some internal commands and PHP functions
			if(!empty($config)){
				$this->setState(array($config));
			}
			
		// CHECKING FOR SERVER OR PHP SPECIFIC CONFIGURATION OPTIONS AND ASSIGNING UNSET CONFIGURATIONS
		
			// Removing full stop from the beginning of both directory URL's
			if($this->data['url-web'][0]=='.'){
				$this->data['url-web'][0]='';
			}
			
			// If request ID is not set
			if(!$this->data['request-id']){
				$this->data['request-id']=md5(uniqid('www',true).microtime().$this->data['request-uri'].$this->data['request-time'].$this->data['client-user-agent'].$this->data['client-ip']);
			}
			
			// If default session cookie domain is not set
			if(!$this->data['session-domain']){
				$this->data['session-domain']=$this->data['http-host'];
			}
			
			// If default session cookie path is not set
			if(!$this->data['session-path']){
				$this->data['session-path']=$this->data['url-web'];
			}
			
			// Finding base URL
			if(!$this->data['url-base']){
				$this->data['url-base']=(($this->data['https-mode'])?'https://':'http://').$this->data['http-host'].$this->data['url-web'];
			}
			
			// Defining default user root folder
			if(!$this->data['directory-filesystem']){
				$this->data['directory-filesystem']=$this->data['directory-system'].'filesystem'.DIRECTORY_SEPARATOR;
			}
			
			// Defining default user root folder
			if(!$this->data['directory-user']){
				$this->data['directory-user']=$this->data['directory-filesystem'].'userdata'.DIRECTORY_SEPARATOR;
			}
			
			// Defining default user root folder
			if(!$this->data['directory-data']){
				$this->data['directory-data']=$this->data['directory-filesystem'].'data'.DIRECTORY_SEPARATOR;
			}
			
			// Defining default static folder
			if(!$this->data['directory-static']){
				$this->data['directory-static']=$this->data['directory-filesystem'].'static'.DIRECTORY_SEPARATOR;
			}
			
			// Defining temporary files root folder
			if(!$this->data['directory-tmp']){
				$this->data['directory-tmp']=$this->data['directory-filesystem'].'tmp'.DIRECTORY_SEPARATOR;
			}
			
			// Defining certificates and keys folder
			if(!$this->data['directory-keys']){
				$this->data['directory-keys']=$this->data['directory-filesystem'].'keys'.DIRECTORY_SEPARATOR;
			}
				
			// If timezone is still set to false, then system attempts to set the currently set timezone
			// Some systems throw deprecated warning if this value is not set
			if($this->data['timezone']==false){
				// Some systems throw a deprecated warning without implicitly re-setting default timezone
				date_default_timezone_set('Europe/London');
				$this->data['timezone']='Europe/London';
			}
			
			// Setting if modified since, if it happens to be set
			if(isset($_SERVER['HTTP_IF_MODIFIED_SINCE']) && $this->data['http-if-modified-since']==false){
				$this->data['http-if-modified-since']=strtotime($_SERVER['HTTP_IF_MODIFIED_SINCE']);
			}
			
			// If default API profile has been changed by configuration, we assign current API profile to default profile as well
			if($this->data['api-public-profile']!='public'){
				$this->data['api-profile']=$this->data['api-public-profile'];
			}
			
			// If first language is not defined then first node from languages array is used
			if($this->data['language']==false){
				$this->data['language']=$this->data['languages'][0];
			}
			
			// Making sure that boundary is not part of the content type definition
			if($this->data['http-content-type']){
				$tmp=explode(';',$this->data['http-content-type']);
				$this->data['http-content-type']=array_shift($tmp);
			}
			
			// Compressed output is turned off if the requesting user agent does not support it
			// This is also turned off if PHP does not support Zlib compressions
			if($this->data['output-compression']!=false){
				if(!in_array($this->data['output-compression'],$this->data['http-accept-encoding']) || !extension_loaded('Zlib')){
					$this->data['output-compression']=false;
				}
			}
			
			// If configuration has not sent a request string then State solves it using request-uri
			if(!$this->data['request-true']){
				// If install is at www.example.com/w/ subfolder and user requests www.example.com/w/en/page/ then this would be parsed to 'en/page/'
				$this->data['request-true']=preg_replace('/(^'.preg_quote($this->data['url-web'],'/').')/ui','',$this->data['request-uri']);
			}
			
			// This sets the developer flag to either true or false depending on configuration
			if($this->data['developer-ip']!='' && $this->data['developer-user-agent']!=''){
				// IP addresses can be comma-separated list
				$developers=explode(',',$this->data['developer-ip']);
				// User agent is matched by finding user agent string within the configuration
				if(in_array($this->data['client-ip'],$developers) && strpos(str_replace(';','',$this->data['client-user-agent']),$this->data['developer-user-agent'])!==false){
					$this->data['developer']=true;
				} 
			}
		
		// FINGERPRINTING
		
			// Fingerprint is created based on data sent by user agent, this can be useful for light detection without cookies
			$fingerprint='';
			
			// If IP is set for session fingerprinting
			if(in_array('ip',$this->data['session-fingerprint'])){
				$fingerprint.=$this->data['client-ip'];
			}
			
			// If browser is used for session fingerprinting
			if(in_array('browser',$this->data['session-fingerprint'])){
				$fingerprint.=$this->data['client-user-agent'];
				$fingerprint.=(isset($_SERVER['HTTP_ACCEPT']))?$_SERVER['HTTP_ACCEPT']:'';
				$fingerprint.=(isset($_SERVER['HTTP_ACCEPT_LANGUAGE']))?$_SERVER['HTTP_ACCEPT_LANGUAGE']:'';
				$fingerprint.=(isset($_SERVER['HTTP_ACCEPT_ENCODING']))?$_SERVER['HTTP_ACCEPT_ENCODING']:'';
				$fingerprint.=(isset($_SERVER['HTTP_ACCEPT_CHARSET']))?$_SERVER['HTTP_ACCEPT_CHARSET']:'';
				$fingerprint.=(isset($_SERVER['HTTP_KEEP_ALIVE']))?$_SERVER['HTTP_KEEP_ALIVE']:'';
				$fingerprint.=(isset($_SERVER['HTTP_CONNECTION']))?$_SERVER['HTTP_CONNECTION']:'';
			}
			
			// If fingerprint is not an empty string then it overwrites the State value
			if($fingerprint!=''){
				// Fingerprint is hashed with MD5 using session name for salt
				$this->data['fingerprint']=md5($this->data['session-name'].$fingerprint);
			}
			
		// JSON OR XML BASED INPUT
		
			// PHP Input data is ignored for input if form submit is done
			if(!in_array($this->data['http-content-type'],array(false,'','application/x-www-form-urlencoded','multipart/form-data'))){
				// Gather sent input
				$phpInput=file_get_contents('php://input');
				// For custom content types, when data is sent as an XML or JSON string
				if($phpInput!=''){
					// Parsing method depends on content type header
					if($this->data['http-content-type']=='application/json'){
						// JSON string is converted to associative array
						$this->data['http-input']=json_decode($phpInput,true);
					} elseif(extension_loaded('SimpleXML') && ($this->data['http-content-type']=='application/xml')){
						// This is not supported in earlier versions of LibXML
						if(defined('LIBXML_PARSEHUGE')){
							$tmp=simplexml_load_string($phpInput,'SimpleXMLElement',LIBXML_NOERROR | LIBXML_NOWARNING | LIBXML_ERR_NONE | LIBXML_PARSEHUGE);
						} else {
							$tmp=simplexml_load_string($phpInput,'SimpleXMLElement',LIBXML_NOERROR | LIBXML_NOWARNING | LIBXML_ERR_NONE);
						}
						// Data is converted to array only if an object was created
						if($tmp){
							$this->data['http-input']=json_decode(json_encode($tmp),true);
						}
					} else {
						// Storing the entire stream directly
						$this->data['http-input']=$phpInput;
					}
				} 
			}
			
			// If special input file is set as XML
			if(isset($_FILES['www-xml']) || isset($_REQUEST['www-xml'])){
				// If this is a file upload or not
				if(isset($_FILES['www-xml'])){
					// This is not supported in earlier versions of LibXML
					if(defined('LIBXML_PARSEHUGE')){
						$tmp=simplexml_load_file($_FILES['www-xml']['tmp_name'],'SimpleXMLElement',LIBXML_NOERROR | LIBXML_NOWARNING | LIBXML_ERR_NONE | LIBXML_PARSEHUGE);
					} else {
						$tmp=simplexml_load_file($_FILES['www-xml']['tmp_name'],'SimpleXMLElement',LIBXML_NOERROR | LIBXML_NOWARNING | LIBXML_ERR_NONE);
					}
				} else {
					// This is not supported in earlier versions of LibXML
					if(defined('LIBXML_PARSEHUGE')){
						$tmp=simplexml_load_string($_REQUEST['www-xml'],'SimpleXMLElement',LIBXML_NOERROR | LIBXML_NOWARNING | LIBXML_ERR_NONE | LIBXML_PARSEHUGE);
					} else {
						$tmp=simplexml_load_string($_REQUEST['www-xml'],'SimpleXMLElement',LIBXML_NOERROR | LIBXML_NOWARNING | LIBXML_ERR_NONE);
					}
				}
				// Data is converted to array only if an object was created
				if($tmp){
					$this->data['http-input']=json_decode(json_encode($tmp),true);
				}
			} 
			
			// If special input file is set as JSON
			if(isset($_FILES['www-json']) || isset($_REQUEST['www-json'])){
				if(isset($_FILES['www-json'])){
					// JSON string is converted to associative array
					$this->data['http-input']=json_decode(file_get_contents($_FILES['www-json']['tmp_name']),true);
				} else {
					// JSON string is converted to associative array
					$this->data['http-input']=json_decode($_REQUEST['www-json'],true);
				}
			}
		
	}
	
	/**
	 * When State class is not used anymore, then state messenger data - if set - is written 
	 * to filesystem based on the State messenger key. This method also deletes session cookie, 
	 * if sessions have been used but the session variable itself is empty.
	 *
	 * @return null
	 */
	final public function __destruct(){
	
		// Only applies if request messenger actually holds data
		if($this->messenger){
			// This stores messenger data in filesystem
			$this->storeMessenger();
		}
		
		// This will commit session to the session storage
		if(!headers_sent()){
			$this->commitHeaders();
		}
		
	}
	
	// STATE MANIPULATION
	
		/**
		 * This is the basic call to return a State variable from the object. If this method is 
		 * called without any variables, then the entire State array is returned. You can send 
		 * one or more key variables to this method to return a specific key from State. If you 
		 * send multiple parameters then this method attempts to find keys of a key in case the 
		 * State variable is an array itself. $input variable is only used within State class 
		 * itself. Method returns null for keys that have not been set.
		 *
		 * @param array $input input variables sent to the function
		 * @return mixed
		 */
		final public function getState($input){
			// Returning the entire array, if entire state was requested
			if(!$input){
				return $this->data;
			}
			// Finding out the specific requested variable
			$return=&$this->data;
			// Looping over each of the input parameters sent to the function
			foreach($input as $key){
				if(!isset($return[$key])){
					trigger_error('State variable '.implode(',',$input).' does not exist',E_USER_NOTICE);
					return null;
				}
				$return=&$return[$key];
			}
			return $return;
		}
		
		/**
		 * This method is used to set a $data variable value in State object. $variable can 
		 * also be an array of keys and values, in which case multiple variables are set at 
		 * once. This method uses stateChanged() for variables that carry additional 
		 * functionality, such as setting timezone. $input variable is only used within 
		 * State class itself.
		 *
		 * @param mixed $input input variables sent to the function
		 * @return boolean
		 */
		final public function setState($input){
				
			// If an array is sent as key-value pair
			if($input && count($input)==1 && is_array($input[0])){
			
				foreach($input[0] as $key=>$val){
					// Setting the state variable (note that the stateChanged may change this)
					$this->data[$key]=$val;
					// Checking for system specific changes due to the changed State
					$this->stateChanged($key,$val);
				}
				
			} elseif(count($input)>1){
			
				// The last element in the sent parameters is the value to be set
				$value=array_pop($input);
				// Setting the proper address of State to the newly set variable
				$pointer=&$this->data;
				foreach($input as $key){
					$pointer=&$pointer[$key];
				}
				$pointer=$value;
				
				// Checking for system specific changes due to the changed State
				return $this->stateChanged($input[0],$value);
				
			} else {
			
				// Only one parameter was sent, meaning that the setting fails
				trigger_error('setState() method requires two variables if not sending an array',E_USER_NOTICE);
				return false;
				
			}
			
			// State value has been set
			return true;
			
		}
		
		/**
		 * This is a private method used internally whenever configuration is changed. It has 
		 * checks for cases when a variable is changed that carries additional functionality 
		 * such as when changing the timezone or output compression. For example, if output 
		 * compression is set, but not supported by user agent that is making the request, 
		 * then output supression is turned off.
		 *
		 * @param string $variable variable name that is changed
		 * @param mixed $value new value of the variable
		 * @return boolean
		 */
		final private function stateChanged($variable,$value=true){
			
			// Certain variables are checked that might change system flags
			switch ($variable) {
				case 'timezone':
					// Attempting to set default timezone
					if(!date_default_timezone_set($value)){
						trigger_error('Cannot set timezone to '.$value,E_USER_WARNING);
						return false;
					}
					break;
				case 'memory-limit':
					if($value){
						if(!function_exists('ini_set') || !ini_set('memory_limit',$value)){
							trigger_error('Cannot set memory limit to '.$value,E_USER_WARNING);
							return false;
						}
					}
					break;
				case 'time-limit':
					if($value){
						set_time_limit($value);
					}
					break;
				case 'locale':
					if($value){
						if(!setlocale(LC_ALL,$value.'.UTF-8')){
							trigger_error('Cannot set locale to '.$value.'.UTF-8',E_USER_WARNING);
							return false;
						}
					}
					break;
				case 'output-compression':
					// If user agent does not expect compressed data and PHP extension is not loaded, then this value cannot be turned on
					if($value==false || !in_array($value,$this->data['http-accept-encoding']) || !extension_loaded('Zlib')){
						$this->data[$variable]=false;
						return false;
					}
					break;
			}
			
			// State has been changed
			return true;
			
		}
		
		/**
		 * This function is called before output is pushed to browser by the API or when State 
		 * object is not used anymore. This method is not accessible to Factory class, but it 
		 * is not private.
		 *
		 * @return boolean
		 */
		final public function commitHeaders(){
		
			// If sessions have been started
			if($this->sessionStarted){
				
				// If session data has changed in any way or session is assigned to be regenerated
				if($this->sessionHandler->regenerateId || $this->data['session-original-data']!=$this->data['session-data']){
					// Checking if there are more data in sessions than just internal keys
					$sessionSize=count($this->data['session-data']);
					if($sessionSize==0 || ($sessionSize==1 && isset($this->data['session-data'][$this->data['session-timestamp-key']])) || ($sessionSize==1 && isset($this->data['session-data'][$this->data['session-fingerprint-key']])) || ($sessionSize==2 && isset($this->data['session-data'][$this->data['session-timestamp-key']]) && isset($this->data['session-data'][$this->data['session-fingerprint-key']]))){
						// This will make the session handler destroy the session
						$this->data['session-data']=array();
					}
					// Sending State session data to session handler
					$this->sessionHandler->sessionData=$this->data['session-data'];
					// Closing sessions
					$this->sessionHandler->sessionCommit();
				}
			
			}
			
			// Commiting headers for added headers
			if(!empty($this->data['headers-set'])){
				foreach($this->data['headers-set'] as $header=>$replace){
					header($header,$replace);
				}
			}
			
			// Removing headers if set for removal
			if(!empty($this->data['headers-unset'])){
				foreach($this->data['headers-unset'] as $header){
					header_remove($header);
				}
			}
			
			// Headers have been commited
			return true;
			
		}
		
	// SITEMAP AND TRANSLATIONS

		/**
		 * This method returns an array of currently active translations, or for a language set 
		 * with $language variable. If $keyword is also set, then it returns a specific translation 
		 * with that keyword from $language translations. If $keyword is an array, then $subkeyword
		 * can be used to return specific translation from that keyword.
		 *
		 * @param boolean|string $language language keyword, if this is not set then returns current language translations
		 * @param boolean|string $keyword if only single keyword needs to be returned
		 * @param boolean|string $subkeyword if the $keyword is an array, then $subkeyword is the actual translation that is requested
		 * @return array, string or false if failed
		 */
		final public function getTranslations($language=false,$keyword=false,$subkeyword=false){
		
			// If language is not set, then assuming current language
			if(!$language){
				$language=$this->data['language'];
			}
			
			// If translations data is already stored in state
			if(!isset($this->data['translations'][$language])){
			
				// Translations can be loaded from overrides folder as well
				if(file_exists($this->data['directory-system'].'overrides'.DIRECTORY_SEPARATOR.'resources'.DIRECTORY_SEPARATOR.$language.'.translations.ini')){
					$sourceUrl=$this->data['directory-system'].'overrides'.DIRECTORY_SEPARATOR.'resources'.DIRECTORY_SEPARATOR.$language.'.translations.ini';
				} elseif(file_exists($this->data['directory-system'].'resources'.DIRECTORY_SEPARATOR.$language.'.translations.ini')){
					$sourceUrl=$this->data['directory-system'].'resources'.DIRECTORY_SEPARATOR.$language.'.translations.ini';
				} else {
					return false;
				}
				
				// This data can also be stored in cache
				$cacheUrl=$this->data['directory-system'].'filesystem'.DIRECTORY_SEPARATOR.'cache'.DIRECTORY_SEPARATOR.'resources'.DIRECTORY_SEPARATOR.$language.'.translations.tmp';
				// Including the translations file
				if(!file_exists($cacheUrl) || filemtime($sourceUrl)>filemtime($cacheUrl)){
					// Translations are parsed from INI file in the resources folder
					$this->data['translations'][$language]=parse_ini_file($sourceUrl,true,INI_SCANNER_RAW);
					if(!$this->data['translations'][$language]){
						trigger_error('Cannot parse INI file: '.$sourceUrl,E_USER_ERROR);
					}
					// Cache of parsed INI file is stored for later use
					if(!file_put_contents($cacheUrl,serialize($this->data['translations'][$language]))){
						trigger_error('Cannot store INI file cache at '.$cacheUrl,E_USER_ERROR);
					}
				} else {
					// Since INI file has not been changed, translations are loaded from cache
					$this->data['translations'][$language]=unserialize(file_get_contents($cacheUrl));
				}
				
			}
			
			// Returning keyword, if it is requested
			if($keyword){
				// If $keyword is an array and subkeyword is requested
				if($subkeyword){
					if(isset($this->data['translations'][$language][$keyword],$this->data['translations'][$language][$keyword][$subkeyword])){
						return $this->data['translations'][$language][$keyword][$subkeyword];
					} else {
						return false;
					}
				} else {
					if(isset($this->data['translations'][$language][$keyword])){
						return $this->data['translations'][$language][$keyword];
					} else {
						return false;
					}
				}
			} else {
				// If keyword was not set, then returning entire array
				return $this->data['translations'][$language];
			}
			
		}
		
		/**
		 * This method includes or reads in a file from '/resources/content/' folder $name is the 
		 * modified filename that can also include subfolders, but without the language prefix and 
		 * without extension in the filename itself. If $language is not defined then currently 
		 * active language is used.
		 *
		 * @param string $name filename without language prefix
		 * @param boolean|string $language language keyword
		 * @return string
		 */
		final public function getContent($name,$language=false){
		
			// If language is not defined then default language is used
			if(!$language){
				$language=$this->data['language'];
			}
			
			// Initial content folder
			$fileDestination='./resources/content/';
			
			// Folder can be defined in the name
			$components=explode('/',$name);
			$count=count($components);
			
			// If a folder is defined
			if($count>1){
				for($i=0;$i<$count;$i++){
					// If the last node is used
					if($count==($i+1)){
						$fileDestination.=$language.'.'.$components[$i];
					} else {
						$fileDestination.=$components[$i].'/';
					}
				}
			} else {
				$fileDestination.=$language.'.'.$name;
			}
			
			// Adding the extension
			$fileDestination.='.htm';
			
			// Making sure that the file itself exists
			if(file_exists($fileDestination)){
				// Echoing out the contents to output buffer
				return file_get_contents($fileDestination);
			} else {
				trigger_error('Cannot find a file in '.$fileDestination,E_USER_WARNING);
				return false;
			}
			
		}
		
		/**
		 * This method returns an array of currently active sitemap, or a sitemap for a language 
		 * set with $language variable. If $keyword is also set, then it returns a specific 
		 * sitemap node with that keyword from $language sitemap file. This method returns the 
		 * original, non-modified sitemap that has not been parsed for use with URL controller.
		 *
		 * @param boolean|string $language language keyword, if this is not set then returns current language sitemap
		 * @param boolean|string $keyword if only a single URL node needs to be returned
		 * @return array or false if failed
		 */
		final public function getSitemapRaw($language=false,$keyword=false){
		
			// If language is not set, then assuming current language
			if(!$language){
				$language=$this->data['language'];
			}
			
			// If translations data is already stored in state
			if(!isset($this->data['sitemap-raw'][$language])){
			
				// Translations can be loaded from overrides folder as well
				if(file_exists($this->data['directory-system'].'overrides'.DIRECTORY_SEPARATOR.'resources'.DIRECTORY_SEPARATOR.$language.'.sitemap.ini')){
					$sourceUrl=$this->data['directory-system'].'overrides'.DIRECTORY_SEPARATOR.'resources'.DIRECTORY_SEPARATOR.$language.'.sitemap.ini';
				} elseif(file_exists($this->data['directory-system'].'resources'.DIRECTORY_SEPARATOR.$language.'.sitemap.ini')){
					$sourceUrl=$this->data['directory-system'].'resources'.DIRECTORY_SEPARATOR.$language.'.sitemap.ini';
				} else {
					return false;
				}
				
				// This data can also be stored in cache
				$cacheUrl=$this->data['directory-system'].'filesystem'.DIRECTORY_SEPARATOR.'cache'.DIRECTORY_SEPARATOR.'resources'.DIRECTORY_SEPARATOR.$language.'.sitemap.tmp';
				// Including the sitemap file
				if(!file_exists($cacheUrl) || filemtime($sourceUrl)>filemtime($cacheUrl)){
					// Sitemap is parsed from INI file in the resources folder
					$this->data['sitemap-raw'][$language]=parse_ini_file($sourceUrl,true,INI_SCANNER_RAW);
					if(!$this->data['sitemap-raw'][$language]){
						trigger_error('Cannot parse INI file: '.$sourceUrl,E_USER_ERROR);
					}
					// Parsing the sitemap
					foreach($this->data['sitemap-raw'][$language] as $key=>$settings){
						$this->data['sitemap-raw'][$language][$key]['nodes']=explode('/',$key);
					}
					// Cache of parsed INI file is stored for later use
					if(!file_put_contents($cacheUrl,serialize($this->data['sitemap-raw'][$language]))){
						trigger_error('Cannot store INI file cache at '.$cacheUrl,E_USER_ERROR);
					}
				} else {
					// Since INI file has not been changed, translations are loaded from cache
					$this->data['sitemap-raw'][$language]=unserialize(file_get_contents($cacheUrl));
				}
				
			}
			
			// Returning keyword, if it is requested
			if($keyword){
				if(isset($this->data['sitemap-raw'][$language][$keyword])){
					return $this->data['sitemap-raw'][$language][$keyword];
				} else {
					return false;
				}
			} else {
				// If keyword was not set, then returning entire array
				return $this->data['sitemap-raw'][$language];
			}
			
		}
		
		/**
		 * This returns sitemap array that is modified for use with View controller and other 
		 * parts of the system. It returns sitemap for current language or a language set with 
		 * $language variable and can return a specific sitemap node based on $keyword.
		 *
		 * @param boolean|string $language language keyword, if this is not set then returns current language sitemap
		 * @param boolean|string $keyword if only a single URL node needs to be returned
		 * @return array or false if failed
		 */
		final public function getSitemap($language=false,$keyword=false){
		
			// If language is not set, then assuming current language
			if(!$language){
				$language=$this->data['language'];
			}
			
			// If translations data is already stored in state
			if(!isset($this->data['sitemap'][$language])){
			
				// Getting raw sitemap data
				$siteMapRaw=$this->getSitemapRaw($language);
				if(!$siteMapRaw){
					return false;
				}
				
				// This is output array
				$this->data['sitemap'][$language]=array();
				
				// System builds usable URL map for views
				foreach($siteMapRaw as $key=>$node){
					// Only sitemap nodes with set view will be assigned to reference
					if(isset($node['view'])){
						// Since the same view can be referenced in multiple locations
						if(isset($node['subview'])){
							$node['view']=$node['view'].'/'.$node['subview'];
						}
						// This is used only if view has not yet been defined
						if(!isset($this->data['sitemap'][$language][$node['view']])){
							$this->data['sitemap'][$language][$node['view']]=$key;
						}
						// Home views do not need a URL node
						if($node['view']!=$this->data['home-view']){
							if(strpos($key,':')!==false){
								$url='';
								$count=0;
								$bits=explode('/',$key);
								foreach($bits as $b){
									if($b[0]!=':'){
										$url.=$b.'/';
									} else {
										$url.=':'.$count.':/';
										$count++;
									}
								}
							} else {
								$url=$key.'/';
							}
						} else {
							$url='';
						}
						// Storing data from Sitemap file
						$this->data['sitemap'][$language][$node['view']]=$siteMapRaw[$key];
						// If first language URL is not enforced, then this is taken into account
						if($language==$this->data['languages'][0] && $this->data['enforce-first-language-url']==false){
							$this->data['sitemap'][$language][$node['view']]['url']=$this->data['url-web'].$url;
						} else {
							$this->data['sitemap'][$language][$node['view']]['url']=$this->data['url-web'].$language.'/'.$url;
						}
					}
				}
				
			}
			
			// Returning keyword, if it is requested
			if($keyword){
				if(isset($this->data['sitemap'][$language][$keyword])){
					return $this->data['sitemap'][$language][$keyword];
				} else {
					return false;
				}
			} else {
				// If keyword was not set, then returning entire array
				return $this->data['sitemap'][$language];
			}
			
		}
		
	// STATE MESSENGER
	
		/**
		 * This method initializes State messenger by giving it an address and assigning the file 
		 * that State messenger will be stored under. If the file already exists and $overwrite is 
		 * not turned on, then it automatically loads contents of that file from filesystem.
		 *
		 * @param string $address key that messenger data will be saved under
		 * @param boolean $overwrite if this is set then existing state messenger file will be overwritten
		 * @return boolean
		 */
		final public function stateMessenger($address,$overwrite=false){
		
			// If messenger is already set
			if($this->messenger){
				// This stores data to filesystem
				$this->storeMessenger();
				// New messenger data will be empty
				$this->messengerData=array();
			}
			
			// New messenger address is hashed
			$this->messenger=md5($address);
			// Messenger data is stored in a filesystem subfolder
			$dataAddress=$this->data['directory-system'].'filesystem'.DIRECTORY_SEPARATOR.'messenger'.DIRECTORY_SEPARATOR.substr($this->messenger,0,2).DIRECTORY_SEPARATOR.$this->messenger.'.tmp';
			// If this state messenger address already stores data, then it is loaded as the base data
			if(!$overwrite && file_exists($dataAddress)){
				$this->messengerData=unserialize(file_get_contents($dataAddress));
			}
			
			// Messenger is always started
			return true;
			
		}
		
		/**
		 * This writes data to State messenger. $data is the key and $value is the value of the 
		 * key. $data can also be an array of keys and values, in which case multiple values are 
		 * set at the same time.
		 *
		 * @param array|string $data key or data array
		 * @param mixed $value value, if data is a key
		 * @return boolean
		 */
		final public function setMessengerData($data,$value=false){
		
			// If messenger address is set
			if($this->messenger){
				// If data is an array, then it adds data recursively
				if(is_array($data)){
					foreach($data as $key=>$value){
						// Setting messenger data
						$this->messengerData[$key]=$value;
					}
				} else {
					// Setting messenger data
					$this->messengerData[$data]=$value;
				}
				return true;
			} else {
				return false;
			}
			
		}
		
		/**
		 * This method removes key from State messenger based on value of $key. If $key is not 
		 * set, then the entire State messenger data is cleared.
		 *
		 * @param boolean|string $key key that will be removed, if set to false then removes the entire data
		 * @return boolean
		 */
		final public function unsetMessengerData($key=false){
		
			// If messenger address is set
			if($this->messenger && $key){
				if(isset($this->messengerData[$key])){
					unset($this->messengerData[$key]);
					return true;
				} else {
					return false;
				}
			} elseif($this->messenger){
				$this->messengerData=array();	
				return true;
			} else {
				return false;
			}
			
		}
		
		/**
		 * This method returns data from State messenger. It either returns all the data from 
		 * initialized state messenger, or just a $key from it. If $remove is set, then data is 
		 * also set for deletion after it has been accessed.
		 *
		 * @param boolean|string $key data keyword
		 * @param boolean $remove true or false flag whether to delete the request data after returning it
		 * @return mixed or false if failed
		 */
		final public function getMessengerData($key=false,$remove=true){
			
			// If messenger address is set
			if($this->messenger && $key){
				if(isset($this->messengerData[$key])){
					$return=$this->messengerData[$key];
					// If key is set for removal, then unsetting it
					if($remove){
						unset($this->messengerData[$key]);
					}
					return $return;
				} else {
					return false;
				}
			} elseif($this->messenger){
				$return=$this->messengerData;
				// If data is set for removal, then removing the whole data array
				if($remove){
					$this->messengerData=array();
				}
				return $return;
			} else {
				return false;
			}
			
		}
		
		/**
		 * This method simply stores messenger data in filesystem, if there is data to store.
		 * 
		 * @return boolean
		 */
		final private function storeMessenger(){
		
			// Data is only stored if messenger is set
			if($this->messenger){
				// Finding data folder
				$dataFolder=$this->data['directory-system'].'filesystem'.DIRECTORY_SEPARATOR.'messenger'.DIRECTORY_SEPARATOR.substr($this->messenger,0,2).DIRECTORY_SEPARATOR;
				// If there is data to store in messenger
				if(!empty($this->messengerData)){
					// Testing if state messenger folder exists
					if(!is_dir($dataFolder)){
						if(!mkdir($dataFolder,0755)){
							trigger_error('Cannot create messenger folder',E_USER_ERROR);
						}
					}
					// Writing messenger data to file
					if(!file_put_contents($dataFolder.$this->messenger.'.tmp',serialize($this->messengerData))){
						trigger_error('Cannot write messenger data',E_USER_ERROR);
					}
				} elseif(file_exists($dataFolder.$this->messenger.'.tmp')){
					unlink($dataFolder.$this->messenger.'.tmp');
				}
				// Processing complete
				return true;
			} else {
				// Messenger was not set
				return false;
			}
			
		}
		
	// SESSION USER AND PERMISSIONS
	
		/**
		 * This method sets user data array in session. This is a simple helper function used 
		 * for holding user-specific data for a web service. $data is an array of user data.
		 *
		 * @param boolean|array $data data array set to user
		 * @return boolean
		 */
		final public function setUser($data=false){
			
			// If user is not being unset
			if($data){
				// Setting the session
				$this->setSession($this->data['session-user-key'],$data);
				// Setting the state variable
				$this->data['user-data']=$data;
				return true;
			} else {
				// Unsetting the user
				return $this->unsetUser();
			}
			
		}
		
		/**
		 * This either returns the entire user data array or just a specific $key of user data 
		 * from the session.
		 *
		 * @param boolean|string $key element returned from user data, if not set then returns the entire user data
		 * @return mixed
		 */
		final public function getUser($key=false){
		
			// Testing if permissions state has been populated or not
			if(!$this->data['user-data']){
				$this->data['user-data']=$this->getSession($this->data['session-user-key']);
				// If this session key did not exist, then returning false
				if(!$this->data['user-data']){
					return false;
				}
			}
			
			// If key is set
			if($key){
				if(isset($this->data['user-data'][$key])){
					// Returning key data
					return $this->data['user-data'][$key];
				} else {
					return false;
				}
			} else {
				// Returning entire user array
				return $this->data['user-data'];
			}
			
		}
		
		/**
		 * This unsets user data and removes the session of user data.
		 *
		 * @return boolean
		 */
		final public function unsetUser(){
		
			// Unsetting the session
			$this->unsetSession($this->data['session-user-key']);
			// Regenerating session cookie
			$this->regenerateSession(true,true);
			// Unsetting the state variable
			$this->data['user-data']=false;
			return true;
			
		}
		
		/**
		 * This method sets an array of $permissions or a comma-separated string of permissions 
		 * for the current user permissions session.
		 *
		 * @param boolean|array|string $permissions an array or a string of permissions
		 * @return boolean
		 */
		final public function setPermissions($permissions=false){
		
			// If permissions are set
			if($permissions){
				// If permissions were sent as a string
				if(!is_array($permissions)){
					$permissions=explode(',',$permissions);
				}
				// Setting the session variable
				$this->setSession($this->data['session-permissions-key'],$permissions);
				// Setting the state variable
				$this->data['user-permissions']=$permissions;
				return true;
			} else {
				// Unsetting permissions
				return $this->unsetPermissions();
			}
			
		}
		
		/**
		 * This method returns an array of currently set user permissions from the session.
		 *
		 * @return array
		 */
		final public function getPermissions(){
		
			// Testing if permissions state has been populated or not
			if(!$this->data['user-permissions']){
				$this->data['user-permissions']=$this->getSession($this->data['session-permissions-key']);
			}
			return $this->data['user-permissions'];
			
		}
	
		/**
		 * This checks for an existence of permissions in the user permissions session array 
		 * or in the API profile permissions setting. $permissions is either a comma-separated 
		 * string of permissions to be checked, or an array. This method returns false when one 
		 * of those permission keys is not set in the permissions session. Method returns true, 
		 * if $permissions exist in the permissions session array.
		 *
		 * @param string|array $permissions comma-separated string or an array that is checked against permissions array
		 * @return boolean
		 */
		final public function checkPermissions($permissions){
		
			// Public profile permissions are stored in the session
			if($this->data['api-profile']==$this->data['api-public-profile']){
				// Testing if permissions state has been populated or not
				if(!$this->data['user-permissions']){
					$this->data['user-permissions']=$this->getSession($this->data['session-permissions-key']);
					// If this session key did not exist, then returning false
					if(!$this->data['user-permissions']){
						return false;
					}
				}
				$permissionsMatch=$this->data['user-permissions'];
			} else {
				// Private API profile permissions are stored separately
				$permissionsMatch=$this->data['api-permissions'];
			}
			
			// Permissions are handled as an array
			if(!is_array($permissions)){
				$permissions=explode(',',$permissions);
			}

			// If all permissions are set, then permissions will not be separately validated and true is assumed
			if(!in_array('*',$permissionsMatch)){
				foreach($permissions as $p){
					// Returning true or false depending on whether this key exists or not
					if(!in_array($p,$permissionsMatch)){
						return false;
					}
				}
			} else {
				foreach($permissions as $p){
					// Returning false, if one of the permissions is disallowed
					if(in_array('!'.$p,$permissionsMatch)){
						return false;
					}
				}
			}
			
			// Permission check passed
			return true;
			
		}
		
		/**
		 * This unsets permissions data from session similarly to how unsetUser() method unsets 
		 * user data from session.
		 *
		 * @return boolean
		 */
		final public function unsetPermissions(){
		
			// Unsetting the session
			$this->unsetSession($this->data['session-permissions-key']);
			// Regenerating session cookie
			$this->regenerateSession(true,true);
			// Unsetting the state variable
			$this->data['user-permissions']=false;
			return true;
			
		}
		
		/**
		 * This method returns the currently active public token that is used to increase security 
		 * against cross-site-request-forgery attacks. This method returns false if user session 
		 * is not populated, in which case public token is not needed. $regenerate sets if the token 
		 * should be regenerated if it already exists, this invalidates forms when Back button is 
		 * used after submitting data, but is more secure. $forced is used to force token generation 
		 * even if no user session is active.
		 *
		 * @param boolean $regenerate if public token should be regenerated
		 * @param boolean $forced if token is generated even when there is no actual user session active
		 * @return string or boolean if no user session active
		 */
		final public function getPublicToken($regenerate=false,$forced=false){
		
			// This is only required to protect users with active sessions
			if($forced || $this->getUser()){
				// Public token is stored in the session
				$token=$this->getSession('www-public-token');
				if($token && $token!='' && !$regenerate){
					// Returning a token
					return $token;
				} else {
					// Generating a new token
					$token=sha1($this->data['client-ip'].$this->data['client-user-agent'].$this->data['request-id'].microtime());
					$this->setSession($this->data['session-token-key'],$token);
					// Setting it also in sessions
					$this->data['api-public-token']=$token;
					// Returning a token
					return $token;
				}
			} else {
				return false;
			}
			
		}
		
		/**
		 * This method is useful when 'api-public-token' setting is off in configuration, but you
		 * still want to protect your API method from public API requests from XSS and other attacks.
		 * This returns false if the provided public API token is incorrect.
		 *
		 * @return boolean
		 */
		final public function checkPublicToken(){
		
			// This is only checked if the token actually exists and public API profile is used
			if($this->data['api-public-token'] && $this->data['api-public-profile']==$this->data['api-profile']){
				if($this->data['api-public-token']==$this->getSession($this->data['session-token-key'])){
					return true;
				} else {
					return false;
				}
			} else {
				return true;
			}
			
		}
		
	// SESSION AND COOKIES
	
		/**
		 * This method validates current session data and checks for lifetime as well as 
		 * session fingerprint. It notifies session handler if any changes have to be made.
         * Configuration array can have these keys:
         * 'name' - session cookie name
         * 'lifetime' - session cookie lifetime
         * 'path' - URL path for session cookie
         * 'domain' - domain for session cookie
         * 'secure' - whether session cookie is for secure connections only
         * 'http-only' - whether session cookie is for HTTP requests only and unavailable for scripts
         * 'user-key' - session key for storing user data
         * 'permissions-key' - session key for storing user permissions
         * 'fingerprint-key' - session key for storing session fingerprint
         * 'timestamp-key' - session key for storing session timestamp
         * 'token-key' - session key for public request tokens for protecting against replay attacks
         * 'fingerprint' - session key for session fingerprinting for protecting against replay attacks
		 *
		 * @param boolean|array $configuration list of session configuration options
		 * @return boolean
		 */
		final public function startSession($configuration=false){
		
			// If configuration options are sent
			if($configuration && is_array($configuration) && !empty($configuration)){
				// Making sure that only session variables are overwritten
				$sessionConfigurationKeys=array('name','lifetime','path','domain','secure','http-only','user-key','permissions-key','fingerprint-key','timestamp-key','token-key','fingerprint');
				foreach($sessionConfigurationKeys as $key){
					if(isset($configuration[$key])){
						$this->data['session-'.$key]=$configuration[$key];
					}
				}
			}
		
			// Starting sessions if session ID does not exist
			if(!$this->sessionHandler->sessionId){
				// Opening sessions and initializing session data
				$this->sessionHandler->sessionOpen((isset($configuration['session-name']))?$configuration['session-name']:$this->data['session-name']);
			}
			
			// Setting session cookie variables from Configuration
			$this->sessionHandler->sessionCookie(array(
				'lifetime'=>$this->data['session-lifetime'],
				'path'=>$this->data['session-path'],
				'domain'=>$this->data['session-domain'],
				'secure'=>$this->data['session-secure'],
				'http-only'=>$this->data['session-http-only'],
			));
			
			// This can regenerate the session ID, if enough time has passed
			if($this->data['session-regenerate']){
				if(!isset($this->data['session-data'][$this->data['session-timestamp-key']])){
					// Storing a session creation time in sessions
					$this->data['session-data'][$this->data['session-timestamp-key']]=$this->data['request-time'];
				} elseif($this->data['request-time']>($this->data['session-data'][$this->data['session-timestamp-key']]+$this->data['session-regenerate'])){
					// Regenerating the session ID
					$this->sessionHandler->regenerateId=true;
					$this->sessionHandler->regenerateRemoveOld=true;
					// Storing a session creation time in sessions
					$this->data['session-data'][$this->data['session-timestamp-key']]=$this->data['request-time'];
				}
			}
			
			// If session fingerprinting is used
			if($this->data['session-fingerprint'] && $this->data['fingerprint']){
				if(!isset($this->data['session-data'][$this->data['session-fingerprint-key']])){
					// Storing session fingerprint in sessions
					$this->data['session-data'][$this->data['session-fingerprint-key']]=$this->data['fingerprint'];
				} elseif($this->data['session-data'][$this->data['session-fingerprint-key']]!=$this->data['fingerprint']){
					// Regenerating the session ID
					$this->sessionHandler->regenerateId=true;
					$this->sessionHandler->regenerateRemoveOld=false;
					// Emptying the session array
					$this->data['session-data']=array();
				}
			}
			
			// Session validation complete
			$this->sessionStarted=true;
			return true;
			
		}
		
		/**
		 * This method notifies the session handler that the session data should be
		 * stored under a new ID.
		 *
		 * @param boolean $regenerate to regenerate or not
		 * @param boolean $deleteOld deletes the previous one, if set to true
		 * @return boolean
		 */
		final public function regenerateSession($regenerate=true,$deleteOld=true){
		
			// Making sure that sessions have been started
			if(!$this->sessionStarted){
				$this->startSession();
			}
			$this->sessionHandler->regenerateId=$regenerate;
			$this->sessionHandler->regenerateRemoveOld=$deleteOld;
			return true;
			
		}
		
		/**
		 * This method sets a session variable $key with a $value. If $key is an array of 
		 * keys and values, then multiple session variables are set at once.
		 *
		 * @param boolean|array|string $key key of the variable or an array of keys and values
		 * @param mixed $value value to be set
		 * @return boolean
		 */
		final public function setSession($key=false,$value=false){
		
			// Making sure that sessions have been started
			if(!$this->sessionStarted){
				$this->startSession();
			}
			// Multiple values can be set if key is an array
			if(is_array($key)){
				foreach($key as $k=>$v){
					// setting value based on key
					$this->data['session-data'][$k]=$v;
				}
			} elseif($key){
				// Setting value based on key
				$this->data['session-data'][$key]=$value;
			} else {
				return false;
			}
			return true;
			
		}
		
		/**
		 * This method returns $key value from session data. If $key is an array of keys, then 
		 * it can return multiple variables from session at once. If $key is not set, then entire 
		 * session array is returned. Method returns null for keys that have not been set.
		 *
		 * @param boolean|string|array $key key to return or an array of keys
		 * @return mixed
		 */
		final public function getSession($key=false){
		
			// Making sure that sessions have been started
			if(!$this->sessionStarted){
				$this->startSession();
			}
			
			// Multiple keys can be returned
			if(is_array($key)){
				// This array will hold multiple values
				$return=array();
				foreach($key as $val){
					// Getting value based on key
					if(isset($this->data['session-data'][$val])){
						$return[$val]=$this->data['session-data'][$val];
					} else {
						$return[$val]=null;
					}
				}
				return $return;
			} elseif($key){
				// Return data from specific key
				if(isset($this->data['session-data'][$key])){
					return $this->data['session-data'][$key];
				} else {
					return null;
				}
			} else {
				// Return entire session data, if key was not set
				return $this->data['session-data'];
			}
			
		}
		
		/**
		 * This method unsets $key value from current session. If $key is an array of keys, then 
		 * multiple variables can be unset at once. If $key is not set at all, then this simply 
		 * destroys the entire session.
		 *
		 * @param boolean|string|array $key key of the value to be unset, or an array of keys
		 * @return boolean
		 */
		final public function unsetSession($key=false){
		
			// Making sure that sessions have been started
			if(!$this->sessionStarted){
				$this->startSession();
			}
			
			// Can unset multiple values
			if(is_array($key)){
				foreach($key as $value){
					unset($this->data['session-data'][$value]);
				}
			} elseif($key){
				unset($this->data['session-data'][$key]);
			} else {
				// Entire session data is unset
				$this->data['session-data']=array();
				// Session cookie will be regenerated if new data is stored
				$this->sessionHandler->regenerateId=true;
				$this->sessionHandler->regenerateRemoveOld=true;
			}
			return true;
			
		}
		
		/**
		 * This method sets a cookie with $key and a $value. $configuration is an array of 
		 * cookie parameters that can be set.
		 *
		 * @param string|array $key key of the variable, or an array of keys and values
		 * @param string|array $value value to be set, can also be an array
		 * @param array $configuration cookie configuration options, list of keys below
		 *  'expire' - timestamp when the cookie is set to expire
		 *  'timeout' - alternative to 'expire', this sets how long the cookie can survive
		 *  'path' - cookie limited to URL path
		 *  'domain' - cookie limited to domain
		 *  'secure' - whether cookie is for secure connections only
		 *  'http-only' - if the cookie is only available for HTTP requests
		 * @return boolean
		 */
		final public function setCookie($key,$value,$configuration=array()){
		
			// Checking for configuration options
			if(!isset($configuration['expire'])){
				if(isset($configuration['timeout'])){
					$configuration['expire']=$this->data['request-time']+$configuration['timeout'];
				} else {
					$configuration['expire']=2147483647;
				}
			}
			if(!isset($configuration['path'])){
				$configuration['path']=$this->data['url-web'];
			}
			if(!isset($configuration['domain'])){
				$configuration['domain']=$this->data['http-host'];
			}
			if(!isset($configuration['secure'])){
				$configuration['secure']=false;
			}
			if(!isset($configuration['http-only'])){
				$configuration['http-only']=true;
			}
			
			// Can set multiple values
			if(is_array($key)){
				// Value can act as a configuration
				if(is_array($value)){
					$configuration=$value;
				}
				foreach($key as $k=>$v){
					// Value can be an array, in which case the values set will be an array
					if(is_array($v)){
						foreach($v as $index=>$val){
							setcookie($k.'['.$index.']',$val,$configuration['expire'],$configuration['path'],$configuration['domain'],$configuration['secure'],$configuration['http-only']);
							// Cookie values can be accessed immediately after they are set
							$_COOKIE[$k][$index]=$val;
						}
					} else {
						// Setting the cookie
						setcookie($k,$v,$configuration['expire'],$configuration['path'],$configuration['domain'],$configuration['secure'],$configuration['http-only']);
						// Cookie values can be accessed immediately after they are set
						$_COOKIE[$k]=$v;
					}
				}
			} else {
				// Value can be an array, in which case the values set will be an array
				if(is_array($value)){
					foreach($value as $index=>$val){
						setcookie($key.'['.$index.']',$val,$configuration['expire'],$configuration['path'],$configuration['domain'],$configuration['secure'],$configuration['http-only']);
						// Cookie values can be accessed immediately after they are set
						$_COOKIE[$key][$index]=$val;
					}
				} else {
					// Setting the cookie
					setcookie($key,$value,$configuration['expire'],$configuration['path'],$configuration['domain'],$configuration['secure'],$configuration['http-only']);
					// Cookie values can be accessed immediately after they are set
					$_COOKIE[$key]=$value;
				}
			}
			
		}
		
		/**
		 * This method returns a cookie value with the set $key. $key can also be an array of 
		 * keys, in which case multiple cookie values are returned in an array. Method returns null 
		 * for keys that have not been set.
		 *
		 * @param string $key key of the value to be returned, can be an array
		 * @return mixed
		 */
		final public function getCookie($key){
		
			// Multiple keys can be returned
			if(is_array($key)){
				// This array will hold multiple values
				$return=array();
				foreach($key as $val){
					if(isset($_COOKIE[$val])){
						$return[$val]=$_COOKIE[$val];
					} else {
						$return[$val]=false;
					}
				}
				return $return;
			} else {
				// Returning single cookie value
				if(isset($_COOKIE[$key])){
					return $_COOKIE[$key];
				} else {
					return null;
				}
			}
			
		}
		
		/**
		 * This method unsets a cookie with the set key of $key. If $key is an array, then 
		 * it can remove multiple cookies at once.
		 *
		 * @param string|array $key key of the value to be unset or an array of keys
		 * @return boolean
		 */
		final public function unsetCookie($key){
		
			// Can set multiple values
			if(is_array($key)){
				foreach($key as $value){
					if(isset($_COOKIE[$value])){
						// Removes cookie by setting its duration to 0
						setcookie($value,'',($this->data['request-time']-3600));
						// Unsets the cookie value
						unset($_COOKIE[$value]);
					}
				}
			} else {
				if(isset($_COOKIE[$key])){
					// Removes cookie by setting its duration to 0
					setcookie($key,'',($this->data['request-time']-3600));
					// Unsets the cookie value
					unset($_COOKIE[$value]);
				} else {
					return false;
				}
			}
			return true;
			
		}
		
	// HEADERS
	
		/**
		 * This method adds a header to the array of headers to be added before data is pushed 
		 * to the client, when headers are sent. $header is the header string to add and $replace 
		 * is a true/false setting for whether previously sent header like this is replaced or not.
		 *
		 * @param string|array $header header string to add or an array of header strings
		 * @param boolean $replace whether the header should be replaced, if previously set
		 * @return boolean
		 */
		final public function setHeader($header,$replace=true){
		
			// Multiple headers can be set at once
			if(is_array($header)){
				foreach($header as $h){
					if($h!=''){
						// Removing the header from unset array
						unset($this->data['headers-unset'][$h]);
						// Assigning the setting to headers array
						$this->data['headers-set'][$h]=$replace;
					}
				}
			} else {
				if($header!=''){
					// Removing the header from unset array
					unset($this->data['headers-unset'][$header]);
					// Assigning the setting to headers array
					$this->data['headers-set'][$header]=$replace;
				}
			}
			return true;
			
		}
	
		/**
		 * This method adds a header to the array of headers to be removed before data is pushed 
		 * to the client, when headers are sent. $header is the header string to remove.
		 *
		 * @param string|array $header header string to add or an array of header strings
		 * @return boolean
		 */
		final public function unsetHeader($header){
		
			// Multiple headers can be unset at once
			if(is_array($header)){
				foreach($header as $h){
					if($h!=''){
						// Removing the header from unset array
						unset($this->data['headers-set'][$h]);
						// Assigning the setting to headers array
						$this->data['headers-unset'][$h]=true;
					}
				}
			} else {
				if($header!=''){
					// Unsetting the header, if previously set
					unset($this->data['headers-set'][$header]);
					// Assigning the setting to headers array
					$this->data['headers-unset'][$header]=true;
				}
			}
			return true;
			
		}
		
	// TOOLS
	
		/**
		 * This method loads tools object if it doesn't exist yet and then allows to 
		 * call various methods of the tool. You can call filesystem cleaner, indexer
		 * or a file-size calculator (and each work recursively).
		 *
		 * @param string $type the type of tool to be loaded
		 * @param mixed $arg1 additional parameter for the tool
		 * @param mixed $arg2 additional parameter for the tool
		 * @return mixed based on the tool
		 */
		final public function callTool($type,$arg1=false,$arg2=false){
		
			// If tool does not exist yet
			if(!$this->tools){
		
				// Loading the Imager class
				if(!class_exists('WWW_Tools')){
					require($this->data['directory-system'].'engine'.DIRECTORY_SEPARATOR.'class.www-tools.php');
				}
				
				// Creating a new tool object
				$this->tools=new WWW_Tools();
				
			}
		
			// Calling the method based on selected tool
			switch($type){
				case 'cleaner':
					// This variable defines the cut-off time of the cleaner
					if($arg2){
						return $this->tools->cleaner($arg1,$arg2);
					} else {
						return $this->tools->cleaner($arg1);
					}
					break;
				case 'indexer':
					// This variable defines the mode of the index method
					if($arg2){
						return $this->tools->indexer($arg1,$arg2);
					} else {
						return $this->tools->indexer($arg1);
					}
					break;
				case 'sizer':
					// Takes just the directory variable
					return $this->tools->sizer($arg1);
					break;
				case 'counter':
					// Takes just the directory variable
					return $this->tools->counter($arg1);
					break;
				default:
					trigger_error('This tool does not exist: '.$type,E_USER_WARNING);
					return false;
					break;
			}
		
		}
	
	// TERMINAL
	
		/**
		 * This method is wrapper function for making terminal calls. It attempts to detect 
		 * what terminal is available on the system, if any, and then execute the call and 
		 * return the results of the call.
		 *
		 * @param string $command command to be executed
		 * @return mixed
		 */
		final public function terminal($command){
		
			// Status variable
			$status=1;
		
			// Checking all possible terminal functions
			if(function_exists('system')){
				ob_start();
				system($command,$status);
				$output=ob_get_contents();
				ob_end_clean();
			} elseif(function_exists('passthru')){
				ob_start();
				passthru($command,$status);
				$output=ob_get_contents();
				ob_end_clean();
			} elseif(function_exists('exec')){
				exec($command,$output,$status);
				$output=implode("\n",$output);
			} elseif(function_exists('shell_exec')){
				$output=shell_exec($command);
			} else {
				// No function was available, returning false
				return false;
			}

			// Returning result
			return array('output'=>$output,'status'=>$status);
			
		}
	
}
	
?>