<?php

/**
 * Basic class that contains common functions,
 * such as:
 * - installation / deinstallation
 * - meta & options management,
 * - adding pages to menu
 * etc
 */
class Dts_Plugin extends Dts_Core {
	
	const CHECK_RESULT_OK = 'ok';
  
  public const OPTION_NAME_FULL = 'developer_sales_full';

  public function __construct( $plugin_root ) {

		$this->plugin_root = $plugin_root;

		add_action( 'plugins_loaded', array( $this, 'initialize'), 10 );
		add_action( 'wp_enqueue_scripts', array( $this, 'register_styles_and_scripts' ) );
    
    if (is_admin()) {
      add_action('admin_enqueue_scripts', array($this, 'register_admin_styles_and_scripts'));
    }
    
		add_action( 'admin_menu', array( $this, 'add_page_to_menu' ) );
    
	}

	public function initialize() {
		$this->register_shortcodes();
    
    add_action( 'dts_cron_hook', array( $this, 'execute_cron' ) );
		self::load_options();
	}

	/* Add options on plugin activate */
	public static function install() {
		self::install_plugin_options();
    
    
    if ( ! wp_next_scheduled( 'dts_cron_hook' ) )  {
			wp_schedule_event( time(), 'daily', 'dts_cron_hook' );
		}
	}
  
	/* Stop cron` on  plugin deactivate */
	public static function uninstall() {
		wp_clear_scheduled_hook( 'dts_cron_hook' );
	}
  
	public static function install_plugin_options() {
		add_option( 'dts_options', self::$default_option_values );
	}
  
	public function register_shortcodes() {		
    add_shortcode( 'top_selling_developers', array( 'Dts_Frontend', 'render_top_sellers' ) );
    add_shortcode( 'weekly_bestsellers_slider', array( 'Dts_Frontend', 'render_weekly_slider' ) );
	}
  
  public function register_admin_styles_and_scripts() {
    
    $file_src = plugins_url( 'css/dts-admin.css', $this->plugin_root );
    wp_enqueue_style( 'dts-admin', $file_src, array(), DTS_VERSION );

    $this->enqueue_slick_slider_styles_scripts();
  }
  
    
  public function register_styles_and_scripts() {
    
    $debug_enabled = $_GET['dts-debug'] ?? false; 
    
    // add Slick styles and scripts for "Shop" page ( product archive page)
    // which is supposed to have developer slider shortcode & top sellers list
    if ( is_post_type_archive( 'product' ) || $debug_enabled ) { 
      
      wp_enqueue_script( 'dts-front-js', plugins_url('/js/dts-front.js', $this->plugin_root), array( 'jquery' ), DTS_VERSION, true );
      wp_localize_script( 'dts-front-js', 'scs_settings', array(
        'ajax_url'			=> admin_url( 'admin-ajax.php' ),
      ) );

      wp_enqueue_style( 'dts-front', plugins_url('/css/dts-front.css', $this->plugin_root), array(), DTS_VERSION );

    
      $this->enqueue_slick_slider_styles_scripts();
    }
  }
  
  public function enqueue_slick_slider_styles_scripts() {
    
    $slick_src = plugins_url( 'slick/slick.css', $this->plugin_root );
    wp_enqueue_style( 'dts-slick', $slick_src, array(), DTS_VERSION );
    
    $slick_theme_src = plugins_url( 'slick/slick-theme.css', $this->plugin_root );
    wp_enqueue_style( 'dts-slick-theme', $slick_theme_src, array(), DTS_VERSION );
    
    wp_enqueue_script( 'dts-slick-js', plugins_url('slick/slick.min.js', $this->plugin_root), array(), DTS_VERSION );
  }
  
