<div class="wrap">
<h2>Sports Desk Settings </h2>
<strong>Current Week of term is: <?php print what_week(); ?></strong>
    <form method="post" action="options.php">
        <?php settings_fields( 'cran_sportsdesk_group' ); ?>
        <label>Current Term Start Date:</label>
	<input type="text" name="cran_term_start" size="10" value="<?php echo get_option('cran_term_start'); ?>" />
        <br />
 	This should be the Sunday before week one, e.g. if term starts for pupils on Monday the 16th April, this would be Sunday 15th
        <br />
        Format is: yyyy-mm-dd e.g. 2012-04-15
        <br/></br>
        <label>Current Term:</label>
	<select name="cran_term">
		<option <?php echo cs_sportdesk_get_term_select_value(get_option('cran_term'), 1); ?>value="1">Michaelmas</option>
		<option <?php echo cs_sportdesk_get_term_select_value(get_option('cran_term'), 2); ?>value="2">Lent</option>
		<option <?php echo cs_sportdesk_get_term_select_value(get_option('cran_term'), 3); ?>value="3">Summer</option>
	</select>
<!--	<input type="text" name="cran_term" size="10" value="<?php echo get_option('cran_term'); ?>" /> -->
        <br />
        <br />
        <label>Current Academic Year:</label>
	<input type="text" name="cran_year" size="10" value="<?php echo get_option('cran_year'); ?>" />
        <br />
        <br />
        <label>Away Fixture Location Parent Page ID:</label>
	<input type="text" name="cran_awayfixturepage" size="10" value="<?php echo get_option('cran_awayfixturepage'); ?>" />
        <br />
        <br />
        <label>Blackboard server IP address (loadbalancer):</label>
	<input type="text" name="cran_blackboard_ip" size="100" value="<?php echo get_option('cran_blackboard_ip'); ?>" />
        <br />
        <br />
        <label>Blackboard Building Block Folder</label>
	<input type="text" name="cran_blackboard_folder" size="100" value="<?php echo get_option('cran_blackboard_folder'); ?>" />
        <br />
        <br />
	<label>Sportsdesk Slug</label>
	<input type="text" name="cran_sportsdesk_slug" style="width:500px" placeholder="EG: /information/sports-desk" value="<?php echo get_option('cran_sportsdesk_slug'); ?>" /><br />
	<small>After you change this you'll need to re-save/flush your permalink structure</small>
	<br />
	<br />
	<label>Team List Display:</label>
	<select name="cran_mode">
		<option value="Enable"
		<?php if(get_option('cran_mode')=='Enable') print " selected='selected' ";?>>Enable</option>
		<option value="Disable"
		<?php if(get_option('cran_mode')=='Disable') print " selected='selected' ";?>>Disable</option>
	</select>
	<br />
	<br />
	<label>Send Debug Log to Slack</label>
	<select name="cran_send_sportsesk_sync_debug_to_slack">
		<option <?php echo cs_sportdesk_get_term_select_value(get_option('cran_send_sportsesk_sync_debug_to_slack'), 0); ?>value="0">No</option>
		<option <?php echo cs_sportdesk_get_term_select_value(get_option('cran_send_sportsesk_sync_debug_to_slack'), 1); ?>value="1">Yes</option>
	</select>
        <p class="submit">
        <input type="submit" class="button-primary" value="<?php _e('Save Changes') ?>" />
        </p>
    </form>
</div>

<?php
function cs_sportdesk_get_term_select_value($current_value, $option) {
	if ($current_value == $option) {
		return " selected=\"selected\" ";
	}
	return false;
}

