<?php

/*
 * Author: doug@neverfear.org
 */

require("Dijkstra.php");

function runTest() {
	$g = new Graph();

	$from = "";
	$to = "";
	$g->generateRandom($from, $to);

	list($distances, $prev) = $g->paths_from($from);

	$path = $g->paths_to($prev, $to);

	print_r($path);

	$g->toDotFile($path);

}

runTest();
