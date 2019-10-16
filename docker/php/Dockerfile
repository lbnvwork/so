FROM ubuntu:18.10

ARG server_uid

ENV TZ=Europe/Moscow
RUN ln -snf /usr/share/zoneinfo/$TZ /etc/localtime && echo $TZ > /etc/timezone

ENV DEBIAN_FRONTEND="noninteractive"

RUN apt-get clean && apt-get update && \
    apt-get install -y php-fpm mysql-client locales sudo golang-go \
    php-mysql php-pear php-mbstring php-intl php-imagick php-curl php-soap php-zip php-xml php-gd  \
    php-xdebug wget git zip mc curl && \
    apt-get clean && \
    rm -rf /var/lib/apt/lists/* /tmp/* /var/tmp/*

RUN sed -i -e 's/# ru_RU.UTF-8 UTF-8/ru_RU.UTF-8 UTF-8/' /etc/locale.gen && \
    locale-gen
ENV LANG ru_RU.UTF-8
ENV LANGUAGE ru_RU:en
ENV LC_ALL ru_RU.UTF-8

RUN go get github.com/mailhog/mhsendmail
RUN cp /root/go/bin/mhsendmail /usr/bin/mhsendmail

#ADD  www.conf /etc/php/7.2/fpm/pool.d/www.conf
ADD php.ini /etc/php/7.2/fpm/php.ini
ADD ./xdebug.ini /etc/php/7.2/cli/conf.d/xdebug.ini
ADD ./xdebug.ini /etc/php/7.2/fpm/conf.d/xdebug.ini

RUN sed -i -e "s/pid =.*/pid = \/var\/run\/php7.2-fpm.pid/" /etc/php/7.2/fpm/php-fpm.conf
RUN sed -i -e "s/error_log =.*/error_log = \/proc\/self\/fd\/2/" /etc/php/7.2/fpm/php-fpm.conf
RUN sed -i -e "s/;daemonize\s*=\s*yes/daemonize = no/g" /etc/php/7.2/fpm/php-fpm.conf
RUN sed -i "s/listen = .*/listen = 9000/" /etc/php/7.2/fpm/pool.d/www.conf
RUN sed -i "s/;catch_workers_output = .*/catch_workers_output = yes/" /etc/php/7.2/fpm/pool.d/www.conf

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

ADD entry-point.sh /

ENTRYPOINT ["/entry-point.sh"]

EXPOSE 9000

CMD ["php-fpm7.2"]