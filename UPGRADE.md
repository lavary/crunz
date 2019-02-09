# Upgrading from v1.10 to v1.11

## Run `Crunz` in directory with your `crunz.yml`

Searching for Crunz's config is now related to `cwd`, not to `vendor/bin/crunz`.

For example, if your `crunz.yml` is in `/var/www/project/crunz.yml`, then run Crunz with `cd` first:
```bash
cd /var/www/project && vendor/bin/crunz schedule:list
```

Cron job also should be changed:
```bash
* * * * * cd /var/www/project && vendor/bin/crunz schedule:run
```

# Upgrading from v1.9 to v1.10

### Do not pass more than five parts to `Crunz\Event::cron()`

Example correct call:
```yaml
$event = new Crunz\Event;
$event->cron('0 * * * *');
```

# Upgrading from v1.7 to v1.8

### Add `timezone` to your `crunz.yml`

Example config file:
```yaml
source: tasks
suffix: Tasks.php
timezone: Europe/Warsaw
```
