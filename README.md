**BIG FAT WARNING**
===
This, and other required Pi-Star modules, are my personal forks, **DO NOT** report bugs, request features or anything like that, to the official **Pi-Star** developer, **Andy Taylor** (MW0MWZ), on the Pi-Star's Facebook page or on the forum.

***

**How to install it**

* You need a working Pi-Star, from [here](http://www.pistar.uk/downloads/). Pi-Star 3 and 4-RC are supported.

* **MAKE A BACKUP OF YOUR Pi-Star CONFIGURATION**
* Connect you Pi-Star
>ssh pi-star@pi-star.local
* Grab the script that permits you to toggle between official repositories and my personnal ones.
> sudo su
> rpi-rw
> cd
> wget http://tinyurl.com/f1rmb-pistar
> chmod +x f1rmb-pistar
* Now you can execute this script with some arguments. For a complete list,  use '-h' or '--help'
The easiest way:
	* To install these fork repositories:
	> ./f1rmb-pistar -ia
	* To switch back to the official ones:
	> ./f1rmb-pistar -ra



1. Once you have installed this fork, you need to go in menu "**Configuration**" -> "**Expert**" -> "**Tools**" and select "**CSS Tool**" 
This will reset all the colors to their default value.
2. You will need to launch "pistar-upgrade" multiple times, until it displays the "**You are already running the latest version..**" message.

***

** What features this fork offers**

* An enhanced POCSAG support. A service and network status indicators are there, You can send pages from the **Admin** web page. When you receive personnal pages, they are extracted from the **Activity** and displayed in a dedicated table. You can send a page to multiple callsigns and/or transmitter groups, separated with comma:
![POCSAG](images/Dapnet_Messenger.png  "POCSAG")

* A way to change the (*default*) YSF reflector on the fly, without restarting all the services:
![Menus](images/Admin.png  "Menus")

* A new menu system, cleaner, nicer:
![Expert Menus](images/Expert_Menus.png  "Expert Menus")

* An easy and extended way to change the color theme (using the farbtastic plugin):
![Farbtastic Color Picker](images/CSS_ColorPicker.png  "Farbtastic Color Picker")
![Gray Colors](images/Color2.png  "Gray Colors")
![Orange Colors](images/Color3.png  "Orange Colors")

* Gateway and DAPNet Activity (*last heard*) tables are extended to the 40 last entries, fitted in a scrolling window.

* Mobile GPS support.

* Lot of small modifications and tweaks that can't be enumerated here.
