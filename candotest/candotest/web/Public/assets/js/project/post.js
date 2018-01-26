
/**
 * 发帖 页面处理js
 * @param {type} msg
 * @returns {undefined}
 */

$(function () {
    
    // 图片上传
    $('#fileupload').fileupload({
        url: urls.uploadUrl,
        dataType: 'json',
        autoUpload: false,
        acceptFileTypes: /(\.|\/)(gif|jpe?g|png)$/i,
        maxFileSize: 5120000,
        disableImageResize: /Android(?!.*Chrome)|Opera/.test(window.navigator.userAgent),
        previewMaxWidth: 100,
        previewMaxHeight: 100,
        previewCrop: true
        
    }).on('fileuploadadd', function (e, data) {
        var hi = '<input type="hidden" name="uploadAdd" data-name="'+data.files[0].name+'">', bar = $('#progress .bar');
        bar.css({"width":"0","height":"0"});
        setTimeout(function(){
            bar.css('height','100%');
        }, 800);
        $('body').append($(hi).data(data));
		
    }).on('fileuploadprocessalways', function (e, data) {

        data.context = $('<div class="preview"><div/>').appendTo('#files');
        $.each(data.files, function (i, file) {
            $('<span data-name="'+file.name+'"><span/>').appendTo(data.context);
        });

        var index = data.index, file = data.files[index], node = $(data.context.children()[index]);
        if (file.preview) {
            node.prepend(file.preview);
        }

        //错误时候的处理
        if (file.error) {
            var name = file.name;
            console.log(name);
            $('input[data-name="'+name+'"]').remove();
            $('span[data-name="'+name+'"]').closest('.preview').remove();
            showMsg("图片" + file.name + '<br>' +file.error, false, 3000);
            return false;
        }

        
    }).on('fileuploadprogressall', function (e, data) {
        var progress = parseInt(data.loaded / data.total * 100, 10);
        $('#progress .bar').css('width',progress + '%');

    }).on('fileuploaddone', function (e, data) {
        var exists = $('span[name="post_exists_img"]');
        
        var zoomHtml = '<div class="exists_preview">';
            zoomHtml += '<div class="item">',
            zoomHtml += '<a data-rel="fancybox-button" href="[thumb]" title="[title]" download="[thumb]" data-gallery="">',
            zoomHtml += '<div class="zoom">',
            zoomHtml += '<img src="[thumb]" alt="[title]">',							
            zoomHtml += '<div class="zoom-icon"></div>',
            zoomHtml += '</div>',
            zoomHtml += '</a>',
            zoomHtml += '<div class="details" data-iid="" data-gid="" data-m="[md5]" data-iurl="[cover]">',
            // zoomHtml += '<a name="image" href="javascript:void(0);" class="icon"><i class="icon-picture"></i></a>',
            zoomHtml += '<a name="delete" href="javascript:void(0);" class="icon"><i class="icon-remove"></i></a>',		
            zoomHtml += '</div>',
            zoomHtml += '</div>',
            zoomHtml += '</div>';
        if (data.result.ok) {
            zoomHtml = zoomHtml.replace(/\[thumb\]/g, data.result.msg.root_path + data.result.msg.url);
            zoomHtml = zoomHtml.replace(/\[cover\]/g, data.result.msg.encode);
            zoomHtml = zoomHtml.replace(/\[title\]/g, data.result.msg.title);
            zoomHtml = zoomHtml.replace(/\[md5\]/g, data.result.msg.md5);
            exists.append(zoomHtml);

            // 删除上传值信息
            $('input[name="uploadAdd"]').each(function (i, v) {
                $(v).remove();
            });
            $.each(data.files, function (i, v) {
                $(".preview:eq(0)").remove();
            });

            var HO = '<input type="hidden" name="thumb[]" value="'+data.result.msg.encode+'" data-m="'+data.result.msg.md5+'"/>';
            $('form').append(HO);
        }
        
    }).on('fileuploadfail', function (e, data) {
        $.each(data.files, function (index) {
            var error = $('<span class="text-danger"/>').text('File upload failed.');
            $(data.context.children()[index])
                    .append('<br>')
                    .append(error);
        });
    }).prop('disabled', !$.support.fileInput)
            .parent().addClass($.support.fileInput ? undefined : 'disabled');
    
    // 开始上传
    $('.start').click(function () {
        var upload = $('input[name="uploadAdd"]');
        if (upload.length <= 0) {
            showMsg("请选择图片", false, 2000);
            return false;
        }

        upload.each(function (i, v) {
            var data = $(v).data();
            data.submit();
        });
    });
    
    // 设置封面图片
    $(document).on('click', 'a[name="image"]', function(){
        var _this = $(this), _parent = _this.closest('.details');
        var gid = _parent.data('gid'), iid = _parent.data('iid'), m = _parent.data('m'), iurl = _parent.data('iurl');
        $('input[name="cover"]').remove();
        var h = '<input type="hidden" name="cover" value="' + iurl + '">';
        $('form').append(h);
        $('.item').removeClass("cover");
        _this.closest('.item').addClass("cover");
    });
    
    // 删除图片
    $(document).on('click', 'a[name="delete"]', function(){
        var _this = $(this), _parent = _this.closest('.details');
        var gid = _parent.data('gid'), iid = _parent.data('iid'), m = _parent.data('m'), iurl = _parent.data('iurl');
        if (_this.closest('.item').hasClass('cover')) {
            showMsg("当前图片是封面图片<br>请重新设置封面图片后，再删除", false, 3000);
            return false;
        }
        
        if(gid && iid){
            imgDel(iid, gid, _this);
        } else {
            _this.closest('.exists_preview').remove();
            $('input[data-m="'+m+'"]').remove();
        }
        
    });
    

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
    var eleTitle = $('.J_title_o');
    $('input[name="user_id"]').blur(function(){
        var _this = $(this);
        var user_id = $(this).val();
        if(!user_id){
            return false;
        }
        
        $.ajax({
            url: urls.getUserContact,
            dataType: 'json',
            data: {user_id:user_id},
            type: "POST",
            beforeSend: function () {
                eleTitle.removeClass('hide');
                App.blockUI(eleTitle);
            },
            success: function(data){
                App.unblockUI(eleTitle);
                if(data.ok){
                    $('input[name="contact_user"]').val(data.msg.truename);
                    $('input[name="contact_mobile"]').val(data.msg.mobile);
                }else{
                    showMsg(data.msg, data.ok, 2000);
                    setTimeout(function () {
                        eleTitle.addClass('hide');
                    }, 500);
                    return false;
                }
            }
        });
        
    }).keyup(function(){
        var _this = $(this);
        if (!_this.val()) {
            // ele.addClass('hide');
            $('input[name="contact_user"]').val('');
            $('input[name="contact_mobile"]').val('');
        }
    });
    
    
    //异步圈子信息
    var eleCircle = $('.J_circle_o');
    $('input[name="circle_id"]').blur(function(){
        var _this = $(this);
        var circle_id = $(this).val();
        if(!circle_id){
            return false;
        }
        
        $.ajax({
            url: urls.getCircle,
            dataType: 'json',
            data: {circle_id:circle_id},
            type: "POST",
            beforeSend: function () {
                eleCircle.removeClass('hide');
                App.blockUI(eleCircle);
            },
            success: function(data){
                App.unblockUI(eleCircle);
                if(!data.ok){
                    showMsg(data.msg, data.ok, 2000);
                    setTimeout(function () {
                        eleCircle.addClass('hide');
                    }, 500);
                    return false;
                }
    
                $('input[name="circle_title"]').val(data.msg.title);
                $('input[name="circle_main"]').val(data.msg.main.name);
                
                if (data.msg.section.length) {
                    var section_id = $('select[name="section_id"]'), html = '';
                    $.each(data.msg.section, function (i, v) {
                        html += '<option value="'+v.id+'">'+v.title+'</option>';
                    });
                    section_id.html(html);
                }
                
            }
        });
    }).keyup(function(){
        var _this = $(this);
        if (!_this.val()) {
            eleCircle.addClass('hide');
            $('input[name="circle_title"]').val('');
            $('input[name="circle_main"]').val('');
            $('select[name="section_id"]').html('<option value="">-请选择所属版块-</option>')
        }
    });
    
    
    // 异步话题信息
    var eleTopic = $('.J_topic_o');
    $('input[name="topic_id"]').blur(function(){
        var _this = $(this);
        var topic_id = $(this).val();
        if(!topic_id){
            return false;
        }
        
        $.ajax({
            url: urls.getTopic,
            dataType: 'json',
            data: {topic_id:topic_id},
            type: "POST",
            beforeSend: function () {
                eleTopic.removeClass('hide');
                App.blockUI(eleTopic);
            },
            success: function(data){
                App.unblockUI(eleTopic);
                if(!data.ok){
                    showMsg(data.msg, data.ok, 2000);
                    setTimeout(function () {
                        eleTopic.addClass('hide');
                    }, 500);
                    return false;
                }
                
                $('input[name="topic_title"]').val(data.msg.title);
                $('input[name="topic_main"]').val(data.msg.user_info.name);
                
            }
        });
    }).keyup(function(){
        var _this = $(this);
        if (!_this.val()) {
            eleTopic.addClass('hide');
            $('input[name="topic_title"]').val('');
            $('input[name="topic_main"]').val('');
        }
    });

    //========================================================================================================================================================================================================
    /**
     * 检查提交
     */
    //========================================================================================================================================================================================================
    $('button[type="submit"]').click(function () {
        var nReg = /^\d+$/, floatReg = /^\d+(\.?)\d{0,2}$/;
        var post = {};
        
        post.circle_id = $('input[name="circle_id"]');
        post.topic_id = $('input[name="topic_id"]');
        post.textareaObj = $('textarea[name="content"]');
        post.user_id = $('input[name="user_id"]');
        
        post.lng = $('input[name="lng"]');
        post.lat = $('input[name="lat"]');
        post.province = $('input[name="province"]');
        post.city = $('input[name="city_name"]');
        post.city_id = $('input[name="city_id"]');
    
        post.img_exists = parseInt($('span[name="post_exists_img"]').find('.exists_preview').length) ? true : false;
        
        post.video_exists = parseInt($('#files_video').find('.preview').length) ? true : false;
        
        
        
        // 基础信息
        var id1 = 'tab_1_1';
        if (post.circle_id.val() == '' || !post.circle_id.val()) {
            com.switchTable(id1);
            setTimeout(function(){
                showMsg("请填写圈子ID信息", false);
                post.circle_id.focus();
                App.scrollTo(post.circle_id, -200);
            }, 200);
            return false;
            
        }else{
            
            var check = true;
            $.ajax({
                url: urls.getCircle,
                dataType: 'json',
                data: {circle_id:post.circle_id.val()},
                type: "POST",
                async: false,
                beforeSend: function () {},
                success: function(data){
                    if(!data.ok){
                        setTimeout(function(){
                            showMsg(data.msg, data.ok, 2000);
                            App.scrollTo(post.circle_id, -200);
                        }, 200);
                        check = false;
                    }
                }
            });
            if (!check) {
                return false;
            }
        }
        
        // if (post.topic_id.val() == '' || !post.topic_id.val()) {
        //     com.switchTable(id1);
        //     setTimeout(function(){
        //         showMsg("请填写话题ID信息", false);
        //         post.topic_id.focus();
        //         App.scrollTo(post.topic_id, -200);
        //     }, 200);
        //     return false;
        //
        // }else{
        //     var check = true;
        //     $.ajax({
        //         url: urls.getTopic,
        //         dataType: 'json',
        //         data: {topic_id:post.topic_id.val()},
        //         type: "POST",
        //         async: false,
        //         beforeSend: function () {},
        //         success: function(data){
        //             if(!data.ok){
        //                 setTimeout(function(){
        //                     showMsg(data.msg, data.ok, 2000);
        //                     App.scrollTo(post.topic_id, -200);
        //                 }, 200);
        //                 check = false;
        //             }
        //         }
        //     });
        //     if (!check) {
        //         return false;
        //     }
        // }
        
        
        if (post.textareaObj.val() == '' || !post.textareaObj.val()) {
            com.switchTable(id1);
            setTimeout(function(){
                showMsg("请填写帖子内容", false);
                post.textareaObj.focus();
                App.scrollTo(post.textareaObj, -200);
            }, 200);
            return false;
        }
        
        if (post.user_id.val() == '' || !post.user_id.val()) {
            com.switchTable(id1);
            setTimeout(function(){
                showMsg("请填写用户UID信息", false);
                post.user_id.focus();
                App.scrollTo(post.user_id, -200);
            }, 200);
            return false;
            
        } else {
            var check = true;
            $.ajax({
                url: urls.getUserContact,
                dataType: 'json',
                data: {user_id:post.user_id.val()},
                type: "POST",
                async: false,
                beforeSend: function () {},
                success: function(data){
                    if(!data.ok){
                        setTimeout(function(){
                            showMsg(data.msg, data.ok, 2000);
                            App.scrollTo(post.user_id, -200);
                        }, 200);
                        check = false;
                    }
                }
            });
            if (!check) {
                return false;
            }
        }
        
        
        // 位置信息
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
        
        
        var id3 = 'tab_1_3';
        if (!post.img_exists && !post.video_exists) {
            com.switchTable(id3);
            var _el = $('a[href="#tab_1_3"]');
            _el.trigger('click');
            App.scrollTo(_el, -150);
            setTimeout(function(){
                showMsg("请至少上传一张图片或上传一个视频", false, 2000);
            }, 400);
            return false;
        }
        
    });


});

/**
 * 删除产品图片
 * @param {type} gid
 * @param {type} iid
 * @returns {undefined}
 */
function imgDel(iid, gid, obj) {
    if (!iid) {
        return false;
    }
    var _selfObj = obj;
    console.log(_selfObj);
    
    //询问框
    var msg = "确定要删除吗？<br/> 删除后，当前帖子中将直接删除此图片信息！！";
    layer.confirm(msg, {}, function(){
        if (!iid) {
            showMsg('请选中要操作的内容', false);
            return false;
        }
        
        $.post(urls.deleteImgUrl, {id: iid, sid:gid}, function (data) {
            showMsg(data.msg, data.ok, 1500);
            if (data.ok) {
                _selfObj.closest(".item").closest('.exists_preview').remove();
            }
        });
        
    }, function(){});
    
}


//图片上传添加
function addImgDiv(obj) {
    var hideDiv = $('.upload_div').html();
    $('span[name="post_img_div"]').append(hideDiv);
}

function delImgDiv(obj) {
    $(obj).closest('.control-group').remove();
}

    