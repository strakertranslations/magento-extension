# Straker Translations Magento 2 Extension
![alt text]( https://www.strakertranslations.com/wp-content/themes/strakertranslations/assets/images/logo.png "Straker Translations")
[Straker Translations](https://www.strakertranslations.com/)

This extension will add the ability to translate products, categories, cms pages and blocks into different languages via Straker Translations API. 

## Installation
* Download zip file of this extension
* Place all the files of the extension in your Magento 2 installation in the folder `app/code/Straker/EasyTranslationPlatform`
* Enable the extension: `php bin/magento --clear-static-content module:enable Straker_EasyTranslationPlatform`
* Upgrade db scheme: `php bin/magento setup:upgrade`
* Clear cache

## SandBox Mode
* To enable sandbox mode go to the configuration page and select the Straker Translations configuration tab to set the environment for testing. 
