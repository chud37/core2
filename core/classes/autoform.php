<?
class AutoForm {
	# Autoform version.
	private $version = "2.86";

	public $version_history = array(
		"2.86" => "Bug in next column count if compact.", 
		"2.85" => "Function \$column->dropdownlist() added.",
		"2.84" => "Added column->join->insertable(true);",
		"2.83" => "Fixed button ordering error.",
		"2.82" => "Added ability to set \$form->column('column')->optional();",
		"2.81" => "Fixed issue with placeholder in textarea.", 
		"2.80" => "Streamlined display_table() function, writing the label and the input after the big switch.",
		"2.79" => "Added \$btn_size to set the button sizes.",
		"2.78" => "Added class 'button-bar' to the button bar.",
		"2.77" => "Added function addarray(\$arrayname, \$array = array()) for easy setting of arrays.",
		"2.76" => "Added prepared statements to UPDATE commands.",
		"2.75" => "ENUM columns now changes depending on the largest length of value.",
		"2.74" => "Upgraded button functionality - ability to hide/remove all buttons and change text.",
		"2.73" => "Added \$this->null_option option for <select> fields.",
		"2.72" => "Added \$this->please_select option for <select> fields.",
		"2.71" => "Added \$this->optional_fi6elds array.",
		"2.70" => "Upgraded \$this->reset_row_count to \$this->reset_form, which now ignores row count and all POSTed data.",
		"2.69" => "Added \$this->reset_row_count to determine whether or not to reset row counts from POSTed data.",
		"2.68" => "Fixed jQuery checkbox \$_POST 'on' bug, prevented checkboxes from \$_POSTing 'on' data.",
		"2.67" => "Added Prepared statements for INSERT commands.",
		"2.66" => "Added \$autofill_userID value & functionality.",
		"2.65" => "Added Inline form edit function.  Activated by \$this->inline_edit = true;",
		"2.64" => "Added Clone Button",
		"2.63" => "Fixed file upload bug in \$this->is_file_key()",
		"2.62" => "Added month support and column type 'year' support.",
		"2.61" => "Changed \$this->hidden_columns from private to public, made it case sensitive again.",
		"2.60" => "Added row dividers, row title functionality in \$form->titles[]",
		"2.59" => "Added column names into class fields for extra CSS control by the user.",
		"2.58" => "Full_Rows var wasn't functioning when the previous \$type was found in \$small_vals.",
		"2.57" => "Began documenting updates."    
	);
	
