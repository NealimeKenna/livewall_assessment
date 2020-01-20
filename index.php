<?php

require_once('config.php');

$spotify = \api\Spotify::getInstance();

if (isset($_GET['code'])) {
    $spotify->setCode($_GET['code']);
}

$spotify->authorize(); ?>
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
                        <a class="btn btn-success" href="<?= \api\Spotify::getConnectUrl() ?>">Connect</a>
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
        <div class="d-flex">
            <div class="col-4 top-40">
                <h3>Top 40</h3>
            </div>
            <div class="col-4">
                <h3>Overeenkomsten</h3>
            </div>
            <div class="col-4 text-center user-top">
                <h3>Top van de user</h3>
                <div class="artists">
                    <h4>Artiesten</h4>
                </div>
                <div class="tracks">
                    <h4>Tracks</h4>
                </div>
            </div>
        </div>
        <script>
            $(function () {
                $.ajaxSetup({
                    async: false
                });

                const top40 = spotifyCurl('getTop40').tracks.items;
                const top40_wrapper = $('.top-40');

                $.each(top40, function (key, item) {
                    const track = item.track;

                    top40_wrapper.append('<a id="track-' + key + '" href="' + track.external_urls.spotify + '" target="_blank" class="card mb-1">' +
                        '<img class="img-fluid" src="' + track.album.images[0].url + '" alt="cover"/>' +
                        '<div class="col-9"><div class="name">' + track.name + '</div></div>' +
                        '</a>');
                });

                const user_top_wrapper = $('.user-top');
                const ut_tracks_wrapper = user_top_wrapper.find('.tracks');
                const ut_artists_wrapper = user_top_wrapper.find('.artists');
                const user_top_artists = spotifyCurl('getUserTopArtists').items;
                const user_top_tracks = spotifyCurl('getUserTopTracks').items;

                $.each(user_top_artists, function(key, item) {
                    ut_artists_wrapper.append('<div>' + item.name + '</div>');
                });

                $.each(user_top_tracks, function(key, item) {
                    ut_tracks_wrapper.append('<div>' + item.name + '</div>');
                });

                function spotifyCurl(func) {
                    let result = false;

                    $.post('actionhandler.php', {func: func}, function (data) {
                        result = $.parseJSON(data);
                    });

                    return result;
                }
            });
        </script>
    <?php } ?>
</body>
</html>