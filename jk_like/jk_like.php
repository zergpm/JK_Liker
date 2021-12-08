<?php
/*
* Plugin Name: JK Like
* Description: Add Like to post
* Version: 0.0.1
* Author: Joker
* License: GPL2
*/


/*
* Activation
* Deactivation
*/
register_activation_hook(__FILE__, 'jk_like_activation');
register_deactivation_hook(__FILE__, 'jk_like_deactivation');


/**
* Create Db for plugin.
* @param no-param
*/
function jk_like_activation(){
	global $wpdb;

	$table_name = $wpdb->prefix . 'jk_like';
	$charset_collate = $wpdb->get_charset_collate();

    $sql = "CREATE TABLE IF NOT EXISTS `$table_name` (
      `id` bigint(20) NOT NULL AUTO_INCREMENT,
      `user_id` bigint(20) UNSIGNED NOT NULL,
      `post_id` bigint(20) UNSIGNED NOT NULL,
      PRIMARY KEY id (id)
    ) $charset_collate;";
 
    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    dbDelta($sql);
}


/**
* Delete Db for plugin.
* @param no-param
*/
function jk_like_deactivation(){
	global $wpdb;
	$table_name = $wpdb->prefix . 'jk_like';
	
	$sql = "DROP TABLE IF EXISTS `$table_name`";
	
	require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
	dbDelta( $sql );
}

/*
* Load JS and Styles
*/

function jk_like_load_scripts() {
  wp_enqueue_script('script', plugins_url( 'assets/js/jk_like.js' , __FILE__ ), array('jquery'),'',true);
  wp_enqueue_style('style', plugins_url('assets/css/jk_like.css',__FILE__ ));
}

add_action('wp_enqueue_scripts', 'jk_like_load_scripts');


/*
* Add Ajax variable to the front jk_like_
*/ 
function jk_like_ajaxurl() {
  echo '<script type="text/javascript">var ajaxurl = "' . admin_url('admin-ajax.php') . '";</script>';
}

add_action('wp_head', 'jk_like_ajaxurl');
add_action( 'wp_ajax_like_action', 'jk_like_action' );
add_action( 'wp_ajax_nopriv_like_action', 'jk_like_nopriv_action' );


/**
 * Get like count
 * @param $post_id int
 * @return int
 */
function jk_like_count($post_id) {
  global $wpdb;
  $table_name = $wpdb->prefix . 'jk_like';

  $sql = "SELECT `id` FROM `$table_name` WHERE `post_id` =".(int)$post_id;
  $like_count = count($wpdb->get_results($sql));

  if (! $like_count > 0) {
    $like_count = 0;
  }

  return $like_count;
}

/**
 * Get like count
 * @param $user_id int, $post_id int, 
 * @return int
 */
function jk_like_check($user_id, $post_id) {
  global $wpdb;
  $table_name = $wpdb->prefix . 'jk_like';

  $sql = "SELECT `id` FROM `$table_name` WHERE `user_id` = $user_id AND `post_id` = $post_id" ;
  $like_count = count($wpdb->get_results($sql));

  if (! $like_count > 0) {
    $like_count = 0;
  }

  return $like_count;
}


/**
 * Add like to current post
 * @param $user_id int, $post_id int, 
 */
function jk_like_add($user_id, $post_id) {
  global $wpdb;
 
  $wpdb->insert( 
    $wpdb->prefix . 'jk_like',
    array(
      'user_id' => (int)$user_id,
      'post_id' => (int)$post_id
    )
  );
}


/**
 * Delete like from current post
 * @param $user_id int, $post_id int, 
 */
function jk_like_rem($user_id, $post_id) {
  global $wpdb;
 
  $wpdb->delete( 
    $wpdb->prefix . 'jk_like',
    array(
      'user_id' => (int)$user_id,
      'post_id' => (int)$post_id
    )
  );
}


/**
 * Get like status from current post
 * @param $user_id int, $post_id int, $status bool
 * * @return array
 */
function jk_like_status($user_id, $post_id, $status) {
  if ($status == true) {
    jk_like_rem($user_id,$post_id);
  } else {
    jk_like_add($user_id,$post_id);
  }

  if(jk_like_check($user_id,$post_id) == 0) {
    $btn_text = 'like';
  } else {
    $btn_text = 'liked';
  }

  $data = array(
    'status' => $status,
    'count' => jk_like_count($post_id),
    'btn_text' => $btn_text
  );

  return json_encode($data);
}


/**
 * Add top 5 liked posts
 * * @return array
 */
function jk_like_get_top_posts() {
  global $wpdb;
  $table_name = $wpdb->prefix . 'jk_like';

  $sql = "SELECT `post_id`, COUNT(*) FROM `$table_name` GROUP BY `post_id` ORDER BY COUNT(*) DESC LIMIT 5";
  $top_posts = $wpdb->get_results($sql);

  return $top_posts;
}

/**
 * Enqueue block for Gutenberg
 * @param $tags, 
 * * @return string
 */
function jk_likeEditorBlock() {
  wp_enqueue_script(
    'jk_like_editor',
    plugins_url( 'assets/js/jk_like_editor_block.js' , __FILE__ ),
    array('wp-blocks','wp-editor'),
    true
  );
}

add_action('enqueue_block_editor_assets', 'jk_likeEditorBlock');


/**
 * Add like button to the front
 * @param $tags, 
 * * @return string
 */
function jk_like_add_btn($tags){

  if(jk_like_check(get_current_user_id(),get_the_ID()) == 0) {
    $btn_text = 'like';
  } else {
    $btn_text = 'liked';
  }

  $like_btn = ' <p class="dashicons-before dashicons-smiley jk-like-post-btn" data-post-id="'
    . get_the_ID()
    . '" data-user-id="' 
    . get_current_user_id()
    . '"><span class="jk-like-post-text">'
    . $btn_text
    . '</span> <span class="jk-like-post-count">'
    . jk_like_count(get_the_ID())
    . '</span></p>';

  return $like_btn;
}

add_filter('the_tags','jk_like_add_btn');


/**
 * Return data for no autirized user
 * * @return boolean
 */
function jk_like_nopriv_action(){
  return false;
}

/**
 * Return data for autirized user
 * * @return array
 */
function jk_like_action(){
    $like_count = jk_like_check($_POST['user_id'],$_POST['post_id']);
    $result = jk_like_status($_POST['user_id'], $_POST['post_id'], $like_count);

    echo $result;
	die;
}