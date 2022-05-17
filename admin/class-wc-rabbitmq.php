<?php

/**
 * The file that defines the core plugin class
 *
 * A class definition that includes attributes and functions used across both the
 * public-facing side of the site and the admin area.
 *
 * @link       https://www.fiverr.com/junaidzx90
 * @since      1.0.0
 *
 * @package    Wc_Rabbitmq
 * @subpackage Wc_Rabbitmq/admin
 */

/**
 * The core plugin class.
 *
 * This is used to define internationalization, admin-specific hooks, and
 * public-facing site hooks.
 *
 * Also maintains the unique identifier of this plugin as well as the current
 * version of the plugin.
 *
 * @since      1.0.0
 * @package    Wc_Rabbitmq
 * @subpackage Wc_Rabbitmq/admin
 * @author     Developer Junayed <admin@easeare.com>
 */
class Wc_Rabbitmq {
	/**
	 * The unique identifier of this plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string    $plugin_name    The string used to uniquely identify this plugin.
	 */
	protected $plugin_name;

	/**
	 * The current version of the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string    $version    The current version of the plugin.
	 */
	protected $version;

	/**
	 * Define the core functionality of the plugin.
	 *
	 * Set the plugin name and the plugin version that can be used throughout the plugin.
	 * Load the dependencies, define the locale, and set the hooks for the admin area and
	 * the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function __construct() {
		if ( defined( 'WC_RABBITMQ_VERSION' ) ) {
			$this->version = WC_RABBITMQ_VERSION;
		} else {
			$this->version = '1.0.0';
		}
		$this->plugin_name = 'wc-rabbitmq';
	}

	/**
	 * Run the loader to execute all of the hooks with WordPress.
	 *
	 * @since    1.0.0
	 */
	public function run() {
		add_action( 'admin_enqueue_scripts', [$this, 'enqueue_styles'] );
		add_action( 'wp_enqueue_scripts', [$this, 'public_enqueue_styles'] );
		add_action( 'init', [$this, '_export'] );
		add_action( 'admin_menu', [$this, 'rabbit_menupage'] );

		add_action( 'woocommerce_checkout_order_processed', [$this, 'rabbit_msg_action'], 10, 3 );

		add_action( 'edit_user_profile', [$this, 'wcrm_custom_user_profile_fields'] );

		add_action( 'edit_user_profile_update', [$this, 'wcrm_save_custom_user_profile_fields'] );

		add_action( 'wp_ajax_import_customers', [$this, 'import_customers'] );
		add_action( 'wp_ajax_nopriv_import_customers', [$this, 'import_customers'] );

		add_action( 'wp_ajax_import_customer_address', [$this, 'import_customer_address'] );
		add_action( 'wp_ajax_nopriv_import_customer_address', [$this, 'import_customer_address'] );

		// Dashoaered address
		add_action( "woocommerce_after_edit_account_address_form", [$this, "woocommerce_after_edit_account_address_form_callback"] );

		add_filter( 'woocommerce_billing_fields', [$this, 'remove_woocommerce_billing_fields'] );
		add_filter( 'woocommerce_shipping_fields', [$this, 'remove_woocommerce_shipping_fields'] );
		add_filter( 'woocommerce_checkout_get_value', [$this, 'adding_default_address_values'], 10, 2 );
		add_filter( 'woocommerce_checkout_fields' , [$this, 'remove_checkout_comment_box'], 99 );

		add_action( 'wp_ajax_unset_selected_address', [$this, 'unset_selected_address'] );
		add_action( 'wp_ajax_nopriv_unset_selected_address', [$this, 'unset_selected_address'] );

		add_action( 'wp_ajax_set_selected_address', [$this, 'set_selected_address'] );
		add_action( 'wp_ajax_nopriv_set_selected_address', [$this, 'set_selected_address'] );

		add_action( 'wp_ajax_delete_additional_address', [$this, 'delete_additional_address'] );
		add_action( 'wp_ajax_nopriv_delete_additional_address', [$this, 'delete_additional_address'] );
	}

