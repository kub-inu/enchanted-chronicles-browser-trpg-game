<?php

$router->get('/', 'web', 'PageController@index');  // Vráti domovskú stránku aplikácie

//$router->get('/dashboard', 'PageController@dashboard')->middleware('auth');
//$router->get('/admin/dashboard', 'PageController@adminDashobard')->middleware('permission:admin.access');


