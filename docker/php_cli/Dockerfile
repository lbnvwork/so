FROM ubuntu:18.10

ARG server_uid

ENV TZ=Europe/Moscow
RUN ln -snf /usr/share/zoneinfo/$TZ /etc/localtime && echo $TZ > /etc/timezone

RUN apt-get update && \
    apt-get install -y mysql-client locales wget git zip mc curl golang-go sudo \
    php-cli php-mysql php-pear php-mbstring php-intl php-imagick php-curl php-soap php-zip php-xml php-gd \
    php-xdebug  && \
    apt-get clean && \
    rm -rf /var/lib/apt/lists/* /tmp/* /var/tmp/*

RUN sed -i -e 's/# ru_RU.UTF-8 UTF-8/ru_RU.UTF-8 UTF-8/' /etc/locale.gen && \
    locale-gen
ENV LANG ru_RU.UTF-8
ENV LANGUAGE ru_RU:en
ENV LC_ALL ru_RU.UTF-8

RUN go get github.com/mailhog/mhsendmail
RUN cp /root/go/bin/mhsendmail /usr/bin/mhsendmail

ADD ./php.ini /etc/php/7.2/cli
ADD ./xdebug.ini /etc/php/7.2/cli/conf.d/xdebug.ini

WORKDIR /var/www/html

# Установка composer
RUN mkdir -m 777 /var/www/.composer
ENV COMPOSER_HOME=/var/www/.composer

RUN usermod -d /var/www -u $server_uid www-data && groupmod -g $server_uid www-data

RUN wget -O /var/www/.composer/cacert.pem https://curl.haxx.se/ca/cacert.pem
RUN curl -sS https://getcomposer.org/installer | php -- \
        --filename=composer \
        --cafile=/var/www/.composer/cacert.pem \
        --install-dir=/usr/local/bin && \
#        echo "alias composer='composer'" >> /root/.bashrc && \
        sudo -E -u www-data composer diagnose

EXPOSE 80 443

COPY ./docker-php-entrypoint.sh /usr/local/bin/
ENTRYPOINT ["docker-php-entrypoint.sh"]
CMD ["php", "-a"]