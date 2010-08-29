<?php if ($content instanceof Content) : ?>
<div class="sidebar">
<?php if ($session->isAuth()) : ?>
[nav type="parent" show_root="false" depth="1" exclude="admin/upgrade"]
<?php else : ?>
[nav type="parent" show_root="false" depth="1"]
<?php endif; ?>
</div>
<?php endif; ?>
