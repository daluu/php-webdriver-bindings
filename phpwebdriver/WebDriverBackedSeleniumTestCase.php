<?php

/*
  Copyright 2011 3e software house & interactive agency

  Licensed under the Apache License, Version 2.0 (the "License");
  you may not use this file except in compliance with the License.
  You may obtain a copy of the License at

  http://www.apache.org/licenses/LICENSE-2.0

  Unless required by applicable law or agreed to in writing, software
  distributed under the License is distributed on an "AS IS" BASIS,
  WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
  See the License for the specific language governing permissions and
  limitations under the License.
 */

/**
 *  Modified from original CWebDriverTestCase.php version...
 *  
 *  NOTE: this is a hack job put together enhancing
 *  CWebDriverTestCase to conform to Selenium RC API (giving you
 *  a WebDriverBackedSeleniumTestCase, plus some functional & 
 *  various addons to interface with PHPUnit similar
 *  to how the Selenium (RC) extension works with PHPUnit, though
 *  of course there is less "native" integration here than with
 *  the Selenium RC version. It has not been thoroughly tested.
 *  Use at your own risk. Patches are welcome. With more work 
 *  fine tuning the integration between WebDriver interface and 
 *  PHPUnit, things will work better. Or you can opt for 
 *  Selenium2TestCase from PHP Selenium extension project, though 
 *  that isn't meant for WebDriverBackedSelenium.
 *  
 *  Base class for functional tests using WebDriver with PHPUnit.
 *  It provides the same interface as PHPUnit's SeleniumTestCase,
 *  intending to allow users to more easily port existing PHP 
 *  Selenium tests to Web Driver with minimal or no code changes. 
 *  And/or export Selenium IDE tests to PHP w/o much rework for 
 *  WebDriver in PHP. Basically reusing Selenium RC API and not
 *  need to switch to WebDriver API akin to WebDriverBackedSelenium.
 *  
 *  NOTE: that this interface does not implement all the Selenium
 *  RC API methods and location strategies, though their method
 *  signatures are in place. If any of these get called, a
 *  special exception is thrown to alert the user that their
 *  test will need rework to run in WebDriver with PHP. See code
 *  below for details about the exception message returned.
 *  
 *  @author kolec
 *  
 */

//references for PHPUnit
require_once 'PHPUnit/Framework.php';
require_once 'PHPUnit/Util/Log/Database.php';
require_once 'PHPUnit/Util/Filter.php';
require_once 'PHPUnit/Util/Test.php';
require_once 'PHPUnit/Util/XML.php';
PHPUnit_Util_Filter::addFileToFilter(__FILE__, 'PHPUNIT');

//references for WebDriver
require_once 'WebDriver.php';

class WebDriverBackedSeleniumTestCase extends PHPUnit_Framework_TestCase {

    public $webdriver;
    private $baseUrl;    
    private $seleniumSpeed; //in ms
    private $confirmationChoice;
    private $promptAnswer;
    private $seleniumTimeout;
    protected $waiting_time;
    protected $max_waiting_time;
    
    //PHPUnit & PHPUnit-Selenium specific vars    
    //public static $browsers = array();
    //protected $drivers = array();
	protected $browserName;
	//protected $testId;
	//protected $captureScreenshotOnFailure = FALSE;
	//protected $screenshotPath = '';
	//protected $screenshotUrl = '';
	//public $snapshotDir = '';

	private $browser = '';
    private $host = 'localhost';
    private $port = 4444;
    protected $name = '';

    //***Start PHPUnit specific methods***
	//public function __construct($name = NULL, array $data = array(), $dataName = '', array $browser = array()) {
    public function __construct($name = NULL, array $data = array(), $dataName = '') {
        parent::__construct($name, $data, $dataName);
              
        $this->baseUrl = "";        
        $this->seleniumSpeed = 0; //default no delay
        $this->confirmationChoice = "ok";
        $this->promptAnswer = "";
        $this->seleniumTimeout = 30000; //default of 30 sec
        $this->waiting_time = 0.5;
        $this->max_waiting_time = 4;
        
        //$this->testId = md5(uniqid(rand(), TRUE));
        print "\nClass name = ".get_class()."\n";
        $class = new ReflectionClass(get_class());
        $staticProperties = $class->getStaticProperties();
        if (!empty($staticProperties['browsers']))
        	$this->getDriver($staticProperties['browsers'][0]);
        else{
        	$browsers = array(
            		'browser' => 'firefox',
            		'host'    => 'localhost',
            		'port'    => 4444,
            		'timeout' => 30000,
        	);
        	$this->getDriver($browsers);
        }       	        
    }
    
	protected function getDriver(array $browser) {
        if (isset($browser['name'])) {
            if (!is_string($browser['name'])) {
                throw new InvalidArgumentException(
                'Array element "name" is no string.'
                );
            }
        } else {
            $browser['name'] = '';
        }

        if (isset($browser['browser'])) {
            if (!is_string($browser['browser'])) {
                throw new InvalidArgumentException(
                'Array element "browser" is no string.'
                );
            }
        } else {
            $browser['browser'] = '';
        }

        if (isset($browser['host'])) {
            if (!is_string($browser['host'])) {
                throw new InvalidArgumentException(
                'Array element "host" is no string.'
                );
            }
        } else {
            $browser['host'] = 'localhost';
        }

        if (isset($browser['port'])) {
            if (!is_int($browser['port'])) {
                throw new InvalidArgumentException(
                'Array element "port" is no integer.'
                );
            }
        } else {
            $browser['port'] = 4444;
        }

        if (isset($browser['timeout'])) {
            if (!is_int($browser['timeout'])) {
                throw new InvalidArgumentException(
                'Array element "timeout" is no integer.'
                );
            }
        } else {
            $browser['timeout'] = 30000;
        }

        if (isset($browser['httpTimeout'])) {
            if (!is_int($browser['httpTimeout'])) {
                throw new InvalidArgumentException(
                'Array element "httpTimeout" is no integer.'
                );
            }
        } else {
            $browser['httpTimeout'] = 45;
        }

        //$driver = new PHPUnit_Extensions_SeleniumTestCase_Driver;      
        $this->setName($browser['name']);
        $this->setBrowser($browser['browser']);
        $this->setHost($browser['host']);
        $this->setPort($browser['port']);
        $this->setTimeout($browser['timeout']);
        $this->setHttpTimeout($browser['httpTimeout']);
        //$driver->setTestCase($this);
        //$driver->setTestId($this->testId);
        
        $this->webdriver = new WebDriver( $this->host, $this->port );
        $this->webdriver->setImplicitWaitTimeout($this->seleniumTimeout);
        $this->webdriver->connect($this->browser);
        
        //$this->drivers[] = $this->webdriver;
        //return $this->webdriver;
        
        //print "\ndebug browser = ".$this->browser."\n";
        //var_dump($this->webdriver);
        //print "\n".$this->webdriver->getSpeed()."\n";
    }
    
    //***Start PHPUnit SeleniumTestCase driver methods***
    public function setName($name){
    	$this->name = $name;
    }
    
    public function setBrowser($browser){
    	$this->browser = $browser;
    }
    
    public function setHost($host){
    	$this->host = $host;
    }
    
    public function setPort($port){
    	$this->port = $port;
    }
    
    //we already have setTimeout as WebDriverBackedSelenium API method below
    
    public function setHttpTimeout($timeout){
    	//do nothing, we ignore...
    }
    //***End PHPUnit SeleniumTestCase driver methods***
    
    public function __call( $name, $arguments ) {
        if( method_exists( $this->webdriver, $name ) ) {
            return call_user_func_array( array( $this->webdriver, $name ), $arguments );
        }
    }	
        
    //setUp ~ class constructor
	//protected function setUp( $host="localhost", $port="4444", $browser="firefox" ) {
    protected function setUp() {
        parent::setUp();
        //$this->webdriver = new WebDriver( $host, $port );
        //$this->webdriver->connect( $browser );
    }

