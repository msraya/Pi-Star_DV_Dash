<div class="header">
    <div style="font-size: 8px; text-align: right; padding-right: 8px;">Pi-Star:<?php echo $configPistarRelease['Pi-Star']['Version']?> / Dashboard:<?php echo $version; ?></div>
    <h1>Pi-Star Digital Voice - Expert Editors</h1>
    <p>
	<div class="navbar">
	    <a href="/admin/configure.php"><?php echo $lang['configuration'];?></a>
	    <a href="/admin/config_backup.php"><?php echo $lang['backup_restore'];?></a>
	    <a href="/admin/expert/upgrade.php">Upgrade</a>
	    <a href="/admin/update.php"><?php echo $lang['update'];?></a>
	    <a href="/admin/"><?php echo $lang['admin'];?></a>
	    <a href="/"><?php echo $lang['dashboard'];?></a>
	</div>
	<div class="lnavbar">
	    <div class="dropdown">
		<button class="dropbutton">Quick Editors
		    <i class="fa fa-caret-down"></i>
		    <div class="dropdown-content">
			<a href="edit_dstarrepeater.php">DStarRepeater</a>
			<a href="edit_ircddbgateway.php">ircDDB Gateway</a>
			<a href="edit_timeserver.php">TimeServer</a>
			<a href="edit_mmdvmhost.php">MMDVMHost</a>
			<a href="edit_dmrgateway.php">DMR Gateway</a>
			<a href="edit_ysfgateway.php">YSF Gateway</a>
			<a href="edit_p25gateway.php">P25 Gateway</a>
			<a href="edit_nxdngateway.php">NXDN Gateway</a>
			<a href="edit_dapnetgateway.php">DAPNet Gateway</a>
			<a href="edit_dapnetapi.php">DAPNet API</a>
			<a href="edit_mobilegps.php">MobileGPS</a>
		    </div>
		</button>		
	    </div>
	    <div class="dropdown">
		<button class="dropbutton">Full Editors
		    <i class="fa fa-caret-down"></i>
		    <div class="dropdown-content">
			<a href="fulledit_dmrgateway.php">DMR Gateway</a>
			<a href="fulledit_pistar-remote.php">PiStar-Remote</a>
			<a href="fulledit_wpaconfig.php">WiFi</a>
			<a href="fulledit_bmapikey.php">Brandmeister API</a>
			<a href="fulledit_dapnetgateway.php">DAPNet Gateway</a>
			<a href="fulledit_dapnetapi.php">DAPNet API</a>
			<a href="fulledit_mobilegps.php">MobileGPS</a>
			<a href="fulledit_cron.php">System Cron</a>
			<a href="fulledit_rssidat.php">RSSI Data</a>
		    </div>
		</button>
	    </div>
	    <div class="dropdown">
		<button class="dropbutton">Tools
		    <i class="fa fa-caret-down"></i>
		    <div class="dropdown-content">
			<a href="edit_dashboard.php">CSS Tool</a>
			<a href="ssh_access.php">SSH Access</a>
		    </div>
		</button>
	    </div>
	</div>
    </p>
</div>
