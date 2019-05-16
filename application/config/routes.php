<?php
defined('BASEPATH') OR exit('No direct script access allowed');

$route['default_controller'] = 'publicpanel/home';
$route['404_override'] = '';
$route['translate_uri_dashes'] = FALSE;

$route['categories/(:any)'] = 'publicpanel/categories/index/$1';
$route['detail/(:num)'] = 'publicpanel/detail/index/$1';
$route['penulis/(:num)'] = 'publicpanel/penulis/index/$1';
$route['kontributor/(:num)'] = 'publicpanel/kontributor/index/$1';
$route['keywords/(:any)'] = 'publicpanel/keywords/index/$1';
$route['search/(:any)'] = 'publicpanel/search/index/$1';