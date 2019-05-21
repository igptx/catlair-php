#**************************************************************************************
# Catlair PHP Copyright (C) 2019 a@itserv.ru
#
# This program is free software: you can redistribute it and/or modify
# it under the terms of the GNU General Public License as published by
# the Free Software Foundation, either version 3 of the License, or
# (at your option) any later version.
#
# This program is distributed in the hope that it will be useful,
# but WITHOUT ANY WARRANTY; without even the implied warranty of
# MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
# GNU General Public License for more details.
# You should have received a copy of the GNU General Public License
# along with this program.  If not, see <https://www.gnu.org/licenses/>.
#
#**************************************************************************************
#
# Release builder.
#
#**************************************************************************************

Target='/var/www/html/catlair/site/site_default/descripts/catlair.7z/file_language_default_catlair.7z';

rm $Target;

7z a $Target -spf '/var/www/html/catlair/site/site_default/descripts';
7z a $Target -spf '/var/www/html/catlair/site/site_default/php';
7z a $Target -spf '/var/www/html/catlair/domain/localhost';
7z a $Target -spf '/var/www/html/catlair/domain/127.0.0.1';
7z a $Target -spf '/var/www/right.sh';

cp 'install.sh' '/var/www/html/catlair/site/site_default/descripts/install.sh/file_language_default_install.sh';
