<?php

/**
* Real Estate Management Main Class - Since 1.0.0
*/

class WCP_Real_Estate_Management
{
    
    function __construct(){

        /***********************************************************************************************/
        /* Admin Menus, Settings, Scripts */
        /***********************************************************************************************/

        // Actions
        add_action( 'init', array($this, 'register_property' ) );
        add_action( 'admin_menu', array( $this, 'menu_pages' ) );
        add_action( 'admin_enqueue_scripts', array($this, 'admin_scripts' ) );
        add_action( 'wp_enqueue_scripts', array($this, 'front_scripts' ) );
        add_action( 'save_post', array($this, 'save_property' ) );
        add_action( 'add_meta_boxes', array($this, 'property_metaboxes' ) );
        add_action( 'admin_init', array($this, 'rem_role_cap') , 999);

        // Edit Profile Fields
        add_action( 'show_user_profile', array($this, 'rem_agent_extra_fields' ) );
        add_action( 'edit_user_profile', array($this, 'rem_agent_extra_fields' ) );

        // Save Profile Fields
        add_action( 'personal_options_update', array($this, 'save_rem_agent_fields' ) );
        add_action( 'edit_user_profile_update', array($this, 'save_rem_agent_fields' ) );        

        // Filters
        add_filter( 'post_updated_messages', array($this, 'property_messages' ) );
        add_filter( 'template_include', array($this, 'rem_templates'), 99 );

        //disable WordPress sanitization to allow more than just $allowedtags from /wp-includes/kses.php
        remove_filter('pre_user_description', 'wp_filter_kses');

        // Translations
        add_action( 'plugins_loaded', array($this, 'wcp_load_plugin_textdomain' ) );

        // Change author in proeprties page
        add_filter( 'wp_dropdown_users', array($this, 'author_override') );

        // Permalink settings
        add_filter( 'load-options-permalink.php', array($this, 'permalink_settings') );     


        /***********************************************************************************************/
        /* AJAX Callbacks */
        /***********************************************************************************************/

        // Saving Admin Settings
        add_action( 'wp_ajax_wcp_rem_save_settings', array($this, 'save_admin_settings' ) );
        
        // Saving Custom Fields
        add_action( 'wp_ajax_wcp_rem_save_custom_fields', array($this, 'save_custom_fields' ) );
        
        // Resetting Custom Fields
        add_action( 'wp_ajax_wcp_rem_reset_custom_fields', array($this, 'reset_custom_fields' ) );
        
        // Contact Agent
        add_action( 'wp_ajax_rem_contact_agent', array($this, 'send_email_agent' ) );
        add_action( 'wp_ajax_nopriv_rem_contact_agent', array($this, 'send_email_agent' ) );

        // Agent Approve/ Deny
        add_action( 'wp_ajax_deny_agent', array($this, 'deny_agent' ) );
        add_action( 'wp_ajax_approve_agent', array($this, 'approve_agent' ) );

        // Manage Columns and filter on admin listings
        add_filter('manage_rem_property_posts_columns', array($this, 'rem_property_column_head'));
        add_action('manage_rem_property_posts_custom_column', array($this, 'rem_property_column_content'), 10, 2);
        add_action('restrict_manage_posts', array($this, 'filter_properties_list_admin') , 10);
        add_filter( 'parse_query', array($this, 'filter_properties_request_query') , 10);

        add_action( 'wp_ajax_wcp_rem_save_custom_agent_fields', array($this, 'save_custom_agent_fields' ) );
        add_action( 'wp_ajax_wcp_rem_reset_custom_agent_fields', array($this, 'reset_custom_agent_fields' ) );        
        add_action( 'wp_ajax_rem_validate_pcode', array($this, 'rem_validate_pcode' ) );        
        add_action( 'wp_ajax_rem_remove_pcode', array($this, 'rem_remove_pcode' ) );        
        add_action( 'admin_notices', array($this, 'validate_notice' ) );        
        add_action( 'after_setup_theme', array($this, 'remove_admin_bar' ) );
        add_filter( 'mce_buttons', array($this, 'remove_tiny_mce_link_buttons') );
        add_filter('use_block_editor_for_post_type', array($this, 'rem_disable_gutenberg'), 10, 2);
    }

    function wcp_load_plugin_textdomain(){
        load_plugin_textdomain( 'real-estate-manager', FALSE, basename( REM_PATH ) . '/languages/' );
    }

    /**
    * Registers a new post type property
    * @since 1.0.0
    */
    function register_property() {
        include_once REM_PATH.'/inc/admin/register-property.php';
    }
    
    /**
    * Property page settings metaboxes
    * @since 1.0.0
    */
    function property_metaboxes(){
        add_meta_box( 'property_settings_meta_box', __( 'Property Information', 'real-estate-manager' ), array($this, 'render_property_settings' ), array('rem_property'));
        add_meta_box( 'property_maps_meta_box', __( 'Location on Map', 'real-estate-manager' ), array($this, 'render_property_location' ), array('rem_property'));
        add_meta_box( 'property_images_meta_box', __( 'Gallery Images', 'real-estate-manager' ), array($this, 'render_property_images' ), array('rem_property'));
        add_meta_box( 'property_multiple_agents_meta_box', __( 'Additional Agents', 'real-estate-manager' ), array($this, 'render_agents_box' ), array('rem_property'), 'side');
    }

    function render_agents_box($post) {

        $saved_agents = get_post_meta($post->ID, 'rem_property_multiple_agents', true);
        $blogusers = get_users( array( 'fields' => array( 'ID','display_name' ) ) );
        $author_id=$post->post_author;
        
        // Array of stdClass objects.
        foreach ( $blogusers as $user ) {
            if ( is_array($saved_agents) && in_array( $user->ID, $saved_agents ) ) {
                $checked = 'checked';
            }else {
                $checked = '';
            }
            // property authore not show in multiple agents
            if ($user->ID != $author_id) {
                
                echo '<div style="padding: 10px;">';
                echo "<label for='".$user->ID."'><input type='checkbox' id='".$user->ID."' name='rem_multiple_agents[]' value='".$user->ID."' ".$checked." > <strong>". esc_html( $user->display_name ) ."<strong></label>";
                echo '</div>';
            }
        }
    }

    function render_property_settings(){
        wp_nonce_field( plugin_basename( __FILE__ ), 'rem_property_settings_nonce' );
        include_once REM_PATH.'/inc/admin/property-settings-metabox.php';
    }

    function render_property_images(){
        include REM_PATH.'/inc/admin/property-images-metabox.php';
    }

    function render_property_location(){
        include REM_PATH.'/inc/admin/property-location-map.php';
    }

    function save_property($post_id){
        // verify if this is an auto save routine. 
        // If it is our form has not been submitted, so we dont want to do anything
        if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) 
            return;

        // verify this came from the our screen and with proper authorization,
        // because save_post can be triggered at other times
        if ( !isset( $_POST['rem_property_settings_nonce'] ) )
            return;

        if ( !wp_verify_nonce( $_POST['rem_property_settings_nonce'], plugin_basename( __FILE__ ) ) )
            return;

