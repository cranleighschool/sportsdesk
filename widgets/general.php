<?php
/*
**
 * Sports desk upcoming Fixtures widget
 */
class SportsDesk_General_Widget extends WP_Widget {

	/**
	 * Register widget with WordPress.
	 */
	public function __construct() {
		parent::__construct(
	 		'sportsdesk_general_widget', // Base ID
			'Sports Desk General', // Name
			#array( 'description' => __( 'Displays latest results from sportsdesk', 'text_domain' ), ) // Args
			array( 'description' => "Widget designed to be display to the right of sports desk to display links to last week and next week" )
		);
	}

	/**
	 * Front-end display of widget.
	 *
	 * @see WP_Widget::widget()
	 *
	 * @param array $args     Widget arguments.
	 * @param array $instance Saved values from database.
	 */
	public function widget( $args, $instance ) {
		extract( $args );
		$title = apply_filters( 'widget_title', $instance['title'] );

		echo $before_widget;
		if ( ! empty( $title ) )
			echo $before_title . $title . $after_title;
		#print "This week is".week_tax();
	        $weeknumber = what_week();
		$lastweek = $weeknumber-1;
		$nextweek = $weeknumber+1;
		//Don't let lastweek be negative
		if($lastweek < 0){
		   $lastweek = 0;
		}
		//Don't let nextweek be bigger than 15
		if($nextweek > 15){
		   $nextweek = 15;
		}
		$lastweek_name = week_tax($lastweek);
		$nextweek_name = week_tax($nextweek);
		//If not wordpress error
		$lastweek_link = get_term_link($lastweek_name,'week');
		$nextweek_link = get_term_link($nextweek_name,'week');
                print "Current Week: ".what_week()."<br />";
		if(!is_wp_error($lastweek_link)){
			print "<a href='".$lastweek_link."'>".$instance['lastweeklabel']."</a><br />";
		}
		if(!is_wp_error($nextweek_link)){
			print "<a href='".$nextweek_link."'>".$instance['nextweeklabel']."</a>";
		}
	
		echo $after_widget;
	}

	/**
	 * Sanitize widget form values as they are saved.
	 *
	 * @see WP_Widget::update()
	 *
	 * @param array $new_instance Values just sent to be saved.
	 * @param array $old_instance Previously saved values from database.
	 *
	 * @return array Updated safe values to be saved.
	 */
	public function update( $new_instance, $old_instance ) {
		$instance = array();
		$instance['title'] = strip_tags( $new_instance['title'] );
		$instance['nextweeklabel'] = strip_tags( $new_instance['nextweeklabel'] );
		$instance['lastweeklabel'] = strip_tags( $new_instance['lastweeklabel'] );

		return $instance;
	}

	/**
	 * Back-end widget form.
	 *
	 * @see WP_Widget::form()
	 *
	 * @param array $instance Previously saved values from database.
	 */
	public function form( $instance ) {
		if ( isset( $instance[ 'title' ] ) ) {
			$title = $instance[ 'title' ];
		}
		else {
			$title = 'Sports Desk';
		}
		if ( isset( $instance[ 'nextweeklabel' ] ) ) {
			$nextweek = $instance[ 'nextweeklabel' ];
		}
		else {
			$nextweek = "Next Week's Fixtures";
		}
		if ( isset( $instance[ 'lastweeklabel' ] ) ) {
			$lastweek = $instance[ 'lastweeklabel' ];
		}
		else {
			$lastweek = "Last Week's Results";
		}
		
		?>
		<p>
		<label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php print "Widget Title"; ?></label> 
		<input class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" type="text" value="<?php echo esc_attr( $title ); ?>" />
		</p>
		<p>
		<label for="<?php echo $this->get_field_id( 'nextweeklabel' ); ?>"><?php print "Text for Next Week Link"; ?></label> 
		<input class="widefat" id="<?php echo $this->get_field_id( 'nextweeklabel' ); ?>" name="<?php echo $this->get_field_name( 'nextweeklabel' ); ?>" type="text" value="<?php echo esc_attr( $nextweek ); ?>" />
		</p>
		<p>
		<label for="<?php echo $this->get_field_id( 'lastweeklabel' ); ?>"><?php print "Text for Last Week Link"; ?></label> 
		<input class="widefat" id="<?php echo $this->get_field_id( 'lastweeklabel' ); ?>" name="<?php echo $this->get_field_name( 'lastweeklabel' ); ?>" type="text" value="<?php echo esc_attr( $lastweek ); ?>" />
		</p>

		<?php 
	}
	

} // End of widget class

