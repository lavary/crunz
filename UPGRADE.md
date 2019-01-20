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
