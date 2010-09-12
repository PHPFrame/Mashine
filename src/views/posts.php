<header id="content-header">
    <h1><?php echo $content->title(); ?></h1>
    <?php echo $content->editLink($user); ?>
</header>

<div id="content-body" class="blog">

<?php if (count($posts) > 0) : ?>
<?php foreach ($posts as $post) : ?>
<<<<<<< HEAD
<article<?php if ($post->status() == 0) echo " class=\"unpublished\""; ?>>
=======
<article class="<?php if ($post->status() == 0) echo "unpublished"; ?>">
>>>>>>> dd5358a6cd5f19350a5537e747e721ff4101370b

<header>
<?php if ($post->status() == 0) : ?>
<div style="float: right;">Unpublished</div>
<?php endif; ?>
<h2 class="post-title">
    <a href="<?php echo $post->slug(); ?>">
        <?php echo $post->title(); ?>
    </a>
</h2>
<<<<<<< HEAD
<p class="post-info">
    Posted by <a href="#"><?php echo $post->author(); ?></a>
    on <time datetime="<?php echo date("Y-m-d\TH:i", strtotime($post->pubDate())); ?>" pubdate><?php echo date("l jS F Y", strtotime($post->pubDate())); ?></time>
</p>
=======
>>>>>>> dd5358a6cd5f19350a5537e747e721ff4101370b
</header>

<div class="post-excerpt">
<?php echo $post->excerpt(); ?>
</div>

<<<<<<< HEAD
<p class="post-readmore">
    <a href="<?php echo $post->slug(); ?>">[ read more... ]</a>
</p>

<footer>
<!--
<p>Tags: <?php echo $post->keywords(); ?></p>
<p>Comments...</p>
-->
<p>
    Share:
    <a href="http://www.facebook.com/sharer.php?u=<?php echo urlencode($base_url.$post->slug()); ?>&t=<?php echo urlencode($post->title()); ?>">
        Facebook
    </a>
     |
    <a href="http://twitter.com/?status=<?php echo urlencode($post->title()); ?>:%20<?php echo urlencode($base_url.$post->slug()); ?>">
        Twitter
    </a>
     |
    <a href="http://www.delicious.com/save?jump=yes&url=<?php echo urlencode($base_url.$post->slug()); ?>&title=<?php echo urlencode($post->title()); ?>">
        Del.icio.us
    </a>
=======
<footer>
<p class="post-info">
    Posted by <?php echo $post->author(); ?>
    on <?php echo date("l jS F Y", strtotime($post->pubDate())); ?>
</p>
<p class="post-info-readmore">
    <a href="<?php echo $post->slug(); ?>">read more...</a>
>>>>>>> dd5358a6cd5f19350a5537e747e721ff4101370b
</p>
</footer>

</article>
<?php endforeach; ?>

<<<<<<< HEAD
</div><!-- #content-body -->

<nav id="pagination">
=======
<nav>
>>>>>>> dd5358a6cd5f19350a5537e747e721ff4101370b
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
<<<<<<< HEAD
</div><!-- #content-body -->
<?php endif ?>
=======
<?php endif ?>

</div><!-- #content-body -->
>>>>>>> dd5358a6cd5f19350a5537e747e721ff4101370b
