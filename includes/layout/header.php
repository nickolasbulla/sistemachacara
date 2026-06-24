<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <!-- css -->
    <?php $css_v = @filemtime(__DIR__ . '/../../assets/css/style.css') ?: time(); ?>
    <link rel="stylesheet" href="<?= BASE_URL ?>assets/css/style.css?v=<?= $css_v ?>">
    <link rel="icon" type="image/png" href="<?= BASE_URL ?>assets/images/favicon.png">

    <!-- para cada página definir seu título -->
    <title><?php echo $titulo_pagina; ?></title>

    <!-- icones -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" />
    <?php if (!empty($usar_datepicker)): ?>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/flatpickr/4.6.13/flatpickr.min.css">
    <?php endif; ?>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/weather-icons/2.0.10/css/weather-icons.min.css" />

</head>

<!-- para cada página definir uma classe pro body, para customizar o css -->
<body class="<?php echo $body_class ?? ''; ?>">