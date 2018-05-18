<?php
/*
 * Author: doug@neverfear.org
 */

require_once("PriorityQueue.php");

class Edge
{

    public $start;
    public $end;
    public $weight;

    public function __construct($start, $end, $weight)
    {
        $this->start = $start;
        $this->end = $end;
        $this->weight = $weight;
    }
}

class Graph
{

    public $nodes = array();

    /**
     * @param int $nodesCount
     * @param int $edgeCount
     */
    public function generateRandom($nodesCount = 10, $edgeCount = 50)
    {

        $nodes = range(0, $nodesCount);

        $nodes = array_map(function($int){
            return chr(97 + $int);
        },$nodes);

        for ($a = 0; $a < $edgeCount; $a++) {

            list($from,$to) = array_rand($nodes,2);

            $from = $nodes[$from];
            $to = $nodes[$to];

            if (!$this->hasEdge($from, $to)) {
                $this->addedge($from, $to, rand(1, 100));
            }

        }
    }

    /**
     * Saves the graph to a .dot file
     * @param $path
     */
    public function toDotFile($path)
    {
        $myfile = fopen("graph.dot", "w") or die("Unable to open file!");
        $out = 'digraph graphname {' . PHP_EOL;
        foreach ($this->nodes as $key => $start) {
            foreach ($this->nodes[$key] as $edge) {
                $out .= $edge->start . ' -> ' . $edge->end . '[label="' . $edge->weight . '"';
                $key_a = array_search($edge->start, $path);
                if (($key_a !== false) && array_key_exists($key_a + 1, $path)) {
                    if ($path[$key_a + 1] == $edge->end) {
                        $out .= "color=red,penwidth=3.0";
                    }
                }
                $out .= '];' . PHP_EOL;
            }
        }
        $out .= '}' . PHP_EOL;
        fwrite($myfile, $out);
        fclose($myfile);
    }

    /**
     * Returns TRUE if there is an edge bethween $from and $to
     * @param $start
     * @param $end
     * @return bool
     */
    public function hasEdge($start, $end)
    {
        if (array_key_exists($start, $this->nodes)) {
            foreach ($this->nodes[$start] as $edge) {
                if ($edge->end == $end) {
                    return true;
                }
            }
        }
        return false;
    }

    /**
     * Returns the list of the names of the nodes
     * @return mixed
     */
    public function randomNodes()
    {
        return array_rand($this->allNodes(), 2);
    }

    /**
     * @param $start
     * @param $end
     * @param int $weight
     */
    public function addedge($start, $end, $weight = 0)
    {
        if (!isset($this->nodes[$start])) {
            $this->nodes[$start] = array();
        }
        array_push($this->nodes[$start], new Edge($start, $end, $weight));
    }

    /**
     * @param $index
     */
    public function removenode($index)
    {
        array_splice($this->nodes, $index, 1);
    }

    /**
     * @return array
     */
    public function allNodes()
    {
        $nodes = array_combine(array_keys($this->nodes), array_keys($this->nodes));
        foreach ($this->nodes as $key => $start) {
            foreach ($this->nodes[$key] as $edge) {
                if (!array_key_exists($edge->end, $nodes)) {
                    $nodes[$edge->end] = $edge->end;
                }
            }
        }
        return $nodes;
    }

    /**
     * @param $from
     * @return array
     */
    public function paths_from($from)
    {
        $dist = array();
        $dist[$from] = 0;

        $visited = array();

        $previous = array();

        $queue = array();
        $Q = new PriorityQueue("compareWeights");
        $Q->add(array($dist[$from], $from));

        $nodes = $this->nodes;

        while ($Q->size() > 0) {
            list($distance, $u) = $Q->remove();

            if (isset($visited[$u])) {
                continue;
            }
            $visited[$u] = True;

            if (!isset($nodes[$u])) {
                print "WARNING: '$u' is not found in the node list\n";
            }

            foreach ($nodes[$u] as $edge) {

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

    /**
     * @param $node_dsts
     * @param $tonode
     * @return array
     */
    public function paths_to($node_dsts, $tonode)
    {
        // unwind the previous nodes for the specific destination node

        $current = $tonode;
        $path = array();

        if (isset($node_dsts[$current])) { // only add if there is a path to node
            array_push($path, $tonode);
        }
        while (isset($node_dsts[$current])) {
            $nextnode = $node_dsts[$current];

            array_push($path, $nextnode);

            $current = $nextnode;
        }

        return array_reverse($path);

    }

    /**
     * @param $from
     * @param $to
     * @return array
     */
    public function getpath($from, $to)
    {
        list($distances, $prev) = $this->paths_from($from);
        return $this->paths_to($prev, $to);
    }

}

function compareWeights($a, $b)
{
    return $a->data[0] - $b->data[0];
}
