Rainmaker
=========

A [feedback loop (FBL)] processor for [Interspire Email Marketer],
written in PHP.

This project is a port of a Python feedback loop processor by
[Vimmaniac] PLC.

Currently a work-in-progress.

[feedback loop (FBL)]: <https://en.wikipedia.org/wiki/Feedback_loop_(email)>
[Interspire Email Marketer]: <https://www.interspire.com/emailmarketer/>
[Vimmaniac]: <http://vimmaniac.com/feebackloop-processor-for-iem-6/>



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
