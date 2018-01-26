(function () {
    var client = {};
    
    //Document对象数据
    if (document) {
        client.domain = document.domain || '';
        client.url = document.URL || '';
        client.title = document.title || '';
        client.referrer = document.referrer || '';
        client.width = jQuery(window).width() || 0;
        client.height = jQuery(window).height() || 0;
        client.documentWidth = jQuery(document).width() || 0;
        client.documentHeight = jQuery(document).height() || 0;
    }
    
    //Window对象数据
    if (window && window.screen) {
        client.sh = window.screen.height || 0;
        client.sw = window.screen.width || 0;
        client.cd = window.screen.colorDepth || 0;
    }
    
    //navigator对象数据
    if (navigator) {
        client.lang = navigator.language || '';
    }
    
    //解析_maq配置
    var _maq = _maq || [];
    _maq.push(['_setAccount', '网站标识']);
    if (_maq) {
        for (var i in _maq) {
            switch (_maq[i][0]) {
                case '_setAccount':
                    client.account = _maq[i][1];
                    break;
                default:
                    break;
            }
        }
    }
    
    //拼接参数串
    var args = '';
    for (var i in client) {
        if (args != '') {
            args += '&';
        }
        args += i + '=' + encodeURIComponent(client[i]);
    }


    if (CONF.client == undefined) {
        jQuery.post(CONF.client_url, client, {});
    }
    
})();