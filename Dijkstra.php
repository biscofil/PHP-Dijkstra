<?php
/*
 * Author: doug@neverfear.org
 */

require_once("PriorityQueue.php");

class Edge {

	public $start;
	public $end;
	public $weight;

	public function __construct($start, $end, $weight) {
		$this->start = $start;
		$this->end = $end;
		$this->weight = $weight;
	}
}

class Graph {

	public $nodes = array();

	public function generateRandom(&$p_from, &$p_to){
			$fromi = NULL;

			for($a = 0; $a < rand(30,100);$a++){

				$from = 97+rand(0,24);
				if(is_null($fromi)){
					$fromi = $from;
				}

				$to = 97+rand(0,24);

				if($from == $to){
					$to++;
				}

				$to = chr($to);
				$from = chr($from);
				if(!$this->hasEdge($from, $to)){

					$this->addedge($from, $to, rand(1,100));

				}

			}

			$p_from = chr($fromi);
			$p_to = $to;
	}

	public function toDotFile($path){
		$myfile = fopen("graph.dot", "w") or die("Unable to open file!");
			$out = 'digraph graphname {'.PHP_EOL;
			foreach($this->nodes as $key => $start){
					foreach($this->nodes[$key] as $edge){
							$out .= $edge->start. ' -> '. $edge->end.'[label="'.$edge->weight.'"';
							$key_a = array_search( $edge->start, $path);
							if(($key_a !== false) && array_key_exists($key_a+1,$path)){
								if($path[$key_a+1] == $edge->end){
									$out .= "color=red,penwidth=3.0"; //",style=bold,color=red";
								}
							}
							$out .= '];'.PHP_EOL;
					}
			}
 	 		$out .= '}'.PHP_EOL;
			fwrite($myfile, $out);
			fclose($myfile);
	}

	public function hasEdge($start, $end){
		if(array_key_exists($start,$this->nodes)){
			foreach($this->nodes[$start] as $edge){
				if($edge->end == $end){
					return true;
				}
			}
		}
		return false;
	}

	public function addedge($start, $end, $weight = 0) {
		if (!isset($this->nodes[$start])) {
			$this->nodes[$start] = array();
		}
		array_push($this->nodes[$start], new Edge($start, $end, $weight));
	}

    public function removenode($index) {
		array_splice($this->nodes, $index, 1);
	}


	public function paths_from($from) {
		$dist = array();
		$dist[$from] = 0;

		$visited = array();

		$previous = array();

		$queue = array();
		$Q = new PriorityQueue("compareWeights");
		$Q->add(array($dist[$from], $from));

		$nodes = $this->nodes;

		while($Q->size() > 0) {
			list($distance, $u) = $Q->remove();

			if (isset($visited[$u])) {
				continue;
			}
			$visited[$u] = True;

			if (!isset($nodes[$u])) {
				print "WARNING: '$u' is not found in the node list\n";
			}

			foreach($nodes[$u] as $edge) {

				$alt = $dist[$u] + $edge->weight;
				$end = $edge->end;
				if (!isset($dist[$end]) || $alt < $dist[$end]) {
					$previous[$end] = $u;
					$dist[$end] = $alt;
					$Q->add(array($dist[$end], $end));
				}
			}
		}
		return array($dist, $previous);
	}

	public function paths_to($node_dsts, $tonode) {
		// unwind the previous nodes for the specific destination node

		$current = $tonode;
		$path = array();

		if (isset($node_dsts[$current])) { // only add if there is a path to node
			array_push($path, $tonode);
		}
		while(isset($node_dsts[$current])) {
			$nextnode = $node_dsts[$current];

			array_push($path, $nextnode);

			$current = $nextnode;
		}

		return array_reverse($path);

	}

	public function getpath($from, $to) {
		list($distances, $prev) = $this->paths_from($from);
		return $this->paths_to($prev, $to);
	}

}

function compareWeights($a, $b) {
	return $a->data[0] - $b->data[0];
}
