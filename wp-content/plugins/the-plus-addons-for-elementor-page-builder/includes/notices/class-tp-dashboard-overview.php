<?php
/**
 * It is Main File to load all Notice, Upgrade Menu and all
 *
 * @link       https://posimyth.com/
 * @since      5.3.4
 * @version    6.4.2
 *
 * @package    Theplus
 * @subpackage ThePlus/Notices
 * */

namespace Theplus\Notices;

/**
 * Exit if accessed directly.
 * */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'Tp_Dashboard_Overview' ) ) {

	/**
	 * This class used for only load All Notice Files
	 *
	 * @since 5.3.4
	 * @version    6.4.2
	 */
	class Tp_Dashboard_Overview {

		/**
		 * Instance
		 *
		 * @since 5.3.4
		 * @access private
		 * @static
		 * @var instance of the class.
		 */
		private static $instance = null;

		/**
		 * API Overview Option
		 *
		 * @var string
		 */
		public $T_P_R_S_S_U_R_L = 'https://theplusaddons.com/wp-content/tpae-feed-cache.json';

		/**
		 * API Overview Data
		 *
		 * @var string
		 */
		public $overview_data = array();

		/**
		 * API Transient key
		 *
		 * @var string
		 */
		public $transient_key = 'tp_dashboard_overview';

		/**
		 * Instance
		 *
		 * Ensures only one instance of the class is loaded or can be loaded.
		 *
		 * @since 5.3.4
		 * @access public
		 * @static
		 * @return instance of the class.
		 */
		public static function instance() {
			if ( is_null( self::$instance ) ) {
				self::$instance = new self();
			}

			return self::$instance;
		}

		/**
		 * Constructor
		 *
		 * Perform some compatibility checks to make sure basic requirements are meet.
		 *
		 * @since 5.3.4
		 * @access public
		 */
		public function __construct() {
			$this->tp_call_api_dashboard_overview();
		}

		/**
		 * Whats's New Data Get
		 *
		 * @since 6.4.2
		 */
		public function tp_call_api_dashboard_overview() {
			$data = get_transient( $this->transient_key );

			if ( false === $data || empty( $data ) ) {

				$feed_url = $this->T_P_R_S_S_U_R_L;

				$response = wp_remote_get( $feed_url, array( 'timeout' => 25 ) );

				$status_code = wp_remote_retrieve_response_code( $response );

				if ( is_wp_error( $response ) || $status_code !== 200 ) {
					$this->overview_data = array(
						'HTTP_CODE' => $status_code,
						'success'   => 0,
						'message'   => 'RSS feed fetch failed',
						'data'      => array(),
					);
					return;
				}

				$body = wp_remote_retrieve_body( $response );

				$data = json_decode( $body, true );

				if ( ! is_array( $data ) ) {
					return array();
				}

				$this->overview_data = array(
					'HTTP_CODE' => $status_code,
					'success'   => 1,
					'message'   => 'RSS data fetched successfully',
					'data'      => $data,
				);

				set_transient( $this->transient_key, $this->overview_data, 4 * DAY_IN_SECONDS );

			} else {
				$this->overview_data = $data;
			}
		}
	}

	Tp_Dashboard_Overview::instance();
}
