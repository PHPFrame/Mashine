<header id="content-header">
    <h1><?php echo $content->title(); ?></h1>
    <?php echo $content->editLink($user); ?>
</header>

<div id="content-body" class="archive">

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

<?php if (count($posts) > 0) : ?>
<?php foreach ($posts as $post) : ?>
<article>

<header>
<h2 class="post-title">
    <a href="<?php echo $post->slug(); ?>">
        <?php echo $post->title(); ?>
    </a>
</h2>
<p class="post-info">
    Posted by <?php echo $post->author(); ?>
    on <?php echo date("l jS F Y", strtotime($post->pubDate())); ?>
</p>
</header>

<div class="post-excerpt">
    <?php echo $post->excerpt(); ?>
</div>

<p class="post-readmore">
    <a href="<?php echo $post->slug(); ?>">[ read more... ]</a>
</p>

</article>
<?php endforeach; ?>

<?php else : ?>
<p>No posts found.</p>
<?php endif ?>

</div><!-- #content-body -->
