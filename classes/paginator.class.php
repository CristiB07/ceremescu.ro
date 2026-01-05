<?php
//update 8.01.2025
/*
 * PHP Pagination Class
 * @author admin@catchmyfame.com - http://www.catchmyfame.com
 * @version 2.0.0
 * @date October 18, 2011
 * @copyright (c) admin@catchmyfame.com (www.catchmyfame.com)
 * @license CC Attribution-ShareAlike 3.0 Unported (CC BY-SA 3.0) - http://creativecommons.org/licenses/by-sa/3.0/
 */
//#[AllowDynamicProperties]
class Pagination{
	var $items_per_page;
	var $items_total;
	var $current_page;
	var $num_pages;
	var $mid_range;
	var $start_range;
	var $end_range;
	var $range;
	var $low;
	var $limit;
	var $return;
	var $default_ipp;
	var $querystring;
	var $ipp_array;
	
	function Paginator()
	{
		$this->current_page = 1;
		$this->mid_range = 7;
		$this->ipp_array = array(10,25,50,100,'All');
		$this->querystring = ''; // Initialize to empty string
		
		// Validate and sanitize items per page
		if (!empty($_GET['ipp'])) {
			if ($_GET['ipp'] === 'All') {
				$this->items_per_page = 'All';
			} elseif (is_numeric($_GET['ipp']) && $_GET['ipp'] > 0) {
				$this->items_per_page = (int)$_GET['ipp'];
			} else {
				$this->items_per_page = $this->default_ipp;
			}
		} else {
			$this->items_per_page = $this->default_ipp;
		}
	}

