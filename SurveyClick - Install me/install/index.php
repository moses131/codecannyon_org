<?php

/** START SESSION */
session_start();
ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);

/** INCLUDE CONSTANTS / CONFIG */
require_once '../config.php';

/** CONNECT TO DATABASE */
try {
    $db = @new mysqli( DB_HOST, DB_USER, DB_PASSWORD, DB_NAME );
    // For older PHP versions
    if( !$db->connect_errno )
    header( 'Location: ../' );
}

catch( \Exception $e ) { }
/** */

spl_autoload_register( function ( $cn ) {
    $type   = strstr( $cn, '\\', true );
    $cn     = str_replace( '\\', '/', $cn );

    if( $type == 'admin' ) {
        // nothing here
    } else {
        if( file_exists( ( $file = DIR . '/' . INCLUDES_DIR . '/' . $cn . '.php' ) ) )
        require_once $file;
    }
} );

/** */

/** INCLUDE CLASS */
require_once 'class.php';

echo '
<!DOCTYPE html>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1" />
<title>Install SurveyClick</title>
<meta name="robots" content="noindex, nofollow" />
<link href="../assets/css/fontawesome-all.min.css" media="all" rel="stylesheet" />
<link href="../' . ADMIN_DIR . '/' . ADMIN_THEMES_DIR . '/default/assets/css/style.css" media="all" rel="stylesheet" />
<link href="../' . ADMIN_DIR . '/' . ADMIN_THEMES_DIR . '/default/assets/css/responsive.css" media="all" rel="stylesheet" />
<link href="assets/css/style.css" media="all" rel="stylesheet" />
<link href="//fonts.googleapis.com/css?family=Quicksand:500,700" rel="stylesheet">
</head>
<body>';

$install    = new InitInstall; 
$step       = $_SERVER['REQUEST_METHOD'] == 'POST' && isset( $_POST['step'] ) && ( (int) $_POST['step'] <= 4 || (int) $_POST['step'] > 1 ) ? $_POST['step'] : 1;
$data       = $_POST ?? [];

if( $step == 'do-installation' ) {
    $install->install( $step, $data );
} ?>

