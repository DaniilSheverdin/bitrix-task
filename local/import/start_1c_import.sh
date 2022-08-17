#!/bin/bash
cd /var/www/newcorp/local/import
echo "`date` -start_update- Подраздления ">>`pwd`/log_update.txt
/usr/bin/php72 -f `pwd`/1c_group.php>>`pwd`/log_update.txt
echo "`date` -start_update- Пользователи ">>`pwd`/log_update.txt
/usr/bin/php72 -f `pwd`/1c_users.php>>`pwd`/log_update.txt
echo "`date` -stop_update- Конец обновления">>`pwd`/log_update.txt
