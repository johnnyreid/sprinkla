

# Sprinkla

* http://wiringpi.com/download-and-install/

* http://meinit.nl/allowing-a-group-to-execute-a-specific-command-without-using-a-password-using-sudo

* Setting permissions
https://www.raspberrypi.org/forums/viewtopic.php?t=9928

www-data ALL=(pi) NOPASSWD: /var/www/app/or/script


https://serverfault.com/questions/554019/allow-www-data-to-execute-shell-script
system("whoami"); to get the username of the webserver, I use www-data in this example. Edit your sudoers file and add the following. Where user is the username it will be run under.

www-data ALL=(user) NOPASSWD: /path/to/program/or/script

Then use the following command in PHP.

system("sudo -u user /path/to/program/or/script");

This repo provide the most complete Phalcon Framework stubs which enables autocompletion in modern IDEs.

## Installing via Composer

1. Install Composer in a common location or in your project:
    ```bash
    curl -s http://getcomposer.org/installer | php
    ```

2. Create the `composer.json` file as follows:
    ```json
    {
        "require-dev": {
            "phalcon/ide-stubs": "*"
        }
    }
    ```

3. Run the composer installer:
    ```bash
    php composer.phar install
    ```

4. Setup your IDE.

## Installing via Git

1. Clone the Phalcon IDE Stubs repository in a common location.

2. Setup your IDE.

## License

Phalcon IDE Stubs is open-sourced software licensed under the New BSD License. © Phalcon Framework Team and contributors.

