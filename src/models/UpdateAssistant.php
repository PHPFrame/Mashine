<?php
/**
 * src/models/UpdateAssistant.php
 *
 * PHP version 5
 *
 * @category  PHPFrame_Applications
 * @package   Mashine
 * @author    Lupo Montero <lupo@e-noise.com>
 * @copyright 2010 E-NOISE.COM LIMITED
 * @license   http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @link      https://github.com/lupomontero/Mashine
 */

/**
 * This class is responsible for managing Mashine updates.
 *
 * @category PHPFrame_Applications
 * @package  Mashine
 * @author   Lupo Montero <lupo@e-noise.com>
 * @license  http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @link     https://github.com/lupomontero/Mashine
 * @since    1.0
 */
class UpdateAssistant
{
    private $_app, $_options;

    /**
     * Constructor.
     *
     * @param PHPFrame_Application $app Instance of application.
     *
     * @return void
     * @since  1.0
     */
    public function __construct(PHPFrame_Application $app)
    {
        $this->_app = $app;
        $this->_options = $app->request()->param("mashine_options");
    }

    /**
     * Check whether Mashine plugin is up to date.
     *
     * @return bool
     * @since  1.0
     */
    public function isUpToDate()
    {
        $installed_version      = $this->_options["mashineplugin_version"];
        $latest_release_version = $this->_fetchLatestReleaseVersion();

        return version_compare($installed_version, $latest_release_version, ">=");
    }

    /**
     * Upgrade Mashine.
     *
     * @return void
     * @since  1.0
     */
    public function upgrade()
    {
        if ($this->isUpToDate()) {
            $msg = "Nothing to upgrade! Mashine is already latest stable version.";
            throw new RuntimeException($msg);
        }

        $url           = $this->_app->config()->get("sources.preferred_mirror");
        $url          .= "/apps/Mashine/latest-release/?get=download";
        $download_tmp  = PHPFrame_Filesystem::getSystemTempDir();
        $download_tmp .= DS."Mashine".DS."download";

        // Make sure we can write in download directory
        PHPFrame_Filesystem::ensureWritableDir($download_tmp);

        $messages = array();
        $messages[] = "Downloading ".$url." to ".$download_tmp;

        // Create the http request
        $request  = new PHPFrame_HTTPRequest($url);

        ob_start();
        $response = $request->download($download_tmp);
        ob_end_clean();

        // If response is not OK we throw exception
        if ($response->getStatus() != 200) {
            $msg  = "Error downloading package. ";
            $msg .= "Reason: ".$response->getReasonPhrase();
            throw new RuntimeException($msg);
        }

        $file_name = preg_replace(
            "/(.*filename=([a-zA-Z0-9_\-\.]+).*)/",
            "$2",
            $response->getHeader("content-disposition")
        );

        $messages[] = "Package downloaded to ".$download_tmp.DS.$file_name;

        // Extract archive in install dir
        $archive = new Archive_Tar($download_tmp.DS.$file_name, "gz");
        $archive->extract($this->_app->getInstallDir());

        $messages[] = "Package extracted to ".$this->_app->getInstallDir();

        $new_version = $this->_fetchLatestReleaseVersion();
        $old_version = $this->_options["mashineplugin_version"];

        // Run post upgrade script if any
        $upgrade_script  = $this->_app->getInstallDir().DS."scripts".DS;
        $upgrade_script .= "Upgrade-".$old_version."-to-".$new_version.".php";
        if (is_file($upgrade_script)) {
            include $upgrade_script;

            $class_name  = "Upgrade_".str_replace(".", "_", $old_version);
            $class_name .= "_to_".str_replace(".", "_", $new_version);

            $upgrade_obj = new $class_name($this->_app);
            $upgrade_obj->run();

            $messages[] = "Upgrade script '".$upgrade_script."' run successfully";
        }

        // Update CMS verion in db file
        $this->_options["mashineplugin_version"] = $new_version;
        $messages[] = "Mashine version updated to ".$new_version;

        // Clear the app registry after the upgrade to make sure it is
        // refreshed in the next request
        PHPFrame_Filesystem::rm($this->_app->getTmpDir().DS."app.reg");

        return $messages;
    }

    /**
     * Check latest release version from preferred mirror.
     *
     * @return string
     * @since  1.0
     */
    private function _fetchLatestReleaseVersion()
    {
        $url  = $this->_app->config()->get("sources.preferred_mirror");
        $url .= "/apps/Mashine/latest-release/?get=version";

        $cache_dir = $this->_app->getTmpDir().DS."updates";
        PHPFrame_Filesystem::ensureWritableDir($cache_dir);

        $http_request = new PHPFrame_HTTPRequest($url);
        $http_request->cacheDir($cache_dir);
        $http_request->cacheTime(600);
        $http_response = $http_request->send();

        if ($http_response->getStatus() != 200) {
            $msg = "An error occurred when getting latest Mashine release number.";
            throw new RuntimeException($msg);
        }

        return $http_response->getBody();
    }
}
