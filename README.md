# statibus

Minimalistic Statuspage with 60s interval Ping, Port & HTTP(S) IPv4 & IPv6 Monitoring.

![Overview](https://i.imgur.com/MhTiDTg.png)


**Key Points**<br />
- [rqlite](https://github.com/rqlite/rqlite) database
- PHP 7.3+ (sqlite3,bcmath,curl)
- Handmade css, no framework, about 2kb
- Zero Javascript

**ToDo**<br />
- external checks

QuickSetup:

1. Get a rqlite instance up and running
2. Rename configs/config.example.php to configs/config.php, you may edit it
2. To Initialize the databse run:
```
php cli.php init
```
4. You can add the first service by running:
```
php cli.php add Server ping 8.8.8.8
```
5. Enable the cronjobs, see => configs/cron|uptime.example<br />
Run cron every 60s, uptime is for generating the uptime percentages, every 5 minutes is fine

You can access the databse anytime via rest api or ./rqlite

**CLI**<br />
- add
```
#php cli.php add <name> <method> <target> <timeout> <httpcode(s)>
```
Examples:
```
php cli.php add Server ping 8.8.8.8
php cli.php add Service port 8.8.8.8:80 2
php cli.php add Website http https://website.com 2 200
php cli.php add Website http https://website.com 2 400,404
```
- other
```
php cli.php list
php cli.php delete <id>
```
