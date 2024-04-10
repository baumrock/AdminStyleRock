<?php

namespace ProcessWire;

/**
 * @author Bernhard Baumrock, 30.05.2021
 * @license Licensed under MIT
 * @link https://www.baumrock.com
 */
class AdminStyleRock extends WireData implements Module, ConfigurableModule
{

  const prefix = "adminstylerock_";

  const field_adminlogo = self::prefix . "adminlogo";

  public $logo;
  public $rockprimary;

  public static function getModuleInfo()
  {
    return [
      'title' => 'AdminStyleRock',
      'version' => json_decode(file_get_contents(__DIR__ . "/package.json"))->version,
      'summary' => 'Docs & Development Module for rock style of AdminThemeUikit',
      'autoload' => true, // in ready() we exit early if we are not in admin
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

    $this->addDarkmodeToggle();

    $config = $this->wire()->config;
    $min = !$config->debug;

    $style = $config->paths($this) . "styles/_rock.less";
    $compiled = $config->paths->assets . "admin";
    if ($min) $compiled .= ".min.css";
    else $compiled .= ".css";

    // prepare less vars
    $vars = [];
    if ($this->rockprimary) $vars = ['rock-primary' => $this->rockprimary];

    // check if a file was changed
    $mCSS = @filemtime($compiled);
    $mLESS = 0;
    $files = $this->wire->files->find(__DIR__ . "/styles", [
      'extensions' => 'less',
    ]);
    foreach ($files as $file) {
      if ($mLESS > $mCSS) continue;
      $mLESS = max($mLESS, filemtime($file));
    }

    $config->AdminThemeUikit = [
      'style' => $style,
      'compress' => $min,
      'customCssFile' => $compiled,
      'recompile' => $mLESS > $mCSS,
      'vars' => $vars,
    ];

    // attach hook to set logo url
    wire()->addHookAfter('AdminThemeUikit::renderFile', $this, "renderFile");
    wire()->addHookAfter("Pages::saved", $this, "addUploadedLogo");
    wire()->addHookAfter("ProcessPageEdit::buildForm", $this, "hideLogofield");
  }

  public function addAlfredStyles(HookEvent $event)
  {
    if ($this->wire->page->template == 'admin') return;
    $rf = $event->object;

    $name = 'head';
    $version = $this->wire->modules->getModuleInfo($rf)['version'];
    if (version_compare($version, '2.40.0', '>')) {
      $name = 'rockfrontend';
    }

    $rf->styles($name)->add(__DIR__ . "/styles/_alfred.less");
    if ($this->rockprimary) {
      $rf->styles($name)->setVar('alfred-primary', $this->rockprimary);
    }
  }

  private function addDarkmodeToggle(): void
  {
    if ($this->wire->config->ajax) return;
    if ($this->wire->external) return;
    $this->addHookAfter("Page::render", function (HookEvent $event) {
      $html = $event->return;
      $icon = '<div>
        <a class="uk-link-reset asr-show-light" data-darkmode=1 title="Dark mode" uk-tooltip><svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24"><path fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3h.393a7.5 7.5 0 0 0 7.92 12.446A9 9 0 1 1 12 2.992z"/></svg></a>
        <a class="uk-link-reset asr-show-dark" data-darkmode=0 title="Light mode" uk-tooltip><svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24"><path fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.828 14.828a4 4 0 1 0-5.656-5.656a4 4 0 0 0 5.656 5.656zm-8.485 2.829l-1.414 1.414M6.343 6.343L4.929 4.929m12.728 1.414l1.414-1.414m-1.414 12.728l1.414 1.414M4 12H2m10-8V2m8 10h2m-10 8v2"/></svg></a>
      </div>';
      $html = str_replace(
        '<div class="uk-navbar-right">',
        '<div class="uk-navbar-right">' . $icon,
        $html
      );

      $url = $this->wire->config->urls($this) . "DarkmodeToggle.js";
      $html = str_replace(
        '</head>',
        "<script src='$url'></script></head>",
        $html
      );
      $event->return = $html;
    });
  }

  public function addUploadedLogo(HookEvent $event): void
  {
    /** @var Page $page */
    $page = $event->arguments(0);
    $field = $page->getField(self::field_adminlogo);
    if (!$field) return;
    $logo = $page->getFormatted(self::field_adminlogo);
    if ($logo) $this->setLogoUrl($logo->maxHeight(100)->url);
    else {
      // do nothing!
      // if we reset the logo here it is not possible to set a custom logo
      // from AdminThemeUikit
    }
  }

  public function hideLogofield(HookEvent $event): void
  {
    $form = $event->return;
    $f = $form->get(self::field_adminlogo);
    if (!$f) return; // no field no use for this hook

    // don't execute the hook on page save
    // otherwise we end up with two images after a new upload
    if ($this->wire->input->post->submit_save) return;

    $remove = false;
    if (!$this->wire->user->isSuperuser()) $remove = true;
    if ($this->wire->input->get('fields') !== self::field_adminlogo) $remove = true;

    if ($remove) $form->remove(self::field_adminlogo);
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

  public function migrate(): void
  {
    /** @var RockMigrations $rm */
    $rm = $this->wire->modules->get('RockMigrations');
    $rm->createField(self::field_adminlogo, 'image', [
      'type' => 'image',
      'label' => 'Admin Logo',
      'maxFiles' => 1,
      'descriptionRows' => 0,
      'extensions' => 'svg png jpeg jpg',
      'maxSize' => 3, // max 3 megapixels
      'icon' => 'picture-o',
      'outputFormat' => FieldtypeFile::outputFormatSingle,
      'tags' => 'AdminStyleRock',
      'okExtensions' => ['svg'],
      'gridMode' => 'list', // left, list
      'collapsed' => Inputfield::collapsedNo,
    ]);
    $home = $this->wire->pages->get(1)->template;
    $rm->addFieldToTemplate(self::field_adminlogo, $home);
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
      'icon' => 'paint-brush',
      'notes' => 'eg #00ff00 or rgba(0,0,0,1)',
      'value' => $this->rockprimary,
      'label' => 'Primary Color',
      'description' => 'This color is used as @rock-primary of the rock.less style'
        . ' and as @alfred-primary for the alfred.less frontend editing style',
    ]);

    /** @var RockMigrations $rm */
    $rm = $this->wire->modules->get('RockMigrations');
    /** @var InputfieldMarkup $f */
    $f = $this->wire->modules->get('InputfieldMarkup');
    $f->label = "Upload Logo";
    $f->icon = "upload";
    if ($rm) {
      $this->migrate();
      $url = $this->wire->pages->get(1)->editUrl();
      $fname = self::field_adminlogo;
      $f->value = "<a href='$url&fields=$fname' target=_blank class='ui-button'>Upload Logo</a>";
      $f->appendMarkup("<p class=notes>You want to use the logo somewhere else? Use \$pages->get(1)->adminstylerock_adminlogo to get the PageImage object that you can resize etc.<br>
        You can set a logo path manually in <a href='{$this->wire->pages->get(2)->url}module/edit?name=AdminThemeUikit'>AdminThemeUikit</a>.</p>");
    } else {
      $f->value = "<div class='uk-alert uk-alert-danger'>RockMigrations is not installed</div>
        <div>You can either install RockMigrations or you can set a logo path manually in
        <a href='{$this->wire->pages->get(2)->url}module/edit?name=AdminThemeUikit'>AdminThemeUikit</a>.";
    }
    $inputfields->add($f);

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

  public function ___uninstall()
  {
    $modules = $this->wire('modules');
    $adminThemeUikit = $modules->get('AdminThemeUikit');
    if ($adminThemeUikit) {
      $adminThemeUikit->logoURL = '';
      $modules->saveConfig('AdminThemeUikit', 'logoURL', '');
    }
  }
}
