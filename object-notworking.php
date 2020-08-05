<?php

class Map
{
    protected $map;

    public function __construct(array $map)
    {
        $this->setMap($map);
    }

    public function getNumberOfRows():int
    {
        return count($this->map);
    }

    public function getNumberOfColumns():int
    {
        return count($this->map[0]);
    }

    public function hasPoint(Point $point):bool
    {
        if ($point->getColumn() < 0 || $point->getColumn() >= $this->getNumberOfColumns()) {
            return false;
        }
        if ($point->getRow() < 0 || $point->getRow() >= $this->getNumberOfRows()) {
            return false;
        }

        return true;
    }

    public function isAllowed(Point $point):bool
    {
        if (!$this->hasPoint($point)) {
            throw new Exception("Point is not in the map");
        }

        return $this->map[$point->getRow()][$point->getColumn()] !== 0;
    }

    private function setMap(array $map)
    {
        $this->map = $map;
        $nbColumns = 0;
        foreach ($this->map as $i => $row) {
            if (!is_array($row)) {
                throw new Exception("Invalid map: row $i is not an array");
            }
            if ($nbColumns == 0) {
                if (count($row) < 1) {
                    throw new Exception("Invalid map: row must contain a column");
                }
                $nbColumns = count($row);
            }
            if ($nbColumns != count($row)) {
                throw new Exception("Invalid map: row $i has a different length");
            }
        }
    }

    public function getMap():array
    {
        return $this->map;
    }
}

class Point
{
    protected $column;
    protected $row;

    public function __construct($row, $column)
    {
        $this->column = $column;
        $this->row = $row;
    }

    public function getColumn():int
    {
        return $this->column;
    }

    public function getRow():int
    {
        return $this->row;
    }

    public function equals(Point $point):bool
    {
        return $this->column == $point->getColumn() && $this->row == $point->getRow();
    }

    public function __toString()
    {
        return '(' . $this->row . ',' . $this->column . ')';
    }
}

class Path
{
    protected $points;

    public function __construct()
    {
        $this->points = [];
    }

    public function addPoint(Point $point):void
    {
        $this->points[] = $point;
    }
  
    public function contains(Point $point):bool
    {
        return in_array($point, $this->points);
    }

    public function length():int
    {
        return count((array)$this->points);
    }

    public function getPoints():array
    {
        return $this->points;
    }

    public function getStart():Point
    {
        return reset($this->points);
    }

    public function getEnd():Point
    {
        return end($this->points);
    }
}


class ShortestPathCalculator
{
    protected $map;
    protected $start;
    protected $end;
    protected $shortestPath;

    public function __construct(Map $map, Point $start, Point $end)
    {
      
        $this->map = $map;
        $this->start = $start;
        $this->end = $end;
        $this->shortestPath = null;
      
        if (!$map->hasPoint($start) || !$map->isAllowed($start)) {
            throw new Exception("Invalid start point");
        }
        
        if (!$map->hasPoint($end) || !$map->isAllowed($end)) {
            throw new Exception("Invalid end point");
        }
    }

    public function getShortestPath():Path
    {
        if (null === $this->shortestPath) {
            $this->calculShortestPath($this->map, $this->start, $this->end, new Path());
        }
        return $this->shortestPath;      
    }

    private function calculShortestPath(Map $map, Point $currentPoint, Point $end, Path $currentPath):void
    {
        
            debug(PHP_EOL . ' ' . $currentPoint);

            $currentPath->addPoint($currentPoint);

            if ($currentPoint->equals($end)) {
              
                debug(' finish in ' . $currentPath->length().'<br>');
                $this->shortestPath = $currentPath;

                $this->calculShortestPath($this->map, $this->start, $this->end, new Path());
              
              
            }

            if (null !== $this->shortestPath && $currentPath->length() >= $this->shortestPath->length()) {
                debug(' too long<br>');
                //return;
                $this->calculShortestPath($this->map, $this->start, $this->end, new Path());
            }

            $siblings = [
                new Point($currentPoint->getRow() - 1, $currentPoint->getColumn()),
                new Point($currentPoint->getRow() + 1, $currentPoint->getColumn()),
                new Point($currentPoint->getRow(), $currentPoint->getColumn() - 1),
                new Point($currentPoint->getRow(), $currentPoint->getColumn() + 1),
            ];

            shuffle($siblings);

            foreach ($siblings as $point) {
                if ($map->hasPoint($point) && $map->isAllowed($point) && !$currentPath->contains($point)) {
                    $this->calculShortestPath($map, $point, $end, $currentPath);
                }
            }
            
       
    }


}


function displayResult($map, $shortestPath)
{
    echo PHP_EOL.'<br>';
    foreach ($map->getMap() as $r => $row) {
        echo '|';
        foreach ($row as $c => $cell) {
            $p = new Point($r, $c);
          
            if ($shortestPath->contains($p)) {
                if ($shortestPath->getStart() == $p) {
                    echo 'd';
                } 
                elseif ($shortestPath->getEnd() == $p) {
                    echo 'a';
                } 
                else {
                    echo 'o';
                }
            } else {
                echo ($cell ? '~' : '&diams;');
            }
            echo '|';
        }
        echo PHP_EOL.'<br>';
    }
}

function debug($message)
{
    if (defined('DEBUG') && DEBUG) {
        echo $message;
    }
}

function debug1($variable){
        $debug1 = debug_backtrace();
        echo '<pre><strong>fichier :</strong> ' . $debug1[0]['file'] . ' <br><strong>ligne :</strong>  ' . $debug1[0]['line'] .'</pre>';
        echo '<pre>' . print_r($variable, true) . '</pre>';
    }


// logic

define('DEBUG', true);

$map = new Map([
    [1,1,1,1,1],
    [1,1,0,0,1],
    [0,1,1,0,1],
    [0,1,1,1,1],
    [1,1,0,1,1],
    [1,1,1,1,0],
]);
$start = new Point(2, 4);
$end = new Point(1, 0);

$calculator = new ShortestPathCalculator($map, $start, $end);
$sp = $calculator->getShortestPath();

displayResult($map, $sp);