	public function add_page_to_menu() {
		add_management_page(
			__( 'Top Developers' ),          // page title.
			__( 'Top Developers' ),          // menu title.
			'manage_options',
			'dts-settings',			                // menu slug.
			array( $this, 'render_settings_page' )   // callback.
		);
  }
  
  	
	public static function execute_cron() {
		
    self::log("cron execution started.");
		
    $developers = self::get_developer_list();
    $dev_sales = get_option( self::OPTION_NAME_FULL, array() );

    $date = date('Y-m-d');
    $dev_sales[$date] = self::calculate_day_sales( $date, $developers );

    update_option( self::OPTION_NAME_FULL, $dev_sales );
		
		self::log("cron execution finished.");
	}
  
  
  public function do_action() {
    
    
    if ( isset($_POST['dts-button'] ) ) {
      
      switch ($_POST['dts-button'] ) {
        case self::ACTION_CALCULATE:
          $developers = self::get_developer_list();
          $dev_sales = get_option( self::OPTION_NAME_FULL, array() );
    
          $date = '2024-' . $_POST['calc-date'];
          $dev_sales[$date] = self::calculate_day_sales( $date, $developers );
     
          update_option( self::OPTION_NAME_FULL, $dev_sales );
        break;
        case self::ACTION_CALCULATE2:
        case self::ACTION_CALCULATE3:
        case self::ACTION_CALCULATE4:
          $developers = self::get_developer_list();
          $dev_sales = get_option( self::OPTION_NAME_FULL, array() );
    
          $start = 0;
          
          if ( $_POST['dts-button'] == self::ACTION_CALCULATE3 ) { $start = 10; }
          if ( $_POST['dts-button'] == self::ACTION_CALCULATE4 ) { $start = 20; }
          
          for ($i = $start; $i < $start + 10; $i++) {
            $date = date('Y-m-d', strtotime("-$i days") );
            $dev_sales[$date] = self::calculate_day_sales( $date, $developers );
          }
          
          update_option( self::OPTION_NAME_FULL, $dev_sales );
        break;
        
        case self::ACTION_SAVE:
          //$stored_options = get_option( 'dts_options', array() );
          //$stored_options['ids_exclude_from_calculation'] = $_POST['ids_exclude_from_calculation'];
          //update_option( 'dts_options', $stored_options );
        break;
        case self::ACTION_CLEAR:
          self::erase_developer_sales();
        break;
        case self::ACTION_CRONTEST:
          self::execute_cron();
        break;
        case self::ACTION_RANDOM:
          self::generate_random_sales();
        break;

      }
    }
  }
  
  /**
   * Get list of developers: [ id => name ]
   * 
   * @return array
   */
  public static function get_developer_list( $use_slug = false, $return_field = 'name' ) {
    $developers     = get_terms( array( 'taxonomy' => 'developer', 'hide_empty' => false, ) );
		$arr_developers = array();
    
    foreach ( $developers as $developer ) {
      
      $key = $use_slug ? $developer->slug : $developer->term_id;
      
      if ( $return_field === 'name') {
        $value = $developer->name;
      }
      else {
        $value =  get_term_meta( $developer->term_id, $return_field, true );
      }
      
      $arr_developers[$key] = $value;
      
    }

		return $arr_developers;
  }
  
  /**
   * Get top N sellers.
   * 
   * If $use_slug is false, returns array [ $developer_id => $sales ], 
   * otherwise returns array [ $developer_slug => $sales ]
   *
   * @param bool $use_slug
   * @return array 
   */
  public static function get_top_sellers( $num = 10, $use_slug = true ) {
    $dev_sales = self::get_total_developer_sales( $use_slug );
    
    arsort( $dev_sales );
    
    $top_sellers = array();
    
    if ( is_array( $dev_sales ) ) {
      $top_sellers = array_slice( $dev_sales, 0, $num, true );
    }

    ksort( $top_sellers );
    
    return $top_sellers;
  }
  
  
  /**
   * Get total sales for all developers, for specified number of days
   * 
   * @param bool $use_slug
   * @param int $number_of_days
   * @return array [ $developer_slug => $total ]
   */
  public static function get_total_developer_sales( $use_slug = true, int $number_of_days = 30 ) {
    $developers     = get_terms( array( 'taxonomy' => 'developer', 'hide_empty' => true ) );
		$arr_developers = array();
    
    $dev_sales = get_option( self::OPTION_NAME_FULL, array() );
    
    foreach ( $developers as $developer ) {
      $key = $use_slug ? $developer->slug : $developer->term_id;
      $arr_developers[ $key ] = 0;
    }
        
    $actual_dates = self::generate_last_n_days( $number_of_days );
      
    if ( is_array( $dev_sales ) && count( $dev_sales ) ) {
      foreach ( $dev_sales as $date => $day_sales ) {
        if ( in_array( $date, $actual_dates ) ) {
          foreach ( $developers as $developer ) {
            $developer_sales = $day_sales[$developer->term_id];

            $key = $use_slug ? $developer->slug : $developer->term_id;
            $arr_developers[ $key ] += $developer_sales;
          }
        }
      }
    }
    
    return $arr_developers;
  }
  
  
  public static function generate_random_sales( int $number_of_days = 30 ) {
    
    $dev_sales = array();
    $developers     = get_terms( array( 'taxonomy' => 'developer', 'hide_empty' => true ) );
    
    $actual_dates = self::generate_last_n_days( $number_of_days );
      
    foreach ( $actual_dates as $date) {
      $random_day_sales = array();
      
      foreach ( $developers as $developer ) {
        $min = 260 - ord( $developer->slug );
        $random_day_sales[$developer->term_id] = rand( $min * 40, $min * 50 );
      }
      
      $dev_sales[$date] = $random_day_sales;
    }

    update_option( self::OPTION_NAME_FULL, $dev_sales );
  }
  
