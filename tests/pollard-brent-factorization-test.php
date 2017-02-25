<?php

require '../lib/danog/PrimeModule.php';
$factorization = new danog\PrimeModule();
$res = $factorization->primefactors('1278426847636566097');
print_r($res);
