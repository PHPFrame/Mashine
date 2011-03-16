<header id="content-header">
  <h1><?php echo $content->title(); ?></h1>
  <?php echo $content->editLink($user); ?>
</header>

<div id="content-body" class="blog">

<?php if (count($posts) > 0) : ?>
<?php foreach ($posts as $post) : ?>
<article<?php if ($post->status() == 0) echo " class=\"unpublished\""; ?>>

<header>
<?php if ($post->status() == 0) : ?>
<div style="float: right;">Unpublished</div>
<?php endif; ?>
<h2 class="post-title">
  <a href="<?php echo $post->slug(); ?>">
    <?php echo $post->title(); ?>
  </a>
</h2>
<p class="post-info">
  Posted by <?php echo $post->author(); ?>
  on <time datetime="<?php echo date("Y-m-d\TH:i", strtotime($post->pubDate())); ?>" pubdate><?php echo date("l jS F Y", strtotime($post->pubDate())); ?></time>
</p>
</header>

<div class="post-excerpt">
<?php echo $post->excerpt(); ?>
</div>

<p class="post-readmore">
  <a href="<?php echo $post->slug(); ?>">[ read more... ]</a>
</p>

<footer>
<?php
$footer = $hooks->doAction("posts_footer", array($post));
if (is_array($footer) && count($footer) > 0) {
  echo "<p>".implode("\n", $footer)."</p>";
}
?>
</footer>

</article>
<?php endforeach; ?>

</div><!-- #content-body -->

<?php if (count($posts) < $posts->getTotal()) : ?>
<nav id="pagination">
<p>
  <a
    id="content-infinite-scrolling-trigger"
    class="parent_id-<?php echo $content->id(); ?>"
    href="<?php echo $content->slug()."?page=".($posts->getCurrentPage()+1)."&amp;limit=".$posts->getLimit(); ?>"
  >
    Next &rarr;
  </a>
</p>
</nav>

<script>
jQuery(document).ready(function() {
	Mashine.infiniteScrolling('#content-infinite-scrolling-trigger');
});
</script>
<?php endif; ?>

<?php else : ?>
<p>No posts found.</p>
</div><!-- #content-body -->
<?php endif; ?>