  /**
   * Get daily sales for all developers, for specified number of days
   *
   * 
   * @param int $number_of_days
   * @return array [ date => developer_sales ]
   */
  public static function get_daily_developer_sales( int $number_of_days = 30 ) {

		$arr_sales = array();
    
    $dev_sales = get_option( self::OPTION_NAME_FULL, array() );
      
    if ( is_array( $dev_sales ) && count( $dev_sales ) ) {
      
      $actual_dates = self::generate_last_n_days( $number_of_days );
      
      foreach ( $dev_sales as $date => $day_sales ) {
        if ( in_array( $date, $actual_dates ) ) {
          $arr_sales[$date] = $day_sales;
        }
      }
    }
    
    return $arr_sales;
  }
  
  /**
   * Prepares an array of date strings in Y-m-d format
   * 
   * [ '2024-03-29', '2024-03-28', '2024-03-27', '2024-03-26', ... ]
   * 
   * @param int $n
   * @return array [ 'Y-m-d' ]
   */
  public static function generate_last_n_days( int $n ) {
    
    $days = array();
    
    for ($i = 0; $i < $n; $i++) {    
        $days[] = date('Y-m-d', strtotime("-$i days") );
    }
    
    return $days;
  }
  
  public static function get_30_days_header() {
    
    $out = '';
    // Loop through the last 30 days
    for ($i = 0; $i < 30; $i++) {
        
        $date = date('m-d', strtotime("-$i days") );
        $out = "<th>$date</th>" . $out;
    }
    
    return $out;
  }
  
  public static function erase_developer_sales() {
    delete_option( self::OPTION_NAME_FULL );
  }
  
  /**
   * 
   * @return array [ date => developer_sales ]
   */
  public static function get_30_days_sales() {
    return self::get_daily_developer_sales( 30 ); 
  }
  
  public static function get_7_days_sales() {
    return self::get_daily_developer_sales( 7 ); 
  }
  
  
  public static function calculate_day_sales( string $date, array $developers ) {
    
    $free_order_ids = self::get_free_order_ids( $date );
    $completed_order_ids = self::get_completed_order_ids( $date, $free_order_ids );

    $dev_sales = array();
    
    foreach ( $developers as $dev_id => $dev_name ) {
      $dev_sales[$dev_id] = self::calculate_developer_sales( $dev_id, $dev_name, $completed_order_ids );
    }
      
    return $dev_sales;
  }
  
  /**
   * 
   * @param string $dev_name
   * @param string $date format Y-m-d
   */
  public static function calculate_developer_sales( int $dev_id, string $dev_name, array $completed_order_ids ) {
    
    $dev_orders = self::get_developer_orders($dev_name, $completed_order_ids );
    $sales = 0;
    
    foreach ( $dev_orders as $order_id ) {
      $order_sales = self::calc_developer_sales_in_order( $dev_id, $dev_name, $order_id );
      
      foreach( $order_sales as $sale ) {
        $sales += $sale['price_after_coupon'];
      }
    }
    
    return $sales;
  }
  
