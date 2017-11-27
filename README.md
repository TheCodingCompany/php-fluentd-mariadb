# FluentD Exec_Filter PHP Plugin to MariaDB

Use this plugin to send data with the exec_filter plugin from FluentD (TCP) to MariaDB.

#TD-agent configuration example
td-agent config example

```
## built-in TCP input
## @see http://docs.fluentd.org/articles/in_forward
## @see https://docs.fluentd.org/v0.12/articles/out_exec_filter
<source>
  @type forward
  @label @PHP_FILE
  port 2017
  bind 127.0.0.1
  @log_level debug
</source>
<label @PHP_FILE>
    <match php_out>
        #@type stdout
        @type exec_filter
        command /bin/php /usr/local/example.php 2>&1
        in_format json
        out_format json
        flush_interval 1s
        tag php_out
        @log_level debug
    </match>
</label>
```

#PHP Code (Example)

```
<?php
require_once("theCodingCompany/MariaDBStore.php");

$settings = array(
    "cdn"       => "mysql:host=127.0.0.1;port=3306;dbname=event_log;",
    "username"  => "root",
    "password"  => "MySecretPassword"
);

/*
* Example data: {"attributes":{"voornaam":"Victor","achternaam":"Angelier"}} that is forwarded from FluentD to STDIN
*/
$fields = array("attributes");

$maria = new theCodingCompany\MariaDBStore($settings);
$maria->setTable("event_log")
    ->setFields($fields)
    ->start();
```

The final data is stored in MariaDB, table: event_log, database: event_log, and value: {"voornaam":"Victor","achternaam":"Angelier"}