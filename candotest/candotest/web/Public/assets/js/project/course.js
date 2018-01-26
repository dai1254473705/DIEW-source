
/**
 * 产品添加 页面处理js
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
        maxFileSize: 999000,
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
        var exists = $('span[name="course_exists_img"]');
        
        var zoomHtml = '<div class="exists_preview">';
            zoomHtml += '<div class="item">',
            zoomHtml += '<a data-rel="fancybox-button" href="[thumb]" title="[title]" download="[thumb]" data-gallery="">',
            zoomHtml += '<div class="zoom">',
            zoomHtml += '<img src="[thumb]" alt="[title]">',							
            zoomHtml += '<div class="zoom-icon"></div>',
            zoomHtml += '</div>',
            zoomHtml += '</a>',
            zoomHtml += '<div class="details" data-iid="" data-gid="" data-m="[md5]" data-iurl="[cover]">',
            zoomHtml += '<a name="image" href="javascript:void(0);" class="icon"><i class="icon-picture"></i></a>',
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



    // 课程表附件上传
    $('#pdf_upload').fileupload({
        url: urls.uploadFileUrl,
        dataType: 'json',
        autoUpload: true,
        acceptFileTypes: /(\.|\/)pdf$/i,
        maxFileSize: 999000,

    }).on('fileuploadadd', function (e, data) {

        var exists = $('#pdfs').find("p"), bar = $('#pdf_progress .bar');

        if (exists.length) {
            var file = data.files[0];
            var or = confirm("上传将覆盖之前的文件，确定上传？");
            if (!or) {
                return false;
            }
            bar.css({"width":"0","height":"0"});
            setTimeout(function(){
                bar.css('height','100%');
            }, 800);
        }

        //暂时不做操作，成功之后，输出路径和查看按钮
        //var file = data.files[0];
        //var html = '<p data-name="'+file.name+'"><span class="msg-danger bold">'+file.name+'</span></p>';
        //$('#pdfs').append(html);

    }).on('fileuploadprocessalways', function (e, data) {

        var index = data.index, file = data.files[index];

        //错误时候的处理
        if (file.error) {
            var name = file.name;
            console.log(name);
            $('p[data-name="'+name+'"]').remove();
            showMsg("文件" + file.name + '<br>' +file.error, false, 3000);
            return false;
        }

    }).on('fileuploadprogressall', function (e, data) {
        var progress = parseInt(data.loaded / data.total * 100, 10);
        $('#pdf_progress .bar').css('width',progress + '%');

    }).on('fileuploaddone', function (e, data) {
        if (data.result == undefined) {
            showMsg("上传错误，请重新上传", false);
            return false;
        }

        if (data.result.ok) {

            //先删除
            var exists = $('#pdfs').find("p")
            $('input[name="attachment"]').remove();
            exists.remove();

            var name = data.result.msg.original, url = data.result.msg.root_path + data.result.msg.url;

            var html = '<p class="span8" data-m="'+data.result.msg.md5+'">';
                html += '<span class="msg-danger bold mt15" style="margin-right:50px;">'+name+'</span>';
                html += '<a href="'+url+'" target="_blank" class="btn mini purple">点击查看</a>';
                html += '</p>';

            $('#pdfs').append(html);


            var PO = '<input type="hidden" name="attachment" value="'+data.result.msg.encode+'" data-m="'+data.result.msg.md5+'"/>';
            $('form').append(PO);

        }

    }).on('fileuploadfail', function (e, data) {


    }).prop('disabled', !$.support.fileInput)
        .parent().addClass($.support.fileInput ? undefined : 'disabled');

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
            imgDel(iid, _this);
        } else {
            _this.closest('.exists_preview').remove();
            $('input[data-m="'+m+'"]').remove();
        }
        
    });



    // 筛选项处理
    var TM = {
        flag: 0,
        box: {},
        form: $('#course_form'),
        init: function(){
            $('button[name="modal_sub"]').click(function(){
                var _this = $(this), checked = [];
                TM.box = _this.closest('.modal'), TM.flag = TM.box.data('flag');
                var checkbox = TM.box.find('input[name="access[]"]:checked');
                if (checkbox.length <= 0) {
                    showMsg("请至少选择一个类别", false, 2000);
                    return false;
                }

                var firstCheck = [];
                $.each(checkbox, function(i, v){
                    var _v = $(v), name =_v.data('name'), value = _v.val(), id = _v.data('id'), pid = _v.data('pid');
                    firstCheck[id] = {
                        id: id,
                        pid: pid,
                        name: name,
                        child: []
                    };
                    checked.push(value);
                });
                //TM._format(firstCheck);

                TM.write(checked);
            });
        },

        //递归分类
        _format: function(data, pid){
            var tmpData = [];
            for(var i in data){
                if (data[i].pid == pid) {
                    tmpData[data[i].id] = data[i];
                }
            }

            var result = [];
            for(var i in tmpData){
                if (tmpData[i].pid == pid) {
                    tmpData[i].child = TM._format(data, tmpData[i].id);
                    result[tmpData[i].id] = tmpData[i];
                }
            }

            return result;

        },

        write: function(data){
            var show = $('#sub_type_'+TM.flag), addr = show.find('.J_type');

            TM._clear();
            TM._setHidden(data);

            var addrHtml = TM.box.find('.span10').html();
            addr.html(addrHtml);

            //删除一些不必要的元素
            addr.find('#uniform-undefined span:not(".checked")').closest(".control-group").remove();
            addr.find('.J_remove_ck').remove();

            TM.box.modal("hide");
            TM.box.on('hidden.bs.modal', function(){
                show.fadeIn();
            });

        },

        _clear: function(){
            var show = $('#sub_type_'+TM.flag), addr = show.find('.J_type'), hidden = $('input[name*="sub_type['+TM.flag+']"]');
            addr.html('');
            hidden.remove();
        },
        _setHidden: function(data){
            for(var i in data){
                var v = data[i];
                var hi = '<input name="sub_type['+TM.flag+'][]" value="'+ v +'" type="hidden" >';
                TM.form.append(hi);
            }
        }
    };
    TM.init();

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

    //检查提交
    $('button[type="submit"]').click(function () {
        var nReg = /^\d+$/, floatReg = /^\d+(\.?)\d{0,2}$/;
        var post = {};
        post.agency_id = $('select[name="agency_id"]');
        post.price = $('input[name="price"]');
        post.img_upload = false;
        post.img_exists = parseInt($('span[name="course_exists_img"]').find('.exists_preview').length) ? true : false;
        post.titleObj = $('input[name*="title"]');


        if (!post.agency_id.val()) {
            showMsg("请选择机构", false);
            post.agency_id.focus();
            return false;
        }

        for(var i in JO.type){
            var ob = JO.type[i], id = ob.id, hidden = $('input[name*="sub_type['+id+']"]');
            if (hidden.length <= 0) {
                showMsg("请选择 “"+ob.name+"” 类别");
                return false;
            }
        }
        
        if (!floatReg.test(post.price.val())) {
            showMsg("请选择正确的课程单价");
            post.price.focus();
            return false;
        }
        
        //名称
        var title = true;
        $.each(post.titleObj, function(i, v){
            var _obj = $(v);
            if (_obj.val() == '' || !_obj.val()) {
                var oid = _obj.closest('.J_sub_tab_pane').prop('id');
                com.switchSubTable(oid);
                title = false;
                setTimeout(function(){
                    showMsg("请完整填写机构名称", false);
                    _obj.focus();
                }, 200);
                return false;
            }
        });
        if (!title) {
            return false;
        }
        
        if (!post.img_exists) {
            showMsg("请至少上传一张产品图片", false);
            com.switchTable('tab_1_3');
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
function imgDel(iid, obj) {
    if (!iid) {
        return false;
    }
    var _selfObj = obj;
    console.log(_selfObj);
    var or = confirm("确定要删除吗？");
    if (!or) {
        return false;
    }
    
    $.post(urls.deleteImgUrl, {img_id: iid}, function (data) {
        showMsg(data.msg, data.ok, 1500);
        if (data.ok) {
            _selfObj.closest(".item").closest('.exists_preview').remove();
        }
    });
}

//文章搜索后添加
function moveTr(obj) {
    var clone_tr = $(obj).closest('tr'),
            article_id = clone_tr.find('input').val(),
            linkedObj = $('table[name="article_linked"]');

    if (linkedObj.find('input[value="' + article_id + '"]').val()) {
        return false;
    }
    var add_html = '<tr>';
    add_html += clone_tr.html().replace('unlink[]', 'linked[]')
            .replace('moveTr(this)', 'delTr(this)')
            .replace('green-stripe', 'red-stripe')
            .replace('添加', '删除');
    add_html += "</tr>";
    $('table[name="article_linked"]').append(add_html);
    clone_tr.remove();
}

//文章添加后删除
function delTr(obj) {
    $(obj).closest('tr').remove();
}

//图片上传添加
function addImgDiv(obj) {
    var hideDiv = $('.upload_div').html();
    $('span[name="course_img_div"]').append(hideDiv);
}

function delImgDiv(obj) {
    $(obj).closest('.control-group').remove();
}

//产品属性添加
function addAttrDiv(obj) {
    var html = '<div class="control-group">';
    html += $(obj).closest('.control-group').html();
    htmlzoomHtml += '</div>';
    var newHtml = html.replace('addAttrDiv(this)', 'delAttrDiv(this)').replace('icon-plus', 'icon-minus');
    $(newHtml).insertAfter($(obj).closest('.control-group'));
}

function delAttrDiv(obj) {
    $(obj).closest('.control-group').remove();
}
    