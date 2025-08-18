# WP ERP Pro

### WP ERP Pro - The Ultimate Company & Business Management Solution

Automate & Manage your growing business even better using Human Resource, Customer Relations, Accounts Management right inside your WordPress

## Minimum Requirement

-   PHP 5.6
-   WordPress 4.4+

## Installation

-   Clone the repository inside `/wp-content/plugins/`
-   CD into folder `cd erp-pro` and run `composer install` and then `composer dump-autoload -o`
-   Activate the plugin
-   Ask for a developer license from support.
-   Goto ```WP ERP --> License``` submenu of your WordPress installation and activate the license

## Commands ###

Install js dependencies by running below command on project root's terminal

```
npm install
```

To compile minified version of the css and scripts run

```
npm run build
```

To compile non minified version of css and js script
```
npm run dev-build
```

Watch assets changes while developing
```
npm run dev
```

## Release Process For ERP PRO ##

- Run `git flow release start 'version_number_here'` eg: `git flow release start 3.0.1`
- Change version number under `erp-pro.php`
- Change version number under `package.json`
- If you've updated any module, change version number for that module under file: `includes/Module.php` method `public function get_all_modules( $modules = [] ) {`
- Run `npm install` to install node packages, periodically run `npm update` to update node packages and `npm audit fix` to automatically fix issues under node packages
- Run `composer install` to install composer packages.
- Run `composer update` to Composer Merge Plugin to install dependencies from updated or newly created sub-level composer.json files in your project
- If you've updated CRM Deals module, kindly check the #Compile deal assets section for more details
- Run `npm run release`
- Submit the newly created zip to the QA team for final testing (**slack:testing channel**)
- Upon QA team approval, commit changes locally with commit message `chore: bump version to version_number_here`
- Run `git flow release finish version_number_here` and finish the release process.
- Run `git push origin develop` to push the changes to remote git repository
- Run `git push origin master` to push the changes to the remote git repository
- Now change [ERP PRO Changelog](https://github.com/wp-erp/erp-utils/blob/main/erp-pro-changelog.txt). You must need to update `Stable tag` to current released version and `Tested up to` version to current WordPress version. Also add the current version changelog here.
- If you've updated any pro module, you need to change [ERP PRO Module Version](https://github.com/wp-erp/erp-utils/blob/main/extensions.json). Update that module version in this file.
- Post on **`slack:release`** channel with changelog and ERP Pro zip file and ask support team to upload  files to server.
- **Kindly add a random 5 to 8 digit prefix to the zip file before sending it to support team to upload on wperp.com server. Otherwise, anyone will be able to download the file from server if they know the filename. eg: change `erp-pro-v1.2.7.zip` into `t9lsre90e-erp-pro-v1.2.7.zip`**


## Compile deal assets ##
Deal module *(modules --> crm --> deals)* is not included in automated build process, you need to build assets file manually for this module.

- Open WP ERP Pro project root in terminal, 
- Navigate to *modules --> crm --> deals* via cd command eg: `cd modules/crm/deals`
- run `npm install` to install all required js dependencies
- To compile all js and css files run `npm run install` or `grunt install` 


## wperp.com configuration ##

### Below changes is required one time only ###

- ERP PRO Repository needs to be cloned on wperp.com server
- Under wperp.com hosting, need to edit `.env` file and need to add previously cloned ERP PRO repository file location under `WD_SITE_UTILITY_ERP_PRO_DIR='erp_pro_file_location_here'`
- Need to setup a github action so that each time master brunch is updated, this cloned repo will pull all the changes from github master branch
- In GitHub actions, we need to must include `composer install` and `composer dumpautoload -o` commands

### File upload On wperp.com ###

- Two product has been created named `WP ERP PRO – Yearly Subscription` and `WP ERP PRO – Monthly Subscription`, below instruction are some for both product
- Goto Edit mode of the product from `Downloads --> All Downloads` submenu
- Need to upload the latest zip file under `Download Files` section
- Need to change current version number under `Licensing section --> Version Number` field.
- Need to clear cache of readme.txt file under `Download section --> Clear Cache`. Click Clear Cache button from here.

Repeat above steps for both Monthly and Yearly ERP PRO Products

## How ERP PRO Zip File Generates Dynamically ##

- After changes merged to the master branch ie: immediately  after `git push origin master` has been run, via git action all the changes will be uploaded automatically to the wperp.com site.
- Zip file can be created via three actions:
  1. Automatically when an update has been released
  2. When user download the zip from My Account page of wperp.com site
  3. Manually Via `EDD Downloads --> Subscriptions` Menu
- Below section Explains how a zip generates dynamically 
  1. If user haven't purchased any pro modules, zip attached to the product will be used. No new zip will be created.
  2. From erp-pro plugin, every twelve hour plugin will check for the update and a request will be sent to the wperp.com site. If user visits `WP Admin Dashboard --> Dashboard --> Updates` submenu, a request to the wperp.com will be initiated instantly (This is default behaviour of WordPress update system ).
  3. Here (wperp.com) request will be validated for valid license key, number of users, module purchased etc.
  4. If the request is valid, it will grab the ERP Pro version number from corresponding Product and all the corresponding module version from this repository [github url](https://github.com/wp-erp/erp-utils/blob/main/extensions.json). 
  5. Both versions will be converted into a hash value and then this value will be compared via already stored value (if any). 
  6. If both version mismatch, a background task will be assigned to create the new zip file. Since this process requires time and later on, this zip needs to be uploaded to the Amazon AWS S3 cloud, user will get a message that zip file is generating if they try to download the zip file from My Account page.
  7. Note that, if a zip file is already exists with the same hash, no new zip will be created and users with same module purchased will be sharing the same zip file.   
  8. After that zip will be available for download and update via WordPress update manager.
  9. Admin can force create zip file from `EDD Downloads --> Subscriptions` subscription edit page. In that case, a new zip will be created and that reference will be updated on our database.


