<header id="content-header">
    <h1><?php echo $content->title(); ?></h1>
    <?php echo $content->editLink($user); ?>
</header>

<div id="content-body" class="blog">

<?php if (count($posts) > 0) : ?>
<?php foreach ($posts as $post) : ?>
<article class="<?php if ($post->status() == 0) echo "unpublished"; ?>">

<header>
<?php if ($post->status() == 0) : ?>
<div style="float: right;">Unpublished</div>
<?php endif; ?>
<h2 class="post-title">
    <a href="<?php echo $post->slug(); ?>">
        <?php echo $post->title(); ?>
    </a>
</h2>
</header>

<div class="post-excerpt">
<?php echo $post->excerpt(); ?>
</div>

<footer>
<p class="post-info">
    Posted by <?php echo $post->author(); ?>
    on <?php echo date("l jS F Y", strtotime($post->pubDate())); ?>
</p>
<p class="post-info-readmore">
    <a href="<?php echo $post->slug(); ?>">read more...</a>
</p>
</footer>

</article>
<?php endforeach; ?>

<nav>
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

<?php else : ?>
<p>No posts found.</p>
<?php endif ?>

</div><!-- #content-body -->
