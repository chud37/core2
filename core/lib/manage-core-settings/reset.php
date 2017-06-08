<div id='reset-warning' class='container-fluid'>
	<div class='row'>
		<div class='col-sm-12'>
			<h2>Reset The Core</h2>
		</div>
	</div>
	
	<div class='row'>
		<div class='col-sm-12'>
			<b style='color:#f00;'>Warning:</b> Reseting the core will wipe all current user data.
			<br/><br/>
			It is <b>highly recommended</b> that you backup your INI files first using the tool below, before you reset.
			<br/><br/>
			Are you sure you want to do this?
		</div>
	</div>
	
	<div class='row'>
		<div class='col-xs-12 text-center'>
			<form action='/manage-core-settings' method='POST'>
				<input type='hidden' name='update-core-settings' value='reset-core-settings' />
				<button type='submit' class='btn btn-danger btn-lg' alt='Reset' title='Reset'><span class='fa fa-undo'></span>Yes, Reset</button>
			</form>
		</div>
	</div>
</div>


<div id='backup-files' class='container-fluid'>
	<div class='row'>
		<div class='col-sm-12'>
			<h2>Backup INI Files</h2>
		</div>
	</div>
	
	<div class='row'>
		<div class='col-sm-12'>
			In order to backup your INI files, please 
		</div>
	</div>
	
	<div class='row'>
		<div class='col-xs-12 text-center'>
			<form action='/manage-core-settings/reset' method='POST'>
				<input type='hidden' name='update-core-settings' value='backup-core-settings' />
				<button type='submit' class='btn btn-info btn-lg' alt='Backup Files' title='Backup Files'><span class='fa fa-floppy-o'></span>Backup Files</button>
			</form>
		</div>
	</div>
</div>