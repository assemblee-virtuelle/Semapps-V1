# MMMFest carto engine 

[![Build Status](https://travis-ci.org/assemblee-virtuelle/mmmfest.svg?branch=master)](https://travis-ci.org/assemblee-virtuelle/mmmfest) <a href="https://codeclimate.com/github/assemblee-virtuelle/mmmfest"><img src="https://codeclimate.com/github/assemblee-virtuelle/mmmfest/badges/gpa.svg" /></a>

A Symfony project created on February 1, 2017, 12:13 pm.

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

- First clone the project wherever you wish
- Then `cd yourdirectory/mmmfest`
- Then `npm install`
- Then `bower install`
- Then `composer install`
- Then access your [Semantic Forms install](http://localhost:9000) and create an account

And everything should be there, still needs to load database schema and stuff ! For this, do the following in the console :

- `php bin/console server:run` to launch the app server, it will ask you some information such as the Semantic Forms instance URL and the associated user
- `php bin/console doctrine:schema:create` to create the database schema in your MySQL install

We will init some data using the console too :

- `php bin/console mmmfest:create:orga` to create a registered organisation with an admin user
- The previous command gives you activation link and randomly generated password for the new user

If you get some errors trying to activate this new user, like the following message : `Form field not found into specification firstName` then you will need to load the mmmfest specific ontology into your Semantic Forms instance. To do as such :

- Access your [Semantic Forms install](http://localhost:9000)
- Log in if you are not yet
- Then in the standard form, load by copying and pasting into the "Display" field the following URLs:
  - http://assemblee-virtuelle.github.io/mmmfest/mm-forms.ttl
  - http://assemblee-virtuelle.github.io/mmmfest/PAIR_temp.owl

Click the "Display" button for both and then try accessing the profile page again and the issue should be solved !

### Instance deployment

[...]

