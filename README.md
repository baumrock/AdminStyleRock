# AdminStyleRock

Also see [AdminStyleHello](https://github.com/baumrock/AdminStyleHello)

Easily style your ProcessWire backend with two simple settings:

- Primary Color
- Logo File or Url

That makes it very easy to make the PW backend match the CI of your clients:

<img src=https://i.imgur.com/3nEvy8I.png width=500>
<img src=https://i.imgur.com/zlHyoii.png width=500>
<img src=https://i.imgur.com/mPrnwIb.png width=500>

## Installation

Just install the module from the backend, choose a color and you are ready to rock! 🚀

## Wording: THEME vs. STYLE

* THEME: When we talk about a PW admin THEME we mean the stylesheet plus all the PHP files that create the markup and business logic.
* STYLE: A style on the other hand (like the rock or reno style) does only modify the look and feel via changing LESS variables or overwriting CSS

## Customizations

You can modify any aspect of your style with CSS/LESS simply by creating and modifying `/site/templates/admin.less`. ProcessWire will automatically recompile all files for you without the need of installing any external dependencies or tools like NPM.

You can change any of UIkits LESS variables or you can overwrite all classes with your own css rules:

```less
// change the global font size of the backend
@global-font-size: 30px;

// custom css overrides
p.notes {
  border: 2px solid red;
}
```

The result is obviously ugly but it shows what you can do 😅

<img width="500" alt="image" src="https://github.com/baumrock/AdminStyleRock/assets/8488586/5673e66c-6d06-4564-aed6-1cf1c1cd1899">


If you want to create your own AdminStyle module please see [info.md](info.md)
