<?php echo $content->editLink($user); ?>

<div class="content_header_wrapper">
    <h1><?php echo $content->title(); ?></h1>
</div>

<div class="entry blog">
    
<ul class="posts">
    

<?php if (count($posts) > 0) : ?>
<?php foreach ($posts as $post) : ?>
<li>
<div class="article">

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

</div><!-- #entry -->
</li>
<?php endforeach; ?>
<?php else : ?>
<div class="entry">
    <p>
        No posts found.
    </p>
</div><!-- #entry -->
<?php endif ?>
</ul>

</div><!-- #entry -->
