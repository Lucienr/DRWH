# DRWH
Dr Warehouse - document oriented biomedical data warehouse

Pre requisite :

Oracle 11g or more, with Oracle text
PHP 5.3 and + with modules :
- OCI8 - https://github.com/imagine-bdd/DRWH/wiki/How-to-install-OCI8-on-Ubuntu-18.04-and-PHP-7.2
- LDAP
- CURL

You will need highstock (There is Non-commercial licence on their website)
	https://www.highcharts.com/products/highstock/
	Version tested in Dr Warehouse is Highstock JS v2.0.4.
The directory highstock must be in the dwh directory

Some directories must be writable for the www-user :
- json_map
- upload
- timeline/xml

The SQL scripts for database creation are in the database directory.


Please contact Nicolas Garcelon :
nicolas.garcelon@institutimagine.org
Imagine Institute
24 boulevard Montparnasse
75015 Paris
France

Demonstration available : http://www.drwarehouse.org/

VERSION=3.0.5-DEV
Edited the 20200831131919
