<?php

/*
 * Author: doug@neverfear.org
 */

require("Dijkstra.php");

function runTest()
{
    $g = new Graph();

    $g->generateRandom();

    list($from, $to) = $g->randomNodes();

    list($distances, $prev) = $g->paths_from($from);

    echo "path between $from and $to" . PHP_EOL;

    $path = $g->paths_to($prev, $to);

    print_r($path);

    $g->toDotFile($path);

}

runTest();
