<?php
// get page for phpinfo
function getQ($getQ)
{
    if (!empty($getQ)) {
        switch ($getQ) {
            case 'info':
                phpinfo();
                exit;
                break;
        }
    }
}

// Get PHP extensions
function getServerExtensions($server)
{
    $ext = [];

    switch ($server) {
        case 'php':
            $ext = get_loaded_extensions();
            break;
        case 'apache':
            $ext = apache_get_modules();
            break;
    }

    sort($ext, SORT_STRING);
    $ext = array_chunk($ext, 2);

    return $ext;
}

// Check PHP version
function getPhpVersion()
{
    // get last version from php.net
    $json = @file_get_contents('https://www.php.net/releases/index.php?json&version=7.2.34');
    $data = json_decode($json);
    $lastVersion = $data->version;

    // get current installed version
    $phpVersion = phpversion();

    // Remove dot character from version ex: 1.2.3 to 123 and convert string to integer
    $intLastVersion = (int) str_replace('.', '', $lastVersion);
    $intCurVersion = (int) str_replace('.', '', $phpVersion);

    return [
        'lastVersion' => $lastVersion,
        'currentVersion' => $phpVersion,
        'intLastVer' => $intLastVersion,
        'intCurVer' => $intCurVersion,
    ];
}

// Httpd Versions
function serverInfo()
{
    $server = explode(' ', $_SERVER['SERVER_SOFTWARE']);
    $openSsl = isset($server[2]) ? $server[2] : null;

    return [
        'httpdVer' => $server[0],
        'openSsl' => $openSsl,
        'phpVer' => getPhpVersion()['currentVersion'],
        'xDebug' => phpversion('xdebug'),
        'docRoot' => $_SERVER['DOCUMENT_ROOT'],
    ];
}

// get SQL version
function getSQLVersion()
{
    $output = shell_exec('mysql -V');
    preg_match('@[0-9]+\.[0-9]+\.[0-9-\w]+@', $output, $version);

    return $version[0];
}

// PHP links
function phpDlLink($version)
{
    $changelog = 'https://www.php.net/ChangeLog-7.php#' . $version;
    $downLink = 'https://windows.php.net/downloads/releases/php-' . $version . '-Win32-VC15-x64.zip';

    return [
        'changeLog' => $changelog,
        'downLink' => $downLink,
    ];
}

// define sites-enabled directory
function getSiteDir()
{
    if (preg_match('/^Apache/', $_SERVER['SERVER_SOFTWARE'])) {
        return '../laragon/etc/apache2/sites-enabled';
    } else {
        return '../laragon/etc/nginx/sites-enabled';
    }
}
?>
<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <title>Development Server</title>

    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0-beta.2/css/bootstrap.min.css" integrity="sha384-PsH8R72JQ3SOdhVi3uxftmaW6Vc51MKb0q5P2rRUpPvrszuE4W1povHYgTpBfshb" crossorigin="anonymous">
    <link href="https://fonts.googleapis.com/css?family=Open+Sans&display=swap" rel="stylesheet">
