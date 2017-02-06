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
?>

