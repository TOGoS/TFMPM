# == Class: phpdev
#
# Install packages needed for php development
#
class phpdev {
  $packages = [ 'php5', 'php5-cli', 'php5-curl', 'php5-mcrypt', 'php5-dev',
    'php5-pgsql', 'php5-gd', 'libapache2-mod-php5', 'php5-tidy']
  
  package { 'python-software-properties':
    ensure  => present,
    require => Exec['apt-get update']
  }

  exec { 'add-apt-repository-php55-channel':
    creates => '/etc/apt/sources.list.d/ondrej-php5-precise.list',
    command => 'sudo add-apt-repository ppa:ondrej/php5 -y',
    require => Package['python-software-properties']
  }

  exec { 'apt-update':
    command => '/usr/bin/apt-get update',
    require => Exec['add-apt-repository-php55-channel'],
    before  => Package['php5']
  }

  package { $packages:
    ensure  => present,
    require => [
      Exec['apt-get update'],
      Exec['add-apt-repository-php55-channel']
    ]
  }

  exec { 'upload-max-filesize':
    creates => '/tmp/phpini_tmp',
    command => "sudo cat /etc/php5/apache2/php.ini | sed 's/upload_max_filesize = .M/upload_max_filesize = 100M/g' | sed 's/post_max_size = .M/post_max_size = 110M/' | sed 's/; max_input_vars = [0-9]\\+/max_input_vars = 1000000/' > /tmp/phpini_tmp && sudo mv /tmp/phpini_tmp /etc/php5/apache2/php.ini",
    require => [
      Package['php5'],
      Package['apache2']
    ],
    notify  => Service['apache2']
  }

  exec { 'install-composer':
    creates => '/usr/local/bin/composer',
    command => 'curl -sS https://getcomposer.org/installer | php; sudo mv composer.phar /usr/local/bin/composer',
    require => [
      Package['php5'],
      Package['php5-curl']
    ]
  }
}
