<?php

class calendar_widget extends WP_Widget {

	// constructor
	function calendar_widget() {
            parent::WP_Widget(false, $name = __('Kalendarium wydarzeń', 'calendar_widget') );
	}

	// widget form creation
	//function form($instance) {	
	/* ... */
	//}

	// widget update
	//function update($new_instance, $old_instance) {
		/* ... */
	//}

	// widget display
	function widget($args, $instance) {
                echo $before_widget;
                
                echo '<div class="widget-text widget wp_widget_plugin_box">';
                echo 'TEST WIDGETA (TODO)!';
                echo '</div>';
                
                echo $after_widget;
	}
}

// register widget
add_action('widgets_init', create_function('', 'return register_widget("calendar_widget");'));

