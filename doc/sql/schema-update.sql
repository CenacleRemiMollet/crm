ALTER TABLE configuration_property ADD CONSTRAINT  fk_configuration_property_user_user_id
   FOREIGN KEY (updater_user_id) REFERENCES user(id);