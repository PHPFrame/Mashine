<div id="content-header">
    <h1><?php echo $title; ?></h1>
</div>

<div class="entry">

<?php if (is_callable(array($plugin, "displayOptionsForm"))) : ?>
<?php echo $plugin->displayOptionsForm(); ?>
<?php else : ?>
<p>
    This plugin does not have any options.
</p>
<?php endif; ?>

</div><!-- .entry -->

<div style="clear:both;"></div>
