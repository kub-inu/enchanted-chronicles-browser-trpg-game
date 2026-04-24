<?php

$router->get('/account', 'account', "AccountController@account")->middleware('auth');


// $router->get('/account', 'auth', "AuthController@resetPasswordPage")->middleware('auth');
// $router->get('/account/profile', 'auth', "AuthController@resetPasswordPage")->middleware('auth');
// $router->get('/account/password', 'auth', "AuthController@resetPasswordPage")->middleware('auth');
// $router->get('/account/security', 'auth', "AuthController@resetPasswordPage")->middleware('auth');
// $router->get('/account/security/sessions', 'auth', "AuthController@resetPasswordPage")->middleware('auth');
// $router->get('/account/security/activity', 'auth', "AuthController@resetPasswordPage")->middleware('auth');
// $router->get('/account/preferences', 'auth', "AuthController@resetPasswordPage")->middleware('auth');
// $router->get('/account/notifications', 'auth', "AuthController@resetPasswordPage")->middleware('auth');
// $router->get('/account/privacy', 'auth', "AuthController@resetPasswordPage")->middleware('auth');
// $router->get('/account/characters', 'auth', "AuthController@resetPasswordPage")->middleware('auth');
// $router->get('/account/guild', 'auth', "AuthController@resetPasswordPage")->middleware('auth');
// $router->get('/account/social', 'auth', "AuthController@resetPasswordPage")->middleware('auth');
// $router->get('/account/friends', 'auth', "AuthController@resetPasswordPage")->middleware('auth');
// $router->get('/account/blocked-users', 'auth', "AuthController@resetPasswordPage")->middleware('auth');
// $router->get('/account/data', 'auth', "AuthController@resetPasswordPage")->middleware('auth');
// $router->get('/account/consents', 'auth', "AuthController@resetPasswordPage")->middleware('auth');
// $router->get('/account/delete', 'auth', "AuthController@resetPasswordPage")->middleware('auth');
// $router->get('/u/{slug}', 'auth', "AuthController@resetPasswordPage")->middleware('auth');