    protected function tearDown() {
    	/** generate screenshot if any test has failed */
    	/*
    	if( $this->hasFailed() && $this->snapshotDir != '' ) {
            $date = get_class."_screenshot_".date('Y-m-d-H-i-s').".png";
            $this->webdriver->getScreenshotAndSaveToFile( $date );
        }
        */
        $this->webdriver->close();
    }
    //***End PHPUnit specific methods***
    
    public function refresh(){
    	print "\n".__FUNCTION__.":\n"; //log commands called like SeleniumTestCase
    	$this->seleniumSpeedDelay();
        $this->webdriver->refresh();        
    }

    public function back(){
    	print "\n".__FUNCTION__.":\n"; //log commands called like SeleniumTestCase
    	$this->seleniumSpeedDelay();
        $this->webdriver->back();
    }

    public function forward(){
    	print "\n".__FUNCTION__.":\n"; //log commands called like SeleniumTestCase
    	$this->seleniumSpeedDelay();
        $this->webdriver->forward();
    }
    
    private function parseLocator($locator){
    	$strategy = null;
    	$locatorValue = null;
    	
    	switch ($locator) {
    		case $this->startsWith($locator,"identifier="):
    			$strategy = LocatorStrategy::id;
    			$locatorValue = substr($locator,strlen("identifier="));
    			break;
    		case $this->startsWith($locator,"id="):
    			$strategy = LocatorStrategy::id;
    			$locatorValue = substr($locator,strlen("id="));
    			break;
    		case $this->startsWith($locator,"name="):
    			$strategy = LocatorStrategy::name;
    			$locatorValue = substr($locator,strlen("name="));
                break;            
            case $this->startsWith($locator,"link="):
    			$strategy = LocatorStrategy::linkText;
    			$locatorValue = substr($locator,strlen("link="));
                break;
    		case $this->startsWith($locator,"//"):
    			$strategy = LocatorStrategy::xpath;
    			$locatorValue = $locator;
    			break;
    		case $this->startsWith($locator,"xpath="):
    			$strategy = LocatorStrategy::xpath;
    			$locatorValue = substr($locator,strlen("xpath="));
    			break;
    		case $this->startsWith($locator,"css="):
    			$strategy = LocatorStrategy::cssSelector;
    			$locatorValue = substr($locator,strlen("css="));
    			break;
    		case $this->startsWith($locator,"dom="):
    			throw new Exception("\nSelenium RC DOM locator strategy not implemented in ".get_class($this).".\n");
    			break;
    		case $this->startsWith($locator,"document."):
    			throw new Exception("\nSelenium RC DOM locator strategy not implemented in ".get_class($this).".\n");
    			break;
    		case $this->startsWith($locator,"ui="):
    			throw new Exception("\nSelenium RC UI specifier string / UI-Element locator strategy not implemented in ".get_class($this).".\n");
    			break;    		
            default:
            	$strategy = LocatorStrategy::id;
            	$locatorValue = $locator;
    	}    	
    	return $this->getElement($strategy,$locatorValue);
    }
    
	private function parseLocatorForElements($locator){
    	$strategy = null;
    	$locatorValue = null;
    	
    	switch ($locator) {
    		case $this->startsWith($locator,"identifier="):
    			$strategy = LocatorStrategy::id;
    			$locatorValue = substr($locator,strlen("identifier="));
    			break;
    		case $this->startsWith($locator,"id="):
    			$strategy = LocatorStrategy::id;
    			$locatorValue = substr($locator,strlen("id="));
    			break;
    		case $this->startsWith($locator,"name="):
    			$strategy = LocatorStrategy::name;
    			$locatorValue = substr($locator,strlen("name="));
                break;            
            case $this->startsWith($locator,"link="):
    			$strategy = LocatorStrategy::linkText;
    			$locatorValue = substr($locator,strlen("link="));
                break;
    		case $this->startsWith($locator,"//"):
    			$strategy = LocatorStrategy::xpath;
    			$locatorValue = $locator;
    			break;
    		case $this->startsWith($locator,"xpath="):
    			$strategy = LocatorStrategy::xpath;
    			$locatorValue = substr($locator,strlen("xpath="));
    			break;
    		case $this->startsWith($locator,"css="):
    			$strategy = LocatorStrategy::cssSelector;
    			$locatorValue = substr($locator,strlen("css="));
    			break;
    		case $this->startsWith($locator,"dom="):
    			throw new Exception("\nSelenium RC DOM locator strategy not implemented in ".get_class($this).".\n");
    			break;
    		case $this->startsWith($locator,"document."):
    			throw new Exception("\nSelenium RC DOM locator strategy not implemented in ".get_class($this).".\n");
    			break;
    		case $this->startsWith($locator,"ui="):
    			throw new Exception("\nSelenium RC UI specifier string / UI-Element locator strategy not implemented in ".get_class($this).".\n");
    			break;    		
            default:
            	$strategy = LocatorStrategy::id;
            	$locatorValue = $locator;
    	}    	
    	return $this->getElements($strategy,$locatorValue);
    }
    
    public function focusFrame($frameId){
    	print "\n".__FUNCTION__.": ".$frameId."\n"; //log commands called like SeleniumTestCase
    	$this->seleniumSpeedDelay();
        $this->webdriver->focusFrame($frameId);
    }

    public function selectFrame($frameId){
    	print "\n".__FUNCTION__.": ".$frameId."\n"; //log commands called like SeleniumTestCase
    	$this->seleniumSpeedDelay();
        $this->webdriver->focusFrame($frameId);
    }
    
    public function setBrowserUrl( $url ) {
    	print "\n".__FUNCTION__.": ".$url."\n"; //log commands called like SeleniumTestCase    	
        $this->baseUrl = $url;
    }

    public function open($url) {
    	print "\n".__FUNCTION__.": ".$url."\n"; //log commands called like SeleniumTestCase
    	$this->seleniumSpeedDelay();
        if($this->startsWith($url,"/")) $urlToOpen = $this->baseUrl . $url;
    	else $urlToOpen = $url;
        $this->webdriver->get( $urlToOpen );
        $this->waitForPageToLoad();
    }

    public function getBodyText() {
    	print "\n".__FUNCTION__.":\n"; //log commands called like SeleniumTestCase
    	$this->seleniumSpeedDelay();
        $html = $this->webdriver->getPageSource();
        $body = preg_replace( "/.*<body[^>]*>|<\/body>.*/si", "", $html );
        return $body;
    }

    public function isTextPresent( $text ) {
    	print "\n".__FUNCTION__.": ".$text."\n"; //log commands called like SeleniumTestCase
    	$this->seleniumSpeedDelay();
        
    	//this may be better solution from
    	//http://groups.google.com/group/selenium-users/browse_thread/thread/8d15fb3a0bb95992
        //return $this->isElementPresent("//*[contains(.,'".$text."')]");
        
        // original code from CWebDriverTestCase
    	$found = false;
        $i = 0;
        do {
            $html = $this->webdriver->getPageSource();
            if( is_string( $html ) ) {
                $found = !(strpos( $html, $text ) === false);
            }
            if( !$found ) {
                sleep( $this->waiting_time );
                $i += $this->waiting_time;
            }
        } while( !$found && $i <= $this->max_waiting_time );
        return $found;
        //
    }

    //public function getAttribute( $xpath ) {
    public function getAttribute( $attributeLocator ) {
    	print "\n".__FUNCTION__.": ".$attributeLocator."\n"; //log commands called like SeleniumTestCase
    	$this->seleniumSpeedDelay();
        list($locator,$attribute) = explode("@",$attributeLocator);
    	return $this->parseLocator($locator)->getAttribute($attribute);
    	/* less desirable method to get attribute
    	 * we want it to work for all locators not just XPath
    	$body = $this->getBodyText();
        $xml = new SimpleXMLElement( $body );
        $nodes = $xml->xpath( "$xpath" );
        return $nodes[0][0];
        */
    }

    public function type($locator,$value) {
    	print "\n".__FUNCTION__.": ".$locator." : ".$value."\n"; //log commands called like SeleniumTestCase
    	$this->seleniumSpeedDelay();
        $element = $this->parseLocator($locator);
        if( isset( $element ) ) {
            ///usleep(100*1000);
            $element->sendKeys( array( $value ) );
            //usleep(500*1000);
        }
    }

