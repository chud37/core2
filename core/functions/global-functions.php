<?
function messages($newMsgs = false, $container = true) {
	global $msgs;
	if($newMsgs) {$displayMsgs = $newMsgs;} else {$displayMsgs = $msgs;}
	if(!$displayMsgs) return false;
	if($container) echo "<div class='container alert-messages'>";
	$f = array("<br>","<br/>","\n");
	$r = "<br/>";
	$messageArrays = array("warning" => array(), "danger" => array(), "info" => array(), "success" => array());
	if($displayMsgs) { 
		foreach($displayMsgs as $val) {
			switch (substr($val,0,1))
			{	case "!" : $messageArrays['danger'][] = substr(str_replace($f,$r,$val),1); break;	
				case "*" : $messageArrays['warning'][] = substr(str_replace($f,$r,$val),1); break;	
				case "?" : $messageArrays['info'][] = substr(str_replace($f,$r,$val),1); break;	
				default: $messageArrays['success'][] = str_replace($f,$r,$val); break;
			}
		}
		foreach($messageArrays as $key => $array) {
			if($array) {
				echo "<div class='row alert-message'><div class='col-xs-12'><div class='alert-box ";
				$iconSize = "small";
				if(count($array) >= 2) {$iconSize = "medium";}
				if(count($array) >= 4) {$iconSize = "large";}
				
				switch ($key) {
					case "danger" : 	echo "alert-danger container-fluid'><div class='col-xs-1 text-center'><i class='alert-icon fa fa-exclamation-circle {$iconSize}'></i></div><div class='col-xs-10'>"; break;
					case "warning" : 	echo "alert-warning container-fluid'><div class='col-xs-1 text-center'><i class='alert-icon fa fa-exclamation-triangle {$iconSize}'></i></div><div class='col-xs-10'>"; break;
					case "info" : 		echo "alert-info container-fluid'><div class='col-xs-1 text-center'><i class='alert-icon fa fa-info-circle {$iconSize}'></i></div><div class='col-xs-10'>"; break;
					case "success": 	echo "alert-success container-fluid'><div class='col-xs-1 text-center'><i class='alert-icon fa fa-check {$iconSize}'></i></div><div class='col-xs-10'>"; break;
				}
				foreach($array as $message) {echo $message . "<br/>";}
				echo "</div></div></div></div>";
			}
		}
	}

	if($container) echo "</div>";
}


function selected($var,$str,$text = "selected") {	
	// Useful for quickly selecting the correct <option> during HTML element <select>, or even adding 'selected' class to any element.
	if(is_array($var)) {
		foreach($var as $v) {if($v == $str) return $text;}
	} else {
		if($var == $str) return $text;
	}
	return false;
}
function recaptcha() {
	global $core_settings;
	// http://stackoverflow.com/a/30749288/1445985
	
	try {
		$url = 'https://www.google.com/recaptcha/api/siteverify';
        $data = array(	'secret'   => $core_settings['recaptcha']['secret'],
                 		'response' => (isset($_POST['g-recaptcha-response']) ? $_POST['g-recaptcha-response'] : false),
                 		'remoteip' => $_SERVER['REMOTE_ADDR']);
        $options = array(
            'http' => array(
                'header'  => "Content-type: application/x-www-form-urlencoded\r\n",
                'method'  => 'POST',
                'content' => http_build_query($data) 
            )
        );
        $context  = stream_context_create($options);
        $result = file_get_contents($url, false, $context);
        return json_decode($result)->success;
    } catch (Exception $e) {
        return null;
    }
}
?>