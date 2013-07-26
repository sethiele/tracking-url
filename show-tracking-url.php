<?php
/*
Plugin Name: Show Tracking URL
Description: Show a Tracking URL with Google Analytics Paras
Version: 0.1
Author: Sebastian Thiele
Author URI: http://www.sebastian-thiele.net
License: GPL2
*/

//?utm_source=facebook&utm_medium=page%3Apost&utm_campaign=*urlslug*
function build_tracking_url($utm_source, $post){
  $utm_medium = "page%3Apost";
  return post_permalink( $post->ID ) . "?utm_source=" . $utm_source . "&utm_medium=" . $utm_medium . "&utm_campaign=" . $post->post_name;
}

function build_output(){
  global $post;
  $sources = array( 'facebook', 'googleplus');

  $output = "<div id=\"strackingurl\"><table style=\"width:100%\">";
  foreach ($sources as $key => $source) {
    $output .= "<tr><td>" . 
        $source . 
      "</td><td><input type=\"text\" style=\"width:100%\" value=\"" . 
        build_tracking_url($source, $post) .
      "\"></td></tr>";
  }
  $output .= "</table></div>";
  return $output;
}

function display_tracking_url( $content ){
  if (is_single( ) && current_user_can( "publish_posts" )){
    return $content . build_output();
  } else {
    return $content;
  }
}

add_filter( 'the_content', 'display_tracking_url', 99 );

?>