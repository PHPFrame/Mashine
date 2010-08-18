<div class="content_header_wrapper">
    <h1><?php echo $title; ?></h1>
</div>

<div class="entry">

<?php if ($user->id() && $session->getUser()->id() < 3 && $session->getUser()->id() != $user->id()) : ?>
Admin actions:
<a
    class="confirm"
    href="user/delete?id=<?php echo $user->id(); ?>"
    title="Are you sure you want to delete user <?php echo $user->email(); ?>?"
>
    Delete
</a>
<?php endif ?>

<form class="validate" id="user-form" action="index.php" method="post">

<fieldset id="login_credentails" style="width: 50%; float:left">
    <legend><?php echo UserLang::LOGIN_CREDENTIALS; ?></legend>

    <?php if ($session->getUser()->groupId() < 3) : ?>
    <p>
        <label for="group_id"><?php echo UserLang::PRIMARY_GROUP; ?></label>
        <?php if ($user->id() == 1) : ?>
            <?php echo $user->groupName(); ?>
        <?php else : ?>
        <select name="group_id" id="group_id">
            <?php foreach ($helper->getGroups() as $key=>$value) : ?>
            <option value="<?php echo $key; ?>"<?php if ($user->groupId() == $key) { echo " selected"; } ?>>
                <?php echo $value."\n"; ?>
            </option>
            <?php endforeach; ?>
        </select>
        <?php endif; ?>
    </p>
    <?php endif; ?>

    <?php if ($session->getUser()->groupId() < 3 && $user->id() > 1) : ?>
    <p>
        Status: <?php echo $user->status(); ?>
        (
        <?php if ($user->status() === "pending"): ?>
        <a href="user/status?id=<?php echo $user->id(); ?>&amp;status=active&amp;ret_url=<?php echo urlencode($ret_url); ?>">
            Activate
        </a>
        <?php endif ?>
        <?php if ($user->status() === "active"): ?>
        <a href="user/status?id=<?php echo $user->id(); ?>&amp;status=suspended&amp;ret_url=<?php echo urlencode($ret_url); ?>">
            Suspend
        </a>
        <?php endif ?>
        <?php if ($user->status() === "suspended" || $user->status() === "cancelled"): ?>
        <a href="user/status?id=<?php echo $user->id(); ?>&amp;status=active&amp;ret_url=<?php echo urlencode($ret_url); ?>">
            Reactivate
        </a>
        <?php endif ?>
        <?php if ($user->status() !== "cancelled"): ?>
         |
        <a href="user/status?id=<?php echo $user->id(); ?>&amp;status=cancelled&amp;ret_url=<?php echo urlencode($ret_url); ?>">
            Cancel
        </a>
        <?php endif ?>
        )
    </p>
    <?php endif ?>

    <p>
        <label for="email"><?php echo UserLang::EMAIL; ?></label>
        <input type="text" name="email" value="<?php echo $user->email(); ?>" id="email" />
    </p>

    <?php if ($session->getUser()->id() < 3 && !$user->id()) : ?>
    <p>
        <?php echo UserLang::AUTOGENERATE_PASS; ?>
    </p>
    <?php else : ?>
    <p>
        <label for="password"><?php echo UserLang::PASSWORD; ?></label>
        <input type="password" name="password" value="" id="password" autocomplete="off" />
    </p>

    <p>
        <label for="confirm_password"><?php echo UserLang::CONFIRM_PASSWORD; ?></label>
        <input type="password" name="confirm_password" value="" id="confirm_password" autocomplete="off" />
    </p>
    <?php endif; ?>
</fieldset>

<fieldset id="contacts" style="width: 50%; float:left">
    <legend><?php echo UserLang::CONTACTS; ?></legend>

    <?php if (!$user->id()) : ?>
    <p>
        <?php echo UserLang::CONTACTS_CAN_BE_ADDED_AFTER; ?>
    </p>
    <?php else : ?>
    <p>
        <a href="user/addcontact?owner=<?php echo $user->id(); ?>"><?php echo UserLang::NEW_CONTACT; ?></a>
    </p>

    <?php $contacts = $user->contacts(); ?>
    <?php if (count($contacts) > 0) : ?>
    <ul>
    <?php foreach ($contacts as $contact) : ?>
        <li>
            <p>
                <?php if ($contact->preferred()) { echo "pref"; } ?>
                <?php echo $contact->orgName(); ?>
                <?php echo $contact->firstName(); ?>
                <?php echo $contact->lastName(); ?>
                <?php echo $contact->orgName(); ?>
                <?php echo $contact->address1(); ?>
                <?php echo $contact->city(); ?>
                <?php echo $contact->postCode(); ?>
                <?php echo $contact->county(); ?>
                <?php echo $contact->country(); ?>
                <?php echo $contact->phone(); ?>
                <?php echo $contact->email(); ?>
            </p>
            <p>
                <a href="user/editcontact?id=<?php echo $contact->id(); ?>">
                    <?php echo GlobalLang::EDIT; ?>
                </a>
                <?php if (!$contact->preferred()) : ?>
                 -
                <a
                    class="confirm"
                    href="user/deletecontact?id=<?php echo $contact->id(); ?>"
                    title="Are you sure you want to delete contact <?php echo $contact->firstName(); ?> <?php echo $contact->lastName(); ?>?"
                >
                    <?php echo GlobalLang::DELETE; ?>
                </a>
                <?php endif; ?>
            </p>
        </li>
    <?php endforeach; ?>
    </ul>
    <?php endif; ?>

    <?php endif; ?>
</fieldset>

<div style="clear:left;"></div>

<p>
    <span class="button_wrapper">
        <input
            type="button"
            class="button back"
            value="<?php echo GlobalLang::BACK; ?>"
            onclick="window.history.back();"
        />
    </span>
    <span class="button_wrapper">
        <button type="reset" class="button reset">
            <?php echo GlobalLang::RESET; ?>
        </button>
    </span>
    <span class="button_wrapper">
        <input
            type="submit"
            class="button"
            value="<?php echo GlobalLang::SAVE; ?>"
        />
    </span>
</p>

<input type="hidden" name="controller" value="user" />
<input type="hidden" name="action" value="save" />
<input type="hidden" name="id" value="<?php echo $user->id(); ?>" />
<input type="hidden" name="ret_url" value="<?php echo $ret_url; ?>" />
</form>

</div><!-- .entry -->

<div style="clear:both;"></div>

<?php //var_dump(iterator_to_array($user)); ?>