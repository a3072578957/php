(function (global) {
    function toArray(input) {
        if (!input) return [];
        if (Array.isArray(input)) return input;
        if (input instanceof Wrapper) return input.elements;
        if (input === window || input === document || input instanceof Element || input instanceof Node) return [input];
        if (input instanceof NodeList || input instanceof HTMLCollection) return Array.from(input);
        return [];
    }

    function createElement(html) {
        var template = document.createElement('template');
        template.innerHTML = html.trim();
        return template.content.firstChild;
    }

    function Wrapper(elements, rawObject) {
        this.elements = elements || [];
        this.rawObject = rawObject || null;
        this.length = this.elements.length;
    }

    Wrapper.prototype.each = function (callback) {
        this.elements.forEach(function (el, index) {
            callback.call(el, index, el);
        });
        return this;
    };

    Wrapper.prototype.eq = function (index) {
        return new Wrapper(this.elements[index] ? [this.elements[index]] : []);
    };

    Wrapper.prototype.on = function (event, handler) {
        return this.each(function () {
            this.addEventListener(event, handler);
        });
    };

    Wrapper.prototype.append = function (child) {
        var nodes = child instanceof Wrapper ? child.elements : (typeof child === 'string' ? [createElement(child)] : toArray(child));
        return this.each(function () {
            var parent = this;
            nodes.forEach(function (node, index) {
                parent.appendChild(index === 0 ? node : node.cloneNode(true));
            });
        });
    };

    Wrapper.prototype.addClass = function (name) {
        return this.each(function () { this.classList.add(name); });
    };

    Wrapper.prototype.removeClass = function (name) {
        return this.each(function () { this.classList.remove(name); });
    };

    Wrapper.prototype.toggleClass = function (name, force) {
        return this.each(function () { this.classList.toggle(name, force); });
    };

    Wrapper.prototype.text = function (value) {
        if (typeof value === 'undefined') {
            return this.elements[0] ? this.elements[0].textContent : '';
        }
        return this.each(function () { this.textContent = value; });
    };

    Wrapper.prototype.attr = function (name, value) {
        if (typeof value === 'undefined') {
            return this.elements[0] ? this.elements[0].getAttribute(name) : undefined;
        }
        return this.each(function () { this.setAttribute(name, value); });
    };

    Wrapper.prototype.data = function (name, value) {
        var key = name.replace(/-([a-z])/g, function (_, c) { return c.toUpperCase(); });
        if (typeof value === 'undefined') {
            if (!this.elements[0]) return undefined;
            if (this.elements[0].dataset && key in this.elements[0].dataset) {
                return this.elements[0].dataset[key];
            }
            return this.elements[0]['__data_' + key];
        }
        return this.each(function () {
            if (this.dataset) {
                this.dataset[key] = value;
            }
            this['__data_' + key] = value;
        });
    };

    Wrapper.prototype.css = function (prop, value) {
        if (typeof value === 'undefined' && typeof prop === 'string') {
            return this.elements[0] ? this.elements[0].style[prop] : undefined;
        }
        return this.each(function () {
            if (typeof prop === 'object') {
                for (var key in prop) {
                    this.style[key] = prop[key];
                }
            } else {
                this.style[prop] = value;
            }
        });
    };

    Wrapper.prototype.offset = function () {
        if (!this.elements[0]) return { top: 0, left: 0 };
        var rect = this.elements[0].getBoundingClientRect();
        return { top: rect.top + window.pageYOffset, left: rect.left + window.pageXOffset };
    };

    Wrapper.prototype.outerWidth = function () {
        return this.elements[0] ? this.elements[0].offsetWidth : 0;
    };

    Wrapper.prototype.outerHeight = function () {
        return this.elements[0] ? this.elements[0].offsetHeight : 0;
    };

    Wrapper.prototype.height = function () {
        if (!this.elements[0]) return 0;
        if (this.elements[0] === window) return window.innerHeight;
        return this.elements[0].clientHeight;
    };

    Wrapper.prototype.scrollTop = function (value) {
        if (typeof value === 'undefined') {
            if (!this.elements[0]) return 0;
            return this.elements[0] === window ? window.pageYOffset : this.elements[0].scrollTop;
        }
        return this.each(function () {
            if (this === window) {
                window.scrollTo(window.pageXOffset, value);
            } else {
                this.scrollTop = value;
            }
        });
    };

    Wrapper.prototype.stop = function () {
        return this;
    };

    Wrapper.prototype.animate = function (props, options) {
        var settings = typeof options === 'number' ? { duration: options } : (options || {});
        var duration = settings.duration || 400;
        var startTime = performance.now();
        var targets = this.elements.length ? this.elements : [this.rawObject || {}];

        targets.forEach(function (target) {
            var initial = {};
            Object.keys(props).forEach(function (key) {
                if (key === 'scrollTop') {
                    initial[key] = target === window || target === document.documentElement || target === document.body ? window.pageYOffset : parseFloat(target.scrollTop || 0);
                } else if (target.style && target.style[key]) {
                    initial[key] = parseFloat(target.style[key]) || 0;
                } else {
                    initial[key] = parseFloat(target[key]) || 0;
                }
            });

            function tick(now) {
                var progress = Math.min(1, (now - startTime) / duration);
                Object.keys(props).forEach(function (key) {
                    var end = parseFloat(props[key]);
                    var current = initial[key] + (end - initial[key]) * progress;
                    if (key === 'scrollTop') {
                        window.scrollTo(window.pageXOffset, current);
                    } else if (target.style && key in target.style) {
                        target.style[key] = current + (String(props[key]).indexOf('px') > -1 ? 'px' : '');
                    } else {
                        target[key] = current;
                    }
                    if (typeof settings.step === 'function') {
                        settings.step.call({ value: current });
                    }
                });

                if (progress < 1) {
                    requestAnimationFrame(tick);
                } else if (typeof settings.complete === 'function') {
                    settings.complete.call(target);
                }
            }

            requestAnimationFrame(tick);
        });

        return this;
    };

    function $(selector) {
        if (typeof selector === 'function') {
            if (document.readyState === 'loading') {
                document.addEventListener('DOMContentLoaded', selector);
            } else {
                selector();
            }
            return new Wrapper([]);
        }

        if (typeof selector === 'string') {
            if (selector.trim().charAt(0) === '<') {
                return new Wrapper([createElement(selector)]);
            }
            return new Wrapper(Array.from(document.querySelectorAll(selector)));
        }

        if (selector instanceof Wrapper) {
            return selector;
        }

        if (selector && typeof selector === 'object' && !(selector instanceof Element) && !(selector instanceof NodeList) && selector !== window && selector !== document) {
            return new Wrapper([], selector);
        }

        return new Wrapper(toArray(selector));
    }

    global.jQuery = global.$ = $;
})(window);