    public function clear( $locator ) {
    	print "\n".__FUNCTION__.": ".$locator."\n"; //log commands called like SeleniumTestCase
    	$this->seleniumSpeedDelay();
    	$element = $this->parseLocator($locator);
        if( isset($element) ) {
            $element->clear();
        }
    }

    public function submit( $locator ) {
    	print "\n".__FUNCTION__.": ".$locator."\n"; //log commands called like SeleniumTestCase
    	$this->seleniumSpeedDelay();
    	$element = $this->parseLocator($locator);
        if( isset( $element ) ) {
            $element->submit();
            usleep( 500 * 1000 );
        }
    }

    public function click($locator){
    	print "\n".__FUNCTION__.": ".$locator."\n"; //log commands called like SeleniumTestCase
    	$this->seleniumSpeedDelay();   	
        $element = $this->parseLocator($locator);
        //if( isset( $element ) ) {
            $element->click();
       //     usleep( 500 * 1000 );
        //}
    }

    public function close() {
    	print "\n".__FUNCTION__.":\n"; //log commands called like SeleniumTestCase
    	$this->seleniumSpeedDelay();
        $this->webdriver->closeWindow(); //not $this->webdriver->close(); to close browser session
    }

    /**
        select item (option) in combobox
        @param $select_id   id of SELECT element
        @param @option_text option text to select
    */
    public function select( $selectLocator, $optionLocator ) {
    	print "\n".__FUNCTION__.": ".$selectLocator." : ".$optionLocator."\n"; //log commands called like SeleniumTestCase
    	$this->seleniumSpeedDelay();
    	$element = $this->parseLocator($selectLocator);
        $option = $element->findOptionElementByText( $optionLocator );
        $option->click();
    }

    private function getElement( $strategy, $name ) {    	
        $i = 0;
        do {
            try {
                $element = $this->webdriver->findElementBy( $strategy, $name );
            } catch( NoSuchElementException $e ) {
                print_r( "\nWaiting for \"" . $name . "\" element to appear...\n" );
                sleep( $this->waiting_time );
                $i += $this->waiting_time;
            }
        } while( !isset( $element ) && $i <= $this->max_waiting_time );
        if( !isset( $element ) )
            throw new Exception("Element has not appeared after " . $this->max_waiting_time . " seconds.");
        return $element;
    }
    
	private function getElements( $strategy, $name ) {    	
        $i = 0;
        do {
            try {
                $elements = $this->webdriver->findElementsBy( $strategy, $name );
            } catch( NoSuchElementException $e ) {
                print_r( "\nWaiting for \"" . $name . "\" elements to appear...\n" );
                sleep( $this->waiting_time );
                $i += $this->waiting_time;
            }
        } while( !isset( $elements ) && $i <= $this->max_waiting_time );
        if( !isset( $elements ) )
            throw new Exception("Elements has not appeared after " . $this->max_waiting_time . " seconds.");
        return $elements;
    }
    
	private function startsWith($haystack, $needle){
    	$length = strlen($needle);
    	return (substr($haystack, 0, $length) === $needle);
	}
    
    /* The following methods below were added to the original class implementation
     * to fully implement the SeleniumTestCase / Selenium RC.
     * 
     * Class methods derived from Selenium command set and RC (Java) API, 
     * refer to these
     * http://release.seleniumhq.org/selenium-core/1.0.1/reference.html
     * http://release.seleniumhq.org/selenium-remote-control/1.0-beta-2/doc/java/
     */
    
    public function addLocationStrategy($strategyName,$functionDefinition){
    	throw new Exception("\nSelenium RC method \"".__FUNCTION__."\" not yet implemented in ".get_class($this).".\n");
    }
    
    public function addScript($scriptContent,$scriptTagId){
    	throw new Exception("\nSelenium RC method \"".__FUNCTION__."\" not yet implemented in ".get_class($this).".\n");
    }
    
    public function addSelection($locator,$optionLocator){
    	throw new Exception("\nSelenium RC method \"".__FUNCTION__."\" not yet implemented in ".get_class($this).".\n");
    }
    
    public function allowNativeXpath($allow=true){
    	//throw new Exception("\nSelenium RC method \"".__FUNCTION__."\" not yet implemented in ".get_class($this).".\n");
    	//we'll just use whatever the default XPath support is for WebDriver for given browser, whether native or not
    	print "\nSelenium RC method \"".__FUNCTION__."\" not implemented. See http://code.google.com/p/selenium/wiki/XpathInWebDriver for reference.\n";
    }
    
    public function altKeyDown(){
    	throw new Exception("\nSelenium RC method \"".__FUNCTION__."\" not yet implemented in ".get_class($this).".\n");
    }
    
	public function altKeyUp(){
    	throw new Exception("\nSelenium RC method \"".__FUNCTION__."\" not yet implemented in ".get_class($this).".\n");
    }
    
	public function answerOnNextPrompt($answer){
		print "\n".__FUNCTION__.": ".$answer."\n"; //log commands called like SeleniumTestCase
		$this->seleniumSpeedDelay();    	
    	$this->promptAnswer = $answer;
    }
    
    public function assignId($locator,$identifier){
    	throw new Exception("\nSelenium RC method \"".__FUNCTION__."\" not yet implemented in ".get_class($this).".\n");
    }
      
    public function attachFile($fieldLocator,$fileLocator){
    	print "\n".__FUNCTION__.": ".$fieldLocator." : ".$fileLocator."\n"; //log commands called like SeleniumTestCase
    	$this->seleniumSpeedDelay();
    	//for this, we'll just simply type the file locator info to the input field
    	//and assume will work for file upload with the browser
    	$this->type($fieldLocator,$fileLocator);
    }
    
    public function captureEntirePageScreenshot($filename,$kwargs=''){
    	print "\n".__FUNCTION__.": ".$filename." : ".$kwargs."\n"; //log commands called like SeleniumTestCase
    	$this->seleniumSpeedDelay();
    	//we ignore the kwargs for Web Driver
    	$this->webdriver->getScreenshotAndSaveToFile($filename);
    }
    
    public function captureEntirePageScreenshotToString($kwargs=''){
    	print "\n".__FUNCTION__.": ".$kwargs."\n"; //log commands called like SeleniumTestCase
    	$this->seleniumSpeedDelay();
    	//we ignore the kwargs for Web Driver
    	return $this->webdriver->getScreenshot();
    }
    
    public function captureScreenshot($filename){
    	print "\n".__FUNCTION__.": ".$filename."\n"; //log commands called like SeleniumTestCase
    	$this->seleniumSpeedDelay();
    	$this->webdriver->getScreenshotAndSaveToFile($filename);
    }
    
    public function captureScreenshotToString(){
    	print "\n".__FUNCTION__.":\n"; //log commands called like SeleniumTestCase
    	$this->seleniumSpeedDelay();
    	return $this->webdriver->getScreenshot();
    }
    
    public function check($locator){
    	print "\n".__FUNCTION__.": ".$locator."\n"; //log commands called like SeleniumTestCase
    	$this->seleniumSpeedDelay();
    	if(!$this->isChecked($locator)){
    		$this->click($locator);
    	}
    }

    public function chooseCancelOnNextConfirmation(){
    	print "\n".__FUNCTION__.":\n"; //log commands called like SeleniumTestCase
    	$this->seleniumSpeedDelay();
    	$this->confirmationChoice = "cancel";
    }
    
    public function chooseOkOnNextConfirmation(){
    	print "\n".__FUNCTION__.":\n"; //log commands called like SeleniumTestCase
    	$this->seleniumSpeedDelay();
    	$this->confirmationChoice = "ok";
    }
    
    public function clickAt($locator,$coordString=''){
    	print "\n".__FUNCTION__.": ".$locator." : ".$coordString."\n"; //log commands called like SeleniumTestCase
    	$this->seleniumSpeedDelay();
    	//ignore coordinates if supplied, just click...
    	$this->click($locator);
    }
    
