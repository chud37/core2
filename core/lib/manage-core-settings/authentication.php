<form action='/manage-core-settings/<?=$section;?>' method='POST'>
<input type='hidden' name='update-core-authentication' value='authentication-credentials' />

<div class='container-fluid'>
	<div class='row'> 
		<div class='col-sm-12 bold'>Authentication Credentials</div>
	</div>
	
	<div class='row'>
		<div class='col-sm-4 text-right'>Host</div>
		<div class='col-sm-8'>
			<input type='text' name='setting[host]' value='<?=$auth->authCredentials['host'];?>' placeholder='localhost' class='form-control' />
		</div>
	</div>
	<div class='row'>
		<div class='col-sm-4 text-right'>Database</div>
		<div class='col-sm-8'>
			<input type='text' name='setting[database]' value='<?=$auth->authCredentials['database'];?>' placeholder='Database' class='form-control' />
		</div>
	</div>
	<div class='row'>                                                                                         
		<div class='col-sm-4 text-right'>Username</div>
		<div class='col-sm-8'>
			<input type='text' name='setting[username]' value='<?=$auth->authCredentials['username'];?>' placeholder='Username' class='form-control' />
		</div>
	</div>
	<div class='row'>
		<div class='col-sm-4 text-right'>Password</div>
		<div class='col-sm-8'>
			<input type='text' name='setting[password]' value='<?=$auth->authCredentials['password'];?>' placeholder='password' class='form-control' />
		</div>
	</div>
	<div class='row'>
		<div class='col-sm-4 text-right'>User Table</div>
		<div class='col-sm-8'>
			<input type='text' name='setting[table]' value='<?=$auth->authCredentials['table'];?>' placeholder='users' class='form-control' />
		</div>
	</div>
	<div class='row'>
		<div class='col-sm-4 text-right'>Locked Access</div>
		<div class='col-sm-8'>
			<select name='setting[locked_access]' class='form-control' />
				<option value='0' <?=selected($auth->locked_access, "0");?>>No</option>
				<option value='1' <?=selected($auth->locked_access, "1");?>>Yes</option>
			</select>
		</div>
	</div>

	
	<div class='row'>
		<div class='col-sm-12 text-right'>
			<button type='submit' class='btn btn-primary' alt='Save' title='Save'><span class='fa fa-floppy-o'></span>Save Authentication Settings</button>
		</div>
	</div>
</div>
</form>