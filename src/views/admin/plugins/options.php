<header id="content-header">
    <h1><?php echo $title; ?></h1>
</header>

<div id="content-body">

<?php if (is_callable(array($plugin, "displayOptionsForm"))) : ?>
<?php echo $plugin->displayOptionsForm(); ?>
<?php else : ?>
<p>This plugin does not have any options.</p>
<?php endif; ?>

</div><!-- #content-body -->
