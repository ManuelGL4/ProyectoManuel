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


CREATE TABLE llx_recursoshumanos_solicitudes(
	-- BEGIN MODULEBUILDER FIELDS
	rowid integer AUTO_INCREMENT PRIMARY KEY NOT NULL, 
	fk_solicitante integer, 
	fk_solicitado integer NOT NULL, 
	tipo varchar(20) NOT NULL, 
	descripcion text, 
	urgencia varchar(20) NOT NULL, 
	vista integer NOT NULL, 
	cerrada integer NOT NULL, 
	fecha_cerrada datetime
	-- END MODULEBUILDER FIELDS
) ENGINE=innodb;
