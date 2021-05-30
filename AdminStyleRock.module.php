<?php namespace ProcessWire;
/**
 * @author Bernhard Baumrock, 30.05.2021
 * @license Licensed under MIT
 * @link https://www.baumrock.com
 */
class AdminStyleRock extends WireData implements Module {

  public static function getModuleInfo() {
    return [
      'title' => 'AdminStyleRock',
      'version' => '0.0.1',
      'summary' => 'ProcessWire admin style for AdminThemeUikit',
      'autoload' => true,
      'singular' => true,
      'icon' => 'css3',
      'requires' => [
        'ProcessWire>=3.0.178',
        'Less',
      ],
      'installs' => [],
    ];
  }

  public function init() {
    $config = $this->wire()->config;
    $min = !$config->debug;
    
    $style = __DIR__."/style/rock.less";
    $compiled = $config->paths->assets."admin";
    if($min) $compiled .= ".min.css";
    else $compiled .= ".css";
    
    $config->AdminThemeUikit = [
      'style' => $style,
      'compress' => $min,
      'customCssFile' => $compiled,
      'recompile' => @(filemtime($style) > filemtime($compiled)),
    ];
  }

}
