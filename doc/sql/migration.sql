
DROP FUNCTION IF EXISTS `camel_case`;

DELIMITER ;;
CREATE FUNCTION `camel_case`(str varchar(128)) RETURNS varchar(128)
BEGIN
DECLARE n, pos INT DEFAULT 1;
DECLARE sub, proper VARCHAR(128) DEFAULT '';

if length(trim(str)) > 0 then
    WHILE pos > 0 DO
        set pos = locate(' ',trim(str),n);
        if pos = 0 then
            set sub = lower(trim(substr(trim(str),n)));
        else
            set sub = lower(trim(substr(trim(str),n,pos-n)));
        end if;

        set proper = concat_ws(' ', proper, concat(upper(left(sub,1)),substr(sub,2)));
        set n = pos + 1;
    END WHILE;
end if;

RETURN trim(proper);
END ;;
DELIMITER ;

-- ============================================

-- **** CLUB ****

CREATE TABLE zzmigr_club AS 
 SELECT id,
        CASE 
          WHEN camel_case(nom) = 'Taekwondo Club Meudon' THEN 'meudon'
          WHEN camel_case(nom) = 'Suisse' THEN 'suisse'
          WHEN camel_case(nom) = 'Taekwonkido Kourou' THEN 'kourou'
          WHEN logo IS NOT NULL THEN substring_index(logo, '.', 1)
          ELSE '?????'
          END AS uuid,
        camel_case(nom) AS name,
        coalesce(logo, 'default.png') AS logo,
        url AS website_url,
        url_fb AS facebook_url,
        mailing_list,
        a_supprimer = 'N' AS active
   FROM devclub;



INSERT INTO club(uuid, name, logo, website_url, facebook_url, mailing_list, active)
 SELECT uuid, name, logo, website_url, facebook_url, mailing_list, active
  FROM zzmigr_club;

-- **** CLUB_LOCATION ****

/*CREATE TABLE zzmigr_club_location AS 
 SELECT oc.id AS o_id,
        nc.id AS n_id,
        concat(mc.uuid, '_0') AS uuid,
        '?' AS name,
        '?' AS address,
        camel_case(ville) AS city,
        '?' AS zipcode,
        departement AS county,
        camel_case(pays) AS country
  FROM devclub oc
   JOIN zzmigr_club mc USING (id)
   JOIN club nc USING (uuid);

INSERT INTO club_location(uuid, name, address, city, zipcode, county, country)
 SELECT uuid, name, address, city, zipcode, county, country
  FROM zzmigr_club_location;*/


-- **** CLUB_LESSON ****

/*CREATE TABLE zzmigr_club_lesson AS 
 SELECT mcl.o_id AS o_id,
        ncl.id AS club_location_id,
        nc.id AS club_id,
        concat(lower(lpad(conv(floor(rand()*pow(36,8)), 10, 36), 8, 0)), lower(lpad(conv(floor(rand()*pow(36,8)), 10, 36), 8, 0))) AS uuid,
        1 AS point,
        'Taekwondo' AS discipline,
        'Tous niveaux' AS age_level,
        'monday' AS day_of_week,
        '19:00:00' AS start_time,
        '20:00:00' AS end_time      
  FROM zzmigr_club mc
   JOIN zzmigr_club_location mcl ON mc.id = mcl.o_id
   JOIN club nc ON mc.uuid = nc.uuid
   JOIN club_location ncl ON mcl.uuid = ncl.uuid;

INSERT INTO club_lesson(club_location_id, club_id, uuid, point, discipline, age_level, day_of_week, start_time, end_time, description)
 SELECT club_location_id, club_id, uuid, point, discipline, age_level, day_of_week, start_time, end_time, ''
  FROM zzmigr_club_lesson;*/



-- **** USER ****

--DROP TABLE IF EXISTS zzmigr_user;

