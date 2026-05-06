(function ($) {
    var mediaApi = {
        pickFromPopup: function (url, targetId, mode) {
            if (window.opener && window.opener.YuexiaMediaPicker) {
                window.opener.YuexiaMediaPicker.receiveSelection(url, targetId, mode || 'field');
                window.close();
            }
        },
        receiveSelection: function (url, targetId, mode) {
            var target = document.getElementById(targetId);
            if (!target) {
                return;
            }

            if (mode === 'editor') {
                insertAtCursor(target, '<p><img src="' + url + '" alt=""></p>');
            } else {
                target.value = url;
                target.dispatchEvent(new Event('input', { bubbles: true }));
            }
        }
    };

    window.YuexiaMediaPicker = mediaApi;

    function insertAtCursor(textarea, text) {
        var start = textarea.selectionStart || 0;
        var end = textarea.selectionEnd || 0;
        var value = textarea.value || '';
        textarea.value = value.slice(0, start) + text + value.slice(end);
        textarea.focus();
        textarea.selectionStart = textarea.selectionEnd = start + text.length;
        textarea.dispatchEvent(new Event('input', { bubbles: true }));
    }

    function wrapSelection(textarea, before, after, fallback) {
        var start = textarea.selectionStart || 0;
        var end = textarea.selectionEnd || 0;
        var value = textarea.value || '';
        var selected = value.slice(start, end) || fallback;
        var text = before + selected + after;
        textarea.value = value.slice(0, start) + text + value.slice(end);
        textarea.focus();
        textarea.selectionStart = start + before.length;
        textarea.selectionEnd = start + before.length + selected.length;
        textarea.dispatchEvent(new Event('input', { bubbles: true }));
    }

    function buildList(textarea) {
        var start = textarea.selectionStart || 0;
        var end = textarea.selectionEnd || 0;
        var value = textarea.value || '';
        var selected = value.slice(start, end) || '条目一\n条目二';
        var items = selected.split(/\r?\n/).filter(function (item) { return item.trim() !== ''; });
        if (!items.length) {
            items = ['条目一', '条目二'];
        }
        var listHtml = '<ul>' + items.map(function (item) { return '<li>' + item + '</li>'; }).join('') + '</ul>';
        textarea.value = value.slice(0, start) + listHtml + value.slice(end);
        textarea.focus();
        textarea.selectionStart = textarea.selectionEnd = start + listHtml.length;
        textarea.dispatchEvent(new Event('input', { bubbles: true }));
    }

    function bindRichEditors() {
        document.querySelectorAll('[data-rich-editor]').forEach(function (editor) {
            var textarea = editor.querySelector('.rich-editor__input');
            var preview = editor.querySelector('.rich-editor__preview');
            if (!textarea || !preview) {
                return;
            }

            function refreshPreview() {
                preview.innerHTML = textarea.value;
            }

            refreshPreview();
            textarea.addEventListener('input', refreshPreview);

            editor.querySelectorAll('[data-editor-command]').forEach(function (button) {
                button.addEventListener('click', function () {
                    var command = button.getAttribute('data-editor-command');
                    if (command === 'preview') {
                        var hidden = preview.hasAttribute('hidden');
                        if (hidden) {
                            refreshPreview();
                            preview.removeAttribute('hidden');
                            button.classList.add('is-active');
                        } else {
                            preview.setAttribute('hidden', 'hidden');
                            button.classList.remove('is-active');
                        }
                        return;
                    }
                    if (command === 'strong') {
                        wrapSelection(textarea, '<strong>', '</strong>', '加粗文字');
                        return;
                    }
                    if (command === 'em') {
                        wrapSelection(textarea, '<em>', '</em>', '斜体文字');
                        return;
                    }
                    if (command === 'h2') {
                        wrapSelection(textarea, '<h2>', '</h2>', '二级标题');
                        return;
                    }
                    if (command === 'h3') {
                        wrapSelection(textarea, '<h3>', '</h3>', '三级标题');
                        return;
                    }
                    if (command === 'p') {
                        wrapSelection(textarea, '<p>', '</p>', '段落内容');
                        return;
                    }
                    if (command === 'quote') {
                        wrapSelection(textarea, '<blockquote>', '</blockquote>', '引用内容');
                        return;
                    }
                    if (command === 'code') {
                        wrapSelection(textarea, '<pre><code>', '</code></pre>', '代码片段');
                        return;
                    }
                    if (command === 'ul') {
                        buildList(textarea);
                        return;
                    }
                    if (command === 'link') {
                        var link = window.prompt('请输入链接地址', 'https://');
                        if (!link) {
                            return;
                        }
                        wrapSelection(textarea, '<a href="' + link + '" target="_blank">', '</a>', '链接文字');
                        return;
                    }
                    if (command === 'image') {
                        var image = window.prompt('请输入图片地址', '/uploads/');
                        if (!image) {
                            return;
                        }
                        insertAtCursor(textarea, '<p><img src="' + image + '" alt=""></p>');
                    }
                });
            });
        });
    }

    function updatePreviewFor(input) {
        var preview = document.querySelector('[data-preview-for="' + input.id + '"]');
        if (!preview) {
            return;
        }
        var url = (input.value || '').trim();
        preview.innerHTML = url ? '<img src="' + url + '" alt="preview">' : '';
    }

    function bindMediaFields() {
        document.querySelectorAll('.media-open-btn').forEach(function (button) {
            button.addEventListener('click', function () {
                var target = button.getAttribute('data-media-target');
                var mode = button.getAttribute('data-media-mode') || 'field';
                var url = '/admin/media?picker=1&target=' + encodeURIComponent(target) + '&mode=' + encodeURIComponent(mode);
                window.open(url, 'yuexia-media-picker', 'width=1120,height=760');
            });
        });

        document.querySelectorAll('.media-field-group input[type="text"]').forEach(function (input) {
            updatePreviewFor(input);
            input.addEventListener('input', function () {
                updatePreviewFor(input);
            });
        });
    }

    function bindCopyButtons() {
        document.querySelectorAll('.media-copy-btn').forEach(function (button) {
            button.addEventListener('click', function () {
                var url = button.getAttribute('data-copy-url') || '';
                if (!url) {
                    return;
                }
                if (navigator.clipboard && navigator.clipboard.writeText) {
                    navigator.clipboard.writeText(url);
                } else {
                    window.prompt('请手动复制这个地址', url);
                }
                button.textContent = '已复制';
                window.setTimeout(function () {
                    button.textContent = '复制地址';
                }, 1200);
            });
        });
    }

    var $slides = $('.banner-card');
    var total = $slides.length;
    var current = 0;
    var timer = null;

    function buildDots() {
        var $dots = $('.banner-dots');
        $slides.each(function (index) {
            var $dot = $('<button class="banner-dot" type="button" aria-label="slide ' + (index + 1) + '"></button>');
            $dot.on('click', function () {
                goTo(index);
                restart();
            });
            $dots.append($dot);
        });
    }

    function updateSlider() {
        $slides.removeClass('is-active').eq(current).addClass('is-active');
        $('.banner-dot').removeClass('is-active').eq(current).addClass('is-active');
    }

    function goTo(index) {
        current = (index + total) % total;
        updateSlider();
    }

    function next() {
        goTo(current + 1);
    }

    function start() {
        if (total < 2) {
            return;
        }
        timer = window.setInterval(next, 4500);
    }

    function stop() {
        if (timer) {
            window.clearInterval(timer);
            timer = null;
        }
    }

    function restart() {
        stop();
        start();
    }

    function bindSlider() {
        $('.banner-next').on('click', function () {
            next();
            restart();
        });

        $('.banner-prev').on('click', function () {
            goTo(current - 1);
            restart();
        });

        $('.banner-shell').on('mouseenter', stop).on('mouseleave', start);
    }

    function revealOnScroll() {
        var winBottom = $(window).scrollTop() + $(window).height();
        $('.reveal-up').each(function () {
            var $el = $(this);
            if (winBottom > $el.offset().top + 60) {
                $el.addClass('is-visible');
            }
        });
    }

    function countUp() {
        $('.counter').each(function () {
            var $el = $(this);
            if ($el.data('done')) {
                return;
            }
            var winBottom = $(window).scrollTop() + $(window).height();
            if (winBottom <= $el.offset().top) {
                return;
            }
            $el.data('done', true);
            $({ value: 0 }).animate({ value: parseInt($el.data('target'), 10) || 0 }, {
                duration: 1200,
                easing: 'swing',
                step: function () {
                    $el.text(Math.floor(this.value));
                },
                complete: function () {
                    $el.text(parseInt($el.data('target'), 10) || 0);
                }
            });
        });
    }

    function updateHeader() {
        $('.site-header').toggleClass('is-scrolled', $(window).scrollTop() > 16);
    }

    function bindAnchors() {
        $('.site-nav a, .btn[href^="#"]').on('click', function (event) {
            var target = $(this).attr('href');
            if (!target || target.charAt(0) !== '#') {
                return;
            }
            var $target = $(target);
            if (!$target.length) {
                return;
            }
            event.preventDefault();
            $('html, body').animate({ scrollTop: $target.offset().top - 76 }, 700);
        });
    }

    function bindDepth() {
        $('[data-depth-zone]').on('mousemove', function (event) {
            var $zone = $(this);
            var offset = $zone.offset();
            var x = event.pageX - offset.left;
            var y = event.pageY - offset.top;
            var rotateY = (x / $zone.outerWidth() - 0.5) * 16;
            var rotateX = (0.5 - y / $zone.outerHeight()) * 16;
            $('.banner-shell').css('transform', 'rotateY(' + rotateY + 'deg) rotateX(' + rotateX + 'deg)');
        }).on('mouseleave', function () {
            $('.banner-shell').css('transform', 'rotateY(0deg) rotateX(0deg)');
        });
    }

    function hoverFloat() {
        $('.work-card, .feature-card').on('mouseenter', function () {
            $(this).stop(true, false).animate({ marginTop: '-6px' }, 180);
        }).on('mouseleave', function () {
            $(this).stop(true, false).animate({ marginTop: '0px' }, 180);
        });
    }

    if (total) {
        buildDots();
        updateSlider();
        bindSlider();
        start();
    }

    bindAnchors();
    bindDepth();
    hoverFloat();
    revealOnScroll();
    countUp();
    updateHeader();
    bindRichEditors();
    bindMediaFields();
    bindCopyButtons();

    $(window).on('scroll', function () {
        revealOnScroll();
        countUp();
        updateHeader();
    });
})(jQuery);
