<?php
function cs_get_match_status() {
	switch(get_post_status()):
		case "publish":
			$show_status = "Played";
			$color = "success";
		break;
		case "future":
			$show_status = "Scheduled";
			$color = "warning";
		break;
		case "pending":
			$show_status = "Cancelled";
			$color = "danger";
		break;
		default:
			$show_status = false;
		break;
	endswitch;

	if ($show_status===false)
		return false;

	return "<span class=\"text-".$color."\">".$show_status."</span>";
}
function what_week(){
	     $startoftermdate = new DateTime(get_option('cran_term_start'));
             $startofterm = strtotime(get_option('cran_term_start'));
	     $now = time();
	     //Number of seconds between the start of term and now
	     $timediff = $now - $startofterm;
	     $daydiff = $timediff / (60*60*24);
	     //Double round up
	     $week = ceil(ceil($daydiff)/7);
	     //If less than zero term hasn't started yet so return week 0
	     if($timediff < 0){
		return 0;
	     }
	     //Generally terms arn't longer than 14 weeks so everything after the end of term should be 15
	     elseif($week > 14){
                return 15;
	     }
	     //otherwise term is progressing normally so return the week we're in
	     else {
	        return $week;
             }
    }
    function week_tax($week = NULL){
	    //sanity check week values
	    if($week > 14){
		$week = 15;
	    }
	    if($week == NULL){
		#print "Term is".get_option('cran_year');
	    	return "Week ".what_week()." ".convert_term_to_words(get_option('cran_term'),get_option('cran_year'));
	    } else{
	        return "Week ".$week." ".convert_term_to_words(get_option('cran_term'),get_option('cran_year'));
    	    }
    }
    function week_tax_slug($week = NULL){
		if($week == NULL){
			$week = what_week();
		}
		#print "WEEK:".$week;
	        return "week-".$week."-".term_tax();
    }
    /* Returns a slug style taxonomy name for the term */
    function term_tax(){
	    return str_replace(' ','-',strtolower(convert_term_to_words(get_option('cran_term'),get_option('cran_year'))));
    }
    function convert_term_to_words($term,$year = NULL){
        if($term == 1){
            return 'Michaelmas '.$year;
        } elseif($term == 2) {
            if($year){ $year++; }
            return 'Lent '.$year;
        } elseif($term == 3) {
            if($year){ $year++; }
            return 'Summer '.$year;
        }
   }

	/**
	 * cranleigh_sportsdesk_check_week_term_exists function.
	 *
	 * @author Fred Bradley <frb@cranleigh.org>
	 * @param int $week_number
	 * @param string $term_name
	 * @param int $year
	 * @param string $which
	 * @param int $attempt (default: 1)
	 * @return instance of get_term_by() if successful, or WP_Error if failure.
	 */
	function cranleigh_sportsdesk_check_week_term_exists(int $week_number, string $term_name, int $year, string $which, int $attempt=1) {
		/* Formulate the Slug - because we wrote the sportsdesk that creates these slugs, we now how to formulate the slug string... */
		$slug = "week-".$week_number."-".$term_name."-".$year;

		/* Check to see if the term that we're parsing exists... */
		if (term_exists( $slug, 'week')) {
			// If the term exists let's get it and return it as an object...
			return get_term_by("slug", $slug, 'week');
		} else {
			// If the term doesn't exist we want to try again..

			//But we only want to try again one. If we've landed here twice (See $attempt param) then let's duck outta here
			if ($attempt > 1) {
				return new WP_Error(404, __("Tried twice to find the taxonomy for the ".$which." week, but alas, I failed!", "cranleigh"));
			}

			/*
			 * So we're still alive, so let's continue to try and find the next week...
			 * We use the $which variable to work out whether we're looking a week behind or a week ahead...
			 * and literally run the function again!
			 */
			if ($which=="previous") {
				return cranleigh_sportsdesk_check_week_term_exists(($week_number-1), $term_name, $year, $which, 2);
			} elseif ($which=="next") {
				return cranleigh_sportsdesk_check_week_term_exists(($week_number+1), $term_name, $year, $which, 2);
			}
		}
		// If we got this far then something went wrong, but let's give a graceful error anyway!
		return new WP_Error(500, __( "I shouldn't have got this far... something's wrong.", "cranleigh" ) );
	}