CREATE TABLE zzmigr_user AS 
 SELECT eleve_id AS o_id,
        concat(lower(lpad(conv(floor(rand()*pow(36,8)), 10, 36), 8, 0)), lower(lpad(conv(floor(rand()*pow(36,8)), 10, 36), 8, 0))) AS uuid,
        Nom AS lastname,
        Prenom AS firstname,
        Sexe AS sex,
        Date_naissance AS birthday,
        Adresse AS address,
        Code_postal AS zipcode,
        Ville AS city,
        Tel AS phone,
        Tel_accident AS phone_emergency,
        Nationalite AS nationality,
        cast(concat('["', replace(Email, ',', '","'), '"]') as char) AS mails,
        Date_ins AS created,
        0 AS blacklist_id,
        cast(null AS char(1000)) AS blacklist_date,
        cast(null AS char(1000)) AS blacklist_reason
  FROM develeve_cenacle;

INSERT INTO zzmigr_user
 SELECT 0 AS o_id,
        concat(lower(lpad(conv(floor(rand()*pow(36,8)), 10, 36), 8, 0)), lower(lpad(conv(floor(rand()*pow(36,8)), 10, 36), 8, 0))) AS uuid,
        Nom AS lastname,
        Prenom AS firstname,
        Sexe AS sex,
        Date_naissance AS birthday,
        Adresse AS address,
        Code_postal AS zipcode,
        Ville AS city,
        Tel AS phone,
        Tel_accident AS phone_emergency,
        Nationalite AS nationality,
        cast(concat('["', replace(Email, ',', '","'), '"]') as char) AS mails,
        str_to_date('2000-01-01 0:00:00','%Y-%m-%d %H:%i:%s') AS created,
        Id AS blacklist_id,
        Date_blacklist AS blacklist_date,
        Motif AS blacklist_reason
  FROM develeve_blacklist;


INSERT INTO user(uuid, lastname, firstname, sex, birthday, address, zipcode, city, phone, phone_emergency, nationality, mails, created, blacklist_date, blacklist_reason)
 SELECT uuid, lastname, firstname, sex, birthday, address, zipcode, city, phone, phone_emergency, nationality, mails, created, blacklist_date, blacklist_reason
  FROM zzmigr_user;


-- **** ACCOUNT ****

CREATE TABLE zzmigr_account AS 
 SELECT eleve_id AS o_id,
        u.id AS user_id,
        o_s.Email AS login,
        concat('sha1:', substr(Pwd, 1, 40)) AS password,
        '[]' AS roles,
        Acces = 'O' AS has_access
  FROM develeve_site o_s
   JOIN zzmigr_user z_u ON o_s.eleve_id = z_u.o_id
   JOIN user u ON u.uuid = z_u.uuid;

ALTER TABLE zzmigr_account CHANGE `roles` `roles` VARCHAR(64) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL; 

UPDATE zzmigr_account
 SET roles = '["ROLE_ADMIN"]'
 WHERE login LIKE 'fagu%'
  OR login LIKE 'stepht%'
  OR login LIKE 'remi%'; 
 

INSERT INTO account(user_id, login, password, roles, has_access)
 SELECT user_id, login, password, roles, has_access
  FROM zzmigr_account;


-- **** USER_LESSON_SUBSCRIBE ****

