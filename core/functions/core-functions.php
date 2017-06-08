<?
	// Core Functions
	// Here are all the functions *required* by the core setup only.  No function should exist in here unless it is specifically used by the core initialization procedure.

	// Core Primary Checks
	function primary_checks() {
		global $root, $core_dirs, $core_settings, $access, $page, $me, $core_error, $auth, $db, $msgs, $swiftmail, $uri;
		
		// Scan for directories and create them if they don't exist
		#foreach($core_dirs as $key => $dir) {
		#	if($key != "global") {
		#		$directory = $root . $dir;
		#		if(!is_dir($directory)) {
		#			//if(_DEBUG_) trigger_error("{$directory} not found.  Creating {$directory}..");
		#			mkdir($directory);
		#			error_log("Core v".CORE_VERSION.": Directory {$dir} not found, created.");
		#		}c
		#	}
		#}
		
		// Fetch the URI into an array.
		$exp = explode("?",$_SERVER['REQUEST_URI']);
		$uri = explode("/",substr($exp[0],1));
		// No more isset($uri[3]) !!
		for($x=0;$x<10;$x++) {if(!isset($uri[$x])) $uri[$x] = false;}
	}
	// Define page variables, as given by $_SERVER['REQUEST_URI']
	function routing(Authentication $auth) {
		global $core_paths, $core_dirs, $core_files, $page, $core_settings, $access, $core_error, $msgs, $uri;

		
		// Merge build CSS & JS with page arrays.
		$core_files['styles'] 			= array_merge($core_files['styles'], getContents(ROOT."{$core_dirs['css']}",false,"/(\.css\.php)|(\.css)/",false,true));
		$core_files['scripts']['head']	= array_merge($core_files['scripts']['head'],getContents(ROOT."{$core_dirs['js']}",false,"/(\.head\.js\.php)|(\.head\.js)|(\.h.js)/",false,true));  
		$core_files['scripts']['foot']	= array_merge($core_files['scripts']['foot'],getContents(ROOT."{$core_dirs['js']}",false,"/\.foot\.js(?:\.php)?|(?<!head)\.js/",false,true));  
		// Find all config/headers/footers from /bin=/[build]
		$core_files['config']  	= getContents(ROOT."{$core_dirs['build']}",false,"/(\.config.php)|(\.cfg\.php)/",false);
		$core_files['head'] 	= getContents(ROOT."{$core_dirs['build']}",false,"/(\.head.php)/",false);  
		$core_files['foot'] 	= getContents(ROOT."{$core_dirs['build']}",false,"/(\.foot.php)/",false);  
		$core_files['headers'] 	= getContents(ROOT."{$core_dirs['build']}",false,"/(\.header.php)/",false);  
		$core_files['footers'] 	= getContents(ROOT."{$core_dirs['build']}",false,"/(\.footer.php)/",false);
		
		// Include CORE CSS if it is found.
		if(is_file($core_paths['lib']."/core.css")) {$core_files['styles'][] = $core_dirs['lib'] . "/core.css";}
		
		if(stripos($_SERVER['REQUEST_URI'],$core_dirs['ini']) !== false) {
			trigger_error("This file is protected.",true);
		}                                                 
		                
		// Remove default files from URI.		
		foreach($uri as $k => $u) {
			switch(strtolower($u)) {
				case "detach": break;
				case "logout":
				case "index.php":
				case "index.htm":
					// If you want to use a page called 'logout', remove the line below.
					// unset($uri[$k]);
					$uri[$k] = false;
				break;
			}
		}
		
		
		
		
		if((is_file(ROOT."{$_SERVER['REQUEST_URI']}")) && (strtolower($_SERVER['REQUEST_URI']) != "/index.php")) {
			# If the URL is valid, and the path exists, load the file.
			inc("{$_SERVER['DOCUMENT_ROOT']}{$_SERVER['REQUEST_URI']}",true);		// Load file and Die
		} elseif(is_file(ROOT."{$core_dirs['bin']}{$_SERVER['REQUEST_URI']}")) {
			# If it is with a page directory (/bin), load it too.
			inc(ROOT."{$core_dirs['bin']}{$_SERVER['REQUEST_URI']}",true);			// Load file and Die
		} else {                                                                      
			if((isset($uri[0])) && ($uri[0]!="")) {
				$page = $uri[0];
			} else {
				$page = $core_settings['home'];
			}
		}

		if(stripos(end($uri),".") !== false) {
			if(!filter_var(end($uri), FILTER_VALIDATE_EMAIL)) {
				# If its a file, and we have gotten to this point then it doesnt exist.  Display error and fail.
				# echo "NOT FOUND : {$_SERVER['REQUEST_URI']}<br/>";
				trigger_error("File {$_SERVER['REQUEST_URI']} not found. ".(isset($_SERVER['HTTP_REFERER']) ? "Referer: {$_SERVER['HTTP_REFERER']}" : "Server Address: {$_SERVER['SERVER_ADDR']}"));
			}
		}

		
		$core_files['root'] = ROOT;
		$core_files['dir'] = $core_dirs['bin'];
		$core_files['requested'] = ($page ? $page : $core_settings['home']); 
		                
		//	Perform checks on $core_files['requested']
		//	If the page needs to be altered, modify the root, dir and received vars to redirect.
		
		if($core_files['requested'] == "manage-core-settings") {
			$admin_access = false;
			switch($core_settings['admin_access']) {
				case "login": if($auth->hasAccess(100)) $admin_access = true; break;
				case "both":
					if($auth->hasAccess(100)) $admin_access = true;
					if(isDev()) $admin_access = true;
				break;
				case "ip": default: if(isDev()) $admin_access = true; break;
			}
			if($admin_access) {
				#$pageRoot = $core_dirs['lib'] . "/";
				$core_files['root'] = $core_paths['core'];
				$core_files['dir'] = "/lib/";
				$core_files['received'] = "manage-core-settings"; 
			} else {
				$core_files['root'] = $core_paths['core'];
				$core_files['dir'] = "/lib/error-pages/";
				$core_files['received'] = "401";
				$core_error['html'] 	= 401;
				appendlog($_SERVER['REQUEST_URI'].(isset($_SERVER['HTTP_REFERER']) ? " - <a href='{$_SERVER['HTTP_REFERER']}' target='_blank'>{$_SERVER['HTTP_REFERER']}</a>" : " - " . $_SERVER['REMOTE_ADDR']), "401 Errors");
			}
		}
		      
		if(($core_settings['under-maintenance']) && (!$auth->hasAccess(100))) {
			$core_files['root'] = $core_paths['core'];
			$core_files['dir'] = "/lib/error-pages/";
			$core_files['received'] = "503";
			$core_error['html'] 	= 503;
			appendlog($_SERVER['REQUEST_URI'].(isset($_SERVER['HTTP_REFERER']) ? " - <a href='{$_SERVER['HTTP_REFERER']}' target='_blank'>{$_SERVER['HTTP_REFERER']}</a>" : " - " . $_SERVER['REMOTE_ADDR']), "503 Errors");
		}
				
		
		// Here we set the received
		if($auth->access) {
			// Autherised: User has access to the protected pages.
			foreach($auth->page_access_levels as $page => $access_level) {
				if(strtolower($page) == strtolower($core_files['requested'])) {
					if(!$auth->hasAccess($access_level)) {
						// Doesnt Have Access
						$core_files['root'] = ROOT;
						$core_files['dir'] = $core_dirs['bin'];
						$core_files['received'] = $core_settings['home'];
						$msgs[] = "!You do not have permission to access <b>/{$core_files['requested']}</b>";    
					}
				}
			}
		} else {
			// No Access: User can only view the public pages.
			foreach($auth->page_access_levels as $page => $access_level) {
				if(strtolower($page) == strtolower($core_files['requested'])) {
					if($access_level != 0) {
						// Doesnt Have Access
						$core_files['root'] = ROOT;
						$core_files['dir'] = $core_dirs['bin'];
						$core_files['received'] = $core_settings['login'];
						if((!$auth->output) && ($_SERVER['REQUEST_URI'] != "/")) {
							$auth->redirect = true;
							$auth->output = "You need to <a href='/login'>login</a> before you can access <b>/{$core_files['requested']}</b>";
						}
					}
				}
			}
			
		}
		
		// Force Homepage - Used at first for QR Code Scanning, so ALL URLs route through to the homepage
		
		if($core_settings['force_homepage']) {
			// If force_homepage is TRUE, no matter what the REQUEST_URI is we send them to the homepage.
			$core_files['received'] = $core_settings['home'];
		}
		
		#// Check for errors, find out if user has access to this page or not.
		#if(!$core_error['html']) {
		#	#dump("page before: " . $loadPage);
		#	if(in_array($core_files['requested'],$auth->page_access['locked'])) {
		#		#dump("page is locked.");
		#		if(!$auth->access) {
		#			#dump("Access is false, This page is locked.");
		#			$core_files['root'] = ROOT;
		#			$core_files['dir'] = $core_dirs['bin'];
		#			$core_files['received'] = $core_settings['login'];
		#		}                                                                           
		#	} else {
		#		#dump("Page is not in locked.");
		#		if($auth->locked_access) {
		#			#dump("Locked access is true.");
		#			if(!in_array($core_files['requested'],$auth->page_access['unlocked'])) {
		#				#dump("page is not unlocked.");
		#				if(!$auth->access) {
		#					#dump("Access is false. This page is not unlocked.");
		#					$core_files['root'] = ROOT;
		#					$core_files['dir'] = $core_dirs['bin'];
		#					$core_files['received'] = $core_settings['login'];
		#				}
		#			}
		#		}
		#	}
		#	#dump("page after: " . $loadPage);
		#}
           
	
		// Now that we have established permission, ($core_files['requested'] has been approved / altered by the above code), check if it exists.
		$fullpath = "{$core_files['root']}{$core_files['dir']}".($core_files['received'] ? "{$core_files['received']}/{$core_files['received']}.php" : "{$core_files['requested']}/{$core_files['requested']}.php");
				
		if(is_file($fullpath)) {
			// Requested File exists, set it as received
			if(!$core_files['received']) $core_files['received'] = $core_files['requested'];
		} else {
			
			// echo $fullpath . "<br/>";
			// Requested File does NOT exist.
			// if /page doesn't exist, then show 404 error.
			$core_files['root'] = $core_paths['core'];
			$core_files['dir'] = "/lib/error-pages/";
			$core_files['received'] = "404";
			$core_error['html'] 	= 404;    
			appendlog($_SERVER['REQUEST_URI'].(isset($_SERVER['HTTP_REFERER']) ? " - <a href='{$_SERVER['HTTP_REFERER']}' target='_blank'>{$_SERVER['HTTP_REFERER']}</a>" : " - " . $_SERVER['REMOTE_ADDR']), "404 Errors");
			
			// Double check if the 404 page exists, if not, trigger error, die gracefully.
			if(!is_file("{$core_files['root']}{$core_files['dir']}{$core_files['received']}/{$core_files['received']}.php")) {
				trigger_error("404 Error: 404 Page not found! Oh the Irony.".(__DEBUG__ ? "<br/>Tried to locate the file in:<br/>{$core_files['root']}{$core_files['dir']}{$core_files['received']}/{$core_files['received']}.php" : false));
				appendlog("404 Page Not Found! " . $_SERVER['REQUEST_URI'].(isset($_SERVER['HTTP_REFERER']) ? " - <a href='{$_SERVER['HTTP_REFERER']}' target='_blank'>{$_SERVER['HTTP_REFERER']}</a>" : " - " . $_SERVER['REMOTE_ADDR']), "404 Errors");
				die();
			}
		}

		// Create some useful constants.
		$page = $core_files['received'];
		define("PAGE", 		$core_files['received']);
		define("ABSOLUTE", 	"{$core_files['root']}{$core_files['dir']}{$core_files['received']}/");
		define("RELATIVE", 	($core_files['root'] == $core_paths['core'] ? $core_paths['url'] : false) . "{$core_files['dir']}{$core_files['received']}/");
		
		
		// Scan the page folder and get all file listings.
		$pageFiles = getContents(ABSOLUTE,false,false,true);
		
		if(!$pageFiles) {
			// If we cannot find any files in the given page folder, die.
			trigger_error("Critical error: Could not find page files.");
			return false;
		}
		                                                 
		// Update the page title
		$core_settings['title'] = ucwords(str_replace(array("-","_")," ",$core_files['received'])) . " - " . $core_settings['site_name'];
		
		$setFirstConfigFile = false;
		$pageCoreFiles = $headers = $footers = array();
		
		
		foreach($pageFiles as $key => $f) {
			$exp = explode(".", $f->getFileName());
			$first_element = $exp[0];
			$array_key = str_replace(".".$f->getExtension(),"",$f->getFileName());
			switch(strtolower($f->getExtension())) {       
				case "js": 	$core_files['scripts']['foot'][$array_key] 	= RELATIVE."{$f->getFileName()}"; break;
				case "css": $core_files['styles'][$array_key] 	= RELATIVE."{$f->getFileName()}"; break;
				case "php":                                     
					if(strtolower($f->getFilename()) == strtolower(PAGE.".cfg.php")) {
						// $page.cfg.php should be first in the .cfg loading sequence.  Set the variable.
						$setFirstConfigFile = ABSOLUTE."{$f->getFileName()}";
					} else {     
						foreach($exp as $e) {
							// Loop through each part of the filename horizontally. filename.config.php
							if($e != $first_element) {	                          
								switch(strtolower($e)) {
									case "config":
									case "css": $core_files['styles'][$array_key] 	= RELATIVE."{$f->getFileName()}"; break(2);
									case "cfg": $pageCoreFiles[$array_key] 	= ABSOLUTE."{$f->getFileName()}"; break(2);
									case "head": $core_files['head'][$array_key] 	= ABSOLUTE."{$f->getFileName()}"; break(2);
									case "header": $headers[$array_key]		= ABSOLUTE."{$f->getFileName()}"; break(2);
									case "footer": $footers[$array_key]		= ABSOLUTE."{$f->getFileName()}"; break(2);
								}
							}
						}
					}
				break;
			}
		}
		
		// Add the initial .cfg.php file, followed by any others that the page might require.
		// This way the page loads the initial .cfg.php files from /bin/[build']/ first, then the $page.cfg.php file, then any others.
		if($setFirstConfigFile) {
			$core_files['config'] += (array("initial_page_config" => $setFirstConfigFile) + $pageCoreFiles);
		} else {
			$core_files['config'] += $pageCoreFiles;
		}

		if(((PAGE == "login") || (PAGE == "create-account") || (PAGE == "forgot-password")) && ($core_settings['login_standalone'])) {
			// If it is a login page, remove the headers and footers.
			$core_files['headers'] = $core_files['footers'] = array();	
		}
		
		// If there are any headers / footers in the $pageRoot, overwrite the default headers with these.
		if($headers) $core_files['headers'] = $headers;
		if($footers) $core_files['footers'] = $footers;
	} 
	// Load site settings from settings.ini
	function load_site_settings() {
		global $core_dirs, $core_files, $core_settings, $default_core_settings, $msgs;
		$core_settingsFile = true;
		$ini = array();
		if(!is_dir(ROOT."{$core_dirs['ini']}")) 				{trigger_error("'".ROOT."{$core_dirs['ini']}' does not exist."); $core_settingsFile = false;}
		if(!is_file(ROOT."{$core_dirs['ini']}settings.ini")) 	{trigger_error("'".ROOT."{$core_dirs['ini']}settings.ini' does not exist."); $core_settingsFile = false;}
		
		// Update core files if neccessary.
		if((isset($_POST['update-core-settings'])) && (isset($_SESSION['core-setting-management']))) 		{manage_core_settings(ROOT."{$core_dirs['ini']}settings.ini");}
		if((isset($_POST['update-core-database'])) && (isset($_SESSION['core-setting-management']))) 		{manage_core_settings(ROOT."{$core_dirs['ini']}database.ini");}
		if((isset($_POST['update-core-authentication'])) && (isset($_SESSION['core-setting-management']))) 	{manage_core_settings(ROOT."{$core_dirs['ini']}authentication.ini");}
		
		if($core_settingsFile) {
			$ini = load_ini_file("settings",true);
			if(isset($ini['Meta Tags']))	$core_settings['meta'] = $ini['Meta Tags'];
			if(isset($ini['Developer IPs']))$core_settings['developerIP'] = $ini['Developer IPs'];
			if(isset($ini['Allowed Upload Extensions'])) 	$core_settings['allowed']['upload'] = $ini['Allowed Upload Extensions'];
			if(isset($ini['Allowed Download Extensions'])) 	$core_settings['allowed']['download'] = $ini['Allowed Download Extensions'];
			
			if(isset($ini['Styles'])) {foreach($ini['Styles'] as $k => $v) {$core_files['styles'][$k] = $v;}}
			if(isset($ini['Header Scripts'])) {foreach($ini['Header Scripts'] as $k => $v) {$core_files['scripts']['head'][$k] = $v;}}
			if(isset($ini['Footer Scripts'])) {foreach($ini['Footer Scripts'] as $k => $v) {$core_files['scripts']['foot'][$k] = $v;}}
			
			if(isset($ini['Google Recaptcha Codes'])) 	$core_settings['recaptcha'] = $ini['Google Recaptcha Codes'];          
			if(isset($ini['PHP SwiftMail Settings'])) 	$core_settings['swiftmail'] = $ini['PHP SwiftMail Settings'];
			
			if(isset($ini['Website Settings']['wrappers'])) 			$core_settings['wrappers'] 		= ($ini['Website Settings']['wrappers'] === "false" || $ini['Website Settings']['wrappers'] == 0 ? false : true);
			if(isset($ini['Website Settings']['homepage']))				$core_settings['home'] 			= $ini['Website Settings']['homepage'];
			if(isset($ini['Website Settings']['force_homepage']))		$core_settings['force_homepage']= $ini['Website Settings']['force_homepage'];
			if(isset($ini['Website Settings']['site_name']))			$core_settings['site_name']		= $ini['Website Settings']['site_name'];
			if(isset($ini['Website Settings']['login'])) 				$core_settings['login'] 		= $ini['Website Settings']['login'];
			if(isset($ini['Website Settings']['salt'])) 				$core_settings['salt'] 			= $ini['Website Settings']['salt'];
			if(isset($ini['Website Settings']['error_email'])) 			$core_settings['error_email'] 	= $ini['Website Settings']['error_email'];
			if(isset($ini['Website Settings']['under-maintenance']))	$core_settings['under-maintenance'] = $ini['Website Settings']['under-maintenance'];
			if(isset($ini['Website Settings']['admin_access']))			$core_settings['admin_access'] 		= $ini['Website Settings']['admin_access'];
			if(isset($ini['Website Settings']['login_standalone']))		$core_settings['login_standalone'] 	= $ini['Website Settings']['login_standalone'];
			if(isset($ini['Website Settings']['site_version']))			$core_settings['site_version'] 		= $ini['Website Settings']['site_version'];
			
			
			
			
			// User Defined Constants
			if(isset($ini['User Defined Constants']) && (is_array($ini['User Defined Constants']))) {
				foreach($ini['User Defined Constants'] as $key => $val) {
					$core_settings['constants'][$key] = $val;
					if(!defined($key)) define($key, $val);
				}
			}
			
			// Apply to $default_core_settings
			$default_core_settings = array(	'settings' => $core_settings,
											'styles' => $core_files['styles'],
											'scripts' => array(	'head' => $core_files['scripts']['head'], 
																'foot' => $core_files['scripts']['foot'])); 
			return true;
		} else {
			return false;	
		}
	}
	// Core setting management
	function manage_core_settings($ini_file = false) {
		global $root, $core_paths, $core_dirs, $core_settings, $default_core_settings, $msgs;
		
		// Requires Config_Lite
		
		if(!is_file($ini_file)) return false; 
				
		if((isset($_POST['update-core-authentication'])) && (isset($_POST['setting'])) && (is_array($_POST['setting']))) {
			$lite = new Config_Lite($ini_file, LOCK_EX);
			$lite->set("Core Authentication","Core Version", CORE_VERSION);
			$lite->set("Core Authentication","LastUpdated",date("g:ia jS M Y"));
			$section_name = ucwords(str_replace("-"," ",$_POST['update-core-authentication']));
			
			
			switch(strtolower($_POST['update-core-authentication'])) {
				case "create-page":
					$page_name = (isset($_POST['page_name']) ? $_POST['page_name'] : false);
					$page_level = (isset($_POST['page_level']) ? $_POST['page_level'] : 0);
					if(!$page_name) {$msgs[] = "!You must provide a page name."; break;}
					if(!isset($_POST['setting'])) {$msgs[] = "!No settings provided."; break;} 
					
					$page_name_url = friendlyURL($page_name);
					
					
					$defaultPages = array('cfg' => 	"<?\n\n\t\$section = (\$uri[1] ? \$uri[1] : false);\n\n?>",
						'head' => 	"<?\n\n// <Head> file automatically generated by Core v".CORE_VERSION."\n\n?>",
						'header' => "<?\n\n// Header file automatically generated by Core v".CORE_VERSION."\n\n?>",
						'footer' => "<?\n\n// Footer file automatically generated by Core v".CORE_VERSION."\n\n?>",
						'php' => 	"<div class='container'>\n\t<div class='row'>\n\t\t<div class='col-sm-12'>\n\t\t\t<h1>".ucwords($page_name)."</h1>\n\t\t</div>\n\t</div>\n</div>",
						'js' => 	"\$(document).ready(function() {\n\n\t\$('button.btn').click(function () {\n\n\t});\n\n});\n",
						'jshead' => "\n\n\t//JS .head File - Loads at the top of the page.\n\n\$(document).ready(function() {\n\n\t\$('button.btn').click(function () {\n\n\t});\n\n});\n",
						'jsfoot' => "\n\n\t//JS .foot File - Loads at the bottom of the page.\n\n\$(document).ready(function() {\n\n\t\$('button.btn').click(function () {\n\n\t});\n\n});\n",
						'css' => 	"\n\n\t#content.{$page_name_url} {margin-bottom:5vh;}",
						'cssphp' => "\n\n\t#content.{$page_name_url} {margin-bottom:5vh;}");
					$defaultExtensions = array(	'cfg' => ".cfg.php",'head' => 	".head.php",'header' => ".header.php",'footer' => ".footer.php",'php' => 	".php",'js' => 	".js",'jshead' => 	".head.js",'jsfoot' => 	".foot.js",'css' => 	".css",'cssphp' => ".css.php");
					
					$new_path = $core_paths['local'] . $core_dirs['bin'] . $page_name_url;
					
					if(is_dir($new_path)) {$msgs[] = "!Folder already exists.  Make the files yourself."; break;}
					
					$created_folder = @mkdir($new_path);
					
					if($created_folder) {
						for($x=0;$x<count($_POST['setting']['name']);$x++) {
							if(isset($_POST['setting']['type'][$x])) {
								// If we can find a corresponding type match	
								$file_name = $_POST['setting']['name'][$x];
								$file_type = $_POST['setting']['type'][$x];
								if($file_name == "") $file_name = $page_name_url;
								$file_name .= (isset($defaultExtensions[$file_type]) ? $defaultExtensions[$file_type] : ".unknown.php");
								// Create the file with default content specific to file type.
								$fh = fopen($new_path . "/" . $file_name,"w");
								fwrite($fh, (isset($defaultPages[$file_type]) ? $defaultPages[$file_type] : "\n\n // Default File Created\n\n"));
								fclose($fh);
							}
						}
						$lite->set("Page Access Levels", $page_name_url, $page_level);
					} else {
						$msgs[] = "!Unable to create folder.  (Check permissions on {$new_path})"; break;
					}
				break;
				case "page-access-levels":
					if(isset($_POST['wipe-data'])) {
						if($lite->hasSection($section_name)) $lite->remove($section_name);
					} else {
						if($lite->hasSection($section_name)) $lite->remove($section_name);
						for($x=0;$x<count($_POST['setting']['name']);$x++) {
							$name = (isset($_POST['setting']['name'][$x]) ? $_POST['setting']['name'][$x] : false);
							$val = (isset($_POST['setting']['val'][$x]) ? $_POST['setting']['val'][$x] : false);
							if($name) $lite->set($section_name, $name, trim($val));
						}
					}
				break;
				case "authentication-credentials":
					foreach($_POST['setting'] as $key => $value) {
						$lite->set($section_name, $key, trim($value));		
					}        
				break;
			}
			$lite->save();
			$msgs[] = "Successfully updated Authentication.";
		} elseif(isset($_POST['update-core-authentication']) && ($_POST['update-core-authentication'] == "reset-core-authentication")) {
			echo "reset core authenticaton.";
		}
		
		
		if((isset($_POST['update-core-settings'])) && (isset($_POST['setting'])) && (is_array($_POST['setting']))) {
			$lite = new Config_Lite($ini_file, LOCK_EX);
			$lite->set("Core Settings","Version",CORE_VERSION);
			$lite->set("Core Settings","LastUpdated",date("g:ia jS M Y"));
			$section_name = ucwords(str_replace("-"," ",$_POST['update-core-settings']));
			switch(strtolower($_POST['update-core-settings'])) {
				case "styles":
				case "page-access-levels":
				case "header-scripts":
				case "footer-scripts":
				case "meta-tags":
				case "user-defined-constants":
					if(isset($_POST['wipe-data'])) {
						if($lite->hasSection($section_name)) $lite->remove($section_name);
					} else {
						if($lite->hasSection($section_name)) $lite->remove($section_name);
						for($x=0;$x<count($_POST['setting']['name']);$x++) {
							$name = (isset($_POST['setting']['name'][$x]) ? $_POST['setting']['name'][$x] : false);
							$val = (isset($_POST['setting']['val'][$x]) ? $_POST['setting']['val'][$x] : false);
							if($name) $lite->set($section_name, $name, trim($val));
						}
					}
				break;
				case "developer-ips":
					// Remove the section and write a new one, just in case they deleted items.
					if(isset($_POST['wipe-data'])) {
						if($lite->hasSection($section_name)) $lite->remove($section_name);
					} else {
						if($lite->hasSection($section_name)) $lite->remove($section_name);
						foreach($_POST['setting'] as $k => $v) {$lite->set($section_name, $k, trim($v));}
					}
				break;
				case "allowed-upload-extensions":
				case "allowed-download-extensions":
					if(isset($_POST['wipe-data'])) {
						if($lite->hasSection($section_name)) $lite->remove($section_name);
					} else {
						if($lite->hasSection($section_name)) $lite->remove($section_name);
						foreach($_POST['setting'] as $key => $value) {
							$lite->set($section_name, $key, trim($value));
						}
					}	
				break;
				case "php-swiftmail-settings":
				case "google-recaptcha-codes":
				case "website-settings":
					foreach($_POST['setting'] as $key => $value) {
						$lite->set($section_name, $key, trim($value));		
					}        
				break;
				
			}
			$lite->save();
			$msgs[] = "Successfully updated <b>{$section_name}</b>.";
		} elseif(isset($_POST['update-core-settings']) && ($_POST['update-core-settings'] == "reset-core-settings")) {
				// Reset the file
				#$ini_file = $root."/{$core_dirs['ini']}settings-test.ini";
				if(is_file($ini_file)) unlink($ini_file);
				// Create the file again
				$handle = fopen($ini_file, 'w') or die('Cannot open file:  ' . $ini_file);
				$lite = new Config_Lite($ini_file, LOCK_EX);
				$lite->set("Core Settings","Version",CORE_VERSION);
				$lite->set("Core Settings","LastUpdated",date("g:ia jS M Y"));
				
				// Website Settings
				$lite->set("Website Settings", "site_name", $default_core_settings['settings']['site_name']);
				$lite->set("Website Settings", "title", $default_core_settings['settings']['title']);
				$lite->set("Website Settings", "home", $default_core_settings['settings']['home']);
				$lite->set("Website Settings", "login", $default_core_settings['settings']['login']);
				$lite->set("Website Settings", "favicon", $default_core_settings['settings']['favicon']);
				$lite->set("Website Settings", "error_email", $default_core_settings['settings']['error_email']);
	
				// Developer IPs
				$x = 0;
				$default_core_settings['settings']['developerIP'][] = "213.122.162.226";
				foreach($default_core_settings['settings']['developerIP'] as $d) {
					$lite->set("Developer IPs", $x++, $d);
				}
				// Add the users IP in, as it was probably in there before.
				$lite->set("Developer IPs", $x, $_SERVER['REMOTE_ADDR']);
				
				// Styles
				$lite->set("Styles", "font-awesome", "http://maxcdn.bootstrapcdn.com/font-awesome/4.5.0/css/font-awesome.min.css");
				$lite->set("Styles", "bootstrap", "https://maxcdn.bootstrapcdn.com/bootstrap/3.3.2/css/bootstrap.min.css");
				$lite->set("Styles", "print", "https://maxcdn.bootstrapcdn.com/bootstrap/3.3.2/css/bootstrap.min.css");
	
				// Header Scripts
				$lite->set("Header Scripts", "jquery", "https://code.jquery.com/jquery-2.1.4.min.js");
				$lite->set("Header Scripts", "boostrap", "https://maxcdn.bootstrapcdn.com/bootstrap/3.3.2/js/bootstrap.min.js");
	
				// Google Recaptcha
				$lite->set("Google Recaptcha Codes", "key", "recaptcha-key");
				$lite->set("Google Recaptcha Codes", "secret", "recaptcha-secret");
				
				// PHP SwiftMail Settings
				$lite->set("PHP SwiftMail Settings", "ip", "localhost");
				$lite->set("PHP SwiftMail Settings", "port", 25);
				$lite->set("PHP SwiftMail Settings", "username", "username@domain.com");
				$lite->set("PHP SwiftMail Settings", "password", "password");
				
				// Allowed Upload Extensions
				$lite->set("Allowed Upload Extensions", 0, "pdf");
				$lite->set("Allowed Upload Extensions", 1, "jpg");
				$lite->set("Allowed Upload Extensions", 2, "png");
				$lite->set("Allowed Upload Extensions", 3, "gif");
				
				// Allowed Download Extensions
				$lite->set("Allowed Download Extensions", 0, "pdf");
				$lite->set("Allowed Download Extensions", 1, "jpg");
				$lite->set("Allowed Download Extensions", 2, "png");
				$lite->set("Allowed Download Extensions", 3, "gif");
				
				// Page Access Levels
				$lite->set("Page Access Levels", "login", "0");
				
				// Meta Tags
				$lite->set("Meta Tags", "site_description", "");
				$lite->set("Meta Tags", "site_keyword", "");
				$lite->set("Meta Tags", "site_title", "Core v".CORE_VERSION);
				$lite->set("Meta Tags", "site_type", "Website");
				$lite->set("Meta Tags", "site_url","");
				$lite->set("Meta Tags", "site_image", "");
				$lite->set("Meta Tags", "site_image_type", "");
				$lite->set("Meta Tags", "site_image_width", "");
				$lite->set("Meta Tags", "site_image_height", "");
				$lite->set("Meta Tags", "site_name", "");
				$lite->save();
				$msgs[] = "Successfully reset <b>settings.ini</b>.";
		}  elseif(isset($_POST['update-core-settings']) && ($_POST['update-core-settings'] == "backup-core-settings")) {
			dump("backup");	
		}
	}
	// Class Autoloader                   
	function class_autoloader($class) {
		global $core_paths;
		$class = strtolower($class);
		// Check if class exists in the core directory.
		if(is_file("{$core_paths['core']}/classes/{$class}.php")) {
			require_once("{$core_paths['core']}/classes/{$class}.php");
			return true;
		}
		// If not, check if it exists in the plugins directory 
		$classes_directories = array("{$core_dirs['build']}/plugins/");
		foreach($classes_directories as $dir) {
			if(file_exists("{$core_paths['local']}{$dir}{$class}.php")) { 
				require_once("{$core_paths['local']}{$dir}{$class}.php");
				return true;
			}	
		}        
		return false;
	}
	// Include a file, the fancy way.
	function inc($str = "", $die = false) {
		global $root, $core_dirs, $core_paths, $core_settings, $core_error, $auth, $db, $msgs, $swiftmail, $uri, $version_history;
		$str = str_replace("//",$_SERVER['DOCUMENT_ROOT']."/",$str);
		$success = false;
		if(is_dir($str)) {
			$objects = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($str), RecursiveIteratorIterator::SELF_FIRST);
			foreach($objects as $name => $object){
				if((strtolower(substr($name,-4)) == ".php") && (!in_array($name,get_included_files()))) {include($name);}
			}
			$success = true;
		} elseif(is_file($str)) {
			if((strtolower(substr($str,-4)) == ".php")) {
				include($str);
			} else {
				if (ob_get_length()) ob_end_clean();
				#$finfo = finfo_open(FILEINFO_MIME_TYPE);
				#$mime = finfo_file($finfo,  $str);
				#header('Content-Type:'.$mime);
				readfile($str);
			}
			$success = true;
		}
		if($die) die(); // The Bart, The.
		return $success;
	}
	// Load an INI file and return the results.
	function load_ini_file($filename = false, $process_sections = false) {
		global $core_paths, $core_dirs;
		if(!$filename) return false;
		if(is_file($filename)) {
			// If it's an absolute path
			$inifile = parse_ini_file($filename,$process_sections);
			return $inifile;
		}
		if(is_file("{$core_paths['local']}{$core_dirs['ini']}{$filename}.ini")) {
			$inifile = parse_ini_file("{$core_paths['local']}{$core_dirs['ini']}{$filename}.ini",$process_sections);
			return $inifile;
		} else {
			trigger_error("Unable to load INI file: {$core_paths['local']}{$core_dirs['ini']}{$filename}.ini", E_USER_WARNING);					
		}
		return false;
	}
	// Get the file contents of a directory.
	function getContents($dir = false, $recursive = true, $filter = "/.$/", $returnObject = false, $relativePaths = false, $filenames_only = false) {
		global $core_settings;
		if(!$filter) $filter = "/.$/";
		if(!$dir) return false;
		if(!is_dir($dir)) {trigger_error("<b>'{$dir}'</b> does not exist."); return false;}
		
		$return = array(); 
		if($recursive) {
			$recursiveIterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($dir), RecursiveIteratorIterator::CHILD_FIRST);
			$ritit = new RegexIterator($recursiveIterator, $filter);
			if($returnObject) { 
				$return = $ritit;
			} else {
				foreach ($ritit as $splFileInfo) { 
					if(($splFileInfo->getFileName() != ".") && ($splFileInfo->getFileName() != "..")) {
						$path = $splFileInfo->isDir() 
								? array($splFileInfo->getFilename() => array()) 
								: array($splFileInfo->getFilename()); 
						for ($depth = $ritit->getDepth() - 1; $depth >= 0; $depth--) { 
							$path = array($ritit->getSubIterator($depth)->current()->getFilename() => $path); 
						}                                         
						$return = array_merge_recursive($return, $path);
					}
				}
			}
		} else {
			$iterator = new FilesystemIterator($dir);
			$regex = new RegexIterator($iterator, $filter);
			if($returnObject) {
				$return = $regex;
			} else {
				foreach($regex as $entry) {
					if($entry->isFile()) {
						if($relativePaths) {
							#if($core_settings['page'] == "gospeltruth") echo $entry->getPathName() . "<br>";
							if($filenames_only) {
								$return[] = $entry->getFilename();
							} else {
								$return[] = str_replace(ROOT,"",(substr($dir,-1)=="/" ? $dir : $dir."/").$entry->getFilename());
							}
						} else {
							if($filenames_only) {
								$return[] = $entry->getFilename();
							} else {
								$return[] = $entry->getPathName();	
							}
						}
					}
				}
				sort($return);
			}
		}
		return $return;
	}
	// Confirm developer status against IP.
	function isDev() {
		global $core_settings;
		$return = ((in_array($_SERVER['REMOTE_ADDR'],$core_settings['developerIP'])) ? true : false); 
		if(debug_backtrace()) {
			$backtrace = debug_backtrace();
			appendlog("isDev() called from ".$backtrace[0]['file']." on line " . $backtrace[0]['line'] . " returned ".var_export($return,1),"isdev");
		}
		return $return;
	}
	////
	// Custom Error Handling
	////
	function fail($core_error_level,$core_error_message,$core_error_file,$core_error_line,$core_error_context) {
		global $strict, $core_settings, $core_error, $db;
		
		// Record the error in the global error array. (Void with error_get_last()?)
		$core_error['level'] 	= $core_error_level;
		$core_error['message'] 	= $core_error_message;
		$core_error['file'] 	= $core_error_file;
		$core_error['line'] 	= $core_error_line;
		$core_error['context'] 	= $core_error_context;
		
		$core_error_levels = array(	256		=> 	"padding: 5px;border-radius: 4px;display: block;width: 100%;color:#8a6d3b;background-color:#fcf8e3;border-color:#faebcc;",	// E_USER_ERROR
									512		=>	"padding: 5px;border-radius: 4px;display: block;width: 100%;color:#a94442;background-color:#f2dede;border-color:#ebccd1;",	// E_USER_WARNING
									1024	=>	"padding: 5px;border-radius: 4px;display: block;width: 100%;color:#31708f;background-color:#d9edf7;border-color:#bce8f1;"	// E_USER_NOTICE
								);	
		
		
		echo "<div class='container-fluid' style='max-width:1170px;'><pre class='error' style='".(isset($core_error_levels[$core_error_level]) ? $core_error_levels[$core_error_level]:$core_error_levels[512])."'><b>Core v".CORE_VERSION.":</b>&nbsp;";
		if(_DEBUG_) {
			echo "<span class='message'>{$core_error_message}</span>\n\n<span class='details'>On line <b>{$core_error_line}</b> in file: <b>{$core_error_file}</b>.</span>";
			if(substr($core_error_file,-12) == "database.php") {
				// It's a database error, output the SQL.
				# Getting incorrect SQL messages because I'm using different database objects ($db, $gfdb, $shopDB etc)
				# echo "\n<span class='sql'>SQL Query:<br/><b>{$db->sql}</b></span>";
				$core_settings['last_error_type'] = "PDO";
			}
		} else {
			// If _DEBUG_ mode is false, just show the error but no file and line numbers.
			if(substr($core_error_file,-12) == "database.php") {
				echo "<span class='message'>There was a database error.  The website administrator has been notified.</span>";
				$core_settings['last_error_type'] = "PDO";
			} else {
				echo "<span class='message'>".str_replace(ROOT,"",$core_error_message)."</span>";
				$core_settings['last_error_type'] = false;
			}
			
		}
		echo "</pre></div>\n\n";
		
		// Email the error to the designated email address if it is set.
		if($core_settings['error_email'] != "") email_error();
		
		// Add the error to the error log file.
		error_log("Core v{$core_settings['version']}: {$core_error_message} (Line {$core_error_line} in {$core_error_file})"); 
		
		if(STRICT) {
			// STRICT mode dies on any error.
			die("Strict mode set to <i>true</i>, execution killed.");
		}
	}
	// Log file management
	function appendlog($str, $logfile = "logs") {	
		global $core_dirs;
		if(!is_dir(ROOT.$core_dirs['logs'])) mkdir(ROOT.$core_dirs['logs']); 
		$fh = fopen(ROOT.$core_dirs['logs'].$logfile.".log", 'a');
		if($str=="<hr>") {fwrite($fh, "<hr>\n");} else {fwrite($fh, "[".date("G:i:s j-n-y",time()) . "] " . $str . "\n");}
		fclose($fh);
	}
	// Email the error to Administrator
	function email_error() {
		global $strict, $core_settings, $core_error,$db;
		$serverKeys = array('DOCUMENT_ROOT','HTTP_COOKIE','HTTP_HOST','HTTP_REFERER','HTTP_USER_AGENT','QUERY_STRING','REDIRECT_STATUS','REDIRECT_URL','REMOTE_ADDR','REMOTE_PORT','REQUEST_METHOD','REQUEST_URI','SCRIPT_FILENAME','SCRIPT_NAME','SERVER_ADDR','SERVER_ADMIN','SERVER_NAME','SERVER_PORT','SERVER_SOFTWARE','REQUEST_TIME');
		$preBox = "<pre style='display:block;margin:10px auto;border:1px solid #ddd;padding:4px;border-radius:4px;color:#404BD8;background:#e8e8e8'>";
		$core_errorMail = "<html><style>body{font-family:'Verdana';}b{color:#f00;}pre b{color:#111;}h1,h2{font-family:'Gerogia','Book Antiqua';}</style><body><h1>Server error on <b>{$_SERVER['HTTP_HOST']}</b></h1><small>Date: ".date("H:m jS M Y",time())."</small><br/>";
		
		$count = 0;
		$core_errorMail .= "<b>{$core_error['file']}</b> on line <b>{$core_error['line']}</b>:<br/>";
		
		if($core_settings['last_error_type'] == "PDO") {
			$core_errorMail .= "<br/><b>Last Error:</b> {$db->error}<br/><br/>
								<b>Error Code:</b> ".nl2br($core_error['message'])."<br/><br/>
								<b>SQL query:</b>
								{$preBox}".trim(preg_replace('/\t+/', '', $db->sql))."</pre>";
		} else {
			$core_errorMail .= "<b>{$core_error['message']}</b>";
		}
		
		$core_errorMail .= "<br/><hr><br/>";
											
		if($_SERVER) {
			$core_errorMail .= "<h2>Server Variables</h2>{$preBox}";
			foreach($_SERVER as $key => $val) {
				if(in_array($key,$serverKeys)) {$core_errorMail .= "<b>".ucwords(strtolower(str_replace("_"," ",$key))).":</b>&nbsp;{$val}<br/>";}	                  
			}
			$core_errorMail .= "</pre>";
		}
		if($_REQUEST) {
			$core_errorMail .= "<h2>Request Variables</h2>{$preBox}";
			foreach($_REQUEST as $key => $val) {
				$core_errorMail .= "<b>{$key}:</b>&nbsp;{$val}<br/>";                  
			}
			$core_errorMail .= "</pre>";
		}
		if(debug_backtrace()) {
			$core_errorMail .= "<h2>Debug Backtrace</h2>{$preBox}";
			$count = 0;
			foreach(debug_backtrace() as $bt) {
				if(is_array($bt) && ($count > 0)) {
					if(!isset($bt['file'])) $bt['file'] = "<unknown>";
					if(!isset($bt['line'])) $bt['line'] = "<unknown>";
					if(!isset($bt['function'])) $bt['function'] = "<unknown>";
					$core_errorMail .= "<b>{$bt['function']}()</b> caused an error in <b>{$bt['file']}</b> on line <b>{$bt['line']}</b>.<br/>";
				}
				$count++;
			}
			$core_errorMail .= "</pre>";
		}	
		$core_errorMail .= "</body></html>";
		error_log($core_errorMail,1,$core_settings['error_email'],"subject:[{$_SERVER['HTTP_HOST']}]\nContent-Type: text/html; charset=ISO-8859-1");	
	}
	// Dump an array/object within <pre> tags.                                                                                                                                   
	function dump($var = false, $title = false, $hidden = false, $pre = true) {
		$uniqueID = hash("sha256",microtime());
		if($pre) {
			echo "<div class='pre-dump' style='position:relative;border: 1px solid #CCC;min-height: 40px;border-radius: 4px;background-color: #f5f5f5;'>
					<b style='position: absolute;top: 5px;left: 10px;color: #079d30;'>Dump</b>
					<b class='title' style='position: absolute;top: 5px;left: 64px;'>::".($title ? "{$title}\n" : time()) ."</b>
					<button data-toggle='collapse' data-target='#{$uniqueID}' style='position: absolute;top: 5px;right: 10px;border: none;background: transparent;color: #aaa;'><i class='fa fa-dot-circle-o'></i></button>
					<pre id='{$uniqueID}' class='collapse' style='".($hidden ? "display:none;":false)."margin-top: 30px;border: none;'>".var_export($var,1)."</pre>
					</div>";
		}else{echo var_export($var,1);}
	}
	function predump($var = false, $title = false) {
		echo "<pre>".($title ? "<b>{$title}</b><br/>" : false).var_export($var,1)."</pre>";	
	}

?>