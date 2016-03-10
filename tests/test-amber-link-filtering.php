<?php

class AmberTestLinkFiltering extends WP_UnitTestCase {

	private $status_stub;
	private $checker_stub;

	function setUp() {

		AmberInstall::activate_site();

 		$this->status_stub = $this->getMockBuilder('AmberStatus')->disableOriginalConstructor()->getMock();
 		$this->checker_stub = $this->getMockBuilder('AmberChecker')->disableOriginalConstructor()->getMock();
 		$this->fetcher_stub = $this->getMockBuilder('AmberFetcher')->disableOriginalConstructor()->getMock();
		$this->fetcher_stub->method('fetch')->willReturn(array('id' => '0a137b375cc3881a70e186ce2172c8d1'));
 		Amber::set_status($this->status_stub);		
 		Amber::set_checker($this->checker_stub);	
 		Amber::set_fetcher($this->fetcher_stub);	

		parent::setUp();
	}

	function test_extract_one_link() {

 		$this->checker_stub->method('check')->willReturn(
   				array(
		            'id' => '0a137b375cc3881a70e186ce2172c8d1',
		            'url' => 'http://fox.com',
		            'last_checked' => 1395590225,
		            'next_check' => 1395590225 + 100000,
		            'status' => 1,
		            'message' => NULL,
          		)
			);

		$post_id = $this->factory->post->create(array(
			'post_content' => 'The quick brown <a href="http://fox.com">fox</a> jumped over the lazy dog',
			));

 		$result = Amber::extract_links($post_id, true);
		$this->assertEquals(1, count($result));
		$this->assertContains("http://fox.com", array_keys($result));
	}

	function test_extract_two_links() {

        $map = array(
          array(array('url' => 'http://fox.com'), true, array( 'status' => 1)),
          array(array('url' => 'http://dog.com'), true, array( 'status' => 1)),
        );
		$this->checker_stub->method('check')->will($this->returnValueMap($map));

		$post_id = $this->factory->post->create(array(
			'post_content' => 'The quick brown <a href="http://fox.com">fox</a> jumped over the lazy <a href="http://dog.com">dog</a>',
			));

 		$result = Amber::extract_links($post_id, true);
		$this->assertEquals(2, count($result));
		$this->assertContains("http://fox.com", array_keys($result));
		$this->assertContains("http://dog.com", array_keys($result));
	}

	function test_extract_one_link_https() {

 		$this->checker_stub->method('check')->willReturn( array( 'status' => 1));

		$post_id = $this->factory->post->create(array(
			'post_content' => 'The quick brown <a href="https://fox.com">fox</a> jumped over the lazy dog',
			));

 		$result = Amber::extract_links($post_id, true);
		$this->assertEquals(1, count($result));
		$this->assertContains("https://fox.com", array_keys($result));
	}

	function test_extract_ignore_relative_links() {

		$post_id = $this->factory->post->create(array(
			'post_content' => 'The quick brown <a href="/fox">fox</a> jumped over the lazy dog',
			));

 		$result = Amber::extract_links($post_id, true);
		$this->assertEquals(0, count($result));
	}

	function test_extract_links_with_attributes() {
 		$this->checker_stub->method('check')->willReturn( array( 'status' => 1));
		$post_id = $this->factory->post->create(array(
			'post_content' => 'The quick brown <a data-thing="blah" href="http://fox.com/fox">fox</a> jumped over the lazy dog',
			));

 		$result = Amber::extract_links($post_id, true);
		$this->assertEquals(1, count($result));
		$this->assertContains("http://fox.com/fox", array_keys($result));
	}

	function test_extract_one_link_different_quotes() {

 		$this->checker_stub->method('check')->willReturn( array( 'status' => 1));

		$post_id = $this->factory->post->create(array(
			'post_content' => 'The quick brown <a href=\'https://fox.com\'>fox</a> jumped over the lazy dog',
			));

 		$result = Amber::extract_links($post_id, true);
		$this->assertEquals(1, count($result));
		$this->assertContains("https://fox.com", array_keys($result));
	}

	function test_extract_one_link_funny_chars() {

 		$this->checker_stub->method('check')->willReturn( array( 'status' => 1));

		$post_id = $this->factory->post->create(array(
			'post_content' => 'The quick brown <a href="http://www.lagrandeepicerie.fr/#e-boutique/Les_produits_du_moment,2/coffret_vins_doux_naturels,149">URL with uncommon chars</a>
 jumped over the lazy dog',
			));

 		$result = Amber::extract_links($post_id, true);
		$this->assertEquals(1, count($result));
		$this->assertContains("http://www.lagrandeepicerie.fr/#e-boutique/Les_produits_du_moment,2/coffret_vins_doux_naturels,149", array_keys($result));
	}

