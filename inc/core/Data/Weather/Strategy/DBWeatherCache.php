<?php
/**
 * This file contains class::WeatherOpenweathermap
 * @package Runalyze\Data\Weather
 */

namespace Runalyze\Data\Weather\Strategy;
use Runalyze\Model\WeatherCache;
use Runalyze\Data\Weather\Temperature;
use Runalyze\Data\Weather\Humidity;
use Runalyze\Data\Weather\Pressure;
use Runalyze\Data\Weather\WindSpeed;
use Runalyze\Data\Weather\WindDegree;
use Runalyze\Data\Weather\Condition;
use Runalyze\Data\Weather\Forecast;
use Runalyze\Data\Weather\Location;

/**
 * Forecast-strategy for using local database cache
 * 
 * 
 *
 * @author Hannes Christiansen
 * @author Michael Pohl
 * @package Runalyze\Data\Weather\Strategy
 */
class DBWeatherCache implements ForecastStrategyInterface {

	/**
	 * PDO
	 * @var PDO 
	 */
	protected $Pdo = null;
	
	/**
	 * WeatherCache
	 * @var \Runalyze\Model\WeatherCache\Entity $WeatherCache
	 */
	protected $WeatherCache = null;
	
	/**
	 * Location
	 * @var \Runalyze\Data\Weather\Location $Location
	 */
	protected $Location = null;
	
	/**
	 * @see \Runalyze\Data\Weather\Sources
	 * @return int
	 */
	public function sourceId()
	{
		return $this->WeatherCache->weatherSource();
	}

	/**
	 * Load conditions
	 * @param \Runalyze\Data\Weather\Location $Location
	 */
	public function loadForecast(Location $Location) {
	    $this->Pdo  = \DB::getInstance();
	    $this->Location = $Location;
	    $data = $this->Pdo->prepare('SELECT * FROM '.PREFIX.'weathercache WHERE `geohash`=:geohash AND `time` BETWEEN :starttime AND :endtime ORDER BY TIME DESC LIMIT 1');
	    if($this->Location->hasPosition()) {
		$bindValues = array(
				'geohash' => substr($this->Location->geohash(), 0, WeatherCache\Entity::GEOHASH_PRECISION),
				'starttime' => $this->Location->time() - Forecast::TIME_PRECISION,
				'endtime' => $this->Location->time() + Forecast::TIME_PRECISION
		);
		$data->execute($bindValues);
		
	    }
	    if(is_array($data->fetch())) {
		$WeatherCache = $data->fetch();
	    } else {
		$WeatherCache = null;
	    }
	    print_r($WeatherCache);
	    $this->WeatherCache = new WeatherCache\Entity($WeatherCache);
	}
	
	/**
	 * Condition
	 * @return \Runalyze\Data\Weather\Condition
	 */
	public function condition() {
		return new Condition($this->WeatherCache->weatherid());
	}

	/**
	 * Temperature
	 * @return \Runalyze\Data\Weather\Temperature
	 */
	public function temperature() {
		return new Temperature($this->WeatherCache->temperature());
	}
	
	/**
	 * WindSpeed
	 * @return \Runalyze\Data\Weather\WindSpeed
	 */
	public function windSpeed() {
		$WindSpeed = new WindSpeed();
		$WindSpeed->setMeterPerSecond($this->WeatherCache->temperature());
		return $WindSpeed;
	}
	
	/**
	 * WindDegree
	 * @return \Runalyze\Data\Weather\WindDegree
	 */
	public function windDegree() {
		return new WindDegree($this->WeatherCache->windDegree());
	}
	
	/**
	 * Humidity
	 * @return \Runalyze\Data\Weather\Humidity
	 */
	public function humidity() {
		return new Humidity($this->WeatherCache->humidity());
	}
	
	/**
	 * Pressure
	 * @return \Runalyze\Data\Weather\Pressure
	 */
	public function pressure() {
		return new Pressure($this->WeatherCache->pressure());
	}
	
	/**
	 * Is it possible to receive weather data?
	 * @return boolean
	 */
	public function isPossible() {
	    return true;
	}
	
	/**
	 * Should this data be cached?
	 * @return boolean
	 */
	public function isCachable() {
	    return false;
	}
	
	/**
	 * Location object
	 * @return \Runalyze\Data\Weather\Location
	 */
	public function location() {
	    return $this->Location;
	}
	
}