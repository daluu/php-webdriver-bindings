<?php

//NOTE: for SauceLabs demo, replace WebDriver.php with WebDriverSauceLabs.php

require '../phpwebdriver/WebDriverBackedSelenium.php';

//non SauceLabs connect string
//$selenium = new WebDriverBackedSelenium('localhost',4444,'internet explorer');

//SauceLabs connect string
$selenium = new WebDriverBackedSelenium('your-username-string:your-access-key-string@ondemand.saucelabs.com',80,'firefox');

/*
This demo creates a test with some default job name in SauceLabs. To pass name to test, you'd have to modify WebDriverBackedSelenium or WebDriverBackedSeleniumTestCase to pass new additional name argument and then store value and use later and/or pass to start() method, which calls
WebDriver->connect(), and pass to that the name parameter.
*/

$selenium->start();
$selenium->setBrowserUrl("http://www.google.com");
$selenium->open('/');
$selenium->type("name=q","php-webdriver-bindings");
$selenium->click("name=btnG");

//vs for direct WebDriver command access, do something like this:
//$selenium->webdriver->findElementBy("name","q")->sendKeys(array("php-webdriver-bindings")); //$selenium->webdriver->findElementBy("name","btnG")->click();

sleep(10);
$selenium->close(); //close browser window
//when done with browser session, do a...
$selenium->stop();
?>