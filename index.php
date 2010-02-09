<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<?php
require "./lib/WeatherApi.php";
?>
<html>
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
        <title></title>
    </head>
    <body>
        <?php
        $weather = new WeatherApi();
        // ({start date}, {num days to recover})
        $weather->get_24h_weather('97214',date('Y-m-d',strtotime('Tomorrow')),3);
        ?>
    </body>
</html>