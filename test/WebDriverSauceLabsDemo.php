<?php

//NOTE: for SauceLabs demo, replace WebDriver.php with WebDriverSauceLabs.php

require '../phpwebdriver/WebDriverSauceLabs.php';

//SauceLabs connect string
$driver = new WebDriver('your-username-string:your-access-key-string@ondemand.saucelabs.com',80);

$driver->connect('firefox','','My SauceLabs demo test');
$driver->get('http://www.google.com');
$driver->findElementBy("name","q")->sendKeys(array("php-webdriver-bindings"));
$driver->findElementBy("name","btnG")->click();

sleep(10);
//close browser window
$driver->closeWindow();
//when done with browser session, do a...
$driver->close();
?>