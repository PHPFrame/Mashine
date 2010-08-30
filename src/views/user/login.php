<div class="content_header_wrapper">
    <h1><?php echo $title; ?></h1>
</div>

<div class="entry">

<form class="validate" id="login-form" action="index.php" method="post">

<p>
    <label for="email"><?php echo UserLang::EMAIL; ?></label>
    <input
        class="required email"
        type="email"
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
    <input type="checkbox" name="remember_me" id="remember_me" value="1" />
    <label for="remember_me" style="display:inline;">
        <?php echo UserLang::REMEMBER_ME; ?>
        (
        <a
            original-title="<?php echo UserLang::REMEMBER_ME_TOOLTIP; ?>"
            href="#"
            class="tooltip"
        >
            <?php echo GlobalLang::WHAT_IS_THIS; ?>
        </a>)
    </label>
</p>


<p>
    <span class="button_wrapper">
        <input
            id="login-button"
            class="button"
            type="submit"
            value="<?php echo UserLang::LOGIN; ?>"
        />
    </span>

    <span id="login-ajax-response"></span>
</p>

<input type="hidden" name="controller" value="user" />
<input type="hidden" name="action" value="login" />
<input type="hidden" name="ret_url" value="<?php echo $ret_url; ?>" />
<input type="hidden" name="token" value="<?php echo $token; ?>" />
</form>

<p>
    <a id="forgotpass-link" href="#"><?php echo UserLang::FORGOT_PASS; ?></a>
</p>

<div id="forgotpass">
<form class="validate" id="forgotpass-form" action="index.php" method="post">
<p>
    <label for="forgot_email"><?php echo UserLang::EMAIL; ?></label>
    <input
        class="required email"
        type="text"
        name="forgot_email"
        id="forgot_email"
        size="25"
    />
</p>

<p>
    <span class="button_wrapper">
        <input
            class="button"
            type="submit"
            value="<?php echo UserLang::SEND_NEW_PASS; ?>"
        />
    </span>
</p>

<input type="hidden" name="controller" value="user" />
<input type="hidden" name="action" value="reset" />
</form>
</div>

<br />

<div>

<p>
    <?php echo implode("\n", $login_plugins)."\n"; ?>
</p>

</div>

</div><!-- .entry -->

<script>initLoginForm();</script>
