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
# Rewrite rights before work with root access.
#
#**************************************************************************************

# Change right for defaul folders.
chmod -R 775 /var/www
chmod -R 775 /var/www/html

# Rewrite right.
chown -R www-data:www-data /var/www/html/*
chmod -R 660 /var/www/html/*
find /var/www/html/* -type d -exec chmod 770 {} \;

# Change right for bash.
chmod +x /var/www/html/catlair/bash/*
