<?php
require_once('modules/Relations/Relations.php');

class RelationsRotho extends Relations {
	
	var $list_fields_name = Array(
	'Relation Name' => 'relation_name',
	'Link To' => 'link_to',
	'Relation State' => 'relation_state',
	'Description' => 'description'
	);
	
	function __construct(){
		parent::__construct();
		
		global $log, $table_prefix;
		
		$this->list_fields = Array(
			'Relation Name'=>Array($table_prefix.'_relations'=>'relation_name'),
			'Link To'=>Array($table_prefix.'_relations'=>'link_to'), 
			'Relation State'=>Array($table_prefix.'_relations'=>'relation_state'), 
			'Description'=>Array($table_prefix.'_crmentity'=>'description')
		);
	}
	
	//danzi.tn@20130719
}
?>