# Melis CMS Page Analytics

Offers basic page analytics reporting inside Melis Platform.

## Getting started

These instructions will get you a copy of the project up and running on your machine.

### Prerequisites

The following modules need to be installed to have Melis CMS Google Analytics module run:
* Melis core
* Melis engine
* Melis CMS
* Melis front
 
### Installing

Run the composer command:
```
composer require melisplatform/melis-cms-page-analytics
```

### Database    

Database model is accessible via the MySQL Workbench file:  
```
/melis-cms-page-analytics/install/sql/model
```  
Database will be installed through composer and its hooks.  
In case of problems, SQL files are located here:  
```
/melis-cms-page-analytics/install/sql  
```

## Tools and elements provided
* Page analytics tool
  - user can choose any of the available analytics module as their site's analytics tool
  - provides statistics such as visitor counts, date visited, etc.

* Page analytics tab inside the Melis CMS page system
 - Provides page specific statistics that the tool provides.
 
## Authors

* **Melis Technology** - [www.melistechnology.com](https://www.melistechnology.com/)

See also the list of [contributors](https://github.com/melisplatform/melis-cms/contributors) who participated in this project.


## License

This project is licensed under the OSL-3.0 License - see the [LICENSE.md](LICENSE.md) file for details