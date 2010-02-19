#!/usr/bin/php
<?php
if ($argc != 4 || in_array($argv[1], array('--help', '-help', '-h', '-?'))) {
?>

This script uses the SimpleWeatherAPI to retrieve a given number of 24h period
summarized forecasts for a given zipcode.

  Usage:
  <?php echo $argv[0]; ?> <zipcode> <startdate> <number of days to retrieve>

  <zipcode> This is a US zipcode of 5 digits. ex: 97210
  <startdate> is in the form of Y-m-d. ex: 2010-02-05
  <number of days to retrieve> This is the number of days to retrieve.  It is
      inclusive of the startdate.
      
  With the --help, -help, -h, or -? options, you can get this help.
<?php }
var_dump($argc);
var_dump($argv);
