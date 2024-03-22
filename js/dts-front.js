

// button to show dev list
let developer_switcher_el = document.getElementById('dts-show-developer-list'); 

// button to show product list
let product_switcher_el   = document.getElementById('dts-show-product-list');

// button to show all devs
let show_all_devs_el      = document.getElementById('dts-show-all-developers'); 

// dev list
let developer_list_el     = document.getElementById('dts-developer-list-container');

// product list
let product_list_el       = document.getElementById('dts-product-list-container');

let top_devs_el           = document.getElementById('dts-list-top-sellers'); 
let top_devs_header_el    = document.getElementById('dts-list-top-sellers-header'); 

let all_devs_el           = document.getElementById('dts-list-all-sellers'); 
let all_devs_header_el    = document.getElementById('dts-list-all-sellers-header'); 

/* Code to switch between 'show products' mode and 'show developers' mode */


/* 'Show developers' is clicked */

developer_switcher_el.addEventListener('click', function( e ) {
  
  // hide products container and show the container for developers
  
  this.style.display = 'none';
  product_switcher_el.style.display     = 'block';
  developer_list_el.style.display       = 'block';
  product_list_el.style.display         = 'none';
  
  // show "top sellers" list and hide "all developers" list
  
  show_all_devs_el.style.display        = 'block';
  top_devs_el.style.display             = 'flex';
  top_devs_header_el.style.display      = 'block';
  
  all_devs_header_el.style.display      = 'none';
  all_devs_el.style.display             = 'none';
});

/* 'Show products' is clicked */

product_switcher_el.addEventListener('click', function( e ) {
  
  // hide developer container and show the container for producrs
  
  this.style.display                    = 'none';
  developer_switcher_el.style.display   = 'block';
  product_list_el.style.display         = 'block';
  developer_list_el.style.display       = 'none';
});
      
/* Code to show all developers */

show_all_devs_el.addEventListener('click', function( e ) {
  this.style.display                    = 'none';
  top_devs_el.style.display             = 'none';
  top_devs_header_el.style.display      = 'none';
  
  all_devs_header_el.style.display      = 'block';
  all_devs_el.style.display             = 'flex';
});