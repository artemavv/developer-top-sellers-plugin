# Show Best selling developers and their products

This Wordpress plugin operates with assumption that your WooCommerce website has custom **developer** taxonomy for WooCommerce products.

Each product can belong to one tag from *developer* taxonomy . If that is your setup, then this plugin will be able to gather sales statistics and find top seller developers. 

Currently plugin tracks developer sales for the last 30 days ( each day's sales counted using a daily cron job).

You can show a list top 30 developers (best selling ones, ordered alphabetically) by using **[top_selling_developers]** shortcode. Users can click on "Show all developers" button to expand that list.

This same shortcode also shows top 30 products (ordered by sales) - to view it, users can click the switcher link under the list.

Here is an example of the shortcode usage, with all availavle shortcode parameters:

`[top_selling_developers title="TOP 30 DEVELOPERS" products_title="BESTSELLING PRODUCTS" show_products_label="Show Bestselling Products" show_developers_label="Show Top Developers"  all_developers_title="ALL DEVELOPERS" show_all_developers_label="Show all developers"]`

There is also separate **[weekly_bestsellers_slider]** shortcode provided by this plugin which shows a slider with 5 developers with best sales in the last week.