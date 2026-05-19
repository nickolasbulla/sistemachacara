<?php

//                                 LOCAL: '/sistemachacara/'
//                                  HOST: '/'
defined('BASE_URL') || define('BASE_URL', '/sistemachacara/');

date_default_timezone_set('America/Sao_Paulo');

include_once __DIR__ . '/db.php';
include_once __DIR__ . '/../includes/helpers/utils.php';
include_once __DIR__ . '/../includes/helpers/log.php';
include_once __DIR__ . '/../includes/helpers/csrf.php';
