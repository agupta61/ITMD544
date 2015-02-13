#!/bin/bash

cd /home/ubuntu/
sudo apt-get -y update
sudo wget http://54.86.57.215/composer.json.gz
gunzip composer.json.gz
sudo apt-get -y install apache2 php5-curl git wget
sudo apt-get -y install php5-cli
sudo apt-get -y install php5-gd
sudo curl -sS https://getcomposer.org/installer | php
sudo php composer.phar install
sudo apt-get install -y php5 libapache2-mod-php5 php5-mcrypt
sudo apt-get install -y php5-mysql
sudo service apache2 restart

sudo wget http://54.86.57.215/index.php.gz
sudo wget http://54.86.57.215/result.php.gz
sudo wget http://54.86.57.215/gallery.php.gz

sudo gunzip index.php.gz
sudo gunzip result.php.gz
sudo gunzip gallery.php.gz

sudo chmod 777 -R /var
sudo mv composer.json /var/www/html
sudo mv index.php /var/www/html
sudo mv result.php /var/www/html
sudo mv gallery.php /var/www/html
sudo cp -R vendor /var/www/html
sudo mkdir /var/www/html/uploads
sudo chmod 777 -R /var/www/html/vendor
sudo chmod 777 -R /var/www/html/uploads
sudo file -s /dev/xvdb
sudo mkfs -t ext4 /dev/xvdb
sudo mkdir mountPointA
sudo mount /dev/xvdb mountPointA
