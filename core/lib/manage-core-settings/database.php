<form action='/manage-core-settings/<?=$section;?>' method='POST'>
<input type='hidden' name='update-core-settings' value='true' />

<div class='container-fluid'>
	
	<div class='row'>
		<div class='col-sm-12 bold'>Database Credentials</div>
	</div>
		
	<div class='row'>
		<div class='col-sm-4 text-right'>Host</div>
		<div class='col-sm-8'>
			<input type='text' name='site_name' value='<?=$db->databaseID['host'];?>' placeholder='My Website' class='form-control' />
		</div>
	</div>
	<div class='row'>
		<div class='col-sm-4 text-right'>Database</div>
		<div class='col-sm-8'>
			<input type='text' name='site_name' value='<?=$db->databaseID['database'];?> ' placeholder='My Website' class='form-control' />
		</div>                                       
	</div>
	<div class='row'>
		<div class='col-sm-4 text-right'>Username</div>
		<div class='col-sm-8'>
			<input type='text' name='site_name' value='<?=$db->databaseID['username'];?>' placeholder='My Website' class='form-control' />
		</div>
	</div>                                                                                                            
	<div class='row'>
		<div class='col-sm-4 text-right'>Password</div>
		<div class='col-sm-8'>
			<input type='text' name='site_name' value='<?=$db->databaseID['password'];?>' placeholder='My Website' class='form-control' />
		</div>
	</div>

	
	<div class='row'>
		<div class='col-sm-12 text-right'>
			<button type='submit' class='btn btn-primary' alt='Save' title='Save'><span class='fa fa-floppy-o'></span>Save Authentication Settings</button>
		</div>
	</div>
</div>
