<?php

class AmberTestLinks extends WP_UnitTestCase {

	function test_get_behavior_up_popup() {
		update_option('amber_options', array('amber_available_action' => AMBER_ACTION_POPUP)); 
		$this->assertEquals("up popup",  Amber::get_behavior(true));
	}

	function test_get_behavior_up_none() {
		update_option('amber_options', array('amber_available_action' => AMBER_ACTION_NONE)); 
		$this->assertNull( Amber::get_behavior(true));
	}

	function test_get_behavior_up_hover() {
		update_option('amber_options', array(
			'amber_available_action' => AMBER_ACTION_HOVER,
			'amber_available_action_hover' => 3,
			)); 
		$this->assertEquals("up hover:3", Amber::get_behavior(true));
	}

	function test_get_behavior_down_popup() {
		update_option('amber_options', array('amber_unavailable_action' => AMBER_ACTION_POPUP)); 
		$this->assertEquals( "down popup", Amber::get_behavior(false));
	}

	function test_get_behavior_down_none() {
		update_option('amber_options', array('amber_unavailable_action' => AMBER_ACTION_NONE)); 
		$this->assertNull( Amber::get_behavior(false));
	}

	function test_get_behavior_down_hover() {
		update_option('amber_options', array(
			'amber_unavailable_action' => AMBER_ACTION_HOVER,
			'amber_unavailable_action_hover' => 0,
			)); 
		$this->assertEquals( "down hover:0", Amber::get_behavior(false));
	}

	function test_get_behavior_country_up_popup() {
		update_option('amber_options', array(
			'amber_available_action' => AMBER_ACTION_NONE,
			'amber_country_available_action' => AMBER_ACTION_POPUP)); 
		$this->assertEquals("up popup",  Amber::get_behavior(true, true));
	}

	function test_get_behavior_country_up_none() {
		update_option('amber_options', array(
			'amber_available_action' => AMBER_ACTION_POPUP,
			'amber_country_available_action' => AMBER_ACTION_NONE)); 
		$this->assertNull( Amber::get_behavior(true, true));
	}

	function test_get_behavior_country_up_hover() {
		update_option('amber_options', array(
			'amber_available_action' => AMBER_ACTION_NONE,
			'amber_available_action_hover' => 1,
			'amber_country_available_action' => AMBER_ACTION_HOVER,
			'amber_country_available_action_hover' => 5,
			)); 
		$this->assertEquals("up hover:5", Amber::get_behavior(true, true));
	}

	function test_get_behavior_country_down_popup() {
		update_option('amber_options', array(
			'amber_unavailable_action' => AMBER_ACTION_NONE,
			'amber_country_unavailable_action' => AMBER_ACTION_POPUP)); 
		$this->assertEquals("down popup",  Amber::get_behavior(false, true));
	}

	function test_get_behavior_country_down_none() {
		update_option('amber_options', array(
			'amber_unavailable_action' => AMBER_ACTION_POPUP,
			'amber_country_unavailable_action' => AMBER_ACTION_NONE)); 
		$this->assertNull( Amber::get_behavior(false, true));
	}
	function test_get_behavior_country_down_hover() {
		update_option('amber_options', array(
			'amber_unavailable_action' => AMBER_ACTION_NONE,
			'amber_unavailable_action_hover' => 1,
			'amber_country_unavailable_action' => AMBER_ACTION_HOVER,
			'amber_country_unavailable_action_hover' => 5,
			)); 
		$this->assertEquals("down hover:5", Amber::get_behavior(false, true));
	}

}

