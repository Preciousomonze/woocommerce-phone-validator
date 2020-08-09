<?php
/**
 * Deprecated filter hooks
 *
 * @package Woo Phone Validator/Compatibility
 * @since   2.0.0
 */

defined( 'ABSPATH' ) || exit;

/**
 * Handles deprecation notices and triggering of legacy filter hooks
 */
class WC_PV_Deprecated_Filter_Hooks extends WC_Deprecated_Filter_Hooks {

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
