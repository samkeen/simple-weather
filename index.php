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
        $here = dirname(__FILE__);
        set_include_path(ini_get('include_path').":${here}/vendor/arc90-service-twitter/lib");



        $weather = new WeatherApi();
        // ({start date}, {num days to recover})
        $weather->get_24h_periods('97214',strtotime('Tomorrow'),2);
        require_once 'Arc90/Service/Twitter.php';

        $username = 'YOUR TWITTER HANDLE';
        $password = 'YOUR TWITTER PW';

        $twitter  = new Arc90_Service_Twitter($username, $password);
        $timeline = $twitter->test('json');
        $timeline =  $twitter->updateStatus($weather->description(),'json');

        echo($timeline);
        ?>
    </body>
</html>