    public function contextMenu($locator){
    	throw new Exception("\nSelenium RC method \"".__FUNCTION__."\" not yet implemented in ".get_class($this).".\n");
    }
    
	public function contextMenuAt($locator,$coordString){
    	throw new Exception("\nSelenium RC method \"".__FUNCTION__."\" not yet implemented in ".get_class($this).".\n");
    }
    
    public function controlKeyDown(){
    	throw new Exception("\nSelenium RC method \"".__FUNCTION__."\" not yet implemented in ".get_class($this).".\n");
    }
    
	public function controlKeyUp(){
    	throw new Exception("\nSelenium RC method \"".__FUNCTION__."\" not yet implemented in ".get_class($this).".\n");
    }
    
    public function createCookie($nameValuePair,$optionsString=''){
    	print "\n".__FUNCTION__.": ".$nameValuePair." : ".$optionsString."\n"; //log commands called like SeleniumTestCase
		$this->seleniumSpeedDelay();
    	list($name,$value) = explode("=",$nameValuePair);

    	//set default cookie options
    	$cookie_path='/';
    	$domain='';
    	$expiry='';
    	//set cookie options if given...
    	if($optionsString != ''){
    		$pathFound = preg_match('@path=(/.+/)@',$optionsString,$pathMatches);
    		if($pathFound) $cookie_path = $pathMatches[1];
    		$domainFound = preg_match('/domain=(.+)/',$optionsString,$domainMatches);
    		if($domainFound) $domain = $domainMatches[1];
    		$expiryFound = preg_match('/max_age=(\d+)/',$optionsString,$expiryMatches);
    		if($expiryFound) $expiry = $expiryMatches[1];
    	}
    	$this->webdriver->setCookie($name, $value, $cookie_path, $domain, $secure=false, $expiry);
    }
    
    public function deleteAllVisibleCookies(){
    	print "\n".__FUNCTION__.":\n"; //log commands called like SeleniumTestCase
    	$this->seleniumSpeedDelay();
    	$this->webdriver->deleteAllCookies();
    }
    
    public function deleteCookie($name,$optionsString=''){
    	print "\n".__FUNCTION__.": ".$name." : ".$optionsString."\n"; //log commands called like SeleniumTestCase
    	$this->seleniumSpeedDelay();
    	//WebDriver doesn't use the optionString from Selenium RC, so we ignore.
    	$this->webdriver->deleteCookie($name);    	
    }
    
    public function deselectPopUp(){
    	print "\n".__FUNCTION__.":\n"; //log commands called like SeleniumTestCase
    	$this->seleniumSpeedDelay();
    	$this->selectWindow();
    }
    
	public function doubleClick($locator){
    	throw new Exception("\nSelenium RC method \"".__FUNCTION__."\" not yet implemented in ".get_class($this).".\n");
    }

	public function doubleClickAt($locator,$coordString=''){
    	throw new Exception("\nSelenium RC method \"".__FUNCTION__."\" not yet implemented in ".get_class($this).".\n");
    }
    
	public function dragAndDrop($locator,$movementsString){
    	//throw new Exception("\nSelenium RC method \"".__FUNCTION__."\" not yet implemented in ".get_class($this).".\n");
	$offsets = explode(',',$movementsString); //as "x,y" like "+70,-300"
	$this->webdriver->moveTo($this->parseLocator($locator));
	$this->webdriver->buttonDown(); //drag
	$this->webdriver->moveTo($this->parseLocator($locator),$offsets[0],$offsets[1]);
	$this->webdriver->buttonUp(); //drop
    }

	public function dragAndDropToObject($locatorOfObjectToBeDragged,$locatorOfDragDestinationObject){
    	//throw new Exception("\nSelenium RC method \"".__FUNCTION__."\" not yet implemented in ".get_class($this).".\n");
	$this->webdriver->moveTo($this->parseLocator($locatorOfObjectToBeDragged));
	$this->webdriver->buttonDown(); //drag
	$this->webdriver->moveTo($this->parseLocator($locatorOfDragDestinationObject));
	$this->webdriver->buttonUp(); //drop
    }
    
	public function dragDrop($locator,$movementsString){
    	throw new Exception("\nSelenium RC method \"".__FUNCTION__."\" not yet implemented in ".get_class($this).".\n");
    }
    
    public function fireEvent($locator,$eventName){
    	//Explicitly simulate an event, to trigger the corresponding "onevent" handler.
    	//eventName - the event name, e.g. "focus" or "blur"
    	//todo
    	throw new Exception("\nSelenium RC method \"".__FUNCTION__."\" not yet implemented in ".get_class($this).".\n");
    }
    
    public function focus($locator){
    	print "\n".__FUNCTION__.": ".$locator."\n"; //log commands called like SeleniumTestCase
    	$this->seleniumSpeedDelay();
    	$element = $this->parseLocator($locator);
    	$element->click(); //these commands will force focus to element
    	$element->clear(); //and not change state of element
    }
    
    public function getAlert(){
    	print "\n".__FUNCTION__.":\n"; //log commands called like SeleniumTestCase
    	$this->seleniumSpeedDelay();
    	$alertText = $this->webdriver->getAlertText();
    	$this->webdriver->acceptAlert();
    	return $alertText;
    }
    
    public function getAllButtons(){
    	print "\n".__FUNCTION__.":\n"; //log commands called like SeleniumTestCase
    	$this->seleniumSpeedDelay();
    	$buttons1 = $this->webdriver->findElementsBy(LocatorStrategy::xpath,"//input[@type='button']");
    	$buttons2 = $this->webdriver->findElementsBy(LocatorStrategy::xpath,"//input[@type='image']");
    	$buttons3 = $this->webdriver->findElementsBy(LocatorStrategy::xpath,"//input[@type='submit']");
    	$buttons4 = $this->webdriver->findElementsBy(LocatorStrategy::xpath,"//input[@type='reset']");
    	$buttonIds = array();
        foreach ($buttons1 as $button) {
            $buttonIds[] = $button->getAttribute("id");
        }
    	foreach ($buttons2 as $button) {
            $buttonIds[] = $button->getAttribute("id");
        }
    	foreach ($buttons3 as $button) {
            $buttonIds[] = $button->getAttribute("id");
        }
    	foreach ($buttons4 as $button) {
            $buttonIds[] = $button->getAttribute("id");
        }
        return $buttonIds;
    }
    
    public function getAllFields(){
    	print "\n".__FUNCTION__.":\n"; //log commands called like SeleniumTestCase
    	$this->seleniumSpeedDelay();
    	$inputs = $this->webdriver->findElementsBy(LocatorStrategy::tagName,"input");    	
    	$inputIds = array();
        foreach ($inputs as $input) {
            $inputIds[] = $input->getAttribute("id");
        }
        return $inputIds;
    }
    
    public function getAllLinks(){
    	print "\n".__FUNCTION__.":\n"; //log commands called like SeleniumTestCase
    	$this->seleniumSpeedDelay();
    	$links = $this->webdriver->findElementsBy(LocatorStrategy::tagName,"a");    	
    	$linkIds = array();
        foreach ($links as $link) {
            $linkIds[] = $link->getAttribute("id");
        }
        return $linkIds;
    }
    
    public function getAllWindowIds(){
    	print "\n".__FUNCTION__.":\n"; //log commands called like SeleniumTestCase
    	$this->seleniumSpeedDelay();
    	//we will use the Web Driver window handle "IDs" as a substitute for Selenium RC window IDs
    	return $this->webdriver->getWindowHandles();
    }
    
    public function getAllWindowNames(){
    	print "\n".__FUNCTION__.":\n"; //log commands called like SeleniumTestCase
    	$this->seleniumSpeedDelay();
    	//not an ideal solution but a hack, some windows will have no "name" associated with it
    	$currHdl = $this->webdriver->getWindowHandle();
    	$hdls = $this->webdriver->getWindowHandles();
    	$names = array();
    	foreach ($hdls as $hdl) {
    		$this->webdriver->selectWindow($hdl); //switch to window
    		$names[] = $this->webdriver->execute("return window.name;",array()); //get its name
    	}
    	$this->webdriver->selectWindow($currHdl); //go back to current window
    	return $names;
    }
    
