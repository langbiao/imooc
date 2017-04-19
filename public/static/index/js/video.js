
/**
 * 自定义控制栏
 */
$(function() {
    var
        $player = $('#video-box-mocoplayer-hls-video-html5-api'),
        $play_right = $('#control-play-right'),
        $play_left = $('#control-play-left'),
        $volume = $('#control-volume'),
        $expand = $('#control-fullscreen');

    var player = $player[0];
    var $timer = $('#timer');
    var $timer_total = $('#total');
    var
        $progressBar = $('#progressBar'),
        $innerBar = $('#innerBar'),
        $volumeControl = $('#volume-control-progress'),
        $volumeInner = $('#volume-inner');

    $play_left
        .on('click', function() {
            if (player.paused) {
                player.play();
                $(this).removeClass('vjs-paused').addClass('vjs-playing');
                $play_right.hide();
            } else {
                player.pause();
                $(this).removeClass('vjs-playing').addClass('vjs-paused');
                $play_right.show();
            }
        });

    $volume
        .on('click', function() {
            if (player.muted) {
                player.muted = false;
                $volumeInner.css('width', 100 + '%');
            } else {
                player.muted = true;
                $volumeInner.css('width', 0);
            }
        });

    $expand
        .on('click', function() {
            if (!document.webkitIsFullScreen) {
                player.webkitRequestFullScreen(); //全屏
                $(this).removeClass('icon-expand').addClass('icon-contract');
            } else {
                document.webkitCancelFullScreen();
                $(this).removeClass('icon-contract').addClass('icon-expand');
            }
        });

    $player
        .on('timeupdate', function() {
            //秒数转换
            var time = player.currentTime.toFixed(1),
                minutes = Math.floor((time / 60) % 60),
                seconds = Math.floor(time % 60);

            if (seconds < 10) {
                seconds = '0' + seconds;
            }
            $timer.text(minutes + ':' + seconds);

            var w = $progressBar.width();
            if (player.duration) {

                var time_total = player.duration.toFixed(1),
                    minutes_total = Math.floor((time_total / 60) % 60),
                    seconds_total = Math.floor(time_total % 60);

                if (seconds_total < 10) {
                    seconds_total = '0' + seconds_total;
                }
                $timer_total.text(minutes_total + ':' + seconds_total);

                var per = (player.currentTime / player.duration).toFixed(3);
                window.per = per;
            } else {
                per = 0;
            }
            $innerBar.css('width', (w * per).toFixed(0) + 'px');

            // if (player.ended) { //播放完毕
            //     $play.removeClass('icon-pause').addClass('icon-play');
            // }
        });

    $progressBar
        .on('click', function(e) {
            var w = $(this).width(),
                x = e.offsetX;
            window.per = (x / w).toFixed(3); //全局变量

            var duration = player.duration;
            player.currentTime = (duration * window.per).toFixed(0);

            $innerBar.css('width', x + 'px');
        });

    $volumeControl
        .on('click', function(e) {
            var w = $(this).width(),
                x = e.offsetX;
            window.vol = (x / w).toFixed(1); //全局变量

            player.volume = window.vol;
            $volumeInner.css('width', x + 'px');
        });

    $(document)
        .on('webkitfullscreenchange', function(e) {
            var w = $progressBar.width(),
                w1 = $volumeControl.width();
            if (window.per) {
                $innerBar.css('width', (window.per * w).toFixed(0) + 'px');
            }
            if (window.vol) {
                $volumeInner.css('width', (window.vol * w1).toFixed(0) + 'px')
            }
        });
});