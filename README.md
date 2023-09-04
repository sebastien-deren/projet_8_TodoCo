ToDoList
========

Base du projet #8 : Améliorez un projet existant

https://openclassrooms.com/projects/ameliorer-un-projet-existant-1


# Projet_8_TODO&Co

Projet 8 d'openclassrooms, mise à jour d'un POC de todo app, implémentation de sécurité et création de documentation

## Prerequisites
- Docker
- Docker compose
- PHP 8.2

*All the command below are to be used in the root folder of the project.*

## Setup:
If you have make installed:  
- simply use `make install`    
  :warning: docker can take some time to create the DB, if you run into some error, fallback to the step by step setup :warning:

### Step by step installation:
-start the symfony project with :  
`docker compose up -d`  
`composer install`  
`symfony serve -d`  

-then create the database and the migrations.

`symfony console doctrine:database:create --if-not-exists`  
`symfony console doctrine:migration:migrate`  

-finally load the fixtures with:  
`symfony console doctrine:fixtures:load`  

## Usage:

 
You can create new User within our site you have to be admin to edit users or make a new user admin. 

>Admin:
>username: "admin"  
>password: "password"

You have access to the project documentation and Quality/perfomance audit in the linked PDF.


