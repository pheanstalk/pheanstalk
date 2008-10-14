<?php

// $Id$

require_once dirname(__FILE__) . '/../../../autorun.php';
require_once dirname(__FILE__) . '/../../dom_tester.php';

class TestOfCssSelectors extends UnitTestCase {
	function TestOfCssSelectors() {
		$html = file_get_contents(dirname(__FILE__) . '/support/dom_tester.html');
		$this->dom = new DomDocument('1.0', 'utf-8');
		$this->dom->validateOnParse = true;
		$this->dom->loadHTML($html);
	}

    function testBasicSelector() {
        $expectation = new CssSelectorExpectation($this->dom, 'h1');
        $this->assertTrue($expectation->test(array('Test page')));

		$expectation = new CssSelectorExpectation($this->dom, 'h2');
        $this->assertTrue($expectation->test(array('Title 1', 'Title 2')));

		$expectation = new CssSelectorExpectation($this->dom, '#footer');
        $this->assertTrue($expectation->test(array('footer')));

		$expectation = new CssSelectorExpectation($this->dom, 'div#footer');
        $this->assertTrue($expectation->test(array('footer')));

		$expectation = new CssSelectorExpectation($this->dom, '.header');
        $this->assertTrue($expectation->test(array('header')));

		$expectation = new CssSelectorExpectation($this->dom, 'p.header');
        $this->assertTrue($expectation->test(array('header')));

		$expectation = new CssSelectorExpectation($this->dom, 'div.header');
        $this->assertTrue($expectation->test(array()));

		$expectation = new CssSelectorExpectation($this->dom, 'ul#mylist ul li');
        $this->assertTrue($expectation->test(array('element 3', 'element 4')));

		$expectation = new CssSelectorExpectation($this->dom, '#nonexistant');
        $this->assertTrue($expectation->test(array()));
    }
    
    function testAttributeSelectors() {
		$expectation = new CssSelectorExpectation($this->dom, 'ul#list li a[href]');
        $this->assertTrue($expectation->test(array('link')));

		$expectation = new CssSelectorExpectation($this->dom, 'ul#list li a[class~="foo1"]');
        $this->assertTrue($expectation->test(array('link')));

		$expectation = new CssSelectorExpectation($this->dom, 'ul#list li a[class~="bar1"]');
        $this->assertTrue($expectation->test(array('link')));

		$expectation = new CssSelectorExpectation($this->dom, 'ul#list li a[class~="foobar1"]');
        $this->assertTrue($expectation->test(array('link')));

		$expectation = new CssSelectorExpectation($this->dom, 'ul#list li a[class^="foo1"]');
        $this->assertTrue($expectation->test(array('link')));

		$expectation = new CssSelectorExpectation($this->dom, 'ul#list li a[class$="foobar1"]');
        $this->assertTrue($expectation->test(array('link')));

		$expectation = new CssSelectorExpectation($this->dom, 'ul#list li a[class*="oba"]');
        $this->assertTrue($expectation->test(array('link')));

		$expectation = new CssSelectorExpectation($this->dom, 'ul#list li a[href="http://www.google.com/"]');
        $this->assertTrue($expectation->test(array('link')));

		$expectation = new CssSelectorExpectation($this->dom, "ul#list li a[href='http://www.google.com/']");
		$this->assertTrue($expectation->test(array('link')));
        
        $expectation = new CssSelectorExpectation($this->dom, 'ul#anotherlist li a[class|="bar1"]');
        $this->assertTrue($expectation->test(array('another link')));  	

		$expectation = new CssSelectorExpectation($this->dom, 'ul#list li a[class*="oba"][class*="ba"]');
        $this->assertTrue($expectation->test(array('link')));  	

		$expectation = new CssSelectorExpectation($this->dom, 'p[class="myfoo"][id="mybar"]');
        $this->assertTrue($expectation->test(array('myfoo bis')));  	

		$expectation = new CssSelectorExpectation($this->dom, 'p[onclick*="a . and a #"]');
        $this->assertTrue($expectation->test(array('works great')));  	
    }
    
