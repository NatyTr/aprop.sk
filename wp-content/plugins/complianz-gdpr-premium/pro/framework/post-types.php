<?php
if (!defined('ABSPATH')) {
    die();
}

/**
 * Hide the quick edit action if it's our own custom post type
 * @param string[] $actions
 * @param WP_POST $post
 *
 * @return string[]
 */
function cmplz_hide_quick_edit($actions, $post){
	if ( get_post_type($post) === 'cmplz-processing' || get_post_type( $post) === 'cmplz-dataleak' ) {
		unset($actions['inline hide-if-no-js']);
	}
	return $actions;
}
add_filter( 'post_row_actions', 'cmplz_hide_quick_edit' ,10, 2);
/**
 * Hide publish metabox
 *
 *
 * */
function cmplz_remove_publish_box() {
    remove_meta_box( 'submitdiv', 'cmplz-processing', 'side' );
    remove_meta_box( 'submitdiv', 'cmplz-dataleak', 'side' );
}
add_action( 'admin_menu', 'cmplz_remove_publish_box' );
/**
 *
 * Add our custom post types to the complianz menu
 *
 * */

add_action('cmplz_admin_menu', 'cmplz_admin_menu_submenu', 30);
function cmplz_admin_menu_submenu() {
    if (!cmplz_user_can_manage()) return;

    add_submenu_page(
            'complianz',
            _x('Processing agreements', 'Name of post type in menu', 'complianz-gdpr'),
            _x('Processing agreements', 'Name of post type', 'complianz-gdpr'),
            'manage_options' ,
            'edit.php?post_type=cmplz-processing'
    );
    add_submenu_page(
            'complianz',
            _x('Dataleak reports', 'Name of post type in menu', 'complianz-gdpr'),
            _x('Dataleak reports', 'Name of post type', 'complianz-gdpr'),
            'manage_options' ,
            'edit.php?post_type=cmplz-dataleak');
}

/**
 * As there are no filters we know of to add buttons on a post page, we use an ugly hack.
 *
 *
 *
 * */

function cmplz_custom_add_new()
{
    global $current_screen;
    if ('cmplz-processing' != $current_screen->post_type && 'cmplz-dataleak' != $current_screen->post_type) {
        return;
    }

    // Region Select
	$regions = cmplz_get_regions();
	$region_select = '';
	if (cmplz_multiple_regions()){
		$region_select = '<select name="cmplz-region-select" id="cmplz-region-select" class="cmplz-region-select">';
		if (empty($regions)) {
			$region_select .= '<option value="">' . __("Complete the wizard to select a region", "complianz-gdpr") . '</option>';
		}
		foreach($regions as $value => $label) {
			$region_select .= '<option value="' . $value . '">' . $label . '</option>';
		}
		$region_select .= '</select>';
	}

    // Create Button
    $url = admin_url("admin.php?page=" . $current_screen->post_type . "-" . array_key_first($regions) );
    $create_button = '<a href="' . $url . '" class="button button-primary cmplz-document-button">' . __('Create', 'complianz-gdpr') . '</a>';

    ?>
    <script type="text/javascript">
        jQuery(document).ready( function($)
        {
            //in older wp versions, the wp-heading inline class is not available
			$(".wrap .wp-heading-inline").after('<?php echo $region_select . $create_button ?>');

            $(document).on('change', '.cmplz-region-select', function() {
                var _href = $('.cmplz-document-button').attr("href").slice(0,-2);
                $('.cmplz-document-button').attr('href', _href + $(this).val());
            });
        });
    </script>
    <?php

}
add_action('admin_head-edit.php','cmplz_custom_add_new');

/**
 * Add admin menu
 */
