define(["jquery","core/templates","core/fragment","report_elucidsitereport/variables","report_elucidsitereport/common"],function(s,p,u,f){return{init:function(o){var e="#wdm-lpstats-individual",t="#wdm-lp-select",r=e+" .table",n=e+" .loader",i=s("#wdm-userfilter .row .col-6:first-child"),l=s(e).find("#wdm-lp-dropdown"),a=null,d=0;function c(e,t){a&&(a.destroy(),s(r).hide(),s(n).show()),u.loadFragment("report_elucidsitereport","lpstats",o,{lpid:e,cohortid:t}).done(function(e){var t=JSON.parse(e);p.render("report_elucidsitereport/lpstatsinfo",t).then(function(e,t){p.replaceNode(r,e,t)}).fail(function(e){console.log(e)}).always(function(){s(r).show(),a=s(r).DataTable({dom:"<'pull-left'f><t><p>",oLanguage:{sEmptyTable:"No Users are enrolled in any Learning Programs"},responsive:!0}),s(n).hide()})})}s(document).ready(function(){i.html(l.html()),s(document).find(t).select2(),s(document).find(t).show(),l.remove();var e=s(document).find(t).val();c(e,d),s(f.cohortFilterItem).on("click",function(){d=s(this).data("cohortid"),s(f.cohortFilterBtn).html(s(this).text()),f.changeExportUrl(d,f.exportUrlLink,f.cohortReplaceFlag),c(e,d)}),s(document).find(t).on("change",function(){s(r).hide(),s(n).show(),e=s(document).find(t).val(),f.changeExportUrl(e,f.exportUrlLink,f.filterReplaceFlag),c(e,d)})})}}});