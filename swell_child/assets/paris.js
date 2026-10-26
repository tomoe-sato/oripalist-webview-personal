/*!
 * パリス（公式キャラクター）のトップページ用パーツ
 * - [paris_hero]   : セリフとポーズを一定間隔で入れ替える（タップでも次へ）
 * - .paris-heading : 見出しの先頭に小さなパリスを入れる
 */
(function () {
    'use strict';

    // セリフを入れ替える間隔（ミリ秒）
    var LINE_INTERVAL = 5000;

    var reduceMotion = window.matchMedia &&
        window.matchMedia('(prefers-reduced-motion: reduce)').matches;

    /* ============================================================
     * パリスのあいさつ
     * ============================================================ */
    function initHero(hero) {
        var lines;
        try {
            lines = JSON.parse(hero.getAttribute('data-paris-lines') || '[]');
        } catch (e) {
            return;
        }
        if (lines.length < 2) {
            return;
        }

        var img = hero.querySelector('.paris-hero__img');
        var text = hero.querySelector('.paris-hero__text');
        var button = hero.querySelector('.paris-hero__paris');
        var dots = hero.querySelectorAll('.paris-hero__dot');
        var current = 0;
        var timer = null;

        // 切り替え時にちらつかないよう画像を先読みする
        lines.forEach(function (line) {
            var preload = new Image();
            preload.src = line.img;
        });

        function show(i) {
            current = i;
            img.src = lines[i].img;
            text.textContent = lines[i].text;
            Array.prototype.forEach.call(dots, function (dot, j) {
                dot.classList.toggle('is-current', j === i);
            });
            if (!reduceMotion) {
                img.classList.remove('is-hop');
                // アニメーションを最初から再生し直す
                void img.offsetWidth;
                img.classList.add('is-hop');
            }
        }

        function next() {
            show((current + 1) % lines.length);
        }

        function start() {
            if (reduceMotion || timer || document.hidden) {
                return;
            }
            timer = window.setInterval(next, LINE_INTERVAL);
        }

        function stop() {
            window.clearInterval(timer);
            timer = null;
        }

        button.addEventListener('click', function () {
            stop();
            next();
            start();
        });

        document.addEventListener('visibilitychange', function () {
            if (document.hidden) {
                stop();
            } else {
                start();
            }
        });

        start();
    }

    /* ============================================================
     * 見出しの横のパリス
     * 追加CSSクラスに paris-heading--search などを付けるとポーズを変えられる
     * ============================================================ */
    function initHeading(heading) {
        var images = window.PARIS_IMAGES || {};
        var pose = 'smile';
        Object.keys(images).forEach(function (key) {
            if (heading.classList.contains('paris-heading--' + key)) {
                pose = key;
            }
        });
        if (!images[pose] || heading.querySelector('.paris-heading__img')) {
            return;
        }

        var img = document.createElement('img');
        img.className = 'paris-heading__img';
        img.src = images[pose];
        img.alt = '';
        img.width = 44;
        img.height = 44;
        heading.insertBefore(img, heading.firstChild);
    }

    function init() {
        Array.prototype.forEach.call(document.querySelectorAll('.paris-hero'), initHero);
        Array.prototype.forEach.call(document.querySelectorAll('.paris-heading'), initHeading);
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }
})();
