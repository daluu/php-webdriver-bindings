<?php

if(is_file('../definedVars.php')) require_once '../definedVars.php';
require_once '../phpwebdriver/WebDriver.php';

/**
 * 
 * @author kolec
 * @version 1.0
 * @property WebDriver $webdriver
 */
class PHPWebDriverTest extends PHPUnit_Framework_TestCase {

    protected function setUp() {
        $this->webdriver = new WebDriver("localhost", 4444);
		$this->webdriver->connect("firefox");
    }

    protected function tearDown() {
        $this->webdriver->close();
    }

	public function testFileUpload() {

        $this->webdriver->get(TEST_URL);

        $element = $this->webdriver->findElementBy(LocatorStrategy::id, "file1");
        $this->assertNotNull($element);
		$remoteNodeLocation = $this->webdriver->sendFile(EG_FILE_DIRECTORY . EG_FILE_NAME);
        $element->sendKeys(array($remoteNodeLocation));
		//$element->sendKeys(array(EG_FILE_DIRECTORY . EG_FILE_NAME));
        $element->submit();
		sleep(1);
        $this->assertTrue($this->isTextPresent(EG_FILE_NAME));
    }
	
    public function testBackAndForward() {

        $this->webdriver->get(TEST_URL);
        sleep(1);

        $element = $this->webdriver->findElementBy(LocatorStrategy::linkText, "say hello (javascript)");
        $this->assertNotNull($element);

        $this->webdriver->get('http://www.3e.pl');
        sleep(1);

        $element = $this->webdriver->findElementBy(LocatorStrategy::linkText, "O Nas");
        $this->assertNotNull($element);

        $this->webdriver->back();
        sleep(1);

        $element = $this->webdriver->findElementBy(LocatorStrategy::linkText, "say hello (javascript)");
        $this->assertNotNull($element);

        $this->webdriver->forward();
        sleep(1);

        $element = $this->webdriver->findElementBy(LocatorStrategy::linkText, "O Nas");
        $this->assertNotNull($element);
    }

    public function testCssProperty() {

        $this->webdriver->get(TEST_URL);

        $element = $this->webdriver->findElementBy(LocatorStrategy::id, "prod_name");
        $this->assertNotNull($element);
        $element->sendKeys(array("selenium 123"));
        $this->assertEquals($element->getValue(), "selenium 123");
        $element->submit();

        $elementResult = $this->webdriver->findElementBy(LocatorStrategy::id, "result1");
        $this->assertNotNull($elementResult);

        $cssProperty = $elementResult->getCssProperty('background-color');
        $this->assertEquals($cssProperty, "#008000");
    }

    public function testElementIsDisplayedAndItsSize() {

        $this->webdriver->get(TEST_URL);

        $element = $this->webdriver->findElementBy(LocatorStrategy::id, "prod_name");
        $this->assertNotNull($element);

        $this->assertTrue($element->isDisplayed());

        $elementSize = $element->getSize();

        $this->assertNotNull($elementSize);
        $this->assertEquals(266, $elementSize->width);
        $this->assertEquals(22, $elementSize->height);
    }

    public function testElementLocations() {

        $this->webdriver->get(TEST_URL);

        $element = $this->webdriver->findElementBy(LocatorStrategy::id, "prod_name");
        $this->assertNotNull($element);

        $location = $element->getLocation();
        $this->assertNotNull($location);
        $this->assertEquals(98, $location->x);
        $this->assertEquals(8, $location->y);

        $locationInView = $element->getLocationInView();
        $this->assertNotNull($locationInView);
        $this->assertEquals(102, $locationInView->x);
        $this->assertEquals(12, $locationInView->y);
    }

    public function testIsOtherId() {

        $this->webdriver->get(TEST_URL);

        $element = $this->webdriver->findElementBy(LocatorStrategy::id, "prod_name");
        $this->assertNotNull($element);

        $result = $element->isOtherId('sel1');
        $this->assertFalse($result);
    }
	
    public function testAlerts() {
        $this->webdriver->get(TEST_URL);
        $element = $this->webdriver->findElementBy(LocatorStrategy::linkText, "say hello (javascript)");
        $this->assertNotNull($element);
        $element->click();
        $this->assertTrue($this->webdriver->getAlertText()=="hello computer !!!");
        $this->webdriver->acceptAlert();
        sleep(4);
    }

    public function testCookieSupport() {
        $this->webdriver->get(TEST_URL);
    $this->webdriver->setCookie('aaa','testvalue'); 
        $cookies = $this->webdriver->getAllCookies();
    $this->assertTrue(count($cookies)==1);
    $this->assertTrue($cookies[0]->name=='aaa');
    $this->assertTrue($cookies[0]->value=='testvalue');
    $this->webdriver->deleteCookie('aaa');
        $cookies = $this->webdriver->getAllCookies();
    $this->assertTrue(count($cookies)==0);
    }


