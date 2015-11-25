<?php

/* Helper class for GeoCoder, contains latitude and longitude */
class GeoCode
{
	public $latitude;
	public $longitude;
	public $approx;

	public function __construct($latitude, $longitude, $approx=false)
	{
		$this->latitude = $latitude;
		$this->longitude = $longitude;
		$this->approx = $approx;
	}
}


class GeoCoder{
	
	private $delay = 0;
	private $bNeedsDelay = false;
 	private $baseUrl = "";
	public $count_calls = 0;
	public $log_level=0;
	public $file_csv = null;
	
	private function initialize()
	{
		

	}

	public function __construct()
	{
        // https://maps.googleapis.com/maps/api/geocode/xml?parameters
		$this->baseUrl = 'http://maps.google.com/maps/api/geocode/xml?sensor=false';
		$this->initialize();
	}
	public function needsDelay()
	{
		return $this->bNeedsDelay;
	}
	public function getDelay()
	{
		return $this->delay;
	}

	public function setDelay($value)
	{
		$this->delay = value;
	}
	
	
	public function getCountCalls()
	{
		return $this->count_calls;
	}

	public function setCountCalls($value)
	{
		$this->count_calls = value;
	}


	/** 
		Search the given location in cache
		if $id is specified, retrieve directly the record
	*/
	private function searchCache($id, $state, $city, $postalCode, $street="", $country="")
	{
		global $adb;
		if($id)
		{
			$query = "SELECT lat,lng,CASE WHEN street='' THEN 1 ELSE 0 END as approx FROM vtiger_map WHERE mapid=$id";
			$result = $adb->query($query);
			if ($result && $adb->num_rows($result)>0)
			{
				$row = $adb->fetchByAssoc($result);
				return new GeoCode($row['lat'],$row['lng'],$row['approx']);
			}
		}
		else
		{
			$query = "SELECT lat,lng,CASE WHEN street='' THEN 1 ELSE 0 END as approx FROM vtiger_map WHERE state='$state' AND city='$city' AND postalCode='$postalCode' ";
			
			if ($street)
				$street= "AND street='$street' ";
			if ($country)
				$country= "AND country='$country' ";
			$result = $adb->query($query.$street.$country);
			if ($result && $adb->num_rows($result)>0)
			{
				$row = $adb->fetchByAssoc($result);
				return new GeoCode($row['lat'],$row['lng'],$row['approx']);
			}
			else //try with a simpler query
			{
				$result = $adb->query($query);
				if ($result && $adb->num_rows($result)>0)
				{
					$row = $adb->fetchByAssoc($result);
					return new GeoCode($row['lat'],$row['lng'],$row['approx']);
				}
			}

		}
		

		return null;
	}


	/**
	Search location on Google Maps GeoCoder and store the result on the database.
	@return a GeoCode() object on success, null otherwise
	*/
	private function retrive($id, $state, $city, $postalCode, $street="", $country="")
	{
		//echo "No hit in cache: contacting Google Maps.<br/>";

		// Initialize delay in geocode speed
		$address = "$street, $postalCode, $city, $state, $country";
		$request_url = $this->baseUrl ."&address=" . urlencode($address);
		$xml = simplexml_load_file($request_url,"SimpleXMLElement",LIBXML_COMPACT);
		if(!$xml)
		{
			//	echo ("Google Maps URL not loading: $request_url");
			return null;
		}

		if ($xml->status == 'OK') {
			// Successful geocode
			$this->bNeedsDelay = false;
			return $this->updateCache(array($id,$state,$city,$postalCode,$street,$country),$xml);
		} else if ($xml->status == 'OVER_QUERY_LIMIT') {
					// sent geocodes too fast
					$this->bNeedsDelay = true;
					echo "Geocode too fast for ".$id." Increasing delay<br/>";
					if($this->delay<3000000) $this->delay += 100000;
		} else {
			if ($xml->status == 'ZERO_RESULTS') {
				//attempt skipping the street
				$request_url = $this->baseUrl . "&address=" . urlencode("$postalCode, $city, $state, $country");
				$xml = simplexml_load_file($request_url);
				if($xml)
				{
					if ($xml->status == 'OK') {
						// Successful geocode
						return $this->updateCache(array($id,$state, $city, $postalCode, "", $country),$xml);
					}
					else
					{
						//attempt skipping the street and the state
						$request_url = $this->baseUrl . "&address=" . urlencode("$postalCode, $city, $country");
						$xml = simplexml_load_file($request_url);
						if($xml)
						{
							if ($xml->status == 'OK') {
								// Successful geocode
								return $this->updateCache(array($id,"", $city, $postalCode, "", $country),$xml);
							}
							else
							{								
								//attempt skipping the street, the state and the postal code
								$request_url = $this->baseUrl . "&address=" . urlencode("$city, $country");
								$xml = simplexml_load_file($request_url);
								if($xml)
								{
									if ($xml->status == 'OK') {
										// Successful geocode
										return $this->updateCache(array($id,"", $city, "", "", $country),$xml);
									}
									else
									{
										//attempt skipping the street, the state and the city
										$request_url = $this->baseUrl . "&address=" . urlencode("$postalCode,$country");
										$xml = simplexml_load_file($request_url);
										if($xml)
										{
											if ($xml->status == 'OK') {
												// Successful geocode
												return $this->updateCache(array($id,"", "", $postalCode, "", $country),$xml);
											}
										}
									}
								}								
							}
						}
					}					
				}
			}
			// failure to geocode
			echo "Address '$address' failed to geocoded. Status: ".$xml->status;
			return null;
		}
	}