function cmplz_admin_menu()
{
    if (!cmplz_user_can_manage()) return;

    $regions = cmplz_get_regions(false, true);
    foreach ($regions as $region => $label) {
        add_submenu_page(
            "edit.php?post_type=cmplz-processing",
            cmplz_sprintf(__('Add new (%s)', 'complianz-gdpr'), $label),
            cmplz_sprintf(__('Add new (%s)', 'complianz-gdpr'), $label),
            'manage_options',
            "cmplz-processing-$region",
            "cmplz_processing_page_$region"
        );

        add_submenu_page(
            "edit.php?post_type=cmplz-dataleak",
            cmplz_sprintf(__('Add new (%s)', 'complianz-gdpr'), $label),
            cmplz_sprintf(__('Add new (%s)', 'complianz-gdpr'), $label),
            'manage_options',
            "cmplz-dataleak-$region",
            "cmplz_dataleak_page_$region"
        );
    }
}
add_action('cmplz_admin_menu', 'cmplz_admin_menu', 30, 1);


/**
 * Set the "example_parent_page_id" submenu as active/current when creating/editing a "example_cpt" post
 * @param $parent_file
 *
 * @return mixed|string
 */

function cmplz_admin_parent_file($parent_file){
    global $submenu_file, $current_screen;

    // Set correct active/current menu and submenu in the WordPress Admin menu for the "example_cpt" Add-New/Edit/List
    if($current_screen->post_type == 'cmplz-processing') {
        $submenu_file = 'edit.php?post_type=cmplz-processing';
        $parent_file = 'complianz';
    }

    if($current_screen->post_type == 'cmplz-dataleak') {
        $submenu_file = 'edit.php?post_type=cmplz-dataleak';
        $parent_file = 'complianz';
    }

    return $parent_file;
}
add_filter('parent_file', 'cmplz_admin_parent_file');

/**
 * add custom post type
 */
function cmplz_register_post_type()
{
    register_post_type(
        "cmplz-processing", //post name to use in code
        array(
            'labels' => array(
                'name' => _x('Processing agreements', 'Name of post type', 'complianz-gdpr'),
                'singular_name' => _x('Processing agreement (%s)', 'Singular name of post type', 'complianz-gdpr'),
                'add_new' => __('Add new', 'complianz-gdpr'),
                'add_new_item' => __('Add new', 'complianz-gdpr'),
                'parent_item_colon' => __('Processing agreement', 'complianz-gdpr'),
                'parent' => 'Processing agreement parent item',
            ),

            //'menu_icon' => 'dashicons-hammer',
            'menu_icon' => cmplz_url . "assets/images/processing.png", //https://developer.wordpress.org/resource/dashicons/#editor-code
            'menu_position' => CMPLZ_PROCESSING_MENU_POSITION,
            'rewrite' => array(
                'slug' => "processing-agreement",
                'pages' => true
            ),
            'exclude_from_search' => true,
            'supports' => array(
                'title',
                'author',
                'revisions',
                //'page-attributes'
            ),
            'publicly_queryable' => false,
            'query_var' => false,
            'public' => true,
            'has_archive' => false,
            'taxonomies' => array('region'),
            'hierarchical' => false,
            'map_meta_cap' => true, //enable capability handling
            'capabilities' => array(
                'create_posts' => 'do_not_allow',
                'delete_post' => true,
            ),
            'show_in_menu' => false
        )
    );

    register_post_type(
        "cmplz-dataleak", //post name to use in code
        array(
            'labels' => array(
                'name' => __('Dataleaks', 'complianz-gdpr'),
                'add_new' => __('Add new', 'complianz-gdpr'),
                'add_new_item' => __('Add new', 'complianz-gdpr'),
                'parent_item_colon' => __('Dataleak', 'complianz-gdpr'),
                'parent' => 'Dataleak parent item',
            ),

            'menu_icon' => cmplz_url . "assets/images/dataleak.png",
            'menu_position' => CMPLZ_DATALEAK_MENU_POSITION,
            'rewrite' => array(
                'slug' => "dataleak",
                'pages' => true
            ),
            'exclude_from_search' => true,
            'supports' => array(
                'title',
                'author',
                //'page-attributes'
            ),
            'publicly_queryable' => false,
            'query_var' => false,
            'public' => true,
            'has_archive' => false,
            'taxonomies' => array('region'),
            'hierarchical' => false,
            'map_meta_cap' => true, //enable capability handling
            'capabilities' => array(
                'create_posts' => 'do_not_allow',
                'delete_post' => true,
            ),
            'show_in_menu' => false

        )
    );
}
add_action('init', 'cmplz_register_post_type', 99, 1);

