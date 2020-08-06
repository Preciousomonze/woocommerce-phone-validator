<?php
/**
 * Deprecated action hooks
 *
 * @package Woo Phone Validator/Compatibility
 * @since   2.0.0
 */

defined( 'ABSPATH' ) || exit;

/**
 * Handles deprecation notices and triggering of legacy action hooks.
 */
class WC_PV_Deprecated_Action_Hooks extends WC_Deprecated_Action_Hooks {

	/**
	 * Array of deprecated hooks we need to handle.
     * Format of 'new' => 'old'.
	 *
	 * @var array
	 */
	protected $deprecated_hooks = array();

	/**
	 * Array of versions on each hook has been deprecated.
	 * Format of 'old_hook' => 'version_number_of_deprecation'
     * 
	 * @var array
	 */
	protected $deprecated_version = array();

}
