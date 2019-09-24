define(["jquery","core/notification","core/fragment","core/modal_factory","core/modal_events","core/templates","report_elucidsitereport/variables","report_elucidsitereport/select2","report_elucidsitereport/jquery.dataTables","report_elucidsitereport/dataTables.bootstrap4"],function(i,o,a,n,d,r,s){var l=null,e="#scheduletab",c="button.dropdown-toggle",u=e+" input#esr-sendduration",t=e+" .dropdown:not(.duration-dropdown)",f=t+" button.dropdown-toggle",p=e+" .dropdown.daily-dropdown button.dropdown-toggle",m=e+" .dropdown.weekly-dropdown button.dropdown-toggle",g=e+" .dropdown.monthly-dropdown button.dropdown-toggle",h=e+" input#esr-sendtime",b='<div class="w-full text-center"><i class="fa fa-spinner fa-spin" aria-hidden="true"></i></div>',v='<div class="alert alert-danger"><b>ERROR:</b> Error while scheduling email<div>',y='<div class="alert alert-success"><b>Success:</b> Email scheduled successfully<div>',w='<div class="alert alert-danger"><b>ERROR:</b> Name and Recepient Fields can not be empty<div>',k='<div class="alert alert-danger"><b>ERROR:</b> Invalid email adderesses space not allowed<div>',_='[data-plugin="tabs"] .nav-link, [data-plugin="tabs"] .tab-pane',x='[aria-controls="scheduletab"], #scheduletab';function R(e,a){return a.getRoot().find("#esr-shceduled-emails").DataTable({ajax:{url:s.requestUrl,type:s.requestType,data:{action:"get_scheduled_emails_ajax",sesskey:i(e).data("sesskey"),data:JSON.stringify({blockname:i(e).attr("data-blockname"),href:i(e).attr("href"),region:i(e).attr("data-region")})}},scrollY:"300px",scrollCollapse:!0,oLanguage:{sEmptyTable:"There is no scheduled emails"},columns:[{data:"esrtoggle"},{data:"esrname"},{data:"esrcomponent"},{data:"esrnextrun"},{data:"esrfrequency"},{data:"esrmanage"}],bInfo:!1,lengthChange:!1,paging:!1})}function T(e){var a="#wdm-elucidsitereport > div";e<780?i(a).addClass("col-lg-12"):i(a).removeClass("col-lg-12"),i(document).find(".table.dataTable").DataTable().draw()}i(document).ready(function(){T(i("#page-admin-report-elucidsitereport-index .page-content").width()),i(window).on("resize",function(){T(s.pluginPage.width())}),i(document).on("click",'.export-dropdown a[data-action="email"]',function(e){e.preventDefault();var t=this;n.create({type:n.types.SAVE_CANCEL,title:"Email Dialog Box",body:a.loadFragment("report_elucidsitereport","email_dialog",i(t).data("contextid"),{blockname:i(t).data("blockname")})},i(this)).done(function(e){var a=e.getRoot();a.on(d.hidden,function(){e.destroy()}),a.on(d.save,function(){!function(e,a){i.ajax({url:e.href,type:"POST",data:a.find("form").serialize()}).done(function(e){(e=i.parseJSON(e)).error?o.addNotification({message:e.errormsg,type:"error"}):o.addNotification({message:"Email has been sent",type:"info"})}).fail(function(){o.addNotification({message:"Failed to send the email",type:"error"})})}(t,a)}),e.setSaveButtonText("Send"),e.show()})}),i(document).on("click",'.export-dropdown a[data-action="emailscheduled"]',function(e){e.preventDefault();var t=this;n.create({title:"Schedule Emails",body:r.render("report_elucidsitereport/email_schedule_tabs",{})},i(this)).done(function(e){var a=e.getRoot();e.modal.addClass("modal-lg"),a.on(d.bodyRendered,function(){a.find("#esr-blockname").val(i(t).data("blockname")),a.find("#esr-region").val(i(t).data("region")),l=R(t,e)}),a.on(d.hidden,function(){e.destroy()}),function(e,a,t){a.on("click","#scheduletab .dropdown a.dropdown-item",function(){!function(e){var a=i(e).data("value"),t=i(e).text(),o=i(e).closest(".dropdown").find(c);o.text(t),o.data("value",a)}(this)}),a.on("click","#scheduletab .dropdown.duration-dropdown a.dropdown-item",function(){!function(e,a){var t=i(e).data("value");i(e).text();a.find(u).val(t),i(f).hide();var o=null;switch(t){case 1:o=i(m);break;case 2:o=i(g);break;default:o=i(p)}o.show();var n=o.data("value");i(h).val(n)}(this,a)}),a.on("click","#scheduletab .dropdown:not(.duration-dropdown) a.dropdown-item",function(){a.find(h).val(i(this).data("value"))}),a.on("click","#listemailstab .esr-email-sched-setting",function(){!function(e,a){var t=i(e).data("id"),o=i(e).data("blockname"),n=i(e).data("region");i.ajax({url:s.requestUrl,type:s.requestType,sesskey:i(e).data("sesskey"),data:{action:"get_scheduled_email_detail_ajax",sesskey:i(e).data("sesskey"),data:JSON.stringify({id:t,blockname:o,region:n})}}).done(function(e){e.error?console.log(e):(function(e,a,n){var d=null,r=null;i.each(e.data,function(e,a){if("object"==typeof a)n.find("#esr-blockname").val(a.blockname),n.find("#esr-region").val(a.region);else if("esrduration"===e){var t='[aria-labelledby="durationcount"] .dropdown-item[data-value="'+a+'"]';d=a,n.find(t).click()}else if("esrtime"===e)r=a;else if("esremailenable"===e){var o=i('input[name="'+e+'"]');a?o.prop("checked",!0):o.prop("checked",!1)}else i('[name="'+e+'"]').val(a)});var t='.dropdown-item[data-value="'+r+'"]',o=null;switch(d){case"1":o=i(".weekly-dropdown");break;case"2":o=i(".monthly-dropdown");break;default:o=i(".daily-dropdown")}o.find(t).click()}(e,0,a),a.find(_).removeClass("active show"),a.find(x).addClass("active show"))}).fail(function(e){console.log(e)})}(this,a)}),a.on("click","#listemailstab .esr-email-sched-delete",function(){!function(a,e,t){var o=i(a).data("id"),n=i(a).data("blockname"),d=i(a).data("region");i.ajax({url:s.requestUrl,type:s.requestType,sesskey:i(a).data("sesskey"),data:{action:"delete_scheduled_email_ajax",sesskey:i(a).data("sesskey"),data:JSON.stringify({id:o,blockname:n,region:d})}}).done(function(e){e.error||(l&&l.destroy(),l=R(a,t),errorBox.html(y))})}(this,0,t)}),a.on("change","#listemailstab [id^='esr-toggle-']",function(){!function(a,e,t){var o=i(a).data("id"),n=i(a).data("blockname"),d=i(a).data("region");i.ajax({url:s.requestUrl,type:s.requestType,sesskey:i(a).data("sesskey"),data:{action:"change_scheduled_email_status_ajax",sesskey:i(a).data("sesskey"),data:JSON.stringify({id:o,blockname:n,region:d})}}).done(function(e){e.error||(l&&l.destroy(),l=R(a,t),errorBox.html(y))})}(this,0,t)}),function(t,o,n){o.on("click",'[data-action="save"]',function(){var a=o.find(".esr-form-error");if(a.html(b).show(),function(e,a){var t=e.find('[name="esrname"]').val(),o=e.find('[name="esrrecepient"]').val();if(""==t||""==o)return a.html(w).show(),!1;return!!/^(\s?[^\s,]+@[^\s,]+\.[^\s,]+\s?,)*(\s?[^\s,]+@[^\s,]+\.[^\s,]+)$/g.test(o)||(a.html(k).show(),!1)}(o.find("form"),a)){var e=s.getUrlParams(t.href,"filter");i.ajax({url:M.cfg.wwwroot+"/report/elucidsitereport/download.php?format=emailscheduled&filter="+e,type:"POST",data:o.find("form").serialize()}).done(function(e){(e=i.parseJSON(e)).error?(a.html(v),console.log(e.error)):(l&&l.destroy(),l=R(t,n),a.html(y))}).fail(function(e){a.html(v),console.log(e)}).always(function(){a.delay(3e3).fadeOut("slow")})}}),o.on("click",'[data-action="cancel"]',function(){o.find("[name^=esr]").val("")})}(e,a,t)}(t,a,e),e.show()})})})});