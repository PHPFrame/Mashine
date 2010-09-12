<div id="content-header">
    <h1><?php echo $title; ?></h1>
</div>

<div class="entry">

<form class="validate" id="user-contacts-form" action="index.php" method="post">

<fieldset id="contact_details" style="width:50%; float:left;">
    <legend><?php echo UserLang::CONTACT_DETAILS; ?></legend>

    <p>
        <label for="org_name"><?php echo UserLang::ORGANISATION; ?></label>
        <input
            type="text"
            name="org_name"
            id="org_name"
            value="<?php echo $contact->orgName(); ?>"
        />
    </p>

    <p>
        <label for="first_name"><?php echo UserLang::FIRST_NAME; ?></label>
        <input
            type="text"
            name="first_name"
            id="first_name"
            class="required"
            value="<?php echo $contact->firstName(); ?>"
        />
    </p>

    <p>
        <label for="last_name"><?php echo UserLang::LAST_NAME; ?></label>
        <input
            type="text"
            name="last_name"
            id="last_name"
            class="required"
            value="<?php echo $contact->lastName(); ?>"
        />
    </p>

    <p>
        <label for="phone"><?php echo UserLang::PHONE; ?></label>
        <input
            type="text"
            name="phone"
            id="phone"
            class="required"
            value="<?php echo $contact->phone(); ?>"
        />
    </p>

    <p>
        <label for="fax"><?php echo UserLang::FAX; ?></label>
        <input
            type="text"
            name="fax"
            id="fax"
            value="<?php echo $contact->fax(); ?>"
        />
    </p>

    <p>
        <label for="email"><?php echo UserLang::EMAIL; ?></label>

        <?php if (!$user->contact() || count($user->contacts()) < 1) : ?>
            <?php echo $contact->email(); ?>
            <input
                type="hidden"
                name="email"
                value="<?php echo $contact->email(); ?>"
            />
        <?php else : ?>
        <input
            type="text"
            name="email"
            id="email"
            class="required"
            value="<?php echo $contact->email(); ?>"
        />
        <?php endif; ?>
    </p>
</fieldset>

<fieldset id="mailing_address" style="width:50%; float:left;">
    <legend><?php echo UserLang::MAILING_ADDRESS; ?></legend>

    <p>
        <label for="address1"><?php echo UserLang::ADDRESS1; ?></label>
        <input
            type="text"
            name="address1"
            id="address1"
            class="required"
            value="<?php echo $contact->address1(); ?>"
        />
    </p>

    <p>
        <label for="address2"><?php echo UserLang::ADDRESS2; ?></label>
        <input
            type="text"
            name="address2"
            id="address2"
            value="<?php echo $contact->address2(); ?>"
        />
    </p>

    <p>
        <label for="city"><?php echo UserLang::CITY; ?></label>
        <input
            type="text"
            name="city"
            id="city"
            class="required"
            value="<?php echo $contact->city(); ?>"
        />
    </p>

    <p>
        <label for="post_code"><?php echo UserLang::POST_CODE; ?></label>
        <input
            type="text"
            name="post_code"
            id="post_code"
            class="required"
            value="<?php echo $contact->postCode(); ?>"
        />
    </p>

    <p>
        <label for="county"><?php echo UserLang::COUNTY; ?></label>
        <input
            type="text"
            name="county"
            id="county"
            class="required"
            value="<?php echo $contact->county(); ?>"
        />
    </p>

    <p>
        <label for="country"><?php echo UserLang::COUNTRY; ?></label>
        <?php echo $helper->countrySelect($contact->country()); ?>
    </p>
</fieldset>

<div style="clear:left;">

</div>

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

    <?php if (!$contact->preferred()) : ?>
    <input type="checkbox" name="preferred" />
    <?php echo UserLang::MAKE_DEFAULT_CONTACT; ?>
    <?php endif; ?>
</p>

<input type="hidden" name="controller" value="user" />
<input type="hidden" name="action" value="savecontact" />
<input type="hidden" name="ret_url" value="<?php echo $ret_url; ?>" />
<input type="hidden" name="id" value="<?php echo $contact->id(); ?>" />
<input type="hidden" name="owner" value="<?php echo $contact->owner(); ?>" />
<input type="hidden" name="group" value="<?php echo $contact->group(); ?>" />
</form>

</div><!-- .entry -->

<div style="clear:both;"></div>