/**
 * Register region taxonomy
 */
function cmplz_register_regions() {
    register_taxonomy(
        'cmplz-region',
        array('cmplz-dataleak','cmplz-processing'),
        array(
            'label' => __( 'Region', 'complianz-gdpr'),
            'publicly_queryable' => false,
            'hierarchical' => true,
            'show_ui' => false,
            'capabilities'      => array(
                'assign_terms' => 'manage_options',
                'edit_terms'   => 'NOT_EXISTING_CAPABILITY',
                'manage_terms' => 'NOT_EXISTING_CAPABILITY',
            ),
            'show_in_nav_menus' => false,
            'show_in_rest' => false,
            'rewrite' => array( 'slug' => 'region' ),
        )
    );
}
add_action( 'init', 'cmplz_register_regions' );

/**
 *  create the dropdown
 */
 function cmplz_add_dropdown_filter(){
     $post_type = isset($_GET['post_type']) ? sanitize_title($_GET['post_type']) : false;

    if (($post_type === 'cmplz-dataleak' || $post_type ==='cmplz-processing' ) && cmplz_multiple_regions()) {
            $values = cmplz_get_regions();
            ?>
            <select name="_cmplz_region">
                <option value=""><?php _e('All regions', 'complianz-gdpr'); ?></option>
                <?php
                $current_v = isset($_GET['_cmplz_region']) ? $_GET['_cmplz_region'] : 'on';
                foreach ($values as $value => $label) {
                    printf
                    (
                        '<option value="%s"%s>%s</option>',
                        $value,
                        $value == $current_v ? ' selected="selected"' : '',
                        $label
                    );
                }
                ?>
            </select>
            <?php
    }
}
add_action( 'restrict_manage_posts', 'cmplz_add_dropdown_filter' );

/**
 * if submitted filter by post meta
 * @param $query
 */
 function cmplz_filter_posts( $query ){
    global $pagenow;
    $post_type = isset($_GET['post_type']) ? sanitize_title($_GET['post_type']) : false;
     if (($post_type === 'cmplz-dataleak' || $post_type ==='cmplz-processing') && is_admin() && $pagenow=='edit.php') {
        if (isset($_GET['_cmplz_region'])){
            $region = sanitize_title($_GET['_cmplz_region']);
            $query->query_vars['tax_query'] = array(array('taxonomy' =>'cmplz-region','field' => 'slug','terms' => $region));
        }
    }
}
add_filter( 'parse_query', 'cmplz_filter_posts' );


/**
 * Add a column "region"
 * @param array $columns
 *
 * @return array
 */

function cmplz_set_edit_field_columns($columns) {

    $columns['region'] = __('Region', 'complianz-gdpr');

    return $columns;
}
add_filter( 'manage_cmplz-dataleak_posts_columns', 'cmplz_set_edit_field_columns' );
add_filter( 'manage_cmplz-processing_posts_columns', 'cmplz_set_edit_field_columns' );

/**
 * Fill the column "region"
 *
 * @param string $column
 * @param int $post_id
 */
function cmplz_custom_field_column( $column, $post_id ) {
    if ($column==='region') {
        $region = COMPLIANZ::$document->get_region($post_id);
        if ($region) {
        	echo cmplz_region_icon($region, 25);
        }
    }
}
add_action( 'manage_cmplz-dataleak_posts_custom_column' , 'cmplz_custom_field_column', 10, 2 );
add_action( 'manage_cmplz-processing_posts_custom_column' , 'cmplz_custom_field_column', 10, 2 );

