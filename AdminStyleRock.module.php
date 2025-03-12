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
  const primary = "#e83561";
  const primary_h = 345;
  const primary_s = '80%';
  const primary_l = '56%';
  const saturation = '5%';

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

  public function init(): void
  {
    // minify assets in /src folder to /dst (using RockDevTools)
    if (
      wire()->config->rockdevtools
      && wire()->modules->isInstalled('RockDevTools')
    ) {
      rockdevtools()->assets()->minify(
        __DIR__ . '/src',
        __DIR__ . '/dst',
      );
    }
  }

  public function ready()
  {
    // add alfred style overrides
    $this->addHookAfter('Page::render', $this, 'addCssVariables');
    if ($this->wire->user->isSuperuser()) $this->addKitchenSink();

    // do everything below only on admin pages
    if ($this->wire->page->template != 'admin') return;

    $this->wire->adminTheme->addBodyClass('AdminStyleRock');

    // add AdminStyleRock JS to backend
    $config = $this->wire()->config;
    $config->scripts->add($config->urls($this) . "AdminStyleRock.js");
    $config->js('asrDarkmodeToggle', !$this->noDarkmodeToggle);

    $style = $config->paths($this) . "styles/_rock.less";
    $compiled = $config->paths->assets . "admin";
    $min = !$config->debug;
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

  protected function addCssVariables(HookEvent $event): void
  {
    /** @var Page $page */
    $page = $event->object;
    if ($page->template != 'admin') return;
    if ($this->wire->config->ajax) return;
    if ($this->wire->config->external) return;

    // add styles from styles.php
    $styles = $this->wire->files->render(__DIR__ . "/styles.php", [
      'h' => $this->rockprimary_h === null ? self::primary_h : $this->rockprimary_h,
      's' => $this->rockprimary_s === null ? self::primary_s : $this->rockprimary_s,
      'l' => $this->rockprimary_l === null ? self::primary_l : $this->rockprimary_l,
      'saturation' => $this->saturation === null ? self::saturation : $this->saturation,
    ]);
    $event->return = str_replace(
      "</head>",
      "$styles</head>",
      $event->return
    );
  }

  protected function addKitchenSink(): void
  {
    if ($this->wire->input->get->name !== 'AdminStyleRock') return;

    if ($this->wire->input->debug) {
      $this->message("Demo Alert Message");
      $this->warning("Demo Alert Warning");
      $this->error("Demo Alert Error");
    }

    $this->addHookAfter('InputfieldForm::render', function ($event) {
      if ($event->object->id !== 'ModuleEditForm') return;

      $form = new InputfieldForm();

      $fs = new InputfieldFieldset();
      $fs->name = "kitchensink";
      $fs->label = 'Kitchen Sink';
      $fs->icon = 'paint-brush';
      $fs->notes = "Kitchen Sink Demo Note";
      $fs->collapsed = $this->wire->input->debug
        ? Inputfield::collapsedNo
        : Inputfield::collapsedYesAjax;
      $fs->description = 'This is a preview of several fields to help with custom development.
        In RockFrontend you can enable livereload also for module pages like this, which is handy when working on the style to get live preview.
        You can add <a href="?name=AdminStyleRock&debug=1">&debug=1</a> to this page\'s url to open Kitchen Sink by default and to show demo alerts.';
      $fs->entityEncodeText = false;
      $form->add($fs);

      $fs->add([
        'type' => 'text',
        'label' => 'Demo Text Input',
        'notes' => 'Demo Note',
        'name' => 'demotext',
        'columnWidth' => 33,
      ]);
      $fs->add([
        'type' => 'select',
        'label' => 'Demo Select',
        'options' => [
          'option1' => 'Option 1',
          'option2' => 'Option 2',
          'option3' => 'Option 3',
        ],
        'value' => 'option1',
        'name' => 'demoselect',
        'columnWidth' => 33,
      ]);
      $fs->add([
        'type' => 'checkbox',
        'label' => 'Demo Checkbox',
        'checkboxLabel' => 'Demo Checkbox Label',
        'name' => 'democheckbox',
        'columnWidth' => 34,
      ]);
      $fs->add([
        'type' => 'asmSelect',
        'label' => 'Demo ASM Select',
        'name' => 'demoasm',
        'options' => [
          'option1' => 'ASM Option 1',
          'option2' => 'ASM Option 2',
          'option3' => 'ASM Option 3',
        ],
        'value' => ['option1'], // Default selected value
        'description' => 'This is a demo ASM Select field.',
      ]);
      $fs->add([
        'type' => 'textarea',
        'label' => 'Demo Textarea',
        'name' => 'demotextarea',
        'columnWidth' => 33,
      ]);
      $fs->add([
        'type' => 'radios',
        'label' => 'Demo Radios',
        'name' => 'demoradios',
        'options' => [
          'option1' => 'Option 1',
          'option2' => 'Option 2',
          'option3' => 'Option 3',
        ],
        'columnWidth' => 33,
      ]);
      $fs->add([
        'type' => 'toggle',
        'label' => 'Demo Toggle',
        'value' => 'yes',
        'name' => 'demotoggle',
        'columnWidth' => 34,
      ]);
      $this->wire->modules->get('JqueryUI')->use('vex');
      $fs->add([
        'type' => 'markup',
        'label' => 'VEX Demo',
        'value' => '
          <button class="ui-button open-vex">VEX Demo</button>
          <a href=# class=open-vex>VEX Demo</a>
          <script>
          $(document).ready(function() {
            $(".open-vex").click(function(e) {
              e.preventDefault();
              ProcessWire.alert("Demo VEX Alert");
            });
          });
          </script>
        ',
        'columnWidth' => 40,
      ]);
      $fs->add([
        'type' => 'markup',
        'label' => 'UIkit Notifications',
        'value' => '
          <button class="uk-button uk-button-default demo" type="button" onclick="UIkit.notification({message: \'Primary message…\', status: \'primary\', timeout: 15000})">Primary</button>
          <button class="uk-button uk-button-default demo" type="button" onclick="UIkit.notification({message: \'Success message…\', status: \'success\', timeout: 15000})">Success</button>
          <button class="uk-button uk-button-default demo" type="button" onclick="UIkit.notification({message: \'Warning message…\', status: \'warning\', timeout: 15000})">Warning</button>
          <button class="uk-button uk-button-default demo" type="button" onclick="UIkit.notification({message: \'Danger message…\', status: \'danger\', timeout: 15000})">Danger</button>
        ',
        'columnWidth' => 60,
      ]);

      $event->return = $form->render() . $event->return;
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

  /**
   * Config inputfields
   * @param InputfieldWrapper $inputfields
   */
  public function getModuleConfigInputfields($inputfields)
  {
    $this->iframe($inputfields);

    $inputfields->add([
      'type' => 'markup',
      'label' => 'Color Palette',
      'icon' => 'paint-brush',
      'value' => $this->wire->files->render(__DIR__ . "/colorpicker.php", [
        'rockprimary' => $this->rockprimary ?: self::primary,
        'saturation' => substr($this->saturation ?: self::saturation, 0, -1),
      ]),
    ]);

    $inputfields->add([
      'type' => 'text',
      'name' => 'rockprimary',
      'value' => $this->rockprimary ?: self::primary,
      'columnWidth' => 50,
    ]);
    $inputfields->add([
      'type' => 'text',
      'name' => 'saturation',
      'value' => $this->saturation ?: self::saturation,
      'columnWidth' => 50,
    ]);
    $inputfields->add([
      'type' => 'hidden',
      'name' => 'rockprimary_h',
      'value' => $this->rockprimary_h,
    ]);
    $inputfields->add([
      'type' => 'hidden',
      'name' => 'rockprimary_s',
      'value' => $this->rockprimary_s,
    ]);
    $inputfields->add([
      'type' => 'hidden',
      'name' => 'rockprimary_l',
      'value' => $this->rockprimary_l,
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

    $inputfields->add([
      'type' => 'checkbox',
      'name' => 'noDarkmodeToggle',
      'icon' => 'moon-o',
      'checked' => $this->noDarkmodeToggle ? 'checked' : '',
      'label' => 'Don\'t add darkmode-toggle to backend navbar',
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
