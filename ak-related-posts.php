<?php
/**
* Plugin Name: AFSB Related Posts
* Plugin URI: http://wpminds.com/
* Description: This plugin load all categories and posts under post content, you can attach related post for each post. Attached posts will show on single post page after post content.
* Version: 1.0
* Author: AFSB DEVELOPERS
* Author URI: http://wpminds.com/
**/

if ( ! defined( 'ABSPATH' ) ) {
	die( 'Invalid request.' );
}

/**********************************************************
 Load plugin JS/CS files
**********************************************************/


function load_ams_plugin_scripts() {

	wp_enqueue_script('backend-accordion-js', plugins_url( 'js/backend-accordion-js.js', __FILE__ ));

	wp_enqueue_style('backend-accordion-css', plugins_url( 'css/backend-accordion-css.css', __FILE__ ), array(), 'screen'); 

}
add_action('admin_enqueue_scripts', 'load_ams_plugin_scripts');

/**********************************************************
 Load plugin JS/CS files
**********************************************************/
function global_notice_meta_box() {

	//$screens = array( 'post', 'page');
	$screens = get_post_types();

	foreach ( $screens as $screen ) {
		add_meta_box(
			'global-notice',
			__( 'Releated Posts', 'sitepoint' ),
			'global_notice_meta_box_callback',
			$screen
		);
	}
}

add_action( 'add_meta_boxes', 'global_notice_meta_box' );

function global_notice_meta_box_callback( $post ) {

	// Add a nonce field so we can check for it later.
	wp_nonce_field( 'global_notice_nonce', 'global_notice_nonce' );

	$releatedCat = get_post_meta( $post->ID, 'releated_category', true );
	$value = get_post_meta( $post->ID, 'releated_posts', true );


	$arrfields = explode(',', $value);
	//	$relCat = explode(',', $releatedCat);

	// $cat_args=array(
	//     'orderby' => 'name',
	//     'order' => 'ASC'
	//    );
	// $categories=get_categories($cat_args);

	// if(!empty($categories))
	// echo 'Front-end label for Related Posts: <input type="text" value="'.get_option('label_name_related_post').'" name="label_name_related_post">';
	//$varhtml = '<div id="accordion_container">';


	//             if (in_array($category->term_id, $relCat)) {
	//                     $catchecked = 'checked';
	//             }else{
	//                     $catchecked = '';
	//             }

	//$internal .= '<div class="accordion_head"><span class="plusminus">+</span><input type="checkbox" name="releated_category[]" '.$catchecked.' value="' .$category->term_id . '"> ' . $category->name.' <!--<a href="'. get_category_link( $category->term_id ) .'" ' . ' target="_blank">' . $category->name.'</a>--> </div>';
	$args=array(
		'post_type' => 'procedure',
		'posts_per_page' => -1,
		//'category__in' => array($category->term_id),
	);

	$all_posts = get_posts( $args );

	$internal .='<div class="accordion_body"><ul>';  
	foreach($all_posts as $all_posts) {
		if (in_array($all_posts->ID, $arrfields)) {
			$checked = 'checked';
		}else{
			$checked = '';
		}

		$internal .='<li><input type="checkbox" name="releated_posts[]" '.$checked.' value="' .$all_posts->ID . '">';
		$short_heading = get_field('procedure_short_heading_for_single_page_drop_down', $all_posts->ID);
		if($short_heading){
			$internal .=  $short_heading;
		}else{	
			$all_posts->post_title;
		}
		$internal .='</li>';
	}
	wp_reset_postdata();
	$internal .='</ul></div>';


	echo $varhtml ='<div class="accordion_container">'.$internal.'</div>';

	//$varhtml ='</div>';

}
/**********************************************************
 Save post IDs
**********************************************************/

function save_global_notice_meta_box_data( $post_id ) {

	// Check if our nonce is set.

	if ( ! isset( $_POST['global_notice_nonce'] ) ) {
		return;
	}

	// Verify that the nonce is valid.
	if ( ! wp_verify_nonce( $_POST['global_notice_nonce'], 'global_notice_nonce' ) ) {
		return;
	}

	// If this is an autosave, our form has not been submitted, so we don't want to do anything.
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
		return;
	}

	// Check the user's permissions.
	if ( isset( $_POST['post_type'] ) && 'page' == $_POST['post_type'] ) {

		if ( ! current_user_can( 'edit_page', $post_id ) ) {
			return;
		}

	}
	else {

		if ( ! current_user_can( 'edit_post', $post_id ) ) {
			return;
		}
	}

	/* OK, it's safe for us to save the data now. */

	/*if ( ! isset( $_POST['releated_posts'] or $_POST['releated_category'] ) ) {
        return;
    }*/

	// Sanitize user input.

	foreach ($_POST['releated_posts'] as $value) {
		$postsid .= $value.',';

	}

	foreach ($_POST['releated_category'] as $catval) {
		$catsid .= $catval.',';

	}

	if(isset($_POST['label_name_related_post'])){
		$related_post_option_name = $_POST['label_name_related_post'];
	}else{
		$related_post_option_name = 'Related Posts'; 
	}


	update_post_meta( $post_id, 'releated_category', $catsid );
	update_post_meta( $post_id, 'releated_posts', $postsid );
	wp_cache_delete ( 'alloptions', 'options' );
	update_option( 'label_name_related_post', $related_post_option_name);

}

add_action( 'save_post', 'save_global_notice_meta_box_data' );

/**********************************************************
Display posts on single post page
**********************************************************/
function show_custom_related_posts($content){
	if( is_singular('procedure') ) {
		global $post;
		$value = get_post_meta( $post->ID, 'releated_posts', true );
		$arrfields = explode(',', $value);
		// 		  $realted_category = get_post_meta( $post->ID, 'releated_category', true );

		// 		  if($realted_category or $value){
		// 			$content .='<h2>'.get_option('label_name_related_post').'</h2>';  
		// 		  }
		//          if($realted_category){

		//                 $arrfieldsCat = explode(',', $realted_category);

		$args = array(
			//'category' => $arrfieldsCat,
			//
			'post_type' => 'procedure',
			'post__in' => $arrfields,
			'posts_per_page' => -1
		);

		$myCatposts = get_posts($args);


		if($myCatposts ){
			$content .='<ul>';	    
			foreach ($myCatposts as $p) :
			$link =	get_post_permalink( $p->ID );
			$content  .= '<li><a href="'.$link.'">';
			$short_headings = get_field('procedure_short_heading_for_single_page_drop_down', $p->ID);
			if($short_headings){
				$content .=  $short_headings;
			}else{	
				$p->post_title;
			}
			$content  .= '</a></li>';
			endforeach; 

			$content .='</ul>';	    
		}

		//          }

		if($value){

			$arrfields = explode(',', $value);

			$args = array(
				'post__in' => $arrfields,
				//'category__not_in'=> $arrfieldsCat,
				'posts_per_page' => -1,

			);

			$myposts = get_posts($args);

			//$content .='<h2>'.get_option('label_name_related_post').'</h2>';	
			if($myposts ){
				$content .='<ul>';	    
				foreach ($myposts as $p) :
				$link =	get_post_permalink( $p->ID );
				$content  .= '<li><a href="'.$link.'">';
				$short_headings = get_field('procedure_short_heading_for_single_page_drop_down', $p->ID);
				if($short_headings){
					$content .=  $short_headings;
				}else{	
					$p->post_title;
				}
				$content  .= '</a></li>';
				endforeach; 
				$content .='</ul>';	    
			}

		}
	}
	return $content;
}
add_shortcode('show_related_posts', 'show_custom_related_posts');