	function enqueue_styles() {
		wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ )."css/wc-rabbitmq-admin.css", array(), $this->version, "all" );
		wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ )."js/wc-rabbitmq-admin.js", array('jquery'), $this->version, true );
		wp_localize_script( $this->plugin_name, 'wcrabbit_ajax', array(
			'ajaxurl' => admin_url("admin-ajax.php"),
			'nonce' => wp_create_nonce( "wcrabbit_nonce" )
		) );
	}

	function public_enqueue_styles() {
		wp_enqueue_style( $this->plugin_name, WC_RABBITMQ_URL."public/css/wc-rabbitmq.css", array(), $this->version, "all" );
		
		wp_enqueue_script( $this->plugin_name, WC_RABBITMQ_URL."public/js/wc-rabbitmq.js", array('jquery'), $this->version, true );
		wp_localize_script( $this->plugin_name, 'wcrabbit_ajax', array(
			'ajaxurl' => admin_url("admin-ajax.php"),
			'nonce' => wp_create_nonce( "wcrabbit_nonce" )
		) );
	}

	function user_has_role($user_id, $role_name){
		$user_meta = get_userdata($user_id);
		$user_roles = $user_meta->roles;
		return in_array($role_name, $user_roles);
	}

	function wcrm_custom_user_profile_fields( $user ){
		global $wpdb;
		
		$user_id = $user->data->ID;

		if(!$this->user_has_role($user_id, 'customer')){
			return;
		}

		$results = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}wcrabbit_customers WHERE user_id = {$user->ID}");

		$customer_id = '';
		$addresses = [];
		if($results){
			foreach($results as $result){
				$id = $result->ID;
				$customer_id = $result->customer_id;
				$address_id = $result->address_id;
				$type = $result->type;
				$country = $result->country;
				$city = $result->city;
				$address = $result->address;

				$address = [
					'ID' => $id,
					'type' => $type,
					'address_id' => $address_id,
					'city' => $city,
					'country' => $country,
					'address' => $address
				];

				$addresses[] = $address;
			}
		}
		?>
		<h3 class="heading">RabbitMQ Fields</h3>
		<hr>
		
		<div id="customer_informations">
			
			<div class="wcrm_inputs">
				<label for="wcrm_customer_id">Customer ID</label>
				<input type="number" name="wcrm_customer_id" id="wcrm_customer_id" value="<?php echo $customer_id ?>"/>
			</div>

			<fieldset>
				<legend>Addresses</legend>
				<table>
					<thead>
						<tr>
							<th>Type of address</th>
							<th>Address ID</th>
							<th>City</th>
							<th>Country</th>
							<th>Address</th>
						</tr>
					</thead>
					<tbody id="wcrm_rows">
						<?php 
						if(sizeof($addresses)>0){
							foreach($addresses as $address){
								?>
								<tr>
									<td>
										<select name="wcrm_fields[<?php echo $address['ID'] ?>][type]" id="type_of_address">
											<option <?php echo (($address['type'] === 'billing') ? 'selected' : '') ?> value="billing">Billing</option>
											<option <?php echo (($address['type'] === 'shipping') ? 'selected' : '') ?> value="shipping">Shipping</option>
										</select>
									</td>
									<td>
										<input type="number" name="wcrm_fields[<?php echo $address['ID'] ?>][address_id]" id="wcrm_address_id" value="<?php echo $address['address_id'] ?>" />
									</td>
									<td>
										<input type="text" name="wcrm_fields[<?php echo $address['ID'] ?>][city]" id="wcrm_city" value="<?php echo $address['city'] ?>" />
									</td>
									<td>
										<input type="text" name="wcrm_fields[<?php echo $address['ID'] ?>][country]" id="wcrm_country" value="<?php echo $address['country'] ?>" />
									</td>
									<td>
										<input type="text" name="wcrm_fields[<?php echo $address['ID'] ?>][address_line]" id="address_line" value="<?php echo $address['address'] ?>" />
									</td>
									<td style="width: 20px; position: relative;">
										<span class="removeAddr">+</span>
									</td>
								</tr>
								<?php
							}
						}else{
							echo "<tr class='noaddr'>
							<td>No address added.</td>
							</tr>";
						}
						?>
					</tbody>
				</table>				
			</fieldset>
		</div>

		<button id="add_address" class="button-secondary">Add new address</button>
		<?php
	}

	/**
	*   @param User Id $user_id
	*/
	function wcrm_save_custom_user_profile_fields( $user_id ){
		try {
			global $wpdb;
			if(!$this->user_has_role($user_id, 'customer')){
				return;
			}

			$wcrm_customer_id = $_POST['wcrm_customer_id'];
			$fields = [];
			if(isset($_POST['wcrm_fields'])){
				$fields = $_POST['wcrm_fields'];
			}
			
			$address = array_values($fields);

			if(is_array($address)){
				$wpdb->query("DELETE FROM {$wpdb->prefix}wcrabbit_customers WHERE user_id = $user_id");

				foreach($address as $addr){
					$wpdb->insert($wpdb->prefix.'wcrabbit_customers', array(
						'user_id' => $user_id,
						'customer_id' => $wcrm_customer_id,
						'address_id' => $addr['address_id'],
						'type' => $addr['type'],
						'country' => $addr['country'],
						'city' => $addr['city'],
						'address' => $addr['address_line'],
						'create_date' => date("Y-m-d h:i:s a")
					));
				}
			}
		} catch (\Throwable $th) {
			//throw $th;
		}
	}

	function rabbit_menupage(){
		add_options_page( "WC RabbitMq", "WC RabbitMq", "manage_options", "wc-rabbitmq", [$this, "rabbitmq_menupage_callback"], null );

		add_settings_section( 'wcrabbitmq_opt_section', '', '', 'wcrabbitmq_opt_page' );

		// Host
		add_settings_field( 'wcrabbitmq_host', 'Host', [$this, 'wcrabbitmq_host_cb'], 'wcrabbitmq_opt_page','wcrabbitmq_opt_section' );
		register_setting( 'wcrabbitmq_opt_section', 'wcrabbitmq_host' );
		// Port
		add_settings_field( 'wcrabbitmq_port', 'Port', [$this, 'wcrabbitmq_port_cb'], 'wcrabbitmq_opt_page','wcrabbitmq_opt_section' );
		register_setting( 'wcrabbitmq_opt_section', 'wcrabbitmq_port' );
		// Username
		add_settings_field( 'wcrabbitmq_username', 'Username', [$this, 'wcrabbitmq_username_cb'], 'wcrabbitmq_opt_page','wcrabbitmq_opt_section' );
		register_setting( 'wcrabbitmq_opt_section', 'wcrabbitmq_username' );
		// Password
		add_settings_field( 'wcrabbitmq_password', 'Password', [$this, 'wcrabbitmq_password_cb'], 'wcrabbitmq_opt_page','wcrabbitmq_opt_section' );
		register_setting( 'wcrabbitmq_opt_section', 'wcrabbitmq_password' );
		// Queue Name
		add_settings_field( 'wcrabbitmq_queueu_name', 'Queue Name', [$this, 'wcrabbitmq_queueu_name_cb'], 'wcrabbitmq_opt_page','wcrabbitmq_opt_section' );
		register_setting( 'wcrabbitmq_opt_section', 'wcrabbitmq_queueu_name' );
		
	}

	function wcrabbitmq_host_cb(){
		echo '<input type="text" placeholder="173.212.227.2" name="wcrabbitmq_host" value="'.get_option('wcrabbitmq_host').'">';
	}
	function wcrabbitmq_port_cb(){
		echo '<input type="number" placeholder="5672" name="wcrabbitmq_port" value="'.get_option('wcrabbitmq_port').'">';
	}
	function wcrabbitmq_username_cb(){
		echo '<input type="text" placeholder="guest" name="wcrabbitmq_username" value="'.get_option('wcrabbitmq_username').'">';
	}
	function wcrabbitmq_password_cb(){
		echo '<input type="text" placeholder="guest" name="wcrabbitmq_password" value="'.get_option('wcrabbitmq_password').'">';
	}
	function wcrabbitmq_queueu_name_cb(){
		echo '<input type="text" name="wcrabbitmq_queueu_name" value="'.get_option('wcrabbitmq_queueu_name').'">';
	}

	function rabbitmq_menupage_callback(){ ?>
		<h3>Setting</h3>
		<hr>

		<div id="rabbit_wrapper">
			<form id="rabbit-setting-form" method="post" action="options.php">
				<?php
				settings_fields( 'wcrabbitmq_opt_section' );
				do_settings_sections('wcrabbitmq_opt_page');
				echo get_submit_button( 'Save Changes', 'secondary', 'save-rabbit-setting' );
				?>
			</form>

			<div method="post" class="export__imports">
				<div id="importAlert"></div>

				<div class="export_buttons">
					<a href="?page=wc-rabbitmq&rabbitaction=export-users" class="button-secondary">Export Users</a>
					<a href="?page=wc-rabbitmq&rabbitaction=export-address" class="button-secondary">Export Addresses</a>
				</div>

				<div class="inp_box">
					<label for="import_users">Import customers</label>
					<input type="file" id="import_rabbit_users" name="rabbit_users_csv">
					<button class="button-primary" id="import_rabbit_users_btn">Import customers</button>
				</div>
				<div class="inp_box">
					<label for="import_addresses">Import addresses</label>
					<input type="file" id="import_rabbit_addresses" name="rabbit_addresses_csv">
					<button class="button-primary" id="import_rabbit_addresses_btn">Import addresses</button>
				</div>
			</div>
		</div>
		

		<?php
	}

	// Export event customer info
	function _export(){
		global $wpdb;
		if(isset($_GET['page']) && $_GET['page'] === "wc-rabbitmq" && isset($_GET['rabbitaction']) && $_GET['rabbitaction'] === "export-users"){
			$customers = get_users( array( 
				'role' => 'customer',
				'order' => 'ASC',
    			'orderby' => 'display_name',
				'number' => -1,
				'fields' => [
					'ID',
					'display_name',
					'user_email'
				]
			 ) );

			if(is_array($customers)){
				$delimiter = ","; 
				$filename = "customers-" . date('Y-m-d') . ".csv";
				
				// Create a file pointer 
				$csf = fopen('php://output', 'w');
				
				// Set column headers 
				$fields = array('External ID', 'Name', 'Email'); 
				fputcsv($csf, $fields, $delimiter); 

				foreach($customers as $customer){
					$external_id = $wpdb->get_var("SELECT customer_id FROM {$wpdb->prefix}wcrabbit_customers WHERE user_id = {$customer->ID}");

					$lineData = array(
						$external_id,
						$customer->display_name,
						$customer->user_email,
					); 

					fputcsv($csf, $lineData, $delimiter);
				}

				// reset the file pointer to the start of the file
				fseek($csf, 0);
				// tell the browser it's going to be a csv file
				header('Content-Type: application/csv');
				// tell the browser we want to save it instead of displaying it
				header('Content-Disposition: attachment; filename="'.$filename.'";');
				header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
				// make php send the generated csv lines to the browser
				fpassthru($csf);
				exit;
			}
		}

		if(isset($_GET['page']) && $_GET['page'] === "wc-rabbitmq" && isset($_GET['rabbitaction']) && $_GET['rabbitaction'] === "export-address"){

			$customers = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}wcrabbit_customers ORDER BY user_id");

			if($customers){
				$delimiter = ","; 
				$filename = "addresses-" . date('Y-m-d') . ".csv";
				
				// Create a file pointer 
				$csf = fopen('php://output', 'w');
				
				// Set column headers 
				$fields = array('Customer Ext ID', 'Address Ext ID', 'Address Type', 'Country', 'City', 'Address'); 
				fputcsv($csf, $fields, $delimiter); 

				foreach($customers as $customer){
					if(get_user_by( "ID", $customer->user_id )){
						$lineData = array(
							$customer->customer_id,
							$customer->address_id,
							$customer->type,
							$customer->country,
							$customer->city,
							$customer->address
						); 
	
						fputcsv($csf, $lineData, $delimiter);
					}else{
						$wpdb->query("DELETE FROM {$wpdb->prefix}wcrabbit_customers WHERE user_id = {$customer->user_id}");
						return;
					}
				}

				// reset the file pointer to the start of the file
				fseek($csf, 0);
				// tell the browser it's going to be a csv file
				header('Content-Type: application/csv');
				// tell the browser we want to save it instead of displaying it
				header('Content-Disposition: attachment; filename="'.$filename.'";');
				header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
				// make php send the generated csv lines to the browser
				fpassthru($csf);
				exit;
			}
		}

		return;
	}
	
	function import_customers(){
		if(!wp_verify_nonce( $_POST['nonce'], "wcrabbit_nonce" )){
			die("Invalid Request!");
		}

		if(isset($_POST['rows'])){
			$data = $_POST['rows'];

			global $wpdb;

			$rows = [];
			for($i=1; $i < sizeof($data); $i++) {
				$row = stripcslashes($data[$i]);
				$row = explode(",", $row);
				if($row[0] !== ""){
					$arr = array(
						'external_id' => str_replace(array('\'', '"'), '', $row[0] ),
						'name' => str_replace(array('\'', '"'), '', $row[1] ),
						'email' => str_replace(array('\'', '"'), '', $row[2] )
					);
					$rows[] = $arr;
				}
			}

			$updated = 0;
			$added = 0;

			if(sizeof($rows) > 0){
				foreach($rows as $row){
					set_time_limit(0);
					$external_id = $row['external_id'];
					$name = sanitize_text_field( $row['name'] );
					$email = sanitize_email( $row['email'] );
					
					if($external_id && $email){
						$user_id = $wpdb->get_var("SELECT user_id FROM {$wpdb->prefix}wcrabbit_customers WHERE customer_id = '$external_id'");
					
						if($user_id){
							wp_update_user( array( 'ID' => $user_id, 'display_name' => $name ) );
							wp_update_user( array( 'ID' => $user_id, 'user_email' => $email ) );
							$updated += 1;
						}else{
							$exist_user = get_user_by( 'email', $email );

							if($exist_user){
								$user_id = $exist_user->ID;
							}else{
								$user_id = wc_create_new_customer($email, $name, rand());
							}
	
							if(!is_wp_error( $user_id )){
								$wpdb->insert($wpdb->prefix.'wcrabbit_customers', array(
									'user_id' => $user_id,
									'customer_id' => $external_id,
									'create_date' => date("Y-m-d h:i:s a"),
								),array("%s", "%s", "%s"));
								$added += 1;
							}
						}
					}
				}

				if($updated > 0 || $added > 0){
					echo json_encode(array("updated" => $updated, "added" => $added));
					die;
				}
				
			}
			
			echo json_encode(array("error" => "Error"));
			die;
		}

		echo json_encode(array("error" => "Error"));
		die;
	}

	function import_customer_address(){
		if(!wp_verify_nonce( $_POST['nonce'], "wcrabbit_nonce" )){
			die("Invalid Request!");
		}

		if(isset($_POST['rows'])){
			$data = $_POST['rows'];

			global $wpdb;

			$rows = [];
			for($i=1; $i < sizeof($data); $i++) {
				$row = stripcslashes($data[$i]);
				$row = explode(",", $row);
				if($row[0] !== ""){
					if(array_key_exists("5", $row)){
						$arr = array(
							'customer_id' => str_replace(array('\'', '"'), '', $row[0] ),
							'address_id' => str_replace(array('\'', '"'), '', $row[1] ),
							'type' => str_replace(array('\'', '"'), '', $row[2] ),
							'country' => str_replace(array('\'', '"'), '', $row[3] ),
							'city' => str_replace(array('\'', '"'), '', $row[4] ),
							'address' => str_replace(array('\'', '"'), '', $row[5] )
						);
						$rows[] = $arr;
					}
				}
			}

			if(is_array($rows) && sizeof($rows) > 0){
				$updated = 0;
				$inserted = 0;
				$error = true;

				foreach($rows as $row){
					set_time_limit(0);
					$customer_id = intval($row['customer_id']);
					$address_id = intval($row['address_id']);

					$address_ID = $wpdb->get_var("SELECT ID FROM {$wpdb->prefix}wcrabbit_customers WHERE customer_id = $customer_id AND `address_id` = $address_id");

					$user_id = $wpdb->get_var("SELECT user_id FROM {$wpdb->prefix}wcrabbit_customers WHERE customer_id = $customer_id");

					if($address_ID){
						$wpdb->update($wpdb->prefix.'wcrabbit_customers', array(
							'address_id' 	=> $row['address_id'],
							'type' 			=> $row['type'],
							'country' 		=> $row['country'],
							'city' 			=> $row['city'],
							'address' 		=> $row['address'],
						),array('ID' => $address_ID), array('%s', '%s', '%s', '%s', '%s'), array('%d'));

						$updated += 1;
						$error = false;
					}else{
						if($user_id){
							$wpdb->insert($wpdb->prefix.'wcrabbit_customers', array(
								'user_id' 		=> $user_id,
								'customer_id' 	=> $customer_id,
								'address_id' 	=> $row['address_id'],
								'type' 			=> $row['type'],
								'country' 		=> $row['country'],
								'city' 			=> $row['city'],
								'address' 		=> $row['address'],
								'create_date' 	=> date("Y-m-d h:i:s a")
							));
	
							$inserted += 1;
							$error = false;
						}
					}
				}
				
				if(!$error){
					echo json_encode(array("updated" => $updated, "added" => $inserted));
					die;
				}
			}
		}

		echo json_encode(array("error" => "Error"));
		die;
	}

	function woocommerce_after_edit_account_address_form_callback(){
		try {
			require_once WC_RABBITMQ_PATH."public/auditional-addresses.php";
		} catch (\Throwable $th) {
			echo "There is an error!";
		}
	}

	// Public functionalities
	function rabbit_msg_action($order_id, $posted_data, $order){
		global $wpdb;

		$messageWillSent = true;

		if(!$messageWillSent){
			return;
		}
		// Get an instance of the WC_Order object
		$order = wc_get_order( $order_id );

		if(!$order){
			return;
		}

		$order_payment_method_title = $order->get_payment_method_title();
		$order_timestamp_created = $order->get_date_created()->getTimestamp();
		$order_total = $order->get_total();

		// ## BILLING INFORMATION:
		$order_billing_city = $order->get_billing_city();
		$order_billing_country = $order->get_billing_country();
		$order_billing_address_1 = $order->get_billing_address_1();

		$billingAddress = array(
			'city' => $order_billing_city,
			'country' => wr_get_country_name("country", $order_billing_country),
			'address_1' => $order_billing_address_1
		);

		// ## SHIPPING INFORMATION:
		$order_shipping_city = $order->get_shipping_city();
		$order_shipping_country = $order->get_shipping_country();
		$order_shipping_address_1 = $order->get_shipping_address_1();

		$shippingAddress = array(
			'city' => $order_shipping_city,
			'country' => wr_get_country_name("country", $order_shipping_country),
			'address_1' => $order_shipping_address_1
		);

		$lines = [];
		// Get and Loop Over Order Items
		foreach ( $order->get_items() as $item_id => $item ) {
			$product = $item->get_product();
			$lines[] = array(
				'sku' => $product->get_sku(),
				'quantity' => $item->get_quantity(),
				'price' => $product->get_price(),
				'total' => $item->get_total(),
				'print' => 'S',
			);
		}

		$user_id = $order->get_user_id();
		if($user_id){
			$customerInfo = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}wcrabbit_customers WHERE user_id = $user_id");

			$customer_id = '';
			$billing_id = '';
			$shipping_id = '';

			if($customerInfo){
				foreach($customerInfo as $address){
					$customer_id = $address->customer_id;
					$address_id = $address->address_id;
					$addressType = $address->type;
					$country = $address->country;
					if(strlen($country) === 2){
						$country = wr_get_country_name("country", $country);
					}
					$city = $address->city;
					$address = $address->address;

					switch ($addressType) {
						case 'billing':
							if(strtolower($city) === strtolower($order_billing_city) && 
							strtolower($country) === strtolower($billingAddress['country']) && 
							strtolower($address) === strtolower($order_billing_address_1)){
								$billing_id = $address_id;
							}
							break;
						case 'shipping':
							if(strtolower($country) === strtolower($order_shipping_city) && 
							strtolower($country) === strtolower($shippingAddress['country']) && 
							strtolower($address) === strtolower($order_shipping_address_1)){
								$shipping_id = $address_id;
							}
							break;
					}
				}
			}

			if(empty($billing_id)){
				if(empty($customer_id)){
					$customer_id = $wpdb->get_var("SELECT customer_id FROM {$wpdb->prefix}wcrabbit_customers WHERE user_id = $user_id");
				}

				$wpdb->insert($wpdb->prefix.'wcrabbit_customers', array(
					'user_id' 		=> $user_id,
					'customer_id' 	=> $customer_id,
					'type' 			=> 'billing',
					'country' 		=> $order_billing_country,
					'city' 			=> $order_billing_city,
					'address' 		=> $order_billing_address_1,
					'create_date' 	=> date("Y-m-d h:i:s a")
				));
			}

			if(empty($shipping_id)){
				if(empty($customer_id)){
					$customer_id = $wpdb->get_var("SELECT customer_id FROM {$wpdb->prefix}wcrabbit_customers WHERE user_id = $user_id");
				}
				
				$wpdb->insert($wpdb->prefix.'wcrabbit_customers', array(
					'user_id' 		=> $user_id,
					'customer_id' 	=> $customer_id,
					'type' 			=> 'shipping',
					'country' 		=> $order_shipping_country,
					'city' 			=> $order_shipping_city,
					'address' 		=> $order_shipping_address_1,
					'create_date' 	=> date("Y-m-d h:i:s a")
				));
			}
		
			$response = array (
				'company' => 'MT',
				'customer' => $customer_id,
				'enterprise' => '01',
				'isDirectBilling' => 'N',
				'paymentState' => 'not_paid',
				'orderDate' => $order_timestamp_created,
				'orderDelivery' => strtotime("+7 day", $order_timestamp_created),
				'amount' => $order_total,
				'printDetail' => 'S',
				'reference' => $order_id,
				'billingAddress' => $billing_id,
				'newBillingAddress' =>  ((empty($billing_id)) ? $billingAddress : ''),
				'shipmentAddress' => $shipping_id,
				'newShippmentAddress' => ((empty($shipping_id)) ? $shippingAddress : ''),
				'paymentMethod:' => $order_payment_method_title,
				'lines' => $lines
			);

			$host = ((get_option('wcrabbitmq_host')) ? get_option('wcrabbitmq_host') : 'localhost');
			$port = ((get_option('wcrabbitmq_port')) ? intval(get_option('wcrabbitmq_port')) : 5672);
			$username = ((get_option('wcrabbitmq_username')) ? get_option('wcrabbitmq_username') : 'guest');
			$password = ((get_option('wcrabbitmq_password')) ? get_option('wcrabbitmq_password') : 'guest');
			$queueu_name = ((get_option('wcrabbitmq_queueu_name')) ? get_option('wcrabbitmq_queueu_name') : 'hello');

			$messageWillSent = true;

			if($messageWillSent){
				$connection = new \PhpAmqpLib\Connection\AMQPStreamConnection($host, $port, $username, $password);
				$channel = $connection->channel();
				
				$channel->queue_declare($queueu_name, false, false, false, false);

				$data = json_encode($response);

				$msg = new \PhpAmqpLib\Message\AMQPMessage($data);
				$channel->basic_publish($msg, '', $queueu_name);
				$messageWillSent = false;
	
				$channel->close();
				$connection->close();
			}
		}
	}
	
	function remove_woocommerce_billing_fields( $fields ) {
		if(get_user_meta(get_current_user_id(), 'is_wr_default_billing', true) && get_user_meta(get_current_user_id(), 'is_wr_default_billing', true) > 0){
			unset( $fields[ 'billing_first_name' ] );
			unset( $fields[ 'billing_last_name' ] );
			unset( $fields[ 'billing_company' ] );
			unset( $fields[ 'billing_address_2' ] );
			unset( $fields[ 'billing_state' ] );
			unset( $fields[ 'billing_postcode' ] );
		}
		return $fields;
	}

	function remove_woocommerce_shipping_fields( $fields ) {
		if(get_user_meta(get_current_user_id(), 'is_wr_default_shipping', true) && get_user_meta(get_current_user_id(), 'is_wr_default_shipping', true) > 0){
			unset( $fields[ 'shipping_first_name' ] );
			unset( $fields[ 'shipping_last_name' ] );
			unset( $fields[ 'shipping_company' ] );
			unset( $fields[ 'shipping_address_2' ] );
			unset( $fields[ 'shipping_state' ] );
			unset( $fields[ 'shipping_postcode' ] );
		}
		return $fields;
	}

	function adding_default_address_values( $null, $input ){
		if((get_user_meta(get_current_user_id(), 'is_wr_default_shipping', true) && get_user_meta(get_current_user_id(), 'is_wr_default_shipping', true) > 0) || (get_user_meta(get_current_user_id(), 'is_wr_default_billing', true) && get_user_meta(get_current_user_id(), 'is_wr_default_billing', true) > 0)){
			global $wpdb;
			$shipping_id = get_user_meta(get_current_user_id(), 'is_wr_default_shipping', true);
			$billing_id = get_user_meta(get_current_user_id(), 'is_wr_default_billing', true);

			$shippingObj = '';
			if($shipping_id){
				$shippingObj = $wpdb->get_row("SELECT * FROM {$wpdb->prefix}wcrabbit_customers WHERE ID = $shipping_id");
			}

			$billingObj = '';
			if($billing_id){
				$billingObj = $wpdb->get_row("SELECT * FROM {$wpdb->prefix}wcrabbit_customers WHERE ID = $billing_id");
			}

			switch ($input) {
				case 'billing_country':
					if($billingObj){
						$country = $billingObj->country;
						$country = strtoupper($country);
						if(strlen($country) > 2){
							$country = wr_get_country_name("code", $country);
						}
						return $country;
					}
					break;
				case 'billing_city':
					if($billingObj){
						return $billingObj->city;
					}
					break;
				case 'billing_address_1':
					if($billingObj){
						return $billingObj->address;
					}
					break;
				case 'shipping_country':
					if($shippingObj){
						$country = $shippingObj->country;
						$country = strtoupper($country);
						if(strlen($country) > 2){
							$country = wr_get_country_name("code", $country);
						}
						return $country;
					}
					break;
				case 'shipping_city':
					if($shippingObj){
						return $shippingObj->city;
					}
					break;
				case 'shipping_address_1':
					if($shippingObj){
						return $shippingObj->address;
					}
					break;
				default:
					return $null;
					break;
			}
		}else{
			return $null;
		}
	}

	function remove_checkout_comment_box( $fields ) {
		if((get_user_meta(get_current_user_id(), 'is_wr_default_shipping', true) && get_user_meta(get_current_user_id(), 'is_wr_default_shipping', true) > 0) || (get_user_meta(get_current_user_id(), 'is_wr_default_billing', true) && get_user_meta(get_current_user_id(), 'is_wr_default_billing', true) > 0)){
			unset($fields['order']['order_comments']);
		}
		return $fields;
	}

	// Ajax call
	function unset_selected_address(){
		if(!wp_verify_nonce( $_POST['nonce'], "wcrabbit_nonce" )){
			die("Invalid Request!");
		}

		if(isset($_POST['type'])){
			$type = $_POST["type"];

			if($type === "billing"){
				delete_user_meta( get_current_user_id(  ), "is_wr_default_billing" );
				echo json_encode(array("success" => "Success"));
				die;
			}

			if($type === "shipping"){
				delete_user_meta( get_current_user_id(  ), "is_wr_default_shipping" );
				echo json_encode(array("success" => "Success"));
				die;
			}
		}
		die;
	}

	function set_selected_address(){
		if(!wp_verify_nonce( $_POST['nonce'], "wcrabbit_nonce" )){
			die("Invalid Request!");
		}

		if(isset($_POST['type']) && isset($_POST['id'])){
			$type = $_POST["type"];
			$id = intval($_POST["id"]);

			if($type === "billing"){
				update_user_meta( get_current_user_id(  ), "is_wr_default_billing", $id );
				echo json_encode(array("success" => "Success"));
				die;
			}

			if($type === "shipping"){
				update_user_meta( get_current_user_id(  ), "is_wr_default_shipping", $id );
				echo json_encode(array("success" => "Success"));
				die;
			}
		}
		die;
	}

	function delete_additional_address(){
		global $wpdb;
		if(!wp_verify_nonce( $_POST['nonce'], "wcrabbit_nonce" )){
			die("Invalid Request!");
		}

		if(isset($_POST['type']) && isset($_POST['id'])){
			$type = $_POST["type"];
			$id = intval($_POST["id"]);

			$wpdb->query("DELETE FROM {$wpdb->prefix}wcrabbit_customers WHERE ID = $id");
			if($type === "billing"){
				delete_user_meta( get_current_user_id(  ), "is_wr_default_billing" );
			}
			if($type === "shipping"){
				delete_user_meta( get_current_user_id(  ), "is_wr_default_shipping" );
			}

			echo json_encode(array("success" => "Success"));
			die;

		}
		die;
	}
}
