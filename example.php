<?php
require_once("class.edgeos.php");

$EdgeOS = new EdgeOS($ip="10.15.100.170", $user="ubnt", $pass="Get214Out");

echo "<pre>";
print_r($EdgeOS->GetSFPs());
print_r($EdgeOS->GetInterfaces());
print_r($EdgeOS->GetSystemInfo());
echo "</pre>";

?>
