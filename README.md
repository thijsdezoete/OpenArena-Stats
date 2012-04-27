# OpenArena statistics
***
The scripts in this repository can be used to serve a web interface for browsing statistics of your OpenArena server running on Linux or Mac OS.
  
## Setup
In order for these scripts to be able to parse the OpenArena logfiles some setup is required.  
  
Make sure the following directory exists and is writeable for the user under which you run the OpenArena server:  
`/var/log/openarena`  
  
Start your Linux server using the following command:  
`nohup openarena-server +set dedicated 1 +set net_port [SERVER_PORT] +set net_ip [SERVER_IP] +exec [CONFIG_FILE] | awk '{printf("%s|%s\n", systime(), $0) >> ("/var/log/openarena/openarena_" strftime("%Y-%m-%d") ".log")}'`  
  
and/or your Mac OS server using the following command:  
`nohup openarena-server +set dedicated 1 +set net_port [SERVER_PORT] +set net_ip [SERVER_IP] +exec [CONFIG_FILE] | gawk '{printf("%s|%s\n", systime(), $0) >> ("/var/log/openarena/openarena_" strftime("%Y-%m-%d") ".log")}'`  
  
This enables logfiles to be written per date and all lines to be prepended with a timestamp. Be sure to replace the values contained within `[` and `]` with their correct values.  
  
If, for whatever reason, you're storing logfiles in a different location, be sure to adjust the path in the `stats.php` file and the command used to start the server.