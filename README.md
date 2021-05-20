Codyas Toolbox
==============

Codyas Toolbox is a very opinionated set of internal tools for developing Symfony apps.

**Features**

  * **CRUD** operations on Doctrine entities (create, edit, list, delete).
  * Admin template optimized for Symfony. 
  * Supports Symfony 4.0 or higher.
 
**Requirements**
  * PHP >= 7
  * Symfony >= 4.0
  * NPM
  * Yarn

Installation
------------
First your need to allow extra contrib to configure the bundle via Flex, run the following in your project root:

``` bash 
composer config extra.symfony.allow-contrib true
```
Then install the bundle: 

``` bash
composer require codyas/toolbox-bundle
```

Configuration
-------------
Most of the project config should be done by Flex. If due to any reason the configuration isn't automatically done, configure the bundle manually:

Register the bundle:

```php
# config/bundles.php

return [
    // ...
    Codyas\Toolbox\CodyasToolboxBundle::class => ['all' => true],
    // ...
];
```
Register the bundle's routes:

```yaml
# config/routes/codyas_toolbox.yaml

codyas_toolbox_bundle:
    resource: '@CodyasToolboxBundle/Controller/CrudController.php'
    type: annotation
```

Templating
----------
This bundle provides a standard templating system for Twig. The templates selected to benefits from the pre-designed view, 
must extends the `base.html.twig` file of the selected theme. Currently one theme is supported, and the themes are located under the 
`Resources/views/` folder of the bundle.  


Assets configuration
--------------------
This bundle provides a selection of assets required by the template(s) and by the CRUD features and some others.
This set of tools is distributed in a NPM package you must install in your project. Please consider that Webpack Encore
is required in order to compile this assets for both dev and production environments.

In your project root execute (NPM and Yarn are required):

```bash
yarn add --save @codyas/symfony-toolbox
```

This will install the assets and all other UI dependencies required by the template. Once the installation
is completed, create an entry point file in the project's assets folder, ex: `admin_app.js` and include the desired features.

```javascript
// assets/js/admin_app.js

require('@codyas/symfony-toolbox');
``` 

Next register the entry point in the `webpack.config.js` file. The entry name must be `codyas_ep` as the templating config expects this name.

```javascript
// assets/js/app.js
// (...)
.addEntry('codyas_ep', './assets/admin/admin_app.js')
// (...)
``` 
 

If some customization of the CRUD behaviour is needed, append the following code to the same file
where **custom_folder** stands for a folder inside **assets/js/** that contains JS files. This files
will be included and executed in the CRUD environment.

```javascript
// assets/js/app.js

function requireAll(r) { r.keys().forEach(r); }
requireAll(require.context('./custom_folder/', true, /\.js$/));
```