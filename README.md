# Crunz

Install a cron job once and for all, manage the rest right from the code.


![Version](http://img.shields.io/badge/Version-v1.0-ff69b4.svg?style=flat-square)
![Build](http://img.shields.io/travis/lavary/crunz.svg?style=flat-square)

Crunz is a framework-agnostic package to schedule periodic tasks (cron jobs) in PHP using a fluent API.

Crunz is wirtten in PHP but it can execute console commands, shell scripts or PHP CLI scripts.

## Installation

To install the package, run the following command:

```bash
composer require lavary\crunz
```

## Starting the Scheduler

After the package is installed, a PHP CLI script named `crunz` is symlinked to the `vendor/bin` directory. You may create a symlink of this file in `/usr/bin` directory, to have access to it from anywhere.

This is the only cron job you need to install at server level. It runs every minute and delegates the responsibility to the scheduler service. Crunz evaluates the tasks and run those which are due.

The server-level cron job could be like so:

```bash
* * * * * path/to/project/vendor/bin/crunz schedule:run  >> /dev/null 2>&1
``` 

## Usage

To create a task, you need to create a file with a name ending with `Tasks.php`, for instance `GeneralTasks.php`. You can create as many tasks files as you need. You can put all the tasks in one file, or across different files and directories based on their usage. By default the source directory is `Tasks/` directory within your project's root directory. 

You can change the default path by using `source` option, when running `schedule:run`.

Here's an example of a basic task file with one task:

```php
<?php

// /var/www/project/Tasks/adminstrativeTasks.php

use Crunz\Schedule;

$schedule = new Schedule();

$schedule->run('cp project project-bk')       
         ->everyMinute()
         ->description('Copying the project directory')
         ->appendOutputTo('/Users/lavary/www/sammi.log');

// ...

// IMPORTANT: You must return the schedule object

return $schedule; 
  
       
```

> **Important:** Please note that you need to return the `Schedule` instance at the end of each task file.


To run the tasks, you need to make sure Crunz is aware of the tasks' location. As noted earlier, Crunz assumes all the task files reside in `Tasks` directory within your project's root directory.

The scheduler scans the respective directory recursively, collects all the task files ending with `Tasks.php`, and registers them the with `Schedule` class.
 
 If you need to keep your task files in another location other than the default location, you may define the source path using the `--source` option - when installing the master cron:
 
 ```bash
 +* * * * * path/to/project/vendor/bin/crunz schedule:run --source=/path/to/the/Tasks/directory  >> /dev/null 2>&1
```

Here's another example:

```php
<?php

// ...

$schedule->run('./deploy.sh')
         ->in('/home')
         ->weekly()
         ->sundays()
         ->at('12:30')
         ->appendOutputTo('/var/log/backup.log');
         
// ...

// Return the Schedule instance

return $schedule;
```

## Generating Task Files Using the Task Generator

You can use the `crunz` command-line utility, to generate a task file. You can edit the file later if you need.

To create a task named `GeneralTasks.php` which runs every five minutes on weekdays, we run the following command:

```bash
path/to/project/vendor/bin/crunz make:task General --frequency=everyFiveMinutes --constraint=weekdays
```

Crunz creates the file in `Tasks/` directory within your project's root directory.

You can also specify the output destination, using `output` option:

```bash
path/to/project/vendor/bin/crunz make:task general --frequency=everyFiveMinutes --constraint=weekdays --output="path/to/Tasks/directory"
```

Use `--help` option to see the list of all available arguments and options along with their default values:

```bash
path/to/project/vendor/bin/crunz --help
```

To see the list of registered tasks, you can use the `schedule:list` command as below:

```bash
path/to/project/vendor/bin/crunz schedule:list

+---+---------------+-------------+-------------------------+
| # | Task          | Expression  | Command to Run          |
+---+---------------+-------------+-------------------------+
| 1 | Backup DB     | * * * * 1 * | /var/wwww/backup_db.php |
+---+---------------+-------------+-------------------------+
```

This is useful to see, if your tasks has been setup as you expect.

## Scheduling Frequency and Constraints

You can use a wide variety of scheduling frequencies according to your use case:

```php
| Method               | Description                            |
|----------------------|----------------------------------------|
| cron('* * * * * *')  | Run the task on a custom Cron schedule |
| everyMinute()        | Run the task every minute              |
| everyFiveMinutes()   | Run the task every five minutes        |
| everyTenMinutes()    | Run the task every ten minutes         |
| everyThirtyMinutes() | Run the task every thirthy minutes     |
| hourly()             | Run the task every hour                |
| daily()              | Run the task every day at midnight     |
| dailyAt('13:00')     | Run the task every day at 13:00        |
| twiceDaily(1, 13)    | Run the task daily at 1:00 & 13:00     |
| weekly()             | Run the task every week                |
| monthly()            | Run the task every month               |
| quarterly()          | Run the task every quarter             |
| yearly()             | Run the task every year                |
```

In addition to the above methods, you can use **magic** methods to set the tasks' frequency of execution.

Here's the anatomy of a magic method:

```php

every[CamelCaseWordNumber]Minute(s)|Hour(s)|Day(s)|Month(s)|Week(s)

```

Based on the above expression, you can set the frequency using the proper method. For example, all the following methods are valid:

* `everyThirtyFiveHours`
* `everyFiveMonths`
* `everyOneMinute`
* `everyMinute`
* `everyThirtySevenMinutes`
* `everyEightyThreeWeeks`
* `everyFiftyHours`
* `everyTwoHundredDays`
* `everyOneThousandAndEightHundredFiftyFiveMinutes`
* ...

Based on the explained pattern, you can create any combination to set your desired frequency. Sky is the limit!

Alternatively, you may use `every()` method (with proper arguments) to achieve the same result:

```php
<?php

// ...
$schedule->run('./backup.sh')
         ->in('/home')
         ->every('hour', 12);   // Every 12 hours
 
 return $schedule;
 
``` 

The preceding code will execute `backup.sh` file every 12 hours.   
  
Here's another example: 
  
   
```php
<?php
$schedule->run('backup.php')
         ->in('/home')
         ->every('day', 2)   // Every two days
         
return $schedule;

```

The above code will execute `backup.php` every two days.


These methods may be combined with additional constraints to create even more finely tuned schedules that only run on certain days of the week. For example, to schedule a command to run weekly on Monday:

```php
<?php

// ...

$schedule->run('./backup.sh')
  ->weekly()
  ->mondays()
  ->at('13:00');

// ...

return $schedule;

```

Here's the list of constraints you can use with the above frequency methods:

```php
| Constraint    | Description                          |
|---------------|--------------------------------------|
| weekdays()    | Limit the task to weekdays           |
| sundays()     | Limit the task to Sunday             |
| mondays()     | Limit the task to Monday             |
| tuesdays()    | Limit the task to Tuesday            |
| wednesdays()  | Limit the task to Wednesday          |
| thirsdays()   | Limit the task to Thursday           |
| fridays()     | Limit the task to Friday             |
| saturdays()   | Limit the task to Saturday           |
| when(Closure) | Limit the task based on a truth test |
```


You can also use `hour()`, `minute()`, `dayOfMonth()`, `month()` and `dayOfWeek()` methods to set the fields individually. 
You can pass the values as arrays or a list of arguments.

```php
<?php
// ...

// Cron equivalent: 0,15,30,34,40 * * * *
$schedule->run('script.php')
         ->minute(0, 15, 30, 34, 40);  // Run the script at 0, 15, 30, 34, 40 minutes of every hour
         
// Cron equivalent: * 13,14 * * *
$schedule->run('script-2.php')
         ->hour([13,14]);  // Run the script 1 am and 2 pm.      

// Cron equivalent: * 6-18 * * *
$schedule->run('script-3.php')
         ->hour('6-18');  // Run the script from 6 am to 18 pm      
         
// Cron equivalent: * 12,13,15-17 * * *         
$schedule->run('script-4.php')
         ->hour(12, 13, '15-17');  // Run the script at 12 am, 1 pm and from 3 pm until 5 pm.          


// Cron equivalent: 0,15,30,34,40 12,13,15-17 * * *   
$schedule->run('script-4.php')
         ->minute(0, 15, 30, 34, 40)
         ->hour(12, 13, '15-17');

return $schedule;

```

You can also use the `cron` method to set the frequency directly, just like you do in a crontab file:

```php
<?php
// ...

$schedule->run('script.php')
         ->cron('0-30 6-18 5,6,7,9-15 1-6 *');
         
return $schedule;

```

## Schedule a Task to Run Only Once 

You can schedule a task to run only once on a certain date (and or time) using `on()` method:

```php
<?php
// ...
$schedule->run('./backup.sh')
         ->on('14:30 2016-02-21');
// ...
```

Or to set the time only:

```php
<?php
// ...
$schedule->run('./backup.sh')
         ->daily()
         ->at('03:45');
// ...
```

> **Note** The time format can be in any format readble by `strtotime`.

## Wake Up and Sleep Time 

It is possible to set an active duration for a task. Regardless of the frequency, they will be turned off and on at certain times in the day or a period of time.

```php
<?php
$schedule->run('./backup.sh')
         ->everyFiveMinutes()
         ->from('2016-02-25 12:35')
         ->to('2016-02-26 12:35');

```

The above task will be run every five minutes from `2016-02-25 12:35` until `2016-02-26 12:35`.


You can also use the `between()` method to do the same thing:

```php
<?php
$schedule->run('./backup.sh')
         ->everyFiveMinutes()
         ->between('2016-02-25 12:35', '2016-02-26 12:35');

```

Or to turn off a task from 12:30 pm to 15 pm:

```php
<?php
$schedule->run('./backup.sh')
         ->everyFiveMinutes()
         ->from('12:30')
         ->to(15);

```

> **Note** The time format can be in any format readble by `strtotime`.

## Schedule Under Certain Conditions

You can run or skip a task based on a certain condition.

Using `when()` :

```php
<?php

// ...

$schedule->run('./backup.sh')->daily()->when(function () {
    return true;
});

// ...

return $schedule;

```

The callback function must return `TRUE` for the task to be run.

or we can skip a task based on a condition:

```php
<?php

// ...

$schedule->run('./backup.sh')->daily()->skip(function () {
    return false;
});

// ...

return $schedule;

```

The callback function must return `TRUE` for the task to be skipped.

## Prevent Task Overlaps

By default, scheduled tasks will be run even if the previous instance of the task is still running. To prevent this, you may use `withoutOverlapping()` method to avoid task overlaps.

```php
<?php

// ...

$schedule->run('./backup.sh')->withoutOverlapping();

// ...

return $schedule;

```

Crunz keeps the PID of the last running instance of the tasks in seprate file (one file per task), and checks if the process is still running. If it's not, it will run a new instance when it's time.


## Handling Output

You can save the task output to a file:

```php
<?php

// ...

$shcedule->run('./back.sh')
         ->sendOutputTo('/var/log/backups.log');

// ...

return $schedule;

```

or append it:

```php
<?php

// ...

$shcedule->run('./back.sh')
         ->appendOutputTo('/var/log/backups.log');

// ...

return $schedule;

```

## Changing Directories

You can use the `in()` method to change directory before running a command:

```php
<?php

// ...

$schedule->run('./deploy.sh')
         ->in('/home')
         ->weekly()
         ->sundays()
         ->at('12:30')
         ->appendOutputTo('/var/log/backup.log');

// ...

return $schedule;

```

## Hooks

It is possible to call a set of callbacks before and after the command is run:

```php
<?php

// ...

$shcedule->run('./back.sh')
         ->before(function() {
            // Initialization phase
         })
         ->after(function() {
            // Cleanup phase
         });

// ...

return $schedule;

```

A use case would be sending an email after the task is run (using your desired mailer library).

## Ping a URL

To ping a url before and after a task is run:

```php
<?php

// ...

$shcedule->run('./back.sh')
         ->beforePing('uri-to-ping-before')
         ->thenPing('uri-to-ping-after');
// ...

return $schedule;

```

## If You Need Help

Please submit all issues and questions using GitHub issues and I will try to help you.


## License
Crunz is free software distributed under the terms of the MIT license.
