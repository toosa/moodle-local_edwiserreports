define(["jquery","core/chartjs","report_elucidsitereport/defaultconfig","report_elucidsitereport/variables","report_elucidsitereport/flatpickr"],(function(e,o,r,t){var a=null,l=null,n=r.getPanel("#activeusersblock"),i=r.getPanel("#activeusersblock","body"),s=r.getPanel("#activeusersblock","title"),d=(r.getPanel("#activeusersblock","footer"),n+" .dropdown-menu[aria-labelledby='filter-dropdown']"),p=d+" .dropdown-item",c=n+" #filter-dropdown.dropdown-toggle",u=n+" #flatpickrCalender",g=i+" .ct-chart",h=i+" .loader",m=n+" button#filter-dropdown",f=i+" .refresh",b=n+t.exportUrlLink,C=null,v=s+" input.form-control.input",k=null;return{init:function(s){function y(s){e(g).hide(),e(h).show(),s||(s="weekly"),e.ajax({url:r.requestUrl,data:{action:"get_activeusers_graph_data_ajax",sesskey:e(n).data("sesskey"),data:JSON.stringify({filter:s})}}).done((function(e){a.graph.data=e.data,a.graph.labels=e.labels})).fail((function(e){console.log(e)})).always((function(){l=function(){l&&l.destroy();return o.defaults.global.defaultFontFamily=a.graph.fontFamily,o.defaults.global.defaultFontStyle=a.graph.fontStyle,l=new o(a.ctx,{type:a.graph.type,data:U(),options:a.graph.options})}(),t.changeExportUrl(s,b,t.filterReplaceFlag),e(i+" #updated-time > span.minute").html(0),setInterval(w,6e4),e(f).removeClass("refresh-spin"),e(h).hide(),e(g).fadeIn("slow"),k("activeUsers")}))}function w(){e(i+" #updated-time > span.minute").html(parseInt(e(i+" #updated-time > span.minute").text())+1)}function U(){return{labels:a.graph.labels,datasets:[{label:a.graph.labelName.activeUsers,data:a.graph.data.activeUsers,backgroundColor:a.graph.backgroundColor.activeUsers,borderColor:a.graph.borderColor.activeUsers,pointBorderColor:a.graph.borderColor.activeUsers,pointBackgroundColor:a.graph.borderColor.activeUsers,pointStyle:a.graph.pointStyle},{label:a.graph.labelName.enrolments,data:a.graph.data.enrolments,backgroundColor:a.graph.backgroundColor.enrolments,borderColor:a.graph.borderColor.enrolments,pointBorderColor:a.graph.borderColor.enrolments,pointBackgroundColor:a.graph.borderColor.enrolments,pointStyle:a.graph.pointStyle},{label:a.graph.labelName.completionRate,data:a.graph.data.completionRate,backgroundColor:a.graph.backgroundColor.completionRate,borderColor:a.graph.borderColor.completionRate,pointBorderColor:a.graph.borderColor.completionRate,pointBackgroundColor:a.graph.borderColor.completionRate,pointStyle:a.graph.pointStyle}]}}k=s,e(document).ready((function(){(a=r.getActiveUsersBlock())?(e(c).on("click",(function(){e(d).addClass("show")})),e(v).ready((function(){var o=e(v).attr("placeholder");e(v).val(o)})),e(document).click((function(o){e(o.target).hasClass("dropdown-menu")||e(o.target).parents(".dropdown-menu").length||e(d).removeClass("show")})),e(p+":not(.custom)").on("click",(function(){C=e(this).attr("value"),e(d).removeClass("show"),e(m).html(e(this).text()),y(C),e(u).val("Custom"),e(v).val("Custom")})),e(f).on("click",(function(){e(this).addClass("refresh-spin"),y(C)})),l=y(),e(u).flatpickr({mode:"range",altInput:!0,altFormat:"d/m/Y",dateFormat:"Y-m-d",maxDate:"today",appendTo:document.getElementById("activeUser-calendar"),onOpen:function(o){e(d).addClass("withcalendar")},onClose:function(){e(d).removeClass("withcalendar"),e(d).removeClass("show"),function(){C=e(u).val();var o=e(v).val();if(!C.includes("to"))return!1;r.changeExportUrl(C,b,t.filterReplaceFlag),e(m).html(o),e(u).val(""),y(C)}()}})):k("activeUsers")}))}}}));