<?php if (count($events) > 0) : ?>
<div class="sysevents-wrapper">
<?php foreach ($events as $event) : ?>
  <div class="sysevents-item sysevents-<?php echo $event[1]; ?>">
    <div class="sysevents-item-header">
      <a href="#" class="close_button" title="close this system event">&times;</a>
    </div>
    <div class="sysevents-item-body">
      <?php echo $event[0]."\n"; ?>
    </div>
  </div>
<?php endforeach; ?>
</div><!-- .sysevents-wrapper -->
<?php endif; ?>
<?php $events->clear(); ?>