        // OK, we're authenticated: we need to find and save the data
        $hidden_fields_json = isset( $_REQUEST['hidden_fields'] ) ? $_REQUEST['hidden_fields'] : '' ;
        if ($hidden_fields_json != '') {
            $hidden_fields_array = json_decode(stripcslashes($hidden_fields));
        }

        if (isset($_POST['rem_property_data']) && $_POST['rem_property_data'] != '') {
            foreach ($_POST['rem_property_data'] as $key => $value) {
                if (!isset($hidden_fields_array) || (isset($hidden_fields_array) && !in_array($key, $hidden_fields))) {
                    if ($key == 'property_price' && strpos($value, '-') ) {
                        $price_array =  explode("-", $value);
                        $min_price = $price_array[0];
                        $max_price = $price_array[1];
                        
                        update_post_meta( $post_id, 'rem_property_min_price', $min_price );
                        update_post_meta( $post_id, 'rem_property_max_price', $max_price );
                    }
                    update_post_meta( $post_id, 'rem_'.$key, $value );
                } else {  
                    update_post_meta( $post_id, 'rem_'.$key, '' );
                }
            }

            if (!isset($_POST['rem_property_data']['property_detail_cbs'])) {
                update_post_meta( $post_id, 'rem_property_detail_cbs', '' );
            }
            if (!isset($_POST['rem_property_data']['property_images'])) {
                update_post_meta( $post_id, 'rem_property_images', '' );
            }
        }

        if (isset($_POST['rem_multiple_agents']) && $_POST['rem_multiple_agents'] != '') {
            update_post_meta( $post_id, 'rem_property_multiple_agents', $_POST['rem_multiple_agents'] );
        } else {
            update_post_meta( $post_id, 'rem_property_multiple_agents', '' );
        }