  /**
   * 
   * @param int $dev_id
   * @param string $dev_name
   * @param int $order_id
   * @return array
   */
  public static function calc_developer_sales_in_order( int $dev_id, string $dev_name, int $order_id ) {
    
    $order = new WC_Order( $order_id );
    
    $items = $order->get_items();

    $results = array();
    foreach ($items as $key => $item) {

      $item_id = $item->get_product_id();
      

      if ( ! has_term( $dev_id, 'developer', $item_id ) ) {
        continue;
      }
        
      $item_result['product_id'] = $item_id;
      $item_result['name'] = $item['name'];
      $item_result['price_after_coupon'] = $order->get_item_total($item, false, true);
      $item_result['price_before_coupon'] = $order->get_item_subtotal($item, false, true);
      $item_result['is_deal_product'] = false;
      $item_result['is_shop_product'] = false;
      
      $item_meta = $item->get_meta_data();

      // see function apd_store_rewards_to_order_meta() for the source of "bigdeal" meta

      foreach ( $item_meta as $meta_item ) {

        if ( $meta_item->key == 'bigdeal' && $meta_item->value == 1 ) {
          $item_result['is_deal_product'] = true;
        }
        
        if ( $meta_item->key == 'shop_product' && $meta_item->value == 1 ) {
          $item_result['is_shop_product'] = true;
        }

        if ( $meta_item->key == 'developer_name' ) {
          $item_result['developer_name'] = $meta_item->value;
        }
      }

      // skip products that were part of the deal at the moment when order has been created
      if ( $item_result['is_deal_product'] ) {
        continue;
      }
      
      if ( $item_result['developer_name'] != $dev_name ) {
        continue;
      }

      $results[] = $item_result;
    }
    
    return $results;
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
  
  public static function render_30_days_sales( $dev_sales, $dev_id ) {
    
    $out = '';
    
    $total_value = 0;
    for ($i = 0; $i < 30; $i++) {
        
        $date = date('Y-m-d', strtotime("-$i days") );
        
        $value = '--';
        
        if ( isset( $dev_sales[$date]) && is_array($dev_sales[$date]) ) {
          $value = $dev_sales[$date][$dev_id] ?? '---';
          $total_value += $dev_sales[$date][$dev_id] ?? 0;
        }
        
        $out = "<td>" . $value . "</td>" . $out;
    }
    
    
    $out = "<td class='total'>" . $total_value . "</td>" . $out;
    
    return $out;
  }
  
	public function render_settings_page() {
    
    $action_results = '';
    
    if ( isset( $_POST['dts-button'] ) ) {
			$action_results = $this->do_action();
		}
		
    $developers = self::get_developer_list();
    self::load_options();
    
    $dev_sales = self::get_30_days_sales(); 
    ?> 

		<h1><?php esc_html_e('Statistics for Top Developers', 'dts'); ?></h1>
    
    <h4>Example shortcode output for [top_selling_developers] shortcode</h4>
    
    <div style="border: 2px solid #111; padding: 20px;">
    <?php echo do_shortcode('[top_selling_developers title="Top developers"]'); ?> 
    </div>
    
    <h4>Example shortcode output for [weekly_bestsellers_slider] shortcode</h4>
    
    <div style="border: 2px solid #111; padding: 20px;">
    <?php echo do_shortcode('[weekly_bestsellers_slider mode="desktop" debug="1" ]'); ?> 
    </div>
    
    <h4>Check scheduled calculations</h4>
    
    <?php $next = wp_next_scheduled( 'dts_cron_hook' ); ?>
    
    <?php if ( $next ): ?>
      <span style="color: green; font-weight: bold;">Event is scheduled, all is good</span>
    <?php else: ?>
      <span style="color: red; font-weight: bold;">NO event scheduled! Please deactivate 'Top Selling Developers' plugin and activate again.</span>
    <?php endif; ?>
    
    <form method="POST" >
      
      <h3>Developer sales in last 30 days</h3>
      
      <table id="dts-table">
        <thead>
          <th>Developer name</th>
          <th>Total sales</th>
          <?php echo self::get_30_days_header(); ?>
        </thead>
        <tbody>
          <?php foreach  ( $developers as $dev_id => $dev_name ): ?>
            <?php $dev_sales_row = self::render_30_days_sales( $dev_sales, $dev_id ); ?>
            <tr>
              <td><?php echo $dev_name; ?></td>
              <?php echo $dev_sales_row; ?>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
      
      <!--
      <p class="submit">  
       <input type="submit" id="dts-button-save" name="dts-button" class="button button-primary" value="<?php echo self::ACTION_SAVE; ?>" />
      </p>
      -->
      
      <h2>Clear data</h2>
      <p class="submit">  
       <input type="submit" id="dts-button-erase" name="dts-button" class="button button-primary" value="<?php echo self::ACTION_CLEAR; ?>" />
      </p>
      
      <h2>Calculate for a single day</h2>
      
      <input type="text" name="calc-date" value="<?php echo date('m-d'); ?>" />
      <p class="submit">  
       <input type="submit" id="dts-button-calc" name="dts-button" class="button button-primary" value="<?php echo self::ACTION_CALCULATE; ?>" />
      </p>
      
      <h2>Calculate for last 10 days</h2>
      
      <p class="submit">  
       <input type="submit" id="dts-button-calc2" name="dts-button" class="button button-primary" value="<?php echo self::ACTION_CALCULATE2; ?>" />
      </p>
      
      <h2>Calculate for 10 days before that</h2>
      
      <p class="submit">  
       <input type="submit" id="dts-button-calc3" name="dts-button" class="button button-primary" value="<?php echo self::ACTION_CALCULATE3; ?>" />
      </p>
      
      <h2>Calculate for 10 more days before that</h2>
      
      <p class="submit">  
       <input type="submit" id="dts-button-calc4" name="dts-button" class="button button-primary" value="<?php echo self::ACTION_CALCULATE4; ?>" />
      </p>
      
      <h2>Test scheduled calculations</h2>
      
      <p class="submit">  
       <input type="submit" id="dts-button-test" name="dts-button" class="button button-primary" value="<?php echo self::ACTION_CRONTEST; ?>" />
      </p>
      
      <h2>Test with random sales</h2>
      
      <p class="submit">  
       <input type="submit" id="dts-button-random" name="dts-button" class="button button-primary" value="<?php echo self::ACTION_RANDOM; ?>" />
      </p>
    </form>
    <?php 
  }

    
  }
}