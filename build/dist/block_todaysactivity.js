define(["jquery","core/chartjs","report_elucidsitereport/defaultconfig"],function(c,n,r){return{init:function(){var o,e=r.getPanel("#todaysactivityblock"),i=r.getPanel("#todaysactivityblock","body"),t=e+" #flatpickrCalender";function d(t){var a=c(e).data("sesskey");c.ajax({url:r.requestUrl,type:r.requestType,dataType:r.requestDataType,data:{action:"get_todaysactivity_data_ajax",sesskey:a,data:JSON.stringify({date:t})}}).done(function(t){c.each(t.data,function(t,a){var o=c(i+" #todays-"+t);o.find(".loader").hide(),o.find(".data").html(a)}),function(t){r.todaysActivityBlock.graph.data=t;t={labels:r.todaysActivityBlock.graph.labels,datasets:[{label:r.todaysActivityBlock.graph.labelName,data:r.todaysActivityBlock.graph.data,backgroundColor:r.todaysActivityBlock.graph.backgroundColor}]};o&&o.destroy();o=new n(r.todaysActivityBlock.ctx,{type:r.todaysActivityBlock.graph.type,options:r.todaysActivityBlock.graph.options,data:t})}(t.data.visitshour)}).fail(function(t){console.log(t)})}c(document).ready(function(){d(),c(t).flatpickr({dateFormat:"d M Y",maxDate:"today",defaultDate:["today"],onChange:function(t,a,o){section.find("loader").show(),d(a)}})})}}});