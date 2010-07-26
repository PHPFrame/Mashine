<?php echo $content->editLink($user); ?>

<div class="content_header_wrapper">
    <h1><?php echo $content->title(); ?></h1>
</div>

<div class="entry blog">

<p>
    Pages:
    <?php for($i=1; $i<=$posts->getPages(); $i++) : ?>
    <?php if ($i>1): ?>
    &nbsp;-&nbsp;
    <?php endif ?>
    <?php if ($i != $posts->getCurrentPage()): ?>
    <a href="<?php echo $content->slug()."?page=".$i; ?>"><?php echo $i ?></a>
    <?php else : ?>
    <?php echo $i ?>
    <?php endif ?>
    <?php endfor; ?>
</p>

<p>
    Total entries: <?php echo $posts->getTotal(); ?>
    (displaying <?php echo $posts->getLimit(); ?> per page)
</p>

<ul id="posts" class="posts">

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

</div><!-- #article -->
</li>
<?php endforeach; ?>
</ul>
<?php else : ?>
<div class="entry">
    <p>
        No posts found.
    </p>
</div><!-- #entry -->
<?php endif ?>

</div><!-- #entry -->
