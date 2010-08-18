<?php echo $content->editLink($user); ?>

<h1><?php echo $content->title(); ?></h1>

<p class="feed-info">
    Feed URL: <a href="<?php echo $content->link(); ?>"><?php echo $content->link(); ?></a><br />
    <?php $description = $content->description(); ?>
    <?php echo ($description) ? "Description: ".$description : ""; ?>
    <?php $img = $helper->displayFeedImage($content); ?>
    <?php echo ($img) ? "Image: ".$img : ""; ?>
</p>

<?php foreach ($content->items() as $item) : ?>
<div class="entry">

<h2 class="post-title">
    <a href="<?php echo $item["link"]; ?>">
        <?php echo $item["title"]; ?>
    </a>
</h2>

<p class="post-info">
    Posted by <?php echo $item["author"]; ?>
    on <?php echo date("l jS F Y", strtotime($item["pub_date"])); ?>
</p>

<div class="post-excerpt">
    <?php echo $item["description"]; ?>
</div>

</div><!-- #entry -->
<?php endforeach; ?>
