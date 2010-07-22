<?php if ($session->isAuth()) : ?>
[cms:depth=1&show_root_as_child=true&exclude=sitemap,user/login,user/signup]
<?php else : ?>
[cms:depth=1&show_root_as_child=true&exclude=sitemap,user/logout]
<?php endif; ?>
