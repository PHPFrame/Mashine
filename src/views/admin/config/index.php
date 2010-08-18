<div class="content_header_wrapper">
    <h1><?php echo $title; ?></h1>
</div>

<div class="entry">

<pre>
<?php
// We inject a span in the [cms] section title to avoid it being processed by
// the CMS plugin as a short tag.
echo str_replace("[cms]", "[<span>cms</span>]", $config);
?>
</pre>

</div><!-- .entry -->

<div style="clear:both;"></div>
