<?php
if ($_SERVER["REQUEST_METHOD"] == "POST") {
  header("Content-Type: application/json");
  try {
    install();
    echo json_encode(array("ok"=>true));
  } catch (Exception $e) {
    echo json_encode(array("error"=>$e->getMessage()));
  }
  exit;
}

function install()
{
  function execCmd($cmd)
  {
    exec($cmd, $out, $ret_val);

    if ($ret_val > 0) {
      throw new Exception("Command ".$cmd." failed. Output: ".implode("\n", $out));
    }
  }

  $install_dir   = str_replace("/scripts", "", dirname(__FILE__));
  $ini_file      = $install_dir.DS."etc".DS."phpframe.ini";
  $plugins_file  = $install_dir.DS."etc".DS."plugins.xml";
  $app_name      = @$_POST["app_name"];
  $base_url      = @$_POST["base_url"];
  $admin_email   = @$_POST["admin_email"];
  $admin_pass    = @$_POST["admin_pass"];
  $db_host       = @$_POST["db_host"];
  $db_user       = @$_POST["db_user"];
  $db_pass       = @$_POST["db_pass"];
  $db_name       = @$_POST["db_name"];
  $drop_tables   = (bool) @$_POST["drop_tables"];
  $dummy_content = (bool) @$_POST["dummy_content"];
  $secret        = md5(uniqid());

  $db = PHPFrame_DatabaseFactory::getDB(array(
    "driver" => "mysql",
    "host" => $db_host,
    "user" => $db_user,
    "pass" => $db_pass,
    "name" => $db_name
  ));

  if ($drop_tables) {
    $sql  = "DROP TABLE IF EXISTS `api_methods`, `contacts`, `content`,";
    $sql .= "`content_data`, `countries`, `groups`, `notifications`,";
    $sql .= "`oauth_acl`, `oauth_clients`, `oauth_tokens`, `options`, `users`,";
    $sql .= "`users_social`";
    $db->query($sql);
  }

  $cmd  = "/usr/local/bin/mysql -u ".$db_user." -p".$db_pass." ".$db_name;
  $cmd .= " < ".$install_dir."/scripts/install.sql";
  execCmd($cmd);

  require_once $install_dir.DS."src".DS."models".DS."user".DS."User.php";
  require_once $install_dir.DS."src".DS."models".DS."user".DS."UsersMapper.php";

  $user = new User();
  $user->groupId(1);
  $user->email($admin_email);

  $crypt = new PHPFrame_Crypt($secret);
  $salt = $crypt->genRandomPassword(32);
  $encrypted = $crypt->encryptPassword($admin_pass, $salt);
  $user->password($encrypted.":".$salt);

  $user->status("active");
  $user->owner(1);
  $user->group(1);
  $user->perms(440);

  $mapper = new UsersMapper($db);
  $mapper->insert($user);

  if ($dummy_content) {
    $cmd  = "/usr/local/bin/mysql -u ".$db_user." -p".$db_pass." ".$db_name;
    $cmd .= " < ".$install_dir."/scripts/dummy.sql";
    execCmd($cmd);
  }

  if (!copy($ini_file."-dist", $ini_file)) {
    //...
  }

  if (!copy($plugins_file."-dist", $plugins_file)) {
    //...
  }

  $config = new PHPFrame_Config($ini_file);
  $config->set("app_name", "\"".$app_name."\"");
  $config->set("base_url", "\"".$base_url."\"");
  $config->set("secret", $secret);
  $config->set("db.host", $db_host);
  $config->set("db.user", $db_user);
  $config->set("db.pass", $db_pass);
  $config->set("db.name", $db_name);
  $config->set("debug.informer_recipients", $admin_email);

  $config->store();

  PHPFrame_Filesystem::ensureWritableDir($install_dir.DS."tmp");
  PHPFrame_Filesystem::ensureWritableDir($install_dir.DS."var");

  // set up eb backup
  $eb_path = $install_dir.DS."lib".DS."BackupServer";
  $eb_pass = md5(uniqid(rand()));
  $cmd  = "htpasswd -bc ".$eb_path.DS.".htpasswd";
  $cmd .= " eb ".$eb_pass;
  execCmd($cmd);

  $eb_htaccess = file_get_contents($eb_path.DS."src".DS.".htaccess");
  $eb_htaccess = str_replace("#Auth", "Auth", $eb_htaccess);
  $eb_htaccess = str_replace("#Require", "Require", $eb_htaccess);
  $eb_htaccess = str_replace("/path/to/.htpasswd", $eb_path.DS.".htpasswd", $eb_htaccess);
  file_put_contents($eb_path.DS."src".DS.".htaccess", $eb_htaccess);

  $sql  = "INSERT INTO `options` (`name`, `value`, `autoload`) ";
  $sql .= "VALUES ('mashineplugin_backup_pass', '".$eb_pass."', 1)";
  $db->query($sql);

  if (is_link($install_dir.DS."public".DS."eb")) {
    unlink($install_dir.DS."public".DS."eb");
  }

  $cmd = "cd ".$install_dir.DS."public && ln -s ../lib/BackupServer/src ./eb";
  execCmd($cmd);
}

