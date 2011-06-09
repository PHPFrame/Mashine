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
$app_ver   = $app->config()->get("version");
$base_url  = $app->config()->get("base_url");
$request   = $app->request();
$content   = $request->param("_content_active");
$options   = $request->param("_options");
$mshn_ver  = $options["mashineplugin_version"];

// Add Javascript and CSS
$this->addScript("http://ajax.googleapis.com/ajax/libs/jquery/1.5.1/jquery.min.js");
$this->addScript($base_url."assets/js/modernizr-2.0-beta.min.js");

$this->addStyleSheet($base_url."assets/css/mashine--v".$mshn_ver.".css");
if ($user->groupId() > 0 && $user->groupId() <= 2) {
    $this->addStyleSheet($base_url."assets/css/mashine.user--v".$mshn_ver.".css");
}
$this->addStyleSheet($base_url."themes/default/css/styles--v".$app_ver.".css");
?>

<header>
<h1 id="sitename"><a href="<?php echo $base_url; ?>"><?php echo $app_name; ?></a></h1>
<nav id="topmenu">
<?php echo $renderer->renderPartial("menu", array("session"=>$session)); ?>
</nav><!-- #topmenu -->
<?php echo $renderer->renderPartial("sysevents", array("events"=>$sysevents)); ?>
</header>

<article id="content">
<?php if ($content instanceof Content && $content->id() > 1) : ?>
<nav id="breadcrumbs">[nav type="breadcrumbs"]</nav>
<?php endif; ?>
<?php echo $this->body()."\n"; ?>
</article><!-- #content -->

<?php
    echo $renderer->renderPartial(
        "sidebar",
        array("content"=>$content, "session"=>$session)
    );
?>

<div style="clear:left;"></div>

<footer id="footer">
  <small id="copyright">
    <p>Powered by Mashine | Theme by Lupo Montero</p>
  </small>
</footer>

<script>var base_url = '<?php echo $base_url; ?>';</script>
<script src="<?php echo $base_url."assets/js/mashine--v".$mshn_ver.".js"; ?>"></script>
<?php if ($user->groupId() > 0 && $user->groupId() <= 2) : ?>
<script src="<?php echo $base_url."assets/js/mashine.user--v".$mshn_ver.".js"; ?>"></script>
<?php endif; ?>