    public function getAllWindowTitles(){
    	print "\n".__FUNCTION__.":\n"; //log commands called like SeleniumTestCase
    	$this->seleniumSpeedDelay();
    	//not an ideal solution but a hack
    	//also refer to http://groups.google.com/group/webdriver/browse_thread/thread/ac88dcc6f4571bb0
    	$currHdl = $this->webdriver->getWindowHandle();
    	$hdls = $this->webdriver->getWindowHandles();
    	$titles = array();
    	foreach ($hdls as $hdl) {
    		$this->webdriver->selectWindow($hdl); //switch to window
    		$titles[] = $this->webdriver->getTitle(); //get its title
    	}
    	$this->webdriver->selectWindow($currHdl); //go back to current window
    	return $titles;
    }
    
	public function getAttributeFromAllWindows($attributeName){
		print "\n".__FUNCTION__.": ".$attributeName."\n"; //log commands called like SeleniumTestCase
    	$this->seleniumSpeedDelay();
		//not an ideal solution but a hack
    	//I'm also interpreting attribute as a window attribute, not an element attribute
    	//for an element on page within window. Selenium API docs don't make this clear.
    	$currHdl = $this->webdriver->getWindowHandle();
    	$hdls = $this->webdriver->getWindowHandles();
    	$attributes = array();
    	foreach ($hdls as $hdl) {
    		$this->webdriver->selectWindow($hdl); //switch to window, then get window attribute
    		$attributes[] = $this->webdriver->execute("return window.".$attributeName.";",array());
    	}
    	$this->webdriver->selectWindow($currHdl); //go back to current window
    	return $attributes;
	}
	
	public function getConfirmation(){
		print "\n".__FUNCTION__.":\n"; //log commands called like SeleniumTestCase
		$this->seleniumSpeedDelay();
    	$confirmationText = $this->webdriver->getAlertText();
		if($this->confirmationChoice == "cancel"){
			$this->webdriver->dismissAlert();
		}else{ //$this->confirmationChoice == "ok"			
			$this->webdriver->acceptAlert();
		}
		return $confirmationText;
	}
	
	public function getCookie(){
		print "\n".__FUNCTION__.":\n"; //log commands called like SeleniumTestCase
		$this->seleniumSpeedDelay();
		return $this->webdriver->getAllCookies();
	}
    
	public function getCookieByName($name){
		print "\n".__FUNCTION__.": ".$name."\n"; //log commands called like SeleniumTestCase
		$this->seleniumSpeedDelay();
		$cookies = $this->webdriver->getAllCookies();
		foreach ($cookies as $cookie) {
			if($cookie->name == $name) return $cookie->value;
		}
		throw new Exception("\nCookie with name \"".$name."\" not found.\n");
	}
	
	public function getCursorPosition($locator){
		throw new Exception("\nSelenium RC method \"".__FUNCTION__."\" not yet implemented in ".get_class($this).".\n");
	}
	
	public function getElementHeight($locator){
		print "\n".__FUNCTION__.": ".$locator."\n"; //log commands called like SeleniumTestCase
		$this->seleniumSpeedDelay();
		$element = $this->parseLocator($locator);    	
        return $element->getAttribute("clientHeight");
        //or offsetHeight, scrollHeight, etc.
        //or $element->getSize(); and parse out height        
	}
	
	public function getElementIndex($locator){
		throw new Exception("\nSelenium RC method \"".__FUNCTION__."\" not yet implemented in ".get_class($this).".\n");
	}
	
	public function getElementPositionLeft($locator){
		print "\n".__FUNCTION__.": ".$locator."\n"; //log commands called like SeleniumTestCase
		$this->seleniumSpeedDelay();
		$element = $this->parseLocator($locator);    	
        return $element->getAttribute("offsetLeft");
        //or scrollLeft, etc.
        //or $element->getLocation(); and parse out x,y coordinates
	}
	
	public function getElementPositionTop($locator){
		print "\n".__FUNCTION__.": ".$locator."\n"; //log commands called like SeleniumTestCase
		$this->seleniumSpeedDelay();
		$element = $this->parseLocator($locator);    	
        return $element->getAttribute("offsetTop");
        //or scrollTop, etc.
        //or $element->getLocation(); and parse out x,y coordinates
	}
	
	public function getElementWidth($locator){
		print "\n".__FUNCTION__.": ".$locator."\n"; //log commands called like SeleniumTestCase
		$this->seleniumSpeedDelay();
		$element = $this->parseLocator($locator);    	
        return $element->getAttribute("clientWidth");
        //or offsetWidth, scrollWidth, etc.
        //or $element->getSize(); and parse out width
	}
	
	public function getEval($script){
		print "\n".__FUNCTION__.": ".$script."\n"; //log commands called like SeleniumTestCase
		$this->seleniumSpeedDelay();
		return $this->webdriver->execute($script,array()); //execute script w/ no arguments
	}
	
	public function getExpression($expression){
		throw new Exception("\nSelenium RC method \"".__FUNCTION__."\" not yet implemented in ".get_class($this).".\n");
	}
	
	public function getHtmlSource(){
		print "\n".__FUNCTION__.":\n"; //log commands called like SeleniumTestCase
		$this->seleniumSpeedDelay();
		return $this->webdriver->getPageSource();
	}
	
	public function getLocation(){
		print "\n".__FUNCTION__.":\n"; //log commands called like SeleniumTestCase
		$this->seleniumSpeedDelay();
		return $this->webdriver->getCurrentUrl();
	}
	
	public function getMouseSpeed(){
		throw new Exception("\nSelenium RC method \"".__FUNCTION__."\" not yet implemented in ".get_class($this).".\n");
	}
	
	public function getPrompt(){
		print "\n".__FUNCTION__.":\n"; //log commands called like SeleniumTestCase
		$this->seleniumSpeedDelay();
    	$promptText = $this->webdriver->getAlertText();
    	$this->webdriver->sendAlertText($this->promptAnswer);
    	$this->webdriver->acceptAlert();
    	return $promptText;
	}
	
	public function getSelectedId($selectLocator){
		print "\n".__FUNCTION__.": ".$selectLocator."\n"; //log commands called like SeleniumTestCase
		$this->seleniumSpeedDelay();
    	$element = $this->parseLocator($selectLocator);
        $optionValue = $element->getValue();
        $option = $element->findOptionElementByValue( $optionValue );
        return $option->getAttribute("id");
	}
	
	public function getSelectedIds($selectLocator){
		throw new Exception("\nSelenium RC method \"".__FUNCTION__."\" not yet implemented in ".get_class($this).".\n");
	}
	
	public function getSelectedIndex($selectLocator){
		print "\n".__FUNCTION__.": ".$selectLocator."\n"; //log commands called like SeleniumTestCase
		$this->seleniumSpeedDelay();
    	$element = $this->parseLocator($selectLocator);
        $optionValue = $element->getValue();
        $option = $element->findOptionElementByValue( $optionValue );
        return $option->getAttribute("selectedIndex");
	}
	
	public function getSelectedIndexes($selectLocator){
		throw new Exception("\nSelenium RC method \"".__FUNCTION__."\" not yet implemented in ".get_class($this).".\n");
	}
	
	public function getSelectedLabel($selectLocator){
		print "\n".__FUNCTION__.": ".$selectLocator."\n"; //log commands called like SeleniumTestCase
		$this->seleniumSpeedDelay();
    	$element = $this->parseLocator($selectLocator);
        $optionValue = $element->getValue();
        $option = $element->findOptionElementByValue( $optionValue );
        return $option->getText();
	}
	
	public function getSelectedLabels($selectLocator){
		throw new Exception("\nSelenium RC method \"".__FUNCTION__."\" not yet implemented in ".get_class($this).".\n");
	}
	
