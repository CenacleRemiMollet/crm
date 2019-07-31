DELIMITER $$

CREATE FUNCTION `proper_case`(str varchar(128)) RETURNS varchar(128)
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
END 
$$

DELIMITER ;


SELECT concat('$club = $this->createClub(\'', proper_case(nom), '\', \'', logo, '\', \'', url, '\';')
 FROM devclub
 WHERE a_supprimer = 'N'


$club           = $this->createClub('Koryo', 'bry_sur_marne.gif', 'http://www.taekwondobry.fr');
