# GoSummarize: the mobile app

- Developed with _NativePHP_.
- PHP 8.4.
- Tested on iOS (should work with Android without extra work).

## Setting up your dev environment

### First time

- You need to have PHP installed locally on your machine.
- On a Mac: install Xcode from the AppStore.
- On a Mac: install Cocoapods: ``brew install cocoapods``.
- On a Mac: install Watchman: ``brew install watchman``
- Copy the _.env.example_ file to a _.env_ one. Any changes are optional.
- Run ``composer install``.
- Run ``php artisan native:install``

### Testing your app
- To launch the app (on a simulator for instance), just run: ``php artisan native:run --watch``

## Deploying on your cellphone

### iOS

This operation usually take roughly two minutes after the initial setup.

- Create an Apple Developer account (free).
- Set your telephone into developer mode.
- Connect it to your Mac.
- Run ``php artisan native:run ios``
- In the device list, select your iPhone.
- Once deployed, you need to approve this application. You have to go to Setting -> General -> VPN & Device Management .

Note: the app needs to be redeployed every 7 days.
