DELETE FROM configuration_property;

LOAD DATA LOCAL INFILE 'doc/sql/config.properties'
 INTO TABLE configuration_property
 CHARACTER SET UTF8
 FIELDS TERMINATED BY ':'
 LINES TERMINATED BY '\n'
 (property_key, property_value)
 SET updated_date = CURRENT_TIMESTAMP;

UPDATE configuration_property
 SET property_key = replace(replace(trim(property_key), '\n', ''), '\r', ''),
     property_value = replace(replace(trim(property_value), '\n', ''), '\r', '');

DELETE FROM configuration_property
 WHERE property_key = ''
  OR property_key LIKE '#%';
