FROM php:7.2-fpm-buster

# 设定时区
ENV TZ=Asia/Shanghai
RUN ln -snf /usr/share/zoneinfo/$TZ /etc/localtime && echo $TZ > /etc/timezone


RUN echo "deb http://mirrors.aliyun.com/debian/ buster main non-free contrib" > /etc/apt/sources.list \
&& echo "deb-src http://mirrors.aliyun.com/debian/ buster main non-free contrib" >> /etc/apt/sources.list 
#设置apt证书
RUN apt update && apt install -y gnupg apt-utils && apt-key adv --keyserver keyserver.ubuntu.com --recv-keys 648ACFD622F3D138 0E98404D386FA1D9	
RUN echo "deb http://mirrors.aliyun.com/debian-security buster/updates main" >> /etc/apt/sources.list \
&& echo "deb-src http://mirrors.aliyun.com/debian-security buster/updates main" >> /etc/apt/sources.list \
&& echo "deb http://mirrors.aliyun.com/debian/ buster-updates main non-free contrib" >> /etc/apt/sources.list \
&& echo "deb-src http://mirrors.aliyun.com/debian/ buster-updates main non-free contrib" >> /etc/apt/sources.list \
&& echo "deb http://mirrors.aliyun.com/debian/ buster-backports main non-free contrib" >> /etc/apt/sources.list \
&& echo "deb-src http://mirrors.aliyun.com/debian/ buster-backports main non-free contrib" >> /etc/apt/sources.list

RUN apt update && apt install -y  nginx net-tools curl && \
    apt install -y --no-install-recommends  m4 autoconf make gcc g++ linux-headers-amd64 libmemcached-dev zlib1g-dev libfreetype6-dev \
		libjpeg62-turbo-dev libpng-dev libbz2-dev libmcrypt-dev
#安装php扩展
RUN pecl install redis-5.1.1 \
	&& pecl install memcached-3.2.0 \
	&& pecl install mcrypt-1.0.1 \
	&& docker-php-ext-enable redis memcached mcrypt \
	&& docker-php-ext-configure gd
RUN docker-php-ext-install -j$(nproc) pdo_mysql opcache mysqli bcmath gd bz2 calendar zip gettext

#删除安装包，释放空间
RUN apt remove -y m4 autoconf make gcc g++ linux-headers-amd64 

#配置文件覆盖
WORKDIR /data
#COPY php.ini /usr/local/etc/php/
COPY php-fpm.conf /usr/local/etc/php-fpm.d/www.conf
COPY nginx.conf /etc/nginx/nginx.conf
COPY run.sh /data/

RUN mkdir /run/nginx && touch /run/nginx/nginx.pid && \
	chmod 755 /data/run.sh && rm -fr /var/www/html/* && rm -fr /usr/local/etc/php-fpm.d/zz-docker.conf

#复制站点内容进去即可
ADD junbo.haidiao888.com_5GL5Jr.tar.gz /var/www/html

#设置目录权限
RUN chown -R  www-data:www-data /var/www/html/

EXPOSE 80
EXPOSE 9000

CMD ["./run.sh"]