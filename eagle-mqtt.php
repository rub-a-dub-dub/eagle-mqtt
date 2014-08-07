<?php

	require("phpMQTT.php");

	/*
	 * Configuration variables. Edit this section.
	 */

	$mqtthost = "localhost";
	$mqttport = 8883;
	$mqttuser = "test";
	$mqttpwd = "test";
	$mqttclient = "eaglepush";
	$mqtttopic = "sensors/magnus/power";
	$mqttqos = 0;

	/*
	 * Application code below.
	 */

	function sendToBroker($saveValue) {
		global $mqtthost, $mqttport, $mqttuser, $mqttpwd, $mqttclient, $mqtttopic, $mqttqos;
		$mqtt = new phpMQTT($mqtthost, $mqttport, $mqttclient);
		if ($mqtt->connect(true, NULL, $mqttuser, $mqttpwd)) {
			$mqtt->publish($mqtttopic, $saveValue, $mqttqos);
			$mqtt->close();
			return true;
		}
		return false;
	}

	function translateXML() {
		$rawData = file_get_contents("php://input");
		if (strlen($rawData) > 0) {
			$xmlData = simplexml_load_string($rawData);
			if ($xmlData !== false) {
				// Only process InstantaneousDemand tags
				$idVal = $xmlData->InstantaneousDemand;
				if (!empty($idVal)) {
					$dem = hexdec($idVal->Demand);
					$mul = hexdec($idVal->Multiplier);
					$div = hexdec($idVal->Divisor);
	
					$mul = ($mul == 0) ? 1 : $mul;
					$div = ($div == 0) ? 1 : $div;
	
					$powerKW = $dem*$mul/$div;
					return $powerKW*1000;	// power in Watts
				}
			}
		}
		return false;
	}

	if ($saveValue = translateXML()) {
		// Able to correctly parse the XML fragment sent
		if (sendToBroker($saveValue))
			header("HTTP/1.1 200 OK");
		else
			header("HTTP/1.1 503 Service Unavailable");
	} else
		header("HTTP/1.1 400 Bad Request");

?>
