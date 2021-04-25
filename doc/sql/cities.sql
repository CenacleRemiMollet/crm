LOAD DATA LOCAL INFILE 'doc/sql/eucircos.csv'
 INTO TABLE city
 CHARACTER SET UTF8
 FIELDS TERMINATED BY ';'
 LINES TERMINATED BY '\n'
 IGNORE 1 ROWS
 (@dummy, @dummy, @dummy, @dummy, county_number, @dummy, @dummy, @dummy, city_name, zip_code, @dummy, latitude, longitude, @dummy);


