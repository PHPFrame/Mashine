<div class="content_header_wrapper">
    <h1><?php echo $title; ?></h1>
</div>

<div class="entry">

<?php foreach ($dashboard_boxes as $box) : ?>
<div class="dashboard-box-outer">
    <div class="dashboard-box-inner">
        <h3><?php echo $box["title"]; ?></h3>
        <div class="dashboard-box-content">
            <?php echo $box["body"]; ?>
        </div>
    </div>
</div>
<?php endforeach; ?>

<div style="clear:both;"></div>

</div><!-- .entry -->

<div style="clear:both;"></div>
