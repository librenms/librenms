<?php

require_once 'common.php';

$headers = array('First Name', 'Last Name', 'City', 'State');
$data = array(
	array('Maryam',   'Elliott',    'Elizabeth City',   'SD'),
	array('Jerry',    'Washington', 'Bessemer',         'ME'),
	array('Allegra',  'Hopkins',    'Altoona',          'ME'),
	array('Audrey',   'Oneil',      'Dalton',           'SK'),
	array('Ruth',     'Mcpherson',  'San Francisco',    'ID'),
	array('Odessa',   'Tate',       'Chattanooga',      'FL'),
	array('Violet',   'Nielsen',    'Valdosta',         'AB'),
	array('Summer',   'Rollins',    'Revere',           'SK'),
	array('Mufutau',  'Bowers',     'Scottsbluff',      'WI'),
	array('Grace',    'Rosario',    'Garden Grove',     'KY'),
	array('Amanda',   'Berry',      'La Habra',         'AZ'),
	array('Cassady',  'York',       'Fulton',           'BC'),
	array('Heather',  'Terrell',    'Statesboro',       'SC'),
	array('Dominic',  'Jimenez',    'West Valley City', 'ME'),
	array('Rhonda',   'Potter',     'Racine',           'BC'),
	array('Nathan',   'Velazquez',  'Cedarburg',        'BC'),
	array('Richard',  'Fletcher',   'Corpus Christi',   'BC'),
	array('Cheyenne', 'Rios',       'Broken Arrow',     'VA'),
	array('Velma',    'Clemons',    'Helena',           'IL'),
	array('Samuel',   'Berry',      'Lawrenceville',    'NU'),
	array('Marcia',   'Swanson',    'Fontana',          'QC'),
	array('Zachary',  'Silva',      'Port Washington',  'MB'),
	array('Hilary',   'Chambers',   'Suffolk',          'HI'),
	array('Idola',    'Carroll',    'West Sacramento',  'QC'),
	array('Kirestin', 'Stephens',   'Fitchburg',        'AB'),
);

$table = new \cli\Table();
$table->setHeaders($headers);
$table->setRows($data);
$table->display();
