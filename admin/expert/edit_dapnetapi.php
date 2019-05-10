<?php

// Make the bare config if we dont have one
if (! file_exists('/etc/dapnetapi.key')) {
    exec('sudo touch /tmp/jsADGHwf9sj294.tmp');
    exec('sudo chown www-data:www-data /tmp/jsADGHwf9sj294.tmp');
    exec('echo "[DAPNETAPI]" > /tmp/jsADGHwf9sj294.tmp');
    exec('echo "USER=" >> /tmp/jsADGHwf9sj294.tmp');
    exec('echo "PASS=" >> /tmp/jsADGHwf9sj294.tmp');
    exec('echo "TRXAREA=" >> /tmp/jsADGHwf9sj294.tmp');
    exec('echo "MY_RIC=" >> /tmp/jsADGHwf9sj294.tmp');
}

$configfile = '/etc/dapnetapi.key';
$tempfile = '/tmp/jsADGHwf9sj294.tmp';

// This is the function going to update your ini file
function update_ini_file($data, $filepath) {
    $content = "";
    
    //parse the ini file to get the sections
    //parse the ini file using default parse_ini_file() PHP function
    $parsed_ini = parse_ini_file($filepath, true);
    
    foreach($data as $section=>$values) {
	// UnBreak special cases
	$section = str_replace("_", " ", $section);
	$content .= "[".$section."]\n";
	//append the values
	foreach($values as $key=>$value) {
            if (strcmp($key, 'TRXAREA') == 0)
                $content .= $key."=\"".$value."\"\n";
            else
                $content .= $key."=".$value."\n";
	}
	$content .= "\n";
    }
    
    //write it into file
    if (!$handle = fopen($filepath, 'w')) {
	return false;
    }
    
    $success = fwrite($handle, $content);
    fclose($handle);
    
    // Updates complete - copy the working file back to the proper location
    exec('sudo mount -o remount,rw /');                         // Make rootfs writable
    exec('sudo cp /tmp/jsADGHwf9sj294.tmp /etc/dapnetapi.key'); // Move the file back
    exec('sudo chmod 644 /etc/dapnetapi.key');                  // Set the correct runtime permissions
    exec('sudo chown root:root /etc/dapnetapi.key');            // Set the owner
    exec('sudo mount -o remount,ro /');                         // Make rootfs read-only
    
    return $success;
}

require_once('edit_template.php');

?>
