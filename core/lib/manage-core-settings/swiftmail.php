<form action='/manage-core-settings/<?=$section;?>' method='POST'>
<input type='hidden' name='update-core-settings' value='PHP-swiftMail-settings' />

<div class='container-fluid'>
	
	<div class='row'>
		<div class='col-sm-11 bold'>Swiftmail</div>
		<div class='col-sm-1 text-right'>
			<button type='button' class='toggle-info' href='#swiftmail-info' data-toggle='collapse'>?</button>
		</div>
	</div>                                                                                                 
	<div class='row'>
		<div class='col-sm-2 text-right'>IP</div>
		<div class='col-sm-10'>
			<input type='text' name='setting[ip]' value='<?=$core_settings['swiftmail']['ip'];?>' placeholder='localhost' class='form-control' />
		</div>                                                                                                                 
	</div>
	<div class='row'>
		<div class='col-sm-2  text-right'>Port</div>                               
		<div class='col-sm-10'>
			<input type='text' name='setting[port]' value='<?=$core_settings['swiftmail']['port'];?>' placeholder='25' class='form-control' />
		</div>
	</div>
	<div class='row'>
		<div class='col-sm-2  text-right'>Username</div>
		<div class='col-sm-10'>
			<input type='text' name='setting[username]' value='<?=$core_settings['swiftmail']['username'];?>' placeholder='john@hotmail.com' class='form-control' />
		</div>
	</div>
	<div class='row'>
		<div class='col-sm-2  text-right'>Password</div>
		<div class='col-sm-10'>
			<input type='text' name='setting[password]' value='<?=$core_settings['swiftmail']['password'];?>' placeholder='password' class='form-control' />
		</div>
	</div>
		
	<div id='swiftmail-info' class='row collapse'>
		<div class='col-sm-offset-2 col-sm-10'>
			<b>Swiftmail</b> allows you to design and send beautiful PHP mail messages.  However, this can only work when the correct settings set.  Once that is set, you can use the object <b>$swiftmail</b> to send the message. 			
		</div>
	</div>
			
	
	<div class='row'>
		<div class='col-sm-offset-2 col-sm-6'>
			<a href='http://swiftmailer.org/docs/introduction.html' target='_blank'>Swiftmail Documentation</a>
		</div>
		<div class='col-sm-4 text-right'>
			<button type='submit' class='btn btn-primary' alt='Save' title='Save'><span class='fa fa-floppy-o'></span>Save Site Settings</button>
		</div>
	</div>


</div>
</form>