	function paginate()
	{
		if(!isset($this->default_ipp)) $this->default_ipp=20;
		if(isset($_GET['ipp']) && $_GET['ipp'] == 'All')
		{
			$this->num_pages = 1;
//			$this->items_per_page = $this->default_ipp;
		}
		else
		{
			if(!is_numeric($this->items_per_page) OR $this->items_per_page <= 0) $this->items_per_page = $this->default_ipp;
			$this->num_pages = ceil($this->items_total/$this->items_per_page);
		}
		$this->current_page = (isset($_GET['page'])) ? (int) $_GET['page'] : 1 ; // must be numeric > 0
		$prev_page = $this->current_page-1;
		$next_page = $this->current_page+1;
		if($_GET)
		{
			$args = explode("&",$_SERVER['QUERY_STRING']);
			foreach($args as $arg)
			{
				$keyval = explode("=",$arg);
				if($keyval[0] != "page" And $keyval[0] != "ipp" AND $keyval[0] != "message") {
					// Sanitize parameter values
					if (isset($keyval[1])) {
						$key = urlencode($keyval[0]);
						$val = urlencode($keyval[1]);
						$this->querystring .= "&" . $key . "=" . $val;
					}
				}
			}
		}

		if($_POST)
		{
			foreach($_POST as $key=>$val)
			{
				if($key != "page" And $key != "ipp") {
					// Sanitize POST values
					$key = urlencode($key);
					$val = urlencode($val);
					$this->querystring .= "&$key=$val";
				}
			}
		}
		if($this->num_pages > 10)
		{
			$base_url = htmlspecialchars(strtok($_SERVER["REQUEST_URI"], '?'), ENT_QUOTES, 'UTF-8');
			$safe_querystring = htmlspecialchars($this->querystring ?? '', ENT_QUOTES, 'UTF-8');
			
			$this->return = ($this->current_page > 1 And $this->items_total >= 10) ? "<a class=\"paginate\" href=\"{$base_url}?page=$prev_page&ipp=$this->items_per_page{$safe_querystring}\">&laquo; Previous</a> ":"<span class=\"disabled\" href=\"#\">&laquo; Previous</span> ";

			$this->start_range = $this->current_page - floor($this->mid_range/2);
			$this->end_range = $this->current_page + floor($this->mid_range/2);

			if($this->start_range <= 0)
			{
				$this->end_range += abs($this->start_range)+1;
				$this->start_range = 1;
			}
			if($this->end_range > $this->num_pages)
			{
				$this->start_range -= $this->end_range-$this->num_pages;
				$this->end_range = $this->num_pages;
			}
			$this->range = range($this->start_range,$this->end_range);

			for($i=1;$i<=$this->num_pages;$i++)
			{
				if($this->range[0] > 2 And $i == $this->range[0]) $this->return .= " ... ";
				// loop through all pages. if first, last, or in range, display
				if($i==1 Or $i==$this->num_pages Or in_array($i,$this->range))
				{
					$this->return .= ($i == $this->current_page) ? "<a class=\"current\" href=\"#\">$i</a> ":"<a class=\"paginate\" href=\"{$base_url}?page=$i&ipp=$this->items_per_page{$safe_querystring}\">$i</a> ";
			}
				if($this->range[$this->mid_range-1] < $this->num_pages-1 And $i == $this->range[$this->mid_range-1]) $this->return .= " ... ";
			}
			$this->return .= (($this->current_page < $this->num_pages And $this->items_total >= 10) And (isset($_GET['page']) && $_GET['page'] != 'All') And $this->current_page > 0) ? "<a class=\"paginate\" href=\"{$base_url}?page=$next_page&ipp=$this->items_per_page{$safe_querystring}\">Next &raquo;</a>\n":"<span class=\"disabled\" href=\"#\">&raquo; Next</span>\n";
			$this->return .= (isset($_GET['page']) && $_GET['page'] == 'All') ? "<a class=\"current\" style=\"margin-left:10px\" href=\"#\">All</a> \n":"<a class=\"paginate\" style=\"margin-left:10px\" href=\"{$base_url}?page=1&ipp=All{$safe_querystring}\">All</a> \n";
		}
		else
		{
			$base_url = htmlspecialchars(strtok($_SERVER["REQUEST_URI"], '?'), ENT_QUOTES, 'UTF-8');
			$safe_querystring = htmlspecialchars($this->querystring ?? '', ENT_QUOTES, 'UTF-8');
			
			for($i=1;$i<=$this->num_pages;$i++)
			{
				$this->return .= ($i == $this->current_page) ? "<a class=\"current\" href=\"#\">$i</a> ":"<a class=\"paginate\" href=\"{$base_url}?page=$i&ipp=$this->items_per_page{$safe_querystring}\">$i</a> ";
			}
			$this->return .= "<a class=\"paginate\" href=\"{$base_url}?page=1&ipp=All{$safe_querystring}\">All</a> \n";
		}
		$this->low = ($this->current_page <= 0) ? 0:($this->current_page-1) * $this->items_per_page;
		if($this->current_page <= 0) $this->items_per_page = 0;
		
		// Secure LIMIT clause - ensure values are integers
		if(isset($_GET['ipp']) && $_GET['ipp'] == 'All') {
			$this->limit = "";
		} else {
			$safe_low = (int)$this->low;
			$safe_ipp = (int)$this->items_per_page;
			$this->limit = " LIMIT $safe_low,$safe_ipp";
		}
	}
	function display_items_per_page()
	{
		$items = '';
		$base_url = htmlspecialchars(strtok($_SERVER["REQUEST_URI"], '?'), ENT_QUOTES, 'UTF-8');
		$safe_querystring = htmlspecialchars($this->querystring ?? '', ENT_QUOTES, 'UTF-8');
		
		if(!isset($_GET[ipp])) $this->items_per_page = $this->default_ipp;
		foreach($this->ipp_array as $ipp_opt) {
			$safe_ipp_opt = htmlspecialchars($ipp_opt, ENT_QUOTES, 'UTF-8');
			$items .= ($ipp_opt == $this->items_per_page) ? "<option selected value=\"$safe_ipp_opt\">$safe_ipp_opt</option>\n":"<option value=\"$safe_ipp_opt\">$safe_ipp_opt</option>\n";
		}
		return "<span class=\"paginate\">Items per page:</span><select class=\"paginate\" onchange=\"window.location='{$base_url}?page=1&ipp='+this[this.selectedIndex].value+'{$safe_querystring}';return false\">$items</select>\n";
	}
	function display_jump_menu()
	{
		$option = '';
		$base_url = htmlspecialchars(strtok($_SERVER["REQUEST_URI"], '?'), ENT_QUOTES, 'UTF-8');
		$safe_querystring = htmlspecialchars($this->querystring ?? '', ENT_QUOTES, 'UTF-8');
		
		for($i=1;$i<=$this->num_pages;$i++)
		{
			$option .= ($i==$this->current_page) ? "<option value=\"$i\" selected>$i</option>\n":"<option value=\"$i\">$i</option>\n";
		}
		return "<span class=\"paginate\">Page:</span><select class=\"paginate\" onchange=\"window.location='{$base_url}?page='+this[this.selectedIndex].value+'&ipp=$this->items_per_page{$safe_querystring}';return false\">$option</select>\n";
	}
	function display_pages()
	{
		return $this->return;
	}
function calculateps() {

	if ((isset( $_GET['page'])) && !empty( $_GET['page'])){
		$Page = (int)$_GET['page'];
		// Ensure page is at least 1
		if ($Page < 1) $Page = 1;
	} else {
		$Page=1;
	}

	$Page_Start = (($this->items_per_page*$Page)-$this->items_per_page);
	return $Page_Start;
}

function calculatepe() {

	if ((isset( $_GET['page'])) && !empty( $_GET['page'])){
		$Page = (int)$_GET['page'];
		// Ensure page is at least 1
		if ($Page < 1) $Page = 1;
	} else {
		$Page=1;
	}

	$Page_End = $this->items_per_page * $Page;
	if($Page_End > $this->items_total)
	{
		$Page_End = $this->items_total;
	}
	return $Page_End;
}
}