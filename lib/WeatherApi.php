<?php
/* 
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */
class WeatherApi {

    // ?zipCodeList=97214&format=24+hourly&startDate=2010-02-04&numDays=2
    const API_NOAA_URI_BASE = 'http://www.weather.gov/forecasts/xml/sample_products/browser_interface/ndfdBrowserClientByDay.php?';
    const API_NOAA_URI_PARAMS = 'zipCodeList={zip_code}&format=24+hourly&startDate={start_date}&numDays={num_days}';

    private $weather_data_paths = array(
        'hi_temp' => '/dwml/data/parameters/temperature[@type="maximum"]/value',
        'low_temp' => '/dwml/data/parameters/temperature[@type="minimum"]/value',
        'summary'    =>  '/dwml/data/parameters/weather/weather-conditions',
        'alert'    =>  '/dwml/data/parameters/hazards',
        'forecast_start_time' => '/dwml/data/time-layout[@summarization="24hourly"]/start-valid-time',
        'forecast_end_time' => '/dwml/data/time-layout[@summarization="24hourly"]/end-valid-time',
        'precip_pobability' => '/dwml/data/parameters/probability-of-precipitation/value'
    );

    private $weather = array();
    public function  __construct() {
        
    }
    public function next_n_days_forcast($zipcode,$num_days_to_retrieve) {

    }

    public function get_24h_weather($zip_code, $start_date, $num_days_to_retrieve) {
        $api_uri = $this->build_api_uri($zip_code, $start_date, $num_days_to_retrieve);
        var_dump($api_uri);
        $weather_xml = file_get_contents(dirname(__FILE__).'/test-noaa.xml');
        $weather_xml = file_get_contents($api_uri);
        if( ! $weather_xml) {
            throw new Exception("ERROR getting xml from API");
        }
//        print_r($weather_xml);die;
        $this->pull_paths(new SimpleXMLElement($weather_xml));
    }
    private function pull_paths(SimpleXMLElement $weather_xml) {
        $paths = $this->weather_data_paths;
        $hi_temps = $weather_xml->xpath($paths['hi_temp']);
        $low_temps = $weather_xml->xpath($paths['low_temp']);
        $summary = $weather_xml->xpath($paths['summary']);
        $warnings = $weather_xml->xpath($paths['alert']);
        $forecast_start_times = $weather_xml->xpath($paths['forecast_start_time']);
        $forecast_end_times = $weather_xml->xpath($paths['forecast_end_time']);
        $precip_probabilities = $weather_xml->xpath($paths['precip_pobability']);
        $plus = '';
        for($index=0;$index<count($hi_temps);$index++) {
            $this->weather["today{$plus}"] = array(
                'temp_low'         =>  isset($low_temps[$index])?(string)$low_temps[$index]:null,
                'temp_high'         => isset($hi_temps[$index])?(string)$hi_temps[$index]:null,
                'weather_txt'   =>  isset($summary[$index])?self::construct_weather_summary($summary[$index]):null,
                'start_time' =>  isset($forecast_start_times[$index])?(string)strtotime($forecast_start_times[$index]):null,
                'end_time' =>  isset($forecast_end_times[$index])?(string)strtotime($forecast_end_times[$index]):null,
                'precip_probability_day' =>  isset ($precip_probabilities[0])?(string)array_shift($precip_probabilities):null,
                'precip_probability_night' => isset ($precip_probabilities[0])?(string)array_shift($precip_probabilities):null
            );
            $this->weather[$index]=$this->weather["today{$plus}"];
            $plus = "+".($index+1);
        }
        var_dump($this->weather);die;
    }
    
    private function construct_weather_summary(SimpleXMLElement $summary_element) {
        $summary = array();
        $summary['text']=isset($summary_element['weather-summary'])?(string)$summary_element['weather-summary']:'';
        if($value = $summary_element->value) {
            $summary['coverage'] = isset($value['coverage'])&&$value['coverage']!='none'?(string)$value['coverage']:null;
            $summary['intensity'] = isset($value['intensity'])&&$summary_element['intensity']!='none'?(string)$value['intensity']:null;
            $summary['weather-type'] = isset($value['weather-type'])&&$value['weather-type']!='none'?(string)$value['weather-type']:null;
        }
        return $summary;
    }
    private function build_api_uri($zip_code, $start_date, $num_days_to_retrieve) {
        $parameters = $this->validate_params($zip_code, $start_date, $num_days_to_retrieve);
        //zipCodeList={zip_code}&format=24+hourly&startDate={start_date}&numDays={num_days}
        return self::API_NOAA_URI_BASE
            . str_replace(array(
                '{zip_code}','{start_date}','{num_days}'),
                $parameters,
                self::API_NOAA_URI_PARAMS
            );
        
    }
    private function validate_params($zipcode,$start_date,$num_days_to_retrieve) {
        return array(
            'zipcode'                              => $zipcode,
            'start_date'                        => $start_date,
            'num_days_to_retrieve'    => $num_days_to_retrieve
        );
    }
}