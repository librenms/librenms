# -*- mode: ruby -*-
# vi: set ft=ruby :

# All Vagrant configuration is done below. The "2" in Vagrant.configure
# configures the configuration version (we support older styles for
# backwards compatibility). Please don't change it unless you know what
# you're doing.
#system("
#    if [ #{ARGV[0]} = 'up' ]; then
#        ./requirements.sh
#    fi
#")


Vagrant.configure(2) do |config|

  # The most common configuration options are documented and commented below.
  # For a complete reference, please see the online documentation at
  # https://docs.vagrantup.com.

  # Every Vagrant development environment requires a box. You can search for
  # boxes at https://atlas.hashicorp.com/search.
  config.vm.box = "ubuntu/trusty64"

  config.vm.define :node1 do |node1|
    #
    # Networking
    #
    node1.vm.network 'private_network', ip: '192.168.33.10'
    #
    # VM Setup
    #
    # Set the hostname to something useful
    node1.vm.hostname = 'influxdb-node1'
    node1.vm.define :influxdb_node1, {}

    node1.vm.provision 'ansible' do |ansible|
      ansible.playbook = 'ansible/main.yml'
      ansible.tags = ENV['ANSIBLE_TAGS'] unless ENV['ANSIBLE_TAGS'].to_s.empty?
      ansible.sudo = true
    end
  end
end
