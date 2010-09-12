<header id="content-header">
    <h1><?php echo $content->title(); ?></h1>
    <?php echo $content->editLink($user); ?>
</header>

<div id="content-body" class="post-body">
<?php echo $content->body(); ?>
</div><!-- #content-body -->
