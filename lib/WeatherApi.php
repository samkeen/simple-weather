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
                'cache_path' => dirname(dirname(__FILE__)).'/cache',
            ),
            $params

        );
        
    }
    public function next_n_days_forcast($zipcode,$num_days_to_retrieve) {

    }
    /**
     * It calls the API, record the weather and writes it to a cache file.
     * Then for calls of get_24hr_periods, this cache can be checked and used if
     * there is a hit, rather than calling the API direct every time.
     * Note: this method is meant to be called by a batch process (such as cron).
     * So for instance we can make all the API calls at 3am for the next few days
     * weather, then calls at anytime during the day (even after the 6am cutoff
     * point) get the cached response
     *
     * @param string $zip_code
     * @param int $start_date
     * @param int $num_days_to_retrieve
     * @param boolean $force_overwrite
     * @return string Full path to the cache file.
     */
    public function cache_weather($zip_code, $start_date, $num_days_to_retrieve,$force_overwrite=false) {
        $cache_file_name = "{$this->params['cache_path']}/{$zip_code}_".date('Y-m-d',$start_date)."_{$num_days_to_retrieve}.xml";
        if( $force_overwrite || ! file_exists($cache_file_name)) {
            if(Util::file_exists_and_writable($this->params['cache_path'])) {
                $api_uri = $this->build_api_uri($zip_code, date('Y-m-d',$start_date), $num_days_to_retrieve);
                $weather_xml = file_get_contents($api_uri);
                if( ! $weather_xml) {
                    throw new Exception("ERROR getting xml from API");
                }
                Util::write_output_file($cache_file_name, $weather_xml);
            }
        }
        
        return $cache_file_name;
    }
    /**
     * Return n number ($num_days_to_retrieve) of summarized 24h period
     * forcasts.  Starting from (and including) $start_date.
     * @param string $zip_code
     * @param int $start_date
     * @param int $num_days_to_retrieve
     */
    public function get_24h_periods($zip_code, $start_date, $num_days_to_retrieve) {
        $cache_file_name = $this->cache_weather($zip_code, $start_date, $num_days_to_retrieve);
        $this->digest_weather_xml($cache_file_name);
    }
    private function digest_weather_xml($weather_xml_path) {
//        var_dump($weather_xml_path);
        $weather_xml = Util::digest_xml_file($weather_xml_path);
        $paths = $this->weather_data_paths;
        $hi_temps = $weather_xml->xpath($paths['hi_temp']);
        $low_temps = $weather_xml->xpath($paths['low_temp']);
        $summary = $weather_xml->xpath($paths['summary']);
        $warnings = $weather_xml->xpath($paths['alert']);
        $forecast_start_times = $weather_xml->xpath($paths['forecast_start_time']);
        $forecast_end_times = $weather_xml->xpath($paths['forecast_end_time']);
        $precip_probabilities = $weather_xml->xpath($paths['precip_pobability']);
        for($index=0;$index<count($hi_temps);$index++) {
            $date_of_weather = isset($forecast_start_times[$index])?(string)strtotime($forecast_start_times[$index]):null;
            if($date_of_weather) {
                $this->weather[date('Y-m-d',$date_of_weather)] = array(
                    'temp_low'         =>  isset($low_temps[$index])?(string)$low_temps[$index]:null,
                    'temp_high'         => isset($hi_temps[$index])?(string)$hi_temps[$index]:null,
                    'weather_txt'   =>  isset($summary[$index])?self::construct_weather_summary($summary[$index]):null,
                    'start_time' =>  $date_of_weather,
                    'end_time' =>  isset($forecast_end_times[$index])?(string)strtotime($forecast_end_times[$index]):null,
                    'precip_probability_day' =>  isset ($precip_probabilities[0])?(string)array_shift($precip_probabilities):null,
                    'precip_probability_night' => isset ($precip_probabilities[0])?(string)array_shift($precip_probabilities):null
                );
            }
        }
//        var_dump($this->weather);
    }
    
    public function description() {
        $desc = '';
//        print_r($this->weather);
        foreach ($this->weather as $date => $weather) {

            $date_desc = date('D_jS',$weather['start_time']);
            $intensity = !empty ($weather['weather_txt']['intensity'])?"{$weather['weather_txt']['intensity']} ":"";
            $weather_type = isset($weather['weather_txt']['weather-type'])?$weather['weather_txt']['weather-type']:'';
            if(empty($weather_type)) {
                $weather_type = isset ($weather['weather_txt']['api_text'])?$weather['weather_txt']['api_text']:'';
            }
            $desc .= "{$date_desc}:{$intensity}{$weather_type}";
            $desc .= " precip%day:{$weather['precip_probability_day']} precip%eve:{$weather['precip_probability_night']} ";
        }
        return trim($desc);
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