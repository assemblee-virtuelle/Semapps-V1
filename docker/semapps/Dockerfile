FROM php:7.1-apache

# add necesary libraries and php extensions
RUN apt-get update \
    && apt-get install -y \
        libcurl3 \
        libcurl3-dev \
        libfreetype6-dev \
        libjpeg62-turbo-dev \
        libpcre3-dev \
        libpng-dev \
        libexif-dev \
        libgmp-dev \
        git \
        libicu-dev \
    && docker-php-ext-configure gd --with-freetype-dir=/usr/include/ --with-jpeg-dir=/usr/include/ \
    && docker-php-ext-configure gmp \
    && docker-php-ext-configure intl \
    && docker-php-ext-install \
        curl \
        mbstring \
        gd \
        exif \
        mysqli \
        pdo \
        pdo_mysql \
        zip \
        gmp \
        intl

# install Composer
RUN \
    COMPOSER_SIG=$(curl -L https://composer.github.io/installer.sig) \
    && php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');" \
    && php -r "if (hash_file('SHA384', 'composer-setup.php') === '$COMPOSER_SIG') { echo 'Installer verified'; } else { echo 'Installer corrupt'; unlink('composer-setup.php'); } echo PHP_EOL;" \
    && php composer-setup.php \
    && php -r "unlink('composer-setup.php');" \
    && mv composer.phar /usr/local/bin/composer

COPY ./php.ini /usr/local/etc/php/

COPY ./apache.conf /etc/apache2/sites-available/semapps.conf
RUN a2ensite semapps
## remove default site, not serving anything anyway
RUN a2dissite 000-default

# enable necesary Apache modules
RUN a2enmod deflate
RUN a2enmod rewrite
RUN a2enmod headers
RUN a2enmod proxy
RUN a2enmod proxy_connect
RUN a2enmod proxy_http
RUN a2enmod proxy_wstunnel

# NPM
curl -sL https://deb.nodesource.com/setup_11.x | bash -
apt-get install -y nodejs

# Yarn
RUN apt-get install -y gnupg
RUN curl -sS http://dl.yarnpkg.com/debian/pubkey.gpg | apt-key add -
RUN echo "deb http://dl.yarnpkg.com/debian/ stable main" | tee /etc/apt/sources.list.d/yarn.list
RUN apt-get update && apt-get install -y yarn

WORKDIR /var/www

COPY ./entrypoint.sh /usr/local/bin/

CMD entrypoint.sh