# == Class: apache
#
# Class defined to create sites and their configuration.
#
class apache {
  package { 'apache2':
    ensure  => present,
    require => Exec['apt-get update']
  }

  # ensures that mode_rewrite is loaded and modifies the default
  # configuration file
  file { '/etc/apache2/mods-enabled/rewrite.load':
    ensure  => link,
    target  => '/etc/apache2/mods-available/rewrite.load',
    require => Package['apache2']
  }

  # create ssl directory for apache certs
  file { '/etc/apache2/ssl':
    ensure => 'directory',
    require => [
      Package['apache2']
    ]
  }

  exec { 'openssl-crt':
    command => 'sudo openssl req -x509 -nodes -days 365 -newkey rsa:2048 -keyout /etc/apache2/ssl/ssl.key -out /etc/apache2/ssl/ssl.crt -subj "/C=US/ST=Wisconsin/L=Madison/O=Earthling Interactive/OU=Dev/CN=hello"',
    require => [
      File['/etc/apache2/ssl'],
      Package['apache2']
    ]
  }

  exec { 'apache2-ssl':
    command => 'sudo a2enmod ssl',
    require => [
      Package['apache2']
    ]
  }

  exec { 'create-temp-vhost':
    creates => '/tmp/apache_temp.conf',
    command => 'cat /vagrant/puppet/manifests/vhost | cat - /etc/apache2/sites-available/000-default.conf > /tmp/apache_temp.conf; chmod 777 /tmp/apache_temp.conf',
    require => Package['apache2']
  }

  exec { 'vhost-setup':
    command => 'sudo mv -f /tmp/apache_temp.conf /etc/apache2/sites-available/000-default.conf',
    require => [
      Exec['create-temp-vhost'],
      Package['apache2']
    ],
    before  => Service['apache2']
  }

  # starts the apache2 service once the packages installed, and
  # monitors changes to its configuration files and reloads if nesessary
  service { 'apache2':
    ensure    => running,
    require   => Package['apache2'],
    subscribe => [
      File['/etc/apache2/mods-enabled/rewrite.load'],
      Exec['vhost-setup'],
      Exec['openssl-crt'],
      Exec['apache2-ssl']
    ],
  }
}
