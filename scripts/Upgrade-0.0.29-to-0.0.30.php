<?php
class Upgrade_0_0_29_to_0_0_30
{
    private $_app, $_install_dir;

    public function __construct(PHPFrame_Application $app)
    {
        $this->_app = $app;
    }

    public function run()
    {
        $db = $this->_app->db();

        $sql = "DROP TABLE IF EXISTS `oauth_methods`";
        $db->query($sql);

        if ($db->isMySQL()) {
            $sql = "CREATE TABLE IF NOT EXISTS `oauth_methods` (
              `id` int(11) NOT NULL AUTO_INCREMENT,
              `method` varchar(100) NOT NULL,
              `oauth` enum('0','2','3') NOT NULL DEFAULT '0' COMMENT '0 = No, 2 = 2-legged, 3 = 3-legged',
              `cookie` enum('0','1') NOT NULL DEFAULT '0',
              PRIMARY KEY (`id`)
            )";
        } elseif ($db->isSQlite()) {
            $sql = "CREATE TABLE IF NOT EXISTS `oauth_methods` (
              `id` INTEGER PRIMARY KEY ASC,
              `method` varchar NOT NULL,
              `oauth` int NOT NULL DEFAULT '0',
              `cookie` int NOT NULL DEFAULT '0'
            )";
        }

        $db->query($sql);
    }
}
