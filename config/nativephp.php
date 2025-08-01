<?php

return [

    /*
    |--------------------------------------------------------------------------
    | App Version Name
    |--------------------------------------------------------------------------
    |
    | This is the human-readable version of your app (e.g. "1.0.0"). It is
    | used as the versionName in Android builds and may be displayed in
    | the app or console to determine the current app release version.
    |
    */

    'version' => env('NATIVEPHP_APP_VERSION', '1.0.0'),

    /*
    |--------------------------------------------------------------------------
    | App Version Code
    |--------------------------------------------------------------------------
    |
    | This is the internal numeric version code used for Play Store builds.
    | It must increase with every release. This is used as versionCode in
    | Android builds and is required for publishing updates to the store.
    |
    */

    'version_code' => env('NATIVEPHP_APP_VERSION_CODE', 1),

    /*
    |--------------------------------------------------------------------------
    | App ID
    |--------------------------------------------------------------------------
    |
    | This is the unique ID of your application used by Android to identify
    | the app package. It is typically written in reverse domain format,
    | such as "com.nativephp.app".
    |
    */

    'app_id' => env('NATIVEPHP_APP_ID'),

    /*
    |--------------------------------------------------------------------------
    | Deeplink Scheme
    |--------------------------------------------------------------------------
    |
    | The deep link scheme to use for opening your app from URLs. For
    | example, using the scheme "nativephp" allows links like:
    | nativephp://some/path to open the app directly.
    |
    */

    'deeplink_scheme' => env('NATIVEPHP_DEEPLINK_SCHEME'),

    /*
    |--------------------------------------------------------------------------
    | Deeplink Host
    |--------------------------------------------------------------------------
    |
    | The domain name to associate with verified HTTPS links and NFC tags.
    | This allows URLs like https://your-host.com/path to open your app
    | when tapped from an NFC tag or clicked from the browser.
    |
    */

    'deeplink_host' => env('NATIVEPHP_DEEPLINK_HOST'),

    /*
    |--------------------------------------------------------------------------
    | Development Team (iOS)
    |--------------------------------------------------------------------------
    |
    | The Apple Developer Team ID to use for code signing iOS apps. This is
    | automatically detected from your installed certificates, but you can
    | override it here if needed. Find your Team ID in your Apple Developer
    | account under Membership details.
    |
    */
    'development_team' => env('NATIVEPHP_DEVELOPMENT_TEAM'),

    /*
    |--------------------------------------------------------------------------
    | App Author
    |--------------------------------------------------------------------------
    |
    | The author of the application. This is used only for display or
    | packaging purposes and has no effect on runtime functionality.
    |
    */

    'author' => env('NATIVEPHP_APP_AUTHOR'),

    /*
    |--------------------------------------------------------------------------
    | Default Native App Service Provider
    |--------------------------------------------------------------------------
    |
    | This is the main service provider used to configure your native app.
    | It is where you can define hotkeys, menus, native windows, and
    | other boot logic that runs inside the NativePHP runtime.
    |
    */

    'provider' => \App\Providers\NativeAppServiceProvider::class,

    /*
    |--------------------------------------------------------------------------
    | Environment Keys to Clean Up
    |--------------------------------------------------------------------------
    |
    | These are keys that will be removed from the .env file during app
    | bundling to prevent secrets or development credentials from being
    | leaked. Wildcards are supported (e.g. AWS_* or *_SECRET).
    |
    */

    'cleanup_env_keys' => [

        'AWS_*',

        'GITHUB_*',

        'DO_SPACES_*',

        '*_SECRET',

        'NATIVEPHP_UPDATER_PATH',

        'NATIVEPHP_APPLE_ID',

        'NATIVEPHP_APPLE_ID_PASS',

        'NATIVEPHP_APPLE_TEAM_ID',
    ],

    /*
    |--------------------------------------------------------------------------
    | Files to Exclude Before Bundling
    |--------------------------------------------------------------------------
    |
    | These files and folders will be removed before the final bundle is
    | built for production. You may use glob/wildcard patterns here to
    | skip unnecessary assets like logs, sessions, or temp data.
    |
    */

    'cleanup_exclude_files' => [

        'storage/framework/sessions',

        'storage/framework/cache',

        'storage/framework/testing',

        'storage/logs/laravel.log',
    ],

    'android' => [

        'gradle_jdk_path' => env('NATIVEPHP_GRADLE_PATH'),

        'android_sdk_path' => env('NATIVEPHP_ANDROID_SDK_LOCATION'),

        'emulator_path' => env('ANDROID_EMULATOR'),

        '7zip-location' => env('NATIVEPHP_7ZIP_LOCATION', 'C:\\Program Files\\7-Zip\\7z.exe'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Hot Reload Configuration
    |--------------------------------------------------------------------------
    */
    'hot_reload' => [
        'watch_paths' => [
            'app',
            'resources',
            'routes',
            'config',
            'database',
        ],

        'exclude_patterns' => [
            '\.git',
            'storage/logs',
            'storage/framework',
            'vendor',
            'node_modules',
            '\.swp',
            '\.tmp',
            '~',
            '\.log',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Permissions
    |--------------------------------------------------------------------------
    |
    | Here you may enable or disable specific native features for your app.
    | Setting a permission to true allows NativePHP to request the necessary
    | access from the operating system at runtime (e.g., for NFC, biometrics,
    | or push notifications). Enable any features you need, make
    | sure you run `native:install --force` after changing.
    |
    */

    'permissions' => [

        'biometric' => false,

        'camera' => false,

        'nfc' => false,

        'push_notifications' => false,

    ],
];
