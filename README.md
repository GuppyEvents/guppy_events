## What do you need to run Guppy Events Application with Vagrant ? 
There are a few pre-requisites before you can begin guppy event web application journey.

1. First, you must install the necessary tools. They're easy to get and will only take a minute:
  * [VirtualBox](https://www.virtualbox.org/wiki/Downloads)
  * [Vagrant](https://www.vagrantup.com/downloads.html)

2. Second, you must find vagrant directory which includes _**Vagrantfile**_ & _**puphpet**_ folder then you must call following commands in vagrant directory. (vagrant needs _**vagrant-bindfs**_ plugin for shared folder configuration which is set to nfs)
  * ```vagrant plugin install vagrant-bindfs```
  * ```vagrant up```
  
3. Third, you must add the following hostname to your hosts file (**/etc/hosts**)
  * ```192.168.56.101  local.guppy.com.tr  www.local.guppy.com.tr```

4. Forth … well, that's all you need, really. You can call **local.guppy.com.tr** from your browser to see guppy event web application ;)


<br /><br />
### What do you need to connect VM mysql from your local ?
If you want to connect VM mysql from your local machine, you need to update mysql configurations in VM.

1.  To do this, first you connect to VM.
 * ```vagrant ssh```
 
2. Go to mysql configuration directory
 * ```sudo su```
 * ```cd /etc/mysql/mysql.conf.d```
 
3. Open **mysqld.cnf** and update it
 * ```vi mysqld.cnf```

4. Update following line
 * _bind-address    = 0.0.0.0_
 
5. Restart mysql service 
 * ```service mysql restart```
 
 
<br /><br />
### What do you need to overcome underperforming VM issue ?
In some cases the default shared folder implementations (such as VirtualBox shared folders) have high performance penalties. If you are seeing less than ideal performance with synced folders, NFS can offer a solution.

1. First you should find "config.yaml" file.

2. Second, you must change following setting to _**sync_type: nfs**_ from _**sync_type: default**_.

3. Third, you should restart your vagrant vm.
 * ```vagrant reload```

Ubuntu user may need to run the following command if vagrant returns nfs is not supported error. [See details](https://github.com/mitchellh/vagrant/issues/1941#issuecomment-36072095)
* ```sudo apt-get install nfs-common nfs-kernel-server```



<br /><br />
### How do you update your database after pull project?
In some cases you need to update your local database schema. To do it,

1. First you should go to "YOUR_PROJECT_DIRECTORY/guppy_events/events" directory.
 * ```cd YOUR_PROJECT_DIRECTORY/guppy_events/events```

2. Second, you can check that your database is sync or not by executing following command.
 * ```php app/console doctrine:schema:update```

3. If you need to update your database then call previous command with --force parameter.
 * ```php app/console doctrine:schema:update --force```
 
4. Additionally, you can see sql which executed to update database.
 * ```php app/console doctrine:schema:update --dump-sql```