    public function testFindOptionElementInCombobox() {
        $this->webdriver->get(TEST_URL);
        $element = $this->webdriver->findElementBy(LocatorStrategy::name, "sel1");
        $this->assertNotNull($element);
        $option3 = $element->findOptionElementByText("option 3");
        $this->assertNotNull($option3);
        $this->assertEquals($option3->getText(), "option 3");
        $this->assertFalse($option3->isSelected());
        $option3->click();
        $this->assertTrue($option3->isSelected());

        $option2 = $element->findOptionElementByValue("2");
        $this->assertNotNull($option2);
        $this->assertEquals($option2->getText(), "option 2");
        $this->assertFalse($option2->isSelected());
        $option2->click();
        $this->assertFalse($option3->isSelected());
        $this->assertTrue($option2->isSelected());
    }

    public function testExecute() {
        $this->webdriver->get(TEST_URL);
        $result = $this->webdriver->executeScript("return sayHello('unitTest')", array());
        $this->assertEquals("hello unitTest !!!", $result);
    }

    public function testScreenShot() {
        $this->webdriver->get(TEST_URL);
        $tmp_filename = "screenshot".uniqid().".png";
        //unlink($tmp_filename);
        $result = $this->webdriver->getScreenshotAndSaveToFile($tmp_filename);
        $this->assertTrue(file_exists($tmp_filename));
        $this->assertTrue(filesize($tmp_filename)>100);
        unlink($tmp_filename);
    }

    /**
     * @expectedException WebDriverException
     */
    public function testHandleError() {
        $this->webdriver->get(TEST_URL);
        $element = $this->webdriver->findElementBy(LocatorStrategy::name, "12323233233aa");
    }

    public function testFindElemenInElementAndSelections() {
        $this->webdriver->get(TEST_URL);
        $element = $this->webdriver->findElementBy(LocatorStrategy::name, "sel1");
        $this->assertNotNull($element);
        $options = $element->findElementsBy(LocatorStrategy::tagName, "option");
        $this->assertNotNull($options);
        $this->assertNotNull($options[2]);
        $this->assertEquals($options[2]->getText(), "option 3");
        $this->assertFalse($options[2]->isSelected());
        $options[2]->click();
        $this->assertTrue($options[2]->isSelected());
        $this->assertFalse($options[0]->isSelected());
    }

    public function testFindElementByXpath() {
        $this->webdriver->get(TEST_URL);
        $option3 = $this->webdriver->findElementBy(LocatorStrategy::xpath, '//select[@name="sel1"]/option[normalize-space(text())="option 3"]');
        $this->assertNotNull($option3);
        $this->assertEquals($option3->getText(), "option 3");
        $this->assertFalse($option3->isSelected());
        $option3->click();
        $this->assertTrue($option3->isSelected());
    }


    public function testFindElementByAndSubmit() {
        $this->webdriver->get(TEST_URL);
        $element = $this->webdriver->findElementBy(LocatorStrategy::id, "prod_name");
        $this->assertNotNull($element);
        $element->sendKeys(array("selenium 123"));
        $this->assertEquals($element->getValue(), "selenium 123");
        $element->clear();
        $this->assertEquals($element->getValue(), "");
        $element->sendKeys(array("selenium 123"));
        $element->submit();
        $element2 = $this->webdriver->findElementBy(LocatorStrategy::id, "result1");
        $this->assertNotNull($element2);
    }

    public function testGetPageAndUrl() {
        $this->webdriver->get(TEST_URL);
        $this->assertEquals($this->webdriver->getTitle(), "Test page");
        $this->assertEquals($this->webdriver->getCurrentUrl(), TEST_URL);
    }

    public function testGetText() {
        $this->webdriver->get(TEST_URL);
        $element = $this->webdriver->findElementBy(LocatorStrategy::name, "div1");
        $this->assertNotNull($element);
        $this->assertEquals($element->getText(), "lorem ipsum");
    }

    public function testGetName() {
        $this->webdriver->get(TEST_URL);
        $element = $this->webdriver->findElementBy(LocatorStrategy::name, "div1");
        $this->assertNotNull($element);
        $this->assertEquals($element->getName(), "div");
    }

    public function testGetPageSource() {
        $this->webdriver->get(TEST_URL);
        $src = $this->webdriver->getPageSource();
        $this->assertNotNull($src);
        $this->assertTrue(strpos($src, "<html>") == 0);
        $this->assertTrue(strpos($src, "<body>") > 0);
        $this->assertTrue(strpos($src, "div1") > 0);
    }
	
