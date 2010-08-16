<div class="content_header_wrapper">
    <h1><?php echo $title; ?></h1>
</div>

<div class="entry">

<p>
    <a href="admin/user/form"><?php echo UserLang::NEW_USER; ?></a>
     |
    <a href="#" id="user-export-toggle">Export</a>
</p>

<div id="user-export" style="display: none;">

<h4>Export users</h4>

<p>
    <form action="index.php" method="post">
        <p>
            <label for="format" class="inline">Format:</label>
            <select name="format" id="format">
                <option value="csv">csv</option>
            </select>
            &nbsp;&nbsp;
            <label for="status" class="inline">Status:</label>
            <input type="checkbox" name="status[]" value="active" /> Active
             |
            <input type="checkbox" name="status[]" value="pending" /> Pending
             |
            <input type="checkbox" name="status[]" value="suspended" /> Suspended
             |
            <input type="checkbox" name="status[]" value="cancelled" /> Cancelled
        </p>

        <p>
            <span class="button_wrapper">
                <input class="button" type="submit" value="Export" />
            </span>
        </p>

        <input type="hidden" name="controller" value="user" />
        <input type="hidden" name="action" value="export" />
    </form>
</p>

</div><!-- #user-export -->

<table>
    <thead>
        <tr>
            <th><?php echo UserLang::NAME; ?></th>
            <th><?php echo UserLang::EMAIL; ?></th>
            <th><?php echo UserLang::PRIMARY_GROUP; ?></th>
            <th><?php echo UserLang::STATUS; ?></th>
            <th></th>
        </tr>
    </thead>
    <tbody>
        <?php foreach($users as $user) : ?>
        <?php $email = new PHPFrame_String($user->email()); ?>
        <?php
        $contact = $user->contact();
        if ($contact instanceof Contact) {
            $name = $contact->firstName()." ".$contact->lastName();
        } else {
            $name = "null";
        }
        ?>
        <tr>
            <td><?php echo $name; ?></td>
            <td>
                <a href="admin/user/form?id=<?php echo $user->id(); ?>">
                <?php echo $email->limitChars(35); ?>
                </a>
            </td>
            <td><?php echo $user->groupName(); ?></td>
            <td><?php echo $user->status(); ?></td>
            <td>
                <a href="admin/user/form?id=<?php echo $user->id(); ?>">
                    Edit
                </a>
                <?php if ($user->id() > 1) : ?>
                 -
                <a
                    href="index.php?controller=user&amp;action=delete&amp;id=<?php echo $user->id(); ?>"
                    class="confirm"
                    title="Are you sure you want to delete user <?php echo $user->email(); ?>?"
                >
                    Delete
                </a>
                <?php endif; ?>
            </td>
        </tr>
        <?php endforeach; ?>
    </tbody>
</table>

</div><!-- .entry -->

<div style="clear:both;"></div>
