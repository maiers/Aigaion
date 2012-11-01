<?php

class SearchResultHelper {

	var $search;
	var $maxOutput;
	var $limitEach;

	function __construct(Array &$searchresults, $limit = 5, $limitEach = true) {
		$this->search = $searchresults;
		$this->maxOutput = $limit;
		$this->limitEach = $limitEach;
	}
	 
	// init output counter
	var $currentOutput = 0;
	 
	// init result array
	var $result = array();

	function filterSearch($filterKey, $title, $labelField, $valueField, $id, $url) {
		// reset current output if limit is applied for each key separetly
		if ($this->limitEach) $this->currentOutput = 0;
		// filter if key exists in search results
		if (array_key_exists($filterKey, $this->search)) {
			$newType = true;
			foreach ($this->search[$filterKey] as $key => $value) {
				if ($this->currentOutput++ >= $this->maxOutput) break;
				$this->result[] = array(
	  	  			  			  		'label' => $value->$labelField, 
	  	  			  			  		'value' => '"' . $value->$valueField .'"',
	  	  			  			  		'newType' => $newType,
	  	  			  			  		'type' => $title,
	  	  			  			  		'url' => site_url($url . $value->$id)
				);
				$newType = false;
			}
		}
	}

	function getResult() {
		return $this->result;
	}
	
	/**
	 * Remove all duplicate results from the result array
	 */
	function clean() {
		
		for ($i = 0; $i < count($this->result); $i++) {
			for ($j = $i+1; $j < count($this->result); $j++) {
				if ($this->result[$i]['type'] === $this->result[$j]['type'] &&
						$this->result[$i]['url'] === $this->result[$j]['url']) {
						array_splice($this->result, $j, 1);					
				}
			}
		}
		
	}

}

?>