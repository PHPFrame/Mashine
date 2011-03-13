<header id="content-header">
  <h1><?php echo $content->title(); ?></h1>
  <?php echo $content->editLink($user); ?>
  <p class="post-info">
    Posted by <?php echo $content->author(); ?>
    on <time datetime="<?php echo date("Y-m-d\TH:i", strtotime($content->pubDate())); ?>" pubdate><?php echo date("l jS F Y", strtotime($content->pubDate())); ?></time>
  </p>
</header>

<div id="content-body" class="post-body">
<?php echo $content->body(); ?>
</div><!-- #content-body -->

<footer>
<?php $footer = $hooks->doAction("post_footer", array($content)); ?>
<?php if (is_array($footer) && count($footer) > 0) echo implode("\n", $footer); ?>
</footer>
