<?php
	//This is just a very simple demonstration of returning results from a database,
	//using some object-oriented wrappers around HTML and database functionality.
	require_once('include_always.php');
	
	$db = new db;
	$html = new html('Inventory');
	$html->addBodyElement('h1','id="title"','Users table results');
	$html->addBodyElement('p','id="info"','This is just a very simple demonstration of
		returning results from a database,
		using some object-oriented wrappers around HTML and database functionality.');
	
	if($result = $db->query('select * from users')) {
		//retrieve results...
		$html->addResultsTable($result);
		$result->close();
	} else {
		$html->addBodyElement('mark','id="noresult"','Could not execute query.');
	}	
	
?>
