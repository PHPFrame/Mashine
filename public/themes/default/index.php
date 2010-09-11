<?php
// Change doctype to HTML5
$imp = new DOMImplementation();
$this->doctype($imp->createDocumentType("html"));

// Get some useful stuff from the application object
$user      = $app->user();
$session   = $app->session();
$sysevents = $session->getSysevents();
$renderer  = $app->response()->renderer();
$app_name  = $app->config()->get("app_name");
$base_url  = $app->config()->get("base_url");
$request   = $app->request();
$content   = $request->param("_content_active");

// Add Javascript and CSS
$this->addScript("http://ajax.googleapis.com/ajax/libs/jquery/1.4.2/jquery.min.js");
// $this->addScript("http://ajax.googleapis.com/ajax/libs/jqueryui/1.8.0/jquery-ui.min.js");
// $this->addScript("http://ajax.microsoft.com/ajax/jquery.validate/1.6/jquery.validate.pack.js");
$this->addStyleSheet($base_url."assets/css/mashine.css");
$this->addStyleSheet($base_url."assets/css/syntaxhighlighter/shCore.css");
$this->addStyleSheet($base_url."assets/css/syntaxhighlighter/shThemeRDark.css");

if ($user->groupId() > 0 && $user->groupId() <= 2) {
    $this->addStyleSheet($base_url."assets/css/mashine.user.css");
}
?>

<div><a name="up" id="up"></a></div>

<div id="wrapper">

<div id="sitename">
    <a href="index.php"><?php echo $app_name; ?></a>
</div>

<div id="topmenu">
<?php echo $renderer->renderPartial("menu", array("session"=>$session))."\n"; ?>
</div>

<?php
echo $renderer->renderPartial(
    "sysevents",
    array("events"=>$sysevents)
)."\n";
?>

<div id="content">
[nav type="breadcrumbs"]
<?php echo $this->body()."\n"; ?>
</div><!-- #content -->

<?php
    echo $renderer->renderPartial(
        "sidebar",
        array("content"=>$content, "session"=>$session)
    )."\n";
?>

<div id="push"></div>
</div><!-- #wrapper -->


<div id="footer">
<p>
    Powered by <?php echo nl2br(PHPFrame::version())."\n"; ?>
</p>
</div>

<script src="http://ajax.googleapis.com/ajax/libs/jqueryui/1.8.0/jquery-ui.min.js"></script>
<script src="http://ajax.microsoft.com/ajax/jquery.validate/1.6/jquery.validate.pack.js"></script>
<script>var base_url = '<?php echo $base_url; ?>';</script>
<script src="<?php echo $base_url; ?>assets/js/mashine.js"></script>
<?php if ($user->groupId() > 0 && $user->groupId() <= 2) : ?>
<script src="<?php echo $base_url; ?>assets/js/mashine.user.js"></script>
<?php endif; ?>
<script src="<?php echo $base_url; ?>assets/js/syntaxhighlighter/shCore.js"></script>
<script src="<?php echo $base_url; ?>assets/js/syntaxhighlighter/shAutoloader.js"></script>
<script>
jQuery(document).ready(function() {
    SyntaxHighlighter.autoloader(
      'js jscript javascript assets/js/syntaxhighlighter/shBrushJScript.js',
      'php assets/js/syntaxhighlighter/shBrushPhp.js',
      'css assets/js/syntaxhighlighter/shBrushCss.js',
      'bash shell assets/js/syntaxhighlighter/shBrushBash.js',
      'plain assets/js/syntaxhighlighter/shBrushPlain.js',
      'python assets/js/syntaxhighlighter/shBrushPython.js',
      'ruby assets/js/syntaxhighlighter/shBrushRuby.js',
      'sql assets/js/syntaxhighlighter/shBrushSql.js',
      'xml html assets/js/syntaxhighlighter/shBrushXml.js'
    );

    SyntaxHighlighter.all();
});
</script>
