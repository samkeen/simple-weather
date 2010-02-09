<?php
/* 
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */
class Tweeter {
    public function tweet_text() {
        $weather = $this->weather;
        $today = array_shift($weather);
        $tweet_txt = "Today({$today['day']}) lo:{$today['temp_low']} hi:{$today['temp_high']} {$today['weather_txt']}";
        foreach ($weather as $weather_day) {
             $tweet_txt .= "\n({$weather_day['day']}) lo:{$weather_day['temp_low']} hi:{$weather_day['temp_high']} {$weather_day['weather_txt']}";
        }
        $tweet_txt .= "\nsrc:Yahoo Weather";
        return $tweet_txt;
    }
}
?>