	public function getSelectedValue($selectLocator){
		print "\n".__FUNCTION__.": ".$selectLocator."\n"; //log commands called like SeleniumTestCase
		$this->seleniumSpeedDelay();
    	$element = $this->parseLocator($selectLocator);
        $optionValue = $element->getValue();
        $option = $element->findOptionElementByValue( $optionValue );
        return $option->getValue();
	}
	
	public function getSelectOptions($selectLocator){
		throw new Exception("\nSelenium RC method \"".__FUNCTION__."\" not yet implemented in ".get_class($this).".\n");
	}
	
	public function getSpeed(){
		print "\n".__FUNCTION__.":\n"; //log commands called like SeleniumTestCase
		$this->seleniumSpeedDelay();
		return $this->seleniumSpeed;
	}
	
	public function getTable($tableCellAddress){
		print "\n".__FUNCTION__.": ".$tableCellAddress."\n"; //log commands called like SeleniumTestCase
		$this->seleniumSpeedDelay();
		list($tableLocator,$rowNum,$colNum) = explode(".",$tableCellAddress);
		$table = $this->parseLocator($tableLocator);
		$row = $table->findElementBy("xpath","//tr[".$rowNum."]");
		$col = $table->findElementBy("xpath","//td[".$colNum."]");
		return $col->getText();
		//if row & col #s are off due to diff browser support,
		//or between Web Driver vs Selenium RC,
		//can do findElementsBy("tag name","tr"); and then one for td
		//then iterate over them until hit row # & col #
	}
	
	public function getText($locator){
		print "\n".__FUNCTION__.": ".$locator."\n"; //log commands called like SeleniumTestCase
		$this->seleniumSpeedDelay();
		$element = $this->parseLocator($locator);
        if( isset( $element ) ) return $element->getText();
        else return "";
	}
	
	public function getTitle(){
		print "\n".__FUNCTION__.":\n"; //log commands called like SeleniumTestCase
		$this->seleniumSpeedDelay();
		return $this->webdriver->getTitle();
	}
	
	public function getValue($locator){
		print "\n".__FUNCTION__.": ".$locator."\n"; //log commands called like SeleniumTestCase
		$this->seleniumSpeedDelay();
		$element = $this->parseLocator($locator);
        if( isset( $element ) ) return $element->getValue();
        else return "";
	}
	
	public function getWhetherThisFrameMatchFrameExpression($currentFrameString,$target){
		throw new Exception("\nSelenium RC method \"".__FUNCTION__."\" not yet implemented in ".get_class($this).".\n");
	}
	
	public function getWhetherThisWindowMatchWindowExpression($currentWindowString,$target){
		throw new Exception("\nSelenium RC method \"".__FUNCTION__."\" not yet implemented in ".get_class($this).".\n");
	}
	
	public function getXpathCount($xpath){
		print "\n".__FUNCTION__.": ".$xpath."\n"; //log commands called like SeleniumTestCase
		$this->seleniumSpeedDelay();
        return count($this->parseLocatorForElements($xpath));
	}
	
    public function goBack(){
    	print "\n".__FUNCTION__.":\n"; //log commands called like SeleniumTestCase
    	$this->seleniumSpeedDelay();
    	$this->webdriver->back();
    }
    
    public function highlight($locator){
    	throw new Exception("\nSelenium RC method \"".__FUNCTION__."\" not yet implemented in ".get_class($this).".\n");
    }
    
    public function ignoreAttributesWithoutValue($ignore){
    	//throw new Exception("\nSelenium RC method \"".__FUNCTION__."\" not yet implemented in ".get_class($this).".\n");
    	//ignore, do nothing instead, as Web Driver should have native XPath support for browsers already
    	//and probably can't do much here? See this for reference...
    	//http://code.google.com/p/selenium/wiki/XpathInWebDriver
    	print "\nSelenium RC method \"".__FUNCTION__."\" not implemented. See http://code.google.com/p/selenium/wiki/XpathInWebDriver for reference.\n";
    }

    public function isAlertPresent(){
    	print "\n".__FUNCTION__.":\n"; //log commands called like SeleniumTestCase
    	$this->seleniumSpeedDelay();
    	try{
    		$this->webdriver->getAlertText();
    		return true;
    	}catch(Exception $e){
    		return false; //NoAlertPresent error/exception - If there is no alert displayed.
    	}
    }
    
    public function isChecked($locator){
    	print "\n".__FUNCTION__.": ".$locator."\n"; //log commands called like SeleniumTestCase
    	$this->seleniumSpeedDelay();
    	return $this->parseLocator($locator)->isSelected();
    }
    
    public function isConfirmationPresent(){
    	print "\n".__FUNCTION__.":\n"; //log commands called like SeleniumTestCase
    	$this->seleniumSpeedDelay();
    	try{
    		$this->webdriver->getAlertText();
    		return true;
    	}catch(Exception $e){
    		return false; //NoAlertPresent error/exception - If there is no alert displayed.
    	}
    }
    
    public function isCookiePresent($name){
    	print "\n".__FUNCTION__.": ".$name."\n"; //log commands called like SeleniumTestCase
    	$this->seleniumSpeedDelay();
		$cookies = $this->webdriver->getAllCookies();
		foreach ($cookies as $cookie) {
			if($cookie->name == $name) return true;
		}
		return false; //otherwise, not found
    }
    
    public function isEditable($locator){
    	print "\n".__FUNCTION__.": ".$locator."\n"; //log commands called like SeleniumTestCase
    	$this->seleniumSpeedDelay();
    	return $this->parseLocator($locator)->isEnabled();
    }
    
    public function isElementPresent($locator){
    	print "\n".__FUNCTION__.": ".$locator."\n"; //log commands called like SeleniumTestCase
    	$this->seleniumSpeedDelay();
    	try{
    		$this->parseLocator($locator);
			return true;
		}catch(Exception $e){
  			//it has not been found
			return false;
		}
    }
    
    public function isOrdered($locator1, $locator2){
    	print "\n".__FUNCTION__.": ".$locator1." : ".$locator1."\n"; //log commands called like SeleniumTestCase
    	$this->seleniumSpeedDelay();
    	//modeled after http://www.jarvana.com/jarvana/view/org/seleniumhq/selenium/selenium/2.0a7/selenium-2.0a7-sources.jar!/org/openqa/selenium/internal/seleniumemulation/IsOrdered.java?format=ok
    	$element1 = $this->parseLocator($locator1);
    	$element2 = $this->parseLocator($locator2);
    	
    	$ordered = <<<ORDEREDSCRIPT
if(arguments[0] === arguments[1]) return false;
var previousSibling;
while((previousSibling = arguments[1].previousSibling) != null){
    if(previousSibling === arguments[0]){
        return true;
    }
    arguments[1] = previousSibling;
}
return false;
ORDEREDSCRIPT;

    	$result = $this->webdriver->executeScript($ordered, $element1, $element2);
    	return $result != null && $result;
    }
    
    public function isPromptPresent(){
    	print "\n".__FUNCTION__.":\n"; //log commands called like SeleniumTestCase
    	$this->seleniumSpeedDelay();
    	try{
    		$this->webdriver->getAlertText();
    		return true;
    	}catch(Exception $e){
    		return false; //NoAlertPresent error/exception - If there is no alert displayed.
    	}
    }
    
    public function isSomethingSelected($selectLocator){
    	print "\n".__FUNCTION__.": ".$selectLocator."\n"; //log commands called like SeleniumTestCase
    	$this->seleniumSpeedDelay();
    	return $this->parseLocator($selectLocator)->isSelected();
    }
    
    public function isVisible($locator){
    	print "\n".__FUNCTION__.": ".$locator."\n"; //log commands called like SeleniumTestCase
    	$this->seleniumSpeedDelay();
    	return $this->parseLocator($locator)->isDisplayed();
    }
    
    public function keyDown($locator, $keySequence){
    	throw new Exception("\nSelenium RC method \"".__FUNCTION__."\" not yet implemented in ".get_class($this).".\n");
    }
    
    public function keyDownNative($keyCode){
    	throw new Exception("\nSelenium RC method \"".__FUNCTION__."\" not yet implemented in ".get_class($this).".\n");
    }
    
