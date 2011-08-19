<?php
chdir(dirname(__FILE__));
require_once('cloudfiles.php');

$conf = parse_ini_file("manifest-test.ini", true);

$user  = $conf['global']['user'];
$key   = $conf['global']['key'];
$realm = UK_AUTHURL;
$container = "test";

$auth = new CF_Authentication(
    $user,
    $key,
    NULL,
    $realm
);
$auth->authenticate();

$conn = new CF_Connection($auth);
$conn->setDebug(1);

$c = $conn->create_container($container);

$o = $c->create_object("test/data");
$o->write("foo", 3);

$o = $c->create_object("test/link");
$o->headers  = array('Content-Disposition' => 'attachment');
$o->manifest = $container . "/test/data";
$o->content_type = "test/plain";
$o->write(".", 1);

echo("-------------------------\n" . $o->public_uri() . "\n");

