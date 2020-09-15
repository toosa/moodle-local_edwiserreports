define(["jquery","core/notification","core/fragment","core/modal_factory","core/modal_events","core/templates","core/str","report_elucidsitereport/variables","report_elucidsitereport/jspdf","report_elucidsitereport/select2","report_elucidsitereport/jquery.dataTables","report_elucidsitereport/dataTables.bootstrap4"],(function(e,a,t,o,n,r,d,i,l){var s=null,c="#scheduletab",u=c+" input#esr-sendtime",f='<div class="w-full text-center"><i class="fa fa-spinner fa-spin" aria-hidden="true"></i></div>',m='<div class="alert alert-danger"><b>ERROR:</b> Error while scheduling email<div>',p='<div class="alert alert-success"><b>Success:</b> Email scheduled successfully<div>',h='<div class="alert alert-danger"><b>ERROR:</b> Email deletion failed<div>';function v(a,t){var o=t.getRoot().find("#esr-shceduled-emails");return e(document).on("click",'[aria-controls="listemailstab"]',(function(){e(window).resize()})),o.DataTable({ajax:{url:i.requestUrl,type:i.requestType,data:{action:"get_scheduled_emails_ajax",sesskey:e(a).data("sesskey"),data:JSON.stringify({blockname:e(a).attr("data-blockname"),href:e(a).attr("href"),region:e(a).attr("data-region")})}},scrollY:"300px",scrollX:!0,scrollCollapse:!0,language:{searchPlaceholder:"Search shceduled email",emptyTable:"There is no scheduled emails"},order:[[1,"asc"]],columns:[{data:"esrtoggle",orderable:!1},{data:"esrname",orderable:!0},{data:"esrcomponent",orderable:!0},{data:"esrnextrun",orderable:!0},{data:"esrfrequency",orderable:!0},{data:"esrmanage",orderable:!1}],responsive:!0,bInfo:!1,lengthChange:!1,paging:!1})}function b(a){var t="#wdm-elucidsitereport > div";a<780?e(t).addClass("col-lg-12"):e(t).removeClass("col-lg-12"),e(t).find(".table.dataTable").DataTable().draw()}e(document).ready((function(){b(e("#page-admin-report-elucidsitereport-index .page-content").width()),e(window).on("resize",(function(){b(i.pluginPage.width())})),e(document).on("click",'.download-links button[value="pdf"]',(function(a){a.preventDefault(),e(document).find("#cover-spin")&&e("body").append('<div id="cover-spin"></div>'),e(document).find("#cover-spin").show(0);var t=e(this).closest("form");e.ajax({url:t.attr("action"),type:"POST",data:{type:e(this).val(),block:t.find('input[name="block"]').val(),filter:t.find('input[name="filter"]').val(),cohortid:t.find('input[name="cohortid"]').val(),region:t.find('input[name="region"]').val()}}).done((function(e){e=JSON.parse(e);var a=new l("p","pt","a4"),t={top:40,bottom:30,left:10,width:"100%"};a.setFontSize(10);a.fromHTML(e.data.html,t.left,t.top,{width:t.width,elementHandlers:{"#bypassme":function(e,a){return!0}}},(function(t){a.save(e.data.filename)}),t)})).always((function(){e(document).find("#cover-spin").hide()}))})),e(document).on("click",'.download-links button[value="email"]',(function(r){r.preventDefault();var d=this;o.create({type:o.types.SAVE_CANCEL,title:i.getEmailModalHeader(e(d).data("blockname"),0),body:t.loadFragment("report_elucidsitereport","email_dialog",e(d).data("contextid"),{blockname:e(d).data("blockname")})},e(this)).done((function(t){var o=t.getRoot();o.on(n.hidden,(function(){t.destroy()})),o.on(n.save,(function(){!function(t,o){e.ajax({url:t.href,type:"POST",data:o.find("form").serialize()}).done((function(t){(t=e.parseJSON(t)).error?a.addNotification({message:t.errormsg,type:"error"}):a.addNotification({message:"Email has been sent",type:"info"})})).fail((function(){a.addNotification({message:"Failed to send the email",type:"error"})}))}(d,o)})),t.setSaveButtonText("Send"),t.show()}))})),e(document).on("click",'.download-links button[value="emailscheduled"]',(function(t){t.preventDefault();var l=this,c=i.getScheduledEmailFormContext();o.create({title:i.getEmailModalHeader(e(l).data("blockname"),1),body:r.render("report_elucidsitereport/email_schedule_tabs",c)},e(this)).done((function(t){var o=t.getRoot();t.modal.addClass("modal-lg"),o.on(n.bodyRendered,(function(){o.find("#esr-blockname").val(e(l).data("blockname")),o.find("#esr-region").val(e(l).data("region")),s=v(l,t)})),o.on(n.hidden,(function(){t.destroy()})),function(t,o,n){o.on("click","#scheduletab .dropdown a.dropdown-item",(function(){!function(a){var t=e(a).data("value"),o=e(a).text(),n=e(a).closest(".dropdown").find("button.dropdown-toggle");n.text(o),n.data("value",t)}(this)})),o.on("click","#scheduletab .dropdown.duration-dropdown a.dropdown-item",(function(){!function(a,t){var o=e(a).data("value");e(a).text();t.find("#scheduletab input#esr-sendduration").val(o),e("#scheduletab .dropdown:not(.duration-dropdown) button.dropdown-toggle").hide();var n=null;switch(o){case 1:n=e("#scheduletab .dropdown.weekly-dropdown button.dropdown-toggle");break;case 2:n=e("#scheduletab .dropdown.monthly-dropdown button.dropdown-toggle");break;default:n=e("#scheduletab .dropdown.daily-dropdown button.dropdown-toggle")}n.show();var r=n.data("value");e(u).val(r)}(this,o)})),o.on("click","#scheduletab .dropdown:not(.duration-dropdown) a.dropdown-item",(function(){o.find(u).val(e(this).data("value"))})),o.on("click","#listemailstab .esr-email-sched-setting",(function(){!function(a,t){var o=e(a).data("id"),n=e(a).data("blockname"),r=e(a).data("region");e.ajax({url:i.requestUrl,type:i.requestType,sesskey:e(a).data("sesskey"),data:{action:"get_scheduled_email_detail_ajax",sesskey:e(a).data("sesskey"),data:JSON.stringify({id:o,blockname:n,region:r})}}).done((function(a){a.error?console.log(a):(!function(a,t,o){var n=null,r=null;e.each(a.data,(function(a,t){if("object"==typeof t)o.find("#esr-blockname").val(t.blockname),o.find("#esr-region").val(t.region);else if("esrduration"===a){var d='[aria-labelledby="durationcount"] .dropdown-item[data-value="'+t+'"]';n=t,o.find(d).click()}else if("esrtime"===a)r=t;else if("esremailenable"===a){var i=e('input[name="'+a+'"]');t?i.prop("checked",!0):i.prop("checked",!1)}else e('[name="'+a+'"]').val(t)}));var d='.dropdown-item[data-value="'+r+'"]',i=null;switch(n){case"1":i=e(".weekly-dropdown");break;case"2":i=e(".monthly-dropdown");break;default:i=e(".daily-dropdown")}i.find(d).click()}(a,0,t),t.find('[data-plugin="tabs"] .nav-link, [data-plugin="tabs"] .tab-pane').removeClass("active show"),t.find('[aria-controls="scheduletab"], #scheduletab').addClass("active show"))})).fail((function(e){console.log(e)}))}(this,o)})),o.on("click","#listemailstab .esr-email-sched-delete",(function(t){var r=this;d.get_strings([{key:"confirmemailremovaltitle",component:"report_elucidsitereport"},{key:"confirmemailremovalquestion",component:"report_elucidsitereport"},{key:"yes",component:"moodle"},{key:"no",component:"moodle"}]).done((function(d){a.confirm(d[0],d[1],d[2],d[3],e.proxy((function(){!function(a,t,o){var n=e(a).data("id"),r=e(a).data("blockname"),d=e(a).data("region"),l=t.find(".esr-form-error");l.html(f).show(),e.ajax({url:i.requestUrl,type:i.requestType,sesskey:e(a).data("sesskey"),data:{action:"delete_scheduled_email_ajax",sesskey:e(a).data("sesskey"),data:JSON.stringify({id:n,blockname:r,region:d})}}).done((function(e){e.error?l.html(h):(s&&s.destroy(),s=v(a,o),l.html('<div class="alert alert-success"><b>Success:</b> Email deleted successfully<div>'))})).fail((function(e){l.html(h),console.log(e)})).always((function(){l.delay(3e3).fadeOut("slow")}))}(r,o,n)}),t.currentTarget))}))})),o.on("change","#listemailstab [id^='esr-toggle-']",(function(){!function(a,t,o){var n=e(a).data("id"),r=e(a).data("blockname"),d=e(a).data("region");e.ajax({url:i.requestUrl,type:i.requestType,sesskey:e(a).data("sesskey"),data:{action:"change_scheduled_email_status_ajax",sesskey:e(a).data("sesskey"),data:JSON.stringify({id:n,blockname:r,region:d})}}).done((function(e){e.error||(s&&s.destroy(),s=v(a,o),errorBox.html(p))}))}(this,0,n)})),function(a,t,o){t.on("click",'[data-action="save"]',(function(){var n=t.find(".esr-form-error");if(n.html(f).show(),function(e,a){var t=e.find('[name="esrname"]').val(),o=e.find('[name="esrrecepient"]').val();if(""==t||""==o)return a.html('<div class="alert alert-danger"><b>ERROR:</b> Name and Recepient Fields can not be empty<div>').show(),!1;if(!/^(\s?[^\s,]+@[^\s,]+\.[^\s,]+\s?,)*(\s?[^\s,]+@[^\s,]+\.[^\s,]+)$/g.test(o))return a.html('<div class="alert alert-danger"><b>ERROR:</b> Invalid email adderesses space not allowed<div>').show(),!1;return!0}(t.find("form"),n)){var r=i.getUrlParams(a.href,"filter"),d=i.getUrlParams(a.href,"cohortid");e.ajax({url:M.cfg.wwwroot+"/report/elucidsitereport/download.php?format=emailscheduled&filter="+r+"&cohortid="+d,type:"POST",data:t.find("form").serialize()}).done((function(t){(t=e.parseJSON(t)).error?(n.html(m),console.log(t.error)):(s&&s.destroy(),s=v(a,o),n.html(p))})).fail((function(e){n.html(m),console.log(e)})).always((function(){n.delay(3e3).fadeOut("slow")}))}})),t.on("click",'[data-action="cancel"]',(function(){t.find('[name^=esr]:not(.d-none):not([id="esr-toggle-"])').val(""),t.find("#esr-id").val(-1)}))}(t,o,n)}(l,o,t),t.show()}))}))}))}));