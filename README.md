# statibus

Minimalistic Statuspage with 30,60s interval Ping, Port & HTTP(S) IPv4 & IPv6 Monitoring.

![Overview](https://i.imgur.com/5ynE6Oo.png)


**Key Points**<br />
- [rqlite](https://github.com/rqlite/rqlite) database
- PHP 7.3+ (sqlite3,bcmath,curl)
- Handmade css, no framework, about 2kb
- Zero Javascript

**ToDo**<br />
- external checks

## QuickSetup:

1. Get a rqlite instance up and running
2. Rename configs/config.example.php to configs/config.php, you may edit it
2. To Initialize the databse run:
```
php cli.php init
```
4. You can add the first service by running:
```
php cli.php group add Servers
php cli.php service add Servers Server ping 8.8.8.8
```
5. Enable the cronjobs, see => configs/cron|uptime.example<br />
Run cron every 60s, uptime is for generating the uptime percentages, every 5 minutes is fine

**You can access the databse anytime via ./rqlite in case the commands are not enough.**

## Updating
SQL Migrations: https://github.com/Ne00n/statibus/tree/main/migrations

## CLI
**service**<br />
- add
```
#php cli.php service add <group> <name> <method> <target> <timeout> <httpcode(s)> <keyword>
```
Examples:
```
php cli.php service add Servers Server ping 8.8.8.8
php cli.php service add Servers Service port 8.8.8.8:80 2
php cli.php service add Servers Website http https://website.com 2 200
php cli.php service add Servers Website http https://website.com 2 400,404
php cli.php service add Servers Keyword http https://keyword.com 2 200 clusterfuck
```
- other
```
php cli.php service list
php cli.php service delete <name>
```
**group**<br />
```
php cli.php group add test
php cli.php group list
php cli.php group delete test
```
