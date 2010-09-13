<?php
class Upgrade_0_1_1_to_0_1_2
{
    private $_app, $_install_dir;

    public function __construct(PHPFrame_Application $app)
    {
        $this->_app = $app;
    }

    public function run()
    {
        $sql = "RENAME TABLE `oauth_methods` TO `api_methods`";
        $this->_app->db()->query($sql);

        // Delete old files
        $files = array(
            "src/models/oauth/OAuthMethodsMapper.php",
            "public/assets/js/mashine.admin.js",
            "public/assets/css/mashine.admin.css",
            "public/assets/css/syntaxhighlighter/page_white_code.png",
            "public/assets/css/syntaxhighlighter/page_white_copy.png",
            "public/assets/css/syntaxhighlighter/printer.png",
            "data/api_method_auth_info.sql"
        );

        foreach ($files as $file) {
            if (is_file($app->getInstallDir().DS.$file)) {
                PHPFrame_Filesystem::rm($app->getInstallDir().DS.$file);
            }
        }

        PHPFrame_Filesystem::rm($app->getInstallDir().DS."public/assets/js/mashine/", true);
    }
}
