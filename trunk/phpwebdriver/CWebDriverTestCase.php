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
*  Base class for functional tests using webdriver. 
*  It provides interface like classic selenium test class.  
*  @author kolec
*/
class CWebDriverTestCase extends PHPUnit_Framework_TestCase {
    public $webdriver;
    public $baseUrl;	

    function __construct() {
        parent::__construct();
    }

    protected function setUp($host="localhost", $port="4444", $browser="firefox")
    {
    	parent::setUp();
        $this->webdriver = new WebDriver($host, $port);
        $this->webdriver->connect($browser);         
    }

    protected function tearDown()
    {
    	$this->webdriver->close();
    }

   public function setBrowserUrl($url) {
	$this->baseUrl = $url;
   }

    public function open( $url, $check_id = '') {
        $urlToOpen = $this->baseUrl.$url;
	
        $this->webdriver->get($urlToOpen);
        if( !empty( $check_id ) ) return $this->getElement( LocatorStrategy::id, $check_id ); else usleep(500*1000);
   }   

   public function getBodyText() {
        $html = $this->webdriver->getPageSource();
	$body = preg_replace("/.*<body[^>]*>|<\/body>.*/si", "", $html);
	return $body;
   }

   public function isTextPresent($text) {	
	$found = false;
        $i = 0;
        do {
		$html = $this->webdriver->getPageSource();
		if (is_string($html)) {
  			$found = !(strpos($html, $text) === false);
		}
		if (!$found) {
	                sleep( $this->waiting_time );
	                $i += $this->waiting_time;
		}
        } while(!$found && $i <= $this->max_waiting_time );
	return $found;
   }

   public function getAttribute($xpath) {
	$body = $this->getBodyText();
	$xml = new SimpleXMLElement($body);
	$nodes = $xml->xpath("$xpath");
	return $nodes[0][0];
   }
   

   public function type( $element_name, $textToType ) {
	$element = $this->getElement( LocatorStrategy::id, $element_name);
	if (isset($element)) {
		///usleep(100*1000);
		$element->sendKeys(array($textToType));
		//usleep(500*1000);
	}	
   }

   public function clear( $element_name ) {
	$element = $this->getElement( LocatorStrategy::id, $element_name);
	if ($element) {
		$element->clear();
	}
   }

   public function submit( $element_name ) {
	$element = $this->getElement( LocatorStrategy::id, $element_name);
	if (isset($element)) {
		$element->submit();
		usleep(500*1000);
	}
   }


   public function click( $element_name ) {
	$element = $this->getElement( LocatorStrategy::id, $element_name);
	if (isset($element)) {
		$element->click();
		usleep(500*1000);
	}
   }

   public function close() {
	$this->webdriver->close();
   }
   
   public function select($element_name, $option_text) {
        $element = $this->getElement( LocatorStrategy::id, $element_name );
	$option = $element->findOptionElementByText($option_text);
	$option->click();
	//$element->sendKeys(array($option_text));
        /*$options = $element->findElementsBy(LocatorStrategy::tagName, "option");
        foreach($options as $option) {
            if ($option->getText()==$option_text) {
                $option->setSelected();
                break;
            }
        }*/
   }

    public function getElementByIdOrName( $element_name ) {
        try {
            $element = $this->webdriver->findElementBy(LocatorStrategy::id, $element_name);
        } catch ( NoSuchElementException $ex) {
            $element = $this->webdriver->findElementBy(LocatorStrategy::name, $element_name);
        } 
        return $element;
   }


    protected $waiting_time = 0.5;
    protected $max_waiting_time = 4;

    public function getElement( $strategy, $name )
    {
        $i = 0;
        do {
            try {
                $element = $this->webdriver->findElementBy( $strategy, $name );
            } catch( NoSuchElementException $e ) {
                print_r( "\nWaiting for \"" . $name . "\" element to appear...\n" );
                sleep( $this->waiting_time );
                $i += $this->waiting_time;
            }
        } while(!isset($element) && $i <= $this->max_waiting_time );
        if( !isset($element) ) $this->fail( "Element has not appeared after " . $this->max_waiting_time . " seconds." );
        return $element;
   }
}



?>