<?php

/*
opyright 2016-2017 Daniil Gentili
(https://daniil.it)
This file is part of MadelineProto.
MadelineProto is free software: you can redistribute it and/or modify it under the terms of the GNU Affero General Public License as published by the Free Software Foundation, either version 3 of the License, or (at your option) any later version.
MadelineProto is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY;
without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
See the GNU Affero General Public License for more details.
You should have received a copy of the GNU General Public License along with the MadelineProto.
If not, see <http://www.gnu.org/licenses/>.
*/

namespace danog;

class PrimeModule
{
    // 	Uses https://github.com/LonamiWebs/Telethon/blob/master/telethon/crypto/factorizator.py, thank you so freaking much!
    public static function native_single($what)
    {
        if (!is_int($what)) {
            return false;
        }
        foreach ([2, 3, 5, 7, 11, 13, 17, 19, 23] as $s) {
            if ($what % $s === 0) {
                return $s;
            }
        }
        $g = 0;
        for ($i = 0; $i < 3; $i++) {
            $q = (rand(0, 127) & 15) + 17;
            $x = rand(0, 1000000000) + 1;
            $y = $x;
            $lim = 1 << ($i + 18);
            for ($j = 1; $j <= $lim; $j++) {
                list($a, $b, $c) = [$x, $x, $q];
                while ($b != 0) {
                    if (($b & 1) != 0) {
                        $c += $a;
                        if ($c >= $what) {
                            $c -= $what;
                        }
                    }
                    $a += $a;
                    if ($a >= $what) {
                        $a -= $what;
                    }
                    $b >>= 1;
                }
                $x = $c;
                $z = ($x < $y) ? $y - $x : $x - $y;
                $g = self::gcd($z, $what);
                if ($g != 1) {
                    break;
                }

                if (($j & ($j - 1)) === 0) {
                    $y = $x;
                }
            }
            if ($g > 1) {
                break;
            }
        }
        $p = $what;

        return min($p, $g);
    }

    private static function gcd($a, $b)
    {
        if ($a == $b) {
            return $a;
        }
        while ($b > 0) {
            list($a, $b) = [$b, self::posmod($a, $b)];
        }

        return $a;
    }

    private static function posmod($a, $b)
    {
        $resto = $a % $b;
        if ($resto < 0) {
            $resto += abs($b);
        }

        return $resto;
    }

    public static function python_single($what)
    {
        if (function_exists('shell_exec')) {
            $res = shell_exec('timeout 10 python '.__DIR__.'/prime.py '.$what);
            if ($res == '' || is_null($res)) {
                return false;
            }
            $newval = intval($res);
            if (is_int($newval)) {
                $res = $newval;
            }
            if ($res === 0) {
                return false;
            }

            return $res;
        }

        return false;
    }

    public static function python_single_alt($what)
    {
        if (function_exists('shell_exec')) {
            $res = shell_exec('python '.__DIR__.'/alt_prime.py '.$what);
            if ($res == '' || is_null($res)) {
                return false;
            }
            $newval = intval($res);
            if (is_int($newval)) {
                $res = $newval;
            }
            if ($res === 0) {
                return false;
            }

            return $res;
        }

        return false;
    }

    public static function wolfram_single($what)
    {
        $query = 'Do prime factorization of '.$what;
        $params = [
            'async'         => true,
            'banners'       => 'raw',
            'debuggingdata' => false,
            'format'        => 'moutput',
            'formattimeout' => 8,
            'input'         => $query,
            'output'        => 'JSON',
            'proxycode'     => json_decode(file_get_contents('http://www.wolframalpha.com/api/v1/code'), true)['code'],
        ];
        $url = 'https://www.wolframalpha.com/input/json.jsp?'.http_build_query($params);
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Referer: https://www.wolframalpha.com/input/?i='.urlencode($query)]);
        curl_setopt($ch, CURLOPT_URL, $url);
        $res = json_decode(curl_exec($ch), true);
        curl_close($ch);
        $fres = false;
        if (!isset($res['queryresult']['pods'])) {
            return false;
        }
        foreach ($res['queryresult']['pods'] as $cur) {
            if ($cur['id'] === 'Divisors') {
                $fres = explode(', ', preg_replace(["/{\d+, /", "/, \d+}$/"], '', $cur['subpods'][0]['moutput']));
                break;
            }
        }
        if (is_array($fres)) {
            $fres = $fres[0];

            $newval = intval($fres);
            if (is_int($newval)) {
                $fres = $newval;
            }

            return $fres;
        }

        return false;
    }

    public static function native($what)
    {
        $res = [self::native_single($what)];
        while (array_product($res) !== $what) {
            $res[] = self::native_single($what / array_product($res));
        }

        return $res;
    }

    public static function python($what)
    {
        $res = [self::python_single($what)];
        if ($res[0] === false) {
            return false;
        }
        while (array_product($res) !== $what) {
            $res[] = self::python_single($what / array_product($res));
        }

        return $res;
    }

    public static function python_alt($what)
    {
        $res = [self::python_single_alt($what)];
        if ($res[0] === false) {
            return false;
        }
        while (array_product($res) !== $what) {
            $res[] = self::python_single_alt($what / array_product($res));
        }

        return $res;
    }

    public static function wolfram($what)
    {
        $res = [self::wolfram_single($what)];
        while (array_product($res) !== $what) {
            $res[] = self::wolfram_single($what / array_product($res));
        }

        return $res;
    }

    public static function auto($what)
    {
        $res = self::python_alt($what);
        if (is_array($res)) {
            return $res;
        }
        $res = self::python($what);
        if (is_array($res)) {
            return $res;
        }
        $res = self::native((int) $what);
        if (is_array($res)) {
            return $res;
        }
        $res = self::wolfram($what);
        if (is_array($res)) {
            return $res;
        }

        return false;
    }

    public static function auto_single($what)
    {
        $res = self::python_single_alt($what);
        if ($res !== false) {
            return $res;
        }
        $res = self::python_single($what);
        if ($res !== false) {
            return $res;
        }
        $res = self::native_single((int) $what);
        if ($res !== false) {
            return $res;
        }
        $res = self::wolfram_single($what);
        if ($res !== false) {
            return $res;
        }

        return false;
    }
}
