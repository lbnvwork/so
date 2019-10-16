FROM nginx:1.14.0

ARG server_uid

RUN apt-get update && apt-get install git mc locales -y && \
    apt-get clean && \
    rm -rf /var/lib/apt/lists/* /tmp/* /var/tmp/*

RUN sed -i -e 's/# ru_RU.UTF-8 UTF-8/ru_RU.UTF-8 UTF-8/' /etc/locale.gen && \
    locale-gen
ENV LANG ru_RU.UTF-8
ENV LANGUAGE ru_RU:en
ENV LC_ALL ru_RU.UTF-8

ADD ./default.conf /etc/nginx/conf.d/default.conf
ADD ./fastcgi-php.conf /etc/nginx/snippets/fastcgi-php.conf
ADD ./fastcgi.conf /etc/nginx/fastcgi.conf
ADD ./ssl/keaz.dev.cert /etc/ssl/certs/ssl-cert-snakeoil.pem
ADD ./ssl/keaz.dev.key /etc/ssl/private/ssl-cert-snakeoil.key

RUN usermod -u $server_uid www-data && groupmod -g $server_uid www-data

#WORKDIR /var/www