/**
 * add a thumbnail column to the edit posts screen
 * @param array $cols
 *
 * @return array
 */
function cmplz_add_pdf_column($cols)
{
    if (!isset($_GET['post_type']) || strpos($_GET['post_type'], 'cmplz-') === FALSE) return $cols;
    $cols['PDF'] = _x('Download', 'Column title in custom post type overview', 'complianz-gdpr');
    return $cols;
}
add_filter('manage_posts_columns', 'cmplz_add_pdf_column');

/**
 * go get the attached images for   the logo and thumbnail columns
 * @param string $column_name
 * @param int $post_id
 */

function cmplz_add_pdf_icon($column_name, $post_id)
{
    if (('PDF' == $column_name)) {
        if (strpos(get_post_type($post_id), 'cmplz-') === FALSE) return;
        if (get_post_type($post_id) === 'cmplz-dataleak' && !COMPLIANZ::$dataleak->dataleak_has_to_be_reported_to_involved($post_id)) return;

        echo '<a target="_blank" href="' . get_cmplz_document_download_url($post_id) . '"><img src="' . cmplz_url . 'assets/images/pdf.png" width=20px height=20px></a>';
    }
}
add_action('manage_cmplz-processing_posts_custom_column', 'cmplz_add_pdf_icon', 10, 2);
add_action('manage_cmplz-dataleak_posts_custom_column', 'cmplz_add_pdf_icon', 10, 2);
/**
 * Generate a download pdf URL.
 *
 * @param $post_id
 *
 * @return string
 */

function get_cmplz_document_download_url( $post_id ) {
	return cmplz_url . 'pro/pdf.php?nonce=' . wp_create_nonce("cmplz_pdf_nonce") .'&region=' . COMPLIANZ::$document->get_region($post_id). '&post_id=' . $post_id . '&token=' . time();
}


/**
 * add a thumbnail column to the edit posts screen
 * @param array $cols
 *
 * @return mixed
 */

function cmplz_add_mail_sent_column($cols)
{
    if (!isset($_GET['post_type']) || $_GET['post_type'] !== 'cmplz-dataleak') return $cols;

    $cols['mail_sending_complete'] = _x('Email sent', 'Column header in posts overview','complianz-gdpr');
    return $cols;
}
add_filter('manage_posts_columns', 'cmplz_add_mail_sent_column');

/**
 * go get the attached images for   the logo and thumbnail columns
 * @param string $column_name
 * @param int $post_id
 */
function cmplz_add_mail_sent_icon($column_name, $post_id)
{
    if (('mail_sending_complete' == $column_name)) {
        if (COMPLIANZ::$dataleak->get_email_batch_progress($post_id) >= 100) {
            echo '<i class="fa fa-check"></i>';
        } elseif (COMPLIANZ::$dataleak->get_email_batch_progress($post_id) == 0) {
            echo '';
        } else {
            echo '<i class="fa fa-envelope"></i>';
        }
    }
}
add_action('manage_cmplz-dataleak_posts_custom_column', 'cmplz_add_mail_sent_icon', 10, 2);

/**
 * Add custom meta boxes
 * @param string $post_type
 */
