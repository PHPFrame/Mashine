<div class="content_header_wrapper">
    <h1><?php echo $title; ?></h1>
</div>

<div class="entry">

<h2>Client Apps</h2>

<?php if (count($clients) > 0) : ?>
<ul>
<?php foreach ($clients as $client) : ?>
    <li>
        <p>
            <?php echo $client->name(); ?> <?php echo $client->version(); ?>
            (<?php echo $client->vendor(); ?>)
            <br />
            Status: <?php echo $client->status(); ?><br />
            API Key: <?php echo $client->key(); ?><br />
            API Secret: <?php echo $client->secret(); ?>
        </p>
<?php endforeach; ?>
</ul>
<?php else : ?>
<p>
    No client apps registered with system.
</p>
<?php endif; ?>

</div><!-- .entry -->

<div style="clear:both;"></div>
