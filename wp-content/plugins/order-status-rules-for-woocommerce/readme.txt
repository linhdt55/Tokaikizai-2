=== Scheduled & Automatic Order Status Controller for WooCommerce ===
Contributors: wpcodefactory, algoritmika, anbinder, karzin, omardabbas, kousikmukherjeeli
Tags: woocommerce, order status, order, status, woo commerce
Requires at least: 4.4
Tested up to: 6.7
Stable tag: 3.7.2
License: GNU General Public License v3.0
License URI: http://www.gnu.org/licenses/gpl-3.0.html

Automate WooCommerce order statuses. Beautifully.

== Description ==

**Scheduled & Automatic Order Status Controller for WooCommerce** plugin lets you schedule automatic WooCommerce order status changes.

For example: automatically change order status to cancelled, when 24 hours have passed since order status was set to pending.

### &#9989; Order Status Rule Options ###

* Order **status from**.
* Order **status to**.
* **Time trigger** (in seconds, minutes, hours, days or weeks).
* Minimum and maximum **order amount** and **order quantity**.
* Required **payment gateways**.
* Required **shipping methods**.
* Required billing and shipping **countries**.
* Required **products**, product **categories**, product **tags** or product **stock status**.
* Required **users**, user **roles** or billing **emails**.
* Required **coupons**.
* And more...

### &#129309; Compatibility ###

