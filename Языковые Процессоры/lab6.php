<?php
new Search;

class Search
{
    private $visited = [],
    $graph = array(
        0 => array(9, 1),
        1 => array(0, 2, 3),
        2 => array(1, 5),
        3 => array(1, 4),
        4 => array(3, 6),
        5 => array(2, 8),
        6 => array(8),
        7 => array(8),
        8 => array(5, 6, 7, 9),
        9 => array(0, 8));

    function __construct()
    {
        $start = 3;
        $finish = 7;

        $this->dfs($this->graph, $start, $finish);

        echo 'starting from: ' . $start . ' & finishing at: ' . $finish . '<br>all vertexes: ' . implode(", ", $this->visited) . '<br>';
        echo 'the path: ' . implode(', ', $this->bfs_path($this->graph, $start, $finish));
    }

    private function bfs_path($graph, $start, $end)
    {
        $queue = new SplQueue();
        $queue->enqueue([$start]);
        $visited = [$start];

        while ($queue->count() > 0) {
            $path = $queue->dequeue();
            $node = $path[sizeof($path) - 1];

            if ($node === $end) {
                return $path;
            }

            foreach ($graph[$node] as $neighbour) {
                if (!in_array($neighbour, $visited)) {
                    $visited[] = $neighbour;

                    $new_path = $path;
                    $new_path[] = $neighbour;
                    $queue->enqueue($new_path);
                }
            };
        }
        return false;
    }

    private function dfs($graph, $startNode, $finish)
    {
        $this->visited[] = $startNode;

        foreach ($graph[$startNode] as $index => $vertex) {
            if (!in_array($vertex, $this->visited)) {
                $this->dfs($graph, $vertex, $finish);
            }
        }
    }
}