	public function keyPress($locator, $keySequence){
    	throw new Exception("\nSelenium RC method \"".__FUNCTION__."\" not yet implemented in ".get_class($this).".\n");
    }
    
    public function keyPressNative($keyCode){
    	throw new Exception("\nSelenium RC method \"".__FUNCTION__."\" not yet implemented in ".get_class($this).".\n");
    }
    
	public function keyUp($locator, $keySequence){
    	throw new Exception("\nSelenium RC method \"".__FUNCTION__."\" not yet implemented in ".get_class($this).".\n");
    }
    
    public function keyUpNative($keyCode){
    	throw new Exception("\nSelenium RC method \"".__FUNCTION__."\" not yet implemented in ".get_class($this).".\n");
    }
    
	public function metaKeyDown(){
    	throw new Exception("\nSelenium RC method \"".__FUNCTION__."\" not yet implemented in ".get_class($this).".\n");
    }
    
	public function metaKeyUp(){
    	throw new Exception("\nSelenium RC method \"".__FUNCTION__."\" not yet implemented in ".get_class($this).".\n");
    }
    
    public function mouseDown($locator){
    	throw new Exception("\nSelenium RC method \"".__FUNCTION__."\" not yet implemented in ".get_class($this).".\n");
    }
    
	public function mouseDownAt($locator,$coordString){
    	throw new Exception("\nSelenium RC method \"".__FUNCTION__."\" not yet implemented in ".get_class($this).".\n");
    }
    
	public function mouseDownRight($locator){
    	throw new Exception("\nSelenium RC method \"".__FUNCTION__."\" not yet implemented in ".get_class($this).".\n");
    }
        
	public function mouseDownRightAt($locator,$coordString){
    	throw new Exception("\nSelenium RC method \"".__FUNCTION__."\" not yet implemented in ".get_class($this).".\n");
    }
    
	public function mouseMove($locator){
    	throw new Exception("\nSelenium RC method \"".__FUNCTION__."\" not yet implemented in ".get_class($this).".\n");
    }
    
	public function mouseMoveAt($locator,$coordString){
    	throw new Exception("\nSelenium RC method \"".__FUNCTION__."\" not yet implemented in ".get_class($this).".\n");
    }    
    
	public function mouseOut($locator){
    	throw new Exception("\nSelenium RC method \"".__FUNCTION__."\" not yet implemented in ".get_class($this).".\n");
    }    
    
	public function mouseOver($locator){
    	throw new Exception("\nSelenium RC method \"".__FUNCTION__."\" not yet implemented in ".get_class($this).".\n");
    }
    
	public function mouseUp($locator){
    	throw new Exception("\nSelenium RC method \"".__FUNCTION__."\" not yet implemented in ".get_class($this).".\n");
    }
    
	public function mouseUpAt($locator,$coordString){
    	throw new Exception("\nSelenium RC method \"".__FUNCTION__."\" not yet implemented in ".get_class($this).".\n");
    }
    
	public function mouseUpRight($locator){
    	throw new Exception("\nSelenium RC method \"".__FUNCTION__."\" not yet implemented in ".get_class($this).".\n");
    }
        
	public function mouseUpRightAt($locator,$coordString){
    	throw new Exception("\nSelenium RC method \"".__FUNCTION__."\" not yet implemented in ".get_class($this).".\n");
    }
    
    public function openWindow($url,$windowId=''){
    	print "\n".__FUNCTION__.": ".$url." : ".$windowId."\n"; //log commands called like SeleniumTestCase
    	$this->seleniumSpeedDelay();
    	if(!empty($windowId)) $this->webdriver->execute("window.open('".$url."','".$windowId."');",array());
    	else $this->webdriver->execute("window.open('".$url."');",array());    		
    }
    
    public function removeAllSelections($locator){
    	throw new Exception("\nSelenium RC method \"".__FUNCTION__."\" not yet implemented in ".get_class($this).".\n");
    }
    
    public function removeScript($scriptTagId){
    	throw new Exception("\nSelenium RC method \"".__FUNCTION__."\" not yet implemented in ".get_class($this).".\n");
    } 
    
    public function removeSelection($locator,$optionLocator) {
    	print "\n".__FUNCTION__.": ".$selectLocator." : ".$optionLocator."\n"; //log commands called like SeleniumTestCase
    	$this->seleniumSpeedDelay();
    	$this->select($locator,$optionLocator);
    }
    
    public function retrieveLastRemoteControlLogs(){
    	throw new Exception("\nSelenium RC method \"".__FUNCTION__."\" not yet implemented in ".get_class($this).".\n");
    }
    
    public function rollup($rollupName,$kwargs){
    	throw new Exception("\nSelenium RC method \"".__FUNCTION__."\" not yet implemented in ".get_class($this).".\n");
    }
    
	public function runScript($script){
		print "\n".__FUNCTION__.": ".$script."\n"; //log commands called like SeleniumTestCase
		$this->seleniumSpeedDelay();
		return $this->webdriver->execute($script,array()); //execute script w/ no arguments
	}
	
	public function selectPopUp($windowId=''){
		print "\n".__FUNCTION__.": ".$windowId."\n"; //log commands called like SeleniumTestCase
		$this->seleniumSpeedDelay();
		if(empty($windowId) || $windowId == "null"){
			//selects !top, !1st window, i.e. last
			$hdls = $this->webdriver->getWindowHandles();
			$hdlCount = count($hdls);
			$this->webdriver->selectWindow($hdls[$hdlCount-1]);
		}
		else{			
			$this->webdriver->selectWindow($windowId);
		}
	}
	
	public function selectWindow($windowId=''){
		print "\n".__FUNCTION__.": ".$windowId."\n"; //log commands called like SeleniumTestCase
		$this->seleniumSpeedDelay();
		if(empty($windowId) || $windowId == "null"){
			//selects top/1st window
			$hdls = $this->webdriver->getWindowHandles();
			$this->webdriver->selectWindow($hdls[0]);
		}
		else{
			switch($windowId){
				case $this->startsWith($windowId,"title="):
					//not ideal solution, but best we can do
					$windowTitle = substr($windowId,strlen("title="));
					$hdls = $this->webdriver->getWindowHandles();
    				foreach ($hdls as $hdl) {
    					$this->selectWindow($hdl); //switch to window
    					if($this->webdriver->getTitle() == $windowTitle) break; //found window, quit
    				}//else too bad, user now on last window in list regardless
    				break;
    			case $this->startsWith($windowId,"name="):
    				$windowName = substr($windowId,strlen("name="));
    				$this->webdriver->selectWindow($windowName);
    				break;
    			case $this->startsWith($locator,"var="):
    				/* We could try to support this by run javascript check
    				 * to see if var exists and the window not already in closed
    				 * state, in which case we can then select it. The check &
    				 * selection is done over iteration like by title method case
    				 * above, only we do javascript eval instead of compare titles.
    				 * But for the time being, too much work, we skip it. Maybe later. 
    				 */
    				throw new Exception("\nSelenium RC \"var=\" JavaScript variable name window locator strategy not implemented in ".get_class($this).".\n");
    				break;    		
            	default:
            		/* window ID or handle, or name (if passed w/o "name=" prepended to it
            		 * we will not try to lookup/select by title w/o "title=" prepended to it
            		 * that's the tester/user's fault for being lazy and not specifically
            		 * defining value as "title=value", in which case, it may fail here...
            		 */            		
            		$this->webdriver->selectWindow($windowId);
			}						
		}
	}
	
	public function setBrowserLogLevel($logLevel){
		throw new Exception("\nSelenium RC method \"".__FUNCTION__."\" not yet implemented in ".get_class($this).".\n");
	}
	
	public function setContext($context){
		print "\n".__FUNCTION__.": ".$context."\n"; //log commands called like SeleniumTestCase
		$this->seleniumSpeedDelay();
		//Writes a message to the status bar
		$this->webdriver->execute("window.status='".$context."';",array());
		//and adds a note to the browser-side log = to be implemented...
	}
	
