<?php
/**
 * Intellectual Property of Svensk Coding Company AB - Sweden All rights reserved.
 * 
 * @copyright (c) 2016, Svensk Coding Company AB
 * @author V.A. (Victor) Angelier <victor@thecodingcompany.se>
 * @version 1.0
 * @license http://www.apache.org/licenses/GPL-compatibility.html GPL
 * 
 */

require_once("theCodingCompany/MariaDBStore.php");

$settings = array(
    "cdn"       => "mysql:host=127.0.0.1;port=3306;dbname=event_log;",
    "username"  => "root",
    "password"  => "SuperSecretPassword"
);

$fields = array("attributes");

$maria = new Fluentd\MariaDBStore($settings);
$maria->setTable("event_log")
    ->setFields($fields)
    ->start();