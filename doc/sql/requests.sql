-- #################### SEARCH ####################

-- USER (all)
SELECT 'user', id, uuid, CONCAT(lastname, ' ', firstname) AS name
 FROM user
 WHERE (remove_accents(lower(lastname)) LIKE :query 
    OR remove_accents(lower(firstname)) LIKE :query
    OR remove_accents(lower(mails)) LIKE :query
    OR remove_accents(lower(address)) LIKE :query
    OR remove_accents(lower(city)) LIKE :query
    OR remove_accents(lower(nationality)) LIKE :query
    )


-- USER (for a teacher)
SELECT 'user', u.id, CONCAT(u.lastname, ' ', u.firstname) AS name
 FROM account a
  JOIN user teacher ON a.user_id = teacher.id
  JOIN user_lesson_subscribe tsubsc ON teacher.id = tsubsc.user_id
  JOIN user_lesson_subscribe usubsc ON tsubsc.lesson_id = usubsc.lesson_id
  JOIN user u ON u.id = usubsc.user_id
 WHERE a.id = :teacherAccountId
   AND (remove_accents(lower(u.lastname)) LIKE :query 
     OR remove_accents(lower(u.firstname)) LIKE :query
     OR remove_accents(lower(u.mails)) LIKE :query
     OR remove_accents(lower(u.address)) LIKE :query
     OR remove_accents(lower(u.city)) LIKE :query
     OR remove_accents(lower(u.nationality)) LIKE :query
   	 )
 GROUP BY 1, 2, 3, 4




-- ACCOUNT
SELECT 'account', a.id, u.uuid, CONCAT(lastname, ' ', firstname) AS name
 FROM account a
  JOIN user u ON a.user_id = u.id
 WHERE remove_accents(lower(login)) LIKE :query
 GROUP BY 1, 2, 3, 4





-- CLUB
SELECT 'club', c.id, c.uuid, c.name
 FROM club c
  JOIN club_lesson cles ON (cles.club_id = c.id AND c.active)
  JOIN club_location cl ON cles.club_location_id = cl.id
 WHERE remove_accents(lower(c.name)) LIKE :query
    OR remove_accents(lower(cl.name)) LIKE :query
    OR remove_accents(lower(cl.city)) LIKE :query
    OR remove_accents(lower(cl.address)) LIKE :query
    OR remove_accents(lower(cl.zipcode)) LIKE :query
    OR remove_accents(lower(cl.county)) LIKE :query
    OR remove_accents(lower(cl.country)) LIKE :query
    OR remove_accents(lower(cles.discipline)) LIKE :query
    OR remove_accents(lower(cles.age_level)) LIKE :query
 GROUP BY 1, 2, 3


-- update ConfigurationProperty
UPDATE configuration_property
 SET property_value = 'mollet.remi@orange.frA',
     updated_date = CURRENT_TIMESTAMP,
     updater_user_id  = 1,
     previous_value = (SELECT property_value FROM configuration_property WHERE property_key = 'club.contact.default.email')
 WHERE configuration_property.property_key = 'club.contact.default.email'
  AND configuration_property.property_value <> 'mollet.remi@orange.frA';
