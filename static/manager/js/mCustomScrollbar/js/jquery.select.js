
//版本 1.6.6
//作者：聂未

; (function ($, window, document, undefined) {
    $.fn.extend({
        select: function (options) {

            //默认参数
            var defaults = {
                width: "auto",            //生成的select框宽度
                minWidth: "none",              //最小宽度值,默认不设置（特殊注明：单位只能是px）
                lineHeight: "17px",          //控制高度
                opacity: true,            //是否设置背景透明
                mainBgColor: "",       //当opacity为false时才生效,主体框的背景色
                searchBgColor: "#fff",      //当opacity为false时才生效,搜索框的背景色
                listBgColor: "#fff",           //当opacity为false时才生效,结果列表的背景色
                listMaxHeight: "200px",     //生成的下拉列表最大高度
                themeColor: "#00bb9c",    //主题颜色
                fontColor: "#000",        //字体颜色
                fontFamily: "'Helvetica Neue', arial, sans-serif",    //字体种类
                fontSize: "15px",           //字体大小
                showSearch: false,        //是否启用搜索框
                rowHoverColor: "#00bb9c", //移动选择时，每一行的hover底色
                fontHoverColor: "#fff",   //移动选择时，每一行的字体hover颜色
                mainContent: "请选择",    //选择显示框的默认文字
                searchContent: "关键词搜索",   //搜索框的默认提示文字
                callBack: function () { }   //选择事件的回调函数
            }

            //将默认的参数对象和传进来的参数对象合并在一起
            var opts = $.extend(defaults, options);

            //格式化opts,去除空格字符
            $.each(opts, function (index, element) {
                if (typeof (element) == "string")
                    opts[index] = element.toString().replace(/\s/g, "");
            });

            //重新为原select标签对象命名
            var $this = this;

            //标识原select是否已经运行过该插件
            if (!$this.attr("state"))
                $this.attr("state", "done");
            else
                return $this;
           

            //获取随机标识字符串
            function randomString(len) {
                len = len || 32;
                var $chars = 'ABCDEFGHJKMNPQRSTWXYZabcdefhijkmnprstwxyz2345678';    /****默认去掉了容易混淆的字符oOLl,9gq,Vv,Uu,I1****/
                var maxPos = $chars.length;
                var pwd = '';
                for (i = 0; i < len; i++) {
                    pwd += $chars.charAt(Math.floor(Math.random() * maxPos));
                }
                return pwd;
            }
            var randomSign = randomString();

            //隐藏原select标签，并且在其后添加需要的html元素
            $this.hide();
            $this.after('\
                    <div class="select_container_nw" sign='+ randomSign + ' tabindex="0" val="" text="" style="z-index:9999;">\
                        <div class="select_main">\
                           <span class="select_content">'+ opts.mainContent + '</span>\
                           <span class="select_arrow">\
                              <i class="iconfont" style="font-size:8px;color:' + opts.themeColor + ';">&#xe654;</i>\
                           </span>\
                           <div class="unable" style="position:absolute;width:100%;height:100%;left:0px;top:0px;z-index:99999;display:none;"></div>\
                        </div>\
                        <div class="select_list">\
                            <div class="select_list_search">\
                               <input type="text" class="select_input" placeholder=' + opts.searchContent + ' />\
                               <i class="iconfont search_svg" style="font-size:22px;color:' + opts.themeColor + ';">&#xe639;</i>\
                            </div>\
                            <div class="select_list_body">\
                                <ul class="select_list_ul">\
                                        <li class="no_result">无 结 果 </li>\
                                </ul>\
                            </div>\
                        </div>\
                    </div>');

            //拿到新生成的对select替代的div
            var $This = $this.next();

            //灌入原select的数据
            $this.find("option").each(function (index, element) {
                var $li = $('<li val=' + $(element).val() + '>' + $(element).text() + '</li>');
                if ($(element).prop("selected") && !!$(element).attr("selected")) {
                    $li.addClass("list_current_sign");
                    $This.find(".select_content").text($(element).text());
                    $This.attr({ "val": $(element).val(), "text": $(element).text() });
                }
                $This.find(".no_result").before($li);
            });

            //传进来的参数操作
            if (opts.width != "" && opts.width != "auto")
                $This.css("width", opts.width);
            if (opts.lineHeight != "" && parseInt(opts.lineHeight) > 17)
                $This.css("line-height", opts.lineHeight);
            if (opts.listMaxHeight != "" && opts.listMaxHeight != "200px")
                $This.find(".select_list_body").css("max-height", opts.listMaxHeight);
            if (typeof (opts.showSearch) == "boolean" && !opts.showSearch)
                $This.find(".select_list_search").hide();
            if (opts.fontColor != "") {
                $This.find(".select_content,.select_input,li").css("color", opts.fontColor);
                $This.find("li").hover(function () {
                    $(this).css({ "color": opts.fontHoverColor });
                }, function () {
                    $(this).css({ "color": opts.fontColor });
                });
            }
            if (opts.fontFamily != "" && opts.fontFamily != "'Helvetica Neue', arial, sans-serif")
                $This.find(".select_content,.select_input,li").css("font-family", opts.fontFamily);
            if (opts.listBgColor != "" && opts.listBgColor != "#fff") {
                $This.find(".select_list,.select_input").css("background-color", opts.listBgColor);
            }
            if (opts.rowHoverColor != "") {
                $This.find("li").hover(function () {
                    $(this).css({ "background-color": opts.rowHoverColor });
                }, function () {
                    $(this).css({ "background-color": opts.opacity ? "transparent" : opts.listBgColor });
                });
            }
            if (typeof (opts.opacity) == "boolean" && opts.opacity)
            {
                $This.find(".select_list,.select_input").css("background-color", "transparent");
                $This.find("li").css("background-color", "transparent");
            }
            else if (typeof (opts.opacity) == "boolean" && !opts.opacity) {
                if (opts.mainBgColor != "")
                    $This.find(".select_main").css("background-color", opts.mainBgColor);
                if (opts.searchBgColor != "#fff")
                    $This.find(".select_input").css("background-color", opts.searchBgColor);
                if (opts.listBgColor != "#fff")
                    $This.find(".select_list_ul li").css("background-color", opts.listBgColor);
            }
            if (opts.fontSize != "" && opts.fontSize != "15px")
                $This.find(".select_content,.select_input,li").css("font-size", opts.fontSize);
            if (opts.width == "auto") {
                $This.css("width", ($This.find(".select_list_ul").width() + 35) + "px");
                if(opts.minWidth!=""&&opts!="none"&&$This.width()<parseInt(opts.minWidth))   //设置最小宽度限制的情况
                    $This.css("width", opts.minWidth);
            }
            $This.find(".select_list").hide();  //隐藏下拉列表

            //设置三角i标签样式
            var gap = 1;
            do {
                $This.find(".select_arrow").find("i").css("font-size", gap + "px");
                gap++;
            }
            while ($This.find(".select_arrow").find("i").height() < $This.find(".select_main").height() - 6);
            $This.find(".select_arrow").css({ "display": "block", "height": $This.find(".select_arrow").find("i").height() + "px", "padding": ($This.find(".select_main").height() - $This.find(".select_arrow").find("i").height()) / 2 + "px 0px", "line-height": $This.find(".select_arrow").find("i").height() + 2 + "px" });

            //设置查询放大镜i标签和查询框样式
            if (typeof (opts.showSearch) == "boolean" && opts.showSearch) {
                if (opts.width == "auto")
                    $This.find(".select_list_search").css("width", $This.width());   //避免input固定宽度过宽，影响自动宽度
                $This.find(".select_input").css("height", ($This.find(".select_main").height() - 3) + "px");
                $This.find(".search_svg").css({ "top": parseInt(($This.find(".select_main").height() - $This.find(".select_arrow").find("i").height()) / 2 - 2) + "px", "font-size": $This.find(".select_arrow").find("i").height() + "px", "line-height": $This.find(".select_input").height() + "px" });
            }

            //样式完成后,隐藏选中元素
            $This.find(".list_current_sign").addClass("list_current");

            //获取此时列表的高度
            var list_height = $This.find(".select_list").height();
            var allowClick = true;

            //列表过滤函数
            function Search(self) {
                var isAllHide = true;
                $This.find(".select_list_ul li").not(".no_result").each(function (index, element) {
                    if ($(element).text().indexOf($(self).val()) == -1)
                        $(element).hide();
                    else if ($(element).hasClass("list_current"))
                        $(element).hide();
                    else {
                        isAllHide = false;
                        $(element).show();
                    }
                });

                if (isAllHide)
                    $This.find(".select_list_ul .no_result").show();
                else
                    $This.find(".select_list_ul .no_result").hide();

                $This.find(".select_list").css("height", "auto");
                list_height = $This.find(".select_list").height();
            }

            //处理宽度为%时的情况
            if (opts.width.indexOf("%")!=-1) {
                $This.find(".select_main").css("width", "100%");
                $This.find(".select_list").css("width","100%");
            }

            //动态调整搜索框的宽度
            if (typeof (opts.showSearch) == "boolean" && opts.showSearch)
                $This.find(".select_input").css({ "width": ($This.find(".select_main").width() - 41) + "px" });


            //为显示框添加点击出现下拉框事件
            $This.find(".select_arrow,.select_content").click(function () {
                if (allowClick)
                    allowClick = false;
                else
                    return;
                $This.find(".select_arrow").toggleClass("cast_rotate");

                if ($This.find(".select_list").hasClass("list_open")) {
                    $This.find(".select_list").removeClass("list_open").animate({ "height": "0px" }, 200, function () {
                        $This.find(".select_list").hide();
                        allowClick = true;
                    });
                }
                else
                    $This.find(".select_list").addClass("list_open").css({ "height": "0px" }).show().animate({ "height": list_height + "px" }, 200, function () {
                        allowClick = true;
                        //$This[0].focus();    //设置主体焦点
                    });
            });

            //动态调整显示框的宽度
            //alert($This.find(".select_arrow").width());  //此处取不到值
            $This.find(".select_content").css({ "width": ($This.width() - 40) + "px" });

            //var blurBlock = false;  // 阻塞动画执行
            //var timeId0 = null;  //定时器1
            //var timeId1 = null;  //定时器2

            //为每一行元素添加点击事件,焦点失去事件设置了延迟,所以会先执行点击事件
            $This.find(".select_list_body").delegate("li", "click", function (event) {

                //无结果直接返回
                if ($(this).hasClass("no_result")) {
                    //clearTimeout(timeId0);
                    //blurBlock = true;
                    //timeId0 = setTimeout(function () {
                    //    blurBlock = false;
                    //}, 120);
                    return;
                }

                //点击逻辑
                $This.find(".select_list_body li").removeClass("list_current").show();
                $(this).addClass("list_current").hide();
                Search($This.find(".select_input"));
                $This.find(".select_content").text($(this).text());
                $This.attr({ "val": $(this).attr("val") == null ? '' : $(this).attr("val"), "text": $(this).text() });
                $this.val($(this).attr("val"));

                list_height = $This.find(".select_list").height();

                //动画
                $This.find(".select_content").trigger("click");

                //回调函数
                opts.callBack();

                //阻止事件冒泡
                event = event ? event : window.event;  //浏览器兼容
                event.stopPropagation();
            });

            ////主体和搜索框失去焦点事件
            //$This.blur(function () {
            //    clearTimeout(timeId1);
            //    timeId1=setTimeout(function () {
            //        //alert(2222222222);
            //        if ($This.find(".select_list").hasClass("list_open") && !$This.find(".select_input").is(":focus") && !blurBlock) {    //选择框未打开和焦点进入文本域失效
            //            $This.find(".select_content").trigger("click");
            //        }
            //    }, 100);
            //});
            //$This.find(".select_input").blur(function () {
            //    clearTimeout(timeId1);
            //    timeId1 = setTimeout(function () {
            //        //alert(2222222222);
            //        if (!blurBlock) {       //搜索无结果失效
            //            //alert(blurBlock);
            //            $This.find(".select_content").trigger("click");
            //        }
            //    }, 100);
            //});


            //为整个文档注册一个点击事件,仿失去焦点隐藏下拉框 
            $(document).click(function (event) {
                if ($This.find(".select_list").hasClass("list_open") && ($(event.target).closest(".select_container_nw").length == 0
                    || $(event.target).closest(".select_container_nw").attr("sign") != randomSign)) {
                    $This.find(".select_content").trigger("click");
                }
            });

            //为搜索框添加keyup事件
            $This.find(".select_input").on("keyup", function (event) {
                Search(this);

                //阻止事件冒泡
                event = event ? event : window.event;  //浏览器兼容
                event.stopPropagation();
            });

            //为搜索框添加propertychange事件(IE8专有事件)
            $This.find(".select_input").on("propertychange", function (event) {
                if ($(this).val().length <= 0)
                    Search(this);

                //阻止事件冒泡
                event = event ? event : window.event;  //浏览器兼容
                event.stopPropagation();
            });

            //引用滚动插件
            $This.find(".select_list_body").mCustomScrollbar({
                theme: "minimal",
                advanced: { autoExpandHorizontalScroll: true }
            });
            $This.find(".select_list_body .mCSB_dragger_bar").css({ "background-color": "#00bb9c" });

            //传进来的参数操作，需要写在滚动插件调用之后
            if (opts.themeColor != "" && opts.themeColor != "#00bb9c") {
                $This.find(".select_main,.select_input,.select_list").css("border-color", opts.themeColor);
                $This.find(".select_list_body .mCSB_dragger_bar").css({ "background-color": opts.themeColor });
            }

            //API  设置插件是否启用
            $this.IsOnUse = function (boolParam) {
                if(boolParam)
                    $This.find(".unable").hide();
                else
                    $This.find(".unable").show();
            }

            //API 返回true表示插件已经初始化完成
            $this.IsDone = function () {
                return true;
            }

            //返回原jquery对象,保持链式调用
            return $this;

            //////////////////////////////////////////////////////////代码内部
        }
    });
})(jQuery, window, document)
