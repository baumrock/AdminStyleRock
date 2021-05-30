# AdminStyleRock

ProcessWire admin style for AdminThemeUikit - note that `style` means that this module only applies changes to the CSS of the admin `theme`. The `theme` is responsible for creating all the markup and business logic.

## How AdminThemeUikit styles work

AdminThemeUikit uses UIkit as CSS framework. See https://getuikit.com/ docs (especially https://getuikit.com/docs/less) to get familiar with UIkit.

AdminThemeUikit adds several customizations to that CSS via less files that are located in the folder `uikit-pw` ([github link](https://github.com/processwire/processwire/tree/dev/wire/modules/AdminTheme/AdminThemeUikit/uikit-pw)).

The base style that is used for all styles (reno or rock or any other) is `pw.less` and you can even use the base style without any customizations if you want!

## Creating your own styles

Creating your own styles is as simple as writing CSS/LESS. You just need to tell ProcessWire to use a different style than the default one:

### 1. Set config

```php
$config->AdminThemeUikit = [
  'style' => '/path/to/your/style.less',
  'recompile' => true,
  'compress' => false,
];
```

You can either put that directly into `site/config.php` or - like this module does it - into the `init()` method of an autoload module. See `AdminStyleRock.module.php` for reference.

Setting `recompile` to `true` makes sure that the css gets compiled on every page load and setting `compress` to `false` makes it create a non-minified CSS file that is easier to work with.

### 2. Create a style.less file

Now create the style file that you set in the config. Start with an empty file and see the base style of AdminThemeUikit without the changes that are applied by either reno or rock theme. You'll notice that it looks very similar to the default uikit theme:

![img](https://i.imgur.com/AiKZnvS.png)

That's it!

Now you can start adding customizations/optimizations to your style.
