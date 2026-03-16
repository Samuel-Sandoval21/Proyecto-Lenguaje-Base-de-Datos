FROM php:8.2-apache

RUN apt-get update && apt-get install -y \
    unzip \
    wget \
    build-essential \
    autoconf \
    && echo "deb http://deb.debian.org/debian bookworm main" >> /etc/apt/sources.list \
    && apt-get update \
    && apt-get install -y libaio1 \
    && rm -rf /var/lib/apt/lists/*

WORKDIR /opt/oracle

# Descargar Oracle Instant Client
RUN wget -q https://download.oracle.com/otn_software/linux/instantclient/2112000/instantclient-basiclite-linux.x64-21.12.0.0.0dbru.zip \
 && wget -q https://download.oracle.com/otn_software/linux/instantclient/2112000/instantclient-sdk-linux.x64-21.12.0.0.0dbru.zip \
 && unzip -q instantclient-basiclite-linux.x64-21.12.0.0.0dbru.zip \
 && unzip -q instantclient-sdk-linux.x64-21.12.0.0.0dbru.zip \
 && rm *.zip

# Registrar librerías
RUN echo /opt/oracle/instantclient_21_12 > /etc/ld.so.conf.d/oracle.conf && ldconfig

ENV LD_LIBRARY_PATH=/opt/oracle/instantclient_21_12

# Instalar OCI8
RUN echo "instantclient,/opt/oracle/instantclient_21_12" | pecl install oci8 \
 && docker-php-ext-enable oci8

# Verificar que OCI8 quedó activo
RUN php -m | grep oci

COPY . /var/www/html/