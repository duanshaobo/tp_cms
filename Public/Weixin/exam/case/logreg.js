if (navigator.userAgent.match(/IEMobile\/10\.0/)) {
  var msViewportStyle = document.createElement('style')
  msViewportStyle.appendChild(
    document.createTextNode(
      '@-ms-viewport{width:auto!important}'
    )
  )
  document.querySelector('head').appendChild(msViewportStyle)
}

(function($) {
    $.extend({
        showModal: function (options){

            defaults = {
                modalClass:"modal-dialog",
                modalLabel:"优雅消息",
                modalFooter:"优雅的做有意义的事情",
                modalBody: ''
            };
            opts = $.extend({},defaults,options);
            $modal = $('#global-modal');

            $modal.find("#modal-dialog").attr('class',opts.modalClass)
                        .find('#modal-label').text(opts.modalLabel)
                    .end()
                        .find('#modal-body>div').html(opts.modalBody)
                    .end()
                        .find('#modal-footer>span').text(opts.modalFooter);
            $modal.modal({
                          backdrop: false,
                          show:true
                        })
            return $modal
        },
        messenger:function(ms){
            $.showModal({
                modalClass:"modal-dialog modal-sm",
                modalBody:ms
            })
        },
        getCookie:function(name) {
            var cookieValue = null;
            if (document.cookie && document.cookie != '') {
                var cookies = document.cookie.split(';');
                for (var i = 0; i < cookies.length; i++) {
                    var cookie = jQuery.trim(cookies[i]);
                    if (cookie.substring(0, name.length + 1) == (name + '=')) {
                        cookieValue = decodeURIComponent(cookie.substring(name.length + 1));
                        break;
                    }
                }
            }
            return cookieValue;
            },
        login:function() {
            var $login_btn = $('a.login','#nav-user')
            if ($login_btn.length){
                $login_btn.trigger('click')
            }else{
                $('body').secretary({
                    modalLabel:'欢迎登录优雅社区',
                    modalFooter:'幸福就是优雅的做有意义的事情',
                    url:'/accounts/login/',
                    callback:function(data,textStatus){
                        if($(data).find('form').length>0){
                               this.dealWithData(data,textStatus)
                            }else{
                                $('#global-modal').modal('hide')
                                $('#nav-user').replaceWith(data)
                                $('input[name="csrfmiddlewaretoken"]').each(function(){
                                    $(this).val($.getCookie('csrftoken'))
                                })
                                $('.login-required').removeClass('login-required')
                            }
                    }
                }).trigger('click')
            }
        },

    })


    $.fn.extend({

        secretary: function(options) {

            var defaults = {
                url:'',
                dataType:'html',
                modalClass:'modal-dialog',
                modalFooter:'',
                modalLabel:'',
                event:'click',
                data:false,
                callback:null,
                short_cut:null,
            }
            var opts = $.extend({},defaults,options);
            var KEY = 'secretary'
            var install = function(event){
                event.preventDefault()
                var $this = $(this)
                var secretary = $this.data(KEY)
                if(!secretary){
                    mopts = {
                    modalClass:opts.modalClass,
                    modalFooter:opts.modalFooter,
                    modalLabel:opts.modalLabel,
                    modalBody:'',
                    }
                    secretary = new Secretary($this,opts.url,opts.data,mopts,opts.callback,opts.short_cut)
                    $this.data(KEY,secretary)
                }
                secretary.dispatcher()
            }
            return   this.each(function(){
                $(this).on(opts.event,install)
            })
        },
        setMaxHeight:function(){
            var largest = 0
            var max = Math.max
            for(var item, i = -1;item=this[++i];){
                largest = max(largest,$(item).height())
            }
            for(var item,i=-1;item=this[++i];){
                $(item).height(largest)
            }
        },//setMaxHeight
        setHeight:function(ratio){
            height = $(this[0]).width()*ratio
            for(var item,i=-1;item=this[++i];){

                $(item).height(height)
            }
        },//setMaxHeight

    })

    var Secretary = function(obj,url,data,mopts,callback,short_cut){
        this._obj = obj
        if(!url){
            this._url = this._obj.attr('href')
        }else{
            this._url = url
        }
        this._mopts = mopts
        this._callback = callback
        this._data = data
        this._short_cut = short_cut
    }

    Secretary.prototype = {
        dispatcher:function(){
            $this = this._obj
            if($this.hasClass('login-required')){
                return  $("#nav-user a.login").trigger('click')
            }else{
                return this.getForm()
            }

        },
        getForm:function(){
            data = {}
            if (this._data){
                data = {hoster:this._obj.siblings('input[name="hoster"]').val()}
            }
            $.ajax({
                type:'GET',
                url:this._url,
                data:data,
                context:this,
                dataType:this._dataType,
                success:this.dealWithData,
            })
        },
        dealWithData:function(data,textStatus){
            this._mopts.modalBody = data
            $modal = $.showModal(this._mopts)
            $form = $('form',$modal)
            if($form){

                $form.on('submit',{form:$form,that:this},this.postForm)
                if(this._url =='/accounts/register/'){
                    $('#modal-body .login').secretary({
                        modalLabel:'欢迎登录优雅社区',
                        modalFooter:'幸福就是优雅的做有意义的事情'
                    })
                }else if(this._url =='/accounts/login/'){
                    $('#modal-body .register').secretary({
                            modalLabel:'欢迎注册优雅社区',
                            modalFooter:'Be happy,be free,be yourself!'
                        })
                }

                if (this._short_cut !== null){
                        this._short_cut()
                    }
            }
        },
        postForm:function(event){
            event.preventDefault()
            form = event.data.form
            that = event.data.that
            if (form.attr('action')){
                url = form.attr('action')
            }else{
                url = opts.url
            }
            var formData = new FormData();
            $.each(form.find("input[type='file']"), function(i, tag) {
                $.each($(tag)[0].files, function(i, file) {
                    formData.append(tag.name, file);
                });
            });
            var params = form.serializeArray();
            $.each(params, function (i, val) {
                formData.append(val.name, val.value);
            });
            $.ajax({
                type:'POST',
                url:url,
                dataType:'html',
                context:that,
                data:formData,
                processData: false,
                contentType: false,
                success:that.showResult,
            })
        },
        showResult:function(data,textStatus,jqXHR){
            if(this._callback){
                return this._callback(data,textStatus,jqXHR )
            }else{
                return this.dealWithData(data,textStatus)
            }
        },
    }

})(jQuery);



