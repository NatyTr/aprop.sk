<?php
/**
 * CookieSnapshot Table Class
 *
 *
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Load WP_List_Table if not loaded
if ( ! class_exists( 'WP_List_Table' ) ) {
	require_once ABSPATH . 'wp-admin/includes/class-wp-list-table.php';
}

class cmplz_Records_Of_Consent_Table extends WP_List_Table {

	/**
	 * Number of items per page
	 *
	 * @var int
	 * @since 1.5
	 */
	public $per_page = 30;

	/**
	 * Number found
	 *
	 * @var int
	 * @since 1.7
	 */
	public $count = 0;

	/**
	 * Total customers
	 *
	 * @var int
	 * @since 1.95
	 */
	public $total = 0;

	/**
	 * The arguments for the data set
	 *
	 * @var array
	 * @since  2.6
	 */
	public $args = array();

	/**
	 * all items
	 * @var array
	 */
	public $all = array();

	/**
	 * Get things started
	 *
	 * @since 1.5
	 * @see   WP_List_Table::__construct()
	 */


	public function __construct() {
		global $status, $page;

		// Set parent defaults
		parent::__construct( array(
			'singular' => __( 'record', 'complianz-gdpr' ),
			'plural'   => __( 'records', 'complianz-gdpr' ),
			'ajax'     => false,
		) );

	}

	/**
	 * Show the search field
	 *
	 * @param string $text     Label for the search box
	 * @param string $input_id ID of the search box
	 *
	 * @return void
	 * @since 1.7
	 *
	 */

	public function search_box( $text, $input_id ) {
		if ( ! empty( $_REQUEST['orderby'] ) ) {
			echo '<input type="hidden" name="orderby" value="' . esc_attr( $_REQUEST['orderby'] ) . '" />';
		}

		if ( ! empty( $_REQUEST['order'] ) ) {
			echo '<input type="hidden" name="order" value="' . esc_attr( $_REQUEST['order'] ) . '" />';
		}

		$search = $this->get_search();

		?>
		<p class="search-box">
			<label class="screen-reader-text"
				   for="<?php echo esc_attr($input_id) ?>"><?php echo esc_html($text); ?>:</label>
			<input type="search" name="s"
				   placeholder="<?php _e( "Search User ID", "complianz-gdpr" ) ?>"
				   value="<?php echo esc_html($search) ?>">
			<?php submit_button( $text, 'button', false, false,
				array( 'ID' => 'search-submit' ) ); ?>
		</p>
		<?php
	}

	/**
	 * Gets the name of the primary column.
	 *
	 * @return string Name of the primary column.
	 * @since  2.5
	 * @access protected
	 *
	 */
	protected function get_primary_column_ID() {
		return __( 'ID', 'complianz-gdpr' );
	}
	/**
	 * Output the checkbox column
	 *
	 * @access      private
	 * @since       1.0
	 * @return      string
	 */

	function column_cb( $item ) {
		return sprintf(
			'<input type="checkbox" name="%1$s_id[]" value="%2$s" />',
			esc_attr( $this->_args['singular'] ),
			esc_attr( $item['ID'] )
		);
	}

	/**
	 * Setup available bulk actions
	 *
	 * @access      private
	 * @since       1.0
	 * @return      array
	 */

	function get_bulk_actions() {
		$actions = array(
			'delete'     => __( 'Delete', 'complianz-gdpr' ),
		);

		return $actions;
	}

	/**
	 * Process bulk actions
	 *
	 * @access      private
	 * @since       1.0
	 * @return      void
	 */
	function process_bulk_action() {

		if (!cmplz_user_can_manage()) {
			return;
		}

		if( !isset($_REQUEST['_wpnonce']) || ! wp_verify_nonce( $_REQUEST['_wpnonce'], 'bulk-records' ) ) {
			return;
		}
		$ids = isset( $_GET['record_id'] ) ? $_GET['record_id'] : false;

		if( ! $ids ) {
			return;
		}

		if ( ! is_array( $ids ) ) {
			$ids = array( $ids );
		}

		foreach ( $ids as $id ) {
			if ( 'delete' === $this->current_action() ) {
				COMPLIANZ::$records_of_consent->delete_record( $id );
			}
		}
	}


	/**
	 * This function renders most of the columns in the list table.
	 *
	 * @param array  $item        Contains all the data of the customers
	 * @param string $column_name The name of the column
	 *
	 * @return string Column Name
	 * @since 1.5
	 *
	 */
	public function column_default( $item, $column_name ) {
		$value = '';

		if ( $column_name === 'time' ) {
			$date  = date( get_option( 'date_format' ), $item['time'] );
			$date  = cmplz_localize_date( $date );
			$time  = date( get_option( 'time_format' ), $item['time'] );
			$value = $date . " " . $time;
		}

		if ( $column_name === 'categories' ) {
			$banner_id = $item['cookiebanner_id'];
			if (empty($banner_id)) {
				$banner_id = cmplz_get_default_banner_id();
			}
			$banner = new CMPLZ_COOKIEBANNER($banner_id);

			$available_cats = $banner->get_available_categories();
			$categories = array();

			foreach ($available_cats as $cat => $label ) {
				if ($item[$cat]) {
					$categories[] = $label;
				}
			}

			$value = implode(', ', $categories);
		}

		if ( $column_name === 'ip' ) {
			$value = $item['ip'];
		}

		if ( $column_name === 'consenttype' ) {
			switch ($item['consenttype']) {
				case 'optin':
				$value = __('Opt-in', "complianz-gdpr");
					break;
				case 'optout':
					$value = __('Opt-out', "complianz-gdpr");
					break;
				case 'other':
				default:
					$value = '-';
			}
		}

		if ( $column_name === 'services' ) {
			$value = $item['services'];
		}

		if ( $column_name === 'region' ) {
			$value = $item['region'];
		}

		if ( $column_name === 'file' ) {
			if ( $item['no_warning'] ){
				$url = false;
			} else {
				//get proof of consent for this time period
				$url = false;
				if (empty($item['poc_url']) ) {
					$file = COMPLIANZ::$records_of_consent->get_poc_for_record( $item['time'], $item['region']);
					if ($file) $url = $file['url'];
				} else {
					$url = $item['poc_url'];
				}
			}
			if ($url) $value  = '<div>PDF</div><a href="' . $url . '" target="_blank">' . __( 'Download', 'complianz-gdpr' ) . '</a>';
		}
		return apply_filters( 'cmplz_records_of_consent_column_' . $column_name, $value, $item['ID'] );
	}

	public function column_ID( $item ) {
		$name = $item['ID'];
		$actions    = array(
			'delete'   => '<a class="cmplz-delete-record" data-id="' . $item['ID'] . '" href="#">' . __( 'Delete', 'complianz-gdpr' ) . '</a>'
		);

		return $name . $this->row_actions( $actions );
	}

	/**
	 * Retrieve the table columns
	 *
	 * @return array $columns Array of all the list table columns
	 * @since 1.5
	 */
	public function get_columns() {
		$columns = array(
				'cb'          => '<input type="checkbox"/>',
				'ID'          => __( 'User ID', 'complianz-gdpr' ),
				'time'        => __( 'Date', 'complianz-gdpr' ),
				'categories'  => __( 'Consent', 'complianz-gdpr' ),
				'ip'          => __( 'IP address', 'complianz-gdpr' ),
				'region'      => __( 'Region', 'complianz-gdpr' ),
				'services'    => __( 'Consented services', 'complianz-gdpr' ),
				'consenttype' => __( 'Consent type', 'complianz-gdpr' ),
				'file'        => __( 'Proof of Consent', 'complianz-gdpr' ),
		);

		return apply_filters( 'cmplz_records_of_consent_columns', $columns );

	}

	/**
	 * Get the sortable columns
	 *
	 * @return array Array of all the sortable columns
	 * @since 2.1
	 */
	public function get_sortable_columns() {
		$columns = array(
			'ID' => array( 'ID', true ),
			'time' => array( 'time', true ),
		);

		return $columns;
	}

	/**
	 * Retrieve the current page number
	 *
	 * @return int Current page number
	 * @since 1.5
	 */
	public function get_paged() {
		return isset( $_GET['paged'] ) ? absint( $_GET['paged'] ) : 1;
	}


	/**
	 * Retrieves the search query string
	 *
	 * @return mixed string If search is present, false otherwise
	 * @since 1.7
	 */
	public function get_search() {
		return ! empty( $_GET['s'] ) ? trim( sanitize_text_field( $_GET['s'] ) ) : false;
	}

	public function date_select() {
		// Month Select
		$selected = false;
		if ( isset( $_GET['cmplz_month_select'] ) ) {
			if ( isset( $_GET['cmplz_year_select'] ) &&  $_GET['cmplz_year_select'] == 0) {
				$selected = 0;
			} else {
				$selected = $_GET['cmplz_month_select'];
			}
		}
		$months = array(
			__( 'Month',"complianz-gdpr"),
			__( 'January' ),
			__( 'February' ),
			__( 'March' ),
			__( 'April' ),
			__( 'May' ),
			__( 'June' ),
			__( 'July' ),
			__( 'August' ),
			__( 'September' ),
			__( 'October' ),
			__( 'November' ),
			__( 'December' )
		);
		echo '<select style="float:right" name="cmplz_month_select" id="cmplz_month_select" class="cmplz_month_select">';
		foreach($months as $value => $label) {
			echo '<option value="' . $value . '" ' . ($selected==$value ? 'selected' : '') . '>' . $label . '</option>';
		}
		echo '</select>';

		// Year Select
		$selected = false;
		if ( isset( $_GET['cmplz_year_select'] ) ) {
			if (isset($_GET['cmplz_month_select']) && $_GET['cmplz_month_select'] == 0) {
				$selected = 0;
			} else {
				$selected = $_GET['cmplz_year_select'];
			}
		}

		if ( $this->total >0 ) {
			$first_year = date("Y", $this->get_oldest_snapshot_time());
			$end_year = date("Y", $this->get_newest_snapshot_time());
			$years  = range($first_year, $end_year);
		} else {
			$years = array();
		}

		echo '<select style="float:right" name="cmplz_year_select" id="cmplz_year_select" class="cmplz_year_select">';
		echo '<option value="0" ' . ($selected==0 ? 'selected' : '') . '>'.__("Year","complianz-gdpr").'</option>';
		foreach( $years as $year) {
			echo '<option value="' . $year . '" ' . ($selected==$year ? 'selected' : '') . '>' . $year . '</option>';
		}
		echo '</select>';
	}

	/**
	 * Get oldest record time stamp
	 * @return int
	 */
	private function get_oldest_snapshot_time() {
		//get lowest time from database
		global $wpdb;
		return $wpdb->get_var("select min(time) from {$wpdb->prefix}cmplz_statistics where time>0");
	}

	/**
	 * Get latest record time stamp
	 * @return int
	 */
	private function get_newest_snapshot_time() {
		//get lowest time from database
		global $wpdb;
		return $wpdb->get_var("select max(time) from {$wpdb->prefix}cmplz_statistics where time>0");
	}

	/**
	 * Build all the reports data
	 *
	 * @return array $reports_data All the data for customer reports
	 * @global object $wpdb Used to query the database using the WordPress
	 *                      Database API
	 * @since 1.5
	 */

	public function reports_data() {

		if ( ! cmplz_user_can_manage() ) {
			return array();
		}

		$paged   = $this->get_paged();
		$offset  = $this->per_page * ( $paged - 1 );
		$search  = $this->get_search();
		$order   = isset( $_GET['order'] ) ? sanitize_text_field( $_GET['order'] ) : 'DESC';
		$orderby = isset( $_GET['orderby'] ) ? sanitize_text_field( $_GET['orderby'] ) : 'time';

		$args = array(
			'number'  => $this->per_page,
			'offset'  => $offset,
			'order'   => $order,
			'orderby' => $orderby,
			'search'  => $search,
		);

		if ( isset( $_GET['cmplz_month_select'] ) && isset( $_GET['cmplz_year_select'] )
		     && $_GET['cmplz_month_select'] != 0 && $_GET['cmplz_year_select'] != 0 ) {

			$args['start_date']  = COMPLIANZ::$proof_of_consent->get_time_stamp_for_date($_GET['cmplz_year_select'], $_GET['cmplz_month_select'], 'start_date');
			$args['end_date']  = COMPLIANZ::$proof_of_consent->get_time_stamp_for_date($_GET['cmplz_year_select'], $_GET['cmplz_month_select'], 'end_date');
		}

		$this->args = $args;
		return COMPLIANZ::$records_of_consent->get_consent_records( $args );
	}

	/**
	 * Setup the table data
	 */

	public function prepare_items() {

		$columns  = $this->get_columns();
		$hidden   = array(); // No hidden columns
		$sortable = $this->get_sortable_columns();

		$this->process_bulk_action();
		$this->_column_headers = array( $columns, $hidden, $sortable );
		$this->items = $this->reports_data();
		$args = $this->args;
		unset($args['number']);
		unset($args['offset']);
		$this->total = COMPLIANZ::$records_of_consent->get_consent_records( $args, true );

		// Add condition to be sure we don't divide by zero.
		// If $this->per_page is 0, then set total pages to 1.

		$total_pages = $this->per_page ? ceil( (int) $this->total / $this->per_page ) : 1;
		$this->set_pagination_args( array(
			'total_items' => $this->total,
			'per_page'    => $this->per_page,
			'total_pages' => $total_pages,
		) );
	}
}
