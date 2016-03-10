<?php

class AmberTestLinkRewriting extends WP_UnitTestCase {

	private $status_stub;

	function setUp() {
 		$this->status_stub = $this->getMockBuilder('AmberStatus')->disableOriginalConstructor()->getMock();
 		Amber::set_status($this->status_stub);		
		parent::setUp();
	}

	function test_filter_no_links() {
 		$result = Amber::filter("The quick brown fox jumped over the lazy dog");
		$this->assertEquals("The quick brown fox jumped over the lazy dog", $result);
	}

	function test_filter_uncached_link() {

		$this->status_stub->method('get_summary')->willReturn(array('default' => array()));
 		$result = Amber::filter("The quick brown <a href='http://fox.com'>fox</a> jumped over the lazy dog");
		$this->assertEquals("The quick brown <a href='http://fox.com'>fox</a> jumped over the lazy dog", $result);
	}

	function test_filter_cached_one_link() {

		update_option('amber_options', array('amber_available_action' => AMBER_ACTION_HOVER)); 

		$this->status_stub->method('get_summary')->willReturn(
		       array(
		        'default' => array(
		          'date' => '1395590225',
		          'location' => 'Amber/cache/0a137b375cc3881a70e186ce2172c8d1',
		          'status' => 1,
		          'size' => 3453,
		        )
			));
 		$result = Amber::filter('The quick brown <a href="http://fox.com">fox</a> jumped over the lazy dog');
		$this->assertEquals('The quick brown <a href="http://fox.com" data-versionurl="http://example.org/Amber/cache/0a137b375cc3881a70e186ce2172c8d1" data-versiondate="2014-03-23T15:57:05+00:00" data-amber-behavior="up hover:2">fox</a> jumped over the lazy dog', $result);
	}

	function test_filter_cached_one_link_https() {

		update_option('amber_options', array('amber_available_action' => AMBER_ACTION_HOVER)); 

		$this->status_stub->method('get_summary')->willReturn(
		       array(
		        'default' => array(
		          'date' => '1395590225',
		          'location' => 'Amber/cache/0a137b375cc3881a70e186ce2172c8d1',
		          'status' => 1,
		          'size' => 3453,
		        )
			));
 		$result = Amber::filter('The quick brown <a href="https://fox.com">fox</a> jumped over the lazy dog');
		$this->assertEquals('The quick brown <a href="https://fox.com" data-versionurl="http://example.org/Amber/cache/0a137b375cc3881a70e186ce2172c8d1" data-versiondate="2014-03-23T15:57:05+00:00" data-amber-behavior="up hover:2">fox</a> jumped over the lazy dog', $result);
	}

	function test_filter_cached_two_links_one_up_one_down() {

        $map = array(
          array('http://fox.com', array(0), array(
			        'default' => array(
			          'date' => '1395590225',
			          'location' => 'Amber/cache/0a137b375cc3881a70e186ce2172c8d1',
			          'status' => 1,
			          'size' => 3453,
			        ))),
          array('http://dog.com', array(0), array(
			        'default' => array(
			          'date' => '1395590225',
			          'location' => 'Amber/cache/DOG37b375cc3881a70e186ce2172c8d1',
			          'status' => 0,
			          'size' => 3453,
			        ))),
        );

		update_option('amber_options', array(
			'amber_available_action' => AMBER_ACTION_HOVER,
			'amber_unavailable_action' => AMBER_ACTION_POPUP,
			)
		); 

		$this->status_stub->method('get_summary')->will($this->returnValueMap($map));
 		$result = Amber::filter('The quick brown <a href="http://fox.com">fox</a> jumped over the lazy <a href="http://dog.com">dog</a>');
		$this->assertEquals('The quick brown <a href="http://fox.com" data-versionurl="http://example.org/Amber/cache/0a137b375cc3881a70e186ce2172c8d1" data-versiondate="2014-03-23T15:57:05+00:00" data-amber-behavior="up hover:2">fox</a> jumped over the lazy <a href="http://dog.com" data-versionurl="http://example.org/Amber/cache/DOG37b375cc3881a70e186ce2172c8d1" data-versiondate="2014-03-23T15:57:05+00:00" data-amber-behavior="down popup">dog</a>', $result);
	}

	function test_filter_cached_one_link_country_specific_behavior() {

		update_option('amber_options', array(
			'amber_available_action' => AMBER_ACTION_HOVER,
			'amber_country_available_action' => AMBER_ACTION_POPUP,
			'amber_country_id' => 'IR',
			)); 

		$this->status_stub->method('get_summary')->willReturn(
		       array(
		        'default' => array(
		          'date' => '1395590225',
		          'location' => 'Amber/cache/0a137b375cc3881a70e186ce2172c8d1',
		          'status' => 1,
		          'size' => 3453,
		        )
			));
 		$result = Amber::filter('The quick brown <a href="http://fox.com">fox</a> jumped over the lazy dog');
		$this->assertEquals('The quick brown <a href="http://fox.com" data-versionurl="http://example.org/Amber/cache/0a137b375cc3881a70e186ce2172c8d1" data-versiondate="2014-03-23T15:57:05+00:00" data-amber-behavior="up hover:2,IR up popup">fox</a> jumped over the lazy dog', $result);
	}

	function test_filter_cached_one_link_country_specific_behavior_same_as_default() {

		update_option('amber_options', array(
			'amber_available_action' => AMBER_ACTION_HOVER,
			'amber_country_available_action' => AMBER_ACTION_HOVER,
			'amber_country_id' => 'IR',
			)); 

		$this->status_stub->method('get_summary')->willReturn(
		       array(
		        'default' => array(
		          'date' => '1395590225',
		          'location' => 'Amber/cache/0a137b375cc3881a70e186ce2172c8d1',
		          'status' => 1,
		          'size' => 3453,
		        )
			));
 		$result = Amber::filter('The quick brown <a href="http://fox.com">fox</a> jumped over the lazy dog');
		$this->assertEquals('The quick brown <a href="http://fox.com" data-versionurl="http://example.org/Amber/cache/0a137b375cc3881a70e186ce2172c8d1" data-versiondate="2014-03-23T15:57:05+00:00" data-amber-behavior="up hover:2">fox</a> jumped over the lazy dog', $result);
	}

}

