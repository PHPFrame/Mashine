<header id="content-header">
    <h1><?php echo $title; ?></h1>
</header>

<div id="content-body">

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
            <td><?php echo ($plugin->enabled()) ? "Yes" : "No"; ?></td>
            <td>
                <a href="admin/plugins/options?id=<?php echo $plugin->id(); ?>">
                    Options
                </a>
                <?php if ($plugin->id() > 1): ?>
                 -
                <?php if ($plugin->enabled()) : ?>
                    <a href="plugins/disable?id=<?php echo $plugin->id(); ?>">Disable</a>
                <?php else : ?>
                    <a href="plugins/enable?id=<?php echo $plugin->id(); ?>">Enable</a>
                <?php endif; ?>
                <!--
                 -
                <a href="plugins/uninstall?id=<?php echo $plugin->id(); ?>">Uninstall</a>
                -->
                <?php endif ?>
            </td>
        </tr>
        <?php endforeach; ?>
    </tbody>
</table>

</div><!-- #content-body -->
