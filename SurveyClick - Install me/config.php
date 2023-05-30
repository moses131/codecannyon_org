<?php

/** MySQL database name */
define( 'DB_NAME', 'YOUR DATABASE' );

/** MySQL database username */
define( 'DB_USER', 'DATABASE USER' );

/** MySQL database password */
define( 'DB_PASSWORD', 'USER PASSWORD' );

/** MySQL hostname */
define( 'DB_HOST', 'localhost' );

/** Database Charset */
define( 'DB_CHARSET', 'utf8mb4' );

/** Tables prefix */
define( 'DB_TABLE_PREFIX', '' );

/** Includes directory location */
define( 'INCLUDES_DIR', 'includes' );

/** Mail templates directory */
define( 'MAIL_TMPS_DIR', 'content/mail_templates' );

/** Themes directory */
define( 'THEMES_DIR', 'content/themes' );

/** Survey directory */
define( 'SURVEY_DIR', 'content/survey' );

/** Temp directory */
define( 'TEMP_DIR', 'content/temp' );

/** Plugins directory */
define( 'PLUGINS_DIR', 'content/plugins' );

/** Uploads directory */
define( 'UPLOADS_DIR', 'content/uploads' );

/** Admin directory location */
define( 'ADMIN_DIR', 'cp' );

/** Admin themes directory */
define( 'ADMIN_THEMES_DIR', 'content/themes' );

/** Scripts directory*/
define( 'SCRIPTS_DIR', 'content/scripts' );

/** Payments currency */
define( 'PAYMENT_CURRENCY', 'USD' );

/** Payments symbol */
define( 'PAYMENT_SYMBOL', '$' );

/** Interval for updating user's action */
define( 'UPDATE_HIT_INTVAL', 60 );

/** Maximum file size allowed to upload by survey respondents. In MiB */
define( 'MAX_SIZE_FILE_TYPE', 5 );

/** Default expiration time for responses not finished. In minutes. 3 days default */
define( 'RESPONSE_TIME_LIMIT', ( 3 * 24 * 60 ) );

/** Default country */
define( 'DEFAULT_COUNTRY', 'us' );

/** Default language */
define( 'DEFAULT_LANGUAGE', 'en_US' );

/** Default admin location */
define( 'ADMIN_LOC', 'account' );

/** Default timezone */
define( 'DEFAULT_TIMEZONE', 'America/New_York' );

/** Script version */
define( 'SCRIPT_VERSION', '1.0' );

/** Do not modify */
define( 'DIR', __DIR__ );