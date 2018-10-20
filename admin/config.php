<?php
// HTTP
define('HTTP_SERVER', 'http://'.$_SERVER["SERVER_NAME"].'/admin/');
define('HTTP_CATALOG', 'http://'.$_SERVER["SERVER_NAME"].'/');
define('HTTP_DOMAIN', $_SERVER["SERVER_NAME"]);

// HTTPS
define('HTTPS_SERVER', 'http://'.$_SERVER["SERVER_NAME"].'/admin/');
define('HTTPS_CATALOG', 'http://'.$_SERVER["SERVER_NAME"].'/');

// DIR
define('DIR_APPLICATION',  $_SERVER['DOCUMENT_ROOT'].'/admin/');
define('DIR_SYSTEM',  $_SERVER['DOCUMENT_ROOT'].'/system/');
define('DIR_IMAGE',  $_SERVER['DOCUMENT_ROOT'].'image/');
define('DIR_STORAGE', __DIR__.'/../system/storage/');
define('DIR_CATALOG',  $_SERVER['DOCUMENT_ROOT'].'/admin/catalog/');
define('DIR_LANGUAGE', DIR_APPLICATION . 'language/');
define('DIR_TEMPLATE', DIR_APPLICATION . 'view/template/');
define('DIR_CONFIG', DIR_SYSTEM . 'config/');
define('DIR_CACHE', DIR_STORAGE . 'cache/');
define('DIR_DOWNLOAD', DIR_STORAGE . 'download/');
define('DIR_LOGS', DIR_STORAGE . 'logs/');
define('DIR_MODIFICATION', DIR_STORAGE . 'modification/');
define('DIR_SESSION', DIR_STORAGE . 'session/');
define('DIR_UPLOAD', DIR_STORAGE . 'upload/');
define('DIR_CATALOG_NEW',  $_SERVER['DOCUMENT_ROOT'].'/catalog/');

// DB
define('DB_DRIVER', 'mysqli');
define('DB_HOSTNAME', 'localhost');
define('DB_USERNAME', 'root');
define('DB_PASSWORD', 'root');
define('DB_DATABASE', 'kate');
define('DB_PORT', '3306');
define('DB_PREFIX', 'oc_');

// OpenCart API
define('OPENCART_SERVER', 'https://www.opencart.com/');