</head>
<style>
    body {
        margin: 0;
        padding: 0;
        color: #fff;
        font-family: 'Rubik', sans-serif;
        box-sizing: border-box;
    }

    a {
        font-family: "Rubik", Sans-serif, serif;
        text-transform: uppercase;
        font-size: 16px !important;
        color: #FFFFFF !important;
        text-decoration: none;
    }

    /* Assign grid instructions to our parent grid container, mobile-first (hide the sidenav) */
    .grid-container {
        display: grid;
        grid-template-columns: 1fr;
        grid-template-rows: 50px 1fr 50px;
        grid-template-areas: 'header' 'main' 'footer';
        height: 100vh;
    }

    /* Give every child element its grid name */
    .header {
        grid-area: header;
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 0 16px;
        color: #ffffff;
        font-family: "Rubik", Sans-serif;
        background-color: #0b162c;
    }

    .main {
        grid-area: main;
        /* background-color: #e5e5e5; */
        background: url(background.jpg) no-repeat center center fixed;
        -webkit-background-size: cover;
        -moz-background-size: cover;
        -o-background-size: cover;
        background-size: cover;
    }

    .main-header {
        display: flex;
        justify-content: space-between;
        margin: 20px;
        padding: 20px;
        height: 150px;
        background-color: #fca311;
        color: #14213d;
    }

    .main-overview {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(265px, 1fr));
        grid-auto-rows: 71px;
        grid-gap: 20px;
        margin: 0px;
    }


    .wrapper {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 10px;
    }

    .overviewcard {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 15px;
        background-color: #00adef;
        /*-----00adef    -----*/
        font-family: "Rubik", Sans-serif, serif;
        border-radius: 5px 5px;
        font-size: 16px;
        color: #FFFFFF !important;
        line-height: 1;
        height: 44px;
    }

    .overviewcard2 {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 15px;
        background-color: #512887;
        /*-----00adef    -----*/
        font-family: "Rubik", Sans-serif, serif;
        border-radius: 5px 5px;
        font-size: 16px;
        color: #FFFFFF !important;
        line-height: 1;
        height: 44px;
    }

    .overviewcard_sites {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 15px;
        background-color: #023e8a;
        /*-----00adef    -----*/
        font-family: "Rubik", Sans-serif, serif;
        border-radius: 5px 5px;
        font-size: 16px;
        color: #FFFFFF !important;
        line-height: 1;
        height: 31px;
    }

    .overviewcard_info {
        font-family: "Rubik", Sans-serif, serif;
        text-transform: uppercase;
        font-size: 16px !important;
        color: #FFFFFF !important;
    }

    .overviewcard_icon {
        font-family: "Rubik", Sans-serif, serif;
        text-transform: uppercase;
        font-size: 16px !important;
        color: #FFFFFF !important;
    }

    .main-cards {
        column-count: 0;
        column-gap: 20px;
        margin: 20px;
    }

    .card {
        display: flex;
        flex-direction: column;
        align-items: center;
        width: 100%;
        background-color: #f1faee;
        margin-bottom: 20px;
        -webkit-column-break-inside: avoid;
        padding: 24px;
        box-sizing: border-box;
    }

    /* Force varying heights to simulate dynamic content */
    .card:first-child {
        height: 300px;
    }

    .card:nth-child(2) {
        height: 200px;
    }

    .card:nth-child(3) {
        height: 265px;
    }

    .sites {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(275px, 1fr));
        grid-gap: 20px;
        margin: 20px;
    }

    .sites li {
        display: flex;
        flex-direction: column;
        align-items: center;
        width: 100%;
        background: #560bad;
        color: #ffffff;
        font-family: 'Rubik', sans-serif;
        font-size: 14px;
        text-align: left;
        text-transform: uppercase;
        margin-bottom: 20px;
        -webkit-column-break-inside: avoid;
        padding: 24px;
        box-sizing: border-box;
    }


    .sites li:hover {
        box-shadow: 0 0 15px 0 #bbb;
    }

    .sites li:hover svg {
        fill: #ffffff;
    }

    .sites li:hover a {
        color: #ffffff;
    }

    .sites li a {
        display: block;
        padding-left: 48px;
        color: #f2f2f2;
        transition: color 250ms ease-in-out;
    }

    .sites img {
        position: absolute;
        margin: 8px;
        margin-left: -40px;
        fill: #f2f2f2;
        transition: fill 250ms ease-in-out;
    }

    .sites svg {
        position: absolute;
        margin: 16px;
        margin-left: -40px;
        fill: #f2f2f2;
        transition: fill 250ms ease-in-out;
    }

    .footer {
        grid-area: footer;
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 0 16px;
        background-color: #0b162c;
        color: #ffffff;
    }

    /* Non-mobile styles, 750px breakpoint */
    @media only screen and (min-width: 46.875em) {
        /* Show the sidenav */
        /*.grid-container {*/
        /*    grid-template-columns: 240px 1fr;*/
        /*    grid-template-areas: "sidenav header" "sidenav main" "sidenav footer";*/
        /*}*/

    }

    /* Medium screens breakpoint (1050px) */
    @media only screen and (min-width: 65.625em) {

        /* Break out main cards into two columns */
        .main-cards {
            column-count: 1;
        }
    }
</style>
<style media="screen">
    h2 {
        padding: 40px 0 15px;
    }

    .project {
        padding-bottom: 15px;

    }
</style>

