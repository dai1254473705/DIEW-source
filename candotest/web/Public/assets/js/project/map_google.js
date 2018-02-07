/**
 * Created by Administrator on 2016/7/12.
 */

// 香港详细坐标
var cityLocation = {
    lat: JO.loc.lat ? JO.loc.lat : 22.284609253228098,
    lng: JO.loc.lng ? JO.loc.lng : 114.15829181671143
}, loc = [], map, marker;

var mapOb = {
    init: function(runtimeLoc, move){
        runtimeLoc.lat = runtimeLoc.lat * 1;
        runtimeLoc.lng = runtimeLoc.lng * 1;
        var $runtimeJson = {
            zoom: 15,
            center: runtimeLoc,
            //mapTypeId: google.maps.MapTypeId.TERRAIN //左侧“地图”，显示“地形”
        };
        map = new google.maps.Map(document.getElementById('allmap'), $runtimeJson);

        //添加地图点击监听事件
        map.addListener('click', function (event) {
            mapOb.addMarker(event.latLng, map);
        });

        //初始化时增加一个默认点
        mapOb.addMarker(runtimeLoc, map);
        if (move) {
            map.panTo(runtimeLoc);
        }

    },
    panTo: function(runtimeLoc){
        map.panTo(runtimeLoc);
    },
    addMarker: function(location, map) {

        // 清除
        this.clearMarker();
        marker = new google.maps.Marker({
            position: location,
            map: map
        });

        // 设置值
        var tmpLoc = {
            lat: 0,
            lng: 0
        };
        if (location.lng != undefined) {
            tmpLoc.lng = location.lng;
        }else{
            tmpLoc.lng = location.lng();
        }
        if (location.lat != undefined) {
            tmpLoc.lat = location.lat;
        }else{
            tmpLoc.lat = location.lat();
        }
        mapOb.setValue(tmpLoc.lng, tmpLoc.lat);

    },
    clearMarker: function(){
        if(typeof marker == 'object'){
            marker.setMap(null);
        }
    },
    setValue: function(lng, lat){
        var obj = {
            lng: $('input[name="lng"]'),
            lat: $('input[name="lat"]'),
        };

        obj.lng.val(lng);
        obj.lat.val(lat);
    },
    getValue: function(){
        var setRealValue = {
            lng: $('input[name="lng"]').val() * 1,
            lat: $('input[name="lat"]').val() * 1
        }
        return setRealValue;
    }
};

function initMap(){
    mapOb.init(cityLocation);
}

$('a[href="#tab_1_2"]').click(function(){
    var setReal = mapOb.getValue();
    setTimeout(function(){
        mapOb.init(setReal, false);
    }, 300);

});



$('.portlet .tools a.reload').click(function () {
    mapOb.init(cityLocation);
});

