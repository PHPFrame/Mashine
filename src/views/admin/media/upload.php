<header id="content-header">
  <h1><?php echo $title; ?></h1>
</header>

<div id="content-body">
  <form action="index.php" method="post" enctype="multipart/form-data">
    <fieldset>
      <legend><?php echo MediaLang::UPLOAD_LEGEND; ?></legend>
      <p>
        File(s) will be uploaded to <?php echo $upload_dir."/".$parent->getRelativePath(); ?>
      </p>
      <p>
        <label for="upload_file"><?php echo MediaLang::UPLOAD_FILE; ?>:</label>
        <input type="file" name="upload_file" id="upload_file" />
      </p>
    </fieldset>
    <p>
      <input type="button" name="backbtn" onclick="window.history.back();" value="<?php echo MediaLang::BACK; ?>" />
      <input type="submit" name="submitbtn" value="<?php echo MediaLang::GO; ?>" />
    </p>

    <input type="hidden" name="parent" value="<?php echo $parent->getRelativePath(); ?>" />
    <input type="hidden" name="controller" value="media" />
    <input type="hidden" name="action" value="upload" />
  </form>
</div><!-- #content-body -->

<script>
var submitUpload = function () {
  var form = document.upload_form;

  if (form.upload_file.value == "") {
    alert('<?php echo MediaLang::UPLOAD_ERROR_NO_FILE_SELECTED; ?>');
    return false;
  } else {
    form.submit();
  }
};
</script>