        if (isset($_POST['_disable_map'])) {
            update_post_meta( $post_id, '_disable_map', 'yes');
        } else {
            update_post_meta( $post_id, '_disable_map', 'no');
        }
    }

    function admin_scripts($check){
        global $post;
        if ( $check == 'post-new.php' || $check == 'post.php' ) {
            if (isset($post->post_type) && 'rem_property' === $post->post_type) {
                wp_enqueue_media();
                wp_enqueue_style( 'rem-bs-css', REM_URL . '/assets/admin/css/bootstrap.min.css' );
                wp_enqueue_style( 'rem-new-property-css', REM_URL . '/assets/admin/css/admin.css' );

                // if select2 is not already added by Yoast SEO
                if (!is_plugin_active( 'wordpress-seo/wp-seo.php' ) && !is_plugin_active( 'wordpress-seo-premium/wp-seo-premium.php' ) ) {
                    wp_enqueue_style( 'select2', REM_URL . '/assets/admin/css/select2.min.css' );
                    wp_enqueue_script( 'select2', REM_URL . '/assets/admin/js/select2.min.js' , array('jquery'));
                }                
                
                $maps_api = apply_filters( 'rem_maps_api', 'AIzaSyBbpbij9IIXGftKhFLMHOuTpAbFoTU_8ZQ' );

                if (rem_get_option('use_map_from', 'leaflet') == 'leaflet') {
                    wp_enqueue_style( 'rem-leaflet-css', REM_URL . '/assets/front/leaflet/leaflet.css');
                    wp_enqueue_script( 'rem-leaflet-js', REM_URL . '/assets/front/leaflet/leaflet.js', array('jquery'));
                    wp_enqueue_style( 'rem-leaflet-geo-css', 'https://unpkg.com/leaflet-control-geocoder/dist/Control.Geocoder.css');
                    wp_enqueue_script( 'rem-leaflet-geo-js', 'https://unpkg.com/leaflet-control-geocoder/dist/Control.Geocoder.js');
                } else {
                    if (is_ssl()) {
                        wp_enqueue_script( 'google-maps', 'https://maps.googleapis.com/maps/api/js?key='.$maps_api.'&libraries=places' );
                    } else {
                        wp_enqueue_script( 'google-maps', 'http://maps.googleapis.com/maps/api/js?key='.$maps_api.'&libraries=places' );
                    }
                }

                wp_enqueue_script( 'rem-new-property-js', REM_URL . '/assets/admin/js/admin-property.js' , array('jquery', 'wp-color-picker', 'jquery-ui-sortable'));

                $def_lat = rem_get_option('default_map_lat', '-33.890542'); 
                $def_long = rem_get_option('default_map_long', '151.274856');

                if (isset($post->ID) && get_post_meta( $post->ID, 'rem_property_latitude', true ) != '') {
                    $def_lat = get_post_meta( $post->ID, 'rem_property_latitude', true );
                }
                if (isset($post->ID) && get_post_meta( $post->ID, 'rem_property_longitude', true ) != '') {
                    $def_long = get_post_meta( $post->ID, 'rem_property_longitude', true );
                }

                $localize_vars = array(
                    'use_map_from' => rem_get_option('use_map_from', 'leaflet'),
                    'def_lat' => $def_lat,
                    'leaflet_styles' => rem_get_leaflet_provider(rem_get_option('leaflet_style')),
                    'def_long' => $def_long,
                    'zoom_level' => rem_get_option('maps_zoom_level', '18'),
                    'drag_icon' => apply_filters( 'rem_maps_drag_icon', REM_URL.'/assets/images/pin-drag.png' ),
                );

                wp_localize_script( 'rem-new-property-js', 'rem_map_ob', $localize_vars );
            }
        }

        if ( $check == 'rem_property_page_rem_settings' ) {
            wp_enqueue_style( 'wp-color-picker' );
            wp_enqueue_media();
            wp_enqueue_style( 'rem-bs-css', REM_URL . '/assets/admin/css/bootstrap.min.css' );
            wp_enqueue_style( 'select2', REM_URL . '/assets/admin/css/select2.min.css' );
            wp_enqueue_style( 'rem-new-property-css', REM_URL . '/assets/admin/css/admin.css' );
            wp_enqueue_script( 'select2', REM_URL . '/assets/admin/js/select2.min.js' , array('jquery'));
            wp_enqueue_script( 'sweet-alerts', REM_URL . '/assets/admin/js/sweetalert.min.js' , array('jquery'));
            wp_enqueue_script( 'rem-save-settings-js', REM_URL . '/assets/admin/js/page-settings.js' , array('jquery', 'wp-color-picker' ));
        }

        if ($check == 'user-edit.php' || $check == 'profile.php') {
            wp_enqueue_media();
            wp_enqueue_script( 'rem-profile-edit', REM_URL . '/assets/admin/js/profile.js' , array('jquery'));
        }

        if ($check == 'rem_property_page_rem_property_agents') {
            wp_enqueue_style( 'rem-bs-css', REM_URL . '/assets/admin/css/bootstrap.min.css' );
            wp_enqueue_script( 'sweet-alerts', REM_URL . '/assets/admin/js/sweetalert.min.js' , array('jquery'));
            wp_enqueue_script( 'rem-agents-settings-js', REM_URL . '/assets/admin/js/manage-agents.js'  , array('jquery'));
        }

        if ($check == 'rem_property_page_rem_documentation') {
            wp_enqueue_style( 'rem-bs-css', REM_URL . '/assets/admin/css/bootstrap.min.css' );
        }

        if ($check == 'rem_property_page_rem_extensions') {
            wp_enqueue_style( 'rem-bs-css', REM_URL . '/assets/admin/css/bootstrap.min.css' );
        }

        if ($check == 'rem_property_page_rem_custom_fields') {
            wp_enqueue_script( 'sweet-alerts', REM_URL . '/assets/admin/js/sweetalert.min.js' , array('jquery'));
            if ( !defined("REM_FIELDS_PATH")  ) {
                wp_enqueue_script( 'rem-save-settings-page', REM_URL . '/assets/admin/js/property-layout.js'  , array( 'jquery', 'jquery-ui-accordion', 'jquery-ui-sortable', 'jquery-ui-draggable' ));
            }
        }

        if ($check == 'rem_property_page_rem_agent_registration') {
            wp_enqueue_script( 'sweet-alerts', REM_URL . '/assets/admin/js/sweetalert.min.js' , array('jquery'));
            wp_enqueue_script( 'rem-agent-fields-page', REM_URL . '/assets/admin/js/agent-registration.js'  , array( 'jquery', 'jquery-ui-accordion', 'jquery-ui-sortable', 'jquery-ui-draggable' ));
        }        
    }

    function front_scripts(){
        $layout_agent = rem_get_option('agent_page_layout', 'plugin');
        $layout_archive = rem_get_option('archive_property_layout', 'plugin');
        $disable_map_script = rem_get_option('disable_map_script', 'no');
        if (is_singular( 'rem_property' )) {

            global $post;

            rem_load_bs_and_fa();

            rem_load_basic_styles();

            // Photorama
            wp_enqueue_style( 'rem-fotorama-css', REM_URL . '/assets/front/lib/fotorama.min.css' );
            wp_enqueue_script( 'rem-photorama-js', REM_URL . '/assets/front/lib/fotorama.min.js', array('jquery'));

            // Imagesfill and Loaded
            wp_enqueue_script( 'rem-imagefill-js', REM_URL . '/assets/front/lib/imagefill.min.js', array('jquery'));   
            wp_enqueue_script( 'rem-imagesloaded-js', REM_URL . '/assets/front/lib/imagesloaded.min.js', array('jquery'));   
            
            // Page Specific
            wp_enqueue_style( 'rem-single-property-css', REM_URL . '/assets/front/css/single-property.css' );

            if (rem_single_property_has_map($post->ID)) {
                $property_id = (isset($post->ID)) ? $post->ID : '';
                $latitude = get_post_meta($property_id, 'rem_property_latitude', true);
                $longitude = get_post_meta($property_id, 'rem_property_longitude', true);
                $address = get_post_meta($property_id, 'rem_property_address', true);
                $zoom = rem_get_option( 'maps_zoom_level', 10);
                $map_type = rem_get_option( 'maps_type', 'roadmap');
                $maps_api = apply_filters( 'rem_maps_api', 'AIzaSyBbpbij9IIXGftKhFLMHOuTpAbFoTU_8ZQ');
                $maps_icon_url = apply_filters( 'rem_maps_location_icon', REM_URL . '/assets/images/pin-maps.png', $post->ID );
                $load_map_from = ($latitude == '' || $longitude == '') ? 'address' : 'coords' ;
                if ($disable_map_script != "yes") {
                    
                    if (rem_get_option('use_map_from', 'leaflet') == 'leaflet') {
                        wp_enqueue_style( 'rem-leaflet-css', REM_URL . '/assets/front/leaflet/leaflet.css');
                        wp_enqueue_script( 'rem-leaflet-js', REM_URL . '/assets/front/leaflet/leaflet.js', array('jquery'));
                    } else {
                        if (is_ssl()) {
                            wp_enqueue_script( 'rem-single-property-map', 'https://maps.googleapis.com/maps/api/js?key='.$maps_api);
                        } else {
                            wp_enqueue_script( 'rem-single-property-map', 'http://maps.googleapis.com/maps/api/js?key='.$maps_api);
                        }
                    }

                }
                $icons_size = rem_get_option('leaflet_icons_size', '43x47');
                $icons_anchor = rem_get_option('leaflet_icons_anchor', '18x47');

                $localize_vars = array(
                    'use_map_from' => rem_get_option('use_map_from', 'leaflet'),
                    'latitude' => $latitude,
                    'longitude' => $longitude,
                    'zoom' => $zoom,
                    'map_type' => $map_type,
                    'leaflet_styles' => rem_get_leaflet_provider(rem_get_option('leaflet_style')),
                    'address' => $address,
                    'load_map_from' => $load_map_from,
                    'maps_icon_url' => $maps_icon_url,
                    'icons_size' => explode("x", $icons_size),
                    'icons_anchor' => explode("x", $icons_anchor),
                    'maps_styles' => stripcslashes(rem_get_option('maps_styles')),
                    'property_map_location_style' => rem_get_option('property_map_location_style', 'pin'),
                    'property_map_radius' => rem_get_option('property_map_radius', '100'),
                    'rem_main_color' => rem_get_option('rem_main_color', '#1FB7A6'),
                );
            }

            wp_enqueue_script( 'rem-single-property-js', REM_URL . '/assets/front/js/single-property.js', array('jquery'));

            if (isset($localize_vars)) {
                wp_localize_script( 'rem-single-property-js', 'rem_property_map', $localize_vars );
            } else {
                $localize_vars = array(
                    'latitude' => 'disable',
                );                
                wp_localize_script( 'rem-single-property-js', 'rem_property_map', $localize_vars );
            }

        }
        if(is_author()){
            global $wp_query;
            $curauth = $wp_query->get_queried_object();
            $author_info = $curauth;
            $author_id = $curauth->ID;
            $load_tem = true;
            if(rem_get_option('agent_page', 'all') == 'agent'){
                if ( in_array( 'rem_property_agent', (array) $curauth->roles ) ) {
                    $load_tem = true;
                } else {
                    $load_tem = false;
                }
            }
            
            if ( $load_tem ) {
                rem_load_bs_and_fa();
                rem_load_basic_styles();
                wp_enqueue_style( 'rem-skillbars-css', REM_URL . '/assets/front/lib/skill-bars.css' );

                // Imagesfill and Loaded
                wp_enqueue_script( 'rem-imagefill-js', REM_URL . '/assets/front/lib/imagefill.min.js', array('jquery'));   
                wp_enqueue_script( 'rem-imagesloaded-js', REM_URL . '/assets/front/lib/imagesloaded.min.js', array('jquery'));   
                
                // Carousel
                wp_enqueue_style( 'rem-carousel-css', REM_URL . '/assets/front/lib/slick.css' );
                wp_enqueue_script( 'rem-carousel-js', REM_URL . '/assets/front/lib/slick.min.js', array('jquery'));

                // Page Specific
                wp_enqueue_style( 'rem-archive-property-css', REM_URL . '/assets/front/css/archive-property.css' );
                wp_enqueue_style( 'rem-profile-agent-css', REM_URL . '/assets/front/css/profile-agent.css' );
                wp_enqueue_script( 'rem-profile-agent-js', REM_URL . '/assets/front/js/profile-agent.js', array('jquery'));
            }            
        }
        if (is_archive() && $layout_archive == 'plugin') {
            global $post;
            if (isset($post->post_type) && $post->post_type == 'rem_property') {
                rem_load_bs_and_fa();
                rem_load_basic_styles();

                // Imagesfill and Loaded
                wp_enqueue_script( 'rem-imagefill-js', REM_URL . '/assets/front/lib/imagefill.min.js', array('jquery'));   
                wp_enqueue_script( 'rem-imagesloaded-js', REM_URL . '/assets/front/lib/imagesloaded.min.js', array('jquery'));   
                
                // Page Specific
                wp_enqueue_style( 'rem-archive-property-css', REM_URL . '/assets/front/css/archive-property.css' );
                wp_enqueue_script( 'rem-tooltip', REM_URL . '/assets/front/lib/tooltip.js', array('jquery'));
                wp_enqueue_script( 'rem-archive-property-js', REM_URL . '/assets/front/js/archive-property.js', array('jquery'));
            }
        }
    }

    function rem_role_cap(){

        if (function_exists('pll_register_string')) {
            $options = rem_get_option('property_detail_fields', '');
            if ($options != '') {
                pll_register_string('All Features ', $options, 'real-estate-manager-pro', true);
                $options_arr = explode("\n", $options);
                foreach ($options_arr as $option) {
                    if (trim($option) != '') {
                        pll_register_string('Property Feature '.trim($option), trim($option), 'real-estate-manager-pro');
                    }
                }
            }
        }

        if(get_option('rem_version_change') != 'done'){
            $fields = get_option( 'rem_property_fields' );
            $new_array = array();
            if (is_array($fields)) {
                foreach ($fields as $field) {
                    if ($field['key'] == 'property_video') {
                        $field['type'] = 'video';
                    }
                    $new_array[] = $field;
                }
                update_option( 'rem_property_fields', $new_array );
            }
            update_option('rem_version_change', 'done');
        }

        if (!$GLOBALS['wp_roles']->is_role( 'rem_property_agent' )) {
            add_role(
                'rem_property_agent',
                __( 'Property Agent', 'real-estate-manager' ),
                array(
                    'read' => true,
                    'edit_posts' => true,
                    'delete_posts' => false,
                    'publish_posts' => false,
                    'upload_files' => true,
                )
            );
            flush_rewrite_rules();
        }

        $roles = array('rem_property_agent', 'editor', 'administrator');

        // Loop through each role and assign capabilities
        foreach($roles as $the_role) { 

            $role = get_role($the_role);

            if ($role) {
                $role->add_cap( 'read' );
                $role->add_cap( 'read_rem_property');
                $role->add_cap( 'read_private_rem_properties' );
                $role->add_cap( 'edit_rem_property' );
                $role->add_cap( 'edit_rem_properties' );

                if($the_role == 'administrator'){
                    $role->add_cap( 'edit_others_rem_properties' );
                    $role->add_cap( 'delete_others_rem_properties' );
                    if (rem_get_option('property_submission_mode') == 'approve') {
                        $role->add_cap( 'publish_rem_properties' );
                    }
                }
                if (rem_get_option('property_submission_mode') == 'publish') {
                    $role->add_cap( 'publish_rem_properties' );
                }
                $role->add_cap( 'edit_published_rem_properties' );
                $role->add_cap( 'delete_private_rem_properties' );
                $role->add_cap( 'delete_published_rem_properties' );
            }
        }
    }

    function rem_agent_extra_fields($user){
        if (1) {
            include REM_PATH . '/inc/menu/agent-profile-fields.php';
        }
        $current_user = wp_get_current_user();
        if ($user->ID == $current_user->ID) { ?>
            <div style="text-align:center;">
                <h2>Login in App scanning this code</h2>
                <p><a target="_blank" href="https://play.google.com/store/apps/details?id=com.webcodingplace.rem">Download the android app</a></p>
                <?php
                    $siteurl = get_site_url();
                    $un = md5(uniqid($current_user->user_login, true));
                    update_user_meta( $current_user->ID, 'rem_barcode_access_token', $un );
                    $url = urlencode($siteurl.'&user='.$current_user->user_login.'&pass='.$un); ?>
                <img src="https://chart.googleapis.com/chart?chs=300x300&cht=qr&chl=<?php echo $url; ?>">
            </div>
        <?php }
    }

    function save_rem_agent_fields($user_id){
        if ( current_user_can( 'edit_user', $user_id )){
            $agent_fields = $this->get_agent_fields();
            foreach ($agent_fields as $field) {
                if (isset($_POST[$field['key']])) {
                    update_user_meta( $user_id, $field['key'], $_POST[$field['key']] );
                }
            }
        }
    }

    function get_all_property_features(){

        $property_individual_cbs = array(
            __( 'Attic', 'real-estate-manager' ),
            __( 'Gas Heat', 'real-estate-manager' ),
            __( 'Balcony', 'real-estate-manager' ),
            __( 'Wine Cellar', 'real-estate-manager' ),
            __( 'Basketball Court', 'real-estate-manager' ),
            __( 'Trash Compactors', 'real-estate-manager' ),
            __( 'Fireplace', 'real-estate-manager' ),
            __( 'Pool', 'real-estate-manager' ),
            __( 'Lake View', 'real-estate-manager' ),
            __( 'Solar Heat', 'real-estate-manager' ),
            __( 'Separate Shower', 'real-estate-manager' ),
            __( 'Wet Bar', 'real-estate-manager' ),
            __( 'Remodeled', 'real-estate-manager' ),
            __( 'Skylights', 'real-estate-manager' ),
            __( 'Stone Surfaces', 'real-estate-manager' ),
            __( 'Golf Course', 'real-estate-manager' ),
            __( 'Health Club', 'real-estate-manager' ),
            __( 'Backyard', 'real-estate-manager' ),
            __( 'Pet Allowed', 'real-estate-manager' ),
            __( 'Office/Den', 'real-estate-manager' ),
            __( 'Laundry', 'real-estate-manager' ),
        );

        if(has_filter('rem_property_features')) {
            $property_individual_cbs = apply_filters('rem_property_features', $property_individual_cbs);
        }

        return $property_individual_cbs;
    }

    function get_all_property_types(){

        $property_type_options  = array(
            __( 'Duplex', 'real-estate-manager' )   => __( 'Duplex', 'real-estate-manager' ),
            __( 'House', 'real-estate-manager' )    => __( 'House', 'real-estate-manager' ),
            __( 'Office', 'real-estate-manager' )   => __( 'Office', 'real-estate-manager' ),
            __( 'Retail', 'real-estate-manager' )   => __( 'Retail', 'real-estate-manager' ),
            __( 'Vila', 'real-estate-manager' )     => __( 'Vila', 'real-estate-manager' ),
        );

        if(has_filter('rem_property_types')) {
            $property_type_options = apply_filters('rem_property_types', $property_type_options);
        }

        return $property_type_options;
    }

    function get_all_property_purpose(){
        
        $property_purpose_options  = array(
           __( 'Rent', 'real-estate-manager' )  => __( 'Rent', 'real-estate-manager' ) ,
           __( 'Sell', 'real-estate-manager' )  => __( 'Sell', 'real-estate-manager' ) ,
        );

        if(has_filter('rem_property_purposes')) {
            $property_purpose_options = apply_filters('rem_property_purposes', $property_purpose_options);
        }

        return $property_purpose_options;
    }

    function get_all_property_status(){

        $property_status_options  = array(
            __( 'Normal', 'real-estate-manager' )       => __( 'Normal', 'real-estate-manager' ),
            __( 'Available', 'real-estate-manager' )    => __( 'Available', 'real-estate-manager' ),
            __( 'Not Available', 'real-estate-manager' )=> __( 'Not Available', 'real-estate-manager' ),
            __( 'Sold', 'real-estate-manager' )         => __( 'Sold', 'real-estate-manager' ),
            __( 'Open House', 'real-estate-manager' )   => __( 'Open House', 'real-estate-manager' ),
        );

        if(has_filter('rem_property_statuses')) {
            $property_status_options = apply_filters('rem_property_statuses', $property_status_options);
        }

        return $property_status_options;
    }

    function send_email_agent(){
        if (isset($_REQUEST) && $_REQUEST != '') {

            if (isset($_REQUEST['g-recaptcha-response'])) {
                if (!$_REQUEST['g-recaptcha-response']) {
                    $resp = array('fail' => 'already', 'msg' => __( 'Please check the captcha form.', 'real-estate-manager' ));
                    echo json_encode($resp); exit;
                } else {
                    $captcha = $_REQUEST['g-recaptcha-response'];
                    $secretKey = rem_get_option('captcha_secret_key', '6LcDhUQUAAAAAGKQ7gd1GsGAkEGooVISGEl3s7ZH');
                    $ip = $_SERVER['REMOTE_ADDR'];
                    $response = wp_remote_post("https://www.google.com/recaptcha/api/siteverify?secret=".$secretKey."&response=".$captcha."&remoteip=".$ip);
                    $responseKeys = json_decode($response['body'], true);
                    if(intval($responseKeys["success"]) !== 1) {
                        $resp = array('fail' => 'error', 'msg' => __( 'There was an error. Please try again after reloading page', 'real-estate-manager' ));
                        echo json_encode($resp); exit;
                    }
                }
            }

            $agent_info = get_userdata($_REQUEST['agent_id']);
            $agent_email = $agent_info->user_email;
            $client_msg = $_REQUEST['client_msg'];
            $client_email = sanitize_email( $_REQUEST['client_email'] );
            $client_name = sanitize_text_field( $_REQUEST['client_name'] );
            $client_phone = (isset($_REQUEST['client_phone'])) ? sanitize_text_field( $_REQUEST['client_phone'] ) : '' ;

            // If its from agent page
            if (isset($_REQUEST['subject']) && $_REQUEST['subject'] != '') {
                $subject = sanitize_text_field( $_REQUEST['subject'] );

            // if from property page
            } else {
                $property_title = esc_attr( get_the_title( $_REQUEST['property_id'] ) );
                $subject = rem_get_option('c_email_subject', $property_title);
                $subject = str_replace("%property_title%", $property_title, $subject);
                $subject = str_replace("%property_id%", $_REQUEST['property_id'], $subject);
            }

            $message = rem_get_option('c_email_msg', $client_msg);
            if (isset($_REQUEST['property_id']) && isset($property_title)) {
                $message = str_replace("%property_id%", $_REQUEST['property_id'], $message);
                $message = str_replace("%property_title%", $property_title, $message);
                $message = str_replace("%property_url%", get_permalink( $_REQUEST['property_id'] ), $message);
                $message = str_replace("%user_message%", $client_msg, $message);
                $message = str_replace("%user_email%", $client_email, $message);
                $message = str_replace("%client_name%", $client_name, $message);
                $message = str_replace("%phone%", $client_phone, $message);
            } else {
                $message = $client_msg;
            }
            if (rem_get_option('email_br', 'enable') == 'enable') {
               $message = nl2br(stripcslashes($message));
            }
            $message = apply_filters( 'rem_agent_contact_email_message', $message, $_REQUEST );
            
            $headers = array();
            $headers[] = "From: {$client_name} <{$client_email}>";
            $headers[] = "Content-Type: text/html";
            $headers[] = "MIME-Version: 1.0\r\n";

            $headers = apply_filters( 'rem_email_headers', $headers );
            
            // Additional Emails
            $emails_meta = rem_get_option('email_agent_contact', '');            
            if ($emails_meta != '') {
                $emails = explode("\n", $emails_meta);
                if (is_array($emails)) {
                    foreach ($emails as $e) {
                        $headers[] = "Cc: $e";
                    }
                }
            }
            
            if (wp_mail( $agent_email, $subject, $message, $headers )) {
                $resp = array('status' => 'sent', 'msg' => __( 'Email Sent Successfully', 'real-estate-manager' ) );
            } else {
                $resp = array('status' => 'fail', 'msg' => __( 'There is some problem, please try later', 'real-estate-manager' ) );
            }
        }

        echo json_encode($resp); die(0);
    }

    function menu_pages(){
        add_submenu_page( 'edit.php?post_type=rem_property', 'All Property Agents', __( 'Agents', 'real-estate-manager' ), 'manage_options', 'rem_property_agents', array($this, 'render_agents_page') );
        add_submenu_page( 'edit.php?post_type=rem_property', 'Real Estate Manager - Custom Fields', __( 'Property Fields', 'real-estate-manager' ), 'manage_options', 'rem_custom_fields', array($this, 'render_custom_fields_page') );
        add_submenu_page( 'edit.php?post_type=rem_property', 'Real Estate Manager - Agent Fields', __( 'Registration Fields', 'real-estate-manager' ), 'manage_options', 'rem_agent_registration', array($this, 'render_agent_fields_page') );
        add_submenu_page( 'edit.php?post_type=rem_property', 'Real Estate Manager - Settings', __( 'Settings', 'real-estate-manager' ), 'manage_options', 'rem_settings', array($this, 'render_settings_page') );
        add_submenu_page( 'edit.php?post_type=rem_property', 'Real Estate Manager - Extensions', __( 'Extensions', 'real-estate-manager' ), 'manage_options', 'rem_extensions', array($this, 'render_ext_page') );
        add_submenu_page( 'edit.php?post_type=rem_property', 'Real Estate Manager - Documentation', __( 'Help/Support', 'real-estate-manager' ), 'manage_options', 'rem_documentation', array($this, 'render_docs_page') );
    }

    function render_custom_fields_page(){
        include_once REM_PATH. '/inc/menu/custom-fields/custom-fields-page.php';
    }

    function render_agent_fields_page(){
        include_once REM_PATH. '/inc/menu/agent/custom-fields-page.php';
    }

    function render_agents_page(){
        include_once REM_PATH. '/inc/menu/page-agents.php';
    }

    function render_docs_page(){
        include_once REM_PATH. '/inc/menu/page-docs.php';
    }

    function render_ext_page(){
        include_once REM_PATH. '/inc/menu/page-extensions.php';
    }

    function render_settings_page(){
        include_once REM_PATH. '/inc/menu/page-settings.php';
    }

    function deny_agent(){
        if (isset($_REQUEST) && current_user_can( 'manage_options' )) {
            $pending_agents = get_option( 'rem_pending_users' );
            do_action( 'rem_new_agent_rejected', $pending_agents[$_REQUEST['userindex']] );
            unset($pending_agents[$_REQUEST['userindex']]);
            update_option( 'rem_pending_users', $pending_agents );
        }
        die(0);
    }

    function approve_agent(){
        if (isset($_REQUEST) && current_user_can( 'manage_options' )) {
            $pending_agents = get_option( 'rem_pending_users' );

            $new_agent = $pending_agents[$_REQUEST['userindex']];

            extract($new_agent);

            $agent_id = wp_create_user( $username, $password, $useremail );

            do_action( 'rem_new_agent_approved', $new_agent );

            if ($agent_id != '') {
                wp_update_user( array( 'ID' => $agent_id, 'role' => 'rem_property_agent' ) );
            }

            $agent_fields = $this->get_agent_fields();

            foreach ($agent_fields as $field) {
                if (isset($new_agent[$field['key']])) {
                    update_user_meta( $agent_id, $field['key'], $new_agent[$field['key']]);
                }
            }

            unset($pending_agents[$_REQUEST['userindex']]);

            update_option( 'rem_pending_users', $pending_agents );
        }

        die(0);
    }
    
    static function rem_activated(){
        /*
         * Adding Custom Role 'rem_property_agent'
         */
        $roles_set = get_option('rem_role_isset');

        if(!$roles_set){
            add_role(
                'rem_property_agent',
                __( 'Property Agent', 'real-estate-manager' ),
                array(
                    'read' => true,
                    'edit_posts' => true,
                    'delete_posts' => false,
                    'publish_posts' => false,
                    'upload_files' => true,
                )
            );
            flush_rewrite_rules();
            update_option('rem_role_isset', true);
        }       
    }

    function rem_templates($template){
        $layout_agent = rem_get_option('agent_page_layout', 'plugin');
        $layout_archive = rem_get_option('archive_property_layout', 'plugin');
        $property_layout = rem_get_option('single_property_layout', 'plugin');

        if (is_author() && $layout_agent == 'plugin') {
            global $wp_query;
            $curauth = $wp_query->get_queried_object();
            $author_info = $curauth;
            $author_id = $curauth->ID;
            $load_tem = true;
            if(rem_get_option('agent_page', 'all') == 'agent'){
                if ( in_array( 'rem_property_agent', (array) $curauth->roles ) ) {
                    $load_tem = true;
                } else {
                    $load_tem = false;
                }
            }
            if ( $load_tem ) {
                $template = REM_PATH . '/templates/agent.php';
            }
        }
        if (is_archive() && $layout_archive == 'plugin') {
            global $post;
            if (isset($post->post_type) && $post->post_type == 'rem_property') {
                $template = REM_PATH . '/templates/list-properties.php';
            }
        }
        if (is_singular( 'rem_property' )) {
            global $post;

            if (isset($post->post_type) && $post->post_type == 'rem_property' && $property_layout == 'plugin') {
                $template = REM_PATH . '/templates/single/default.php';
            }

            if (isset($post->post_type) && $post->post_type == 'rem_property' && $property_layout == 'full_width') {
                $template = REM_PATH . '/templates/single/full-width.php';
            }

            if (isset($post->post_type) && $post->post_type == 'rem_property' && $property_layout == 'left_sidebar') {
                $template = REM_PATH . '/templates/single/left-sidebar.php';
            }

            if (isset($post->post_type) && $post->post_type == 'rem_property' && $property_layout == 'auto') {
                $theme = wp_get_theme();
                $template_path = REM_PATH.'/templates/single/'.$theme.'.php';
                if (file_exists($template_path)) {
                    $template = $template_path;
                } else {
                    $template = REM_PATH . '/templates/single/default.php';
                }
            }
        }

        return $template;
    }

    function admin_settings_fields(){

        include REM_PATH.'/inc/menu/admin-settings-arr.php';

        return $fieldsData;
    }

    function render_setting_field($field){
        ob_start();
        include REM_PATH.'/inc/menu/render-admin-settings.php';
        $field_html = ob_get_clean();
        return apply_filters( 'rem_admin_settings_field_raw_html', $field_html, $field );
    }

    function save_admin_settings(){
        if (isset($_REQUEST)) {
            $resp = array('status' => '', 'title' => '', 'message' => '');
            
            if (update_option( 'rem_all_settings', $_REQUEST )) {
                $resp['status'] = 'success';
                $resp['title'] = __( 'Settings Saved!', 'real-estate-manager' );
                $resp['message'] = __( 'Settings are saved in the database successfully.', 'real-estate-manager' );
                if (isset($_REQUEST['property_submission_mode'])) {
                    $role = get_role( 'rem_property_agent' );
                    if ($_REQUEST['property_submission_mode'] == 'publish') {
                        $role->add_cap( 'publish_rem_properties' );
                    } elseif ($_REQUEST['property_submission_mode'] == 'approve') {
                        $role->remove_cap( 'publish_rem_properties' );
                    }
                }
            } else {
                $resp['status'] = 'error';
                $resp['title'] = __( 'Failed!', 'real-estate-manager' );
                $resp['message'] = __( 'There is some error or you did not make any change.', 'real-estate-manager' );
            }
            echo json_encode($resp);
        }
        die(0);
    }

    function save_custom_fields(){
        if (isset($_REQUEST['fields'])) {
            $resp = array('status' => '', 'title' => '', 'message' => '');
            $fields_arr = array();
            foreach ($_REQUEST['fields'] as $field) {
                $field['editable'] = ($field['editable'] == 'false') ? false : true;
                $field['options'] = ($field['options'] != '') ? explode("\n", trim($field['options'])) : array();
                $fields_arr[] = $field;
            }
            if (update_option( 'rem_property_fields', $fields_arr )) {
                $resp['status'] = 'success';
                $resp['title'] = __( 'Settings Saved!', 'real-estate-manager' );
                $resp['message'] = __( 'Settings are saved in the database successfully.', 'real-estate-manager' );
            } else {
                $resp['status'] = 'error';
                $resp['title'] = __( 'Failed!', 'real-estate-manager' );
                $resp['message'] = __( 'There is some error or you did not make any change.', 'real-estate-manager' );
            }

            echo json_encode($resp);
        }
        die(0);
    }

    function reset_custom_fields(){
        if (isset($_REQUEST['reset']) && $_REQUEST['reset'] == 'yes') {
            delete_option( 'rem_property_fields' );
        }
        die(0);
    }

    function property_messages( $messages ) {
        $post             = get_post();
        $post_type        = get_post_type( $post );
        $post_type_object = get_post_type_object( $post_type );

        $messages['rem_property'] = array(
            0  => '', // Unused. Messages start at index 1.
            1  => __( 'Property updated.', 'real-estate-manager' ),
            2  => __( 'Custom field updated.', 'real-estate-manager' ),
            3  => __( 'Custom field deleted.', 'real-estate-manager' ),
            4  => __( 'Property updated.', 'real-estate-manager' ),
            /* translators: %s: date and time of the revision */
            5  => isset( $_GET['revision'] ) ? sprintf( __( 'Property restored to revision', 'real-estate-manager' ), wp_post_revision_title( (int) $_GET['revision'], false ) ) : false,
            6  => __( 'Property published.', 'real-estate-manager' ),
            7  => __( 'Property saved.', 'real-estate-manager' ),
            8  => __( 'Property submitted.', 'real-estate-manager' ),
            9  => sprintf(
                __( 'Property scheduled.', 'real-estate-manager' ),
                // translators: Publish box date format, see http://php.net/date
                date_i18n( __( 'M j, Y @ G:i', 'real-estate-manager' ), strtotime( $post->post_date ) )
            ),
            10 => __( 'Property draft updated.', 'real-estate-manager' )
        );

        if ( $post_type_object->publicly_queryable && 'rem_property' === $post_type ) {
            $permalink = get_permalink( $post->ID );

            $view_link = sprintf( ' <a href="%s">%s</a>', esc_url( $permalink ), __( 'View Property', 'real-estate-manager' ) );
            $messages[ $post_type ][1] .= $view_link;
            $messages[ $post_type ][6] .= $view_link;
            $messages[ $post_type ][9] .= $view_link;

            $preview_permalink = add_query_arg( 'preview', 'true', $permalink );
            $preview_link = sprintf( ' <a target="_blank" href="%s">%s</a>', esc_url( $preview_permalink ), __( 'Preview Property', 'real-estate-manager' ) );
            $messages[ $post_type ][8]  .= $preview_link;
            $messages[ $post_type ][10] .= $preview_link;
        }

        return $messages;
    }

    function single_property_fields(){
        $area_unit = rem_get_option('properties_area_unit', 'Sq Ft');
        $saved_fields = get_option( 'rem_property_fields' );
        $inputFields  = array();
        if ($saved_fields != '' && is_array($saved_fields)) {
            $inputFields = $saved_fields;
        } else {
            include REM_PATH.'/inc/arrays/property-fields.php';
        }

        if(has_filter('rem_property_settings_fields')) {
            $inputFields = apply_filters('rem_property_settings_fields', $inputFields);
        }

        return $inputFields;
    }

    function get_all_property_fields(){
        $inputFields = array();
        include REM_PATH . '/inc/admin/single-property-fields.php';
        return apply_filters( 'rem_all_input_fields', $inputFields );
    }

    function author_override($output){
        global $post, $user_ID;
        if (isset($post->post_type) && 'rem_property' === $post->post_type) {

            // return if this isn't the theme author override dropdown
            if (!preg_match('/post_author_override/', $output)) return $output;

            // return if we've already replaced the list (end recursion)
            if (preg_match ('/post_author_override_replaced/', $output)) return $output;

            // replacement call to wp_dropdown_users
            $output = wp_dropdown_users(array(
                'echo' => 0,
                'name' => 'post_author_override_replaced',
                'selected' => empty($post->ID) ? $user_ID : $post->post_author,
                'include_selected' => true
            ));

            // put the original name back
            $output = preg_replace('/post_author_override_replaced/', 'post_author_override', $output);

        }

        return $output;

    }

    function permalink_settings(){
        if( isset( $_POST['rem_property_permalink'] ) ){
            update_option( 'rem_property_permalink', sanitize_title_with_dashes( $_POST['rem_property_permalink'] ) );
        }
        
        // Add a settings field to the permalink page
        add_settings_field( 'rem_property_permalink', __( 'Property Page Base' , 'real-estate-manager' ), array($this, 'render_property_permalink_field'), 'permalink', 'optional' );
    }


    function render_property_permalink_field(){
        $s_value = get_option( 'rem_property_permalink' );
        $value = ($s_value != '') ? $s_value : 'property' ;
        echo '<input type="text" value="' . esc_attr( $value ) . '" name="rem_property_permalink" id="rem_property_permalink" class="regular-text" />';
    }

    function get_agent_fields(){
        $saved_fields = get_option( 'rem_agent_fields' );
        $fields  = array();
        if ($saved_fields != '' && is_array($saved_fields)) {
            $fields = $saved_fields;
        } else {
            include REM_PATH.'/inc/arrays/agent-fields.php';
        }

        $fields = apply_filters( 'rem_agent_fields', $fields );

        return $fields;
    }

    function rem_property_column_head($defaults){
        $defaults['property_type'] = __( 'Type', 'real-estate-manager' );
        $defaults['property_purpose'] = __( 'Purpose', 'real-estate-manager' );
        $defaults['property_status'] = __( 'Status', 'real-estate-manager' );
        $defaults['is_featured'] = __( 'Is Featured', 'real-estate-manager' );
        return $defaults;       
    }

    function rem_property_column_content($column_name, $p_id){
        if ($column_name == 'property_type') {
            echo get_post_meta( $p_id, 'rem_property_type', true );
        }
        if ($column_name == 'property_purpose') {
            echo get_post_meta( $p_id, 'rem_property_purpose', true );
        }
        if ($column_name == 'property_status') {
            echo get_post_meta( $p_id, 'rem_property_status', true );
        }
        if ($column_name == 'is_featured') {
            echo get_post_meta( $p_id, 'rem_property_featured', true );
        }
    }

    function filter_properties_list_admin($post_type){
        if('rem_property' !== $post_type){
          return; //filter your post
        }

        global $rem_ob;
        $all_types = $rem_ob->get_all_property_types();

        echo '<select id="filter-by-property-type" name="filter_property_type">';
        echo '<option value="">' . __( 'All Types', 'real-estate-manager' ) . ' </option>';
        $selected = (isset($_REQUEST['filter_property_type'])) ? $_REQUEST['filter_property_type'] : '' ;
        foreach($all_types as $type){
          $select = ($type == $selected) ? ' selected="selected"':'';
          echo '<option value="'.$type.'"'.$select.'>' . $type . ' </option>';
        }
        echo '</select>';


        $all_purpose = $rem_ob->get_all_property_purpose();

        echo '<select id="filter-by-property-purpose" name="filter_property_purpose">';
        echo '<option value="">' . __( 'All Purpose', 'real-estate-manager' ) . ' </option>';
        $selected = (isset($_REQUEST['filter_property_purpose'])) ? $_REQUEST['filter_property_purpose'] : '' ;
        foreach($all_purpose as $purpose){
          $select = ($purpose == $selected) ? ' selected="selected"':'';
          echo '<option value="'.$purpose.'"'.$select.'>' . $purpose . ' </option>';
        }
        echo '</select>';

        $all_status = $rem_ob->get_all_property_status();

        echo '<select id="filter-by-property-status" name="filter_property_status">';
        echo '<option value="">' . __( 'All Status', 'real-estate-manager' ) . ' </option>';
        $selected = (isset($_REQUEST['filter_property_status'])) ? $_REQUEST['filter_property_status'] : '' ;
        foreach($all_status as $status){
          $select = ($status == $selected) ? ' selected="selected"':'';
          echo '<option value="'.$status.'"'.$select.'>' . $status . ' </option>';
        }
        echo '</select>';        
    }

    function rem_validate_pcode(){
        if (isset($_REQUEST['code'])) {
            $url = get_site_url();
            $url = urlencode($url);
            $data = $this->get_response( 'http://clients.webcodingplace.com/wp-json/envato/validate/?code='.$_REQUEST['code'].'&id=20482813&url='.$url );
            $resp_arr = json_decode($data['body'], true);
            if ($resp_arr['status'] == 'success') {
                update_option( 'rem_validated', trim($_REQUEST['code']) );
            } else {
                delete_option( 'rem_validated' );
            }
            echo $data['body'];
        }
        die(0);
    }

    function rem_remove_pcode(){
        if (isset($_REQUEST)) {
            $code = get_option( 'rem_validated' );
            $data = $this->get_response( 'http://clients.webcodingplace.com/wp-json/envato/remove/?code='.$code );
            $resp_arr = json_decode($data['body'], true);
            if ($resp_arr['status'] == 'success') {
                delete_option( 'rem_validated' );
            }
            echo $data['body'];
        }
        die(0);
    }

    function validate_notice(){
        if (!is_rem_validated()) { ?>
            <div class="notice notice-warning is-dismissible">
                <p><strong>Thank you for choosing Real Estate Manager!</strong></p>
                <p><strong>Please <a href="<?php echo admin_url('edit.php?post_type=rem_property&page=rem_settings'); ?>">register</a></strong> this copy of the plugin to get notify and install new updates.</p>
                <p>Also be advised that according to <a href="https://codecanyon.net/licenses/standard?license=regular" target="_blank">CodeCanyon Standard Licenses</a> each site/project built using Real Estate Manager requires a separate license, which can be purchased <a href="https://codecanyon.net/item/real-estate-manager-pro/20482813?ref=WebCodingPlace" target="_blank">here</a>.</p>
            </div>
        <?php
        }
    }

    function remove_admin_bar(){
        if( is_user_logged_in() ) {
            $user = wp_get_current_user();
            $roles = ( array ) $user->roles;
            if (is_array($roles) && in_array("rem_property_agent", $roles)) {
               show_admin_bar(false);
            }
        }
    }

    function remove_tiny_mce_link_buttons($buttons){
        if( is_user_logged_in() ) {
            $user = wp_get_current_user();
            $roles = ( array ) $user->roles;
            if (is_array($roles) && in_array("rem_property_agent", $roles)) {
                $remove = 'link';
                if ( ( $key = array_search( $remove, $buttons ) ) !== false ){
                    unset( $buttons[$key] );
                }
            }
        }
        return $buttons;
    }

    function rem_disable_gutenberg($current_status, $post_type){
        if ($post_type === 'rem_property') return false;
        return $current_status;        
    }

    function filter_properties_request_query($query){
        //modify the query only if it admin and main query.
        if( !(is_admin() AND $query->is_main_query()) ){ 
          return $query;
        }

        //we want to modify the query for the targeted custom post and filter option
        if( !('rem_property' === $query->query['post_type']) ){
          return $query;
        }

        //for the default value of our filter no modification is required
        if( isset($_REQUEST['filter_property_type']) && $_REQUEST['filter_property_type'] != ''){
            $query->query_vars['meta_query'][] = array(
                array(
                    'key'     => 'rem_property_type',
                    'value'   => $_REQUEST['filter_property_type'],
                    'type'    => 'LIKE',
                ),
            );
        }

        if(isset($_REQUEST['filter_property_purpose']) && $_REQUEST['filter_property_purpose'] != ''){
            $query->query_vars['meta_query'][] = array(
                array(
                    'key'     => 'rem_property_purpose',
                    'value'   => $_REQUEST['filter_property_purpose'],
                    'type'    => 'LIKE',
                ),
            );
        }

        if(isset($_REQUEST['filter_property_status']) && $_REQUEST['filter_property_status'] != ''){
            $query->query_vars['meta_query'][] = array(
                array(
                    'key'     => 'rem_property_status',
                    'value'   => $_REQUEST['filter_property_status'],
                    'type'    => 'LIKE',
                ),
            );
        }

        return $query;
    }

    function save_custom_agent_fields(){
        if (isset($_REQUEST['fields'])) {
            $resp = array('status' => '', 'title' => '', 'message' => '');
            if (update_option( 'rem_agent_fields', $_REQUEST['fields'] )) {
                $resp['status'] = 'success';
                $resp['title'] = __( 'Settings Saved!', 'real-estate-manager' );
                $resp['message'] = __( 'Settings are saved in the database successfully.', 'real-estate-manager' );
            } else {
                $resp['status'] = 'error';
                $resp['title'] = __( 'Failed!', 'real-estate-manager' );
                $resp['message'] = __( 'There is some error or you did not make any change.', 'real-estate-manager' );
            }
            echo json_encode($resp);
        }
        die(0);
    }

    function reset_custom_agent_fields(){
        if (isset($_REQUEST['reset']) && $_REQUEST['reset'] == 'yes') {
            delete_option( 'rem_agent_fields' );
        }
        die(0);
    }

    /**
     * Defines the function used to initial the cURL library.
     *
     * @param  string  $url        To URL to which the request is being made
     * @return string  $response   The response, if available; otherwise, null
     */
    private function curl( $url ) {

        $curl = curl_init( $url );

        curl_setopt( $curl, CURLOPT_RETURNTRANSFER, true );
        curl_setopt( $curl, CURLOPT_HEADER, 0 );
        curl_setopt( $curl, CURLOPT_USERAGENT, '' );
        curl_setopt( $curl, CURLOPT_TIMEOUT, 10 );

        $response = curl_exec( $curl );
        if( 0 !== curl_errno( $curl ) || 200 !== curl_getinfo( $curl, CURLINFO_HTTP_CODE ) ) {
            $response = null;
        } // end if
        curl_close( $curl );

        return $response;

    } // end curl

    /**
     * Retrieves the response from the specified URL using one of PHP's outbound request facilities.
     *
     * @params  $url    The URL of the feed to retrieve.
     * @returns         The response from the URL; null if empty.
     */
    private function get_response( $url ) {

        $response = null;

        // First, we try to use wp_remote_get
        $response = wp_remote_get( $url );
        if( is_wp_error( $response ) ) {

            // If that doesn't work, then we'll try file_get_contents
            $response = file_get_contents( $url );
            if( false == $response ) {

                // And if that doesn't work, then we'll try curl
                $response = $this->curl( $url );
                if( null == $response ) {
                    $response = 0;
                } // end if/else

            } // end if

        } // end if

        return $response;
    }
}
?>