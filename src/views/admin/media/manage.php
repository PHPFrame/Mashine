<header id="content-header">
  <h1><?php echo $title; ?></h1>
</header>

<div id="content-body">

<div id="imagebrowser-server-info">

<h4><a href="#"><?php echo MediaLang::PHP_UPLOAD_DIRECTIVES; ?></a></h4>

<ul>
    <li>
        memory_limit: <?php echo ini_get("memory_limit"); ?>
    </li>
    <li>
        upload_max_filesize: <?php echo ini_get("upload_max_filesize"); ?>
    </li>
    <li>
        post_max_size: <?php echo ini_get("post_max_size"); ?>
    </li>
    <li>
        max_execution_time: <?php echo ini_get("max_execution_time"); ?>
    </li>
    <li>
        max_input_time: <?php echo ini_get("max_input_time"); ?>
    </li>
</ul>

</div><!-- #imagebrowser-server-info -->

<?php if ($current_dir->isWritable()): ?>

<div id="imagebrowser-toolbar">

<div class="imagebrowser-toolbar-item">
<form name="new_dir_form" action="index.php" method="post">
<table cellpadding="3" cellspacing="1" border="0">
    <tr>
        <td><img src="components/com_imagebrowser/assets/img/folder_new.png" alt="<?php echo MediaLang::NEW_DIR; ?>" /></td>
        <td><?php echo MediaLang::NEW_DIR; ?>:</td>
        <td><input type="text" name="name" size="" /></td>
        <td><input type="button" name="submitbutton" onclick="submitNewDir();" value="<?php echo MediaLang::GO; ?>" /></td>
    </tr>
</table>
<input type="hidden" name="parent" value="<?php echo $current_dir->getRelativePath(); ?>" />
<input type="hidden" name="task" value="mkdir" />
<input type="hidden" name="option" value="com_imagebrowser" />
</form>
</div><!-- #imagebrowser-toolbar-item -->

<div class="imagebrowser-toolbar-item">
<form name="upload_form" action="index.php" method="post" enctype="multipart/form-data">
<table cellpadding="3" cellspacing="1" border="0">
    <tr>
        <td><img src="components/com_imagebrowser/assets/img/up.png" alt="<?php echo MediaLang::UPLOAD_IMAGE; ?>" /></td>
        <td><?php echo MediaLang::UPLOAD_IMAGE; ?>:</td>
        <td><input type="file" name="upload_file" size="" /></td>
        <td><input type="submit" name="submit" onclick="return submitUpload();" value="<?php echo MediaLang::UPLOAD; ?>" /></td>
        <td>(<?php echo MediaLang::MAX_UPLOAD_SIZE; ?>: <?php echo ini_get("upload_max_filesize"); ?>)</td>
    </tr>
</table>
<input type="hidden" name="parent" value="<?php echo $current_dir->getRelativePath(); ?>" />
<input type="hidden" name="task" value="upload" />
<input type="hidden" name="option" value="com_imagebrowser" />
</form>
</div><!-- #imagebrowser-toolbar-item -->

<div style="clear: left;"></div>

<div class="imagebrowser-toolbar-item">
<form action="index.php" method="post">
<table cellpadding="3" cellspacing="1" border="0">
    <tr>
        <td><img src="components/com_imagebrowser/assets/img/generate_thumb.png" alt="<?php echo MediaLang::GENERATE_DIR_THUMBS; ?>" /></td>
        <td><?php echo MediaLang::GENERATE_DIR_THUMBS; ?>:</td>
        <td><input type="submit" name="submit" value="<?php echo MediaLang::GO; ?>" /></td>
    </tr>
</table>
<input type="hidden" name="node" value="<?php echo $current_dir->getRelativePath(); ?>" />
<input type="hidden" name="task" value="generateThumbs" />
<input type="hidden" name="option" value="com_imagebrowser" />
</form>
</div><!-- #imagebrowser-toolbar-item -->

<div class="imagebrowser-toolbar-item">
<form action="index.php" method="post">
<table cellpadding="3" cellspacing="1" border="0">
    <tr>
        <td><img src="components/com_imagebrowser/assets/img/resize.png" alt="<?php echo MediaLang::PROCESS_FORCE_MAX_DIMENSIONS; ?>" /></td>
        <td><?php echo MediaLang::PROCESS_FORCE_MAX_DIMENSIONS; ?>:</td>
        <td><input type="submit" name="submit" value="<?php echo MediaLang::GO; ?>" /></td>
    </tr>
</table>
<input type="hidden" name="node" value="<?php echo $current_dir->getRelativePath(); ?>" />
<input type="hidden" name="task" value="resize" />
<input type="hidden" name="option" value="com_imagebrowser" />
</form>
</div><!-- #imagebrowser-toolbar-item -->

<div style="clear: left;"></div>

</div><!-- #imagebrowser-toolbar -->

<?php endif; ?>

<div id="imagebrowser-breadcrumbs">
    <?php echo MediaLang::PATH; ?>: <?php echo $current_dir->getBreadCrumbs(); ?>