	/**
	Search the given location and return the geographic coordination, 
	First serach in the cache (database table), if no result found lookup the location on Google Maps GeoCoder and save the response for future requests.
	@return a GeoCode() object on success, null otherwise
	*/
	public function getGeoCode($id, $state, $city, $postalCode, $street="", $country="")
	{
		if(!$city)
			return null;

		if($country==null || $country=="" ) $country="it";
		
		$ret = null;

		//check cache
		$ret = $this->searchCache($id, $state, $city, $postalCode, $street, $country);
		if($ret)
		{
			$this->bNeedsDelay = false;
			return $ret;
		} else //retrive data from google maps geocoder and save the data into database
		{
			$ret = $this->retrive($id, $state, $city, $postalCode, $street, $country);
		}
		return $ret;
		
	}
	
		/**
	Search the given location and return the geographic coordination, 
	First serach in the cache (database table), if no result found lookup the location on Google Maps GeoCoder and save the response for future requests.
	@return a GeoCode() object on success, null otherwise
	*/
	public function getGeoCodeFromCache($id, $state, $city, $postalCode, $street="", $country="")
	{
		if(!$city)
			return null;

		if($country==null || $country=="" ) $country="it";
		
		$ret = null;

		//check cache
		$ret = $this->searchCache($id, $state, $city, $postalCode, $street, $country);
		if($ret)
		{
			$this->bNeedsDelay = false;
			return $ret;
		} else 
		{
			$ret = null;
		}
		return $ret;
		
	}

	/**
	Save new coordinates to database.
	@return a GeoCode() object on success, null otherwise
	*/
	private function updateCache($location,$xml,$mappingstatus=0)
	{
		global $adb;
		$lat = $xml->result->geometry->location->lat;
		$lng = $xml->result->geometry->location->lng;

		$id = $location[0];
		$state = $adb->sql_escape_string($location[1]);
		$city = $adb->sql_escape_string($location[2]);
		$postalCode = $adb->sql_escape_string($location[3]);
		$street = $adb->sql_escape_string($location[4]);
		$country = $adb->sql_escape_string($location[5]);

		$query = "INSERT INTO vtiger_map (mapid,state,city,postalCode,country,street,lat,lng,mappingdate,mappingstatus) VALUES ($id,'$state','$city','$postalCode','$country','$street','$lat','$lng',GETDATE(),$mappingstatus)";
		$update_result = $adb->query($query);
		if (!$update_result) {
			return null;
		}
		else
		{
			$query = "UPDATE vtiger_accountscf SET cf_871 = '1' WHERE accountid = " . $id ;
			$adb->query($query);
			return new GeoCode($lat,$lng,$street=="");
		}
	}