$uri = new PHPFrame_URI();
$base_url = $uri->getBase();
?>
<html>
<head>
  <title>Mashine installer</title>
  <style>
  body {
    font-family: Georgia;
    font-size: 13px;
    width: 520px;
    margin: 20px auto;
  }
  fieldset {
    margin: 0 0 10px;
    padding: 15px;
    border: 1px solid #CCC;
  }
  legend {
    font-size: 1.4em;
  }
  label {
    display: inline-block;
    width: 150px;
  }
  input {
    width: 200px;
  }
  .error {
    display: inline-block;
    color: red;
    font-size: 0.9em;
    padding: 0 0 0 4px;
  }
  </style>
</head>
<body>
  <h1>Mashine installer</h1>
  <form id="install-form">
    <fieldset>
      <legend>App info</legend>
      <label>App name</label>
      <input name="app_name" class="required" value="mashine" />
      <br />
      <label>Base URL</label>
      <input name="base_url" class="required" value="<?php echo $base_url; ?>" />
    </fieldset>
    <fieldset>
      <legend>Admin user</legend>
      <label>Email</label>
      <input name="admin_email" class="required email" value="root@example.com" />
      <br />
      <label>Password</label>
      <input type="password" name="admin_pass" class="required" />
    </fieldset>
    <fieldset>
      <legend>MySQL DB Credentials</legend>
      <label>Host</label>
      <input name="db_host" value="localhost" class="required" />
      <br />
      <label>Username</label>
      <input name="db_user" class="required" value="mashine" />
      <br />
      <label>Password</label>
      <input name="db_pass" class="required" value="HV9WmKjrTF3bx4HV" />
      <br />
      <label>Database name</label>
      <input name="db_name" class="required" value="mashine" />
    </fieldset>
    <fieldset>
      <legend>Options</legend>
      <label>Drop tables before installing?</label>
      <input type="checkbox" name="drop_tables" value="1" />
      <br />
      <label>Install dummy content?</label>
      <input type="checkbox" name="dummy_content" value="1" />
    </fieldset>
    <p>
      <input type="submit" value="Install" />
    </p>
  </form>

<script src="assets/js/jquery/jquery-1.5.1.js"></script>
<script src="assets/js/jquery/jquery.validate.js"></script>
<script>
jQuery(document).ready(function ($) {

$('#install-form').validate({
  errorElement: 'div',
  submitHandler: function (form) {
    var btn = $('input[type=submit]');

    btn.val('Installing...');
    $.ajax({
      url: '<?php echo $base_url; ?>',
      type: 'POST',
      data: $(form).serialize(),
      success: function (data) {
        if (!data.ok) {
          alert(data.error);
        } else {
          window.location = '<?php echo $base_url; ?>';
        }
      },
      complete: function () {
        btn.val('Install');
      }
    });
  }
});

});
</script>
</body>
</html>