<body>
    <div class="grid-container">
        <div class="menu-icon">
            <i class="fas fa-bars header__menu"></i>
        </div>

        <header class="header">
            <div class="header__search">My Development Server</div>
            <div class="header__avatar">Welcome Back!</div>
        </header>

        <!---------------------------------------------------------------->
        <main class="main">
            <div class="main-overview">
                <div class="overviewcard">
                    <div class="overviewcard_icon"></div>
                    <div class="overviewcard_info"><?php echo $_SERVER['SERVER_SOFTWARE']; ?></div>
                </div>
                <div class="overviewcard">
                    <div class="overviewcard_icon">Server Port</div>
                    <div class="overviewcard_info"><?php echo $_SERVER['SERVER_PORT']; ?></div>
                </div>
                <div class="overviewcard">
                    <div class="overviewcard_icon">PHP</div>
                    <div class="overviewcard_info"><?php echo phpversion(); ?></div>
                </div>
                <div class="overviewcard">
                    <div class="overviewcard_icon">Document Root</div>
                    <div class="overviewcard_info"><?php echo $_SERVER['DOCUMENT_ROOT']; ?></div>
                </div>
            </div>

            <div class="main-overview">
                <div class="overviewcard2">
                    <div class="overviewcard_icon">Manage Databases</div>
                    <div class="overviewcard_info"><a href="http://localhost/MAMP/phpmyadmin.php?lang=en" target="_blank">PHPMYADMIN</a></div>
                </div>
                <div class="overviewcard2">
                    <div class="overviewcard_icon"></div>
                    <div class="overviewcard_info"></div>
                </div>
                <div class="overviewcard2">
                    <div class="overviewcard_icon"></div>
                    <div class="overviewcard_info"></div>
                </div>
                <div class="overviewcard2">
                    <div class="overviewcard_icon"></div>
                    <div class="overviewcard_info"></div>
                </div>
            </div>
            <!---------------------------------------------------------------->

            <?php
            $dir_nom = 'D:\MAMP\htdocs'; // Replace by your htdocs path
            $dir = opendir($dir_nom) or die('<small>Listing error: directory does not exist</small>');
            $file = array();
            $folder = array();
            $done = array();
            $waiting = array();
            $blacklist = array();

            while ($element = readdir($dir)) {
                if ($element != '.' && $element != '..') {
                    if (!is_dir($dir_nom . '/' . $element)) {
                        $file[] = $element;
                    } elseif (strpos($element, 'assets') !== false) {
                        $blacklist[] = $element;
                    } elseif (strpos($element, '[done]') !== false || strpos($element, '[done]') !== false) {
                        $done[] = $element;
                    } elseif (strpos($element, '[waiting]') !== false || strpos($element, '[waiting]') !== false) {
                        $waiting[] = $element;
                    } else {
                        $folder[] = $element;
                    }
                }
            }

            closedir($dir);
            ?>


            <!------------------------------------------------------>
            <div class="container">
                <div class="row col-12">

                    <div class="col-6">
                        <?= count($folder); ?> <?php if (count($folder) <= 1) {
                                                    echo " project is";
                                                } else {
                                                    echo "projects are";
                                                } ?> In Development</p>
                    </div>
                    <div class="col-6">
                        <p><b><?= count($waiting); ?> <?php if (count($waiting) <= 1) {
                                                            echo " project is";
                                                        } else {
                                                            echo "projects are";
                                                        } ?> In Development</b> - <?= count($done); ?> Projects are finished</p>

                    </div>
                </div>
            </div>


            <div class="container ">
                <!-- row of columns -->
                <div class="row">
                    <div class="col-12" id="now">
                        <h2>HOSTED PROJECTS</h2>
                    </div>

                    <div class="wrapper">
                        <?php
                        if (!empty($folder)) {
                            sort($folder); // for ascending sort, rsort() for descending sort
                            foreach ($folder as $link) { ?>
                                <div class="col-4 project">
                                    <h3><?php echo "$link"; ?></h3>
                                    <a class="btn btn-outline-success" href="<?php echo $link; ?>" target="_blank" role="button">Open</a>
                                </div>
                        <?php }
                        }
                        ?>
                    </div>

                    <?php if (!empty($waiting)) : ?>


                        <div class="col-12" id="waiting">
                            <h2>Project with Pending Status </h2>
                        </div>
                        <?php
                        if (!empty($waiting)) {
                            sort($waiting); // for ascending sort, rsort() for descending sort
                            foreach ($waiting as $link) { ?>
                                <div class="col-4 project">
                                    <h3>
                                        <img src="assets/img/waiting.svg" alt="waiting icon" width="20" height="20">
                                        <?php echo str_replace('[waiting]', '', $link); ?>
                                    </h3>
                                    <a class="btn btn-outline-success" href="<?php echo $link; ?>" role="button">Open</a>
                                </div>
                        <?php }
                        }
                        ?>
                    <?php endif; ?>

                    <hr>
                    <div class="col-12" id="done">
                        <h2>Delivered Projects</h2>
                    </div>
                    <?php
                    if (!empty($done)) {
                        sort($done); // for ascending sort, rsort() for descending sort
                        foreach ($done as $link) { ?>
                            <div class="col-4 project">
                                <h3>
                                    <img src="assets/img/done.svg" alt="waiting icon" width="20" height="20">
                                    <?php echo str_replace('[ended]', '', $link); ?>
                                </h3>
                                <a class="btn btn-outline-success" href="<?php echo $link; ?>" role="button">Open</a>
                            </div>
                    <?php }
                    }
                    ?>
                </div>
                <hr>



            </div>


            <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.12.4/jquery.min.js"></script>
        </main>

        <footer class="footer">
            <div class="footer__copyright">&copy; 2023 Tarek Tarabichi</div>
            <div class="footer__signature">Made with <span style="color: #e25555;">&hearts;</span> and powered by MAMP</div>
        </footer>
    </div>
</body>

</html>
