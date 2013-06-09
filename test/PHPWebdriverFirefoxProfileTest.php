<?php

if(is_file('../definedVars.php')) require_once '../definedVars.php';
require_once '../phpwebdriver/WebDriver.php';

/**
 * 
 * @author dluu/mangaroo
 * @version 1.0
 * @property WebDriver $webdriver
 */
class PHPWebDriverTest extends PHPUnit_Framework_TestCase {

    protected function setUp() {
        $this->webdriver = new WebDriver("localhost", 4444);
		$ffprofile = $this->webdriver->prepareBrowserProfile(FF_PROFILE_PATH);
		$this->webdriver->connect("firefox","15",array('firefox_profile' => $ffprofile));
    }

    protected function tearDown() {
        $this->webdriver->close();
    }

	public function testFirefoxProfile() {
        $this->webdriver->get(TEST_URL);
		sleep(30);
        //nothing really to test here, must verify visually that desired FF profile loaded
		//and can bring up any installed extensions that are part of the profile
    }
}

?>