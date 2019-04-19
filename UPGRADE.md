# Upgrading from v1.12 to v2.0

## Stop using `mail` transport for mailer

As of `v6.0` SwiftMailer dropped support for `mail` transport,
so `Crunz` `v2.0` won't support it either,
please use `smtp` or `sendmail` transport.

# Upgrading from v1.11 to v1.12

## Always return `\Crunz\Schedule` from task files

Example of wrong task file:

```php
<?php

return [];
```

Example of correct task file:
```php
<?php

use Crunz\Schedule;

$scheduler = new Schedule();

$scheduler
    ->run('php -v')
    ->description('PHP version')
    ->everyMinute();

// Crunz\Schedule instance returned
return $scheduler;
```

## Stop using `\Crunz\Event::setProcess`

If you, for some reason, use above method you should stop it.
This method was intended to be `private` and will be in `v2.0`,
which will lead to exception if you call it.

Example of wrong usage

```php
<?php

use Crunz\Schedule;

$process = new \Symfony\Component\Process\Process('php -i');
$scheduler = new Schedule();
$task = $scheduler->run('php -v');
$task
    // setProcess is deprecated
    ->setProcess($process)
    ->description('PHP version')
    ->everyMinute()
;

return $scheduler;
``` 

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
