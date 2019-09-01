<?php

/**
 * ndetal.com Parser
 *
 * @author deadie
 */

require_once __DIR__ . '/init.php';

?>
<!doctype html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>deadie's ndetal.ru Parser</title>
</head>
<body>

    <div class="info">
        <h3>deadie's ndetal.com Parser</h3>
        <p>
            <button type="button" class="start">Парсинг запчастей Combilift</button>
            &nbsp;
            <!--<button type="button" class="stop">STOP</button>-->
        </p>
        <div class="info-total">Итого товаров: <span class="info-total-value">0</span></div>
    </div>

    <div class="progress">
        <div class="progress-bar">
            <div class="progress-bar-current"></div>
        </div>
        <div class="progress-percent">
            <span class="percent-value">0</span>%
        </div>
    </div>

    <div class="log"></div>

    <link rel="stylesheet" href="styles.css">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.4.1/jquery.min.js"></script>
    <script src="scripts.js"></script>

</body>
</html>
