<?php

include __DIR__.'/../vendor/autoload.php';

$collection = new \Warkhosh\Component\Collection\Collection([1,2,3]);

var_dump($collection->toArray());
