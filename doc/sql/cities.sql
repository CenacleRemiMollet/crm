LOAD DATA LOCAL INFILE 'doc/sql/eucircos.csv'
 INTO TABLE city
 CHARACTER SET UTF8
 FIELDS TERMINATED BY ';'
 LINES TERMINATED BY '\n'
 IGNORE 1 ROWS
 (@dummy, @dummy, @dummy, @dummy, county_number, @dummy, @dummy, @dummy, city_name, zip_code, @dummy, latitude, longitude, @dummy);


-- DELETE FROM city
--  WHERE id IN (
--   SELECT city.id
--    FROM city
--     LEFT OUTER JOIN (
--     SELECT min(id) AS id, zip_code, city_name
--       FROM city
--       GROUP BY zip_code, city_name
--     ) AS uniq ON city.id = uniq.id
--    WHERE uniq.id IS NULL
--  );
