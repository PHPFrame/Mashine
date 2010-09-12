<?php if ($content instanceof Content) : ?>
<aside class="sidebar">
<?php if ($session->isAuth()) : ?>
[nav type="parent" show_root="false" depth="1" exclude="admin/upgrade"]
<?php else : ?>
[nav type="parent" show_root="false" depth="1"]
<?php endif; ?>
</aside>
<?php endif; ?>
