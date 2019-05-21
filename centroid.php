<?php
/**
 * Created by PhpStorm.
 * User: zjeff
 * Date: 5/4/2019
 * Time: 6:25 PM
 */

class centroid
{
    private $classified_count;
    private $in_progress_new_centroid;
    //used to calculate euclidean distance
    private $x;
    private $y;

    public function __construct($x, $y)
    {
        $this->x = $x;
        $this->y = $y;
        $this->classified_count = 0;
        $this->in_progress_new_centroid = new coordinate(0,0);
    }

    public function get_classified_count()
    {
        return $this->classified_count;
    }

    public function new_classified($coordinate)
    {
        $this->classified_count++;
        $x_average = $this->in_progress_new_centroid->get_x() + (($coordinate->get_x() - $this->in_progress_new_centroid->get_x())/$this->classified_count);
        $y_average = $this->in_progress_new_centroid->get_y() + (($coordinate->get_y() - $this->in_progress_new_centroid->get_y())/$this->classified_count);
        $this->in_progress_new_centroid->set_coordinate($x_average, $y_average);
    }

    public function relocate_centroid()
    {
        $this->x = $this->in_progress_new_centroid->get_x();
        $this->y = $this->in_progress_new_centroid->get_y();
        $this->classified_count = 0;
    }

    public function get_x()
    {
        return $this->x;
    }

    public function get_y()
    {
        return $this->y;
    }

    public function pretty_printing()
    {
        return "Centroid X: " . $this->x . " Centroid Y: " . $this->y. "<br>";
    }
}