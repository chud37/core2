<?
	#	-----------------------------------------------------------------
	#	Core2 Initialization
	#
	#	Core2 Website Structure written by Chud37
	#	Cloned from https://github.com/chud37/core2.git
	#	Website: http://chud37.com/code
	#	Email: hello@chud37.com
	#	-----------------------------------------------------------------
	
	#Set the absolute path to the core file here: 
	define("CORE_PATH", __DIR__."/core/core2.php");
	
	#Use the authentication class to connect with an Auth Database. (Credentials specified in /bin/[build]/ini/authentication.ini)
	define("USES_AUTHENTICATION", false);
	
	#Use the database class to connect with a database. (Credentials specified in /bin/[build]/ini/database.ini)
	define("USES_DATABASE", false);
		
	#Turn Debug mode on/off.
	define("_DEBUG_", true);
	
	#[PHP Log Errors]
	ini_set("log_errors", 1);	
	
	#[PHP Error Reporting (32767 is E_ALL)]
	error_reporting(32767);
	
	#[PHP Display Errors]
	ini_set('display_errors', 'On');
	
	#[Set the Timezone]
	date_default_timezone_set("Europe/London");
	
	session_start();
	require_once(CORE_PATH);

?>