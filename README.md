# Structure Plus



## Requirements

This plugin requires Craft CMS 5.0.0 or later, and PHP 7.3 or later.

## Installation

1. Create a plugins/ directory in your Craft project.
2. Clone this repository into the plugins/ directory.
3. Checkout the ``main`` branch
3. Update the composer.json file in your Craft project to include the following repository:
```json
{
    "repositories": [
        {
            "type": "path",
            "url": "plugins/*"
        }
    ]
}
```
4. Run `ddev composer require boost/craft-structure-plus:dev-master` in your Craft project.
5. Install the plugin and test it out.