* [High-Performance Order Storage (HPOS)](https://woocommerce.com/document/high-performance-order-storage/).
* [WooCommerce Subscriptions](https://woocommerce.com/products/woocommerce-subscriptions/) plugin.

### &#128472; Feedback ###

* We are open to your suggestions and feedback. Thank you for using or trying out one of our plugins!
* [Visit plugin site](https://wpfactory.com/item/order-status-rules-for-woocommerce/).

== Installation ==

1. Upload the entire plugin folder to the `/wp-content/plugins/` directory.
2. Activate the plugin through the "Plugins" menu in WordPress.
3. Start by visiting plugin settings at "WooCommerce > Settings > Order Status Rules".

== Changelog ==

= 3.7.2 - 12/03/2025 =
* Fix - Fixed an Open Redirection vulnerability in the admin.
* WC tested up to: 9.7.

= 3.7.1 - 20/12/2024 =
* Dev - Recommendations - Library updated.
* Dev - Key Manager - Library updated.
* Dev - Coding standards improved.
* WC tested up to: 9.5.
* Tested up to: 6.7.
* Plugin name updated.

= 3.7.0 - 21/10/2024 =
* Dev - Plugin settings moved to the "WPFactory" menu.
* Dev - "Recommendations" added.
* Dev - Key Manager - Library updated.
* Dev - Code refactoring.

= 3.6.0 - 04/10/2024 =
* Dev - "Key Manager" added.
* WC tested up to: 9.3.

= 3.5.5 - 30/08/2024 =
* Fix - Possible "Unsupported operand types" PHP error fixed.
* WC tested up to: 9.2.

= 3.5.4 - 30/07/2024 =
* Fix - Admin Options - Settings Tools - "Reset section settings" tool fixed (it was resetting settings for all rules).
* Dev - Admin Options - Settings Tools - "Copy settings" tool added.
* Dev - Rule - Conditions - Products/categories/tags/stock status - Action - "Exclude all" option added.
* Tested up to: 6.6.
* WC tested up to: 9.1.

= 3.5.3 - 13/06/2024 =
* Dev - Conditions - Order meta - "Meta compare" option added (defaults to "Equals").
* WC tested up to: 8.9.
* WooCommerce added to the "Requires Plugins" (plugin header).

= 3.5.2 - 30/04/2024 =
* Dev - Rule - Conditions - Order Users - Paying customer - Possible PHP warning fixed.
* Tested up to: 6.5.
* WC tested up to: 8.8.

= 3.5.1 - 06/03/2024 =
* Dev - Admin settings descriptions updated.
* WC tested up to: 8.6.

= 3.5.0 - 30/01/2024 =
* Dev - Rule - "Skip dates" option added.
* Dev - PHP 8.2 compatibility - "Creation of dynamic property is deprecated" notice fixed.
* Dev - Code refactoring.
* Tested up to: 6.4.
* WC tested up to: 8.5.

= 3.4.2 - 23/09/2023 =
* WordPress plugin logo, banner updated.

= 3.4.1 - 20/09/2023 =
* Dev - Developers - `alg_wc_order_status_rules_wc_get_orders` filter added.
* WC tested up to: 8.1.

= 3.4.0 - 04/09/2023 =
* Fix - Declaring HPOS compatibility for the free plugin version, even if the Pro version is activated.
* Dev - Advanced - Orders Query - "Max orders" option added (defaults to `-1`).
* Tested up to: 6.3.
* WC tested up to: 8.0.

= 3.3.0 - 16/07/2023 =
* Dev - Advanced - Process rules on - "Subscription status changed" and "Admin "Edit subscription" page" options added.
* Dev - Advanced - "Statuses" option added. Possible values: "WooCommerce Order Statuses" and "WooCommerce Subscription Statuses". Defaults to "WooCommerce Order Statuses".
* Dev - Advanced - "Save status change on" option added. Possible values: "Order status changed" and "Subscription status changed". Defaults to "Order status changed".
* Dev - Advanced - "Meta box" option added. Possible values: "Orders" and "Subscriptions". Defaults to "Orders".
* Dev - Advanced - Orders Query - "Order types" option added. Possible values: "Orders" and "Subscriptions". Defaults to "Orders".
* Dev - Advanced - Admin settings - "Rules processing hooks" renamed to "Process rules on".
* Dev - Admin settings sections rearranged - "Tools", "My Account", "Extra" sections added. "Advanced > Orders Query Options" subsection added.
* Dev - Code refactoring.
* Dev - Developers - `alg_wc_order_status_rules_wc_get_orders_args` filter added.

= 3.2.0 - 10/07/2023 =
* Dev – "High-Performance Order Storage (HPOS)" compatibility.

= 3.1.0 - 04/07/2023 =
* Dev - Advanced - "Default order status" option added.
* Dev - Advanced - "Process Payment Order Status" options added ("Direct bank transfer", "Check payments", "Cash on delivery (COD)").

= 3.0.3 - 26/06/2023 =
* Dev - Developers - `alg_wc_order_status_rules_hooks_priority` filter added.

= 3.0.2 - 18/06/2023 =
* WC tested up to: 7.8.

= 3.0.1 - 08/05/2023 =
* Tested up to: 6.2.
* WC tested up to: 7.6.

= 3.0.0 - 10/02/2023 =
* Fix - Admin settings - General - Reset Settings - Now displaying the correct "Total rules" on settings reset.
* Dev - Admin - `DISABLE_WP_CRON` notice removed. Instead, a similar message added to the "Advanced > Periodical Processing Options" section description.
* Dev - Advanced - Rules processing hooks - "Checkout order processed" option added.
* Dev - Advanced - Rules processing hooks - '"Thank you" (i.e., "Order received") page' option added.
* Dev - Advanced - Rules processing hooks - 'Admin "Edit order" page' option added.
* Dev - Order status change history - `get_order_status_change_history()` - Code refactoring.

= 2.9.3 - 23/01/2023 =
* Fix - Advanced - Rules processing hooks - Now properly handling the `process_rules_for_order()` callback on order status update (`woocommerce_order_status_changed` action).
* Dev - Developers - `alg_wc_order_status_rules_check_dates_order_date` filter added.
* Tested up to: 6.1.
* WC tested up to: 7.3.

= 2.9.2 - 17/11/2022 =
* Dev - Developers - `alg_wc_order_status_rules_before_rule_applied` and `alg_wc_order_status_rules_after_rule_applied` actions added.
* WC tested up to: 7.1.

= 2.9.1 - 20/10/2022 =
* Dev - Admin settings updated.
* Readme.txt updated.
* WC tested up to: 7.0.

= 2.9.0 - 11/08/2022 =
* Dev - Rule - Conditions - Products/categories/tags/stock status - "Require all" option is now a dropdown with an additional "Exclude" value.
* Dev - Rule - Conditions - "Billing emails" option added.
* Dev - Rule - Conditions - Minimum/Maximum amount - "Minimum/Maximum order amount type" options added. Defaults to "Order subtotal". Another possible value is "Order total".
* Dev - Admin settings updated; subsections added; etc.
* Dev - Minor code refactoring.
* WC tested up to: 6.8.

= 2.8.2 - 27/07/2022 =
* Dev - Process rules - Extra safe-checks added for the orders.
* Dev - Deploy script added.
* Tested up to: 6.0.
* WC tested up to: 6.7.

= 2.8.1 - 21/04/2022 =
* Fix - Rule - Conditions - Coupons - "Any coupon" option was ignored (plugin was processing the "Specific coupon(s)" option instead). This is fixed now.
* Dev - Rule - Conditions - Coupons - "No coupons" option added.

= 2.8.0 - 20/04/2022 =
* Dev - Rule - Conditions - "Coupons" options added.
* Dev - Rule - Conditions - "Paying customer" options added.
* Dev - Admin settings descriptions updated.
* Dev - Code refactoring.

= 2.7.2 - 15/04/2022 =
* Fix - Admin settings - Users - "Guest" user was removed by mistake in v2.6.1. This is fixed now.
* Dev - Code refactoring.

= 2.7.1 - 14/04/2022 =
* Fix - Advanced - Disabled conditions - "Billing countries" and "Shipping countries" options could not be disabled. This is fixed now.

= 2.7.0 - 14/04/2022 =
* Dev - Rule - Conditions - "Product stock status" options added.
* Dev - Rule - Conditions - "Billing countries" and "Shipping countries" options added.
* Dev - Rule - Conditions - "All products in the order must match the selection (vs at least one product)" options added to "Products", "Product categories" and "Product tags" conditions. Defaults to `no`.

= 2.6.1 - 13/04/2022 =
* Dev - Admin settings - Users - Using AJAX now.
* Dev - Code refactoring.
* WC tested up to: 6.4.

= 2.6.0 - 08/04/2022 =
* Dev - Advanced - "Disabled conditions" option added.
* Dev - Code refactoring.
* WC tested up to: 6.3.

= 2.5.1 - 26/01/2022 =
* Dev - Advanced - "Orders sorting" options added.
* Tested up to: 5.9.
* WC tested up to: 6.1.

= 2.5.0 - 17/01/2022 =
* Dev - Conditions - Products - Admin settings are using AJAX now.
* Dev - Code refactoring.
* WC tested up to: 6.0.

= 2.4.1 - 14/12/2021 =
* Dev - Rule - Conditions - "Date created before" and "Date created after" options added.

= 2.4.0 - 09/12/2021 =
* Dev - Rule - Conditions - Order meta - "Multiple meta values" option added (i.e., multiple values are now allowed in the "Meta value" option, as a comma-separated list).
* Dev - Rule - Skip days - Algorithm improved.
* Dev - Order Status History - "No order status rules are scheduled to be applied..." message added.
* Dev - Code refactoring.
* WC tested up to: 5.9.

= 2.3.0 - 27/10/2021 =
* Dev - Advanced - Periodical Processing - "Action Scheduler" options added.
* Dev - Advanced - "Rules processing hooks" option added (defaults to `Order status changed`). Can be filtered with the `alg_wc_order_status_rules_hooks` filter.
* Dev - Advanced - Admin settings rearranged: "Periodical Processing Options", "Order Status History Options" subsections added. Settings descriptions updated.

= 2.2.0 - 22/10/2021 =
* Fix - Admin settings - The issue when "Total rules" option has just been changed, but number of rule settings tabs doesn't reflect it, is fixed now.
* Dev - Triggering order status rules processing on any order status change now (i.e., rules with zero time triggers will be processed immediately now).

= 2.1.0 - 21/10/2021 =
* Dev - Rule - "Shipping methods" option added.
* Dev - Rule - "Minimum order quantity" and "Maximum order quantity" options added.
* Dev - Advanced - "On non-matching order status" option added.
* Dev - Safe-checks added when retrieving order's payment gateway.
* Dev - Admin "Rule" settings restyled ("Conditions" subsection added).
* Dev - Code refactoring.
* WC tested up to: 5.8.

= 2.0.0 - 06/09/2021 =
* Dev - Admin settings rearranged. Now each order status rule has its own settings section. Tools moved from the "Advanced" section to the "General" section.
* Dev - Rule - "Order meta" options added.
* Dev - Optional `alg_wc_order_status_rules_process_rules_redirect` URL param added.
* Dev - Developers - `alg_wc_order_status_rules_do_apply_rule` filter added.
* Dev - Developers - `alg_wc_order_status_after_save_settings` filter renamed to `alg_wc_order_status_rules_after_save_settings`.

= 1.9.0 - 25/08/2021 =
* Dev - Rule - "User roles" option added.
* Dev - Rule - "Users" option added.
* Dev - Rule - Minimum/Maximum order amount - Decimal values are allowed in settings now (step set to `0.000001`).
* WC tested up to: 5.6.

= 1.8.1 - 11/08/2021 =
* Dev - Rule - "Minimum order amount" and "Maximum order amount" options added.
* Dev - Rule - Admin settings - "Select all" and "Deselect all" buttons added to all "multiselect" options.

= 1.8.0 - 02/08/2021 =
* Dev - Rule - Time trigger - It's possible to set the value to zero now.
* Dev - Advanced & Tools - "My Account > Orders" options added.
* Dev - Advanced & Tools - On empty order status change history - Defaults to "Use order date modified" now.
* Dev - Save status change - Hook priority set to `10` now (was `PHP_INT_MAX`).
* Dev - Admin settings descriptions updated.
* Dev - Code refactoring.

= 1.7.0 - 29/07/2021 =
* Fix - PHP error fixed. Was occurring when creating a new order by admin with "On empty order status change history" option set to "Use order date ...".
* Dev - Advanced - Compatibility Options - "Doctreat" option added.
* Dev - Debug - Now adding to the log if rules was processed manually (i.e., via "Tools > Run all rules now").
* Dev - Plugin is initialized on `plugins_loaded` action now.
* Dev - Code refactoring.
* Tested up to: 5.8.
* WC tested up to: 5.5.

= 1.6.1 - 13/05/2021 =
* Fix - "PHP Parse error" fixed.

= 1.6.0 - 11/05/2021 =
* Dev - Rule - "Products" option added.
* Dev - Rule - "Product categories" option added.
* Dev - Rule - "Product tags" option added.
* Dev - Order Status History - Descriptions updated in the meta box, e.g., rule title added.
* Dev - Code refactoring.

= 1.5.0 - 20/04/2021 =
* Dev - Rule - "Payment gateways" option added.
* Dev - Admin settings restyled: new "Advanced & Tools" section added.
* Dev - Minor code refactoring.
* Tested up to: 5.7.
* WC tested up to: 5.2.

= 1.4.0 - 26/02/2021 =
* Dev - Advanced - Use WP cron - "WP cron interval" option added (defaults to "Once Hourly").
* Dev - Advanced - "On empty order status change history" option added.
* Dev - Rule - Time trigger - "Unit" option added (defaults to "hour(s)").
* Dev - Rule - Time trigger - Defaults to `1` now.
* Dev - Advanced - "Debug" option added.
* Dev - Localization - `load_plugin_textdomain()` moved to the `init` hook.
* Dev - Admin descriptions updated.
* Dev - Code refactoring.
* Tested up to: 5.6.
* WC tested up to: 5.0.

= 1.3.1 - 17/09/2020 =
* Dev - Allow rules processing via URL - Hook priority increased.

= 1.3.0 - 15/09/2020 =
* Dev - Advanced - "Use WP cron" option added (defaults to `yes`).
* Dev - Advanced - "Allow rules processing via URL" option added (defaults to `no`). This is an alternative to WP crons (i.e., allows using "real" (i.e., server) cron jobs instead).
* WC tested up to: 4.5.
* Tested up to: 5.5.

= 1.2.0 - 27/03/2020 =
* Fix - "Reset settings" admin notice fixed.
* Dev - Tools - "Run all rules now" tool added.
* Dev - "Next cron event is scheduled on ..." info added to admin settings.
* Dev - Code refactoring.
* Dev - Admin settings descriptions updated.
* WC tested up to: 4.0.
* Tested up to: 5.3.

= 1.1.0 - 11/07/2019 =
* Dev - Admin settings descriptions updated. "Your settings have been reset" notice added.
* Dev - "Total rules" default value changed to `1`.
* Dev - Rule - "Admin title (optional)" option added.
* Dev - Rule - "Enable/Disable" default value changed to `no`.
* Dev - Code refactoring.
* Plugin URI updated.
* Tested up to: 5.2.
* WC tested up to: 3.6.

= 1.0.1 - 07/06/2018 =
* Fix - Break added in `process_rules()` function, so multiple status updates wouldn't happen at once.
* Dev - "Skip days" rules options added.

= 1.0.0 - 16/05/2018 =
* Initial Release.

== Upgrade Notice ==

= 1.0.0 =
This is the first release of the plugin.
