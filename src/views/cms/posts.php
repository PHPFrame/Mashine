<?php echo $content->editLink($user); ?>

<div class="content_header_wrapper">
    <h1><?php echo $content->title(); ?></h1>
</div>

<div class="entry blog">

<ul id="posts" class="posts">

<?php if (count($posts) > 0) : ?>
<?php foreach ($posts as $post) : ?>
<li>
<div class="article <?php if ($post->status() == 0) echo "unpublished"; ?>">

<?php if ($post->status() == 0) : ?>
<div style="float: right;">Unpublished</div>
<?php endif; ?>

<h2 class="post-title">
    <a href="<?php echo $post->slug(); ?>">
        <?php echo $post->title(); ?>
    </a>
</h2>

<div class="post-excerpt">
    <?php echo $post->excerpt(); ?>
</div>

<span class="post-info">
    Posted by <?php echo $post->author(); ?>
    on <?php echo date("l jS F Y", strtotime($post->pubDate())); ?>
</span>
<span class="post-info-readmore">
    <a href="<?php echo $post->slug(); ?>">
        read more...
    </a>
</span>

<div style="clear:both;"></div>

</div><!-- #article -->
</li>
<?php endforeach; ?>
</ul>

<p>
    <a
        id="content-infinite-scrolling-trigger"
        class="parent_id-<?php echo $content->id(); ?>"
        href="<?php echo $content->slug()."?page=".($posts->getCurrentPage()+1)."&amp;limit=".$posts->getLimit(); ?>"
    >
        Next &rarr;

    </a>
</p>

<?php else : ?>
<div class="entry">
    <p>
        No posts found.
    </p>
</div><!-- #entry -->
<?php endif ?>

</div><!-- #entry -->