	function test_extract_excluded_site() {

		update_option('amber_options', array('amber_excluded_sites' => "yahoo.com")); 
 		$this->checker_stub->method('check')->willReturn( array( 'status' => 1));

		$post_id = $this->factory->post->create(array(
			'post_content' => 'The quick brown <a href="http://yahoo.com">fox</a> jumped over the lazy <a href="http://dog.com">dog</a>',
			));

 		$result = Amber::extract_links($post_id, true);
		$this->assertEquals(2, count($result));
		$this->assertEquals("", $result["http://yahoo.com"]);
		$this->assertEquals(1, $result["http://dog.com"]);
	}

	function test_extract_excluded_site_with_path() {

		update_option('amber_options', array('amber_excluded_sites' => "yahoo.com")); 
 		$this->checker_stub->method('check')->willReturn( array( 'status' => 1));

		$post_id = $this->factory->post->create(array(
			'post_content' => 'The quick brown <a href="http://yahoo.com/fox">fox</a> jumped over the lazy <a href="http://dog.com">dog</a>',
			));

 		$result = Amber::extract_links($post_id, true);
		$this->assertEquals(2, count($result));
		$this->assertEquals("", $result["http://yahoo.com/fox"]);
		$this->assertEquals(1, $result["http://dog.com"]);
	}


	function test_extract_excluded_site_substring() {

		update_option('amber_options', array('amber_excluded_sites' => "yahoo.com")); 
 		$this->checker_stub->method('check')->willReturn( array( 'status' => 1));

		$post_id = $this->factory->post->create(array(
			'post_content' => 'The quick brown <a href="http://yahoo.com/fox">fox</a> jumped over the lazy <a href="http://dog.com/yahoo.com">dog</a>',
			));

 		$result = Amber::extract_links($post_id, true);
		$this->assertEquals(2, count($result));
		$this->assertEquals("", $result["http://yahoo.com/fox"]);
		$this->assertEquals("", $result["http://dog.com/yahoo.com"]);
	}

	function test_extract_excluded_site_substring_not_allows() {

		update_option('amber_options', array('amber_excluded_sites' => "yahoo.com$")); 
 		$this->checker_stub->method('check')->willReturn( array( 'status' => 1));

		$post_id = $this->factory->post->create(array(
			'post_content' => 'The quick brown <a href="http://yahoo.com/fox">fox</a> jumped over the lazy <a href="http://dog.com/yahoo.com">dog</a>',
			));

 		$result = Amber::extract_links($post_id, true);
		$this->assertEquals(2, count($result));
		$this->assertEquals(1, $result["http://yahoo.com/fox"]);
		$this->assertEquals("", $result["http://dog.com/yahoo.com"]);
	}


	function test_extract_excluded_site_another_regexp() {

		update_option('amber_options', array('amber_excluded_sites' => "com/exclude")); 
 		$this->checker_stub->method('check')->willReturn( array( 'status' => 1));

		$post_id = $this->factory->post->create(array(
			'post_content' => 'The quick brown <a href="http://yahoo.com/exclude/bananas">fox</a> jumped over the lazy <a href="http://dog.com/yahoo.com">dog</a>',
			));

 		$result = Amber::extract_links($post_id, true);
		$this->assertEquals(2, count($result));
		$this->assertEquals("", $result["http://yahoo.com/exclude/bananas"]);
		$this->assertEquals(1, $result["http://dog.com/yahoo.com"]);
	}

	function test_extract_excluded_site_bad_regexp() {

		update_option('amber_options', array('amber_excluded_sites' => "*yahoo.com")); 
 		$this->checker_stub->method('check')->willReturn( array( 'status' => 1));

		$post_id = $this->factory->post->create(array(
			'post_content' => 'The quick brown <a href="http://yahoo.com/">fox</a> jumped over the lazy <a href="http://dog.com/yahoo.com">dog</a>',
			));

		/* This will emit warning messages, but they can be ignored */
 		$result = Amber::extract_links($post_id, true);

		$this->assertEquals(2, count($result));
		$this->assertEquals(1, $result["http://yahoo.com/"]);
		$this->assertEquals(1, $result["http://dog.com/yahoo.com"]);
	}











}