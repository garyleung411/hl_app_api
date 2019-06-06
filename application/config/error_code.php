<?php 

	$result = array(
		"1"		=>	"Success",
		"2"		=> 	"Success, but no data found",
		"3"		=> 	"Unexpected parameters",
		"404"	=> 	"Undefined link",
		"999"	=> 	"Invalid link format",
	);
	// "index": {
       // "E101": "Index Response null",
       // "E102": "Index Response body null",
       // "E103": "Index Response result not 1",
       // "E104": "Index Response data null"
     // },
     // "daily": {
       // "E201": "Daily Response null",
       // "E202": "Daily Response body null",
       // "E203": "Daily Response result not 1",
       // "E204": "Daily Response data null"
     // },
     // "pop": {
       // "E301": "Popnews Response null",
       // "E302": "PopnewsResponse body null",
       // "E303": "Popnews Response result not 1",
       // "E304": "Popnews Response data null"
     // },
     // "life": {
       // "E401": "Life Response null",
       // "E402": "Life Response body null",
       // "E403": "Life Response result not 1",
       // "E404": "Life Response data null"
     // },
     // "column": {
       // "E501": "Column Response null",
       // "E502": "Column Response body null",
       // "E503": "Column Response result not 1",
       // "E504": "Column Response data null"
     // },
     // "detail": {
       // "E501": "Detail Response null",
       // "E502": "Detail Response body null",
       // "E503": "Detail Response result not 1",
       // "E504": "Detail Response data null"
	$data = array(
		"entry"	=>	array(
			"E001"=> "Entry Response null",
			"E002"=> "Entry Response body null",
			"E003"=> "Entry Response result not 1",
			"E004"=> "Entry Response data null",
		),
		"index"	=>	array(
		),
		"daily"	=>	array(
		),
		"pop"	=>	array(
		),
		"life"	=>	array(
		),
		"column"	=>	array(
		),
		"detail"	=>	array(
		),
		
	);
	
	$error_code = array(
		"result"	=>	$result,
		"data"		=>	$data,
	);
