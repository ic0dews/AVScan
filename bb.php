<?php
if (!isset($_GET['id']))
{
  exit();
}
echo '<pre>';
echo '[size=large]Runtime scan by [color=#FFFFFF][b]AvScan[/b][/color]: [/size] ' . PHP_EOL;
echo '[url=http://avscan.network/result.php?id=' . $_GET['id'] . '][img]https://avscan.space/img/' . $_GET['id'] . ".png[/img][/url]";
echo '</pre>';
 ?>
