<?php if ($session->isAuth()) : ?>
[cms:depth=1&show_root_as_child=true&exclude=user/login,user/signup]
<a href="user/logout">Logout</a>
<?php else : ?>
[cms:depth=1&show_root_as_child=true]
<?php endif; ?>
