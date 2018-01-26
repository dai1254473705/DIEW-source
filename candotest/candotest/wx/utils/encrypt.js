var Base64 = require('./base64.js');
var ENC = {
  config: {
    key: 'iUoYu23z8njUkeL'
  },
  encrypt: function (strings) {
    var key = this.config.key;
    var strings = Base64.encode(strings);
    var len = key.length;
    var code = '';
    for (var i = 0; i < strings.length; i++) {
      var k = i % len;
      code += String.fromCharCode(strings.charCodeAt(i) ^ key.charCodeAt(k));
    }
    return Base64.encode(code);
  },

  decrypt: function (strings) {
    var key = 'e10adc3949ba59abbe56e057f20f883e';
    var strings = base64.encode(strings);
    var len = key.length;
    var code = '';
    for (var i = 0; i < strings.length; i++) {
      var k = i % len;
      code += String.fromCharCode(strings.charCodeAt(i) ^ key.charCodeAt(k));
    }
    return base64.encode(code);
  }
}

module.exports = ENC; //最后加上这个