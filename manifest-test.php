<?php
set_include_path(implode(PATH_SEPARATOR, array(
  './lib',
  get_include_path()
)));
require_once('cloudfiles.php');

$user="";
$key="";
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

