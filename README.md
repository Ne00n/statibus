# statibus

Status Page with 60s Ping, Port & HTTP(S) Monitoring.

![Overview](https://i.imgur.com/MhTiDTg.png)

- [rqlite](https://github.com/rqlite/rqlite) database
- PHP 7.3+ (sqlite3,bcmath,curl)
- Handmade css, no framework, about 2kb
- Zero Javascript

**ToDo**<br />
- external checks

QuickSetup:

Beforehand make sure you have a working mailserver running otherwise you need to enable the accounts by hand.<br />
Also you need curl + mtr installed on all machines.

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
5. Enable the cronjobs, see => configs/cron|uptime.example
Run cron every 60s, uptime is for generating the uptime percentages, every 5 minutes is fine

You can access the databse anytime via rest api or ./rqlite