<div class="fp df oa">
    <div class="box">
        <div class="lks">
            <form method="POST" autocomplete="off">
                <?php 
                // Step 1
                if( $step == 1 ) {
                    echo '<h2>Install <strong>SurveyClick</strong></h2>';
                    echo $install->message();
                    echo $install->getDetails(); 
                    if( !$install->canGoNext() ) echo '<div class="msg alert mt30">Fix all issues to install the script</div>';
                    else { 
                        $install->saveInputs();
                        echo '
                        <input type="hidden" name="step" value="2" />
                        <div class="install-button">
                            <button>Next</button>
                            <span><span>1</span>/4</span>
                        </div>';
                    }

                // Step 2
                } else if( $step == 2 ) {
                    echo '<h2>DB Connection</h2>';
                    echo $install->message();
                    $install->saveInputs();
                    echo '
                    <div class="form_line">
                        <i class="fas fa-database"></i>
                        <input type="text" id="db[name]" name="db[name]" placeholder="Database" autocomplete="off" value="' . ( $data['db']['name'] ?? '' ) . '" required="">
                    </div>

                    <div class="form_line">
                        <i class="fas fa-server"></i>
                        <input type="text" id="db[host]" name="db[host]" placeholder="Host" autocomplete="off" value="' . ( $data['db']['host'] ?? 'localhost' ) . '" required="">
                    </div>

                    <div class="form_line">
                        <i class="fas fa-user"></i>
                        <input type="text" id="db[user]" name="db[user]" placeholder="User" value="' . ( $data['db']['user'] ?? '' ) . '" autocomplete="off" required="">
                    </div>

                    <div class="form_line">
                        <i class="fas fa-key"></i>
                        <input type="text" id="db[password]" name="db[password]" placeholder="Password" value="' . ( $data['db']['password'] ?? '' ) . '" autocomplete="off">
                    </div>
                    
                    <div class="form_line tc">More options</div>
                    <div class="form_line">
                        <input type="text" id="db[prefix]" name="db[prefix]" placeholder="Prefix" value="' . ( $data['db']['prefix'] ?? '' ) . '" autocomplete="off">
                    </div>

                    <input type="hidden" name="step" value="3" />
                    <div class="install-button">
                        <button>Next</button>
                        <span><span>2</span>/4</span>
                    </div>';

                // Step 3
                } else if( $step == 3 ) {
                    $siteHelper = new site\main_helper;
                    echo '<h2>Server options</h2>';
                    echo $install->message();
                    $install->saveInputs();
                    echo '
                    <div class="form_line tc">Timezone</div>
                    <div class="form_line">
                        <span>This will override server\'s timezone</span>
                        <select name="server[tz]">';
                        foreach( timezone_identifiers_list() as $tz ) {
                            echo '<option value="' . $tz . '"' . ( isset( $data['server']['tz'] ) ? ( $data['server']['tz'] == $tz ? ' selected' : '' ) : ( $tz == date_default_timezone_get() ? ' selected' : '' ) ) . '>' . $tz . '</option>';
                        }
                        echo '
                        </select>
                    </div>

                    <div class="form_line tc">Currency</div>
                    <div class="form_line">
                        <span>Payments will be made in this currency. Prices & balances will be displayed in the same currency</span>
                        <select name="server[currency]">';
                        foreach( $install->getCurrencies() as $a => $s ) {
                            echo '<option value="' . $a . '"' . ( isset( $data['server']['currency'] ) ? ( $data['server']['currency'] == $a ? ' selected' : '' ) : ( $a == PAYMENT_CURRENCY ? ' selected' : '' ) ) . '>' . $a . '</option>';
                        }
                        echo '
                        </select>
                    </div>

                    <div class="form_line tc">Default language</div>
                    <div class="form_line">
                        <select name="server[language]">';
                        foreach( $siteHelper->getDefaultLanguages() as $lang_id => $lang ) {
                            echo '<option value="' . $lang_id . '"' . ( isset( $data['server']['language'] ) ? ( $data['server']['language'] == $lang_id ? ' selected' : '' ) : ( $lang_id == PAYMENT_CURRENCY ? ' selected' : '' ) ) . '>' . $lang['name'] . '</option>';
                        }
                        echo '
                        </select>
                    </div>

                    <div class="form_line tc">Accounts directory</div>
                    <div class="form_line">
                        <span>Example: yourwebsite.com/<strong>' . ADMIN_LOC . '</strong>/surveys</span>
                        <input type="text" id="server[accounts_url]" name="server[accounts_url]" placeholder="Prefix" value="' . ( $data['server']['accounts_url'] ?? ADMIN_LOC ) . '" autocomplete="off">
                    </div>

                    <div class="form_line tc">Max file size</div>
                    <div class="form_line">
                        <span>Maximum file size allowed to upload by survey respondents. In MiB</span>
                        <input type="number" id="server[max_file_size]" name="server[max_file_size]" step=".1" value="' . ( $data['server']['max_file_size'] ?? MAX_SIZE_FILE_TYPE ) . '" autocomplete="off">
                    </div>

                    <input type="hidden" name="step" value="4" />
                    <div class="install-button">
                        <button>Next</button>
                        <span><span>3</span>/4</span>
                    </div>';

                // Step 4
                } else if( $step == 4 ) {
                    echo '<h2>Owner\'s account</h2>';
                    echo $install->message();
                    $install->saveInputs();
                    echo '
                    <div class="form_line">
                        <i class="fas fa-user"></i>
                        <input type="text" id="owner[user]" name="owner[user]" placeholder="Name" value="' . ( $data['owner']['user'] ?? '' ) . '" autocomplete="off" required="">
                    </div>

                    <div class="form_line">
                        <i class="fas fa-at"></i>
                        <input type="text" id="owner[email]" name="owner[email]" placeholder="Email address" value="' . ( $data['owner']['email'] ?? '' ) . '" autocomplete="off" required="">
                    </div>

                    <div class="form_line">
                        <i class="fas fa-key"></i>
                        <input type="text" id="owner[password]" name="owner[password]" placeholder="Password" value="' . ( $data['owner']['password'] ?? '' ) . '" autocomplete="off" required="">
                    </div>

                    <input type="hidden" name="step" value="install" />
                    <div class="install-button">
                        <button>Install your website</button>
                        <span><span>4</span>/4</span>
                    </div>';
                
                // Installing
                } else if( $step == 'install' ) {
                    echo '<h2>Installing...</h2>
                    <div class="tc">
                    <div class="big"><i class="fas fa-circle-notch fa-spin"></i></div>
                    <div>Please wait</div>
                    </div>';
                    $install->saveInputs();
                    echo '<input type="hidden" name="step" value="do-installation" />
                    <script>
                    document.querySelector( "form" ).submit();
                    </script>';

                // Installed
                } else if( $step == 'installed' ) {
                    echo '<h2>You did it!</h2>
                    <div class="tc">
                    <div class="big"><i class="fas fa-check"></i></div>
                    <div class="redirect_in">You will be redirected in <strong>5</strong> seconds</div>
                    </div>

                    <script>
                    var c = document.querySelector( ".redirect_in > strong" );
                    var v = parseInt( c.innerHTML );
                    var i = setInterval( function() {
                        v--;
                        c.innerHTML = v;
                        if( i == v ) {
                            clearInterval( i );
                            window.location = "../' . ( $data['server']['accounts_url'] ?? ADMIN_LOC ) . '";
                        }
                    }, 1000 );
                    </script>';

                // Error
                } else if( $step == 'error' ) {
                    echo '
                    <div class="tc mb30">
                        <div class="big"><i class="fas fa-times"></i></div>
                    </div>';
                    echo $install->message();
    
                // 404
                } else {
                    echo '<div class="msg error">Something went terribly wrong</div>';
                    echo '<a href="index.php" class="btn">Try again</a>';
                } ?>
            </form>
        </div>
    </div>
</div>

<?php $install->clear(); ?>

</body>
</html>