	public function setCursorPosition($locator, $position){
		throw new Exception("\nSelenium RC method \"".__FUNCTION__."\" not yet implemented in ".get_class($this).".\n");
	}
	
	public function setExtensionJs($extensionJs){
		throw new Exception("\nSelenium RC method \"".__FUNCTION__."\" not yet implemented in ".get_class($this).".\n");
	}
	
	public function setMouseSpeed($pixels){
		throw new Exception("\nSelenium RC method \"".__FUNCTION__."\" not yet implemented in ".get_class($this).".\n");
	}
	
	public function setSpeed($value){
		print "\n".__FUNCTION__.": ".$value."\n"; //log commands called like SeleniumTestCase
		$this->seleniumSpeedDelay();
		$this->seleniumSpeed = $value;
	}
	
	public function setTimeout($timeout){
		$this->seleniumSpeedDelay();		
		$this->seleniumTimeout = $timeout;
		//probably good to use this as well to set Web Driver's implicit timeout for finding elements
		//$this->webdriver->setImplicitWaitTimeout($timeout);
	}
	
	public function shiftKeyDown(){
    	throw new Exception("\nSelenium RC method \"".__FUNCTION__."\" not yet implemented in ".get_class($this).".\n");
    }
	
	public function shiftKeyUp(){
    	throw new Exception("\nSelenium RC method \"".__FUNCTION__."\" not yet implemented in ".get_class($this).".\n");
    }
    
    public function showContextualBanner($className='',$methodName='') {
    	throw new Exception("\nSelenium RC method \"".__FUNCTION__."\" not yet implemented in ".get_class($this).".\n");
    }
    
    public function shutDownSeleniumServer(){
    	print "\n".__FUNCTION__.":\n"; //log commands called like SeleniumTestCase
    	$this->seleniumSpeedDelay();
    	$this->webdriver->close();
    }
	
    public function start($options=''){
    	print "\n".__FUNCTION__.": ".$options."\n"; //log commands called like SeleniumTestCase
    	$this->seleniumSpeedDelay();
    	//no options for WebDriver, we ignore...
    	$this->webdriver->connect($this->browser);
    }
    
	public function stop(){
		print "\n".__FUNCTION__.":\n"; //log commands called like SeleniumTestCase
		$this->seleniumSpeedDelay();
    	$this->webdriver->close();
    }
    
    public function typeKeys($locator,$value){
    	print "\n".__FUNCTION__.": ".$locator." : ".$value."\n"; //log commands called like SeleniumTestCase
    	$this->seleniumSpeedDelay();
    	//we'll just use type for the time being...
    	$this->type($locator,$value);
    }
    
    public function uncheck($locator){
    	print "\n".__FUNCTION__.": ".$locator."\n"; //log commands called like SeleniumTestCase
    	$this->seleniumSpeedDelay();
    	$this->parseLocator($locator)->clear();
    }
    
    public function useXpathLibrary($libraryName){
    	//throw new Exception("\nSelenium RC method \"".__FUNCTION__."\" not yet implemented in ".get_class($this).".\n");
    	//we'll just use whatever the default XPath (library) support is for WebDriver for given browser
    	print "\nSelenium RC method \"".__FUNCTION__."\" not implemented. See http://code.google.com/p/selenium/wiki/XpathInWebDriver for reference.\n";
    }
    
    public function waitForCondition($script, $timeout=''){
    	if(empty($timeout)) $timeout = $this->seleniumTimeout;
    	print "\n".__FUNCTION__.": ".$script." : ".$timeout."\n"; //log commands called like SeleniumTestCase
    	$this->seleniumSpeedDelay();    	
    	for ($ms = 0; ; $ms++) {
            if ($ms >= $timeout) {
                throw new Exception("\nTimed out after waiting ".$timeout." milliseconds for condition : ".$script."\n");
            }
            $status = $this->webdriver->execute($script,array());
            if($status == true) break;
            usleep(1000); //sleep 1 ms or 1000 us/microsecond
        }    	
    } 
    
    public function waitForFrameToLoad($frameAddress, $timeout=''){
    	if(empty($timeout)) $timeout = $this->seleniumTimeout;
    	throw new Exception("\nSelenium RC method \"".__FUNCTION__."\" not yet implemented in ".get_class($this).".\n");
    	//note we use frame IDs here instead for Web Driver. so frame address ~ frame ID
    	/*
    	print "\n".__FUNCTION__.": ".$frameAddress." : ".$timeout."\n"; //log commands called like SeleniumTestCase
    	for ($ms = 0; ; $ms++) {
            if ($ms >= $timeout) {
                throw new Exception("\nTimed out after waiting ".$timeout." milliseconds for frame to load\n");
            }
            try{
            	//probably don't want to select frame though, in case want to stay at current frame/window            	
            	$this->selectFrame($frameAddress);
            	return; //break;
            }catch(Exception $e){
            	//do nothing
            }           
            usleep(1000); //sleep 1 ms or 1000 us/microsecond
        }
        */
    }
    
    public function waitForPageToLoad($timeout=''){
    	if(empty($timeout)) $timeout = $this->seleniumTimeout;
    	print "\n".__FUNCTION__.": ".$timeout."\n"; //log commands called like SeleniumTestCase
    	$this->seleniumSpeedDelay();
    	for ($ms = 0; ; $ms++) {
            if ($ms >= $timeout) {
                throw new Exception("\nTimed out after waiting ".$timeout." milliseconds for page to load\n");
            }
            $status = $this->webdriver->execute("return document.readyState;",array());
            if($status == "complete") break;
            usleep(1000); //sleep 1 ms or 1000 us/microsecond
        }
        /* references:
         * http://www.w3schools.com/jsref/prop_doc_readystate.asp
         * https://developer.mozilla.org/en/DOM/document.readyState
         * should work in all browsers and for FF, v3.6+
         */
    }
    
    public function waitForPopUp($windowID,$timeout=''){
    	if(empty($timeout)) $timeout = $this->seleniumTimeout;
    	print "\n".__FUNCTION__.": ".$windowID." : ".$timeout."\n"; //log commands called like SeleniumTestCase
    	$this->seleniumSpeedDelay();
    	$currHdl = $this->webdriver->getWindowHandle();
    	for ($ms = 0; ; $ms++) {
            if ($ms >= $timeout) {
            	$this->webdriver->selectWindow($currHdl); //go back to current window
                throw new Exception("\nTimed out after waiting ".$timeout." milliseconds for popup window named \"".$windowID."\"\n");
            }
    		$hdls = $this->webdriver->getWindowHandles();
    		$name = "";
    		foreach ($hdls as $hdl) {
    			$this->selectWindow($hdl); //switch to window
    			$name = $this->webdriver->execute("return window.name;",array());
    			if($name == $windowID){
    				$this->webdriver->selectWindow($currHdl); //go back to current window
    				return; //found window, quit
    			} 
    		}
            usleep(1000); //sleep 1 ms or 1000 us/microsecond
        }    	
    }
    
    public function windowFocus(){
    	print "\n".__FUNCTION__.":\n"; //log commands called like SeleniumTestCase
    	$this->seleniumSpeedDelay();
    	$this->webdriver->execute("window.focus();",array());
    }
    
    public function windowMaximize(){
    	print "\n".__FUNCTION__.":\n"; //log commands called like SeleniumTestCase
    	$this->seleniumSpeedDelay();
    	$winMaxScript = <<<WINMAXSCRIPT
if (window.screen){
    window.moveTo(0, 0);
    window.resizeTo(window.screen.availWidth,window.screen.availHeight);
}
WINMAXSCRIPT;

    	$this->webdriver->execute($winMaxScript,array());
    }
    
    private function seleniumSpeedDelay(){
    	if($this->seleniumSpeed > 0) usleep(1000*$this->seleniumSpeed);
    	/* 1ms = 1000 us / microseconds
    	 * because some function calls return, we'll add delay before
    	 * execution of function rather than after
    	 * and we only do this for implement Selenium RC API methods
    	 * skip unimplemented ones and skip private functions
    	 */
    }
}
