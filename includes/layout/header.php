<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <!-- css -->
    <link rel="stylesheet" href="/sistemachacara/assets/css/style.css">
    <link rel="icon" type="image/png" href="/sistemachacara/assets/images/favicon.png">

    <!-- para cada página definir seu título -->
    <title><?php echo $titulo_pagina; ?></title>

    <!-- icones -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/weather-icons/2.0.10/css/weather-icons.min.css" />

    <?php date_default_timezone_set('America/Sao_Paulo'); ?>
</head>

<!-- para cada página definir uma classe pro body, para customizar o css -->
<body class="<?php echo $body_class ?? ''; ?>">