# statibus

Minimalistic Statuspage with 30,60s interval Ping, Port & HTTP(S) IPv4 & IPv6 Monitoring.

![Overview](https://i.imgur.com/5ynE6Oo.png)


**Key Points**<br />
- [rqlite](https://github.com/rqlite/rqlite) database
- PHP 7.3+ (bcmath,curl)
- Handmade css, no framework, about 2kb
- Zero Javascript

**ToDo**<br />
- nothin

## QuickSetup:

1. Get a [rqlite](https://github.com/rqlite/rqlite/releases) instance up and running<br />
Check configs/rqlite.service if you wish to run rqlite as a service.
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
```
php cli.php service add <group> <name> <method> <target> <timeout> <httpcode(s)> <keyword>
```
Examples:
```
php cli.php service add Servers Server ping 8.8.8.8
php cli.php service add Servers Service port 8.8.8.8:80 2
php cli.php service add Servers Website http https://website.com 2 200
php cli.php service add Servers Website http https://website.com 2 400,404
php cli.php service add Servers Keyword http https://keyword.com 2 200 clusterfuck
php cli.php service list
php cli.php service delete <name>
```
**group**<br />
```
php cli.php group add <name>
php cli.php group list
php cli.php group delete <name>
```
**remotes**<br />
```
#url example: https://check.com/check.php you can rename the file of course
php cli.php remote add <name> <url>
php cli.php remote list
php cli.php remote delete <name>
```
