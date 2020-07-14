<?php
/**
* REST API end points for mobile app
*/
class REM_REST_ROUTES
{
    
    function __construct(){
        add_action( 'rest_api_init', array($this, 'register_api_hooks') );
    }

    function register_api_hooks(){
        register_rest_route( 'rem', '/login', array( 'methods'  => 'POST', 'callback' => array($this, 'api_login'), ) );
        register_rest_route( 'rem', '/stats', array( 'methods'  => 'POST', 'callback' => array($this, 'get_stats'), ) );
        register_rest_route( 'rem', '/properties', array( 'methods'  => 'POST', 'callback' => array($this, 'get_my_properties'), ) );
        register_rest_route( 'rem', '/all-properties', array( 'methods'  => 'POST', 'callback' => array($this, 'get_all_properties'), ) );
        register_rest_route( 'rem', '/property', array( 'methods'  => 'POST', 'callback' => array($this, 'get_single_property'), ) );
        register_rest_route( 'rem', '/get-fields', array( 'methods'  => 'POST', 'callback' => array($this, 'get_property_fields'), ) );
        register_rest_route( 'rem', '/create-property', array( 'methods'  => 'POST', 'callback' => array($this, 'create_property'), ) );
        register_rest_route( 'rem', '/delete-property', array( 'methods'  => 'POST', 'callback' => array($this, 'delete_property'), ) );
        register_rest_route( 'rem', '/contact-agent', array( 'methods'  => 'POST', 'callback' => array($this, 'send_email_to_agent'), ) );
    }

    function get_single_property(WP_REST_Request $request){
        $property_id = $request->get_param( 'property_id' );
        $resp = array(
            'status' => 'failed',
            'message' => 'There is some error!',
            'data' => array(),
        );
        global $rem_ob;
        $fields = $rem_ob->single_property_fields();
        $data = array();

        foreach ($fields as $field) {
            if (isset($field['key']) && get_post_meta( $property_id, 'rem_'.$field['key'], true ) != '') {
                $data[] = array(
                    'title' => $field['title'],
                    'value' => get_post_meta( $property_id, 'rem_'.$field['key'], true ),
                    'key'   => $field['key'],
                );   
            }
        }
        
        $property_details_cbs = get_post_meta( $property_id, 'rem_property_detail_cbs', true );
        $features = array();
        foreach($property_details_cbs as $option_name => $value) { if($option_name != '') {
            $features[] = $option_name;
        } }        

        $property_images = get_post_meta( $property_id, 'rem_property_images', true );

        if (is_array($property_images)) {
            foreach ($property_images as $id) {
                $image_url = wp_get_attachment_url($id);
                // $image_url = str_replace('localhost', '192.168.0.107', $image_url);
                if($image_url){
                    $resp['imgs'][] = array(
                        'url'   => $image_url,
                        'title' => html_entity_decode(wp_strip_all_tags(rem_display_property_price($property_id))),
                        'caption' => get_the_title( $property_id ),
                    );      
                }
                 
            }
        }        

        $resp['data'] = $data;
        $resp['features'] = $features;
        $content_post = get_post($property_id);
        $content = $content_post->post_content;
        $content = apply_filters('the_content', $content);
        $content = str_replace(']]>', ']]&gt;', $content);        
        $resp['content'] = strip_tags($content);
        $resp['status'] = 'success';
        $resp['message'] = 'Operation Successful!';

        return $resp;
    }

