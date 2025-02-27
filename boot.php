<?php

/**
 * @author FriendsOfRedaxo: Joachim Doerr https://github.com/joachimdoerr
 * @package redaxo5
 * @license MIT
 */

/** @var rex_addon $this */

// register permissions
if (rex::isBackend() && is_object(rex::getUser())) {
    rex_perm::register('cke5_addon[]');
    // load assets
    \Cke5\Provider\Cke5AssetsProvider::provideCke5ProfileEditData();
    \Cke5\Provider\Cke5AssetsProvider::provideCke5PreviewData();
    \Cke5\Provider\Cke5AssetsProvider::provideCke5BaseData();
    \Cke5\Provider\Cke5AssetsProvider::provideCke5CustomData();

    // Check REDAXO version
    if (rex_string::versionCompare(rex::getVersion(), '5.13.0-dev', '>=')) {
        rex_view::addCssFile($this->getAssetsUrl('cke5_dark.css'));
        if ($user = rex::requireUser()) {
            // get user settings for theme
            if ($theme_type = $user->getValue('theme')) {
                $theme = $theme_type;
           } else {
                $theme = 'auto';
            }
            
            if (rex::getProperty('theme') && rex::getProperty('theme') =='light')
            {
                $theme = 'light';
            }
             if (rex::getProperty('theme') && rex::getProperty('theme') =='dark')
            {
                $theme = 'dark';
            }
        }

        // set theme properties
        rex_view::setJsProperty('cke5theme', (string)$theme);
        rex_view::setJsProperty('cke5darkcss', rex_url::addonAssets('cke5') . 'dark.css');
    } else {
        rex_view::setJsProperty('cke5theme', 'notheme');
    }

    $addon = rex_addon::get('cke5');
    if ($addon->getConfig('updated') && $addon->getConfig('updated') == true ) {
      \Cke5\Handler\Cke5ExtensionHandler::updateOrCreateProfiles();
      $addon->setConfig('updated', false);
    }
    
    // upload image
    if (rex_request::request('cke5upload') == 1) {
        \Cke5\Handler\Cke5UploadHandler::uploadCke5Img();
    }

    // register extension point actions
    if (rex_be_controller::getCurrentPagePart(1) == 'cke5') {
        rex_extension::register('PAGE_TITLE', ['\Cke5\Handler\Cke5ExtensionHandler', 'addIcon'], rex_extension::EARLY);
        rex_extension::register('PAGES_PREPARED', ['\Cke5\Handler\Cke5ExtensionHandler', 'hiddenMain'], rex_extension::EARLY);
        rex_extension::register('REX_FORM_CONTROL_FIELDS', ['\Cke5\Handler\Cke5ExtensionHandler', 'removeDemoControlFields'], rex_extension::LATE);
        rex_extension::register(['REX_FORM_SAVED', 'REX_FORM_DELETED', 'CKE5_PROFILE_CLONE', 'CKE5_PROFILE_DELETE', 'CKE5_PROFILE_ADD', 'CKE5_PROFILE_UPDATED'], ['\Cke5\Handler\Cke5ExtensionHandler', 'createProfiles']);
    }
}
