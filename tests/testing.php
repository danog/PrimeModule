#!/usr/bin/env php
<?php
require 'vendor/autoload.php';
require 'StructClass.php';
require 'Struct.php';
require 'StructTools.php';
function get_time($callable, $param)
{
    $a = microtime(true);
    $result = $callable($param);
    $time = microtime(true) - $a;
    if (is_array($result)) {
        $result = json_encode($result);
    }

    return [$time, $result];
}
function test($n)
{
    $init = '| '.str_pad('result', strlen($n), ' ', STR_PAD_RIGHT).' |  type       |         time         |';
    echo '|'.str_pad('', strlen($init) - 2, '-', STR_PAD_RIGHT).'|'.PHP_EOL;

    echo '|'.str_pad('Multiple factorization of '.$n, strlen($init) - 2, ' ', STR_PAD_BOTH).'|'.PHP_EOL;
    echo '|'.str_pad('', strlen($init) - 2, '_', STR_PAD_RIGHT).'|'.PHP_EOL;
    echo $init.PHP_EOL;

    list($time, $result) = get_time(['\danog\PrimeModule', 'native_cpp'], $n, true);
    $GLOBALS['medium']['native_cpp'] += $time;
    echo '| '.str_pad($result, 6, ' ', STR_PAD_RIGHT).' |  native cpp | '.str_pad($time, 20, ' ', STR_PAD_RIGHT).' |'.PHP_EOL;

    list($time, $result) = get_time(['\danog\PrimeModule', 'python_alt'], $n, true);
    $GLOBALS['medium']['python_alt'] += $time;
    echo '| '.str_pad($result, 6, ' ', STR_PAD_RIGHT).' |  python alt | '.str_pad($time, 20, ' ', STR_PAD_RIGHT).' |'.PHP_EOL;

    list($time, $result) = get_time(['\danog\PrimeModule', 'python'], $n);
    $GLOBALS['medium']['python'] += $time;
    /*
    echo '| '.str_pad($result, 6, ' ', STR_PAD_RIGHT).' |  wolfram    | '.str_pad($time, 20, ' ', STR_PAD_RIGHT).' |'.PHP_EOL;
    list($time, $result) = get_time(['\danog\PrimeModule', 'native'], $n);
    $GLOBALS['medium']['native'] += $time;
    */
    echo '| '.str_pad($result, 6, ' ', STR_PAD_RIGHT).' |  python     | '.str_pad($time, 20, ' ', STR_PAD_RIGHT).' |'.PHP_EOL;
    list($time, $result) = get_time(['\danog\PrimeModule', 'wolfram'], $n);
    $GLOBALS['medium']['wolfram'] += $time;
    echo '| '.str_pad($result, 6, ' ', STR_PAD_RIGHT).' |  native     | '.str_pad($time, 20, ' ', STR_PAD_RIGHT).' |'.PHP_EOL;
    //echo '| '.str_pad($n, 6, " ", STR_PAD_RIGHT).' |  auto       | '.str_pad(get_time(['\danog\PrimeModule', 'auto'], $n), 20, " ", STR_PAD_RIGHT).' |'.PHP_EOL;
    echo '|'.str_pad('', strlen($init) - 2, '-', STR_PAD_RIGHT).'|'.PHP_EOL.PHP_EOL;
}
function test_single($n, $messy = false)
{
    $init = '| '.str_pad('result', strlen($n), ' ', STR_PAD_RIGHT).' |  type       |         time         |';
    echo '|'.str_pad('', strlen($init) - 2, '-', STR_PAD_RIGHT).'|'.PHP_EOL;
    echo '|'.str_pad('Single factorization of '.$n, strlen($init) - 2, ' ', STR_PAD_BOTH).'|'.PHP_EOL;
    echo '|'.str_pad('', strlen($init) - 2, '_', STR_PAD_RIGHT).'|'.PHP_EOL;
    echo $init.PHP_EOL;

    list($time, $result) = get_time(['\danog\PrimeModule', 'native_single_cpp'], $n);
    $GLOBALS['medium']['native_cpp'] += $time;
    echo '| '.str_pad($result, strlen($n), ' ', STR_PAD_RIGHT).' |  native cpp | '.str_pad($time, 20, ' ', STR_PAD_RIGHT).' |'.PHP_EOL;

    list($time, $result) = get_time(['\danog\PrimeModule', 'python_single_alt'], $n);
    $GLOBALS['medium']['python_alt'] += $time;
    echo '| '.str_pad($result, strlen($n), ' ', STR_PAD_RIGHT).' |  python alt | '.str_pad($time, 20, ' ', STR_PAD_RIGHT).' |'.PHP_EOL;

    if (!$messy) {
        list($time, $result) = get_time(['\danog\PrimeModule', 'python_single'], $n);
        $GLOBALS['medium']['python'] += $time;
        echo '| '.str_pad($result, strlen($n), ' ', STR_PAD_RIGHT).' |  python     | '.str_pad($time, 20, ' ', STR_PAD_RIGHT).' |'.PHP_EOL;
    }

    list($time, $result) = get_time(['\danog\PrimeModule', 'native_single'], $n);
    $GLOBALS['medium']['native'] += $time;
    echo '| '.str_pad($result, strlen($n), ' ', STR_PAD_RIGHT).' |  native     | '.str_pad($time, 20, ' ', STR_PAD_RIGHT).' |'.PHP_EOL;
    list($time, $result) = get_time(['\danog\PrimeModule', 'wolfram_single'], $n);
    $GLOBALS['medium']['wolfram'] += $time;
    echo '| '.str_pad($result, strlen($n), ' ', STR_PAD_RIGHT).' |  wolfram    | '.str_pad($time, 20, ' ', STR_PAD_RIGHT).' |'.PHP_EOL;

    echo '|'.str_pad('', strlen($init) - 2, '-', STR_PAD_RIGHT).'|'.PHP_EOL.PHP_EOL;
}
function random_string($length)
{
    if (function_exists('random_bytes')) {
        return random_bytes($length);
    }
    $s = '';
    for ($x = 0; $x < $length; $x++) {
        $s .= chr(rand(0, 255));
    }

    return $s;
}
function gen_payload()
{
    return chr(10).str_repeat(chr(0), 8).\danog\PHP\Struct::pack('<q', (int) (time() << 32)).pack('VV', 20, 1615239032).random_string(16);
}

