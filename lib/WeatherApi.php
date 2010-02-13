<?php
/* 
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */
require dirname(__FILE__).'/Util.php';
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

    private $params;

    private $weather = array();
    public function  __construct(array $params=array()) {
        $this->params = array_merge(
            array(
                'cache_path' => realpath(dirname(__FILE__).'/../cache'),
            ),
            $params

        );
        
    }
    public function next_n_days_forcast($zipcode,$num_days_to_retrieve) {

    }
    public function cache_weather($zip_code, $start_date, $num_days_to_retrieve,$force_overwrite=false) {
        $cache_file_name = "{$this->params['cache_path']}/{$zip_code}:".date('Y-m-d',$start_date).":{$num_days_to_retrieve}.xml";
        if( $force_overwrite || ! file_exists($cache_file_name)) {
            $api_uri = $this->build_api_uri($zip_code, date('Y-m-d',$start_date), $num_days_to_retrieve);
            $weather_xml = file_get_contents($api_uri);
            if( ! $weather_xml) {
                throw new Exception("ERROR getting xml from API");
            }
            Util::write_output_file($cache_file_name, $weather_xml);
        }
        return $cache_file_name;
    }
    public function get_24h_weather($zip_code, $start_date, $num_days_to_retrieve) {
        $cache_file_name = $this->cache_weather($zip_code, $start_date, $num_days_to_retrieve);
        $this->pull_paths($cache_file_name);
    }
    private function pull_paths($weather_xml_path) {
        $weather_xml = new SimpleXMLElement($weather_xml_path,null,true);
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
        var_dump($this->weather);
    }
    
    public function description() {
        $desc = '';
        $intensity = !empty ($this->weather[0]['weather_txt']['intensity'])?"{$this->weather[0]['weather_txt']['intensity']} ":"";
        $desc = "Today:{$intensity}{$this->weather[0]['weather_txt']['weather-type']}";
        $desc .= " precip-day%:{$this->weather[0]['precip_probability_day']} precip-eve%:{$this->weather[0]['precip_probability_night']}";
        return $desc;
    }

    
    private function construct_weather_summary(SimpleXMLElement $summary_element) {
        $summary = array();
        $summary['api_text']=isset($summary_element['weather-summary'])?(string)$summary_element['weather-summary']:'';
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