	public function testImplicitWait(){
		$this->webdriver->get("http://www.google.com");
		$this->webdriver->setImplicitWaitTimeout(5*1000);
		$starTime = time();
		try{
			$img = $this->webdriver->findElementBy(LocatorStrategy::id, "does not exist");
		}catch(NoSuchElementException $e){
			//do nothing, we wanted to test implicit wait (full timeout) on non-existant element
		}		
		$endTime = time();
		$this->assertTrue(($endTime - $starTime) >= 5 ? TRUE : FALSE);
	}
	
	public function testWindowHandling() {
        $this->webdriver->get(TEST_URL);
		$mainWinHandle = $this->webdriver->getWindowHandle();
        $element = $this->webdriver->findElementBy(LocatorStrategy::linkText, "Open new popup window to Google");
        $this->assertNotNull($element);
        $element->click();
		sleep(5); //give some arbitrary time for new window popup to open
		$winHandles = $this->webdriver->getWindowHandles();
		$this->assertEquals(2,count($winHandles));
		$this->assertEquals($mainWinHandle,$winHandles[0]);
		$this->webdriver->selectWindow($winHandles[1]);
		$this->assertNotEquals("Test page",$this->webdriver->getTitle());
		$this->webdriver->closeWindow();
		$this->webdriver->selectWindow($mainWinHandle);
		$this->assertEquals("Test page",$this->webdriver->getTitle());
    }
	
	public function testMouseOver(){
		$this->webdriver->get("http://homepage.ntlworld.com/bobosola/png_mouseover.htm");
		$img = $this->webdriver->findElementBy(LocatorStrategy::id, "img100");
		$this->assertEquals(1,preg_match("/logo_off.png$/",$img->getAttribute("src")));
		$this->webdriver->moveTo($img);
		$this->assertEquals(1,preg_match("/logo_on.png$/",$img->getAttribute("src")));
	}
	
	public function testDragAndDrop(){
		//originally, to use this test site/URL: http://jqueryui.com/droppable/
		//but too much hassle to switch iframes, so let's directly go to the source page
		$this->webdriver->get("http://jqueryui.com/resources/demos/droppable/default.html");
		$srcDrag = $this->webdriver->findElementBy(LocatorStrategy::id, "draggable");
		$targetDrop = $this->webdriver->findElementBy(LocatorStrategy::id, "droppable");
		$stateChecker = $this->webdriver->findElementBy(LocatorStrategy::cssSelector, "#droppable > p");
		$this->assertEquals("Drop here",$stateChecker->getText());
		
		$this->webdriver->moveTo($srcDrag);
		$this->webdriver->buttonDown();
		$this->webdriver->moveTo($targetDrop);
		$this->webdriver->buttonUp();
		sleep(2); //give time for UI to update for drag & drop operation
		$stateChecker = $this->webdriver->findElementBy(LocatorStrategy::cssSelector, "#droppable > p");
		$this->assertEquals("Dropped!",$stateChecker->getText());		
	}
	
	public function testDragAndDropWithOffset(){
		//originally, to use this test site/URL: http://jqueryui.com/droppable/
		//but too much hassle to switch iframes, so let's directly go to the source page
		$this->webdriver->get("http://jqueryui.com/resources/demos/droppable/default.html");
		$srcDrag = $this->webdriver->findElementBy(LocatorStrategy::id, "draggable");
		$targetDrop = $this->webdriver->findElementBy(LocatorStrategy::id, "droppable");
		$stateChecker = $this->webdriver->findElementBy(LocatorStrategy::cssSelector, "#droppable > p");
		$this->assertEquals("Drop here",$stateChecker->getText());
		
		$this->webdriver->moveTo($srcDrag,1,5);
		$this->webdriver->buttonDown();
		$this->webdriver->moveTo($targetDrop,10,20);
		$this->webdriver->buttonUp();
		sleep(2); //give time for UI to update for drag & drop operation
		$stateChecker = $this->webdriver->findElementBy(LocatorStrategy::cssSelector, "#droppable > p");
		$this->assertEquals("Dropped!",$stateChecker->getText());		
	}
	
	public function testWindowMaximize(){
		$this->webdriver->get("http://www.google.com");
		/* TODO - maybe get size of current window before maximize,
		 * then maximize window, then get size and verify size is
		 * larger. But if window already maximized, that's a
		 * different case. And can't compare with fixed size
		 * since screen resolution is different across systems.
		 * For now, just visually verify when running unit test
		 * and confirm no failure/exception from test result.
		 */
		$this->webdriver->windowMaximize();		
	}
	
	private function isTextPresent($text) {

        $waiting_time = 0.5;
        $max_waiting_time = 4;

        $found = false;
        $i = 0;
        do {
            $html = $this->webdriver->getPageSource();
            if (is_string($html)) {
                $found = !(strpos($html, $text) === false);
            }
            if (!$found) {
                sleep($waiting_time);
                $i += $waiting_time;
            }
        } while (!$found && $i <= $max_waiting_time);
        return $found;
    }

}

?>