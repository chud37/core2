<?
	header("Content-type: text/css; charset: UTF-8");
	$backgroundPath = str_replace($_SERVER['DOCUMENT_ROOT'],"",__DIR__)."/background.jpg";
?>
	body {background: url('<?=$backgroundPath;?>');background-attachment: fixed;}
