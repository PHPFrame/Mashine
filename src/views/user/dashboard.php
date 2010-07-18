<div class="content_header_wrapper">
    <h1><?php echo $title; ?></h1>
</div>

<div class="entry">

<div class="dashboard-box-outer">
    <div class="dashboard-box-inner">
        <h3>News</h3>
        <div class="dashboard-box-content">
            <h4>
                <a href="#">
                    Brand new ImageBrowser 2.0 for Joomla 1.5.x released
                </a>
            </h4>
            <p>
                We are very happy to announce that we have just released a new
                version of the popular ImageBrowser component for Joomla!. This
                new version has been re-written from the ground up and puts in
                place a new architecture that will allow for more agile
                development...
            </p>
        </div>
    </div>
</div>

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

<div class="dashboard-box-outer">
    <div class="dashboard-box-inner">
        <h3>System status</h3>
        <div id="system_status" class="dashboard-box-content">

        </div>
    </div>
</div>

<div style="clear:both;"></div>

</div><!-- .entry -->

<div style="clear:both;"></div>

<script type="text/javascript" charset="utf-8">
jQuery(document).ready(function($) {
    var system_status = $('#system_status');
    system_status.html('Loading...');

    $.getJSON(base_url + 'status', function(data) {
        var html = '<ul>';

        for (var i in data) {
            html += '<li>';
            html += '<h4>' + data[i].subject + '</h4>';
            html += data[i].when + ' [' + data[i].type + ']';
            html += '<br />' + data[i].body;
            html += '</li>';
        }

        html += '</ul>';

        system_status.html(html);
    });

});
</script>
