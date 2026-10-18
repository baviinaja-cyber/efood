<?php
/**
 * Frontend Head Partial - EFOOD
 */
$pageTitle = $pageTitle ?? 'EFOOD - Super App Pesan Makanan';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title><?= \App\Helpers\Security::escape($pageTitle) ?></title>
    
    <!-- Meta & SEO -->
    <meta name="description" content="Aplikasi pesan makanan online super app cepat, hemat, dan terpercaya. Pilihan aneka kuliner terbaik dengan pengantaran cepat.">
    <meta name="theme-color" content="#FF5722">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="default">
    <meta name="apple-mobile-web-app-title" content="EFOOD">
    <link rel="manifest" href="<?= \App\Helpers\View::url('manifest.webmanifest') ?>">
    <link rel="icon" type="image/svg+xml" href="<?= \App\Helpers\View::url('icons/icon.svg') ?>">

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    <!-- Bootstrap 5 & FontAwesome 6 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" rel="stylesheet">

    <!-- Leaflet CSS untuk Peta -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />

    <!-- EFOOD App CSS -->
    <link rel="stylesheet" href="<?= \App\Helpers\View::url('assets/css/app.css') ?>">
</head>
<body>
<div class="efood-container">
