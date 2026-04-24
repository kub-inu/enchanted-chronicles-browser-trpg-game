<?php

//Prihlásenie usera
$router->post("/auth/login", 'auth', "AuthController@login")->middleware('csrf', 'guest');

//Obnova hesla
$router->get('/reset-password/{token?}', 'auth', "AuthController@resetPasswordPage")->middleware('guest');
$router->post('/reset-password', 'auth', "AuthController@sendVerifyTokenForResetPassword")->middleware('csrf', 'guest');
$router->post('/auth/password/reset', 'auth', "AuthController@resetForgottenPassword")->middleware('csrf', 'guest');


// Registrácia nového usera
$router->get("/auth/register", 'user', "UserController@UserRegistrationPage")->middleware('guest');
$router->post("/auth/register", 'user', "UserController@register")->middleware('csrf', 'guest');

//Overenie emailovej adresy usera & obbnova expirovaného tokenu
$router->get("/auth/verify/{token}", 'auth', "AuthController@verifyEmailAdressPage")->middleware('guest');
$router->post("/auth/verify/resend", 'auth', "AuthController@resendEmailVerify")->middleware('csrf', 'guest');

//Odhlásenie usera
$router->post("/auth/logout", 'auth', "AuthController@logout")->middleware('csrf', 'auth');