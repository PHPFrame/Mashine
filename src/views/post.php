<header id="content-header">
  <h1><?php echo $content->title(); ?></h1>
  <?php echo $content->editLink($user); ?>
</header>

<div id="content-body" class="post-body">
<?php echo $content->body(); ?>
</div><!-- #content-body -->

<footer>
<?php $footer = $hooks->doAction("post_footer", array($content)); ?>
<?php if (is_array($footer) && count($footer) > 0) echo implode("\n", $footer); ?>
</footer>

