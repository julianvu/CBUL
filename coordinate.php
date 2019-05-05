<?php
/**
 * Created by PhpStorm.
 * User: zjeff
 * Date: 5/4/2019
 * Time: 4:09 PM
 */

class coordinate
{
    private $x;
    private $y;
    private $nearest_centroid_coordinate;

    public function __construct($x, $y)
    {
        $this->x = $x;
        $this->y = $y;
        $this->nearest_centroid_coordinate = null;
    }

    public function get_nearest_centroid_coordinate()
    {
        return $this->nearest_centroid_coordinate;
    }

    public function set_nearest_centroid_coordinate($new_nearest_centroid_coordinate)
    {
        $this->nearest_centroid_coordinate = $new_nearest_centroid_coordinate;
    }

    public function get_x()
    {
        return $this->x;
    }

    public function get_y()
    {
        return $this->y;
    }
}