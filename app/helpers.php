<?php declare(strict_types = 1);

use Symfony\Component\VarDumper\VarDumper;

if (! function_exists('dd')) {
    /**
     * Dump the passed variables and end the script.
     *
     * @param  mixed
     * @return void
     */
    function dd(...$args)
    {
        array_map(function ($x) {
            VarDumper::dump($x);
        }, $args);
        die(1);
    }
}
