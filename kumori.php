<?php
/*
Plugin Name: Kumori (曇)
Plugin URI: http://kumori-plugin.blogspot.com/
Description: Transcode video files on-the-cloud using AWS Elastic Transcoder and S3
Version: 0.21
Author: Arslanoglou Georgios
Author URI: http://gpower2.blogspot.com/
License: GPLv2
*/

// get the physical path of the plugin (for future includes)
define( 'KUMORI_PATH', plugin_dir_path(__FILE__) );
// get the url of the plugin (for future links)
define( 'KUMORI_URL', plugin_dir_url(__FILE__));

// add the menus for kumori
add_action( 'admin_menu', 'kumori_menu' );

function kumori_menu() {
    // add the utility page for the Kumori Tools and point it to the S3 manage page    
    add_utility_page( 
            'Kumori Tools', // $page_title
            'Kumori Tools', // $menu_title
            'edit_posts', // $capability
            KUMORI_PATH . 'kumori/KumoriActions.php', // $menu_slug
            '', // $function
            KUMORI_URL . 'kumori/kumori_favicon2_png24.png' // $icon_url
    );

    // add the submenu page for the Kumori-fy Videos page
    $kumori_actions_hook = add_submenu_page( 
            KUMORI_PATH . 'kumori/KumoriActions.php', // $parent_slug, 
            'Kumori-fy Videos!', // $page_title, 
            'Kumori-fy Videos!', // $menu_title, 
            'edit_posts', // $capability, 
            KUMORI_PATH . 'kumori/KumoriActions.php', // $menu_slug, 
            ''// $function 
    );     
	define('KUMORI_ACTIONS_PAGE',  substr($kumori_actions_hook, strripos ($kumori_actions_hook, "_" ) + 1 ) . ".php" );
	//echo KUMORI_ACTIONS_PAGE;
    
    // add the submenu page for the S3 manage page
    $kumori_s3_actions_hook = add_submenu_page( 
            KUMORI_PATH . 'kumori/KumoriActions.php', // $parent_slug, 
            'Manage S3', // $page_title, 
            'Manage S3', // $menu_title, 
            'update_core', // $capability, 
            KUMORI_PATH . 'kumori/S3Actions.php', // $menu_slug, 
            ''// $function 
    ); 
	define('KUMORI_S3_ACTIONS_PAGE',  substr($kumori_s3_actions_hook, strripos ($kumori_s3_actions_hook, "_" ) + 1 ) . ".php" );
	//echo KUMORI_S3_ACTIONS_PAGE;
    
    // add the submenu page for the Elastic Transcoder manage page
    $kumori_etr_actions_hook = add_submenu_page( 
            KUMORI_PATH . 'kumori/KumoriActions.php', // $parent_slug, 
            'Manage Elastic Transcoder', // $page_title, 
            'Manage Elastic Transcoder', // $menu_title, 
            'update_core', // $capability, 
            KUMORI_PATH . 'kumori/ETrActions.php', // $menu_slug, 
            ''// $function 
    );            
	define('KUMORI_ETR_ACTIONS_PAGE',  substr($kumori_etr_actions_hook, strripos ($kumori_etr_actions_hook, "_" ) + 1 ) . ".php" );
	//echo KUMORI_ETR_ACTIONS_PAGE;
    
    // initialize the settings for kumori
    function kumori_settings_init() {
        // Add the section to media settings so we can add our fields to it
        // $id, $title, $callback, $page
        add_settings_section(
               'kumori_settings_section',
               'Kumori Settings',
               'kumori_setting_section_callback_function',
               'media'
        );
        
        // Add the field with the names and function to use for our new
        // settings, put it in our new section
        // $id, $title, $callback, $page, $section, $args
        add_settings_field(
               'kumori_aws_access_id',
               'AWS Access Id',
               'kumori_aws_access_id_callback_function',
               'media',
               'kumori_settings_section'
        );

        add_settings_field(
               'kumori_aws_secret_key',
               'AWS Secret Key',
               'kumori_aws_secret_key_callback_function',
               'media',
               'kumori_settings_section'
        );

        add_settings_field(
               'kumori_aws_region',
               'AWS Service Region',
               'kumori_aws_region_callback_function',
               'media',
               'kumori_settings_section'
        );
        
        add_settings_field(
               'kumori_debug_mode',
               'Debug Mode',
               'kumori_debug_mode_callback_function',
               'media',
               'kumori_settings_section'
        );        
        // Register our setting so that $_POST handling is done for us and
        // our callback function just has to echo the <input>
        register_setting('media', 'kumori_aws_access_id');
        register_setting('media', 'kumori_aws_secret_key');
        register_setting('media', 'kumori_aws_region');
        register_setting('media', 'kumori_debug_mode');
    }

    add_action('admin_init', 'kumori_settings_init');

    function kumori_setting_section_callback_function() {
           echo '<p>The required settings for Kumori to work. These are the credentials for the AWS service.</p>';
    }

    function kumori_aws_access_id_callback_function() {
        echo '<input name="kumori_aws_access_id" id="gv_kumori_aws_access_id" type="password" value="'. get_option('kumori_aws_access_id').'" class="code" size="50"/>';
    } 
    
    function kumori_aws_secret_key_callback_function() {
        echo '<input name="kumori_aws_secret_key" id="gv_kumori_aws_secret_key" type="password" value="'. get_option('kumori_aws_secret_key').'" class="code" size="50"/>';
    } 
    
    function kumori_aws_region_callback_function() {   
        echo '<input name="kumori_aws_region" id="gv_kumori_aws_region" type="text" value="'. get_option('kumori_aws_region').'" class="code" size="50"/>
            <p class="description">            
            NORTHERN_VIRGINIA = "us-east-1"
            <br/>NORTHERN_CALIFORNIA = "us-west-1"
            <br/>OREGON = "us-west-2"
            <br/>GOV_CLOUD_US = "us-gov-west-1"
            <br/>IRELAND = "eu-west-1"
            <br/>SINGAPORE = "ap-southeast-1"
            <br/>SYDNEY = "ap-southeast-2"
            <br/>TOKYO = "ap-northeast-1"
            <br/>SAO_PAULO = "sa-east-1"
            </p>';        
    }     
    
    function kumori_debug_mode_callback_function() {
        echo '<input name="kumori_debug_mode" id="gv_kumori_debug_mode" type="checkbox" value="1" '. checked(1, get_option('kumori_debug_mode'), false).' class="code" size="50"/>';
    } 
}


?>
