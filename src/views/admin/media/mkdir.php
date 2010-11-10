<header id="content-header">
  <h1><?php echo $title; ?></h1>
</header>

<div id="content-body">
  <form action="index.php" method="post">
    <fieldset>
      <legend>New directory details</legend>
      <p>
        Directory will be created in <?php echo $upload_dir."/".$parent->getRelativePath(); ?>
      </p>
      <p>
        <label for="name"><?php echo MediaLang::DIRNAME; ?>:</label>
        <input type="text" name="name" id="name" />
      </p>
    </fieldset>
    <p>
      <input type="button" name="backbtn" onclick="window.history.back();" value="<?php echo MediaLang::BACK; ?>" />
      <input type="submit" name="submitbtn" value="<?php echo MediaLang::GO; ?>" />
    </p>

    <input type="hidden" name="parent" value="<?php echo $parent->getRelativePath(); ?>" />
    <input type="hidden" name="controller" value="media" />
    <input type="hidden" name="action" value="mkdir" />
  </form>
</div><!-- #content-body -->

<script>
var submitNewDir = function () {
  var form = document.new_dir_form;
  var myRegxp = /^([\sa-zA-Z0-9_\-]+)$/;

  if (myRegxp.test(form.name.value) == false) {
    alert('<?php echo MediaLang::INVALID_DIR_NAME; ?>');
  } else if (form.name.value == "") {
    alert('<?php echo MediaLang::INVALID_DIR_NAME; ?>');
  } else {
    form.submit();
  }
};
</script>