	/**
	Populate the cache given a set of locations, pay attention to delay of each request
	Input array is a multidimensional array, each entry is a location with this composition:
		[$id, $state, $city, $postalCode, $street, $country, $error, $mapped]]
		0     1       2      3            4              5     6      7
	*/
	public function populateCache($locations)
	{
		// Initialize delay in geocode speed
		if(!$this->delay || $this->delay == 0) $this->delay = 500000;
		$recordInserted = 0;
		// Iterate through the rows, geocoding each address
		echo "Total locations: ".sizeof($locations)."<br/>";
		foreach ($locations as $location) {
			$geocode_pending = true;
			$location[7] = 0;
			while ($geocode_pending) {
				$address = "{$location[4]}, {$location[3]}, {$location[2]}, {$location[5]}, {$location[1]}"; 
				$id = $location[0];
				$request_url = $this->baseUrl . "&address=" . urlencode($address);
				$xml = simplexml_load_file($request_url);
				if(!$xml)
				{
					if($this->log_level>0 && $this->file_csv){$location[6]=1; fputcsv($this->file_csv,  $location); }
					echo "Can't retrieve '$address' whith url=$request_url<br/>";
					break;
				}
				if ($xml->status == 'OK') {
					// Successful geocode
					$geocode_pending = false;
					$update_result = $this->updateCache($location,$xml,1);
					
					if (!$update_result) {
						if($this->log_level>0 && $this->file_csv){$location[6]=2; fputcsv($this->file_csv,  $location); }
						break;
					}
					else 
					{
						$recordInserted = $recordInserted + 1;
						$location[7] = 1;
					}
// 					else 
// 						echo "Added $id => {$location['state']},{$location['city']},{$location['postalCode']},$lat,$lng<br/>";
				} else if ($xml->status == 'OVER_QUERY_LIMIT') {
					// sent geocodes too fast
					echo "Geocode too fast. Increasing delay<br/>";
					if($this->delay<3000000) $this->delay += 100000;
				}
				else if ($xml->status == "ZERO_RESULTS" || $xml->status == "INVALID_REQUEST" ) {
					// echo "attempt only with  state, postalCode, city and country \n";
					$request_url = $this->baseUrl . "&address=" . urlencode("{$location[3]}, {$location[2]}, {$location[5]}, {$location[1]}");
					$xml = simplexml_load_file($request_url);
					if($xml)
					{
						if ($xml->status == 'OK') {
							// Successful geocode
							$update_result = $this->updateCache(array($location[0],$location[1], $location[2], $location[3], "", $location[5]),$xml,2);
							$recordInserted = $recordInserted + 1;
							$location[7] = 1;
						}
						else
						{
							
							
							$request_url = $this->baseUrl . "&address=" . urlencode("{$location[3]}, {$location[2]}, {$location[5]}");
							$xml = simplexml_load_file($request_url);
							if($xml)
							{
								if ($xml->status == 'OK') {
									// Successful geocode
									$update_result = $this->updateCache(array($location[0],"", $location[2], $location[3], "", $location[5]),$xml,3);
									$recordInserted = $recordInserted + 1;
									$location[7] = 1;
								}
								else
								{
									$request_url = $this->baseUrl . "&address=" . urlencode("{$location[3]},{$location[5]}");
									$xml = simplexml_load_file($request_url);
									if($xml)
									{
										if ($xml->status == 'OK') {
											// Successful geocode
											$update_result = $this->updateCache(array($location[0],"", "", $location[3], "", $location[5]),$xml,4);
											$recordInserted = $recordInserted + 1;
											$location[7] = 1;
										}
										else
										{
											echo "Address $id => '$address' failed to geocoded. Received status ".$xml->status." <br/>";
											if($this->log_level>0 && $this->file_csv){$location[6]=5; fputcsv($this->file_csv,  $location); }
										}
									}
								}
							}
						}
						$geocode_pending = false; //skip to next location
					}
					else
					{
						echo "No way, no match for " . $id . " =>" . $request_url . " <br/>";
						if($this->log_level>0 && $this->file_csv){$location[6]=3; fputcsv($this->file_csv,  $location); }
					}
				} else {
					// failure to geocode
					$geocode_pending = false;
					echo "Address $id => '$address' failed to geocoded. Received status ".$xml->status." <br/>";
					if($this->log_level>0 && $this->file_csv){$location[6]=4; fputcsv($this->file_csv,  $location); }
				}
			usleep($this->delay);
			}
			flush();
		}
		return $recordInserted;
		
	}
}





?>
