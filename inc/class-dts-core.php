<?php


class Dts_Core {

  
  protected const SORT_AZ = 'A_Z';
  protected const SORT_ZA = 'Z_A';
  
  protected const ACTION_SAVE             = 'Save';
  protected const ACTION_CLEAR            = 'Erase data';
  protected const ACTION_CALCULATE        = 'Calculate';
  protected const ACTION_CALCULATE2       = 'Calculate last 10 days';
  protected const ACTION_CALCULATE3       = 'Calculate 10 days before 10';
  protected const ACTION_CALCULATE4       = 'Calculate 10 days before 20';
  protected const ACTION_CRONTEST         = 'Test scheduled event';
  protected const ACTION_RANDOM           = 'Generate random sales data';
  
	public static $prefix = 'dts_';
	
	public static $option_names = [
    'ids_exclude_from_calculation',
    'developer_selling_data'
  ];

	public static $default_option_values = [
    'ids_exclude_from_calculation'  => '0',
    'developer_selling_data'        => array()
	];

	public static $option_values = array();

	public static function init() {
		self::load_options();
	}

	public static function load_options() {
		$stored_options = get_option( 'dts_options', array() );
    
		foreach ( self::$option_names as $option_name ) {
			if ( isset( $stored_options[$option_name] ) ) {
				self::$option_values[$option_name] = $stored_options[$option_name];
			}
			else {
				self::$option_values[$option_name] = self::$default_option_values[$option_name];
			}
		}
	}

	protected function display_messages( $error_messages, $messages ) {
		$out = '';
		if ( count( $error_messages ) ) {
			foreach ( $error_messages as $message ) {
				$out .= '<div class="notice-error settings-error notice is-dismissible"><p>'
				. '<strong>'
				. $message
				. '</strong></p>'
				. '<button type="button" class="notice-dismiss"><span class="screen-reader-text">Dismiss this notice.</span></button>'
				. '</div>';
			}
		}
		if ( count( $messages ) ) {
			foreach ( $messages as $message ) {
				$out .= '<div class="notice-info notice is-dismissible"><p>'
				. '<strong>'
				. $message
				. '</strong></p>'
				. '<button type="button" class="notice-dismiss"><span class="screen-reader-text">Dismiss this notice.</span></button>'
				. '</div>';
			}
		}

		return $out;
	}
	
	public static function log($data) {

		$filename = pathinfo( __FILE__, PATHINFO_DIRNAME ) . DIRECTORY_SEPARATOR .'log.txt';
		if ( isset($_REQUEST['dts_log_to_screen']) && $_REQUEST['dts_log_to_screen'] == 1 ) {
			echo( 'log::<pre>' . print_r($data, 1) . '</pre>' );
		}
		else {
			file_put_contents($filename, date("Y-m-d H:i:s") . " | " . print_r($data,1) . "\r\n\r\n", FILE_APPEND);
		}
	}

}
