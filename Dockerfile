FROM node:20-alpine AS frontend-builder

WORKDIR /app

COPY package.json package-lock.json ./
RUN npm install


COPY . .
RUN npm run build



FROM php:8.3-apache


RUN apt-get update \
    && apt-get install -y libsqlite3-dev \
    && docker-php-ext-install pdo_sqlite \
    && apt-get clean 
RUN a2enmod rewrite


WORKDIR /var/www/html


COPY ./src .


COPY --from=frontend-builder /app/public/style.css ./dist/output.css

EXPOSE 80