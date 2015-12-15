PHP-PayPal-IPN
==============

A PHP class for handling Instant Payment Notifications (IPN) from PayPal.

Features:

* Switch between live and sandbox by setting the `use_sandbox` property.
* Supports HTTPS (SSL) transactions by default (HTTP is discontinued by PayPal as of 2016).
* Supports both cURL and fsockopen network libraries by setting the `use_curl` property (cURL is recommended).
* Verifies an HTTP 200 response status code from the PayPal server.
* Get detailed plain text reports of the entire IPN using the `getTextReport()` method for use in emails and logs to administrators.
* Throws various exceptions to differentiate between common errors in code or server configuration versus invalid IPN responses.

Requirements:

* PHP 5.3.0 or newer.
* cURL extension (if `use_curl` property is enabled).

Documentation
-------------

The source code contains PHPDoc style comments which should be used as the primary reference.

Example scripts and a sample report (as returned by the `getTextReport()` method) can be found in the [Examples](examples/) folder.

### Getting Started

This code is intended for web developers. You should understand how the IPN process works conceptually and you should understand when and why you would be using IPN. Reading the [PayPal Instant Payment Notification Guide][1] is a good place to start.

You should also have a [PayPal Sandbox Account][2] with a test buyer account and a test seller account. When logged into your sandbox account there is an IPN simulator under the "Test Tools" menu which you can used to test your IPN listener.

Once you have your sandbox account setup, you simply create a PHP script that will be your IPN listener. In that script, use the `IpnListener()` class as demonstrated in the [Examples](examples/).

An in-depth tutorial [PayPal IPN with PHP](http://www.micahcarrick.com/paypal-ipn-with-php.html) descibes how to implement an Instant Payment Notification listener script in PHP - a must-read if you had no prior experience with PayPal IPN.

[1]: https://cms.paypal.com/cms_content/US/en_US/files/developer/IPNGuide.pdf
[2]: https://developer.paypal.com

History
-------

This library was forked from [WadeShuler/PHP-PayPal-IPN](https://github.com/WadeShuler/PHP-PayPal-IPN) v2.5.2 which in turn was forked from [Quixotix/PHP-PayPal-IPN](https://github.com/Quixotix/PHP-PayPal-IPN) v2.1.0.

Below is a brief summary of their differences and reasons for forking:

1. Quixotix's version is no longer maintained and contains various critical issues.
2. WadeShuler's version was meant to address all issues of the original version, however, several breaking changes have been made unnecessarily.
3. The goal of this version is to keep the library operational by fixing any issues, while trying to retain backward compatibility with the original version.


License
-------

This work is licensed under [GPL 2.0](http://choosealicense.com/licenses/gpl-2.0/).

Copyright notice from the original author: [LICENSE](LICENSE).