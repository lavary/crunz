# -*- mode: ruby -*-
# vi: set ft=ruby :

$composer = <<SCRIPT

COMPOSER_VERSION="1.7.2";
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

    config.vm.provision "shell" do |s|
        s.path = "vagrant/install-php.sh"
        s.args = ["5.6"]
    end

    config.vm.provision "shell" do |s|
        s.path = "vagrant/install-php.sh"
        s.args = ["7.1"]
    end

    config.vm.provision "shell", inline: $composer, privileged: false
end
