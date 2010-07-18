<?php if (count($events) > 0) : ?>
<div class="sysevents">
<ul>
<?php foreach ($events as $event) : ?>
<li>
    <div class="sysevent">
        <div class="sysevent-header sysevents_<?php echo $event[1]; ?>">
            <a href="#" class="close_button" 
               title="close this system event">&times;</a>
        </div><!-- .sidebar-item-header -->
        
        <div class="sysevent-body">
            <?php echo $event[0]."\n"; ?>
        </div><!-- .sysevent-body -->
        
    </div><!-- .sysevent -->
</li>
<?php endforeach; ?>
</ul>
</div><!-- .sysevents -->
<?php endif; ?>

<?php $events->clear(); ?>

