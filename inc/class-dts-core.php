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
  
  public static function get_developer_orders( $dev_name, array $order_ids ) {
    global $wpdb;
    
    $ids = implode(',', $order_ids );
    
    $wp = $wpdb->prefix;
    $sql  = "SELECT ID as pid FROM {$wp}posts AS p "
      . " LEFT JOIN `{$wp}woocommerce_order_items` AS oi on p.`ID` = oi.`order_id` "
      . "	LEFT JOIN `{$wp}woocommerce_order_itemmeta` AS im on im.`order_item_id` = oi.`order_item_id` "
      . " WHERE im.`meta_key` = 'developer_name' AND im.`meta_value` = %s "
      . " AND p.ID in ( $ids )";

    
      
    $query_sql = $wpdb->prepare( $sql, $dev_name );
    
    //echo('TTT get_developer_orders<pre>' . print_r($query_sql, 1) . '</pre>');
    
    $sql_results = $wpdb->get_results($query_sql, ARRAY_A);

    $dev_order_ids = array();
    foreach ($sql_results as $row) {
      $dev_order_ids[] = $row['pid'];
    }
    
    return $dev_order_ids;
  }
  
  
  public static function get_completed_order_ids( string $date, array $exclude_ids = array() ) {
    global $wpdb;
    $wp = $wpdb->prefix;

    $date_condition = " ( {$wp}posts.post_date >= '" . $date . " 00:00:00' AND {$wp}posts.post_date <= '" . $date . " 23:59:59' ) ";

    if ( count( $exclude_ids ) ) {
      $exclude_condition = " {$wp}posts.ID NOT IN (" . implode(',', $exclude_ids) . ") ";
    } else {
      $exclude_condition = " 1 = 1 ";
    }

    $query_sql = "SELECT {$wp}posts.ID  as pid from {$wp}posts WHERE $date_condition AND $exclude_condition AND {$wp}posts.post_type = 'shop_order' AND {$wp}posts.post_status = 'wc-completed'  ORDER BY {$wp}posts.post_date DESC";

    $sql_results = $wpdb->get_results($query_sql, ARRAY_A);

    $ids = array();
    
    foreach ($sql_results as $row) {
      $ids[] = $row['pid'];
    }
    return $ids;
  }
  
  /**
   * 
   * @global object $wpdb
   * @param string $date format Y-m-d
   * @return array
   */
  public static function get_free_order_ids( string $date ) {
    global $wpdb;
    $wp = $wpdb->prefix;
    
    $date_condition = "( ( {$wp}posts.post_date >= '" . $date . " 00:00:00' AND {$wp}posts.post_date <= '" . $date . " 23:59:59' ) ) ";

    $query_sql = "SELECT p.ID as pid from {$wp}posts AS p
				LEFT JOIN `{$wp}postmeta` AS pm on p.`ID` = pm.`post_id`
				WHERE $date_condition AND pm.`meta_key` = '_order_total' AND ( pm.`meta_value` = '0.00' OR pm.`meta_value` = '0' )";
    $sql_results = $wpdb->get_results( $query_sql, ARRAY_A );

    $free_order_ids = array();
    
    foreach ($sql_results as $row) {
      $free_order_ids[] = $row['pid'];
    }
    return $free_order_ids;
  }
  
  /**
   * Finds all active WooCommerce products and their developers 
   * 
   * returns array [ $developer_slug => [ product_id => product_name ] ]
   * 
   * @global object $wpdb
   * @return array
   */
  public static function get_active_woocommerce_products() {
    global $wpdb;
    $wp = $wpdb->prefix;
    
    $query_sql = "SELECT p.ID AS pid, p.post_title AS name, t.slug AS developer_slug from {$wp}posts AS p
        JOIN {$wp}term_relationships tr     ON p.ID = tr.object_id
        JOIN {$wp}term_taxonomy tt          ON tr.term_taxonomy_id = tt.term_taxonomy_id
        JOIN {$wp}terms t                   ON tt.term_id = t.term_id
				WHERE p.`post_status` = 'publish' AND p.`post_type` = 'product'
        AND tt.taxonomy = 'developer'";
        
    /*
    SELECT p.ID, p.post_title, p.post_type
FROM wp_posts p
JOIN wp_term_relationships tr ON p.ID = tr.object_id
JOIN wp_term_taxonomy tt ON tr.term_taxonomy_id = tt.term_taxonomy_id
JOIN wp_terms t ON tt.term_id = t.term_id
WHERE p.post_type = 'your_post_type'
  AND tt.taxonomy = 'category'
  AND t.slug IN ('category1', 'category2', 'category3');
     * 
     */
        
    $sql_results = $wpdb->get_results( $query_sql, ARRAY_A );

    $developer_products = array();
    
    foreach ( $sql_results as $row ) {
      
      $product_id     = $row['pid'];
      $product_name   = $row['name'];
      $slug           = $row['developer_slug'];
      
      if ( ! isset( $developer_products[$slug] ) ) {
        $developer_products[$slug] = array();
      }
      
      $developer_products[$slug][$product_id] = $product_name;
    }
    
    return $developer_products;
  }

}