	# The database Autoform will use. Set in __construct.
	private $db = false;
	# The table contained in the above database that Autoform will use. Set in __construct.
	private $table = false;
	# The action Autoform is currently performing.
	private $action = false;                                                                                                     
	# Errors from submitted forms get placed in here.
	public $errors = array();
	# Data to output if $this->debug is TRUE.
	private $debug_data = array();
	# Array for use with str_replace() to remove unwanted data from column names.
	private $purify_col_names = array("enum(","varchar(","tinyint(","int(",")","'","year(");
	# Types of columns that Autoform recognises.
	private $types = array("enum","varchar","timestamp","text","tinyint","int","date","year");      
	# TRUE to display debug information.
	public $debug = false;		
	# Internal Debug Messages, for AutoForm development.  Will only display if $this->debug = true
	private $show_internal_messages = true;
	# Varchar Sizes depending on how they divide by 50. (0 = 2 columns, 2 = 3 Columns etc)
	private $varchar_sizes = array(	0 => 2, 1 => 2,2 => 4,3 => 4, 4 => 4, 5 => 4);
	private $months = array(1 => "january",2 => "february",3 => "march",4 => "april",5 => "may",6 => "june",7 => "july",8 => "august",9 => "september",10 => "october",11 => "november",12 => "december");
	private $css = "<style type='text/css'>
				/* Auto Generated Form CSS */
				#autoform-form {position:relative;} 
				#autoform-help {border: 1px solid #CCC;padding:10px 20px 20px 20px;border-radius: 4px;font-size:14px;margin-bottom: 20px;} 
				#autoform-help div.title {font-size:25px;font-weight:bold;} 
				#autoform-help div.heading {font-weight:bold;text-align:center;border-bottom:1px solid #EEE;margin-top:30px;font-size:16px;}
				#autoform-help div.heading button {float:right;background: none;border: none;color:#777}
				#autoform-help div.temporarily-hidden {display:none;} 
				#autoform-help #variable-list div.subheading {font-weight:bold;font-family:'Verdana','Arial';color:#34B334;margin-top:10px;}
				#autoform-help #variable-list div.var {font-family:Courier;}
				#autoform-help #variable-list div.var span {font-weight:bold;color:#09f;}
				#autoform-help #variable-list i {font-weight:bold;color:#069;}
				#autoform-help #code, #autoform-help #code-explained {background: #F9F2F4;padding: 10px;border-radius: 4px;}
				button.autoform-inline-submit {display:none;}
				a.autoform_image-list-item > .row {margin-bottom: 5px;border-radius: 4px;padding: 5px;border: 1px solid #e8e8e8;}
				a.autoform_image-list-item > .row:hover {background:#f5f5f5;}
				a.autoform_image-list-item > .row > div:first-child {padding-left:5px;}
				a.autoform_image-list-item > .row > div > img {border-radius:3px;}
				a.autoform_image-list-item > .row > div > b {font-size:30px;}
				a.autoform_image-list-item > .row > div > span.hover {display:none;}     
				a.autoform_image-list-item > .row:hover > div > span.hover {display:inline-block;} 
				#autoform-form .row {margin-bottom:5px;}
				#autoform-form.delete:before {content: \"\\f00d\";font-family: FontAwesome;position: absolute;top: -10px;right: 10px;font-size: 50px;color:#AB1814;}
				#autoform-form.delete:after {content: \"\\f00d\";font-family: FontAwesome;position: absolute;bottom: 0px;left: 10px;font-size: 50px;color:#AB1814;}
				#autoform-form.delete {border-radius: 4px;padding-top: 50px;padding-bottom: 10px;padding-right: 10px;padding-left: 10px;border: 1px dashed #AB1814;}
				#autoform-form.delete .form-control {background:#000;color: #FFF;border: none;cursor: not-allowed;pointer-events: none;}
				#autoform-form.delete .autoform-image-wrap img.autoform-image {border-radius:3px;}
				#autoform-form #locate-userID {padding: 10px;border-radius: 4px;border: 1px solid #ccc;margin-bottom: 10px;}
				#autoform-form #locate-userID input#userID-search-string {background: url('http://i.awme.net/logos/awmpass-logo.png');background-repeat: no-repeat;background-size: 100px;background-position: right;}
				#autoform-form #locate-userID #userID-search-results div.container-fluid {max-height: 20vh;overflow-y: scroll;}
				#autoform-form #locate-userID #userID-search-results div.container-fluid a {border-radius:4px;visibility:block}                                                                              
				#autoform-form #locate-userID #userID-search-results div.container-fluid a:hover {background:#DDD;}
				#autoform-form #locate-userID #userID.check {background: #C1FBC4 url('/ext/event/check.png');background-position: right;background-repeat: no-repeat;background-size: 20px;color: #000;font-weight: bold;}
				#autoform-form div.duplicate-fields {padding-bottom:10px;}             
				#autoform-form div.duplicate-fields+div.duplicate-fields {padding-top:10px}                                                                                   
				#autoform-form div.form-label {font-weight: bold;margin-top: 5px;}
				#autoform-form div.form-label.error {color:#f00;}
				#autoform-form div.form-label span.error-message {font-weight:normal;font-style:italic;font-size:12px;display:block;}
				#autoform-form div.form-label span.debug {font-weight: normal;font-size: 12px;display: block;color: #6DC2E0;font-family: Courier;}
				#autoform-form div.form-input img.autoform-image {max-height:60px;display:block;border-radius:2px;}
				#autoform-form a.image-select img {max-height:100px;max-width:100px;}
				#autoform-form div.form-input img.autoform-image.left {float:left;margin-right:5px;}
				#autoform-form div.form-input img.autoform-image.middle {margin: auto auto 5px auto;}
				#autoform-form div.form-input img.autoform-image.right {float:right;margin-left:5px;}
				#autoform-form div.form-input select.boolean.no {background:#F9E3E3;color:#A70E0E;}
				#autoform-form div.form-input select.boolean.yes {background:#DFD;color:#088808;}
				#autoform-form div.form-input .form-control.error {background: rgba(255, 0, 0, 0.06); border: 1px solid #D48080;}
				#autoform-form div.form-input .form-control.textarea {overflow-y: scroll;height: auto;max-height: 120px;
				#autoform-form div.form-input div.error-popout {position: absolute;background-color: #FF5D5D;border-radius: 4px;padding: 5px;top: -40px;color: #FFF; opacity:0;
					-webkit-transition: opacity 0.5s ease;-moz-transition: opacity 0.5s ease;-o-transition: opacity 0.5s ease;transition: opacity 0.5s ease;}
				#autoform-form div.form-input div.error-popout:after{border-color: #FF5D5D transparent;content: '';position: absolute;bottom: -10px;left: 2%;border-width: 10px 10px 0px 10px;border-style: solid;visibility: block;width: 0;}
				#autoform-form div.form-input:hover div.error-popout {opacity:1;}</style>";
	private $join_columns_default_values = array(	"database" => false,
													"table" => false,
													"id" => "id",
													"where" => false,
													"null-option" => false,
													"column" => false,
													"dir" => "ASC",
													"default" => false,
													"insertable" => false);
	// Indivicual Cell Data.  Structure: array("firstname" => array("label_size" => 2, "input_size" => 4, "class" => "text-right"));
	public $cells = array();
	// The working column when chaining commands together.
	private $working_item = false;
	// Stores arrays loaded from the database, such as session[1] and session[2] etc.
	private $array_columns = array();
	// The buttons loaded at the end of the form.
	public $buttons = array("delete" 	=> array("show" => false, "name" => "delete", 	"class" => "btn btn-danger", 	"icon" => "times",		"alt" => "Delete Entry","title" => "Delete Entry", 	"caption" => "Delete Entry"),
							 "plus"		=> array("show" => false, "name" => "plus", 	"class" => "btn btn-default", 	"icon" => "plus",		"alt" => "Add Row", 	"title" => "Add Row", 		"caption" => "Add Row"),
							 "minus"	=> array("show" => false, "name" => "minus", 	"class" => "btn btn-default", 	"icon" => "times",		"alt" => "Delete Row", 	"title" => "Delete Row", 	"caption" => "Delete Row"),
							 "clone"	=> array("show" => false, "name" => "clone", 	"class" => "btn btn-primary", 	"icon" => "copy",		"alt" => "Clone Entry", "title" => "Clone Entry", 	"caption" => "Clone Entry"),
							 "save" 	=> array("show" => false, "name" => "save", 	"class" => "btn btn-success", 	"icon" => "floppy-o",	"alt" => "Save Entry", 	"title" => "Save Entry", 	"caption" => "Save Entry"));

	
	public $postedID = false;						# The rowID that was previously posted.
	public $list = array(
			"sql" => false,
			"body" => "<span class='title'>{title}</span>",
			"dir" => "ASC",
			"image" => false, 			# Image: The column name to be displayed as an image in function $this->list();
			"title" => false, 
			"url" => false);
	public $file_types = array("jpg","jpeg","bmp","gif","png","pdf");	# Allowed file types to be uploaded.
	public $full_rows = array("text");									# Define which values sit on their own row, can be [type] or [column name]
	public $small_values = array("enum","tinyint","int","double","date","float","varchar","year");		# Define which values can sit on the same row.
	public $file_triggers = array("image","file","img");		# Keywords that indicate this column is a file upload.
	public $image_align = "left"; 								# 0[left], 1[middle], 2[center] to display an image column. 
	public $display_image = true;								# Choose whether to display the image in the file input field.	
	public $hidden_columns = array("id","userid","ts");			# Columns that Autoform will ignore.
	public $page = "/";											# The current page autoform is running on.
	
	public $form_action = false;			# Set the <form action=''>
	public $multiple_rows = false;			# Whether the user can create multiple rows or not.
	public $multiple_buttons = false;		# If mulitple_rows = TRUE, buttons plus/minus buttons are displayed in each div.duplicate-fields section.
	public $reset_form = false;				# Ignores Row Count, Ignores $_POST data.
	public $filepath = false;				# The absolute path for the file pool.
	public $urlpath = false;				# The hyperlink for the file pool.
	public $compact = false;				# In compact mode, the form_label is directly above the form_input, as opposed to each in their own cell. 
	
	private $structure = array();			# The column structure of the given table. (was public) 
	public $form_label_cols = 6;			# How many columns for the form labels.
	
	
	public $status = false;					# The status of autoform.  =('created','updated','delete','final-delete')
	
	
	#public $entry = "Entry";				# The word for the button 'Update Entry' and 'Create Entry'.
	public $form_response = false;			# TRUE if the form was posted successfully.
	public $inline_edit = false;			# Set the URL for AutoForm (Required for Inline Table Editing)
	public $autofill_userID = false;		# If the user is logged in, automatically fill in the $userID value.
	public $userID = false;					# AutoForm will try and detect a username from $auth Authentication object.
	public $static_variables = array();		# Static variable provided by the instance caller that will be hidden input on each form.  Much like userID behavior.
	public $optional_fields = array();		# Instead of writing a feedback form, the user can specify whether certain fields are optional or not.  Add [column-name] to array.
	public $please_select = array();		# Adds a 'Please Select' option to <select> fields.
	public $null_option = array();			# Adds a 'None' option to <select> fields.
	public $defaults = array();				# Adds a default value.  Currently only set on tinyint(1).  add an array [column_name] = "default value";
	
	# Buttons
	# Change to string to modify the text of the button.  False to remove the button.
	#public $delete_button = false;			# FALSE to remove delete button.
	#public $clone_button = true;			# FALSE to remove clone button. 
	#public $save_button = true;				# FALSE to remove save button.  
	public $btn_size = "btn-md";			# Set the button size for column arrays.
	public $image_select = false; 			# Choose whether to display the image select box or not.
	public $join_columns = array();
	
	############################
	## Construct & Initialize ##
	############################
	public function __construct($database = false, $table = false, Database $db, $debug = false) {
		
		global $auth;
		
		if((isset($auth->me['id'])) && ($this->userID === false)) {
			$this->userID = $auth->me['id'];
		}
		
		if($db) {$this->db = $db;} else {die("Auto Form needs a database object.");}
		
		if((!$database) || (!$table)) die("Auto Form needs a database and a table name to work with.");
		if($database) $this->database = $database;
		if($table) $this->table = $table;
		$this->debug = $debug;
		$this->filepath = $_SERVER['DOCUMENT_ROOT'];
		$this->urlpath = $_SERVER['HTTP_HOST'];
		
		$find_uri = explode("/",trim($_SERVER['REQUEST_URI']));
		if($find_uri[0] == "") {$this->page = $find_uri[1];}else{$this->page = $find_uri[0];}
		
		$this->form_action = "/{$this->page}/";
		
		// Get data from the database.
		$this->structure = $this->db->select("SELECT column_name, column_comment, column_type FROM information_schema.columns WHERE `table_schema`='{$this->database}' AND table_name='{$this->table}';");
		if(!$this->structure) {$this->status = "{$table} not found in database.";}
		
		$information_schema = $this->db->select("SELECT * FROM information_schema.tables WHERE table_name='{$this->table}';",true);
		if($information_schema) {
			foreach($information_schema as $key => $val) {$this->table_data[strtolower($key)] = $val;}
		} else {
			$this->status = "Could not load table data from `information_schema`.`tables`.  Check your table name.";
		}
		if(!$this->table) 				{$this->status = "No table selected: Please provide a table to read from.";}
		#if(!function_exists("select")) 	{$this->status = "Function select() not found, cannot read from database.";}

		if(!$this->status) {
			$this->postedID = (isset($_POST['id'][0]) ? $_POST['id'][0] : false);
		} else {
			die($this->status);
		}
	}
	public function create() {
		// Create new entry
		$this->action = "create";
		return $this->display_table();
	}
	public function read($row = false) {
		// Load row and display it only.
		if(!$row) return false;
		$this->action = "read";
		return $this->display_table($row);
	}
	public function update($row) {
		// Load $row and allow the user to save it.
		if(!$row) return false;
		$this->action = "update";
		return $this->display_table($row);
	}
	public function delete($row) {
		// Delete a row.
		$this->action = "delete";
		return $this->display_table($row);
	}
	private function init() {
		if(substr($this->filepath,-1) != "/") $this->filepath .= "/";
		if(substr($this->urlpath,-1) != "/") $this->urlpath .= "/";
		if(substr($this->form_action,-1) != "/") $this->form_action .= "/";
	}
	
	
	private function is_file_key($haystack, $original_value = false) {
		foreach($this->file_triggers as $needle) {
			if(stripos($haystack,$needle) !== false) return "file";
		}
		return $original_value;
	}	
	private function debug_messages($str = "") {
		if(($this->debug) && ($this->show_internal_messages) && ($str)) {
			echo $str . "<br/>";		
		}
	}
	private function var_replace($str,$var,$value) {
		if(stripos($str,"{".$var."}") !== false) {
			$str = str_replace("{".$var."}",utf8_encode($value),$str);
		}
		return $str;
	}
	private function final_delete($rowID = false) {
		if(!$rowID) {$this->status = "final_delete() error: No rowID given."; return false;}
		if($this->debug) $this->debug_data[] = "AutoForm::Final_Delete Procedure Started.  RowID: <b>{$rowID}</b>";
		$check_row = $this->db->select("SELECT * FROM `{$this->database}`.`{$this->table}` WHERE `id`=".$rowID.";",true);
		if($check_row) {
			$this->db->prepared = array("rowID" => $rowID);
			$deleteSQL = "DELETE FROM `{$this->database}`.`{$this->table}` WHERE `id`=:rowID;";
			if($this->db->runSQL($deleteSQL)) {
				return true;
			} else {
				$this->status = "autoform::final_delete() SQL error: " . mysql_error();
				return false;
			}
		} else {
			$this->status = "autoform::final_delete() error: Could not locate rowID within `{$this->database}`.`{$this->table}`.";
			return false;	
		}
	}
	
	########################
	## Process & Validate ##
	########################
	public function process() { 
		if(isset($_POST['autoform_update'])) {$this->status = 'updated'; return $this->validate();}		
		if(isset($_POST['autoform_create'])) {$this->status = 'created'; return $this->validate();}
		if(isset($_POST['autoform_clone'])) {$this->status = 'cloned'; return $this->validate();}
		if(isset($_POST['autoform_inline'])) {$this->status = 'inline'; return $this->validate();}
		if((isset($_POST['autoform_delete'])) && (isset($_POST['id'][0]))) {$this->status = 'delete';}
		if((isset($_POST['autoform_final_delete'])) && (isset($_POST['id'][0]))) {$this->status = 'final-delete'; $this->form_response = $this->final_delete($this->postedID);}
	}
	private function validate() {
		global $msgs, $allowed_upload_ext;
		      
		$results = false;
		
		$this->init();
		
		if($this->debug) {echo "AutoForm::Validate Procedure Started.<br/>";}
		                                              
		$this->errors = array();
		if((!$this->database) || (!$this->table)) die("Database ('{$this->database}') or table ('{$this->table}') not provided.");
	
		         
		if(((isset($_POST['autoform_create'])) || (isset($_POST['autoform_update'])) || (isset($_POST['autoform_inline'])) || (isset($_POST['autoform_clone']))) && ($this->table != "")) {
			$userID = false;
			$posted_col_names = $found_arrays = $this->errors = array();
			$disabled_fields = array("save-form","row_count");
			$row_count = (isset($_POST['row_count']) ? $_POST['row_count'] : false);
			
			$files = $this->sort_files();
			
			if($this->structure) {
				foreach($this->structure as $s) {$column_names[] = $s['column_name'];}
			} else {
				die("Unable to extract column names from information_schema.columns.  Remember: Only column names with comments will be inserted.");
			}
			
			// Possible bug with userID, I created $this->userID which is set in __construct.
			#if(isset($_POST['userID'])) {array_unshift($column_names,"userID"); $userID = $_POST['userID'];}
			// Not sure why I put that there.
			
			if($this->userID) $userID = $this->userID;
			
			// Build $values array for the $sql, and check for errors.
			// Scan through the column names from the table, and check if they have been POSTed.  There is a possibility of multiple rows being posted at once, 
			// so we use $row_count to loop through them.  For example, If we have a column called 'name' in our form, it would be posted like this:
			// $_POST['name'][0] = 'James'
			// Where '0' is the row count.
			// This way we only INSERT (Or UPDATE) what was posted AND matches the database too.
			
			foreach($column_names as $val) {
				// Scan through the posted rows set in $row_count.
				for($row=0;$row<$row_count;$row++) {
					#dump($this->static_variables);
					#echo "{$val} - ".var_export(in_array($val,$this->static_variables),1)."<br/>";
					if(($val == "userID") || (isset($this->static_variables[$val]))) {
						// userID is the same for all rows, so we give it special treatment.
						if($val == "userID") {
							$values[$row]['userID'] = $userID;
						} elseif(isset($this->static_variables[$val])) {
							$values[$row][$val] = $this->static_variables[$val];
						}
						if(!in_array($val,$posted_col_names)) $posted_col_names[] = $val;
					} else {
						// The column name is not 'userID', so lets check the rest.
						if(stripos($val,"[")) {
							// It's an array.
							$exp = explode("[",$val);
							$arrayname = $exp[0];
							if(!isset($this->errors[$row][$arrayname])) $this->errors[$row][$arrayname] = 1; 
							$id = str_replace(array($arrayname,"[","]"),"",$val);
							if(!in_array($arrayname."[{$id}]",$posted_col_names)) $posted_col_names[] = $arrayname."[{$id}]";
							
							if(isset($_POST[$arrayname][$row][$id])) {
								#$values[$row][$val] = mysql_real_escape_string($_POST[$arrayname][$row][$id]);
								#echo "[arrayval] POST[{$arrayname}]-[{$row}][{$id}] = {$_POST[$arrayname][$row][$id]}<br/>";
								 $values[$row][$arrayname."[{$id}]"] = (($_POST[$arrayname][$row][$id] == "on") ? "1" : utf8_encode($_POST[$arrayname][$row][$id]));
								 // Found a value, there is no error to see here.
								 $this->errors[$row][$arrayname] = false;
							} else { 
								$values[$row][$arrayname."[{$id}]"] = '';	
							}
							// There is no error feedback for arrays, they are all mandatory.
							#if($values[$row][$arrayname."[{$id}]"] > $this->errors[$row][$arrayname]) {
							#	$this->errors[$row][$arrayname] = $values[$row][$arrayname."[{$id}]"];
							#}
						} else {
							// Its a standard value.
							if(isset($_POST[$val][$row])) {
								if(!in_array($val,$posted_col_names)) $posted_col_names[] = $val;
								$value = utf8_encode($_POST[$val][$row]);
								
								# $join['database'] = (isset($this->join_columns[$s['column_name']]["database"]) ? 	$this->join_columns[$s['column_name']]["database"] : $this->database);
								# $join['table'] 	= 	(isset($this->join_columns[$s['column_name']]["table"]) ? 		$this->join_columns[$s['column_name']]["table"] : $this->join_columns_default_values['table']);
								# $join['id'] 	=	(isset($this->join_columns[$s['column_name']]["id"]) ? 			$this->join_columns[$s['column_name']]["id"] : $this->join_columns_default_values['id']);
								# $join['column'] = 	(isset($this->join_columns[$s['column_name']]["column"]) ? 		$this->join_columns[$s['column_name']]["column"] : $this->join_columns_default_values['column']);
								# $join['dir'] 	=	(isset($this->join_columns[$s['column_name']]["dir"]) ? 		$this->join_columns[$s['column_name']]["dir"] : $this->join_columns_default_values['dir']);
								# $join['where'] 	=	(isset($this->join_columns[$s['column_name']]["where"]) ? 		$this->join_columns[$s['column_name']]["where"] : $this->join_columns_default_values['where']);
								# $join['null'] 	=	(isset($this->join_columns[$s['column_name']]['null-option']) ? $this->join_columns[$s['column_name']]['null-option'] : $this->join_columns_default_values['null-option']);
								# $join['default']=	(isset($this->join_columns[$s['column_name']]['default']) ? 	$this->join_columns[$s['column_name']]['default'] : $this->join_columns_default_values['default']);
								# $join['insertable']=(isset($this->join_columns[$s['column_name']]['insertable']) ? 	$this->join_columns[$s['column_name']]['insertable'] : $this->join_columns_default_values['insertable']);
								
								$insertedID = false;
								if((isset($this->join_columns[$val]['insertable'])) && ($this->join_columns[$val]['insertable'] == true)) {
									// It's an insertable column.  Look for a value to insert, and if it's available, insert it.
									// autoform_insert-{$s['column_name']}[]
									if((isset($_POST["autoform_insert-{$val}"][$row])) && ($_POST["autoform_insert-{$val}"][$row] != "")) {
										$insertDB 		= (isset($this->join_columns[$val]["database"]) ? 	$this->join_columns[$val]["database"] : false);
										$insertTable 	= (isset($this->join_columns[$val]["table"]) ? 	$this->join_columns[$val]["table"] : false);
										$insertColumn 	= (isset($this->join_columns[$val]["column"]) ? 	$this->join_columns[$val]["column"] : false);
										if(($insertDB) && ($insertTable) && ($insertColumn)) {
											$this->db->prepared = array('insertData' => $_POST["autoform_insert-{$val}"][$row]);
											$insertedID = $this->db->runSQL("INSERT INTO `{$insertDB}`.`{$insertTable}` (`{$insertColumn}`) VALUES (:insertData);");
										}
									}
								}								
								if($insertedID) {
									$values[$row][$val] = $insertedID;
								} else {
									$values[$row][$val] = $value;
								}
								
								#if(strtolower($val) == "url") $values[$row][$val] = $this->create_url($value);	
								if(function_exists("autoform_feedback")) {
									$this->errors[$row][$val] = autoform_feedback($val, $value);
									if(!$this->errors[$row][$val]) unset($this->errors[$row][$val]);
								} else {               
									if(($value == "") && (!in_array($val,$this->optional_fields))) {
										$this->errors[$row][$val] = "Please enter a value for <b>{$val}.</b>";
									}
								}
							} else {
								//if($val == "autoformID") {
								//	$values[0]['autoformID'] = $_POST[$val];	
								//}
								// $values[$row][$val] = '';	
							}	
						}
					}
					

					// Check if there were any files to upload.
					if(isset($files[$row]['input-name']) && ($files[$row]['input-name'] == $val)) {
						if(!$files[$row]['error']) {
							$ext = pathinfo($files[$row]['name'], PATHINFO_EXTENSION);
							if(in_array($ext,$this->file_types)===TRUE) {
								// File is in allowed upload file types.	
								$posted_col_names[] = $val;
								$values[$row][$val] = $files[$row]['name'];
								$files[$row]['valid'] = true;
							} else {
								$this->errors[$row][$val] = "File type </b>{$ext}</b> not allowed.";
								$files[$row]['valid'] = false; // Don't upload it later.
								unset($values[$row][$val]);
							}
						} else {
							if((isset($_POST['autoform_file_'.$val][$row])) && ($_POST['autoform_file_'.$val][$row] != "")) {
								// There was an error, but there was data found in the text box instead.  So we'll use that.
								$posted_col_names[] = $val;
								$values[$row][$val] = $_POST['autoform_file_'.$val][$row];
								$files[$row]['valid'] = false; // Don't upload it later.
							} else {
								// There was no data given at all for this field.
								if(function_exists("autoform_feedback")) {
									$this->errors[$row][$val] = autoform_feedback($val, $value);
									if(!$this->errors[$row][$val]) unset($this->errors[$row][$val]);
								} else {
									$this->errors[$row][$val] = $files[$row]['error'];
								}
								unset($values[$row][$val]);
								$files[$row]['valid'] = false; 	// Don't upload it later.
							}
						}
					}
				}
			}
			
			// Now Build the SQL using $keys[] and $values[] 
			$sql_type = "create";
			if(isset($_POST['autoform_update'])) {$sql_type = "update";}
			if(isset($_POST['autoform_inline'])) {$sql_type = "update";}
			if(isset($_POST['autoform_clone'])) {
				$sql_type = "create";
				// Remove the `id` field if it exists from all rows.
				#$posted_col_names[] = $val;
				#$values[$x][$val] = $files[$x]['name'];
				foreach($posted_col_names as $key => $val) {if($key == "id") unset($posted_col_names[$key]);}
				foreach($values as $row_number => $row) {
					foreach($row as $key => $val) {if($key == "id") unset($values[$row_number][$key]);}
				}
			}
			
			
			$this->db->prepared = $rows = array();
			switch($sql_type) {
				case "create":
					$sql = "INSERT INTO `{$this->database}`.`{$this->table}` ";
					// Column Names
					#$sql .= " (`" . implode("`,`",$column_names) . "`) VALUES ";
					$sql .= " (`" . implode("`,`",$posted_col_names) . "`) VALUES ";
					// Insert Values
					# foreach($values as $val) $rows[] = "('" . implode("','",$val) . "')";   
					foreach($values as $line => $columns) {
						$row = array();
						foreach($columns as $k => $v) {
							$preparedColName = str_replace(array("[","]","-"),"_",$k);
							if($v=="autoform_null_value") {
								$this->db->prepared[$preparedColName.$line] = null;
							} else {
								$this->db->prepared[$preparedColName.$line] = $v;
							}
							$row[] = ":{$preparedColName}{$line}";
						}
						$rows[] = "(" . implode(",",$row) . ")";
						#$this->db->prepared[] = $val;
					}
					$sql .= implode(",",$rows) . ";";
				break;                
				case "update":
					$sql = "UPDATE `{$this->database}`.`{$this->table}` SET ";
					// Only loop through 0 because we don't update multiple rows at once.  So just the first row.
					$autoformID = false;
					foreach($values[0] as $key => $val) {
						if($key == "id") {
							$autoformID = $val;
						} else {
							$preparedColName = str_replace(array("[","]","-","_"),"",$key);
							if($val == "autoform_null_value") {
								$this->db->prepared[$preparedColName] = null;
							} else {
								$this->db->prepared[$preparedColName] = $val;
							}
							$rows[] = "`{$key}` = :{$preparedColName}";
						}
					}
					if($autoformID) {
						$this->db->prepared["autoformID"] = $autoformID;
						$sql .= implode(",", $rows) . " WHERE `id`=:autoformID;";
					}
				break;				                               
			}
			
			// Scan $this->errors to check if there are actual values in the array.
			$no_errors = true;
			if($this->errors) {
				foreach($this->errors as $key => $row) {
					foreach($row as $err) {if($err != false) {$no_errors = false;}}
				}
			}          
			          
			if($no_errors) { 
				if($this->debug) {echo "No form Errors.<br/>";}
				// No errors, upload files (if there are any)
				if((isset($files)) && (isset($this->filepath))) {
					foreach($files as $f) {   
						if($f['valid']) {
							$uploaded = move_uploaded_file($f['tmp_name'], $this->filepath.$f['name']);
							
						}
					}
				}
				if($this->debug) {echo "<pre><b>\$_POST Data</b><br/>".var_export($_POST,true)."</pre><br/>";}
				if($this->debug) {echo "<pre><b>Prepared Variables:</b><br/>".var_export($this->db->prepared,true)."</pre><br/>";}
				if($this->debug) {echo "<pre><b>SQL Statement:</b><br/>".var_export($sql,true)."</pre><br/>";}
				
				// RunSQL
				$results = $this->db->runSQL($sql);
				
				if(!$this->db->error) {			
					if($this->debug) echo "SQL successfully executed.<br/>";
					$this->form_response = true;
					if(isset($_POST['autoform_inline'])) {echo $results; die();} else {return $results;}
					
				} else {
					if($this->debug) echo "Failed to execute SQL: {$this->db->error}<br/>";
					$this->form_response = false;
					if(isset($_POST['autoform_inline'])) {echo "0"; die();} else {return false;}
				}
			} else {
				if($this->debug) {
					echo "There were errors in the form.<br/>";
					echo "Errors:<br/>";
					var_dump($this->errors);
					if($this->debug) {echo "<pre><b>\$_POST Data</b><br/>".var_export($_POST,true)."</pre><br/>";}
					if($this->debug) {echo "<pre><b>Prepared Variables:</b><br/>".var_export($this->db->prepared,true)."</pre><br/>";}
					if($this->debug) {echo "<pre><b>SQL Statement:</b><br/>".var_export($sql,true)."</pre><br/>";}
				}
				$this->form_response = false;
			
				if(isset($_POST['autoform_inline'])) {echo "0"; die();} else {return false;}
			}
		} else {
			if($this->debug) {echo "autoform_[create||update] not set, or unable to read table ({$this->table}).<br/>";}
			$this->form_response = "autoform_[create||update] not set, or unable to read table ({$this->table}).";
		}
	}
	
	####################
	## Display Table! ##
	####################
	private function display_table($row = false) {
        //  -------Display Table--------
        //  The main function - this is the function that performs the all the work.
        //  It takes the table structure, the comments, the column types and outputs a form
        //  based on that.  The base form that it will output is each column on its own row, 
        //  with a label size of col-xs-6 and the form-control size of col-xs-6.  This can be
        //  severely modified to the users taste depending on the set up they use.
		$this->init();
		
		// Only output the CSS if we are going to use it.  Previously this was in __construct.
		echo $this->css;
		
		$data = array();
		$return = "<!---AutoForm Class by Chud37.com-->\n";
		
		
		// Initialize $data variable
		switch($this->action) {
		case "read":
		case "update":
		case "action":
		case "delete":
			$this->multiple_rows = false;
			$row_data = $this->db->select("SELECT * FROM `{$this->database}`.`{$this->table}` WHERE `id`='{$row}';",true);
			if(!$row_data) {
			$return .= "Unable to select rowID #{$row}."; return $return;}
			// We need to parse the $row_data to make it the same structure as a $_POST var would be.
			foreach($row_data as $k => $v) {$data[$k][0] = $v;}
			// If they have just posted a form, we need to load their data instead, as there were errors.
			if(isset($_POST['autoform_update'])) {
				if(!$this->reset_form) {$data = $_POST;}
				// If there are files in this form, they won't have been uploaded if there were errors.
				// So we need to add them into the $data variable if they don't exist.
				foreach($row_data as $key => $val) {                 
					if($this->is_file_key($key) == "file") {$data[$key][0] = $val;}
				}
			}
		break;               
		default: 
		case false: 
		case 'create':
			if(!$this->reset_form) {$data = $_POST;}
		break;
		}
		
		
		
		// Do not modify
		$same_row = false;
		$displayed_userID = false;
		$file_input_id = 0;
	
		// Find the first comment  
		foreach($this->structure as $val) {if($val['column_comment'] != "") break;}
		
		$row_count = (isset($data['row_count']) ? (int) $data['row_count'] : 1);
		if((!$this->multiple_rows) || ($this->reset_form)) $row_count = 1;
		
		
		//
		//	Begin Output - Build <form> 
		//
		if(($this->action != "read")) {
			if(!$this->form_action) {
				$form_action = "/{$this->page}/{$this->table_data['table_name']}";
			} else {
				$form_action = $this->form_action;                                                                      
			}
			$return .= "<form action='{$form_action}' method='POST'  id='autoform-form' class='{$this->action}' enctype='multipart/form-data'>\n
						<input type='hidden' id='row_count' name='row_count' value='{$row_count}' />
						<input type='hidden' name='MAX_FILE_SIZE' value='10048576' />
						<input type='hidden' name='autoform_posted_table' value='{$this->table}' />";
			if(($this->static_variables) && (is_array($this->static_variables))) {
				foreach($this->static_variables as $name => $val) {
					$return .= 	"<input type='hidden' id='{$name}' name='{$name}' value='{$val}' />\n";
				}
			}
						
			if(($this->action == "update") || (($this->action == "delete"))) {$return .= "<input type='hidden' name='id[]' value='{$row}'>";}
		} else {
			$return .= "<div id='autoform-form' class='{$this->action}'>";	
		}
	
		
		if(($this->action != "read") && ($this->action != "delete")) {
			// Scan and see if we need to print out the userID search function.
			// This is why the 'userID' column is found in hidden_columns, because we need to print it seperately.
			foreach($this->structure as $key => $val) {
				if(strtolower($val['column_name']) == 'userid') {
					 if($this->userID) {$return .= "<input type='hidden' id='userID'  name='userID' value='{$this->userID}' />\n";}
				}
			}
		}
		
		$form_input_cols = (12 - $this->form_label_cols);
		
//
//	Scan columns and determine column types.
//
		foreach($this->structure as $id => $s) {
			// Output the row or column based on it's type and length.
			$type = $this->structure[$id]['column_type'];
			// Just for ease of use: $types = array("enum","varchar","timestamp","text","tinyint","int","date","year"); 
			foreach($this->types as $t) {
				if(stripos($this->structure[$id]['column_type'],$t) !== false) {$type = $t; break;}
			}
			// Check whether this column is in $join_columns or not, change the $type if it is.
			foreach($this->join_columns as $key => $val) {
				if($this->structure[$id]['column_name'] == $key) {
					if(isset($val['table']) && (isset($val['column']))) $type = "joined_column"; break;
				}
			}
			
			// If the column_name has an '[' in it, then we can assume it's part of an array of choices.
			if(stripos($this->structure[$id]['column_name'],"[") !== false) {
				// Scan all columns for the rest of the array.
				// Array columns are outputted under 'autoform-array'
				$arrayname = explode("[",$this->structure[$id]['column_name']);
				// Make sure we haven't already scanned it.
				$found = false;
				foreach($this->array_columns as $name => $column_data) {
					if($name == $this->structure[$id]['column_name']) {$found = true;$type = "autoform-array";}
					foreach($column_data as $d) {if($d['name'] == $this->structure[$id]['column_name']) {$found = true; $type = "autoform-array";}}
				}
				
				if(!$found) {
					$column_array = array();                 
					foreach($this->structure as $k => $st) {
						if((stripos($st['column_name'],$arrayname[0]) !== false) && ($st['column_name'] != $this->structure[$id]['column_name'])) {
							$this->hidden_columns[] = $st['column_name'];
							$columnID = str_replace(array($arrayname[0],"[","]"),"",$st['column_name']);
							$column_array[] = array("name" => $st['column_name'], "comment" => $st['column_comment'], "array_name" => $arrayname[0], "id" => $columnID);
						}
					}
					if(count($column_array) >= 1) {
						$columnID = str_replace(array($arrayname[0],"[","]"),"",$this->structure[$id]['column_name']);
						// echo "adding: " . var_export(array("name" => $this->structure[$id]['column_name'], "comment" => $this->structure[$id]['column_comment'], "array_name" => $arrayname[0], "id" => $columnID));
						array_unshift($column_array, array("name" => $this->structure[$id]['column_name'], "comment" => $this->structure[$id]['column_comment'], "array_name" => $arrayname[0], "id" => $columnID));
						$type = "autoform-array";
						$this->array_columns[$this->structure[$id]['column_name']] = $column_array;
						$this->structure[$id]['arrayname'] = $arrayname[0];
					} 
				}
													 
			}
			$this->structure[$id]['type'] = $type;
		}
		                               
//
// Begin Row Output Loop
// $locateID
		$return .= "<div id='duplicate-section'>\n";
		for($row=0; $row < $row_count; $row++) {
			$hidden_columns = $this->hidden_columns;
			$return .= "<div class='duplicate-fields'>\n";
			
			# $force_same_row = false; // Remove this later
			$col_count = 0;
			
			// Output until row_count.
			foreach($this->structure as $id => $s) {
				
				
				
				// If it is not found in $hidden_columns, show it.
				if((!in_array(strtolower($s['column_name']),$this->hidden_columns)) && (!in_array($s['column_name'],$this->hidden_columns))) {
					// Get the column type.  The only exception to this is below, if we determine it to be an array.
					# $type = $this->col_type($id);
					# echo $s['column_name'] . " -- " . $s['type'] . "<br/>";
					
					// Find which type of row we need to display based on column_name or column_type
					// If same row is false, start a new row. 
					$row_class = false;
					if(!$same_row) {
						// If this column sit's on its own row, give it a class name.
						if(in_array($type,$this->full_rows) || (in_array($s['column_name'],$this->full_rows))) {
							$row_class = " " . $s['column_name'];
						}
						$return .= "<div class='row-divide{$id}'>".(isset($this->titles[$id]) ? "<h3 class='autoform_title {$s['column_name']}'>{$this->titles[$id]}</h3>" : false)."</div>
									<div class='row{$row_class} row-number-{$id}'>";
					}
					
					
					
					// If theres no comment for this column, use the column_name
					if($s['column_comment'] == "") {$s['column_comment'] = ucwords(str_replace(array("_","-")," ",$s['column_name']));}
					
					// Errors given back from $this->validate();
					if($s['type'] == "autoform-array") {
						$error_class = ((isset($this->errors[$row][$s['arrayname']]) && ($this->errors[$row][$s['arrayname']] !== false)) ? "error" : false);
					} else {
						$error_class = (isset($this->errors[$row][$s['column_name']]) ? "error" : false);	
					}
					
					$error_message = (isset($this->errors[$row][$s['column_name']]) ? "<span class='error-message'>{$this->errors[$row][$s['column_name']]}</span>" : false);
					
					// The debug text shown only when $this->debug is true.
					$debug = ($this->debug ? "<span class='debug'>{$s['column_name']}, {$s['column_type']}</span>" : false);
					
					// Label & Input size override.
					$overridden_label_cols = $overridden_input_cols = false; 
					
					// Label & Input Classes
					$form_label_classes = array_filter(array("form-label",$this->find_cell_data($s['column_name'],"label_class"),$error_class,$s['column_name']));
					$form_input_classes = array_filter(array("form-input",$this->find_cell_data($s['column_name'],"input_class"),$error_class,$s['column_name']));					
					      
					$disabled = ($this->action == "read" ? " disabled" : false);
					
/////////////////////////////////
	    # BEGIN SWITCH #
///////////////////////////////// 
					switch($s['type']) {
						
					case "joined_column":		
						// This column grabs info from another table.
						// Get external data.
						$join = array();
						$join['database'] = (isset($this->join_columns[$s['column_name']]["database"]) ? 	$this->join_columns[$s['column_name']]["database"] : $this->database);
						$join['table'] 	= 	(isset($this->join_columns[$s['column_name']]["table"]) ? 		$this->join_columns[$s['column_name']]["table"] : $this->join_columns_default_values['table']);
						$join['id'] 	=	(isset($this->join_columns[$s['column_name']]["id"]) ? 			$this->join_columns[$s['column_name']]["id"] : $this->join_columns_default_values['id']);
						$join['column'] = 	(isset($this->join_columns[$s['column_name']]["column"]) ? 		$this->join_columns[$s['column_name']]["column"] : $this->join_columns_default_values['column']);
						$join['dir'] 	=	(isset($this->join_columns[$s['column_name']]["dir"]) ? 		$this->join_columns[$s['column_name']]["dir"] : $this->join_columns_default_values['dir']);
						$join['where'] 	=	(isset($this->join_columns[$s['column_name']]["where"]) ? 		$this->join_columns[$s['column_name']]["where"] : $this->join_columns_default_values['where']);
						$join['null'] 	=	(isset($this->join_columns[$s['column_name']]['null-option']) ? $this->join_columns[$s['column_name']]['null-option'] : $this->join_columns_default_values['null-option']);
						$join['default']=	(isset($this->join_columns[$s['column_name']]['default']) ? 	$this->join_columns[$s['column_name']]['default'] : $this->join_columns_default_values['default']);
						$join['insertable']=(isset($this->join_columns[$s['column_name']]['insertable']) ? 	$this->join_columns[$s['column_name']]['insertable'] : $this->join_columns_default_values['insertable']);
						// Try and get the data from the database using the information that we have.
						$join['data'] = $this->db->select("SELECT `{$join['id']}`,`{$join['column']}` FROM `{$join['database']}`.`{$join['table']}` {$join['where']} ORDER BY `{$join['column']}` {$join['dir']};");
						
						if((!$join['table']) || (!$join['column']) || (!$join['data'])) {
							$form_input = "<div class='col-xs-{$form_input_cols} form-input error'>Join Error.<br/>
											MySQL Error: " . $this->db->last_error . "<br/>
											Table: " . var_export($join['table'],true) . "<br/>
											Column: " . var_export($join['column'],true) . "<br/>
											Data: " . var_export($join['data'],true) . "</div>";
						} else {
				
							$select = "<select name='{$s['column_name']}[]' id='autoform_{$s['column_name']}_select' class='form-control {$error_class}' title='".$this->find_cell_data($s['column_name'],"input_title")."'{$disabled}>\n";
							$previously_selected = (isset($data[$s['column_name']][$row]) ? $data[$s['column_name']][$row] : false);
							if(($join['default']) && (!$previously_selected)) {$previously_selected = $join['default'];}
							if(($join['null']) || (in_array($s['column_name'], $this->null_option))) {
								$select .= "<option value='autoform_null_value' ".selected($previously_selected, "NULL").">None</option>\n";
							}elseif(in_array($s['column_name'], $this->please_select)) {	
								$select .= "<option value='' ".selected($previously_selected, '').">Please Select..</option>\n";
							} 
							foreach($join['data'] as $v) {
								$human_friendly_value = trim(ucwords(str_replace(array("_","-")," ",strtolower($v[$join['column']]))));
								$select .= "<option value='{$v[$join['id']]}'".selected($previously_selected, $v[$join['id']]," selected").">{$human_friendly_value}</option>\n";
							}
							$select .= "</select>\n";
							
							if(($join['insertable']) && ($this->action != "read")) { 
								$form_input = "<div id='autoform_{$s['column_name']}_insertable_add' class='input-group'>
													<span class='input-group-btn'>
														<button type='button' data-column='{$s['column_name']}' data-friendly-name='{$s['column_comment']}' data-insert-target='#autoform_insert-{$s['column_name']}' class='join-column-insertable btn btn-default add'><span class='fa fa-plus' style='margin:0;'></span></button>
													</span>
													{$select}
												</div>
												<div id='autoform_{$s['column_name']}_insertable_cancel' class='input-group hide'>
													<span class='input-group-btn'>
														<button type='button' data-column='{$s['column_name']}' data-friendly-name='{$s['column_comment']}' data-insert-target='#autoform_insert-{$s['column_name']}' class='join-column-insertable btn btn-default cancel'><span class='fa fa-times' style='margin:0;'></span></button>
													</span>
													<input type='text' id='autoform_insert-{$s['column_name']}' name='autoform_insert-{$s['column_name']}[]' class='form-control'>
												</div>";
							} else {
								$form_input = $select;	
							}
							
						}
					break;
					
					
					
					
					
					case "enum":
						// And ENUM column type requries a SELECT box for the user to choose a specific value.
						$values = explode(",",str_replace($this->purify_col_names,"",$s['column_type']));
						
						// Find largest value to determine column size.
						$largest = 0;
						foreach($values as $v) {if(strlen($v) > $largest) $largest = strlen($v);}
						
						$test_size = floor($largest / 50);
						$input_size = (isset($this->varchar_sizes[$test_size]) ? $this->varchar_sizes[$test_size] : $form_input_cols); 

						$form_input = "<select name='{$s['column_name']}[]' class='form-control {$error_class}' title='".$this->find_cell_data($s['column_name'],"input_title")."'{$disabled}>\n";
						$previously_selected = (isset($data[$s['column_name']][$row]) ? $data[$s['column_name']][$row] : false);
						if(in_array($s['column_name'], $this->please_select)) {		// Adds 'Please Select' to the drop down.
							$form_input .= "<option value='' ".selected($previously_selected, '').">Please Select..</option>\n";
						}elseif(in_array($s['column_name'], $this->null_option)) {	// Adds a 'None' option to the select drop down.
							$form_input .= "<option value='NULL' ".selected($previously_selected, "NULL").">None</option>\n";
						} 
						foreach($values as $v) {
							$human_friendly_value = str_replace(array("_","-")," ",$v);
							$form_input .= "<option ".selected($previously_selected, $human_friendly_value).">{$human_friendly_value}</option>\n";
						}
						$form_input .= "</select>\n";
					break;
					
					
					
					
					
					case "varchar":
						// VarChar is a simple text box.
						$input_type = "text";
										
						if((stripos($s['column_comment'],"email") !== false) || (stripos($s['column_comment'],"e-mail") !== false)) {$input_type = "email";}
						if(stripos($s['column_comment'],"phone") !== false) {$input_type = "telephone";}
						$size = str_replace($this->purify_col_names,"",$s['column_type']);
						
						$input_type = $this->is_file_key($s['column_name'], $input_type);
						
						// Unless it's a file. 
						$image = false;
						
						
						
						if($input_type == "file") {
							
							
							if(($this->action == "read") || ($this->action == "update") || ($this->action == "delete")) {
								
								
								if(is_file($this->filepath . $data[$s['column_name']][$row])) {
									
									
									$mime = mime_content_type($this->filepath . $data[$s['column_name']][$row]);
								
									if(($mime == "image/jpeg") || ($mime == "image/png") || ($mime == "image/gif")) {
										if($this->display_image) $image = "<img src='{$this->urlpath}/{$data[$s['column_name']][$row]}' class='autoform-image {$this->image_align} img-responsive' />";
									}
									
								} else {
									
									if(strtolower(substr($data[$s['column_name']][$row],0,4)) == "http") {
										if($this->display_image) $image = "<img src='{$data[$s['column_name']][$row]}' class='autoform-image {$this->image_align} img-responsive' />";
									}	
								}
							}
							
							if(($this->action == "read") || ($this->action == "delete")) { 
								$input_group = "<input type='text' id='{$s['column_name']}-{$row}' class='form-control file-input-text' value=\"".(isset($data[$s['column_name']][$row]) ? $data[$s['column_name']][$row] : "")."\"{$disabled}>";
							} else {
								$input_group = "<input type='file' name='{$s['column_name']}[{$row}]' style='display:none;' />\n
												<div class='input-group {$this->image_align}'>
													<input type='text' name='autoform_file_{$s['column_name']}[{$row}]' id='{$s['column_name']}-{$row}' class='form-control file-input-text'  title='".$this->find_cell_data($s['column_name'],"input_title")."'  placeholder='".$this->find_cell_data($s['column_name'],"placeholder")."' value='".(isset($data[$s['column_name']][$row]) ? $data[$s['column_name']][$row] : "")."'>
													<span class='input-group-btn'>
														<a class='btn btn-default file-input-btn' name='{$s['column_name']}[{$row}]' title='".$this->find_cell_data($s['column_name'],"input_title")."'>Browse</a>
													</span>
												</div>";
							}
							$form_input = "{$image}\n{$input_group}\n";
							
							if($this->image_select) {
								$images = getContents($this->filepath,true);
								$return .= "
									</div> <!--Close Col-->
									</div> <!--Close Row-->
									<div id='image-select' class='row'>
									<div class='col-xs-".($overridden_label_cols ? $overridden_label_cols : $this->form_label_cols)."'></div>
									<div class='col-xs-{$form_input_cols}'>";
									for($img=0;$img<40;$img++) {
										$return .= "<div class='col-xs-2'>
														<a href='#' class='image-select' data-url='{$this->urlpath}{$images[$img]}' data-target='{$s['column_name']}-{$row}'><img src='{$this->urlpath}{$images[$img]}' class='img-responsive'></a>
													</div>";}
									
									$return .= "";
								
							}
							
							# $force_same_row = true;
						} else {
							// Phew! That was a wild ride, right? But like I said, just a simple textbox.
							$test_size = floor($size / 50);
							$input_size = (isset($this->varchar_sizes[$test_size]) ? $this->varchar_sizes[$test_size] : $form_input_cols);      
							
							$dropdownlist = $this->find_cell_data($s['column_name'],"dropdownlist");
							
							if(($dropdownlist) && is_array($dropdownlist)) {
								// Unless its a drop down list.  Which it still could be, if overridden by the code that calls the class.
								$associative = $this->isAssociative($dropdownlist);
								$form_input = "<select name='{$s['column_name']}[]' class='form-control {$error_class}' title='".$this->find_cell_data($s['column_name'],"input_title")."'{$disabled}>\n";
								
								
								$previously_selected = (isset($data[$s['column_name']][$row]) ? $data[$s['column_name']][$row] : false);
								if(in_array($s['column_name'], $this->please_select)) {		// Adds 'Please Select' to the drop down.
									$form_input .= "<option value='' ".selected($previously_selected, '').">Please Select..</option>\n";
								}elseif(in_array($s['column_name'], $this->null_option)) {	// Adds a 'None' option to the select drop down.
									$form_input .= "<option value='NULL' ".selected($previously_selected, "NULL").">None</option>\n";
								} 
							
								foreach($dropdownlist as $key => $val) {
									if($associative)  {
										$form_input .= "<option value='{$key}' ".selected($previously_selected, $key).">{$val}</option>\n";
									} else {
										$form_input .= "<option ".selected($previously_selected, $val).">{$val}</option>\n";
									}
								}
								$form_input .= "</select>\n";
							} else {
								// But NOW it's a simple text box! 
								$form_input = "<input type='{$input_type}' class='form-control {$error_class}' name='{$s['column_name']}[]'  title='".$this->find_cell_data($s['column_name'],"input_title")."' maxlength='{$size}' placeholder='".$this->find_cell_data($s['column_name'],"placeholder")."' value=\"".(isset($data[$s['column_name']][$row]) ? htmlspecialchars(utf8_decode($data[$s['column_name']][$row])) : false)."\" {$disabled}/>\n";
							}
						}
					break;
					
					
					
					case "year":
					case "double":
					case "int":                                             
					case "float":
						// Large number Output
						# $size = str_replace($this->purify_col_names,"",$s['column_type']);
						
						$dropdownlist = $this->find_cell_data($s['column_name'],"dropdownlist");
							
						if(($dropdownlist) && is_array($dropdownlist)) {
							// Unless its a drop down list.  Which it still could be, if overridden by the code that calls the class.
							$associative = $this->isAssociative($dropdownlist);
							$form_input = "<select name='{$s['column_name']}[]' class='form-control {$error_class}' title='".$this->find_cell_data($s['column_name'],"input_title")."'{$disabled}>\n";
							
							$previously_selected = (isset($data[$s['column_name']][$row]) ? $data[$s['column_name']][$row] : false);
							if(in_array($s['column_name'], $this->please_select)) {		// Adds 'Please Select' to the drop down.
								$form_input .= "<option value='' ".selected($previously_selected, '').">Please Select..</option>\n";
							}elseif(in_array($s['column_name'], $this->null_option)) {	// Adds a 'None' option to the select drop down.
								$form_input .= "<option value='NULL' ".selected($previously_selected, "NULL").">None</option>\n";
							} 
						
							foreach($dropdownlist as $key => $val) {
								if($associative)  {
									$form_input .= "<option value='{$key}' ".selected($previously_selected, $key).">{$val}</option>\n";
								} else {
									$form_input .= "<option ".selected($previously_selected, $val).">{$val}</option>\n";
								}
							}
							$form_input .= "</select>\n";
						} else {
							$form_input = "<input type='number' step='any' class='form-control {$error_class}' name='{$s['column_name']}[]' placeholder='".$this->find_cell_data($s['column_name'],"placeholder")."' title='".$this->find_cell_data($s['column_name'],"input_title")."' value='".(isset($data[$s['column_name']][$row]) ? $data[$s['column_name']][$row] : "")."'{$disabled}/>\n";
						}
					break;                                                               
						
					
					
					case "autoform-array": 
						// Array Column First
						if((isset($this->errors[$row][$s['arrayname']])) && ($this->errors[$row][$s['arrayname']] != false)) {  
							$error_class = "error";
							$error_message = "<span class='error-message'>You must select at least one.</span>";
						}                 
						$form_input = "<div class='btn-group' data-toggle='buttons'>";
						foreach($this->array_columns[$s['column_name']] as $ca) {
							$active = false;
							if(isset($data[$ca['array_name']][$row][$ca['id']])) {
								// If $data is being loaded from a $_POST variable, then the data will look like this.
								$active = (isset($data[$ca['array_name']][$row][$ca['id']]) && (strtolower($data[$ca['array_name']][$row][$ca['id']]) == "on") ? "active" : false);
							} elseif(isset($data[$ca['name']][$row])) {
								// If the data is being loaded from the database, it will look like this.
								$active = (isset($data[$ca['name']][$row]) && ($data[$ca['name']][$row] == "1") ? "active" : false);
							}
							$form_input .= "<label class='btn btn-default button-array {$this->btn_size} {$active}' title='".$this->find_cell_data($s['column_name'],"input_title")."'><input type='checkbox' name='{$ca['array_name']}[$row][{$ca['id']}]' ".selected($active,"active","checked").">{$ca['comment']}</label>\n";
						}
						$form_input .= "</div>";
						$same_row = 1;
					break;
					
					
					case "tinyint":
						$int_size = str_replace($this->purify_col_names,"",$s['column_type']);
						// Small Number Output
						// Because `int` is in small_values, col_size is divided by 2.
						$input_size = floor($form_input_cols/4);
													
						$post_value = (isset($data[$s['column_name']][$row]) ? $data[$s['column_name']][$row] : (isset($this->defaults[$s['column_name']]) ? $this->defaults[$s['column_name']] : false));
					
						$form_input = "<select name='{$s['column_name']}[]' class='form-control {$error_class} {$s['column_name']}' title='".$this->find_cell_data($s['column_name'],"input_title")."'{$disabled}>";
						if(in_array($s['column_name'], $this->please_select)) { 		// Adds 'Please Select' to the drop down.
							$form_input .= "<option value='' ".selected($previously_selected, '').">Please Select..</option>\n";
						}elseif(in_array($s['column_name'], $this->null_option)) {		// Adds a 'None' option to the select drop down.
							$form_input .= "<option value='NULL' ".selected($previously_selected, "NULL").">None</option>\n";
						}
						
						switch($int_size) {
							case "1":
								// Boolean Output
								$form_input .= "<option value='1' ".selected($post_value,1)." class='yes'>Yes</option>\n<option value='0' ".selected($post_value,0)." class='no'>No</option>\n";
							break;
							case "2":
								if(stripos($s['column_name'],"month") !== false) {
									for($count=1;$count<13;$count++) {$form_input .= "<option value='{$count}' ".selected($post_value,$count).">".ucwords($this->months[$count])."</option>";}
								} else {
									for($count=0;$count<99;$count++) {$form_input .= "<option ".selected($post_value,$count).">{$count}</option>\n";}
								}
							break;
							case "4":
								for($count=0;$count<255;$count++) {$form_input .= "<option ".selected($post_value,$count).">{$count}</option>\n";}
							break;
						}
						$form_input .= "</select>\n";
					break;
					
					
					
					case "text":
						if(($this->action == "read") || ($this->action == "delete")) {
							$form_input = "<div class='form-control textarea {$error_class} {$s['column_name']}'>".(isset($data[$s['column_name']][$row]) ? utf8_decode($data[$s['column_name']][$row]) : "")."</div>\n";
						} else {
							$form_input = "	<textarea name='{$s['column_name']}[]' class='form-control {$error_class} {$s['column_name']}' title='".$this->find_cell_data($s['column_name'],"input_title")."' placeholder='".$this->find_cell_data($s['column_name'],"placeholder")."'{$disabled}>".(isset($data[$s['column_name']][$row]) ? utf8_decode($data[$s['column_name']][$row]) : "")."</textarea>\n";
							
						}
						#$force_same_row = true;
						#$col_count += (($overridden_label_cols ? $overridden_label_cols : $this->form_label_cols) + ($overridden_input_cols ? $overridden_input_cols : $form_input_cols));
					break;
					
					
					case "date":
						$form_input = "<input type='date' class='form-control {$error_class}' name='{$s['column_name']}[]' title='".$this->find_cell_data($s['column_name'],"input_title")."' value='".(isset($data[$s['column_name']][$row]) ? $data[$s['column_name']][$row] : "")."' {$disabled}/>\n";
					break;
					
					case "timestamp":
						// Default col_size for timestamps.
						$form_input = "<input type='datetime' class='form-control {$error_class}' name='{$s['column_name']}[]' title='".$this->find_cell_data($s['column_name'],"input_title")."' placeholder='".date("d-m-Y",time())."' {$disabled}/>\n";
					break;
					
					case false:
					default:
						$form_input = "Unrecognized type: {$type},<br/>{$s['column_name']}";
					} // {End Switch}
	
					
					list($label_size,$input_size) = $this->col_size($id);
					
					// Output the label
					$compact_label = false;
					$set_compact = ((($this->compact) || ($this->find_cell_data($s['column_name'],"compact"))) ? true : false);
					$override_label = $this->find_cell_data($s['column_name'],"label");
					
					if($override_label) {
						$label_text = $override_label;	
					} elseif($s['type'] == "autoform-array") {
						$label_text = ucwords($this->array_columns[$s['column_name']][0]['array_name']);
					} else {
						$label_text = $s['column_comment'];
					}
					
					$label = "<div class='".(!$set_compact ? "col-xs-{$label_size} ":false).implode(" ",$form_label_classes)."' title='".$this->find_cell_data($s['column_name'],"label_title")."'>{$label_text}{$error_message}{$debug}</div>\n";
					
					# echo "{$s['column_name']} -- " . var_export($this->find_cell_data($s['column_name'],"compact"),1) . "<br/>";$label_text
								
					if($set_compact) {
						$compact_label = true;
						$col_count += $input_size;
					} else {
						$return .= $label;
						$col_count += ($label_size + $input_size);
					}
					// Add the form_input
					$return .= "<div class='col-xs-{$input_size} ".implode(" ",$form_input_classes)."'>".($set_compact ? $label:false)."{$form_input}</div>";
					
					// Debug info in case we need to modify in the future.
					$this->debug_messages("<hr><b>{$s['column_comment']}</b> (Current $col_count: {$col_count})");
					if($overridden_label_cols) $this->debug_messages(":: <i>Label</i> Column Size Overridden to: {$overridden_label_cols}");
					if($overridden_input_cols) $this->debug_messages(":: <i>Input</i> Column Size Overridden to: {$overridden_input_cols}");
					
					// Find the ID of the next item we are going to display.
					$nextID = false;  
					for($locateID=($id+1);$locateID<count($this->structure);$locateID++) {
						if((isset($this->structure[$locateID])) && (!in_array($this->structure[$locateID]['column_name'],$this->hidden_columns))) {$nextID = $locateID; break;} 
					} 
					
					if(isset($this->structure[$nextID])) {
						# $this->debug_messages("<b>\$force_same_row:</b> ".var_export($force_same_row,true));
						$next_size = str_replace($this->purify_col_names,"",$this->structure[$nextID]['column_type']);
						$next_type = $this->structure[$nextID]['type'];
						$this->debug_messages("Next Column Name: <b>{$this->structure[$nextID]['column_name']}</b> (Size: {$next_size}), Type: <b>{$next_type}</b>");
						list($next_label_size,$next_input_size) = $this->col_size($nextID);
						
						//$this->debug_messages("Next Column {$s['column_name']}: " . var_export($this->find_cell_data($s['column_name'],"compact"),1));
						
						if($this->find_cell_data($this->structure[$nextID]['column_name'],"compact")) {
							$next_col_count = $next_input_size;
							$this->debug_messages("Next Column: {$s['column_name']} is <b>compact</b>.");
						} else {
							$next_col_count = $next_label_size + $next_input_size;
						}
						
						$same_row = true;
						$this->debug_messages("Col_Count: {$col_count} &amp; Next Col Count: {$next_col_count}");
						if(($col_count >= 12) || (($col_count + $next_col_count) > 12)) {
							$same_row = false;                                                                                                            
							$col_count = 0;
							$this->debug_messages("Next Col Count greater than 12.");
							$this->debug_messages("\$same_row set to <b>false</b>");                                                                                       
						}
					}  else {$same_row = false; $col_count = 0;}
					                                                                                                                                                                         
					if(in_array($type,$this->full_rows) || (in_array($s['column_name'],$this->full_rows))) {
						$same_row = false; $col_count=0;
						$this->debug_messages("Full Rows or Force Same Row is <b>true</b>");
					}
					if($col_count >= 12) {$same_row = false; $col_count = 0;}
					
					if(isset($this->cells[$s['column_name']]['break_after'])) {
						$same_row = false;$col_count =0;
						$this->debug_messages("<i>Break After</i> set to <b>true</b>");
					}
					                                               
					$this->debug_messages("On Close: <b>\$same_row:</b> ".var_export($same_row,true).", <b>\$col_count:</b> {$col_count}");
					// If same_row == false, close the row.
					if(!$same_row) {$return .= "</div><!--End Row-->";}
				}
			}
			
			# if(($col_count < 12) && ($same_row)) $return .= "</div><!--Close Row {$col_count}-->";
			
			
			// If multiple_rows && multiple_buttons are true, the plus/minus buttons are display at the bottom of each div.duplicate-fields
			if(($this->multiple_rows) && ($this->multiple_buttons)) {
				$return .= "<div class='row'>
								<div class='col-xs-12 text-right'>
									".($this->buttons['plus']['show'] ? "<button type='button' name='autoform_plus' class='{$this->buttons['plus']['class']}' alt='{$this->buttons['plus']['alt']}' title='{$this->buttons['plus']['title']}'>".($this->buttons['plus']['icon'] ? "<span class='fa fa-{$this->buttons['plus']['icon']}'></span>":false)."{$this->buttons['plus']['caption']}</button>\n":false)."
									".($this->buttons['minus']['show'] ? "<button type='button' name='autoform_minus' class='{$this->buttons['minus']['class']}' alt='{$this->buttons['minus']['alt']}' title='{$this->buttons['minus']['title']}'>".($this->buttons['minus']['icon'] ? "<span class='fa fa-{$this->buttons['minus']['icon']}'></span>":false)."{$this->buttons['minus']['caption']}</button>\n":false)."
								</div>
							  </div>";}
			
			$return .= "</div><!--End Duplicate Fields-->";
		}
		$return .= "</div><!--End Duplicate Section-->\n";
		
		
		if(($this->action != "read") && ($this->action != "delete")) {       
			$return .= "<div class='row button-bar'>\n";
			$button_output = array();
			foreach($this->buttons as $button => $button_info) {
				if($button_info['show']) {
					$button_data = false;
					switch($button) {
						case "delete": case "clone": $button_data = "type='submit' name='autoform_{$button}'";break;
						case "save": $button_data = "type='submit' name='autoform_".($this->action != false ? $this->action : "create")."'"; break;
						case "plus": case "minus":
							if(!$this->multiple_buttons) {$button_data = "type='button' name='autoform_{$button}'";}
						break;
					}
					if($button_data) $button_output[] = "<button {$button_data} class='{$button_info['class']}' alt='{$button_info['alt']}' title='{$button_info['title']}'>".($button_info['icon'] ? "<span class='fa fa-{$button_info['icon']}'></span>":false)."{$button_info['caption']}</button>\n";;
				}
			}
			
			$return .= "<div class='col-xs-12'>";
			foreach($button_output as $b) {$return .= "{$b}";}
			$return .= "</div>";
			
			// Close the row and the form.
			$return .= "</div>\n</form>";
		} else {
			if($this->action == "delete") {    
				$return .= "<div class='row button-bar'>\n
								<div class='col-xs-12 text-right'>\n
								<button type='submit' class='btn btn-danger' name='autoform_final_delete'><span class='fa fa-times'></span>Delete</button>\n
								</div>
							</div>";
				// Close the row and the form.
				$return .= "</form>";
			}
			
			#$return .= "</div><!--Close Container--->";	
		}
		
		?>
		<script>
		function searchUserID() {
			var e = $('#userID-search-string').val();
			$.ajax({
				url: '/ext/event/search.php?s=' + e,
				context: document.body
			}).done(function(e) {
				$('#userID-search-results').html(e)
			})
		}
		$(document).ready(function() {
							
			$("#autoform-form button.join-column-insertable.add").click(function () {
				var identity = $(this).data("friendly-name");
				var column = $(this).data("column");
				var target = $(this).data("insert-target");
				
				var newValue = prompt("Please enter a new value for "+identity);
				if(newValue) {
					$(target).val(newValue);
					$("#autoform_"+column+"_insertable_add").addClass("hide");
					$("#autoform_"+column+"_insertable_cancel").removeClass("hide");
				}
			});
			$("#autoform-form button.join-column-insertable.cancel").click(function () {
				var column = $(this).data("column");
				var target = $(this).data("insert-target");
				$(target).val("");
				$("#autoform_"+column+"_insertable_add").removeClass("hide");
				$("#autoform_"+column+"_insertable_cancel").addClass("hide");
			});
			$('select.boolean').on('change', function(ev) {
				$(this).removeClass("no").removeClass("yes");
				$(this).addClass($(this).children(':selected').text().toLowerCase());
			});
			$("#autoform-form").on("click", "a.image-select",function(e) {
				e.preventDefault();
				$("#"+$(this).data("target")).val($(this).data("url"));
			});
			$("#autoform-form").on("click", "a.file-input-btn",function(e) {
				e.preventDefault();
				var name = this.name;
				$("input[name='"+name+"']").click();
			});
			$("#autoform-form").on("change","input[type='file']", function() {
				var name = "#"+this.name.replace("[",'-').replace("]",'');
				$(name).val($(this).val().replace(/C:\\fakepath\\/i, ''));
			});
			$('#autoform-form #userID').change(function() {
				$(this).removeClass('check')
			});
			$('#autoform-form').on("click", "button[name=autoform_plus]", function() {
			// $('#autoform-form button[name=autoform_plus]').click(function() {
				$('div.duplicate-fields:last-child').clone().find('input').each(function() {
					//console.log("type:" + $(this).prop('type'));
					switch($(this).prop('type')) {
						case 'checkbox':this.name = this.name.replace(/\[(\d+)\]/, function(e, t) {return '[' + (parseInt(t, 10) + 1) + ']'}), this.checked = !1;break;
						default:this.name = this.name.replace(/\[(\d+)\]/, function(e, t) {return '[' + (parseInt(t, 10) + 1) + ']'}), this.value = null;break;}
				}).end().find('label.button-array.active').each(function() {
					$(this).removeClass('active')
				}).end().find("a.file-input-btn").each(function() {
					this.name = this.name.replace(/\[(\d+)\]/, function(e, t) {return '[' + (parseInt(t, 10) + 1) + ']'});
				}).end().find("input.file-input-text").each(function() {
					this.id = this.id.replace(/(\d+)/, function(e, t) {return (parseInt(t, 10) + 1)});
				}).end().appendTo('#duplicate-section'), $('#autoform-form input#row_count').val($('div.duplicate-fields').length);
			});
			$('#autoform-form').on("click", "button[name=autoform_minus]", function() {
				if($(this).closest("div.duplicate-fields").length) {
					if($("div.duplicate-fields").length > 1) {$(this).closest("div.duplicate-fields").remove();}
				}
				if($(this).closest("div.button-bar").length) {
					if($("div.duplicate-fields").length > 1) {$("div.duplicate-fields:last-child").remove();}
				}
				$('#autoform-form input#row_count').val($('div.duplicate-fields').length)
			});
			$('#autoform-form #userID-search-string').keypress(function(e) {
				13 == e.which && (e.preventDefault(), searchUserID())
			});
			$('#autoform-form #userID-search-button').click(function() {
				searchUserID()
			})
			$('#autoform-form #content #locate-userID #userID-search-results').on('click', 'a.userID-select-user', function() {
				var e = $(this).attr('data-id');
				$('#userID').val(e).addClass('check')
			});
		});
	</script>
		<?
		
		return $return;
	}
	
	private function sort_files() {
		//	Sort $_FILES into a easily managed array.
		//	Usage: $files = sortFiles();
		$error_code = array(0 => "", UPLOAD_ERR_INI_SIZE => "File too large.",UPLOAD_ERR_FORM_SIZE => "Filesize exceeds max size.",UPLOAD_ERR_PARTIAL => "Upload error =>  only partially uploaded.",UPLOAD_ERR_NO_FILE => "No file was uploaded", UPLOAD_ERR_NO_TMP_DIR =>  "Missing a temporary folder", UPLOAD_ERR_CANT_WRITE =>  "Failed to write file to disk.", UPLOAD_ERR_EXTENSION => "File upload stopped by extension.");
		$files = array();
		// 'image' => array (
		// 	'name' => array (
		// 		  0 => '20130602_155328000_iOS.jpg',
		// 		  1 => '20130603_081937463_iOS.jpg',
		// 		),
		// 	'type' => array (
		// 		  0 => 'image/jpeg',
		// 		  1 => '',
		// 		),
		// 	'tmp_name' => array (
		// 		  0 => '/tmp/phponrgTJ',
		// 		  1 => '',
		// 		),
		// 	'error' => array (
		// 		  0 => 0,
		// 		  1 => 2,
		// 		),
		// 	'size' => array (
		// 		  0 => 207053,
		// 		  1 => 0,
		// 		))
		if((isset($_FILES))) {
			foreach($_FILES as $key => $file) {
				foreach($file as $k => $f) {
					foreach($f as $row => $val) {
						if($k == "error") {
							$files[$row][$k] = $error_code[$val];
						} else {
							$files[$row][$k] = $val;
						}
						$files[$row]['input-name'] = $key;
					}
				}
			}
			return $files;
		} else {
			return false;	
		}
	}
	private function find_cell_data($column_name = false, $data_type = false) {
		if((!$column_name) || (!$data_type)) return false;
		// Check to see if they want to override the form_label size and/or form_input size.
		foreach($this->cells as $column => $data) {
			if(($column == $column_name) && (isset($data[$data_type]))) {return $data[$data_type];}
		}
		// We 'aint found a match, Fagin.
		return false;
	}

	private function col_size($columnID = false) {
		// Override Sizing: Check to see if they want to override the form_label size and/or form_input size.
		$overridden_label_cols = $this->find_cell_data($this->structure[$columnID]['column_name'],"label_size");
		$overridden_input_cols = $this->find_cell_data($this->structure[$columnID]['column_name'],"input_size");
		$label_size = ($overridden_label_cols ? $overridden_label_cols : $this->form_label_cols);
	    if($this->compact) {
			$input_size = 12;
		} else {
			$input_size = 12 - $this->form_label_cols;
		}
		
		if($overridden_input_cols) {$input_size = $overridden_input_cols;}
		                                               
		if($this->compact) $label_size = 0;
		
		return array($label_size,$input_size);
	}                     
	private function isAssociative(array $arr) {
		if (array() === $arr) return false;
		return array_keys($arr) !== range(0, count($arr) - 1);
	}
	
	######################
	## Helper Functions ##
	######################
	public function create_url($str) {
		return str_replace(" ","_",strtolower(preg_replace("/[^A-Za-z0-9 _]/", '', trim($str))));	
	}
	public function addarray($arrayname, $array = array()) {
	if((isset($this->$arrayname)) && (is_array($array))) {
		$this->{$arrayname} = array_merge($this->{$arrayname}, $array);
	} else {
		return false;	
	}
}
	public function hide_column($column = false) {
		if(!$column) return false;
		if(is_array($column)) {
			foreach($column as $c) {$this->hidden_columns[] = $c;}	
		} else {
			$this->hidden_columns[] = $column;	
		}
		return true;
	}
	public function set_label_sizes($size = false) {
		if(!$size) return false;
		foreach($this->structure as $s) {
			$this->cells[$s['column_name']]['label_size'] = $size;	
		}
	}
	public function set_input_sizes($size = false) {
		if(!$size) return false;
		foreach($this->structure as $s) {
			$this->cells[$s['column_name']]['input_size'] = $size;	
		}
	}
	public function add_hidden($key = false, $var = false) {
		if((!$key) || (!$var)) return false;
		// Overwrite if exists.
		$this->static_variables[$key] = $var;
	}
	public function title($rowID = false, $title = false) {
		if((!$rowID) || (!$title)) return false;
		$this->titles[$rowID] = $title;
		return true;
	}
	
	##################################
	## Label / Form Input Functions ##
	##################################
	public function column($column = false) {
		$this->current_item = $column;
		return $this;
	}
	public function hide() {
		foreach($this->hidden_columns as $h) {if($h == $this->current_item) return false;}
		array_push($this->hidden_columns, $this->current_item);
	}
	public function optional() {
		foreach($this->optional_fields as $h) {if($h == $this->current_item) return false;}
		$this->optional_fields[] = $this->current_item;
	}
	public function set_compact($compact = false) {
		if($compact) {return $this->chain_command("cells",$compact,"compact");} else {return false;}
	}
	public function full_row($column = false) {
		if($column) {
			foreach($this->full_rows as $h) {if($h == $this->current_item) return false;}
			array_push($this->full_rows, $this->current_item);
		}
	}
	public function label_size($size = false) {
		if($size) {return $this->chain_command("cells",$size);} else {return false;}
	}
	public function input_size($size = false) {
		if($size) {return $this->chain_command("cells",$size);} else {return false;}
	}
	public function label_class($class = false) {
		if($class) {return $this->chain_command("cells",$class);} else {return false;}
	}
	public function input_class($class = false) {            
		if($class) {return $this->chain_command("cells",$class);} else {return false;}
	}
	public function label_title($title = false) {
		if($title) {return $this->chain_command("cells",$title);} else {return false;}
	}
	public function input_title($title = false) {
		if($title) {return $this->chain_command("cells",$title);} else {return false;}
	}
	public function break_after($break = false) {
		if($break === true) {return $this->chain_command("cells",$break);} else {return false;}
	}
	public function placeholder($placeholder = false) {
		if($placeholder) {return $this->chain_command("cells",$placeholder);} else {return false;}
	}
	public function label($label = false) {
		if($label) {return $this->chain_command("cells",$label);} else {return false;}
	}
	public function dropdownlist($array = array()) {
		if(($array) && (is_array($array))) {return $this->chain_command("cells",$array);} else {return false;}
	}
	
	##########################
	## Join Table Functions ##
	##########################
	public function join_to($database = false) {
		if($database) {return $this->chain_command("join_columns",$database, "database");} else {return false;}
	}    
	public function table($table = false) {
		if($table) {return $this->chain_command("join_columns",$table);} else {return false;}
	}    
	public function where($where = false) {
		if($where) {return $this->chain_command("join_columns",$where);} else {return false;}
	}    
	public function use_column($use_column = false) {
		if($use_column) {return $this->chain_command("join_columns",$use_column, "column");} else {return false;}
	}    
	public function set_default($val = false) {
		if($val) {return $this->chain_command("join_columns",$val, "default");} else {return false;}
	}
	public function id($id = false) {
		if($id) {return $this->chain_command("join_columns",$id);} else {return false;}
	}
	public function null($null = false) {
		if($null) {return $this->chain_command("join_columns",$null, "null-option");} else {return false;}
	}
	public function insertable($insert = false) {
		if($insert) {return $this->chain_command("join_columns",$insert);} else {return false;}
	}
	
	
	############################
	## Button Chain Functions ##
	############################
	public function button($button = false) {
		$this->current_item = $button;
		return $this;
	}
	public function show($show = false) {
		return $this->chain_command("buttons",$show);
	} 
	public function caption($caption = false) {
		if($caption) {return $this->chain_command("buttons",$caption);} else {return false;}
	}  
	public function classes($classes = false) {
		if($classes) {return $this->chain_command("buttons",$classes,"class");} else {return false;}
	}
	public function icon($icon = false) {
		if($icon) {return $this->chain_command("buttons",$icon);} else {return false;}
	}

	private function chain_command($arrayname, $var, $key_override = false) {
		$key = ($key_override ? $key_override : debug_backtrace()[1]['function']);
		if((isset($this->{$arrayname}[$this->current_item])) && (is_array($this->{$arrayname}[$this->current_item]))) {                                     
			$this->{$arrayname}[$this->current_item][$key] = $var;
		} else {
			$this->{$arrayname}[$this->current_item] = array($key => $var);	
		}
		return $this;
	}
	public function switch_tables($newTable = false) {
		if($newTable) {
			$this->table = $newTable;
			$this->structure = $this->db->select("SELECT column_name, column_comment, column_type FROM information_schema.columns WHERE `table_schema`='{$this->database}' AND table_name='{$this->table}';");
			if(!$this->structure) {$this->status = "{$this->table} not found in database.";}
			$information_schema = $this->db->select("SELECT * FROM information_schema.tables WHERE table_name='{$this->table}';",true);
			if($information_schema) {
				foreach($information_schema as $key => $val) {$this->table_data[strtolower($key)] = $val;}
			} else {
				$this->status = "Could not load table data from `information_schema`.`tables`.  Check your table name.";
			}
		} else {
			return false;	
		}
	}
	
	###########
	## Lists ##
	###########
	public function image_list($orderby = false, $direction = "ASC", $limit_amount = false) {
		
		$this->init();
		
		// if(!$this->list['image']) {$this->status = "image_list['image'] variable not set."; return false;}
		// if(!$this->list['title']) {$this->status = "image_list['title'] variable not set."; return false;}
		// if(!$this->list['url']) {$this->status = "image_list['url'] variable not set."; return false;}

		$image = ($this->list['image'] ? $this->list['image'] : "image");  
		$title = ($this->list['title'] ? $this->list['title'] : "name");  
		$url = ($this->list['url'] ? $this->list['url'] : "url");  
		
		$return = "<!---AutoForm Class by Chud37.com-->\n";
                                                   
		$sql_order_by = ($orderby ? $orderby : $this->list['title']);
		$sql_limit = ($limit_amount ? "LIMIT {$limit_amount}" : false);
		if(!isset($this->list['sql'])) {$sql = "SELECT {$columns} FROM `{$this->database}`.`{$this->table}` ORDER BY `{$sql_order_by}` {$direction} {$sql_limit};";} else {$sql = $this->list['sql'];}
	
		#$table_count = $this->db->select("SELECT COUNT(*) FROM `{$this->database}`.`{$this->table}`;",true,true);
		$table_data = $this->db->select($sql);
		                                                                           
		
		if(mysql_error() && ($this->debug)) {
			$this->debug_messages("MySQL Error: " . mysql_error());
			$this->status = "MySQL Error: " . mysql_error(); 
			$return .= "MySQL Error.";
		}
		
		if($table_data) {
			// Table Data
			foreach($table_data as $td) {                       
				if(strtolower(substr($td[$image],0,4)) == "http") {
					$image_src = $td[$image];	
				} else {
					$image_src = $this->urlpath . $td[$image];	
				}
				
				$body = $this->list['body'];
				foreach($td as $key => $val) {
					if(stripos($this->list['body'],"{".$key."}") !== false) {
						$body = str_replace("{".$key."}",utf8_encode($val),$body);
					}
				}
				
				$return .= "<a href='{$this->form_action}{$td[$url]}' class='autoform_image-list-item'>
								<div class='row'>
									<div class='col-xs-1 autoform_image-list-item-image'>
										<img src='{$image_src}' class='img-responsive'>
									</div>
									<div class='col-xs-11 autoform_image-list-item-body'>
										{$body}
										<span class='hover'>Click to edit</span>
									</div>
								</div>
							</a>";

			}
		}            
		
		return $return;
		
	}
	public function table_list() {
	
		// Outputs a table overview to the browser.
		// Settings:
		//	$this->list['sql'] to set specific SQL
		//	$this->list['columns'] to show only certain columns.
		// 	$this->list['button'] to display a button and set a string for button text.
		//	$this->form_action to set the URL action for the form
		// 	$this->inline_edit to TRUE for inline editing
		$this->init();
		
		$return = "<!---AutoForm Class by Chud37.com-->\n";
          
		// Only output the CSS if we are going to use it.  Previously this was in __construct.
		echo $this->css;
		
		$sql = "SELECT * FROM `{$this->database}`.`{$this->table}` ORDER BY 1 {$this->list['dir']};";
		if(isset($this->list['sql']) && $this->list['sql']) {$sql = $this->list['sql'];}
		$table_data = $this->db->select($sql);
		                                                                           
		if(!$table_data) {
			$return .= "No table data.";
			if($this->debug) {echo "SQL: $sql<br/>";}
			return $return;	
		}
		
		if(!isset($this->list['columns'])) {
			$this->list['columns'] = array();
			foreach($table_data[0] as $column => $row) {
				$this->list['columns'][] = $column;
			}	
		}
		
		$hidden_columns = $this->hidden_columns;
		
		foreach($table_data[0] as $column => $row) {
			if(!in_array($column,$this->list['columns'])) $hidden_columns[] = $column;
		}
		
		if($table_data) {
			
			// Table Headers
			$return .= "<table id='autoform_table' class='table table-condensed table-hover'><thead>";
			foreach($table_data[0] as $key => $val) {
				if(!in_array($key,$hidden_columns)) {
					$theader = $key;
					foreach($this->structure as $k => $v) {
						if(($v['column_name'] == $key) && ($v['column_comment'] != "")) $theader = $v['column_comment'];
					}
					// If $theader STILL equals key at this point ucwords it. 
					if($theader == $key) $theader = ucwords(str_replace(array("-","_")," ",$key));
					$return .=  "<th>{$theader}</th>";
				}
			}
			if(isset($this->list['button'])) {$return .= "<th><!--Form Button--></th>";}
			$return .= "</thead><tbody>";
			
			if(!$this->form_action) {
				$form_action = "/{$this->page}/{$this->table_data['table_name']}";
			} else {
				$form_action = $this->form_action;
			}
			
			
			// Table Data
			
			foreach($table_data as $td) {
				$return .= "<tr>";
				
				foreach($td as $column => $value) {
					if(!in_array($column,$hidden_columns)) {
						if((stripos($column,"month") !== false) && (isset($this->months[$value]))) {
							$return .= "<td class='{$column}'>".ucwords($this->months[$value])."</td>";
						} else {
							foreach($this->structure as $s) {
								if($s['column_name'] == $column) {
									$columntype = $s['column_type'];	
								}
							}
							
							$outputValue = $value;
							switch($columntype) {
								case "tinyint(1)":
									switch($value) {
										case 0: $outputValue = "No"; break;
										case 1: $outputValue = "Yes"; break;
									}
								break;
							}
							
							$return .= "<td class='{$column} autoform_inline-editable autoform_inline-row-{$td['id']}' data-column='{$column}' data-rowid='{$td['id']}' ".(isset($this->list['button']) ? "contenteditable='true'" : false).">{$outputValue}</td>";
						}
					}
				}
				
				if(isset($this->list['button'])) {
					$button = $this->list['button'];
					foreach($td as $key => $val) {
						$button = $this->var_replace($button,$key,$val);	
					}
					$return .= "<td class='autoform_table-button text-right'>
									<a href='{$this->form_action}{$button}' class='btn btn-default autoform_inline-view row{$td['id']}'>View</a>";
					// Inline editable content for quick updating.
					if($this->inline_edit) {
						$return .= "<form class='autoform_inline-form row{$td['id']}' action='{$form_action}' method='POST'>\n
									<input type='hidden' name='autoform_inline' value='true' />\n
									<input type='hidden' name='row_count' value='1' />\n
									<input type='hidden' name='id[0]' value='{$td['id']}' />\n";
						foreach($td as $column => $value) {if(!in_array($column,$hidden_columns)) {$return .= "<input type='hidden' name='{$column}[0]' class='autoform_inline-value-{$td['id']}-{$column}' value='' />";}}
						$return .= "<button type='submit' class='btn btn-primary autoform-inline-submit row{$td['id']}' data-rowid='{$td['id']}'><span class='fa fa-floppy-o'></span>Save</button></form>";
					}
					$return .= "</td>";
				}
				$return .= "</tr>";
			}
			$return .= "</tbody></table>";
		} ?>
		<script>
		$(document).ready(function() {
			$("td.autoform_inline-editable").click(function () {
				var column = $(this).data("column");
				var rowID = $(this).data("rowid");
				$("button.autoform-inline-submit.row"+rowID).show();
			});
			
			$(document).on("paste", "td.autoform_inline-editable", function(e){
				e.preventDefault();
				var text = e.originalEvent.clipboardData.getData("text/plain");
				document.execCommand("insertHTML", false, text);
			}); 
			
			$("form.autoform_inline-form").on("click", "button.autoform-inline-submit",function(e) {
				var rowID = $(this).data("rowid");
				// Populate the hidden fields.
				$("td.autoform_inline-row-"+rowID).each(function () {
					var column = $(this).data("column");
					$("input.autoform_inline-value-"+rowID+"-"+column).val($(this).html());
				});
				// Submit the form.
				var postData = $("form.autoform_inline-form.row"+rowID).serializeArray();
				var formURL = $("form.autoform_inline-form.row"+rowID).attr("action");
				$.ajax({
					url : formURL,
					type: "POST",
					data : postData,
					success:function(data, textStatus, jqXHR) 
					{
						alert("Updated row.");
						$("button.autoform-inline-submit.row"+rowID).hide();
					},
					error: function(jqXHR, textStatus, errorThrown) 
					{
						alert(textStatus);
						
					}
				});
				e.preventDefault();
				//e.unbind();
			});
		});
		</script><?
		return $return;
	}
}
?>