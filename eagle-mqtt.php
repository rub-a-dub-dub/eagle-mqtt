<?php

	require("phpMQTT.php");

	$mqtt = new phpMQTT("localhost", 8883, "web-post-client", "wiklund", "lynnleo");
	$value = rand();

	if ($mqtt->connect()) {
		$mqtt->publish("sensors/magnus/power", $value, 0);
		$mqtt->close();
	}

?>