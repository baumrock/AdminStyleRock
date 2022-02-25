<?php namespace ProcessWire;
/**
 * @author Bernhard Baumrock, 30.05.2021
 * @license Licensed under MIT
 * @link https://www.baumrock.com
 */
class AdminStyleRock extends WireData implements Module, ConfigurableModule {

  public $rockprimary;

  public static function getModuleInfo() {
    return [
      'title' => 'AdminStyleRock',
      'version' => '0.0.9',
      'summary' => 'Docs & Development Module for rock style of AdminThemeUikit',
      'autoload' => 'template=admin',
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

    $style = $config->paths($this)."style/rock.less";
    $compiled = $config->paths->assets."admin";
    if($min) $compiled .= ".min.css";
    else $compiled .= ".css";

    // prepare less vars
    $vars = [];
    if($this->rockprimary) $vars = ['rock-primary' => $this->rockprimary];

    $config->AdminThemeUikit = [
      'style' => $style,
      'compress' => $min,
      'customCssFile' => $compiled,
      'recompile' => @(filemtime($style) > filemtime($compiled)),
      'vars' => $vars,
    ];

  }

  public function ___install() {
    $m = "AdminThemeUikit";
    $modules = $this->wire->modules;
    $data = $modules->getConfig($m);
    $data['logoURL'] = $this->wire->config->urls($this)."baumrock.svg";
    $modules->saveConfig($m, $data);
  }

  /**
  * Config inputfields
  * @param InputfieldWrapper $inputfields
  */
  public function getModuleConfigInputfields($inputfields) {

    // add main color
    $inputfields->add([
      'type' => 'text',
      'name' => 'rockprimary',
      'notes' => 'eg #00ff00 or rgba(0,0,0,1)',
      'value' => $this->rockprimary,
      'label' => '@rock-primary',
    ]);

    // link to change logo url
    $url = $this->wire->pages->get(2)->url."module/edit?name=AdminThemeUikit";
    $inputfields->add([
      'type' => 'markup',
      'label' => 'Change Logo',
      'value' => "<a href='$url' class='ui-button'>Change the logo url in
        'Masthead + navigation' section of AdminThemeUikit</a>",
    ]);

    return $inputfields;
  }

}
