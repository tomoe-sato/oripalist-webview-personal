/*!
 * オリパ診断チャート（4タイプ判定版）
 * for オリパリスト WebView
 * version: 1.1
 */
(function () {
    'use strict';

    var ROOT_ID = 'oripa-shindan';

    /* ============================================================
     * ▼ シェア設定（変更する場合はこのブロックだけ編集してください）
     * ============================================================ */

    // アプリDL用URL（共有・コピーで使う唯一のURL。診断ページURLは使いません）
    var ORIPALIST_DOWNLOAD_URL = 'https://play.oripa-list.jp/vvHM/shindanapp';

    // 投稿文の末尾に付けるハッシュタグ
    var SHARE_HASHTAGS = '#オリパタイプ診断 #オリパリスト';

    // 共有ダイアログのタイトル
    var SHARE_TITLE = 'オリパタイプ診断';

    // タイプ別のシェア画像URLと投稿文（キーは TYPES と共通）
    var shareConfig = {
        mild: {
            label: 'マイルド型',
            image: '/wp-content/uploads/2026/06/oripa-type-mild.jpg',
            text: '私のオリパタイプは「マイルド型」でした🌿\n無理なく安心して楽しみたい派らしい。\nあなたはどのタイプ？'
        },
        loop: {
            label: 'ループ型',
            image: '/wp-content/uploads/2026/06/oripa-type-loop.jpg',
            text: '私のオリパタイプは「ループ型」でした🔁\nポイントを回しながら長く楽しみたい派らしい。\nあなたはどのタイプ？'
        },
        highrisk: {
            label: 'ハイリスク型',
            image: '/wp-content/uploads/2026/06/oripa-type-highrisk.jpg',
            text: '私のオリパタイプは「ハイリスク型」でした🔥\n大きな当たりを狙って勝負したい派らしい。\nあなたはどのタイプ？'
        },
        event: {
            label: 'イベント型',
            image: '/wp-content/uploads/2026/06/oripa-type-event.jpg',
            text: '私のオリパタイプは「イベント型」でした🎉\n限定イベントや新しい企画を楽しみたい派らしい。\nあなたはどのタイプ？'
        }
    };

    /* ▲ シェア設定ここまで ============================================ */

    var TYPES = {
        mild: {
            emoji: '🌱',
            name: 'マイルドタイプ',
            image: '/wp-content/uploads/2026/06/oripa-type-mild.jpg',
            tagline: 'コツコツ回収を狙う堅実派',
            desc: '回収率を重視し、リスクを抑えながら着実にアドを取りにいく堅実なプレイスタイル。一撃の夢より、トータルでプラスを積み上げることに満足感を覚えるタイプです。',
            advice: '高還元をうたうオリパや「1/2でアド確定」系がおすすめ。複数のオリパに分散して、ブレを抑えながら遊ぶと相性◎。'
        },
        loop: {
            emoji: '🔁',
            name: 'ループタイプ',
            image: '/wp-content/uploads/2026/06/oripa-type-loop.jpg',
            tagline: '安心して長く遊ぶ安定派',
            desc: '最低保証や実質単価を重視し、大きく負けないことを最優先する安定志向。低単価のオリパをコツコツ回し続けて、長く楽しむことに価値を感じるタイプです。',
            advice: '最低保証50%以上や「実質○円」系の低単価オリパがベストマッチ。100円以下のオリパをループして遊ぶスタイルがハマります。'
        },
        highrisk: {
            emoji: '🔥',
            name: 'ハイリスクタイプ',
            image: '/wp-content/uploads/2026/06/oripa-type-highrisk.jpg',
            tagline: '一撃の夢を追うギャンブラー',
            desc: '回収率より「一撃の夢」を重視する勝負師タイプ。ラストワンや爆アド報告に心を奪われ、高額一点勝負でロマンを追いかけることに最高の興奮を覚えます。',
            advice: 'ラストワン超豪華系や「爆アド報告続出」のオリパが刺さります。資金管理だけは忘れずに、夢の一撃を狙いましょう。'
        },
        event: {
            emoji: '🎪',
            name: 'イベントタイプ',
            image: '/wp-content/uploads/2026/06/oripa-type-event.jpg',
            tagline: '限定・特別設定を狙うイベント派',
            desc: '周年祭や記念キャンペーンなど、期間限定・数量限定で設定が熱くなるオリパを狙って引くタイプ。「今だけ」「ここだけ」の特別な還元やラインナップに最大の魅力を感じます。',
            advice: '周年祭・記念セール・限定コラボなど、設定が引き上げられる期間限定オリパがねらい目。イベント情報をこまめにチェックして、熱いタイミングを逃さず引きましょう。'
        }
    };

    var QUESTIONS = [
        {
            q: 'オリパを引く時、いちばん重視するのは？',
            a: [
                { t: '少しでも回収率が高いこと', k: 'mild', p: 2 },
                { t: '安心してコツコツ遊べること', k: 'loop', p: 2 },
                { t: '一撃の夢があること', k: 'highrisk', p: 3 },
                { t: '開封のワクワク感', k: 'event', p: 2 }
            ]
        },
        {
            q: '残り100口のオリパ。どれを選ぶ？',
            a: [
                { t: '当たりを引けば1.5倍以上増えるもの', k: 'mild', p: 3 },
                { t: '最低保証が50％以上のもの', k: 'loop', p: 3 },
                { t: 'ラストワンが超豪華なもの', k: 'highrisk', p: 3 },
                { t: '周年祭や限定イベント系', k: 'event', p: 3 }
            ]
        },
        {
            q: 'ハズレを引いた時、どう考える？',
            a: [
                { t: '「次で回収できるかも」', k: 'mild', p: 2 },
                { t: '「大きく負けなければOK」', k: 'loop', p: 2 },
                { t: '「次こそ当たりを引く！」', k: 'highrisk', p: 1 },
                { t: '「楽しかったから満足！」', k: 'event', p: 1 }
            ]
        },
        {
            q: '惹かれるオリパ紹介は？',
            a: [
                { t: '「1/2の確率でアド確定!!」', k: 'mild', p: 2 },
                { t: '「実質○円で引ける！」', k: 'loop', p: 2 },
                { t: '「爆アド報告続出！」', k: 'highrisk', p: 2 },
                { t: '「限定演出・お祭り開催中！」', k: 'event', p: 3 }
            ]
        },
        {
            q: 'もし1万円使うなら？',
            a: [
                { t: '複数のオリパに分けて買う', k: 'mild', p: 2 },
                { t: '100円以下のオリパをひたすら引く', k: 'loop', p: 1 },
                { t: '高額一点勝負', k: 'highrisk', p: 2 },
                { t: 'イベントオリパから選ぶ', k: 'event', p: 2 }
            ]
        }
    ];

    var currentQ = 0;
    var history = [];
    var scores = { mild: 0, loop: 0, highrisk: 0, event: 0 };

    function escapeHTML(str) {
        return String(str).replace(/[&<>"']/g, function (c) {
            return ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' })[c];
        });
    }

    function buildIntroHTML() {
        return [
            '<div class="ops ops-intro">',
            '  <img class="ops-intro__cover" src="https://web.oripa-list.jp/wp-content/uploads/2026/07/oripalist-shindan-package.jpg" alt="オリパタイプ診断チャート">',
            '  <button type="button" class="ops-btn ops-btn--primary ops-intro__start">🎯 診断スタート</button>',
            '</div>'
        ].join('');
    }

    function showRoot(root) {
        if (!root) return;
        root.hidden = false;
        root.removeAttribute('hidden');
    }

    function init() {
        var root = document.getElementById(ROOT_ID);
        if (!root) return;
        showRoot(root);
        hideAllShortcodeOutputs();
        root.innerHTML = buildIntroHTML();
        bindIntro(root);
    }

    function bindIntro(root) {
        var btn = root.querySelector('.ops-intro__start');
        if (btn) btn.addEventListener('click', startQuiz);
    }

    function startQuiz() {
        var root = document.getElementById(ROOT_ID);
        currentQ = 0;
        history = [];
        scores = { mild: 0, loop: 0, highrisk: 0, event: 0 };
        resetShareState();
        showRoot(root);
        hideAllShortcodeOutputs();
        renderQuestion();
    }

    function renderQuestion() {
        var root = document.getElementById(ROOT_ID);
        if (!root) return;

        var q = QUESTIONS[currentQ];
        var total = QUESTIONS.length;
        var num = currentQ + 1;
        var pct = (num / total) * 100;

        var optionsHTML = q.a.map(function (opt, i) {
            return [
                '<button type="button" class="ops-option" data-i="' + i + '">',
                '  <span class="ops-option__letter">' + 'ABCD'.charAt(i) + '</span>',
                '  <span class="ops-option__text">' + escapeHTML(opt.t) + '</span>',
                '</button>'
            ].join('');
        }).join('');

        var backHTML = currentQ > 0
            ? '<button type="button" class="ops-back">← 前の質問にもどる</button>'
            : '';

        root.innerHTML = [
            '<div class="ops ops-quiz">',
            '  <div class="ops-progress">',
            '    <div class="ops-progress__label">🎲 Question ' + num + ' / ' + total + '</div>',
            '    <div class="ops-progress__track"><div class="ops-progress__fill" style="width:' + pct + '%"></div></div>',
            '  </div>',
            '  <div class="ops-question-card">',
            '    <h3 class="ops-question">' + escapeHTML(q.q) + '</h3>',
            '  </div>',
            '  <div class="ops-options">' + optionsHTML + '</div>',
            '  ' + backHTML,
            '</div>'
        ].join('');

        var optionBtns = root.querySelectorAll('.ops-option');
        for (var i = 0; i < optionBtns.length; i++) {
            optionBtns[i].addEventListener('click', onAnswer);
        }
        var backBtn = root.querySelector('.ops-back');
        if (backBtn) backBtn.addEventListener('click', onBack);
    }

    function onAnswer(e) {
        var btn = e.currentTarget;
        var i = parseInt(btn.getAttribute('data-i'), 10);
        var opt = QUESTIONS[currentQ].a[i];
        scores[opt.k] += opt.p;
        history.push(opt);
        currentQ++;
        if (currentQ >= QUESTIONS.length) {
            renderResult();
        } else {
            renderQuestion();
        }
    }

    function onBack() {
        var last = history.pop();
        if (last) scores[last.k] -= last.p;
        currentQ--;
        renderQuestion();
    }

    function determineWinner() {
        var order = ['mild', 'loop', 'highrisk', 'event'];
        var winner = order[0];
        for (var i = 1; i < order.length; i++) {
            if (scores[order[i]] > scores[winner]) winner = order[i];
        }
        return winner;
    }

    function renderResult() {
        var root = document.getElementById(ROOT_ID);
        if (!root) return;

        var winner = determineWinner();
        var top = TYPES[winner];
        var maxScore = scores[winner];
        var order = ['mild', 'loop', 'highrisk', 'event'];

        var scoreRowsHTML = order.map(function (k) {
            var pct = maxScore > 0 ? Math.round(scores[k] / maxScore * 100) : 0;
            var isWin = k === winner;
            var nameShort = TYPES[k].name.replace('タイプ', '');
            return [
                '<div class="ops-score-row' + (isWin ? ' is-win' : '') + '">',
                '  <span class="ops-score-row__name">' + TYPES[k].emoji + ' ' + escapeHTML(nameShort) + '</span>',
                '  <div class="ops-score-row__track"><div class="ops-score-row__fill" style="width:' + pct + '%"></div></div>',
                '  <span class="ops-score-row__value">' + pct + '%</span>',
                '</div>'
            ].join('');
        }).join('');

        var imageHTML = top.image
            ? '  <img class="ops-result__image" src="' + escapeHTML(top.image) + '" alt="' + escapeHTML(top.name) + 'の診断結果画像">'
            : '';

        var shareHTML = shareConfig[winner]
            ? [
                '  <div class="ops-share">',
                '    <button type="button" class="ops-share__main" data-ops-share>📣 診断結果をシェアする</button>',
                '    <div class="ops-share__sub">',
                '      <button type="button" class="ops-share__btn" data-ops-save>🖼️ 結果画像を保存する</button>',
                '      <button type="button" class="ops-share__btn" data-ops-copy>📋 投稿文をコピーする</button>',
                '    </div>',
                '  </div>'
            ].join('')
            : '';

        root.innerHTML = [
            '<div class="ops ops-result ops-type-' + winner + ' ops-fade-in">',
            '  <div class="ops-result__eyebrow">✨ 診断結果 ✨</div>',
            imageHTML,
            '  <p class="ops-result__desc">' + escapeHTML(top.desc) + '</p>',
            '  <div class="ops-result__advice">',
            '    <div class="ops-result__advice-label">おすすめの遊び方</div>',
            '    <p>' + escapeHTML(top.advice) + '</p>',
            '  </div>',
            '  <div class="ops-result__scores">',
            '    <div class="ops-result__scores-label">タイプ別スコア</div>',
            '    ' + scoreRowsHTML,
            '  </div>',
            shareHTML,
            '  <button type="button" class="ops-btn ops-retry">もう一度診断する</button>',
            '</div>'
        ].join('');

        var retryBtn = root.querySelector('.ops-retry');
        if (retryBtn) retryBtn.addEventListener('click', function () {
            resetShareState();
            showRoot(root);
            hideAllShortcodeOutputs();
            root.innerHTML = buildIntroHTML();
            bindIntro(root);
            root.scrollIntoView({ behavior: 'smooth', block: 'start' });
        });

        bindShareButtons(root, winner);
        preloadShareImage(winner);

        revealShortcodeOutput(winner);
        root.scrollIntoView({ behavior: 'smooth', block: 'start' });
    }

    /* ===== シェア機能 ===== */

    // 事前読み込みしたシェア画像を保持する
    var shareState = { type: null, file: null };
    var toastTimer = null;

    function hasDownloadUrl() {
        return /^https?:\/\//i.test(ORIPALIST_DOWNLOAD_URL);
    }

    function buildShareText(key) {
        var conf = shareConfig[key];
        if (!conf) return '';
        var lines = [conf.text];
        if (hasDownloadUrl()) {
            lines.push('', 'オリパリストのアプリで無料診断👇', ORIPALIST_DOWNLOAD_URL);
        }
        lines.push('', SHARE_HASHTAGS);
        return lines.join('\n');
    }

    function shareFileName(key) {
        return 'oripalist-type-' + key + '.png';
    }

    function fetchShareBlob(key) {
        var conf = shareConfig[key];
        if (!conf || !conf.image || !window.fetch) {
            return Promise.reject(new Error('share image unavailable'));
        }
        return fetch(conf.image).then(function (res) {
            if (!res.ok) throw new Error('share image fetch failed');
            return res.blob();
        });
    }

    // 共有ボタンが押される前に画像を読み込んでおく（押下時に同期的に共有できるようにするため）
    function preloadShareImage(key) {
        shareState.type = key;
        shareState.file = null;
        if (typeof File !== 'function') return;
        fetchShareBlob(key).then(function (blob) {
            if (shareState.type !== key) return; // 診断をやり直した後の結果は破棄
            shareState.file = new File([blob], shareFileName(key), { type: blob.type || 'image/png' });
        }).catch(function () {
            // 画像を取得できない場合は投稿文のみで共有する
        });
    }

    function resetShareState() {
        shareState.type = null;
        shareState.file = null;
    }

    function showToast(message) {
        var root = document.getElementById(ROOT_ID);
        if (!root) return;
        var el = root.querySelector('.ops-toast');
        if (!el) {
            el = document.createElement('div');
            el.className = 'ops-toast';
            root.appendChild(el);
        }
        el.textContent = message;
        el.classList.add('is-visible');
        if (toastTimer) clearTimeout(toastTimer);
        toastTimer = setTimeout(function () {
            el.classList.remove('is-visible');
        }, 2800);
    }

    function copyToClipboard(text) {
        if (navigator.clipboard && navigator.clipboard.writeText) {
            return navigator.clipboard.writeText(text);
        }
        return new Promise(function (resolve, reject) {
            var ta = document.createElement('textarea');
            ta.value = text;
            ta.setAttribute('readonly', '');
            ta.style.position = 'fixed';
            ta.style.top = '0';
            ta.style.opacity = '0';
            document.body.appendChild(ta);
            ta.select();
            ta.setSelectionRange(0, ta.value.length);
            var ok = false;
            try {
                ok = document.execCommand('copy');
            } catch (e) {
                ok = false;
            }
            document.body.removeChild(ta);
            if (ok) resolve(); else reject(new Error('copy failed'));
        });
    }

    function copyShareText(key, message) {
        copyToClipboard(buildShareText(key)).then(function () {
            showToast(message);
        }).catch(function () {
            showToast('コピーできませんでした。投稿文を長押しでコピーしてください');
        });
    }

    function onShareClick(key) {
        var fallbackMessage = '投稿文をコピーしました。結果画像を保存して投稿してください';

        // Web Share API 非対応（主にPC）の場合は投稿文コピーで代替
        if (!navigator.share) {
            copyShareText(key, fallbackMessage);
            return;
        }

        var text = buildShareText(key);
        var file = (shareState.type === key) ? shareState.file : null;
        var data = (file && navigator.canShare && navigator.canShare({ files: [file] }))
            ? { files: [file], text: text }
            : { title: SHARE_TITLE, text: text };

        var result;
        try {
            result = navigator.share(data);
        } catch (e) {
            copyShareText(key, fallbackMessage);
            return;
        }
        if (result && result.catch) {
            result.catch(function (err) {
                if (err && err.name === 'AbortError') return; // 共有画面を閉じただけなので何もしない
                copyShareText(key, fallbackMessage);
            });
        }
    }

    function onSaveClick(key) {
        var conf = shareConfig[key];
        if (!conf || !conf.image) return;

        var link = document.createElement('a');
        if (!('download' in link) || !window.URL || !window.URL.createObjectURL) {
            window.open(conf.image, '_blank');
            showToast('画像を長押しして保存してください');
            return;
        }

        var source = (shareState.type === key && shareState.file)
            ? Promise.resolve(shareState.file)
            : fetchShareBlob(key);

        source.then(function (blob) {
            var url = URL.createObjectURL(blob);
            link.href = url;
            link.download = shareFileName(key);
            link.rel = 'noopener';
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);
            setTimeout(function () { URL.revokeObjectURL(url); }, 10000);
            showToast('結果画像を保存しました');
        }).catch(function () {
            window.open(conf.image, '_blank');
            showToast('画像を長押しして保存してください');
        });
    }

    function bindShareButtons(root, key) {
        var shareBtn = root.querySelector('[data-ops-share]');
        if (shareBtn) shareBtn.addEventListener('click', function () { onShareClick(key); });

        var saveBtn = root.querySelector('[data-ops-save]');
        if (saveBtn) saveBtn.addEventListener('click', function () { onSaveClick(key); });

        var copyBtn = root.querySelector('[data-ops-copy]');
        if (copyBtn) copyBtn.addEventListener('click', function () { copyShareText(key, '投稿文をコピーしました'); });
    }

    function hideAllShortcodeOutputs() {
        var root = document.getElementById(ROOT_ID);
        var els = document.querySelectorAll('.oripa-shortcode-output');
        for (var i = 0; i < els.length; i++) {
            if (root && els[i] === root) continue;
            els[i].hidden = true;
        }
    }

    function revealShortcodeOutput(type) {
        var els = document.querySelectorAll('.oripa-shortcode-output');
        for (var i = 0; i < els.length; i++) {
            els[i].hidden = els[i].getAttribute('data-ops-type') !== type;
        }
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }
})();
