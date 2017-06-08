<?
##  		       _    _                   _    _               _                
//	   _ _  _   _ | |_ | |__    ___  _ __  | |_ (_)  ___   __ _ | |_   ___   _ __ 
##	 / _` || | | || __|| '_ \  / _ \| '_ \ | __|| | / __| / _` || __| / _ \ | '__|
//	| (_| || |_| || |_ | | | ||  __/| | | || |_ | || (__ | (_| || |_ | (_) || |   
##	 \__,_| \__,_| \__||_| |_| \___||_| |_| \__||_| \___| \__,_| \__| \___/ |_| by chud37. 
##	v2 - (w)OOP!

class Authentication {
	public $version = "2.40";
	public $version_history = array(
	"2.40"	=>	"Added COOKIE_EXPIRATION constant.",
	"2.39"	=>	"Added saveUserData() function.",
	"2.38"	=>	"Added hasAccess() function.",
	"2.37"	=>	"Made hashpassword() public.",
	"2.36"	=>	"Post_URLS are now gone, everything is done with set URLS with \$pageroot.",
	"2.35"	=>	"Added the ability to pass a URL to form_display()",
	"2.34"	=>	"Fixed isset() error on line 480.",
	"2.33"	=>	"Fixed activity() error.",
	"2.32"	=>	"User can now logout by hyperlinking to /logout",
	"2.31" 	=> 	"Added \$version_history variable for public access.",
	"2.30" 	=> 	"Added functionality to extend Authentication (used in Activation)",
	"2.29" 	=> 	"Added \$this->console to login screen.",
	"2.28" 	=> 	"Created \$this->output, a single error message from all functions to notify the user.",
	"2.27" 	=> 	"Merged \AWMPass with Authentication object.",
	"2.26" 	=> 	"Added checks and alternate INI file loading facility.",
	"2.25" 	=> 	"Prepared the statements in activity(), notes() and get_me()",
	"2.24" 	=> 	"Streamlined construct and error reporting.",
	"2.23" 	=> 	"Fixed errors in __construct and removed is_file(ini) test",
	"2.22" 	=> 	"Updated to use Database class instead of an independant database connection.",
	"2.12" 	=> 	"Made \$locked_access public standalone variable",
	"2.11" 	=> 	"Fixed minor errors regarding \$locked_access and DB connection.",
	"2.10" 	=> 	"Reverted 'Authentication' back to type 'class'");
	
	// Set the cookie expiration time to 2 weeks.  Users have 2 weeks to log back in again until it forgets them.
	const COOKIE_EXPIRATION = 1209600;
	
	private $uri = array();      
		                                          
	protected $email = false;
	protected $password = false;

	protected $db; 							# Authentication DB Object
	public $authCredentials = array();		# Authentication Credentials
	protected $form_status = false;			# Internal status for forms.
	protected $swiftmail = false;
	                                                           
	public $locked_access = false;	# TRUE if the entire website is accessible by login only.
	public $access = false;			# TRUE if user has access to locked areas.
	public $me = false;				# $me is the array within which we hold all the user data.
	public $salt = false;			# Salt for salting passwords
	public $page_access = array("unlocked" => array(), "locked" => array());
	public $page_access_levels = array("login" => 0);
	public $redirect = false;		# If set to TRUE, the login form will redirect to current URI.
	
	public $create_auto_login = false;	# After creating account, automatically log the user in?
	         
	public $hiddenvars = array();	# If you want to insert any hidden variables inside the forms, add them here as array ($key => $value)
	
	// Messages
	private $errors = array();		# Internal error messages
	public $output = false;			# Returned single success/error message
	public $console = array();		# For debugging, console messages.
	
	// Styling
	public $container = false;				# Wrap forms in containers?
	public $img	= false;					# Specify header image for forms.
	public $display_create_account = true;	# Whether or not to display create account link, and if a string, the URL for the link.
	public $container_style = array(	"login"	=>		"border-radius: 4px;border: 2px solid #FFF;max-width:450px;margin-top:15vh;background: rgba(255, 255, 255, 0.6);",
										"create"	=>	"border-radius: 4px;border: 2px solid #FFF;max-width:450px;margin-top:15vh;background: rgba(255, 255, 255, 0.8);",
										"forgot"	=>	"border-radius: 4px;border: 2px solid #FFF;max-width:520px;margin-top:15vh;background: rgba(255, 255, 255, 0.8);");
	public $email_files		= array(	"forgotten_password_reset" => false, "forgotten_password_request" => false);
	public $transparent = false;	
	
	// URLs
	public $post_url = array(	"login" 	=>	"/login",
								"create"	=>	"/login/create-account",
								"forgot"	=>	"/login/forgotten-password"
							);
	public $pageroot = "";          
	
	public $forgot_success_url = false;