function cmplz_add_custom_meta_box($post_type)
{
    global $post;

    if (strpos($post_type, 'cmplz-') === FALSE) return;
    add_meta_box('cmplz_document_meta_box_html', __('Document contents', 'complianz-gdpr'), 'cmplz_show_document', null, 'normal', 'high', array());
    add_meta_box('cmplz_edit_meta_box', __('Edit', 'complianz-gdpr'), 'cmplz_show_edit_metabox', null, 'side', 'high', array());

    add_meta_box('cmplz_region_meta_box', __('Region', 'complianz-gdpr'), 'cmplz_show_region', null, 'side', 'high', array());

    //if it doesn't have to be reported, don't show the email option
    if ($post_type === 'cmplz-dataleak' && $post && COMPLIANZ::$dataleak->dataleak_has_to_be_reported_to_involved($post->ID)) {
        add_meta_box('cmplz_email_meta_box', __('Email', 'complianz-gdpr'), 'cmplz_mail_option', null, 'side', 'high', array(//'__block_editor_compatible_meta_box' => true,
        ));
    }

    if (($post_type !== 'cmplz-dataleak') || ($post_type === 'cmplz-dataleak' && $post && COMPLIANZ::$dataleak->dataleak_has_to_be_reported_to_involved($post->ID))) {
        add_meta_box('cmplz_download_meta_box', __('Download', 'complianz-gdpr'), 'cmplz_download_option', null, 'side', 'high', array(//'__block_editor_compatible_meta_box' => true,
        ));
    }

}
add_action('add_meta_boxes', 'cmplz_add_custom_meta_box');

/**
 * Show mail option
 */
function cmplz_mail_option()
{
    COMPLIANZ::$dataleak->send_mail_button();
}

/**
 * Show download option
 */
function cmplz_download_option()
{
    global $post;
    $permalink = get_cmplz_document_download_url( $post->ID );
    ?>
    <a target="_blank" href="<?php echo esc_url_raw($permalink) ?>" class="button"><?php _e('Download PDF', 'complianz-gdpr') ?></a>
    <?php
}

/**
 * Show the current region
 */
function cmplz_show_region(){
    global $post;
    $region = COMPLIANZ::$document->get_region($post->ID);
	echo cmplz_region_icon($region, 40);
}

/**
 * Show edit metabox
 */

function cmplz_show_edit_metabox(){
    global $post;

    $region = COMPLIANZ::$document->get_region($post->ID);
    $supported_regions = cmplz_get_regions();
    if (array_key_exists($region, $supported_regions)) {
        $edit_link = admin_url('admin.php?page=' . get_post_type($post) . '-' . $region . '&post_id=' . $post->ID . '&step=2');
        ?>
        <a href="<?php echo esc_url_raw($edit_link) ?>" class="button"><?php _e('Edit document', 'complianz-gdpr') ?></a>
        <?php
    } else {
        cmplz_notice(__('Because you have disabled the region connected to this document, editing is currently not possbile.','complianz-gdpr'),'warning');
    }
}

/**
 * Show document
 */
function cmplz_show_document()
{
    global $post;
    $region = COMPLIANZ::$document->get_region($post->ID);
    $type = str_replace('cmplz-', '', get_post_type($post));

    if ($type === 'dataleak') {
        COMPLIANZ::$dataleak->dataleak_conclusion($post->ID);
    }

    if ($type === 'processing'){
        echo COMPLIANZ::$document->get_document_html('processing', $region, $post->ID);
    }

    if ($type === 'dataleak') {
        if ($region!=='eu' || COMPLIANZ::$dataleak->dataleak_has_to_be_reported_to_involved($post->ID)) {
            echo COMPLIANZ::$document->get_document_html('dataleak', $region, $post->ID);
        }
    }
}

/**
 * Add a region query arg, so we know which region we're in for the localization.
 *
 *
 * */

add_filter( 'get_edit_post_link', 'cmplz_edit_post_link', 10, 1 );
function cmplz_edit_post_link( $url ) {
    $region = COMPLIANZ::$document->get_region(get_the_ID());
    return  add_query_arg( 'region', $region, $url);
}

/**
 * Check if Gutenberg can edit the post type
 *
 * */

function cmplz_gutenberg_can_edit_post_type($can_edit)
{
    global $post;

    if ($post && get_post_type($post->ID) == 'cmplz-processing' || get_post_type($post->ID) == 'cmplz-dataleak') {
        return false;
    }

    return $can_edit;
}
add_filter( 'gutenberg_can_edit_post_type', 'cmplz_gutenberg_can_edit_post_type' , 15, 1);
