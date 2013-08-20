<?php
/*
Plugin Name: Tracking URL
Description: Displays tracking URLs for sharing in Social Networks or where ever
Version: 1.0
Author: Sebastian Thiele
Author URI: http://www.sebastian-thiele.net
License: GPL2
*/

// i18n

/**
 * Load Language files
 */
function tracking_url_prepare(){
  load_plugin_textdomain( 'tracking-url', null, basename( __DIR__ ) . "/lang/" );
}

// Building the URLs and the Table

/**
 * Build the Tracking url of a page
 * 
 * @since 1.0
 * 
 * @param String $utm_source The Source
 * @param String $utm_medium The Medium
 * @param Post $post The Post Obect
 * 
 * @return String The URL with Tracking Params
 */
function build_tracking_url($utm_source, $utm_medium, $post){
  return post_permalink( $post->ID ) . "?utm_source=" . $utm_source . "&utm_medium=" . $utm_medium . "&utm_campaign=" . $post->post_name;
}

/**
 * Build the Output Table
 * 
 * @since 1.0
 * 
 * @return String The Table with all the Sources
 */
function build_output(){
  global $post;
  $options = get_option('tracking_url_options');
  $output = "<div class=\"trackingurl\">" .
    "<h2>" . __( "Tracking URL", 'tracking-url' ) . "</h2>" . 
    "<table class=\"trackingurl-table\">";
  foreach ($options as $key => $option) {
    $output .= "<tr>".
      "<td class=\"trackingurl-source\">" . 
        $option['source'] . 
      "</td>" .
      "<td class=\"trackingurl-source-value\">" .
        "<input " .
          "type=\"text\" " .
          "class=\"trackingurl-source-value-field trackingurl-source-" . $option['source'] . "\" ".
          "value=\"" . build_tracking_url($option['source'], $option['medium'], $post) . "\">" .
      "</td>" .
    "</tr>";
  }
  $output .= "</table>" .
    "</div>";
  return $output;
}

/**
 * Hook to alter the Content of a Page
 * 
 * @since 1.0
 * 
 * @param String $content The Content of the Article
 * 
 * @link http://codex.wordpress.org/Plugin_API/Filter_Reference/the_content Wordpress Documentation
 * 
 * @see the_content Filter
 * 
 * @return String The Content of the Post. If is_single then with Table otherwise without
 */
function display_tracking_url( $content ){
  if (is_single( ) && current_user_can( "publish_posts" )){
    return $content . build_output();
  } else {
    return $content;
  }
}

// Admin Menue

/**
 * Build the Admin Menu Link
 * 
 * @since 1.0
 * 
 * @link http://codex.wordpress.org/Plugin_API/Action_Reference/admin_menu Wordpress Documentation
 * 
 * @see admin_menu action
 */
function tracking_url_admin_menu() {
  add_options_page( 'Tracking URL', 'Tracking URL', 'manage_options', 'tracking-url', 'tracking_url_admin_page' );
}

/**
 * Display the Option Page
 * 
 * @since 1.0
 */
function tracking_url_admin_page() {
  if ( !current_user_can( 'manage_options' ) )  {
    wp_die( __( 'You do not have sufficient permissions to access this page.' ) );
  }
  echo '<div class="wrap">';
  screen_icon();
  echo '<h2>' . __( "Tracking URL Settings", 'tracking-url' ) . '</h2>';
    echo '<form method="post" action="options.php">';
      settings_fields('tracking_url_options');
      do_settings_sections('tracking-url');
      submit_button();
    echo '</form>';
  echo '</div>';
}

/**
 * Register the Settings API
 * 
 * @since 1.0
 * 
 * @see admin_init action
 */
function tracking_url_admin_init(){
  register_setting( 'tracking_url_options', 'tracking_url_options', 'plugin_options_validate' );
  add_settings_section('plugin_main', __( "Setup Tracking:", 'tracking-url' ), 'plugin_section_text', 'tracking-url');
  
  $options = get_option('tracking_url_options');
  foreach ($options as $key => $source) {
    add_settings_field('source' . $source['source'], __( 'Setting for ', 'tracking-url' ) . $source['source'], 'plugin_setting_string', 'tracking-url', 'plugin_main', array( 'key' => $key));
  }
  add_settings_field('source_new', __( 'Add new Source or let free', 'tracking-url' ), 'plugin_setting_string', 'tracking-url', 'plugin_main', array( 'key' => "new"));
}

/**
 * Description for Setting Section
 * 
 * @since 1.0
 * 
 * @see add_settings_section Function
 * 
 * @todo Write Text witch makes sence
 */
function plugin_section_text() {
  echo '<p>';
    printf( __( 'Here you can add new Sources for every tracking Source you like. See details of tracking <a href="%1$s" target="_blank">here</a>.', 'tracking-url' ), "https://support.google.com/analytics/answer/1033867" );
  echo '</p>';
  echo '<p>' .
    __( 'The Campaign Name is the URL-Slug of the Article.', 'tracking-url' ) . 
  '</p>';
}

/**
 * Display the Input Fields for Section tracking url
 * 
 * @since 1.0
 * 
 * @see add_settings_field Function
 */
function plugin_setting_string($args) {
  $options = get_option('tracking_url_options');
  $options = $options[$args['key']];
  echo __( "Source:", 'tracking-url' ) . " <input id='source_" .$args['key'] . "' name='tracking_url_options[" . $args['key'] . "][source]' type='text' value='" . urldecode( $options['source'] ) . "' placeholder='" . __( "Source", 'tracking-url' ) . "' />";
  echo __( "Medium:", 'tracking-url' ) . " <input id='medium_" .$args['key'] . "' name='tracking_url_options[" . $args['key'] . "][medium]' type='text' value='" . urldecode( $options['medium'] ) . "' placeholder='" . __( "Medium", 'tracking-url' ) . "' />";
}

/**
 * Validates and computes the input
 * 
 * @since 1.0
 * 
 * @see register_setting Function
 * 
 * @param Array $input All Input from the Form
 * 
 * @return Array The computet input
 * 
 * @todo Validation for empty input
 */
function plugin_options_validate($input) {
  if( isset( $input['new']['source']) &&  ($input['new']['source'] != NULL) ){
    $var = array( 'source' => $input['new']['source']);
    array_push($input, $var);
    unset($input['new']);
  } elseif ( isset( $input['new']['source']) && ($input['new']['source'] == NULL) ){
    unset($input['new']);
  }
  foreach ($input as $key => $entry) {
    $input[$key]['source'] = urlencode(strtolower($entry['source']));
    $input[$key]['medium'] = urlencode(strtolower($entry['medium']));
  }

  return $input;
}


// Hooks

// Hook to manipulate the Content of a post
add_filter( 'the_content', 'display_tracking_url', 99 );

// Hook the admin Menue
add_action('admin_menu', 'tracking_url_admin_menu');

// Hook add Settings
add_action('admin_init', 'tracking_url_admin_init');

// Hook Plugin loaded
add_action( 'plugins_loaded', 'tracking_url_prepare' );
?>
