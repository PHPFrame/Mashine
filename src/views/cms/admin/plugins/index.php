<div class="content_header_wrapper">
    <h1><?php echo $title; ?></h1>
</div>

<div class="entry">

<table>
    <thead>
        <tr>
            <th>Name:</th>
            <th>Summary:</th>
            <th>Author:</th>
            <th>Version:</th>
            <th>Enabled:</th>
            <th></th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($plugins as $plugin) : ?>
        <tr>
            <td><?php echo $plugin->name(); ?></td>
            <td><?php echo $plugin->summary(); ?></td>
            <td><?php echo $plugin->author(); ?></td>
            <td><?php echo $plugin->version(); ?></td>
            <td><?php echo $plugin->enabled(); ?></td>
            <td>
                <a href="admin/plugins/options?id=<?php echo $plugin->id(); ?>">
                    Options
                </a>
                 -
                <a href="#">Disable</a>
                 -
                <a href="#">Uninstall</a>
            </td>
        </tr>
        <?php endforeach; ?>
    </tbody>
</table>

</div><!-- .entry -->

<div style="clear:both;"></div>
