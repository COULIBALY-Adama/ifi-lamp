FROM phusion/baseimage

MAINTAINER Coulibaly Adama <coulibaly_p22@ifi.edu.vn>
ENV REFRESHED_AT 2018-08-21


ENV DOCKER_USER_ID 501 
ENV DOCKER_USER_GID 20

ENV BOOT2DOCKER_ID 1000
ENV BOOT2DOCKER_GID 50

# Permission d'écriture Apache/PHP
RUN usermod -u ${BOOT2DOCKER_ID} www-data && \
    usermod -G staff www-data && \
    useradd -r mysql && \
    usermod -G staff mysql

RUN groupmod -g $(($BOOT2DOCKER_GID + 10000)) $(getent group $BOOT2DOCKER_GID | cut -d: -f1)
RUN groupmod -g ${BOOT2DOCKER_GID} staff

# Installation des paquets
ENV DEBIAN_FRONTEND noninteractive
RUN add-apt-repository -y ppa:ondrej/php && \
  apt-key adv --keyserver keyserver.ubuntu.com --recv-keys 4F4EA0AAE5267A6C && \
  apt-get update && \
  apt-get -y upgrade && \
  apt-get -y install supervisor wget git apache2 php-xdebug libapache2-mod-php mysql-server php-mysql pwgen php-apcu php7.0-mcrypt php-gd php-xml php-mbstring php-gettext zip unzip php-zip curl php-curl && \
  apt-get -y autoremove && \
  echo "ServerName localhost" >> /etc/apache2/apache2.conf

# Les dependance de phpMyAdmin
RUN phpenmod mcrypt

# Ajout de configurations d'images
ADD conf/start-apache2.sh /start-apache2.sh
ADD conf/start-mysqld.sh /start-mysqld.sh
ADD conf/run.sh /run.sh
RUN chmod 755 /*.sh
ADD conf/supervisord-apache2.conf /etc/supervisor/conf.d/supervisord-apache2.conf
ADD conf/supervisord-mysqld.conf /etc/supervisor/conf.d/supervisord-mysqld.conf
ADD conf/mysqld_innodb.cnf /etc/mysql/conf.d/mysqld_innodb.cnf

# Configuration de mysql sur le: 0.0.0.0
RUN sed -i "s/.*bind-address.*/bind-address = 0.0.0.0/" /etc/mysql/my.cnf

# Configuration des fuseaux horaires PHP pour Asie/Hanoi
RUN sed -i "s/;date.timezone =/date.timezone = Asia\/Ho_Chi_Minh/g" /etc/php/7.2/apache2/php.ini
RUN sed -i "s/;date.timezone =/date.timezone = Asia\/Ho_Chi_Minh/g" /etc/php/7.2/cli/php.ini

# Suppression de la base de données pré-installée
RUN rm -rf /var/lib/mysql

# Ajout des dependances MySQL
ADD conf/create_mysql_users.sh /create_mysql_users.sh
RUN chmod 755 /*.sh

# Ajout de phpmyadmin
ENV PHPMYADMIN_VERSION=4.8.2
RUN wget -O /tmp/phpmyadmin.tar.gz https://files.phpmyadmin.net/phpMyAdmin/${PHPMYADMIN_VERSION}/phpMyAdmin-${PHPMYADMIN_VERSION}-all-languages.tar.gz
RUN tar xfvz /tmp/phpmyadmin.tar.gz -C /var/www
RUN ln -s /var/www/phpMyAdmin-${PHPMYADMIN_VERSION}-all-languages /var/www/phpmyadmin
RUN mv /var/www/phpmyadmin/config.sample.inc.php /var/www/phpmyadmin/config.inc.php

# Ajout de composer
RUN php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');" && \
    php composer-setup.php && \
    php -r "unlink('composer-setup.php');" && \
    mv composer.phar /usr/local/bin/composer

ENV MYSQL_PASS:-$(pwgen -s 12 1)
# ENV ALLOW_EMPTY_PASSWORD="no" \
#    MYSQL_ROOT_PASSWORD="root" \ 
#    MYSQL_ROOT_USER="root"

# Configuration pour activer .htaccess
ADD conf/apache_default /etc/apache2/sites-available/000-default.conf
RUN a2enmod rewrite

# Dossier de configuration /app avec exemple d'application
RUN rm -fr /app && git clone https://github.com/COULIBALY-Adama/IFI-EmploiDuTemps.git /app
RUN mkdir -p /app && rm -fr /var/www/html && ln -s /app /var/www/html

#ADD app/ /app

# Dossier de configuration /mysql avec exemple de mysql
# RUN rm -fr /mysql && git clone https://github.com/COULIBALY-Adama/ifi-database.git /mysql
# RUN mkdir -p /mysql && rm -fr /var/lib/mysql && ln -s /mysql /var/lib/mysql

# Variables d'environnement pour la configuration de php
ENV PHP_UPLOAD_MAX_FILESIZE 10M
ENV PHP_POST_MAX_SIZE 10M

# Ajout de volumes pour les applications et la base de données MySql
VOLUME  ["/etc/mysql", "/var/lib/mysql"]

EXPOSE 80 3306
CMD ["/run.sh"]
