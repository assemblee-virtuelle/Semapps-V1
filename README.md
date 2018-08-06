# Semapps carto engine

## Installation

### For developers

#### Prerequesites 

In order to contribute, you need to have 
- [Composer](https://getcomposer.org "Composer")
- [NPM](https://www.npmjs.com/ "NPM")
- [Bower](https://bower.io/ "Bower")
- PHP >= 5.6
- [Semantic Forms up and running](https://github.com/jmvanel/semantic_forms/wiki/User_manual
 "Bower")
 - MySQL server
 
#### Installation steps

##### Dependencies

###### Installing Semantic Forms

- Ensure that Java JRE 8 is installed, on Debian: `sudo apt-get install default-jre`
- Download the zip file of version 2.0 of Semantic Forms (SF) from its [repository](https://github.com/jmvanel/semantic_forms/releases)
- Unzip and change directory to SF: `cd semantic_forms_play-1.0-SNAPSHOT`
- Copy the start script to the current directory: `cp scripts/start.sh .`
- One can change the port used by the SF server by changing the `PORT` variable in `start.sh` (9111 by default)
- Give the execution permission to ```start.sh``` and `bin/semantic_forms_play` and run `start.sh` to start the SF server:
```bash
chmod +x start.sh
chmod +x bin/semantic_forms_play
./start.sh
```
- To verify that Semantic Form is running, try to visit the port 9111 (or the port you have configured in `start.sh` of your localhost in your web browser (e.g. <http://localhost:9111> or <http://127.0.0.1:9111>)


###### Configuring MySQL

- Install the MySQL client and server, run the MySQL server.
- From root, create a MySQL user as you want, create a database for symfony and grant the permissions to that user for this database. For example, to create a user `admin` with privileges on a database `symfony`:
```bash
mysql -u root -p
Enter password:
mysql> CREATE USER 'admin'@'localhost' IDENFIED BY 'yourpassword';
mysql> CREATE DATABASE symfony;
mysql> GRANT ALL PRIVILEGES ON symfony.* TO 'admin'@'localhost' WITH GRANT OPTION;
```
- MacPorts users may need to comment the line `!include /opt/local/etc/mysql57/macports-default.cnf` in `/opt/local/etc/mysql57/my.cnf` and to configure the default port by adding there:
```bash
[mysqld]
port = 3306
```

###### NPM, Bower and Composer and the SemApps dependencies
- Install NPM and Composer. On Debian, `sudo apt-get install npm composer`. If the Composer is not packaged, install it manually to a binary directory `INSTALLDIR` in your `PATH`:
```bash
php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');"
php -r "if (hash_file('SHA384', 'composer-setup.php') === '544e09ee996cdf60ece3804abc52599c22b1f40f4323403c44d44fdfdd586475ca9813a858088ffbc1f233e9b180f061') { echo 'Installer verified'; } else { echo 'Installer corrupt'; unlink('composer-setup.php'); } echo PHP_EOL;"
export INSTALLDIR=$HOME/local/bin
php composer-setup.php --install-dir=$INSTALLDIR --filename=composer
php -r "unlink('composer-setup.php');"
```
- Clone the project wherever you wish, `git clone https://github.com/assemblee-virtuelle/Semapps`
- Change directory to Semapps, `cd yourdirectory/Semapps`
- Install npm dependencies in this directory, `npm install`
- Install some dependencies `bower install` or `node_modules/bower/bin/bower install`
- Install some other dependencies `composer install`. When asked, provide:
  * The host for the database
  * The database port
  * The database name, e.g. `symfony` to match the above [MySQL configuration](#configuring-mysql)
  * The database user name, e.g. `admin` to match the above [MySQL configuration](#configuring-mysql), and the password
  * The mail transporter, host, user and password
  * The domain name for SemApps
  * A secret token
  * The domain name of Semantic Forms (<127.0.0.1:9111>, if installed locally on the default port)
  * The administrator user name for Semantic Forms and the password
  * The maximum time (in seconds) to wait for a reply from Semantic Forms
  * The main components of the ontology (keep the default value to use the PAIR ontology)
  * A special alert message
- You can configure this manually in `app/config/parameters.yml`
- Then access your [Semantic Forms install](http://localhost:9000) and create an account

##### set the logo
- go to `cd yourdirectory/semapps/web/common/images/`
- place here a file named `logo.png`
- actualize your webpage

##### start coding
- Create the SQL database: `php bin/console doctrine:schema:create`
- Create the first user: `php bin/console semapps:create:user`
- Run the server: `php bin/console server:run`
[...]

### Instance deployment

[...]

## The SemApps architecture

A summary of which modules do what, where are the files.

## Implemanting a new ontology



## Instances of Semapps

- [La cartographie des Grands Voisins](http://reseau.lesgrandsvoisins.org/)
  - [Presentation of the project](https://www.virtual-assembly.org/appli-carto-grands-voisins/) (French)
  - [Repository](https://github.com/assemblee-virtuelle/grands-voisins-v2)
