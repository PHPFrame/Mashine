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
    const LOGIN_ERROR_UNKNOWN_EMAIL = "Sorry, we couldn't log you in. Please
    re-type your email address. If you don't have an account please
    <a href=\"%s\">sign up here</a>.";

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

    const SIGNUP_EMAIL_SUBJECT = "Welcome To E-NOISE | %s";
    const SIGNUP_EMAIL_BODY =
"Dear %s,

Welcome to your new E-NOISE web hosting account for %s. This email contains
some vital information about your E-NOISE account, so please keep it safe.

Your Hosting Account is now active but please note that if your Domain Name was
newly registered or if you recently changed your domain registration record,
you may have to use the IP address for 2 or 3 days until your domain becomes
live. If you have transferred your Domain Name to E-NOISE you will receive a
separate confirmation email.

*** E-NOISE Dashboard Login ***
Your Username is: %s
Your Password is: %s
Dashboard Login Address: http://www.e-noise.com
(Your Dashboard is used for managing your Hosting Account including billing,
    support, domain management and Control Panel access)

*** Hosting Package Information ***
Domain Name: yourdomianname.co.uk or until the domain is live
http://79.170.44.137/yourdomainname.co.uk/
Package Type: 2009-06-Home
Renewal Date: day/month/year

*** FTP Access***
Username: yourdomainname.co.uk
Password: password
Server Name: ftp.yourdomainname.co.uk or 79.170.44.31

*** Email ***
WebMail Address: http://webmail.e-noise.com/
Personalised WebMail Address:
http://webmail.yourdomainname.co.uk (once domain is live)

Incoming Mail Server: mail.yourdomainname.co.uk or 79.170.40.79
Outgoing Mail Server: mail.yourdomainname.co.uk or 79.170.40.79

To configure your email please select Control Panel from your Dashboard and
click 'Email'

Be sure to secure your scripts, as any spam sent out using sendmail will result
in account deactivation.

***DNS Settings***
If your domain name was previously hosted somewhere else, you will need to
update your DNS information to the following:

ns.mainnameserver.com
ns2.mainnameserver.com

***Support***
If you have any questions please don’t hesitate to contact the Support Team by
sending an email to support@e-noise.com from the email address registered with
your E-NOISE account. Alternatively you can create a new ticket by clicking on
Support in your Dashboard.

***Useful Links***
Support
FAQ’s
How to unlock FTP Access
One Click WordPress and Joomla! installation
How to configure your email client

Happy hosting...
The E-NOISE Team";

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

    const TICKET_OPENED_CUSTOMER = "Thank you for your ticket, we will reply as soon as we can.";
    const TICKET_OPENED_SUPPORT = "Support ticket [NUMBER] has been opened ";
    const TICKET_UPDATED_CUSTOMER = "Thank you for updating [TICKETNUMBER] the Support department will reply shortly. The status is [TICKET STATUS] ";
    const TICKET_UPDATED_SUPPORT = "Your Support Ticket [NUMBER], [TICKETNAME] has been updated. The status is [TICKET STATUS]";
    const TICKET_CREATED = "Your Support Ticket has been created, for reference it is #[TICKETNUMBER]. The status is[TICKETSTATUS]";

    const GOT_FACEBOOK = "It looks like you have a Facebook Account, you can login using Facebook Connect";

    const LOGIN_WRONG_PASSWORD = "Sorry we couldn't log you in. Please re-type your password. (Remember, they're case sensitive)";
    const LOGIN_ENTER_PASSWORD = "Sorry we couldn't log you in. Please enter your password. (Remember, they're case sensitive)";
    const LOGIN_WRONG_EMAIL = "Sorry, we couldn't log you in. Please re-type your email address. If you don't have an account please [SIGN UP HERE].";
    const LOGIN_WRONG_DETAILS = "Sorry, we couldn't find your details. If you don't have an account please [SIGN UP HERE].";
    const LOGIN_CONFIRM_EMAIL = "We have sent you an activation email to [CLEINT EMAIL] Please check your mail to confirm your address and activate your account.";
    const ACOUNT_ACTIVATED = "Your account has been successfully activated";
    const DOMAIN_TAKEN = "[DOMAINNAME] has already been registered. If you own this domain please select the Transfer or Self-Manage option.";
    const DOMAIN_AVAILABLE = "[DOMAINNAME] is available! Feel free to continue with your order";
    const DOMAIN_NOT_AVAILABLE = "Warning! [DOMAINNAME] cannot be registered with E-NOISE as [DOMINS EXTENSION] is an unsupported domain extension. Please choose another Domain Name";
    const SELECT_DOMAIN = "Whoops! Please choose a Domain name";
    const SELECT_PACKAGE = "Whoops! Please select select a Hosting Package";
    const PLEASE_SIGN_IN = "Whoops! Please log in to access your [FEATURE]";
    const STARTED_NO_FREE_DOMIAN = "Whoops! The Starter Package does not include a free Domain Name. Please select the Transfer or Self-Manage option. Alternatively please choose a Hosting Package that includes a Free Domain Name. ";

    // admin
    const NEW_USER = "New user";
    const NAME = "Name";
    const PRIMARY_GROUP = "Primary group";
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
