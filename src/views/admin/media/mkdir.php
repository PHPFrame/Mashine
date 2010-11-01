<header id="content-header">
  <h1><?php echo $title; ?></h1>
</header>

<div id="content-body">
  <form name="new_dir_form" action="index.php" method="post">
    <fieldset>
      <legend>New directory details</legend>
      <p>
        <label for="name"><?php echo MediaLang::DIRNAME; ?>:</label>
        <input type="text" name="name" id="name" />
      </p>
    </fieldset>
    <p>
      <input type="button" name="backbtn" onclick="window.history.back();" value="<?php echo MediaLang::BACK; ?>" />
      <input type="submit" name="submitbtn" value="<?php echo MediaLang::GO; ?>" />
    </p>

    <input type="hidden" name="parent" value="<?php echo $current_dir->getRelativePath(); ?>" />
    <input type="hidden" name="controller" value="media" />
    <input type="hidden" name="action" value="mkdir" />
  </form>
</div><!-- #content-body -->

