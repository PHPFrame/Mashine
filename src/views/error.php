<?php
switch($error["code"]) {
case 400 :
  $title = "Bad Request";
  $body  = "<p>Something bad happened :-(</p>";
  break;
case 401 :
  $title = "Unauthorised";
  $body  = "<p>You don't seem to have permission to do this... make sure you";
  $body .= "<a href=\"user/login\">login</a> before trying again.</p>";
  break;
case 403 :
  $title = "Forbidden";
  $body  = "<p>Request not allowed on this server.</p>";
  break;
case 404 :
  $title = "Not Found";
  $body  = "<p>Shame on us :-( </p><p>It looks like we couldn't find the page you ";
  $body .= "where looking for...</p>";
  break;
default :
  $title = "Internal Server Error";
  $body  = "<p>Something bad happened :-(</p>";
  break;
}
?>

<header id="content-header">
  <h1><?php echo $title; ?></h1>
</header>

<div id="content-body" class="page">
  <?php echo $body; ?>
</div><!-- #content-body -->
