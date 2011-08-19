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

$c = $conn->create_container(
    $conf['test']['container']
);
$c->make_public();

$o = $c->create_object($conf['test']['data']);
$o->headers  = array(
    'Content-Disposition' => 'inline',
);
$o->metadata = array(
    'Test' => 'data',
);
$o->write("foo\n", 4);

$o = $c->create_object($conf['test']['link']);
$o->headers  = array(
    'Content-Disposition' => 'attachment',
);
$o->metadata = array(
    'Test' => 'link',
);
$o->manifest = $container . "/" . $conf['test']['data'];
$o->content_type = "text/plain";
$o->write(".", 1);

$o = $c->create_object($conf['test']['data']);
$o = $c->create_object($conf['test']['link']);

echo("\n-------------------------\n\n\n" .
     $o->public_uri() .
    "\n\n\n-------------------------\n"
);

