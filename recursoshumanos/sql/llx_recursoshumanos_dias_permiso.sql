-- Copyright (C) ---Put here your own copyright and developer email---
--
-- This program is free software: you can redistribute it and/or modify
-- it under the terms of the GNU General Public License as published by
-- the Free Software Foundation, either version 3 of the License, or
-- (at your option) any later version.
--
-- This program is distributed in the hope that it will be useful,
-- but WITHOUT ANY WARRANTY; without even the implied warranty of
-- MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
-- GNU General Public License for more details.
--
-- You should have received a copy of the GNU General Public License
-- along with this program.  If not, see https://www.gnu.org/licenses/.


CREATE TABLE llx_recursoshumanos_dias_permiso(
	-- BEGIN MODULEBUILDER FIELDS
	rowid integer AUTO_INCREMENT PRIMARY KEY NOT NULL, 
	label varchar(255), 
	date_creation datetime NOT NULL, 
	fk_user_creat integer NOT NULL, 
	fk_user_modif integer, 
	status smallint NOT NULL, 
	fk_user_solicitado integer NOT NULL, 
	date_solic datetime NOT NULL, 
	date_solic_fin datetime, 
	motivos text, 
	fk_user_validador integer
	-- END MODULEBUILDER FIELDS
) ENGINE=innodb;
