<?php
$start = microtime(true);

// Get absolute path to application
$ds = DIRECTORY_SEPARATOR;
$install_dir = str_replace($ds.'public', '', dirname(__FILE__));

// Include PHPFrame
if (!class_exists("PHPFrame")) {
    require_once $install_dir.$ds."lib".$ds."PHPFrame".$ds."PHPFrame.php";
}

// Create new instance of "Application"
$app = new PHPFrame_Application(array("install_dir"=>$install_dir));

// Handle request
$app->dispatch();

$output_format = $app->response()->header("Content-Type");
$ajax = $app->request()->ajax();
if ($output_format == "text/html" && !$ajax) {
    echo "\n<!-- Total execution time ".round((microtime(true) - $start), 3)." seconds -->";
}
