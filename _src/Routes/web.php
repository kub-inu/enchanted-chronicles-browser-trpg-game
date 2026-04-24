<?php

$router->get('/', 'web', 'PageController@index');  // Vráti domovskú stránku aplikácie

$router->get('/lobby', 'web', 'PageController@lobby')->middleware('auth');

//$router->get('/dashboard', 'PageController@dashboard')->middleware('auth');
//$router->get('/admin/dashboard', 'PageController@adminDashobard')->middleware('permission:admin.access');


