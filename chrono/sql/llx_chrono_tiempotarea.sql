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


CREATE TABLE khns_chrono_tiempotarea(
	-- BEGIN MODULEBUILDER FIELDS
	rowid integer AUTO_INCREMENT PRIMARY KEY NOT NULL, 
	fk_project integer, 
	status smallint NOT NULL, 
	fk_user_time integer NOT NULL, 
	fk_task integer NOT NULL, 
	fecha_inicio datetime, 
	fecha_fin datetime, 
	tiempo_transcurrido integer, 
	nota varchar
	-- END MODULEBUILDER FIELDS
) ENGINE=innodb;