    function testCombinators() {
		$expectation = new CssSelectorExpectation($this->dom, 'body  h1');
        $this->assertTrue($expectation->test(array('Test page')));  	

		$expectation = new CssSelectorExpectation($this->dom, 'div#combinators > ul  >   li');
        $this->assertTrue($expectation->test(array('test 1', 'test 2')));  	

		$expectation = new CssSelectorExpectation($this->dom, 'div#combinators>ul>li');
        $this->assertTrue($expectation->test(array('test 1', 'test 2')));
        
		$expectation = new CssSelectorExpectation($this->dom, 'div#combinators li  +   li');
        $this->assertTrue($expectation->test(array('test 2', 'test 4')));
        
		$expectation = new CssSelectorExpectation($this->dom, 'div#combinators li+li');
        $this->assertTrue($expectation->test(array('test 2', 'test 4')));

		$expectation = new CssSelectorExpectation($this->dom, 'h1, h2');
        $this->assertTrue($expectation->test(array('Test page', 'Title 1', 'Title 2')));

		$expectation = new CssSelectorExpectation($this->dom, 'h1,h2');
        $this->assertTrue($expectation->test(array('Test page', 'Title 1', 'Title 2')));

		$expectation = new CssSelectorExpectation($this->dom, 'h1  ,   h2');
        $this->assertTrue($expectation->test(array('Test page', 'Title 1', 'Title 2')));

		$expectation = new CssSelectorExpectation($this->dom, 'h1, h1,h1');
        $this->assertTrue($expectation->test(array('Test page')));

		$expectation = new CssSelectorExpectation($this->dom, 'h1,h2,h1');
        $this->assertTrue($expectation->test(array('Test page', 'Title 1', 'Title 2')));

		$expectation = new CssSelectorExpectation($this->dom, 'p[onclick*="a . and a #"], div#combinators > ul > li + li');
        $this->assertTrue($expectation->test(array('works great', 'test 2')));
    }
}

class TestsOfChildAndAdjacentSelectors extends DomTestCase {
	function TestsOfChildAndAdjacentSelectors() {
		$html = file_get_contents(dirname(__FILE__) . '/support/child_adjacent.html');
		$this->dom = new DomDocument('1.0', 'utf-8');
		$this->dom->validateOnParse = true;
		$this->dom->loadHTML($html);
	}

    function testFirstChild() {
		$expectation = new CssSelectorExpectation($this->dom, 'p:first-child');
        $this->assertTrue($expectation->test(array('First paragraph')));

		$expectation = new CssSelectorExpectation($this->dom, 'body > p:first-child');
        $this->assertTrue($expectation->test(array('First paragraph')));

		$expectation = new CssSelectorExpectation($this->dom, 'body > p > a:first-child');
        $this->assertTrue($expectation->test(array('paragraph')));
    }

    function testChildren() {
		$expectation = new CssSelectorExpectation($this->dom, 'body > p');
        $this->assertTrue($expectation->test(array('First paragraph', 'Second paragraph', 'Third paragraph')));

		$expectation = new CssSelectorExpectation($this->dom, 'body > p > a');
        $this->assertTrue($expectation->test(array('paragraph')));
    }

    function testAdjacents() {
		$expectation = new CssSelectorExpectation($this->dom, 'p + p');
        $this->assertTrue($expectation->test(array('Second paragraph', 'Third paragraph')));

		$expectation = new CssSelectorExpectation($this->dom, 'body > p + p');
        $this->assertTrue($expectation->test(array('Second paragraph', 'Third paragraph')));

		$expectation = new CssSelectorExpectation($this->dom, 'body > p + p > a');
        $this->assertTrue($expectation->test(array('paragraph')));
    }
}

?>
