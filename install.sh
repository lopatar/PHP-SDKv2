#!/bin/bash
echo "Installing..."
mkdir App public
echo "Created App & public folders"
mkdir App/Controllers App/Views App/Models
echo "Created App/Controllers, App/Views & App/Models folders"

cp Config.sample.php App/Config.php
echo "Copied Config.sample.php to App/Config.php, edit it as you wish!"
cp index.sample.php public/index.php
echo "Copied index.sample.php to public/index.php, please point your web server to this file!"

echo "Running composer install"
composer install
echo "DONE!"