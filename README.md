[Mashine](http://github.com/E-NOISE/Mashine)
===

THIS PROJECT IS STILL UNDER DEVELOPMENT AND THERE ARE NO RELEASED PACKAGES YET.

Mashine is a simple we publishing platform as well as a great host application
to develop custom apps. Mashine is written in PHP and leverages the
[PHPFrame](http://github.com/PHPFrame) MVC framework in order to provide a
robust and lightweight application.

## Core Features:

* CMS (Pages and blog management)
* WYSIWYG ([TinyMCE](http://tinymce.moxiecode.com/))
* Built-in XML sitemaps
* Built-in RSS Feeds
* Flexible theme engine
* Plugin API (hooks and filters)
* Backup tool
* Automatic updates from GUI
* RESTful API with [_OAuth_](http://oauth.net/) authorisation and output in
  JSON, PHP and XML formats
* (Import from WordPress)
* (Import from Joomla!)

## Built-in plugins

* Google Analytics: Integration of
  [Google Analytics](http://www.google.com/analytics/) tracker
* Social: Fetch RSS/Atom feeds and integrate
  [Facebook](http://www.facebook.com/) Connect and
  [Twitter](http://twitter.com/)
* Contact form
* ([_OpenID_](http://openid.net/))

## About the development process

* Supports both MySQL and SQLite databases
* Object Oriented
* Automated build
* Unit tests
* PEAR Coding Standards (checked with PHP Code Sniffer)
* CSS and JavaScript minimisation

## Installing Mashine

1. Dowload and extract:

    `wget http://dist.phpframe.org/apps/Mashine/latest-release/?get=download`

    `tar -xzvf Mashine-0.0.xx.tgz`

    `rm Mashine-0.0.xx.tgz`

2. Create working directories and make the writable to the web server:

    `mkdir var tmp`

    `chown :www-data var/ tmp/`

    `chmod 771 var/ tmp/`

3. Create configuration file:

    `cp etc/phpframe.ini-dist etc/phpframe.ini`

4. Manually edit the configuration file as needed.