</div>

<div id="imagebrowser-nodes">
<?php foreach ($current_dir as $child) : ?>
<?php if ($child instanceof MediaDirectory): ?>

    <div class="imagebrowser-node-wrapper">

    <div class="imagebrowser-node">
        <div class="imagebrowser-dir-thumb">
        <a href="<?php echo $child->getNodeURL(); ?>">
        <img
            src="<?php echo $child->getThumbURL(); ?>"
            alt="<?php echo $child->getFilename(); ?>"
        />
        </a>
        </div>

        <div class="imagebrowser-node-info">
            <h3 class="imagebrowser-node-info-name">
                <a href="<?php echo $child->getNodeURL(); ?>">
                    <?php echo $child->getFilename(16); ?>
                </a>
            </h3>
        </div>

        <div style="clear: left;"></div>
    </div><!-- imagebrowser-node -->

    <div class="imagebrowser-node-buttons">
        <?php if ($current_dir->isWritable()): ?>
        <a href="<?php echo $child->getRenameURL(); ?>">
            <?php echo MediaLang::RENAME; ?>
        </a>
        <?php endif; ?>

        <?php if ($child->isWritable()): ?>
        <a
            class="imagebrowser-node-buttons-delete"
            href="<?php echo $child->getDeleteURL(); ?>"
            onclick="return confirmDelete('dir', '<?php echo $child->getFilename(); ?>');"
        >
            <?php echo MediaLang::DELETE; ?>
        </a>
        <?php endif; ?>
    </div>

    </div><!-- imagebrowser-node-wrapper -->

<?php elseif ($child instanceof ImageBrowser_Image) : ?>

    <div class="imagebrowser-node-wrapper">

    <div class="imagebrowser-node">
        <div class="imagebrowser-img-thumb">
        <img
            src="<?php echo $child->getThumbURL(); ?>"
            alt="<?php echo $child->getFilename(); ?>"
            title="<?php echo $child->getCaption(); ?>"
        />
        </div>

        <div class="imagebrowser-node-info">
            <h3 class="imagebrowser-node-info-name">
                <?php echo $child->getFilename(16); ?>
            </h3>
            <p>
                <?php echo MediaLang::FILESIZE; ?>:
                <?php echo $child->getSize(true); ?>
                <br />
                <?php echo MediaLang::MODIFIED; ?>:
                <?php echo $child->getMTime(true); ?>
            </p>
        </div>

        <div style="clear: left;"></div>
    </div><!-- .imagebrowser-node -->

    <div class="imagebrowser-node-buttons">
        <?php if ($current_dir->isWritable()): ?>
        <a href="<?php echo $child->getCaptionEditURL(); ?>">
               <?php echo MediaLang::EDIT_CAPTION; ?>
        </a>
        <a href="<?php echo $child->getGenerateThumbURL(); ?>">
            <?php echo MediaLang::GENERATE_THUMB; ?>
        </a>
        <?php endif; ?>
        <?php if ($child->isWritable()): ?>
        <a
            class="imagebrowser-node-buttons-delete"
            href="<?php echo $child->getDeleteURL(); ?>"
            onclick="return confirmDelete('img', '<?php echo $child->getFilename(); ?>');"
        >
            <?php echo MediaLang::DELETE; ?>
        </a>
        <?php endif; ?>
    </div>

    </div><!-- imagebrowser-node-wrapper -->

<?php endif; ?>
<?php endforeach; ?>
</div><!-- #imagebrowser-nodes -->

<div style="clear: left;"></div>

<div id="imagebrowser-footer">
    <p>
        <a href="Javascript:history.go(-1)">
            [ <?php echo MediaLang::BACK; ?> ]
        </a>
    </p>
</div><!-- #imagebrowser-footer -->

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

var submitUpload = function () {
  var form = document.upload_form;

  if (form.upload_file.value == "") {
    alert('<?php echo MediaLang::UPLOAD_ERROR_NO_FILE_SELECTED; ?>');
    return false;
  } else {
    form.submit();
  }
};

var confirmDelete = function (type, fname) {
  var msg = '<?php echo MediaLang::CONFIRM_DELETE; ?>';
  msg += type + ' ' + fname + '?';

  return confirm(msg);
};

jQuery(document).ready(function($) {
  var server_info_ul = $('#imagebrowser-server-info ul');
  server_info_ul.hide();

  $('#imagebrowser-server-info h4 a').click(function(e) {
    e.preventDefault();
    server_info_ul.toggle('slow');
  });

  $('.imagebrowser-node-buttons').hide();

  var showNodeButtons = function(node) {
    $(node).next('.imagebrowser-node-buttons').show();
  };

  var hideNodeButtons = function(node) {
    $(node).children('.imagebrowser-node-buttons').hide();
  };

  $(".imagebrowser-node").bind({
    mouseenter: function(){
      showNodeButtons(this);
    }
  });

  $(".imagebrowser-node-wrapper").bind({
    mouseleave: function() {
      hideNodeButtons(this);
    }
  });
});

</script>


</div><!-- #content-body -->


