<?php

require_once(dirname(__FILE__).'/../../../autorun.php');
require_once(dirname(__FILE__).'/../package.php');

class TestOfSynchronisationCheck extends UnitTestCase {
	function testOfSynchronisationNotNecessary() {
	    $source = dirname(__FILE__)."/package/fr/no-synchronisation.xml";
	    $synchro = new PackagingSynchronisation($source);
	    $this->assertEqual($synchro->result(), "");

	    $source = dirname(__FILE__)."/package/en/synchronisation.xml";
	    $synchro = new PackagingSynchronisation($source);
	    $this->assertEqual($synchro->result(), "");
	}
	
	function testOfSynchronisationNecessary() {
	    $source = dirname(__FILE__)."/package/fr/synchronisation.xml";
	    $synchro = new PackagingSynchronisation($source);
	    $this->assertEqual($synchro->revision(), "1672");
	    $this->assertEqual($synchro->sourceRevision(), "1671");
	    $this->assertEqual($synchro->sourceLang(), "en");
	    $this->assertEqual($synchro->lastSynchroRevision(), "1475");
	    $this->assertPattern("/style=\"color : red\"/", $synchro->result());
	}
}

class TestOfContentTransformationFromXMLToHTML extends UnitTestCase {
	function testOfNonLinksFileWithPHPExtension() {
		$file = dirname(__FILE__).'/package/one_section_with_autorum_php.xml';
		$source = simplexml_load_file($file, "SimpleTestXMLElement");
		$content = $source->content();
		$this->assertPattern('/autorun\.php/', $content);
		$this->assertNoPattern('/autorun\.html/', $content);
		$this->assertPattern('/autodive\.php/', $content);
		$this->assertNoPattern('/autodive\.html/', $content);
		$this->assertNoPattern('/autowalk\.php/', $content);
		$this->assertPattern('/autowalk\.html/', $content);
	}

    function testOfPHPTags() {
		$file = dirname(__FILE__).'/package/one_section_with_php_code.xml';
		$source = simplexml_load_file($file, "SimpleTestXMLElement");
		$content = $source->content();
		$this->assertPattern('/<pre>/', $content);
		$this->assertNoPattern('/<\!\[CDATA\[/', $content);
		$this->assertPattern('/<p>/', $content);
	}

	function testOfContentWithoutSections() {
		$file = dirname(__FILE__).'/package/content_without_section.xml';
		$source = simplexml_load_file($file, "SimpleTestXMLElement");
		$content = $source->content();
		$this->assertPattern('/<p>/', $content);
	}

	function testOfSingleLink() {
		$file = dirname(__FILE__).'/package/here_download.xml';
		$source = simplexml_load_file($file, "SimpleTestXMLElement");
		$map = dirname(__FILE__).'/package/map.xml';
		$links = $source->links($map);
		$this->assertEqual(count($links), 4);
		$links_download = '<ul><li><a href="download.html">Download SimpleTest</a></li></ul>';
		$this->assertEqual($links['download'], $links_download);
	}

	function testOfMultipleLinks() {
		$file = dirname(__FILE__).'/package/here_support.xml';
		$source = simplexml_load_file($file, "SimpleTestXMLElement");
		$map = dirname(__FILE__).'/package/map.xml';
		$links = $source->links($map);
		$this->assertEqual(count($links), 4);
		$links_support = '<ul><li><a href="support.html">Support mailing list</a></li>'.
		'<li><a href="books.html">Books</a></li></ul>';
		$this->assertEqual($links['support'], $links_support);
	}

	function testOfHierarchicalLinks() {
		$file = dirname(__FILE__).'/package/here_overview.xml';
		$source = simplexml_load_file($file, "SimpleTestXMLElement");
		$map = dirname(__FILE__).'/package/map.xml';
		$links = $source->links($map);
		$this->assertEqual(count($links), 4);
		$links_start_testing = '<ul><li><a href="start-testing.html">Start testing with SimpleTest</a></li>'.
		'<li><a href="overview.html">Documentation overview</a>'.
		'<ul><li><a href="unit_test_documentation.html">Unit tester</a></li>'.
		'<li><a href="group_test_documentation.html">Group tests</a></li></ul>'.
		'</li><li><a href="tutorial.html">Tutorial overview</a></li></ul>';
		$this->assertEqual($links['start_testing'], $links_start_testing);
	}

	function testOfRootLinksWithHierarchy() {
		$file = dirname(__FILE__).'/package/here_simpletest.xml';
		$source = simplexml_load_file($file, "SimpleTestXMLElement");
		$map = dirname(__FILE__).'/package/map.xml';
		$links = $source->links($map);
		$this->assertEqual(count($links), 4);
		$links_start_testing = '<ul><li><a href="start-testing.html">Start testing with SimpleTest</a></li>'.
		'<li><a href="overview.html">Documentation overview</a></li>'.
		'<li><a href="tutorial.html">Tutorial overview</a></li></ul>';
		$this->assertEqual($links['start_testing'], $links_start_testing);
	}

	function testOfLinksWithNonRootParent() {
		$file = dirname(__FILE__).'/package/here_unit-tester.xml';
		$source = simplexml_load_file($file, "SimpleTestXMLElement");
		$map = dirname(__FILE__).'/package/map.xml';
		$links = $source->links($map);
		$this->assertEqual(count($links), 4);
		$links_start_testing = '<ul><li><a href="start-testing.html">Start testing with SimpleTest</a></li>'.
		'<li><a href="overview.html">Documentation overview</a>'.
		'<ul><li><a href="unit_test_documentation.html">Unit tester</a></li>'.
		'<li><a href="group_test_documentation.html">Group tests</a></li></ul>'.
		'</li><li><a href="tutorial.html">Tutorial overview</a></li></ul>';
		$this->assertEqual($links['start_testing'], $links_start_testing);
	}
}

?>