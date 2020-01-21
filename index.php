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
            <div class="col-4 playlist">
                <h3>Playlist</h3>
            </div>
            <div class="col-4 text-center compare-results">
                <h3>Overeenkomsten</h3>
                <div class="artists">
                    <h4>Artiesten:</h4>
                </div>
                <div class="tracks">
                    <h4>Tracks:</h4>
                </div>
                <div class="total">
                    <h4>Totaal:</h4>
                </div>
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
                // Potential for a user input spotify playlist ID. For now just get the NL Top 40.
                const playlist = spotifyCurl('getTop40').tracks.items;
                const playlist_size = playlist.length;
                const playlist_wrapper = $('.playlist');
                const user_top_wrapper = $('.user-top');
                const compare_results = $('.compare-results');
                const compare_artists = compare_results.find('.artists');
                const compare_tracks = compare_results.find('.tracks');
                const compare_total = compare_results.find('.total');
                const ut_tracks_wrapper = user_top_wrapper.find('.tracks');
                const ut_artists_wrapper = user_top_wrapper.find('.artists');
                const user_top_artists = spotifyCurl('getUserTopArtists').items;
                const user_top_tracks = spotifyCurl('getUserTopTracks').items;

                // Load the playlist onto the screen.
                $.each(playlist, function (key, item) {
                    const track = item.track;
                    let artists = '';

                    if (track.artists.length > 0) {
                        $.each(track.artists, function (artist_key, artist) {
                            artists += '<div class="artist">' + artist.name + '</div>';
                        });
                    }

                    playlist_wrapper.append('<a id="track-' + key + '" href="' + track.external_urls.spotify + '" target="_blank" class="card mb-1 flex-row">' +
                        '<img class="img-fluid" src="' + track.album.images[0].url + '" alt="cover"/>' +
                        '<div><div class="name">Track: ' + track.name + '</div>' +
                        artists +
                        '</div></a>');
                });

                let artist_matches = [];

                // Load the users top artists onto the screen.
                $.each(user_top_artists, function (key, item) {
                    const append = '<div>' + item.name + '</div>';

                    ut_artists_wrapper.append(append);

                    const matches = $.grep(playlist, function (track) {
                        return $.grep(track.track.artists, function (artist) {
                            return artist.id === item.id;
                        }).length > 0;
                    });

                    if (matches.length > 0) {
                        artist_matches = $.unique($.merge(artist_matches, matches));

                        compare_artists.append(append);
                    }
                });

                let track_matches = [];

                // Load the users top tracks onto the screen.
                $.each(user_top_tracks, function (key, item) {
                    const append = '<div>' + item.name + '</div>';

                    ut_tracks_wrapper.append(append);

                    const matches = $.grep(playlist, function (track) {
                        return track.track.id === item.id;
                    });

                    if (matches.length > 0) {
                        track_matches = $.unique($.merge(track_matches, matches));

                        compare_tracks.append('<div>' + item.name + '</div>');
                    }
                });

                // Calculate and show the total percentages of similarity between the user top and the given playlist.
                let total_matches = $.unique($.merge(track_matches, artist_matches));

                compare_artists.find('h4').append(' ' + artist_matches.length / playlist_size * 100 + '%');
                compare_tracks.find('h4').append(' ' + track_matches.length / playlist_size * 100 + '%');
                compare_total.find('h4').append(' ' + total_matches.length / playlist_size * 100 + '%');

                // Send a post request to the Spotify class. Do this async so the function waits on the post before
                // executing the return.
                function spotifyCurl(func) {
                    $.ajaxSetup({async: false});

                    let result = false;

                    $.post('actionhandler.php', {func: func}, function (data) {
                        result = $.parseJSON(data);
                    });

                    $.ajaxSetup({async: true});

                    return result;
                }
            });
        </script>
    <?php } ?>
</body>
</html>