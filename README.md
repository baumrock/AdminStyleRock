# AdminStyleRock

ProcessWire admin style for AdminThemeUikit - note that `style` means that this module only applies changes to the CSS of the admin `theme`. The `theme` is responsible for creating all the markup and business logic.

## How AdminThemeUikit styles work

AdminThemeUikit uses UIkit as CSS framework. See https://getuikit.com/ docs (especially https://getuikit.com/docs/less) to get familiar with UIkit. It then adds several customizations to that CSS via less files that are located in the folder `uikit-pw` ([github link](https://github.com/processwire/processwire/tree/dev/wire/modules/AdminTheme/AdminThemeUikit/uikit-pw)).

The base style that is used for all styles (reno or rock or any other) is `pw.less` and you can even use the base style without any customizations if you want!

# Creating your own styles

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

Now create the style file that you set in the config. Start with an empty file with a single comment like this:

```LESS
/* ##### Admin Style Rock ##### */
```

Now reload your backend and you should see the base style of AdminThemeUikit without the changes that are applied by either reno or rock theme. You'll notice that it looks very similar to the default uikit theme:

![img](https://i.imgur.com/AiKZnvS.png)

That's it!

If you look at the compiled CSS file (`site/assets/admin.css` by default) you'll see that the admin theme has over 16k lines of css and after that it adds the CSS of your style and after that it adds the CSS of `admin.less`.

![img](https://i.imgur.com/DKcQlcv.png)

Now you can start adding customizations/optimizations to your style.

# Example customizations

### Tweak PW notices

PW base style notifications look like this:

![img](https://i.imgur.com/f345nL6.png)

As you can see there are some margins that we don't want. We add the following line to our style:

```LESS
.pw-notices {
  margin-top: 1px;
  > li {
    margin: 1px;
  }
}
```

![img](https://i.imgur.com/H1oWg42.png)

### Tweak page edit tabs

PW base style page editor tabs look like this:

![img](https://i.imgur.com/38hDjNm.png)

Add this line to your style to remove the paddings:

```LESS
.uk-tab>* { padding-left: 0; }
```

![img](https://i.imgur.com/O0BeYIy.png)

### Changing UIkit variables

You can change any of uikit's less variables in your style. UIkit themes are built in components. See https://github.com/uikit/uikit/blob/develop/src/less/components/base.less as an example. Also check if your IDE has plugins to scan your project for LESS variables. I'm using VSCode + https://marketplace.visualstudio.com/items?itemName=mrmlnc.vscode-less

![img](https://i.imgur.com/fZoSm7W.png)

#### Changing fonts

Thanks to UIkit it is easy to change the base font. For this example we'll use https://fonts.google.com/specimen/Sigmar+One. All we need to do is to add the font to the styles array of PW:

```php
$config->styles->add('https://fonts.googleapis.com/css2?family=Sigmar+One&display=swap');
```

And then set the correct UIkit variable - so add this to your style:

```LESS
@global-font-family: 'Sigmar One', cursive;
```

Once reloaded you'll get a totally different look of your PW backend:

![img](https://i.imgur.com/cllilIV.png)

#### Changing colors

Changing colors is a little more complicated. I'll demonstrate why. First, we change the uikit primary background variable:

```LESS
@global-primary-background: #610088;
```

![img](https://i.imgur.com/zmxNU72.png)

Did you notice something in the screenshot? Links and hover colors are still the default blue. Same goes for the page tree:

![img](https://i.imgur.com/5N3i964.png)

So if we wanted to get a consistent color change we'd need to set more variables - actually a lot more and some of them are quite hidden, for example buttons of VEX dialogs that you'd likely miss to change if you needed to make all these changes every time you want to change the main color of your style. That's where the "rock" style comes into play!

# The "rock" style

The goal of the rock style is to make it as easy as possible to adapt your backend to the CI of your client. That's why it uses only ONE single main color (`@rock-primary`) and keeps all other design elements in a neutral grey.

Now try changing the main color of the rock style by setting your config to use the rock style and then adding these two lines to `admin.less`:

```LESS
@rock-primary: #610088;
@global-font-family: 'Sigmar One', cursive;
```

![img](https://i.imgur.com/fxLJzk2.png)

This will even change selection background:

![img](https://i.imgur.com/OvD2kAh.png)

Note that the global font family is not a `@rock-` variable because it would only change one single uikit less variable anyhow while `@rock-primary` changes many. To avoid confusion I think it is the best to introduce new variables only if they didn't exist or if they changed multiple uikit variables at once.

# Creating PRs for the rock style

To make it easy for me to maintain the rock style please provide PRs here: https://github.com/baumrock/AdminStyleRock/pulls

Once the changes are tested and approved I can ask Ryan to pull the changes into the core.