	public function __construct($auth_ini_file = false, $process_data = true) {
		global $settings, $swiftmail;

		// Define our own URI array here.
		$tmp_exp = explode("?",$_SERVER['REQUEST_URI']);
		$tmp_uri = explode("/",substr($tmp_exp[0],1));
		for($x=0;$x<10;$x++) {if(!isset($tmp_uri[$x])) {$this->uri[$x] = false;} else {$this->uri[$x] = $tmp_uri[$x];}}
		
		$this->swiftmail = $swiftmail;
		if(!$auth_ini_file) {
			if(STRICT) trigger_error("No authentication initialization file provided.");
			$this->output = $this->console[] = "No authentication initialization file provided.";
			return false;
		}
		
		$this->email = 		(isset($_REQUEST['email']) ? strtolower(trim($_REQUEST['email'])) : false);
		if($this->email) 	{$this->output = $this->console[] = "Email address: <b>{$this->email}</b>";}
		$this->password = 	(isset($_REQUEST['password']) ? $this->hashpassword($_REQUEST['password']) : false);
		$this->salt = 		(isset($settings['salt']) ? $settings['salt'] : "chud37");
				
		if(is_file($auth_ini_file)) {
			$ini = parse_ini_file($auth_ini_file, true);
		} else {
			if(function_exists("load_ini_file")) {
				$ini = load_ini_file($auth_ini_file, true);	
			} else {
				$this->output = $this->console[] = "Function load_ini_file() not found.";
				trigger_error("Function load_ini_file() not found.");	
			}
		}
		                    
		
		// This is only useful for INI file management.
		if(isset($ini['Authentication Credentials']['host'])) 		{$this->authCredentials['host'] 	= $ini['Authentication Credentials']['host'];     	} else {$this->authCredentials['host'] = $databaseID['host'];}
		if(isset($ini['Authentication Credentials']['username'])) 	{$this->authCredentials['username'] = $ini['Authentication Credentials']['username'];	} else {$this->authCredentials['username'] = $databaseID['username'];}
		if(isset($ini['Authentication Credentials']['password'])) 	{$this->authCredentials['password'] = $ini['Authentication Credentials']['password'];	} else {$this->authCredentials['password'] = $databaseID['password'];}
		
		// We need the database and table name to be specific in our queries. 
		if(isset($ini['Authentication Credentials']['database'])) 	{$this->authCredentials['database'] = $ini['Authentication Credentials']['database'];	} else {$this->authCredentials['database'] = false;}   
		if(isset($ini['Authentication Credentials']['table'])) 		{$this->authCredentials['table'] 	= $ini['Authentication Credentials']['table'];		} else {$this->authCredentials['table'] = "users";}   
		                    
		if(isset($ini['Page Access Levels'])) 	{$this->page_access_levels = $ini['Page Access Levels'];}
		if((isset($ini['Authentication Credentials']['unlocked']))		&& (is_array($ini['Authentication Credentials']['unlocked']))) 		$this->page_access['unlocked'] 	= $ini['Authentication Credentials']['unlocked']; 
		if((isset($ini['Authentication Credentials']['locked'])) 		&& (is_array($ini['Authentication Credentials']['locked'])))		$this->page_access['locked'] 	= $ini['Authentication Credentials']['locked']; 
		if(isset($ini['Authentication Credentials']['locked_access'])) {$this->locked_access = $ini['Authentication Credentials']['locked_access'];} 

		if(isset($ini['Email Files']['forgotten_password_reset'])) 		{$this->email_files['forgotten_password_reset'] = $ini['Email Files']['forgotten_password_reset'];} 
		if(isset($ini['Email Files']['forgotten_password_request'])) 	{$this->email_files['forgotten_password_request'] = $ini['Email Files']['forgotten_password_request'];} 

		
		if($process_data) {
			
			if(!$this->authCredentials['database']) {
				$this->output = $this->console[] = "Database name not set.";
				trigger_error("Database name not set.");
			}
			
			// Establish an individual connection to the database, as it's a one time thing.
			$this->db = new Database($auth_ini_file);
					
			if(!$this->db) {
				$this->output = $this->console[] = "Database object not initialized.";
				return false;
			}
	
			$this->console[] = "Successfully loaded Authentication v{$this->version}, settings and database initialized.";
			
			// If the Authentication object is extended, we won't neccessarily want to run this part of the code.
			$authentication = (isset($_REQUEST['authenticate']) ? $_REQUEST['authenticate'] : false);
			$authentication = (isset($_REQUEST['auth']) ? $_REQUEST['auth'] : $authentication);
			
			// The forgotten-password reset tool is done entirely by a URL.  We'll check that now with very specifc rules to catch it should it occur.
			if(($this->uri[1] == "forgotten-password") && ($this->uri[2] == "reset") && (strlen($this->uri[3]) == 64)) $authentication = "forgotten_password_reset";
			
			// This enables the user to logout by simply adding /logout to anywhere in the URI.
			foreach($this->uri as $u) {if($u == "logout") $authentication = "logout";}
		
			if($authentication) $this->console[] = "Authentication action: <b>{$authentication}</b>";
		
			switch($authentication) {
				case "login": 						$this->login(); 						break;
				case "logout": 						$this->logout(); 						break;
				case "create_account":				$this->create_account();				break;
				case "forgotten_password_request":	$this->forgotten_password_request();	break;
				case "forgotten_password_reset":	$this->forgotten_password_reset();		break;
				case false: default:				$this->recognise(); 					break;
			}
		}
		// Construct defines:
		//	$me, $access, $error
	
	}
	private function recognise() {
		//	Check if user has a cookie or a session variable set, if so log them in.
		$hash = false;
		$this->console[] = "Searching for hash...";
		if(isset($_SESSION[$_SERVER['SERVER_NAME']])) {
			$hash = $_SESSION[$_SERVER['SERVER_NAME']];
			$this->console[] = "Authentication found In <b>Session</b> [<b>$hash</b>]";
		} elseif(isset($_COOKIE["#AWMPass"])) {
			$hash = $_COOKIE["#AWMPass"];
			$this->console[] = "Authentication found In <b>Cookie</b> [<b>{$_SERVER['SERVER_NAME']}</b>] hash: [<b>$hash</b>]";
		}                                            
		if($hash) {
			$this->console[] = "Found a hash, checking against database..";
			//	Check Hash against database.
			$this->db->prepared = array("hash" => $hash);
			if($this->db->select("SELECT `hash` FROM `{$this->authCredentials['database']}`.`{$this->authCredentials['table']}` WHERE `hash`=:hash;",true)) 
			{
				if($this->locked_access) {
					$this->console[] = "Locked access set to <b>true</b>, validating credentials..";
					$this->db->prepared = array("hash" => $hash,"server_name" => $_SERVER['SERVER_NAME']);
					$check = $this->db->select("SELECT `{$this->authCredentials['database']}`.`{$this->authCredentials['table']}`.`id`,`hash`,`sessionID`,`email` FROM `{$this->authCredentials['database']}`.`{$this->authCredentials['table']}`
						LEFT JOIN `{$this->authCredentials['database']}`.`access` ON `{$this->authCredentials['database']}`.`access`.`userID` = `{$this->authCredentials['database']}`.`{$this->authCredentials['table']}`.`id`
						WHERE `hash`=:hash
						AND `{$this->authCredentials['database']}`.`access`.`domain` = :server_name
						AND `{$this->authCredentials['database']}`.`access`.`level` >= '1';",true);
				} else {
					$this->console[] = "Locked access set to <b>false</b>, validating credentials..";
					$this->db->prepared = array("hash" => $hash);
					$check = $this->db->select("SELECT `{$this->authCredentials['database']}`.`{$this->authCredentials['table']}`.`id`,`sessionID`,`hash`,`email` FROM `{$this->authCredentials['database']}`.`{$this->authCredentials['table']}` WHERE `hash`=:hash;",true);
				}
				
				if($check) 
				{
					$this->console[] = "Found in database (<b>{$check['email']}</b>)";
					$this->console[] = "Loading user data..";
					$this->me = $this->get_me($check['id']);
					$this->console[] = "Saving cookie..";
					setcookie("#AWMPass", $check['hash'], time() + (self::COOKIE_EXPIRATION), '/', $this->get_top_domain_name());
					$this->console[] = "Successfully authenticated user against database.";
					$this->console[] = "Updating timestamp for userID {$this->me['id']}.";
					$this->db->prepared = array("servername" => str_replace("www.","",$_SERVER['SERVER_NAME']), "hash" => $hash);
					$this->db->runSQL("UPDATE `{$this->authCredentials['database']}`.`{$this->authCredentials['table']}` SET `lastseen` = NOW(), `domain`=:servername WHERE `hash`=:hash;",false,"authentication",true);
					$this->access = true;
				} else {                                  
					$this->error = $this->find_auth_error();
				}                                                     
			} else {
				$this->console[] = "Hash ({$hash}) was not valid.";
			}
		} else {
			$this->console[] = "<b>No hash found</b> in <i>Session</i> or <i>Cookie</i>";
		}
	}
	private function login($email = false, $password = false) {
		//	User is trying to login with (email) and (password).
			
		if(($email) && ($password)) {
			$this->email = $email;
			$this->password = $this->hashpassword($password);
		}       
		
		if(($this->email) && ($this->password) && (filter_var($this->email, FILTER_VALIDATE_EMAIL)))  {
			// Check database for user.
			if($this->locked_access) {
				$this->db->prepared = array("email" => $this->email, "password" => $this->password, "server_name" => $_SERVER['SERVER_NAME']);
				$check = $this->db->select("SELECT `{$this->authCredentials['database']}`.`{$this->authCredentials['table']}`.`id`,`hash`,`email` FROM `{$this->authCredentials['database']}`.`{$this->authCredentials['table']}`
						LEFT JOIN `{$this->authCredentials['database']}`.`access` ON `{$this->authCredentials['database']}`.`access`.`userID` = `{$this->authCredentials['database']}`.`{$this->authCredentials['table']}`.`id`
						WHERE `email` = :email 
						AND `password` = :password
						AND `{$this->authCredentials['database']}`.`access`.`domain` = :server_name
						AND `{$this->authCredentials['database']}`.`access`.`level` >= '1';",true);
			} else {
				$this->db->prepared = array("email" => $this->email, "password" => $this->password);
				$check = $this->db->select("SELECT `{$this->authCredentials['database']}`.`{$this->authCredentials['table']}`.`id`,`hash`,`email` FROM `{$this->authCredentials['database']}`.`{$this->authCredentials['table']}` WHERE `email` = :email AND `password` = :password;",true);
			}
			         
				
			if($check) 
			{
				// Successfully logged the user in.
				$this->console[] = "Successfully authenticated username and password.";
				$this->console[] = "Session ID: ".session_id();
				$_SESSION[$_SERVER['SERVER_NAME']] = $check['hash'];
				$this->db->prepared = array("servername" => str_replace("www.","",$_SERVER['SERVER_NAME']),"id" => $this->me['id']);
				$this->db->runSQL("UPDATE `{$this->authCredentials['database']}`.`{$this->authCredentials['table']}` SET `lastseen` = NOW(), `domain`=:servername WHERE `id`=:id;");
				if(isset($_POST['remember_me'])) 
				{
					$this->console[] = "User clicked 'Remember Me'";
					setcookie("#AWMPass", $check['hash'], time() + (self::COOKIE_EXPIRATION), '/', $this->get_top_domain_name());
					$this->console[] = "Authentication saved into cookie.";
				}
				$this->access = true;      
				// Populate $this->me
				$this->me = $this->get_me($check['id']);
				$this->activity("User #{$this->me['id']} ({$this->me['email']}) successfully logged in.",4);
				$this->console[] = "User #{$this->me['id']} ({$this->me['email']}) successfully logged in.";
				$this->output = ":User #{$this->me['id']} ({$this->me['email']}) successfully logged in.";
			} else {
				// Failed to login the user in.
				$this->output = $this->find_auth_error($this->email,$this->password);
			}
		} else {
			$this->console[] = "Could not attempt a valid login.";
			if(!$this->password) {
				$this->output = "Please type your password.";
				$this->console[] = "Password not found.";
			}
			if(!$this->email) {
				$this->output = "Please type your email address.";
				$this->console[] = "E-Mail address not found.";
			}
			if(!filter_var($this->email, FILTER_VALIDATE_EMAIL)) {
				$this->output = "Please type a valid email address.";
				$this->console[] = "{$this->email} is not a valid email address.";
			}
		}
	}
	private function logout() {
		// Perform Logout procedure.
		$hash = false;
		if(isset($_SESSION[$_SERVER['SERVER_NAME']])) {
			$hash = $_SESSION[$_SERVER['SERVER_NAME']];
		} elseif(isset($_COOKIE["#AWMPass"])) {
			$hash = $_COOKIE["#AWMPass"];
		}
		$name = false;
		if($hash) {
			$this->me = $this->db->select("SELECT `id`,`name` FROM `{$this->authCredentials['database']}`.`{$this->authCredentials['table']}` WHERE `hash`='".$hash."';",true);
			if($this->me) {
				$this->activity("User #{$this->me['id']} ({$this->me['name']}) logged out.",3);
				$this->console[] = "User #{$this->me['id']} ({$this->me['name']}) logged out.";
				$name = ucwords($this->me['name']);
				$this->db->prepared = array("servername" => $_SERVER['SERVER_NAME'], "id" => $this->me['id']);
				$this->db->runSQL("UPDATE `{$this->authCredentials['database']}`.`{$this->authCredentials['table']}` SET `lastseen` = NOW(), `domain`=:servername WHERE `id`=:id;");
			}
		}                                                                               
		$this->console[] = "Regenerating Session..";
		session_regenerate_id();
		unset($_SESSION[$_SERVER['SERVER_NAME']]);
		$this->console[] = "Erasing Cookie..";
		setcookie("#AWMPass", "", time()-1000, '/', $this->get_top_domain_name());
		$this->console[] = "Nullifying \$me..";
		$this->me = $this->access = false;
		$this->output = $this->console[] = ":<b>{$name}</b> Successfully logged out.";
		
	}
	private function create_account() {
		$name = (isset($_POST['name']) ? $_POST['name'] : false);
		$email = (isset($this->email) ? $this->email : false);
		
		// The actual password inserted is hashed in __construct().  These variables are simply for error checking (blank, etc).
		$password = (isset($_POST['password']) ? $_POST['password'] : false);
		$confirmpassword = (isset($_POST['confirm-password']) ? $_POST['confirm-password'] : false);
		
		// Set to false until we can be sure the form is correct.
		$this->form_status = false;
		
		// Generate unique URL for this user
		// $url_test = str_replace(array("  "," "),"_",strtolower(preg_replace("/[^A-Za-z0-9 ]/", '', $name)));
		// $count = 0;
		// do {
		// 	$checkURL = $this->db->select("SELECT `url` FROM `{$this->authCredentials['database']}`.`{$this->authCredentials['table']}` WHERE `url` = '{$url_test}';",true);
		// 	$count++;
		// 	if($checkURL) $url_test .= $count;
		// } while($checkURL);
		
		// Generate unique hash for this user.
		$hash_test = hash("sha256",$email.time());
		do {
			$checkHASH = $this->db->select("SELECT `hash` FROM `{$this->authCredentials['database']}`.`{$this->authCredentials['table']}` WHERE `hash` = '{$hash_test}';",true);
			if($checkHASH) $hash_test = hash("sha256",$email.time());
		} while($checkHASH);
		
		// Check their email address is valid.
		if(!(filter_var($email, FILTER_VALIDATE_EMAIL))) {
			$this->errors['email'] = "<b>{$email}</b> is not a valid email address.";
		}
		if($email == "") $this->console[] = $this->errors['email'] = "Email address is required.";
		
		
		// Check the email address is free.
		$checkEMAIL = $this->db->select("SELECT `email` FROM `{$this->authCredentials['database']}`.`{$this->authCredentials['table']}` WHERE `email`='{$email}';",true);
		if($checkEMAIL) {
			$this->console[] = $this->errors['email'] = "There is already a user with the email address <b>{$email}</b>";	
		}
		
		// Verify the google captcha
		if(function_exists("recaptcha")) {
			if(recaptcha() == false) {
				$this->console[] = $this->errors['recaptcha'] = "You have entered an invalid captcha.";
			}
		}

		// Small error checks
		if($name == "") {$this->console[] = $this->errors['name'] = "Name cannot be left blank.";}
		if($password == "") {$this->console[] = $this->errors['password'] = "Password cannot be left blank.";}
		if($password != $confirmpassword) {$this->console[] = $this->errors['password'] = "Password and confirm password must match.";}
		                                                                                                       
		if(!$this->errors) {
			// No errors, create the new user in the database and email user.         
			$this->db->prepared = array(
					"name" => $this->authFriendlyName($name),
					"email" => strtolower($email),
					"password" => $this->password,
					"servername" => $_SERVER['SERVER_NAME'],
					"sendmail" => (isset($_POST['send-mail']) ? "1" : "0"),
					"hash" => $hash_test
				);               
			$sql = "INSERT INTO `{$this->authCredentials['database']}`.`{$this->authCredentials['table']}` (`name`,`email`,`password`,`domain`,`sendmail`,`hash`) VALUES (:name,:email,:password,:servername,:sendmail,:hash);";
			$userID = $this->db->runSQL($sql);
			
			if($userID) {
				$this->activity(strtolower($email)." created an account",10,$userID);
				$mailbody = "<!DOCTYPE html PUBLIC '-//W3C//DTD XHTML 1.0 Transitional//EN' 'http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd'><html xmlns='http://www.w3.org/1999/xhtml'><head> <title></title> <meta http-equiv='Content-Type' content='text/html; charset=utf-8'/> <style type='text/css'> body{margin:0;mso-line-height-rule:exactly;padding:0;min-width:100%}table{border-collapse:collapse;border-spacing:0}td{padding:0;vertical-align:top}.border,.spacer{font-size:1px;line-height:1px}.spacer{width:100%}img{border:0;-ms-interpolation-mode:bicubic}.image{font-size:12px;Margin-bottom:24px;mso-line-height-rule:at-least}.image img{display:block}.logo{mso-line-height-rule:at-least}.logo img{display:block}strong{font-weight:700}h1,h2,h3,li,ol,p,ul{Margin-top:0}li,ol,ul{padding-left:0}blockquote{Margin-top:0;Margin-right:0;Margin-bottom:0;padding-right:0}.column-top{font-size:32px;line-height:32px}.column-bottom{font-size:8px;line-height:8px}.column{text-align:left}.contents{table-layout:fixed;width:100%}.padded{padding-left:32px;padding-right:32px;word-break:break-word;word-wrap:break-word}.wrapper{display:table;table-layout:fixed;width:100%;min-width:620px;-webkit-text-size-adjust:100%;-ms-text-size-adjust:100%}table.wrapper{table-layout:fixed}.one-col,.three-col,.two-col{width:600px}.centered{Margin-left:auto;Margin-right:auto}.two-col .image{Margin-bottom:23px}.two-col .column-bottom{font-size:9px;line-height:9px}.two-col .column{width:300px}.three-col .image{Margin-bottom:21px}.three-col .column-bottom{font-size:11px;line-height:11px}.three-col .column{width:200px}.three-col .first .padded{padding-left:32px;padding-right:16px}.three-col .second .padded{padding-left:24px;padding-right:24px}.three-col .third .padded{padding-left:16px;padding-right:32px}@media only screen and (min-width:0){.wrapper{text-rendering:optimizeLegibility}}@media only screen and (max-width:620px){[class=wrapper]{min-width:318px!important;width:100%!important}[class=wrapper] .one-col,[class=wrapper] .three-col,[class=wrapper] .two-col{width:318px!important}[class=wrapper] .column,[class=wrapper] .gutter{display:block;float:left;width:318px!important}[class=wrapper] .padded{padding-left:32px!important;padding-right:32px!important}[class=wrapper] .block{display:block!important}[class=wrapper] .hide{display:none!important}[class=wrapper] .image{margin-bottom:24px!important}[class=wrapper] .image img{height:auto!important;width:100%!important}}.wrapper h1{font-weight:700}.wrapper h2{font-style:italic;font-weight:400}.wrapper h3{font-weight:400}.one-col blockquote,.three-col blockquote,.two-col blockquote{font-style:italic}.one-col-feature h1{font-weight:400}.one-col-feature h2{font-style:normal;font-weight:700}.one-col-feature h3{font-style:italic}td.border{width:1px}tr.border{background-color:#e9e9e9;height:1px}tr.border td{line-height:1px}.one-col,.one-col-feature,.three-col,.two-col{background-color:#fff;font-size:14px;table-layout:fixed}.footer,.header,.one-col,.one-col-feature,.preheader,.three-col,.two-col{Margin-left:auto;Margin-right:auto}.preheader table{width:602px}.preheader .title,.preheader .webversion{padding-top:10px;padding-bottom:12px;font-size:12px;line-height:21px}.preheader .title{text-align:left}.preheader .webversion{text-align:right;width:300px}.header{width:602px}.header .logo{padding:32px 0}.header .logo div{font-size:26px;font-weight:700;letter-spacing:-.02em;line-height:32px}.header .logo div a{text-decoration:none}.header .logo div.logo-center{text-align:center}.header .logo div.logo-center img{Margin-left:auto;Margin-right:auto}.gmail{width:650px;min-width:650px}.gmail td{font-size:1px;line-height:1px}.wrapper a{text-decoration:underline;transition:all .2s}.wrapper h1{font-size:36px;Margin-bottom:18px}.wrapper h2{font-size:26px;line-height:32px;Margin-bottom:20px}.wrapper h3{font-size:18px;line-height:22px;Margin-bottom:16px}.wrapper h1 a,.wrapper h2 a,.wrapper h3 a{text-decoration:none}.one-col blockquote,.three-col blockquote,.two-col blockquote{font-size:14px;border-left:2px solid #e9e9e9;Margin-left:0;padding-left:16px}table.divider{width:100%}.divider .inner{padding-bottom:24px}.divider table{background-color:#e9e9e9;font-size:2px;line-height:2px;width:60px}.wrapper .gray{background-color:#f7f7f7}.wrapper .gray blockquote{border-left-color:#ddd}.wrapper .gray .divider table{background-color:#ddd}.padded .image{font-size:0}.image-frame{padding:8px}.image-background{display:inline-block;font-size:12px}.btn{Margin-bottom:24px;padding:2px}.btn a{border:1px solid #fff;display:inline-block;font-size:13px;font-weight:700;line-height:15px;outline-style:solid;outline-width:2px;padding:10px 30px;text-align:center;text-decoration:none!important}.one-col .column table:nth-last-child(2) td h1:last-child,.one-col .column table:nth-last-child(2) td h2:last-child,.one-col .column table:nth-last-child(2) td h3:last-child,.one-col .column table:nth-last-child(2) td ol:last-child,.one-col .column table:nth-last-child(2) td p:last-child,.one-col .column table:nth-last-child(2) td ul:last-child{Margin-bottom:24px}.one-col ol,.one-col p,.one-col ul{font-size:16px;line-height:24px}.one-col ol,.one-col ul{Margin-left:18px}.two-col .column table:nth-last-child(2) td h1:last-child,.two-col .column table:nth-last-child(2) td h2:last-child,.two-col .column table:nth-last-child(2) td h3:last-child,.two-col .column table:nth-last-child(2) td ol:last-child,.two-col .column table:nth-last-child(2) td p:last-child,.two-col .column table:nth-last-child(2) td ul:last-child{Margin-bottom:23px}.two-col .image-frame{padding:6px}.two-col h1{font-size:26px;line-height:32px;Margin-bottom:16px}.two-col h2{font-size:20px;line-height:26px;Margin-bottom:18px}.two-col h3{font-size:16px;line-height:20px;Margin-bottom:14px}.two-col ol,.two-col p,.two-col ul{font-size:14px;line-height:23px}.two-col ol,.two-col ul{Margin-left:16px}.two-col li{padding-left:5px}.two-col .divider .inner{padding-bottom:23px}.two-col .btn{Margin-bottom:23px}.two-col blockquote{padding-left:16px}.three-col .column table:nth-last-child(2) td h1:last-child,.three-col .column table:nth-last-child(2) td h2:last-child,.three-col .column table:nth-last-child(2) td h3:last-child,.three-col .column table:nth-last-child(2) td ol:last-child,.three-col .column table:nth-last-child(2) td p:last-child,.three-col .column table:nth-last-child(2) td ul:last-child{Margin-bottom:21px}.three-col .image-frame{padding:4px}.three-col h1{font-size:20px;line-height:26px;Margin-bottom:12px}.three-col h2{font-size:16px;line-height:22px;Margin-bottom:14px}.three-col h3{font-size:14px;line-height:18px;Margin-bottom:10px}.three-col ol,.three-col p,.three-col ul{font-size:12px;line-height:21px}.three-col ol,.three-col ul{Margin-left:14px}.three-col li{padding-left:6px}.three-col .divider .inner{padding-bottom:21px}.three-col .btn{Margin-bottom:21px}.three-col .btn a{font-size:12px;line-height:14px;padding:8px 19px}.three-col blockquote{padding-left:16px}.one-col-feature .column-top{font-size:36px;line-height:36px}.one-col-feature .column-bottom{font-size:4px;line-height:4px}.one-col-feature .column{text-align:center;width:600px}.one-col-feature .column table:nth-last-child(2) td h1:last-child,.one-col-feature .column table:nth-last-child(2) td h2:last-child,.one-col-feature .column table:nth-last-child(2) td h3:last-child,.one-col-feature .column table:nth-last-child(2) td ol:last-child,.one-col-feature .column table:nth-last-child(2) td p:last-child,.one-col-feature .column table:nth-last-child(2) td ul:last-child,.one-col-feature .image{Margin-bottom:32px}.one-col-feature h1,.one-col-feature h2,.one-col-feature h3{text-align:center}.one-col-feature h1{font-size:52px;Margin-bottom:22px}.one-col-feature h2{font-size:42px;Margin-bottom:20px}.one-col-feature h3{font-size:32px;line-height:42px;Margin-bottom:20px}.one-col-feature ol,.one-col-feature p,.one-col-feature ul{font-size:21px;line-height:32px;Margin-bottom:32px}.one-col-feature ol a,.one-col-feature p a,.one-col-feature ul a{text-decoration:none}.one-col-feature p{text-align:center}.one-col-feature ol,.one-col-feature ul{Margin-left:40px;text-align:left}.one-col-feature li{padding-left:3px}.one-col-feature .btn{Margin-bottom:32px;text-align:center}.one-col-feature .divider .inner{padding-bottom:32px}.one-col-feature blockquote{border-bottom:2px solid #e9e9e9;border-left-color:#fff;border-left-width:0;border-left-style:none;border-top:2px solid #e9e9e9;Margin-bottom:32px;Margin-left:0;padding-bottom:42px;padding-left:0;padding-top:42px;position:relative}.one-col-feature blockquote:after,.one-col-feature blockquote:before{background:-moz-linear-gradient(left,#fff 25%,#e9e9e9 25%,#e9e9e9 75%,#fff 75%);background:-webkit-gradient(linear,left top,right top,color-stop(25%,#fff),color-stop(25%,#e9e9e9),color-stop(75%,#e9e9e9),color-stop(75%,#fff));background:-webkit-linear-gradient(left,#fff 25%,#e9e9e9 25%,#e9e9e9 75%,#fff 75%);background:-o-linear-gradient(left,#fff 25%,#e9e9e9 25%,#e9e9e9 75%,#fff 75%);background:-ms-linear-gradient(left,#fff 25%,#e9e9e9 25%,#e9e9e9 75%,#fff 75%);background:linear-gradient(to right,#fff 25%,#e9e9e9 25%,#e9e9e9 75%,#fff 75%);content:'';display:block;height:2px;left:0;outline:#fff solid 1px;position:absolute;right:0}.one-col-feature blockquote:before{top:-2px}.one-col-feature blockquote:after{bottom:-2px}.one-col-feature blockquote ol,.one-col-feature blockquote p,.one-col-feature blockquote ul{font-size:42px;line-height:48px;Margin-bottom:48px}.one-col-feature blockquote ol:last-child,.one-col-feature blockquote p:last-child,.one-col-feature blockquote ul:last-child{Margin-bottom:0!important}.footer{width:602px}.footer .padded{font-size:12px;line-height:20px}.social{padding-top:32px;padding-bottom:22px}.social img{display:block}.social .divider{font-family:sans-serif;font-size:10px;line-height:21px;text-align:center;padding-left:14px;padding-right:14px}.social .social-text{height:21px;vertical-align:middle!important;font-size:10px;font-weight:700;text-decoration:none;text-transform:uppercase}.social .social-text a{text-decoration:none}.address{width:250px}.address .padded{text-align:left;padding-left:0;padding-right:10px}.subscription{width:350px}.subscription .padded{text-align:right;padding-right:0;padding-left:10px}.address,.subscription{padding-top:32px;padding-bottom:64px}.address a,.subscription a{font-weight:700;text-decoration:none}.address table,.subscription table{width:100%}@media only screen and (max-width:651px){.gmail{display:none!important}}@media only screen and (max-width:620px){[class=wrapper] .one-col .column:last-child table:nth-last-child(2) td h1:last-child,[class=wrapper] .one-col .column:last-child table:nth-last-child(2) td h2:last-child,[class=wrapper] .one-col .column:last-child table:nth-last-child(2) td h3:last-child,[class=wrapper] .one-col .column:last-child table:nth-last-child(2) td ol:last-child,[class=wrapper] .one-col .column:last-child table:nth-last-child(2) td p:last-child,[class=wrapper] .one-col .column:last-child table:nth-last-child(2) td ul:last-child,[class=wrapper] .one-col-feature .column:last-child table:nth-last-child(2) td h1:last-child,[class=wrapper] .one-col-feature .column:last-child table:nth-last-child(2) td h2:last-child,[class=wrapper] .one-col-feature .column:last-child table:nth-last-child(2) td h3:last-child,[class=wrapper] .one-col-feature .column:last-child table:nth-last-child(2) td ol:last-child,[class=wrapper] .one-col-feature .column:last-child table:nth-last-child(2) td p:last-child,[class=wrapper] .one-col-feature .column:last-child table:nth-last-child(2) td ul:last-child,[class=wrapper] .three-col .column:last-child table:nth-last-child(2) td h1:last-child,[class=wrapper] .three-col .column:last-child table:nth-last-child(2) td h2:last-child,[class=wrapper] .three-col .column:last-child table:nth-last-child(2) td h3:last-child,[class=wrapper] .three-col .column:last-child table:nth-last-child(2) td ol:last-child,[class=wrapper] .three-col .column:last-child table:nth-last-child(2) td p:last-child,[class=wrapper] .three-col .column:last-child table:nth-last-child(2) td ul:last-child,[class=wrapper] .two-col .column:last-child table:nth-last-child(2) td h1:last-child,[class=wrapper] .two-col .column:last-child table:nth-last-child(2) td h2:last-child,[class=wrapper] .two-col .column:last-child table:nth-last-child(2) td h3:last-child,[class=wrapper] .two-col .column:last-child table:nth-last-child(2) td ol:last-child,[class=wrapper] .two-col .column:last-child table:nth-last-child(2) td p:last-child,[class=wrapper] .two-col .column:last-child table:nth-last-child(2) td ul:last-child{Margin-bottom:24px!important}[class=wrapper] .address,[class=wrapper] .subscription{display:block;float:left;width:318px!important;text-align:center!important}[class=wrapper] .address{padding-bottom:0!important}[class=wrapper] .subscription{padding-top:0!important}[class=wrapper] h1{font-size:36px!important;line-height:42px!important;Margin-bottom:18px!important}[class=wrapper] h2{font-size:26px!important;line-height:32px!important;Margin-bottom:20px!important}[class=wrapper] h3{font-size:18px!important;line-height:22px!important;Margin-bottom:16px!important}[class=wrapper] ol,[class=wrapper] p,[class=wrapper] ul{font-size:16px!important;line-height:24px!important;Margin-bottom:24px!important}[class=wrapper] ol,[class=wrapper] ul{Margin-left:18px!important}[class=wrapper] li{padding-left:2px!important}[class=wrapper] blockquote{padding-left:16px!important}[class=wrapper] .two-col .column:nth-child(n+3){border-top:1px solid #e9e9e9}[class=wrapper] .btn{margin-bottom:24px!important}[class=wrapper] .btn a{display:block!important;font-size:13px!important;font-weight:700!important;line-height:15px!important;padding:10px 30px!important}[class=wrapper] .column-bottom{font-size:8px!important;line-height:8px!important}[class=wrapper] .first .column-bottom,[class=wrapper] .second .column-top,[class=wrapper] .third .column-top,[class=wrapper] .three-col .second .column-bottom{display:none}[class=wrapper] .image-frame{padding:4px!important}[class=wrapper] .header .logo{padding-left:10px!important;padding-right:10px!important}[class=wrapper] .header .logo div{font-size:26px!important;line-height:32px!important}[class=wrapper] .header .logo div img{display:inline-block!important;max-width:280px!important;height:auto!important}[class=wrapper] .footer,[class=wrapper] .header,[class=wrapper] .webversion,[class=wrapper] table.border{width:320px!important}[class=wrapper] .header .logo a,[class=wrapper] .preheader .webversion{text-align:center!important}[class=wrapper] .border td,[class=wrapper] .preheader table{width:318px!important}[class=wrapper] .border td.border{width:1px!important}[class=wrapper] .image .border td{width:auto!important}[class=wrapper] .title{display:none}[class=wrapper] .footer .padded{text-align:center!important}[class=wrapper] .footer .subscription .padded{padding-top:20px!important}[class=wrapper] .footer .social-link{display:block!important}[class=wrapper] .footer .social-link table{margin:0 auto 10px!important}[class=wrapper] .footer .divider{display:none!important}[class=wrapper] .one-col-feature .btn,[class=wrapper] .one-col-feature .image{margin-bottom:28px!important}[class=wrapper] .one-col-feature .divider .inner{padding-bottom:28px!important}[class=wrapper] .one-col-feature h1{font-size:42px!important;line-height:48px!important;margin-bottom:20px!important}[class=wrapper] .one-col-feature h2{font-size:32px!important;line-height:36px!important;margin-bottom:18px!important}[class=wrapper] .one-col-feature h3{font-size:26px!important;line-height:32px!important;margin-bottom:20px!important}[class=wrapper] .one-col-feature ol,[class=wrapper] .one-col-feature p,[class=wrapper] .one-col-feature ul{font-size:20px!important;line-height:28px!important;margin-bottom:28px!important}[class=wrapper] .one-col-feature blockquote{font-size:18px!important;line-height:26px!important;margin-bottom:28px!important;padding-bottom:26px!important;padding-left:0!important;padding-top:26px!important}[class=wrapper] .one-col-feature blockquote ol,[class=wrapper] .one-col-feature blockquote p,[class=wrapper] .one-col-feature blockquote ul{font-size:26px!important;line-height:32px!important}[class=wrapper] .one-col-feature blockquote ol:last-child,[class=wrapper] .one-col-feature blockquote p:last-child,[class=wrapper] .one-col-feature blockquote ul:last-child{margin-bottom:0!important}[class=wrapper] .one-col-feature .column table:last-of-type h1:last-child,[class=wrapper] .one-col-feature .column table:last-of-type h2:last-child,[class=wrapper] .one-col-feature .column table:last-of-type h3:last-child{margin-bottom:28px!important}}@media only screen and (max-width:320px){[class=wrapper] td.border{display:none}[class=wrapper] .footer,[class=wrapper] .header,[class=wrapper] .webversion,[class=wrapper] table.border{width:318px!important}}</style><!--[if gte mso 9]> <style>.column-top{mso-line-height-rule: exactly !important;}</style><![endif]--> <meta name='robots' content='noindex,nofollow'/><meta property='og:title' content='My First Campaign'/></head> <body style='margin: 0;mso-line-height-rule: exactly;padding: 0;min-width: 100%;background-color: #fbfbfb'><style type='text/css'>@import url(https://fonts.googleapis.com/css?family=Open+Sans:400italic,700italic,700,400);body,.wrapper,.emb-editor-canvas{background-color:#fbfbfb}.border{background-color:#e9e9e9}h1{color:#5489f5}.wrapper h1{}.wrapper h1{font-family:sans-serif}@media only screen and (min-width: 0){.wrapper h1{font-family:'Open Sans',sans-serif !important}}h1{}.one-col h1{line-height:44px}.two-col h1{line-height:34px}.three-col h1{line-height:28px}.wrapper .one-col-feature h1{line-height:58px}@media only screen and (max-width: 620px){h1{line-height:44px !important}}h2{color:#555}.wrapper h2{}.wrapper h2{font-family:Georgia,serif}h2{}.one-col h2{line-height:32px}.two-col h2{line-height:26px}.three-col h2{line-height:22px}.wrapper .one-col-feature h2{line-height:52px}@media only screen and (max-width: 620px){h2{line-height:32px !important}}h3{color:#5c93e6}.wrapper h3{}.wrapper h3{font-family:sans-serif}h3{}.one-col h3{line-height:26px}.two-col h3{line-height:22px}.three-col h3{line-height:20px}.wrapper .one-col-feature h3{line-height:40px}@media only screen and (max-width: 620px){h3{line-height:26px !important}}p,ol,ul{color:#565656}.wrapper p,.wrapper ol,.wrapper ul{}.wrapper p,.wrapper ol,.wrapper ul{font-family:Georgia,serif}p,ol,ul{}.one-col p,.one-col ol,.one-col ul{line-height:25px;Margin-bottom:25px}.two-col p,.two-col ol,.two-col ul{line-height:23px;Margin-bottom:23px}.three-col p,.three-col ol,.three-col ul{line-height:21px;Margin-bottom:21px}.wrapper .one-col-feature p,.wrapper .one-col-feature ol,.wrapper .one-col-feature ul{line-height:32px}.one-col-feature blockquote p,.one-col-feature blockquote ol,.one-col-feature blockquote ul{line-height:50px}@media only screen and (max-width: 620px){p,ol,ul{line-height:25px !important;Margin-bottom:25px !important}}.image{color:#565656}.image{font-family:Georgia,serif}.wrapper a{color:#41637e}.wrapper a:hover{color:#30495c !important}.wrapper .logo div{color:#41637e}.wrapper .logo div{font-family:sans-serif}@media only screen and (min-width: 0){.wrapper .logo div{font-family:Avenir,sans-serif !important}}.wrapper .logo div a{color:#41637e}.wrapper .logo div a:hover{color:#41637e !important}.wrapper .one-col-feature p a,.wrapper .one-col-feature ol a,.wrapper .one-col-feature ul a{border-bottom:1px solid #41637e}.wrapper .one-col-feature p a:hover,.wrapper .one-col-feature ol a:hover,.wrapper .one-col-feature ul a:hover{color:#30495c !important;border-bottom:1px solid #30495c !important}.btn a{}.wrapper .btn a{}.wrapper .btn a{font-family:Georgia,serif}.wrapper .btn a{background-color:#41637e;color:#fff !important;outline-color:#41637e;text-shadow:0 1px 0 #3b5971}.wrapper .btn a:hover{background-color:#3b5971 !important;color:#fff !important;outline-color:#3b5971 !important}.preheader .title,.preheader .webversion,.footer .padded{color:#999}.preheader .title,.preheader .webversion,.footer .padded{font-family:Georgia,serif}.preheader .title a,.preheader .webversion a,.footer .padded a{color:#999}.preheader .title a:hover,.preheader .webversion a:hover,.footer .padded a:hover{color:#737373 !important}.footer .social .divider{color:#e9e9e9}.footer .social .social-text,.footer .social a{color:#999}.wrapper .footer .social .social-text,.wrapper .footer .social a{}.wrapper .footer .social .social-text,.wrapper .footer .social a{font-family:Georgia,serif}.footer .social .social-text,.footer .social a{}.footer .social .social-text,.footer .social a{letter-spacing:0.05em}.footer .social .social-text:hover,.footer .social a:hover{color:#737373 !important}.image .border{background-color:#c8c8c8}.image-frame{background-color:#dadada}.image-background{background-color:#f7f7f7}</style> <center class='wrapper' style='display: table;table-layout: fixed;width: 100%;min-width: 620px;-webkit-text-size-adjust: 100%;-ms-text-size-adjust: 100%;background-color: #fbfbfb'> <table class='gmail' style='border-collapse: collapse;border-spacing: 0;width: 650px;min-width: 650px'><tbody><tr><td style='padding: 0;vertical-align: top;font-size: 1px;line-height: 1px'>&nbsp;</td></tr></tbody></table> <table class='preheader centered' style='border-collapse: collapse;border-spacing: 0;Margin-left: auto;Margin-right: auto'> <tbody><tr> <td style='padding: 0;vertical-align: top'> <table style='border-collapse: collapse;border-spacing: 0;width: 602px'> <tbody><tr> <td class='title' style='padding: 0;vertical-align: top;padding-top: 10px;padding-bottom: 12px;font-size: 12px;line-height: 21px;text-align: left;color: #999;font-family: Georgia,serif'>Welcome to your AWMPass&nbsp;</td><td style='padding: 0;vertical-align: top;padding-top: 10px;padding-bottom: 12px;font-size: 12px;line-height: 21px;text-align: right;width: 300px;color: #999;font-family: Georgia,serif'> Any Problems? <a href='https://awme.net/contact/website'>Contact Us</a> </td></tr></tbody></table> </td></tr></tbody></table> <table class='header centered' style='border-collapse: collapse;border-spacing: 0;Margin-left: auto;Margin-right: auto;width: 602px'> <tbody><tr><td class='border' style='padding: 0;vertical-align: top;font-size: 1px;line-height: 1px;background-color: #e9e9e9;width: 1px'>&nbsp;</td></tr><tr><td class='logo' style='padding: 32px 0;vertical-align: top;mso-line-height-rule: at-least'><div class='logo-center' style='font-size: 26px;font-weight: 700;letter-spacing: -0.02em;line-height: 32px;color: #41637e;font-family: sans-serif;text-align: center' align='center' id='emb-email-header'><a style='text-decoration: none;transition: all .2s;color: #41637e' href='https://www.awme.net'><img style='border: 0;-ms-interpolation-mode: bicubic;display: block;Margin-left: auto;Margin-right: auto;max-width: 507px' src='https://i.awme.net/logos/awm-logo.png' alt='Andrew Wommack Ministries Europe' width='338' height='144'/></a></div></td></tr></tbody></table> <table class='border' style='border-collapse: collapse;border-spacing: 0;font-size: 1px;line-height: 1px;background-color: #e9e9e9;Margin-left: auto;Margin-right: auto' width='602'> <tbody><tr><td style='padding: 0;vertical-align: top'>&#8203;</td></tr></tbody></table> <table class='centered' style='border-collapse: collapse;border-spacing: 0;Margin-left: auto;Margin-right: auto'> <tbody><tr> <td class='border' style='padding: 0;vertical-align: top;font-size: 1px;line-height: 1px;background-color: #e9e9e9;width: 1px'>&#8203;</td><td style='padding: 0;vertical-align: top'> <table class='one-col' style='border-collapse: collapse;border-spacing: 0;Margin-left: auto;Margin-right: auto;width: 600px;background-color: #ffffff;font-size: 14px;table-layout: fixed'> <tbody><tr> <td class='column' style='padding: 0;vertical-align: top;text-align: left'> <div><div class='column-top' style='font-size: 32px;line-height: 32px'>&nbsp;</div></div><table class='contents' style='border-collapse: collapse;border-spacing: 0;table-layout: fixed;width: 100%'> <tbody><tr> <td class='padded' style='padding: 0;vertical-align: top;padding-left: 32px;padding-right: 32px;word-break: break-word;word-wrap: break-word'> <h1 style='Margin-top: 0;color: #1675A9;font-weight: 700;font-size: 36px;Margin-bottom: 18px;font-family: sans-serif;line-height: 44px'>
						<strong style='font-weight: bold'>Welcome to your AWMPass Account</strong></h1>
						<p style='Margin-top: 0;color: #565656;font-family: Helvetica, Arial, sans-serif;font-size: 16px;line-height: 25px;Margin-bottom: 25px'>
						Hello,<br/>
						Your AWMPass account is now active!  Simply log in at <a href='https://awme.net/login'>AWME.net</a> to start using your account straight away.  To get started, We'd highly recommend you save a default address to your account, which will let you check out from the <a href='https://awme.net/shop'>AWME.net Online Shop</a> quicker.
						<br/><br/>
						If you have any questions, please <a href='https://awme.net/contact/website'>contact us here,</a> and we'll do our very best to respond and help you as quickly as we can.
						</p>
						<p style='Margin-top: 0;color: #565656;font-family: Helvetica, Arial, sans-serif;font-size: 16px;line-height: 25px;Margin-bottom: 25px'><b>Did you know?</b><br/>There are so many great resources on <a href='https://awme.net'>AWME.net</a>, from free teachings to <a href='https://www.awme.net/audio'>mp3 downloads</a>, not to mention our <a href='https://awme.net/shop'>online shop<a/> that is overflowing with Andrews <a href='https://www.awme.net/shop/list/10'>Books</a>, <a href='https://www.awme.net/shop/list/75'>DVDs</a>, <a href='https://www.awme.net/shop/list/117'>Study Guides</a> and much more!<br/><br/>If however you like to <i>watch</i> Andrew teach, be sure to visit <a href='https://gospeltruth.tv/'>GospelTruth.tv</a> to find a whole range of teachings organised by subject.</p></td></tr></tbody></table> <div class='column-bottom' style='font-size: 8px;line-height: 8px'>&nbsp;</div></td></tr></tbody></table> </td><td class='border' style='padding: 0;vertical-align: top;font-size: 1px;line-height: 1px;background-color: #e9e9e9;width: 1px'>&#8203;</td></tr></tbody></table> 
						<table class='border' style='border-collapse: collapse;border-spacing: 0;font-size: 1px;line-height: 1px;background-color: #e9e9e9;Margin-left: auto;Margin-right: auto' width='602'> <tbody><tr class='border' style='font-size: 1px;line-height: 1px;background-color: #e9e9e9;height: 1px'> <td class='border' style='padding: 0;vertical-align: top;font-size: 1px;line-height: 1px;background-color: #e9e9e9;width: 1px'>&#8203;</td><td style='padding: 0;vertical-align: top;line-height: 1px'>&#8203;</td><td class='border' style='padding: 0;vertical-align: top;font-size: 1px;line-height: 1px;background-color: #e9e9e9;width: 1px'>&#8203;</td></tr></tbody></table> <table class='centered' style='border-collapse: collapse;border-spacing: 0;Margin-left: auto;Margin-right: auto'> <tbody><tr> <td style='padding: 0;vertical-align: top'><table class='border' style='border-collapse: collapse;border-spacing: 0;font-size: 1px;line-height: 1px;background-color: #e9e9e9;Margin-left: auto;Margin-right: auto' width='602'> <tbody><tr><td style='padding: 0;vertical-align: top'>&#8203;</td></tr></tbody></table> <table class='footer centered' style='border-collapse: collapse;border-spacing: 0;Margin-left: auto;Margin-right: auto;width: 602px'> <tbody> <tr> <td style='padding: 0;vertical-align: top'> <table style='border-collapse: collapse;border-spacing: 0'> <tbody><tr> <td class='address' style='padding: 0;vertical-align: top;width: 250px;padding-top: 32px;padding-bottom: 64px'> <table class='contents' style='border-collapse: collapse;border-spacing: 0;table-layout: fixed;width: 100%'> <tbody><tr> <td class='padded' style='padding: 0;vertical-align: top;padding-left: 0;padding-right: 10px;word-break: break-word;word-wrap: break-word;text-align: left;font-size: 12px;line-height: 20px;color: #999;font-family: Georgia,serif'> <div>Andrew Wommack Ministries Europe<br/>Call Us: +44(0) 1922 473300</div></td></tr></tbody></table> </td><td class='subscription' style='padding: 0;vertical-align: top;width: 350px;padding-top: 32px;padding-bottom: 64px'> <table class='contents' style='border-collapse: collapse;border-spacing: 0;table-layout: fixed;width: 100%'> <tbody><tr> <td class='padded' style='padding: 0;vertical-align: top;padding-left: 10px;padding-right: 0;word-break: break-word;word-wrap: break-word;font-size: 12px;line-height: 20px;color: #999;font-family: Georgia, serif;text-align: right'> <div>Manage your account settings at awme.net/account</div><div> <span class='block'> <span> <preferences style='font-weight:bold;text-decoration:none;' lang='en'> <a href='/account/settings'>Preferences</a></preferences> <span class='hide'>&nbsp;&nbsp;|&nbsp;&nbsp;</span> </span> </span> <span class='block'><unsubscribe style='font-weight:bold;text-decoration:none;'><a href='/account/settings'>Unsubscribe</a></unsubscribe></span> </div></td></tr></tbody></table> </td></tr></tbody></table> </td></tr></tbody></table> </center> </body></html>";
				                                                      
				// Create a message
				$message = Swift_Message::newInstance("AWMPass Account Creation")
								->setFrom(array('no-reply@awme.net' => 'Notification Service'))
								->setTo(strtolower($email))
								->setBody($mailbody,'text/html');
				$mailresult = $this->swiftmail->send($message);
				if($mailresult) {
					$this->activity("Sent account confirmation email",25,$userID);
					$this->console[] = $this->output = "Account created successfully.";
					$this->form_status = true;
				} else {
					$this->console[] = $this->output = "There was a problem sending an email to {$email}.";
				}
				if($this->create_auto_login) $this->login($email, $password);
			} else {
				$this->console[] = $this->output = "There was a problem creating new User ID.";
			}
		} else {
			$this->console[] = $this->output = "There were errors in your form. Please correct them and try again.";
		}
	}
	private function forgotten_password_request() {
		$this->db->prepared = array("email" => $this->email);
		$check_user = $this->db->select("SELECT * FROM `{$this->authCredentials['database']}`.`{$this->authCredentials['table']}` WHERE `email`=:email;",true);
		if($check_user) {
			
			$change_password_URL = "https://{$_SERVER['HTTP_HOST']}{$this->post_url['forgot']}/reset/{$check_user['hash']}";
			
			// Send them an email with the instructions in it as to how to change their password.
			//$mailbody = "<!DOCTYPE html PUBLIC '-//W3C//DTD XHTML 1.0 Transitional//EN' 'http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd'><html xmlns='http://www.w3.org/1999/xhtml'><head> <title></title> <meta http-equiv='Content-Type' content='text/html; charset=utf-8'/> <style type='text/css'> body{margin:0;mso-line-height-rule:exactly;padding:0;min-width:100%}table{border-collapse:collapse;border-spacing:0}td{padding:0;vertical-align:top}.border,.spacer{font-size:1px;line-height:1px}.spacer{width:100%}img{border:0;-ms-interpolation-mode:bicubic}.image{font-size:12px;Margin-bottom:24px;mso-line-height-rule:at-least}.image img{display:block}.logo{mso-line-height-rule:at-least}.logo img{display:block}strong{font-weight:700}h1,h2,h3,li,ol,p,ul{Margin-top:0}li,ol,ul{padding-left:0}blockquote{Margin-top:0;Margin-right:0;Margin-bottom:0;padding-right:0}.column-top{font-size:32px;line-height:32px}.column-bottom{font-size:8px;line-height:8px}.column{text-align:left}.contents{table-layout:fixed;width:100%}.padded{padding-left:32px;padding-right:32px;word-break:break-word;word-wrap:break-word}.wrapper{display:table;table-layout:fixed;width:100%;min-width:620px;-webkit-text-size-adjust:100%;-ms-text-size-adjust:100%}table.wrapper{table-layout:fixed}.one-col,.three-col,.two-col{width:600px}.centered{Margin-left:auto;Margin-right:auto}.two-col .image{Margin-bottom:23px}.two-col .column-bottom{font-size:9px;line-height:9px}.two-col .column{width:300px}.three-col .image{Margin-bottom:21px}.three-col .column-bottom{font-size:11px;line-height:11px}.three-col .column{width:200px}.three-col .first .padded{padding-left:32px;padding-right:16px}.three-col .second .padded{padding-left:24px;padding-right:24px}.three-col .third .padded{padding-left:16px;padding-right:32px}@media only screen and (min-width:0){.wrapper{text-rendering:optimizeLegibility}}@media only screen and (max-width:620px){[class=wrapper]{min-width:318px!important;width:100%!important}[class=wrapper] .one-col,[class=wrapper] .three-col,[class=wrapper] .two-col{width:318px!important}[class=wrapper] .column,[class=wrapper] .gutter{display:block;float:left;width:318px!important}[class=wrapper] .padded{padding-left:32px!important;padding-right:32px!important}[class=wrapper] .block{display:block!important}[class=wrapper] .hide{display:none!important}[class=wrapper] .image{margin-bottom:24px!important}[class=wrapper] .image img{height:auto!important;width:100%!important}}.wrapper h1{font-weight:700}.wrapper h2{font-style:italic;font-weight:400}.wrapper h3{font-weight:400}.one-col blockquote,.three-col blockquote,.two-col blockquote{font-style:italic}.one-col-feature h1{font-weight:400}.one-col-feature h2{font-style:normal;font-weight:700}.one-col-feature h3{font-style:italic}td.border{width:1px}tr.border{background-color:#e9e9e9;height:1px}tr.border td{line-height:1px}.one-col,.one-col-feature,.three-col,.two-col{background-color:#fff;font-size:14px;table-layout:fixed}.footer,.header,.one-col,.one-col-feature,.preheader,.three-col,.two-col{Margin-left:auto;Margin-right:auto}.preheader table{width:602px}.preheader .title,.preheader .webversion{padding-top:10px;padding-bottom:12px;font-size:12px;line-height:21px}.preheader .title{text-align:left}.preheader .webversion{text-align:right;width:300px}.header{width:602px}.header .logo{padding:32px 0}.header .logo div{font-size:26px;font-weight:700;letter-spacing:-.02em;line-height:32px}.header .logo div a{text-decoration:none}.header .logo div.logo-center{text-align:center}.header .logo div.logo-center img{Margin-left:auto;Margin-right:auto}.gmail{width:650px;min-width:650px}.gmail td{font-size:1px;line-height:1px}.wrapper a{text-decoration:underline;transition:all .2s}.wrapper h1{font-size:36px;Margin-bottom:18px}.wrapper h2{font-size:26px;line-height:32px;Margin-bottom:20px}.wrapper h3{font-size:18px;line-height:22px;Margin-bottom:16px}.wrapper h1 a,.wrapper h2 a,.wrapper h3 a{text-decoration:none}.one-col blockquote,.three-col blockquote,.two-col blockquote{font-size:14px;border-left:2px solid #e9e9e9;Margin-left:0;padding-left:16px}table.divider{width:100%}.divider .inner{padding-bottom:24px}.divider table{background-color:#e9e9e9;font-size:2px;line-height:2px;width:60px}.wrapper .gray{background-color:#f7f7f7}.wrapper .gray blockquote{border-left-color:#ddd}.wrapper .gray .divider table{background-color:#ddd}.padded .image{font-size:0}.image-frame{padding:8px}.image-background{display:inline-block;font-size:12px}.btn{Margin-bottom:24px;padding:2px}.btn a{border:1px solid #fff;display:inline-block;font-size:13px;font-weight:700;line-height:15px;outline-style:solid;outline-width:2px;padding:10px 30px;text-align:center;text-decoration:none!important}.one-col .column table:nth-last-child(2) td h1:last-child,.one-col .column table:nth-last-child(2) td h2:last-child,.one-col .column table:nth-last-child(2) td h3:last-child,.one-col .column table:nth-last-child(2) td ol:last-child,.one-col .column table:nth-last-child(2) td p:last-child,.one-col .column table:nth-last-child(2) td ul:last-child{Margin-bottom:24px}.one-col ol,.one-col p,.one-col ul{font-size:16px;line-height:24px}.one-col ol,.one-col ul{Margin-left:18px}.two-col .column table:nth-last-child(2) td h1:last-child,.two-col .column table:nth-last-child(2) td h2:last-child,.two-col .column table:nth-last-child(2) td h3:last-child,.two-col .column table:nth-last-child(2) td ol:last-child,.two-col .column table:nth-last-child(2) td p:last-child,.two-col .column table:nth-last-child(2) td ul:last-child{Margin-bottom:23px}.two-col .image-frame{padding:6px}.two-col h1{font-size:26px;line-height:32px;Margin-bottom:16px}.two-col h2{font-size:20px;line-height:26px;Margin-bottom:18px}.two-col h3{font-size:16px;line-height:20px;Margin-bottom:14px}.two-col ol,.two-col p,.two-col ul{font-size:14px;line-height:23px}.two-col ol,.two-col ul{Margin-left:16px}.two-col li{padding-left:5px}.two-col .divider .inner{padding-bottom:23px}.two-col .btn{Margin-bottom:23px}.two-col blockquote{padding-left:16px}.three-col .column table:nth-last-child(2) td h1:last-child,.three-col .column table:nth-last-child(2) td h2:last-child,.three-col .column table:nth-last-child(2) td h3:last-child,.three-col .column table:nth-last-child(2) td ol:last-child,.three-col .column table:nth-last-child(2) td p:last-child,.three-col .column table:nth-last-child(2) td ul:last-child{Margin-bottom:21px}.three-col .image-frame{padding:4px}.three-col h1{font-size:20px;line-height:26px;Margin-bottom:12px}.three-col h2{font-size:16px;line-height:22px;Margin-bottom:14px}.three-col h3{font-size:14px;line-height:18px;Margin-bottom:10px}.three-col ol,.three-col p,.three-col ul{font-size:12px;line-height:21px}.three-col ol,.three-col ul{Margin-left:14px}.three-col li{padding-left:6px}.three-col .divider .inner{padding-bottom:21px}.three-col .btn{Margin-bottom:21px}.three-col .btn a{font-size:12px;line-height:14px;padding:8px 19px}.three-col blockquote{padding-left:16px}.one-col-feature .column-top{font-size:36px;line-height:36px}.one-col-feature .column-bottom{font-size:4px;line-height:4px}.one-col-feature .column{text-align:center;width:600px}.one-col-feature .column table:nth-last-child(2) td h1:last-child,.one-col-feature .column table:nth-last-child(2) td h2:last-child,.one-col-feature .column table:nth-last-child(2) td h3:last-child,.one-col-feature .column table:nth-last-child(2) td ol:last-child,.one-col-feature .column table:nth-last-child(2) td p:last-child,.one-col-feature .column table:nth-last-child(2) td ul:last-child,.one-col-feature .image{Margin-bottom:32px}.one-col-feature h1,.one-col-feature h2,.one-col-feature h3{text-align:center}.one-col-feature h1{font-size:52px;Margin-bottom:22px}.one-col-feature h2{font-size:42px;Margin-bottom:20px}.one-col-feature h3{font-size:32px;line-height:42px;Margin-bottom:20px}.one-col-feature ol,.one-col-feature p,.one-col-feature ul{font-size:21px;line-height:32px;Margin-bottom:32px}.one-col-feature ol a,.one-col-feature p a,.one-col-feature ul a{text-decoration:none}.one-col-feature p{text-align:center}.one-col-feature ol,.one-col-feature ul{Margin-left:40px;text-align:left}.one-col-feature li{padding-left:3px}.one-col-feature .btn{Margin-bottom:32px;text-align:center}.one-col-feature .divider .inner{padding-bottom:32px}.one-col-feature blockquote{border-bottom:2px solid #e9e9e9;border-left-color:#fff;border-left-width:0;border-left-style:none;border-top:2px solid #e9e9e9;Margin-bottom:32px;Margin-left:0;padding-bottom:42px;padding-left:0;padding-top:42px;position:relative}.one-col-feature blockquote:after,.one-col-feature blockquote:before{background:-moz-linear-gradient(left,#fff 25%,#e9e9e9 25%,#e9e9e9 75%,#fff 75%);background:-webkit-gradient(linear,left top,right top,color-stop(25%,#fff),color-stop(25%,#e9e9e9),color-stop(75%,#e9e9e9),color-stop(75%,#fff));background:-webkit-linear-gradient(left,#fff 25%,#e9e9e9 25%,#e9e9e9 75%,#fff 75%);background:-o-linear-gradient(left,#fff 25%,#e9e9e9 25%,#e9e9e9 75%,#fff 75%);background:-ms-linear-gradient(left,#fff 25%,#e9e9e9 25%,#e9e9e9 75%,#fff 75%);background:linear-gradient(to right,#fff 25%,#e9e9e9 25%,#e9e9e9 75%,#fff 75%);content:'';display:block;height:2px;left:0;outline:#fff solid 1px;position:absolute;right:0}.one-col-feature blockquote:before{top:-2px}.one-col-feature blockquote:after{bottom:-2px}.one-col-feature blockquote ol,.one-col-feature blockquote p,.one-col-feature blockquote ul{font-size:42px;line-height:48px;Margin-bottom:48px}.one-col-feature blockquote ol:last-child,.one-col-feature blockquote p:last-child,.one-col-feature blockquote ul:last-child{Margin-bottom:0!important}.footer{width:602px}.footer .padded{font-size:12px;line-height:20px}.social{padding-top:32px;padding-bottom:22px}.social img{display:block}.social .divider{font-family:sans-serif;font-size:10px;line-height:21px;text-align:center;padding-left:14px;padding-right:14px}.social .social-text{height:21px;vertical-align:middle!important;font-size:10px;font-weight:700;text-decoration:none;text-transform:uppercase}.social .social-text a{text-decoration:none}.address{width:250px}.address .padded{text-align:left;padding-left:0;padding-right:10px}.subscription{width:350px}.subscription .padded{text-align:right;padding-right:0;padding-left:10px}.address,.subscription{padding-top:32px;padding-bottom:64px}.address a,.subscription a{font-weight:700;text-decoration:none}.address table,.subscription table{width:100%}@media only screen and (max-width:651px){.gmail{display:none!important}}@media only screen and (max-width:620px){[class=wrapper] .one-col .column:last-child table:nth-last-child(2) td h1:last-child,[class=wrapper] .one-col .column:last-child table:nth-last-child(2) td h2:last-child,[class=wrapper] .one-col .column:last-child table:nth-last-child(2) td h3:last-child,[class=wrapper] .one-col .column:last-child table:nth-last-child(2) td ol:last-child,[class=wrapper] .one-col .column:last-child table:nth-last-child(2) td p:last-child,[class=wrapper] .one-col .column:last-child table:nth-last-child(2) td ul:last-child,[class=wrapper] .one-col-feature .column:last-child table:nth-last-child(2) td h1:last-child,[class=wrapper] .one-col-feature .column:last-child table:nth-last-child(2) td h2:last-child,[class=wrapper] .one-col-feature .column:last-child table:nth-last-child(2) td h3:last-child,[class=wrapper] .one-col-feature .column:last-child table:nth-last-child(2) td ol:last-child,[class=wrapper] .one-col-feature .column:last-child table:nth-last-child(2) td p:last-child,[class=wrapper] .one-col-feature .column:last-child table:nth-last-child(2) td ul:last-child,[class=wrapper] .three-col .column:last-child table:nth-last-child(2) td h1:last-child,[class=wrapper] .three-col .column:last-child table:nth-last-child(2) td h2:last-child,[class=wrapper] .three-col .column:last-child table:nth-last-child(2) td h3:last-child,[class=wrapper] .three-col .column:last-child table:nth-last-child(2) td ol:last-child,[class=wrapper] .three-col .column:last-child table:nth-last-child(2) td p:last-child,[class=wrapper] .three-col .column:last-child table:nth-last-child(2) td ul:last-child,[class=wrapper] .two-col .column:last-child table:nth-last-child(2) td h1:last-child,[class=wrapper] .two-col .column:last-child table:nth-last-child(2) td h2:last-child,[class=wrapper] .two-col .column:last-child table:nth-last-child(2) td h3:last-child,[class=wrapper] .two-col .column:last-child table:nth-last-child(2) td ol:last-child,[class=wrapper] .two-col .column:last-child table:nth-last-child(2) td p:last-child,[class=wrapper] .two-col .column:last-child table:nth-last-child(2) td ul:last-child{Margin-bottom:24px!important}[class=wrapper] .address,[class=wrapper] .subscription{display:block;float:left;width:318px!important;text-align:center!important}[class=wrapper] .address{padding-bottom:0!important}[class=wrapper] .subscription{padding-top:0!important}[class=wrapper] h1{font-size:36px!important;line-height:42px!important;Margin-bottom:18px!important}[class=wrapper] h2{font-size:26px!important;line-height:32px!important;Margin-bottom:20px!important}[class=wrapper] h3{font-size:18px!important;line-height:22px!important;Margin-bottom:16px!important}[class=wrapper] ol,[class=wrapper] p,[class=wrapper] ul{font-size:16px!important;line-height:24px!important;Margin-bottom:24px!important}[class=wrapper] ol,[class=wrapper] ul{Margin-left:18px!important}[class=wrapper] li{padding-left:2px!important}[class=wrapper] blockquote{padding-left:16px!important}[class=wrapper] .two-col .column:nth-child(n+3){border-top:1px solid #e9e9e9}[class=wrapper] .btn{margin-bottom:24px!important}[class=wrapper] .btn a{display:block!important;font-size:13px!important;font-weight:700!important;line-height:15px!important;padding:10px 30px!important}[class=wrapper] .column-bottom{font-size:8px!important;line-height:8px!important}[class=wrapper] .first .column-bottom,[class=wrapper] .second .column-top,[class=wrapper] .third .column-top,[class=wrapper] .three-col .second .column-bottom{display:none}[class=wrapper] .image-frame{padding:4px!important}[class=wrapper] .header .logo{padding-left:10px!important;padding-right:10px!important}[class=wrapper] .header .logo div{font-size:26px!important;line-height:32px!important}[class=wrapper] .header .logo div img{display:inline-block!important;max-width:280px!important;height:auto!important}[class=wrapper] .footer,[class=wrapper] .header,[class=wrapper] .webversion,[class=wrapper] table.border{width:320px!important}[class=wrapper] .header .logo a,[class=wrapper] .preheader .webversion{text-align:center!important}[class=wrapper] .border td,[class=wrapper] .preheader table{width:318px!important}[class=wrapper] .border td.border{width:1px!important}[class=wrapper] .image .border td{width:auto!important}[class=wrapper] .title{display:none}[class=wrapper] .footer .padded{text-align:center!important}[class=wrapper] .footer .subscription .padded{padding-top:20px!important}[class=wrapper] .footer .social-link{display:block!important}[class=wrapper] .footer .social-link table{margin:0 auto 10px!important}[class=wrapper] .footer .divider{display:none!important}[class=wrapper] .one-col-feature .btn,[class=wrapper] .one-col-feature .image{margin-bottom:28px!important}[class=wrapper] .one-col-feature .divider .inner{padding-bottom:28px!important}[class=wrapper] .one-col-feature h1{font-size:42px!important;line-height:48px!important;margin-bottom:20px!important}[class=wrapper] .one-col-feature h2{font-size:32px!important;line-height:36px!important;margin-bottom:18px!important}[class=wrapper] .one-col-feature h3{font-size:26px!important;line-height:32px!important;margin-bottom:20px!important}[class=wrapper] .one-col-feature ol,[class=wrapper] .one-col-feature p,[class=wrapper] .one-col-feature ul{font-size:20px!important;line-height:28px!important;margin-bottom:28px!important}[class=wrapper] .one-col-feature blockquote{font-size:18px!important;line-height:26px!important;margin-bottom:28px!important;padding-bottom:26px!important;padding-left:0!important;padding-top:26px!important}[class=wrapper] .one-col-feature blockquote ol,[class=wrapper] .one-col-feature blockquote p,[class=wrapper] .one-col-feature blockquote ul{font-size:26px!important;line-height:32px!important}[class=wrapper] .one-col-feature blockquote ol:last-child,[class=wrapper] .one-col-feature blockquote p:last-child,[class=wrapper] .one-col-feature blockquote ul:last-child{margin-bottom:0!important}[class=wrapper] .one-col-feature .column table:last-of-type h1:last-child,[class=wrapper] .one-col-feature .column table:last-of-type h2:last-child,[class=wrapper] .one-col-feature .column table:last-of-type h3:last-child{margin-bottom:28px!important}}@media only screen and (max-width:320px){[class=wrapper] td.border{display:none}[class=wrapper] .footer,[class=wrapper] .header,[class=wrapper] .webversion,[class=wrapper] table.border{width:318px!important}}</style><!--[if gte mso 9]> <style>.column-top{mso-line-height-rule: exactly !important;}</style><![endif]--> <meta name='robots' content='noindex,nofollow'/><meta property='og:title' content='My First Campaign'/></head> <body style='margin: 0;mso-line-height-rule: exactly;padding: 0;min-width: 100%;background-color: #fbfbfb'><style type='text/css'>@import url(https://fonts.googleapis.com/css?family=Open+Sans:400italic,700italic,700,400);body,.wrapper,.emb-editor-canvas{background-color:#fbfbfb}.border{background-color:#e9e9e9}h1{color:#5489f5}.wrapper h1{}.wrapper h1{font-family:sans-serif}@media only screen and (min-width: 0){.wrapper h1{font-family:'Open Sans',sans-serif !important}}h1{}.one-col h1{line-height:44px}.two-col h1{line-height:34px}.three-col h1{line-height:28px}.wrapper .one-col-feature h1{line-height:58px}@media only screen and (max-width: 620px){h1{line-height:44px !important}}h2{color:#555}.wrapper h2{}.wrapper h2{font-family:Georgia,serif}h2{}.one-col h2{line-height:32px}.two-col h2{line-height:26px}.three-col h2{line-height:22px}.wrapper .one-col-feature h2{line-height:52px}@media only screen and (max-width: 620px){h2{line-height:32px !important}}h3{color:#5c93e6}.wrapper h3{}.wrapper h3{font-family:sans-serif}h3{}.one-col h3{line-height:26px}.two-col h3{line-height:22px}.three-col h3{line-height:20px}.wrapper .one-col-feature h3{line-height:40px}@media only screen and (max-width: 620px){h3{line-height:26px !important}}p,ol,ul{color:#565656}.wrapper p,.wrapper ol,.wrapper ul{}.wrapper p,.wrapper ol,.wrapper ul{font-family:Georgia,serif}p,ol,ul{}.one-col p,.one-col ol,.one-col ul{line-height:25px;Margin-bottom:25px}.two-col p,.two-col ol,.two-col ul{line-height:23px;Margin-bottom:23px}.three-col p,.three-col ol,.three-col ul{line-height:21px;Margin-bottom:21px}.wrapper .one-col-feature p,.wrapper .one-col-feature ol,.wrapper .one-col-feature ul{line-height:32px}.one-col-feature blockquote p,.one-col-feature blockquote ol,.one-col-feature blockquote ul{line-height:50px}@media only screen and (max-width: 620px){p,ol,ul{line-height:25px !important;Margin-bottom:25px !important}}.image{color:#565656}.image{font-family:Georgia,serif}.wrapper a{color:#41637e}.wrapper a:hover{color:#30495c !important}.wrapper .logo div{color:#41637e}.wrapper .logo div{font-family:sans-serif}@media only screen and (min-width: 0){.wrapper .logo div{font-family:Avenir,sans-serif !important}}.wrapper .logo div a{color:#41637e}.wrapper .logo div a:hover{color:#41637e !important}.wrapper .one-col-feature p a,.wrapper .one-col-feature ol a,.wrapper .one-col-feature ul a{border-bottom:1px solid #41637e}.wrapper .one-col-feature p a:hover,.wrapper .one-col-feature ol a:hover,.wrapper .one-col-feature ul a:hover{color:#30495c !important;border-bottom:1px solid #30495c !important}.btn a{}.wrapper .btn a{}.wrapper .btn a{font-family:Georgia,serif}.wrapper .btn a{background-color:#41637e;color:#fff !important;outline-color:#41637e;text-shadow:0 1px 0 #3b5971}.wrapper .btn a:hover{background-color:#3b5971 !important;color:#fff !important;outline-color:#3b5971 !important}.preheader .title,.preheader .webversion,.footer .padded{color:#999}.preheader .title,.preheader .webversion,.footer .padded{font-family:Georgia,serif}.preheader .title a,.preheader .webversion a,.footer .padded a{color:#999}.preheader .title a:hover,.preheader .webversion a:hover,.footer .padded a:hover{color:#737373 !important}.footer .social .divider{color:#e9e9e9}.footer .social .social-text,.footer .social a{color:#999}.wrapper .footer .social .social-text,.wrapper .footer .social a{}.wrapper .footer .social .social-text,.wrapper .footer .social a{font-family:Georgia,serif}.footer .social .social-text,.footer .social a{}.footer .social .social-text,.footer .social a{letter-spacing:0.05em}.footer .social .social-text:hover,.footer .social a:hover{color:#737373 !important}.image .border{background-color:#c8c8c8}.image-frame{background-color:#dadada}.image-background{background-color:#f7f7f7}</style> <center class='wrapper' style='display: table;table-layout: fixed;width: 100%;min-width: 620px;-webkit-text-size-adjust: 100%;-ms-text-size-adjust: 100%;background-color: #fbfbfb'> <table class='gmail' style='border-collapse: collapse;border-spacing: 0;width: 650px;min-width: 650px'><tbody><tr><td style='padding: 0;vertical-align: top;font-size: 1px;line-height: 1px'>&nbsp;</td></tr></tbody></table> <table class='preheader centered' style='border-collapse: collapse;border-spacing: 0;Margin-left: auto;Margin-right: auto'> <tbody><tr> <td style='padding: 0;vertical-align: top'> <table style='border-collapse: collapse;border-spacing: 0;width: 602px'> <tbody><tr> <td class='title' style='padding: 0;vertical-align: top;padding-top: 10px;padding-bottom: 12px;font-size: 12px;line-height: 21px;text-align: left;color: #999;font-family: Georgia,serif'>Welcome to your AWMPass&nbsp;</td><td style='padding: 0;vertical-align: top;padding-top: 10px;padding-bottom: 12px;font-size: 12px;line-height: 21px;text-align: right;width: 300px;color: #999;font-family: Georgia,serif'> Any Problems? <a href='http://awme.net/contact/website'>Contact Us</a> </td></tr></tbody></table> </td></tr></tbody></table> <table class='header centered' style='border-collapse: collapse;border-spacing: 0;Margin-left: auto;Margin-right: auto;width: 602px'> <tbody><tr><td class='border' style='padding: 0;vertical-align: top;font-size: 1px;line-height: 1px;background-color: #e9e9e9;width: 1px'>&nbsp;</td></tr><tr><td class='logo' style='padding: 32px 0;vertical-align: top;mso-line-height-rule: at-least'><div class='logo-center' style='font-size: 26px;font-weight: 700;letter-spacing: -0.02em;line-height: 32px;color: #41637e;font-family: sans-serif;text-align: center' align='center' id='emb-email-header'><a style='text-decoration: none;transition: all .2s;color: #41637e' href='http://www.awme.net'><img style='border: 0;-ms-interpolation-mode: bicubic;display: block;Margin-left: auto;Margin-right: auto;max-width: 507px' src='http://i.awme.net/logos/awm-logo.png' alt='Andrew Wommack Ministries Europe' width='338' height='144'/></a></div></td></tr></tbody></table> <table class='border' style='border-collapse: collapse;border-spacing: 0;font-size: 1px;line-height: 1px;background-color: #e9e9e9;Margin-left: auto;Margin-right: auto' width='602'> <tbody><tr><td style='padding: 0;vertical-align: top'>&#8203;</td></tr></tbody></table> <table class='centered' style='border-collapse: collapse;border-spacing: 0;Margin-left: auto;Margin-right: auto'> <tbody><tr> <td class='border' style='padding: 0;vertical-align: top;font-size: 1px;line-height: 1px;background-color: #e9e9e9;width: 1px'>&#8203;</td><td style='padding: 0;vertical-align: top'> <table class='one-col' style='border-collapse: collapse;border-spacing: 0;Margin-left: auto;Margin-right: auto;width: 600px;background-color: #ffffff;font-size: 14px;table-layout: fixed'> <tbody><tr> <td class='column' style='padding: 0;vertical-align: top;text-align: left'> <div><div class='column-top' style='font-size: 32px;line-height: 32px'>&nbsp;</div></div><table class='contents' style='border-collapse: collapse;border-spacing: 0;table-layout: fixed;width: 100%'> <tbody><tr> <td class='padded' style='padding: 0;vertical-align: top;padding-left: 32px;padding-right: 32px;word-break: break-word;word-wrap: break-word'> <h1 style='Margin-top: 0;color: #1675A9;font-weight: 700;font-size: 36px;Margin-bottom: 18px;font-family: sans-serif;line-height: 44px'><strong style='font-weight: bold'>Password Reset Request</strong></h1>
			//		<p style='Margin-top: 0;color: #565656;font-family: Helvetica, Arial, sans-serif;font-size: 16px;line-height: 25px;Margin-bottom: 25px'>
			//		Hello,<br/>
			//		A password reset request was flagged for this email address at <a href='http://awme.net/login'>AWME.net</a>.  <b>If this was not you, then please ignore this email</b> -  and we will not contact you again.<br/><br/>
			//		<b>I requested the password reset, so what now?</b><br/>
			//		Just follow the steps below to get your password automatically reset for you:<br/>
			//		1. <a href='https://{$_SERVER['HTTP_HOST']}/{$pageroot}/forgotten-password/reset/{$check_user['hash']}'>Click on this link to reset your password.</a><br/>
			//		2. You will receive a new password automatically generated.<br/><br/>
			//		We will choose a random verse from the new testament for your new password.  Be sure to change it as soon as you can though to something you can easily remember.
			//		</p>
			//		<p style='Margin-top: 0;color: #565656;font-family: Helvetica, Arial, sans-serif;font-size: 16px;line-height: 25px;Margin-bottom: 25px'><b>Did you know?</b><br/>There are so many great resources on <a href='http://awme.net'>AWME.net</a>, from free teachings to <a href='http://www.awme.net/audio'>mp3 downloads</a>, not to mention our <a href='http://awme.net/shop'>online shop<a/> that is overflowing with Andrews <a href='http://www.awme.net/shop/list/10'>Books</a>, <a href='http://www.awme.net/shop/list/75'>DVDs</a>, <a href='http://www.awme.net/shop/list/117'>Study Guides</a> and much more!<br/><br/>If however you like to <i>watch</i> Andrew teach, be sure to visit <a href='http://gospeltruth.tv/'>GospelTruth.tv</a> to find a whole range of teachings organised by subject.</p></td></tr></tbody></table> <div class='column-bottom' style='font-size: 8px;line-height: 8px'>&nbsp;</div></td></tr></tbody></table> </td><td class='border' style='padding: 0;vertical-align: top;font-size: 1px;line-height: 1px;background-color: #e9e9e9;width: 1px'>&#8203;</td></tr></tbody></table> 
			//		<table class='border' style='border-collapse: collapse;border-spacing: 0;font-size: 1px;line-height: 1px;background-color: #e9e9e9;Margin-left: auto;Margin-right: auto' width='602'> <tbody><tr class='border' style='font-size: 1px;line-height: 1px;background-color: #e9e9e9;height: 1px'> <td class='border' style='padding: 0;vertical-align: top;font-size: 1px;line-height: 1px;background-color: #e9e9e9;width: 1px'>&#8203;</td><td style='padding: 0;vertical-align: top;line-height: 1px'>&#8203;</td><td class='border' style='padding: 0;vertical-align: top;font-size: 1px;line-height: 1px;background-color: #e9e9e9;width: 1px'>&#8203;</td></tr></tbody></table> <table class='centered' style='border-collapse: collapse;border-spacing: 0;Margin-left: auto;Margin-right: auto'> <tbody><tr> <td style='padding: 0;vertical-align: top'><table class='border' style='border-collapse: collapse;border-spacing: 0;font-size: 1px;line-height: 1px;background-color: #e9e9e9;Margin-left: auto;Margin-right: auto' width='602'> <tbody><tr><td style='padding: 0;vertical-align: top'>&#8203;</td></tr></tbody></table> <table class='footer centered' style='border-collapse: collapse;border-spacing: 0;Margin-left: auto;Margin-right: auto;width: 602px'> <tbody> <tr> <td style='padding: 0;vertical-align: top'> <table style='border-collapse: collapse;border-spacing: 0'> <tbody><tr> <td class='address' style='padding: 0;vertical-align: top;width: 250px;padding-top: 32px;padding-bottom: 64px'> <table class='contents' style='border-collapse: collapse;border-spacing: 0;table-layout: fixed;width: 100%'> <tbody><tr> <td class='padded' style='padding: 0;vertical-align: top;padding-left: 0;padding-right: 10px;word-break: break-word;word-wrap: break-word;text-align: left;font-size: 12px;line-height: 20px;color: #999;font-family: Georgia,serif'> <div>Andrew Wommack Ministries Europe<br/>Call Us: +44(0) 1922 473300</div></td></tr></tbody></table> </td><td class='subscription' style='padding: 0;vertical-align: top;width: 350px;padding-top: 32px;padding-bottom: 64px'> <table class='contents' style='border-collapse: collapse;border-spacing: 0;table-layout: fixed;width: 100%'> <tbody><tr> <td class='padded' style='padding: 0;vertical-align: top;padding-left: 10px;padding-right: 0;word-break: break-word;word-wrap: break-word;font-size: 12px;line-height: 20px;color: #999;font-family: Georgia, serif;text-align: right'> <div>Manage your account settings at awme.net/account</div><div> <span class='block'> <span> <preferences style='font-weight:bold;text-decoration:none;' lang='en'> <a href='http://".$_SERVER['HTTP_HOST']."/account/settings'>Preferences</a></preferences> <span class='hide'>&nbsp;&nbsp;|&nbsp;&nbsp;</span> </span> </span> <span class='block'><unsubscribe style='font-weight:bold;text-decoration:none;'><a href='http://".$_SERVER['HTTP_HOST']."/account/settings'>Unsubscribe</a></unsubscribe></span> </div></td></tr></tbody></table> </td></tr></tbody></table> </td></tr></tbody></table> </center> </body></html>";
			
			
			ob_start();               
			include($this->email_files['forgotten_password_request']);
			$mailbody = ob_get_clean();  
			
					
			// Create a message
			$message = Swift_Message::newInstance("Password Reset Request")
						->setFrom(array('no-reply@awme.net' => 'AWMPass'))
						->setTo($this->email)
						->setBody($mailbody,'text/html');
			$mailresult = $this->swiftmail->send($message);
			if($mailresult) {
				$this->db->prepared = array("userID" => $check_user['id']);
				$this->db->runSQL("UPDATE `{$this->authCredentials['database']}`.`{$this->authCredentials['table']}` SET `sessionID` = 'forgotten-password' WHERE `id`=:userID;");
				$this->form_status = "sent-request-success";
				$this->activity("Requested password reset.",30,$check_user['id']);	
			} else {
				$this->form_status = "sent-request-failure";
				$this->output = "We had a problem sending an email to <b>{$this->email}</b>.";	
			}
		} else {
			$this->form_status = "sent-request-failure";
			$this->output = "<b>{$this->email}</b> was not found in the AWMPass database.<br/>Maybe it was a different email address you used?";
		}                                                                                         
	}
	private function forgotten_password_reset() {
		
		$url_hash = ($this->uri[3] ? $this->uri[3] : false);
		if($url_hash) {
			$this->db->prepared = array("urlhash" => $url_hash);
			$check_user = $this->db->select("SELECT * FROM `{$this->authCredentials['database']}`.`{$this->authCredentials['table']}` WHERE `sessionID`='forgotten-password' AND `hash`=:urlhash;",true);
			if($check_user) {
				$vID = rand(23146,31102);
				$this->db->prepared = array("verseID" => $vID);
				$verse = $this->db->select("SELECT `book`,`chapter`,`verse`,`contents` FROM `awme_common`.`bible-kjv` WHERE `id`=:verseID;",true);
				$newpassword = trim(str_replace(" ","",$verse['book']).$verse['chapter'].":".$verse['verse']);

				// Do not double salt! hashpassword() already salts for you.
				$psw = $this->hashpassword($newpassword);
		
				$this->db->prepared = array("password" => $psw, "userID" => $check_user['id']);
				$sql_reset_password = "UPDATE `{$this->authCredentials['database']}`.`{$this->authCredentials['table']}` SET `password`=:password,`sessionID`='' WHERE `id`=:userID;";
		
				if($this->db->runSQL($sql_reset_password)) {
					// Send them an email notifying them that their password has been changed.
					//$mailbody = "<!DOCTYPE html PUBLIC '-//W3C//DTD XHTML 1.0 Transitional//EN' 'http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd'><html xmlns='http://www.w3.org/1999/xhtml'><head> <title></title> <meta http-equiv='Content-Type' content='text/html; charset=utf-8'/> <style type='text/css'> body{margin:0;mso-line-height-rule:exactly;padding:0;min-width:100%}table{border-collapse:collapse;border-spacing:0}td{padding:0;vertical-align:top}.border,.spacer{font-size:1px;line-height:1px}.spacer{width:100%}img{border:0;-ms-interpolation-mode:bicubic}.image{font-size:12px;Margin-bottom:24px;mso-line-height-rule:at-least}.image img{display:block}.logo{mso-line-height-rule:at-least}.logo img{display:block}strong{font-weight:700}h1,h2,h3,li,ol,p,ul{Margin-top:0}li,ol,ul{padding-left:0}blockquote{Margin-top:0;Margin-right:0;Margin-bottom:0;padding-right:0}.column-top{font-size:32px;line-height:32px}.column-bottom{font-size:8px;line-height:8px}.column{text-align:left}.contents{table-layout:fixed;width:100%}.padded{padding-left:32px;padding-right:32px;word-break:break-word;word-wrap:break-word}.wrapper{display:table;table-layout:fixed;width:100%;min-width:620px;-webkit-text-size-adjust:100%;-ms-text-size-adjust:100%}table.wrapper{table-layout:fixed}.one-col,.three-col,.two-col{width:600px}.centered{Margin-left:auto;Margin-right:auto}.two-col .image{Margin-bottom:23px}.two-col .column-bottom{font-size:9px;line-height:9px}.two-col .column{width:300px}.three-col .image{Margin-bottom:21px}.three-col .column-bottom{font-size:11px;line-height:11px}.three-col .column{width:200px}.three-col .first .padded{padding-left:32px;padding-right:16px}.three-col .second .padded{padding-left:24px;padding-right:24px}.three-col .third .padded{padding-left:16px;padding-right:32px}@media only screen and (min-width:0){.wrapper{text-rendering:optimizeLegibility}}@media only screen and (max-width:620px){[class=wrapper]{min-width:318px!important;width:100%!important}[class=wrapper] .one-col,[class=wrapper] .three-col,[class=wrapper] .two-col{width:318px!important}[class=wrapper] .column,[class=wrapper] .gutter{display:block;float:left;width:318px!important}[class=wrapper] .padded{padding-left:32px!important;padding-right:32px!important}[class=wrapper] .block{display:block!important}[class=wrapper] .hide{display:none!important}[class=wrapper] .image{margin-bottom:24px!important}[class=wrapper] .image img{height:auto!important;width:100%!important}}.wrapper h1{font-weight:700}.wrapper h2{font-style:italic;font-weight:400}.wrapper h3{font-weight:400}.one-col blockquote,.three-col blockquote,.two-col blockquote{font-style:italic}.one-col-feature h1{font-weight:400}.one-col-feature h2{font-style:normal;font-weight:700}.one-col-feature h3{font-style:italic}td.border{width:1px}tr.border{background-color:#e9e9e9;height:1px}tr.border td{line-height:1px}.one-col,.one-col-feature,.three-col,.two-col{background-color:#fff;font-size:14px;table-layout:fixed}.footer,.header,.one-col,.one-col-feature,.preheader,.three-col,.two-col{Margin-left:auto;Margin-right:auto}.preheader table{width:602px}.preheader .title,.preheader .webversion{padding-top:10px;padding-bottom:12px;font-size:12px;line-height:21px}.preheader .title{text-align:left}.preheader .webversion{text-align:right;width:300px}.header{width:602px}.header .logo{padding:32px 0}.header .logo div{font-size:26px;font-weight:700;letter-spacing:-.02em;line-height:32px}.header .logo div a{text-decoration:none}.header .logo div.logo-center{text-align:center}.header .logo div.logo-center img{Margin-left:auto;Margin-right:auto}.gmail{width:650px;min-width:650px}.gmail td{font-size:1px;line-height:1px}.wrapper a{text-decoration:underline;transition:all .2s}.wrapper h1{font-size:36px;Margin-bottom:18px}.wrapper h2{font-size:26px;line-height:32px;Margin-bottom:20px}.wrapper h3{font-size:18px;line-height:22px;Margin-bottom:16px}.wrapper h1 a,.wrapper h2 a,.wrapper h3 a{text-decoration:none}.one-col blockquote,.three-col blockquote,.two-col blockquote{font-size:14px;border-left:2px solid #e9e9e9;Margin-left:0;padding-left:16px}table.divider{width:100%}.divider .inner{padding-bottom:24px}.divider table{background-color:#e9e9e9;font-size:2px;line-height:2px;width:60px}.wrapper .gray{background-color:#f7f7f7}.wrapper .gray blockquote{border-left-color:#ddd}.wrapper .gray .divider table{background-color:#ddd}.padded .image{font-size:0}.image-frame{padding:8px}.image-background{display:inline-block;font-size:12px}.btn{Margin-bottom:24px;padding:2px}.btn a{border:1px solid #fff;display:inline-block;font-size:13px;font-weight:700;line-height:15px;outline-style:solid;outline-width:2px;padding:10px 30px;text-align:center;text-decoration:none!important}.one-col .column table:nth-last-child(2) td h1:last-child,.one-col .column table:nth-last-child(2) td h2:last-child,.one-col .column table:nth-last-child(2) td h3:last-child,.one-col .column table:nth-last-child(2) td ol:last-child,.one-col .column table:nth-last-child(2) td p:last-child,.one-col .column table:nth-last-child(2) td ul:last-child{Margin-bottom:24px}.one-col ol,.one-col p,.one-col ul{font-size:16px;line-height:24px}.one-col ol,.one-col ul{Margin-left:18px}.two-col .column table:nth-last-child(2) td h1:last-child,.two-col .column table:nth-last-child(2) td h2:last-child,.two-col .column table:nth-last-child(2) td h3:last-child,.two-col .column table:nth-last-child(2) td ol:last-child,.two-col .column table:nth-last-child(2) td p:last-child,.two-col .column table:nth-last-child(2) td ul:last-child{Margin-bottom:23px}.two-col .image-frame{padding:6px}.two-col h1{font-size:26px;line-height:32px;Margin-bottom:16px}.two-col h2{font-size:20px;line-height:26px;Margin-bottom:18px}.two-col h3{font-size:16px;line-height:20px;Margin-bottom:14px}.two-col ol,.two-col p,.two-col ul{font-size:14px;line-height:23px}.two-col ol,.two-col ul{Margin-left:16px}.two-col li{padding-left:5px}.two-col .divider .inner{padding-bottom:23px}.two-col .btn{Margin-bottom:23px}.two-col blockquote{padding-left:16px}.three-col .column table:nth-last-child(2) td h1:last-child,.three-col .column table:nth-last-child(2) td h2:last-child,.three-col .column table:nth-last-child(2) td h3:last-child,.three-col .column table:nth-last-child(2) td ol:last-child,.three-col .column table:nth-last-child(2) td p:last-child,.three-col .column table:nth-last-child(2) td ul:last-child{Margin-bottom:21px}.three-col .image-frame{padding:4px}.three-col h1{font-size:20px;line-height:26px;Margin-bottom:12px}.three-col h2{font-size:16px;line-height:22px;Margin-bottom:14px}.three-col h3{font-size:14px;line-height:18px;Margin-bottom:10px}.three-col ol,.three-col p,.three-col ul{font-size:12px;line-height:21px}.three-col ol,.three-col ul{Margin-left:14px}.three-col li{padding-left:6px}.three-col .divider .inner{padding-bottom:21px}.three-col .btn{Margin-bottom:21px}.three-col .btn a{font-size:12px;line-height:14px;padding:8px 19px}.three-col blockquote{padding-left:16px}.one-col-feature .column-top{font-size:36px;line-height:36px}.one-col-feature .column-bottom{font-size:4px;line-height:4px}.one-col-feature .column{text-align:center;width:600px}.one-col-feature .column table:nth-last-child(2) td h1:last-child,.one-col-feature .column table:nth-last-child(2) td h2:last-child,.one-col-feature .column table:nth-last-child(2) td h3:last-child,.one-col-feature .column table:nth-last-child(2) td ol:last-child,.one-col-feature .column table:nth-last-child(2) td p:last-child,.one-col-feature .column table:nth-last-child(2) td ul:last-child,.one-col-feature .image{Margin-bottom:32px}.one-col-feature h1,.one-col-feature h2,.one-col-feature h3{text-align:center}.one-col-feature h1{font-size:52px;Margin-bottom:22px}.one-col-feature h2{font-size:42px;Margin-bottom:20px}.one-col-feature h3{font-size:32px;line-height:42px;Margin-bottom:20px}.one-col-feature ol,.one-col-feature p,.one-col-feature ul{font-size:21px;line-height:32px;Margin-bottom:32px}.one-col-feature ol a,.one-col-feature p a,.one-col-feature ul a{text-decoration:none}.one-col-feature p{text-align:center}.one-col-feature ol,.one-col-feature ul{Margin-left:40px;text-align:left}.one-col-feature li{padding-left:3px}.one-col-feature .btn{Margin-bottom:32px;text-align:center}.one-col-feature .divider .inner{padding-bottom:32px}.one-col-feature blockquote{border-bottom:2px solid #e9e9e9;border-left-color:#fff;border-left-width:0;border-left-style:none;border-top:2px solid #e9e9e9;Margin-bottom:32px;Margin-left:0;padding-bottom:42px;padding-left:0;padding-top:42px;position:relative}.one-col-feature blockquote:after,.one-col-feature blockquote:before{background:-moz-linear-gradient(left,#fff 25%,#e9e9e9 25%,#e9e9e9 75%,#fff 75%);background:-webkit-gradient(linear,left top,right top,color-stop(25%,#fff),color-stop(25%,#e9e9e9),color-stop(75%,#e9e9e9),color-stop(75%,#fff));background:-webkit-linear-gradient(left,#fff 25%,#e9e9e9 25%,#e9e9e9 75%,#fff 75%);background:-o-linear-gradient(left,#fff 25%,#e9e9e9 25%,#e9e9e9 75%,#fff 75%);background:-ms-linear-gradient(left,#fff 25%,#e9e9e9 25%,#e9e9e9 75%,#fff 75%);background:linear-gradient(to right,#fff 25%,#e9e9e9 25%,#e9e9e9 75%,#fff 75%);content:'';display:block;height:2px;left:0;outline:#fff solid 1px;position:absolute;right:0}.one-col-feature blockquote:before{top:-2px}.one-col-feature blockquote:after{bottom:-2px}.one-col-feature blockquote ol,.one-col-feature blockquote p,.one-col-feature blockquote ul{font-size:42px;line-height:48px;Margin-bottom:48px}.one-col-feature blockquote ol:last-child,.one-col-feature blockquote p:last-child,.one-col-feature blockquote ul:last-child{Margin-bottom:0!important}.footer{width:602px}.footer .padded{font-size:12px;line-height:20px}.social{padding-top:32px;padding-bottom:22px}.social img{display:block}.social .divider{font-family:sans-serif;font-size:10px;line-height:21px;text-align:center;padding-left:14px;padding-right:14px}.social .social-text{height:21px;vertical-align:middle!important;font-size:10px;font-weight:700;text-decoration:none;text-transform:uppercase}.social .social-text a{text-decoration:none}.address{width:250px}.address .padded{text-align:left;padding-left:0;padding-right:10px}.subscription{width:350px}.subscription .padded{text-align:right;padding-right:0;padding-left:10px}.address,.subscription{padding-top:32px;padding-bottom:64px}.address a,.subscription a{font-weight:700;text-decoration:none}.address table,.subscription table{width:100%}@media only screen and (max-width:651px){.gmail{display:none!important}}@media only screen and (max-width:620px){[class=wrapper] .one-col .column:last-child table:nth-last-child(2) td h1:last-child,[class=wrapper] .one-col .column:last-child table:nth-last-child(2) td h2:last-child,[class=wrapper] .one-col .column:last-child table:nth-last-child(2) td h3:last-child,[class=wrapper] .one-col .column:last-child table:nth-last-child(2) td ol:last-child,[class=wrapper] .one-col .column:last-child table:nth-last-child(2) td p:last-child,[class=wrapper] .one-col .column:last-child table:nth-last-child(2) td ul:last-child,[class=wrapper] .one-col-feature .column:last-child table:nth-last-child(2) td h1:last-child,[class=wrapper] .one-col-feature .column:last-child table:nth-last-child(2) td h2:last-child,[class=wrapper] .one-col-feature .column:last-child table:nth-last-child(2) td h3:last-child,[class=wrapper] .one-col-feature .column:last-child table:nth-last-child(2) td ol:last-child,[class=wrapper] .one-col-feature .column:last-child table:nth-last-child(2) td p:last-child,[class=wrapper] .one-col-feature .column:last-child table:nth-last-child(2) td ul:last-child,[class=wrapper] .three-col .column:last-child table:nth-last-child(2) td h1:last-child,[class=wrapper] .three-col .column:last-child table:nth-last-child(2) td h2:last-child,[class=wrapper] .three-col .column:last-child table:nth-last-child(2) td h3:last-child,[class=wrapper] .three-col .column:last-child table:nth-last-child(2) td ol:last-child,[class=wrapper] .three-col .column:last-child table:nth-last-child(2) td p:last-child,[class=wrapper] .three-col .column:last-child table:nth-last-child(2) td ul:last-child,[class=wrapper] .two-col .column:last-child table:nth-last-child(2) td h1:last-child,[class=wrapper] .two-col .column:last-child table:nth-last-child(2) td h2:last-child,[class=wrapper] .two-col .column:last-child table:nth-last-child(2) td h3:last-child,[class=wrapper] .two-col .column:last-child table:nth-last-child(2) td ol:last-child,[class=wrapper] .two-col .column:last-child table:nth-last-child(2) td p:last-child,[class=wrapper] .two-col .column:last-child table:nth-last-child(2) td ul:last-child{Margin-bottom:24px!important}[class=wrapper] .address,[class=wrapper] .subscription{display:block;float:left;width:318px!important;text-align:center!important}[class=wrapper] .address{padding-bottom:0!important}[class=wrapper] .subscription{padding-top:0!important}[class=wrapper] h1{font-size:36px!important;line-height:42px!important;Margin-bottom:18px!important}[class=wrapper] h2{font-size:26px!important;line-height:32px!important;Margin-bottom:20px!important}[class=wrapper] h3{font-size:18px!important;line-height:22px!important;Margin-bottom:16px!important}[class=wrapper] ol,[class=wrapper] p,[class=wrapper] ul{font-size:16px!important;line-height:24px!important;Margin-bottom:24px!important}[class=wrapper] ol,[class=wrapper] ul{Margin-left:18px!important}[class=wrapper] li{padding-left:2px!important}[class=wrapper] blockquote{padding-left:16px!important}[class=wrapper] .two-col .column:nth-child(n+3){border-top:1px solid #e9e9e9}[class=wrapper] .btn{margin-bottom:24px!important}[class=wrapper] .btn a{display:block!important;font-size:13px!important;font-weight:700!important;line-height:15px!important;padding:10px 30px!important}[class=wrapper] .column-bottom{font-size:8px!important;line-height:8px!important}[class=wrapper] .first .column-bottom,[class=wrapper] .second .column-top,[class=wrapper] .third .column-top,[class=wrapper] .three-col .second .column-bottom{display:none}[class=wrapper] .image-frame{padding:4px!important}[class=wrapper] .header .logo{padding-left:10px!important;padding-right:10px!important}[class=wrapper] .header .logo div{font-size:26px!important;line-height:32px!important}[class=wrapper] .header .logo div img{display:inline-block!important;max-width:280px!important;height:auto!important}[class=wrapper] .footer,[class=wrapper] .header,[class=wrapper] .webversion,[class=wrapper] table.border{width:320px!important}[class=wrapper] .header .logo a,[class=wrapper] .preheader .webversion{text-align:center!important}[class=wrapper] .border td,[class=wrapper] .preheader table{width:318px!important}[class=wrapper] .border td.border{width:1px!important}[class=wrapper] .image .border td{width:auto!important}[class=wrapper] .title{display:none}[class=wrapper] .footer .padded{text-align:center!important}[class=wrapper] .footer .subscription .padded{padding-top:20px!important}[class=wrapper] .footer .social-link{display:block!important}[class=wrapper] .footer .social-link table{margin:0 auto 10px!important}[class=wrapper] .footer .divider{display:none!important}[class=wrapper] .one-col-feature .btn,[class=wrapper] .one-col-feature .image{margin-bottom:28px!important}[class=wrapper] .one-col-feature .divider .inner{padding-bottom:28px!important}[class=wrapper] .one-col-feature h1{font-size:42px!important;line-height:48px!important;margin-bottom:20px!important}[class=wrapper] .one-col-feature h2{font-size:32px!important;line-height:36px!important;margin-bottom:18px!important}[class=wrapper] .one-col-feature h3{font-size:26px!important;line-height:32px!important;margin-bottom:20px!important}[class=wrapper] .one-col-feature ol,[class=wrapper] .one-col-feature p,[class=wrapper] .one-col-feature ul{font-size:20px!important;line-height:28px!important;margin-bottom:28px!important}[class=wrapper] .one-col-feature blockquote{font-size:18px!important;line-height:26px!important;margin-bottom:28px!important;padding-bottom:26px!important;padding-left:0!important;padding-top:26px!important}[class=wrapper] .one-col-feature blockquote ol,[class=wrapper] .one-col-feature blockquote p,[class=wrapper] .one-col-feature blockquote ul{font-size:26px!important;line-height:32px!important}[class=wrapper] .one-col-feature blockquote ol:last-child,[class=wrapper] .one-col-feature blockquote p:last-child,[class=wrapper] .one-col-feature blockquote ul:last-child{margin-bottom:0!important}[class=wrapper] .one-col-feature .column table:last-of-type h1:last-child,[class=wrapper] .one-col-feature .column table:last-of-type h2:last-child,[class=wrapper] .one-col-feature .column table:last-of-type h3:last-child{margin-bottom:28px!important}}@media only screen and (max-width:320px){[class=wrapper] td.border{display:none}[class=wrapper] .footer,[class=wrapper] .header,[class=wrapper] .webversion,[class=wrapper] table.border{width:318px!important}}</style><!--[if gte mso 9]> <style>.column-top{mso-line-height-rule: exactly !important;}</style><![endif]--> <meta name='robots' content='noindex,nofollow'/><meta property='og:title' content='My First Campaign'/></head> <body style='margin: 0;mso-line-height-rule: exactly;padding: 0;min-width: 100%;background-color: #fbfbfb'><style type='text/css'>@import url(https://fonts.googleapis.com/css?family=Open+Sans:400italic,700italic,700,400);body,.wrapper,.emb-editor-canvas{background-color:#fbfbfb}.border{background-color:#e9e9e9}h1{color:#5489f5}.wrapper h1{}.wrapper h1{font-family:sans-serif}@media only screen and (min-width: 0){.wrapper h1{font-family:'Open Sans',sans-serif !important}}h1{}.one-col h1{line-height:44px}.two-col h1{line-height:34px}.three-col h1{line-height:28px}.wrapper .one-col-feature h1{line-height:58px}@media only screen and (max-width: 620px){h1{line-height:44px !important}}h2{color:#555}.wrapper h2{}.wrapper h2{font-family:Georgia,serif}h2{}.one-col h2{line-height:32px}.two-col h2{line-height:26px}.three-col h2{line-height:22px}.wrapper .one-col-feature h2{line-height:52px}@media only screen and (max-width: 620px){h2{line-height:32px !important}}h3{color:#5c93e6}.wrapper h3{}.wrapper h3{font-family:sans-serif}h3{}.one-col h3{line-height:26px}.two-col h3{line-height:22px}.three-col h3{line-height:20px}.wrapper .one-col-feature h3{line-height:40px}@media only screen and (max-width: 620px){h3{line-height:26px !important}}p,ol,ul{color:#565656}.wrapper p,.wrapper ol,.wrapper ul{}.wrapper p,.wrapper ol,.wrapper ul{font-family:Georgia,serif}p,ol,ul{}.one-col p,.one-col ol,.one-col ul{line-height:25px;Margin-bottom:25px}.two-col p,.two-col ol,.two-col ul{line-height:23px;Margin-bottom:23px}.three-col p,.three-col ol,.three-col ul{line-height:21px;Margin-bottom:21px}.wrapper .one-col-feature p,.wrapper .one-col-feature ol,.wrapper .one-col-feature ul{line-height:32px}.one-col-feature blockquote p,.one-col-feature blockquote ol,.one-col-feature blockquote ul{line-height:50px}@media only screen and (max-width: 620px){p,ol,ul{line-height:25px !important;Margin-bottom:25px !important}}.image{color:#565656}.image{font-family:Georgia,serif}.wrapper a{color:#41637e}.wrapper a:hover{color:#30495c !important}.wrapper .logo div{color:#41637e}.wrapper .logo div{font-family:sans-serif}@media only screen and (min-width: 0){.wrapper .logo div{font-family:Avenir,sans-serif !important}}.wrapper .logo div a{color:#41637e}.wrapper .logo div a:hover{color:#41637e !important}.wrapper .one-col-feature p a,.wrapper .one-col-feature ol a,.wrapper .one-col-feature ul a{border-bottom:1px solid #41637e}.wrapper .one-col-feature p a:hover,.wrapper .one-col-feature ol a:hover,.wrapper .one-col-feature ul a:hover{color:#30495c !important;border-bottom:1px solid #30495c !important}.btn a{}.wrapper .btn a{}.wrapper .btn a{font-family:Georgia,serif}.wrapper .btn a{background-color:#41637e;color:#fff !important;outline-color:#41637e;text-shadow:0 1px 0 #3b5971}.wrapper .btn a:hover{background-color:#3b5971 !important;color:#fff !important;outline-color:#3b5971 !important}.preheader .title,.preheader .webversion,.footer .padded{color:#999}.preheader .title,.preheader .webversion,.footer .padded{font-family:Georgia,serif}.preheader .title a,.preheader .webversion a,.footer .padded a{color:#999}.preheader .title a:hover,.preheader .webversion a:hover,.footer .padded a:hover{color:#737373 !important}.footer .social .divider{color:#e9e9e9}.footer .social .social-text,.footer .social a{color:#999}.wrapper .footer .social .social-text,.wrapper .footer .social a{}.wrapper .footer .social .social-text,.wrapper .footer .social a{font-family:Georgia,serif}.footer .social .social-text,.footer .social a{}.footer .social .social-text,.footer .social a{letter-spacing:0.05em}.footer .social .social-text:hover,.footer .social a:hover{color:#737373 !important}.image .border{background-color:#c8c8c8}.image-frame{background-color:#dadada}.image-background{background-color:#f7f7f7}</style> <center class='wrapper' style='display: table;table-layout: fixed;width: 100%;min-width: 620px;-webkit-text-size-adjust: 100%;-ms-text-size-adjust: 100%;background-color: #fbfbfb'> <table class='gmail' style='border-collapse: collapse;border-spacing: 0;width: 650px;min-width: 650px'><tbody><tr><td style='padding: 0;vertical-align: top;font-size: 1px;line-height: 1px'>&nbsp;</td></tr></tbody></table> <table class='preheader centered' style='border-collapse: collapse;border-spacing: 0;Margin-left: auto;Margin-right: auto'> <tbody><tr> <td style='padding: 0;vertical-align: top'> <table style='border-collapse: collapse;border-spacing: 0;width: 602px'> <tbody><tr> <td class='title' style='padding: 0;vertical-align: top;padding-top: 10px;padding-bottom: 12px;font-size: 12px;line-height: 21px;text-align: left;color: #999;font-family: Georgia,serif'>Welcome to your AWMPass&nbsp;</td><td style='padding: 0;vertical-align: top;padding-top: 10px;padding-bottom: 12px;font-size: 12px;line-height: 21px;text-align: right;width: 300px;color: #999;font-family: Georgia,serif'> Any Problems? <a href='http://awme.net/contact/website'>Contact Us</a> </td></tr></tbody></table> </td></tr></tbody></table> <table class='header centered' style='border-collapse: collapse;border-spacing: 0;Margin-left: auto;Margin-right: auto;width: 602px'> <tbody><tr><td class='border' style='padding: 0;vertical-align: top;font-size: 1px;line-height: 1px;background-color: #e9e9e9;width: 1px'>&nbsp;</td></tr><tr><td class='logo' style='padding: 32px 0;vertical-align: top;mso-line-height-rule: at-least'><div class='logo-center' style='font-size: 26px;font-weight: 700;letter-spacing: -0.02em;line-height: 32px;color: #41637e;font-family: sans-serif;text-align: center' align='center' id='emb-email-header'><a style='text-decoration: none;transition: all .2s;color: #41637e' href='http://www.awme.net'><img style='border: 0;-ms-interpolation-mode: bicubic;display: block;Margin-left: auto;Margin-right: auto;max-width: 507px' src='http://i.awme.net/logos/awm-logo.png' alt='Andrew Wommack Ministries Europe' width='338' height='144'/></a></div></td></tr></tbody></table> <table class='border' style='border-collapse: collapse;border-spacing: 0;font-size: 1px;line-height: 1px;background-color: #e9e9e9;Margin-left: auto;Margin-right: auto' width='602'> <tbody><tr><td style='padding: 0;vertical-align: top'>&#8203;</td></tr></tbody></table> <table class='centered' style='border-collapse: collapse;border-spacing: 0;Margin-left: auto;Margin-right: auto'> <tbody><tr> <td class='border' style='padding: 0;vertical-align: top;font-size: 1px;line-height: 1px;background-color: #e9e9e9;width: 1px'>&#8203;</td><td style='padding: 0;vertical-align: top'> <table class='one-col' style='border-collapse: collapse;border-spacing: 0;Margin-left: auto;Margin-right: auto;width: 600px;background-color: #ffffff;font-size: 14px;table-layout: fixed'> <tbody><tr> <td class='column' style='padding: 0;vertical-align: top;text-align: left'> <div><div class='column-top' style='font-size: 32px;line-height: 32px'>&nbsp;</div></div><table class='contents' style='border-collapse: collapse;border-spacing: 0;table-layout: fixed;width: 100%'> <tbody><tr> <td class='padded' style='padding: 0;vertical-align: top;padding-left: 32px;padding-right: 32px;word-break: break-word;word-wrap: break-word'> <h1 style='Margin-top: 0;color: #1675A9;font-weight: 700;font-size: 36px;Margin-bottom: 18px;font-family: sans-serif;line-height: 44px'><strong style='font-weight: bold'>Your Password has been reset</strong></h1>
					//		<p style='Margin-top: 0;color: #565656;font-family: Helvetica, Arial, sans-serif;font-size: 16px;line-height: 25px;Margin-bottom: 25px'>
					//		Hello,<br/>
					//		Your password has now been reset to: \"<b>{$newpassword}</b>\" (Without the quotation marks)<br/>
					//		You can now use this to log into <a href='http://{$_SERVER['HTTP_HOST']}'>http://{$_SERVER['HTTP_HOST']}</a>.  We strongly advise that you log in as soon as possible and update your password to something personal and memorable to you.
					//		</p>
					//		<p style='Margin-top: 0;color: #565656;font-family: Helvetica, Arial, sans-serif;font-size: 16px;line-height: 25px;Margin-bottom: 25px'><b>Did you know?</b><br/>There are so many great resources on <a href='http://awme.net'>AWME.net</a>, from free teachings to <a href='http://www.awme.net/audio'>mp3 downloads</a>, not to mention our <a href='http://awme.net/shop'>online shop<a/> that is overflowing with Andrews <a href='http://www.awme.net/shop/list/10'>Books</a>, <a href='http://www.awme.net/shop/list/75'>DVDs</a>, <a href='http://www.awme.net/shop/list/117'>Study Guides</a> and much more!<br/><br/>If however you like to <i>watch</i> Andrew teach, be sure to visit <a href='http://gospeltruth.tv/'>GospelTruth.tv</a> to find a whole range of teachings organised by subject.</p></td></tr></tbody></table> <div class='column-bottom' style='font-size: 8px;line-height: 8px'>&nbsp;</div></td></tr></tbody></table> </td><td class='border' style='padding: 0;vertical-align: top;font-size: 1px;line-height: 1px;background-color: #e9e9e9;width: 1px'>&#8203;</td></tr></tbody></table> 
					//		<table class='border' style='border-collapse: collapse;border-spacing: 0;font-size: 1px;line-height: 1px;background-color: #e9e9e9;Margin-left: auto;Margin-right: auto' width='602'> <tbody><tr class='border' style='font-size: 1px;line-height: 1px;background-color: #e9e9e9;height: 1px'> <td class='border' style='padding: 0;vertical-align: top;font-size: 1px;line-height: 1px;background-color: #e9e9e9;width: 1px'>&#8203;</td><td style='padding: 0;vertical-align: top;line-height: 1px'>&#8203;</td><td class='border' style='padding: 0;vertical-align: top;font-size: 1px;line-height: 1px;background-color: #e9e9e9;width: 1px'>&#8203;</td></tr></tbody></table> <table class='centered' style='border-collapse: collapse;border-spacing: 0;Margin-left: auto;Margin-right: auto'> <tbody><tr> <td style='padding: 0;vertical-align: top'><table class='border' style='border-collapse: collapse;border-spacing: 0;font-size: 1px;line-height: 1px;background-color: #e9e9e9;Margin-left: auto;Margin-right: auto' width='602'> <tbody><tr><td style='padding: 0;vertical-align: top'>&#8203;</td></tr></tbody></table> <table class='footer centered' style='border-collapse: collapse;border-spacing: 0;Margin-left: auto;Margin-right: auto;width: 602px'> <tbody> <tr> <td style='padding: 0;vertical-align: top'> <table style='border-collapse: collapse;border-spacing: 0'> <tbody><tr> <td class='address' style='padding: 0;vertical-align: top;width: 250px;padding-top: 32px;padding-bottom: 64px'> <table class='contents' style='border-collapse: collapse;border-spacing: 0;table-layout: fixed;width: 100%'> <tbody><tr> <td class='padded' style='padding: 0;vertical-align: top;padding-left: 0;padding-right: 10px;word-break: break-word;word-wrap: break-word;text-align: left;font-size: 12px;line-height: 20px;color: #999;font-family: Georgia,serif'> <div>Andrew Wommack Ministries Europe<br/>Call Us: +44(0) 1922 473300</div></td></tr></tbody></table> </td><td class='subscription' style='padding: 0;vertical-align: top;width: 350px;padding-top: 32px;padding-bottom: 64px'> <table class='contents' style='border-collapse: collapse;border-spacing: 0;table-layout: fixed;width: 100%'> <tbody><tr> <td class='padded' style='padding: 0;vertical-align: top;padding-left: 10px;padding-right: 0;word-break: break-word;word-wrap: break-word;font-size: 12px;line-height: 20px;color: #999;font-family: Georgia, serif;text-align: right'> <div>Manage your account settings at awme.net/account</div><div> <span class='block'> <span> <preferences style='font-weight:bold;text-decoration:none;' lang='en'> <a href='/account/settings'>Preferences</a></preferences> <span class='hide'>&nbsp;&nbsp;|&nbsp;&nbsp;</span> </span> </span> <span class='block'><unsubscribe style='font-weight:bold;text-decoration:none;'><a href='/account/settings'>Unsubscribe</a></unsubscribe></span> </div></td></tr></tbody></table> </td></tr></tbody></table> </td></tr></tbody></table> </center> </body></html>";
					
					ob_start();
					include($this->email_files['forgotten_password_reset']);
					$mailbody = ob_get_clean(); 		
							
					$message = Swift_Message::newInstance("Password Reset Notification")   
								->setFrom(array('no-reply@awme.net' => 'AWMPass'))
								->setTo($check_user['email'])
								->setBody($mailbody,'text/html');
					$mailresult = $this->swiftmail->send($message);
					if($mailresult) {
						$this->form_status = "sent-reset-success";
						$this->activity("Successfully reset password.",31,$check_user['id']);
					} else {
						$this->output = "We had a problem sending an email.  This is most likely a server issue, please try again later.";
						$this->form_status = "sent-reset-failure";
					}
				} else {
					$this->output = "There was a problem accessing the database.  We are working to get it fixed as soon as we can.";
					$this->form_status = "sent-reset-failure";	
				}
			} else {
				$this->output = "Unable to determine user identity.";
				$this->form_status = "sent-reset-failure";	
			}
		} else {
			$this->form_status = "sent-reset-failure";
			$this->output = "The link you are trying to access appears to be incorrect.  Please double check your email to try again.";
		}
	}
	            
	public function form_display($url = false) {
		if(isset($this->uri[1])) {
			switch($this->uri[1]) {
				case "forgotten-password": echo $this->form_forgot(($url ? $url : $this->post_url['forgot'])); break;
				case "create-account": 
					echo ($this->display_create_account ? $this->form_create(($url ? $url : $this->post_url['create'])) : $this->form_login(($url ? $url : $this->post_url['login'])));
				break;
				default: case false: echo $this->form_login(($url ? $url : $this->post_url['login'])); break;
			} 
		} else {
			echo $this->form_login(($url ? $url : $this->post_url['login']));
		}
	}
	public function form_login($url = false) {
		$hiddenvars = "";
		
		// Stylize the output if there is any.  Assume it's negative, unless there is a colon (:) at the beginning.
		$outputCSS = "background:#F7E7E7;border-bottom:2px solid #F3BFBF;color:#f00;";
		if($this->output) {
			if(substr($this->output,0,1) == ":") {$outputCSS = "background: #DBF3DC;border-bottom:2px solid #71BF6C;color: #1B7A1F;"; $this->output = substr($this->output,1);}
		}
		
		foreach($this->hiddenvars as $key => $value) {$hiddenvars .= "<input type='hidden' name='{$key}' value='{$value}' />";}
		$return = 	"<!--Authentication Login Form-->\n".($this->container ? "<div class='container-fluid'><div id='login' class='container' style='{$this->container_style['login']}'>" : false) 
					. "<form action='{$this->post_url['login']}' method='POST'>
					<input type='hidden' name='authenticate' value='login'>{$hiddenvars}
					".(is_string($this->img) ? "<div class='row text-center' style='margin-top:2vh;margin-bottom:2vh'><img src='{$this->img}' class='login-logo' style='max-width:60%;'/></div>" : false)."
					<div class='row' style='margin-top:10px;'>
						<div class='col-xs-6 col-sm-8 col-md-9'><b>Email Address</b></div>
						<div class='col-xs-6 col-sm-4 col-md-3 text-right'><img src='https://i.awme.net/logos/awmpass.png' class='img-responsive'></div>
					</div>                                                  
					<div class='row' style='margin-bottom:10px;'><div class='col-md-12'><input type='email' name='email' class='form-control' placeholder='Email' value='".(isset($_POST['email']) ? $_POST['email'] : '')."'></div></div>
					<div class='row'><div class='col-md-12'><b>Password</b></div></div>
					<div class='row' style='margin-bottom:10px;'>
						<div class='col-xs-8 col-sm-8 col-md-8'>
							<input type='password' name='password' placeholder='AWMPass Password' class='form-control'>
							<div style='float:left;display:inline-block;font-size:11px;font-family=Verdana;'><label><input type='checkbox' style='vertical-align:bottom;'name='remember_me'>&nbsp;Remember Me</label></div>
							<div style='float:right;display:inline-block;font-family=Verdana'><a href='#' id='show-console' style='cursor:default;width:10px;height:10px;display:block;' onClick='event.preventDefault(); $(\"#console\").slideToggle();'></a></div>
						</div>
						<div class='col-xs-4 col-sm-4 col-md-4 text-right'>
							<button type='submit' class='btn btn-success'><span class='glyphicon glyphicon-log-in' style='margin-right:10px;'></span>Login</button>
						</div>
					</div>
					".($this->output ? "<div class='row' style='padding:10px;font-weight:bold;{$outputCSS}'><div class='col-md-12 text-center'>{$this->output}</div></div>" : false)."
					<div class='row trouble' style='font-size: 12px;background:rgba(240,240,240,0.7);padding:10px;'>
						<div class='col-xs-6 col-sm-6 col-md-6'>
							".($this->display_create_account ? "<a href='".(is_string($this->display_create_account) ? $this->display_create_account : "{$this->post_url['create']}")."' style='font-family:Verdana;color:#111;font-weight:bold;'>Create account..</a>" : false)."
						</div>
						<div class='col-xs-6 col-sm-6 col-md-6 text-right'>
							<a href='{$this->post_url['forgot']}' style='font-family:Verdana;color:#111;font-weight:bold;'>I forgot my password</a>
						</div>
					</div>
					<div id='console' class='row' style='display:none;background:#000;color:#0f0;padding:10px;font-size:12px;line-height:12px;font-family:\"Courier New\",Courier,monospace;'>".implode("<br/>",str_replace("<b>","<b style='color:#fff;'>",$this->console))."</div>
					</form>";
		
		if($this->container) {
			$return .= "</div></div>";
		}
					
		return $return;
	}
	public function form_create($url = false) {
		$post_url = ($url ? $url : $this->post_url['create']);
		
		$hiddenvars = "";
		foreach($this->hiddenvars as $key => $value) {$hiddenvars .= "<input type='hidden' name='{$key}' value='{$value}' />";}
		
		switch($this->form_status) {
		case false: // Form hasn't been POSTed yet or there were errors.
		$return = "<!--Authentication Create Account Form-->\n
					<script src='https://www.google.com/recaptcha/api.js'></script>
					".($this->container ? "<div class='container-fluid'><div id='login' class='container' style='{$this->container_style['create']}'>" : false).
					(is_string($this->img) ? "<div class='row text-center' style='margin-top:2vh;margin-bottom:2vh'><img src='{$this->img}' class='login-logo' style='max-width:60%;'/></div>" : false).
					"<form action='{$this->post_url['create']}' method='POST'>
					<input type='hidden' name='authenticate' value='create_account' />{$hiddenvars}			
					<div class='row'>
						<div class='col-xs-6 col-sm-8 col-md-9'><b>Your Email Address</b></div>
						<div class='col-xs-6 col-sm-4 col-md-3 text-right'><img src='https://i.awme.net/logos/awmpass.png' class='img-responsive'></div>
					</div>
					<div class='row' style='margin-bottom:10px;'><div class='col-md-12'><input type='email' name='email' class='form-control".(isset($this->errors['email']) ? " awmpass-error" : false)."' placeholder='Email' value='".(isset($_POST['email']) ? $_POST['email'] : false)."'></div></div>
					".(isset($this->errors['email']) ? "<div class='row' style='color:#f00;padding:5px;background:#F7E7E7;margin-bottom:10px'><div class='col-xs-12 awmpass-error'>{$this->errors['email']}</div></div>" : false)."
					<div class='row'><div class='col-md-12'><b>Your Full Name</b></div></div>
					<div class='row' style='margin-bottom:10px;'><div class='col-md-12'><input type='text' name='name' class='form-control".(isset($this->errors['email']) ? " awmpass-error" : false)."' placeholder='Mr John Smith' value='".(isset($_POST['name']) && (!is_array($_POST['name'])) ? $_POST['name'] : false)."'></div></div>
					".(isset($this->errors['name']) ? "<div class='row' style='color:#f00;padding:5px;background:#F7E7E7;margin-bottom:10px'><div class='col-xs-12 awmpass-error'>{$this->errors['name']}</div></div>" : false)."
					<div class='row'>
						<div class='col-xs-12 col-sm-6 col-md-6'><b>Your Password</b></div>
						<div class='col-xs-12 col-sm-6 col-md-6'><b>Confirm Password</b></div>
					</div>
					<div class='row' style='margin-bottom:10px;'>
						<div class='col-xs-12 col-sm-6 col-md-6'><input type='password' name='password' placeholder='Type a new password' class='form-control".(isset($errors['password']) ? " awmpass-error" : false)."'></div>
						<div class='col-xs-12 col-sm-6 col-md-6'><input type='password' name='confirm-password' placeholder='Confirm your password' class='form-control".(isset($errors['password']) ? " awmpass-error" : false)."'></div>
					</div>
					".(isset($this->errors['password']) ? "<div class='row' style='color:#f00;padding:5px;background:#F7E7E7;margin-bottom:10px'><div class='col-xs-12 awmpass-error'>{$this->errors['password']}</div></div>" : false)."
					<div class='row'><div class='col-md-12'>
							<b>Can we email you?</b>
							<p>We promise we won't send you spam, but if an offer or two comes up, we'll let you know.</p>
						</div>
					</div>                                           
					<div class='row' style='margin-bottom:10px;'><div class='col-md-12'>
						<div class='input-group'>
							<span class='input-group-addon'><input type='checkbox' class='dynamic' name='send-mail'></span>
							<label class='form-control'>Allow AWME to send you the odd email or two</label>
						</div>
					</div></div>
					
					<div class='row'><div class='col-md-12'><b>Are you human?</b></div></div>                                                  
					<div class='row' style='margin-bottom:10px;'>
						<div class='col-xs-12 text-center'>
							<div class='g-recaptcha' data-sitekey='6LfQvhoUAAAAAIu5LQC5_PzLB9w8SYeMYaK_oKvk'></div>
						</div>
					</div>
					".(isset($this->errors['recaptcha']) ? "<div class='row' style='color:#f00;padding:5px;background:#F7E7E7;margin-bottom:10px'><div class='col-xs-12 awmpass-error'>{$this->errors['recaptcha']}</div></div>" : false)."
					<div class='row' style='margin-bottom:20px;'>
						<div class='col-xs-6 col-sm-6 col-md-6'><a href='{$this->post_url['login']}' class='btn btn-default'>Cancel</a></div>
						<div class='col-xs-6 col-sm-6 col-md-6 text-right'><button type='submit' class='btn btn-primary'>Create Account</button></div>
					</div>
					</form>".($this->container ? "</div></div>" : false);
		break;
		case true:
			$return = "<!--Authentication Login Form-->\n" . 
					($this->container ? "<div class='container-fluid'><div id='login' class='container' style='border-radius:4px;border: 2px solid #DDD;max-width:520px;margin:5vh auto;".($this->transparent ? false : "background:#fff;")."'>" : false).
					(is_string($this->img) ? "<div class='row text-center' style='margin-top:2vh;margin-bottom:2vh'><img src='{$this->img}' class='login-logo' style='max-width:60%;'/></div>" : false).
					"<div class='row' style='margin-bottom:10px;'>
							<div class='col-md-12'><h2>Hallelujah!</h2></div>
						</div>
						".($this->output ? "<div class='row' style='margin-top:10px;margin-bottom:10px;padding:10px;background: #C6FBC6;'><div class='col-md-12 text-center'><b style='color:#2B9413;'>{$this->output}</b></div></div>" : false)."
						<div class='row' style='margin-bottom:20px;'>
							<div class='col-md-12'>
								Your account was created successfully.  In a moment, you'll receive a confirmation E-Mail to <b>{$this->email}</b>, which will give you a bit more information about  your AWMPass and what you can get out of it! 
								<br/><br/>
								Of course, there is nothing to stop you from logging in <i>right now,</i> - simply click the button below to be taken to the Login page and type the E-Mail address and password you just used to sign up, and you'll have access right away.
								<br/><br/>
							</div>
						</div>
						<div class='row' style='margin-bottom:15px;'>
							<div class='col-xs-12 col-sm-12 col-md-12 text-right'><a href='{$this->post_url['login']}' class='btn btn-success'><span class='fa fa-arrow-right' style='margin-right:10px;'></span>Log In</a></div>
						</div>".($this->container ? "</div></div>" : false);;
		break;
		}
		
		
		
		
		return $return;
	}
	public function form_forgot($url = false) {
		$post_url = ($url ? $url : $this->post_url['forgot']);
		$hiddenvars = "";
		foreach($this->hiddenvars as $key => $value) {$hiddenvars .= "<input type='hidden' name='{$key}' value='{$value}' />";}
		
		$return = "<!--Authentication Login Form-->\n" . 
					($this->container ? "<div class='container-fluid'><div id='login' class='container' style='{$this->container_style['forgot']}'>" : false).
					(is_string($this->img) ? "<div class='row text-center' style='margin-top:2vh;margin-bottom:2vh'><img src='{$this->img}' class='login-logo' style='max-width:60%;'/></div>" : false);
		switch($this->form_status) {  
	    case "sent-reset-failure":
			$return .= "<div class='row' style='margin-bottom:10px;'>
							<div class='col-md-12'><h2>There was a problem..</h2></div>
						</div>
						".($this->output ? "<div class='row' style='margin-top:10px;margin-bottom:10px;padding:10px;background:#F7E7E7;'><div class='col-md-12 text-center'><b style='color:#f00;'>{$this->output}</b></div></div>" : false)."
						<div class='row' style='margin-bottom:20px;'>
							<div class='col-md-12'>
								We had a problem trying to reset your password.
								<br/><br/>
								You can <a href='{$this->post_url['forgot']}'>try again</a> or you <a href='https://www.awme.net/contact/website'>contact us here</a> to see if we can do anything about it.
							</div>
						</div>";  
		break;  
		case "sent-reset-success":
			$return .= "<div class='row' style='margin-bottom:10px;'>
						<div class='col-md-12'><h2>We've sent you an E-Mail!</h2></div>
						</div>
						<div class='row' style='margin-bottom:20px;'>
							<div class='col-md-12'>
								We've sent another email to <b>{$this->email}</b> containing your new password.
								<br/><br/>
								Please <a href='{$this->post_url['login']}'>login to your AWMPass here</a> to change your password to something personal and memorable as soon as you can - that way your account stays secure.
								<br/><br/>
								If you didn't receive the email, or you are having other problems with your account, <a href='https://www.awme.net/contact/website'>contact us here.</a>
							</div>
						</div>
						<div class='row' style='margin-bottom:20px;'>
							<div class='col-md-12 text-right'>
								<a href='{$this->post_url['login']}' name='password-reset-success' class='btn btn-success' alt='Login' title='Login'><span class='fa fa-lock'></span>Login</a>
							</div>
						</div>
						
						";  
		break;
		case "sent-request-failure":
			$return .= "<div class='row' style='margin-bottom:10px;'>
							<div class='col-md-12'><h2>There was a problem..</h2></div>
						</div>
						<div class='row'>
							<div class='col-md-12' style='margin-bottom:20px;'>
								We had a problem sending an email to <b>{$this->email}.</b>  The response from the server said:
								".($this->output ? "<div class='row' style='margin-top:10px;margin-bottom:10px;padding:10px;background:#F7E7E7;'><div class='col-md-12 text-center'><b style='color:#f00;'>{$this->output}</b></div></div>" : false)."
								You can <a href='/login/forgotten-password'>try again</a> or you <a href='https://www.awme.net/contact/website'>contact us here</a> to see if we can do anything about it.
							</div>
						</div>
						<div class='row' style='margin-bottom:15px;'>
							<div class='col-xs-6 col-sm-6 col-md-6'><a href='{$this->post_url['forgot']}' class='btn btn-default'>Try Again..</a></div>
							<div class='col-xs-6 col-sm-6 col-md-6 text-right'><a href='//awme.net/contact/website' class='btn btn-primary'><span class='fa fa-envelope-o' style='margin-right:10px;'></span>Contact Us</a></div>
						</div>";  
		break;
		case "sent-request-success":
			$return .=  "<div class='row' style='margin-bottom:10px;'>
						<div class='col-md-12'><h2>We've sent you an E-Mail!</h2></div>
						</div>
						<div class='row' style='margin-bottom:20px;'><div class='col-md-12'>
						We've sent an email to <b>{$_POST['email']}</b>.  Please allow a few minutes for the email to arrive, and be sure to check that it hasn't been placed in to you <b>spam or junk</b> folders.
						<br/><br/>
						If you didn't receive the email, or you are having other problems with your account, <a href='https://www.awme.net/contact/website'>contact us here.</a>
						</div></div>";
			break;
		default: case false: case "":
			$return .= "<form action='{$this->post_url['forgot']}' method='POST'>
							<input type='hidden' name='authenticate' value='forgotten_password_request' />{$hiddenvars}
							<input type='hidden' name='pageroot' value='{$this->post_url['forgot']}' />
							<div class='row' style='margin-bottom:10px;'>
								<div class='col-md-12'><h2>Forgotten Your Password?</h2></div>
							</div>
							<div class='row' style='margin-bottom:20px;'>
								<div class='col-md-12'>
									No problem!  Simply enter your email address below and we'll send you an email with details as to what to do next.
									<br/><br/>
									If you didn't receive the email, or you are having other problems with your account, <a href='https://www.awme.net/contact/website'>contact us here.</a>
								</div>
							</div>                   
							<div class='row'>
								<div class='col-xs-6 col-sm-8 col-md-8'><b>Your Email Address</b></div>
								<div class='col-xs-6 col-sm-4 col-md-4 text-right'>
									<a href='//awme.net/awmpass' target='_blank' alt='What is AWMPass?\n\nClick to find out more.' title='What is AWMPass? Click to find out more.'>
										<img src='https://i.awme.net/logos/awmpass.png' class='awmpass-info-logo' style='max-width:70px;float:right;'>
									</a>
								</div>
							</div>                                                  
							<div class='row' style='margin-bottom:10px;'><div class='col-md-12'><input type='email' name='email' class='form-control' placeholder='Email Address'></div></div>
							<div class='row' style='margin-bottom:15px;'>
								<div class='col-xs-6 col-sm-6 col-md-6'><a href='{$this->post_url['login']}' class='btn btn-default'>Cancel</a></div>
								<div class='col-xs-6 col-sm-6 col-md-6 text-right'><button type='submit' class='btn btn-primary'><span class='fa fa-envelope-o' style='margin-right:10px;'></span>Send Email</button></div>
							</div>
						</form>";
		break;
		}
		$return .= ($this->container ? "</div></div>" : false);
		return $return;
	}

	private function get_top_domain_name() {
		$exp = explode(".",$_SERVER['SERVER_NAME']);
		return ".".$exp[count($exp)-2].".".$exp[count($exp)-1];
	}
	private function find_auth_error() {
		// Perform additional checks for our logs.
		$email_check = $password_check = $access_domain_check = $access_level_check = false;
		$email_check = $this->db->select("SELECT `id`,`email` FROM `{$this->authCredentials['database']}`.`{$this->authCredentials['table']}` WHERE `email` = '{$this->email}';",true);
		if($email_check) {
			// If the email address does not exist at all, then don't bother with the other checks.
			$password_check = $this->db->select("SELECT `password` FROM `{$this->authCredentials['database']}`.`{$this->authCredentials['table']}` WHERE `email` = '{$this->email}' AND `password` = '{$this->password}';",true);
			if($email_check) {
				$access_domain_check = $this->db->select("SELECT `domain` FROM `{$this->authCredentials['database']}`.`access` WHERE `userID`='{$email_check['id']}' AND `domain` = '{$_SERVER['SERVER_NAME']}';",true,true);
				$access_level_check = $this->db->select("SELECT `level` FROM `{$this->authCredentials['database']}`.`access` WHERE `userID`='{$email_check['id']}' AND `domain` = '{$_SERVER['SERVER_NAME']}';",true,true);
			}                                        
			if($email_check) 			{$this->console[] = "Email verified.";}else{$this->console[] = "<b>Email not verified.</b>";}
			if($password_check) 		{$this->console[] = "Password correct.";}else{$this->console[] = "<b>Invalid Password.</b>";}
			if($access_domain_check)	{$this->console[] = "Domain access verified.";}else{$this->console[] = "<b>Domain access not verified.</b>";}
			if($access_level_check > 0) 	{$this->console[] = "Domain access level verified.";}else{$this->console[] = "<b>Domain access level not verified.</b>";}
		}
		// Set $error_msg for /login page to display the results.
		if((!$email_check) || (!$password_check)) {
			$error_msg = "Incorrect password.";
			if(!$email_check) {$error_msg = "Email address not recognised.";}
		} else {
			if($access_level_check <= 0) $error_msg = "You do not have access to this area.";
			if(!$access_domain_check) $error_msg = "You do not have access to this domain.";
		}
		// Save activity in DB
		if($email_check) { 
			$this->activity("User #{$email_check['id']} ({$this->email}) failed to login.",2,true);       
		} else {
			$this->activity("{$this->email} failed to login.",2,true);
		}
		if($error_msg) {$this->console[] = "Failure Message: <b>{$error_msg}</b>";}
		return $error_msg;
	}
	public function hashpassword($string = false) {
		if($string) {return hash("sha256", $this->salt . $string . $this->salt);}
		return false;
	}
	
	
	public function get_me($id) {
		$me = array();
		$this->db->prepared = array("userID" => $id); 
		$me = $this->db->select("SELECT * FROM `{$this->authCredentials['database']}`.`{$this->authCredentials['table']}` WHERE `{$this->authCredentials['database']}`.`{$this->authCredentials['table']}`.`id` = :userID;",true);
		
		
		
		if(($this->db->tableExists("user-data")) && ($this->db->tableExists("user-data-keys"))) {
			$this->db->prepared = array("userID" => $id);
			$user_data = $this->db->select("SELECT `key`,`val` FROM `user-data` RIGHT JOIN `user-data-keys` ON `user-data`.`keyID` = `user-data-keys`.`id` AND  `userID` = :userID;");
			if($user_data) {
				foreach($user_data as $u) {
					$me[$u['key']] = $u['val'];
				}
			}
		}
		
		$this->db->prepared = array("userID" => $id, "domain" => $_SERVER['SERVER_NAME']);
		$me['access'] = $this->db->select("SELECT `level` FROM `{$this->authCredentials['database']}`.`access` WHERE `userID` = :userID AND `domain` = :domain;",true,true);
		
	
		#if($this->db->tableExists("activity")) {
		#	$this->db->prepared = array("userID" => $id);
		#	$me['activity'] = $this->db->select("SELECT `userID`, `name`,
		#												`users`.`ts` AS `userdate`,
		#												`activity`.`ts`,
		#												`activity`,
		#												`activity`.`domain`,
		#												`activity`.`flag`,
		#												`activity-flags`.`description`, `color` 
		#												FROM `activity`
		#												LEFT JOIN `activity-flags` ON `activity-flags`.`flag` = `activity`.`flag`
		#												LEFT JOIN `users` ON `activity`.`userID` = `users`.`id`
		#												WHERE (`activity`.`ts` >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)) AND `userID`=:userID ORDER BY `activity`.`ts` DESC LIMIT 5;");	
		#} else {
		#	$me['activity'] = false;
		#}   
		
		
		
		

		#var_dump(function_exists("this->authDB->tableExists"));
		if($this->db->tableExists("domains")) {
			$this->db->prepared = array("userID" => $id);
			$me['domains'] = $this->db->select("SELECT `access`.`id`,`access`.`domain`,`domains`.`description`,`level`,`title`,`icon` FROM `access` LEFT JOIN `domains` ON `access`.`domain` = `domains`.`domain` WHERE `userID` = :userID;");
		} else {
			$this->db->prepared = array("userID" => $id);
			$me['domains'] = $this->db->select("SELECT * FROM `access` WHERE `userID` = :userID;");
		}
		return $me;
	}     
	public function saveUserData($userID, $key, $val) {
		if((!$userID) || (!$key)) return false;
		$updatedUserFlags = false;
		$this->db->prepared = array("key" => $key, "userID" => $userID);
		$checkUserData = $this->db->select("SELECT `user-data-keys`.`id`,`val` FROM `user-data` 
										LEFT JOIN `user-data-keys` ON `user-data`.`keyID` = `user-data-keys`.`id` 
										WHERE `key` = :key AND `userID` = :userID",true);
		if($checkUserData) {
			// It exists, UPDATE it.	
			$this->db->prepared = array("value" => $val, "userID" => $userID, "keyID" => $checkUserData['id']);
			if($this->db->runSQL("UPDATE `user-data` SET `val` = :value WHERE `userID` = :userID AND `keyID` = :keyID")) {$updatedUserFlags = true;}
		} else {
			// It doesnt exist, INSERT it.
			$this->db->prepared = array("userID" => $userID,"key" => $key,"value"=>$val);
			if($this->db->runSQL("INSERT INTO `user-data` (`userID`,`keyID`,`val`) VALUES (:userID,(SELECT `id` FROM `user-data-keys` WHERE `key` = :key),:value);")) {$updatedUserFlags = true;}
			
		}
		return $updatedUserFlags;
	}
	public function deleteUserData($userID, $key) {
		if((!$userID) || (!$key)) return false;
		$this->db->prepared = array("key" => $key, "userID" => $userID);
		return $this->db->runSQL("DELETE FROM `user-data` WHERE `keyID` = (SELECT `id` FROM `user-data-keys` WHERE `key` = :key) AND `userID` = :userID;");
	}
	public function hasAccess($level = 0) {
		// Test user's access level with given $level
		$hasAccess = false;
		if(!$this->access) return false;
		if(($this->access) && ($level <= 1)) return true;
		if((isset($this->me['domains'])) && (is_array($this->me['domains']))) {
			foreach($this->me['domains'] as $d) {
				if(((isset($d['domain'])) && ($d['domain'] == $_SERVER['SERVER_NAME']) && ($d['level'] == $level)) || ((isset($d['domain'])) && ($d['domain'] == $_SERVER['SERVER_NAME']) && ($d['level'] == 100))){
					$hasAccess = true;
				}
			}
		}
		return $hasAccess;
	}
	public function activity($activity,$flag='0',$override = false) {
		$add_activity = false;
		if((isset($this->me['id'])) || ($override)) {
			$server_name = strtolower(str_replace(array("http://","//","www."),"",$_SERVER['SERVER_NAME']));
			$this->db->prepared = array("userID" => (isset($this->me['id']) ? $this->me['id'] : (($override > 2) ? $override : '0')),
										"activity" => $activity,
										"server" => $server_name,
										"flag" => $flag);
		
			$add_activity = $this->db->runSQL("INSERT INTO `{$this->authCredentials['database']}`.`activity` (`userID`,`activity`,`domain`,`flag`) VALUES (:userID,:activity,:server,:flag);");
		}
		return $add_activity;
	}
	public function note($note = false,$type='note',$overide = false) {
		$add_activity = false;
		if(!$note) return false;
		// Override is in case we want to make a note against a different Users UserID.
		$server_name = strtolower(str_replace(array("http://","//","www."),"",$_SERVER['SERVER_NAME']));
		$add_note = false;
		if(!$overide) {
			if($this->access) {
				$this->db->prepared = array("userID" => $this->me['id'],"note" => $note,"type" => $type, "server" => $server_name);
				$add_note = $this->db->runSQL("INSERT INTO `{$this->authCredentials['database']}`.`notes` (`userID`,`note`,`type`,`domain`) VALUES (:userID,:note,:type,:server);");
			}
		} else {
			$this->db->prepared = array("userID" => (($overide > 2) ? $overide : '0'),"note" => $note,"type" => $type, "server" => $server_name);
			$add_note = $this->db->runSQL("INSERT INTO `{$this->authCredentials['database']}`.`notes` (`userID`,`note`,`type`,`domain`) VALUES (:userID,:note,:type,:server);");
		}
		return $add_note;
	}
	
	private function authFriendlyName($str) {
		$split = array(); $find = array("`"); $replace = array("'");
		$explode = explode(" ",strtolower(str_replace($find,$replace,$str)));
		foreach($explode as $s) {
			if(stripos($s,"'") !== false) {
				$exp = explode("'",$s);
				$tmp_split = array();
				foreach($exp as $e) $tmp_split[] = ucwords($e); 
				$split[] = implode("'",$tmp_split);
			} elseif(substr(strtolower($s),0,2) == "mc") {
				$tmp_split = substr(strtolower($s),2);
				$split[] = "Mc".ucwords($tmp_split);
			} else {
				$split[] = ucwords($s);
			}
		}
		$name = implode(" ", $split);
		return $name;
	}
	
}	
?>