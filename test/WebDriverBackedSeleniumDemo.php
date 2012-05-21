<?php
require '../phpwebdriver/WebDriverBackedSelenium.php';

$selenium = new WebDriverBackedSelenium('localhost',4444,'firefox');
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