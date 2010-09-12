<<<<<<< HEAD
<header id="content-header">
=======
<div id="content-header">
>>>>>>> dd5358a6cd5f19350a5537e747e721ff4101370b
    <h1><?php echo $title; ?></h1>
</header>

<div id="content-body">

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

</div><!-- #content-body -->
