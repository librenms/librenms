# Developing for LibreNMS

Developing for LibreNMS has never been easier.

Thanks to [Wes Kennedy](https://twitter.com/liveanarchist), there is
now a vagrant file that will allow you to install a virtual machine
that has LibreNMS already running on it!

To get started, you can just copy the script from `/contrib/dev_init`,
or you can enter the following commands into your shell:

```
mkdir -p dev/librenms && cd $_
curl -O http://wkennedy.co/uploads/librenms/Vagrantfile
curl -O http://wkennedy.co/uploads/librenms/bootstrap.sh
chmod +x bootstrap.sh
vagrant up
```

This may take a few minutes and requires you to already have Vagrant
installed.  If you don't have Vagrant installed, it's easy to setup.
See the [installation instructions here](http://docs.vagrantup.com/v2/installation/).
