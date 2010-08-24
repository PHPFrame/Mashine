<?php
$start = microtime(true);

// Include PHPFrame
if (!class_exists("PHPFrame")) {
    require_once "PHPFrame.php";
}

// Get absolute path to application
$install_dir = str_replace(DS.'public', '', dirname(__FILE__));

// Create new instance of "Application"
$app = new PHPFrame_Application(array("install_dir"=>$install_dir));

// Handle request
$app->dispatch();

$output_format = $app->response()->header("Content-Type");
if ($output_format == "text/html") {
    echo "\n<!-- Total execution time ".round((microtime(true) - $start), 3)." seconds -->";
}
