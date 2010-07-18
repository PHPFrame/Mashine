<div class="content_header_wrapper">
    <h1><?php echo $title; ?></h1>
</div>

<div class="entry">

<form class="validate" id="signup-form" action="index.php" method="post">

<fieldset id="log_in_credentials" class="">
<legend><?php echo UserLang::LOGIN_CREDENTIALS; ?></legend>

<p>
    <label for="email"><?php echo UserLang::EMAIL; ?></label>
    <input
        class="required email"
        type="text"
        name="email"
        id="email"
        value="<?php echo $email; ?>"
        size="25"
    />
</p>

<p>
    <label for="password"><?php echo UserLang::PASSWORD; ?></label>
    <input
        class="required"
        type="password"
        name="password"
        id="password"
        size="25"
    />
</p>

<p>
    <label for="confirm_password"><?php echo UserLang::CONFIRM_PASSWORD; ?></label>
    <input
        class="required"
        type="password"
        name="confirm_password"
        id="confirm_password"
        size="25"
    />
</p>

</fieldset>

<fieldset id="billing_details" class="">
<legend><?php echo UserLang::BILLING_DETAILS; ?></legend>

<p>
    <label for="org_name"><?php echo UserLang::ORGANISATION; ?></label>
    <input type="text" name="org_name" id="org_name" size="25" />
</p>

<p style="width:50%; float:left;">
    <label for="first_name"><?php echo UserLang::FIRST_NAME; ?></label>
    <input
        class="required"
        type="text"
        name="first_name"
        id="first_name"
        size="25"
    />
</p>

<p style="width:50%; float:left;">
    <label for="last_name"><?php echo UserLang::LAST_NAME; ?></label>
    <input
        class="required"
        type="text"
        name="last_name"
        id="last_name"
        size="25"
    />
</p>

<p style="width:50%; float:left;">
    <label for="address1"><?php echo UserLang::ADDRESS1; ?></label>
    <input
        class="required"
        type="text"
        name="address1"
        id="address1"
        size="25"
    />
</p>

<p style="width:50%; float:left;">
    <label for="address2"><?php echo UserLang::ADDRESS2; ?></label>
    <input type="text" name="address2" id="address2" size="25" />
</p>

<p style="width:50%; float:left;">
    <label for="city"><?php echo UserLang::CITY; ?></label>
    <input class="required" type="text" name="city" id="city" size="25" />
</p>

<p style="width:50%; float:left;">
    <label for="post_code"><?php echo UserLang::POST_CODE; ?></label>
    <input
        class="required"
        type="text"
        name="post_code"
        id="post_code"
        size="25"
    />
</p>

<p style="width:50%; float:left;">
    <label for="county"><?php echo UserLang::COUNTY; ?></label>
    <input class="required" type="text" name="county" id="county" size="25" />
</p>

<p style="width:50%; float:left;">
    <label for="country"><?php echo UserLang::COUNTRY; ?></label>
    <?php echo $helper->countrySelect(); ?>
</p>

<p style="width:50%; float:left;">
    <label for="phone"><?php echo UserLang::PHONE; ?></label>
    <input class="required" type="text" name="phone" id="phone" size="25" />
</p>

<p style="width:50%; float:left;">
    <label for="fax"><?php echo UserLang::FAX; ?></label>
    <input type="text" name="fax" id="fax" size="25" />
</p>

</fieldset>

<p>
    <span class="button_wrapper">
        <input class="button" type="submit" value="<?php echo UserLang::SIGNUP; ?>" />
    </span>
</p>

<input type="hidden" name="controller" value="user" />
<input type="hidden" name="action" value="save" />
<input type="hidden" name="ret_url" value="<?php echo $ret_url; ?>" />
</form>

</div><!-- .entry -->
