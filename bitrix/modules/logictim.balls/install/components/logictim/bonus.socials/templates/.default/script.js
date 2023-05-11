window.socialShareCallback = (function () {
    function loadJs(id, src, onload) {
        var js, fjs = document.getElementsByTagName('script')[0];
        if (typeof onload !== 'function') onload = function () {};
        if (document.getElementById(id)) return document.getElementById(id);
        js = document.createElement('script');
        js.id = id;
        js.type = 'text/javascript';
        js.src = src;
        js.async = true;
        js.onload = onload;
        fjs.parentNode.insertBefore(js, fjs);
        return js;
    }

    function FacebookShare(appId, initCallback) {
        window.fbAsyncInit = function () {
            FB.init({
                appId: appId,
                xfbml: true,
                version: 'v2.7'
            });
            initCallback();
        };

        loadJs('facebook-jssdk', '//connect.facebook.net/ru_RU/sdk.js');

        this.share = function (button, url, callback) {
            function _share() {
                button.removeEventListener('click', _share);
                FB.ui({
                        method: 'share',
                        mobile_iframe: true,
                        href: url
                    }, function (response) { if (response !== undefined) callback(); }
                );
            }

            button.addEventListener('click', _share);
        };
    }

    function VkShare(appId, initCallback) {
        window.vkAsyncInit = function () {
            VK.init({
                apiId: appId
            });
            initCallback();
        };

        loadJs('vk-openapi', '//vk.com/js/api/openapi.js');

        this.share = function (button, url, callback) {
            var interval = 0;
            var container = document.createElement('div');
            var original = document.createElement('div');
            container.style.position = 'absolute';
            container.style.height = window.getComputedStyle(buttons.vk).height;
            container.style.width = window.getComputedStyle(buttons.vk).width;
            container.style.overflow = 'hidden';
            //container.style.opacity = 0;
            original.id = 'vk_share_' + new Date().getTime();
            original.style.left = '-2px';
            container.appendChild(original);
            button.appendChild(container);

            VK.Observer.subscribe('widgets.like.shared', function () {
                VK.Observer.unsubscribe('widgets.like.shared');
                button.removeChild(container);
                callback();
            });

            VK.Widgets.Like(original.id, {
                type: 'mini',
                height: 30,
                pageUrl: url,
                //pageImage: 'image.png'
            });

            interval = setInterval(function () {
                if (original.childElementCount > 0) {
                    clearInterval(interval);
                    original.childNodes[0].addEventListener('load', function (event) {
                        //original.style.left = '-100%';
                        //event.target.style['-ms-zoom'] = '2';
                        //event.target.style['-moz-transform'] = 'scale(2)';
                        //event.target.style['-o-transform'] = 'scale(2)';
                        //event.target.style['-webkit-transform'] = 'scale(2)';
                    });
                }
            }, 50);
        };
    }

    function OdnoklassnikiShare(initCallback) {
        loadJs('ok_shareWidget', '//connect.ok.ru/connect.js', function () {
            initCallback();
        });

        this.share = function (button, url, callback) {
            var container = document.createElement('div');
            var original = document.createElement('div');
            container.style.position = 'absolute';
            container.style.height = window.getComputedStyle(buttons.vk).height;
            container.style.width = window.getComputedStyle(buttons.vk).width;
            container.style.overflow = 'hidden';
            //container.style.opacity = 0;
            original.id = 'odnoklassniki_share_' + new Date().getTime();
            container.appendChild(original);
            button.appendChild(container);

            OK.CONNECT.insertShareWidget(
                original.id,
                url,
                '{"sz":30,"st":"straight"}'
            );

            function _handleMessage(event) {
                if (event.origin.match(/^http(s)?:\/\/connect.ok.ru$/g) && event.data.match(/^ok_shared\$.*$/g)) {
                    window.removeEventListener('message', _handleMessage);
                    button.removeChild(container);
                    callback();
                }
            }

            window.addEventListener('message', _handleMessage);
        }
    }

    return {
        FacebookShare: FacebookShare,
        VkShare: VkShare,
        OdnoklassnikiShare: OdnoklassnikiShare
    }
})();