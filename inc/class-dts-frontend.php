<?php


class Dts_Frontend extends Dts_Core {

  /**
   * Handler for "top_selling_developers" shortcode
   * 
   * Renders two lists (only one list is shown at the same time)
   * 
   * a) list of all developers ( by default shows top 30 developers, but can be expanded to full list)
   * b) list of top 30 bestselling products
   * 
   * User can switch between lists by clicking on switcher links
   * 
   * 
   * @param array $atts
   * @return string HTML for the developer list
   */
  public static function render_top_sellers( $atts ) {
    
     
    $input_fields = [
      'title'                 => 'Top Bestsellers',
      'products_title'        => 'Top Bestsellers',
      'all_developers_title'  => 'All developers',
      'show_products_label'   => 'Show top products',
      'show_developers_label' => 'Show top developers',
      'show_all_developers_label' => 'Show all developers'
    ];
    
    extract( shortcode_atts( $input_fields, $atts ) );
    
    $top_sellers = Dts_Plugin::get_top_sellers( 30 ); // get [ slug => sales ]
    
    $top_selling_products  = Dts_Plugin::get_top_selling_products( 30 ); // get array of [ product name, url ]
    
    $all_developers = Dts_Plugin::get_developer_list( true ); // get [ slug => name ]
    
    foreach ( $top_sellers as $slug => $sales ) {
      $dev_name = $all_developers[$slug] ?? '---';
      $top_sellers[$slug] = $dev_name;
    }
      
    $out = self::render_developer_list( $top_sellers, $all_developers, $title, $show_all_developers_label, $all_developers_title, $show_developers_label );
    
    $out .= self::render_product_list( $top_selling_products, $products_title );
    $out .= self::render_list_switcher( $show_products_label, $show_developers_label );
    
    $out .= '&nbsp;';
    
    return $out;
  }
  
  
  public static function render_list_switcher( $product_label, $dev_label ) {
    
    $out = '<div id="dts-show-developer-list" class="dts-list-switcher" style="display:none;">' . $dev_label . '</div>';
    $out .= '<div id="dts-show-product-list" class="dts-list-switcher" >' . $product_label . '</div>';
    
    return $out;
  }
  /**
   * Prepares HTML for the list of developers ( split into N columns by 10 developers)
   * 
   * @param array $devs
   * @param string $title
   * @return string
   */
  public static function render_developer_list( array $devs, array $all_devs, 
    string $title = 'Top Bestsellers', string $show_title = 'Show all', 
    string $all_developers_title = 'All developers', string $show_top = 'Show top deveopers' ) {
    
    $out = '<div id="dts-developer-list-container">';
    $out .= '<div class="dts-developer-list-header" id="dts-list-top-sellers-header" ><h2 class="title">' . $title . '</h2></div>';
    
    /* List of 30 Top selling developers, 10 developers per column  */
    
    $out .= '<div class="dts-developer-list" id="dts-list-top-sellers">';
    $out .= '<div class="list-body">';
      
    $counter = 0;
    
    foreach ( $devs as $slug => $dev_name ) {
      
      $out .= '<div><a href="/developer/' . $slug . '">' . $dev_name. '</a></div>';
      
      $counter++;
      if ( ($counter) % 10 == 0 && $counter < count($devs) ) {
        $out .= '</div><div class="list-body">';
      }
    }
    
    $out .= '</div>';
    $out .= '</div>';
    
    /* List of All developers, in three columns  */
    
    $counter = 0;
    $column_amount = ceil( count( $all_devs ) / 3 );
    
    $out .= '<div class="dts-developer-list-header" id="dts-list-all-sellers-header" style="display:none;"><h2 class="title">' . $all_developers_title . '</h2></div>';
    $out .= '<div class="dts-developer-list" id="dts-list-all-sellers" style="display:none;" >';
    $out .= '<div class="list-body">';
    
    foreach ( $all_devs as $slug => $dev_name ) {
      
      $out .= '<div><a href="/developer/' . $slug . '">' . $dev_name. '</a></div>';
      
      $counter++;
      if ( ($counter) % $column_amount == 0 && $counter < count($all_devs) ) {
        $out .= '</div><div class="list-body">';
      }
    }
    
    $out .= '</div>'; // close the last column
    $out .= '</div>'; // close columns container 
    
    $out .= '<span id="dts-show-all-developers" class="dts-show-all">' . $show_title  .'</span></div>';
    $out .= '<span id="dts-show-top-developers" class="dts-show-all" style="display:none ">' . $show_top  .'</span></div>';
    
    return $out;
  }
  
  public static function render_product_list( $products, $title ) {
    
    $out = '<div id="dts-product-list-container" style="display:none;">';
    $out .= '<div class="dts-developer-list-header"><h2 class="title">' . $title . '</h2></div>';
    $out .= '<div class="dts-developer-list" >';
    $out .= '<div class="list-body">';
      
    $counter = 0;
    
    foreach ( $products as $product_data) {
      
      $out .= '<div><a href="' . $product_data[1] . '">' . $product_data[0] . '</a></div>';
      
      $counter++;
      if ( ($counter) % 10 == 0 && $counter < count($products) ) {
        $out .= '</div><div class="list-body">';
      }
    }
    
    $out .= '</div>';
    $out .= '</div>';
    $out .= '</div>';
    
    return $out;
  }
  
  /**
   * Handler for "weekly_bestsellers_slider" shortcode
   * 
   * @param array $atts
   * @return string HTML for the developer list
   */
  public static function render_weekly_slider( $atts ) {
    
     
    $input_fields = [
      'mode'      => 'desktop',
      'debug'     => 0
    ];
    
    extract( shortcode_atts( $input_fields, $atts ) );
    
    $top_sellers = Dts_Plugin::get_top_sellers( 5, true ); // get [ slug => sales ]
    
    if ( $mode === 'desktop' ) {
      $developer_images = Dts_Plugin::get_developer_list( true, 'developer_image' ); // get [ slug => image ]
    }
    else {
      $developer_images = Dts_Plugin::get_developer_list( true, 'developer_image_mobile' ); // get [ slug => image ]
    }
    
    foreach ( $top_sellers as $slug => $sales ) {
      $dev_image_id = $developer_images[$slug] ?? 0;
      $top_seller_images[ $slug ] = $dev_image_id;
    }
      
    $out = self::render_developer_slider( $top_seller_images, $mode, $debug );
    
    return $out;
  }
  
  /**
   * Prepares HTML for the slider of developers ( mode either 'desktop' or 'mobile' )
   * 
   * @param array $devs
   * @param string $mode
   * @return string
   */
  public static function render_developer_slider( array $devs, $mode = 'desktop', $debug_display = false ) {
    
    $out = '<div class="dts-developer-slider dts-developer-slider-' . $mode . '">';
    $out .= '<div class="slick-slider">';
    
    foreach ( $devs as $slug => $img_id ) {
      
      if ( $img_id ) {
        $image_src = wp_get_attachment_image_src( $img_id, 'full' );
        $out .= '<div><a href="/developer/' . $slug . '"><img src="' . $image_src[0] . '" /></a></div>';
      }
      else {
        if ( $debug_display ) {
          $out .= '<div><a href="/developer/' . $slug . '">[no image for "' . $slug .'"]</a></div>';
        }
      }
      
    }
    
    $out .= '</div>';
    $out .= '</div>';
   
    $out .= '<script> jQuery(document).ready( function(){ jQuery(".dts-developer-slider-' . $mode . ' .slick-slider").slick({}); });';
    
    $out .= '</script>';
    return $out;
  }
  
}

