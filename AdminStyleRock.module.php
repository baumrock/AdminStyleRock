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
      ],
      'installs' => [],
    ];
  }

  public function init() {
    $config = $this->wire()->config;
    $config->AdminThemeUikit = [
      'style' => __DIR__."/style/rock.less",
      'recompile' => $config->debug,
      'compress' => !$config->debug,
    ];
  }

}
