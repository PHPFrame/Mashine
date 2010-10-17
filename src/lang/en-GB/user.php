<?php
class UserLang
{
    // Login form
    const EMAIL = "Email";
    const PASSWORD = "Password";
    const REMEMBER_ME = "Remember me?";
    const REMEMBER_ME_TOOLTIP = "If you check this box your browser will
    remember your session and you will stay signed in until you either click
    on the 'logout' link or delete your browser's cookies.";
    const LOGIN = "Log in";
    const FORGOT_PASS = "Forgot your password?";
    const SEND_NEW_PASS = "Send me a new password";

    const LOGIN_ERROR_WRONG_PASSWORD = "Sorry we couldn't log you in. Please
    re-type your password. (Remember, they're case sensitive)";
    const LOGIN_ERROR_ENTER_PASSWORD = "Sorry we couldn't log you in. Please
    enter your password. (Remember, they're case sensitive)";
    const LOGIN_ERROR_INVALID_EMAIL = "Invalid email.";
    const LOGIN_ERROR_UNKNOWN_EMAIL = "Sorry, we couldn't log you in. Wrong
    email address. Don't have an account? <a href=\"%s\">sign up here</a>.";

    // Sign up form
    const LOGIN_CREDENTIALS = "Log in credentials";
    const CONFIRM_PASSWORD = "Confirm password";
    const BILLING_DETAILS = "Billing details";
    const ORGANISATION = "Organisation";
    const FIRST_NAME = "First name";
    const LAST_NAME = "Last name";
    const ADDRESS1 = "Address1";
    const ADDRESS2 = "Address2";
    const CITY = "City / Town";
    const POST_CODE = "Post code";
    const COUNTY = "County / State";
    const COUNTRY = "Country";
    const PHONE = "Phone";
    const FAX = "Fax";
    const SIGNUP = "Sign up";

    // Save user error/success messages
    const ERROR_EMAIL_ALREADY_REGISTERED = "Email address (%s) is already
    registered.";

    const SIGNUP_SUCCESS = "We have sent you an activation email to %s.
    Please check your mail to confirm your address and activate your account.";

    const SIGNUP_EMAIL_BODY =
"Dear %s,

Welcome to your new account on %s.

Your user details are:

Email: %s
Password: %s

Please click on the link below to verify your email address.
%s

The new account will not be fully active until the email address has been
confirmed.";

    const NEW_USER_EMAIL_SUBJECT = "New account created";
    const NEW_USER_EMAIL_BODY =
"An admin has created a user account for you on E-NOISE.com.

Your user details are:

Email: %s
Password: %s

Please click on the link below to verify your email address.
%s";
    const NEW_USER_SUCCESS = "An email with the activation link has been
    sent to %s. The new account will not be fully active until the email address
    has been confirmed.";
    const UPDATE_USER_SUCCESS = "User details updated.";

    const GOT_FACEBOOK = "It looks like you have a Facebook Account, you can login using Facebook Connect";

    // admin
    const NEW_USER = "New user";
    const NAME = "Name";
    const PRIMARY_GROUP = "Group";
    const STATUS = "Status";

    // user form
    const AUTOGENERATE_PASS = "A new password will be automatically created and
    emailed to the new user's email address.";
    const CONTACTS = "Contacts";
    const CONTACTS_CAN_BE_ADDED_AFTER = "Contact details can be added after
    user has been created in the system.";
    const NEW_CONTACT = "Add new contact";

    // Contacts form
    const CONTACT_DETAILS = "Contact details";
    const MAILING_ADDRESS = "Mailing address";
    const MAKE_DEFAULT_CONTACT = "Make default contact for your account.";
}

