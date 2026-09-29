<?php
require("vendor/autoload.php");
$openapi = (new \OpenApi\Generator())->generate(['src/Controller/Api']);
#header('Content-Type: application/x-yaml');
$filename = 'public_html/crm/swagger-config.json';
if (file_put_contents($filename, $openapi->toJson()) === FALSE) {
	echo "Cannot write to file ($filename)";
}

