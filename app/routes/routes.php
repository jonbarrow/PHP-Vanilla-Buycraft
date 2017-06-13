<?php

//////////////////////////////////////////
///         NORMAL SITE ROUTES         ///
//////////////////////////////////////////

$app->get("/", "home:index");
$app->get('/pay', "home:pay");
$app->post('/checkout', "home:checkout");
$app->post('/ver', "home:ver");