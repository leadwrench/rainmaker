Rainmaker
=========

A [feedback loop (FBL)] processor for [Interspire Email Marketer],
written in PHP.

This project is a port of a Python feedback loop processor by
[Vimmaniac] PLC.

Latest release: v0.1.0.

[feedback loop (FBL)]: <https://en.wikipedia.org/wiki/Feedback_loop_(email)>
[Interspire Email Marketer]: <https://www.interspire.com/emailmarketer/>
[Vimmaniac]: <http://vimmaniac.com/feebackloop-processor-for-iem-6/>




Requirements
------------

* [PHP] 5.3+
* [Composer]

[PHP]: <http://php.net/>
[Composer]: <http://getcomposer.org/doc/00-intro.md#installation-nix>




Installation
------------

There's two main ways to use Rainmaker -- you can run it as a
stand-alone binary; or it can be integrated into another project.


#### Stand-alone Usage

This method should be the easiest way to get up and running.


1. Download the source code from [GitHub]:

        git clone https://github.com/bradfeehan/rainmaker.git
        cd rainmaker

2. Install dependencies with [Composer]:

        curl -sS https://getcomposer.org/installer | php
        php composer.phar install

3. Create a configuration file:

        cp config/config.sample.yml config/config.yml
        vim config/config.yml

4. Start the daemon using the command-line interface:

        bin/rainmaker daemon:start config/config.yml


Basic help on the command-line interface is available by running
`bin/rainmaker help`.

[GitHub]: <https://github.com/bradfeehan/rainmaker>


#### Integration into an application

The functionality of Rainmaker can also be integrated into another
piece of PHP software, using Composer.


1. Add Rainmaker as a dependency in your project's `composer.json`:

        {
            "name": "acme/my_project",
            "repositories": [
                {
                    "type": "vcs",
                    "url": "https://github.com/bradfeehan/rainmaker"
                }
            ],
            "require": {
                "bradfeehan/rainmaker": "~0.1.0"
            }
        }

2. Update Composer dependencies:

        composer update bradfeehan/rainmaker

3. Include Composer's autoloader in your application's bootstrap
   process:

        require_once 'vendor/autoload.php';


Now you should be able to access Rainmaker's PHP classes from inside
your application, and Composer will handle autoloading them.




Configuration
-------------

Rainmaker is configured using a YAML configuration file.

The configuration file to use can be specified on the command-line when
starting the daemon:

```bash
bin/rainmaker daemon:start /path/to/config.yml
```

Here's a comprehensive example of a Rainmaker configuration file:

```yaml
mailboxes:
  - name: test@example.com
    protocol: imap
    host: imap.example.com
    port: 993
    ssl: SSL
    user: test@example.com
    password: secret
logger:
  class: Monolog\Logger
  configuration:
    class: BradFeehan\Rainmaker\Logging\MonologConfigurer
```


#### `mailboxes`

A mailbox represents a place that Rainmaker will check for feedback
reports (an individual e-mail account).

Each item in the `mailboxes` array defines a mailbox. The configuration
file **must** define at least one mailbox. Each mailbox has the
following configuration parameters:

* `name`:     A friendly name for this account (used in logs)
* `protocol`: The protocol to use to access this mailbox (one of `pop`
              for POP3, or `imap` for IMAP accounts)
* `host`:     The hostname of the mail server to connect to
* `port`:     The port of the mail server to connect to
* `ssl`:      Determines whether SSL or TLS is used (one of false,
              "SSL", or "TLS")
* `user`:     The user name to authenticate to the mail server with
* `password`: The password to authenticate to the mail server with


#### `logger`

The logging in Rainmaker can be customised in the configuration file.

The value of `class` is a string that names the class to instantiate
for the main logger object used in Rainmaker. This is optional, the
default value is `Monolog\Logger`.

The `class` under `configuration` specifies a `ConfigurerInterface` to
instantiate which handles creation and configuration of the logger.
This is also optional and defaults to
`BradFeehan\Rainmaker\Logging\MonologConfigurer`.

In future, additional logging parameters may become configurable,
including the logging level.




Running Tests
-------------

To run the tests, first clone the project and install development
dependencies using Composer:

```bash
composer install --dev
```

Then you can run PHPUnit directly from within the project:

```bash
cd rainmaker
vendor/bin/phpunit
```
