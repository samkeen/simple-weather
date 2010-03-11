<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<?php
require "./lib/WeatherApi.php";
require "./lib/Tweeter.php";
?>
<html>
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
        <title></title>
    </head>
    <body>
        <?php

//          $t = new Tweeter('__test__test__', 'holycrap!');
//          $t->tweet('testing 001');

//
//
        $weather = new WeatherApi();
        // ({start date}, {num days to recover})
        $weather->get_24h_periods('97214',strtotime('2010-02-23'),2);
        var_dump($weather->description());
        ?>
    </body>
</html>