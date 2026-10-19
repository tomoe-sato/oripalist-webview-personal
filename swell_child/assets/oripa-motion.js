/*!
 * オリパリスト WebView 動きのある表示
 * - .oripa-slider : 自動スワイプのカルーセル（指でのスワイプも可）
 * - .oripa-reveal : 画面に入ったらフェードイン
 */
(function () {
    'use strict';

    // 自動で次のスライドへ進む間隔（ミリ秒）
    var AUTOPLAY_INTERVAL = 4000;

    // 指で操作したあと、自動スワイプを再開するまでの待ち時間（ミリ秒）
    var RESUME_DELAY = 6000;

    var reduceMotion = window.matchMedia &&
        window.matchMedia('(prefers-reduced-motion: reduce)').matches;

    /* ============================================================
     * カルーセル
     * ============================================================ */
    function initSlider(track) {
        var slides = Array.prototype.filter.call(track.children, function (el) {
            return !el.classList.contains('blocks-gallery-caption');
        });
        if (slides.length < 2) {
            return;
        }

        var wrap = document.createElement('div');
        wrap.className = 'oripa-slider-wrap';
        track.parentNode.insertBefore(wrap, track);
        wrap.appendChild(track);

        var dotsEl = document.createElement('div');
        dotsEl.className = 'oripa-slider__dots';
        var dots = slides.map(function (slide, i) {
            var dot = document.createElement('button');
            dot.type = 'button';
            dot.className = 'oripa-slider__dot';
            dot.setAttribute('aria-label', (i + 1) + '枚目を表示');
            dot.addEventListener('click', function () {
                goTo(i);
                pauseThenResume();
            });
            dotsEl.appendChild(dot);
            return dot;
        });
        wrap.appendChild(dotsEl);

        var current = -1;
        var timer = null;
        var resumeTimer = null;
        var inView = true;

        function slideOffset(i) {
            var slide = slides[i];
            return slide.offsetLeft - (track.clientWidth - slide.offsetWidth) / 2;
        }

        function goTo(i) {
            track.scrollTo({
                left: slideOffset(i),
                behavior: reduceMotion ? 'auto' : 'smooth'
            });
        }

        function nearestIndex() {
            var center = track.scrollLeft + track.clientWidth / 2;
            var best = 0;
            var bestDist = Infinity;
            slides.forEach(function (slide, i) {
                var dist = Math.abs(slide.offsetLeft + slide.offsetWidth / 2 - center);
                if (dist < bestDist) {
                    bestDist = dist;
                    best = i;
                }
            });
            return best;
        }

        function setCurrent(i) {
            if (i === current) {
                return;
            }
            current = i;
            slides.forEach(function (slide, j) {
                slide.classList.toggle('is-current', j === i);
            });
            dots.forEach(function (dot, j) {
                dot.setAttribute('aria-current', j === i ? 'true' : 'false');
            });
        }

        function next() {
            goTo((nearestIndex() + 1) % slides.length);
        }

        function start() {
            if (reduceMotion || timer || !inView || document.hidden) {
                return;
            }
            timer = window.setInterval(next, AUTOPLAY_INTERVAL);
        }

        function stop() {
            window.clearInterval(timer);
            timer = null;
        }

        function pauseThenResume() {
            stop();
            window.clearTimeout(resumeTimer);
            resumeTimer = window.setTimeout(start, RESUME_DELAY);
        }

        var ticking = false;
        track.addEventListener('scroll', function () {
            if (ticking) {
                return;
            }
            ticking = true;
            window.requestAnimationFrame(function () {
                ticking = false;
                setCurrent(nearestIndex());
            });
        }, { passive: true });

        // 指で触っている間は自動スワイプを止める
        track.addEventListener('touchstart', function () {
            stop();
            window.clearTimeout(resumeTimer);
        }, { passive: true });
        track.addEventListener('touchend', pauseThenResume, { passive: true });
        track.addEventListener('mouseenter', stop);
        track.addEventListener('mouseleave', start);

        // 画面外・アプリがバックグラウンドの間は止める
        if ('IntersectionObserver' in window) {
            new IntersectionObserver(function (entries) {
                inView = entries[0].isIntersecting;
                if (inView) {
                    start();
                } else {
                    stop();
                }
            }).observe(track);
        }
        document.addEventListener('visibilitychange', function () {
            if (document.hidden) {
                stop();
            } else {
                start();
            }
        });

        // 最初のスライドを中央に置く
        track.scrollLeft = slideOffset(0);
        setCurrent(0);
        start();
    }

    /* ============================================================
     * フェードイン
     * ============================================================ */
    function initReveal() {
        var targets = document.querySelectorAll('.oripa-reveal');
        if (!targets.length) {
            return;
        }
        if (reduceMotion || !('IntersectionObserver' in window)) {
            Array.prototype.forEach.call(targets, function (el) {
                el.classList.add('is-inview');
            });
            return;
        }
        var observer = new IntersectionObserver(function (entries) {
            entries.forEach(function (entry) {
                if (entry.isIntersecting) {
                    entry.target.classList.add('is-inview');
                    observer.unobserve(entry.target);
                }
            });
        }, { threshold: 0.15 });
        Array.prototype.forEach.call(targets, function (el) {
            observer.observe(el);
        });
    }

    function init() {
        Array.prototype.forEach.call(document.querySelectorAll('.oripa-slider'), initSlider);
        initReveal();
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }
})();
