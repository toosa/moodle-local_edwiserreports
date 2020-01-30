define(["jquery","report_elucidsitereport/defaultconfig","core/templates","report_elucidsitereport/select2"],(function(e,t,n){e(document).ready((function(){e("#ed_rpm").select2({multiple:!0}),e("#ed_lps").select2({multiple:!0}),e("#ed_courses").select2({multiple:!0}),e("#ed_lps").on("change",(function(o){e("div[class^='lp']").show();var a=[];if(e(o.currentTarget).find("option:selected").each((function(t,n){a[t]=e(n).val()})),a.length>1){var i=a.indexOf("0");i>-1&&(a.splice(i,1),o.preventDefault(),e("#ed_lps").select2("val",a))}e.ajax({url:t.requestUrl,type:t.requestType,dataType:t.requestDataType,data:{action:"get_customqueryreport_data_ajax",sesskey:M.cfg.sesskey,data:JSON.stringify({lpids:a})}}).done((function(t){e("#ed_courses").html("");var o={courses:t};n.render("report_elucidsitereport/customquery_options",o).then((function(t,o){n.replaceNodeContents(e("#ed_courses"),t,o)}))})).fail((function(e){})),a.length||e("div[class^='lp']").hide()})),e("#ed_rpm").on("change",(function(o){e("div[class^='rpm']").show();var a=[];if(e(o.currentTarget).find("option:selected").each((function(t,n){a[t]=e(n).val()})),a.length>1){var i=a.indexOf("0");i>-1&&(a.splice(i,1),e("#ed_rpm").select2("val",a))}e.ajax({url:t.requestUrl,type:t.requestType,dataType:t.requestDataType,data:{action:"get_customqueryreport_rpm_data_ajax",sesskey:M.cfg.sesskey,data:JSON.stringify({rpmids:a})}}).done((function(t){e("#ed_lps").html(""),e("#ed_courses").html("");var o="report_elucidsitereport/customquery_lpoptions",a={lps:t.lps};if(t.lps.length>0&&n.render(o,a).then((function(t,o){n.appendNodeContents(e("#ed_lps"),t,o)})),t.courses.length>0){o="report_elucidsitereport/customquery_options",a={courses:t.courses};n.render(o,a).then((function(t,o){n.appendNodeContents(e("#ed_courses"),t,o)}))}})).fail((function(e){console.log(e)})),a.length||(e("div[class^='rpm']").hide(),e("div[class^='lp']").hide())})),e("#ed_courses").on("change",(function(t){var n=[];if(e(t.currentTarget).find("option:selected").each((function(t,o){n[t]=e(o).val()})),n.length>1){var o=n.indexOf("0");o>-1&&(n.splice(o,1),e("#ed_courses").select2("val",n))}}));var o=t.getPanel("#customQueryReportBlock");let a=e(o).find("#customQueryReportsForm");e(o).find("#customqueryenroll").flatpickr({mode:"range",altInput:!0,altFormat:"d/m/Y",dateFormat:"Y-m-d",maxDate:"today",defaultDate:["today",(new Date).fp_incr(-30)],onClose:function(e,t,n){if(2==e.length){let t=e[0].getTime(),n=e[1].getTime();a.find("input[name=enrolstartdate]").val(t/1e3),a.find("input[name=enrolenddate]").val(n/1e3)}else a.find("input[name=enrolstartdate]").val(""),a.find("input[name=enrolenddate]").val("")}});e(o).find("#customquerycompletion").flatpickr({mode:"range",altInput:!0,altFormat:"d/m/Y",dateFormat:"Y-m-d",maxDate:"today",defaultDate:["today",(new Date).fp_incr(-30)],onClose:function(e,t,n){if(2==e.length){let t=e[0].getTime(),n=e[1].getTime();a.find("input[name=completionstartdate]").val(t/1e3),a.find("input[name=completionenddate]").val(n/1e3)}else a.find("input[name=completionstartdate]").val(""),a.find("input[name=completionenddate]").val("")}}),e(document).on("click","#customqueryenroll ~ button.input-search-close",(function(){e("#customqueryenroll ~ input.form-control").val(""),a.find("input[name=enrolstartdate]").val(""),a.find("input[name=enrolenddate]").val("")})),e(document).on("click","#customquerycompletion ~ button.input-search-close",(function(){e("#customquerycompletion ~ input.form-control").val(""),a.find("input[name=completionstartdate]").val(""),a.find("input[name=completionenddate]").val("")})),e("#customQueryReportDownload").click((function(t){var n;jQuery("#ed_courses > option:selected").length>=1?(n=[],e(o).find("input[type=checkbox]:checked").each((function(e,t){n.push(t.id)})),a.find("input[name=reporttype]").val("queryReport"),a.find("input[name=checkedFields]").val(n),function(){var t,n,i;t=e(o).find("#ed_rpm").val(),a.find("input[name=reportingmanagers]").val(t),n=e(o).find("#ed_lps").val(),a.find("input[name=lps]").val(n),i=e(o).find("#ed_courses").val(),a.find("input[name=courses]").val(i)}()):(e(".coursealert").show(),setTimeout((function(){e(".coursealert").hide()}),3e3),t.preventDefault())})),e("[data-hide]").on("click",(function(){e(this).closest("."+e(this).attr("data-hide")).hide()}))}))}));