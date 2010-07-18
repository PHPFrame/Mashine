<?php if ($content instanceof Content) : ?>
<?php if ($content->id() == 9 || $content->parentId() == 9) : ?>
<div class="sidebar">
[cms:type=parent&show_root_as_child=true&depth=1&exclude=admin/upgrade]
</div>
<?php endif; ?>
<?php endif; ?>
