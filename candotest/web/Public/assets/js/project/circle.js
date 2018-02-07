
/**
 * 圈子添加 页面处理js
 * @param {type} msg
 * @returns {undefined}
 */

$(function () {
    var com = {
        switchTable: function(oid){
            var id = oid;
            $('.J_tabbable li').removeClass('active');
            $('.J_tab_pane').removeClass('active');

            $('a[href="#'+id+'"]').closest('li').addClass('active');
            $('#'+id).addClass('active');
        },
        switchSubTable: function(oid){
            com.switchTable('tab_1_1');
            var id = oid;
            $('.J_sub_tabbable li').removeClass('active');
            $('.J_sub_tab_pane').removeClass('active');

            $('a[href="#'+id+'"]').closest('li').addClass('active');
            $('#'+id).addClass('active');
        },

    }
    
    //异步用户信息
//    var ele = $('.J_title_o');
//    $('input[name="user_id"]').blur(function(){
//        var _this = $(this);
//        var user_id = $(this).val();
//        if(!user_id){
//            return false;
//        }
//        
//        $.ajax({
//            url: urls.getUserContact,
//            dataType: 'json',
//            data: {user_id:user_id},
//            type: "POST",
//            beforeSend: function () {
//                ele.removeClass('hide');
//                App.blockUI(ele);
//            },
//            success: function(data){
//                App.unblockUI(ele);
//                if(data.ok){
//                    $('input[name="contact_user"]').val(data.msg.truename);
//                    $('input[name="contact_mobile"]').val(data.msg.mobile);
//                }
//            }
//        });
//        
//    }).keyup(function(){
//        var _this = $(this);
//        if (!_this.val()) {
//            // ele.addClass('hide');
//            $('input[name="contact_user"]').val('');
//            $('input[name="contact_mobile"]').val('');
//        }
//    });
    
    
    //检查提交
    $('a[data-dismiss="fileupload"]').click(function(){
        $('input[name="avatar_hidden"]').val("");
    });
    
    $('button[type="submit"]').click(function () {
        var nReg = /^\d+$/, floatReg = /^\d+(\.?)\d{0,2}$/;
        var post = {};
        
        post.avatar = $('input[name="avatar"]');
        post.hideAvatar = $('input[name="avatar_hidden"]');
        post.user_id = $('input[name="user_id"]');
        post.titleObj = $('input[name="title"]');
        post.desc = $('textarea[name="desc"]');
        
        post.lng = $('input[name="lng"]');
        post.lat = $('input[name="lat"]');
        post.province = $('input[name="province"]');
        post.city = $('input[name="city_name"]');
        post.city_id = $('input[name="city_id"]');
        
        // 基础信息
        var id1 = 'tab_1_1';
    
        if (post.avatar.val() == '' || !post.avatar.val()) {
            if (!post.hideAvatar.val()) {
                com.switchTable(id1);
                setTimeout(function(){
                    showMsg("请上传圈子头像", false);
                    post.avatar.focus();
                    App.scrollTo(post.avatar, -200);
                }, 200);
                return false;
            }
        }
    
        if (post.user_id.val() == '' || !post.user_id.val()) {
            com.switchTable(id1);
            setTimeout(function(){
                showMsg("请填写圈主UID信息", false);
                post.user_id.focus();
                App.scrollTo(post.user_id, -200);
            }, 200);
            return false;
        }
        
        if (post.titleObj.val() == '' || !post.titleObj.val()) {
            com.switchTable(id1);
            setTimeout(function(){
                showMsg("请填写圈子名称", false);
                post.titleObj.focus();
                App.scrollTo(post.titleObj, -200);
            }, 200);
            return false;
        }
        if (post.desc.val() == '' || !post.desc.val()) {
            com.switchTable(id1);
            setTimeout(function(){
                showMsg("请填写圈子简介", false);
                post.desc.focus();
                App.scrollTo(post.desc, -200);
            }, 200);
            return false;
        }
        
        var id2 = 'tab_1_2';
        if (!post.lng.val() || !post.lat.val() || !post.city.val()) {
            com.switchTable(id2);
            $('a[href="#tab_1_2"]').trigger('click');
            setTimeout(function(){
                showMsg("请选择位置信息", false);
                App.scrollTo(post.lng, -200);
                post.lng.focus();
            }, 400);
            return false;
        }
        
    });


});

    