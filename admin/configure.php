<?php
// Get the CPU temp and colour the box accordingly...
$cpuTempCRaw = exec('cat /sys/class/thermal/thermal_zone0/temp');
if ($cpuTempCRaw > 1000) { $cpuTempC = round($cpuTempCRaw / 1000, 1); } else { $cpuTempC = round($cpuTempCRaw, 1); }
$cpuTempF = round(+$cpuTempC * 9 / 5 + 32, 1);
if ($cpuTempC < 50) { $cpuTempHTML = "<td style=\"background: #1d1\">".$cpuTempC."&deg;C/".$cpuTempF."&deg;F</td>\n"; }
if ($cpuTempC >= 50) { $cpuTempHTML = "<td style=\"background: #fa0\">".$cpuTempC."&deg;C/".$cpuTempF."&deg;F</td>\n"; }
if ($cpuTempC >= 69) { $cpuTempHTML = "<td style=\"background: #f00\">".$cpuTempC."&deg;C/".$cpuTempF."&deg;F</td>\n"; }

// Pull in some config
require_once('config/version.php');
require_once('config/ircddblocal.php');
require_once('config/language.php');
$cpuLoad = sys_getloadavg();

// Load the pistar-release file
$pistarReleaseConfig = '/etc/pistar-release';
$configPistarRelease = parse_ini_file($pistarReleaseConfig, true);

// Load the dstarrepeater config file
$configdstar = array();
if ($configdstarfile = fopen('/etc/dstarrepeater','r')) {
        while ($line1 = fgets($configdstarfile)) {
		if (strpos($line1, '=') !== false) {
                	list($key1,$value1) = preg_split('/=/',$line1);
                	$value1 = trim(str_replace('"','',$value1));
                	if (strlen($value1) > 0)
                	$configdstar[$key1] = $value1;
		}
        }
        fclose($configdstarfile);
}

// Load the ircDDBGateway config file
$configs = array();
if ($configfile = fopen($gatewayConfigPath,'r')) {
        while ($line = fgets($configfile)) {
		if (strpos($line, '=') !== false) {
                	list($key,$value) = preg_split('/=/',$line);
                	$value = trim(str_replace('"','',$value));
                	if ($key != 'ircddbPassword' && strlen($value) > 0)
                	$configs[$key] = $value;
		}
        }
        fclose($configfile);
}

// ini_set('display_errors', 1);
// ini_set("log_errors", 1);
// ini_set("error_log", "/tmp/php-error.log");

// Load the mmdvmhost config file
$mmdvmConfigFile = '/etc/mmdvmhost';
$configmmdvm = parse_ini_file($mmdvmConfigFile, true);

// Load the ysfgateway config file
$ysfgatewayConfigFile = '/etc/ysfgateway';
$configysfgateway = parse_ini_file($ysfgatewayConfigFile, true);

function ensureFileExists($fname) {
    if (!file_exists('/etc/'.$fname) || trim(@file_get_contents('/etc/'.$fname)) == false) {
	exec('sudo mount -o remount,rw /');
	exec('sudo sudo rm -rf /tmp/cfgupdate && mkdir -p /tmp/cfgupdate && sudo unzip /usr/local/bin/config_clean.zip -d /tmp/cfgupdate && sudo rm -f /etc/'.$fname.' && sudo mv -f /tmp/cfgupdate/'.$fname.' /etc/'.$fname.' && sudo chmod 644 /etc/'.$fname.' && sudo chown root:root /etc/'.$fname.' && sudo rm -rf /tmp/cfgupdate');
	exec('sudo mount -o remount,ro /');
    }
}

function write_log($log_msg) {
    $log_filename = "/tmp/ea7ee_logs";
    if (!file_exists($log_filename))
    {
        mkdir($log_filename, 0777, true);
    }
    $log_file_data = $log_filename.'/debug.log';
  file_put_contents($log_file_data, $log_msg . "\n", FILE_APPEND);
   
}
//write_log(print_r($configysfgateway, true));

// Load the ysf2nxdn config file
ensureFileExists('ysf2nxdn');
if (file_exists('/etc/ysf2nxdn')) {
	$ysf2nxdnConfigFile = '/etc/ysf2nxdn';
	if (fopen($ysf2nxdnConfigFile,'r')) { $configysf2nxdn = parse_ini_file($ysf2nxdnConfigFile, true); }
}

// Load the ysf2p25 config file
ensureFileExists('ysf2p25');
if (file_exists('/etc/ysf2p25')) {
	$ysf2p25ConfigFile = '/etc/ysf2p25';
	if (fopen($ysf2p25ConfigFile,'r')) { $configysf2p25 = parse_ini_file($ysf2p25ConfigFile, true); }
}

// Load the dmr2ysf config file
ensureFileExists('dmr2ysf');
if (file_exists('/etc/dmr2ysf')) {
	$dmr2ysfConfigFile = '/etc/dmr2ysf';
	if (fopen($dmr2ysfConfigFile,'r')) { $configdmr2ysf = parse_ini_file($dmr2ysfConfigFile, true); }
}

// Load the dmr2nxdn config file
ensureFileExists('dmr2nxdn');
if (file_exists('/etc/dmr2nxdn')) {
	$dmr2nxdnConfigFile = '/etc/dmr2nxdn';
	if (fopen($dmr2nxdnConfigFile,'r')) { $configdmr2nxdn = parse_ini_file($dmr2nxdnConfigFile, true); }
}

// Load the p25gateway config file
if (file_exists('/etc/p25gateway')) {
	$p25gatewayConfigFile = '/etc/p25gateway';
	if (fopen($p25gatewayConfigFile,'r')) { $configp25gateway = parse_ini_file($p25gatewayConfigFile, true); }
}

// Load the nxdngateway config file
if (file_exists('/etc/nxdngateway')) {
	$nxdngatewayConfigFile = '/etc/nxdngateway';
	if (fopen($nxdngatewayConfigFile,'r')) { $confignxdngateway = parse_ini_file($nxdngatewayConfigFile, true); }
}

// Load the nxdn2dmr config file
if (file_exists('/etc/nxdn2dmr')) {
	$nxdn2dmrConfigFile = '/etc/nxdn2dmr';
	if (fopen($nxdn2dmrConfigFile,'r')) { $confignxdn2dmr = parse_ini_file($nxdn2dmrConfigFile, true); }
}

//
// Old Mobile GPS conf conversion stuff
//
// Delete the old MobileGPS config file
if (file_exists('/etc/mobilegps'))
{
    exec('sudo mount -o remount,rw /');
    exec('sudo rm -f /etc/mobilegps');
    exec('sudo mount -o remount,ro /');
}
// Convert MMDVMHost config file
if (isset($configmmdvm['Mobile GPS']))
{
    if (isset($configmmdvm['Mobile GPS']['Enable']))
    {
	$configmmdvm['GPSD']['Enable'] = $configmmdvm['Mobile GPS']['Enable'];
	unset($configmmdvm['Mobile GPS']['Enable']);
    }
    
    if (isset($configmmdvm['Mobile GPS']['Address']))
    {
	unset($configmmdvm['Mobile GPS']['Address']);
    }
    
    if (isset($configmmdvm['Mobile GPS']['Port']))
    {
	unset($configmmdvm['Mobile GPS']['Port']);
    }
    
    unset($configmmdvm['Mobile GPS']);
}
// YSF Gateway config file
if (isset($configysfgateway['Mobile GPS']))
{
    if (isset($configysfgateway['Mobile GPS']['Enable']))
    {
	unset($configysfgateway['Mobile GPS']['Enable']);
    }
    
    if (isset($configysfgateway['Mobile GPS']['Address']))
    {
	unset($configysfgateway['Mobile GPS']['Address']);
    }
    
    if (isset($configysfgateway['Mobile GPS']['Port']))
    {
	unset($configysfgateway['Mobile GPS']['Port']);
    }
    
    unset($configysfgateway['Mobile GPS']);
}
// NXDN Gateway config file
if (isset($confignxdngateway['Mobile GPS']))
{
    if (isset($confignxdngateway['Mobile GPS']['Enable']))
    {
	unset($confignxdngateway['Mobile GPS']['Enable']);
    }
    
    if (isset($confignxdngateway['Mobile GPS']['Address']))
    {
	unset($confignxdngateway['Mobile GPS']['Address']);
    }
    
    if (isset($confignxdngateway['Mobile GPS']['Port']))
    {
	unset($confignxdngateway['Mobile GPS']['Port']);
    }
    
    unset($confignxdngateway['Mobile GPS']);
}

// GPSd
if (isset($configmmdvm['GPSD']) != TRUE)
{
    $configmmdvm['GPSD']['Enable'] = 0;
    $configmmdvm['GPSD']['Address'] = "127.0.0.1";
    $configmmdvm['GPSD']['Port'] = "2947";
}

if ($configmmdvm['GPSD']['Enable'] == 1)
{
    if (isset($configmmdvm['GPSD']['Address']) != TRUE)
    {
	$configmmdvm['GPSD']['Address'] = "127.0.0.1";
    }
    
    if (isset($configmmdvm['GPSD']['Port']) != TRUE)
    {
	$configmmdvm['GPSD']['Port'] = "2947";
    }
}

// DAPNet Gateway config
if (file_exists('/etc/dapnetgateway')) {
	$configDAPNetConfigFile = '/etc/dapnetgateway';
	if (fopen($configDAPNetConfigFile,'r')) { $configdapnetgw = parse_ini_file($configDAPNetConfigFile, true); }
}

// POCSAG
if ( $configmmdvm['POCSAG']['Enable'] == 1 ) {
    // DAPNet API config (create default file)
    if (!file_exists('/etc/dapnetapi.key')) {
        exec('sudo touch /tmp/jsADGHwf9sj294.tmp');
        exec('sudo chown www-data:www-data /tmp/jsADGHwf9sj294.tmp');
        exec('echo "[DAPNETAPI]" > /tmp/jsADGHwf9sj294.tmp');
        exec('echo "USER=" >> /tmp/jsADGHwf9sj294.tmp');
        exec('echo "PASS=" >> /tmp/jsADGHwf9sj294.tmp');
        exec('echo "TRXAREA=" >> /tmp/jsADGHwf9sj294.tmp');
        exec('echo "MY_RIC=" >> /tmp/jsADGHwf9sj294.tmp');
        
        exec('sudo mount -o remount,rw /');
        exec('sudo cp /tmp/jsADGHwf9sj294.tmp /etc/dapnetapi.key');
        exec('sudo chmod 644 /etc/dapnetapi.key');
        exec('sudo chown root:root /etc/dapnetapi.key');
        exec('sudo mount -o remount,ro /');
    }
    
    // DAPNet API config 
    if (file_exists('/etc/dapnetapi.key')) {
        $configDAPNetAPIConfigFile = '/etc/dapnetapi.key';
        if (fopen($configDAPNetAPIConfigFile,'r')) { $configdapnetapi = parse_ini_file($configDAPNetAPIConfigFile, true); }
    }
}

// Load the dmrgateway config file
$dmrGatewayConfigFile = '/etc/dmrgateway';
if (fopen($dmrGatewayConfigFile,'r')) { $configdmrgateway = parse_ini_file($dmrGatewayConfigFile, true); }

// Load the modem config information
if (file_exists('/etc/dstar-radio.dstarrepeater')) {
	$modemConfigFileDStarRepeater = '/etc/dstar-radio.dstarrepeater';
	if (fopen($modemConfigFileDStarRepeater,'r')) { $configModem = parse_ini_file($modemConfigFileDStarRepeater, true); }
}

if (file_exists('/etc/dstar-radio.mmdvmhost')) {
	$modemConfigFileMMDVMHost = '/etc/dstar-radio.mmdvmhost';
	if (fopen($modemConfigFileMMDVMHost,'r')) { $configModem = parse_ini_file($modemConfigFileMMDVMHost, true); }
}

function aprspass ($callsign) {
	$stophere = strpos($callsign, '-');
	if ($stophere) $callsign = substr($callsign, 0, $stophere);
	$realcall = strtoupper(substr($callsign, 0, 10));
	// initialize hash
	$hash = 0x73e2;
	$i = 0;
	$len = strlen($realcall);
	// hash callsign two bytes at a time
	while ($i < $len) {
		$hash ^= ord(substr($realcall, $i, 1))<<8;
		$hash ^= ord(substr($realcall, $i + 1, 1));
		$i += 2;
	}
	// mask off the high bit so number is always positive
	return $hash & 0x7fff;
}

$progname = basename($_SERVER['SCRIPT_FILENAME'],".php");
$rev=$version;
$MYCALL=strtoupper($callsign);
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">

<html xmlns="http://www.w3.org/1999/xhtml" xmlns:v="urn:schemas-microsoft-com:vml" lang="en">
<head>
    <meta name="robots" content="index" />
    <meta name="robots" content="follow" />
    <meta name="language" content="English" />
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <?php echo "<meta name=\"GENERATOR\" content=\"$progname $rev\" />\n"; ?>
    <meta name="Author" content="Andrew Taylor (MW0MWZ)" />
    <meta name="Description" content="Pi-Star Configuration" />
    <meta name="KeyWords" content="Pi-Star, MW0MWZ" />
    <meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate" />
    <meta http-equiv="pragma" content="no-cache" />
    <link rel="shortcut icon" href="images/favicon.ico" type="image/x-icon" />
    <meta http-equiv="Expires" content="0" />
    <title><?php echo "$MYCALL"." - ".$lang['digital_voice']." ".$lang['dashboard']." - ".$lang['configuration'];?></title>
    <link rel="stylesheet" type="text/css" href="/css/font-awesome-4.7.0/css/font-awesome.min.css" />
    <link rel="stylesheet" type="text/css" href="/css/pistar-css.php?version=0.94" />
    <script type="text/javascript">
	function disablesubmitbuttons() {
		var inputs = document.getElementsByTagName('input');
		for (var i = 0; i < inputs.length; i++) {
			if (inputs[i].type === 'button') {
				inputs[i].disabled = true;
			}
		}
	}
	function submitform() {
		disablesubmitbuttons();
		document.getElementById("config").submit();
	}
	function submitPassform() {
		disablesubmitbuttons();
		document.getElementById("adminPassForm").submit();
	}
	function factoryReset() {
		if (confirm('WARNING: This will set all your settings back to factory defaults. WiFi setup will be retained to maintain network access to this Pi.\n\nAre you SURE you want to do this?\n\nPress OK to restore the factory configuration\nPress Cancel to go back.')) {
			document.getElementById("factoryReset").submit();
		} else {
			return false;
		}
	}
	function resizeIframe(obj) {
		var numpix = parseInt(obj.contentWindow.document.body.scrollHeight, 10);
		obj.style.height = numpix + 'px';
	}
	function getLocation() {
		if (navigator.geolocation) {
			navigator.geolocation.getCurrentPosition(showPosition);
		}
	}
	function showPosition(position) {
		document.getElementById("confLatitude").value = position.coords.latitude.toFixed(5);
		document.getElementById("confLongitude").value = position.coords.longitude.toFixed(5);
	}
    </script>
    <script type="text/javascript" src="/functions.js?version=1.705"></script>
</head>
<body onload="checkFrequency(); return false;">
    <div class="container">
	<div class="header">
	    <div style="font-size: 8px; text-align: right; padding-right: 8px;">Pi-Star:<?php echo $configPistarRelease['Pi-Star']['Version']?> / <?php echo $lang['dashboard'].": ".$version; ?></div>
	    <h1>Pi-Star <?php echo $lang['digital_voice']." - ".$lang['configuration'];?></h1>
	    <p>
		<div class="navbar">
		    <a class="menureset" href="javascript:factoryReset();"><?php echo $lang['factory_reset'];?></a>
		    <a class="menubackup" href="/admin/config_backup.php"><?php echo $lang['backup_restore'];?></a>
		    <a class="menuupdate" href="/admin/update.php"><?php echo $lang['update'];?></a>
		    <a class="menupower" href="/admin/power.php"><?php echo $lang['power'];?></a>
		    <a class="menuexpert" href="/admin/expert/">Expert</a>
		    <a class="menuadmin" href="/admin/"><?php echo $lang['admin'];?></a>
		    <a class="menudashboard" href="/"><?php echo $lang['dashboard'];?></a>
		</div>
	    </p>
	</div>
	<div class="contentwide">
<?php
// Hardware Detail
if ($_SERVER["PHP_SELF"] == "/admin/configure.php") {
//HTML output starts here
?>
    <h2><?php echo $lang['hardware_info'];?></h2>
    <table style="table-layout: fixed;">
    <tr>
    <th><a class="tooltip" href="#"><?php echo $lang['hostname'];?><br /><span><b>System IP Address:<br /><?php echo str_replace(',', ',<br />', exec('hostname -I'));?></b></span></a></th>
    <th><a class="tooltip" href="#"><?php echo $lang['kernel'];?><span><b>Release</b>This is the version<br />number of the Linux Kernel running<br />on this Raspberry Pi.</span></a></th>
    <th colspan="2"><a class="tooltip" href="#"><?php echo $lang['platform'];?><span><b>Uptime:<br /><?php echo str_replace(',', ',<br />', exec('uptime -p'));?></b></span></a></th>
    <th colspan="2"><a class="tooltip" href="#"><?php echo $lang['cpu_load'];?><span><b>CPU Load</b></span></a></th>
    <th><a class="tooltip" href="#"><?php echo $lang['cpu_temp'];?><span><b>CPU Temp</b></span></a></th>
    </tr>
    <tr>
    <td><?php echo php_uname('n');?></td>
    <td><?php echo php_uname('r');?></td>
    <td colspan="2"><?php echo exec('platformDetect.sh');?></td>
    <td colspan="2">1m:<?php echo $cpuLoad[0];?> / 5m:<?php echo $cpuLoad[1];?> / 15m:<?php echo $cpuLoad[2];?></td>
    <?php echo $cpuTempHTML; ?>
    </tr>
    </table>
<br />
<?php if (!empty($_POST)):
	// Make the root filesystem writable
	system('sudo mount -o remount,rw /');

        if (isset($_POST['adminPasswordUpdate'])) {
	    echo "<table>\n";
	    echo "<tr><th>Working...</th></tr>\n";
	    echo "<tr><td>Updating Password...</td></tr>\n";
	    echo "</table>\n";
	    
	    // Admin Password Change
	    if (empty($_POST['adminPassword']) != TRUE ) {
		$rollAdminPass0 = 'htpasswd -b /var/www/.htpasswd pi-star '.escapeshellcmd($_POST['adminPassword']);
		system($rollAdminPass0);
		$rollAdminPass2 = 'sudo echo -e "'.escapeshellcmd($_POST['adminPassword']).'\n'.escapeshellcmd($_POST['adminPassword']).'" | sudo passwd pi-star';
		system($rollAdminPass2);
		
		// File Manager password
		$fmAuth = '<?php'."\n".'$auth_users = array('."\n".'\'root\' => \''.password_hash(escapeshellcmd($_POST['adminPassword']), PASSWORD_DEFAULT).'\','."\n".'\'pi-star\' => \''.password_hash(escapeshellcmd($_POST['adminPassword']), PASSWORD_DEFAULT).'\''."\n".');'."\n".'?>'."\n";
		if (($fd = fopen('/etc/tinyfilemanager-auth.php', "w")))
		{
		    @fwrite($fd, $fmAuth);
		    fclose($fd);
		}
		
		exec('sudo chown www-data:www-data '.$fmAuth.'');
		exec('sudo chmod 664 '.$fmAuth.'');

		echo "<br />\n";
		echo "<table>\n";
		echo "<tr><th>Done</th></tr>\n";
		echo "<tr><td>Password Updated...</td><tr>\n";
		echo "</table>\n";
		unset($_POST);
	    }
	    
	    // Make the root filesystem writable
	    system('sudo mount -o remount,ro /');

            echo '<script type="text/javascript">setTimeout(function() { window.location=window.location;},3000);</script>';
	    die();
	}

	// Stop Cron (occasionally remounts root as RO - would be bad if it did this at the wrong time....)
	system('sudo systemctl stop cron.service > /dev/null 2>/dev/null &');			//Cron

	// Stop the DV Services
	system('sudo systemctl stop gpsd.service > /dev/null 2>/dev/null &');			// GPSd Service
	system('sudo systemctl stop dstarrepeater.service > /dev/null 2>/dev/null &');		// D-Star Radio Service
	system('sudo systemctl stop mmdvmhost.service > /dev/null 2>/dev/null &');		// MMDVMHost Radio Service
	system('sudo systemctl stop ircddbgateway.service > /dev/null 2>/dev/null &');		// ircDDBGateway Service
	system('sudo systemctl stop timeserver.service > /dev/null 2>/dev/null &');		// Time Server Service
	system('sudo systemctl stop pistar-watchdog.service > /dev/null 2>/dev/null &');	// PiStar-Watchdog Service
	system('sudo systemctl stop pistar-remote.service > /dev/null 2>/dev/null &');		// PiStar-Remote Service
	system('sudo systemctl stop ysfgateway.service > /dev/null 2>/dev/null &');		// YSFGateway
	// system('sudo systemctl stop ysf2dmr.service > /dev/null 2>/dev/null &');		// YSF2DMR
	system('sudo systemctl stop ysf2nxdn.service > /dev/null 2>/dev/null &');		// YSF2NXDN
	system('sudo systemctl stop ysf2p25.service > /dev/null 2>/dev/null &');		// YSF2P25
	system('sudo systemctl stop nxdn2dmr.service > /dev/null 2>/dev/null &');		// NXDN2DMR
	system('sudo systemctl stop ysfparrot.service > /dev/null 2>/dev/null &');		// YSFParrot
	system('sudo systemctl stop p25gateway.service > /dev/null 2>/dev/null &');		// P25Gateway
	system('sudo systemctl stop p25parrot.service > /dev/null 2>/dev/null &');		// P25Parrot
	system('sudo systemctl stop nxdngateway.service > /dev/null 2>/dev/null &');		// NXDNGateway
	system('sudo systemctl stop nxdnparrot.service > /dev/null 2>/dev/null &');		// NXDNParrot
	system('sudo systemctl stop dmr2ysf.service > /dev/null 2>/dev/null &');		// DMR2YSF
	system('sudo systemctl stop dmr2nxdn.service > /dev/null 2>/dev/null &');		// DMR2YSF
	system('sudo systemctl stop dmrgateway.service > /dev/null 2>/dev/null &');		// DMRGateway
	system('sudo systemctl stop dapnetgateway.service > /dev/null 2>/dev/null &');		// DAPNetGateway

	echo "<table>\n";
	echo "<tr><th>Working...</th></tr>\n";
	echo "<tr><td>Stopping services and applying your configuration changes...</td></tr>\n";
	echo "</table>\n";

	// Let the services actualy stop
	sleep(1);


	// Factory Reset Handler Here
	if (empty($_POST['factoryReset']) != TRUE ) {
	  echo "<br />\n";
          echo "<table>\n";
          echo "<tr><th>Factory Reset Config</th></tr>\n";
          echo "<tr><td>Loading fresh configuration file(s)...</td><tr>\n";
          echo "</table>\n";
          unset($_POST);

	  // Over-write the config files with the clean copies
	  exec('sudo unzip -o /usr/local/bin/config_clean.zip -d /etc/');
	  exec('sudo rm -rf /etc/dstar-radio.*');
	  exec('sudo rm -rf /etc/pistar-css.ini');
	  exec('sudo git --work-tree=/usr/local/sbin --git-dir=/usr/local/sbin/.git update-index --assume-unchanged pistar-upnp.service');
	  exec('sudo git --work-tree=/usr/local/sbin --git-dir=/usr/local/sbin/.git reset --hard origin/master');
	  exec('sudo git --work-tree=/usr/local/bin --git-dir=/usr/local/bin/.git reset --hard origin/master');
	  exec('sudo git --work-tree=/var/www/dashboard --git-dir=/var/www/dashboard/.git reset --hard origin/master');
          echo '<script type="text/javascript">setTimeout(function() { window.location=window.location;},5000);</script>';
	  // Make the root filesystem read-only
          system('sudo mount -o remount,ro /');
	  echo "<br />\n</div>\n";
          echo "<div class=\"footer\">\nPi-Star web config, &copy; Andy Taylor (MW0MWZ) 2014-".date("Y").".<br />\n";
          echo "Need help? Click <a style=\"color: #ffffff;\" href=\"https://www.facebook.com/groups/pistarusergroup/\" target=\"_new\">here for the Support Group</a><br />\n";
          echo 'or Click <a style="color: #ffffff;" href="https://forum.pistar.uk/" target="_new">here to join the Support Forum</a>'."\n";
          echo "<br />\n</div>\n</div>\n</body>\n</html>\n";
	  die();
	  }

	// Handle the case where the config is not read correctly
	if (count($configmmdvm) <= 18) {
	  echo "<br />\n";
	  echo "<table>\n";
	  echo "<tr><th>ERROR</th></tr>\n";
	  echo "<tr><td>Unable to read source configuration file(s)...</td><tr>\n";
	  echo "<tr><td>Please wait a few seconds and retry...</td></tr>\n";
	  echo "</table>\n";
	  unset($_POST);
	  echo '<script type="text/javascript">setTimeout(function() { window.location=window.location;},5000);</script>';
	  die();
	}

	// Change Radio Control Software
	if (empty($_POST['controllerSoft']) != TRUE ) {
	  system('sudo rm -rf /etc/dstar-radio.*');
	  if (escapeshellcmd($_POST['controllerSoft']) == 'DSTAR') { system('sudo touch /etc/dstar-radio.dstarrepeater'); }
	  if (escapeshellcmd($_POST['controllerSoft']) == 'MMDVM') { system('sudo touch /etc/dstar-radio.mmdvmhost'); }
	  }

	// HostAP
	if (empty($_POST['autoAP']) != TRUE ) {
	  if (escapeshellcmd($_POST['autoAP']) == 'OFF') { system('sudo touch /etc/hostap.off'); }
	  if (escapeshellcmd($_POST['autoAP']) == 'ON') { system('sudo rm -rf /etc/hostap.off'); }
	}

	// Change Dashboard Language
	if (empty($_POST['dashboardLanguage']) != TRUE ) {
	  $rollDashLang = 'sudo sed -i "/pistarLanguage=/c\\$pistarLanguage=\''.escapeshellcmd($_POST['dashboardLanguage']).'\';" /var/www/dashboard/config/language.php';
	  system($rollDashLang);
	  }

	// Set the ircDDBGAteway Remote Password and Port
	if (empty($_POST['confPassword']) != TRUE ) {
	  $rollConfPassword0 = 'sudo sed -i "/remotePassword=/c\\remotePassword='.escapeshellcmd($_POST['confPassword']).'" /etc/ircddbgateway';
	  $rollConfPassword1 = 'sudo sed -i "/password=/c\\password='.escapeshellcmd($_POST['confPassword']).'" /root/.Remote\ Control';
	  $rollConfRemotePort = 'sudo sed -i "/port=/c\\port='.$configs['remotePort'].'" /root/.Remote\ Control';
	  system($rollConfPassword0);
	  system($rollConfPassword1);
	  system($rollConfRemotePort);
	  }

	// Set the ircDDBGateway Defaut Reflector
	if (empty($_POST['confDefRef']) != TRUE ) {
	  if (stristr(strtoupper(escapeshellcmd($_POST['confDefRef'])), strtoupper(escapeshellcmd($_POST['confCallsign']))) != TRUE ) {
	    if (strlen($_POST['confDefRef']) != 7) {
		$targetRef = strtoupper(escapeshellcmd(str_pad($_POST['confDefRef'], 7, " ")));
	        } else {
		$targetRef = strtoupper(escapeshellcmd($_POST['confDefRef']));
	        }
	    $rollconfDefRef = 'sudo sed -i "/reflector1=/c\\reflector1='.$targetRef.escapeshellcmd($_POST['confDefRefLtr']).'" /etc/ircddbgateway';
	    system($rollconfDefRef);
	    }
	  }

	// Set the ircDDBGAteway Defaut Reflector Autostart
	if (empty($_POST['confDefRefAuto']) != TRUE ) {
	  if (escapeshellcmd($_POST['confDefRefAuto']) == 'ON') {
	    $rollconfDefRefAuto = 'sudo sed -i "/atStartup1=/c\\atStartup1=1" /etc/ircddbgateway';
	  }
	  if (escapeshellcmd($_POST['confDefRefAuto']) == 'OFF') {
	    $rollconfDefRefAuto = 'sudo sed -i "/atStartup1=/c\\atStartup1=0" /etc/ircddbgateway';
	  }
	  system($rollconfDefRefAuto);
	  }

	// Set the Latitude
	if (empty($_POST['confLatitude']) != TRUE ) {
	  $newConfLatitude = preg_replace('/[^0-9\.\-]/', '', $_POST['confLatitude']);
	  $rollConfLat0 = 'sudo sed -i "/latitude=/c\\latitude='.$newConfLatitude.'" /etc/ircddbgateway';
	  $rollConfLat1 = 'sudo sed -i "/latitude1=/c\\latitude1='.$newConfLatitude.'" /etc/ircddbgateway';
	  $configmmdvm['Info']['Latitude'] = $newConfLatitude;
	  $configysfgateway['Info']['Latitude'] = $newConfLatitude;
	  $configysf2nxdn['Info']['Latitude'] = $newConfLatitude;
	  $configysf2p25['Info']['Latitude'] = $newConfLatitude;
	  $configdmrgateway['Info']['Latitude'] = $newConfLatitude;
	  $confignxdngateway['Info']['Latitude'] = $newConfLatitude;
	  system($rollConfLat0);
	  system($rollConfLat1);
	  }

	// Set the Longitude
	if (empty($_POST['confLongitude']) != TRUE ) {
	  $newConfLongitude = preg_replace('/[^0-9\.\-]/', '', $_POST['confLongitude']);
	  $rollConfLon0 = 'sudo sed -i "/longitude=/c\\longitude='.$newConfLongitude.'" /etc/ircddbgateway';
	  $rollConfLon1 = 'sudo sed -i "/longitude1=/c\\longitude1='.$newConfLongitude.'" /etc/ircddbgateway';
	  $configmmdvm['Info']['Longitude'] = $newConfLongitude;
	  $configysfgateway['Info']['Longitude'] = $newConfLongitude;
	  $configysf2nxdn['Info']['Longitude'] = $newConfLongitude;
	  $configysf2p25['Info']['Longitude'] = $newConfLongitude;
	  $configdmrgateway['Info']['Longitude'] = $newConfLongitude;
	  $confignxdngateway['Info']['Longitude'] = $newConfLongitude;
	  system($rollConfLon0);
	  system($rollConfLon1);
	  }

	// Set GPSd
	if (empty($_POST['GPSD']) != TRUE ) {
	   $gpsdEnabled = (escapeshellcmd($_POST['GPSD']) == 'ON' ) ? "1" : "0";
	   $configmmdvm['GPSD']['Enable'] = $gpsdEnabled;
//	   $configysfgateway['GPSD']['Enable'] = $gpsdEnabled;
	   $confignxdngateway['GPSD']['Enable'] = $gpsdEnabled;
	    
	   if (empty($_POST['gpsdPort']) != TRUE ) {
	      $configmmdvm['GPSD']['Port'] = escapeshellcmd($_POST['gpsdPort']);
	   }
	    
	   if (empty($_POST['gpsdServer']) != TRUE ) {
	      $configmmdvm['GPSD']['Address'] = escapeshellcmd($_POST['gpsdServer']);
	   }

	   // Port and Address for YSF and NXDN gateways	   
	   $confignxdngateway['GPSD']['Port'] = $configmmdvm['GPSD']['Port'];
	   $confignxdngateway['GPSD']['Address'] = $configmmdvm['GPSD']['Address'];
	}

	// Set the Town
	if (empty($_POST['confDesc1']) != TRUE ) {
	  $newConfDesc1 = preg_replace('/[^A-Za-z0-9\.\s\,\-]/', '', $_POST['confDesc1']);
	  $rollDesc1 = 'sudo sed -i "/description1=/c\\description1='.$newConfDesc1.'" /etc/ircddbgateway';
	  $rollDesc11 = 'sudo sed -i "/description1_1=/c\\description1_1='.$newConfDesc1.'" /etc/ircddbgateway';
	  $configmmdvm['Info']['Location'] = '"'.$newConfDesc1.'"';
	  $configysfgateway['Info']['Location'] = '"'.$newConfDesc1.'"';	  
	  $configdmrgateway['Info']['Location'] = '"'.$newConfDesc1.'"';
	  $configysf2nxdn['Info']['Location'] = '"'.$newConfDesc1.'"';
	  $configysf2p25['Info']['Location'] = '"'.$newConfDesc1.'"';
	  $confignxdngateway['Info']['Name'] = '"'.$newConfDesc1.'"';
	  system($rollDesc1);
	  system($rollDesc11);
	  }

	// Set the Country
	if (empty($_POST['confDesc2']) != TRUE ) {
	  $newConfDesc2 = preg_replace('/[^A-Za-z0-9\.\s\,\-]/', '', $_POST['confDesc2']);
	  $rollDesc2 = 'sudo sed -i "/description2=/c\\description2='.$newConfDesc2.'" /etc/ircddbgateway';
	  $rollDesc22 = 'sudo sed -i "/description1_2=/c\\description1_2='.$newConfDesc2.'" /etc/ircddbgateway';
          $configmmdvm['Info']['Description'] = '"'.$newConfDesc2.'"';
	  $configdmrgateway['Info']['Description'] = '"'.$newConfDesc2.'"';
          $configysfgateway['Info']['Description'] = '"'.$newConfDesc2.'"';
	  $confignxdngateway['Info']['Description'] = '"'.$newConfDesc2.'"';
	  system($rollDesc2);
	  system($rollDesc22);
	  }

	// Set the URL
	if (empty($_POST['confURL']) != TRUE ) {
	  $newConfURL = strtolower(preg_replace('/[^A-Za-z0-9\.\s\,\-\/\:]/', '', $_POST['confURL']));
	  if (escapeshellcmd($_POST['urlAuto']) == 'auto') { $txtURL = "http://www.qrz.com/db/".strtoupper(escapeshellcmd($_POST['confCallsign'])); }
	  if (escapeshellcmd($_POST['urlAuto']) == 'man')  { $txtURL = $newConfURL; }
	  if (escapeshellcmd($_POST['urlAuto']) == 'auto') { $rollURL0 = 'sudo sed -i "/url=/c\\url=http://www.qrz.com/db/'.strtoupper(escapeshellcmd($_POST['confCallsign'])).'" /etc/ircddbgateway';  }
	  if (escapeshellcmd($_POST['urlAuto']) == 'man') { $rollURL0 = 'sudo sed -i "/url=/c\\url='.$newConfURL.'" /etc/ircddbgateway'; }
		  $configmmdvm['Info']['URL'] = $txtURL;
	  $configysfgateway['Info']['URL'] = $txtURL;
	  $configysf2nxdn['Info']['URL'] = $txtURL;
	  $configysf2p25['Info']['URL'] = $txtURL;
	  $configdmrgateway['Info']['URL'] = $txtURL;
	  system($rollURL0);
	  }

	// Set the APRS Host for ircDDBGateway
	if (empty($_POST['selectedAPRSHost']) != TRUE ) {
	  $rollAPRSHost = 'sudo sed -i "/aprsHostname=/c\\aprsHostname='.escapeshellcmd($_POST['selectedAPRSHost']).'" /etc/ircddbgateway';
	  system($rollAPRSHost);
	  $configysfgateway['aprs.fi']['Server'] = escapeshellcmd($_POST['selectedAPRSHost']);
	  $configysf2nxdn['aprs.fi']['Server'] = escapeshellcmd($_POST['selectedAPRSHost']);
	  $configysf2p25['aprs.fi']['Server'] = escapeshellcmd($_POST['selectedAPRSHost']);
	  $configysf2nxdn['aprs.fi']['Enable'] = "0";
	  $configysf2p25['aprs.fi']['Enable'] = "0";
	  }

	// Set ircDDBGateway and TimeServer language
	if (empty($_POST['ircDDBGatewayAnnounceLanguage']) != TRUE) {
	  $ircDDBGatewayAnnounceLanguageArr = explode(',', escapeshellcmd($_POST['ircDDBGatewayAnnounceLanguage']));
	  $rollIrcDDBGatewayLang = 'sudo sed -i "/language=/c\\language='.escapeshellcmd($ircDDBGatewayAnnounceLanguageArr[0]).'" /etc/ircddbgateway';
	  $rollTimeserverLang = 'sudo sed -i "/language=/c\\language='.escapeshellcmd($ircDDBGatewayAnnounceLanguageArr[1]).'" /etc/timeserver';
	  system($rollIrcDDBGatewayLang);
	  system($rollTimeserverLang);
	}

	// Clear timeserver modules
	$rollTimeserverBandA = 'sudo sed -i "/sendA=/c\\sendA=0" /etc/timeserver';
	$rollTimeserverBandB = 'sudo sed -i "/sendB=/c\\sendB=0" /etc/timeserver';
	$rollTimeserverBandC = 'sudo sed -i "/sendC=/c\\sendC=0" /etc/timeserver';
	$rollTimeserverBandD = 'sudo sed -i "/sendD=/c\\sendD=0" /etc/timeserver';
	$rollTimeserverBandE = 'sudo sed -i "/sendE=/c\\sendE=0" /etc/timeserver';
	system($rollTimeserverBandA);
	system($rollTimeserverBandB);
	system($rollTimeserverBandC);
	system($rollTimeserverBandD);
	system($rollTimeserverBandE);

	// Set the POCSAG Frequency
	if (empty($_POST['pocsagFrequency']) != TRUE ) {
	  $newPocsagFREQ = preg_replace('/[^0-9\.]/', '', $_POST['pocsagFrequency']);
	  $newPocsagFREQ = str_pad(str_replace(".", "", $newPocsagFREQ), 9, "0");
	  $newPocsagFREQ = mb_strimwidth($newPocsagFREQ, 0, 9);
	  $configmmdvm['POCSAG']['Frequency'] = $newPocsagFREQ;
	}

	// Set the POCSAG AuthKey
	if (empty($_POST['pocsagAuthKey']) != TRUE ) {
	  $configdapnetgw['DAPNET']['AuthKey'] = escapeshellcmd($_POST['pocsagAuthKey']);
	}

	// Set the POCSAG Callsign
	if (empty($_POST['pocsagCallsign']) != TRUE ) {
	  $configdapnetgw['General']['Callsign'] = strtoupper(escapeshellcmd($_POST['pocsagCallsign']));
	}

	// Set the POCSAG WhiteList
	if (empty($_POST['MMDVMModePOCSAG']) != TRUE ) {
	    if ((escapeshellcmd($_POST['MMDVMModePOCSAG']) == 'ON') && (isset($configdapnetgw['General']['WhiteList'])) && (empty($_POST['pocsagWhitelist']) == TRUE)) { unset($configdapnetgw['General']['WhiteList']); }
	    if (empty($_POST['pocsagWhitelist']) != TRUE ) {
		$configdapnetgw['General']['WhiteList'] = preg_replace('/[^0-9\,]/', '', escapeshellcmd($_POST['pocsagWhitelist']));
	    }
	}

	// Set the POCSAG BlackList
	if (empty($_POST['MMDVMModePOCSAG']) != TRUE ) {
	    if ((escapeshellcmd($_POST['MMDVMModePOCSAG']) == 'ON') && (isset($configdapnetgw['General']['BlackList'])) && (empty($_POST['pocsagBlacklist']) == TRUE)) { unset($configdapnetgw['General']['BlackList']); }
	    if (empty($_POST['pocsagBlacklist']) != TRUE ) {
		$configdapnetgw['General']['BlackList'] = preg_replace('/[^0-9\,]/', '', escapeshellcmd($_POST['pocsagBlacklist']));
	    }
	}

	// Set the POCSAG Server
	if (empty($_POST['pocsagServer']) != TRUE ) {
	  $configdapnetgw['DAPNET']['Address'] = escapeshellcmd($_POST['pocsagServer']);
	}

	// Set the DAPNET RIC
	if (empty($_POST['dapnetAPIRic']) != TRUE ) {
        $configdapnetapi['DAPNETAPI']['MY_RIC'] = escapeshellcmd(trim($_POST['dapnetAPIRic']));
	}

	// Set the DAPNET API Transmitter Group
	if (empty($_POST['dapnetAPITrxGroup']) != TRUE ) {
        $dapnetapitrxgrp = preg_replace('/[^,:space:[:alnum:]-]/', "", trim(strtolower($_POST['dapnetAPITrxGroup']))); // Only A-Z a-z 0-9 - and , allowed
        while (preg_match('/,,/', $dapnetapitrxgrp)) { $dapnetapitrxgrp = preg_replace('/,,/', ",", $dapnetapitrxgrp); } // Replace any double comma with single comma
        while (preg_match('/--/', $dapnetapitrxgrp)) { $dapnetapitrxgrp = preg_replace('/--/', "-", $dapnetapitrxgrp); } // Replace any double dash with single dash
        $dapnetapitrxgrp = rtrim($dapnetapitrxgrp, ","); // Remove comma at the end of the string, if any.

        // Store cleaned TRX Group(s)
        $configdapnetapi['DAPNETAPI']['TRXAREA'] = '"'.$dapnetapitrxgrp.'"';
	}

	// Set the DAPNET API Password
	if (empty($_POST['dapnetAPIPass']) != TRUE ) {
        $configdapnetapi['DAPNETAPI']['PASS'] = escapeshellcmd(trim($_POST['dapnetAPIPass']));
	}

	// Set the DAPNET API Username
	if (empty($_POST['dapnetAPIUser']) != TRUE ) {
        $configdapnetapi['DAPNETAPI']['USER'] = escapeshellcmd(trim($_POST['dapnetAPIUser']));
	}

	// Set the Frequency for Duplex
	if (empty($_POST['confFREQtx']) != TRUE && empty($_POST['confFREQrx']) != TRUE ) {
	  if (empty($_POST['confHardware']) != TRUE ) { $confHardware = escapeshellcmd($_POST['confHardware']); }
	  $newConfFREQtx = preg_replace('/[^0-9\.]/', '', $_POST['confFREQtx']);
	  $newConfFREQrx = preg_replace('/[^0-9\.]/', '', $_POST['confFREQrx']);
	  $newFREQtx = str_pad(str_replace(".", "", $newConfFREQtx), 9, "0");
	  $newFREQtx = mb_strimwidth($newFREQtx, 0, 9);
	  $newFREQrx = str_pad(str_replace(".", "", $newConfFREQrx), 9, "0");
	  $newFREQrx = mb_strimwidth($newFREQrx, 0, 9);
	  $newFREQirc = substr_replace($newFREQtx, '.', '3', 0);
	  $newFREQirc = mb_strimwidth($newFREQirc, 0, 9);
	  $newFREQOffset = ($newFREQrx - $newFREQtx)/1000000;
	  $newFREQOffset = number_format($newFREQOffset, 4, '.', '');
	  $rollFREQirc = 'sudo sed -i "/frequency1=/c\\frequency1='.$newFREQirc.'" /etc/ircddbgateway';
	  $rollFREQdvap = 'sudo sed -i "/dvapFrequency=/c\\dvapFrequency='.$newFREQrx.'" /etc/dstarrepeater';
	  $rollFREQdvmegaRx = 'sudo sed -i "/dvmegaRXFrequency=/c\\dvmegaRXFrequency='.$newFREQrx.'" /etc/dstarrepeater';
	  $rollFREQdvmegaTx = 'sudo sed -i "/dvmegaTXFrequency=/c\\dvmegaTXFrequency='.$newFREQtx.'" /etc/dstarrepeater';
	  $rollModeDuplex = 'sudo sed -i "/mode=/c\\mode=0" /etc/dstarrepeater';
	  $rollGatewayType = 'sudo sed -i "/gatewayType=/c\\gatewayType=0" /etc/ircddbgateway';
	  $rollFREQOffset = 'sudo sed -i "/offset1=/c\\offset1='.$newFREQOffset.'" /etc/ircddbgateway';
	  $configmmdvm['Info']['RXFrequency'] = $newFREQrx;
	  $configmmdvm['Info']['TXFrequency'] = $newFREQtx;
	  $configdmrgateway['Info']['RXFrequency'] = $newFREQrx;
	  $configdmrgateway['Info']['TXFrequency'] = $newFREQtx;
	  $configysfgateway['Info']['RXFrequency'] = $newFREQrx;
	  $configysfgateway['Info']['TXFrequency'] = $newFREQtx;
	  //$configysfgateway['General']['Suffix'] = "RPT";
	  $configysf2nxdn['Info']['RXFrequency'] = $newFREQrx;
	  $configysf2nxdn['Info']['TXFrequency'] = $newFREQtx;
	  $configysf2nxdn['YSF Network']['Suffix'] = "RPT";
	  $configysf2p25['Info']['RXFrequency'] = $newFREQrx;
	  $configysf2p25['Info']['TXFrequency'] = $newFREQtx;
	  $configysf2p25['YSF Network']['Suffix'] = "RPT";
	  $configdmr2ysf['YSF Network']['Suffix'] = "RPT";
	  $confignxdngateway['Info']['RXFrequency'] = $newFREQrx;
	  $confignxdngateway['Info']['TXFrequency'] = $newFREQtx;
	  $confignxdngateway['General']['Suffix'] = "RPT";

	  system($rollFREQirc);
	  system($rollFREQdvap);
	  system($rollFREQdvmegaRx);
	  system($rollFREQdvmegaTx);
	  system($rollModeDuplex);
	  system($rollGatewayType);
	  system($rollFREQOffset);

	// Set RPT1 and RPT2
	  if (empty($_POST['confDStarModuleSuffix'])) {
	    if ($newFREQtx >= 1240000000 && $newFREQtx <= 1300000000) {
		$confRPT1 = str_pad(escapeshellcmd($_POST['confCallsign']), 7, " ")."A";
		$confIRCrepeaterBand1 = "A";
		$configmmdvm['D-Star']['Module'] = "A";
		$rollTimeserverBand = 'sudo sed -i "/sendA=/c\\sendA=1" /etc/timeserver';
		system($rollTimeserverBand);
		}
	    if ($newFREQtx >= 420000000 && $newFREQtx <= 450000000) {
		$confRPT1 = str_pad(escapeshellcmd($_POST['confCallsign']), 7, " ")."B";
		$confIRCrepeaterBand1 = "B";
		$configmmdvm['D-Star']['Module'] = "B";
		$rollTimeserverBand = 'sudo sed -i "/sendB=/c\\sendB=1" /etc/timeserver';
		system($rollTimeserverBand);
		}
	    if ($newFREQtx >= 218000000 && $newFREQtx <= 226000000) {
		$confRPT1 = str_pad(escapeshellcmd($_POST['confCallsign']), 7, " ")."A";
		$confIRCrepeaterBand1 = "A";
		$configmmdvm['D-Star']['Module'] = "A";
		$rollTimeserverBand = 'sudo sed -i "/sendA=/c\\sendA=1" /etc/timeserver';
		system($rollTimeserverBand);
		}
	    if ($newFREQtx >= 144000000 && $newFREQtx <= 148000000) {
		$confRPT1 = str_pad(escapeshellcmd($_POST['confCallsign']), 7, " ")."C";
		$confIRCrepeaterBand1 = "C";
		$configmmdvm['D-Star']['Module'] = "C";
		$rollTimeserverBand = 'sudo sed -i "/sendC=/c\\sendC=1" /etc/timeserver';
		system($rollTimeserverBand);
		}
	  }
	  else {
	     $confRPT1 = str_pad(escapeshellcmd($_POST['confCallsign']), 7, " ").strtoupper(escapeshellcmd($_POST['confDStarModuleSuffix']));
	     $confIRCrepeaterBand1 = strtoupper(escapeshellcmd($_POST['confDStarModuleSuffix']));
	     $configmmdvm['D-Star']['Module'] = strtoupper(escapeshellcmd($_POST['confDStarModuleSuffix']));
	     $rollTimeserverBand = 'sudo sed -i "/send'.strtoupper(escapeshellcmd($_POST['confDStarModuleSuffix'])).'=/c\\send'.strtoupper(escapeshellcmd($_POST['confDStarModuleSuffix'])).'=1" /etc/timeserver';
	     system($rollTimeserverBand);
	  }

	  $newCallsignUpper = strtoupper(escapeshellcmd($_POST['confCallsign']));
	  $confRPT2 = str_pad(escapeshellcmd($_POST['confCallsign']), 7, " ")."G";

	  $confRPT1 = strtoupper($confRPT1);
	  $confRPT2 = strtoupper($confRPT2);

	  $rollRPT1 = 'sudo sed -i "/callsign=/c\\callsign='.$confRPT1.'" /etc/dstarrepeater';
	  $rollRPT2 = 'sudo sed -i "/gateway=/c\\gateway='.$confRPT2.'" /etc/dstarrepeater';
	  $rollBEACONTEXT = 'sudo sed -i "/beaconText=/c\\beaconText='.$confRPT1.'" /etc/dstarrepeater';
	  $rollIRCrepeaterBand1 = 'sudo sed -i "/repeaterBand1=/c\\repeaterBand1='.$confIRCrepeaterBand1.'" /etc/ircddbgateway';
	  $rollIRCrepeaterCall1 = 'sudo sed -i "/repeaterCall1=/c\\repeaterCall1='.$newCallsignUpper.'" /etc/ircddbgateway';

	  system($rollRPT1);
	  system($rollRPT2);
	  system($rollBEACONTEXT);
	  system($rollIRCrepeaterBand1);
	  system($rollIRCrepeaterCall1);
	}

	// Set the Frequency for Simplex
	if (empty($_POST['confFREQ']) != TRUE ) {
	  if (empty($_POST['confHardware']) != TRUE ) { $confHardware = escapeshellcmd($_POST['confHardware']); }
	  $newConfFREQ = preg_replace('/[^0-9\.]/', '', $_POST['confFREQ']);
	  $newFREQ = str_pad(str_replace(".", "", $newConfFREQ), 9, "0");
	  $newFREQ = mb_strimwidth($newFREQ, 0, 9);
	  $newFREQirc = substr_replace($newFREQ, '.', '3', 0);
	  $newFREQirc = mb_strimwidth($newFREQirc, 0, 9);
	  $newFREQOffset = "0.0000";
	  $rollFREQirc = 'sudo sed -i "/frequency1=/c\\frequency1='.$newFREQirc.'" /etc/ircddbgateway';
	  $rollFREQdvap = 'sudo sed -i "/dvapFrequency=/c\\dvapFrequency='.$newFREQ.'" /etc/dstarrepeater';
	  $rollFREQdvmegaRx = 'sudo sed -i "/dvmegaRXFrequency=/c\\dvmegaRXFrequency='.$newFREQ.'" /etc/dstarrepeater';
	  $rollFREQdvmegaTx = 'sudo sed -i "/dvmegaTXFrequency=/c\\dvmegaTXFrequency='.$newFREQ.'" /etc/dstarrepeater';
	  $rollModeSimplex = 'sudo sed -i "/mode=/c\\mode=1" /etc/dstarrepeater';
	  $rollGatewayType = 'sudo sed -i "/gatewayType=/c\\gatewayType=1" /etc/ircddbgateway';
	  $rollFREQOffset = 'sudo sed -i "/offset1=/c\\offset1='.$newFREQOffset.'" /etc/ircddbgateway';
	  $configmmdvm['Info']['RXFrequency'] = $newFREQ;
	  $configmmdvm['Info']['TXFrequency'] = $newFREQ;
	  $configdmrgateway['Info']['RXFrequency'] = $newFREQ;
	  $configdmrgateway['Info']['TXFrequency'] = $newFREQ;
	  $configysfgateway['Info']['RXFrequency'] = $newFREQ;
	  $configysfgateway['Info']['TXFrequency'] = $newFREQ;
	  //$configysfgateway['General']['Suffix'] = "ND";
	  $configysf2nxdn['Info']['RXFrequency'] = $newFREQ;
	  $configysf2nxdn['Info']['TXFrequency'] = $newFREQ;
	  $configysf2nxdn['YSF Network']['Suffix'] = "ND";
	  $configysf2p25['Info']['RXFrequency'] = $newFREQ;
	  $configysf2p25['Info']['TXFrequency'] = $newFREQ;
	  $configysf2p25['YSF Network']['Suffix'] = "ND";
	  $configdmr2ysf['YSF Network']['Suffix'] = "ND";
	  $confignxdngateway['Info']['RXFrequency'] = $newFREQ;
	  $confignxdngateway['Info']['TXFrequency'] = $newFREQ;
	  $confignxdngateway['General']['Suffix'] = "ND";

	  system($rollFREQirc);
	  system($rollFREQdvap);
	  system($rollFREQdvmegaRx);
	  system($rollFREQdvmegaTx);
	  system($rollModeSimplex);
	  system($rollGatewayType);
	  system($rollFREQOffset);

	// Set RPT1 and RPT2
	  if (empty($_POST['confDStarModuleSuffix'])) {
	    if ($newFREQ >= 1240000000 && $newFREQ <= 1300000000) {
		$confRPT1 = str_pad(escapeshellcmd($_POST['confCallsign']), 7, " ")."A";
		$confIRCrepeaterBand1 = "A";
		$configmmdvm['D-Star']['Module'] = "A";
		$rollTimeserverBand = 'sudo sed -i "/sendA=/c\\sendA=1" /etc/timeserver';
		system($rollTimeserverBand);
		}
	    if ($newFREQ >= 420000000 && $newFREQ <= 450000000) {
		$confRPT1 = str_pad(escapeshellcmd($_POST['confCallsign']), 7, " ")."B";
		$confIRCrepeaterBand1 = "B";
		$configmmdvm['D-Star']['Module'] = "B";
		$rollTimeserverBand = 'sudo sed -i "/sendB=/c\\sendB=1" /etc/timeserver';
		system($rollTimeserverBand);
		}
	    if ($newFREQ >= 218000000 && $newFREQ <= 226000000) {
		$confRPT1 = str_pad(escapeshellcmd($_POST['confCallsign']), 7, " ")."A";
		$confIRCrepeaterBand1 = "A";
		$configmmdvm['D-Star']['Module'] = "A";
		$rollTimeserverBand = 'sudo sed -i "/sendA=/c\\sendA=1" /etc/timeserver';
		system($rollTimeserverBand);
		}
	    if ($newFREQ >= 144000000 && $newFREQ <= 148000000) {
		$confRPT1 = str_pad(escapeshellcmd($_POST['confCallsign']), 7, " ")."C";
		$confIRCrepeaterBand1 = "C";
		$configmmdvm['D-Star']['Module'] = "C";
		$rollTimeserverBand = 'sudo sed -i "/sendA=/c\\sendA=1" /etc/timeserver';
		system($rollTimeserverBand);
		}
	  }
	  else {
	     $confRPT1 = str_pad(escapeshellcmd($_POST['confCallsign']), 7, " ").strtoupper(escapeshellcmd($_POST['confDStarModuleSuffix']));
	     $confIRCrepeaterBand1 = strtoupper(escapeshellcmd($_POST['confDStarModuleSuffix']));
	     $configmmdvm['D-Star']['Module'] = strtoupper(escapeshellcmd($_POST['confDStarModuleSuffix']));
	     $rollTimeserverBand = 'sudo sed -i "/send'.strtoupper(escapeshellcmd($_POST['confDStarModuleSuffix'])).'=/c\\send'.strtoupper(escapeshellcmd($_POST['confDStarModuleSuffix'])).'=1" /etc/timeserver';
	     system($rollTimeserverBand);
	  }

	  $newCallsignUpper = strtoupper(escapeshellcmd($_POST['confCallsign']));
	  $confRPT2 = str_pad(escapeshellcmd($_POST['confCallsign']), 7, " ")."G";

	  $confRPT1 = strtoupper($confRPT1);
	  $confRPT2 = strtoupper($confRPT2);

	  $rollRPT1 = 'sudo sed -i "/callsign=/c\\callsign='.$confRPT1.'" /etc/dstarrepeater';
	  $rollRPT2 = 'sudo sed -i "/gateway=/c\\gateway='.$confRPT2.'" /etc/dstarrepeater';
	  $rollBEACONTEXT = 'sudo sed -i "/beaconText=/c\\beaconText='.$confRPT1.'" /etc/dstarrepeater';
	  $rollIRCrepeaterBand1 = 'sudo sed -i "/repeaterBand1=/c\\repeaterBand1='.$confIRCrepeaterBand1.'" /etc/ircddbgateway';
	  $rollIRCrepeaterCall1 = 'sudo sed -i "/repeaterCall1=/c\\repeaterCall1='.$newCallsignUpper.'" /etc/ircddbgateway';

	  system($rollRPT1);
	  system($rollRPT2);
	  system($rollBEACONTEXT);
	  system($rollIRCrepeaterBand1);
	  system($rollIRCrepeaterCall1);
	  }

	// Set Callsign
	if (empty($_POST['confCallsign']) != TRUE ) {
	  $newCallsignUpper = strtoupper($_POST['confCallsign']);
		  //preg_replace('/[^A-Za-z0-9]/', '', $_POST['confCallsign']));
	  // Removed the need for the r prefix - OpenQuad have fixed up the servers not to require it.
	  // if (preg_match("/^[0-9]/", $newCallsignUpper)) { $newCallsignUpperIRC = 'r'.$newCallsignUpper; } else { $newCallsignUpperIRC = $newCallsignUpper; }
	  $newCallsignUpperIRC = $newCallsignUpper;

	  $rollGATECALL = 'sudo sed -i "/gatewayCallsign=/c\\gatewayCallsign='.$newCallsignUpper.'" /etc/ircddbgateway';
	  $rollDPLUSLOGIN = 'sudo sed -i "/dplusLogin=/c\\dplusLogin='.str_pad($newCallsignUpper, 8, " ").'" /etc/ircddbgateway';
	  $rollDASHBOARDcall = 'sudo sed -i "/callsign=/c\\$callsign=\''.$newCallsignUpper.'\';" /var/www/dashboard/config/ircddblocal.php';
	  $rollTIMESERVERcall = 'sudo sed -i "/callsign=/c\\callsign='.$newCallsignUpper.'" /etc/timeserver';
	  $rollSTARNETSERVERcall = 'sudo sed -i "/callsign=/c\\callsign='.$newCallsignUpper.'" /etc/starnetserver';
	  $rollSTARNETSERVERirc = 'sudo sed -i "/ircddbUsername=/c\\ircddbUsername='.$newCallsignUpperIRC.'" /etc/starnetserver';

	  // Only roll ircDDBGateway Username if using OpenQuad
	  if ($configs['ircddbHostname'] == "rr.openquad.net") {
		  $rollIRCUSER = 'sudo sed -i "/ircddbUsername=/c\\ircddbUsername='.$newCallsignUpperIRC.'" /etc/ircddbgateway';
		  system($rollIRCUSER);
	  }

	  //if ( strlen($newCallsignUpper) < 6 ) { $configysfgateway['General']['Callsign'] = $newCallsignUpper."-1"; }
	  //else { $configysfgateway['General']['Callsign'] = $newCallsignUpper; }
	  $configysfgateway['General']['Callsign'] = $newCallsignUpper;

	  if (empty($_POST['APRSCallsign']) == TRUE )
		$configysfgateway['aprs.fi']['AprsCallsign'] = $newCallsignUpper;
	  else $configysfgateway['aprs.fi']['AprsCallsign'] = $_POST['APRSCallsign'];

	  $configmmdvm['General']['Callsign'] = $newCallsignUpper;
	  $configysfgateway['aprs.fi']['Password'] = aprspass($newCallsignUpper);
	  $configysfgateway['aprs.fi']['Description'] = $newCallsignUpper."_Pi-Star";
	  $configysf2nxdn['aprs.fi']['Password'] = aprspass($newCallsignUpper);
	  $configysf2nxdn['aprs.fi']['Description'] = $newCallsignUpper."_Pi-Star";
	  $configysf2nxdn['YSF Network']['Callsign'] = $newCallsignUpper;
	  $configysf2p25['aprs.fi']['Password'] = aprspass($newCallsignUpper);
	  $configysf2p25['aprs.fi']['Description'] = $newCallsignUpper."_Pi-Star";
	  $configysf2p25['YSF Network']['Callsign'] = $newCallsignUpper;
	  $configdmr2ysf['YSF Network']['Callsign'] = $newCallsignUpper;
	  $configp25gateway['General']['Callsign'] = $newCallsignUpper;
	  $confignxdngateway['aprs.fi']['Description'] = $newCallsignUpper."_Pi-Star";
	  $confignxdngateway['aprs.fi']['Password'] = aprspass($newCallsignUpper);
	  $confignxdngateway['General']['Callsign'] = $newCallsignUpper;
	  $configysfgateway['Info']['Name'] = $newCallsignUpper."_Pi-Star";
	  $configysf2nxdn['Info']['Description'] = $newCallsignUpper."_Pi-Star";
	  $configysf2p25['Info']['Description'] = $newCallsignUpper."_Pi-Star";

	  // If ircDDBGateway config supports APRS Password
	  if (isset($configs['aprsPassword'])) {
		  $rollircDDBGatewayAprsPassword = 'sudo sed -i "/aprsPassword=/c\\aprsPassword='.aprspass($newCallsignUpper).'" /etc/ircddbgateway';
		  system($rollircDDBGatewayAprsPassword);
	  }

	  system($rollGATECALL);
	 // system($rollIRCUSER);
	  system($rollDPLUSLOGIN);
	  system($rollDASHBOARDcall);
	  system($rollTIMESERVERcall);
	  system($rollSTARNETSERVERcall);
	  system($rollSTARNETSERVERirc);
	}

	// Set the P25 Startup Host
	if (empty($_POST['p25StartupHost']) != TRUE ) {
          $newP25StartupHost = strtoupper(escapeshellcmd($_POST['p25StartupHost']));
          if ($newP25StartupHost === "NONE") {
		  unset($configp25gateway['Network']['Startup']);
		  unset($configysf2p25['P25 Network']['StartupDstId']);
	  } else {
		  $configp25gateway['Network']['Startup'] = $newP25StartupHost;
		  $configysf2p25['P25 Network']['StartupDstId'] = $newP25StartupHost;
	  }
	}

	// Set P25 NAC
	if (empty($_POST['p25nac']) != TRUE ) {
	  $p25nacNew = strtolower(escapeshellcmd($_POST['p25nac']));
	  if (preg_match('/[a-f0-9]{3}/', $p25nacNew)) {
	    $configmmdvm['P25']['NAC'] = $p25nacNew;
	  }
	}

	// Set the NXDN Startup Host
	if (empty($_POST['nxdnStartupHost']) != TRUE ) {
	  $newNXDNStartupHost = strtoupper(escapeshellcmd($_POST['nxdnStartupHost']));
	  if (file_exists('/etc/nxdngateway')) {
		if ($newNXDNStartupHost === "NONE") {
			unset($confignxdngateway['Network']['Startup']);
		} else {
			$confignxdngateway['Network']['Startup'] = $newNXDNStartupHost;
	  	}
	  } else {
		$configmmdvm['NXDN Network']['GatewayAddress'] = $newNXDNStartupHost;
		$configmmdvm['NXDN Network']['GatewayPort'] = "41007";
	  }
	  $configysf2nxdn['NXDN Network']['StartupDstId'] = $newNXDNStartupHost;
	}

	// Set NXDN RAN
	if (empty($_POST['nxdnran']) != TRUE ) {
	  $nxdnranNew = strtolower(escapeshellcmd($_POST['nxdnran']));
	  $nxdnranNew = preg_replace('/[^0-9]/', '', $nxdnranNew);
	  if (($nxdnranNew >= 1) && ($nxdnranNew <= 64)) {
	      $configmmdvm['NXDN']['RAN'] = $nxdnranNew;
	  }
	}

	// Set the ysfgateway Startup Set
	$configysfgateway['YSF Network']['Enable'] = "1";
	if (empty($_POST['ysfESSId']) != TRUE ) {
		$configysfgateway['General']['Id'] = escapeshellcmd($_POST['ysfESSId']);
	}

	if (empty($_POST['ysfStartupHost']) != TRUE ) {
	   $newYSFStartupHostArr = explode(',', escapeshellcmd($_POST['ysfStartupHost']));
	   $newYSFStartupHost = strtoupper($newYSFStartupHostArr[1]);
	   if ($newYSFStartupHost == "none") { $configysfgateway['YSF Network']['Startup'] = "7"; }
	   else { $configysfgateway['YSF Network']['Startup'] = $newYSFStartupHostArr[0]; }
	}

	if (empty($_POST['dmrStartupHost']) != TRUE ) {
		$newDMRStartupHost = escapeshellcmd($_POST['dmrStartupHost']);
		if ($newDMRStartupHost == "none") { unset($configysfgateway['DMR Network']['Startup']); }
		else { $configysfgateway['DMR Network']['Startup'] = $newDMRStartupHost; }
	}

	if (empty($_POST['fcsStartupHost']) != TRUE ) {	
		if ($_POST['fcsStartupHost'] != "none") $newFCSStartupHost = substr($_POST['fcsStartupHost'],3);
		else $newFCSStartupHost = "00118";
		$configysfgateway['FCS Network']['Startup'] = $newFCSStartupHost;
	} else $configysfgateway['FCS Network']['Startup'] = "00118";

	if (empty($_POST['ysfStartupMode']) != TRUE ) {
		$newysfStartupMode = strtoupper(escapeshellcmd($_POST['ysfStartupMode']));
		$configysfgateway['Network']['Type'] = $newysfStartupMode;
		switch ($newysfStartupMode) {
			case "YSF":
				$configysfgateway['Network']['Startup'] = $newYSFStartupHost;
			break;
			case "DMR":
				$configysfgateway['Network']['Startup'] = $newDMRStartupHost;
			break;
			case "FCS":
				$configysfgateway['Network']['Startup'] = $newFCSStartupHost;
			break;
		}
	}	

	if ((empty($_POST['MMDVMModeFUSION']) != TRUE) && (escapeshellcmd($_POST['MMDVMModeFUSION']) == 'ON' ))  {
		if (empty($_POST['ysfgatewayYSFNetworkOptions']) != TRUE) {
			$newYCSOptions = $_POST['ysfgatewayYSFNetworkOptions'];
			$configysfgateway['YSF Network']['Options'] = '"'.$newYCSOptions.'"';
			if ($_POST['ysfgatewayYSFNetworkOptions'] == "") $configysfgateway['YSF Network']['Options'] = "";
		}

		if (empty($_POST['ysfgatewayFCSNetworkOptions']) != TRUE) {
			$newFCSOptions = $_POST['ysfgatewayFCSNetworkOptions'];
			$configysfgateway['FCS Network']['Options'] = '"'.$newFCSOptions.'"';
			if ($_POST['ysfgatewayFCSNetworkOptions'] == "") $configysfgateway['FCS Network']['Options'] = "";
		}

		if (empty($_POST['StartupDGID']) != TRUE ) {
			$configysfgateway['YSF Network']['StartupDGID'] = $_POST['StartupDGID'];
			if ($_POST['StartupDGID'] == "") $configysfgateway['YSF Network']['StartupDGID'] = "";
		}

		if (empty($_POST['BeaconTime']) != TRUE ) {
			$newBeaconTime = escapeshellcmd($_POST['BeaconTime']);
			$configysfgateway['General']['BeaconTime'] = $newBeaconTime;
			if ($_POST['BeaconTime'] == "0") $configysfgateway['General']['BeaconTime'] = "0";
		}
	
		if (empty($_POST['TimeoutTime']) != TRUE ) {
			$newTimeoutTime = escapeshellcmd($_POST['TimeoutTime']);
			$configysfgateway['Network']['InactivityTimeout'] = $newTimeoutTime;
			if ($_POST['TimeoutTime'] == "0") $configysfgateway['Network']['InactivityTimeout'] = "0";
		}
	}

	if (empty($_POST['DMREnable']) != TRUE ) {	
		if (escapeshellcmd($_POST['DMREnable']) == 'ON' )  { $configysfgateway['DMR Network']['Enable'] = "1"; }
		if (escapeshellcmd($_POST['DMREnable']) == 'OFF' ) { $configysfgateway['DMR Network']['Enable'] = "0"; }
	}	

	if (empty($_POST['FCSEnable']) != TRUE ) {	
		if (escapeshellcmd($_POST['FCSEnable']) == 'ON' )  { $configysfgateway['FCS Network']['Enable'] = "1"; }
		if (escapeshellcmd($_POST['FCSEnable']) == 'OFF' ) { $configysfgateway['FCS Network']['Enable'] = "0"; }
	}	

	if (empty($_POST['APRSEnable']) != TRUE ) {
		if (escapeshellcmd($_POST['APRSEnable']) == 'ON' )  { $configysfgateway['aprs.fi']['Enable'] = "1"; }
		if (escapeshellcmd($_POST['APRSEnable']) == 'OFF' ) { $configysfgateway['aprs.fi']['Enable'] = "0"; }
	}

	// Set YSFGateway to automatically pass through WiresX
	if (empty($_POST['wiresXCommandPassthrough']) != TRUE ) {
	  if (escapeshellcmd($_POST['wiresXCommandPassthrough']) == 'ON' )  { $configysfgateway['General']['WiresXCommandPassthrough'] = "1"; }
	  if (escapeshellcmd($_POST['wiresXCommandPassthrough']) == 'OFF' ) { $configysfgateway['General']['WiresXCommandPassthrough'] = "0"; }
	}

	// Remove hostfiles.ysfupper and use the new YSFGateway Feature
	if (empty($_POST['confHostFilesYSFUpper']) != TRUE ) {
		if (escapeshellcmd($_POST['confHostFilesYSFUpper']) == 'ON' )   { $configysfgateway['General']['WiresXMakeUpper'] = "1"; }
		if (escapeshellcmd($_POST['confHostFilesYSFUpper']) == 'OFF' )  { $configysfgateway['General']['WiresXMakeUpper'] = "0"; }
		if (file_exists('/etc/hostfiles.ysfupper')) { system('sudo rm -rf /etc/hostfiles.ysfupper'); }
	}

	// Set YSFGateway HotSpotFollow
	if (empty($_POST['HotSpotFollow']) != TRUE ) {
		if (escapeshellcmd($_POST['HotSpotFollow']) == 'ON' )  { $configysfgateway['aprs.fi']['HotSpotFollow'] = "1"; }
		if (escapeshellcmd($_POST['HotSpotFollow']) == 'OFF' ) { $configysfgateway['aprs.fi']['HotSpotFollow'] = "0"; }
	}

	

	if (empty($_POST['ysfdmrMasterHost']) != TRUE ) {
		$ysf2dmrMasterHostArr = explode(',', escapeshellcmd($_POST['ysfdmrMasterHost']));			
		$configysfgateway['DMR Network']['Address'] = $ysf2dmrMasterHostArr[0];
		$configysfgateway['DMR Network']['Password'] = '"'.$ysf2dmrMasterHostArr[1].'"';
		$configysfgateway['DMR Network']['Port'] = $ysf2dmrMasterHostArr[2];
		if (substr($ysf2dmrMasterHostArr[3], 0, 2) == "BM") {
			$configysfgateway['DMR Network']['Hosts'] = "/usr/local/etc/DMRHosts.txt";
			$configysfgateway['DMR Network']['EnableUnlink'] = "1";
			unset ($configysfgateway['DMR Network']['Options']);			 
		} elseif (substr($ysf2dmrMasterHostArr[3], 0, 2) == "AA") {
			$configysfgateway['DMR Network']['Local'] = "0";
			$configysfgateway['DMR Network']['Hosts'] = "/usr/local/etc/DMRPA7_Talkgroups.txt";
			$configysfgateway['DMR Network']['EnableUnlink'] = "0";
			if (empty($_POST['ysfgatewayNetworkOptions']) != TRUE ) {
				$ysf2dmrOptionsLineStripped = str_replace('"', "", $_POST['ysfgatewayNetworkOptions']);
				$configysfgateway['DMR Network']['Options'] = '"'.$ysf2dmrOptionsLineStripped.'"';
			 } else unset ($configysfgateway['DMR Network']['Options']);			
		} else {
			$configysfgateway['DMR Network']['Hosts'] = "/usr/local/etc/DMRP_Talkgroups.txt";
			$configysfgateway['DMR Network']['EnableUnlink'] = "0"; 
			if (empty($_POST['ysfgatewayNetworkOptions']) != TRUE ) {
				$ysf2dmrOptionsLineStripped = str_replace('"', "", $_POST['ysfgatewayNetworkOptions']);
				$configysfgateway['DMR Network']['Options'] = '"'.$ysf2dmrOptionsLineStripped.'"';
			 }
			 else {
				$configysfgateway['DMR Network']['Options'] = "\"StartRef=4370;RlinkTime=40;\"";
				$configysfgateway['DMR Network']['Startup'] = "4370";
			 }
		}

	}

	if (empty($_POST['dmrPassWord']) != TRUE ) {
		$newdmrPassWord = escapeshellcmd($_POST['dmrPassWord']);
		if (empty($_POST['ysfdmrMasterHost']) != TRUE ) {
			$ysf2dmrMasterHostArr = explode(',', escapeshellcmd($_POST['ysfdmrMasterHost']));					
			if (substr($ysf2dmrMasterHostArr[3], 0, 2) == "AA") {
				$newdmrPassWord = "passw0rd";
			}
		}
		$configysfgateway['DMR Network']['Password'] = $newdmrPassWord;
	} 	

	if (empty($_POST['APIKey']) != TRUE ) {
		$newAPIKey = escapeshellcmd($_POST['APIKey']);
		$configysfgateway['aprs.fi']['APIKey'] = $newAPIKey;
	}

	// Set the YSF2NXDN Master
	if (empty($_POST['ysf2nxdnStartupDstId']) != TRUE ) {
	  $configysf2nxdn['NXDN Network']['StartupDstId'] = escapeshellcmd($_POST['ysf2nxdnStartupDstId']);
	  if (file_exists('/etc/nxdngateway')) {
	    if (escapeshellcmd($_POST['ysf2nxdnStartupDstId']) === "none") {
	      unset($confignxdngateway['Network']['Startup']);
	    } else {
	      $confignxdngateway['Network']['Startup'] = escapeshellcmd($_POST['ysf2nxdnStartupDstId']);
	    }
	  }
	}

	// Set the YSF2NXDN Id
	if (empty($_POST['ysf2nxdnId']) != TRUE ) {
	  $configysf2nxdn['NXDN Network']['Id'] = preg_replace('/[^0-9]/', '', $_POST['ysf2nxdnId']);
	}

	// Set the YSF2P25 Master
	if (empty($_POST['ysf2p25StartupDstId']) != TRUE ) {
	  $newYSF2P25StartupHost = strtoupper(escapeshellcmd($_POST['ysf2p25StartupDstId']));

	  if ($newYSF2P25StartupHost === "NONE") {
		  unset($configp25gateway['Network']['Startup']);
		  unset($configysf2p25['P25 Network']['StartupDstId']);
	  } else {
		  $configp25gateway['Network']['Startup'] = $newYSF2P25StartupHost;
		  $configysf2p25['P25 Network']['StartupDstId'] = $newYSF2P25StartupHost;
	  }
	}

	// Set the YSF2P25 P25Id
	if (empty($_POST['ysf2p25Id']) != TRUE ) {
	  $configysf2p25['P25 Network']['Id'] = preg_replace('/[^0-9]/', '', $_POST['ysf2p25Id']);
	}

	// Set Duplex
	if (empty($_POST['trxMode']) != TRUE ) {
	  if ($configmmdvm['Info']['RXFrequency'] === $configmmdvm['Info']['TXFrequency'] && $_POST['trxMode'] == "DUPLEX" ) {
	    $configmmdvm['Info']['RXFrequency'] = $configmmdvm['Info']['TXFrequency'] - 1;
	    }
	  if ($configmmdvm['Info']['RXFrequency'] !== $configmmdvm['Info']['TXFrequency'] && $_POST['trxMode'] == "SIMPLEX" ) {
	    $configmmdvm['Info']['RXFrequency'] = $configmmdvm['Info']['TXFrequency'];
	    }
	  if ($_POST['trxMode'] == "DUPLEX") {
	    $configmmdvm['General']['Duplex'] = 1;
	    $configmmdvm['DMR Network']['Slot1'] = '1';
	    $configmmdvm['DMR Network']['Slot2'] = '1';
	  }
	  if ($_POST['trxMode'] == "SIMPLEX") {
	    $configmmdvm['General']['Duplex'] = 0;
	    $configmmdvm['DMR Network']['Slot1'] = '0';
	    $configmmdvm['DMR Network']['Slot2'] = '1';
	  }
	}

	// Set DMR / CCS7 ID
	if (empty($_POST['dmrId']) != TRUE ) {
	  $newPostDmrId = preg_replace('/[^0-9]/', '', $_POST['dmrId']);

	  $newPostDmrId = substr($newPostDmrId, 0, 7);

	  $configmmdvm['General']['Id'] = $newPostDmrId;
	  $configmmdvm['DMR']['Id'] = $newPostDmrId;
	  //$configysfgateway['General']['Id'] = $newPostDmrId;
	  $configdmrgateway['XLX Network']['Id'] = $newPostDmrId;
	  $configdmr2ysf['DMR Network']['Id'] = $newPostDmrId;
	  $configdmr2nxdn['DMR Network']['Id'] = $newPostDmrId;
	}

	// Set DMR Extended ID
	if (empty($_POST['dmrExtendedId']) != TRUE ) {
	  $newPostdmrExtendedId = preg_replace('/[^0-9]/', '', $_POST['dmrExtendedId']);
	  $configmmdvm['DMR']['Id'] = $configmmdvm['General']['Id'].$newPostdmrExtendedId;
	}

	// Set BrandMeister Extended ID
	if (empty($_POST['bmExtendedId']) != TRUE ) {
	  $newPostbmExtendedId = preg_replace('/[^0-9]/', '', $_POST['bmExtendedId']);
	  $configdmrgateway['DMR Network 1']['Id'] = $configmmdvm['General']['Id'].$newPostbmExtendedId;
	}

	// Set DMR Plus Extended ID
	if (empty($_POST['dmrPlusExtendedId']) != TRUE ) {
	  $newPostdmrPlusExtendedId = preg_replace('/[^0-9]/', '', $_POST['dmrPlusExtendedId']);
	  $configdmrgateway['DMR Network 2']['Id'] = $configmmdvm['General']['Id'].$newPostdmrPlusExtendedId;
	}

	// Set NXDN ID
	if (empty($_POST['nxdnId']) != TRUE ) {
	  $newPostNxdnId = preg_replace('/[^0-9]/', '', $_POST['nxdnId']);
	  $configmmdvm['NXDN']['Id'] = $newPostNxdnId;
	  if ($configmmdvm['NXDN']['Id'] > 65535) { unset($configmmdvm['NXDN']['Id']); }
	}

	// Set DMR Master Server
	if (empty($_POST['dmrMasterHost']) != TRUE ) {
	  $dmrMasterHostArr = explode(',', escapeshellcmd($_POST['dmrMasterHost']));
	  $configmmdvm['DMR Network']['RemoteAddress'] = $dmrMasterHostArr[0];
	  $configmmdvm['DMR Network']['Password'] = '"'.$dmrMasterHostArr[1].'"';
	  $configmmdvm['DMR Network']['RemotePort'] = $dmrMasterHostArr[2];
	  if (empty($_POST['bmHSSecurity']) != TRUE ) {
		  $configModem['BrandMeister']['Password'] = '"'.$_POST['bmHSSecurity'].'"';
		  if ($dmrMasterHostArr[0] != '127.0.0.1') { $configmmdvm['DMR Network']['Password'] = '"'.$_POST['bmHSSecurity'].'"'; }
	  } else {
		  unset ($configModem['BrandMeister']['Password']);
	  }

		if (substr($dmrMasterHostArr[3], 0, 2) == "BM") {
			unset ($configmmdvm['DMR Network']['Options']);
			unset ($configdmrgateway['DMR Network 2']['Options']);
			unset ($configmmdvm['DMR Network']['Local']);
		} elseif (substr($dmrMasterHostArr[3], 0, 2) == "AA") {
			$configmmdvm['DMR Network']['Password']="passw0rd";
			$configmmdvm['DMR Network']['Local']="0";
		}

		// DMR Gateway
		if ($dmrMasterHostArr[0] == '127.0.0.1' && $dmrMasterHostArr[2] == '62031') {
			unset ($configmmdvm['DMR Network']['Options']);
			// F1RMB: don't erase DMR Network 2::Options
			//unset ($configdmrgateway['DMR Network 2']['Options']);
			$configmmdvm['DMR Network']['Local'] = "62032";
			if (isset($configdmr2ysf['DMR Network']['LocalAddress'])) {
				$configdmr2ysf['DMR Network']['LocalAddress'] = "127.0.0.1";
			}
			if (isset($configdmr2nxdn['DMR Network']['LocalAddress'])) {
				$configdmr2nxdn['DMR Network']['LocalAddress'] = "127.0.0.1";
			}
		}

		// DMR2YSF
		if ($dmrMasterHostArr[0] == '127.0.0.2' && $dmrMasterHostArr[2] == '62033') {
			unset ($configmmdvm['DMR Network']['Options']);
			$configmmdvm['DMR Network']['Local'] = "62034";
			if (isset($configdmr2ysf['DMR Network']['LocalAddress'])) {
				$configdmr2ysf['DMR Network']['LocalAddress'] = "127.0.0.2";
			}
		}

		// DMR2NXDN
		if ($dmrMasterHostArr[0] == '127.0.0.3' && $dmrMasterHostArr[2] == '62035') {
			unset ($configmmdvm['DMR Network']['Options']);
			$configmmdvm['DMR Network']['Local'] = "62036";
			if (isset($configdmr2nxdn['DMR Network']['LocalAddress'])) {
				$configdmr2nxdn['DMR Network']['LocalAddress'] = "127.0.0.3";
			}
		}

		// Set the DMR+ Options= line
		if (substr($dmrMasterHostArr[3], 0, 4) == "DMR+") {
			unset ($configmmdvm['DMR Network']['Local']);
			if (empty($_POST['dmrNetworkOptions']) != TRUE ) {
				$dmrOptionsLineStripped = str_replace('"', "", $_POST['dmrNetworkOptions']);
				$configmmdvm['DMR Network']['Options'] = '"'.$dmrOptionsLineStripped.'"';
				$configdmrgateway['DMR Network 2']['Options'] = '"'.$dmrOptionsLineStripped.'"';
			}
			else {
				unset ($configmmdvm['DMR Network']['Options']);
				unset ($configdmrgateway['DMR Network 2']['Options']);
			}
		}
	}
	if (empty($_POST['dmrMasterHost']) == TRUE ) {
	    unset ($configmmdvm['DMR Network']['Options']);

	    // F1RMB: Keep DMR Network2::Options, if any
	    //unset ($configdmrgateway['DMR Network 2']['Options']);
	    if (empty($configdmrgateway['DMR Network 2']['Options']) == FALSE) {
		$dmrOptionsLineStripped = str_replace('"', "", $configdmrgateway['DMR Network 2']['Options']);
		$configdmrgateway['DMR Network 2']['Options'] = '"'.$dmrOptionsLineStripped.'"';
	    }
	}
	if (empty($_POST['dmrMasterHost1']) != TRUE ) {
	  $dmrMasterHostArr1 = explode(',', escapeshellcmd($_POST['dmrMasterHost1']));
	  $configdmrgateway['DMR Network 1']['Address'] = $dmrMasterHostArr1[0];
	  $configdmrgateway['DMR Network 1']['Password'] = '"'.$dmrMasterHostArr1[1].'"';
	  if (empty($_POST['bmHSSecurity']) != TRUE ) {
	    $configdmrgateway['DMR Network 1']['Password'] = '"'.$_POST['bmHSSecurity'].'"';
	  }
	  $configdmrgateway['DMR Network 1']['Port'] = $dmrMasterHostArr1[2];
	  $configdmrgateway['DMR Network 1']['Name'] = $dmrMasterHostArr1[3];
	}
	if (empty($_POST['dmrMasterHost2']) != TRUE ) {
	  $dmrMasterHostArr2 = explode(',', escapeshellcmd($_POST['dmrMasterHost2']));
	  $configdmrgateway['DMR Network 2']['Address'] = $dmrMasterHostArr2[0];
	  $configdmrgateway['DMR Network 2']['Password'] = '"'.$dmrMasterHostArr2[1].'"';
	  $configdmrgateway['DMR Network 2']['Port'] = $dmrMasterHostArr2[2];
	  $configdmrgateway['DMR Network 2']['Name'] = $dmrMasterHostArr2[3];
	  if (empty($_POST['dmrNetworkOptions']) != TRUE ) {
	    $dmrOptionsLineStripped = str_replace('"', "", $_POST['dmrNetworkOptions']);
	    unset ($configmmdvm['DMR Network']['Options']);
	    $configdmrgateway['DMR Network 2']['Options'] = '"'.$dmrOptionsLineStripped.'"';
	  }
	  else {
		unset ($configdmrgateway['DMR Network 2']['Options']);
	  }
	}
	if (empty($_POST['dmrMasterHost3']) != TRUE ) {
	  $dmrMasterHostArr3 = explode(',', escapeshellcmd($_POST['dmrMasterHost3']));
	  $configdmrgateway['XLX Network 1']['Address'] = $dmrMasterHostArr3[0];
	  $configdmrgateway['XLX Network 1']['Password'] = '"'.$dmrMasterHostArr3[1].'"';
	  $configdmrgateway['XLX Network 1']['Port'] = $dmrMasterHostArr3[2];
	  $configdmrgateway['XLX Network 1']['Name'] = $dmrMasterHostArr3[3];
	  $configdmrgateway['XLX Network']['Startup'] = substr($dmrMasterHostArr3[3], 4);
	}

	// XLX StartUp TG
	if (empty($_POST['dmrMasterHost3Startup']) != TRUE ) {
	  $dmrMasterHost3Startup = escapeshellcmd($_POST['dmrMasterHost3Startup']);
	  if ($dmrMasterHost3Startup != "None") {
	    $configdmrgateway['XLX Network 1']['Startup'] = $dmrMasterHost3Startup;
	  }
	  else { unset($configdmrgateway['XLX Network 1']['Startup']); }
	}

	// XLX Module Override
	if (empty($_POST['dmrMasterHost3StartupModule']) != TRUE ) {
	  $dmrMasterHost3StartupModule = escapeshellcmd($_POST['dmrMasterHost3StartupModule']);
	  if ($dmrMasterHost3StartupModule == "Default") {
	    unset($configdmrgateway['XLX Network']['Module']);
	  } else {
	    $configdmrgateway['XLX Network']['Module'] = $dmrMasterHost3StartupModule;
	  }
	}
	unset($configmmdvm['DMR Network']['JitterEnabled']);

	// Set Talker Alias Option
	if (empty($_POST['dmrEmbeddedLCOnly']) != TRUE ) {
	  if (escapeshellcmd($_POST['dmrEmbeddedLCOnly']) == 'ON' ) { $configmmdvm['DMR']['EmbeddedLCOnly'] = "1"; }
	  if (escapeshellcmd($_POST['dmrEmbeddedLCOnly']) == 'OFF' ) { $configmmdvm['DMR']['EmbeddedLCOnly'] = "0"; }
	}

	// Set Dump TA Data Option for GPS support
	if (empty($_POST['dmrDumpTAData']) != TRUE ) {
	  if (escapeshellcmd($_POST['dmrDumpTAData']) == 'ON' ) { $configmmdvm['DMR']['DumpTAData'] = "1"; }
	  if (escapeshellcmd($_POST['dmrDumpTAData']) == 'OFF' ) { $configmmdvm['DMR']['DumpTAData'] = "0"; }
	}

	// Set the XLX DMRGateway Master On or Off
	if (empty($_POST['dmrGatewayXlxEn']) != TRUE ) {
	  if (escapeshellcmd($_POST['dmrGatewayXlxEn']) == 'ON' ) { $configdmrgateway['XLX Network 1']['Enabled'] = "1"; $configdmrgateway['XLX Network']['Enabled'] = "1"; }
	  if (escapeshellcmd($_POST['dmrGatewayXlxEn']) == 'OFF' ) { $configdmrgateway['XLX Network 1']['Enabled'] = "0"; $configdmrgateway['XLX Network']['Enabled'] = "0"; }
	}

	// Set the DMRGateway Network 2 On or Off
	if (empty($_POST['dmrGatewayNet2En']) != TRUE ) {
	  if (escapeshellcmd($_POST['dmrGatewayNet2En']) == 'ON' ) { $configdmrgateway['DMR Network 2']['Enabled'] = "1"; }
	  if (escapeshellcmd($_POST['dmrGatewayNet2En']) == 'OFF' ) { $configdmrgateway['DMR Network 2']['Enabled'] = "0"; }
	}

	// Set the DMRGateway Network 1 On or Off
	if (empty($_POST['dmrGatewayNet1En']) != TRUE ) {
	  if (escapeshellcmd($_POST['dmrGatewayNet1En']) == 'ON' ) { $configdmrgateway['DMR Network 1']['Enabled'] = "1"; }
	  if (escapeshellcmd($_POST['dmrGatewayNet1En']) == 'OFF' ) { $configdmrgateway['DMR Network 1']['Enabled'] = "0"; }
	}

	// Remove old settings
	if (isset($configmmdvm['General']['ModeHang'])) { unset($configmmdvm['General']['ModeHang']); }
	if (isset($configdmrgateway['General']['Timeout'])) { unset($configdmrgateway['General']['Timeout']); }
	if (isset($configmmdvm['General']['RFModeHang'])) { $configmmdvm['General']['RFModeHang'] = 300; }
	if (isset($configmmdvm['General']['NetModeHang'])) { $configmmdvm['General']['NetModeHang'] = 300; }

	// Set DMR Hang Timers
	if (empty($_POST['dmrRfHangTime']) != TRUE ) {
	  $configmmdvm['DMR']['ModeHang'] = preg_replace('/[^0-9]/', '', $_POST['dmrRfHangTime']);
	  $configdmrgateway['General']['RFTimeout'] = preg_replace('/[^0-9]/', '', $_POST['dmrRfHangTime']);
	}
	if (empty($_POST['dmrNetHangTime']) != TRUE ) {
	  $configmmdvm['DMR Network']['ModeHang'] = preg_replace('/[^0-9]/', '', $_POST['dmrNetHangTime']);
	  $configdmrgateway['General']['NetTimeout'] = preg_replace('/[^0-9]/', '', $_POST['dmrNetHangTime']);
	}
	// Set D-Star Hang Timers
	if (empty($_POST['dstarRfHangTime']) != TRUE ) {
	  $configmmdvm['D-Star']['ModeHang'] = preg_replace('/[^0-9]/', '', $_POST['dstarRfHangTime']);
	}
	if (empty($_POST['dstarNetHangTime']) != TRUE ) {
	  $configmmdvm['D-Star Network']['ModeHang'] = preg_replace('/[^0-9]/', '', $_POST['dstarNetHangTime']);
	}
	// Set YSF Hang Timers
	if (empty($_POST['ysfRfHangTime']) != TRUE ) {
	  $configmmdvm['System Fusion']['ModeHang'] = preg_replace('/[^0-9]/', '', $_POST['ysfRfHangTime']);
	}
	if (empty($_POST['ysfNetHangTime']) != TRUE ) {
	  $configmmdvm['System Fusion Network']['ModeHang'] = preg_replace('/[^0-9]/', '', $_POST['ysfNetHangTime']);
	}
	// Set P25 Hang Timers
	if (empty($_POST['p25RfHangTime']) != TRUE ) {
	  $configmmdvm['P25']['ModeHang'] = preg_replace('/[^0-9]/', '', $_POST['p25RfHangTime']);
	}
	if (empty($_POST['p25NetHangTime']) != TRUE ) {
	  $configmmdvm['P25 Network']['ModeHang'] = preg_replace('/[^0-9]/', '', $_POST['p25NetHangTime']);
	}
	// Set NXDN Hang Timers
	if (empty($_POST['nxdnRfHangTime']) != TRUE ) {
	  $configmmdvm['NXDN']['ModeHang'] = preg_replace('/[^0-9]/', '', $_POST['nxdnRfHangTime']);
	}
	if (empty($_POST['nxdnNetHangTime']) != TRUE ) {
	  $configmmdvm['NXDN Network']['ModeHang'] = preg_replace('/[^0-9]/', '', $_POST['nxdnNetHangTime']);
	}

	// Set the hardware type
	if (empty($_POST['confHardware']) != TRUE ) {
	$confHardware = escapeshellcmd($_POST['confHardware']);
	$configModem['Modem']['Hardware'] = $confHardware;
	// Set the Start delay
	$rollDstarRepeaterStartDelay = 'sudo sed -i "/OnStartupSec=/c\\OnStartupSec=30" /lib/systemd/system/dstarrepeater.timer';
	$rollMMDVMHostStartDelay = 'sudo sed -i "/OnStartupSec=/c\\OnStartupSec=30" /lib/systemd/system/mmdvmhost.timer';
	// Turn on RPT1 Validation in DStarRepeater
	$rollRpt1Validation = 'sudo sed -i "/rpt1Validation=/c\\rpt1Validation=1" /etc/dstarrepeater';
	// Set Standard IP/Port for DStarRepeater/MMDVMHost
	$rollRepeaterAddress1 = 'sudo sed -i "/repeaterAddress1=/c\\repeaterAddress1=127.0.0.1" /etc/ircddbgateway';
	$rollRepeaterPort1 = 'sudo sed -i "/repeaterPort1=/c\\repeaterPort1=20011" /etc/ircddbgateway';

	  if ( $confHardware == 'idrp2c' ) {
	    $rollRepeaterType1 = 'sudo sed -i "/repeaterType1=/c\\repeaterType1=1" /etc/ircddbgateway';
	    $rollRepeaterAddress1 = 'sudo sed -i "/repeaterAddress1=/c\\repeaterAddress1=172.16.0.1" /etc/ircddbgateway';
	    $rollRepeaterPort1 = 'sudo sed -i "/repeaterPort1=/c\\repeaterPort1=20000" /etc/ircddbgateway';
	    system($rollRepeaterType1);
	    $testNeworkConfig = exec('grep "eth0:1" /etc/network/interfaces | wc -l');
	    if (substr($testNeworkConfig, 0, 1) === '0') {
	      system('sudo sed -i "$ a\ \\nauto eth0:1\\nallow-hotplug eth0:1\\niface eth0:1 inet static\\n    address 172.16.0.20\\n    netmask 255.255.255.0" /etc/network/interfaces');
	    }
	  }

	  if ( $confHardware == 'icomTerminalAuto' ) {
	    $rollModemType = 'sudo sed -i "/modemType=/c\\modemType=Icom Access Point\/Terminal Mode" /etc/dstarrepeater';
	    $rollIcomPort = 'sudo sed -i "/icomPort=/c\\icomPort=/dev/icom_ta" /etc/dstarrepeater';
	    $rollRpt1Validation = 'sudo sed -i "/rpt1Validation=/c\\rpt1Validation=0" /etc/dstarrepeater';
	    system($rollModemType);
	    system($rollIcomPort);
	  }

	  if ( $confHardware == 'dvmpis' ) {
	    $rollModemType = 'sudo sed -i "/modemType=/c\\modemType=DVMEGA" /etc/dstarrepeater';
	    $rollDVMegaPort = 'sudo sed -i "/dvmegaPort=/c\\dvmegaPort=/dev/ttyAMA0" /etc/dstarrepeater';
	    $rollDVMegaVariant = 'sudo sed -i "/dvmegaVariant=/c\\dvmegaVariant=2" /etc/dstarrepeater';
	    $configmmdvm['Modem']['Port'] = "/dev/ttyAMA0";
	    $rollRepeaterType1 = 'sudo sed -i "/repeaterType1=/c\\repeaterType1=0" /etc/ircddbgateway';
	    system($rollModemType);
	    system($rollDVMegaPort);
	    system($rollDVMegaVariant);
	    system($rollRepeaterType1);
	    $configmmdvm['General']['Duplex'] = 0;
	    $configmmdvm['DMR Network']['Slot1'] = 0;
	  }

	  if ( $confHardware == 'dvmpid' ) {
	    $rollModemType = 'sudo sed -i "/modemType=/c\\modemType=DVMEGA" /etc/dstarrepeater';
	    $rollDVMegaPort = 'sudo sed -i "/dvmegaPort=/c\\dvmegaPort=/dev/ttyAMA0" /etc/dstarrepeater';
	    $rollDVMegaVariant = 'sudo sed -i "/dvmegaVariant=/c\\dvmegaVariant=3" /etc/dstarrepeater';
	    $configmmdvm['Modem']['Port'] = "/dev/ttyAMA0";
	    $rollRepeaterType1 = 'sudo sed -i "/repeaterType1=/c\\repeaterType1=0" /etc/ircddbgateway';
	    system($rollModemType);
	    system($rollDVMegaPort);
	    system($rollDVMegaVariant);
	    system($rollRepeaterType1);
	    $configmmdvm['General']['Duplex'] = 0;
	    $configmmdvm['DMR Network']['Slot1'] = 0;
	  }

	  if ( $confHardware == 'dvmuadu' ) {
	    $rollModemType = 'sudo sed -i "/modemType=/c\\modemType=DVMEGA" /etc/dstarrepeater';
	    $rollDVMegaPort = 'sudo sed -i "/dvmegaPort=/c\\dvmegaPort=/dev/ttyUSB0" /etc/dstarrepeater';
	    $rollDVMegaVariant = 'sudo sed -i "/dvmegaVariant=/c\\dvmegaVariant=3" /etc/dstarrepeater';
            $configmmdvm['Modem']['Port'] = "/dev/ttyUSB0";
	    $rollRepeaterType1 = 'sudo sed -i "/repeaterType1=/c\\repeaterType1=0" /etc/ircddbgateway';
	    system($rollModemType);
	    system($rollDVMegaPort);
	    system($rollDVMegaVariant);
	    system($rollRepeaterType1);
	    $configmmdvm['General']['Duplex'] = 0;
	    $configmmdvm['DMR Network']['Slot1'] = 0;
	  }

	  if ( $confHardware == 'dvmuada' ) {
	    $rollModemType = 'sudo sed -i "/modemType=/c\\modemType=DVMEGA" /etc/dstarrepeater';
	    $rollDVMegaPort = 'sudo sed -i "/dvmegaPort=/c\\dvmegaPort=/dev/ttyACM0" /etc/dstarrepeater';
	    $rollDVMegaVariant = 'sudo sed -i "/dvmegaVariant=/c\\dvmegaVariant=3" /etc/dstarrepeater';
            $configmmdvm['Modem']['Port'] = "/dev/ttyACM0";
	    $rollRepeaterType1 = 'sudo sed -i "/repeaterType1=/c\\repeaterType1=0" /etc/ircddbgateway';
	    system($rollModemType);
	    system($rollDVMegaPort);
	    system($rollDVMegaVariant);
	    system($rollRepeaterType1);
	    $configmmdvm['General']['Duplex'] = 0;
	    $configmmdvm['DMR Network']['Slot1'] = 0;
	  }

	  if ( $confHardware == 'dvmbss' ) {
	    $rollModemType = 'sudo sed -i "/modemType=/c\\modemType=DVMEGA" /etc/dstarrepeater';
	    $rollDVMegaPort = 'sudo sed -i "/dvmegaPort=/c\\dvmegaPort=/dev/ttyUSB0" /etc/dstarrepeater';
	    $rollDVMegaVariant = 'sudo sed -i "/dvmegaVariant=/c\\dvmegaVariant=2" /etc/dstarrepeater';
	    $rollDstarRepeaterStartDelay = 'sudo sed -i "/OnStartupSec=/c\\OnStartupSec=60" /lib/systemd/system/dstarrepeater.timer';
	    $rollMMDVMHostStartDelay = 'sudo sed -i "/OnStartupSec=/c\\OnStartupSec=60" /lib/systemd/system/mmdvmhost.timer';
            $configmmdvm['Modem']['Port'] = "/dev/ttyUSB0";
	    $rollRepeaterType1 = 'sudo sed -i "/repeaterType1=/c\\repeaterType1=0" /etc/ircddbgateway';
	    system($rollModemType);
	    system($rollDVMegaPort);
	    system($rollDVMegaVariant);
	    system($rollRepeaterType1);
	    $configmmdvm['General']['Duplex'] = 0;
	    $configmmdvm['DMR Network']['Slot1'] = 0;
	  }

	  if ( $confHardware == 'dvmbsd' ) {
	    $rollModemType = 'sudo sed -i "/modemType=/c\\modemType=DVMEGA" /etc/dstarrepeater';
	    $rollDVMegaPort = 'sudo sed -i "/dvmegaPort=/c\\dvmegaPort=/dev/ttyUSB0" /etc/dstarrepeater';
	    $rollDVMegaVariant = 'sudo sed -i "/dvmegaVariant=/c\\dvmegaVariant=3" /etc/dstarrepeater';
	    $rollDstarRepeaterStartDelay = 'sudo sed -i "/OnStartupSec=/c\\OnStartupSec=60" /lib/systemd/system/dstarrepeater.timer';
	    $rollMMDVMHostStartDelay = 'sudo sed -i "/OnStartupSec=/c\\OnStartupSec=60" /lib/systemd/system/mmdvmhost.timer';
            $configmmdvm['Modem']['Port'] = "/dev/ttyUSB0";
	    $rollRepeaterType1 = 'sudo sed -i "/repeaterType1=/c\\repeaterType1=0" /etc/ircddbgateway';
	    system($rollModemType);
	    system($rollDVMegaPort);
	    system($rollDVMegaVariant);
	    system($rollRepeaterType1);
	    $configmmdvm['General']['Duplex'] = 0;
	    $configmmdvm['DMR Network']['Slot1'] = 0;
	  }

	  if ( $confHardware == 'dvmuagmsku' ) {
	    $rollModemType = 'sudo sed -i "/modemType=/c\\modemType=DVMEGA" /etc/dstarrepeater';
	    $rollDVMegaPort = 'sudo sed -i "/dvmegaPort=/c\\dvmegaPort=/dev/ttyUSB0" /etc/dstarrepeater';
	    $rollDVMegaVariant = 'sudo sed -i "/dvmegaVariant=/c\\dvmegaVariant=0" /etc/dstarrepeater';
            $configmmdvm['Modem']['Port'] = "/dev/ttyUSB0";
	    $rollRepeaterType1 = 'sudo sed -i "/repeaterType1=/c\\repeaterType1=0" /etc/ircddbgateway';
	    system($rollModemType);
	    system($rollDVMegaPort);
	    system($rollDVMegaVariant);
	    system($rollRepeaterType1);
	  }

	  if ( $confHardware == 'dvmuagmska' ) {
	    $rollModemType = 'sudo sed -i "/modemType=/c\\modemType=DVMEGA" /etc/dstarrepeater';
	    $rollDVMegaPort = 'sudo sed -i "/dvmegaPort=/c\\dvmegaPort=/dev/ttyACM0" /etc/dstarrepeater';
	    $rollDVMegaVariant = 'sudo sed -i "/dvmegaVariant=/c\\dvmegaVariant=0" /etc/dstarrepeater';
            $configmmdvm['Modem']['Port'] = "/dev/ttyACM0";
	    $rollRepeaterType1 = 'sudo sed -i "/repeaterType1=/c\\repeaterType1=0" /etc/ircddbgateway';
	    system($rollModemType);
	    system($rollDVMegaPort);
	    system($rollDVMegaVariant);
	    system($rollRepeaterType1);
	  }

	  if ( $confHardware == 'dvrptr1' ) {
	    $rollModemType = 'sudo sed -i "/modemType=/c\\modemType=DV-RPTR V1" /etc/dstarrepeater';
	    $rollDVRPTRPort = 'sudo sed -i "/dvrptr1Port=/c\\dvrptr1Port=/dev/ttyACM0" /etc/dstarrepeater';
            $configmmdvm['Modem']['Port'] = "/dev/ttyACM0";
	    $rollRepeaterType1 = 'sudo sed -i "/repeaterType1=/c\\repeaterType1=0" /etc/ircddbgateway';
	    system($rollModemType);
	    system($rollDVRPTRPort);
	    system($rollRepeaterType1);
	  }

	  if ( $confHardware == 'dvrptr2' ) {
	    $rollModemType = 'sudo sed -i "/modemType=/c\\modemType=DV-RPTR V2" /etc/dstarrepeater';
	    $rollDVRPTRPort = 'sudo sed -i "/dvrptr1Port=/c\\dvrptr1Port=/dev/ttyACM0" /etc/dstarrepeater';
            $configmmdvm['Modem']['Port'] = "/dev/ttyACM0";
	    $rollRepeaterType1 = 'sudo sed -i "/repeaterType1=/c\\repeaterType1=0" /etc/ircddbgateway';
	    system($rollModemType);
	    system($rollDVRPTRPort);
	    system($rollRepeaterType1);
	  }

	  if ( $confHardware == 'dvrptr3' ) {
	    $rollModemType = 'sudo sed -i "/modemType=/c\\modemType=DV-RPTR V3" /etc/dstarrepeater';
	    $rollDVRPTRPort = 'sudo sed -i "/dvrptr1Port=/c\\dvrptr1Port=/dev/ttyACM0" /etc/dstarrepeater';
            $configmmdvm['Modem']['Port'] = "/dev/ttyACM0";
	    $rollRepeaterType1 = 'sudo sed -i "/repeaterType1=/c\\repeaterType1=0" /etc/ircddbgateway';
	    system($rollModemType);
	    system($rollDVRPTRPort);
	    system($rollRepeaterType1);
	  }

	  if ( $confHardware == 'gmsk_modem' ) {
	    $rollModemType = 'sudo sed -i "/modemType=/c\\modemType=GMSK Modem" /etc/dstarrepeater';
	    system($rollModemType);
	    $rollRepeaterType1 = 'sudo sed -i "/repeaterType1=/c\\repeaterType1=0" /etc/ircddbgateway';
	    system($rollRepeaterType1);
	  }

	  if ( $confHardware == 'dvap' ) {
	    $rollModemType = 'sudo sed -i "/modemType=/c\\modemType=DVAP" /etc/dstarrepeater';
            $configmmdvm['Modem']['Port'] = "/dev/ttyUSB0";
	    $rollRepeaterType1 = 'sudo sed -i "/repeaterType1=/c\\repeaterType1=0" /etc/ircddbgateway';
	    system($rollModemType);
	    system($rollRepeaterType1);
	  }

	  if ( $confHardware == 'zumspotlibre' ) {
	    $rollModemType = 'sudo sed -i "/modemType=/c\\modemType=MMDVM" /etc/dstarrepeater';
	    $rollRepeaterType1 = 'sudo sed -i "/repeaterType1=/c\\repeaterType1=0" /etc/ircddbgateway';
	    system($rollModemType);
	    system($rollRepeaterType1);
	    $configmmdvm['Modem']['Port'] = "/dev/ttyACM0";
	    $configmmdvm['General']['Duplex'] = 0;
	    $configmmdvm['DMR Network']['Slot1'] = 0;
	  }

	  if ( $confHardware == 'zumspotusb' ) {
	    $rollModemType = 'sudo sed -i "/modemType=/c\\modemType=MMDVM" /etc/dstarrepeater';
	    $rollRepeaterType1 = 'sudo sed -i "/repeaterType1=/c\\repeaterType1=0" /etc/ircddbgateway';
	    system($rollModemType);
	    system($rollRepeaterType1);
	    $configmmdvm['Modem']['Port'] = "/dev/ttyACM0";
	    $configmmdvm['General']['Duplex'] = 0;
	    $configmmdvm['DMR Network']['Slot1'] = 0;
	  }

	  if ( $confHardware == 'zumspotgpio' ) {
	    $rollModemType = 'sudo sed -i "/modemType=/c\\modemType=MMDVM" /etc/dstarrepeater';
	    $rollRepeaterType1 = 'sudo sed -i "/repeaterType1=/c\\repeaterType1=0" /etc/ircddbgateway';
	    system($rollModemType);
	    system($rollRepeaterType1);
	    $configmmdvm['Modem']['Port'] = "/dev/ttyAMA0";
	    $configmmdvm['General']['Duplex'] = 0;
	    $configmmdvm['DMR Network']['Slot1'] = 0;
	  }

	  if ( $confHardware == 'zumspotdualgpio' ) {
	    $rollModemType = 'sudo sed -i "/modemType=/c\\modemType=MMDVM" /etc/dstarrepeater';
	    $rollRepeaterType1 = 'sudo sed -i "/repeaterType1=/c\\repeaterType1=0" /etc/ircddbgateway';
	    system($rollModemType);
	    system($rollRepeaterType1);
	    $configmmdvm['Modem']['Port'] = "/dev/ttyAMA0";
	    $configmmdvm['General']['Duplex'] = 0;
	    $configmmdvm['DMR Network']['Slot1'] = 0;
	  }

	  if ( $confHardware == 'zumspotduplexgpio' ) {
	    $rollModemType = 'sudo sed -i "/modemType=/c\\modemType=MMDVM" /etc/dstarrepeater';
	    $rollRepeaterType1 = 'sudo sed -i "/repeaterType1=/c\\repeaterType1=0" /etc/ircddbgateway';
	    system($rollModemType);
	    system($rollRepeaterType1);
	    $configmmdvm['Modem']['Port'] = "/dev/ttyAMA0";
	    $configmmdvm['General']['Duplex'] = 1;
	  }

          if ( $confHardware == 'zumradiopiusb' ) {
            $rollModemType = 'sudo sed -i "/modemType=/c\\modemType=MMDVM" /etc/dstarrepeater';
            $rollRepeaterType1 = 'sudo sed -i "/repeaterType1=/c\\repeaterType1=0" /etc/ircddbgateway';
            system($rollRepeaterType1);
            $configmmdvm['Modem']['Port'] = "/dev/ttyACM0";
            $configmmdvm['General']['Duplex'] = 0;
            $configmmdvm['DMR Network']['Slot1'] = 0;
          }

	  if ( $confHardware == 'zumradiopigpio' ) {
	    $rollModemType = 'sudo sed -i "/modemType=/c\\modemType=MMDVM" /etc/dstarrepeater';
	    $rollMMDVMPort = 'sudo sed -i "/mmdvmPort=/c\\mmdvmPort=/dev/ttyAMA0" /etc/dstarrepeater';
	    $rollRepeaterType1 = 'sudo sed -i "/repeaterType1=/c\\repeaterType1=0" /etc/ircddbgateway';
	    system($rollModemType);
	    system($rollMMDVMPort);
	    system($rollRepeaterType1);
	    $configmmdvm['Modem']['Port'] = "/dev/ttyAMA0";
	  }

	  if ( $confHardware == 'zum' ) {
	    $rollModemType = 'sudo sed -i "/modemType=/c\\modemType=MMDVM" /etc/dstarrepeater';
	    $rollMMDVMPort = 'sudo sed -i "/mmdvmPort=/c\\mmdvmPort=/dev/ttyACM0" /etc/dstarrepeater';
	    $rollRepeaterType1 = 'sudo sed -i "/repeaterType1=/c\\repeaterType1=0" /etc/ircddbgateway';
	    system($rollModemType);
	    system($rollMMDVMPort);
	    system($rollRepeaterType1);
            $configmmdvm['Modem']['Port'] = "/dev/ttyACM0";
	  }

	  if ( $confHardware == 'stm32dvm' ) {
	    $rollModemType = 'sudo sed -i "/modemType=/c\\modemType=MMDVM" /etc/dstarrepeater';
	    $rollMMDVMPort = 'sudo sed -i "/mmdvmPort=/c\\mmdvmPort=/dev/ttyAMA0" /etc/dstarrepeater';
	    $rollRepeaterType1 = 'sudo sed -i "/repeaterType1=/c\\repeaterType1=0" /etc/ircddbgateway';
	    system($rollModemType);
	    system($rollMMDVMPort);
	    system($rollRepeaterType1);
	    $configmmdvm['Modem']['Port'] = "/dev/ttyAMA0";
	  }

	  if ( $confHardware == 'stm32usb' ) {
	    $rollModemType = 'sudo sed -i "/modemType=/c\\modemType=MMDVM" /etc/dstarrepeater';
	    $rollMMDVMPort = 'sudo sed -i "/mmdvmPort=/c\\mmdvmPort=/dev/ttyUSB0" /etc/dstarrepeater';
	    $rollRepeaterType1 = 'sudo sed -i "/repeaterType1=/c\\repeaterType1=0" /etc/ircddbgateway';
	    system($rollModemType);
	    system($rollMMDVMPort);
	    system($rollRepeaterType1);
	    $configmmdvm['Modem']['Port'] = "/dev/ttyUSB0";
	  }

	  if ( $confHardware == 'f4mgpio' ) {
	    $rollModemType = 'sudo sed -i "/modemType=/c\\modemType=MMDVM" /etc/dstarrepeater';
	    $rollMMDVMPort = 'sudo sed -i "/mmdvmPort=/c\\mmdvmPort=/dev/ttyAMA0" /etc/dstarrepeater';
	    $rollRepeaterType1 = 'sudo sed -i "/repeaterType1=/c\\repeaterType1=0" /etc/ircddbgateway';
	    system($rollModemType);
	    system($rollMMDVMPort);
	    system($rollRepeaterType1);
	    $configmmdvm['Modem']['Port'] = "/dev/ttyAMA0";
	  }

	  if ( $confHardware == 'f4mf7m' ) {
	    $rollModemType = 'sudo sed -i "/modemType=/c\\modemType=MMDVM" /etc/dstarrepeater';
	    $rollRepeaterType1 = 'sudo sed -i "/repeaterType1=/c\\repeaterType1=0" /etc/ircddbgateway';
	    system($rollModemType);
	    system($rollRepeaterType1);
	    $configmmdvm['Modem']['Port'] = "/dev/ttyUSB0";
	    $configmmdvm['General']['Duplex'] = 1;
	  }

	  if ( $confHardware == 'mmdvmhshat' ) {
	    $rollModemType = 'sudo sed -i "/modemType=/c\\modemType=MMDVM" /etc/dstarrepeater';
	    $rollRepeaterType1 = 'sudo sed -i "/repeaterType1=/c\\repeaterType1=0" /etc/ircddbgateway';
	    system($rollModemType);
	    system($rollRepeaterType1);
	    $configmmdvm['Modem']['Port'] = "/dev/ttyAMA0";
	    $configmmdvm['General']['Duplex'] = 0;
	    $configmmdvm['DMR Network']['Slot1'] = 0;
	  }

	  if ( $confHardware == 'mmdvmhshatambe' ) {
	    $rollModemType = 'sudo sed -i "/modemType=/c\\modemType=MMDVM" /etc/dstarrepeater';
	    $rollRepeaterType1 = 'sudo sed -i "/repeaterType1=/c\\repeaterType1=0" /etc/ircddbgateway';
	    system($rollModemType);
	    system($rollRepeaterType1);
	    $configmmdvm['Modem']['Port'] = "/dev/ttySC0";
	    $configmmdvm['General']['Duplex'] = 0;
	    $configmmdvm['DMR Network']['Slot1'] = 0;
	  }

	  if ( $confHardware == 'mmdvmhsdualhatgpio' ) {
	    $rollModemType = 'sudo sed -i "/modemType=/c\\modemType=MMDVM" /etc/dstarrepeater';
	    $rollRepeaterType1 = 'sudo sed -i "/repeaterType1=/c\\repeaterType1=0" /etc/ircddbgateway';
	    system($rollModemType);
	    system($rollRepeaterType1);
	    $configmmdvm['Modem']['Port'] = "/dev/ttyAMA0";
	    $configmmdvm['General']['Duplex'] = 1;
	  }

	  if ( $confHardware == 'mmdvmhsdualhatusb' ) {
	    $rollModemType = 'sudo sed -i "/modemType=/c\\modemType=MMDVM" /etc/dstarrepeater';
	    $rollRepeaterType1 = 'sudo sed -i "/repeaterType1=/c\\repeaterType1=0" /etc/ircddbgateway';
	    system($rollModemType);
	    system($rollRepeaterType1);
	    $configmmdvm['Modem']['Port'] = "/dev/ttyACM0";
	    $configmmdvm['General']['Duplex'] = 1;
	  }

	  if ( $confHardware == 'mmdvmrpthat' ) {
	    $rollModemType = 'sudo sed -i "/modemType=/c\\modemType=MMDVM" /etc/dstarrepeater';
	    $rollRepeaterType1 = 'sudo sed -i "/repeaterType1=/c\\repeaterType1=0" /etc/ircddbgateway';
	    system($rollModemType);
	    system($rollRepeaterType1);
	    $configmmdvm['Modem']['Port'] = "/dev/ttyAMA0";
	    $configmmdvm['General']['Duplex'] = 1;
	  }

	  if ( $confHardware == 'mmdvmmdohat' ) {
	    $rollModemType = 'sudo sed -i "/modemType=/c\\modemType=MMDVM" /etc/dstarrepeater';
	    $rollRepeaterType1 = 'sudo sed -i "/repeaterType1=/c\\repeaterType1=0" /etc/ircddbgateway';
	    system($rollModemType);
	    system($rollRepeaterType1);
	    $configmmdvm['Modem']['Port'] = "/dev/ttyAMA0";
	    $configmmdvm['General']['Duplex'] = 0;
	    $configmmdvm['DMR Network']['Slot1'] = 0;
	  }

	  if ( $confHardware == 'mmdvmvyehat' ) {
	    $rollModemType = 'sudo sed -i "/modemType=/c\\modemType=MMDVM" /etc/dstarrepeater';
	    $rollRepeaterType1 = 'sudo sed -i "/repeaterType1=/c\\repeaterType1=0" /etc/ircddbgateway';
	    system($rollModemType);
	    system($rollRepeaterType1);
	    $configmmdvm['Modem']['Port'] = "/dev/ttyAMA0";
	    $configmmdvm['General']['Duplex'] = 0;
	    $configmmdvm['DMR Network']['Slot1'] = 0;
	  }

	  if ( $confHardware == 'mmdvmvyehatdual' ) {
	    $rollModemType = 'sudo sed -i "/modemType=/c\\modemType=MMDVM" /etc/dstarrepeater';
	    $rollRepeaterType1 = 'sudo sed -i "/repeaterType1=/c\\repeaterType1=0" /etc/ircddbgateway';
	    system($rollModemType);
	    system($rollRepeaterType1);
	    $configmmdvm['Modem']['Port'] = "/dev/ttyAMA0";
	    $configmmdvm['General']['Duplex'] = 1;
	  }

	  if ( $confHardware == 'mnnano-spot' ) {
	    $rollModemType = 'sudo sed -i "/modemType=/c\\modemType=MMDVM" /etc/dstarrepeater';
	    $rollRepeaterType1 = 'sudo sed -i "/repeaterType1=/c\\repeaterType1=0" /etc/ircddbgateway';
	    system($rollModemType);
	    system($rollRepeaterType1);
	    $configmmdvm['Modem']['Port'] = "/dev/ttyAMA0";
	    $configmmdvm['General']['Duplex'] = 0;
	    $configmmdvm['DMR Network']['Slot1'] = 0;
	  }

	  if ( $confHardware == 'mnnano-teensy' ) {
	    $rollModemType = 'sudo sed -i "/modemType=/c\\modemType=MMDVM" /etc/dstarrepeater';
	    $rollMMDVMPort = 'sudo sed -i "/mmdvmPort=/c\\mmdvmPort=/dev/ttyUSB0" /etc/dstarrepeater';
	    $rollRepeaterType1 = 'sudo sed -i "/repeaterType1=/c\\repeaterType1=0" /etc/ircddbgateway';
	    system($rollModemType);
	    system($rollMMDVMPort);
	    system($rollRepeaterType1);
	    $configmmdvm['Modem']['Port'] = "/dev/ttyUSB0";
	    $configmmdvm['General']['Duplex'] = 0;
	    $configmmdvm['DMR Network']['Slot1'] = 0;
	  }

	  if ( $confHardware == 'nanodv' ) {
	    $rollModemType = 'sudo sed -i "/modemType=/c\\modemType=MMDVM" /etc/dstarrepeater';
	    $rollRepeaterType1 = 'sudo sed -i "/repeaterType1=/c\\repeaterType1=0" /etc/ircddbgateway';
	    system($rollModemType);
	    system($rollRepeaterType1);
	    $configmmdvm['Modem']['Port'] = "/dev/ttyAMA0";
	    $configmmdvm['General']['Duplex'] = 0;
	    $configmmdvm['DMR Network']['Slot1'] = 0;
	  }

	  if ( $confHardware == 'nanodvusb' ) {
	    $rollModemType = 'sudo sed -i "/modemType=/c\\modemType=MMDVM" /etc/dstarrepeater';
	    $rollRepeaterType1 = 'sudo sed -i "/repeaterType1=/c\\repeaterType1=0" /etc/ircddbgateway';
	    system($rollModemType);
	    system($rollRepeaterType1);
	    $configmmdvm['Modem']['Port'] = "/dev/ttyACM0";
	    $configmmdvm['General']['Duplex'] = 0;
	    $configmmdvm['DMR Network']['Slot1'] = 0;
	  }

	  if ( $confHardware == 'dvmpicast' ) {
	    $rollModemType = 'sudo sed -i "/modemType=/c\\modemType=DVMEGA" /etc/dstarrepeater';
	    $rollDVMegaPort = 'sudo sed -i "/dvmegaPort=/c\\dvmegaPort=/dev/ttyAMA0" /etc/dstarrepeater';
	    $rollDVMegaVariant = 'sudo sed -i "/dvmegaVariant=/c\\dvmegaVariant=2" /etc/dstarrepeater';
	    $rollRepeaterType1 = 'sudo sed -i "/repeaterType1=/c\\repeaterType1=0" /etc/ircddbgateway';
	    $configmmdvm['Modem']['Port'] = "/dev/ttyAMA0";
	    system($rollModemType);
	    system($rollDVMegaPort);
	    system($rollDVMegaVariant);
	    system($rollRepeaterType1);
	    $configmmdvm['General']['Duplex'] = 0;
	    $configmmdvm['DMR Network']['Slot1'] = 0;
	  }

	  if ( $confHardware == 'dvmpicasths' ) {
	    $rollModemType = 'sudo sed -i "/modemType=/c\\modemType=DVMEGA" /etc/dstarrepeater';
	    $rollDVMegaPort = 'sudo sed -i "/dvmegaPort=/c\\dvmegaPort=/dev/ttyS2" /etc/dstarrepeater';
	    $rollDVMegaVariant = 'sudo sed -i "/dvmegaVariant=/c\\dvmegaVariant=3" /etc/dstarrepeater';
	    $rollRepeaterType1 = 'sudo sed -i "/repeaterType1=/c\\repeaterType1=0" /etc/ircddbgateway';
	    $configmmdvm['Modem']['Port'] = "/dev/ttyS2";
	    system($rollModemType);
	    system($rollDVMegaPort);
	    system($rollDVMegaVariant);
	    system($rollRepeaterType1);
	    $configmmdvm['General']['Duplex'] = 0;
	    $configmmdvm['DMR Network']['Slot1'] = 0;
	  }

	  if ( $confHardware == 'dvmpicasthd' ) {
	    $rollModemType = 'sudo sed -i "/modemType=/c\\modemType=DVMEGA" /etc/dstarrepeater';
	    $rollDVMegaPort = 'sudo sed -i "/dvmegaPort=/c\\dvmegaPort=/dev/ttyS2" /etc/dstarrepeater';
	    $rollDVMegaVariant = 'sudo sed -i "/dvmegaVariant=/c\\dvmegaVariant=3" /etc/dstarrepeater';
	    $rollRepeaterType1 = 'sudo sed -i "/repeaterType1=/c\\repeaterType1=0" /etc/ircddbgateway';
	    $configmmdvm['Modem']['Port'] = "/dev/ttyS2";
	    system($rollModemType);
	    system($rollDVMegaPort);
	    system($rollDVMegaVariant);
	    system($rollRepeaterType1);
	    $configmmdvm['General']['Duplex'] = 0;
	    $configmmdvm['DMR Network']['Slot1'] = 0;
	  }
	  
	  if ( $confHardware == 'opengd77' ) {
	    $rollModemType = 'sudo sed -i "/modemType=/c\\modemType=MMDVM" /etc/dstarrepeater';
	    $rollRepeaterType1 = 'sudo sed -i "/repeaterType1=/c\\repeaterType1=0" /etc/ircddbgateway';
	    system($rollModemType);
	    system($rollRepeaterType1);
	    $configmmdvm['Modem']['Port'] = "/dev/ttyACM0";
	    $configmmdvm['General']['Duplex'] = 0;
	    $configmmdvm['DMR Network']['Slot1'] = 0;

	    if ((empty($_POST['mmdvmDisplayType']) == TRUE) || (escapeshellcmd($_POST['mmdvmDisplayType']) == "None"))
	    {
		$configmmdvm['General']['Display'] = "CAST";
		unset($_POST['mmdvmDisplayType']);
	    }
	  }

	  // Set the Service start delay
	  system($rollDstarRepeaterStartDelay);
	  system($rollMMDVMHostStartDelay);
	  // Turn on RPT1 validation on DStarRepeater
	  system($rollRpt1Validation);
	  // Set Standard IP/Port for ircDDBGateway
	  system($rollRepeaterAddress1);
	  system($rollRepeaterPort1);
	}

	// Set the Dashboard Public
	if (empty($_POST['dashAccess']) != TRUE ) {
	  $publicDashboard = 'sudo sed -i \'/$DAEMON -a $ipVar 80/c\\\t\t$DAEMON -a $ipVar 80 80 TCP > /dev/null 2>&1 &\' /usr/local/sbin/pistar-upnp.service';
	  $privateDashboard = 'sudo sed -i \'/$DAEMON -a $ipVar 80/ s/^#*/#/\' /usr/local/sbin/pistar-upnp.service';

	  if (escapeshellcmd($_POST['dashAccess']) == 'PUB' ) { system($publicDashboard); }
	  if (escapeshellcmd($_POST['dashAccess']) == 'PRV' ) { system($privateDashboard); }
	}

	// Set the ircDDBGateway Remote Public
	if (empty($_POST['ircRCAccess']) != TRUE ) {
	  $publicRCirc = 'sudo sed -i \'/$DAEMON -a $ipVar 10022/c\\\t\t$DAEMON -a $ipVar 10022 10022 UDP > /dev/null 2>&1 &\' /usr/local/sbin/pistar-upnp.service';
	  $privateRCirc = 'sudo sed -i \'/$DAEMON -a $ipVar 10022/ s/^#*/#/\' /usr/local/sbin/pistar-upnp.service';

	  if (escapeshellcmd($_POST['ircRCAccess']) == 'PUB' ) { system($publicRCirc); }
	  if (escapeshellcmd($_POST['ircRCAccess']) == 'PRV' ) { system($privateRCirc); }
	}

	// Set SSH Access Public
	if (empty($_POST['sshAccess']) != TRUE ) {
	  $publicSSH = 'sudo sed -i \'/$DAEMON -a $ipVar 22/c\\\t\t$DAEMON -a $ipVar 22 22 TCP > /dev/null 2>&1 &\' /usr/local/sbin/pistar-upnp.service';
	  $privateSSH = 'sudo sed -i \'/$DAEMON -a $ipVar 22/ s/^#*/#/\' /usr/local/sbin/pistar-upnp.service';

	  if (escapeshellcmd($_POST['sshAccess']) == 'PUB' ) { system($publicSSH); }
	  if (escapeshellcmd($_POST['sshAccess']) == 'PRV' ) { system($privateSSH); }
	}

	// Set uPNP On or Off
	if (empty($_POST['uPNP']) != TRUE ) {
	  $uPNPon = 'sudo sed -i \'/pistar-upnp.service/c\\*/5 *\t* * *\troot\t/usr/local/sbin/pistar-upnp.service start > /dev/null 2>&1 &\' /etc/crontab';
	  $uPNPoff = 'sudo sed -i \'/pistar-upnp.service/ s/^#*/#/\' /etc/crontab';
	  $uPNPsvcOn = 'sudo systemctl enable pistar-upnp.timer';
	  $uPNPsvcOff = 'sudo systemctl disable pistar-upnp.timer';

	  if (escapeshellcmd($_POST['uPNP']) == 'ON' ) { system($uPNPon); system($uPNPsvcOn); }
	  if (escapeshellcmd($_POST['uPNP']) == 'OFF' ) { system($uPNPoff); system($uPNPsvcOff); }
	}

	// D-Star Time Announce
	if (empty($_POST['confTimeAnnounce']) != TRUE ) {
	  if (escapeshellcmd($_POST['confTimeAnnounce']) == 'ON' )  { system('sudo rm -rf /etc/timeserver.dissable'); }
	  if (escapeshellcmd($_POST['confTimeAnnounce']) == 'OFF' )  { system('sudo touch /etc/timeserver.dissable'); }
	}

	// Set MMDVMHost DMR Mode
	if (empty($_POST['MMDVMModeDMR']) != TRUE ) {
	  if (escapeshellcmd($_POST['MMDVMModeDMR']) == 'ON' )  { $configmmdvm['DMR']['Enable'] = "1"; $configmmdvm['DMR Network']['Enable'] = "1"; }
	  if (escapeshellcmd($_POST['MMDVMModeDMR']) == 'OFF' ) { $configmmdvm['DMR']['Enable'] = "0"; $configmmdvm['DMR Network']['Enable'] = "0"; }
	}

	// Set MMDVMHost D-Star Mode
	if (empty($_POST['MMDVMModeDSTAR']) != TRUE ) {
          if (escapeshellcmd($_POST['MMDVMModeDSTAR']) == 'ON' )  { $configmmdvm['D-Star']['Enable'] = "1"; $configmmdvm['D-Star Network']['Enable'] = "1"; }
          if (escapeshellcmd($_POST['MMDVMModeDSTAR']) == 'OFF' ) { $configmmdvm['D-Star']['Enable'] = "0"; $configmmdvm['D-Star Network']['Enable'] = "0"; }
	}

	// Set MMDVMHost Fusion Mode
	if (empty($_POST['MMDVMModeFUSION']) != TRUE ) {
          if (escapeshellcmd($_POST['MMDVMModeFUSION']) == 'ON' )  { $configmmdvm['System Fusion']['Enable'] = "1"; $configmmdvm['System Fusion Network']['Enable'] = "1"; $configdmr2ysf['Enabled']['Enabled'] = "0"; }
          if (escapeshellcmd($_POST['MMDVMModeFUSION']) == 'OFF' ) { $configmmdvm['System Fusion']['Enable'] = "0"; $configmmdvm['System Fusion Network']['Enable'] = "0"; }
	}

	// Set MMDVMHost P25 Mode
	if (empty($_POST['MMDVMModeP25']) != TRUE ) {
          if (escapeshellcmd($_POST['MMDVMModeP25']) == 'ON' )  { $configmmdvm['P25']['Enable'] = "1"; $configmmdvm['P25 Network']['Enable'] = "1"; $configysf2p25['Enabled']['Enabled'] = "0"; }
          if (escapeshellcmd($_POST['MMDVMModeP25']) == 'OFF' ) { $configmmdvm['P25']['Enable'] = "0"; $configmmdvm['P25 Network']['Enable'] = "0"; }
	}

	// Set MMDVMHost NXDN Mode
	if (empty($_POST['MMDVMModeNXDN']) != TRUE ) {
          if (escapeshellcmd($_POST['MMDVMModeNXDN']) == 'ON' )  { $configmmdvm['NXDN']['Enable'] = "1"; $configmmdvm['NXDN Network']['Enable'] = "1"; $configysf2nxdn['Enabled']['Enabled'] = "0"; }
          if (escapeshellcmd($_POST['MMDVMModeNXDN']) == 'OFF' ) { $configmmdvm['NXDN']['Enable'] = "0"; $configmmdvm['NXDN Network']['Enable'] = "0"; }
	}

	// Set YSF2NXDN Mode
	if (empty($_POST['MMDVMModeYSF2NXDN']) != TRUE ) {
          if (escapeshellcmd($_POST['MMDVMModeYSF2NXDN']) == 'ON' )  { $configysf2nxdn['Enabled']['Enabled'] = "1"; $configmmdvm['NXDN']['Enable'] = "0"; $configmmdvm['NXDN Network']['Enable'] = "0";}
          if (escapeshellcmd($_POST['MMDVMModeYSF2NXDN']) == 'OFF' ) { $configysf2nxdn['Enabled']['Enabled'] = "0"; }
	}

	// Set YSF2P25 Mode
	if (empty($_POST['MMDVMModeYSF2P25']) != TRUE ) {
          if (escapeshellcmd($_POST['MMDVMModeYSF2P25']) == 'ON' )  { $configysf2p25['Enabled']['Enabled'] = "1"; $configmmdvm['P25']['Enable'] = "0"; $configmmdvm['P25 Network']['Enable'] = "0"; }
          if (escapeshellcmd($_POST['MMDVMModeYSF2P25']) == 'OFF' ) { $configysf2p25['Enabled']['Enabled'] = "0"; }
	  if (escapeshellcmd($_POST['MMDVMModeFUSION']) == 'OFF' ) { $configysf2p25['Enabled']['Enabled'] = "0"; }
	}

	// Set DMR2YSF Mode
	if (empty($_POST['MMDVMModeDMR2YSF']) != TRUE ) {
          if (escapeshellcmd($_POST['MMDVMModeDMR2YSF']) == 'ON' )  {
		  $configdmr2ysf['Enabled']['Enabled'] = "1";
		  unset($configdmrgateway['DMR Network 3']);
		  $configdmrgateway['DMR Network 3']['Enabled'] = "0";
		  $configdmrgateway['DMR Network 3']['Name'] = "DMR2YSF_Cross-over";
		  $configdmrgateway['DMR Network 3']['Id'] = $configdmrgateway['DMR Network 2']['Id'];
		  $configdmrgateway['DMR Network 3']['Address'] = "127.0.0.1";
		  $configdmrgateway['DMR Network 3']['Port'] = "62033";
		  $configdmrgateway['DMR Network 3']['Local'] = "62034";
		  $configdmrgateway['DMR Network 3']['TGRewrite0'] = "2,7000001,2,1,999998";
		  $configdmrgateway['DMR Network 3']['SrcRewrite0'] = "2,1,2,7000001,999998";
		  $configdmrgateway['DMR Network 3']['PCRewrite0'] = "2,7000001,2,1,999998";
		  $configdmrgateway['DMR Network 3']['Password'] = '"'."PASSWORD".'"';
		  $configdmrgateway['DMR Network 3']['Location'] = "0";
		  $configdmrgateway['DMR Network 3']['Debug'] = "0";
		  $configmmdvm['System Fusion']['Enable'] = "0";
		  $configmmdvm['System Fusion Network']['Enable'] = "0";
	  }
          if (escapeshellcmd($_POST['MMDVMModeDMR2YSF']) == 'OFF' ) {
		  $configdmr2ysf['Enabled']['Enabled'] = "0";
		  $configdmrgateway['DMR Network 3']['Enabled'] = "0";
	  }
	}

	// Set DMR2NXDN Mode
	if (empty($_POST['MMDVMModeDMR2NXDN']) != TRUE ) {
          if (escapeshellcmd($_POST['MMDVMModeDMR2NXDN']) == 'ON' )  {
		  if (empty($_POST['MMDVMModeDMR2YSF']) != TRUE ) {
		  	if (escapeshellcmd($_POST['MMDVMModeDMR2YSF']) == 'ON' )  {
				$configdmr2ysf['Enabled']['Enabled'] = "0";
		  	}
	  	  }
		  if (empty($_POST['MMDVMModeYSF2NXDN']) != TRUE ) {
		  	if (escapeshellcmd($_POST['MMDVMModeYSF2NXDN']) == 'ON' )  {
				$configysf2nxdn['Enabled']['Enabled'] = "0";
		  	}
	  	  }
		  $configdmr2nxdn['Enabled']['Enabled'] = "1";
		  unset($configdmrgateway['DMR Network 3']);
		  $configdmrgateway['DMR Network 3']['Enabled'] = "0";
		  $configdmrgateway['DMR Network 3']['Name'] = "DMR2NXDN_Cross-over";
		  $configdmrgateway['DMR Network 3']['Id'] = $configdmrgateway['DMR Network 2']['Id'];
		  $configdmrgateway['DMR Network 3']['Address'] = "127.0.0.1";
		  $configdmrgateway['DMR Network 3']['Port'] = "62035";
		  $configdmrgateway['DMR Network 3']['Local'] = "62036";
		  $configdmrgateway['DMR Network 3']['TGRewrite0'] = "2,7000001,2,1,999998";
		  $configdmrgateway['DMR Network 3']['SrcRewrite0'] = "2,1,2,7000001,999998";
		  $configdmrgateway['DMR Network 3']['PCRewrite0'] = "2,7000001,2,1,999998";
		  $configdmrgateway['DMR Network 3']['Password'] = '"'."PASSWORD".'"';
		  $configdmrgateway['DMR Network 3']['Location'] = "0";
		  $configdmrgateway['DMR Network 3']['Debug'] = "0";
		  $configmmdvm['NXDN']['Enable'] = "0";
		  $configmmdvm['NXDN Network']['Enable'] = "0";
	  }
          if (escapeshellcmd($_POST['MMDVMModeDMR2NXDN']) == 'OFF' ) {
		  $configdmr2nxdn['Enabled']['Enabled'] = "0";
		  $configdmrgateway['DMR Network 3']['Enabled'] = "0";
	  }
	}

	// Work out if DMR Network 3 should be ON or not
	if (empty($_POST['MMDVMModeDMR2YSF']) != TRUE || empty($_POST['MMDVMModeDMR2NXDN']) != TRUE) {
		if (escapeshellcmd($_POST['MMDVMModeDMR2YSF']) == 'ON' || escapeshellcmd($_POST['MMDVMModeDMR2NXDN']) == 'ON') {
			$configdmrgateway['DMR Network 3']['Enabled'] = "1";
		} else {
			$configdmrgateway['DMR Network 3']['Enabled'] = "0";
		}
	}

	// Set POCSAG Mode
	if (empty($_POST['MMDVMModePOCSAG']) != TRUE ) {
          if (escapeshellcmd($_POST['MMDVMModePOCSAG']) == 'ON' )  { $configmmdvm['POCSAG']['Enable'] = "1"; $configmmdvm['POCSAG Network']['Enable'] = "1"; }
          if (escapeshellcmd($_POST['MMDVMModePOCSAG']) == 'OFF' ) { $configmmdvm['POCSAG']['Enable'] = "0"; $configmmdvm['POCSAG Network']['Enable'] = "0"; }
	}

	// Set the MMDVMHost Display Type
	if  (empty($_POST['mmdvmDisplayType']) != TRUE ) {
	  if (substr($_POST['mmdvmDisplayType'] , 0, 4 ) === "OLED") {
		  $configmmdvm['General']['Display'] = "OLED";
		  $configmmdvm['OLED']['Type'] = substr($_POST['mmdvmDisplayType'] , 4, 1 );
	  }
	  else {
		  $configmmdvm['General']['Display'] = escapeshellcmd($_POST['mmdvmDisplayType']);
	  }
	}

	// Set the MMDVMHost Display Type
	if  (empty($_POST['mmdvmDisplayPort']) != TRUE ) {
	  if (($_POST['mmdvmDisplayPort'] == "None") || ($_POST['mmdvmDisplayPort'] == "modem")) {
		  $configmmdvm['TFT Serial']['Port'] = $_POST['mmdvmDisplayPort'];
		  $configmmdvm['Nextion']['Port'] = $_POST['mmdvmDisplayPort'];
	  } else {
		  $configmmdvm['TFT Serial']['Port'] = "/dev/".$_POST['mmdvmDisplayPort'];
		  $configmmdvm['Nextion']['Port'] = "/dev/".$_POST['mmdvmDisplayPort'];
	  }
	}

	// Set the Nextion Display Layout
	if (empty($_POST['mmdvmNextionDisplayType']) != TRUE ) {
	  if (escapeshellcmd($_POST['mmdvmNextionDisplayType']) == "G4KLX") { $configmmdvm['Nextion']['ScreenLayout'] = "0"; }
	  if (escapeshellcmd($_POST['mmdvmNextionDisplayType']) == "ON7LDSL2") { $configmmdvm['Nextion']['ScreenLayout'] = "2"; }
	  if (escapeshellcmd($_POST['mmdvmNextionDisplayType']) == "ON7LDSL3") { $configmmdvm['Nextion']['ScreenLayout'] = "3"; }
	  if (escapeshellcmd($_POST['mmdvmNextionDisplayType']) == "ON7LDSL3HS") { $configmmdvm['Nextion']['ScreenLayout'] = "4"; }
	}

	// Set MMDVMHost DMR Colour Code
	if (empty($_POST['dmrColorCode']) != TRUE ) {
          $configmmdvm['DMR']['ColorCode'] = escapeshellcmd($_POST['dmrColorCode']);
	}

	// Set Node Lock Status
	if (empty($_POST['nodeMode']) != TRUE ) {
	  if (escapeshellcmd($_POST['nodeMode']) == 'prv' ) {
            $configmmdvm['DMR']['SelfOnly'] = 1;
            $configmmdvm['D-Star']['SelfOnly'] = 1;
	    $configmmdvm['System Fusion']['SelfOnly'] = 1;
	    $configmmdvm['P25']['SelfOnly'] = 1;
	    $configmmdvm['NXDN']['SelfOnly'] = 1;
            system('sudo sed -i "/restriction=/c\\restriction=1" /etc/dstarrepeater');
          }
	  if (escapeshellcmd($_POST['nodeMode']) == 'pub' ) {
            $configmmdvm['DMR']['SelfOnly'] = 0;
            $configmmdvm['D-Star']['SelfOnly'] = 0;
	    $configmmdvm['System Fusion']['SelfOnly'] = 0;
	    $configmmdvm['P25']['SelfOnly'] = 0;
	    $configmmdvm['NXDN']['SelfOnly'] = 0;
            system('sudo sed -i "/restriction=/c\\restriction=0" /etc/dstarrepeater');
          }
	}

	// Set the Hostname
	if (empty($_POST['confHostname']) != TRUE ) {
	  $newHostnameLower = strtolower(preg_replace('/[^A-Za-z0-9\-]/', '', $_POST['confHostname']));
	  $currHostname = exec('cat /etc/hostname');
	  $rollHostname = 'sudo sed -i "s/'.$currHostname.'/'.$newHostnameLower.'/" /etc/hostname';
	  $rollHosts = 'sudo sed -i "s/'.$currHostname.'/'.$newHostnameLower.'/" /etc/hosts';
	  $rollMotd = 'sudo sed -i "s/'.$currHostname.'/'.$newHostnameLower.'/" /etc/motd';
	  system($rollHostname);
	  system($rollHosts);
	  system($rollMotd);
	  if (file_exists('/etc/hostapd/hostapd.conf')) {
		  // Update the Hotspot name to the Hostname
		  $rollApSsid = 'sudo sed -i "/^ssid=/c\\ssid='.$newHostnameLower.'" /etc/hostapd/hostapd.conf';
		  system($rollApSsid);
	  }
	}

	// Add missing values to DMRGateway
	if (!isset($configdmrgateway['Info']['Enabled'])) { $configdmrgateway['Info']['Enabled'] = "0"; }
	if (!isset($configdmrgateway['Info']['Power'])) { $configdmrgateway['Info']['Power'] = $configmmdvm['Info']['Power']; }
	if (!isset($configdmrgateway['Info']['Height'])) { $configdmrgateway['Info']['Height'] = $configmmdvm['Info']['Height']; }
	if (!isset($configdmrgateway['XLX Network']['Enabled'])) { $configdmrgateway['XLX Network']['Enabled'] = "0"; }
	if (!isset($configdmrgateway['XLX Network']['File'])) { $configdmrgateway['XLX Network']['File'] = "/usr/local/etc/XLXHosts.txt"; }
	if (!isset($configdmrgateway['XLX Network']['Port'])) { $configdmrgateway['XLX Network']['Port'] = "62030"; }
	if (!isset($configdmrgateway['XLX Network']['Password'])) { $configdmrgateway['XLX Network']['Password'] = "passw0rd"; }
	if (!isset($configdmrgateway['XLX Network']['ReloadTime'])) { $configdmrgateway['XLX Network']['ReloadTime'] = "60"; }
	if (!isset($configdmrgateway['XLX Network']['Slot'])) { $configdmrgateway['XLX Network']['Slot'] = "2"; }
	if (!isset($configdmrgateway['XLX Network']['TG'])) { $configdmrgateway['XLX Network']['TG'] = "6"; }
	if (!isset($configdmrgateway['XLX Network']['Base'])) { $configdmrgateway['XLX Network']['Base'] = "64000"; }
	if (!isset($configdmrgateway['XLX Network']['Startup'])) { $configdmrgateway['XLX Network']['Startup'] = "950"; }
	if (!isset($configdmrgateway['XLX Network']['Relink'])) { $configdmrgateway['XLX Network']['Relink'] = "60"; }
	if (!isset($configdmrgateway['XLX Network']['Debug'])) { $configdmrgateway['XLX Network']['Debug'] = "0"; }
	if (!isset($configdmrgateway['DMR Network 3']['Enabled'])) { $configdmrgateway['DMR Network 3']['Enabled'] = "0"; }
	if (!isset($configdmrgateway['DMR Network 3']['Name'])) { $configdmrgateway['DMR Network 3']['Name'] = "HBLink"; }
	if (!isset($configdmrgateway['DMR Network 3']['Address'])) { $configdmrgateway['DMR Network 3']['Address'] = "1.2.3.4"; }
	if (!isset($configdmrgateway['DMR Network 3']['Port'])) { $configdmrgateway['DMR Network 3']['Port'] = "5555"; }
	if (!isset($configdmrgateway['DMR Network 3']['TGRewrite0'])) { $configdmrgateway['DMR Network 3']['TGRewrite0'] = "2,11,2,11,1"; }
	if (!isset($configdmrgateway['DMR Network 3']['Password'])) { $configdmrgateway['DMR Network 3']['Password'] = "PASSWORD"; }
	if (!isset($configdmrgateway['DMR Network 3']['Location'])) { $configdmrgateway['DMR Network 3']['Location'] = "0"; }
	if (!isset($configdmrgateway['DMR Network 3']['Debug'])) { $configdmrgateway['DMR Network 3']['Debug'] = "0"; }
	if (!isset($configdmrgateway['XLX Network']['UserControl'])) { $configdmrgateway['XLX Network']['UserControl'] = "1"; }

	// Add missing options to MMDVMHost
	if (!isset($configmmdvm['Modem']['RFLevel'])) { $configmmdvm['Modem']['RFLevel'] = "100"; }
	if (!isset($configmmdvm['Modem']['RXDCOffset'])) { $configmmdvm['Modem']['RXDCOffset'] = "0"; }
	if (!isset($configmmdvm['Modem']['TXDCOffset'])) { $configmmdvm['Modem']['TXDCOffset'] = "0"; }
	if (!isset($configmmdvm['Modem']['CWIdTXLevel'])) { $configmmdvm['Modem']['CWIdTXLevel'] = "50"; }
	if (!isset($configmmdvm['Modem']['NXDNTXLevel'])) { $configmmdvm['Modem']['NXDNTXLevel'] = "50"; }
	if (!isset($configmmdvm['Modem']['POCSAGTXLevel'])) { $configmmdvm['Modem']['POCSAGTXLevel'] = "50"; }
	if (!isset($configmmdvm['D-Star']['AckReply'])) { $configmmdvm['D-Star']['AckReply'] = "1"; }
	if (!isset($configmmdvm['D-Star']['AckTime'])) { $configmmdvm['D-Star']['AckTime'] = "750"; }
	if (!isset($configmmdvm['D-Star']['AckMessage'])) { $configmmdvm['D-Star']['AckMessage'] = "0"; }
	if (!isset($configmmdvm['D-Star']['RemoteGateway'])) { $configmmdvm['D-Star']['RemoteGateway'] = "0"; }
	if (!isset($configmmdvm['DMR']['BeaconInterval'])) { $configmmdvm['DMR']['BeaconInterval'] = "60"; }
	if (!isset($configmmdvm['DMR']['BeaconDuration'])) { $configmmdvm['DMR']['BeaconDuration'] = "3"; }
	if (!isset($configmmdvm['DMR']['OVCM'])) { $configmmdvm['DMR']['OVCM'] = "0"; }
	if (!isset($configmmdvm['P25']['RemoteGateway'])) { $configmmdvm['P25']['RemoteGateway'] = "0"; }
	if (!isset($configmmdvm['OLED']['Scroll'])) { $configmmdvm['OLED']['Scroll'] = "0"; }
	if (!isset($configmmdvm['NXDN']['Enable'])) { $configmmdvm['NXDN']['Enable'] = "0"; }
	if (!isset($configmmdvm['NXDN']['RAN'])) { $configmmdvm['NXDN']['RAN'] = "1"; }
	if (!isset($configmmdvm['NXDN']['SelfOnly'])) { $configmmdvm['NXDN']['SelfOnly'] = "1"; }
	if (!isset($configmmdvm['NXDN']['RemoteGateway'])) { $configmmdvm['NXDN']['RemoteGateway'] = "0"; }
	if (!isset($configmmdvm['NXDN Network']['Enable'])) { $configmmdvm['NXDN Network']['Enable'] = "0"; }
	if (!isset($configmmdvm['NXDN Network']['LocalPort'])) { $configmmdvm['NXDN Network']['LocalPort'] = "3300"; }
	if (!isset($configmmdvm['NXDN Network']['GatewayAddress'])) { $configmmdvm['NXDN Network']['GatewayAddress'] = "127.0.0.1"; }
	if (!isset($configmmdvm['NXDN Network']['GatewayPort'])) { $configmmdvm['NXDN Network']['GatewayPort'] = "4300"; }
	if (!isset($configmmdvm['NXDN Network']['Debug'])) { $configmmdvm['NXDN Network']['Debug'] = "0"; }
	if (!isset($configmmdvm['NXDN Id Lookup']['File'])) { $configmmdvm['NXDN Id Lookup']['File'] = "/usr/local/etc/NXDN.csv"; }
	if (!isset($configmmdvm['NXDN Id Lookup']['Time'])) { $configmmdvm['NXDN Id Lookup']['Time'] = "24"; }
	if (!isset($configmmdvm['System Fusion']['TXHang'])) { $configmmdvm['System Fusion']['TXHang'] = "3"; }
	if (!isset($configmmdvm['Lock File']['Enable'])) { $configmmdvm['Lock File']['Enable'] = "0"; }
	if (!isset($configmmdvm['Lock File']['File'])) { $configmmdvm['Lock File']['File'] = "/tmp/MMDVMHost.lock"; }
	if (!isset($configmmdvm['GPSD']['Enable'])) { $configmmdvm['GPSD']['Enable'] = "0"; }
 	if (!isset($configmmdvm['GPSD']['Address'])) { $configmmdvm['GPSD']['Address'] = "127.0.0.1"; }
	if (!isset($configmmdvm['GPSD']['Port'])) { $configmmdvm['GPSD']['Port'] = "2947"; }
	if (!isset($configmmdvm['OLED']['Rotate'])) { $configmmdvm['OLED']['Rotate'] = "0"; }
	if (!isset($configmmdvm['OLED']['Cast'])) { $configmmdvm['OLED']['Cast'] = "0"; }
	if (!isset($configmmdvm['OLED']['LogoScreensaver'])) { $configmmdvm['OLED']['LogoScreensaver'] = "0"; }
	if (!isset($configmmdvm['Remote Control']['Enable'])) { $configmmdvm['Remote Control']['Enable'] = "0"; }
	if (!isset($configmmdvm['Remote Control']['Port'])) { $configmmdvm['Remote Control']['Port'] = "7642"; }
	if (isset($configmmdvm['TFT Serial']['Port'])) {
		if ( $configmmdvm['TFT Serial']['Port'] == "/dev/modem" ) { $configmmdvm['TFT Serial']['Port'] = "modem"; }
	}
	if (isset($configmmdvm['Nextion']['Port'])) {
		if ( $configmmdvm['Nextion']['Port'] == "/dev/modem" ) { $configmmdvm['Nextion']['Port'] = "modem"; }
	}

	// Add missing options to DMR2YSF
	if (!isset($configdmr2ysf['YSF Network']['FCSRooms'])) { $configdmr2ysf['YSF Network']['FCSRooms'] = "/usr/local/etc/FCSHosts.txt"; }
	if (!isset($configdmr2ysf['DMR Network']['DefaultDstTG'])) { $configdmr2ysf['DMR Network']['DefaultDstTG'] = "9"; }
	if (!isset($configdmr2ysf['DMR Network']['TGUnlink'])) { $configdmr2ysf['DMR Network']['TGUnlink'] = "4000"; }
	if (!isset($configdmr2ysf['DMR Network']['TGListFile'])) { $configdmr2ysf['DMR Network']['TGListFile'] = "/usr/local/etc/TGList_YSF.txt"; }
	$configdmr2ysf['Log']['DisplayLevel'] = "0";
	$configdmr2ysf['Log']['FileLevel'] = "2";

	// Add missing options to YSFGateway
	if (!isset($configysfgateway['Network']['Jitter'])) { $configysfgateway['Network']['Jitter'] = "3"; }
	if (!isset($configysfgateway['General']['RptAddress'])) { $configysfgateway['General']['RptAddress'] = "127.0.0.1"; }
	if (!isset($configysfgateway['General']['RptPort'])) { $configysfgateway['General']['RptPort'] = "3200"; }
	if (!isset($configysfgateway['General']['LocalAddress'])) { $configysfgateway['General']['LocalAddress'] = "127.0.0.1"; }
	if (!isset($configysfgateway['General']['LocalPort'])) { $configysfgateway['General']['LocalPort'] = "4200"; }
	if (!isset($configysfgateway['General']['Daemon'])) { $configysfgateway['General']['Daemon'] = "1"; }
	if (!isset($configysfgateway['Log']['DisplayLevel'])) { $configysfgateway['Log']['DisplayLevel'] = "0"; }
	if (!isset($configysfgateway['Log']['FileLevel'])) { $configysfgateway['Log']['FileLevel'] = "1"; }
	if (!isset($configysfgateway['Log']['FilePath'])) { $configysfgateway['Log']['FilePath'] = "/var/log/pi-star"; }
	if (!isset($configysfgateway['Log']['FileRoot'])) { $configysfgateway['Log']['FileRoot'] = "YSFGateway"; }
	if (!isset($configysfgateway['aprs.fi']['Icon'])) { $configysfgateway['aprs.fi']['Icon'] = "YY"; }
	if (!isset($configysfgateway['aprs.fi']['Beacon'])) { $configysfgateway['aprs.fi']['Beacon'] = "Yaesu DMO with EA7EE Sofware"; }
	if (!isset($configysfgateway['aprs.fi']['BeaconTime'])) { $configysfgateway['aprs.fi']['BeaconTime'] = "20"; }
	if (!isset($configysfgateway['aprs.fi']['Port'])) { $configysfgateway['aprs.fi']['Port'] = "14580"; }
	if (!isset($configysfgateway['aprs.fi']['Refresh'])) { $configysfgateway['aprs.fi']['Refresh'] = "4"; }
	if (!isset($configysfgateway['General']['SaveAMBE'])) { $configysfgateway['General']['SaveAMBE'] = "0"; }
	if (!isset($configysfgateway['General']['AMBECompA'])) { $configysfgateway['General']['AMBECompA'] = "4"; }
	if (!isset($configysfgateway['General']['AMBECompB'])) { $configysfgateway['General']['AMBECompB'] = "3"; }			
	if (isset($configysfgateway['Network']['YSF2DMRAddress'])) { unset($configysfgateway['Network']['YSF2DMRAddress']); }
	if (isset($configysfgateway['Network']['YSF2DMRPort'])) { unset($configysfgateway['Network']['YSF2DMRPort']); }

	if (!isset($configysfgateway['YSF Network']['Options'])) { $configysfgateway['YSF Network']['Options'] = "";}
	if (!isset($configysfgateway['FCS Network']['Options'])) { $configysfgateway['FCS Network']['Options'] = "";}

	if (!isset($configysfgateway['Remote Commands']['Enable'])) { $configysfgateway['Remote Commands']['Enable'] = "1"; }
	if (!isset($configysfgateway['Remote Commands']['Port'])) { $configysfgateway['Remote Commands']['Port'] = "6073"; }
	
	$configysfgateway['Network']['Debug'] = "0";
	if (!isset($configysfgateway['Network']['NoChange'])) { $configysfgateway['Network']['NoChange'] = "0"; }	
	$configysfgateway['YSF Network']['Port'] = "42000";
	$configysfgateway['YSF Network']['Hosts'] = "/usr/local/etc/YSFHosts.txt";
	$configysfgateway['Network']['ReloadTime'] = "60";
	$configysfgateway['YSF Network']['ParrotAddress'] = "127.0.0.1";
	$configysfgateway['YSF Network']['ParrotPort'] = "42012";
	if (!isset($configysfgateway['YSF Network']['NXDNEnable'])) { $configysfgateway['YSF Network']['NXDNEnable'] = "0"; }
	if (!isset($configysfgateway['YSF Network']['NXDNStartup'])) { $configysfgateway['YSF Network']['NXDNStartup'] = "400"; }
	if (!isset($configysfgateway['YSF Network']['NXDNHosts'])) { $configysfgateway['YSF Network']['NXDNHosts'] = "/usr/local/etc/TGList_NXDN.txt"; }				
	$configysfgateway['YSF Network']['YSF2NXDNAddress'] = "127.0.0.1";
	$configysfgateway['YSF Network']['YSF2NXDNPort'] = "42014";
	if (!isset($configysfgateway['YSF Network']['P25Enable'])) { $configysfgateway['YSF Network']['P25Enable'] = "0"; }
	if (!isset($configysfgateway['YSF Network']['P25Startup'])) { $configysfgateway['YSF Network']['P25Startup'] = "400"; }	
	if (!isset($configysfgateway['YSF Network']['P25Hosts'])) { $configysfgateway['YSF Network']['P25Hosts'] = "/usr/local/etc/TGList_P25.txt"; }
	$configysfgateway['YSF Network']['YSF2P25Address'] = "127.0.0.1";
	$configysfgateway['YSF Network']['YSF2P25Port'] = "42015";
	$configysfgateway['FCS Network']['Port'] = "42001";
	$configysfgateway['FCS Network']['Rooms'] = "/usr/local/etc/FCSHosts.txt";
	if (!isset($configysfgateway['DMR Network']['TGUnlink'])) { $configysfgateway['DMR Network']['TGUnlink'] = "4000"; }
	if (!isset($configysfgateway['DMR Network']['PCUnlink'])) { $configysfgateway['DMR Network']['PCUnlink'] = "0"; }
	if (!isset($configysfgateway['DMR Network']['Local'])) { $configysfgateway['DMR Network']['Local'] = "62032"; }
	if (!isset($configysfgateway['DMR Network']['File'])) { $configysfgateway['DMR Network']['File'] = "/usr/local/etc/DMRIds.dat"; }
	if (!isset($configysfgateway['DMR Network']['Time'])) { $configysfgateway['DMR Network']['Time'] = "720"; }	
	if (!isset($configysfgateway['General']['NewsPath'])) { $configysfgateway['General']['NewsPath'] = "/tmp/news"; }			
	if (!isset($configysfgateway['General']['BeaconPath'])) { $configysfgateway['General']['BeaconPath'] = "/usr/local/sbin/beacon.amb"; }	
	if (!isset($configysfgateway['YSF Network']['StartupDGID'])) { $configysfgateway['YSF Network']['StartupDGID'] = "0"; }

	// Add missing options to YSF2NXDN
	$configysf2nxdn['YSF Network']['LocalPort'] = $configysfgateway['YSF Network']['YSF2NXDNPort'];
	$configysf2nxdn['YSF Network']['DstPort'] = $configysfgateway['YSF Network']['Port'];
	$configysf2nxdn['YSF Network']['Daemon'] = "1";
	$configysf2nxdn['YSF Network']['EnableWiresX'] = "1";
	if (!isset($configysf2nxdn['Enabled']['Enabled'])) { $configysf2nxdn['Enabled']['Enabled'] = "0"; }
	$configysf2nxdn['NXDN Id Lookup']['File'] = "/usr/local/etc/NXDN.csv";
	$configysf2nxdn['NXDN Network']['TGListFile'] = "/usr/local/etc/TGList_NXDN.txt";
	$configysf2nxdn['Log']['DisplayLevel'] = "0";
	$configysf2nxdn['Log']['FileLevel'] = "0";
	$configysf2nxdn['Log']['FilePath'] = "/var/log/pi-star";
	$configysf2nxdn['Log']['FileRoot'] = "YSF2NXDN";
	if (!isset($configysf2nxdn['aprs.fi']['Enable'])) { $configysf2nxdn['aprs.fi']['Enable'] = "0"; }

	// Add missing options to YSF2P25
	$configysf2p25['YSF Network']['LocalPort'] = $configysfgateway['YSF Network']['YSF2P25Port'];
	$configysf2p25['YSF Network']['DstPort'] = $configysfgateway['YSF Network']['Port'];
	$configysf2p25['YSF Network']['Daemon'] = "1";
	$configysf2p25['YSF Network']['EnableWiresX'] = "1";
	if (!isset($configysf2p25['Enabled']['Enabled'])) { $configysf2p25['Enabled']['Enabled'] = "0"; }
	$configysf2p25['DMR Id Lookup']['File'] = "/usr/local/etc/DMRIds.dat";
	$configysf2p25['P25 Network']['TGListFile'] = "/usr/local/etc/TGList_P25.txt";
	$configysf2p25['Log']['DisplayLevel'] = "0";
	$configysf2p25['Log']['FileLevel'] = "0";
	$configysf2p25['Log']['FilePath'] = "/var/log/pi-star";
	$configysf2p25['Log']['FileRoot'] = "YSF2P25";
	if (isset($configysf2p25['aprs.fi'])) { unset($configysf2p25['aprs.fi']); }

	// Clean up for NXDN Gateway
	if (file_exists('/etc/nxdngateway')) {
		if (isset($confignxdngateway['Network']['HostsFile'])) {
			$confignxdngateway['Network']['HostsFile1'] = $confignxdngateway['Network']['HostsFile'];
			$confignxdngateway['Network']['HostsFile2'] = "/usr/local/etc/NXDNHostsLocal.txt";
			unset($confignxdngateway['Network']['HostsFile']);
			if (!file_exists('/usr/local/etc/NXDNHostsLocal.txt')) { exec('sudo touch /usr/local/etc/NXDNHostsLocal.txt'); }
		}
		$configmmdvm['NXDN Network']['LocalAddress'] = "127.0.0.1";
		$configmmdvm['NXDN Network']['LocalPort'] = "14021";
		$configmmdvm['NXDN Network']['GatewayAddress'] = "127.0.0.1";
		$configmmdvm['NXDN Network']['GatewayPort'] = "14020";
		if(isset($configmmdvm['NXDN']['SelfOnly'])) {
			$nxdnSelfOnlyTmp = $configmmdvm['NXDN']['SelfOnly'];
			unset($configmmdvm['NXDN']['SelfOnly']);
			$configmmdvm['NXDN']['SelfOnly'] = $nxdnSelfOnlyTmp;
		}
		if(isset($configmmdvm['NXDN']['ModeHang'])) {
			$nxdnRfModeHangTmp = $configmmdvm['NXDN']['ModeHang'];
			unset($configmmdvm['NXDN']['ModeHang']);
			$configmmdvm['NXDN']['ModeHang'] = $nxdnRfModeHangTmp;
		}
		if(isset($configmmdvm['NXDN Network']['ModeHang'])) {
			$nxdnNetModeHangTmp = $configmmdvm['NXDN Network']['ModeHang'];
			unset($configmmdvm['NXDN Network']['ModeHang']);
			$configmmdvm['NXDN Network']['ModeHang'] = $nxdnNetModeHangTmp;
		}
		// Add in all the APRS stuff
		if(!isset($confignxdngateway['Info']['Power'])) { $confignxdngateway['Info']['Power'] = "1"; }
		if(!isset($confignxdngateway['Info']['Height'])) { $confignxdngateway['Info']['Height'] = "0"; }
		if(!isset($confignxdngateway['aprs.fi']['Enable'])) { $confignxdngateway['aprs.fi']['Enable'] = "0"; }
		if(!isset($confignxdngateway['aprs.fi']['Server'])) { $confignxdngateway['aprs.fi']['Server'] = "euro.aprs2.net"; }
		if(!isset($confignxdngateway['aprs.fi']['Port'])) { $confignxdngateway['aprs.fi']['Port'] = "14580"; }
		if(!isset($confignxdngateway['aprs.fi']['Password'])) { $confignxdngateway['aprs.fi']['Password'] = "9999"; }
		if(!isset($confignxdngateway['aprs.fi']['Description'])) { $confignxdngateway['aprs.fi']['Description'] = "APRS for NXDN Gateway"; }
		if(!isset($confignxdngateway['aprs.fi']['Suffix'])) { $confignxdngateway['aprs.fi']['Suffix'] = "N"; }
		// GPSd stuff
		if(!isset($confignxdngateway['GPSD']['Enable'])) { $confignxdngateway['GPSD']['Enable'] = "0"; }
		if(!isset($confignxdngateway['GPSD']['Address'])) { $confignxdngateway['GPSD']['Address'] = "127.0.0.1"; }
		if(!isset($confignxdngateway['GPSD']['Port'])) { $confignxdngateway['GPSD']['Port'] = "2947"; }
	}

	// Clean up legacy options
	$dmrGatewayVer = exec("DMRGateway -v | awk {'print $3'} | cut -c 1-8");
	if ($dmrGatewayVer > 20170924) {
		unset($configdmrgateway['XLX Network 1']);
		unset($configdmrgateway['XLX Network 2']);
	}

	// Add P25Gateway Options
	$p25GatewayVer = exec("P25Gateway -v | awk {'print $3'} | cut -c 1-8");
	if ($p25GatewayVer >= 20200403) {
		if (!isset($configp25gateway['Remote Commands']['Enable'])) { $configp25gateway['Remote Commands']['Enable'] = "1"; }
		if (!isset($configp25gateway['Remote Commands']['Port'])) { $configp25gateway['Remote Commands']['Port'] = "6074"; }
	}

	// Add NXDNGateway Options
	$nxdnGatewayVer = exec("NXDNGateway -v | awk {'print $3'} | cut -c 1-8");
	if ($nxdnGatewayVer >= 20200403) {
		if (!isset($confignxdngateway['Remote Commands']['Enable'])) { $confignxdngateway['Remote Commands']['Enable'] = "1"; }
		if (!isset($confignxdngateway['Remote Commands']['Port'])) { $confignxdngateway['Remote Commands']['Port'] = "6075"; }
	}

	// Add the DAPNet Config
	if (!isset($configdapnetgw['General']['Callsign'])) { $configdapnetgw['General']['Callsign'] = "M1ABC"; }
	if (!isset($configdapnetgw['General']['RptAddress'])) { $configdapnetgw['General']['RptAddress'] = "127.0.0.1"; }
	if (!isset($configdapnetgw['General']['RptPort'])) { $configdapnetgw['General']['RptPort'] = "3800"; }
	if (!isset($configdapnetgw['General']['LocalAddress'])) { $configdapnetgw['General']['LocalAddress'] = "127.0.0.1"; }
	if (!isset($configdapnetgw['General']['LocalPort'])) { $configdapnetgw['General']['LocalPort'] = "4800"; }
	if (!isset($configdapnetgw['General']['Daemon'])) { $configdapnetgw['General']['Daemon'] = "0"; }
	if (!isset($configdapnetgw['Log']['DisplayLevel'])) { $configdapnetgw['Log']['DisplayLevel'] = "0"; }
	if (!isset($configdapnetgw['Log']['FileLevel'])) { $configdapnetgw['Log']['FileLevel'] = "2"; }
	if (!isset($configdapnetgw['Log']['FilePath'])) { $configdapnetgw['Log']['FilePath'] = "/var/log/pi-star"; }
	if (!isset($configdapnetgw['Log']['FileRoot'])) { $configdapnetgw['Log']['FileRoot'] = "DAPNETGateway"; }
	if (!isset($configdapnetgw['DAPNET']['Address'])) { $configdapnetgw['DAPNET']['Address'] = "dapnet.afu.rwth-aachen.de"; }
	if (!isset($configdapnetgw['DAPNET']['Port'])) { $configdapnetgw['DAPNET']['Port'] = "43434"; }
	if (!isset($configdapnetgw['DAPNET']['AuthKey'])) { $configdapnetgw['DAPNET']['AuthKey'] = "TOPSECRET"; }
	if (!isset($configdapnetgw['DAPNET']['SuppressTimeWhenBusy'])) { $configdapnetgw['DAPNET']['SuppressTimeWhenBusy'] = "1"; }
	if (!isset($configdapnetgw['DAPNET']['Debug'])) { $configdapnetgw['DAPNET']['Debug'] = "0"; }
	if (!isset($configmmdvm['POCSAG']['Enable'])) { $configmmdvm['POCSAG']['Enable'] = "0"; }
	if (!isset($configmmdvm['POCSAG']['Frequency'])) { $configmmdvm['POCSAG']['Frequency'] = "439987500"; }
	if (!isset($configmmdvm['POCSAG Network']['Enable'])) { $configmmdvm['POCSAG Network']['Enable'] = "0"; }
	if (!isset($configmmdvm['POCSAG Network']['LocalAddress'])) { $configmmdvm['POCSAG Network']['LocalAddress'] = "127.0.0.1"; }
	if (!isset($configmmdvm['POCSAG Network']['LocalPort'])) { $configmmdvm['POCSAG Network']['LocalPort'] = "3800"; }
	if (!isset($configmmdvm['POCSAG Network']['GatewayAddress'])) { $configmmdvm['POCSAG Network']['GatewayAddress'] = "127.0.0.1"; }
	if (!isset($configmmdvm['POCSAG Network']['GatewayPort'])) { $configmmdvm['POCSAG Network']['GatewayPort'] = "4800"; }
	if (!isset($configmmdvm['POCSAG Network']['ModeHang'])) { $configmmdvm['POCSAG Network']['ModeHang'] = "5"; }
	if (!isset($configmmdvm['POCSAG Network']['Debug'])) { $configmmdvm['POCSAG Network']['Debug'] = "0"; }
	if (isset($configmmdvm['POCSAG Network']['ModeHang'])) { $configmmdvm['POCSAG Network']['ModeHang'] = "5"; }

	// Create the hostfiles.nodextra file if required
	if (empty($_POST['confHostFilesNoDExtra']) != TRUE ) {
		if (escapeshellcmd($_POST['confHostFilesNoDExtra']) == 'ON' )  {
			if (!file_exists('/etc/hostfiles.nodextra')) { system('sudo touch /etc/hostfiles.nodextra'); }
		}
		if (escapeshellcmd($_POST['confHostFilesNoDExtra']) == 'OFF' )  {
			if (file_exists('/etc/hostfiles.nodextra')) { system('sudo rm -rf /etc/hostfiles.nodextra'); }
		}
	}

	// Continue Page Output
	echo "<br />";
	echo "<table>\n";
	echo "<tr><th>Done...</th></tr>\n";
	echo "<tr><td>Changes applied, starting services...</td></tr>\n";
	echo "</table>\n";

	// MMDVMHost config file wrangling
	$mmdvmContent = "";
	foreach($configmmdvm as $mmdvmSection=>$mmdvmValues) {
		// UnBreak special cases
		$mmdvmSection = str_replace("_", " ", $mmdvmSection);
		$mmdvmContent .= "[".$mmdvmSection."]\n";
                // append the values
                foreach($mmdvmValues as $mmdvmKey=>$mmdvmValue) {
			$mmdvmContent .= $mmdvmKey."=".$mmdvmValue."\n";
			}
			$mmdvmContent .= "\n";
		}

	if (!$handleMMDVMHostConfig = fopen('/tmp/bW1kdm1ob3N0DQo.tmp', 'w')) {
		return false;
	}
	if (!is_writable('/tmp/bW1kdm1ob3N0DQo.tmp')) {
          echo "<br />\n";
          echo "<table>\n";
          echo "<tr><th>ERROR</th></tr>\n";
          echo "<tr><td>Unable to write configuration file(s)...</td><tr>\n";
          echo "<tr><td>Please wait a few seconds and retry...</td></tr>\n";
          echo "</table>\n";
          unset($_POST);
          echo '<script type="text/javascript">setTimeout(function() { window.location=window.location;},5000);</script>';
          die();
	}
	else {
		$success = fwrite($handleMMDVMHostConfig, $mmdvmContent);
		fclose($handleMMDVMHostConfig);
		if (intval(exec('cat /tmp/bW1kdm1ob3N0DQo.tmp | wc -l')) > 140 ) {
			exec('sudo mv /tmp/bW1kdm1ob3N0DQo.tmp /etc/mmdvmhost');		// Move the file back
			exec('sudo chmod 644 /etc/mmdvmhost');					// Set the correct runtime permissions
			exec('sudo chown root:root /etc/mmdvmhost');				// Set the owner
		}
	}


	if ($configmmdvm['System Fusion']['Enable'] == "1") {
		// ysfgateway config file wrangling
		$ysfgwContent = "";
			foreach($configysfgateway as $ysfgwSection=>$ysfgwValues) {
					// UnBreak special cases
					$ysfgwSection = str_replace("_", " ", $ysfgwSection);
					$ysfgwContent .= "[".$ysfgwSection."]\n";
					// append the values
					foreach($ysfgwValues as $ysfgwKey=>$ysfgwValue) {
							$ysfgwContent .= $ysfgwKey."=".$ysfgwValue."\n";
							}
							$ysfgwContent .= "\n";
					}

			if (!$handleYSFGWconfig = fopen('/tmp/eXNmZ2F0ZXdheQ.tmp', 'w')) {
					return false;
			}

		if (!is_writable('/tmp/eXNmZ2F0ZXdheQ.tmp')) {
			echo "<br />\n";
			echo "<table>\n";
			echo "<tr><th>ERROR</th></tr>\n";
			echo "<tr><td>Unable to write configuration file(s)...</td><tr>\n";
			echo "<tr><td>Please wait a few seconds and retry...</td></tr>\n";
			echo "</table>\n";
			unset($_POST);
			echo '<script type="text/javascript">setTimeout(function() { window.location=window.location;},5000);</script>';
			die();
		}
		else {
				$success = fwrite($handleYSFGWconfig, $ysfgwContent);
				fclose($handleYSFGWconfig);
			if (intval(exec('cat /tmp/eXNmZ2F0ZXdheQ.tmp | wc -l')) > 35 ) {
				exec('sudo mv /tmp/eXNmZ2F0ZXdheQ.tmp /etc/ysfgateway');		// Move the file back
				exec('sudo chmod 644 /etc/ysfgateway');					// Set the correct runtime permissions
				exec('sudo chown root:root /etc/ysfgateway');				// Set the owner
			}
		}
	}

	// NXDNGateway config file wrangling
	$nxdngwContent = "";
        foreach($confignxdngateway as $nxdngwSection=>$nxdngwValues) {
                // UnBreak special cases
                $nxdngwSection = str_replace("_", " ", $nxdngwSection);
                $nxdngwContent .= "[".$nxdngwSection."]\n";
                // append the values
                foreach($nxdngwValues as $nxdngwKey=>$nxdngwValue) {
                        $nxdngwContent .= $nxdngwKey."=".$nxdngwValue."\n";
                        }
                        $nxdngwContent .= "\n";
                }

        if (!$handleNXDNGWconfig = fopen('/tmp/kXKwkDKy793HF5.tmp', 'w')) {
                return false;
        }

	if (!is_writable('/tmp/kXKwkDKy793HF5.tmp')) {
          echo "<br />\n";
          echo "<table>\n";
          echo "<tr><th>ERROR</th></tr>\n";
          echo "<tr><td>Unable to write configuration file(s)...</td><tr>\n";
          echo "<tr><td>Please wait a few seconds and retry...</td></tr>\n";
          echo "</table>\n";
          unset($_POST);
          echo '<script type="text/javascript">setTimeout(function() { window.location=window.location;},5000);</script>';
          die();
	}
	else {
	        $success = fwrite($handleNXDNGWconfig, $nxdngwContent);
	        fclose($handleNXDNGWconfig);
		if ( (intval(exec('cat /tmp/kXKwkDKy793HF5.tmp | wc -l')) > 30 ) && (file_exists('/etc/nxdngateway')) ) {
			exec('sudo mv /tmp/kXKwkDKy793HF5.tmp /etc/nxdngateway');		// Move the file back
			exec('sudo chmod 644 /etc/nxdngateway');				// Set the correct runtime permissions
			exec('sudo chown root:root /etc/nxdngateway');				// Set the owner
		}
	}

	// P25Gateway config file wrangling
	$p25gwContent = "";
        foreach($configp25gateway as $p25gwSection=>$p25gwValues) {
                // UnBreak special cases
                $p25gwSection = str_replace("_", " ", $p25gwSection);
                $p25gwContent .= "[".$p25gwSection."]\n";
                // append the values
                foreach($p25gwValues as $p25gwKey=>$p25gwValue) {
                        $p25gwContent .= $p25gwKey."=".$p25gwValue."\n";
                        }
                        $p25gwContent .= "\n";
                }

        if (!$handleP25GWconfig = fopen('/tmp/sJSySkheSgrelJX.tmp', 'w')) {
                return false;
        }

	if (!is_writable('/tmp/sJSySkheSgrelJX.tmp')) {
          echo "<br />\n";
          echo "<table>\n";
          echo "<tr><th>ERROR</th></tr>\n";
          echo "<tr><td>Unable to write configuration file(s)...</td><tr>\n";
          echo "<tr><td>Please wait a few seconds and retry...</td></tr>\n";
          echo "</table>\n";
          unset($_POST);
          echo '<script type="text/javascript">setTimeout(function() { window.location=window.location;},5000);</script>';
          die();
	}
	else {
	        $success = fwrite($handleP25GWconfig, $p25gwContent);
	        fclose($handleP25GWconfig);
		if ( (intval(exec('cat /tmp/sJSySkheSgrelJX.tmp | wc -l')) > 30 ) && (file_exists('/etc/p25gateway')) ) {
			exec('sudo mv /tmp/sJSySkheSgrelJX.tmp /etc/p25gateway');		// Move the file back
			exec('sudo chmod 644 /etc/p25gateway');					// Set the correct runtime permissions
			exec('sudo chown root:root /etc/p25gateway');				// Set the owner
		}
	}

	// ysf2nxdn config file wrangling
        $ysf2nxdnContent = "";
        foreach($configysf2nxdn as $ysf2nxdnSection=>$ysf2nxdnValues) {
                // UnBreak special cases
                $ysf2nxdnSection = str_replace("_", " ", $ysf2nxdnSection);
                $ysf2nxdnContent .= "[".$ysf2nxdnSection."]\n";
                // append the values
                foreach($ysf2nxdnValues as $ysf2nxdnKey=>$ysf2nxdnValue) {
                        $ysf2nxdnContent .= $ysf2nxdnKey."=".$ysf2nxdnValue."\n";
                        }
                        $ysf2nxdnContent .= "\n";
                }
        if (!$handleYSF2NXDNconfig = fopen('/tmp/dsWGR34tHRrSFFGb.tmp', 'w')) {
                return false;
        }
        if (!is_writable('/tmp/dsWGR34tHRrSFFGb.tmp')) {
          echo "<br />\n";
          echo "<table>\n";
          echo "<tr><th>ERROR</th></tr>\n";
          echo "<tr><td>Unable to write configuration file(s)...</td><tr>\n";
          echo "<tr><td>Please wait a few seconds and retry...</td></tr>\n";
          echo "</table>\n";
          unset($_POST);
          echo '<script type="text/javascript">setTimeout(function() { window.location=window.location;},5000);</script>';
          die();
        }
        else {
                $success = fwrite($handleYSF2NXDNconfig, $ysf2nxdnContent);
                fclose($handleYSF2NXDNconfig);
                if (intval(exec('cat /tmp/dsWGR34tHRrSFFGb.tmp | wc -l')) > 35 ) {
                        exec('sudo mv /tmp/dsWGR34tHRrSFFGb.tmp /etc/ysf2nxdn');                 // Move the file back
                        exec('sudo chmod 644 /etc/ysf2nxdn');                                    // Set the correct runtime permissions
                        exec('sudo chown root:root /etc/ysf2nxdn');                              // Set the owner
                }
        }

	// ysf2p25 config file wrangling
        $ysf2p25Content = "";
        foreach($configysf2p25 as $ysf2p25Section=>$ysf2p25Values) {
                // UnBreak special cases
                $ysf2p25Section = str_replace("_", " ", $ysf2p25Section);
                $ysf2p25Content .= "[".$ysf2p25Section."]\n";
                // append the values
                foreach($ysf2p25Values as $ysf2p25Key=>$ysf2p25Value) {
                        $ysf2p25Content .= $ysf2p25Key."=".$ysf2p25Value."\n";
                        }
                        $ysf2p25Content .= "\n";
                }
        if (!$handleYSF2P25config = fopen('/tmp/dsWGR34tHRrSFFGc.tmp', 'w')) {
                return false;
        }
        if (!is_writable('/tmp/dsWGR34tHRrSFFGc.tmp')) {
          echo "<br />\n";
          echo "<table>\n";
          echo "<tr><th>ERROR</th></tr>\n";
          echo "<tr><td>Unable to write configuration file(s)...</td><tr>\n";
          echo "<tr><td>Please wait a few seconds and retry...</td></tr>\n";
          echo "</table>\n";
          unset($_POST);
          echo '<script type="text/javascript">setTimeout(function() { window.location=window.location;},5000);</script>';
          die();
        }
        else {
                $success = fwrite($handleYSF2P25config, $ysf2p25Content);
                fclose($handleYSF2P25config);
                if (intval(exec('cat /tmp/dsWGR34tHRrSFFGc.tmp | wc -l')) > 25 ) {
                        exec('sudo mv /tmp/dsWGR34tHRrSFFGc.tmp /etc/ysf2p25');                 // Move the file back
                        exec('sudo chmod 644 /etc/ysf2p25');                                    // Set the correct runtime permissions
                        exec('sudo chown root:root /etc/ysf2p25');                              // Set the owner
                }
        }

	// dmr2ysf config file wrangling
        $dmr2ysfContent = "";
        foreach($configdmr2ysf as $dmr2ysfSection=>$dmr2ysfValues) {
                // UnBreak special cases
                $dmr2ysfSection = str_replace("_", " ", $dmr2ysfSection);
                $dmr2ysfContent .= "[".$dmr2ysfSection."]\n";
                // append the values
                foreach($dmr2ysfValues as $dmr2ysfKey=>$dmr2ysfValue) {
                        $dmr2ysfContent .= $dmr2ysfKey."=".$dmr2ysfValue."\n";
                        }
                        $dmr2ysfContent .= "\n";
                }
        if (!$handleDMR2YSFconfig = fopen('/tmp/dhJSgdy7755HGc.tmp', 'w')) {
                return false;
        }
        if (!is_writable('/tmp/dhJSgdy7755HGc.tmp')) {
          echo "<br />\n";
          echo "<table>\n";
          echo "<tr><th>ERROR</th></tr>\n";
          echo "<tr><td>Unable to write configuration file(s)...</td><tr>\n";
          echo "<tr><td>Please wait a few seconds and retry...</td></tr>\n";
          echo "</table>\n";
          unset($_POST);
          echo '<script type="text/javascript">setTimeout(function() { window.location=window.location;},5000);</script>';
          die();
        }
        else {
                $success = fwrite($handleDMR2YSFconfig, $dmr2ysfContent);
                fclose($handleDMR2YSFconfig);
                if (intval(exec('cat /tmp/dhJSgdy7755HGc.tmp | wc -l')) > 25 ) {
                        exec('sudo mv /tmp/dhJSgdy7755HGc.tmp /etc/dmr2ysf');		// Move the file back
                        exec('sudo chmod 644 /etc/dmr2ysf');				// Set the correct runtime permissions
                        exec('sudo chown root:root /etc/dmr2ysf');			// Set the owner
                }
        }

	// dmr2nxdn config file wrangling
        $dmr2nxdnContent = "";
        foreach($configdmr2nxdn as $dmr2nxdnSection=>$dmr2nxdnValues) {
                // UnBreak special cases
                $dmr2nxdnSection = str_replace("_", " ", $dmr2nxdnSection);
                $dmr2nxdnContent .= "[".$dmr2nxdnSection."]\n";
                // append the values
                foreach($dmr2nxdnValues as $dmr2nxdnKey=>$dmr2nxdnValue) {
                        $dmr2nxdnContent .= $dmr2nxdnKey."=".$dmr2nxdnValue."\n";
                        }
                        $dmr2nxdnContent .= "\n";
                }
        if (!$handleDMR2NXDNconfig = fopen('/tmp/nthfheS55HGc.tmp', 'w')) {
                return false;
        }
        if (!is_writable('/tmp/nthfheS55HGc.tmp')) {
          echo "<br />\n";
          echo "<table>\n";
          echo "<tr><th>ERROR</th></tr>\n";
          echo "<tr><td>Unable to write configuration file(s)...</td><tr>\n";
          echo "<tr><td>Please wait a few seconds and retry...</td></tr>\n";
          echo "</table>\n";
          unset($_POST);
          echo '<script type="text/javascript">setTimeout(function() { window.location=window.location;},5000);</script>';
          die();
        }
        else {
                $success = fwrite($handleDMR2NXDNconfig, $dmr2nxdnContent);
                fclose($handleDMR2NXDNconfig);
                if (intval(exec('cat /tmp/nthfheS55HGc.tmp | wc -l')) > 25 ) {
                        exec('sudo mv /tmp/nthfheS55HGc.tmp /etc/dmr2nxdn');		// Move the file back
                        exec('sudo chmod 644 /etc/dmr2nxdn');				// Set the correct runtime permissions
                        exec('sudo chown root:root /etc/dmr2nxdn');			// Set the owner
                }
        }

        // DAPNet Gateway Config file wragling
	$dapnetContent = "";
        foreach($configdapnetgw as $dapnetSection=>$dapnetValues) {
                // UnBreak special cases
                $dapnetSection = str_replace("_", " ", $dapnetSection);
                $dapnetContent .= "[".$dapnetSection."]\n";
                // append the values
                foreach($dapnetValues as $dapnetKey=>$dapnetValue) {
                        $dapnetContent .= $dapnetKey."=".$dapnetValue."\n";
                        }
                        $dapnetContent .= "\n";
                }
        if (!$handledapnetconfig = fopen('/tmp/lsHWie734HS.tmp', 'w')) {
                return false;
        }
        if (!is_writable('/tmp/lsHWie734HS.tmp')) {
          echo "<br />\n";
          echo "<table>\n";
          echo "<tr><th>ERROR</th></tr>\n";
          echo "<tr><td>Unable to write configuration file(s)...</td><tr>\n";
          echo "<tr><td>Please wait a few seconds and retry...</td></tr>\n";
          echo "</table>\n";
          unset($_POST);
          echo '<script type="text/javascript">setTimeout(function() { window.location=window.location;},5000);</script>';
          die();
        }
        else {
                $success = fwrite($handledapnetconfig, $dapnetContent);
                fclose($handledapnetconfig);
                if (intval(exec('cat /tmp/lsHWie734HS.tmp | wc -l')) > 19 ) {
                        exec('sudo mv /tmp/lsHWie734HS.tmp /etc/dapnetgateway');		// Move the file back
                        exec('sudo chmod 644 /etc/dapnetgateway');				// Set the correct runtime permissions
                        exec('sudo chown root:root /etc/dapnetgateway');			// Set the owner
                }
        }
	// DAPNet API Key file wragling
        if ( isset($configdapnetapi) ) {
		$dapnetAPIContent = "";
		foreach($configdapnetapi as $dapnetAPISection=>$dapnetAPIValues) {
			// UnBreak special cases
			$dapnetAPISection = str_replace("_", " ", $dapnetAPISection);
			$dapnetAPIContent .= "[".$dapnetAPISection."]\n";
			// append the values
			foreach($dapnetAPIValues as $dapnetAPIKey=>$dapnetAPIValue) {
				$dapnetAPIContent .= $dapnetAPIKey."=".$dapnetAPIValue."\n";
			}
			$dapnetAPIContent .= "\n";
		}
		if (!$handledapnetapi = fopen('/tmp/jsADGHwf9sj294.tmp', 'w')) {
			return false;
		}
		if (!is_writable('/tmp/jsADGHwf9sj294.tmp')) {
			echo "<br />\n";
			echo "<table>\n";
			echo "<tr><th>ERROR</th></tr>\n";
			echo "<tr><td>Unable to write configuration file(s)...</td><tr>\n";
			echo "<tr><td>Please wait a few seconds and retry...</td></tr>\n";
			echo "</table>\n";
			unset($_POST);
			echo '<script type="text/javascript">setTimeout(function() { window.location=window.location;},5000);</script>';
			die();
		}
		else {
			$success = fwrite($handledapnetapi, $dapnetAPIContent);
			fclose($handledapnetapi);
			if (intval(exec('cat /tmp/jsADGHwf9sj294.tmp | wc -l')) > 3 ) {
				exec('sudo mv /tmp/jsADGHwf9sj294.tmp /etc/dapnetapi.key');		// Move the file back
				exec('sudo chmod 644 /etc/dapnetapi.key');				// Set the correct runtime permissions
				exec('sudo chown root:root /etc/dapnetapi.key');			// Set the owner
			}
		}
	}

	// dmrgateway config file wrangling
	$dmrgwContent = "";
        foreach($configdmrgateway as $dmrgwSection=>$dmrgwValues) {
                // UnBreak special cases
                $dmrgwSection = str_replace("_", " ", $dmrgwSection);
                $dmrgwContent .= "[".$dmrgwSection."]\n";
                // append the values
                foreach($dmrgwValues as $dmrgwKey=>$dmrgwValue) {
                        $dmrgwContent .= $dmrgwKey."=".$dmrgwValue."\n";
                        }
                        $dmrgwContent .= "\n";
                }
        if (!$handledmrGWconfig = fopen('/tmp/k4jhdd34jeFr8f.tmp', 'w')) {
                return false;
        }
	if (!is_writable('/tmp/k4jhdd34jeFr8f.tmp')) {
          echo "<br />\n";
          echo "<table>\n";
          echo "<tr><th>ERROR</th></tr>\n";
          echo "<tr><td>Unable to write configuration file(s)...</td><tr>\n";
          echo "<tr><td>Please wait a few seconds and retry...</td></tr>\n";
          echo "</table>\n";
          unset($_POST);
          echo '<script type="text/javascript">setTimeout(function() { window.location=window.location;},5000);</script>';
          die();
	}
	else {
	        $success = fwrite($handledmrGWconfig, $dmrgwContent);
	        fclose($handledmrGWconfig);
		if (fopen($dmrGatewayConfigFile,'r')) {
			if (intval(exec('cat /tmp/k4jhdd34jeFr8f.tmp | wc -l')) > 55 ) {
          			exec('sudo mv /tmp/k4jhdd34jeFr8f.tmp /etc/dmrgateway');	// Move the file back
          			exec('sudo chmod 644 /etc/dmrgateway');				// Set the correct runtime permissions
	 			exec('sudo chown root:root /etc/dmrgateway');			// Set the owner
			}
		}
	}

	// modem config file wrangling
        $configModemContent = "";
        foreach($configModem as $configModemSection=>$configModemValues) {
                // UnBreak special cases
                $configModemSection = str_replace("_", " ", $configModemSection);
                $configModemContent .= "[".$configModemSection."]\n";
                // append the values
                foreach($configModemValues as $modemKey=>$modemValue) {
                        $configModemContent .= $modemKey."=".$modemValue."\n";
                        }
                        $configModemContent .= "\n";
                }

        if (!$handleModemConfig = fopen('/tmp/sja7hFRkw4euG7.tmp', 'w')) {
                return false;
        }

        if (!is_writable('/tmp/sja7hFRkw4euG7.tmp')) {
          echo "<br />\n";
          echo "<table>\n";
          echo "<tr><th>ERROR</th></tr>\n";
          echo "<tr><td>Unable to write configuration file(s)...</td><tr>\n";
          echo "<tr><td>Please wait a few seconds and retry...</td></tr>\n";
          echo "</table>\n";
          unset($_POST);
          echo '<script type="text/javascript">setTimeout(function() { window.location=window.location;},5000);</script>';
          die();
        }
	else {
                $success = fwrite($handleModemConfig, $configModemContent);
                fclose($handleModemConfig);
		if (file_exists('/etc/dstar-radio.dstarrepeater')) {
                    if (fopen($modemConfigFileDStarRepeater,'r')) {
                        exec('sudo mv /tmp/sja7hFRkw4euG7.tmp '.$modemConfigFileDStarRepeater);	// Move the file back
                        exec('sudo chmod 644 $modemConfigFileDStarRepeater');			// Set the correct runtime permissions
                        exec('sudo chown root:root $modemConfigFileDStarRepeater');			// Set the owner
                    }
		}
		if (file_exists('/etc/dstar-radio.mmdvmhost')) {
                    if (fopen($modemConfigFileMMDVMHost,'r')) {
                        exec('sudo mv /tmp/sja7hFRkw4euG7.tmp '.$modemConfigFileMMDVMHost);		// Move the file back
                        exec('sudo chmod 644 $modemConfigFileMMDVMHost');				// Set the correct runtime permissions
                        exec('sudo chown root:root $modemConfigFileMMDVMHost');			// Set the owner
                    }
		}
    }

	// Start the DV Services
	system('sudo systemctl daemon-reload > /dev/null 2>/dev/null &');			// Restart Systemd to account for any service changes
	system('sudo systemctl start gpsd.service > /dev/null 2>/dev/null &');			// GPSd Service
	system('sudo systemctl start dstarrepeater.service > /dev/null 2>/dev/null &');		// D-Star Radio Service
	system('sudo systemctl start mmdvmhost.service > /dev/null 2>/dev/null &');		// MMDVMHost Radio Service
	system('sudo systemctl start ircddbgateway.service > /dev/null 2>/dev/null &');		// ircDDBGateway Service
	system('sudo systemctl start timeserver.service > /dev/null 2>/dev/null &');		// Time Server Service
	system('sudo systemctl start pistar-watchdog.service > /dev/null 2>/dev/null &');	// PiStar-Watchdog Service
	system('sudo systemctl start pistar-remote.service > /dev/null 2>/dev/null &');		// PiStar-Remote Service
	if (empty($_POST['uPNP']) != TRUE ) {
		if (escapeshellcmd($_POST['uPNP']) == 'ON' ) { system('sudo systemctl start pistar-upnp.service > /dev/null 2>/dev/null &'); }
	}
//	system('sudo systemctl start ysf2dmr.service > /dev/null 2>/dev/null &');		// YSF2DMR
	system('sudo systemctl start ysf2nxdn.service > /dev/null 2>/dev/null &');		// YSF2NXDN
	system('sudo systemctl start ysf2p25.service > /dev/null 2>/dev/null &');		// YSF2P25
	system('sudo systemctl start nxdn2dmr.service > /dev/null 2>/dev/null &');		// NXDN2DMR
	system('sudo systemctl start ysfgateway.service > /dev/null 2>/dev/null &');		// YSFGateway
	system('sudo systemctl start ysfparrot.service > /dev/null 2>/dev/null &');		// YSFParrot
	system('sudo systemctl start p25gateway.service > /dev/null 2>/dev/null &');		// P25Gateway
	system('sudo systemctl start p25parrot.service > /dev/null 2>/dev/null &');		// P25Parrot
	system('sudo systemctl start nxdngateway.service > /dev/null 2>/dev/null &');		// NXDNGateway
	system('sudo systemctl start nxdnparrot.service > /dev/null 2>/dev/null &');		// NXDNParrot
	system('sudo systemctl start dmr2ysf.service > /dev/null 2>/dev/null &');		// DMR2YSF
	system('sudo systemctl start dmr2nxdn.service > /dev/null 2>/dev/null &');		// DMR2NXDN
	system('sudo systemctl start dmrgateway.service > /dev/null 2>/dev/null &');		// DMRGateway
	system('sudo systemctl start dapnetgateway.service > /dev/null 2>/dev/null &');		// DAPNetGateway

	// Set the system timezone
	if (empty($_POST['systemTimezone']) != TRUE) {
	   $rollTimeZone = 'sudo timedatectl set-timezone '.escapeshellcmd($_POST['systemTimezone']);
	   system($rollTimeZone);
	   $rollTimeZoneConfig = 'sudo sed -i "/date_default_timezone_set/c\\date_default_timezone_set(\''.escapeshellcmd($_POST['systemTimezone']).'\')\;" /var/www/dashboard/config/config.php';
	   system($rollTimeZoneConfig);
	}

	// Start Cron (occasionally remounts root as RO - would be bad if it did this at the wrong time....)
	system('sudo systemctl start cron.service > /dev/null 2>/dev/null &');			//Cron

	unset($_POST);
	echo '<script type="text/javascript">setTimeout(function() { window.location=window.location;},7500);</script>';

	// Make the root filesystem read-only
	system('sudo mount -o remount,ro /');

else:
	// Output the HTML Form here
	if ((file_exists('/etc/dstar-radio.mmdvmhost') || file_exists('/etc/dstar-radio.dstarrepeater')) && !$configModem['Modem']['Hardware']) { echo "<script type\"text/javascript\">\n\talert(\"WARNING:\\nThe Modem selection section has been updated,\\nPlease re-select your modem from the list.\")\n</script>\n";$configModem['Modem']['Hardware'] = "None"; }
?>
<form id="factoryReset" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
	<div><input type="hidden" name="factoryReset" value="1" /></div>
</form>

<form id="config" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
	<h2><?php echo $lang['control_software'];?></h2>
    <table>
    <tr>
    <th width="200"><a class="tooltip" href="#"><?php echo $lang['setting'];?><span><b>Setting</b></span></a></th>
    <th colspan="2"><a class="tooltip" href="#"><?php echo $lang['value'];?><span><b>Value</b>The current value from<br />the configuration files</span></a></th>
    </tr>
    <tr>
    <td align="left"><a class="tooltip2" href="#"><?php echo $lang['controller_software'];?>:<span><b>Radio Control Software</b>Choose the software used to control the DV Radio Module. Please note that DV Mega hardware will require a firmware upgrade.</span></a></td>
    <?php
	if (file_exists('/etc/dstar-radio.mmdvmhost')) {
		echo "   <td align=\"left\" colspan=\"2\"><input type=\"radio\" name=\"controllerSoft\" value=\"DSTAR\" onclick=\"alert('After applying your Configuration Settings, you will need to powercycle your Pi.');\" />DStarRepeater <input type=\"radio\" name=\"controllerSoft\" value=\"MMDVM\" checked=\"checked\" />MMDVMHost (DV-Mega Minimum Firmware 3.07 Required)</td>\n";
		}
	elseif (file_exists('/etc/dstar-radio.dstarrepeater')) {
		echo "   <td align=\"left\" colspan=\"2\"><input type=\"radio\" name=\"controllerSoft\" value=\"DSTAR\" checked=\"checked\" />DStarRepeater <input type=\"radio\" name=\"controllerSoft\" value=\"MMDVM\" onclick=\"alert('After applying your Configuration Settings, you will need to powercycle your Pi.');\" />MMDVMHost (DV-Mega Minimum Firmware 3.07 Required)</td>\n";
	}
	else { // Not set - default to MMDVMHost
		echo "   <td align=\"left\" colspan=\"2\"><input type=\"radio\" name=\"controllerSoft\" value=\"DSTAR\" onclick=\"alert('After applying your Configuration Settings, you will need to powercycle your Pi.');\" />DStarRepeater <input type=\"radio\" name=\"controllerSoft\" value=\"MMDVM\" checked=\"checked\" />MMDVMHost (DV-Mega Minimum Firmware 3.07 Required)</td>\n";
	}
    ?>
    </tr>
    <tr>
    <td align="left"><a class="tooltip2" href="#"><?php echo $lang['controller_mode'];?>:<span><b>TRX Mode</b>Choose the mode type Simplex node or Duplex repeater.</span></a></td>
    <?php
	if ($configmmdvm['Info']['RXFrequency'] === $configmmdvm['Info']['TXFrequency']) {
		echo "   <td align=\"left\" colspan=\"2\"><input type=\"radio\" name=\"trxMode\" value=\"SIMPLEX\" checked=\"checked\" />Simplex Node <input type=\"radio\" name=\"trxMode\" value=\"DUPLEX\" />Duplex Repeater (or Half-Duplex on Hotspots)</td>\n";
		}
	else {
		echo "   <td align=\"left\" colspan=\"2\"><input type=\"radio\" name=\"trxMode\" value=\"SIMPLEX\" />Simplex Node <input type=\"radio\" name=\"trxMode\" value=\"DUPLEX\" checked=\"checked\" />Duplex Repeater (or Half-Duplex on Hotspots)</td>\n";
		}
    ?>
    </tr>
    </table>
	<div><input type="button" value="<?php echo $lang['apply'];?>" onclick="submitform()" /><br /><br /></div>
<?php if (file_exists('/etc/dstar-radio.mmdvmhost')) { ?>
    <input type="hidden" name="MMDVMModeDMR" value="OFF" />
    <input type="hidden" name="MMDVMModeDSTAR" value="OFF" />
    <input type="hidden" name="MMDVMModeFUSION" value="OFF" />
    <input type="hidden" name="MMDVMModeP25" value="OFF" />
    <input type="hidden" name="MMDVMModeNXDN" value="OFF" />
    <input type="hidden" name="MMDVMModeYSF2NXDN" value="OFF" />
    <input type="hidden" name="MMDVMModeYSF2P25" value="OFF" />
    <input type="hidden" name="MMDVMModeDMR2YSF" value="OFF" />
    <input type="hidden" name="MMDVMModeDMR2NXDN" value="OFF" />
    <input type="hidden" name="MMDVMModePOCSAG" value="OFF" />
    <input type="hidden" name="GPSD" value="OFF" />
    <input type="hidden" name="pocsagWhitelist" value="<?php if (isset($configdapnetgw['General']['WhiteList'])) { echo $configdapnetgw['General']['WhiteList']; } else { echo ""; } ?>" />
    <input type="hidden" name="pocsagBlacklist" value="<?php if (isset($configdapnetgw['General']['BlackList'])) { echo $configdapnetgw['General']['BlackList']; } else { echo ""; } ?>" />
	<h2><?php echo $lang['mmdvmhost_config'];?></h2>
    <table>
    <tr>
    <th width="200"><a class="tooltip" href="#"><?php echo $lang['setting'];?><span><b>Setting</b></span></a></th>
    <th colspan="2"><a class="tooltip" href="#"><?php echo $lang['value'];?><span><b>Value</b>The current value from<br />the configuration files</span></a></th>
    </tr>
    <tr>
    <td align="left"><a class="tooltip2" href="#"><?php echo $lang['dmr_mode'];?>:<span><b>DMR Mode</b>Turn on DMR Features</span></a></td>
    <?php
	if ( $configmmdvm['DMR']['Enable'] == 1 ) {
	    echo "<td align=\"left\"><div class=\"switch\"><input id=\"toggle-dmr\" class=\"toggle toggle-round-flat\" type=\"checkbox\" name=\"MMDVMModeDMR\" value=\"ON\" checked=\"checked\" aria-checked=\"true\" aria-label=\"Turn DMR Mode Off\" /><label for=\"toggle-dmr\"></label></div></td>\n";
		}
	else {
	    echo "<td align=\"left\"><div class=\"switch\"><input id=\"toggle-dmr\" class=\"toggle toggle-round-flat\" type=\"checkbox\" name=\"MMDVMModeDMR\" value=\"ON\" aria-checked=\"false\" aria-label=\"Turn DMR Mode On\" /><label for=\"toggle-dmr\"></label></div></td>\n";
	}
    ?>
    <td>RF Hangtime: <input type="text" name="dmrRfHangTime" size="7" maxlength="3" value="<?php if (isset($configmmdvm['DMR']['ModeHang'])) { echo $configmmdvm['DMR']['ModeHang']; } else { echo "20"; } ?>" />
    Net Hangtime: <input type="text" name="dmrNetHangTime" size="7" maxlength="3" value="<?php if (isset($configmmdvm['DMR Network']['ModeHang'])) { echo $configmmdvm['DMR Network']['ModeHang']; } else { echo "20"; } ?>" />
    </td>
    </tr>
    <tr>
    <td align="left"><a class="tooltip2" href="#"><?php echo $lang['d-star_mode'];?>:<span><b>D-Star Mode</b>Turn on D-Star Features</span></a></td>
    <?php
	if ( $configmmdvm['D-Star']['Enable'] == 1 ) {
		echo "<td align=\"left\"><div class=\"switch\"><input id=\"toggle-dstar\" class=\"toggle toggle-round-flat\" type=\"checkbox\" name=\"MMDVMModeDSTAR\" value=\"ON\" checked=\"checked\" /><label for=\"toggle-dstar\"></label></div></td>\n";
		}
	else {
		echo "<td align=\"left\"><div class=\"switch\"><input id=\"toggle-dstar\" class=\"toggle toggle-round-flat\" type=\"checkbox\" name=\"MMDVMModeDSTAR\" value=\"ON\" /><label for=\"toggle-dstar\"></label></div></td>\n";
	}
    ?>
    <td>RF Hangtime: <input type="text" name="dstarRfHangTime" size="7" maxlength="3" value="<?php if (isset($configmmdvm['D-Star']['ModeHang'])) { echo $configmmdvm['D-Star']['ModeHang']; } else { echo "20"; } ?>" />
    Net Hangtime: <input type="text" name="dstarNetHangTime" size="7" maxlength="3" value="<?php if (isset($configmmdvm['D-Star Network']['ModeHang'])) { echo $configmmdvm['D-Star Network']['ModeHang']; } else { echo "20"; } ?>" />
    </td>
    </tr>
    <tr>
    <td align="left"><a class="tooltip2" href="#"><?php echo $lang['ysf_mode'];?>:<span><b>YSF Mode</b>Turn on YSF Features</span></a></td>
    <?php
	if ( $configmmdvm['System Fusion']['Enable'] == 1 ) {
		echo "<td align=\"left\"><div class=\"switch\"><input id=\"toggle-ysf\" class=\"toggle toggle-round-flat\" type=\"checkbox\" name=\"MMDVMModeFUSION\" value=\"ON\" checked=\"checked\" /><label for=\"toggle-ysf\"></label></div></td>\n";
		}
	else {
		echo "<td align=\"left\"><div class=\"switch\"><input id=\"toggle-ysf\" class=\"toggle toggle-round-flat\" type=\"checkbox\" name=\"MMDVMModeFUSION\" value=\"ON\" /><label for=\"toggle-ysf\"></label></div></td>\n";
	}
    ?>
    <td>RF Hangtime: <input type="text" name="ysfRfHangTime" size="7" maxlength="3" value="<?php if (isset($configmmdvm['System Fusion']['ModeHang'])) { echo $configmmdvm['System Fusion']['ModeHang']; } else { echo "20"; } ?>" />
    Net Hangtime: <input type="text" name="ysfNetHangTime" size="7" maxlength="3" value="<?php if (isset($configmmdvm['System Fusion Network']['ModeHang'])) { echo $configmmdvm['System Fusion Network']['ModeHang']; } else { echo "20"; } ?>" />
    </td>
    </tr>
    <tr>
    <td align="left"><a class="tooltip2" href="#"><?php echo $lang['p25_mode'];?>:<span><b>P25 Mode</b>Turn on P25 Features</span></a></td>
    <?php
	if ( $configmmdvm['P25']['Enable'] == 1 ) {
		echo "<td align=\"left\"><div class=\"switch\"><input id=\"toggle-p25\" class=\"toggle toggle-round-flat\" type=\"checkbox\" name=\"MMDVMModeP25\" value=\"ON\" checked=\"checked\" /><label for=\"toggle-p25\"></label></div></td>\n";
		}
	else {
		echo "<td align=\"left\"><div class=\"switch\"><input id=\"toggle-p25\" class=\"toggle toggle-round-flat\" type=\"checkbox\" name=\"MMDVMModeP25\" value=\"ON\" /><label for=\"toggle-p25\"></label></div></td>\n";
	}
    ?>
    <td>RF Hangtime: <input type="text" name="p25RfHangTime" size="7" maxlength="3" value="<?php if (isset($configmmdvm['P25']['ModeHang'])) { echo $configmmdvm['P25']['ModeHang']; } else { echo "20"; } ?>" />
    Net Hangtime: <input type="text" name="p25NetHangTime" size="7" maxlength="3" value="<?php if (isset($configmmdvm['P25 Network']['ModeHang'])) { echo $configmmdvm['P25 Network']['ModeHang']; } else { echo "20"; } ?>" />
    </td>
    </tr>
    <tr>
    <td align="left"><a class="tooltip2" href="#"><?php echo $lang['nxdn_mode'];?>:<span><b>NXDN Mode</b>Turn on NXDN Features</span></a></td>
    <?php
	if ( $configmmdvm['NXDN']['Enable'] == 1 ) {
		echo "<td align=\"left\"><div class=\"switch\"><input id=\"toggle-nxdn\" class=\"toggle toggle-round-flat\" type=\"checkbox\" name=\"MMDVMModeNXDN\" value=\"ON\" checked=\"checked\" /><label for=\"toggle-nxdn\"></label></div></td>\n";
		}
	else {
		echo "<td align=\"left\"><div class=\"switch\"><input id=\"toggle-nxdn\" class=\"toggle toggle-round-flat\" type=\"checkbox\" name=\"MMDVMModeNXDN\" value=\"ON\" /><label for=\"toggle-nxdn\"></label></div></td>\n";
	}
    ?>
    <td>RF Hangtime: <input type="text" name="nxdnRfHangTime" size="7" maxlength="3" value="<?php if (isset($configmmdvm['NXDN']['ModeHang'])) { echo $configmmdvm['NXDN']['ModeHang']; } else { echo "20"; } ?>" />
    Net Hangtime: <input type="text" name="nxdnNetHangTime" size="7" maxlength="3" value="<?php if (isset($configmmdvm['NXDN Network']['ModeHang'])) { echo $configmmdvm['NXDN Network']['ModeHang']; } else { echo "20"; } ?>" />
    </td>
    </tr>

    <?php if (file_exists('/etc/ysf2nxdn')) { ?>
    <tr>
    <td align="left"><a class="tooltip2" href="#">YSF2NXDN:<span><b>YSF2NXDN Mode</b>Turn on YSF2NXDN Features</span></a></td>
    <?php
	if ( $configysf2nxdn['Enabled']['Enabled'] == 1 ) {
		echo "<td colspan=\"2\" align=\"left\"><div class=\"switch\"><input id=\"toggle-ysf2nxdn\" class=\"toggle toggle-round-flat\" type=\"checkbox\" name=\"MMDVMModeYSF2NXDN\" value=\"ON\" checked=\"checked\" /><label for=\"toggle-ysf2nxdn\"></label></div></td>\n";
		}
	else {
		echo "<td colspan=\"2\" align=\"left\"><div class=\"switch\"><input id=\"toggle-ysf2nxdn\" class=\"toggle toggle-round-flat\" type=\"checkbox\" name=\"MMDVMModeYSF2NXDN\" value=\"ON\" /><label for=\"toggle-ysf2nxdn\"></label></div></td>\n";
	}
    ?>
    </tr>
    <?php } ?>
    <?php if (file_exists('/etc/ysf2p25')) { ?>
    <tr>
    <td align="left"><a class="tooltip2" href="#">YSF2P25:<span><b>YSF2P25 Mode</b>Turn on YSF2P25 Features</span></a></td>
    <?php
	if ( $configysf2p25['Enabled']['Enabled'] == 1 ) {
		echo "<td colspan=\"2\" align=\"left\"><div class=\"switch\"><input id=\"toggle-ysf2p25\" class=\"toggle toggle-round-flat\" type=\"checkbox\" name=\"MMDVMModeYSF2P25\" value=\"ON\" checked=\"checked\" /><label for=\"toggle-ysf2p25\"></label></div></td>\n";
		}
	else {
		echo "<td colspan=\"2\" align=\"left\"><div class=\"switch\"><input id=\"toggle-ysf2p25\" class=\"toggle toggle-round-flat\" type=\"checkbox\" name=\"MMDVMModeYSF2P25\" value=\"ON\" /><label for=\"toggle-ysf2p25\"></label></div></td>\n";
	}
    ?>
    </tr>
    <?php } ?>
    <?php if (file_exists('/etc/dmr2ysf')) { ?>
    <tr>
    <td align="left"><a class="tooltip2" href="#">DMR2YSF:<span><b>DMR2YSF Mode</b>Turn on DMR2YSF Features</span></a></td>
    <?php
	if ( $configdmr2ysf['Enabled']['Enabled'] == 1 ) {
		echo "<td align=\"left\"><div class=\"switch\"><input id=\"toggle-dmr2ysf\" class=\"toggle toggle-round-flat\" type=\"checkbox\" name=\"MMDVMModeDMR2YSF\" value=\"ON\" checked=\"checked\" /><label for=\"toggle-dmr2ysf\"></label></div></td>\n";
		}
	else {
		echo "<td align=\"left\"><div class=\"switch\"><input id=\"toggle-dmr2ysf\" class=\"toggle toggle-round-flat\" type=\"checkbox\" name=\"MMDVMModeDMR2YSF\" value=\"ON\" /><label for=\"toggle-dmr2ysf\"></label></div></td>\n";
	}
    ?>
    <td>Uses 7 prefix on DMRGateway</td>
    </tr>
    <?php } ?>
    <?php if (file_exists('/etc/dmr2nxdn')) { ?>
    <tr>
    <td align="left"><a class="tooltip2" href="#">DMR2NXDN:<span><b>DMR2NXDN Mode</b>Turn on DMR2NXDN Features</span></a></td>
    <?php
	if ( $configdmr2nxdn['Enabled']['Enabled'] == 1 ) {
		echo "<td align=\"left\"><div class=\"switch\"><input id=\"toggle-dmr2nxdn\" class=\"toggle toggle-round-flat\" type=\"checkbox\" name=\"MMDVMModeDMR2NXDN\" value=\"ON\" checked=\"checked\" /><label for=\"toggle-dmr2nxdn\"></label></div></td>\n";
		}
	else {
		echo "<td align=\"left\"><div class=\"switch\"><input id=\"toggle-dmr2nxdn\" class=\"toggle toggle-round-flat\" type=\"checkbox\" name=\"MMDVMModeDMR2NXDN\" value=\"ON\" /><label for=\"toggle-dmr2nxdn\"></label></div></td>\n";
	}
    ?>
    <td>Uses 7 prefix on DMRGateway</td>
    </tr>
    <?php } ?>
    <?php if (file_exists('/etc/dapnetgateway')) { ?>
    <tr>
    <td align="left"><a class="tooltip2" href="#">POCSAG:<span><b>POCSAG Mode</b>Turn on POCSAG Features</span></a></td>
    <?php
	if ( $configmmdvm['POCSAG']['Enable'] == 1 ) {
		echo "<td align=\"left\"><div class=\"switch\"><input id=\"toggle-pocsag\" class=\"toggle toggle-round-flat\" type=\"checkbox\" name=\"MMDVMModePOCSAG\" value=\"ON\" checked=\"checked\" /><label for=\"toggle-pocsag\"></label></div></td>\n";
		}
	else {
		echo "<td align=\"left\"><div class=\"switch\"><input id=\"toggle-pocsag\" class=\"toggle toggle-round-flat\" type=\"checkbox\" name=\"MMDVMModePOCSAG\" value=\"ON\" /><label for=\"toggle-pocsag\"></label></div></td>\n";
	}
    ?>
    <td>POCSAG Paging Features</td>
    </tr>
    <?php } ?>
    <tr>
    <td align="left"><a class="tooltip2" href="#"><?php echo $lang['mmdvm_display'];?>:<span><b>Display Type</b>Choose your display type, if you have one.</span></a></td>
    <td align="left" colspan="2"><select name="mmdvmDisplayType">
	    <option <?php if (($configmmdvm['General']['Display'] == "None") || ($configmmdvm['General']['Display'] == "") ) {echo 'selected="selected" ';}; ?>value="None">None</option>
	    <option <?php if (($configmmdvm['General']['Display'] == "OLED") && ($configmmdvm['OLED']['Type'] == "3")) {echo 'selected="selected" ';}; ?>value="OLED3">OLED Type 3</option>
	    <option <?php if (($configmmdvm['General']['Display'] == "OLED") && ($configmmdvm['OLED']['Type'] == "6")) {echo 'selected="selected" ';}; ?>value="OLED6">OLED Type 6</option>
	    <option <?php if ($configmmdvm['General']['Display'] == "Nextion") {echo 'selected="selected" ';}; ?>value="Nextion">Nextion</option>
	    <option <?php if ($configmmdvm['General']['Display'] == "HD44780") {echo 'selected="selected" ';}; ?>value="HD44780">HD44780</option>
	    <option <?php if ($configmmdvm['General']['Display'] == "TFT Serial") {echo 'selected="selected" ';}; ?>value="TFT Serial">TFT Serial</option>
	    <option <?php if ($configmmdvm['General']['Display'] == "LCDproc") {echo 'selected="selected" ';}; ?>value="LCDproc">LCDproc</option>
	    <option <?php if ($configmmdvm['General']['Display'] == "CAST") {echo 'selected="selected" ';}; ?>value="CAST">CAST</option>
	    </select>
	    Port: <select name="mmdvmDisplayPort">
	    <?php
            if (($configmmdvm['General']['Display'] == "None") || ($configmmdvm['General']['Display'] == "")) {
                echo '      <option selected="selected" value="None">None</option>'."\n";
            } else {
                echo '      <option value="None">None</option>'."\n";
            }
            if (isset($configmmdvm['Nextion']['Port'])) {
                if ($configmmdvm['Nextion']['Port'] == "modem") {
                        echo '      <option selected="selected" value="modem">modem</option>'."\n";
                } else {
                        echo '      <option value="modem">modem</option>'."\n";
                }
                if ( ($configmmdvm['Nextion']['Port'] == "None") || ($configmmdvm['Nextion']['Port'] == "" )) { } else {
			$currentPort = str_replace($configmmdvm['Nextion']['Port'], "/dev/", "");
                        echo '      <option selected="selected" value="'.$currentPort.'">'.$configmmdvm['Nextion']['Port'].'</option>'."\n";
                }
            }
            exec('ls /dev/ | egrep -h "ttyA|ttyUSB"', $availablePorts);
            foreach($availablePorts as $port) {
                 echo "     <option value=\"$port\">/dev/$port</option>\n";
            }
	    ?>
	    <?php if (file_exists('/dev/ttyS2')) { ?>
	    <option <?php if ($configmmdvm['Nextion']['Port'] == "/dev/ttyS2") {echo 'selected="selected" ';}; ?>value="ttyS2">/dev/ttyS2</option>
    	    <?php } ?>
	    <?php if (file_exists('/dev/ttyNextionDriver')) { ?>
	    <option <?php if ($configmmdvm['Nextion']['Port'] == "/dev/ttyNextionDriver") {echo 'selected="selected" ';}; ?>value="ttyNextionDriver">/dev/ttyNextionDriver</option>
    	    <?php } ?>
	    </select>
	    Nextion Layout: <select name="mmdvmNextionDisplayType">
	    <option <?php if ($configmmdvm['Nextion']['ScreenLayout'] == "0") {echo 'selected="selected" ';}; ?>value="G4KLX">G4KLX</option>
	    <option <?php if ($configmmdvm['Nextion']['ScreenLayout'] == "2") {echo 'selected="selected" ';}; ?>value="ON7LDSL2">ON7LDS L2</option>
	    <option <?php if ($configmmdvm['Nextion']['ScreenLayout'] == "3") {echo 'selected="selected" ';}; ?>value="ON7LDSL3">ON7LDS L3</option>
	    <option <?php if ($configmmdvm['Nextion']['ScreenLayout'] == "4") {echo 'selected="selected" ';}; ?>value="ON7LDSL3HS">ON7LDS L3 HS</option>
	    </select>
    </td></tr>
    <!--<tr>
    <td align="left"><a class="tooltip2" href="#"><?php echo $lang['mode_hangtime'];?>:<span><b>Net Hang Time</b>Stay in the last mode for this many seconds</span></a></td>
    <td align="left" colspan="2"><input type="text" name="hangTime" size="13" maxlength="3" value="<?php echo $configmmdvm['General']['RFModeHang']; ?>" /> in seconds (90 secs works well for Multi-Mode)</td>
    </tr>-->
    </table>
	<div><input type="button" value="<?php echo $lang['apply'];?>" onclick="submitform()" /><br /><br /></div>
    <?php } ?>
	<h2><?php echo $lang['general_config'];?></h2>
    <table>
    <tr>
    <th width="200"><a class="tooltip" href="#"><?php echo $lang['setting'];?><span><b>Setting</b></span></a></th>
    <th colspan="2"><a class="tooltip" href="#"><?php echo $lang['value'];?><span><b>Value</b>The current value from the<br />configuration files</span></a></th>
    </tr>
    <tr>
    <td align="left"><a class="tooltip2" href="#"><?php echo $lang['hostname'];?>:<span><b>System Hostname</b>This is the system hostname, used for access to the dashboard etc.</span></a></td>
    <td align="left" colspan="2"><input type="text" name="confHostname" size="13" maxlength="15" value="<?php echo exec('cat /etc/hostname'); ?>" />Do not add suffixes such as .local</td>
    </tr>
    <tr>
    <td align="left"><a class="tooltip2" href="#"><?php echo $lang['node_call'];?>:<span><b>Gateway Callsign</b>This is your licenced callsign for use on this gateway, do not append the "G"</span></a></td>
    <td align="left" colspan="2"><input type="text" name="confCallsign" size="13" maxlength="7" value="<?php echo $configs['gatewayCallsign'] ?>" /></td>
    </tr>
    <?php if (file_exists('/etc/dstar-radio.mmdvmhost') && (($configmmdvm['DMR']['Enable'] == 1) || ($configmmdvm['P25']['Enable'] == 1 ) || ($configmmdvm['System Fusion']['Enable'] == 1 ))) { ?>
    <tr>
    <td align="left"><a class="tooltip2" href="#"><?php echo $lang['dmr_id'];?>:<span><b>CCS7/DMR ID</b>Enter your CCS7 / DMR ID here</span></a></td>
    <td align="left" colspan="2"><input type="text" name="dmrId" size="13" maxlength="9" value="<?php if (isset($configmmdvm['General']['Id'])) { echo $configmmdvm['General']['Id']; } else { echo $configmmdvm['DMR']['Id']; } ?>" /></td>
    </tr><?php } ?>
    <?php if (file_exists('/etc/dstar-radio.mmdvmhost') && ($configmmdvm['NXDN']['Enable'] == 1)) { ?>
    <tr>
      <td align="left"><a class="tooltip2" href="#">NXDN ID:<span><b>NXDN ID</b>Enter your NXDN ID here</span></a></td>
      <td align="left" colspan="2"><input type="text" name="nxdnId" size="13" maxlength="5" value="<?php if (isset($configmmdvm['NXDN']['Id'])) { echo $configmmdvm['NXDN']['Id']; } ?>" /></td>
    </tr><?php } ?>
    <?php if ($configmmdvm['Info']['TXFrequency'] === $configmmdvm['Info']['RXFrequency']) {
	echo "    <tr>\n";
	echo "    <td align=\"left\"><a class=\"tooltip2\" href=\"#\">".$lang['radio_freq'].":<span><b>Radio Frequency</b>This is the Frequency your<br />Pi-Star is on</span></a></td>\n";
	echo "    <td align=\"left\" colspan=\"2\"><input type=\"text\" id=\"confFREQ\" onkeyup=\"checkFrequency(); return false;\" name=\"confFREQ\" size=\"13\" maxlength=\"12\" value=\"".number_format($configmmdvm['Info']['RXFrequency'], 0, '.', '.')."\" />MHz</td>\n";
	echo "    </tr>\n";
	}
	else {
	echo "    <tr>\n";
	echo "    <td align=\"left\"><a class=\"tooltip2\" href=\"#\">".$lang['radio_freq']." RX:<span><b>Radio Frequency</b>This is the Frequency your<br />repeater will listen on</span></a></td>\n";
	echo "    <td align=\"left\" colspan=\"2\"><input type=\"text\" id=\"confFREQrx\" onkeyup=\"checkFrequency(); return false;\" name=\"confFREQrx\" size=\"13\" maxlength=\"12\" value=\"".number_format($configmmdvm['Info']['RXFrequency'], 0, '.', '.')."\" />MHz</td>\n";
	echo "    </tr>\n";
	echo "    <tr>\n";
	echo "    <td align=\"left\"><a class=\"tooltip2\" href=\"#\">".$lang['radio_freq']." TX:<span><b>Radio Frequency</b>This is the Frequency your<br />repeater will transmit on</span></a></td>\n";
	echo "    <td align=\"left\" colspan=\"2\"><input type=\"text\" id=\"confFREQtx\" onkeyup=\"checkFrequency(); return false;\" name=\"confFREQtx\" size=\"13\" maxlength=\"12\" value=\"".number_format($configmmdvm['Info']['TXFrequency'], 0, '.', '.')."\" />MHz</td>\n";
	echo "    </tr>\n";
	}
?>
    <tr>
    <td align="left"><a class="tooltip2" href="#"><?php echo $lang['lattitude'];  if (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] != 'off') { echo '<button type="button" onclick="getLocation()">Get</button>'; } ?>:<span><b>Gateway Latitude</b>This is the latitude where the gateway is located (positive number for North, negative number for South)</span></a></td>
    <td align="left" colspan="2"><input type="text" id="confLatitude" name="confLatitude" size="13" maxlength="9" value="<?php echo $configs['latitude'] ?>" />degrees (positive value for North, negative for South)</td>
    </tr>
    <tr>
    <td align="left"><a class="tooltip2" href="#"><?php echo $lang['longitude']; if (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] != 'off') { echo '<button type="button" onclick="getLocation()">Get</button>'; } ?>:<span><b>Gateway Longitude</b>This is the longitude where the gateway is located (positive number for East, negative number for West)</span></a></td>
    <td align="left" colspan="2"><input type="text" id="confLongitude" name="confLongitude" size="13" maxlength="9" value="<?php echo $configs['longitude'] ?>" />degrees (positive value for East, negative for West)</td>
    </tr>
    <tr>
    <td align="left"><a class="tooltip2" href="#">GPSd:<span><b>GPS daemon support</b>Read NMEA data from a serially connected GPS unit and then to make that data available for other programs.</span></a></td>
    <td align="left" colspan="2">
    <?php
    // Enabled ??
    if ($configmmdvm['GPSD']['Enable'] == "1") { echo "<div class=\"switch\"><input id=\"toggle-GPSD\" class=\"toggle toggle-round-flat\" type=\"checkbox\" name=\"GPSD\" value=\"ON\" checked=\"checked\" /><label for=\"toggle-GPSD\"></label></div>\n"; }
    else { echo "<div class=\"switch\"><input id=\"toggle-GPSD\" class=\"toggle toggle-round-flat\" type=\"checkbox\" name=\"GPSD\" value=\"ON\" /><label for=\"toggle-GPSD\"></label></div>\n"; } ?>
    </td>
    </tr>
    <tr>
    <td align="left"><a class="tooltip2" href="#"><?php echo $lang['town'];?>:<span><b>Gateway Town</b>The town where the gateway is located</span></a></td>
    <td align="left" colspan="2"><input type="text" name="confDesc1" size="30" maxlength="30" value="<?php echo $configs['description1'] ?>" /></td>
    </tr>
    <tr>
    <td align="left"><a class="tooltip2" href="#"><?php echo $lang['country'];?>:<span><b>Gateway Country</b>The country where the gateway is located</span></a></td>
    <td align="left" colspan="2"><input type="text" name="confDesc2" size="30" maxlength="30" value="<?php echo $configs['description2'] ?>" /></td>
    </tr>
    <tr>
    <td align="left"><a class="tooltip2" href="#"><?php echo $lang['url'];?>:<span><b>Gateway URL</b>The URL used to access this dashboard</span></a></td>
    <td align="left"><input type="text" name="confURL" size="30" maxlength="30" value="<?php echo $configs['url'] ?>" /></td>
    <td width="300">
    <input type="radio" name="urlAuto" value="auto"<?php if (strpos($configs['url'], 'www.qrz.com/db/'.$configmmdvm['General']['Callsign']) !== FALSE) {echo ' checked="checked"';} ?> />Auto
    <input type="radio" name="urlAuto" value="man"<?php if (strpos($configs['url'], 'www.qrz.com/db/'.$configmmdvm['General']['Callsign']) == FALSE) {echo ' checked="checked"';} ?> />Manual</td>
    </tr>
<?php if ( (file_exists('/etc/dstar-radio.dstarrepeater')) || (file_exists('/etc/dstar-radio.mmdvmhost')) ) { ?>
    <tr>
    <td align="left"><a class="tooltip2" href="#"><?php echo $lang['radio_type'];?>:<span><b>Radio/Modem</b>What kind of radio or modem hardware do you have?</span></a></td>
    <td align="left" colspan="2"><select name="confHardware">
		<option<?php if (!$configModem['Modem']['Hardware']) { echo ' selected="selected"';}?> value="">--</option>
	        <?php if (file_exists('/dev/icom_ta')) { ?>
	    		<option<?php if ($configModem['Modem']['Hardware'] === 'icomTerminalAuto') {		echo ' selected="selected"';}?> value="icomTerminalAuto">Icom Radio in Terminal Mode (DStarRepeater Only)</option>
	        <?php } ?>
	        <option<?php if ($configModem['Modem']['Hardware'] === 'idrp2c') {		echo ' selected="selected"';}?> value="idrp2c">Icom Repeater Controller ID-RP2C (DStarRepeater Only)</option>
		<option<?php if ($configModem['Modem']['Hardware'] === 'dvmpis') {		echo ' selected="selected"';}?> value="dvmpis">DV-Mega Raspberry Pi Hat (GPIO) - Single Band (70cm)</option>
		<option<?php if ($configModem['Modem']['Hardware'] === 'dvmpid') {		echo ' selected="selected"';}?> value="dvmpid">DV-Mega Raspberry Pi Hat (GPIO) - Dual Band</option>
		<option<?php if ($configModem['Modem']['Hardware'] === 'dvmuadu') {		echo ' selected="selected"';}?> value="dvmuadu">DV-Mega on Arduino (USB - /dev/ttyUSB0) - Dual Band</option>
	        <option<?php if ($configModem['Modem']['Hardware'] === 'dvmuada') {		echo ' selected="selected"';}?> value="dvmuada">DV-Mega on Arduino (USB - /dev/ttyACM0) - Dual Band</option>
		<option<?php if ($configModem['Modem']['Hardware'] === 'dvmuagmsku') {		echo ' selected="selected"';}?> value="dvmuagmsku">DV-Mega on Arduino (USB - /dev/ttyUSB0) - GMSK Modem</option>
		<option<?php if ($configModem['Modem']['Hardware'] === 'dvmuagmska') {		echo ' selected="selected"';}?> value="dvmuagmska">DV-Mega on Arduino (USB - /dev/ttyACM0) - GMSK Modem</option>
		<option<?php if ($configModem['Modem']['Hardware'] === 'dvmbss') {		echo ' selected="selected"';}?> value="dvmbss">DV-Mega on Bluestack (USB) - Single Band (70cm)</option>
		<option<?php if ($configModem['Modem']['Hardware'] === 'dvmbsd') {		echo ' selected="selected"';}?> value="dvmbsd">DV-Mega on Bluestack (USB) - Dual Band</option>
	    	<?php if (file_exists('/dev/ttyS2')) { ?>
	    		<option<?php if ($configModem['Modem']['Hardware'] === 'dvmpicast') {	echo ' selected="selected"';}?> value="dvmpicast">DV-Mega Cast Base Radio (Main Unit)</option>
	    		<option<?php if ($configModem['Modem']['Hardware'] === 'dvmpicasths') {	echo ' selected="selected"';}?> value="dvmpicasths">DV-Mega Cast Hotspot - Single Band (70cm)</option>
	    		<option<?php if ($configModem['Modem']['Hardware'] === 'dvmpicasthd') {	echo ' selected="selected"';}?> value="dvmpicasthd">DV-Mega Cast Hotspot - Dual Band (2m/70cm)</option>
	    	<?php } ?>
		<option<?php if ($configModem['Modem']['Hardware'] === 'gmsk_modem') {		echo ' selected="selected"';}?> value="gmsk_modem">GMSK Modem (USB DStarRepeater Only)</option>
	        <option<?php if ($configModem['Modem']['Hardware'] === 'dvrptr1') {		echo ' selected="selected"';}?> value="dvrptr1">DV-RPTR V1 (USB)</option>
		<option<?php if ($configModem['Modem']['Hardware'] === 'dvrptr2') {		echo ' selected="selected"';}?> value="dvrptr2">DV-RPTR V2 (USB)</option>
		<option<?php if ($configModem['Modem']['Hardware'] === 'dvrptr3') {		echo ' selected="selected"';}?> value="dvrptr3">DV-RPTR V3 (USB)</option>
		<option<?php if ($configModem['Modem']['Hardware'] === 'dvap') {		echo ' selected="selected"';}?> value="dvap">DVAP (USB)</option>
		<option<?php if ($configModem['Modem']['Hardware'] === 'zum') {			echo ' selected="selected"';}?> value="zum">MMDVM / MMDVM_HS / Teensy / ZUM (USB)</option>
		<option<?php if ($configModem['Modem']['Hardware'] === 'stm32dvm') {		echo ' selected="selected"';}?> value="stm32dvm">STM32-DVM / MMDVM_HS - Raspberry Pi Hat (GPIO)</option>
		<option<?php if ($configModem['Modem']['Hardware'] === 'stm32usb') {		echo ' selected="selected"';}?> value="stm32usb">STM32-DVM (USB)</option>
	        <option<?php if ($configModem['Modem']['Hardware'] === 'zumspotlibre') {	echo ' selected="selected"';}?> value="zumspotlibre">ZUMspot - Libre (USB)</option>
		<option<?php if ($configModem['Modem']['Hardware'] === 'zumspotusb') {		echo ' selected="selected"';}?> value="zumspotusb">ZUMspot - USB Stick</option>
		<option<?php if ($configModem['Modem']['Hardware'] === 'zumspotgpio') {		echo ' selected="selected"';}?> value="zumspotgpio">ZUMspot - Single Band Raspberry Pi Hat (GPIO)</option>
	        <option<?php if ($configModem['Modem']['Hardware'] === 'zumspotdualgpio') {	echo ' selected="selected"';}?> value="zumspotdualgpio">ZUMspot - Dual Band Raspberry Pi Hat (GPIO)</option>
		<option<?php if ($configModem['Modem']['Hardware'] === 'zumspotduplexgpio') {	echo ' selected="selected"';}?> value="zumspotduplexgpio">ZUMspot - Duplex Raspberry Pi Hat (GPIO)</option>
	        <option<?php if ($configModem['Modem']['Hardware'] === 'zumradiopigpio') {	echo ' selected="selected"';}?> value="zumradiopigpio">ZUM Radio-MMDVM for Pi (GPIO)</option>    
	        <option<?php if ($configModem['Modem']['Hardware'] === 'zumradiopiusb') {	echo ' selected="selected"';}?> value="zumradiopiusb">ZUM Radio-MMDVM-Nucleo (USB)</option>
	        <option<?php if ($configModem['Modem']['Hardware'] === 'mnnano-spot') {		echo ' selected="selected"';}?> value="mnnano-spot">MicroNode Nano-Spot (Built In)</option>
	        <option<?php if ($configModem['Modem']['Hardware'] === 'mnnano-teensy') {	echo ' selected="selected"';}?> value="mnnano-teensy">MicroNode Teensy (USB)</option>
	        <option<?php if ($configModem['Modem']['Hardware'] === 'f4mgpio') {		echo ' selected="selected"';}?> value="f4mgpio">MMDVM F4M-GPIO (GPIO)</option>
	        <option<?php if ($configModem['Modem']['Hardware'] === 'f4mf7m') {		echo ' selected="selected"';}?> value="f4mf7m">MMDVM F4M/F7M (F0DEI) for USB</option>
	        <option<?php if ($configModem['Modem']['Hardware'] === 'mmdvmhshat') {		echo ' selected="selected"';}?> value="mmdvmhshat">MMDVM_HS_Hat (DB9MAT &amp; DF2ET) for Pi (GPIO)</option>
	        <option<?php if ($configModem['Modem']['Hardware'] === 'mmdvmhsdualhatgpio') {	echo ' selected="selected"';}?> value="mmdvmhsdualhatgpio">MMDVM_HS_Dual_Hat (DB9MAT, DF2ET &amp; DO7EN) for Pi (GPIO)</option>
	        <option<?php if ($configModem['Modem']['Hardware'] === 'mmdvmhsdualhatusb') {	echo ' selected="selected"';}?> value="mmdvmhsdualhatusb">MMDVM_HS_Dual_Hat (DB9MAT, DF2ET &amp; DO7EN) for Pi (USB)</option>
	        <option<?php if ($configModem['Modem']['Hardware'] === 'mmdvmhshatambe') {	echo ' selected="selected"';}?> value="mmdvmhshatambe">MMDVM_HS_AMBE (D2RG HS_AMBE) for Pi (GPIO)</option>
	        <option<?php if ($configModem['Modem']['Hardware'] === 'mmdvmrpthat') {		echo ' selected="selected"';}?> value="mmdvmrpthat">MMDVM_RPT_Hat (DB9MAT, DF2ET &amp; F0DEI) for Pi (GPIO)</option>
	        <option<?php if ($configModem['Modem']['Hardware'] === 'mmdvmmdohat') {		echo ' selected="selected"';}?> value="mmdvmmdohat">MMDVM_HS_MDO Hat (BG3MDO) for Pi (GPIO)</option>
	        <option<?php if ($configModem['Modem']['Hardware'] === 'mmdvmvyehat') {		echo ' selected="selected"';}?> value="mmdvmvyehat">MMDVM_HS_NPi Hat (VR2VYE) for Nano Pi (GPIO)</option>
	        <option<?php if ($configModem['Modem']['Hardware'] === 'mmdvmvyehatdual') {	echo ' selected="selected"';}?> value="mmdvmvyehatdual">MMDVM_HS_Hat_Dual Hat (VR2VYE) for Pi (GPIO)</option>
	    	<option<?php if ($configModem['Modem']['Hardware'] === 'nanodv') {		echo ' selected="selected"';}?> value="nanodv">MMDVM_NANO_DV (BG4TGO) for NanoPi AIR (GPIO)</option>
	    	<option<?php if ($configModem['Modem']['Hardware'] === 'nanodvusb') {		echo ' selected="selected"';}?> value="nanodvusb">MMDVM_NANO_DV (BG4TGO) for NanoPi AIR (USB)</option>
		<option<?php if ($configModem['Modem']['Hardware'] === 'opengd77') {		echo ' selected="selected"';}?> value="opengd77">OpenGD77 DMR hotspot (USB)</option>
    </select></td>
    </tr>
<?php } ?>
    <tr>
    <td align="left"><a class="tooltip2" href="#"><?php echo $lang['node_type'];?>:<span><b>Node Lock</b>Set the public/private node type. Public should only be used with the correct licence.</span></a></td>
    <td align="left" colspan="2">
    <input type="radio" name="nodeMode" value="prv"<?php if ($configmmdvm['DMR']['SelfOnly'] == 1) {echo ' checked="checked"';} ?> />Private
    <input type="radio" name="nodeMode" value="pub"<?php if ($configmmdvm['DMR']['SelfOnly'] == 0) {echo ' checked="checked"';} ?> />Public</td>
    </tr>
    <tr>
    <td align="left"><a class="tooltip2" href="#"><?php echo $lang['aprs_host'];?>:<span><b>APRS Host</b>Set your prefered APRS host here</span></a></td>
    <td colspan="2" style="text-align: left;"><select name="selectedAPRSHost">
<?php
        $testAPSRHost = $configs['aprsHostname'];
    	$aprsHostFile = fopen("/usr/local/etc/APRSHosts.txt", "r");
        while (!feof($aprsHostFile)) {
                $aprsHostFileLine = fgets($aprsHostFile);
                $aprsHost = preg_split('/:/', $aprsHostFileLine);
                if ((strpos($aprsHost[0], ';') === FALSE ) && ($aprsHost[0] != '')) {
                        if ($testAPSRHost == $aprsHost[0]) { echo "      <option value=\"$aprsHost[0]\" selected=\"selected\">$aprsHost[0]</option>\n"; }
                        else { echo "      <option value=\"$aprsHost[0]\">$aprsHost[0]</option>\n"; }
                }
        }
        fclose($aprsHostFile);
        ?>
    </select></td>
    </tr>
    <tr>
    <td align="left"><a class="tooltip2" href="#"><?php echo $lang['timezone'];?>:<span><b>System TimeZone</b>Set the system timezone</span></a></td>
    <td style="text-align: left;" colspan="2"><select name="systemTimezone">
<?php
  exec('timedatectl list-timezones', $tzList);
  if (!in_array("UTC", $tzList)) { array_push($tzList, "UTC"); }
  exec('cat /etc/timezone', $tzCurrent);
    foreach ($tzList as $timeZone) {
      if ($timeZone == $tzCurrent[0]) { echo "      <option selected=\"selected\" value=\"".$timeZone."\">".$timeZone."</option>\n"; }
      else { echo "      <option value=\"".$timeZone."\">".$timeZone."</option>\n"; }
    }
?>
    </select></td>
    </tr>
<?php
    $lang_dir = './lang';
    if (is_dir($lang_dir)) {
	echo '    <tr>'."\n";
	echo '    <td align="left"><a class="tooltip2" href="#">'.$lang['dash_lang'].':<span><b>Dashboard Language</b>Set the language for the dashboard.</span></a></td>'."\n";
	echo '    <td align="left" colspan="2"><select name="dashboardLanguage">'."\n";

	if ($dh = opendir($lang_dir)) {
	while ($files[] = readdir($dh))
		sort($files); // Add sorting for the Language(s)
		foreach ($files as $file){
			if (($file != 'index.php') && ($file != '.') && ($file != '..') && ($file != '')) {
				$file = substr($file, 0, -4);
				if ($file == $pistarLanguage) { echo "      <option selected=\"selected\" value=\"".$file."\">".$file."</option>\n"; }
				else { echo "      <option value=\"".$file."\">".$file."</option>\n"; }
			}
		}
		closedir($dh);
	}
	echo '    </select></td></tr>'."\n";
    }
?>
    </table>
	<div><input type="button" value="<?php echo $lang['apply'];?>" onclick="submitform()" /><br /><br /></div>
    <?php if (file_exists('/etc/dstar-radio.mmdvmhost') && $configmmdvm['DMR']['Enable'] == 1) {
    $dmrMasterFile = fopen("/usr/local/etc/DMR_Hosts.txt", "r"); ?>
	<h2><?php echo $lang['dmr_config'];?></h2>
    <input type="hidden" name="dmrEmbeddedLCOnly" value="OFF" />
    <input type="hidden" name="dmrDumpTAData" value="OFF" />
    <input type="hidden" name="dmrGatewayXlxEn" value="OFF" />
    <input type="hidden" name="dmrGatewayNet1En" value="OFF" />
    <input type="hidden" name="dmrGatewayNet2En" value="OFF" />
    <input type="hidden" name="dmrDMRnetJitterBufer" value="OFF" />
    <table>
    <tr>
    <th width="200"><a class="tooltip" href="#"><?php echo $lang['setting'];?><span><b>Setting</b></span></a></th>
    <th colspan="2"><a class="tooltip" href="#"><?php echo $lang['value'];?><span><b>Value</b>The current value from the<br />configuration files</span></a></th>
    </tr>
    <tr>
    <td align="left"><a class="tooltip2" href="#"><?php echo $lang['dmr_master'];?>:<span><b>DMR Master (MMDVMHost)</b>Set your prefered DMR master here</span></a></td>
    <td style="text-align: left;"><select name="dmrMasterHost">
<?php
        $testMMDVMdmrMaster = $configmmdvm['DMR Network']['RemoteAddress'];
	$testMMDVMdmrMasterPort = $configmmdvm['DMR Network']['RemotePort'];
        while (!feof($dmrMasterFile)) {
                $dmrMasterLine = fgets($dmrMasterFile);
                $dmrMasterHost = preg_split('/\s+/', $dmrMasterLine);
                if ((strpos($dmrMasterHost[0], '#') === FALSE ) && (substr($dmrMasterHost[0], 0, 3) != "XLX") && ($dmrMasterHost[0] != '')) {
                        if (($testMMDVMdmrMaster == $dmrMasterHost[2]) && ($testMMDVMdmrMasterPort == $dmrMasterHost[4])) { echo "      <option value=\"$dmrMasterHost[2],$dmrMasterHost[3],$dmrMasterHost[4],$dmrMasterHost[0]\" selected=\"selected\">$dmrMasterHost[0]</option>\n"; $dmrMasterNow = $dmrMasterHost[0]; }
                        else { echo "      <option value=\"$dmrMasterHost[2],$dmrMasterHost[3],$dmrMasterHost[4],$dmrMasterHost[0]\">$dmrMasterHost[0]</option>\n"; }
                }
        }
        fclose($dmrMasterFile);
        ?>
    </select></td>
    </tr>
<?php if ($dmrMasterNow == "DMRGateway") { ?>
    <tr>
    <td align="left"><a class="tooltip2" href="#"><?php echo $lang['bm_master'];?>:<span><b>BrandMeister Master</b>Set your prefered DMR master here</span></a></td>
    <td style="text-align: left;"><select name="dmrMasterHost1">
<?php
	$dmrMasterFile1 = fopen("/usr/local/etc/DMR_Hosts.txt", "r");
	$testMMDVMdmrMaster1 = $configdmrgateway['DMR Network 1']['Address'];
	$testMMDVMdmrMaster1Port = $configdmrgateway['DMR Network 1']['Port'];
	while (!feof($dmrMasterFile1)) {
		$dmrMasterLine1 = fgets($dmrMasterFile1);
                $dmrMasterHost1 = preg_split('/\s+/', $dmrMasterLine1);
                if ((strpos($dmrMasterHost1[0], '#') === FALSE ) && (substr($dmrMasterHost1[0], 0, 2) == "BM") && ($dmrMasterHost1[0] != '')) {
                        if (($testMMDVMdmrMaster1 == $dmrMasterHost1[2]) && ($testMMDVMdmrMaster1Port == $dmrMasterHost1[4])) { echo "      <option value=\"$dmrMasterHost1[2],$dmrMasterHost1[3],$dmrMasterHost1[4],$dmrMasterHost1[0]\" selected=\"selected\">$dmrMasterHost1[0]</option>\n"; }
                        else { echo "      <option value=\"$dmrMasterHost1[2],$dmrMasterHost1[3],$dmrMasterHost1[4],$dmrMasterHost1[0]\">$dmrMasterHost1[0]</option>\n"; }
                }
	}
	fclose($dmrMasterFile1);
?>
    </select></td></tr>
    <tr>
      <td align="left"><a class="tooltip2" href="#">BM Hotspot Security:<span><b>BrandMeister Password</b>Override the Password for BrandMeister with your own custom password, make sure you already configured this using BM Self Care. Empty the field to use the default.</span></a></td>
      <td align="left">
        <input type="password" name="bmHSSecurity" size="30" maxlength="30" value="<?php if (isset($configModem['BrandMeister']['Password'])) {echo $configModem['BrandMeister']['Password'];} ?>"></input>
      </td>
    </tr>
    <tr>
    <td align="left"><a class="tooltip2" href="#"><?php echo $lang['bm_network'];?> ESSID:<span><b>BrandMeister Extended ID</b>This is the extended ID, to make your DMR ID 9 digits long</span></a></td>
    <td align="left">
<?php
	if (isset($configdmrgateway['DMR Network 1']['Id'])) {
		if (strlen($configdmrgateway['DMR Network 1']['Id']) > strlen($configmmdvm['General']['Id'])) {
			$brandMeisterESSID = substr($configdmrgateway['DMR Network 1']['Id'], -2);
		} else {
			$brandMeisterESSID = "None";
		}
	} else {
		if (isset($configmmdvm['General']['Id'])) {
			if (strlen($configmmdvm['General']['Id']) == 9) {
				$brandMeisterESSID = substr($configmmdvm['General']['Id'], -2);
			} else {
				$brandMeisterESSID = "None";
			}
		} else {
			$brandMeisterESSID = "None";
		}
	}

	if (isset($configmmdvm['General']['Id'])) { if ($configmmdvm['General']['Id'] !== "1234567") { echo substr($configmmdvm['General']['Id'], 0, 7); } }
	echo "<select name=\"bmExtendedId\">\n";
	if ($brandMeisterESSID == "None") { echo "      <option value=\"None\" selected=\"selected\">None</option>\n"; } else { echo "      <option value=\"None\">None</option>\n"; }
	for ($brandMeisterESSIDInput = 1; $brandMeisterESSIDInput <= 99; $brandMeisterESSIDInput++) {
		$brandMeisterESSIDInput = str_pad($brandMeisterESSIDInput, 2, "0", STR_PAD_LEFT);
		if ($brandMeisterESSID === $brandMeisterESSIDInput) {
			echo "      <option value=\"$brandMeisterESSIDInput\" selected=\"selected\">$brandMeisterESSIDInput</option>\n";
		} else {
			echo "      <option value=\"$brandMeisterESSIDInput\">$brandMeisterESSIDInput</option>\n";
		}
	}
	echo "</select>\n";
?>
    </td></tr>
    <tr>
    <td align="left"><a class="tooltip2" href="#"><?php echo $lang['bm_network'];?> Enable:<span><b>BrandMeister Network Enable</b>Enable or disable BrandMeister Network</span></a></td>
    <td align="left">
    <?php if ($configdmrgateway['DMR Network 1']['Enabled'] == 1) { echo "<div class=\"switch\"><input id=\"toggle-dmrGatewayNet1En\" class=\"toggle toggle-round-flat\" type=\"checkbox\" name=\"dmrGatewayNet1En\" value=\"ON\" checked=\"checked\" /><label for=\"toggle-dmrGatewayNet1En\"></label></div>\n"; }
    else { echo "<div class=\"switch\"><input id=\"toggle-dmrGatewayNet1En\" class=\"toggle toggle-round-flat\" type=\"checkbox\" name=\"dmrGatewayNet1En\" value=\"ON\" /><label for=\"toggle-dmrGatewayNet1En\"></label></div>\n"; } ?>
    </td>
    </tr>
    <tr>
    <td align="left"><a class="tooltip2" href="#"><?php echo $lang['bm_network'];?>:<span><b>BrandMeister Dashboards</b>Direct links to your BrandMeister Dashboards</span></a></td>
    <td>
    <a href="https://brandmeister.network/?page=hotspot&amp;id=<?php echo $configmmdvm['General']['Id']; if ($brandMeisterESSID != "None") { echo $brandMeisterESSID; }; ?>" target="_new" style="color: #000;">Repeater Information</a> |
    <a href="https://brandmeister.network/?page=hotspot-edit&amp;id=<?php echo $configmmdvm['General']['Id']; if ($brandMeisterESSID != "None") { echo $brandMeisterESSID; }; ?>" target="_new" style="color: #000;">Edit Repeater (BrandMeister Selfcare)</a>
    </td>
    </tr>
    <tr>
    <td align="left"><a class="tooltip2" href="#"><?php echo $lang['dmr_plus_master'];?>:<span><b>DMR+ Master</b>Set your prefered DMR master here</span></a></td>
    <td style="text-align: left;"><select name="dmrMasterHost2">
<?php
	$dmrMasterFile2 = fopen("/usr/local/etc/DMR_Hosts.txt", "r");
	$testMMDVMdmrMaster2= $configdmrgateway['DMR Network 2']['Address'];
	$testMMDVMdmrMaster2Port = $configdmrgateway['DMR Network 2']['Port'];
	while (!feof($dmrMasterFile2)) {
		$dmrMasterLine2 = fgets($dmrMasterFile2);
		$dmrMasterHost2 = preg_split('/\s+/', $dmrMasterLine2);
		if ((strpos($dmrMasterHost2[0], '#') === FALSE ) && ((substr($dmrMasterHost2[0], 0, 4) == "DMR+") || (substr($dmrMasterHost2[0], 0, 3) == "HB_")) && ($dmrMasterHost2[0] != '')) {
                    if (($testMMDVMdmrMaster2 == $dmrMasterHost2[2]) && ($testMMDVMdmrMaster2Port == $dmrMasterHost2[4])) {
			echo "      <option value=\"$dmrMasterHost2[2],$dmrMasterHost2[3],$dmrMasterHost2[4],$dmrMasterHost2[0]\" selected=\"selected\">$dmrMasterHost2[0]</option>\n";
		    }
		    else {
			echo "      <option value=\"$dmrMasterHost2[2],$dmrMasterHost2[3],$dmrMasterHost2[4],$dmrMasterHost2[0]\">$dmrMasterHost2[0]</option>\n";
		    }
                }
	}
	fclose($dmrMasterFile2);
?>
    </select></td></tr>
    <tr>
    <td align="left"><a class="tooltip2" href="#"><?php echo $lang['dmr_plus_network'];?>:<span><b>DMR+ Network</b>Set your options= for DMR+ here</span></a></td>
    <td align="left">
    Options=<input type="text" name="dmrNetworkOptions" size="40" maxlength="100" value="<?php if (isset($configdmrgateway['DMR Network 2']['Options'])) { echo $configdmrgateway['DMR Network 2']['Options']; } ?>" />
    </td>
    </tr>
    <tr>
    <td align="left"><a class="tooltip2" href="#"><?php echo $lang['dmr_plus_network'];?> ESSID:<span><b>DMR Plus Extended ID</b>This is the extended ID, to make your DMR ID 8 digits long</span></a></td>
    <td align="left">
<?php
	if (isset($configdmrgateway['DMR Network 2']['Id'])) {
		if (strlen($configdmrgateway['DMR Network 2']['Id']) > strlen($configmmdvm['General']['Id'])) {
			$dmrPlusESSID = substr($configdmrgateway['DMR Network 2']['Id'], -2);
		} else {
			$dmrPlusESSID = "None";
		}
	} else {
		if (isset($configmmdvm['General']['Id'])) {
			if (strlen($configmmdvm['General']['Id']) == 9) {
				$dmrPlusESSID = substr($configmmdvm['General']['Id'], -2);
			} else {
				$dmrPlusESSID = "None";
			}
		} else {
			$dmrPlusESSID = "None";
		}
	}

	if (isset($configmmdvm['General']['Id'])) { if ($configmmdvm['General']['Id'] !== "1234567") { echo substr($configmmdvm['General']['Id'], 0, 7); } }
	echo "<select name=\"dmrPlusExtendedId\">\n";
	if ($dmrPlusESSID == "None") { echo "      <option value=\"None\" selected=\"selected\">None</option>\n"; } else { echo "      <option value=\"None\">None</option>\n"; }
	for ($dmrPlusESSIDInput = 1; $dmrPlusESSIDInput <= 99; $dmrPlusESSIDInput++) {
		$dmrPlusESSIDInput = str_pad($dmrPlusESSIDInput, 2, "0", STR_PAD_LEFT);
		if ($dmrPlusESSID === $dmrPlusESSIDInput) {
			echo "      <option value=\"$dmrPlusESSIDInput\" selected=\"selected\">$dmrPlusESSIDInput</option>\n";
		} else {
			echo "      <option value=\"$dmrPlusESSIDInput\">$dmrPlusESSIDInput</option>\n";
		}
	}
	echo "</select>\n";
?>
    </td></tr>
    <tr>
    <td align="left"><a class="tooltip2" href="#"><?php echo $lang['dmr_plus_network'];?> Enable:<span><b>DMR+ Network Enable</b></span></a></td>
    <td align="left">
    <?php if ($configdmrgateway['DMR Network 2']['Enabled'] == 1) { echo "<div class=\"switch\"><input id=\"toggle-dmrGatewayNet2En\" class=\"toggle toggle-round-flat\" type=\"checkbox\" name=\"dmrGatewayNet2En\" value=\"ON\" checked=\"checked\" /><label for=\"toggle-dmrGatewayNet2En\"></label></div>\n"; }
    else { echo "<div class=\"switch\"><input id=\"toggle-dmrGatewayNet2En\" class=\"toggle toggle-round-flat\" type=\"checkbox\" name=\"dmrGatewayNet2En\" value=\"ON\" /><label for=\"toggle-dmrGatewayNet2En\"></label></div>\n"; } ?>
    </td>
    </tr>
    <tr>
    <td align="left"><a class="tooltip2" href="#"><?php echo $lang['xlx_master'];?>:<span><b>XLX Master</b>Set your prefered XLX master here</span></a></td>
    <td style="text-align: left;"><select name="dmrMasterHost3">
<?php
	$dmrMasterFile3 = fopen("/usr/local/etc/DMR_Hosts.txt", "r");
	$testMMDVMdmrMaster3 = "";
	if (isset($configdmrgateway['XLX Network 1']['Address'])) { $testMMDVMdmrMaster3= $configdmrgateway['XLX Network 1']['Address']; }
	if (isset($configdmrgateway['XLX Network']['Startup'])) { $testMMDVMdmrMaster3= $configdmrgateway['XLX Network']['Startup']; }
	while (!feof($dmrMasterFile3)) {
		$dmrMasterLine3 = fgets($dmrMasterFile3);
                $dmrMasterHost3 = preg_split('/\s+/', $dmrMasterLine3);
                if ((strpos($dmrMasterHost3[0], '#') === FALSE ) && (substr($dmrMasterHost3[0], 0, 3) == "XLX") && ($dmrMasterHost3[0] != '')) {
                        if ($testMMDVMdmrMaster3 == $dmrMasterHost3[2]) { echo "      <option value=\"$dmrMasterHost3[2],$dmrMasterHost3[3],$dmrMasterHost3[4],$dmrMasterHost3[0]\" selected=\"selected\">$dmrMasterHost3[0]</option>\n"; }
			if ('XLX_'.$testMMDVMdmrMaster3 == $dmrMasterHost3[0]) { echo "      <option value=\"$dmrMasterHost3[2],$dmrMasterHost3[3],$dmrMasterHost3[4],$dmrMasterHost3[0]\" selected=\"selected\">$dmrMasterHost3[0]</option>\n"; }
                        else { echo "      <option value=\"$dmrMasterHost3[2],$dmrMasterHost3[3],$dmrMasterHost3[4],$dmrMasterHost3[0]\">$dmrMasterHost3[0]</option>\n"; }
                }
	}
	fclose($dmrMasterFile3);
?>
    </select></td></tr>
    <?php if (isset($configdmrgateway['XLX Network 1']['Startup'])) { ?>
    <tr>
    <td align="left"><a class="tooltip2" href="#"><?php echo $lang['xlx_startup_tg'];?>:<span><b>XLX Startup TG</b></span></a></td>
    <td align="left"><select name="dmrMasterHost3Startup">
<?php
	if (isset($configdmrgateway['XLX Network 1']['Startup'])) {
		echo '      <option value="None">None</option>'."\n";
	}
	else {
		echo '      <option value="None" selected="selected">None</option>'."\n";
	}
	for ($xlxSu = 1; $xlxSu <= 26; $xlxSu++) {
		$xlxSuVal = '40'.sprintf('%02d', $xlxSu);
		if ((isset($configdmrgateway['XLX Network 1']['Startup'])) && ($configdmrgateway['XLX Network 1']['Startup'] == $xlxSuVal)) {
			echo '      <option value="'.$xlxSuVal.'" selected="selected">'.$xlxSuVal.'</option>'."\n";
		}
		else {
			echo '      <option value="'.$xlxSuVal.'">'.$xlxSuVal.'</option>'."\n";
		}
	}
?>
    </select></td></tr>
    <?php } ?>
    <?php if (isset($configdmrgateway['XLX Network']['TG'])) { ?>
    <tr>
    <td align="left"><a class="tooltip2" href="#"><?php echo $lang['xlx_startup_module'];?>:<span><b>XLX Startup Module override</b>Default will use the host file option, or override it here.</span></a></td>
    <td align="left"><select name="dmrMasterHost3StartupModule">
<?php
	if (isset($configdmrgateway['XLX Network']['Module'])) {
		echo '        <option value="'.$configdmrgateway['XLX Network']['Module'].'" selected="selected">'.$configdmrgateway['XLX Network']['Module'].'</option>'."\n";
		echo '        <option value="Default">Default</option>'."\n";
	} else {
		echo '        <option value="Default" selected="selected">Default</option>'."\n";
	}
?>
	<option value="A">A</option>
        <option value="B">B</option>
        <option value="C">C</option>
        <option value="D">D</option>
        <option value="E">E</option>
        <option value="F">F</option>
        <option value="G">G</option>
        <option value="H">H</option>
        <option value="I">I</option>
        <option value="J">J</option>
        <option value="K">K</option>
        <option value="L">L</option>
        <option value="M">M</option>
        <option value="N">N</option>
        <option value="O">O</option>
        <option value="P">P</option>
        <option value="Q">Q</option>
        <option value="R">R</option>
        <option value="S">S</option>
        <option value="T">T</option>
        <option value="U">U</option>
        <option value="V">V</option>
        <option value="W">W</option>
        <option value="X">X</option>
        <option value="Y">Y</option>
        <option value="Z">Z</option>
    </select></td>
    </tr>
    <?php } ?>
    <tr>
    <td align="left"><a class="tooltip2" href="#"><?php echo $lang['xlx_enable'];?>:<span><b>XLX Master Enable</b>Turn your XLX connection on or off.</span></a></td>
    <td align="left">
    <?php
    if ((isset($configdmrgateway['XLX Network 1']['Enabled'])) && ($configdmrgateway['XLX Network 1']['Enabled'] == 1)) { echo "<div class=\"switch\"><input id=\"toggle-dmrGatewayXlxEn\" class=\"toggle toggle-round-flat\" type=\"checkbox\" name=\"dmrGatewayXlxEn\" value=\"ON\" checked=\"checked\" /><label for=\"toggle-dmrGatewayXlxEn\"></label></div>\n"; }
    else if ((isset($configdmrgateway['XLX Network']['Enabled'])) && ($configdmrgateway['XLX Network']['Enabled'] == 1)) { echo "<div class=\"switch\"><input id=\"toggle-dmrGatewayXlxEn\" class=\"toggle toggle-round-flat\" type=\"checkbox\" name=\"dmrGatewayXlxEn\" value=\"ON\" checked=\"checked\" /><label for=\"toggle-dmrGatewayXlxEn\"></label></div>\n"; }
    else { echo "<div class=\"switch\"><input id=\"toggle-dmrGatewayXlxEn\" class=\"toggle toggle-round-flat\" type=\"checkbox\" name=\"dmrGatewayXlxEn\" value=\"ON\" /><label for=\"toggle-dmrGatewayXlxEn\"></label></div>\n"; } ?>
    </td></tr>
<?php }
    if (substr($dmrMasterNow, 0, 2) == "BM") { echo '    <tr>
      <td align="left"><a class="tooltip2" href="#">Hotspot Security:<span><b>Custom Password</b>Override the Password for your DMR Host with your own custom password, make sure you already configured this with your chosen DMR Host too. Empty the field to use the default.</span></a></td>
      <td align="left">
        <input type="password" name="bmHSSecurity" size="30" maxlength="30" value="'; if (isset($configModem['BrandMeister']['Password'])) {echo $configModem['BrandMeister']['Password'];}; echo '"></input>
      </td>
    </tr>
    <tr>
    <td align="left"><a class="tooltip2" href="#">'.$lang['bm_network'].':<span><b>BrandMeister Dashboards</b>Direct links to your BrandMeister Dashboards</span></a></td>
    <td>
      <a href="https://brandmeister.network/?page=hotspot&amp;id='.$configmmdvm['General']['Id'].'" target="_new" style="color: #000;">Repeater Information</a> |
      <a href="https://brandmeister.network/?page=hotspot-edit&amp;id='.$configmmdvm['General']['Id'].'" target="_new" style="color: #000;">Edit Repeater (BrandMeister Selfcare)</a>
    </td>
    </tr>'."\n";}
    if (substr($dmrMasterNow, 0, 4) == "DMR+") {
      echo '    <tr>
    <td align="left"><a class="tooltip2" href="#">'.$lang['dmr_plus_network'].':<span><b>DMR+ Network</b>Set your options= for DMR+ here</span></a></td>
    <td align="left">
    Options=<input type="text" name="dmrNetworkOptions" size="40" maxlength="100" value="';
	if (isset($configmmdvm['DMR Network']['Options'])) { echo $configmmdvm['DMR Network']['Options']; }
        echo '" />
    </td>
    </tr>'."\n";}
?>

<?php if ($dmrMasterNow !== "DMRGateway") { ?>
    <tr>
    <td align="left"><a class="tooltip2" href="#">DMR ESSID:<span><b>DMR Extended ID</b>This is the extended ID, to make your DMR ID 8 or 9 digits long</span></a></td>
    <td align="left">
<?php
	if (isset($configmmdvm['DMR']['Id'])) {
		if (strlen($configmmdvm['DMR']['Id']) > strlen($configmmdvm['General']['Id'])) {
			$essidLen = strlen($configmmdvm['DMR']['Id']) - strlen($configmmdvm['General']['Id']);
			$dmrESSID = substr($configmmdvm['DMR']['Id'], - $essidLen);
		} else {
			$dmrESSID = "None";
		}
	} else {
		$dmrESSID = "None";
	}

	if (isset($configmmdvm['General']['Id'])) { if ($configmmdvm['General']['Id'] !== "1234567") { echo substr($configmmdvm['General']['Id'], 0, 7); } }
	echo "<select name=\"dmrExtendedId\">\n";
	if ($dmrESSID == "None") { echo "      <option value=\"None\" selected=\"selected\">None</option>\n"; } else { echo "      <option value=\"None\">None</option>\n"; }
	for ($dmrESSIDInput = 1; $dmrESSIDInput <= 99; $dmrESSIDInput++) {
		$dmrESSIDInput = str_pad($dmrESSIDInput, 2, "0", STR_PAD_LEFT);
		if ($dmrESSID === $dmrESSIDInput) {
			echo "      <option value=\"$dmrESSIDInput\" selected=\"selected\">$dmrESSIDInput</option>\n";
		} else {
			echo "      <option value=\"$dmrESSIDInput\">$dmrESSIDInput</option>\n";
		}
	}
	echo "</select>\n";
?>
    </td></tr>
    <tr>
<?php } ?>
    <td align="left"><a class="tooltip2" href="#"><?php echo $lang['dmr_cc'];?>:<span><b>DMR Color Code</b>Set your DMR Color Code here</span></a></td>
    <td style="text-align: left;"><select name="dmrColorCode">
	<?php for ($dmrColorCodeInput = 0; $dmrColorCodeInput <= 15; $dmrColorCodeInput++) {
		if ($configmmdvm['DMR']['ColorCode'] == $dmrColorCodeInput) { echo "<option selected=\"selected\" value=\"$dmrColorCodeInput\">$dmrColorCodeInput</option>\n"; }
		else {echo "      <option value=\"$dmrColorCodeInput\">$dmrColorCodeInput</option>\n"; }
	} ?>
    </select></td>
    </tr>
    <tr>
    <td align="left"><a class="tooltip2" href="#"><?php echo $lang['dmr_embeddedlconly'];?>:<span><b>DMR EmbeddedLCOnly</b>Turn ON to disable extended message support, including GPS and Talker Alias data. This can help reduce problems with some DMR Radios that do not support such features.</span></a></td>
    <td align="left">
    <?php if ($configmmdvm['DMR']['EmbeddedLCOnly'] == 1) { echo "<div class=\"switch\"><input id=\"toggle-dmrEmbeddedLCOnly\" class=\"toggle toggle-round-flat\" type=\"checkbox\" name=\"dmrEmbeddedLCOnly\" value=\"ON\" checked=\"checked\" /><label for=\"toggle-dmrEmbeddedLCOnly\"></label></div>\n"; }
    else { echo "<div class=\"switch\"><input id=\"toggle-dmrEmbeddedLCOnly\" class=\"toggle toggle-round-flat\" type=\"checkbox\" name=\"dmrEmbeddedLCOnly\" value=\"ON\" /><label for=\"toggle-dmrEmbeddedLCOnly\"></label></div>\n"; } ?>
    </td></tr>
    <tr>
    <td align="left"><a class="tooltip2" href="#"><?php echo $lang['dmr_dumptadata'];?>:<span><b>DMR DumpTAData</b>Turn ON to dump GPS and Talker Alias data to MMDVMHost log file.</span></a></td>
    <td align="left">
    <?php if ($configmmdvm['DMR']['DumpTAData'] == 1) { echo "<div class=\"switch\"><input id=\"toggle-dmrDumpTAData\" class=\"toggle toggle-round-flat\" type=\"checkbox\" name=\"dmrDumpTAData\" value=\"ON\" checked=\"checked\" /><label for=\"toggle-dmrDumpTAData\"></label></div>\n"; }
    else { echo "<div class=\"switch\"><input id=\"toggle-dmrDumpTAData\" class=\"toggle toggle-round-flat\" type=\"checkbox\" name=\"dmrDumpTAData\" value=\"ON\" /><label for=\"toggle-dmrDumpTAData\"></label></div>\n"; } ?>
    </td></tr>
    <!-- <tr>
    <td align="left"><a class="tooltip2" href="#"><?php echo "DMR JitterBuffer";?>:<span><b>DMR JitterBuffer</b>Turn on for improved network resiliancy, in high Latency networks.</span></a></td>
    <td align="left">
    <?php // if ((isset($configmmdvm['DMR Network']['JitterEnabled'])) && ($configmmdvm['DMR Network']['JitterEnabled'] == 0)) { echo "<div class=\"switch\"><input id=\"toggle-dmrJitterBufer\" class=\"toggle toggle-round-flat\" type=\"checkbox\" name=\"dmrDMRnetJitterBufer\" value=\"ON\"/><label for=\"toggle-dmrJitterBufer\"></label></div>\n"; }
    // else { echo "<div class=\"switch\"><input id=\"toggle-dmrJitterBufer\" class=\"toggle toggle-round-flat\" type=\"checkbox\" name=\"dmrDMRnetJitterBufer\" value=\"ON\" checked=\"checked\" /><label for=\"toggle-dmrJitterBufer\"></label></div>\n"; }
    ?>
    </td></tr>. -->
    </table>
	<div><input type="button" value="<?php echo $lang['apply'];?>" onclick="submitform()" /><br /><br /></div>
<?php } ?>

<?php if (file_exists('/etc/dstar-radio.dstarrepeater') || $configmmdvm['D-Star']['Enable'] == 1) { ?>
	<h2><?php echo $lang['dstar_config'];?></h2>
	<input type="hidden" name="confTimeAnnounce" value="OFF" />
	<input type="hidden" name="confHostFilesNoDExtra" value="OFF" />
    <table>
    <tr>
    <th width="200"><a class="tooltip" href="#"><?php echo $lang['setting'];?><span><b>Setting</b></span></a></th>
    <th colspan="2"><a class="tooltip" href="#"><?php echo $lang['value'];?><span><b>Value</b>The current value from the<br />configuration files</span></a></th>
    </tr>
    <tr>
    <td align="left"><a class="tooltip2" href="#"><?php echo $lang['dstar_rpt1'];?>:<span><b>RPT1 Callsign</b>This is the RPT1 field for your radio</span></a></td>
    <td align="left" colspan="2"><?php echo str_replace(' ', '&nbsp;', substr($configdstar['callsign'], 0, 7)) ?>
	<select name="confDStarModuleSuffix">
	<?php echo "  <option value=\"".substr($configdstar['callsign'], 7)."\" selected=\"selected\">".substr($configdstar['callsign'], 7)."</option>\n"; ?>
        <option>A</option>
        <option>B</option>
        <option>C</option>
        <option>D</option>
        <option>E</option>
        <option>F</option>
        <option>G</option>
        <option>H</option>
        <option>I</option>
        <option>J</option>
        <option>K</option>
        <option>L</option>
        <option>M</option>
        <option>N</option>
        <option>O</option>
        <option>P</option>
        <option>Q</option>
        <option>R</option>
        <option>S</option>
        <option>T</option>
        <option>U</option>
        <option>V</option>
        <option>W</option>
        <option>X</option>
        <option>Y</option>
        <option>Z</option>
    </select></td>
    </tr>
    <tr>
    <td align="left"><a class="tooltip2" href="#"><?php echo $lang['dstar_rpt2'];?>:<span><b>RPT2 Callsign</b>This is the RPT2 field for your radio</span></a></td>
    <td align="left" colspan="2"><?php echo str_replace(' ', '&nbsp;', $configdstar['gateway']) ?></td>
    </tr>
    <tr>
    <td align="left"><a class="tooltip2" href="#"><?php echo $lang['dstar_irc_password'];?>:<span><b>Remote Password</b>Used for ircDDBGateway remote control access</span></a></td>
    <td align="left" colspan="2"><input type="password" name="confPassword" size="30" maxlength="30" value="<?php echo $configs['remotePassword'] ?>" /></td>
    </tr>
    <tr>
    <td align="left"><a class="tooltip2" href="#"><?php echo $lang['dstar_default_ref'];?>:<span><b>Default Reflector</b>Used for setting the default reflector.</span></a></td>
    <td align="left" colspan="1"><select name="confDefRef"
	onchange="if (this.options[this.selectedIndex].value == 'customOption') {
	  toggleField(this,this.nextSibling);
	  this.selectedIndex='0';
	  } ">
<?php
$dcsFile = fopen("/usr/local/etc/DCS_Hosts.txt", "r");
$dplusFile = fopen("/usr/local/etc/DPlus_Hosts.txt", "r");
$dextraFile = fopen("/usr/local/etc/DExtra_Hosts.txt", "r");

echo "    <option value=\"".substr($configs['reflector1'], 0, 6)."\" selected=\"selected\">".substr($configs['reflector1'], 0, 6)."</option>\n";
echo "    <option value=\"customOption\">Text Entry</option>\n";

while (!feof($dcsFile)) {
	$dcsLine = fgets($dcsFile);
	if (strpos($dcsLine, 'DCS') !== FALSE && strpos($dcsLine, '#') === FALSE)
		echo "	<option value=\"".substr($dcsLine, 0, 6)."\">".substr($dcsLine, 0, 6)."</option>\n";
}
fclose($dcsFile);
while (!feof($dplusFile)) {
	$dplusLine = fgets($dplusFile);
	if (strpos($dplusLine, 'REF') !== FALSE && strpos($dplusLine, '#') === FALSE) {
		echo "	<option value=\"".substr($dplusLine, 0, 6)."\">".substr($dplusLine, 0, 6)."</option>\n";
	}
	if (strpos($dplusLine, 'XRF') !== FALSE && strpos($dplusLine, '#') === FALSE) {
		echo "	<option value=\"".substr($dplusLine, 0, 6)."\">".substr($dplusLine, 0, 6)."</option>\n";
	}
}
fclose($dplusFile);
while (!feof($dextraFile)) {
	$dextraLine = fgets($dextraFile);
	if (strpos($dextraLine, 'XRF') !== FALSE && strpos($dextraLine, '#') === FALSE)
		echo "	<option value=\"".substr($dextraLine, 0, 6)."\">".substr($dextraLine, 0, 6)."</option>\n";
}
fclose($dextraFile);

?>
    </select><input name="confDefRef" style="display:none;" disabled="disabled" type="text" size="7" maxlength="7"
            onblur="if(this.value==''){toggleField(this,this.previousSibling);}" />
    <select name="confDefRefLtr">
	<?php echo "  <option value=\"".substr($configs['reflector1'], 7)."\" selected=\"selected\">".substr($configs['reflector1'], 7)."</option>\n"; ?>
        <option>A</option>
        <option>B</option>
        <option>C</option>
        <option>D</option>
        <option>E</option>
        <option>F</option>
        <option>G</option>
        <option>H</option>
        <option>I</option>
        <option>J</option>
        <option>K</option>
        <option>L</option>
        <option>M</option>
        <option>N</option>
        <option>O</option>
        <option>P</option>
        <option>Q</option>
        <option>R</option>
        <option>S</option>
        <option>T</option>
        <option>U</option>
        <option>V</option>
        <option>W</option>
        <option>X</option>
        <option>Y</option>
        <option>Z</option>
    </select>
    </td>
    <td width="300">
    <input type="radio" name="confDefRefAuto" value="ON"<?php if ($configs['atStartup1'] == '1') {echo ' checked="checked"';} ?> />Startup
    <input type="radio" name="confDefRefAuto" value="OFF"<?php if ($configs['atStartup1'] == '0') {echo ' checked="checked"';} ?> />Manual</td>
    </tr>
    <tr>
    <td align="left"><a class="tooltip2" href="#"><?php echo $lang['dstar_irc_lang'];?>:<span><b>ircDDBGateway Language</b>Set your prefered language here</span></a></td>
    <td colspan="2" style="text-align: left;"><select name="ircDDBGatewayAnnounceLanguage">
<?php
        $testIrcLanguage = $configs['language'];
	if (is_readable("/var/www/dashboard/config/ircddbgateway_languages.inc")) {
	  $ircLanguageFile = fopen("/var/www/dashboard/config/ircddbgateway_languages.inc", "r");
        while (!feof($ircLanguageFile)) {
                $ircLanguageFileLine = fgets($ircLanguageFile);
                $ircLanguage = preg_split('/;/', $ircLanguageFileLine);
                if ((strpos($ircLanguage[0], '#') === FALSE ) && ($ircLanguage[0] != '')) {
			$ircLanguage[2] = rtrim($ircLanguage[2]);
                        if ($testIrcLanguage == $ircLanguage[1]) { echo "      <option value=\"$ircLanguage[1],$ircLanguage[2]\" selected=\"selected\">".htmlspecialchars($ircLanguage[0])."</option>\n"; }
                        else { echo "      <option value=\"$ircLanguage[1],$ircLanguage[2]\">".htmlspecialchars($ircLanguage[0])."</option>\n"; }
                }
        }
          fclose($ircLanguageFile);
	}
        ?>
    </select></td>
    </tr>
    <tr>
    <td align="left"><a class="tooltip2" href="#"><?php echo $lang['dstar_irc_time'];?>:<span><b>Time Announce</b>Announce time hourly</span></a></td>
    <?php
	if ( !file_exists('/etc/timeserver.dissable') ) {
		echo "<td align=\"left\" colspan=\"2\"><div class=\"switch\"><input id=\"toggle-timeAnnounce\" class=\"toggle toggle-round-flat\" type=\"checkbox\" name=\"confTimeAnnounce\" value=\"ON\" checked=\"checked\" /><label for=\"toggle-timeAnnounce\"></label></div></td>\n";
		}
	else {
		echo "<td align=\"left\" colspan=\"2\"><div class=\"switch\"><input id=\"toggle-timeAnnounce\" class=\"toggle toggle-round-flat\" type=\"checkbox\" name=\"confTimeAnnounce\" value=\"ON\" /><label for=\"toggle-timeAnnounce\"></label></div></td>\n";
	}
    ?>
    </tr>
    <tr>
    <td align="left"><a class="tooltip2" href="#">Use DPlus for XRF:<span><b>No DExtra</b>Should host files use DPlus Protocol for XRFs</span></a></td>
    <?php
	if ( file_exists('/etc/hostfiles.nodextra') ) {
		echo "<td align=\"left\"><div class=\"switch\"><input id=\"toggle-dplusHostFiles\" class=\"toggle toggle-round-flat\" type=\"checkbox\" name=\"confHostFilesNoDExtra\" value=\"ON\" checked=\"checked\" /><label for=\"toggle-dplusHostFiles\"></label></div></td>\n";
		}
	else {
		echo "<td align=\"left\"><div class=\"switch\"><input id=\"toggle-dplusHostFiles\" class=\"toggle toggle-round-flat\" type=\"checkbox\" name=\"confHostFilesNoDExtra\" value=\"ON\" /><label for=\"toggle-dplusHostFiles\"></label></div></td>\n";
	}
    ?>
    <td>Note: Update Required if changed</td>
    </tr>
    </table>
	<div><input type="button" value="<?php echo $lang['apply'];?>" onclick="submitform()" /><br /><br /></div>
<?php } ?>


<?php if (file_exists('/etc/dstar-radio.mmdvmhost') && ($configmmdvm['System Fusion Network']['Enable'] == 1 || $configdmr2ysf['Enabled']['Enabled'] == 1 )) { ?>
	<input type="hidden" name="APRSEnable" value="OFF" />
	<input type="hidden" name="confHostFilesYSFUpper" value="OFF" />
	<input type="hidden" name="wiresXCommandPassthrough" value="OFF" />
	<input type="hidden" name="HotSpotFollow" value="OFF" />
	<input type="hidden" name="DMREnable" value="OFF" />
	<input type="hidden" name="FCSEnable" value="OFF" />	
	<h2><?php echo "EA7EE Yaesu System Fusion Configuration";?></h2>
    <table>
    <tr>
    <th width="200"><a class="tooltip" href="#"><?php echo $lang['setting'];?><span><b>Setting</b></span></a></th>
    <th colspan="2"><a class="tooltip" href="#"><?php echo $lang['value'];?><span><b>Value</b>The current value from the<br />configuration files</span></a></th>
    </tr>

    <tr>
    <td align="left"><a class="tooltip2" href="#"><?php echo "Startup Mode";?>:<span><b>Mode</b>Set your prefered Startup Mode here</span></a></td>
    <td colspan="2" style="text-align: left;"><select name="ysfStartupMode">
<?php
        if (isset($configysfgateway['Network']['Type'])) {
                $testYSFType = $configysfgateway['Network']['Type'];
                echo "      <option value=\"none\">None</option>\n";
        	}
        else {
                $testYSFType = "none";
                echo "      <option value=\"none\" selected=\"selected\">None</option>\n";
			}

		if ($testYSFType == "YSF")  {
			echo "      <option value=\"YSF\" selected=\"selected\">YSF</option>\n";
	    } else {
			echo "      <option value=\"YSF\">YSF</option>\n";
		} 
	   	if ($testYSFType == "FCS") {
			echo "      <option value=\"FCS\" selected=\"selected\">FCS</option>\n";
		} else {
			echo "      <option value=\"FCS\">FCS</option>\n";
		}
		if ($testYSFType == "DMR")  {
			echo "      <option value=\"DMR\" selected=\"selected\">DMR</option>\n";
		} else {
			echo "      <option value=\"DMR\">DMR</option>\n";
		}
		if ($testYSFType == "NXDN") {
			echo "      <option value=\"NXDN\" selected=\"selected\">NXDN</option>\n";
	   	} else {
			echo "      <option value=\"NXDN\">NXDN</option>\n";
		}
	   	if ($testYSFType == "P25") {
		   echo "      <option value=\"P25\" selected=\"selected\">P25</option>\n";
	   	} else {
			echo "      <option value=\"P25\">P25</option>\n";
		}		
?>
</select></td>	
</tr>	
<tr>
<td align="left"><a class="tooltip2" href="#"><?php echo $lang['ysf_startup_host'];?>:<span><b>YSF Host</b>Set your prefered YSF Host here</span></a></td>
<td colspan="2" style="text-align: left;"><select name="ysfStartupHost">
<?php
		$ysfHosts = fopen("/usr/local/etc/YSFHosts.txt", "r");
		if (isset($configysfgateway['YSF Network']['Startup'])) {
                $testYSFHost = $configysfgateway['YSF Network']['Startup'];
                echo "      <option value=\"none\">None</option>\n";
        	}
        else {
                $testYSFHost = "none";
                echo "      <option value=\"none\" selected=\"selected\">None</option>\n";
			} 
       while (!feof($ysfHosts)) {
                $ysfHostsLine = fgets($ysfHosts);
                $ysfHost = preg_split('/;/', $ysfHostsLine);
                if ((strpos($ysfHost[0], '#') === FALSE ) && ($ysfHost[0] != '')) {
                        if ( ($testYSFHost == $ysfHost[0]) || ($testYSFHost == $ysfHost[1]) ) { echo "      <option value=\"$ysfHost[0],$ysfHost[1]\" selected=\"selected\">YSF$ysfHost[0] - ".htmlspecialchars($ysfHost[1])." - ".htmlspecialchars($ysfHost[2])."</option>\n"; }
			else { echo "      <option value=\"$ysfHost[0],$ysfHost[1]\">YSF$ysfHost[0] - ".htmlspecialchars($ysfHost[1])." - ".htmlspecialchars($ysfHost[2])."</option>\n"; }
                }
        }
        fclose($ysfHosts);
        ?>
    </select></td>
    </tr>
    <tr>
 	<td align="left"><a style="color:red;" class="tooltip2" href="#"><?php echo "Startup DG-ID for TreeHouse";?>:<span><b style="background-color:#6bff33;">Initial DG-ID to use</b>This is default DGID room for the TreeHouse server. Others servers leave blank</span></a></td>
    <td align="left" colspan="2"><input type="text" name="StartupDGID" size="2" maxlength="2" value="<?php echo $configysfgateway['YSF Network']['StartupDGID'];?>" /></td>
    </tr>

    <tr>
    <td align="left"><a style="color:red;" class="tooltip2" href="#"><?php echo "Statical DGID's for YCS-Network";?>:<span><b style="background-color:#6bff33;">YCS Network</b>Example: 14;24;96</span></a></td>
    <td align="left" colspan="2"> Options=<input type="text" name="ysfgatewayYSFNetworkOptions" size="80" maxlength="100" value="<?php if (isset($configysfgateway['YSF Network']['Options'])) { echo $configysfgateway['YSF Network']['Options']; } ?>" /></td>
    </tr>

    <td align="left"><a class="tooltip2" href="#">APRS Enable:<span><b>APRS Enable</b>Enable APRS output only for EA7EE ysfgateway.</span></a></td>
    <?php
	if ( isset($configysfgateway['aprs.fi']['Enable']) ) {
		if ( $configysfgateway['aprs.fi']['Enable'] ) {
			echo "<td align=\"left\" colspan=\"2\"><div class=\"switch\"><input id=\"toggle-APRSEnable\" class=\"toggle toggle-round-flat\" type=\"checkbox\" name=\"APRSEnable\" value=\"ON\" checked=\"checked\" /><label for=\"toggle-APRSEnable\"></label></div></td>\n";
		}
		else {
			echo "<td align=\"left\" colspan=\"2\"><div class=\"switch\"><input id=\"toggle-APRSEnable\" class=\"toggle toggle-round-flat\" type=\"checkbox\" name=\"APRSEnable\" value=\"ON\" /><label for=\"toggle-APRSEnable\"></label></div></td>\n";
		}
	} else {
		echo "<td align=\"left\" colspan=\"2\"><div class=\"switch\"><input id=\"toggle-APRSEnable\" class=\"toggle toggle-round-flat\" type=\"checkbox\" name=\"APRSEnable\" value=\"ON\" /><label for=\"toggle-APRSEnable\"></label></div></td>\n";
	}
    ?>
    </tr>

	<td align="left"><a class="tooltip2" href="#"><?php echo "APRS Callsign";?>:<span><b>Callsign for APRS-IS</b>When conected to APRS-IS Callsign must be unique. You would need to add -1,-2.. for APRS to connect when using many hotspot</span></a></td>
    <td align="left" colspan="2"><input type="text" name="APRSCallsign" size="10" maxlength="10" value="<?php echo $configysfgateway['aprs.fi']['AprsCallsign'];?>" /></td>
    </tr>
    <tr>
 	<td align="left"><a class="tooltip2" href="#"><?php echo "aprs.fi ApiKey";?>:<span><b>ApiKey from aprs.fi</b>The AprsKey from registration on aprs.fi</span></a></td>
    <td align="left" colspan="2"><input type="text" name="APIKey" size="23" maxlength="23" value="<?php echo $configysfgateway['aprs.fi']['APIKey'];?>" /></td>
    </tr>
    <tr>
 	<td align="left"><a class="tooltip2" href="#"><?php echo "Beacon Time";?>:<span><b>Beacon Time in minutes</b>Beacon Time, 0 = Beacon OFF</span></a></td>
    <td align="left" colspan="2"><input type="text" name="BeaconTime" size="2" maxlength="2" value="<?php echo $configysfgateway['General']['BeaconTime'];?>" /></td>
    </tr>
    <tr>
 	<td align="left"><a class="tooltip2" href="#"><?php echo "Re-Link Time";?>:<span><b>Re-Link Time in minutes</b>Timeout Time, 0 = No Timeout</span></a></td>
    <td align="left" colspan="2"><input type="text" name="TimeoutTime" size="2" maxlength="2" value="<?php echo $configysfgateway['Network']['InactivityTimeout'];?>" /></td>
    </tr>
    <tr>
    <tr>
    <td align="left"><a class="tooltip2" href="#">UPPERCASE Hostfiles:<span><b>UPPERCASE Hostfiles</b>Should host files use UPPERCASE only - fixes issues with FT-70D radios.</span></a></td>
    <?php
	if ( isset($configysfgateway['General']['WiresXMakeUpper']) ) {
		if ( $configysfgateway['General']['WiresXMakeUpper'] ) {
			echo "<td align=\"left\"><div class=\"switch\"><input id=\"toggle-confHostFilesYSFUpper\" class=\"toggle toggle-round-flat\" type=\"checkbox\" name=\"confHostFilesYSFUpper\" value=\"ON\" checked=\"checked\" /><label for=\"toggle-confHostFilesYSFUpper\"></label></div></td>\n";
		}
		else {
			echo "<td align=\"left\"><div class=\"switch\"><input id=\"toggle-confHostFilesYSFUpper\" class=\"toggle toggle-round-flat\" type=\"checkbox\" name=\"confHostFilesYSFUpper\" value=\"ON\" /><label for=\"toggle-confHostFilesYSFUpper\"></label></div></td>\n";
		}
	} else {
		echo "<td align=\"left\"><div class=\"switch\"><input id=\"toggle-confHostFilesYSFUpper\" class=\"toggle toggle-round-flat\" type=\"checkbox\" name=\"confHostFilesYSFUpper\" value=\"ON\" /><label for=\"toggle-confHostFilesYSFUpper\"></label></div></td>\n";
	}
    ?>
    <td>Note: Update Required if changed</td>
    </tr>
    <tr>
    <td align="left"><a class="tooltip2" href="#">WiresX Passthrough:<span><b>WiresX Auto Passthrough</b>Use this to automatically send WiresX commands through to YSF2xxx cross-over modes.</span></a></td>
    <?php
	if ( isset($configysfgateway['General']['WiresXCommandPassthrough']) ) {
		if ( $configysfgateway['General']['WiresXCommandPassthrough'] ) {
			echo "<td align=\"left\" colspan=\"2\"><div class=\"switch\"><input id=\"toggle-confWiresXCommandPassthrough\" class=\"toggle toggle-round-flat\" type=\"checkbox\" name=\"wiresXCommandPassthrough\" value=\"ON\" checked=\"checked\" /><label for=\"toggle-confWiresXCommandPassthrough\"></label></div></td>\n";
		}
		else {
			echo "<td align=\"left\" colspan=\"2\"><div class=\"switch\"><input id=\"toggle-confWiresXCommandPassthrough\" class=\"toggle toggle-round-flat\" type=\"checkbox\" name=\"wiresXCommandPassthrough\" value=\"ON\" /><label for=\"toggle-confWiresXCommandPassthrough\"></label></div></td>\n";
		}
	} else {
		echo "<td align=\"left\" colspan=\"2\"><div class=\"switch\"><input id=\"toggle-confWiresXCommandPassthrough\" class=\"toggle toggle-round-flat\" type=\"checkbox\" name=\"wiresXCommandPassthrough\" value=\"ON\" /><label for=\"toggle-confWiresXCommandPassthrough\"></label></div></td>\n";
	}
    ?>
    </tr>
    <tr>
    <td align="left"><a class="tooltip2" href="#">Hotspot Follow User:<span><b>Hotspot Follow User</b>Use this to change HotSpot Location when main user changes location (personal moving hotspots).</span></a></td>
    <?php
	if ( isset($configysfgateway['aprs.fi']['HotSpotFollow']) ) {
		if ( $configysfgateway['aprs.fi']['HotSpotFollow'] ) {
			echo "<td align=\"left\" colspan=\"2\"><div class=\"switch\"><input id=\"toggle-confHotSpotFollow\" class=\"toggle toggle-round-flat\" type=\"checkbox\" name=\"HotSpotFollow\" value=\"ON\" checked=\"checked\" /><label for=\"toggle-confHotSpotFollow\"></label></div></td>\n";
		}
		else {
			echo "<td align=\"left\" colspan=\"2\"><div class=\"switch\"><input id=\"toggle-confHotSpotFollow\" class=\"toggle toggle-round-flat\" type=\"checkbox\" name=\"HotSpotFollow\" value=\"ON\" /><label for=\"toggle-confHotSpotFollow\"></label></div></td>\n";
		}
	} else {
		echo "<td align=\"left\" colspan=\"2\"><div class=\"switch\"><input id=\"toggle-confHotSpotFollow\" class=\"toggle toggle-round-flat\" type=\"checkbox\" name=\"HotSpotFollow\" value=\"ON\" /><label for=\"toggle-confHotSpotFollow\"></label></div></td>\n";
	}
    ?>
    </tr>
	<tr>
    <td align="left"><a class="tooltip2" href="#">DMR Enable:<span><b>DMR Enable</b>Enable DMR Transcoding.</span></a></td>
    <?php
	if ( isset($configysfgateway['DMR Network']['Enable']) ) {
		if ( $configysfgateway['DMR Network']['Enable'] ) {
			echo "<td align=\"left\"><div class=\"switch\"><input id=\"toggle-DMREnable\" class=\"toggle toggle-round-flat\" type=\"checkbox\" name=\"DMREnable\" value=\"ON\" checked=\"checked\" /><label for=\"toggle-DMREnable\"></label></div></td>\n";
		}
		else {
			echo "<td align=\"left\"><div class=\"switch\"><input id=\"toggle-DMREnable\" class=\"toggle toggle-round-flat\" type=\"checkbox\" name=\"DMREnable\" value=\"ON\" /><label for=\"toggle-DMREnable\"></label></div></td>\n";
		}
	} else {
		echo "<td align=\"left\"><div class=\"switch\"><input id=\"toggle-DMREnable\" class=\"toggle toggle-round-flat\" type=\"checkbox\" name=\"DMREnable\" value=\"ON\" /><label for=\"toggle-DMREnable\"></label></div></td>\n";
	}
    ?>
    <td>Note: Update Required if changed</td>
    </tr>
    </tr>
	<td align="left"><a class="tooltip2" href="#"><?php echo "ESS DMR Id";?>:<span><b>ESS DMR Id</b>Extended (9 digit number) Id for DMR</span></a></td>
    <td align="left" colspan="2"><input type="text" name="ysfESSId" size="13" maxlength="9" value="<?php echo $configysfgateway['General']['Id'];?>" /></td>
    </tr>

    <tr>
    <td align="left"><a class="tooltip2" href="#"><?php echo $lang['dmr_master'];?>:<span><b>DMR Master</b>Set your prefered DMR master here</span></a></td>
    <td colspan="2" style="text-align: left;"><select name="ysfdmrMasterHost">
<?php
		$mastertmp = "BM"; 
		$dmrMasterFile = fopen("/usr/local/etc/DMR_Hosts.txt", "r");
        $testMMDVMysf2dmrMaster = $configysfgateway['DMR Network']['Address'];
        while (!feof($dmrMasterFile)) {
                $dmrMasterLine = fgets($dmrMasterFile);
                $ysfdmrMasterHost = preg_split('/\s+/', $dmrMasterLine);
                if ((strpos($ysfdmrMasterHost[0], '#') === FALSE ) && (substr($ysfdmrMasterHost[0], 0, 3) != "XLX") && (substr($ysfdmrMasterHost[0], 0, 4) != "DMRG") && (substr($ysfdmrMasterHost[0], 0, 4) != "DMR2") && ($ysfdmrMasterHost[0] != '')) {
                        if ($testMMDVMysf2dmrMaster == $ysfdmrMasterHost[2]) { $ysfmastertmp = $ysfdmrMasterHost[0]; echo "      <option value=\"$ysfdmrMasterHost[2],$ysfdmrMasterHost[3],$ysfdmrMasterHost[4],$ysfdmrMasterHost[0]\" selected=\"selected\">$ysfdmrMasterHost[0]</option>\n"; }
                        else { echo "      <option value=\"$ysfdmrMasterHost[2],$ysfdmrMasterHost[3],$ysfdmrMasterHost[4],$ysfdmrMasterHost[0]\">$ysfdmrMasterHost[0]</option>\n";}
                }
        }
        fclose($dmrMasterFile);
        ?>
    </select></td>
    </tr>

	<tr>
	<td align="left"><a class="tooltip2" href="#"><?php echo "DMR Startup Host";?>:<span><b>DMR Host</b>Set your prefered DMR Host here</span></a></td>
	<td colspan="2" style="text-align: left;"><select name="dmrStartupHost">
	<?php

			if (isset($configysfgateway['DMR Network']['Startup'])) {
				$testDMRHost = $configysfgateway['DMR Network']['Startup'];
				echo "      <option value=\"none\">None</option>\n";
			}
			else {
					$testDMRHost = "none";
					echo "      <option value=\"none\" selected=\"selected\">None</option>\n";
			} 


			if (file_exists("/usr/local/etc/DMRHosts.txt") && file_exists("/usr/local/etc/DMRP_Talkgroups.txt")) {
				if (substr($ysfmastertmp,0,2) == "BM") {
					$dmrFile = fopen("/usr/local/etc/DMRHosts.txt", "r");
					while (!feof($dmrFile)) {
						$dmrLine = fgets($dmrFile);
						$dmrHost = preg_split('/;/', $dmrLine);
						if ((strpos($dmrHost[0], '#') === FALSE ) && ($dmrHost[0] != '')) {
							if (($testDMRHost == $dmrHost[0]) || ($testDMRHost == $dmrHost[3]) ) { echo "      <option value=\"$dmrHost[0]\" selected=\"selected\">$dmrHost[0] - ".htmlspecialchars($dmrHost[3])."</option>\n";}
							else { echo "      <option value=\"$dmrHost[0]\">$dmrHost[0] - ".htmlspecialchars($dmrHost[3])."</option>\n"; }
						}
					}
					fclose($dmrFile);
				}	
				elseif (substr($ysfmastertmp,0,2) == "AA") {
					$dmrFile = fopen("/usr/local/etc/DMRPA7_Talkgroups.txt", "r");
					while (!feof($dmrFile)) {
						$dmrLine = fgets($dmrFile);
						$dmrHost = preg_split('/;/', $dmrLine);			
						if ((strpos($dmrHost[0], '#') === FALSE ) && ($dmrHost[0] != '')) {
							if (($testDMRHost == $dmrHost[0]) || ($testDMRHost == $dmrHost[1]) ) { echo "      <option value=\"$dmrHost[0]\" selected=\"selected\">$dmrHost[0] - ".htmlspecialchars($dmrHost[1])."</option>\n";}
							else { echo "      <option value=\"$dmrHost[0]\">$dmrHost[0] - ".htmlspecialchars($dmrHost[1])."</option>\n"; }
						}
					}
					fclose($dmrFile);						
				}
				else {
					$dmrFile = fopen("/usr/local/etc/DMRP_Talkgroups.txt", "r");
					while (!feof($dmrFile)) {
						$dmrLine = fgets($dmrFile);
						$dmrHost = preg_split('/;/', $dmrLine);			
						if ((strpos($dmrHost[0], '#') === FALSE ) && ($dmrHost[0] != '')) {
							if (($testDMRHost == $dmrHost[0]) || ($testDMRHost == $dmrHost[1]) ) { echo "      <option value=\"$dmrHost[0]\" selected=\"selected\">$dmrHost[0] - ".htmlspecialchars($dmrHost[1])."</option>\n";}
							else { echo "      <option value=\"$dmrHost[0]\">$dmrHost[0] - ".htmlspecialchars($dmrHost[1])."</option>\n"; }
						}
					}
					fclose($dmrFile);		
				}
			}
			?>
	</select></td>
	</tr>
	<?php
	if (substr($ysfmastertmp,0,2) == "BM") {
		echo "<tr>	<td align=\"left\"><a class=\"tooltip2\" href=\"#\"> PassWord:<span><b>Password</b>Password for DMR Server</span></a></td> <td align=\"left\" colspan=\"2\"><input type=\"password\" name=\"dmrPassWord\" size=\"23\" maxlength=\"23\" value=";
		echo $configysfgateway['DMR Network']['Password'];
		echo " /></td> </tr> ";
	} else {
		$configysfgateway['DMR Network']['Password']="passw0rd";
	} 
	?>
    <tr>
    <td align="left"><a class="tooltip2" href="#"><?php echo $lang['dmr_plus_network'];?>:<span><b>DMR+ Network</b>Set your options= for DMR+ here</span></a></td>
    <td align="left" colspan="2"> Options=<input type="text" name="ysfgatewayNetworkOptions" size="40" maxlength="100" value="<?php if (isset($configysfgateway['DMR Network']['Options'])) { echo $configysfgateway['DMR Network']['Options']; } ?>" /></td>
    </tr>
	<tr>
	<td align="left"><a class="tooltip2" href="#">FCS Enable:<span><b>FCS Enable</b>Enable FCS.</span></a></td>
	<?php
	if ( isset($configysfgateway['FCS Network']['Enable']) ) {
		if ( $configysfgateway['FCS Network']['Enable'] ) {
			echo "<td align=\"left\"><div class=\"switch\"><input id=\"toggle-FCSEnable\" class=\"toggle toggle-round-flat\" type=\"checkbox\" name=\"FCSEnable\" value=\"ON\" checked=\"checked\" /><label for=\"toggle-FCSEnable\"></label></div></td>\n";
		}
		else {
			echo "<td align=\"left\"><div class=\"switch\"><input id=\"toggle-FCSEnable\" class=\"toggle toggle-round-flat\" type=\"checkbox\" name=\"FCSEnable\" value=\"ON\" /><label for=\"toggle-FCSEnable\"></label></div></td>\n";
		}
	} else {
		echo "<td align=\"left\"><div class=\"switch\"><input id=\"toggle-FCSEnable\" class=\"toggle toggle-round-flat\" type=\"checkbox\" name=\"FCSEnable\" value=\"ON\" /><label for=\"toggle-FCSEnable\"></label></div></td>\n";
	}
	?>
	<td>Note: Update Required if changed</td>
	</tr>
	<tr>
	<td align="left"><a class="tooltip2" href="#"><?php echo "FCS Startup Host";?>:<span><b>FCS Host</b>Set your prefered FCS Host here</span></a></td>
	<td colspan="2" style="text-align: left;"><select name="fcsStartupHost">
	<?php
        if (isset($configysfgateway['FCS Network']['Startup'])) {
                $testFCSHost = "FCS".$configysfgateway['FCS Network']['Startup'];
                echo "      <option value=\"none\">None</option>\n";
        	}
        else {
                $testFCSHost = "none";
                echo "      <option value=\"none\" selected=\"selected\">None</option>\n";
			} 

		if (file_exists("/usr/local/etc/FCSHosts.txt")) {
            $fcsHosts = fopen("/usr/local/etc/FCSHosts.txt", "r");
            while (!feof($fcsHosts)) {
                $fcsHostsLine = fgets($fcsHosts);
                $fcsHost = preg_split('/;/', $fcsHostsLine);
				if (substr($fcsHost[0], 0, 3) == "FCS") {
                    if ( ($testFCSHost == $fcsHost[0]) || ($testFCSHost == $fcsHost[1]) ) { echo "      <option value=\"$fcsHost[0]\" selected=\"selected\">$fcsHost[0] - ".htmlspecialchars($fcsHost[1])."</option>\n"; }
                    else { echo "      <option value=\"$fcsHost[0]\">$fcsHost[0] - ".htmlspecialchars($fcsHost[1])."</option>\n"; }
                        }
                }
                fclose($fcsHosts);
        }
        ?>

		<tr>
		<td align="left"><a style="color:red;" class="tooltip2" href="#"><?php echo "Statical DGID's for YCS Network";?><span><b style="background-color:#6bff33;">FCS Options</b>Example: 14;24;96</span></a></td>
		<td align="left" colspan="2"> Options=<input type="text" name="ysfgatewayFCSNetworkOptions" size="80" maxlength="100" value="<?php if (isset($configysfgateway['FCS Network']['Options'])) { echo $configysfgateway['FCS Network']['Options']; } ?>" /></td>
		</tr>

    </select></td>
    </tr>

    <?php if (file_exists('/etc/dstar-radio.mmdvmhost') && $configysf2nxdn['Enabled']['Enabled'] == 1) { ?>
    <tr>
      <td align="left"><a class="tooltip2" href="#">(YSF2NXDN) NXDN ID:<span><b>NXDN ID</b>Enter your NXDN ID here</span></a></td>
      <td align="left" colspan="2"><input type="text" name="ysf2nxdnId" size="13" maxlength="5" value="<?php if (isset($configysf2nxdn['NXDN Network']['Id'])) { echo $configysf2nxdn['NXDN Network']['Id']; } ?>" /></td>
    </tr>
    <tr>
        <td align="left"><a class="tooltip2" href="#"><?php echo $lang['nxdn_startup_host'];?>:<span><b>NXDN Host</b>Set your prefered NXDN Host here</span></a></td>
        <td colspan="2" style="text-align: left;"><select name="ysf2nxdnStartupDstId">
<?php
	$nxdnHosts = fopen("/usr/local/etc/NXDNHosts.txt", "r");
	$testNXDNHost = $configysf2nxdn['NXDN Network']['StartupDstId'];
	if ($testNXDNHost == "") { echo "      <option value=\"none\" selected=\"selected\">None</option>\n"; }
        else { echo "      <option value=\"none\">None</option>\n"; }
	if ($testNXDNHost == "10") { echo "      <option value=\"10\" selected=\"selected\">10 - Parrot</option>\n"; }
        else { echo "      <option value=\"10\">10 - Parrot</option>\n"; }
        while (!feof($nxdnHosts)) {
                $nxdnHostsLine = fgets($nxdnHosts);
                $nxdnHost = preg_split('/\s+/', $nxdnHostsLine);
                if ((strpos($nxdnHost[0], '#') === FALSE ) && ($nxdnHost[0] != '')) {
                        if ($testNXDNHost == $nxdnHost[0]) { echo "      <option value=\"$nxdnHost[0]\" selected=\"selected\">$nxdnHost[0] - $nxdnHost[1]</option>\n"; }
                        else { echo "      <option value=\"$nxdnHost[0]\">$nxdnHost[0] - $nxdnHost[1]</option>\n"; }
                }
        }
        fclose($nxdnHosts);
	if (file_exists('/usr/local/etc/NXDNHostsLocal.txt')) {
		$nxdnHosts2 = fopen("/usr/local/etc/NXDNHostsLocal.txt", "r");
		while (!feof($nxdnHosts2)) {
               		$nxdnHostsLine2 = fgets($nxdnHosts2);
               		$nxdnHost2 = preg_split('/\s+/', $nxdnHostsLine2);
               		if ((strpos($nxdnHost2[0], '#') === FALSE ) && ($nxdnHost2[0] != '')) {
               	        	if ($testNXDNHost == $nxdnHost2[0]) { echo "      <option value=\"$nxdnHost2[0]\" selected=\"selected\">$nxdnHost2[0] - $nxdnHost2[1]</option>\n"; }
               	        	else { echo "      <option value=\"$nxdnHost2[0]\">$nxdnHost2[0] - $nxdnHost2[1]</option>\n"; }
               		}
		}
	fclose($nxdnHosts2);
	}
?>
</select></td>
</tr>
<?php } ?>

    <?php if (file_exists('/etc/dstar-radio.mmdvmhost') && $configysf2p25['Enabled']['Enabled'] == 1) { ?>
    <tr>
      <td align="left"><a class="tooltip2" href="#">(YSF2P25) <?php echo $lang['dmr_id'];?>:<span><b>DMR ID</b>Enter your CCS7 / DMR ID here</span></a></td>
      <td align="left" colspan="2"><input type="text" name="ysf2p25Id" size="13" maxlength="7" value="<?php if (isset($configysf2p25['P25 Network']['Id'])) { echo $configysf2p25['P25 Network']['Id']; } ?>" /></td>
    </tr>
    <tr>
      <td align="left"><a class="tooltip2" href="#"><?php echo $lang['p25_startup_host'];?>:<span><b>P25 Host</b>Set your prefered P25 Host here</span></a></td>
      <td colspan="2" style="text-align: left;"><select name="ysf2p25StartupDstId">
<?php
	$p25Hosts = fopen("/usr/local/etc/P25Hosts.txt", "r");
	if (isset($configysf2p25['P25 Network']['StartupDstId'])) {
		$testP25Host = $configysf2p25['P25 Network']['StartupDstId'];
	} else {
		$testP25Host = "";
	}
	if ($testP25Host == "") { echo "      <option value=\"none\" selected=\"selected\">None</option>\n"; }
        else { echo "      <option value=\"none\">None</option>\n"; }
	if ($testP25Host == "10") { echo "      <option value=\"10\" selected=\"selected\">10 - Parrot</option>\n"; }
        else { echo "      <option value=\"10\">10 - Parrot</option>\n"; }
        while (!feof($p25Hosts)) {
                $p25HostsLine = fgets($p25Hosts);
                $p25Host = preg_split('/\s+/', $p25HostsLine);
                if ((strpos($p25Host[0], '#') === FALSE ) && ($p25Host[0] != '')) {
                        if ($testP25Host == $p25Host[0]) { echo "      <option value=\"$p25Host[0]\" selected=\"selected\">$p25Host[0] - $p25Host[1]</option>\n"; }
                        else { echo "      <option value=\"$p25Host[0]\">$p25Host[0] - $p25Host[1]</option>\n"; }
                }
        }
        fclose($p25Hosts);
        if (file_exists('/usr/local/etc/P25HostsLocal.txt')) {
		$p25Hosts2 = fopen("/usr/local/etc/P25HostsLocal.txt", "r");
		while (!feof($p25Hosts2)) {
                	$p25HostsLine2 = fgets($p25Hosts2);
                	$p25Host2 = preg_split('/\s+/', $p25HostsLine2);
                	if ((strpos($p25Host2[0], '#') === FALSE ) && ($p25Host2[0] != '')) {
                        	if ($testP25Host == $p25Host2[0]) { echo "      <option value=\"$p25Host2[0]\" selected=\"selected\">$p25Host2[0] - $p25Host2[1]</option>\n"; }
                        	else { echo "      <option value=\"$p25Host2[0]\">$p25Host2[0] - $p25Host2[1]</option>\n"; }
                	}
		}
		fclose($p25Hosts2);
	}
        ?>
    </select></td>
    </tr>  
<?php } ?>

</table>
	<div><input type="button" value="<?php echo $lang['apply'];?>" onclick="submitform()" /><br /><br /></div>
    <?php } ?>

	
<?php if (file_exists('/etc/dstar-radio.mmdvmhost') && $configmmdvm['P25 Network']['Enable'] == 1) {
$p25Hosts = fopen("/usr/local/etc/P25Hosts.txt", "r");
	?>
	<h2><?php echo $lang['p25_config'];?></h2>
    <table>
    <tr>
    <th width="200"><a class="tooltip" href="#"><?php echo $lang['setting'];?><span><b>Setting</b></span></a></th>
    <th colspan="2"><a class="tooltip" href="#"><?php echo $lang['value'];?><span><b>Value</b>The current value from the<br />configuration files</span></a></th>
    </tr>
    <tr>
    <td align="left"><a class="tooltip2" href="#"><?php echo $lang['p25_startup_host'];?>:<span><b>P25 Host</b>Set your prefered P25 Host here</span></a></td>
    <td style="text-align: left;"><select name="p25StartupHost">
<?php
	if (isset($configp25gateway['Network']['Startup'])) { $testP25Host = $configp25gateway['Network']['Startup']; } else { $testP25Host = "none"; }
	if ($testP25Host == "") { echo "      <option value=\"none\" selected=\"selected\">None</option>\n"; }
        else { echo "      <option value=\"none\">None</option>\n"; }
	if ($testP25Host == "10") { echo "      <option value=\"10\" selected=\"selected\">10 - Parrot</option>\n"; }
        else { echo "      <option value=\"10\">10 - Parrot</option>\n"; }
        while (!feof($p25Hosts)) {
                $p25HostsLine = fgets($p25Hosts);
                $p25Host = preg_split('/\s+/', $p25HostsLine);
                if ((strpos($p25Host[0], '#') === FALSE ) && ($p25Host[0] != '')) {
                        if ($testP25Host == $p25Host[0]) { echo "      <option value=\"$p25Host[0]\" selected=\"selected\">$p25Host[0] - $p25Host[1]</option>\n"; }
                        else { echo "      <option value=\"$p25Host[0]\">$p25Host[0] - $p25Host[1]</option>\n"; }
                }
        }
        fclose($p25Hosts);
        if (file_exists('/usr/local/etc/P25HostsLocal.txt')) {
		$p25Hosts2 = fopen("/usr/local/etc/P25HostsLocal.txt", "r");
		while (!feof($p25Hosts2)) {
                	$p25HostsLine2 = fgets($p25Hosts2);
                	$p25Host2 = preg_split('/\s+/', $p25HostsLine2);
                	if ((strpos($p25Host2[0], '#') === FALSE ) && ($p25Host2[0] != '')) {
                        	if ($testP25Host == $p25Host2[0]) { echo "      <option value=\"$p25Host2[0]\" selected=\"selected\">$p25Host2[0] - $p25Host2[1]</option>\n"; }
                        	else { echo "      <option value=\"$p25Host2[0]\">$p25Host2[0] - $p25Host2[1]</option>\n"; }
                	}
		}
		fclose($p25Hosts2);
	}
        ?>
    </select></td>
    </tr>
<?php if ($configmmdvm['P25']['NAC']) { ?>
    <tr>
    <td align="left"><a class="tooltip2" href="#"><?php echo $lang['p25_nac'];?>:<span><b>P25 NAC</b>Set your NAC code here</span></a></td>
    <td align="left"><input type="text" name="p25nac" size="13" maxlength="3" value="<?php echo $configmmdvm['P25']['NAC'];?>" /></td>
    </tr>
<?php } ?>
    </table>
	<div><input type="button" value="<?php echo $lang['apply'];?>" onclick="submitform()" /><br /><br /></div>
<?php } ?>
	
<?php if (file_exists('/etc/dstar-radio.mmdvmhost') && ($configmmdvm['NXDN Network']['Enable'] == 1 || $configdmr2nxdn['Enabled']['Enabled'] == 1) ) { ?>
	<h2><?php echo $lang['nxdn_config'];?></h2>
    <table>
      <tr>
        <th width="200"><a class="tooltip" href="#"><?php echo $lang['setting'];?><span><b>Setting</b></span></a></th>
        <th colspan="2"><a class="tooltip" href="#"><?php echo $lang['value'];?><span><b>Value</b>The current value from the<br />configuration files</span></a></th>
      </tr>
      <tr>
        <td align="left"><a class="tooltip2" href="#"><?php echo $lang['nxdn_startup_host'];?>:<span><b>NXDN Host</b>Set your prefered NXDN Host here</span></a></td>
        <td style="text-align: left;"><select name="nxdnStartupHost">
<?php
	if (file_exists('/etc/nxdngateway')) {
		$nxdnHosts = fopen("/usr/local/etc/NXDNHosts.txt", "r");
		if (isset($confignxdngateway['Network']['Startup'])) { $testNXDNHost = $confignxdngateway['Network']['Startup']; } else { $testNXDNHost = ""; }
		if ($testNXDNHost == "") { echo "      <option value=\"none\" selected=\"selected\">None</option>\n"; }
	        else { echo "      <option value=\"none\">None</option>\n"; }
		if ($testNXDNHost == "10") { echo "      <option value=\"10\" selected=\"selected\">10 - Parrot</option>\n"; }
	        else { echo "      <option value=\"10\">10 - Parrot</option>\n"; }
	        while (!feof($nxdnHosts)) {
	                $nxdnHostsLine = fgets($nxdnHosts);
	                $nxdnHost = preg_split('/\s+/', $nxdnHostsLine);
	                if ((strpos($nxdnHost[0], '#') === FALSE ) && ($nxdnHost[0] != '')) {
	                        if ($testNXDNHost == $nxdnHost[0]) { echo "      <option value=\"$nxdnHost[0]\" selected=\"selected\">$nxdnHost[0] - $nxdnHost[1]</option>\n"; }
	                        else { echo "      <option value=\"$nxdnHost[0]\">$nxdnHost[0] - $nxdnHost[1]</option>\n"; }
	                }
	        }
	        fclose($nxdnHosts);
		if (file_exists('/usr/local/etc/NXDNHostsLocal.txt')) {
			$nxdnHosts2 = fopen("/usr/local/etc/NXDNHostsLocal.txt", "r");
			while (!feof($nxdnHosts2)) {
                		$nxdnHostsLine2 = fgets($nxdnHosts2);
                		$nxdnHost2 = preg_split('/\s+/', $nxdnHostsLine2);
                		if ((strpos($nxdnHost2[0], '#') === FALSE ) && ($nxdnHost2[0] != '')) {
                	        	if ($testNXDNHost == $nxdnHost2[0]) { echo "      <option value=\"$nxdnHost2[0]\" selected=\"selected\">$nxdnHost2[0] - $nxdnHost2[1]</option>\n"; }
                	        	else { echo "      <option value=\"$nxdnHost2[0]\">$nxdnHost2[0] - $nxdnHost2[1]</option>\n"; }
                		}
			}
		fclose($nxdnHosts2);
		}
	} else {
		echo '<option value="176.9.1.168">D2FET Test Host - 176.9.1.168</option>'."\n";
	}
?>
        </select></td>
      </tr>
    <?php if ($configmmdvm['NXDN']['RAN']) { ?>
      <tr>
        <td align="left"><a class="tooltip2" href="#"><?php echo $lang['nxdn_ran'];?>:<span><b>NXDN RAN</b>Set your RAN code here, sane values are 1-64</span></a></td>
        <td align="left"><input type="text" name="nxdnran" size="13" maxlength="2" value="<?php echo $configmmdvm['NXDN']['RAN'];?>" /></td>
      </tr>
    <?php } ?>
    </table>
	<div><input type="button" value="<?php echo $lang['apply'];?>" onclick="submitform()" /><br /><br /></div>
<?php } ?>



<!-- GPSd -->
<?php if ( $configmmdvm['GPSD']['Enable'] == 1 ) { ?>
	<h2><?php echo $lang['gpsd_config'];?></h2>
    <table>
      <tr>
        <th width="200"><a class="tooltip" href="#"><?php echo $lang['setting'];?><span><b>Setting</b></span></a></th>
        <th colspan="2"><a class="tooltip" href="#"><?php echo $lang['value'];?><span><b>Value</b>The current value from the<br />configuration files</span></a></th>
      </tr>
      <tr>
        <td align="left"><a class="tooltip2" href="#"><?php echo $lang['gpsd_port'];?>:<span><b>GPSd Server Port</b>Define the GPSd server port here</span></a></td>
        <td align="left"><input type="text" name="gpsdPort" size="13" maxlength="8" value="<?php echo $configmmdvm['GPSD']['Port'];?>" /></td>
      </tr>
      <tr>
        <td align="left"><a class="tooltip2" href="#"><?php echo $lang['gpsd_address'];?>:<span><b>GPSd Server Address</b>Set the GPSd server address here</span></a></td>
        <td align="left"><input type="text" name="gpsdAddress" size="13" maxlength="128" value="<?php echo $configmmdvm['GPSD']['Address'];?>" /></td>
      </tr>
    </table>
	<div><input type="button" value="<?php echo $lang['apply'];?>" onclick="submitform()" /><br /><br /></div>
<?php } ?>	

<!-- GPSd -->

<?php if ( $configmmdvm['POCSAG']['Enable'] == 1 ) { ?>
	<h2><?php echo $lang['pocsag_config'];?></h2>
    <table>
      <tr>
        <th width="200"><a class="tooltip" href="#"><?php echo $lang['setting'];?><span><b>Setting</b></span></a></th>
        <th colspan="2"><a class="tooltip" href="#"><?php echo $lang['value'];?><span><b>Value</b>The current value from the<br />configuration files</span></a></th>
      </tr>
      <tr>
        <td align="left"><a class="tooltip2" href="#">POCSAG Server:<span><b>POCSAG Server</b>Set the POCSAG Network here</span></a></td>
	<td style="text-align: left;"><select name="pocsagServer">
        	<option value="<?php echo $configdapnetgw['DAPNET']['Address'];?>" selected="selected"><?php echo $configdapnetgw['DAPNET']['Address'];?></option>
		<option value="dapnet.afu.rwth-aachen.de">dapnet.afu.rwth-aachen.de</option>
		<option value="dapnet.db0sda.ampr.org">dapnet.db0sda.ampr.org (HAMNET)</option>
		<option value="node1.dapnet-italia.it">node1.dapnet-italia.it</option>
		</select></td>
      </tr>
      <tr>
        <td align="left"><a class="tooltip2" href="#">POCSAG <?php echo $lang['node_call'];?>:<span><b>POCSAG Callsign</b>Set your paging callsign here</span></a></td>
        <td align="left"><input type="text" name="pocsagCallsign" size="13" maxlength="12" value="<?php echo $configdapnetgw['General']['Callsign'];?>" /></td>
      </tr>
      <tr>
        <td align="left"><a class="tooltip2" href="#">POCSAG <?php echo $lang['radio_freq'];?>:<span><b>POCSAG Frequency</b>Set your paging frequency here</span></a></td>
        <td align="left"><input type="text" id="pocsagFrequency" onkeyup="checkFrequency(); return false;" name="pocsagFrequency" size="13" maxlength="12" value="<?php echo number_format($configmmdvm['POCSAG']['Frequency'], 0, '.', '.');?>" /></td>
      </tr>
      <tr>
        <td align="left"><a class="tooltip2" href="#">DAPNET AuthKey:<span><b>DAPNET AuthKey</b>Set your DAPNET AuthKey here</span></a></td>
        <td align="left"><input type="password" name="pocsagAuthKey" size="30" maxlength="50" value="<?php echo $configdapnetgw['DAPNET']['AuthKey'];?>" /></td>
      </tr>
      <tr>
        <td align="left"><a class="tooltip2" href="#">POCSAG Whitelist:<span><b>POCSAG Whitelist</b>Set your POCSAG RIC Whitelist here, if these are set ONLY these RICs will be transmitted. List is comma seperated.</span></a></td>
        <td align="left"><input type="text" name="pocsagWhitelist" size="60" maxlength="350" value="<?php if (isset($configdapnetgw['General']['WhiteList'])) { echo $configdapnetgw['General']['WhiteList']; } ?>" /></td>
      </tr>
      <tr>
        <td align="left"><a class="tooltip2" href="#">POCSAG Blacklist:<span><b>POCSAG Blacklist</b>Set your POCSAG RIC Blacklist here, if these are set any other RIC will be transmitted, but not these. List is comma seperated.</span></a></td>
        <td align="left"><input type="text" name="pocsagBlacklist" size="60" maxlength="350" value="<?php if (isset($configdapnetgw['General']['BlackList'])) { echo $configdapnetgw['General']['BlackList']; } ?>" /></td>
      </tr>
      <tr>
        <td align="left"><a class="tooltip2" href="#">DAPNET API Username:<span><b>POCSAG API Username</b>Set your POCSAG API Username here</span></a></td>
        <td align="left"><input type="text" name="dapnetAPIUser" size="13" maxlength="12" value="<?php if (isset($configdapnetapi['DAPNETAPI']['USER'])) { echo $configdapnetapi['DAPNETAPI']['USER']; } ?>" /></td>
      </tr>
      <tr>
        <td align="left"><a class="tooltip2" href="#">DAPNET API Password:<span><b>DAPNET API Password</b>Set your DAPNET API password here</span></a></td>
        <td align="left"><input type="password" name="dapnetAPIPass" size="30" maxlength="50" value="<?php if (isset($configdapnetapi['DAPNETAPI']['PASS'])) { echo $configdapnetapi['DAPNETAPI']['PASS']; } ?>" /></td>
      </tr>
      <tr>
        <td align="left"><a class="tooltip2" href="#">DAPNET API Trx Group:<span><b>POCSAG API Transmitter Group</b>Set the desired transmitter group here</span></a></td>
        <td align="left"><input type="text" name="dapnetAPITrxGroup" size="13" maxlength="30" value="<?php if (isset($configdapnetapi['DAPNETAPI']['TRXAREA'])) { echo $configdapnetapi['DAPNETAPI']['TRXAREA']; } ?>" /></td>
      </tr>
      <tr>
        <td align="left"><a class="tooltip2" href="#">DAPNET API RIC:<span><b>POCSAG API RIC</b>Set the RIC that is considered as personnal message destination</span></a></td>
        <td align="left"><input type="text" name="dapnetAPIRic" size="13" maxlength="7" value="<?php if (isset($configdapnetapi['DAPNETAPI']['MY_RIC'])) { echo $configdapnetapi['DAPNETAPI']['MY_RIC']; } ?>" /></td>
      </tr>
    </table>
	<div><input type="button" value="<?php echo $lang['apply'];?>" onclick="submitform()" /><br /><br /></div>
<?php } ?>	


	<h2><?php echo $lang['fw_config'];?></h2>
    <table>
    <tr>
    <th width="200"><a class="tooltip" href="#"><?php echo $lang['setting'];?><span><b>Setting</b></span></a></th>
    <th colspan="2"><a class="tooltip" href="#"><?php echo $lang['value'];?><span><b>Value</b>The current value from the<br />configuration files</span></a></th>
    </tr>
    <tr>
    <td align="left"><a class="tooltip2" href="#"><?php echo $lang['fw_dash'];?>:<span><b>Dashboard Access</b>Do you want the dashboard access to be publicly available? This modifies the uPNP firewall configuration.</span></a></td>
    <?php
	$testPrvPubDash = exec('sudo sed -n 32p /usr/local/sbin/pistar-upnp.service | cut -c 1');
	if (substr($testPrvPubDash, 0, 1) === '#') {
		echo "   <td align=\"left\" colspan=\"2\"><input type=\"radio\" name=\"dashAccess\" value=\"PRV\" checked=\"checked\" />Private <input type=\"radio\" name=\"dashAccess\" value=\"PUB\" />Public</td>\n";
		}
	else {
		echo "   <td align=\"left\" colspan=\"2\"><input type=\"radio\" name=\"dashAccess\" value=\"PRV\" />Private <input type=\"radio\" name=\"dashAccess\" value=\"PUB\" checked=\"checked\" />Public</td>\n";
	}
    ?>
    </tr>
    <tr>
    <td align="left"><a class="tooltip2" href="#"><?php echo $lang['fw_irc'];?>:<span><b>ircDDBGateway Remote</b>Do you want the ircDDBGateway remote control access to be publicly available? This modifies the uPNP firewall Configuration.</span></a></td>
    <?php
	$testPrvPubIRC = exec('sudo sed -n 33p /usr/local/sbin/pistar-upnp.service | cut -c 1');
	if (substr($testPrvPubIRC, 0, 1) === '#') {
		echo "   <td align=\"left\" colspan=\"2\"><input type=\"radio\" name=\"ircRCAccess\" value=\"PRV\" checked=\"checked\" />Private <input type=\"radio\" name=\"ircRCAccess\" value=\"PUB\" />Public</td>\n";
		}
	else {
		echo "   <td align=\"left\" colspan=\"2\"><input type=\"radio\" name=\"ircRCAccess\" value=\"PRV\" />Private <input type=\"radio\" name=\"ircRCAccess\" value=\"PUB\" checked=\"checked\" />Public</td>\n";
	}
    ?>
    </tr>
    <tr>
    <td align="left"><a class="tooltip2" href="#"><?php echo $lang['fw_ssh'];?>:<span><b>SSH Access</b>Do you want access to be publicly available over SSH (used for support issues)? This modifies the uPNP firewall Configuration.</span></a></td>
    <?php
	$testPrvPubSSH = exec('sudo sed -n 31p /usr/local/sbin/pistar-upnp.service | cut -c 1');
	if (substr($testPrvPubSSH, 0, 1) === '#') {
		echo "   <td align=\"left\" colspan=\"2\"><input type=\"radio\" name=\"sshAccess\" value=\"PRV\" checked=\"checked\" />Private <input type=\"radio\" name=\"sshAccess\" value=\"PUB\" />Public</td>\n";
		}
	else {
		echo "   <td align=\"left\" colspan=\"2\"><input type=\"radio\" name=\"sshAccess\" value=\"PRV\" />Private <input type=\"radio\" name=\"sshAccess\" value=\"PUB\" checked=\"checked\" />Public</td>\n";
	}
    ?>
    </tr>
    <?php if (file_exists('/etc/default/hostapd')) { ?>
    <tr>
      <td align="left"><a class="tooltip2" href="#">Auto AP:<span><b>Auto AP</b>Do you want your Pi to create its own AP if it cannot connect to WiFi within 120 secs of boot</span></a></td>
      <?php
        if (file_exists('/etc/hostap.off')) {
	  echo "   <td align=\"left\"><input type=\"radio\" name=\"autoAP\" value=\"ON\" />On <input type=\"radio\" name=\"autoAP\" value=\"OFF\" checked=\"checked\" />Off</td>\n";
	}
        else {
	  echo "   <td align=\"left\"><input type=\"radio\" name=\"autoAP\" value=\"ON\" checked=\"checked\" />On <input type=\"radio\" name=\"autoAP\" value=\"OFF\" />Off</td>\n";
	}
      ?>
      <td>Note: Reboot Required if changed</td>
    </tr>
    <?php } ?>
    <tr>
      <td align="left"><a class="tooltip2" href="#">uPNP:<span><b>uPNP</b>Do you want your Pi to create its own Firewall rules for use with D-Star.</span></a></td>
      <?php
        $testupnp = exec('grep "pistar-upnp.service" /etc/crontab | cut -c 1');
	if (substr($testupnp, 0, 1) === '#') {
	  echo "   <td align=\"left\" colspan=\"2\"><input type=\"radio\" name=\"uPNP\" value=\"ON\" />On <input type=\"radio\" name=\"uPNP\" value=\"OFF\" checked=\"checked\" />Off</td>\n";
	}
        else {
	  echo "   <td align=\"left\" colspan=\"2\"><input type=\"radio\" name=\"uPNP\" value=\"ON\" checked=\"checked\" />On <input type=\"radio\" name=\"uPNP\" value=\"OFF\" />Off</td>\n";
	}
      ?>
    </tr>
    </table>
	<div><input type="button" value="<?php echo $lang['apply'];?>" onclick="submitform()" /></div>
    </form>

<?php
//	exec('ifconfig wlan0',$return);
//	exec('iwconfig wlan0',$return);
//	$strWlan0 = implode(" ",$return);
//	$strWlan0 = preg_replace('/\s\s+/', ' ', $strWlan0);
//	if (strpos($strWlan0,'HWaddr') !== false) {
//		preg_match('/HWaddr ([0-9a-f:]+)/i',$strWlan0,$result);
//	}
//	elseif (strpos($strWlan0,'ether') !== false) {
//		preg_match('/ether ([0-9a-f:]+)/i',$strWlan0,$result);
//	}
//	$strHWAddress = $result['1'];
//
//	if ( isset($strHWAddress) ) {
	if ( file_exists('/sys/class/net/wlan0') || file_exists('/sys/class/net/wlan1') || file_exists('/sys/class/net/wlan0_ap') ) {
echo '
<br />
    <h2>'.$lang['wifi_config'].'</h2>
    <table><tr><td>
    <iframe frameborder="0" scrolling="auto" name="wifi" src="wifi.php?page=wlan0_info" width="100%" onload="javascript:resizeIframe(this);">If you can see this message, your browser does not support iFrames, however if you would like to see the content please click <a href="wifi.php?page=wlan0_info">here</a>.</iframe>
    </td></tr></table>'; } ?>

<br />
	<h2><?php echo $lang['remote_access_pw'];?></h2>
    <form id="adminPassForm" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
    <table>
    <tr><th width="200"><?php echo $lang['user'];?></th><th colspan="3"><?php echo $lang['password'];?></th></tr>
    <tr>
    <td align="left"><b>pi-star</b></td>
    <td align="left"><label for="pass1">Password:</label><input type="password" name="adminPassword" id="pass1" onkeyup="checkPass(); return false;" size="15" />
    <label for="pass2">Confirm Password:</label><input type="password" name="adminPassword" id="pass2" onkeyup="checkPass(); return false;" />
    <br /><span id="confirmMessage" class="confirmMessage"></span></td>
    <td align="right"><input type="hidden" name="adminPasswordUpdate" value="1" /><input type="button" id="submitpwd" value="<?php echo $lang['set_password'];?>" onclick="submitPassform()" disabled="disabled" /></td>
    </tr>
    <tr><td colspan="3"><b>WARNING:</b> This changes the password for this admin page<br />AND the "pi-star" SSH account</td></tr>
    </table>
    </form>
<?php endif; ?>
<br />
</div>
<div class="footer">
Pi-Star web config, &copy; Andy Taylor (MW0MWZ) 2014-<?php echo date("Y"); ?>.<br />
Need help? Click <a style="color: #ffffff;" href="https://www.facebook.com/groups/pistarusergroup/" target="_new">here for the Support Group</a><br />
or Click <a style="color: #ffffff;" href="https://forum.pistar.uk/" target="_new">here to join the Support Forum</a><br />
</div>
</div>
</body>
</html>

<?php } else { ?>
<br />
<br />
</div>
<div class="footer">
Pi-Star web config, &copy; Andy Taylor (MW0MWZ) 2014-<?php echo date("Y"); ?>.<br />
Need help? Click <a style="color: #ffffff;" href="https://www.facebook.com/groups/pistarusergroup/" target="_new">here for the Support Group</a><br />
or Click <a style="color: #ffffff;" href="https://forum.pistar.uk/" target="_new">here to join the Support Forum</a><br />
</div>
</div>
</body>
</html>
<?php } ?>
