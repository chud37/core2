<div class='container body'>
	<div class='row'>
		<div id='menu' class='col-sm-3'>
			<b>Site Settings</b>
			<div class="list-group small">
				<a class='list-group-item <?=selected("site-settings",$section,"active");?>' href='/manage-core-settings/site-settings'>Site Settings</a>
				<a class='list-group-item <?=selected("google-recaptcha",$section,"active");?>' href='/manage-core-settings/google-recaptcha'>Google Recaptcha</a>
				<a class='list-group-item <?=selected("swiftmail",$section,"active");?>' href='/manage-core-settings/swiftmail'>SwiftMail</a>
				<a class='list-group-item <?=selected("upload-download",$section,"active");?>' href='/manage-core-settings/upload-download'>Upload Download</a>
				<a class='list-group-item <?=selected("meta-og",$section,"active");?>' href='/manage-core-settings/meta-og'>Meta / OG Tags</a>
				<a class='list-group-item <?=selected("constants",$section,"active");?>' href='/manage-core-settings/constants'>User Constants</a>
				<a class='list-group-item <?=selected("files",$section,"active");?>' href='/manage-core-settings/files'>Dependencies</a>
			</div>
			<b>Database &amp; Authentication</b>
			<div class="list-group small">
				<a class='list-group-item <?=selected("authentication",$section,"active");?>' href='/manage-core-settings/authentication'>Authentication</a>
				<a class='list-group-item <?=selected("page-access",$section,"active");?>' href='/manage-core-settings/page-access'>Page Access Levels</a>
				<a class='list-group-item <?=selected("database",$section,"active");?>' href='/manage-core-settings/database'>Database</a>        
			</div>
			<b>Information &amp; Reset</b>
			<div class='list-group small'>
				<a class='list-group-item <?=selected("build",$section,"active");?>' href='/manage-core-settings/build'>Current Build</a>
				<a class='list-group-item <?=selected("core-histories",$section,"active");?>' href='/manage-core-settings/core-histories'>Version Histories</a>
				<a class='list-group-item <?=selected("log-files",$section,"active");?>' href='/manage-core-settings/log-files'>Log Files</a>
				<a class='list-group-item <?=selected("php-info",$section,"active");?>' href='/manage-core-settings/php-info'>PHP Info</a>
				<a class='list-group-item <?=selected("reset",$section,"active");?>' href='/manage-core-settings/reset'>Backup &amp; Reset</a>
			</div>         
		</div>
		<div id='<?=$section;?>' class='col-sm-9'>
		<?
			if(is_file(ABSOLUTE . "/{$section}.php")) {
				require_once(ABSOLUTE . "/{$section}.php");
			} else {
				require_once(ABSOLUTE . "/site-settings.php");
			}
		?>
		</div>
	</div>
</div>