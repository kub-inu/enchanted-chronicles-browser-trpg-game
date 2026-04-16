<?php
/**
 * Authentifikačný modul - registrácia, overenie, prihlásenie, odhlásenie
 */
$router->get("/auth/register", 'auth', "AuthController@UserRegistrationPage")->middleware('guest');
//$router->get("/auth/register/verify/{token}", 'auth', "AuthController@registerVerifyPage")->middleware('guest');

//$router->post("/auth/register/verify/{token}", 'auth', "AuthController@createNewUser")->middleware('csrf', 'guest');


$router->post("/auth/login", 'auth', "AuthController@login")->middleware('csrf', 'guest');
$router->post("/auth/logout", 'auth', "AuthController@logout")->middleware('csrf', 'auth');






/**
 * Prešlo refaktorom
 */

$router->get("/auth/verify/{token}", 'auth', "AuthController@verifyEmailAdressPage")->middleware('guest');
$router->post("/auth/register", 'user', "UserController@register")->middleware('csrf', 'guest');

