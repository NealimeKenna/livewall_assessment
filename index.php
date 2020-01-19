<?php error_reporting(E_ALL);
ini_set("display_errors", 1);

session_start();

define('SITE_URL', (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]" . explode('?', $_SERVER['REQUEST_URI'], 2)[0]);

require_once('autoloader.php');

use \api\Spotify;

$spotify = Spotify::getInstance();

if (isset($_GET['code'])) {
    $spotify->setCode($_GET['code']);
}

$spotify->authorize();

//Dumpje van Tom
echo "<pre style='background-color: #FFF; z-index: 9999999; position: relative;'>";
    var_dump($spotify);
echo "</pre>";?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Livewall Assessment</title>
    <!-- Normally you should load these recources locally, but for such a small and temporary project I decided to use
        their CDN to save time. -->
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.4.1/css/bootstrap.min.css"
          integrity="sha384-Vkoo8x4CGsO3+Hhxv8T/Q5PaXtkKtu6ug5TOeNV6gBiFeWPGFN9MuhOf23Q9Ifjh" crossorigin="anonymous">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.4.1/jquery.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.0/dist/umd/popper.min.js"
            integrity="sha384-Q6E9RHvbIyZFJoft+2mJbHaEWldlvI9IOYy5n3zV9zzTtmI3UksdQRVvoxMfooAo"
            crossorigin="anonymous"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.4.1/js/bootstrap.min.js"
            integrity="sha384-wfSDF2E50Y2D1uUdj0O3uMBJnjuUD4Ih7YwaYd1iqfktj0Uod8GCExl3Og8ifwB6"
            crossorigin="anonymous"></script>
    <!-- Own styling and scripts -->
    <link rel="stylesheet" href="assets/style.css">
</head>
<body>
    <?php if (!$spotify->getAccessToken()) { ?>
        <div id="connectModal" class="modal" role="dialog">
            <div class="modal-dialog modal-dialog-centered" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Please Connect</h5>
                    </div>
                    <div class="modal-body text-center">
                        <p>
                            Please connect your Spotify account. We will not save or distribute any information from
                            your account.
                        </p>
                        <a class="btn btn-success" href="<?= Spotify::getConnectUrl() ?>">Connect</a>
                    </div>
                </div>
            </div>
        </div>
        <script>
            $(function () {
                $('#connectModal').modal({
                    'show': true,
                    'backdrop': 'static'
                });
            });
        </script>
    <?php } elseif ($user = $spotify->getUserInfo()) { ?>
        <div class="user-bar w-100 fixed-top d-flex justify-content-between bg-secondary text-white">
            <img class="img-fluid" src="<?= $user->images[0]->url ?>" alt="profile_picture"/>
            <div>
                    <span class="align-middle">
                        User: <?= $user->display_name ?>
                    </span>
            </div>
            <div class="pr-2">
                    <span class="align-middle">
                        Spotify:
                        <a href="<?= $user->external_urls->spotify ?>" target="_blank"
                           class="text-white">
                            <?= $user->external_urls->spotify ?>
                        </a>
                    </span>
            </div>
        </div>
        <div class="col-4 top-40">
            <?php if ($top_40 = $spotify->getTop40()) {
                foreach ($top_40->tracks->items as $entry) {
                    $track = $entry->track; ?>
                    <a href="<?= $track->external_urls->spotify ?>" class="row mb-1">
                        <div class="col-3">
                            <img class="img-fluid" src="<?= $track->album->images[0]->url ?>" alt="cover"/>
                        </div>
                        <div class="col-9">
                            <div class="name">
                                <?= $track->name ?>
                            </div>
                        </div>
                    </a>
                <?php }
            } ?>
        </div>
        <div class="col-4"></div>
        <div class="col-4"></div>
    <?php } ?>
</body>
</html>