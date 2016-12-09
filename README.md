## What do you need to run Guppy Events Application with Vagrant ? 
There are a few pre-requisites before you can begin guppy event web application journey.

1. First, you must install the necessary tools. They're easy to get and will only take a minute:
  * [VirtualBox](https://www.virtualbox.org/wiki/Downloads)
  * [Vagrant](https://www.vagrantup.com/downloads.html)

2. Second, you must find vagrant directory which includes _**Vagrantfile**_ & _**puphpet**_ folder then you must call following command in vagrant directory.
  * ```vagrant up```

4. Third, you must add the following hostname to your hosts file (**/etc/hosts**)
  * ```192.168.56.101  local.guppy.com.tr  www.local.guppy.com.tr```

3. Forth … well, that's all you need, really. You can call **local.guppy.com.tr** from your browser to see guppy event web application ;)
