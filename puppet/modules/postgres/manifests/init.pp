# == Class: postgres
#
# Install other support packages needed
#
class postgres {
  $packages = [ 'postgresql-9.3', 'postgresql-contrib', 'postgresql-9.3-postgis']
  $password = 'REJ#%*OfdaklJ*O4t5eH'

  exec { 'add-postgresql-repo-key':
    creates => '/etc/apt/trusted.gpg.d/apt.postgresql.org.gpg',
    command => 'wget --quiet -O - https://www.postgresql.org/media/keys/ACCC4CF8.asc | sudo apt-key add -',
  }

  exec { 'add-postgresql-repo':
    creates => '/etc/apt/sources.list.d/postgresql.list',
    command => 'sudo sh -c \'echo "deb http://apt.postgresql.org/pub/repos/apt/ precise-pgdg main" >> /etc/apt/sources.list.d/postgresql.list\''
  }

  package { $packages:
    ensure  => present,
    require => [
      Exec['apt-get update'],
      Exec['add-postgresql-repo-key'],
      Exec['add-postgresql-repo']
    ]
  }

  exec { 'set-postgres-password':
    command => "sudo -u postgres psql -U postgres -d postgres -c \"alter user postgres with password '${password}';\"",
    require => Package['postgresql-9.3']
  }

  exec { 'update-pg_hba.conf':
    creates => '/tmp/postgres_temp',
    command => "sudo cat /etc/postgresql/9.3/main/pg_hba.conf | sed 's/127.0.0.1\\/32/0.0.0.0\\/0/g' > /tmp/postgres_temp && sudo mv /tmp/postgres_temp /etc/postgresql/9.3/main/pg_hba.conf",
    require => [
      Package['postgresql-9.3']
    ],
    notify  => Service['postgresql']
  }

  exec { 'update-postgresql.conf':
    creates => '/tmp/postgresqlconf_temp',
    command => 'sudo cat /etc/postgresql/9.3/main/postgresql.conf | sed "s/#listen_addresses = \'localhost\'/listen_addresses = \'*\'/g" > /tmp/postgresqlconf_temp && sudo mv /tmp/postgresqlconf_temp /etc/postgresql/9.3/main/postgresql.conf',
    require => [
      Package['postgresql-9.3']
    ],
    notify  => Service['postgresql']
  }


  # listen_addresses = 'localhost' listen_addresses = '*'

  # starts the postgresql service once the packages installed, and
  # monitors changes to its configuration files and reloads if nesessary
  service { 'postgresql':
    ensure    => running,
    require   => Package['postgresql-9.3'],
    subscribe => [
      Exec['update-pg_hba.conf'],
      Exec['update-postgresql.conf']
    ],
  }
}
