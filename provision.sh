# Used to provision!

set -euo pipefail


#### Install some packages needed by later steps

apt-get install -y wget


#### Set up Postgres

postgres_password='REJ#%*OfdaklJ*O4t5eH'

wget -q -O - https://www.postgresql.org/media/keys/ACCC4CF8.asc | apt-key add -
cat \
    /etc/apt/sources.list.d/postgresql.list \
    <(echo "deb http://apt.postgresql.org/pub/repos/apt/ trusty-pgdg main") \
    | uniq > /tmp/postgresql.list
mv /tmp/postgresql.list /etc/apt/sources.list.d/postgresql.list

apt-get update -y
apt-get install -y postgresql-9.3 postgresql-contrib postgresql-9.3-postgis-2.1

service postgresql start

sed -i 's/127.0.0.1\/32/0.0.0.0\/0/g' /etc/postgresql/9.3/main/pg_hba.conf
sed -i "s/#listen_addresses = 'localhost'/listen_addresses = '*'/g" /etc/postgresql/9.3/main/postgresql.conf

sudo -u postgres psql -U postgres -d postgres -c "alter user postgres with password '${postgres_password}';"


apt-get install -y vim make openjdk-7-jdk git zip emacs24-nox screen


#### Set up Apache (and install PHP while we're at it)

apt-get install -y apache2 \
    php5 php5-cli php5-curl php5-mcrypt php5-dev php5-pgsql php5-gd libapache2-mod-php5 php5-tidy \
    python-software-properties

a2enmod rewrite
#ln -s /etc/apache2/mods-available/rewrite.load /etc/apache2/mods-enabled/rewrite.load
a2enmod ssl

mkdir -p /etc/apache2/ssl
openssl req -x509 -nodes -days 365 -newkey rsa:2048 \
    -keyout /etc/apache2/ssl/ssl.key \
    -out /etc/apache2/ssl/ssl.crt \
    -subj "/C=US/ST=Wisconsin/L=Madison/O=Earthling Interactive/OU=Dev/CN=hello" \
    2>/dev/null # Otherwise it is tooo noisy!

cp /vagrant/vm-files/vhost /etc/apache2/sites-available/000-default.conf
chmod 0600 /etc/apache2/sites-available/000-default.conf


#### Fix some PHP INI settings

sed -i \
    -e 's/upload_max_filesize = [0-9]\+M/upload_max_filesize = 100M/g' \
    -e 's/post_max_size = [0-9]\+M/post_max_size = 110M/g' \
    -e 's/; max_input_vars = [0-9]\+/max_input_vars = 1000000/g' \
    /etc/php5/apache2/php.ini

wget -q 'https://getcomposer.org/installer' -O - | php
mv composer.phar /usr/local/bin/composer



#### Start apache

service apache2 start
