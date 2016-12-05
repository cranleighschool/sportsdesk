<?php
/*
**
 * Sports desk results widget
 */
class SportsDesk_Locations_Widget extends WP_Widget {

	/**
	 * Register widget with WordPress.
	 */
	public function __construct() {
		parent::__construct(
	 		'sportsdesk_awaylocations_widget', // Base ID
			'Sports Desk Away Fixture Locations', // Name
			#array( 'description' => __( 'Displays latest results from sportsdesk', 'text_domain' ), ) // Args
			array( 'description' => "Displays the away fixture locations dropdown widget" )
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
		
		//Away Location Dropdown
                $select_loc = wp_dropdown_pages("child_of=".$instance['parentid']."&echo=0");

                $select_loc = preg_replace_callback("#<option[^>]*>[^<]*</option>#", array($this,"replace_pageid_for_slug"), $select_loc);

                echo $select_loc;

		
	?>

	<script type="text/javascript"><!--
    		var oppdropdown = document.getElementById("page_id");
    		function onOppChange() {
			if ( oppdropdown.options[oppdropdown.selectedIndex].value != -1 ) {
				location.href = oppdropdown.options[oppdropdown.selectedIndex].value;
			}
    		}
    		oppdropdown.onchange = onOppChange;
	--></script>

	<?php
		
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
		$instance['parentid'] = strip_tags( $new_instance['parentid'] );
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
			$title = 'Away Fixture Locations';
		}
		//if ( isset( $instance[ 'teamname' ] ) ) {
	//		$teamname = $instance[ 'teamname' ];
	//	}
	//	else {
	//		$teamname = 'All';
	//	}
		if ( isset( $instance[ 'parentid' ] ) ) {
			$parentid = $instance[ 'parentid' ];
		}
		else {
			$parentid = '2829';
		}	
		?>
		<p>
		<label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php print "Widget Title"; ?></label> 
		<input class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" type="text" value="<?php echo esc_attr( $title ); ?>" />
		</p>	
		<p>
		<label for="<?php echo $this->get_field_id( 'parentid' ); ?>"><?php print "Parent Page ID"; ?></label> 
		<input class="widefat" id="<?php echo $this->get_field_id( 'parentid' ); ?>" name="<?php echo $this->get_field_name( 'parentid' ); ?>" type="text" value="<?php echo esc_attr( $parentid ); ?>" />
		</p>	


		<?php 
	}

	function replace_pageid_for_slug($option){
		$pages = get_pages("child_of=".$instance['parentid']);
		//print_r($pages);
		preg_match('/value="(\d*)"/', $option[0], $matches);

		$id = $matches[1];
		$slug = "";
		foreach($pages as $page){
			if($page->ID == $id){
				$slug = get_permalink($page->ID);
			}
		}
		//$slug = "123";
		return preg_replace("/value=\"(\d*)\"/", "value=\"$slug\"", $option[0]);
	}	
	
} // class Foo_Widget
?>
