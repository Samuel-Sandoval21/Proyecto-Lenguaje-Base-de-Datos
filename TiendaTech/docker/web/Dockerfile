FROM php:8.2-apache

RUN apt-get update && apt-get install -y \
    unzip \
    wget \
    libaio-dev \
    build-essential \
    autoconf

WORKDIR /opt/oracle

# Descargar instant client
RUN wget https://download.oracle.com/otn_software/linux/instantclient/2112000/instantclient-basiclite-linux.x64-21.12.0.0.0dbru.zip \
 && wget https://download.oracle.com/otn_software/linux/instantclient/2112000/instantclient-sdk-linux.x64-21.12.0.0.0dbru.zip

RUN unzip instantclient-basiclite-linux.x64-21.12.0.0.0dbru.zip \
 && unzip instantclient-sdk-linux.x64-21.12.0.0.0dbru.zip \
 && rm *.zip

ENV LD_LIBRARY_PATH=/opt/oracle/instantclient_21_12

# Copiar OCI8 descargado
COPY oci8-3.3.0 /tmp/oci8

# Compilar OCI8 manualmente
RUN cd /tmp/oci8 \
 && phpize \
 && ./configure --with-oci8=instantclient,/opt/oracle/instantclient_21_12 \
 && make \
 && make install \
 && docker-php-ext-enable oci8