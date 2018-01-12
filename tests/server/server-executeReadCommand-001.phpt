--TEST--
MongoDB\Driver\Server::executeReadCommand()
--SKIPIF--
<?php require __DIR__ . "/../utils/basic-skipif.inc"; ?>
<?php NEEDS('STANDALONE'); CLEANUP(STANDALONE); NEEDS_ATLEAST_MONGODB_VERSION(STANDALONE, "3.6"); NEEDS_STORAGE_ENGINE(STANDALONE, "wiredTiger"); ?>
--FILE--
<?php
require_once __DIR__ . "/../utils/basic.inc";
require_once __DIR__ . "/../utils/observer.php";

$manager = new MongoDB\Driver\Manager(STANDALONE);
$server = $manager->selectServer(new MongoDB\Driver\ReadPreference(MongoDB\Driver\ReadPreference::RP_SECONDARY));

(new CommandObserver)->observe(
    function() use ($server) {
        $command = new MongoDB\Driver\Command( [
            'aggregate' => NS,
            'pipeline' => [],
            'cursor' => new stdClass(),
        ] );
        $server->executeReadCommand(
            DATABASE_NAME,
            $command,
            [
                'readPreference' => new \MongoDB\Driver\ReadPreference(\MongoDB\Driver\ReadPreference::RP_SECONDARY),
                'readConcern' => new \MongoDB\Driver\ReadConcern(\MongoDB\Driver\ReadConcern::MAJORITY),
            ]
        );
    },
    function(stdClass $command) {
        echo "Read Preference: ", $command->{'$readPreference'}->mode, "\n";
        echo "Read Concern: ", $command->readConcern->level, "\n";
    }
);


?>
===DONE===
<?php exit(0); ?>
--EXPECTF--
Read Preference: secondary
Read Concern: majority
===DONE===