    function get_property_fields(WP_REST_Request $request){
        $token = $request->get_param( 'token' );
        $user_id = $this->is_valid_request($token);
        $property_id = $request->get_param( 'property_id' );
        $resp = array(
            'status' => 'failed',
            'message' => 'There is some error!',
            'data' => array(),
        );
        if (!$user_id) {
            return $resp;
        }

        global $rem_ob;
        $inputFields = $rem_ob->get_all_property_fields();
        $tabsData = rem_get_single_property_settings_tabs();
        $valid_tabs = array();
        foreach ($tabsData as $tab_key => $tab_title) {
            foreach ($inputFields as $field) {
                $field_tab = (isset($field['tab'])) ? $field['tab'] : '' ;
                if ($tab_key == $field_tab && !in_array($field_tab, $valid_tabs)) {
                   $valid_tabs[] = $field_tab; 
                }
            }
        }
        $fields_array = array();
        foreach ($tabsData as $name => $title) {
            if ($name != 'property_video' && $name != 'property_attachments') {
                if (in_array($name, $valid_tabs)) {
                        
                    $fields_array[] = array( "type"=> "section", "title"=> $title,"key"=> $name);
                    foreach ($inputFields as $field) {
                        if($field['tab'] == $name && $field['accessibility'] != 'disable' ){
                            $arr_to_push = array( "type"=> $field['type'], "title"=> $field['title'],"key"=> $field['key']);
                            if (isset($field['options'])) {
                                $arr_to_push['options'] = $field['options'];
                            }
                            if( $property_id && get_post_meta($property_id, 'rem_'.$field['key'], true) != "" ){
                                $arr_to_push['value'] = get_post_meta($property_id, 'rem_'.$field['key'], true);
                            }
                            $fields_array[] = $arr_to_push;
                        }
                    }
                } 
            } 
        }
        
        $resp = array(
            'status' => 'success',
            'message' => 'Operation Successfull!',
            'data' => $fields_array,
        );
        
        if($property_id){
            $features = get_post_meta($property_id, 'rem_property_detail_cbs', true);
            $features = array_keys($features);
            $resp['features'] = $features; 
            $resp['title'] = get_the_title($property_id);
            $content_post = get_post($property_id);
            $content = $content_post->post_content;
            $content = apply_filters('the_content', $content);
            $content = str_replace(']]>', ']]&gt;', $content);        
            $resp['content'] = strip_tags($content);
            $resp['property_status'] = get_post_status($property_id);
            
            $property_images = get_post_meta( $property_id, 'rem_property_images', true );
        
            if (is_array($property_images)) {
                $resp['imgs'] = array();
                foreach ($property_images as $id) {
                    $image_url = wp_get_attachment_url($id);
                    // $image_url = str_replace('localhost', '192.168.0.107', $image_url);
                    if($image_url){
                        $resp['imgs'][] = array(
                            'url'   => $image_url,
                            'id' => $id,
                            'caption' => get_the_title( $property_id ),
                        );      
                    }
                     
                }
            }            
        }
        


        return $resp;
    }
    
    function get_all_properties(WP_REST_Request $request){

        $resp = array(
            'status' => 'failed',
            'message' => 'failed',
            'data' => array(),
        );
        
        $myproperties = new WP_Query( array(
            'post_type' => 'rem_property',
            'posts_per_page' => -1,
            'post_status'    => array('draft', 'publish', 'pending'),
        ) );

        $properties = array();
        if( $myproperties->have_posts() ){
            while( $myproperties->have_posts() ){ 
                $myproperties->the_post();
                $properties[] = array(
                    'title' => get_the_title(),
                    'id' => get_the_id(),
                    'excerpt' => get_the_excerpt(),
                    'date' => get_the_date(),
                    'img' => get_the_post_thumbnail_url( get_the_id(), 'thumbnail' ),
                    'status' => get_post_status(get_the_id()),
                    'price' => html_entity_decode(wp_strip_all_tags(rem_display_property_price(get_the_id()))),
                );

            }
            wp_reset_postdata();
        } else {
            $resp['message'] = 'No Properties Found!';
            return json_encode($resp);
        }

        $resp['status'] = 'success';
        $resp['message'] = 'Success';
        $resp['data'] = $properties;

        return $resp;      
    }

    function create_property(WP_REST_Request $request){
        $token = $request->get_param( 'token' );
        $property_id = $request->get_param( 'property_id' );
        $existing_images = $request->get_param( 'existing_images' );
        $user_id = $this->is_valid_request($token);
        
        $resp = array(
            'status' => 'failed',
            'message' => 'There is some error!',
            'data' => array(),
        );
        if (!$user_id) {
            return $resp;
        }
        if ($request->get_param( 'title' ) == '') {
            $resp['message'] = 'Please provide a title';
            return $resp;
        }
        
        if ($property_id) {
            $images_arr = array();
            $existing_images = json_decode($existing_images);
            if(is_array($existing_images)){
                foreach ($existing_images as $imagedata) {
                    $images_arr[] = $imagedata->id;        
                }                
            }
            update_post_meta( $property_id, 'rem_property_images', $images_arr );    
        }
        
        $args = array(
            "post_title"    => $request->get_param( 'title' ),
            "post_type"     => "rem_property",
            "post_status"   => $request->get_param( 'status' ),
            'post_author'   => $user_id,
        );
        
        if($request->get_param( 'content' ) && $request->get_param( 'content' ) != ''){
            $args['post_content'] = $request->get_param( 'content' );
        }

        if($property_id){
            $args['ID'] = $property_id;
        }
        $post_id = wp_insert_post( $args );

        $fields = $request->get_param( 'fields_data' );
        if (!empty($fields)) {
            $fields = json_decode($fields);
            foreach ($fields as $key => $value) {
                
                update_post_meta( $post_id, 'rem_'.$key, $value );
            }
        }
        
        $features = $request->get_param( 'features' );
        $features = json_decode($features);
        if (!empty($features)) {
            $selected_features = array();
            foreach ($features as $feature) {
                $selected_features[$feature] = 'on' ;
            }
        }
        update_post_meta( $post_id, 'rem_property_detail_cbs', $selected_features );

        $imageData = $request->get_file_params();

        
        $image_ids = $this->upload_property_images($imageData, $post_id);
        if($property_id){
            $previous_images = get_post_meta($property_id, 'rem_property_images', true);
            $all_images = array_merge($previous_images, $image_ids);
            update_post_meta( $post_id, 'rem_property_images', $all_images );    
        } else {
            update_post_meta( $post_id, 'rem_property_images', $image_ids );    
        }
        
        
        $resp = array(
            'status' => 'success',
            'message' => 'Property is created with ID '.$post_id.' and status '.$request->get_param( 'status' ),
            'data' => $post_id,
        );
        if($property_id){
            $resp['message'] = 'Property Data Updated!';
        }
        return $resp;

    }