INSERT INTO user_club_subscribe(uuid, user_id, club_id, roles)
 SELECT concat(lower(lpad(conv(floor(rand()*pow(36,8)), 10, 36), 8, 0)), lower(lpad(conv(floor(rand()*pow(36,8)), 10, 36), 8, 0))) AS uuid,
        u.id AS user_id,
        c.id AS club_id,
        concat('["', if(a.user_id IS NOT NULL AND role = 'ROLE_CLUB_MANAGER', 'ROLE_CLUB_MANAGER', 'ROLE_STUDENT'), '"]') AS roles
  FROM (
   SELECT *
    FROM (
     SELECT Eleve_id, cast(ocid as signed) AS ocid, 'ROLE_CLUB_MANAGER' AS role
      FROM (
       SELECT Eleve_id, json_extract(concat('[', replace(Resp_club, ';', ','), ']'), '$[0]') AS ocid
        FROM develeve_site
        WHERE Resp_club IS NOT NULL AND Resp_club != ''
       UNION
       SELECT Eleve_id, json_extract(concat('[', replace(Resp_club, ';', ','), ']'), '$[1]') AS ocid
        FROM develeve_site
        WHERE Resp_club IS NOT NULL AND Resp_club != ''
       UNION
       SELECT Eleve_id, json_extract(concat('[', replace(Resp_club, ';', ','), ']'), '$[2]') AS ocid
        FROM develeve_site
        WHERE Resp_club IS NOT NULL AND Resp_club != ''
       UNION
       SELECT Eleve_id, json_extract(concat('[', replace(Resp_club, ';', ','), ']'), '$[3]') AS ocid
        FROM develeve_site
        WHERE Resp_club IS NOT NULL AND Resp_club != ''
       UNION
       SELECT Eleve_id, json_extract(concat('[', replace(Resp_club, ';', ','), ']'), '$[4]') AS ocid
        FROM develeve_site
        WHERE Resp_club IS NOT NULL AND Resp_club != ''
       ) ut
     WHERE ocid IS NOT NULL
     UNION ALL
     SELECT Eleve_id, club_id AS ocid, 'ROLE_STUDENT' AS role
      FROM develeve_cenacle
    ) fgb
    GROUP BY 1, 2, 3
  ) unnest
  JOIN zzmigr_club zc ON unnest.ocid = zc.id
  JOIN club c ON c.uuid = zc.uuid
  JOIN zzmigr_user zu ON unnest.Eleve_id = zu.o_id
  JOIN user u ON zu.uuid = u.uuid
  LEFT JOIN account a ON u.id = a.user_id;



-- **** Fix duplicate rows ****  

-- user_club_subscribe
CREATE TABLE zzz_deduplication_ucs AS
 SELECT user_id, club_id, JSON_ARRAYAGG(replace(replace(replace(roles, '"', ''), '[', ''), ']', '')) AS roles, '[]' AS newroles
  FROM user_club_subscribe
  GROUP BY user_id, club_id
   HAVING count(*) > 1;
     
ALTER TABLE zzz_deduplication_ucs CHANGE `newroles` `newroles` VARCHAR(512) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL; 

UPDATE zzz_deduplication_ucs
 SET newroles = JSON_ARRAY_APPEND(newroles, '$', 'ROLE_STUDENT')
 WHERE json_contains(roles, json_quote('ROLE_STUDENT'));
UPDATE zzz_deduplication_ucs
 SET newroles = JSON_ARRAY_APPEND(newroles, '$', 'ROLE_TEACHER')
 WHERE json_contains(roles, json_quote('ROLE_TEACHER'));
UPDATE zzz_deduplication_ucs
 SET newroles = JSON_ARRAY_APPEND(newroles, '$', 'ROLE_CLUB_MANAGER')
 WHERE json_contains(roles, json_quote('ROLE_CLUB_MANAGER'));

DELETE ucs
 FROM user_club_subscribe ucs
  JOIN zzz_deduplication_ucs z USING (user_id, club_id);

INSERT INTO user_club_subscribe(user_id, club_id, uuid, roles)
 SELECT user_id, club_id,
        concat(lower(lpad(conv(floor(rand()*pow(36,8)), 10, 36), 8, 0)), lower(lpad(conv(floor(rand()*pow(36,8)), 10, 36), 8, 0))),
        newroles
  FROM zzz_deduplication_ucs;

DROP TABLE zzz_deduplication_ucs;

  
-- **** << DROP temporary tables >> ****

--DROP TABLE zzmigr_club;
-- xx DROP TABLE zzmigr_club_location;
-- xx DROP TABLE zzmigr_club_lesson;
--DROP TABLE zzmigr_user;
--DROP TABLE zzmigr_account;










  