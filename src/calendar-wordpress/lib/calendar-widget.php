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
            //global $post;
            echo '<div class="widget-text widget wp_widget_plugin_box">';
            $args = array(
                'post_type' => 'calendar_event',
                'posts_per_page' => -1,
                'orderby' => 'meta_value',
                'meta_key' => '_event_stop',
                'order' => 'ASC',
            );
            $WidgetQuery = new WP_Query( $args );
            $today = date( 'Y/m/d 00:00' );
            $WidgetQuery->set('meta_query', array( array(
                'key' => '_event_stop',
                'value' => $today,
                'compare' => '>=',
            )));

            if($WidgetQuery->have_posts()){
                while ( $WidgetQuery->have_posts() )
                {
                    $WidgetQuery->the_post();
                    // display date
                    $dateStart = get_post_meta(get_the_ID(),'_event_start',true);
                    $time = strtotime($dateStart);
                    $months = array('GRU','STY','LUT','MAR','KWI','MAJ','CZE','LIP','SIE','WRZ','PAZ','LIS','GRU');
                    echo '<table class="jc_table"><tbody>';
                    echo '<tr class="jc_header"">';
                    echo '<td class="jc_date" title="'.$dateStart.'">';
                    if(date('N',$time)==6) {        echo '<span class="jc_day jc_saturday">';}
                    else if (date('N',$time)==7){   echo '<span class="jc_day jc_sunday">';}
                    else {                          echo '<span class="jc_day">';}
                    
                    echo date('d',$time).'</span><br/><span class="jc_month">'.$months[date('n',$time)].'</span>';
                    echo '</td><td class="jc_subject">';
                    echo '<a href="'.get_permalink().'">'; the_title(); echo '</a>';
                    echo '</td></tr>';
                    echo '</tbody></table>';
                    
                }
            }

            wp_reset_postdata();

            echo '</div>';
            echo $after_widget;
	}
}

// register widget
add_action('widgets_init', create_function('', 'return register_widget("calendar_widget");'));

