<?php
// Add HTML attributes
$html_node = $this->dom()->getElementsByTagName("html")->item(0);
$this->addNodeAttr($html_node, "xmlns:fb", "http://www.facebook.com/2008/fbml");

// Get some useful stuff from the application object
$user      = $app->user();
$session   = $app->session();
$sysevents = $session->getSysevents();
$renderer  = $app->response()->renderer();
$app_name  = $app->config()->get("app_name");
$base_url  = $app->config()->get("base_url");
$request   = $app->request();
$content   = $request->param("active_content");

// Add Javascript and CSS
$this->addScript("http://ajax.googleapis.com/ajax/libs/jquery/1.4.2/jquery.min.js");
$this->addScript("http://ajax.googleapis.com/ajax/libs/jqueryui/1.8.0/jquery-ui.min.js");
$this->addScript("http://ajax.microsoft.com/ajax/jquery.validate/1.6/jquery.validate.pack.js");
$this->addScript($base_url."assets/js/cms.js");
$this->addStyleSheet($base_url."assets/css/cms.css");

if ($user->groupId() > 0 && $user->groupId() <= 2) {
    $this->addScript($base_url."assets/js/cms.admin.js");
    $this->addStyleSheet($base_url."assets/css/cms.admin.css");
}
?>

<div><a name="up" id="up"></a></div>

<div id="wrapper">

<h1 id="sitename">
    <a href="index.php"><?php echo $app_name; ?></a>
</h1>

<div id="topmenu">
<?php echo $renderer->renderPartial("menu")."\n"; ?>
</div>

<?php
echo $renderer->renderPartial(
    "sysevents",
    array("events"=>$sysevents)
)."\n";
?>

[cms:type=breadcrumbs]

<div id="content">
<?php echo $this->body()."\n"; ?>
</div><!-- #content -->

<?php
    echo $renderer->renderPartial(
        "sidebar",
        array("content"=>$content)
    )."\n";
?>

<div id="push"></div>
</div><!-- #wrapper -->


<div id="footer">
Powered by <?php echo nl2br(PHPFrame::version())."\n"; ?>
</div>

<script type="text/javascript" charset="utf-8">
    var base_url = '<?php echo $base_url; ?>';
</script>
