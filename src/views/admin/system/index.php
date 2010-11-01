<header id="content-header">
  <h1><?php echo $title; ?></h1>
</header>

<div id="content-body">
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

  <h3 id="php-upload-directives"><?php echo MediaLang::PHP_UPLOAD_DIRECTIVES; ?></h3>
  <ul>
    <li>memory_limit: <?php echo ini_get("memory_limit"); ?></li>
    <li>upload_max_filesize: <?php echo ini_get("upload_max_filesize"); ?></li>
    <li>post_max_size: <?php echo ini_get("post_max_size"); ?></li>
    <li>max_execution_time: <?php echo ini_get("max_execution_time"); ?></li>
    <li>max_input_time: <?php echo ini_get("max_input_time"); ?></li>
  </ul>
</div><!-- #content-body -->