    function delete_property(WP_REST_Request $request){
        $token = $request->get_param( 'token' );
        $user_id = $this->is_valid_request($token);
        $property_id = $request->get_param( 'property_id' );

        $resp = array(
            'status' => 'failed',
            'message' => 'There is some error!',
            'data' => array(),
        );
        if (!$user_id) {
            return $resp;
        }

        if (get_post_field( 'post_author', $property_id ) == $user_id) {
            if (rem_get_option('attachment_deletion', 'remain') == 'delete') {
                $gallery_images = get_post_meta( $property_id, 'rem_property_images', true );
                if (is_array($gallery_images)) {
                    foreach ($gallery_images as $key => $id) {
                        wp_delete_attachment( $id, false );
                    }
                }
            }
            if (rem_get_option('property_deletion', 'delete') == 'trash') {
                wp_trash_post( $property_id );
            } else {
                wp_delete_post( $property_id, true );
            }
            $resp = array(
                'status' => 'success',
                'message' => 'Deleted',
                'data' => array(),
            );
        } else {
            $resp = array(
                'status' => 'failed',
                'message' => 'Sorry! You can not delete this property.',
                'data' => array(),
            );
        }
        return $resp;

    }
    
    function send_email_to_agent(WP_REST_Request $request){
        $property_id    = $request->get_param( 'property_id' );
        $clientName     = $request->get_param( 'clientName' );
        $clientemail    = $request->get_param( 'clientEmail' );
        $clientMessage  = $request->get_param( 'clientMessage' );

        $author_id = get_post_field ('post_author', $property_id);
        $agent_info = get_userdata($author_id);
        $agent_email = $agent_info->user_email;

        $subject = get_the_title($property_id);;

        $headers = array();
        $headers[] = "From: {$clientName} <{$clientemail}>";
        $headers[] = "Content-Type: text/html";
        $headers[] = "MIME-Version: 1.0\r\n";
        if (wp_mail( $agent_email, $subject, $clientMessage, $headers )) {
            $resp = array('status' => 'sent', 'message' => __( 'Email Sent Successfully', 'real-estate-manager'  ) );
        } else {
            $resp = array('status' => 'fail', 'message' => __( 'There is some problem, please try later', 'real-estate-manager' ) );
        }
        return $resp;
    }

    function upload_property_images($imageData, $post_id){
        $image_ids = array();
        if ( isset($imageData['images']['name']) ) {  
            foreach ($imageData['images']['name'] as $key => $value) {            
                if ($imageData['images']['name'][$key]) { 
                    $file = array( 
                        'name' => $imageData['images']['name'][$key],
                        'type' => $imageData['images']['type'][$key], 
                        'tmp_name' => $imageData['images']['tmp_name'][$key], 
                        'error' => $imageData['images']['error'][$key],
                        'size' => $imageData['images']['size'][$key]
                    ); 
                    $_FILES = array ("rem_images" => $file); 
                    foreach ($_FILES as $file => $array) {
                          require_once(ABSPATH . "wp-admin" . '/includes/image.php');
                          require_once(ABSPATH . "wp-admin" . '/includes/file.php');
                          require_once(ABSPATH . "wp-admin" . '/includes/media.php');
                          $attach_id = media_handle_upload( $file, $post_id );
                          $image_ids[] = $attach_id;
                    }
                } 
            } 
        }
        
        return $image_ids;
    }

