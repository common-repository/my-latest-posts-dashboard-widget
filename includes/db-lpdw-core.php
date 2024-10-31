<?php
/*

HOW IT WORKS
------------

A wp_query loop runs in a dashboard widget, outputting latest posts by the logged-in user.

The options for this loop are held in wp_usermeta under `meta_key` 'db_lpdw_options'.
these options are updated using the widget config box, which writes to that location in the db if new values are given.
they are wiped on uninstall.
*/


/* SET UP TRANSLATION */
 
add_action('init', 'db_lpdw_translation');

function db_lpdw_translation() {
	$languagepath = dirname( plugin_basename( __FILE__ ) ) . '/../languages';
	load_plugin_textdomain( 'db-lpdw', false, $languagepath );
}

add_action( 'wp_dashboard_setup', 'db_lpdw_widget_setup' );

/*
db_lpdw_widget_setup()
register the widget on the dashboard.
calls dp_lpdw_widget_display to show it, which in turn calls db_lpdw_widget_config to get the options
*/

function db_lpdw_widget_setup() {	
	$title = __('My latest posts', 'db-lpdw');
	wp_add_dashboard_widget( 'db-lpdw-dashboard-widget', $title, 'db_lpdw_widget_display', 'db_lpdw_widget_config' );
}

/* APPLY CSS STYLES

there must be a more elegant way to do this... wp_enqueue_script() doesn't work well for admin area.

*/

add_action( 'admin_head', 'db_lpdw_style_init');

function db_lpdw_style_init() {
  	echo "<link rel='stylesheet' type='text/css' href='" . WP_PLUGIN_URL . "/my-latest-posts-dashboard-widget/css/db-lpdw-style.css'>";
}


/* DASHBOARD WIDGET */

/*
dp_lpdw_widget_display()
does what it says on the tin
*/

function db_lpdw_widget_display() {
	$user = wp_get_current_user();
	$user_id =  $user->ID;

	//get options
	$db_lpdw_options = db_lpdw_widget_options($user_id);
	//find posts by the current user
	$db_lpdw_query_opts = array(
		'author' => $user_id,
		'showposts' => $db_lpdw_options['showposts'],
		'order' => $db_lpdw_options['sort-order']
		);
	$db_lpdw_query = new WP_Query($db_lpdw_query_opts);
	?><div class="db-lpdw-widget"><?php

	//run loop
	if ( $db_lpdw_query->have_posts() ) {
	 	while ( $db_lpdw_query->have_posts() ) : $db_lpdw_query->the_post();
		?><a href="<?php the_permalink() ?>" title="<?php the_title(); ?>"><?php the_title(); ?></a>
		<?php if ( $db_lpdw_options['show-date'] == 1 ) {
			echo "<abbr>".get_the_time(get_option('date_format'))."</abbr></p>";
			//can't use the_date() multiple times
		} else { echo "</p>"; }
		endwhile;
		?></div><?php
	} else { 
		echo "<p>You haven't written any posts yet.</p> <br />"; 
		echo "<a class=\"button-secondary\" title=\"Write a new post\" href=" . get_bloginfo("url") . "/wp-admin/post-new.php>Write a new post</a>";
?> </div> <?php
	}
}

/*
db_lpdw_widget_options()
compares submitted form data with defaults
updates default options with new values, if there are any
*/

function db_lpdw_widget_options($user_id) {
		
	$db_lpdw_defaults = array(
		'showposts' => 3,
		'show-date' => 0,
		'sort-order' => 'DESC'
		);

	//if no options are being set, empty the $options array - no defaults will be overridden
	if ( (!$db_lpdw_options = get_user_meta( $user_id, 'db_lpdw_options' ) ) ) {
		$db_lpdw_options = array();
	} else {
		$db_lpdw_options = get_user_meta( $user_id, 'db_lpdw_options', true );
	}

	return array_merge( $db_lpdw_defaults, $db_lpdw_options ); 
}

/* db_lpdw_widget_config
provides the configuration panel functionality
gets old options from db_lpdw_widget_options() and overrides changed options with data from $_POST
*/

function db_lpdw_widget_config() {
	
	$user = wp_get_current_user();
	$user_id =  $user->ID;
	$db_lpdw_options = db_lpdw_widget_options($user_id);
	$showpost_poss_values = array( 1 , 2 , 3 , 4 , 5 , 10 );

	//collect user-submitted values
	if ( 'post' == strtolower($_SERVER['REQUEST_METHOD'] ) && $_POST['widget_id'] == 'db-lpdw-dashboard-widget' ) { 
		$clean = array();
		if( in_array( $_POST['showposts'], $showpost_poss_values ) )
			$clean['showposts'] = $_POST['showposts'];
		$clean['show-date'] = ( $_POST['show-date'] == '1' ? 1 : 0 );	
		$clean['sort-order'] = ( $_POST['sort-order'] == '1' ? 'ASC' : 'DESC' );	
		
		//store this array in the user_meta table.
		update_user_meta( $user_id, 'db_lpdw_options', $clean );
	} ?>

	<?php /* display input form for user settings */ ?>

	<label for="showposts"><?php _e('How many items? ', 'db-lpdw'); ?><select id="showposts" name="showposts">
			
	<?php
		foreach($showpost_poss_values as $i) {
		echo "<option value='$i'" . ( $db_lpdw_options['showposts'] == $i ? " selected='selected'" : '' ) . ">$i</option>"; }
	?>
			
	</select>
	</label>
	</p>
 
	<label for="show-date">	<?php _e('Show post dates? ', 'db-lpdw'); ?>
	<input id="show-date" name="show-date" type="checkbox" value="1"
		<?php if ( 1 == $db_lpdw_options['show-date'] ) echo ' checked="checked"'; ?> 
	/>	
	</label>
	</p>

	<label for="sort-order"><?php _e('Show older posts first? ', 'db-lpdw'); ?>
	<input id="sort-order" name="sort-order" type="checkbox" value="1"
		<?php if ( 'ASC' == $db_lpdw_options['sort-order'] ) echo ' checked="checked"'; ?> 
	/>	
	</label>

<?php } ?>
