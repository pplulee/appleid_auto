FROM php:7.4-cli
RUN docker-php-ext-install mysqli
COPY ./ /usr/src/myapp
WORKDIR /usr/src/myapp
EXPOSE 80
CMD [ "php", "-S", "0.0.0.0:80", "-t", "/usr/src/myapp" ]