<?php
// Load the language support
require_once('../config/language.php');
//Load the Pi-Star Release file
$pistarReleaseConfig = '/etc/pistar-release';
$configPistarRelease = array();
$configPistarRelease = parse_ini_file($pistarReleaseConfig, true);
//Load the Version Info
require_once('../config/version.php');
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
	  "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xmlns:v="urn:schemas-microsoft-com:vml" lang="en">
    <head>
	<meta name="robots" content="index" />
	<meta name="robots" content="follow" />
	<meta name="language" content="English" />
	<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
	<meta name="Author" content="Andrew Taylor (MW0MWZ)" />
	<meta name="Description" content="Pi-Star Expert Editor" />
	<meta name="KeyWords" content="Pi-Star" />
	<meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate" />
	<meta http-equiv="pragma" content="no-cache" />
	<link rel="shortcut icon" href="images/favicon.ico" type="image/x-icon">
	<meta http-equiv="Expires" content="0" />
	<title>Pi-Star - Digital Voice Dashboard - Expert Editor</title>
	<script type="text/javascript" src="/jquery.min.js"></script>
	<script type="text/javascript" src="/css/farbtastic/farbtastic.min.js"></script>
	<link rel="stylesheet" type="text/css" href="/css/farbtastic/farbtastic.css" />
	<link rel="stylesheet" type="text/css" href="/css/font-awesome-4.7.0/css/font-awesome.min.css" />
	<link rel="stylesheet" type="text/css" href="/css/pistar-css.php" />
	<style type="text/css" media="screen">
	 .colorwell {
	     border: 2px solid #fff;
	     width: 6em;
	     text-align: center;
	     cursor: pointer;
	 }
	 body .colorwell-selected {
	     border: 2px solid #000;
	     font-weight: bold;
	 }
	</style>
	<script type="text/javascript">
	 function factoryReset()
	 {
	     if (confirm('WARNING: This will set these settings back to factory defaults.\n\nAre you SURE you want to do this?\n\nPress OK to restore the factory configuration\nPress Cancel to go back.')) {
		 document.getElementById("factoryReset").submit();
	     } else {
		 return false;
	     }
	 }

	 $(document).ready(function() {
	     var f = $.farbtastic('#colorpicker');
	     var p = $('#colorpicker').css('opacity', 1).hide();
	     var selected;
	     $('.colorwell')
	         .each(function () { f.linkTo(this); $(this).css('opacity', 1); })
	         .focus(function() {
		     if (selected) {
			 $(selected).removeClass('colorwell-selected');
		     }
		     p.show();
		     f.linkTo(this);
		     $(selected = this).addClass('colorwell-selected');
		 })
		 .blur(function() {
		     if (selected) {
			 $(selected).removeClass('colorwell-selected');
			 seleted = null;
			 p.hide();
			 f.linkTo(function(){});
		     }
		 });
	 });
	 
	</script>
    </head>
    <body>
	<div class="container">
	    <?php include './header-menu.inc'; ?>
	    <div class="contentwide">
		
		<?php
		if (!file_exists('/etc/pistar-css.ini')) {
		    //The source file does not exist, lets create it....
		    $outFile = fopen("/tmp/bW1kd4jg6b3N0DQo.tmp", "w") or die("Unable to open file!"); //#bf0707
		    $fileContent = "[Background]\nPageColor=#edf0f5\nContentColor=#ffffff\nBannersColor=#dd4b39\nNavbarColor=#242d31\nNavbarHoverColor=#a60000\nDropdownColor=#f9f9f9\nDropdownHoverColor=#d0d0d0\n\n";
		    $fileContent .= "[Text]\nBannersColor=#ffffff\nBannersDropColor=#303030\nNavbarColor=#ffffff\nNavbarHoverColor=#ffffff\nDropdownColor=#000000\n\n";
		    $fileContent .= "[Tables]\nHeadDropColor=#8b0000\nBgEvenColor=#f7f7f7\nBgOddColor=#d0d0d0\n\n";
		    $fileContent .= "[Content]\nTextColor=#000000\n\n";
		    $fileContent .= "[BannerH2]\nEnabled=0\nText=Some Text\n\n";
		    $fileContent .= "[BannerExtText]\nEnabled=0\nText=Some long text entry\n";
		    fwrite($outFile, $fileContent);
		    fclose($outFile);
		    
		    // Put the file back where it should be
		    exec('sudo mount -o remount,rw /');                             // Make rootfs writable
		    exec('sudo cp /tmp/bW1kd4jg6b3N0DQo.tmp /etc/pistar-css.ini');  // Move the file back
		    exec('sudo chmod 644 /etc/pistar-css.ini');                     // Set the correct runtime permissions
		    exec('sudo chown root:root /etc/pistar-css.ini');               // Set the owner
		    exec('sudo mount -o remount,ro /');                             // Make rootfs read-only
		}
		
		//Do some file wrangling...
		exec('sudo cp /etc/pistar-css.ini /tmp/bW1kd4jg6b3N0DQo.tmp');
		exec('sudo chown www-data:www-data /tmp/bW1kd4jg6b3N0DQo.tmp');
		exec('sudo chmod 664 /tmp/bW1kd4jg6b3N0DQo.tmp');
		
		//ini file to open
		$filepath = '/tmp/bW1kd4jg6b3N0DQo.tmp';
		
		//after the form submit
		if($_POST) {
		    $data = $_POST;
		    // Factory Reset Handler Here
		    if (empty($_POST['factoryReset']) != TRUE ) {
			echo "<br />\n";
			echo "<table>\n";
			echo "<tr><th>Factory Reset Config</th></tr>\n";
			echo "<tr><td>Loading fresh configuration file(s)...</td><tr>\n";
			echo "</table>\n";
			unset($_POST);
			//Reset the config
			exec('sudo mount -o remount,rw /');                             // Make rootfs writable
			exec('sudo rm -rf /etc/pistar-css.ini');                        // Delete the Config
			exec('sudo mount -o remount,ro /');                             // Make rootfs read-only
			echo '<script type="text/javascript">setTimeout(function() { window.location=window.location;},0);</script>';
			die();
		    } else {
			//update ini file, call function
			update_ini_file($data, $filepath);
		    }
		}

		function endsWith($haystack, $needle)
		{
		    $length = strlen($needle);
		    
		    if ($length == 0) {
			return true;
		    }
		    
		    return (strcasecmp(substr($haystack, -$length), $needle) == 0);
		}
		
		//this is the function going to update your ini file
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
			    if ($value == '') {
				$content .= $key."=none\n";
			    }
			    else {
				$content .= $key."=".$value."\n";
			    }
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
		    exec('sudo mount -o remount,rw /');                             // Make rootfs writable
		    exec('sudo cp /tmp/bW1kd4jg6b3N0DQo.tmp /etc/pistar-css.ini');  // Move the file back
		    exec('sudo chmod 644 /etc/pistar-css.ini');                     // Set the correct runtime permissions
		    exec('sudo chown root:root /etc/pistar-css.ini');               // Set the owner
		    exec('sudo mount -o remount,ro /');                             // Make rootfs read-only
		    
		    return $success;
		}
		
		//parse the ini file using default parse_ini_file() PHP function
		$parsed_ini = parse_ini_file($filepath, true);
		
		echo '<form action="" method="post">'."\n";

		// Colorpicker
		echo '<div style="width: 72%; position: fixed; pointer-events: none;" >'."\n";
		echo '<div id="colorpicker" style="float: right; margin: 10px; pointer-events: auto;"></div>'."\n";
		echo '</div>'."\n";
		
		foreach($parsed_ini as $section=>$values) {
		    // keep the section as hidden text so we can update once the form submitted
		    echo "<input type=\"hidden\" value=\"$section\" name=\"$section\" />\n";
		    echo "<table>\n";
		    echo "<tr><th colspan=\"2\">$section</th></tr>\n";
		    // print all other values as input fields, so can edit. 
		    // note the name='' attribute it has both section and key
		    foreach($values as $key=>$value) {
			if (endsWith($key, 'Color')) {
			    echo "<tr><td align=\"right\" width=\"30%\">$key</td><td align=\"left\"><input type=\"text\" class=\"colorwell\" name=\"{$section}[$key]\" value=\"$value\" /></td></tr>\n";
			}
			else {
			    echo "<tr><td align=\"right\" width=\"30%\">$key</td><td align=\"left\"><input type=\"text\" name=\"{$section}[$key]\" value=\"$value\" /></td></tr>\n";
			    }
		    }
		    echo "</table>\n";
		    echo '<input type="submit" value="'.$lang['apply'].'" />'."\n";
		    echo "<br />\n";
		}
		echo "</form>";
		echo "<br /><br />\n";
		echo 'if you took it all too far and now it makes you feel sick, click below to reset.'."\n";
		echo '<form id="factoryReset" action="" method="post">'."\n";
		echo '  <div><input type="hidden" name="factoryReset" value="1" /></div>'."\n";
		echo '</form>'."\n";
		echo '<input type="button" onclick="javascript:factoryReset();" value="'.$lang['factory_reset'].'" />'."\n";
		?>
	    </div>
	    
	    <div class="footer">
		Pi-Star / Pi-Star Dashboard, &copy; Andy Taylor (MW0MWZ) 2014-<?php echo date("Y"); ?>.<br />
		ircDDBGateway Dashboard by Hans-J. Barthen (DL5DI),<br />
		MMDVMDash developed by Kim Huebel (DG9VH), <br />
		Need help? Click <a style="color: #ffffff;" href="https://www.facebook.com/groups/pistarusergroup/" target="_new">here for the Support Group</a><br />
		Get your copy of Pi-Star from <a style="color: #ffffff;" href="http://www.pistar.uk/downloads/" target="_new">here</a>.<br />
	    </div>
	    
	</div>
    </body>
</html>
