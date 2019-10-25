#!/bin/bash


grep -R get_traduction *.php > liste_trads.txt

python generate_trad_csv.py liste_trads.txt
 
