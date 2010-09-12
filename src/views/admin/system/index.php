<div id="content-header">
    <h1><?php echo $title; ?></h1>
</div>

<div class="entry">

<p>
    <?php include "OS/Guess.php"; $os = new OS_Guess(); ?>
    <strong>OS</strong>: <?php echo $os->getSysname(); ?> <?php echo $os->getRelease(); ?> <?php echo $os->getCpu(); ?><br />
    <strong>Web server</strong>: <?php echo $_SERVER["SERVER_SOFTWARE"]?><br />
    <strong>PHPFrame version</strong>: <?php echo PHPFrame::RELEASE_VERSION; ?> <?php echo PHPFrame::RELEASE_STABILITY; ?><br />
    <strong>Mashine version</strong>: <?php echo $options["mashineplugin_version"]; ?><br />
    <strong>Host name</strong>: <?php echo $_SERVER["SERVER_NAME"]; ?><br />
    <strong>IP address</strong>: <?php echo $_SERVER["SERVER_ADDR"]; ?><br />
    <strong>Document root</strong>: <?php echo $_SERVER["DOCUMENT_ROOT"]; ?><br />
    <strong>Server admin</strong>: <?php echo $_SERVER["SERVER_ADMIN"]; ?>
</p>

</div><!-- .entry -->

<div style="clear:both;"></div>
