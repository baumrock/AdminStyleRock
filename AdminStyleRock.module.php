<?php

namespace ProcessWire;

/**
 * @author Bernhard Baumrock, 30.05.2021
 * @license Licensed under MIT
 * @link https://www.baumrock.com
 */
class AdminStyleRock extends WireData implements Module, ConfigurableModule
{

  public $logo;
  public $rockprimary;

  public static function getModuleInfo()
  {
    return [
      'title' => 'AdminStyleRock',
      'version' => '1.0.7',
      'summary' => 'Docs & Development Module for rock style of AdminThemeUikit',
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

  public function ready()
  {
    // add alfred style overrides
    $this->addHookAfter('RockFrontend::addAlfredStyles', $this, 'addAlfredStyles');

    // do everything below only on admin pages
    if ($this->wire->page->template != 'admin') return;

    $config = $this->wire()->config;
    $min = !$config->debug;

    $style = $config->paths($this) . "styles/rock.less";
    $compiled = $config->paths->assets . "admin";
    if ($min) $compiled .= ".min.css";
    else $compiled .= ".css";

    // prepare less vars
    $vars = [];
    if ($this->rockprimary) $vars = ['rock-primary' => $this->rockprimary];

    $config->AdminThemeUikit = [
      'style' => $style,
      'compress' => $min,
      'customCssFile' => $compiled,
      'recompile' => @(filemtime($style) > filemtime($compiled)),
      'vars' => $vars,
    ];

    // attach hook to set logo url
    $this->addHookAfter("Modules::saveConfig", $this, "updateLogo");
    $this->addHookAfter("Inputfield::render", $this, "lockLogoField");
    $this->addHookAfter('AdminThemeUikit::renderFile', $this, "renderFile");
  }

  public function addAlfredStyles(HookEvent $event)
  {
    if ($this->wire->page->template == 'admin') return;
    $rf = $event->object;
    $rf->styles()->add(__DIR__ . "/styles/alfred.less");
    if ($this->rockprimary) {
      $rf->styles()->setVar('alfred-primary', $this->rockprimary);
    }
  }

  /**
   * Lock logo field of AdminThemeUikit
   * @return void
   */
  public function lockLogoField(HookEvent $event)
  {
    $field = $event->object;
    if ($field->name !== 'logoURL') return;
    if ($event->process != 'ProcessModule') return;
    if ($this->wire->input->get('name', 'string') !== 'AdminThemeUikit') return;
    $event->return = $field->value . " (set in AdminStyleRock)"
      . "<input type=hidden name=logoURL value={$field->value}>";
  }

  public function renderFile(HookEvent $event)
  {
    $file = basename($event->arguments(0));
    if ($file === '_offcanvas.php') {
      // remove processwire logo from offcanvas
      $event->return = str_replace(
        '<p id="offcanvas-nav-header">',
        "<button class=uk-offcanvas-close type=button uk-close"
          . " style='width:40px;height:40px;'></button>"
          . "<p id='offcanvas-nav-header' hidden>",
        $event->return
      );
    }
  }

  /**
   * Set logo of AdminThemeUikit
   */
  public function setLogoUrl($url, $m = 'AdminThemeUikit')
  {
    $modules = $this->wire->modules;
    $data = $modules->getConfig($m);
    $data['logoURL'] = $url;
    $modules->saveConfig($m, $data);
  }

  public function updateLogo(HookEvent $event)
  {
    $module = $event->arguments(0);
    if ($module != 'AdminStyleRock') return;
    $data = $event->arguments(1);
    if (!array_key_exists('logo', $data)) return;
    $this->setLogoUrl($data['logo']);
  }

  public function ___install()
  {
    $this->setLogoUrl($this->wire->config->urls($this) . "baumrock.svg");
  }

  /**
   * Config inputfields
   * @param InputfieldWrapper $inputfields
   */
  public function getModuleConfigInputfields($inputfields)
  {
    $this->iframe($inputfields);

    // add main color
    $inputfields->add([
      'type' => 'text',
      'name' => 'rockprimary',
      'notes' => 'eg #00ff00 or rgba(0,0,0,1)',
      'value' => $this->rockprimary,
      'label' => 'Primary Color',
      'description' => 'This color is used as @rock-primary of the rock.less style'
        . ' and as @alfred-primary for the alfred.less frontend editing style',
    ]);

    // set logo url
    $inputfields->add([
      'type' => 'text',
      'name' => 'logo',
      'notes' => 'This will set the logo url of AdminThemeUikit',
      'value' => $this->logo,
      'label' => 'Logo URL',
    ]);

    return $inputfields;
  }

  /**
   * Add a hidden iframe to the module config screen
   * This loads the frontpage once which forces the CSS to recreate.
   * We need to do this because we can't know the name/folder of the frontend
   * CSS assets!
   * @return void
   */
  public function iframe($inputfields)
  {
    /** @var RockFrontend $rf */
    $rf = $this->wire->modules->get('RockFrontend');
    if (!$rf) return;
    if ($iframe = $this->wire->session->asriframe) {
      $inputfields->add([
        'name' => 'iframe',
        'type' => 'markup',
        'label' => 'Iframe',
        'value' => $iframe,
      ]);
      $this->wire->session->asriframe = null;
    }
    if ($this->wire->input->post('rockprimary', 'string')) {
      $rf->forceRecompile();
      $url = $this->wire->pages->get(1)->httpUrl();
      $iframe = "
        <style>#Inputfield_iframe {position:absolute;width:10px;height:10px;left:-1000px;top:-1000px;}</style>
        <iframe src=$url width=100% height=400></iframe>
        ";
      $this->wire->session->asriframe = $iframe;
    }
  }
}
