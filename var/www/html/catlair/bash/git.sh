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
# Create GIT
#
#**************************************************************************************


#**************************************************************************************
# Create file for ssh auth
# /root/.ssh/config
# https://www.keybits.net/post/automatically-use-correct-ssh-key-for-remote-git-repo/
# File Contain:
#
# Host github.com-johnthesmith
#     HostName github.com
#     User git
#     IdentityFile /root/.ssh/github
#     IdentitiesOnly yes
#**************************************************************************************


GIT_PATH='/var/www/html/catlair_git';

if ! [ -d $GIT_PATH ]; then
    mkdir $GIT_PATH;
fi

cd $GIT_PATH;

# Copy data
cp -r --parent '/var/www/html/catlair/site/site_default/descripts' $GIT_PATH;
cp -r --parent '/var/www/html/catlair/site/site_default/php' $GIT_PATH;
cp -r --parent '/var/www/html/catlair/domain/localhost' $GIT_PATH;
cp -r --parent '/var/www/html/catlair/domain/127.0.0.1' $GIT_PATH;

cp -r --parent '/var/www/html/catlair/bash/build.sh' $GIT_PATH;
cp -r --parent '/var/www/html/catlair/bash/git.sh' $GIT_PATH;
cp -r --parent '/var/www/html/catlair/bash/right.sh' $GIT_PATH;

cp '/var/www/html/catlair/site/site_default/descripts/README/content_language_ru' $GIT_PATH'/README';
cp '/var/www/html/catlair/site/site_default/descripts/GPLv3.txt/content_language_ru' $GIT_PATH'/licence.txt';

# Github

if ! [ -d $GIT_PATH'/.git' ]; then
    # Init githup if path not found
    git init;
    git add -A;
    git commit -m "first commit";
    git remote add origin git@github.com:johnthesmith/catlair-php.git
else
    # Init githup if path found
    git add -A;
    git commit -m "commit";
fi

# guthub push
git pull origin
git push -u origin master;
