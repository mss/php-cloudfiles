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

$gzip = $conf['test']['gzip'];
$data = "foo\n";
if ($gzip)
    $data = gzip($data);

$o = $c->create_object($conf['test']['data']);
$o->headers  = array(
    'Content-Disposition' => 'inline',
    'Content-Encoding'    => $gzip ? "gzip" : "identity",
);
$o->metadata = array(
    'Data' => 'test',
);
$o->write($data, strlen($data));

$o = $c->create_object($conf['test']['link']);
$o->headers  = array(
    'Content-Disposition' => 'attachment',
    'Content-Encoding'    => $gzip ? "gzip" : "identity",
);
$o->metadata = array(
    'Link' => 'test',
);
$o->manifest = $conf['test']['container'] . "/" . $conf['test']['data'];
$o->content_type = "text/plain";
$o->write(".", 1);

$o = $c->create_object($conf['test']['data']);
$o = $c->create_object($conf['test']['link']);

echo("\n-------------------------\n\n\n" .
     $o->public_uri() .
    "\n\n\n-------------------------\n"
);
exit;

function gzip($data) {
    $pipe = array();
    $gzip = proc_open(implode(" ", array(
                "gzip",
                "--best",
                "--to-stdout",
                "--force",
                "--no-name",
                "--quiet"
            )), array(
                array("pipe", "r"),
                array("pipe", "w")
            ), $pipe
        );
    if ($gzip === false) {
        header("Status: 503");
        header("X-Error: popen");
        echo("gzip error\n");
        exit(1);
    }
    fwrite($pipe[0], $data);
    fclose($pipe[0]);
    $data = stream_get_contents($pipe[1]);
    fclose($pipe[1]);
    proc_close($gzip);
    return $data;
}