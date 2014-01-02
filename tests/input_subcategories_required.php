<?php

/*
	Input to be used for tests requiring Verticals, Categories, and Subcategories.
	
	This file contains the subcategories that are "Interesting" in some way --
	i.e. known to provide an uncommon condition that should be tested for.
	
	These should be tested every time.
*/

$subcategories = array (
	// As of 2014-01-02, this category contained a single product which will be
	// rejected by our validation methods due to a missing price field.
	0 => array (
		0 => 'Women\'s',
		1 => 'Women\'s Swim Shop',
		2 => 'Women\'s Hats & Visors',
	),
  	
  	// As of 2014-01-02, the following category had at least one product missing an image URL.
  	1 => array (
		0 => 'Lawn & Garden',
		1 => 'Watering, Hoses & Sprinklers',
		2 => 'Sprinklers',
	),
  
);