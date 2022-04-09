<!DOCTYPE html>

<html>
  <head>
    <!-- Basic -->
    <link rel="icon" type="image/png" href="../public/favicon-16x16.png" sizes="16x16">
    <link rel="icon" type="image/png" href="../public/favicon-32x32.png" sizes="32x32">

    <!-- Favicons for all shapes and sizes of browsers  -->
    <link rel="icon" type="image/png" href="../public/favicons/favicon-192x192.png" sizes="192x192">
    <link rel="icon" type="image/png" href="../public/favicons/favicon-160x160.png" sizes="160x160">
    <link rel="icon" type="image/png" href="../public/favicons/favicon-96x96.png" sizes="96x96">
    <link rel="icon" type="image/png" href="../public/favicons/favicon-16x16.png" sizes="16x16">
    <link rel="icon" type="image/png" href="../public/favicons/favicon-32x32.png" sizes="32x32">
    <link rel="apple-touch-icon" sizes="57x57" href="../public/favicons/apple-touch-icon-57x57.png">
    <link rel="apple-touch-icon" sizes="114x114" href="../public/favicons/apple-touch-icon-114x114.png">
    <link rel="apple-touch-icon" sizes="72x72" href="../public/favicons/apple-touch-icon-72x72.png">
    <link rel="apple-touch-icon" sizes="144x144" href="../public/favicons/apple-touch-icon-144x144.png">
    <link rel="apple-touch-icon" sizes="60x60" href="../public/favicons/apple-touch-icon-60x60.png">
    <link rel="apple-touch-icon" sizes="120x120" href="../public/favicons/apple-touch-icon-120x120.png">
    <link rel="apple-touch-icon" sizes="76x76" href="../public/favicons/apple-touch-icon-76x76.png">
    <link rel="apple-touch-icon" sizes="152x152" href="../public/favicons/apple-touch-icon-152x152.png">
    <link rel="apple-touch-icon" sizes="180x180" href="../public/favicons/apple-touch-icon-180x180.png">
    <meta name="apple-mobile-web-app-title" content="Inibyt">
    <meta name="msapplication-TileColor" content="#b91d47">
    <meta name="viewport" content="width=device-width, minimum-scale=1, maximum-scale=1, user-scalable=no">
    <meta name="msapplication-TileImage" content="../public/favicons/mstile-144x144.png">
    <meta name="application-name" content="Inibyt">

    <!-- Title information-->
    <?php if (isset($title)): ?>
      <title>Inibyt: <?= htmlspecialchars($title) ?></title>
    <?php else: ?>
      <title>Inibyt</title>
    <?php endif ?>

    <!-- Style information-->
    <link href="../public/css/bootstrap.min.css" rel="stylesheet"/>
    <link href="../public/css/bootstrap-theme.min.css" rel="stylesheet"/>
    <link href="../public/css/typeahead.css" rel="stylesheet"/>
    <link href="../public/css/styles.css" rel="stylesheet"/>

    <!-- Javascript information-->
    <script src="../public/js/jquery-1.11.1.min.js"></script>
    <script src="../public/js/d3.min.js"></script>
    <script src="../public/js/bootstrap.min.js"></script>
    <script src="../public/js/ajax.js"></script>

    <!-- New Javascript for dynamic search-->
    <script src="../public/js/deletetype.js"></script>
    <script src="../public/js/addtype.js"></script>
    <script src="../public/js/underscore.js"></script>
    <script src="../public/js/typeahead.jquery.js"></script>
    <script src="../public/js/jquery.sendkeys.js"></script>
    <script src="../public/js/bililiteRange.js"></script>

  </head>
  <body>

    <div id = "content">
