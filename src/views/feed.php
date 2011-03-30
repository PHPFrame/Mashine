<header id="content-header">
  <h1><?php echo $content->title(); ?></h1>
  <?php echo $content->editLink($user); ?>
  <p class="feed-info">
    Feed URL: <a href="<?php echo $content->link(); ?>"><?php echo $content->link(); ?></a><br />
    <?php $description = $content->description(); ?>
    <?php echo ($description) ? "Description: ".$description : ""; ?>
    <?php $img = $helper->displayFeedImage($content); ?>
    <?php echo ($img) ? "Image: ".$img : ""; ?>
  </p>
</header>

<div id="content-body" class="feed">

<?php foreach ($content->items() as $item) : ?>
<article>

<header>
<h2 class="post-title">
  <a href="<?php echo $item["link"]; ?>">
    <?php echo $item["title"]; ?>
  </a>
</h2>
<p class="post-info">
  Posted by <?php echo $item["author"]; ?>
  on <time datetime="<?php echo date("Y-m-d\TH:i", strtotime($item["pub_date"])); ?>" pubdate><?php echo date("l jS F Y", strtotime($item["pub_date"])); ?><time>
</p>
</header>

<div class="post-excerpt">
<?php echo $item["description"]; ?>
</div>

</article>
<?php endforeach; ?>

</div><!-- #content-body -->
