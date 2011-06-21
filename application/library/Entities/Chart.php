<?php 
 /* 
  ******************************************************************** 
  * This script is written by Hermawan Haryanto [hermawan@haryanto]  * 
  * Objective   : To create Bar Chart of given Data                  * 
  *               Can be used for Finance Report, Log Report,        * 
  *               or anything that need bar chart                    * 
  * Create date : 09:25 - March 01, 2002                             * 
  * Licensed    : GPL - Copyleft                                     * 
  ******************************************************************** 
  */ 

  class Entities_Chart 
  { 
    var $title; 
    var $width; 
    var $height; 
    var $bartitle; 
    var $barheight; 
    var $barwidth; 
    var $background; 
    var $multiply; 
    var $spacebar; 
    var $leftspace; 
    var $rightspace; 
    var $upspace; 
    var $bottomspace; 
    var $leftlegend; 
    var $linecolor; 
    var $barcolor; 
    var $titlecolor; 
    var $legendcolor; 
    var $barinfocolor; 
    function Chart() 
    { 
      $this->title = "Chart Title"; 
      $this->background = Array(153,204,102); 
      $this->linecolor = Array(240,240,240); 
      $this->barcolor = Array(255,255,153); 
      $this->titlecolor = Array(111,166,55); 
      $this->legendcolor = Array(0,0,0); 
      $this->barinfocolor = Array(0,0,0); 
      $this->width = 600; 
      $this->height = 500; 
      $this->barwidth = 20; 
      $this->leftspace = 75; 
      $this->rightspace = 25; 
      $this->upspace = 50; 
      $this->bottomspace = 50; 
      $this->leftlegend = 10; 
    } 
    function setTitleColor($color) 
    { 
      $this->titlecolor = explode(",",$color); 
    } 
    function setLegendColor($color) 
    { 
      $this->legendcolor = explode(",",$color); 
    } 
    function setBarInfoColor($color) 
    { 
      $this->barinfocolor = explode(",",$color); 
    } 
    function setBarColor($color) 
    { 
      $this->barcolor = explode(",",$color); 
    } 
    function setLineColor($color) 
    { 
      $this->linecolor = explode(",",$color); 
    } 
    function setLeftLegend($leftlegend) 
    { 
      $this->leftlegend = $leftlegend; 
    } 
    function setLeftSpace($leftspace) 
    { 
      $this->leftspace = $leftspace; 
    } 
    function setRightSpace($rightspace) 
    { 
      $this->rightspace = $rightspace; 
    } 
    function setTitle($title) 
    { 
      $this->title = $title; 
    } 
    function setBackground($color) 
    { 
      $this->background = explode(",",$color); 
    } 
    function setWidth($width) 
    { 
      $this->width = $width; 
    } 
    function setHeight($height) 
    { 
      $this->height = $height; 
    } 
    function setBarWidth($barwidth) 
    { 
      $this->barwidth = $barwidth; 
    } 
    function addBar($bartitle,$barheight) 
    { 
      $this->bartitle[] = $bartitle; 
      $this->barheight[] = $barheight; 
    } 
    function prepare() 
    { 
     $err="";
      $arr = $this->barheight; 
      sort($arr); 
      reset($arr); 
      $this->highestvalue = end($arr); 
      $this->valueincriement = $this->highestvalue / $this->leftlegend; 
      $this->multiply = (  ($this->height-($this->upspace + $this->bottomspace)) / $this->highestvalue); 
      $this->spacebar = ( ($this->width - ($this->leftspace + $this->rightspace)) - ($this->barwidth * count($arr) ) ) / (count($arr)+1); 
      $this->legendspace = ($this->height - ($this->upspace+$this->bottomspace)) / $this->leftlegend; 
      if($this->barwidth * count($this->barheight) > ($this->width-($this->upspace+$this->bottomspace))){ 
        $err = "Bar width is to large"; 
      } 
      if($err!=""){ 
        print $err; 
        exit; 
      } 
    } 
    function generateChart() 
    { 
      $im = ImageCreate($this->width,$this->height); 
      $white = ImageColorAllocate($im,255,255,255); 
      $background = ImageColorAllocate($im,$this->background[0],$this->background[1],$this->background[2]); 
      $black = ImageColorAllocate($im,0,0,0); 
      $gray = ImageColorAllocate($im,100,100,100); 
      $linecolor = ImageColorAllocate($im,$this->linecolor[0],$this->linecolor[1],$this->linecolor[2]); 
      $barcolor = ImageColorAllocate($im,$this->barcolor[0],$this->barcolor[1],$this->barcolor[2]); 
      $titlecolor = ImageColorAllocate($im,$this->titlecolor[0],$this->titlecolor[1],$this->titlecolor[2]); 
      $legendcolor = ImageColorAllocate($im,$this->legendcolor[0],$this->legendcolor[1],$this->legendcolor[2]); 
      $barinfocolor = ImageColorAllocate($im,$this->barinfocolor[0],$this->barinfocolor[1],$this->barinfocolor[2]); 
      @ImageFilledRectangle($im,0,25,$this->width,$this->height-15,$background); 
      @ImageFilledRectangle($im,1,26,$this->width-2,$this->height-16,$white); 
      @ImageFilledRectangle($im,2,27,$this->width-3,$this->height-17,$background); 
      @ImageFilledRectangle($im,$this->leftspace,$this->upspace-10,$this->width-$this->rightspace,$this->height-($this->bottomspace-20),$white); 
      $titlewidth = strlen($this->title) * ImageFontWidth(5); 
      $titlexpos = ($this->width - $titlewidth)/2; 
      @ImageString ($im, 5, $titlexpos, 5, $this->title, $titlecolor); 
      @ImageString ($im, 1, 0, $this->height - 10, "easyChart.class - Get your own copy at http://hermawan.haryan.to it's Licensed under GNU Public License", $black); 
      for($i=0;$i<=$this->leftlegend;$i++){ 
        $legendx = 5; 
        $legendy = $this->upspace + ($this->legendspace * $i) - 6; 
        @ImageString($im, 2, $legendx, $legendy, $this->highestvalue-($this->valueincriement*$i), $legendcolor);  
        @ImageLine ($im, $this->leftspace, $legendy+6, $this->width - $this->rightspace, $legendy+6, $linecolor); 
      } 
      for($i=0;$i<count($this->barheight);$i++){ 
        $j=$i+1; 
        $x1 = ($j * $this->spacebar) + ($i * $this->barwidth) + $this->leftspace; 
        $y1 = $this->height - ($this->barheight[$i]*$this->multiply) - $this->upspace; 
        $x2 = $x1 + $this->barwidth; 
        $y2 = $this->height - $this->bottomspace; 
        $bartitlewidth = strlen($this->bartitle[$i]) * ImageFontWidth(2); 
        $centerofbar = $x1 + ($this->barwidth / 2); 
        $bartitlex = $centerofbar - ($bartitlewidth/2); 
        $bartitley = $y2 + 5; 
        @ImageFilledRectangle($im, $x1, $y1, $x2, $y2, $barcolor); 
        @ImageString($im, 2, $bartitlex, $bartitley, $this->bartitle[$i], $barinfocolor); 
      } 
      @ImageJPEG($im); 
      @ImageDestroy($im);  
    } 
  }; 
?> 
