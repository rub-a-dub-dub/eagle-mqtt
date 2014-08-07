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
		$mqtt = new phpMQTT($mqtthost, $mqttport, $mqttclient, $mqttuser, $mqttpwd);
		$value = rand();

		if ($mqtt->connect()) {
			$mqtt->publish($mqtttopic, $value, $mqttqos);
			$mqtt->close();
		}
	}

	function translateXML() {
		$rawData = http_get_request_body();
		$xmlData = simplexml_load_string($rawData);
		if ($xmlData) {
			// Only process InstantaneousDemand tags
			$idVal = $xmlData->InstantaneousDemand;
			if (defined($idVal)) {
				$dem = hexdec($idVal->Demand);
				$mul = hexdec($idVal->Multiplier);
				$div = hexdec($idVal->Divisor);

				$mul = ($mul == 0) ? 1 : $mul;
				$div = ($div == 0) ? 1 : $div;

				$powerKW = $dem*$mul/$div;
				return $powerKW*1000;	// power in Watts
			}
		}
		return false;
	}

	if ($saveValue = translateXML()) {
		// Able to correctly parse the XML fragment sent
		sendToBroker($saveValue);
		// We don't let users know anything about the success or failure
	}

?>