$(function(){

	function csrfSafeMethod(method) {
        return (/^(GET|HEAD|OPTIONS|TRACE)$/.test(method));
	}
	function sameOrigin(url) {
		var host = document.location.host;
		var protocol = document.location.protocol;
		var sr_origin = '//' + host;
		var origin = protocol + sr_origin;
		return (url == origin || url.slice(0, origin.length + 1) == origin + '/') ||
			(url == sr_origin || url.slice(0, sr_origin.length + 1) == sr_origin + '/') ||
			!(/^(\/\/|http:|https:).*/.test(url));
	}
	$.ajaxSetup({
		beforeSend: function(xhr, settings) {
			if (!csrfSafeMethod(settings.type) && sameOrigin(settings.url)) {
				xhr.setRequestHeader("X-CSRFToken", $.getCookie('csrftoken'));
			}
            //xhr.setRequestHeader('Content-Type', 'application/xml;charset=utf-8')
		},
        contentType:'application/x-www-form-urlencoded;charset=utf-8',
        error:function(xhr,textStatus,StatusCode){
                    $.messenger(xhr)
                    }
	});

    $("#ue-report a.feedback").secretary({
            modalLabel:'分享我的社区体验',
            modalFooter:'We can make a difference!'
        })

    $('#nav-user a.login').secretary(
        {
            modalLabel:'欢迎登录优雅社区',
            modalFooter:'幸福就是优雅的做有意义的事情',
            callback:function(data,textStatus){
                if($(data).find('form').length>0){
                       this.dealWithData(data,textStatus)
                    }else{
                        $('#global-modal').modal('hide')
                        $('#nav-user').replaceWith(data)
                        $('input[name="csrfmiddlewaretoken"]').each(function(){
                            $(this).val($.getCookie('csrftoken'))
                        })
                        $('.login-required').removeClass('login-required')
                    }
            }
        })

    $('#nav-user a.register').secretary(
        {
            modalLabel:'欢迎注册优雅社区',
            modalFooter:'Be happy,be free,be yourself!'
        })

    $('form.login-required textarea,form.login-required  input').on('focus',function(event){
        if($(this).parents('form.login-required').length){
            event.preventDefault()
            $.login()
            return $(this).off('focus')
        }else{
            return
        }
    })


	$('a[data-toggle="tooltip"],span[data-toggle="tooltip"],p[data-toggle="tooltip"]').tooltip({
							delay:300})

    $('a.delete-post').secretary({
        modalLabel:'删除',
        modalClass:"modal-dialog modal-sm",
        modalFooter:'让过去的成为过去',
        callback:function(data,textStatus){
                    if($(data).find('form').length>0){
                           this.dealWithData(data,textStatus)
                        }else{
                            $('#global-modal').modal('hide')
                            this._obj.parent().remove()
                        }
                },
    })
    $('a.complain-post').secretary({
        modalLabel:'投诉',
        modalFooter:'共同维护优雅的社区',
        data:true
    })

    $('a#experiment-apply,a#party-apply').secretary({
        modalLabel:'报名确认',
        modalClass:"modal-dialog modal-sm",
        modalFooter:'为科学事业献身咯',
        })
    $('a.vote').on('click',function(event){
        event.preventDefault()

        var $button = $(this)
        if($button.hasClass('login-required')){
            return $.login()
        }
        url = $button.attr('href')
        $.ajax({
            type:"POST",
            url:url,
            data:{},
            context:this,
            success:function(data,textStatus){
                $button.attr('class',data.info.class)
                    .attr('data-original-title',data.info.title)
                    .find('span').text(data.sum)

            }
        })
        return false

    })
})