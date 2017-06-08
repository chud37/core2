<?	
#//      .g8"""bgd        .g8""8q. 	   	   `7MM"""Mq.  		`7MM"""YMM		//#  
#//     .dP'     `M     .dP'    `YM.		 MM   `MM. 		  MM    `7      //#
#//     dM'       `     dM'      `MM		 MM   ,M9  		  MM   d        //#
#//     MM              MM        MM		 MMmmdM9   		  MMmmMM        //#
#//     MM.             MM.      ,MP		 MM  YM.   		  MM   Y  ,     //#
#//     `Mb.     ,'     `Mb.    ,dP'		 MM   `Mb. 		  MM     ,M     //# 
#//      `"bmmmd'         `"bmmd"' 	   	   .JMML. .JMM.		.JMMmmmmMMM     //#

$version_history = array(
	"2.59"	=> 	"Added USES_AUTHENTICATION and USES_DATABASE constants. Set them to false to build a website without authentication and database connectivity.",
	"2.58"	=>  "Fixed mkdir(); bug in core2.php",
	"2.57"	=>	"Added in the ability to set \$_SESSION['msgs'] to display messages via the session.",
	"2.56"	=> 	"Fixed an issue where .head.js was loaded twice. ",
	"2.55"	=>	"\$page.cfg.php now loads before the other config files, bringing order to the loading process.",
	"2.54"	=>	"Fixed an error that enabled index.php to load inside itself in core_functions/routing().",
	"2.53"	=>	"/manage_core_settings can now be accessed by both developer IP or login level.",
	"2.52"	=>	"Added function appendlog(\$str, \$logfile)",
	"2.51"	=>	"Added login_standalone setting, to determine whether or not to show headers/footers when displaying \$auth->form_display().  Default: true.",
	"2.50"	=>	"/manage-core-settings access defaults to IP.",
	"2.49"	=>	"You can now choose how to access /manage-core-settings, via IP or login level.",
	"2.48"	=>	"Manage Core Settings is now available to users with an access level of 100, instead of a specific IP.",
	"2.47"	=>	"Added 503 error pages, and new setting 'Under Maintenance' to restrict public access.",
	"2.46"	=>	"Reintroduced \$page variable.",
	"2.45"	=>	"Debug mode can be set outside of the core2.php file.",
	"2.44"	=>	"Added Current Build to /manage-core-settings",
	"2.43"	=>	"Added the ability to reset settings.ini",
	"2.42"	=>	"Added the ability to modify the CSS and JS core files from the INI files.",
	"2.41"	=>	"Updated /manage-core-settings and moved all functionality to /lib/",
	"2.40"	=>	"Optimized routing() to load a page from /lib/ if neccessary.",
	"2.39"	=>	"Added User Defined Constants.",
	"2.38"	=>	"Added \$core_files['foot'] to load JS at the end of the files.",
	"2.37"	=>	"Added ability to load default.head.php from the [build] directory.",
	"2.36"	=>	"Added setting wrapper=[true|false].  Defines whether to ouput header/content/footer tags.",
	"2.35"	=>	"\$core_settings['salt'] was not set by default.",
	"2.34"	=>	"Updated \$version_history and CORE_VERSION constant.",
	"2.33"	=>	"Added /manage-core-settings page.",
	"2.32"	=>	"Added \$core_paths['lib'].",
	"2.31"	=>	"Added core_mod.php functionality.",
	"2.30"	=>	"Core can now be loaded from remote directory.  Meaning that you can install one instance of core on the server, multiple domains/subdomains can access it.",
	"2.29"	=>	"Removed \$core_dirs['lib'], /etc/lib, /etc/classes, /etc/functions are now hardcoded.",	
	"2.28"	=>	"Added \$core_paths variable.",
	"2.27"	=>	"Print stylesheet feature now available.",
	"2.26"	=>	"Version history now saved and accessible.",
	"2.25"	=>	"Added global_dirs, loads global files if they are found, otherwise searches for local files.",
	"2.24"	=>	"Function primary_checks() introduced, checks for build directories and creates them if they dont exist.",
	"2.23"	=>	"Logged all error messages to error log in fail().  Improved file not found error messages.",
	"2.22"	=>	"Added Swiftmail support",
	"2.21"	=>	"Modified load_site_settings to load faster.",
	"2.20"	=>	"Added support for /bin/[build]/css and /bin/[build]/js.");
	
	// Constants
	define("ROOT",			$_SERVER['DOCUMENT_ROOT']);		// The document root
	define("IP",			$_SERVER['REMOTE_ADDR']);		// The user's IP
	define("CORE_VERSION", 	key($version_history));			// Core Version #
	define("STRICT", 		false);							// STRICT mode kills the page on any error encountered.
	if(!defined("CORE_ONLY")) 	define("CORE_ONLY", false);	// CORE_ONLY to load Core Settings only and don't render a page.  Useful for Ajax routines etc.
	if(!defined("_DEBUG_")) 	define("_DEBUG_", true);	// Debug mode On/Off
	if(!defined("CORE_URL")) 	define("CORE_URL", false);	// CORE_URL for outside access to the core settings.
	
	if(!defined("USES_AUTHENTICATION")) define("USES_AUTHENTICATION", false);	// CORE_URL for outside access to the core settings.
	if(!defined("USES_DATABASE")) 		define("USES_DATABASE", false);			// CORE_URL for outside access to the core settings.
	
	// Core Variables
	$root 	= 	ROOT;
	
	$core_paths	=	array(	"local"		=>	ROOT,		// Local Directory, where /bin is kept.
							"core"		=>  __DIR__,	// Core directory, where /classes /functions and /lib is kept.
							"lib"		=> __DIR__ . "/lib"
						  );
	$core_dirs 	= 	array(	"bin"		=> 	"/pages/",
							"build" 	=> 	"/pages/[build]/", 
							"ini" 		=> 	"/pages/[build]/ini/",
							"css" 		=> 	"/pages/[build]/css/",
							"plugins"	=> 	"/pages/[build]/plugins",
							"js" 		=> 	"/pages/[build]/js/",                                                             
							"logs" 		=> 	"/pages/[build]/logs/",
							"errors" 	=> 	"/pages/[build]/logs/errors/",
							"img"		=>	"/pages/[build]/img/",
							"lib"		=> 	"/core/lib/"
						);
	$core_settings = array(	"version" 	=> CORE_VERSION, 
							"home" 		=> "home", 
							"login" 	=> "login",
							"detach" 	=> false,
							"meta"		=> array("site_name" => "", "site_title" => "", "site_description" => "", "site_keyword" => "", "site_type" => "", "site_url" => "", "site_image" => "", "site_image_type" => "", "site_image_height" => ""),
							"site_name" => "v".CORE_VERSION,
							"title" 	=> "Core ",
							"salt"		=> "chud37",
							"wrappers"	=> true,
							"favicon" 	=> "/favicon.ico",
							"og" 		=> array(""),
							"allowed"	=> array("upload" => array(), "download" => array()),
							"constants"	=> array(),
							"error_email" 		=> 	false, 
							"under-maintenance" => 	false,
							"developerIP"		=> 	array($_SERVER['SERVER_ADDR']),
							"admin_access"		=> 	"ip",
							"login_standalone"	=> 	true,
							"site_version"		=>	"",
							"force_homepage" 	=> 	false
						);
	$page 			=	false;
	$core_files 	= 	array(	"scripts" 	=> array("head"	=>	array(	"jquery" => "https://ajax.googleapis.com/ajax/libs/jquery/2.2.2/jquery.min.js",
																		"bootstrap" => "https://maxcdn.bootstrapcdn.com/bootstrap/3.3.2/js/bootstrap.min.js"),
													 "foot"	=>	array()),
								"styles" 	=> array(	"font-awesome" => "https://maxcdn.bootstrapcdn.com/font-awesome/4.5.0/css/font-awesome.min.css",
														"bootstrap" => "https://maxcdn.bootstrapcdn.com/bootstrap/3.3.2/css/bootstrap.min.css",
														"print" => "https://maxcdn.bootstrapcdn.com/bootstrap/3.3.2/css/bootstrap.min.css"),
								"config" 	=> array(), 'head' => array(), 'foot' => array(), 'headers' 	=> array(), 'footers' 	=> array(),
								"root"	=>	ROOT, 
								"dir" => $core_dirs['bin'], 
								"requested" => $core_settings['home'], 
								"received" => ""
							  );
	$core_error 	= 	array(	"html" 	=> false, 
								"PDO" => false, 
								"level" => false, 
								"message" => false, 
								"file" => false, 
								"line" => false
							  );
	
	$msgs = (isset($_SESSION['msgs']) ? $_SESSION['msgs'] : array());    		# Messages array, displayed by messages(); function.  Loads $_SESSION['msgs'] if it is set.
	if(isset($_SESSION['msgs'])) $_SESSION['msgs'] = array();					# Remove all messages from $_SESSION['msgs'].
		             
	#===========================================================================
	#	Core LOAD: Begin by loading Core functions and classes.
	#	Prepare to render the page.
	#===========================================================================
			
	// Load Core functionality & other function files (either from global directory or local)
	require_once($core_paths['core']."/functions/core-functions.php");
	inc($core_paths['core']."/functions");
	                                         
	if(is_file(ROOT."/vendor/autoload.php")) {require_once(ROOT."/vendor/autoload.php");}
	
	// If the user wants to modify the Core Variables in anyway, or include scripts before the Core starts, 
	// they can create a file called core_mod.php in their etc.
	if(is_file(ROOT . "/core-mod.php")) {include(ROOT . "/core-mod.php");}
	
	// Set Custom Error Handler
	set_error_handler("fail", E_ALL);
	
	// Error Log File
	if(!is_dir(ROOT.$core_dirs['logs'])) {mkdir(ROOT.$core_dirs['logs'],0777,true);}
	if(!is_dir(ROOT.$core_dirs['errors'])) {mkdir(ROOT.$core_dirs['errors'],0777,true);}
	ini_set("error_log", ROOT.$core_dirs['errors'].date("z: D jS M y",time()).".txt");
	
	// Set Class Autoloader    
	spl_autoload_register("class_autoloader");

	#===========================================================================
	#	Core START: Variables and Primary functions are all loaded by this point.
	#	Begin website generation.
	#===========================================================================
	
	// Core Directory checks
	primary_checks();
	
	// Load site settings from INI file (Found in /bin/[build]/ini/settings.ini
	load_site_settings();
	
	// Database Class
	if(USES_DATABASE) $db = new Database("database");
	
	// Authentication Class
	if(USES_AUTHENTICATION) {
		$auth = new Authentication("authentication");
	} else {
		$auth = new Authentication("authentication", false);	
	}
	
	// Die if CORE_ONLY is true.
	if(!CORE_ONLY) {
		// routing() examines the REQUEST_URI.  Requires Authentication object.
		routing($auth);             
		// Render the webpage using the given files defined from routing().
		if(is_file(ROOT . "/{$core_dirs['build']}/core_render_page.php")) {
			// Use the custom page renderer.
			include(ROOT . "/{$core_dirs['build']}/core_render_page.php");
		} else {
			// Use the default page renderer.
			include("/{$core_paths['lib']}/core_render_page.php");
		}
	}
?>