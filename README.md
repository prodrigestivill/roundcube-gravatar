Roundcube Gravatar
==================

Non official plug-in showing [Gravatar](https://www.gravatar.com/) profile images inside the [Roundcube webmail](https://roundcube.net/) client, it also supports custom avatars API.

It has been implemented as Roundcube readonly hidden addressbook. You have to ensure this plugin is added into the latest entry in the main config (`$config['plugins']`). If so and any address book (LDAP, Google, etc...) already has a photo for a contact it will use first the other pictures and lastly if none reported it will use gravatar (Roundcube will use it in following the order listed in the main config).

Tested in roundcube 1.2.0.


Installation
============

Intallation steps:
  - Via composer:
    - Run `php composer.phar require "prodrigestivill/gravatar":"dev-master"`
  - Via git:
    - Clone the repository:
      `cd roundcube/plugins && git clone git@github.com:prodrigestivill/roundcube-gravatar.git gravatar`
  - Via tarball:
    - Download and extract the tarball into `roundcube/plugins` directory and rename the extracted directory to `gravatar`


For the expected behaviour **ensure** it is always the latest plugin (or at least addressbook plugin) in the `$config['plugins']` list at `config/config.inc.php`.
Also consider **cleaning the browser cache** when changing from disabled to enabled or vice versa.

To enable per user: Login to Roundcube and enable/disable plugin by navigating to the Settings page, clicking on Preferences, click on Address Book, and Enable Gravatar, and Save.


To configure/change default values:
  - Copy `config.inc.php.dist` to `config.inc.php` in `plugins/gravatar/` directory.
  - Modify the values you are interested into change and comment the rest with '//'


Custom API
==========

You can define your custom API for photos at 'gravatar_custom_photo_api' in `config.inc.php` in `plugins/gravatar/` directory. With the following substitutions.
  - %%: literal '%'
  - %s: schema ('http', 'https') depending of 'gravatar_https' config
  - %e: contact email (escaped with urlencode)
  - %m: hashed md5(email)
  - %a: hashed sha1(email)
  - %z: configured avatar size (in px)
  - %r: configured rating ('g', 'pg', 'r', 'x')

Usage for default Gravatar API is: `%s://www.gravatar.com/avatar/%m?s=%z&r=%r&d=404`


You can use local files, it is not needed to be an URL. But if you plan to use it for direct filesystem access, for security reasons it is best to only use hashed email substitutions, even that all parameters are escaped with urlencode.


Examples:
```php
$config['gravatar_custom_photo_api'] = 'http://www.example.com/directory/%e.jpg?s=%z';
//OR//
$config['gravatar_custom_photo_api'] = '/path/%m.jpg';
```
