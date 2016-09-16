# Opencart DB Log
Logger for opencart database queries

## Installation
* Copy files from `upload` directory into website root directory
* In admin panel go to Extensions -> Extension Installer
* Click "Upload" button and select `dblog.ocmod.xml`
* Go to Extensions -> Modifications, enable DB Log modification and click "Refresh" button

## Config
In `system/config` directory you can find `dblog.example.php` file. You can rename it to `dblog.php` or copy with this name and change configs values in the file.
NOTE: if your store global config have configs with the same names as in the file, global config will be used instead of file's one.

## Licence
MIT. See the [LICENCE](https://github.com/artem328/opencart-db-log/blob/master/LICENSE.md) file