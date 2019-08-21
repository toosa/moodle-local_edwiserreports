define(["jquery","core/chartjs","report_elucidsitereport/defaultconfig","report_elucidsitereport/flatpickr"],function(b,v,y){return{init:function(){var o=y.activeUsersBlock,a=y.getPanel("#activeusersblock"),e=y.getPanel("#activeusersblock","body"),r=y.getPanel("#activeusersblock","title"),t=(y.getPanel("#activeusersblock","footer"),a+" .dropdown-menu[aria-labelledby='filter-dropdown']"),l=t+" .dropdown-item",n=a+" #filter-dropdown.dropdown-toggle",d=a+" #flatpickrCalender",s=e+" .ct-chart",i=e+" .loader",p=a+" button#filter-dropdown",c=r+" .refresh",u=a+" .dropdown-menu[aria-labelledby='export-dropdown'] .dropdown-item",g=null,m=r+" input.form-control.input";function h(e){b(s).addClass("d-none"),b(i).removeClass("d-none"),b.ajax({url:y.requestUrl,data:{action:"get_activeusers_graph_data_ajax",sesskey:b(a).data("sesskey"),data:JSON.stringify({filter:e})}}).done(function(e){o.graph.data=e.data,o.graph.labels=e.labels}).fail(function(e){console.log(e)}).always(function(){C=function(){C&&C.destroy();return v.defaults.global.defaultFontFamily=o.graph.fontFamily,v.defaults.global.defaultFontStyle=o.graph.fontStyle,C=new v(o.ctx,{type:o.graph.type,data:function(){return{labels:o.graph.labels,datasets:[{label:o.graph.labelName.activeUsers,data:o.graph.data.activeUsers,backgroundColor:o.graph.backgroundColor.activeUsers,borderColor:o.graph.borderColor.activeUsers,pointBorderColor:o.graph.borderColor.activeUsers,pointBackgroundColor:o.graph.borderColor.activeUsers,pointStyle:o.graph.pointStyle},{label:o.graph.labelName.enrolments,data:o.graph.data.enrolments,backgroundColor:o.graph.backgroundColor.enrolments,borderColor:o.graph.borderColor.enrolments,pointBorderColor:o.graph.borderColor.enrolments,pointBackgroundColor:o.graph.borderColor.enrolments,pointStyle:o.graph.pointStyle},{label:o.graph.labelName.completionRate,data:o.graph.data.completionRate,backgroundColor:o.graph.backgroundColor.completionRate,borderColor:o.graph.borderColor.completionRate,pointBorderColor:o.graph.borderColor.completionRate,pointBackgroundColor:o.graph.borderColor.completionRate,pointStyle:o.graph.pointStyle}]}}(),options:o.graph.options})}(),setInterval(f,6e4),b(c).removeClass("refresh-spin"),b(i).addClass("d-none"),b(s).removeClass("d-none")})}function f(){b(r+" #updated-time > span.minute").html(parseInt(b(r+" #updated-time > span.minute").text())+1)}b(document).ready(function(){b(n).on("click",function(){b(t).addClass("show")}),b(m).ready(function(){var e=b(m).attr("placeholder");b(m).val(e)}),b(document).click(function(e){b(e.target).hasClass("dropdown-menu")||b(e.target).parents(".dropdown-menu").length||b(t).removeClass("show")}),b(l+":not(.custom)").on("click",function(){g=b(this).attr("value"),b(t).removeClass("show"),b(p).html(b(this).text()),y.changeExportUrl(g,u,V.filterReplaceFlag),h(g),b(d).val("Custom"),b(m).val("Custom")}),b(c).on("click",function(){b(this).addClass("refresh-spin"),b(r+" #updated-time > span.minute").html(0),h(g)}),b(d).flatpickr({mode:"range",altInput:!0,altFormat:"d/m/Y",dateFormat:"Y-m-d",maxDate:"today",appendTo:document.getElementById("activeUser-calendar"),onOpen:function(e){b(t).addClass("withcalendar")},onClose:function(){b(t).removeClass("withcalendar"),b(t).removeClass("show"),function(){g=b(d).val();var e=b(m).val();g.includes("to")&&(y.changeExportUrl(g,u,V.filterReplaceFlag),b(p).html(e),b(d).val(""),h(g))}()}})});var C=h()}}});