    function get_my_properties(WP_REST_Request $request){

        $token = $request->get_param( 'token' );
        $user_id = $this->is_valid_request($token);

        $resp = array(
            'status' => 'failed',
            'message' => 'There is some error.',
            'data' => array(),
        );
        if (!$user_id) {
            return $resp;
        }
        $myproperties = new WP_Query( array(
            'author' => $user_id,
            'post_type' => 'rem_property',
            'posts_per_page' => -1,
            'post_status'    => array('draft', 'publish', 'pending'),
        ) );

        $properties = array();
        if( $myproperties->have_posts() ){
            while( $myproperties->have_posts() ){ 
                $myproperties->the_post();
                $properties[] = array(
                    'title' => get_the_title(),
                    'id' => get_the_id(),
                    'excerpt' => get_the_excerpt(),
                    'date' => get_the_date(),
                    'img' => get_the_post_thumbnail_url( get_the_id(), 'thumbnail' ),
                    'status' => get_post_status(get_the_id()),
                );

            }
            wp_reset_postdata();
        } else {
            $resp['message'] = 'No Properties Found!';
            return $resp;
        }
        $resp['message'] = 'Operation Successful!';
        $resp['status'] = 'success';
        $resp['data'] = $properties;

        return $resp;   
    }

    function api_login(WP_REST_Request $request){
        
        $username = $request->get_param( 'username' );
        $password = $request->get_param( 'password' );
        $fromQR = $request->get_param( 'fromqr' );
        
        $response = array(
            'data'      => array(),
            'message'   => 'Invalid email or password',
            'status'    => 'failed'
        );
        
        
        if( $username != '' && $password != '' ){
            
            if ( $this->checkValidEmail($username) ) {
                $user = get_user_by( 'email', $username );   
            } else {
                $user = get_user_by( 'login', $username);
            }

            if ( $user ){
                
                if ($fromQR == 'YES' && get_user_meta( $user->ID, 'rem_barcode_access_token', true ) == $password) {
                    $password_check = true;    
                } else {
                    $password_check = wp_check_password( $password, $user->user_pass, $user->ID );
                }


                if ( $password_check ){

                    /* Generate a unique auth token */
                    $token = bin2hex(openssl_random_pseudo_bytes(64));
                    
                    if( update_user_meta( $user->ID, 'rem_auth_token', $token ) ){
                        $post_status = $this->check_post_stats($user->ID);
                        $response['status'] = 'success';
                        $response['data'] = array(
                            'rem_auth_token'    =>  $token,
                            'user_id'           =>  $user->ID,
                            'user_login'        =>  $user->user_login,
                        );
                        $response['message'] = 'Successfully Authenticated';
                    }
                }
            }
        }

        return $response;
    }
     
    function get_stats(WP_REST_Request $request){
        
        $token = $request->get_param( 'token' );
        $user_id = $this->is_valid_request($token);
        $resp = array(
            'status' => 'failed',
            'message' => 'Token Expired!',
        );

        if ($user_id) {
            $post_stats = $this->check_post_stats($user_id);
            $resp = array(
                'status' => 'success',
                'message' => 'Success',
                'data' => $post_stats,
            );
        }
        return $resp;
    }

    function is_valid_request($token){
        
        $args = array( 'meta_query' => array( array( 'key' => 'rem_auth_token', 'value' => $token ) )  );
        $users = get_users($args);

        if (empty($users) || isset($users[1])) {
            return false;
        } else {
            return $users[0]->ID;
        }

    }

    function checkValidEmail($email) {
       $find1 = strpos($email, '@');
       $find2 = strpos($email, '.');
       return ($find1 !== false && $find2 !== false && $find2 > $find1);
    }
    
    function check_post_stats($user_id){
        $post_args = array(
            'post_type' => 'rem_property',
            'posts_per_page' => -1,
            'author'    => $user_id,
            'post_status'    => array('draft', 'publish', 'pending'),
        );
        
        $ps = get_posts($post_args);
    
        $pending_pps = 0;
        $published_pps = 0;
        $draft_pps = 0;
        
        if (is_array($ps)) {
                
            foreach ($ps as $key => $post) {
                
                switch ($post->post_status) {
                    case 'publish':
                        $published_pps += 1;            
                        break;
                    case 'draft':
                        $draft_pps += 1;            
                        break;
                    case 'pending':
                        $pending_pps += 1;            
                        break;
                }
            }
        }
        
        $prop_stats = array(
            'publish' => $published_pps,
            'pending' => $pending_pps,
            'draft' => $draft_pps,
        );
        return $prop_stats;
    }
}

?>