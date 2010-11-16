<header id="content-header">
  <h1><?php echo $title; ?></h1>
</header>

<div id="content-body">

<p id="media-server-info">
  Having trouble uploading files? Check you PHP upload directives
  <a href="admin/system#php-upload-directives">here</a>.
</p><!-- #media-server-info -->

<?php if ($node->isWritable()): ?>

<ul id="media-toolbar">
  <li>
    <a href="media/mkdir?parent=<?php echo $node->getRelativePath(); ?>">
      <span class="ui-icon ui-icon-plusthick"></span>New dir
    </a>
  </li>
  <li>
    <a href="media/upload?parent=<?php echo $node->getRelativePath(); ?>">
      <span class="ui-icon ui-icon-document"></span>Upload
    </a>
  </li>
  <li>
    <a
      href="media/generate_thumbs?node=<?php echo $node->getRelativePath(); ?>"
      class="tooltip"
      title="<?php echo MediaLang::GENERATE_DIR_THUMBS; ?>"
    >
      <span class="ui-icon ui-icon-gear"></span>Process thumbs
    </a>
  </li>
  <li>
    <a
      href="media/resize?node=<?php echo $node->getRelativePath(); ?>"
      class="tooltip"
      title="<?php echo MediaLang::PROCESS_FORCE_MAX_DIMENSIONS; ?>"
    >
      <span class="ui-icon ui-icon-arrow-4-diag"></span>Process images
    </a>
  </li>
</ul>

<?php endif; ?>

<div id="media-breadcrumbs">
  <?php echo MediaLang::PATH; ?>: <?php echo $node->getBreadCrumbs("admin/media")."\n"; ?>
</div>

<div id="media-nodes">
<?php foreach ($node as $child) : ?>
<?php if ($child instanceof MediaDirectory): ?>

  <div class="media-node-wrapper">

    <div class="media-node">
      <div class="media-dir-thumb">
        <a href="admin/media?node=<?php echo urlencode($child->getRelativePath()); ?>">
        <img
          src="<?php echo $child->getThumbURL(); ?>"
          alt="<?php echo $child->getFilename(); ?>"
        />
        </a>
      </div>

      <div class="media-node-info">
        <h3 class="media-node-info-name">
          <a href="admin/media?node=<?php echo urlencode($child->getRelativePath()); ?>">
            <?php echo $child->getFilename(12)."\n"; ?>
          </a>
        </h3>
      </div>

      <div style="clear: left;"></div>
    </div><!-- media-node -->

    <div class="media-node-buttons">
      <?php if ($node->isWritable()): ?>
      <a href="media/rename?node=<?php echo $child->getRelativePath(); ?>">
        <?php echo MediaLang::RENAME."\n"; ?>
      </a>
      <?php endif; ?>

      <?php if ($child->isWritable()): ?>
      <a
        class="media-node-buttons-delete confirm"
        title="<?php echo MediaLang::CONFIRM_DELETE." ".MediaLang::DIR." '".$child->getFilename()."'?"; ?>"
        href="media/delete?node=<?php echo $child->getRelativePath(); ?>"
      >
        <?php echo MediaLang::DELETE."\n"; ?>
      </a>
      <?php endif; ?>
    </div>

  </div><!-- media-node-wrapper -->

<?php elseif ($child instanceof Image) : ?>

  <div class="media-node-wrapper">

    <div class="media-node">
      <div class="media-img-thumb">
        <img
          src="<?php echo $child->getThumbURL(); ?>"
          alt="<?php echo $child->getFilename(); ?>"
          title="<?php echo $child->getCaption(); ?>"
        />
      </div>

      <div class="media-node-info">
        <h3 class="media-node-info-name">
          <?php echo $child->getFilename(12)."\n"; ?>
        </h3>
        <p>
          <?php echo MediaLang::FILESIZE; ?>:
          <?php echo $child->getSize(true)."\n"; ?>
          <br />
          <?php echo MediaLang::MODIFIED; ?>:
          <?php echo $child->getMTime(true)."\n"; ?>
        </p>
      </div>

      <div style="clear: left;"></div>
    </div><!-- .media-node -->

    <div class="media-node-buttons">
<?php if ($node->isWritable()): ?>
      <a href="media/caption?node=<?php echo $child->getRelativePath(); ?>">
         <?php echo MediaLang::EDIT_CAPTION."\n"; ?>
      </a>
      <a href="media/generate_thumbs?node=<?php echo $child->getRelativePath(); ?>">
        <?php echo MediaLang::GENERATE_THUMB."\n"; ?>
      </a>
<?php endif; ?>
<?php if ($child->isWritable()): ?>
      <a
        class="media-node-buttons-delete confirm"
        title="<?php echo MediaLang::CONFIRM_DELETE." ".MediaLang::FILE." '".$child->getFilename()."'?"; ?>"
        href="media/delete?node=<?php echo $child->getRelativePath(); ?>"
      >
        <?php echo MediaLang::DELETE."\n"; ?>
      </a>
<?php endif; ?>
    </div>

  </div><!-- media-node-wrapper -->

<?php endif; ?>
<?php endforeach; ?>
</div><!-- #media-nodes -->

<div style="clear: left;"></div>

<div id="media-footer">
  <p>
    <a href="Javascript:history.go(-1)">[ <?php echo MediaLang::BACK; ?> ]</a>
  </p>
</div><!-- #media-footer -->

</div><!-- #content-body -->

<script>
jQuery(document).ready(function ($) {
  $('.media-node-buttons').hide();

  $(".media-node").bind({
    mouseenter: function () {
      $(this).next('.media-node-buttons').show();
    }
  });

  $(".media-node-wrapper").bind({
    mouseleave: function () {
      $(this).children('.media-node-buttons').hide();
    }
  });
});
</script>
