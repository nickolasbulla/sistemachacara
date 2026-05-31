<?php

//                                 LOCAL: '/sistemachacara/'
//                                  HOST: '/'
defined('BASE_URL') || define('BASE_URL', '/sistemachacara/');

header("Content-Security-Policy: default-src 'self'; style-src 'self' 'unsafe-inline' https://cdnjs.cloudflare.com; script-src 'self' 'unsafe-inline' https://code.jquery.com https://cdnjs.cloudflare.com; img-src 'self' https://res.cloudinary.com data:; font-src 'self' https://cdnjs.cloudflare.com; connect-src 'self' https://api.open-meteo.com https://archive-api.open-meteo.com https://brasilapi.com.br;");

date_default_timezone_set('America/Sao_Paulo');

include_once __DIR__ . '/db.php';
include_once __DIR__ . '/../includes/helpers/utils.php';
include_once __DIR__ . '/../includes/helpers/log.php';
include_once __DIR__ . '/../includes/helpers/csrf.php';
