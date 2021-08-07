#!/usr/bin/php
<?php
ini_set('error_reporting', E_ALL);

$domoticz_ip				= "192.168.x.xxx";	// IP of domoticz / localhost or 127.0.0.1
$domoticz_port				= "7080"; 			// Portnumber of domoticz
$domoticz_2_ip				= "192.168.x.xxx";	// IP of domoticz / 2nd/backup instance (optional)
$domoticz_2_port			= "7080"; 			// Portnumber of domoticz on 2nd/backup instance (optional)
$domoticz_2_idx				= "6";				// IDX on 2nd/backup instance (for ie. Telegram alert)


echo "------------------------------------------------
";
echo "Start log: " . date("d-m-Y H:i:s") . "
";

// Domoticz service status
@exec('systemctl is-active domoticz', $output, $return_var);
if (($return_var == "0")) // 0 is running, 3 is stopped
{	$nstatus = "1";
    $sstatus = "2";
    echo "Domoticz service is active on Pi -> all OK
";
}
else
{
	echo "Domoticz service is inactive on Pi -> restart service
";
	// **** SWITCH ****
	@exec('sudo service domoticz.sh restart'); //restart the service
	// **** SWITCH ****
}

// Domoticz HTTP API service status
ini_set("default_socket_timeout", 1);
if(fsockopen($domoticz_ip,$domoticz_port))
{   echo "Domoticz HTTP API instance is active on Pi -> all OK
";
}
else
{   echo "Domoticz HTTP API instance is inactive on Pi -> second check
";
	sleep(3);
	// Re-check Domoticz HTTP API service status
    if(fsockopen($domoticz_ip,$domoticz_port))
    {   echo "Domoticz HTTP API instance is actually active on Pi -> do nothing
";
    }
    else
	// Restart service of Domoticz
    {   echo "Domoticz HTTP API instance is really not active on Pi -> restart service
";
		// Alert via 2nd/backup Domoticz instance ie. NAS / second Pi (optional)
		$nstatus = "0";
		$sstatus = "0";
		// **** Remove the '//' on next line when using 2nd/backup Domoticz instance
		//file_get_contents("http://$domoticz_2_ip:$domoticz_2_port/json.htm?type=command&param=udevice&idx=$domoticz_2_idx&svalue=$sstatus&nvalue=$nstatus");

		// **** SWITCH ****
		@exec('sudo service domoticz.sh restart'); //restart the service
		// **** SWITCH ****
    }
}

?>
