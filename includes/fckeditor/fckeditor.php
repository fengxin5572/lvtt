<?php
//dezend by  QQ:2172298892
if (!function_exists('version_compare') || version_compare(phpversion(), '5', '<')) {
	include_once 'fckeditor_php4.php';
}
else {
	include_once 'fckeditor_php5.php';
}

?>