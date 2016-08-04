# Branchee Block

This module allows the site builder to choose a menu with some options, and output a block that contains the menu with applicable [Branchee](https://github.com/dubbs/branchee) markup.

Branchee is a lightweight mobile navigation plugin with built in transitions. The menu is generated from a basic unordered list.

A default version of the Branchee library is provided in the module, but can be overridden with a new version by overriding the library in your theme. A built in style for the menu can be chosen in the block settings, or a custom class can be provided if you want to customize the styling as per the styling recommendations in the Branchee library.

## How to Use

* Go to `/admin/structure/block` and click **Place Block** in the region you want the mobile menu to appear in.
* Choose the Menu you would like to display.
* Choose a built in theme, or select **Other** and enter a class name that will be used as a prefix so you can style the menu yourself.
* Click **Save Block**

## How to Update/Override Library

A defined library can now be overridden in your theme's `*.info.yml` file. Here is an example of overriding the branchee library:

```
libraries-override:
  # Replace an entire library.
  branchee_block/branchee: mytheme/branchee

  # Replace an asset with another.
  branchee_block/branchee:
    js:
      libraries/branchee/dist/branchee.min.js: directory_to_new_branchee/branchee.min.js
    css:
      component:
        libraries/branchee/dist/branchee.min.css: directory_to_new_branchee/branchee.min.css
```

## Useful Tips

*Coming Soon*
