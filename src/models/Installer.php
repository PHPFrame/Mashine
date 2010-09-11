<?php
/**
 * src/models/Installer.php
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
 * Installer class.
 *
 * @category PHPFrame_Applications
 * @package  Mashine
 * @author   Lupo Montero <lupo@e-noise.com>
 * @license  http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @link     https://github.com/lupomontero/Mashine
 * @since    1.0
 */
class Installer
{
    private $_app;

    /**
     * Constructor.
     *
     * @return void
     * @since  1.0
     */
    public function __construct(PHPFrame_Application $app)
    {
        $this->_app = $app;
    }

    /**
     * Get reference to application object.
     *
     * @return PHPFrame_Application
     * @since  1.0
     */
    public function app()
    {
        return $this->_app;
    }

    /**
     * Install database.
     *
     * @return void
     * @since  1.0
     */
    public function installDB()
    {
        $this->_installOauthTables();
        $this->_installGroupsTable();
        $this->_installContactsTable();
        $this->_installUsersTable();
        $this->_installCountriesTable();
        $this->_installNotificationsTable();
        $this->_installContentTable();
        $this->_populateDummyContent();
    }

    private function _installOauthTables()
    {
        $db  = $this->app()->db();
        $ort = new PHPFrame_ObjectRelationalToolbox();
        $ort->createTable($db, new OAuthClient(), "#__oauth_clients");

        $mapper = new OAuthClientsMapper($db);
        $oauth_client = new OAuthClient();
        $oauth_client->name("API Browser");
        $oauth_client->version("1.0");
        $oauth_client->vendor("Mashine Project");
        $mapper->insert($oauth_client);

        $ort->createTable($db, new OAuthToken(), "#__oauth_tokens");
        $ort->createTable($db, new OAuthACL(), "#__oauth_acl");

        $sql = "DROP TABLE IF EXISTS `api_methods`";
        $db->query($sql);

        if ($db->isSQLite()) {
            $sql = "CREATE TABLE `api_methods` (
                `id` INTEGER PRIMARY KEY ASC,
                `method` varchar NOT NULL,
                `oauth` int NOT NULL DEFAULT '0',
                `cookie` int NOT NULL DEFAULT '0'
            );";
        } else {
            $sql = "CREATE TABLE `api_methods` (
            `id` int NOT NULL PRIMARY KEY,
            `method` varchar NOT NULL,
            `oauth` int NOT NULL DEFAULT '0',
            `cookie` int NOT NULL DEFAULT '0'
            )";
        }

        $db->query($sql);

        $this->_processSqlScript("api_methods.sql");
    }

    private function _installGroupsTable()
    {
        $db  = $this->app()->db();
        $ort = new PHPFrame_ObjectRelationalToolbox();
        $ort->createTable($db, new PHPFrame_Group(), "#__groups");

        $mapper = new GroupsMapper($db);

        $group = new PHPFrame_Group();
        $group->name("wheel");
        $group->owner(1);
        $group->group(1);
        $group->perms(444);
        $mapper->insert($group);

        $group = new PHPFrame_Group();
        $group->name("staff");
        $group->owner(1);
        $group->group(1);
        $group->perms(444);
        $mapper->insert($group);

        $group = new PHPFrame_Group();
        $group->name("registered");
        $group->owner(1);
        $group->group(1);
        $group->perms(444);
        $mapper->insert($group);
    }

    private function _installUsersTable()
    {
        $db  = $this->app()->db();
        $ort = new PHPFrame_ObjectRelationalToolbox();
        $ort->createTable($db, new User(), "#__users");

        $mapper = new UsersMapper($db);

        $user = new User();
        $user->groupId(1);
        $user->email("root@example.com");

        // Create incrypted password and store encrypted string with salt
        // appended after a ":".
        $salt      = $this->app()->crypt()->genRandomPassword(32);
        $encrypted = $this->app()->crypt()->encryptPassword("Passw0rd", $salt);
        $user->password($encrypted.":".$salt);

        $user->status("active");
        $user->owner(1);
        $user->group(1);
        $user->perms(440);
        $mapper->insert($user);
    }

    private function _installContactsTable()
    {
        $db  = $this->app()->db();
        $ort = new PHPFrame_ObjectRelationalToolbox();
        $ort->createTable($db, new Contact(), "#__contacts");

        $mapper = new ContactsMapper($db);

        $contact = new Contact();
        $contact->firstName("Root");
        $contact->lastName("User");
        $contact->address1("Some Street");
        $contact->address2("");
        $contact->city("Some city");
        $contact->postCode("000000");
        $contact->county("Some county");
        $contact->country("GB");
        $contact->phone("0123456789");
        $contact->email("root@example.com");
        $contact->owner(1);
        $contact->group(1);
        $contact->perms(440);
        $mapper->insert($contact);
    }

    private function _installCountriesTable()
    {
        $db = $this->app()->db();

        $db->query("CREATE TABLE IF NOT EXISTS countries (
          iso CHAR(2) NOT NULL PRIMARY KEY,
          name VARCHAR(80) NOT NULL,
          printable_name VARCHAR(80) NOT NULL,
          iso3 CHAR(3),
          numcode SMALLINT
        )");

        $this->_processSqlScript("iso_country_list.sql");
        // $install_dir = $this->app()->getInstallDir();
        // $sql_file    = $install_dir.DS."data".DS."iso_country_list.sql";
        // $sql_file    = new SplFileObject($sql_file);
        // foreach ($sql_file as $line) {
        //     if ($line) {
        //         $db->query($line);
        //     }
        // }
    }

    private function _processSqlScript($fname)
    {
        $install_dir = $this->app()->getInstallDir();
        $sql_file    = $install_dir.DS."data".DS.$fname;
        $sql_file    = new SplFileObject($sql_file);
        foreach ($sql_file as $line) {
            if ($line) {
                $this->app()->db()->query($line);
            }
        }
    }

    private function _installNotificationsTable()
    {
        $db  = $this->app()->db();
        $ort = new PHPFrame_ObjectRelationalToolbox();
        $ort->createTable($db, new Notification(), "#__notifications");
    }

    private function _installContentTable()
    {
        $db = $this->app()->db();

        if ($db->isSQLite()) {
            $sql = "CREATE TABLE `#__content` (
            `parent_id` int NOT NULL DEFAULT '0',
            `slug` varchar NOT NULL,
            `title` varchar NOT NULL,
            `short_title` varchar,
            `pub_date` datetime,
            `status` tinyint,
            `robots_index` tinyint,
            `robots_follow` tinyint,
            `type` varchar NOT NULL,
            `id` INTEGER PRIMARY KEY ASC,
            `ctime` int,
            `mtime` int,
            `owner` int NOT NULL DEFAULT '0',
            `group` int NOT NULL DEFAULT '0',
            `perms` int NOT NULL DEFAULT '664'
            )";
        } else {
            $sql = "CREATE TABLE `#__content` (
            `parent_id` int NOT NULL DEFAULT '0',
            `slug` varchar(255) NOT NULL,
            `title` varchar(255) NOT NULL,
            `short_title` varchar(50),
            `pub_date` datetime,
            `status` tinyint,
            `robots_index` tinyint,
            `robots_follow` tinyint,
            `type` varchar(100) NOT NULL,
            `id` int NOT NULL AUTO_INCREMENT PRIMARY KEY,
            `ctime` int,
            `mtime` int,
            `owner` int NOT NULL DEFAULT '0',
            `group` int NOT NULL DEFAULT '0',
            `perms` int NOT NULL DEFAULT '664'
            )";
        }

        $db->query($sql);

        if ($db->isSQLite()) {
            $sql = "CREATE TABLE `#__content_data` (
            `content_id` INTEGER PRIMARY KEY ASC,
            `description` text,
            `keywords` text,
            `body` text,
            `params` text
            )";
        } else {
            $sql = "CREATE TABLE `#__content_data` (
            `content_id` int NOT NULL PRIMARY KEY,
            `description` text,
            `keywords` text,
            `body` text,
            `params` text
            )";
        }

        $db->query($sql);

        $mapper = new ContentMapper($db, $this->app()->getTmpDir().DS."cms");

        $content = new PageContent();
        $content->parentId(0);
        $content->title("Home");
        $content->slug("home");
        $content->status(1);
        $content->description("This is the home page!");
        $content->keywords("home, page");
        $content->body("Here we should show some posts using a short tag...");
        $content->owner(1);
        $content->group(2);
        $content->perms(664);
        $mapper->insert($content);

        $content = new MVCContent();
        $content->parentId(1);
        $content->title("Log in");
        $content->slug("user/login");
        $content->status(1);
        $content->pubDate("1970-01-01 00:01:00");
        $content->description(null);
        $content->keywords(null);
        $content->param("controller", "user");
        $content->param("action", "login");
        $content->owner(1);
        $content->group(1);
        $content->perms(644);
        $mapper->insert($content);

        $content = new MVCContent();
        $content->parentId(1);
        $content->title("Log out");
        $content->slug("user/logout");
        $content->status(1);
        $content->pubDate("1970-01-01 00:00:00");
        $content->description(null);
        $content->keywords(null);
        $content->param("controller", "user");
        $content->param("action", "logout");
        $content->owner(1);
        $content->group(1);
        $content->perms(644);
        $mapper->insert($content);

        $content = new MVCContent();
        $content->parentId(1);
        $content->title("Sign up");
        $content->slug("user/signup");
        $content->status(1);
        $content->pubDate("1970-01-01 00:00:30");
        $content->description(null);
        $content->keywords(null);
        $content->param("controller", "user");
        $content->param("action", "signup");
        $content->owner(1);
        $content->group(1);
        $content->perms(644);
        $mapper->insert($content);

        $dashboard = new MVCContent();
        $dashboard->parentId(1);
        $dashboard->title("Dashboard");
        $dashboard->slug("dashboard");
        $dashboard->status(1);
        $dashboard->pubDate("1970-01-01 00:01:00");
        $dashboard->description("Dashboard ...");
        $dashboard->keywords(null);
        $dashboard->param("controller", "user");
        $dashboard->param("action", "index");
        $dashboard->owner(1);
        $dashboard->group(3);
        $dashboard->perms(440);
        $mapper->insert($dashboard);

        $user_detail = new MVCContent();
        $user_detail->parentId($dashboard->id());
        $user_detail->title("User profile");
        $user_detail->slug("profile");
        $user_detail->status(1);
        $user_detail->pubDate("1970-01-01 00:04:00");
        $user_detail->description("User profile...");
        $user_detail->keywords(null);
        $user_detail->param("controller", "user");
        $user_detail->param("action", "form");
        $user_detail->owner(1);
        $user_detail->group(3);
        $user_detail->perms(440);
        $mapper->insert($user_detail);

        $content = new MVCContent();
        $content->parentId($user_detail->id());
        $content->title("Add contact");
        $content->slug("user/addcontact");
        $content->status(1);
        $content->description("Add contact...");
        $content->keywords(null);
        $content->param("controller", "user");
        $content->param("action", "contactform");
        $content->owner(1);
        $content->group(3);
        $content->perms(440);
        $mapper->insert($content);

        $content = new MVCContent();
        $content->parentId($user_detail->id());
        $content->title("Modify contact");
        $content->slug("user/editcontact");
        $content->status(1);
        $content->description("Modify contact...");
        $content->keywords(null);
        $content->param("controller", "user");
        $content->param("action", "contactform");
        $content->owner(1);
        $content->group(3);
        $content->perms(440);
        $mapper->insert($content);

        $content_manage = new MVCContent();
        $content_manage->parentId($dashboard->id());
        $content_manage->title("Manage content");
        $content_manage->slug("admin/content");
        $content_manage->status(1);
        $content_manage->pubDate("1970-01-01 00:03:30");
        $content_manage->description(null);
        $content_manage->keywords(null);
        $content_manage->param("controller", "content");
        $content_manage->param("action", "manage");
        $content_manage->owner(1);
        $content_manage->group(1);
        $content_manage->perms(440);
        $mapper->insert($content_manage);

        $content = new MVCContent();
        $content->parentId($content_manage->id());
        $content->title("Content form");
        $content->slug("admin/content/form");
        $content->status(1);
        $content->description(null);
        $content->keywords(null);
        $content->param("controller", "content");
        $content->param("action", "form");
        $content->owner(1);
        $content->group(1);
        $content->perms(440);
        $mapper->insert($content);

        $users_manage = new MVCContent();
        $users_manage->parentId($dashboard->id());
        $users_manage->title("Manage users");
        $users_manage->slug("admin/user");
        $users_manage->status(1);
        $users_manage->pubDate("1970-01-01 00:03:00");
        $users_manage->description(null);
        $users_manage->keywords(null);
        $users_manage->param("controller", "user");
        $users_manage->param("action", "manage");
        $users_manage->owner(1);
        $users_manage->group(2);
        $users_manage->perms(440);
        $mapper->insert($users_manage);

        $content = new MVCContent();
        $content->parentId($users_manage->id());
        $content->title("User form");
        $content->slug("admin/user/form");
        $content->status(1);
        $content->description(null);
        $content->keywords(null);
        $content->param("controller", "user");
        $content->param("action", "form");
        $content->owner(1);
        $content->group(2);
        $content->perms(440);
        $mapper->insert($content);

        $plugins = new MVCContent();
        $plugins->parentId($dashboard->id());
        $plugins->title("Plugins");
        $plugins->slug("admin/plugins");
        $plugins->status(1);
        $plugins->pubDate("1970-01-01 00:02:30");
        $plugins->description(null);
        $plugins->keywords(null);
        $plugins->param("controller", "plugins");
        $plugins->param("action", "index");
        $plugins->owner(1);
        $plugins->group(1);
        $plugins->perms(440);
        $mapper->insert($plugins);

        $content = new MVCContent();
        $content->parentId($plugins->id());
        $content->title("Plugin Options");
        $content->slug("admin/plugins/options");
        $content->status(1);
        $content->description(null);
        $content->keywords(null);
        $content->param("controller", "plugins");
        $content->param("action", "options");
        $content->owner(1);
        $content->group(1);
        $content->perms(440);
        $mapper->insert($content);

        $content = new MVCContent();
        $content->parentId($dashboard->id());
        $content->title("Config");
        $content->slug("admin/config");
        $content->status(1);
        $content->pubDate("1970-01-01 00:02:00");
        $content->description(null);
        $content->keywords(null);
        $content->param("controller", "system");
        $content->param("action", "sysconfig");
        $content->owner(1);
        $content->group(1);
        $content->perms(440);
        $mapper->insert($content);

        $content = new MVCContent();
        $content->parentId($dashboard->id());
        $content->title("System info");
        $content->slug("admin/system");
        $content->status(1);
        $content->pubDate("1970-01-01 00:01:30");
        $content->description(null);
        $content->keywords(null);
        $content->param("controller", "system");
        $content->param("action", "index");
        $content->owner(1);
        $content->group(1);
        $content->perms(440);
        $mapper->insert($content);

        $content = new MVCContent();
        $content->parentId($dashboard->id());
        $content->title("REST API");
        $content->slug("admin/api");
        $content->status(1);
        $content->pubDate("1970-01-01 00:01:00");
        $content->description(null);
        $content->keywords(null);
        $content->param("controller", "system");
        $content->param("action", "api");
        $content->owner(1);
        $content->group(1);
        $content->perms(440);
        $mapper->insert($content);

        $content = new MVCContent();
        $content->parentId($dashboard->id());
        $content->title("Upgrade");
        $content->slug("admin/upgrade");
        $content->status(1);
        $content->pubDate("1970-01-01 00:00:30");
        $content->description(null);
        $content->keywords(null);
        $content->param("controller", "system");
        $content->param("action", "upgrade");
        $content->owner(1);
        $content->group(1);
        $content->perms(440);
        $mapper->insert($content);

        $content = new MVCContent();
        $content->parentId($dashboard->id());
        $content->title("Backup");
        $content->slug("admin/backup");
        $content->status(1);
        $content->pubDate("1970-01-01 00:00:00");
        $content->description(null);
        $content->keywords(null);
        $content->param("controller", "system");
        $content->param("action", "backup");
        $content->owner(1);
        $content->group(1);
        $content->perms(440);
        $mapper->insert($content);

        $content = new PageContent();
        $content->parentId(1);
        $content->title("Sitemap");
        $content->slug("sitemap");
        $content->status(1);
        $content->pubDate("1970-01-01 00:00:00");
        $content->description("This is the site map");
        $content->keywords("sitemap");
        $content->param("view", "sitemap");
        $content->owner(1);
        $content->group(2);
        $content->perms(444);
        $mapper->insert($content);
    }

    private function _populateDummyContent()
    {
        $db = $this->app()->db();
        $mapper = new ContentMapper($db, $this->app()->getTmpDir().DS."cms");

        $blog = new PostsCollectionContent();
        $blog->parentId(1);
        $blog->title("A really cool blog...");
        $blog->shortTitle("Blog");
        $blog->slug("blog");
        $blog->status(1);
        $blog->description("A nice blog...");
        $blog->keywords("");
        $blog->body("This is my personal blog, where I...");
        $blog->param("posts_per_page", 3);
        $blog->owner(1);
        $blog->group(2);
        $blog->perms(664);
        $mapper->insert($blog);

        for ($i=0; $i<40; $i++) {

        $content = new PostContent();
        $content->parentId($blog->id());
        $content->title("Lorem ipsum ".$i);
        $content->slug("lorem-ipsum-".$i);
        $content->status(1);
        $content->description("");
        $content->keywords("");
        $content->body('<p><strong>Pellentesque habitant morbi tristique</strong> senectus et netus et malesuada fames ac turpis egestas. Vestibulum tortor quam, feugiat vitae, ultricies eget, tempor sit amet, ante. Donec eu libero sit amet quam egestas semper. <em>Aenean ultricies mi vitae est.</em> Mauris placerat eleifend leo. Quisque sit amet est et sapien ullamcorper pharetra. Vestibulum erat wisi, condimentum sed, <code>commodo vitae</code>, ornare sit amet, wisi. Aenean fermentum, elit eget tincidunt condimentum, eros ipsum rutrum orci, sagittis tempus lacus enim ac dui. <a href="#">Donec non enim</a> in turpis pulvinar facilisis. Ut felis.</p>
        <!-- More -->
        <h4>Header Level 4</h4>

        <ol>
           <li>Lorem ipsum dolor sit amet, consectetuer adipiscing elit.</li>
           <li>Aliquam tincidunt mauris eu risus.</li>
        </ol>

        <blockquote><p>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Vivamus magna. Cras in mi at felis aliquet congue. Ut a est eget ligula molestie gravida. Curabitur massa. Donec eleifend, libero at sagittis mollis, tellus est malesuada tellus, at luctus turpis elit sit amet quam. Vivamus pretium ornare est.</p></blockquote>

        <h3>Header Level 3</h3>

        <ul>
           <li>Lorem ipsum dolor sit amet, consectetuer adipiscing elit.</li>
           <li>Aliquam tincidunt mauris eu risus.</li>
        </ul>

        <pre><code>
        #header h1 a {
            display: block;
            width: 300px;
            height: 80px;
        }
        </code></pre>');
        $content->owner(1);
        $content->group(2);
        $content->perms(664);
        $mapper->insert($content);

        $content = new PostContent();
        $content->parentId($blog->id());
        $content->title("Another post ".$i);
        $content->slug("another-post-".$i);
        $content->status(1);
        $content->description("");
        $content->keywords("");
        $content->body('<p><strong>Pellentesque habitant morbi tristique</strong> senectus et netus et malesuada fames ac turpis egestas. Vestibulum tortor quam, feugiat vitae, ultricies eget, tempor sit amet, ante. Donec eu libero sit amet quam egestas semper. <em>Aenean ultricies mi vitae est.</em> Mauris placerat eleifend leo. Quisque sit amet est et sapien ullamcorper pharetra. Vestibulum erat wisi, condimentum sed, <code>commodo vitae</code>, ornare sit amet, wisi. Aenean fermentum, elit eget tincidunt condimentum, eros ipsum rutrum orci, sagittis tempus lacus enim ac dui. <a href="#">Donec non enim</a> in turpis pulvinar facilisis. Ut felis.</p>
        <!-- More -->
        <h4>Header Level 4</h4>

        <ol>
           <li>Lorem ipsum dolor sit amet, consectetuer adipiscing elit.</li>
           <li>Aliquam tincidunt mauris eu risus.</li>
        </ol>

        <blockquote><p>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Vivamus magna. Cras in mi at felis aliquet congue. Ut a est eget ligula molestie gravida. Curabitur massa. Donec eleifend, libero at sagittis mollis, tellus est malesuada tellus, at luctus turpis elit sit amet quam. Vivamus pretium ornare est.</p></blockquote>

        <h3>Header Level 3</h3>

        <ul>
           <li>Lorem ipsum dolor sit amet, consectetuer adipiscing elit.</li>
           <li>Aliquam tincidunt mauris eu risus.</li>
        </ul>

        <pre><code>
        #header h1 a {
            display: block;
            width: 300px;
            height: 80px;
        }
        </code></pre>');
        $content->owner(1);
        $content->group(2);
        $content->perms(664);
        $mapper->insert($content);

        }

        $content = new FeedContent();
        $content->parentId(1);
        $content->title("Mashine on GitHub");
        $content->slug("mashine-on-github");
        $content->status(1);
        $content->pubDate("2000-01-01 00:01:00");
        $content->param("feed_url", "http://github.com/E-NOISE/Mashine/commits/master.atom");
        $content->param("cache_time", 600);
        $content->owner(1);
        $content->group(1);
        $content->perms(644);
        $mapper->insert($content);

        $content = new PageContent();
        $content->parentId(1);
        $content->title("About");
        $content->slug("about");
        $content->status(1);
        $content->pubDate("2000-01-01 00:00:00");
        $content->description("An example about page...");
        $content->keywords("");
        $content->body('<p><strong>Pellentesque habitant morbi tristique</strong> senectus et netus et malesuada fames ac turpis egestas. Vestibulum tortor quam, feugiat vitae, ultricies eget, tempor sit amet, ante. Donec eu libero sit amet quam egestas semper. <em>Aenean ultricies mi vitae est.</em> Mauris placerat eleifend leo. Quisque sit amet est et sapien ullamcorper pharetra. Vestibulum erat wisi, condimentum sed, <code>commodo vitae</code>, ornare sit amet, wisi. Aenean fermentum, elit eget tincidunt condimentum, eros ipsum rutrum orci, sagittis tempus lacus enim ac dui. <a href="#">Donec non enim</a> in turpis pulvinar facilisis. Ut felis.</p>

        <h4>Header Level 4</h4>

        <ol>
           <li>Lorem ipsum dolor sit amet, consectetuer adipiscing elit.</li>
           <li>Aliquam tincidunt mauris eu risus.</li>
        </ol>

        <blockquote><p>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Vivamus magna. Cras in mi at felis aliquet congue. Ut a est eget ligula molestie gravida. Curabitur massa. Donec eleifend, libero at sagittis mollis, tellus est malesuada tellus, at luctus turpis elit sit amet quam. Vivamus pretium ornare est.</p></blockquote>

        <h5>Header Level 5</h5>

        <ul>
           <li>Lorem ipsum dolor sit amet, consectetuer adipiscing elit.</li>
           <li>Aliquam tincidunt mauris eu risus.</li>
        </ul>

        <pre><code>
        #header h1 a {
            display: block;
            width: 300px;
            height: 80px;
        }
        </code></pre>');
        $content->owner(1);
        $content->group(2);
        $content->perms(664);
        $mapper->insert($content);
    }
}
