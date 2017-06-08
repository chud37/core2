<div class='container-fluid'>
	<div class='row'>
		<div class='col-sm-12'><h1>Core v<?=CORE_VERSION;?></h1></div>
	</div>
	<? foreach($version_history as $version => $notes) { ?>
	<div class='row version-history'>
		<div class='col-sm-1 bold'><?=$version;?></div>
		<div class='col-sm-11'><?=$notes;?></div>
	</div>
	<? } ?>
</div>

<div class='container-fluid'>
	<div class='row'>
		<div class='col-sm-12'><h1>Database v<?=$db->version;?></h1></div>
	</div>
	<? foreach($db->version_history as $version => $notes) { ?>
	<div class='row version-history'>
		<div class='col-sm-1 bold'><?=$version;?></div>
		<div class='col-sm-11'><?=$notes;?></div>
	</div>
	<? } ?>
</div>

<div class='container-fluid'>
	<div class='row'>
		<div class='col-sm-12'><h1>Authentication v<?=$auth->version;?></h1></div>
	</div>
	<? foreach($auth->version_history as $version => $notes) { ?>
	<div class='row version-history'>
		<div class='col-sm-1 bold'><?=$version;?></div>
		<div class='col-sm-11'><?=$notes;?></div>
	</div>
	<? } ?>
</div>