echo PHP_EOL.'----------- HUGE SEMIPRIME TESTS (100 semiprimes) ----------'.PHP_EOL;
$GLOBALS['medium'] = ['python' => 0, 'python_alt' => 0, 'wolfram' => 0, 'native' => 0, 'native_cpp' => 0];
$tg = fsockopen('tcp://149.154.167.51:443');
fwrite($tg, chr(239));
stream_set_timeout($tg, 1);
$tot = 100;
for ($x = 0; $x < $tot; $x++) {
    fwrite($tg, gen_payload());
    $number = \danog\PHP\Struct::unpack('>q', substr(stream_get_contents($tg, 85), 58, 8))[0];
    test_single($number);
}
fclose($tg);

foreach ($medium as $type => $total) {
    echo $type.': total time '.$total.', medium time '.($total / $tot).PHP_EOL;
}

echo PHP_EOL.'------------------- SMALL MULTIPLE FACTOR TESTS -------------------'.PHP_EOL;
$GLOBALS['medium'] = ['python' => 0, 'python_alt' => 0, 'wolfram' => 0, 'native' => 0, 'native_cpp' => 0];

foreach ([200, 327, 35, 13589] as $multiple) {
    test($multiple);
}

foreach ($medium as $type => $total) {
    echo $type.': total time '.$total.', medium time '.($total / 4).PHP_EOL;
}

echo PHP_EOL.'------------------- HUGE SEMIPRIME TESTS (MESSY) ------------------'.PHP_EOL;
$GLOBALS['medium'] = ['python' => 0, 'python_alt' => 0, 'wolfram' => 0, 'native' => 0];
$m = [1724114033281923457, 2189285106422392999, 3510535493073971807, 1756377470921216651, 1767867620107504387, 2149465210997855797];
foreach ($m as $messy) {
    test_single($messy, true);
}
foreach ($medium as $type => $total) {
    echo $type.': total time '.$total.', medium time '.($total / count($m)).PHP_EOL;
}
