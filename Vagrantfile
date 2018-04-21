# -*- mode: ruby -*-
# vi: set ft=ruby :

$script = <<SCRIPT

export DEBIAN_FRONTEND=noninteractive;

PHP_VERSION="5.6";

## Add Onrej PPA for php 5.6 - 7.2
add-apt-repository -y -u ppa:ondrej/php;

apt-get install git \
    make \
    "php$PHP_VERSION" \
    "php$PHP_VERSION-cli" \
    "php$PHP_VERSION-common" \
    "php$PHP_VERSION-zip" \
    "php$PHP_VERSION-mbstring" \
    "php$PHP_VERSION-opcache" \
    "php$PHP_VERSION-xml" \
    "php$PHP_VERSION-intl" -y

PHP_INI_PATH="/etc/php/$PHP_VERSION/cli/conf.d/99-user.ini";

touch /var/log/php_error.log
chown vagrant:vagrant /var/log/php_error.log

touch "$PHP_INI_PATH";

echo "display_errors = On" >> "$PHP_INI_PATH";
echo "display_startup_errors = On" >> "$PHP_INI_PATH";
echo "log_errors = On" >> "$PHP_INI_PATH";
echo "error_log = /var/log/php_error.log" >> "$PHP_INI_PATH";

SCRIPT

$composer = <<SCRIPT

COMPOSER_VERSION="1.6.4";
BIN_DIR="/home/vagrant/bin";
COMPOSER_FILE="$BIN_DIR/composer";

mkdir -p "$BIN_DIR" &&

wget -q -O "$COMPOSER_FILE" "https://getcomposer.org/download/$COMPOSER_VERSION/composer.phar" &&

chmod +x "$COMPOSER_FILE";

SCRIPT

Vagrant.configure("2") do |config|
    config.vm.box = "ubuntu/xenial64"

    config.vm.network :private_network, ip: "192.168.3.25"

    config.vm.synced_folder ".", "/vagrant",
        mount_options: ["actimeo=1"],
        nfs: true,
        linux__nfs_options: ["rw", "no_subtree_check", "all_squash", "async"]

    if Vagrant::Util::Platform.windows? then
        config.winnfsd.uid = 1000
        config.winnfsd.gid = 1000
    end

    config.vm.provider "virtualbox" do |vb|
        vb.memory = "1536"
        vb.cpus = 2
        vb.name = "crunz-dev"
    end

    config.vm.provision "shell", inline: $script
    config.vm.provision "shell", inline: $composer
end
