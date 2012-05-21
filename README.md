php-webdriver-bindings
======================

Selenium Webdriver bindings for PHP (fork of original on Google Code: http://code.google.com/p/php-webdriver-bindings/)

NOTE: I don't intend to update this forked project much in the near term as I don't use PHP with WebDriver lately. This fork is primarily to offer the code changes I made to the original, particularly a WebDriverBackedSelenium class (and TestCase class for PHPUnit), some feature additions, and some SauceLabs support, that are not available in the original project (yet). And submitting patches to the project was less effective than forking it, in terms of further updates to patches (if I make any).

NOTE: Consider the changes brought by this fork as beta quality. It's not ready for production use. Explore at your own risk. Feedback, patches, and pull requests are welcome.

-----------------------------
Readme from original project:
-----------------------------

PHPWebdriver 

This is library for writing functional Selenium 2 Webdriver tests in Php.
It works using JsonWireProtocol to comunicate with Selenium server.


To see how it works see test directory.