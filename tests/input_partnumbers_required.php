<?php

/*
	Input to be used for tests requiring Part Numbers.
	
	This file contains part numbers that are "Interesting" in some way --
	i.e. known to provide an uncommon condition that should be tested for.
	
*/


$part_numbers = array(

	// As of 2014-01-03, this product was listed as "VARIATION" while having no actual variants in the API response.
	array('016VA71287912P'),
	
	// As of 2014-01-03, this was an example of a product with a "range" of prices, e.g. "From $24.00 To $26.00"
	array('077VA56282412P'),
	
	// As of 2014-01-03, this was a hardline product with no cat entry id.
	array('SPM10883623415'),
	
	// As of 2014-01-06, this was a "collection" product.
	// Collections are not supported by the plugin at this time. Product should register as invalid.
	array('024CO55922312B'),
	
	// As of 2014-01-10, these products provide examples of various softlines combinations:
	array('076VA55548812P'), // Shoe available in Medium and Wide widths
	array('076VA21776701P'), // Shoe available in Medium and Wide widths
	array('076SA005000P'), // Shoe available in Medium and Wide widths
	array('007VA58000212P'), // Sweatpants (soft line) - multiple sizes, 1 color
	array('007VA65853512P'), // Athletic shirt (soft line) - multiple sizes, multiple colors

);