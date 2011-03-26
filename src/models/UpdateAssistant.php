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
 * @link      http://github.com/E-NOISE/Mashine
 */

/**
 * This class is responsible for managing Mashine updates.
 *
 * @category PHPFrame_Applications
 * @package  Mashine
 * @author   Lupo Montero <lupo@e-noise.com>
 * @license  http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @link     http://github.com/E-NOISE/Mashine
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
        $this->_options = $app->request()->param("_options");
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

        $this->_checkFilePermissions();

        $new_version = $this->_fetchLatestReleaseVersion();
        $old_version = $this->_options["mashineplugin_version"];
        $url = $this->_getDistUrl();

        // get md5 checksum
        $request = new PHPFrame_HTTPRequest($url."/?get=hash");
        $response = $request->send();
        $checksum = $response->getBody();

        $install_dir = $this->_app->getInstallDir();
        $tmp_dir = $install_dir.DS."tmp";

        $messages = array();
        $messages[] = "Downloading ".$url."/?get=download to ".$tmp_dir;

        $request = new PHPFrame_HTTPRequest($url."/?get=download");
        ob_start();
        $response = $request->download($tmp_dir);
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

        // verify checksum
        if ($checksum != md5_file($tmp_dir.DS.$file_name)) {
            throw new RuntimeException("Invalid md5 checksum.");
        }

        $messages[] = "Package downloaded to ".$tmp_dir.DS.$file_name;

        // Extract archive in tmp dir
        $extract_dir = $tmp_dir.DS."Mashine-".$new_version;
        PHPFrame_Filesystem::ensureWritableDir($extract_dir);
        $archive = new Archive_Tar($tmp_dir.DS.$file_name, "gz");
        $archive->extract($extract_dir);

        // remove plugins xml file to avoid averwriting
        unlink($extract_dir.DS."etc".DS."plugins.xml");

        // copy files
        PHPFrame_Filesystem::cp($extract_dir.DS, $install_dir.DS, true);

        // delete tmp files
        PHPFrame_Filesystem::rm($extract_dir, true);
        PHPFrame_Filesystem::rm($tmp_dir.DS.$file_name);

        $messages[] = "Package extracted to ".$this->_app->getInstallDir();

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

        // Update CMS version in db and xml file
        $this->_options["mashineplugin_version"] = $new_version;

        $plugins = $this->_app->plugins();
        $mashine_plugin = $plugins->getInfo("MashinePlugin");
        $mashine_plugin->version($new_version);
        $plugins->mapper()->insert($mashine_plugin);

        $messages[] = "Mashine version updated to ".$new_version;

        // Clear the app registry after the upgrade to make sure it is
        // refreshed in the next request
        PHPFrame_Filesystem::rm($this->_app->getTmpDir().DS."app.reg");

        return $messages;
    }

    private function _getDistUrl()
    {
        $pref_state = $this->_app->config()->get("sources.preferred_state");
        $url  = $this->_app->config()->get("sources.preferred_mirror");
        $url .= "/apps/Mashine/latest-";

        if ($pref_state == "beta") {
            $url .= "build";
        } else {
            $url .= "release";
        }

        return $url;
    }

    /**
     * Check latest release version from preferred mirror.
     *
     * @return string
     * @since  1.0
     */
    private function _fetchLatestReleaseVersion()
    {
        $cache_dir = $this->_app->getTmpDir().DS."updates";
        PHPFrame_Filesystem::ensureWritableDir($cache_dir);

        $url = $this->_getDistUrl()."/?get=version";
        $http_request = new PHPFrame_HTTPRequest($url);
        $http_request->cacheDir($cache_dir);
        $http_request->cacheTime(600);
        $http_response = $http_request->send();

        if ($http_response->getStatus() != 200) {
            $msg = "An error occurred getting latest Mashine version.";
            throw new RuntimeException($msg);
        }

        return $http_response->getBody();
    }

    private function _checkFilePermissions()
    {
        $dir_it = new RecursiveDirectoryIterator($this->_app->getInstallDir());
        $it = new RecursiveIteratorIterator(
            $dir_it,
            RecursiveIteratorIterator::SELF_FIRST
        );

        foreach ($it as $file) {
            if (!preg_match("/^\./", $it->getSubPath())
                && !preg_match("/\.(git|svn)\//", $it->getSubPath())
                && !preg_match("/^\./", $file->getFilename())
                && !is_writable($file->getRealPath())
            ) {
                $msg  = "File permissions error. Please make sure that ";
                $msg .= "application files are writable before running the ";
                $msg .= "upgrade.";
                throw new RuntimeException($msg);
            }
        }
    }
}
